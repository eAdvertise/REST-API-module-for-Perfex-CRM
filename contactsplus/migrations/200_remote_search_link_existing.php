<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * ContactsPlus v2.0.0
 * No schema changes required.
 * Keeps versioning consistent for the release that fixes:
 * - remote search in "Link Existing Contact" modal
 * - initial load no longer biased by alphabetical first batch
 */
function contactsplus_migration_200()
{
    return true;
}