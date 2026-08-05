<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Fix_receipts extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        if (!is_admin()) {
            show_error('Admins only', 403);
        }
    }

    /**
     * Τρέξτο μια φορά:
     * - Dry run (default): /admin/maintenance/fix_receipts
     * - Apply updates:     /admin/maintenance/fix_receipts?apply=1
     */
    public function index()
    {
        $apply = (int) $this->input->get('apply') === 1;

        $rows = $this->db->where('(client_id IS NULL OR client_id = 0)', null, false)
                         ->where("(invoices_applied IS NOT NULL AND TRIM(invoices_applied) <> '')", null, false)
                         ->get(db_prefix().'receipts')
                         ->result_array();

        $updated = 0;
        $skipped = 0;
        $conflicts = 0;

        if ($apply) {
            $this->db->trans_start();
        }

        foreach ($rows as $r) {
            $receipt_id = (int)$r['id'];
            $raw = trim((string)$r['invoices_applied']);
            $invoiceIds = $this->extract_invoice_ids($raw);

            if (empty($invoiceIds)) {
                $skipped++;
                log_activity("FixReceipts: #$receipt_id skipped (no invoice ids parsed from invoices_applied='{$raw}')");
                continue;
            }

            // Βρες όλους τους διαφορετικούς clientid αυτών των invoices
            $clients = $this->db->select('DISTINCT(clientid) AS cid')
                                ->where_in('id', $invoiceIds)
                                ->get(db_prefix().'invoices')
                                ->result_array();
            $clientIds = array_values(array_unique(array_map(function($x){return (int)$x['cid'];}, $clients)));
            // Καθάρισε μηδενικά/NULL
            $clientIds = array_values(array_filter($clientIds, fn($v)=>$v>0));

            if (count($clientIds) === 1) {
                $client_id = $clientIds[0];
                if ($apply) {
                    $this->db->where('id', $receipt_id)
                             ->update(db_prefix().'receipts', ['client_id' => $client_id]);
                }
                $updated++;
                log_activity("FixReceipts: #$receipt_id -> client_id={$client_id} (from invoices ".implode(',',$invoiceIds).")");
            } else {
                $conflicts++;
                log_activity("FixReceipts: #$receipt_id conflict (clients=".implode(',',$clientIds).", invoices=".implode(',',$invoiceIds).")");
            }
        }

        if ($apply) {
            $this->db->trans_complete();
            if ($this->db->trans_status() === false) {
                show_error('Transaction failed. No changes committed.', 500);
                return;
            }
        }

        echo "<pre>";
        echo "Receipts checked: ".count($rows).PHP_EOL;
        echo "Updated:  {$updated}".($apply ? '' : " (dry-run)").PHP_EOL;
        echo "Skipped:  {$skipped}".PHP_EOL;
        echo "Conflicts:{$conflicts}".PHP_EOL;
        echo PHP_EOL;
        echo "Tip: first run without ?apply=1 (dry-run). If looks good, run again with ?apply=1.".PHP_EOL;
        echo "</pre>";
    }

    /**
     * Προσπαθεί να βγάλει invoice ids από το invoices_applied
     * Υποστηρίζει:
     * - "123"
     * - "123,124,125"
     * - JSON array αριθμών: [123,124]
     * - JSON array αντικειμένων: [{"invoice_id":123,"amount":50.00}, ...]
     * - JSON object με κλειδί invoices: {"invoices":[123,124]}
     */
    private function extract_invoice_ids($raw)
    {
        if ($raw === '' || $raw === null) return [];

        // Καθαρό νούμερο
        if (ctype_digit($raw)) {
            return [ (int)$raw ];
        }

        // CSV
        if (strpos($raw, ',') !== false && preg_match('/^[0-9,\s]+$/', $raw)) {
            $parts = array_map('trim', explode(',', $raw));
            $ids = [];
            foreach ($parts as $p) {
                if (ctype_digit($p)) $ids[] = (int)$p;
            }
            return array_values(array_unique($ids));
        }

        // JSON;
        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $ids = [];

            // π.χ. [123,124]
            if (is_array($decoded) && array_keys($decoded) === range(0, count($decoded)-1)) {
                foreach ($decoded as $item) {
                    if (is_numeric($item)) $ids[] = (int)$item;
                    elseif (is_array($item) && isset($item['invoice_id']) && is_numeric($item['invoice_id'])) {
                        $ids[] = (int)$item['invoice_id'];
                    } elseif (is_array($item) && isset($item['id']) && is_numeric($item['id'])) {
                        $ids[] = (int)$item['id'];
                    }
                }
            }
            // π.χ. {"invoices":[...]} ή {"items":[{"invoice_id":...}]}
            if (is_array($decoded) && !empty($decoded)) {
                if (isset($decoded['invoices']) && is_array($decoded['invoices'])) {
                    foreach ($decoded['invoices'] as $v) {
                        if (is_numeric($v)) $ids[] = (int)$v;
                    }
                }
                if (isset($decoded['items']) && is_array($decoded['items'])) {
                    foreach ($decoded['items'] as $it) {
                        if (isset($it['invoice_id']) && is_numeric($it['invoice_id'])) {
                            $ids[] = (int)$it['invoice_id'];
                        }
                    }
                }
            }

            $ids = array_values(array_unique(array_filter($ids, fn($v)=>$v>0)));
            if (!empty($ids)) return $ids;
        }

        return [];
    }
}
