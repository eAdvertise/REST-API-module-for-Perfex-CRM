<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_payment_fields_to_receipts
{
    /** Τρέχει στο activate / upgrade του module */
    public function up()
    {
        $CI  = &get_instance();
        $db  = $CI->db;
        $tbl = db_prefix() . 'receipts';

        // Αν δεν υπάρχει καν ο πίνακας, σταμάτα (ή φτιάξ’ τον σε άλλο migration).
        if (!$db->table_exists($tbl)) {
            return;
        }

        // Use SHOW COLUMNS instead of field_exists() because CI can serve stale
        // field metadata during module activation/re-activation.
        $fieldExists = function (string $field) use ($db, $tbl): bool {
            return $db->query("SHOW COLUMNS FROM `{$tbl}` LIKE " . $db->escape($field))->num_rows() > 0;
        };

        // helper: πρόσθεσε στήλη μόνο αν δεν υπάρχει
        $addIfMissing = function (string $field, string $sqlAdd) use ($db, $fieldExists) {
            if ($fieldExists($field)) {
                return;
            }

            try {
                $db->query($sqlAdd);
            } catch (Throwable $e) {
                // Αν φάμε duplicate/exists, απλά αγνόησέ το για idempotency
                if ((int)$e->getCode() !== 1060
                    && stripos($e->getMessage(), 'Duplicate column') === false
                    && stripos($e->getMessage(), 'already exists') === false) {
                    throw $e;
                }
            }
        };

        // === Στήλες πληρωμής (ONLY IF MISSING) ===
        $addIfMissing('payment_date', "ALTER TABLE `{$tbl}` ADD `payment_date` DATE NULL AFTER `total_amount`");
        $addIfMissing('payment_mode', "ALTER TABLE `{$tbl}` ADD `payment_mode` VARCHAR(100) NULL AFTER `payment_date`");
        $addIfMissing('payment_method', "ALTER TABLE `{$tbl}` ADD `payment_method` VARCHAR(100) NULL AFTER `payment_mode`");
        $addIfMissing('transaction_id', "ALTER TABLE `{$tbl}` ADD `transaction_id` VARCHAR(191) NULL AFTER `payment_method`");
        $addIfMissing('date_created', "ALTER TABLE `{$tbl}` ADD `date_created` DATETIME NULL AFTER `transaction_id`");
        $addIfMissing('staff_id', "ALTER TABLE `{$tbl}` ADD `staff_id` INT(11) NULL DEFAULT NULL AFTER `date_created`");
        $addIfMissing('source_payment_id', "ALTER TABLE `{$tbl}` ADD `source_payment_id` INT(11) NULL DEFAULT NULL AFTER `staff_id`");

        // === Indexes (ONLY IF MISSING) ===
        $addIndexIfMissing = function (string $indexName, string $sqlAdd) use ($db, $tbl) {
            $exists = $db->query("SHOW INDEX FROM `{$tbl}` WHERE Key_name = " . $db->escape($indexName))->num_rows() > 0;
            if (!$exists) {
                try { $db->query($sqlAdd); } catch (Throwable $e) { /* ignore */ }
            }
        };

        $addIndexIfMissing('idx_payment_date', "ALTER TABLE `{$tbl}` ADD INDEX `idx_payment_date` (`payment_date`)");
        $addIndexIfMissing('idx_client_date',  "ALTER TABLE `{$tbl}` ADD INDEX `idx_client_date` (`client_id`,`payment_date`)");
        $addIndexIfMissing('idx_source_pid',   "ALTER TABLE `{$tbl}` ADD INDEX `idx_source_pid` (`source_payment_id`)");
    }
}
