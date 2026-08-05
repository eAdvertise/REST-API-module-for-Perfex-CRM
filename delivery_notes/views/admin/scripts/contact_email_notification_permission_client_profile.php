<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php if (has_contact_permission('delivery_notes')) { ?>
<div class="checkbox checkbox-info email-notifications-delivery_notes">
    <input type="checkbox" value="1" id="delivery_note_emails" name="delivery_note_emails" <?php if ($contact->delivery_note_emails == 1) {
                                                                                                    echo ' checked';
                                                                                                } ?>>
    <label for="delivery_note_emails"><?= _l('delivery_note'); ?></label>
</div>
<?php } ?>