<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<script id="multiple_delivery_note_payment">
document.addEventListener('DOMContentLoaded', function() {
    var invoicesTableSelector = 'table#delivery_notes';
    var isInvoiceView = window.location.href.includes('admin/delivery_notes') && $(invoicesTableSelector)
        .length > 0;
    if (isInvoiceView) {
        const multipleInvoicePaymentSelect = () => {
            if (!$('select#bulk_action').length) {
                var form =
                    `<select id="bulk_action" name="bulk_action" class="required form-control input-sm pull-left" placeholder="<?= _l('dropdown_non_selected_tex'); ?>">
                        <option value="" disabled selected><?= _l('dropdown_non_selected_tex'); ?></option>
                        <option value="to-invoice"><?= _l('add_batch_delivery_to_invoice'); ?></option>
                    </select>`;
                $(form).insertBefore("#delivery_notes_length");
            }
        }

        const saveMultipleInvoicePayment = () => {
            const ids = [];
            const checkedBoxes = document.querySelectorAll(
                `${invoicesTableSelector} .mutliple-delivery-note-toggle:checked`);
            for (let i = 0; i < checkedBoxes.length; i++) {
                ids.push(checkedBoxes[i].value);
            }
            add_batch_delivery_notes_invoice({
                ids
            });

            return true;
        }
        window.saveMultipleInvoicePayment = saveMultipleInvoicePayment;


        // Prevent sorting when the check box is clicked on the heading 
        $(`${invoicesTableSelector} #mutliple-delivery-note-toggle`).on('click', function(e) {
            e.stopPropagation();
        });

        // Check box toggle select/deselect all
        $(`${invoicesTableSelector} tr`).on('change', '#mutliple-delivery-note-toggle', function(e) {
            multipleInvoicePaymentSelect();
            var checkboxes = $(`${invoicesTableSelector} .mutliple-delivery-note-toggle`);
            checkboxes.prop('checked', $(this).prop('checked'));
        });

        // Ensure the bulk action select input in place
        $(`${invoicesTableSelector} td .mutliple-delivery-note-toggle`).on('change', function(e) {
            multipleInvoicePaymentSelect();
        });

        // Show the modal
        $(document).on('change', 'select#bulk_action', function(e) {
            // fetch the batch modal with the selected ids
            if ($(this).val() == 'to-invoice') {
                saveMultipleInvoicePayment();
                setTimeout(() => {
                    $('select#bulk_action').val('').trigger('change');
                }, 100);
            }
        });


        setTimeout(() => {
            multipleInvoicePaymentSelect();
        }, 1000);
    }
});
</script>