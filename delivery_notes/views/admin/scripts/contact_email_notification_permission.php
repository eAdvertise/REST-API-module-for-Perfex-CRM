<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<template data-id="delivery_note_emails_template">
    <div class="col-md-6 row">
        <div class="row">
            <div class="col-md-6 mtop10 border-right">
                <span><?= _l(DELIVERY_NOTE_MODULE_NAME); ?></span>
            </div>
            <div class="col-md-6 mtop10">
                <div class="onoffswitch">
                    <input type="checkbox" id="delivery_note_emails" data-perm-id="<?= DELIVERY_NOTE_MODULE_CONTACT_PERMISSION_ID; ?>" class="onoffswitch-checkbox" <?= (isset($contact) && $contact->delivery_note_emails == '1') ? 'checked' : ''; ?> value="1" name="delivery_note_emails">
                    <label class="onoffswitch-label" for="delivery_note_emails"></label>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    "use strict";
    (function() {
        const template = document.querySelector('[data-id=delivery_note_emails_template]');
        const container = document.getElementById('contact_email_notifications');
        if (template && container) {
            const clone = template.content.cloneNode(true);
            container.appendChild(clone); // Appends at the end
        }
    })();
</script>
