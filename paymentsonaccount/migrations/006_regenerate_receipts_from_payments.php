<?php defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Regenerate_Receipts_From_Payments
{
    public function up()
    {
        $CI = &get_instance();

        // Τρέχει μόνο μία φορά
        if (get_option('poa_receipts_regenerated') == '1') {
            return;
        }

        // Προϋποθέσεις
        if (!$CI->db->table_exists(db_prefix().'receipts')) {
            log_activity('[POA 006] receipts table missing. Aborting.');
            return;
        }

        // 1) Βάλε στήλη source_payment_id αν δεν υπάρχει
        if (!$CI->db->field_exists('source_payment_id', db_prefix().'receipts')) {
            $CI->db->query("ALTER TABLE `".db_prefix()."receipts` ADD `source_payment_id` INT(11) NULL DEFAULT NULL AFTER `id`");
            $CI->db->query("CREATE INDEX `idx_source_payment_id` ON `".db_prefix()."receipts` (`source_payment_id`)");
        }

        // 2) Ασφάλεια αρίθμησης: Βρες τον ΜΕΓΙΣΤΟ αριθμητικό τρέχοντα από τα receipt_number και ανέβασε το next_receipt_number αν χρειάζεται
        $prefix  = (string) get_option('receipt_number_prefix');
        $padding = (int) (get_option('number_padding') ?: 4);

        $maxNum = 0;
        $res = $CI->db->select('receipt_number')->get(db_prefix().'receipts')->result_array();
        foreach ($res as $row) {
            $rn = (string) ($row['receipt_number'] ?? '');
            if ($prefix !== '' && strpos($rn, $prefix) === 0) {
                $numPart = substr($rn, strlen($prefix));
            } else {
                $numPart = $rn;
            }
            if (ctype_digit($numPart)) {
                $n = (int)$numPart;
                if ($n > $maxNum) $maxNum = $n;
            }
        }
        $currentNext = (int) get_option('next_receipt_number');
        if ($maxNum >= $currentNext) {
            update_option('next_receipt_number', $maxNum + 1);
            log_activity('[POA 006] next_receipt_number bumped to '.($maxNum+1));
        }

        // 3) Φέρε ΟΛΑ τα payments με τον αντίστοιχο πελάτη
        //    (αν έχεις πολύ μεγάλα datasets, κάνε το σε batches)
        $payments = $CI->db->select('ip.id as payment_id, ip.invoiceid, ip.amount, ip.paymentmode, ip.date, ip.transactionid, inv.clientid')
            ->from(db_prefix().'invoicepaymentrecords as ip')
            ->join(db_prefix().'invoices as inv','inv.id = ip.invoiceid','left')
            ->order_by('ip.date','ASC')
            ->get()->result();

        if (!$payments) {
            update_option('poa_receipts_regenerated','1');
            log_activity('[POA 006] No payments found. Nothing to regenerate.');
            return;
        }

        $CI->load->model('paymentsonaccount/payments_on_account_model');

        $ok = 0; $skipped = 0; $failed = 0;

        foreach ($payments as $p) {
            if (empty($p->clientid) || empty($p->invoiceid)) { $skipped++; continue; }

            // Αν ΥΠΑΡΧΕΙ ήδη receipt γι’ αυτό το payment, μην το ξαναδημιουργήσεις
            $exists = $CI->db->where('source_payment_id', (int)$p->payment_id)
                             ->count_all_results(db_prefix().'receipts');
            if ($exists > 0) { $skipped++; continue; }

            $date = $p->date ? date('Y-m-d', strtotime($p->date)) : date('Y-m-d');

            try {
                // create_receipt πρέπει να χρησιμοποιεί atomic αριθμοδότη (generate_receipt_number)
                $receipt_id = $CI->payments_on_account_model->create_receipt(
                    (int)$p->clientid,                 // client_id
                    (float)$p->amount,                 // total_amount
                    $p->paymentmode,                   // payment_mode
                    [(int)$p->invoiceid],              // invoices_applied
                    'Migrated from Payment ID: '.(int)$p->payment_id, // note
                    $date,                             // payment_date
                    null,                              // payment_method
                    $p->transactionid,                 // transaction_id
                    false,                             // on_account
                    (int)$p->payment_id                // source_payment_id (idempotency)
                );

                // Αν το model δεν γράφει το source_payment_id, κάνε fallback update:
                if ($receipt_id) {
                    $CI->db->where('id', $receipt_id)
                           ->update(db_prefix().'receipts', ['source_payment_id' => (int)$p->payment_id]);
                }

                $ok++;
            } catch (Throwable $e) {
                $failed++;
                log_activity('[POA 006] Failed regenerate payment '.$p->payment_id.' -> '.$e->getMessage());
            }
        }

        update_option('poa_receipts_regenerated','1');
        log_activity('[POA 006] Regenerated receipts. OK: '.$ok.' | Skipped: '.$skipped.' | Failed: '.$failed);
    }
}
