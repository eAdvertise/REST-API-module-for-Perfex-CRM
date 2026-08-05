<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Webhook Service (v3)
 *
 * Manages webhook delivery, logging, the async delivery queue, SSRF
 * protection and HMAC-signed requests.
 *
 * Delivery modes (option api_webhook_delivery_mode):
 *  - immediate (default): send synchronously when the event fires (legacy)
 *  - queue: enqueue and deliver via Perfex cron (with an admin-load fallback),
 *           with automatic retries and exponential backoff
 */
class Api_Webhook_Service
{
    /** Claims older than this many minutes are considered stale and re-queued */
    const STALE_CLAIM_MINUTES = 10;

    /** Retry backoff (minutes) indexed by attempt number */
    const RETRY_BACKOFF = [1 => 1, 2 => 5, 3 => 15, 4 => 60, 5 => 240];

    private $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->database();
    }

    /**
     * Send webhook for an event
     *
     * @param string $event Event name
     * @param array $data Event data
     * @return void
     */
    public function triggerWebhooks($event, $data)
    {
        // Get active webhooks for this event
        $webhooks = $this->getWebhooksForEvent($event);

        if (empty($webhooks)) {
            return;
        }

        $mode = function_exists('get_option') ? get_option('api_webhook_delivery_mode') : '';

        foreach ($webhooks as $webhook) {
            if ($mode === 'queue') {
                $this->enqueue($webhook, $event, $data);
            } else {
                $this->sendWebhook($webhook, $event, $data);
            }
        }
    }

    /**
     * Get webhooks that should be triggered for an event
     *
     * @param string $event Event name
     * @return array
     */
    private function getWebhooksForEvent($event)
    {
        $this->CI->db->where('active', 1);
        $webhooks = $this->CI->db->get(db_prefix() . 'api_webhooks')->result_array();

        $matching = [];
        foreach ($webhooks as $webhook) {
            $events = explode(',', $webhook['events']);
            $events = array_map('trim', $events);

            // Support wildcard matching
            if (in_array('*', $events) || in_array($event, $events)) {
                $matching[] = $webhook;
            }
        }

        return $matching;
    }

    /**
     * Build the standard payload envelope for an event.
     *
     * @param string $event
     * @param array $data
     * @param string|null $secret Legacy in-body signature when secret set
     * @return array
     */
    private function buildPayload($event, $data, $secret = null)
    {
        $payload = [
            'event'     => $event,
            'timestamp' => time(),
            'data'      => $data,
        ];

        // Legacy in-body signature kept for existing consumers
        if (!empty($secret)) {
            $payload['signature'] = $this->generateSignature($payload, $secret);
        }

        return $payload;
    }

    /**
     * Send a webhook synchronously (immediate mode / queue worker).
     *
     * @param array $webhook Webhook configuration
     * @param string $event Event name
     * @param array $data Event data
     * @return bool
     */
    public function sendWebhook($webhook, $event, $data)
    {
        $payload = $this->buildPayload($event, $data, isset($webhook['secret']) ? $webhook['secret'] : null);

        return $this->deliver($webhook, $event, $payload);
    }

    /**
     * Perform the HTTP delivery of a prepared payload (shared by immediate
     * mode and the queue worker) with SSRF protection and HMAC headers.
     *
     * @param array $webhook
     * @param string $event
     * @param array $payload Full payload envelope
     * @param int $attempt Attempt number (for the log row)
     * @return bool
     */
    public function deliver($webhook, $event, $payload, $attempt = 1)
    {
        $payloadString = json_encode($payload);

        // Log webhook attempt
        $logId = $this->logWebhookAttempt($webhook['id'], $event, $webhook['url'], $payload, $attempt);

        // SSRF guard: refuse to contact loopback/link-local/metadata targets
        $strict     = function_exists('get_option') ? (get_option('api_webhook_ssrf_strict') == '1') : false;
        $resolvedIp = null;
        $ssrfError  = null;
        if (!self::isUrlSafe($webhook['url'], $strict, $resolvedIp, $ssrfError)) {
            $message = 'Blocked by SSRF protection: ' . $ssrfError;
            $this->updateWebhookLog($logId, false, null, null, $message);
            $this->updateWebhookStats($webhook['id'], false);
            return false;
        }

        // Parse custom headers
        $headers = [];
        if (!empty($webhook['headers'])) {
            $customHeaders = json_decode($webhook['headers'], true);
            if (is_array($customHeaders)) {
                $headers = $customHeaders;
            }
        }
        $headers['Content-Type'] = 'application/json';
        $headers['User-Agent']   = 'Perfex-CRM-API/3.0';
        $headers['X-Perfex-Event'] = $event;

        // HMAC signature header with timestamp (replay protection):
        // X-Perfex-Signature: t=<unix>,v1=hmac_sha256("{t}.{body}", secret)
        if (!empty($webhook['secret'])) {
            $headers['X-Perfex-Signature'] = self::buildSignatureHeader($payloadString, $webhook['secret'], time());
        }

        $success      = false;
        $responseCode = null;
        $responseBody = null;
        $errorMessage = null;

        try {
            $sslVerify = true;
            if (function_exists('get_option') && get_option('api_webhook_ssl_verify') === '0') {
                $sslVerify = false;
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $webhook['url']);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payloadString);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->formatHeaders($headers));
            curl_setopt($ch, CURLOPT_TIMEOUT, isset($webhook['timeout']) ? (int)$webhook['timeout'] : 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $sslVerify);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // redirects could bypass the SSRF check
            if (defined('CURLPROTO_HTTP') && defined('CURLPROTO_HTTPS')) {
                curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
            }

            // Pin the checked IP to prevent DNS rebinding between check & send
            if ($resolvedIp !== null && filter_var($resolvedIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $parts = parse_url($webhook['url']);
                if (!empty($parts['host'])) {
                    $port = isset($parts['port'])
                        ? (int)$parts['port']
                        : ((isset($parts['scheme']) && strtolower($parts['scheme']) === 'https') ? 443 : 80);
                    curl_setopt($ch, CURLOPT_RESOLVE, [$parts['host'] . ':' . $port . ':' . $resolvedIp]);
                }
            }

            $responseBody = curl_exec($ch);
            $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (curl_errno($ch)) {
                $errorMessage = curl_error($ch);
            } else {
                // Consider 2xx status codes as success
                $success = ($responseCode >= 200 && $responseCode < 300);

                if (!$success) {
                    $errorMessage = "HTTP {$responseCode}: " . substr((string)$responseBody, 0, 500);
                }
            }

            curl_close($ch);
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
        }

        // Update log
        $this->updateWebhookLog($logId, $success, $responseCode, $responseBody, $errorMessage);

        // Update webhook statistics
        $this->updateWebhookStats($webhook['id'], $success);

        return $success;
    }

    // ------------------------------------------------------------------
    // Async queue
    // ------------------------------------------------------------------

    /**
     * Enqueue an event for asynchronous delivery.
     *
     * @param array $webhook
     * @param string $event
     * @param array $data
     * @return int Queue row id
     */
    public function enqueue($webhook, $event, $data)
    {
        $payload = $this->buildPayload($event, $data, isset($webhook['secret']) ? $webhook['secret'] : null);

        $maxAttempts = isset($webhook['retry_count']) ? max(1, (int)$webhook['retry_count']) : 3;

        $this->CI->db->insert(db_prefix() . 'api_webhook_queue', [
            'webhook_id'      => $webhook['id'],
            'event'           => $event,
            'payload'         => json_encode($payload),
            'status'          => 'pending',
            'attempts'        => 0,
            'max_attempts'    => $maxAttempts,
            'next_attempt_at' => date('Y-m-d H:i:s'),
            'created_at'      => date('Y-m-d H:i:s'),
        ]);

        return $this->CI->db->insert_id();
    }

    /**
     * Process due queue entries. Safe to call concurrently: rows are claimed
     * atomically with a per-run token so two workers never send the same job.
     *
     * @param int $limit Max jobs this run
     * @return array [processed, delivered, failed]
     */
    public function processQueue($limit = 25)
    {
        $queueTable = db_prefix() . 'api_webhook_queue';
        $now        = date('Y-m-d H:i:s');

        // Recover stale claims (worker died mid-flight)
        $this->CI->db->query(
            'UPDATE `' . $queueTable . "` SET status = 'pending', claim_token = NULL, claimed_at = NULL
             WHERE status = 'processing' AND claimed_at < (NOW() - INTERVAL " . self::STALE_CLAIM_MINUTES . ' MINUTE)'
        );

        // Atomic claim: single UPDATE with ORDER BY / LIMIT
        $token = bin2hex(random_bytes(16));
        $this->CI->db->query(
            'UPDATE `' . $queueTable . "` SET status = 'processing', claim_token = ?, claimed_at = ?
             WHERE status = 'pending' AND next_attempt_at <= ?
             ORDER BY id ASC LIMIT " . (int)$limit,
            [$token, $now, $now]
        );

        $jobs = $this->CI->db
            ->where('claim_token', $token)
            ->get($queueTable)
            ->result_array();

        $delivered = 0;
        $failed    = 0;

        foreach ($jobs as $job) {
            $webhook = $this->CI->db
                ->where('id', $job['webhook_id'])
                ->get(db_prefix() . 'api_webhooks')
                ->row_array();

            $payload = json_decode($job['payload'], true);
            $attempt = (int)$job['attempts'] + 1;

            $ok = false;
            if ($webhook && (int)$webhook['active'] === 1 && is_array($payload)) {
                $ok = $this->deliver($webhook, $job['event'], $payload, $attempt);
            }

            if ($ok) {
                $delivered++;
                $this->CI->db->where('id', $job['id'])->update($queueTable, [
                    'status'       => 'delivered',
                    'attempts'     => $attempt,
                    'claim_token'  => null,
                    'claimed_at'   => null,
                    'completed_at' => date('Y-m-d H:i:s'),
                    'last_error'   => null,
                ]);
            } else {
                $isFinal = $attempt >= (int)$job['max_attempts'] || !$webhook;
                if ($isFinal) {
                    $failed++;
                }
                $backoff = self::backoffMinutes($attempt);
                $this->CI->db->where('id', $job['id'])->update($queueTable, [
                    'status'          => $isFinal ? 'failed' : 'pending',
                    'attempts'        => $attempt,
                    'claim_token'     => null,
                    'claimed_at'      => null,
                    'next_attempt_at' => date('Y-m-d H:i:s', time() + $backoff * 60),
                    'last_error'      => $webhook ? 'Delivery failed (see webhook logs)' : 'Webhook deleted or inactive',
                ]);
            }
        }

        return ['processed' => count($jobs), 'delivered' => $delivered, 'failed' => $failed];
    }

    /**
     * Minutes to wait before the given (1-based) retry attempt.
     *
     * @param int $attempt
     * @return int
     */
    public static function backoffMinutes($attempt)
    {
        if (isset(self::RETRY_BACKOFF[$attempt])) {
            return self::RETRY_BACKOFF[$attempt];
        }
        $max = max(array_keys(self::RETRY_BACKOFF));
        return self::RETRY_BACKOFF[$max];
    }

    // ------------------------------------------------------------------
    // SSRF protection
    // ------------------------------------------------------------------

    /**
     * Validate a webhook URL against SSRF targets.
     *
     * Always blocked: non-http(s) schemes, loopback, 0.0.0.0/8, link-local /
     * cloud metadata (169.254.0.0/16 incl. 169.254.169.254), CGNAT
     * (100.64.0.0/10), IPv6 loopback/link-local/unique-local.
     * Strict mode additionally blocks private LAN ranges (10/8, 172.16/12,
     * 192.168/16).
     *
     * @param string $url
     * @param bool $strict
     * @param string|NULL $resolvedIp Receives the resolved IP on success
     * @param string|NULL $error Receives the reason on failure
     * @return bool
     */
    public static function isUrlSafe($url, $strict = false, &$resolvedIp = null, &$error = null)
    {
        $parts = parse_url($url);
        if ($parts === false || empty($parts['host'])) {
            $error = 'malformed URL';
            return false;
        }

        $scheme = isset($parts['scheme']) ? strtolower($parts['scheme']) : '';
        if (!in_array($scheme, ['http', 'https'], true)) {
            $error = 'scheme not allowed: ' . ($scheme !== '' ? $scheme : '(none)');
            return false;
        }

        $host = trim($parts['host'], '[]');

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $ip = $host;
        } else {
            $ip = gethostbyname($host);
            if ($ip === $host) {
                $error = 'host could not be resolved';
                return false;
            }
        }

        $reason = null;
        if (self::isIpBlocked($ip, $strict, $reason)) {
            $error = $reason;
            return false;
        }

        $resolvedIp = $ip;
        return true;
    }

    /**
     * Decide whether an IP is a forbidden SSRF target.
     *
     * @param string $ip
     * @param bool $strict Also block private LAN ranges
     * @param string|NULL $reason Receives the block reason
     * @return bool TRUE when blocked
     */
    public static function isIpBlocked($ip, $strict = false, &$reason = null)
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            $reason = 'not a valid IP';
            return true;
        }

        // Reserved ranges (loopback, link-local/metadata, 0.0.0.0/8, v6 equivalents)
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE) === false) {
            $reason = 'reserved/loopback/metadata address';
            return true;
        }

        // CGNAT 100.64.0.0/10 is not covered by the PHP filter flags
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $long = ip2long($ip);
            if ($long !== false && ($long & 0xFFC00000) === ip2long('100.64.0.0')) {
                $reason = 'carrier-grade NAT range';
                return true;
            }
        }

        if ($strict && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE) === false) {
            $reason = 'private LAN address (strict mode)';
            return true;
        }

        $reason = null;
        return false;
    }

    // ------------------------------------------------------------------
    // Signatures
    // ------------------------------------------------------------------

    /**
     * Build the X-Perfex-Signature header value:
     * t=<unix>,v1=<hex hmac_sha256("{t}.{body}", secret)>
     * Consumers verify by recomputing the HMAC and comparing, and reject
     * timestamps older than their tolerance window (replay protection).
     *
     * @param string $payloadString Raw JSON body
     * @param string $secret
     * @param int $timestamp
     * @return string
     */
    public static function buildSignatureHeader($payloadString, $secret, $timestamp)
    {
        $signed = hash_hmac('sha256', $timestamp . '.' . $payloadString, $secret);
        return 't=' . $timestamp . ',v1=' . $signed;
    }

    /**
     * The authoritative webhook event catalog, grouped by resource.
     * Names follow the module's underscore convention; legacy names emitted
     * by existing triggers are included so current webhooks keep working.
     *
     * @return array group => [event => description]
     */
    public static function eventCatalog()
    {
        return [
            'customers' => [
                'customer_created'        => 'Customer created',
                'customer_updated'        => 'Customer updated',
                'customer_deleted'        => 'Customer deleted',
                'client_updated'          => 'Customer updated (legacy alias)',
                'client_status_changed'   => 'Customer active status changed',
                'customer_group_changed'  => 'Customer groups changed',
            ],
            'contacts' => [
                'contact_created'        => 'Contact created',
                'contact_updated'        => 'Contact updated',
                'contact_deleted'        => 'Contact deleted',
                'contact_status_changed' => 'Contact active status changed',
            ],
            'leads' => [
                'lead_created'        => 'Lead created',
                'lead_updated'        => 'Lead updated',
                'lead_deleted'        => 'Lead deleted',
                'lead_assigned'       => 'Lead assigned to staff',
                'lead_converted'      => 'Lead converted to customer',
                'lead_status_changed' => 'Lead status changed',
                'lead_marked_lost'    => 'Lead marked as lost',
                'lead_note_added'     => 'Note added to lead',
            ],
            'invoices' => [
                'invoice_created'        => 'Invoice created',
                'invoice_updated'        => 'Invoice updated',
                'invoice_deleted'        => 'Invoice deleted',
                'invoice_sent'           => 'Invoice sent to customer',
                'invoice_paid'           => 'Invoice fully paid',
                'invoice_partially_paid' => 'Invoice partially paid',
                'invoice_overdue'        => 'Invoice became overdue',
                'invoice_status_changed' => 'Invoice status changed',
                'invoice_cancelled'      => 'Invoice cancelled',
            ],
            'estimates' => [
                'estimate_created'              => 'Estimate created',
                'estimate_updated'              => 'Estimate updated',
                'estimate_deleted'              => 'Estimate deleted',
                'estimate_sent'                 => 'Estimate sent to customer',
                'estimate_accepted'             => 'Estimate accepted',
                'estimate_declined'             => 'Estimate declined',
                'estimate_expired'              => 'Estimate expired',
                'estimate_converted_to_invoice' => 'Estimate converted to invoice',
            ],
            'proposals' => [
                'proposal_created'  => 'Proposal created',
                'proposal_updated'  => 'Proposal updated',
                'proposal_deleted'  => 'Proposal deleted',
                'proposal_sent'     => 'Proposal sent',
                'proposal_accepted' => 'Proposal accepted',
                'proposal_declined' => 'Proposal declined',
                'proposal_expired'  => 'Proposal expired',
            ],
            'credit_notes' => [
                'credit_note_created'  => 'Credit note created',
                'credit_note_updated'  => 'Credit note updated',
                'credit_note_deleted'  => 'Credit note deleted',
                'credit_note_applied'  => 'Credit note applied to invoice',
                'credit_note_refunded' => 'Credit note refunded',
            ],
            'payments' => [
                'payment_created' => 'Payment recorded',
                'payment_updated' => 'Payment updated',
                'payment_deleted' => 'Payment deleted',
                'amount_paid'     => 'Payment recorded (legacy alias)',
            ],
            'projects' => [
                'project_created'        => 'Project created',
                'project_updated'        => 'Project updated',
                'project_deleted'        => 'Project deleted',
                'project_status_changed' => 'Project status changed',
                'project_completed'      => 'Project marked finished',
                'project_member_added'   => 'Member added to project',
                'project_member_removed' => 'Member removed from project',
            ],
            'tasks' => [
                'task_created'        => 'Task created',
                'task_updated'        => 'Task updated',
                'task_deleted'        => 'Task deleted',
                'task_assigned'       => 'Task assigned to staff',
                'task_status_changed' => 'Task status changed',
                'task_completed'      => 'Task completed',
                'task_comment_added'  => 'Comment added to task',
                'task_timer_started'  => 'Task timer started',
                'task_timer_stopped'  => 'Task timer stopped',
            ],
            'tickets' => [
                'ticket_created'        => 'Ticket created',
                'ticket_updated'        => 'Ticket updated',
                'ticket_deleted'        => 'Ticket deleted',
                'ticket_reply_created'  => 'Reply added to ticket',
                'ticket_status_changed' => 'Ticket status changed',
                'ticket_assigned'       => 'Ticket assigned to staff',
                'ticket_closed'         => 'Ticket closed',
                'ticket_reopened'       => 'Ticket reopened',
            ],
            'contracts' => [
                'contract_created'       => 'Contract created',
                'contract_updated'       => 'Contract updated',
                'contract_deleted'       => 'Contract deleted',
                'contract_signed'        => 'Contract signed',
                'contract_renewed'       => 'Contract renewed',
                'contract_expired'       => 'Contract expired',
                'contract_comment_added' => 'Comment added to contract',
            ],
            'expenses' => [
                'expense_created'              => 'Expense created',
                'expense_updated'              => 'Expense updated',
                'expense_deleted'              => 'Expense deleted',
                'expense_converted_to_invoice' => 'Expense converted to invoice',
            ],
            'items' => [
                'item_created' => 'Item created',
                'item_updated' => 'Item updated',
                'item_deleted' => 'Item deleted',
            ],
            'staff' => [
                'staff_created'     => 'Staff member created',
                'staff_updated'     => 'Staff member updated',
                'staff_deleted'     => 'Staff member deleted',
                'staff_login'       => 'Staff member logged in',
                'staff_activated'   => 'Staff member activated',
                'staff_deactivated' => 'Staff member deactivated',
            ],
            'milestones' => [
                'milestone_created' => 'Milestone created',
                'milestone_updated' => 'Milestone updated',
                'milestone_deleted' => 'Milestone deleted',
            ],
            'subscriptions' => [
                'subscription_created'   => 'Subscription created',
                'subscription_updated'   => 'Subscription updated',
                'subscription_deleted'   => 'Subscription deleted',
                'subscription_cancelled' => 'Subscription cancelled',
                'subscription_renewed'   => 'Subscription renewed',
            ],
            'timesheets' => [
                'timesheet_created' => 'Timesheet created',
                'timesheet_updated' => 'Timesheet updated',
                'timesheet_deleted' => 'Timesheet deleted',
            ],
            'calendar' => [
                'calendar_event_created' => 'Calendar event created',
                'calendar_event_updated' => 'Calendar event updated',
                'calendar_event_deleted' => 'Calendar event deleted',
            ],
            'knowledge_base' => [
                'kb_article_created' => 'Knowledge base article created',
                'kb_article_updated' => 'Knowledge base article updated',
                'kb_article_deleted' => 'Knowledge base article deleted',
                'kb_group_created'   => 'Knowledge base group created',
                'kb_group_updated'   => 'Knowledge base group updated',
                'kb_group_deleted'   => 'Knowledge base group deleted',
            ],
            'notes' => [
                'note_created' => 'Note created',
                'note_updated' => 'Note updated',
                'note_deleted' => 'Note deleted',
            ],
            'system' => [
                'api_key_created' => 'API key created',
                'api_key_updated' => 'API key updated',
                'api_key_deleted' => 'API key deleted',
                'webhook_created' => 'Webhook created',
                'webhook_updated' => 'Webhook updated',
                'webhook_deleted' => 'Webhook deleted',
            ],
        ];
    }

    /**
     * Flat list of all valid event names (plus the '*' wildcard).
     *
     * @return array
     */
    public static function eventNames()
    {
        $names = ['*'];
        foreach (self::eventCatalog() as $events) {
            foreach ($events as $name => $description) {
                $names[] = $name;
            }
        }
        return $names;
    }

    /**
     * Format headers for cURL
     *
     * @param array $headers Headers array
     * @return array Formatted headers
     */
    private function formatHeaders($headers)
    {
        $formatted = [];
        foreach ($headers as $key => $value) {
            $formatted[] = $key . ': ' . $value;
        }
        return $formatted;
    }

    /**
     * Generate legacy in-body webhook signature
     *
     * @param array $payload Payload data
     * @param string $secret Secret key
     * @return string
     */
    private function generateSignature($payload, $secret)
    {
        $payloadString = json_encode($payload);
        return hash_hmac('sha256', $payloadString, $secret);
    }

    /**
     * Log webhook attempt
     *
     * @param int $webhookId Webhook ID
     * @param string $event Event name
     * @param string $url Webhook URL
     * @param array $payload Payload data
     * @param int $attempt Attempt number
     * @return int Log ID
     */
    private function logWebhookAttempt($webhookId, $event, $url, $payload, $attempt = 1)
    {
        $this->CI->db->insert(db_prefix() . 'api_webhook_logs', [
            'webhook_id'     => $webhookId,
            'event'          => $event,
            'url'            => $url,
            'payload'        => json_encode($payload),
            'status'         => 'pending',
            'attempt_number' => $attempt,
        ]);

        return $this->CI->db->insert_id();
    }

    /**
     * Update webhook log
     *
     * @param int $logId Log ID
     * @param bool $success Success status
     * @param int $responseCode HTTP response code
     * @param string $responseBody Response body
     * @param string $errorMessage Error message
     * @return void
     */
    private function updateWebhookLog($logId, $success, $responseCode, $responseBody, $errorMessage)
    {
        $this->CI->db->where('id', $logId);
        $this->CI->db->update(db_prefix() . 'api_webhook_logs', [
            'status'        => $success ? 'success' : 'failed',
            'response_code' => $responseCode,
            'response_body' => $responseBody ? substr($responseBody, 0, 1000) : null,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Update webhook statistics
     *
     * @param int $webhookId Webhook ID
     * @param bool $success Success status
     * @return void
     */
    private function updateWebhookStats($webhookId, $success)
    {
        $field = $success ? 'success_count' : 'failure_count';
        $this->CI->db->set($field, $field . ' + 1', false);
        $this->CI->db->set('last_triggered', date('Y-m-d H:i:s'));
        $this->CI->db->where('id', $webhookId);
        $this->CI->db->update(db_prefix() . 'api_webhooks');
    }
}
