<?php defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Migrate_Payments_To_Receipts
{
    public function up()
    {
        $CI = &get_instance();

        // Βεβαιώσου ότι υπάρχει η στήλη source_payment_id
        if (!$CI->db->field_exists('source_payment_id', db_prefix().'receipts')) {
            $CI->db->query("ALTER TABLE `".db_prefix()."receipts` ADD `source_payment_id` INT(11) NULL DEFAULT NULL AFTER `id`");
        }

        // Φέρε όλα τα payments που δεν έχουν γίνει receipts ακόμη
        $payments = $CI->db->get(db_prefix().'payments')->result();
        if (empty($payments)) { return; }

        $migratedCount = 0;
        foreach ($payments as $p) {
            // Έλεγξε αν υπάρχει ήδη απόδειξη με το ίδιο source_payment_id
            $exists = $CI->db->where('source_payment_id', $p->id)
                             ->count_all_results(db_prefix().'receipts');
            if ($exists > 0) {
                continue; // Ήδη μετατράπηκε
            }

            // Δημιούργησε την εγγραφή στο receipts
            $CI->db->insert(db_prefix().'receipts', [
                'source_payment_id' => $p->id,
                'client_id'         => $p->clientid,
                'total_amount'      => $p->amount,
                'payment_date'      => $p->date,
                'payment_mode'      => $p->paymentmode,
                'note'              => 'Migrated from Payment ID: '.$p->id,
                'datecreated'       => date('Y-m-d H:i:s'),
            ]);

            $migratedCount++;
        }

        log_activity('[POA] Migrated '.$migratedCount.' payments into receipts.');
    }
}
