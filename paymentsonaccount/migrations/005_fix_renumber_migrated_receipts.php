<?php defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Fix_Renumber_Migrated_Receipts
{
    public function up()
    {
        $CI = &get_instance();

        // Τρέξε μία φορά μόνο
        if (get_option('poa_receipts_renumbered') == '1') {
            return;
        }

        if (!$CI->db->table_exists(db_prefix().'receipts')) { return; }
        if (!$CI->db->field_exists('source_payment_id', db_prefix().'receipts')) { return; }

        // (Προαιρετικό) Πρόσθεσε unique index στον receipt_number αφού πρώτα διορθώσουμε τα διπλά
        $addUniqueIndex = function() use ($CI) {
            // Έλεγξε αν υπάρχει ήδη index με αυτό το όνομα
            $exists = $CI->db->query("SHOW INDEX FROM `".db_prefix()."receipts` WHERE Key_name = 'uniq_receipt_number'")->num_rows() > 0;
            if (!$exists) {
                // Μην σκάσει αν υπάρχουν ακόμη διπλότυπα
                try {
                    $CI->db->query("ALTER TABLE `".db_prefix()."receipts` ADD UNIQUE KEY `uniq_receipt_number` (`receipt_number`)");
                } catch (Throwable $e) {
                    log_activity('[POA 005] Could not add UNIQUE index for receipt_number: '.$e->getMessage());
                }
            }
        };

        // Helper: δώσε atomic επόμενο αριθμό βάσει options
        $nextNumber = function() use ($CI) {
            $CI->db->trans_start();

            // Κλείδωσε το row για update (atomic)
            $row = $CI->db->query("SELECT * FROM `".db_prefix()."options` WHERE `name`='next_receipt_number' FOR UPDATE")->row();
            $next = isset($row->value) ? (int)$row->value : 1;

            $prefix  = (string) get_option('receipt_number_prefix');
            $padding = (int) (get_option('number_padding') ?: 4);
            $num     = $prefix . str_pad($next, max(1,$padding), '0', STR_PAD_LEFT);

            // Αύξησε το μετρητή
            $CI->db->set('value', 'value+1', false)
                   ->where('name', 'next_receipt_number')
                   ->update(db_prefix().'options');

            $CI->db->trans_complete();
            return $num;
        };

        // Πιάσε ΜΟΝΟ τις migrated (έχουν source_payment_id), με σταθερή σειρά
        $receipts = $CI->db->order_by('payment_date','ASC')
                           ->order_by('id','ASC')
                           ->get_where(db_prefix().'receipts', 'source_payment_id IS NOT NULL')->result();

        if (empty($receipts)) {
            update_option('poa_receipts_renumbered','1');
            $addUniqueIndex();
            return;
        }

        $updated = 0;
        foreach ($receipts as $r) {
            $newNo = $nextNumber();

            $CI->db->where('id', $r->id)
                   ->update(db_prefix().'receipts', ['receipt_number' => $newNo]);

            // Προσθέτουμε/κρατάμε και το note (αν δεν υπάρχει ήδη)
            if (empty($r->note) || stripos($r->note, 'Migrated from Payment ID:') === false) {
                $CI->db->where('id', $r->id)
                       ->update(db_prefix().'receipts', ['note' => trim(($r->note ?? '').'  Migrated from Payment ID: '.$r->source_payment_id)]);
            }

            $updated++;
        }

        $addUniqueIndex();

        update_option('poa_receipts_renumbered','1');
        log_activity('[POA 005] Renumbered '.$updated.' migrated receipts.');
    }
}
