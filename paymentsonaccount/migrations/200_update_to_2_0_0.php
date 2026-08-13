<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * IMPORTANT:
 * Για να δουλέψει το module migration με semantic version 2.0.0,
 * η κλάση ΠΡΕΠΕΙ να ονομάζεται Migration_Version_200
 * (δηλ. 2.0.0 -> 200).
 */
class Migration_Version_200 extends App_module_migration
{
    public function up()
    {
        // Βάλε ό,τι αλλαγές schema/data θες για την 2.0.0
        // Εδώ απλώς “σημαδεύουμε” την έκδοση σε option, προαιρετικό:
        $current = get_option('paymentsonaccount_module_version');
        if (!$current || version_compare($current, '2.0.0', '<')) {
            update_option('paymentsonaccount_module_version', '2.0.0');
        }

        // Π.χ. αν χρειάζεσαι adjustments:
        // $CI = &get_instance();
        // $CI->db->query('ALTER TABLE ...');
    }
}
