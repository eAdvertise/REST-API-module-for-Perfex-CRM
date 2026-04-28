<?php
defined('BASEPATH') or exit('No direct script access allowed');

function guestinvoices_run_install()
{
    $CI = &get_instance();
    // Ασφαλής δημιουργία options (με prefix)
    if (0 == total_rows(db_prefix().'options', ['name' => 'guest_inv_default_status'])) {
        add_option('guest_inv_default_status', 'draft', 1); // draft|unpaid
    }
    if (0 == total_rows(db_prefix().'options', ['name' => 'guest_inv_auto_send'])) {
        add_option('guest_inv_auto_send', '0', 1); // 0/1
    }
}
