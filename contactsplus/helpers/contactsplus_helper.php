<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Contacts Plus — permission & email notification defaults
 *
 * Διαβάζει defaults από τα options του Perfex (αν υπάρχουν) και
 * δίνει ασφαλή fallbacks αν δεν έχουν οριστεί.
 *
 * Μπορείς να ορίσεις/αλλάξεις τα defaults με τις αντίστοιχες options:
 *   default_contact_perm_<key>            (0/1)
 *   default_contact_email_notif_<key>     (0/1)
 *
 * όπου <key>:
 *   Permissions: invoices, estimates, contracts, proposals, support, projects, waybills
 *   Email:       invoice, estimate, credit_note, project, tickets, task, contract, waybills
 */

if (!function_exists('contactsplus_perm_defaults')) {
    function contactsplus_perm_defaults(): array
    {
        $CI = &get_instance();
        $keys = ['invoices','estimates','contracts','proposals','support','projects','waybills'];

        $defaults = [];
        foreach ($keys as $k) {
            // Προσπάθησε να διαβάσεις από options (0/1). Αν δεν υπάρχει, fallback = 0 (OFF).
            $opt = get_option('default_contact_perm_'.$k);
            $defaults[$k] = $opt === '' || $opt === null ? 0 : (int)$opt;
        }

        return $defaults;
    }
}

if (!function_exists('contactsplus_email_defaults')) {
    function contactsplus_email_defaults(): array
    {
        $CI = &get_instance();
        $keys = ['invoice','estimate','credit_note','project','tickets','task','contract','waybills'];

        $defaults = [];
        foreach ($keys as $k) {
            $opt = get_option('default_contact_email_notif_'.$k);
            $defaults[$k] = $opt === '' || $opt === null ? 0 : (int)$opt;
        }

        return $defaults;
    }
}
