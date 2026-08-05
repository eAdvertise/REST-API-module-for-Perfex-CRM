<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Paymentsonaccount extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('paymentsonaccount/payments_on_account_model');
    }

    /**
     * GET /admin/paymentsonaccount/migrate_old_receipts_to_new?dry=1
     * - STEP 1: tbl_receipts -> tblreceipts (override receipt_number = ref)
     * - STEP 2: Orphans from tblinvoicepaymentrecords -> create receipts (idempotent via source_payment_id)
     */
    public function migrate_old_receipts_to_new()
    {
        if (!has_permission('payments_on_account', '', 'view')) {
            access_denied('Receipts Migration');
        }

        $dryParam = $this->input->get('dry');
        $dry = ($dryParam === '1' || (int)$dryParam === 1);

        // Detect table names safely
        $T_OLD_RECEIPTS = $this->detect_table(['tbl_receipts', db_prefix().'_receipts']);
        $T_OLD_LINKS    = $this->detect_table(['tbl_receiptsInvPayments', db_prefix().'_receiptsInvPayments']);
        $T_PAYMENTS     = $this->detect_table([db_prefix().'invoicepaymentrecords']); // core
        $T_NEW_RECEIPTS = $this->detect_table([db_prefix().'receipts']);              // new module (tblreceipts)

        $this->output->set_content_type('application/json');

        if (!$T_OLD_RECEIPTS || !$T_NEW_RECEIPTS || !$T_PAYMENTS) {
            echo json_encode([
                'dry_run' => $dry,
                'error'   => 'Missing required tables',
                'found'   => [
                    'old_receipts' => $T_OLD_RECEIPTS,
                    'old_links'    => $T_OLD_LINKS,
                    'payments'     => $T_PAYMENTS,
                    'new_receipts' => $T_NEW_RECEIPTS,
                ],
            ]);
            return;
        }

        $migrated_old = 0;
        $skipped_ref_conflict = 0;
        $created_orphans = 0;
        $errors = [];

        // ---------------------------
        // STEP 1: OLD -> NEW
        // ---------------------------
        $old_rows = $this->db->order_by('id','ASC')->get($T_OLD_RECEIPTS)->result_array();
        foreach ($old_rows as $r) {
            $old_id     = (int)$r['id'];
            $client_id  = (int)$r['customer_id'];
            $ref        = trim((string)$r['ref']);
            $amount     = (float)$r['amount'];
            $mode       = (string)$r['paymentmode'];
            $method     = (string)$r['paymentmethod'];
            $date       = $r['date'] ?: date('Y-m-d');
            $recorded   = $r['daterecorded'] ?: date('Y-m-d H:i:s');
            $note       = (string)($r['note'] ?? '');
            $txn        = (string)($r['transactionid'] ?? '');

            // collect invoice IDs from old links (if table exists)
            $invIds = [];
            if ($T_OLD_LINKS) {
                $links = $this->db->where('receiptid', $old_id)->get($T_OLD_LINKS)->result_array();
                foreach ($links as $ln) {
                    $invPaymentId = (int)$ln['invpaymentid'];
                    if ($invPaymentId <= 0) { continue; }
                    $pr = $this->db->select('invoiceid')->where('id',$invPaymentId)->get($T_PAYMENTS)->row();
                    if ($pr && (int)$pr->invoiceid > 0) { $invIds[] = (int)$pr->invoiceid; }
                }
                $invIds = array_values(array_unique($invIds));
            }

            // conflict?
            $exists = $this->db->select('id')->where('receipt_number', $ref)->get($T_NEW_RECEIPTS)->row();
            if ($exists) { $skipped_ref_conflict++; continue; }

            if ($dry) {
                // just count what WOULD happen
                $migrated_old++;
                continue;
            }

            $payload = [
                'receipt_number'   => $ref,
                'client_id'        => $client_id,
                'total_amount'     => $amount,
                'payment_date'     => $date,
                'payment_mode'     => $mode,
                'payment_method'   => $method ?: null,
                'transaction_id'   => $txn ?: null,
                'date_created'     => $recorded,
                'invoices_applied' => json_encode($invIds),
                'staff_id'         => is_staff_logged_in() ? (int)get_staff_user_id() : null,
                'note'             => $note ?: null,
            ];

            $ok = $this->db->insert($T_NEW_RECEIPTS, $payload);
            if ($ok && $this->db->affected_rows() > 0) {
                $migrated_old++;
            } else {
                $errors[] = "Failed to insert OLD#{$old_id} (ref={$ref})";
            }
        }

        // ---------------------------
        // STEP 2: ORPHANS
        // ---------------------------
        // orphans = payments NOT present in old links
        $orphans_sql = "
            SELECT p.*
            FROM {$T_PAYMENTS} p
            " . ($T_OLD_LINKS ? "LEFT JOIN {$T_OLD_LINKS} l ON l.invpaymentid = p.id" : "LEFT JOIN (SELECT NULL) l ON 1=0") . "
            WHERE l.id IS NULL
        ";
        $orphans = $this->db->query($orphans_sql)->result_array();

        foreach ($orphans as $p) {
            $pid        = (int)$p['id'];
            $invoice_id = (int)$p['invoiceid'];
            // client_id may not exist on some Perfex schemas; fall back to invoice client
            $client_id  = (int)($p['client_id'] ?? 0);
            if ($client_id <= 0 && $invoice_id > 0) {
                $invRow = $this->db->select('clientid')->where('id',$invoice_id)->get(db_prefix().'invoices')->row();
                if ($invRow) { $client_id = (int)$invRow->clientid; }
            }

            $amount     = (float)$p['amount'];
            $mode       = (string)$p['paymentmode'];
            $method     = (string)$p['paymentmethod'];
            $date       = $p['date'] ?: date('Y-m-d');
            $note       = (string)($p['note'] ?? '');
            $txn        = (string)($p['transactionid'] ?? '');

            // already created?
            $already = $this->db->select('id')->where('source_payment_id',$pid)->get($T_NEW_RECEIPTS)->row();
            if ($already) { continue; }

            $applied = [];
            $on_account = true;
            if ($invoice_id > 0) {
                $applied = [$invoice_id];
                $on_account = false;
            }

            if ($dry) {
                $created_orphans++;
                continue;
            }

            try {
                $this->payments_on_account_model->create_receipt(
                    $client_id,
                    $amount,
                    $mode,
                    $applied,
                    $note,
                    $date,
                    $method,
                    $txn,
                    $on_account,
                    $pid // source_payment_id for idempotency
                );
                $created_orphans++;
            } catch (\Throwable $e) {
                $errors[] = "ORPHAN payment#{$pid} failed: ".$e->getMessage();
            }
        }

        echo json_encode([
            'dry_run'               => $dry,
            'migrated_old'          => $migrated_old,
            'skipped_ref_conflicts' => $skipped_ref_conflict,
            'created_orphans'       => $created_orphans,
            'errors'                => $errors,
        ]);
    }

    /**
     * Helper: try first existing table name from list.
     * @param array $candidates
     * @return string|null
     */
    private function detect_table(array $candidates): ?string
    {
        foreach ($candidates as $t) {
            if ($this->db->table_exists($t)) return $t;
        }
        return null;
    }
}
