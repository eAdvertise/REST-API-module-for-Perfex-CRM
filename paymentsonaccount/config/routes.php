<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Χαρτογραφούμε το “κοντό” URL που θες → στον controller που μόλις φτιάξαμε.
 *
 * Τελικό URL:
 *   /admin/paymentsonaccount/migrate_old_receipts_to_new?dry=1
 *   /admin/paymentsonaccount/migrate_old_receipts_to_new
 */
$route['admin/paymentsonaccount/migrate_old_receipts_to_new']
    = 'paymentsonaccount/migrate_poa_receipts/migrate_old_receipts_to_new';

// Προαιρετικό ping για έλεγχο routing:
$route['admin/paymentsonaccount/migrate'] = 'paymentsonaccount/migrate_poa_receipts/index';
