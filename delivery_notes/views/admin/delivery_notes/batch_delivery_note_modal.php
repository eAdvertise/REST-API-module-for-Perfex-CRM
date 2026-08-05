<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" id="batch-invoice-modal">
    <div class="modal-dialog modal-xxl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php
                                        echo _l('add_batch_delivery_to_invoice') ?></h4>
            </div>
            <?php
            echo form_open('admin/delivery_notes/add_batch_delivery_to_invoice', ['id' => 'batch-delivery-form']); ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group select-placeholder">
                            <select id="batch-delivery-filter" class="selectpicker multiple_batch_delivery_select"
                                name="client_filter" data-width="100%"
                                data-none-selected-text="<?php echo _l('batch_payment_filter_by_customer') ?>">
                                <option value=""></option>
                                <?php foreach ($customers as $customer) { ?>
                                <option value="<?php echo e($customer->userid); ?>"><?php echo e($customer->company); ?>
                                </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-4 batch-delivery-mode-filter" style="display: none;">
                        <select id="batch-delivery-mode-filter" class="selectpicker" name="mode_filter"
                            data-width="100%" data-none-selected-text="-">
                            <option></option>
                            <option value="unpaid"><?= _l('convert'); ?></option>
                            <option value="draft"><?= _l('convert_and_save_as_draft'); ?>
                            <option value="unpaid-single">
                                <?= _l('convert_as_single_invoice'); ?></option>
                            <option value="draft-single">
                                <?= _l('convert_as_single_invoice_draft'); ?>
                            </option>
                            </option>
                        </select>
                    </div>
                    <div class="col-sm-12">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th><strong><?php echo _l('delivery_note'); ?>
                                                #</strong></th>
                                        <th><strong><?php echo _l('delivery_note_batch_action'); ?></strong>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($delivery_notes as $index => $note) {
                                        $note = (object)$note; ?>
                                    <tr class="batch_delivery_item" data-clientid="<?php echo e($note->clientid); ?>"
                                        data-invoiceId="<?php echo $note->id ?>">
                                        <td>
                                            <a href="<?php echo admin_url('delivery_notes/list_delivery_notes/' . $note->id); ?>"
                                                target="_blank">
                                                <?php echo format_delivery_note_number($note->id) ?>
                                            </a><br>
                                            <a class="text-dark"
                                                href="<?php echo admin_url('clients/client/' . $note->clientid); ?>"
                                                target="_blank">
                                                <?php echo $note->company ?>
                                            </a>

                                            <input type="hidden"
                                                name="delivery_note[<?php echo $index ?>][delivery_noteid]"
                                                value="<?php echo $note->id ?>">
                                        </td>
                                        <td class="tw-w-56">
                                            <div class="form-group">
                                                <select class="selectpicker"
                                                    name="delivery_note[<?php echo $index ?>][mode]" data-width="100%"
                                                    data-none-selected-text="-" required="required">
                                                    <option value=""></option>
                                                    <option value="unpaid"><?= _l('convert'); ?></option>
                                                    <option value="draft"><?= _l('convert_and_save_as_draft'); ?>
                                                    <option value="unpaid-single">
                                                        <?= _l('convert_as_single_invoice'); ?></option>
                                                    <option value="draft-single">
                                                        <?= _l('convert_as_single_invoice_draft'); ?>
                                                    </option>
                                                </select>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default close_btn"
                        data-dismiss="modal"><?php
                                                                                                    echo _l('close'); ?></button>
                    <button type="submit" class="btn btn-primary"><?php
                                                                    echo _l('apply'); ?></button>
                </div>
                <?php
                echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

<script>
function fillDeliveryNoteBatchInfo(selectedCustomer) {
    var selectedCustomer = $("#batch-delivery-filter").val();
    var selectedMode = $("#batch-delivery-mode-filter").val();
    console.log(selectedCustomer, 'dd', selectedMode)
    // Reset all input
    $(".batch_delivery_item select[name$='[mode]']").val('').trigger('change');

    if (selectedCustomer == '') {
        return;
    }

    var rows = $(`.batch_delivery_item:visible:not(.hide)[data-clientid=${selectedCustomer}]`);
    for (let i = 0; i < rows.length; i++) {
        var row = rows[i];
        $(row).find("select[name$='[mode]']").val(selectedMode)
            .trigger('change');
    }
}
$(document).on("change", "#batch-delivery-filter", function() {
    var selectedCustomer = $(this).val();
    $(".batch-delivery-mode-filter .selectpicker").val('').trigger(
        'change');
    if (selectedCustomer == '') {
        $(".batch-delivery-mode-filter").hide();
    } else {
        $(".batch-delivery-mode-filter").show();
    }

    setTimeout(() => {
        fillDeliveryNoteBatchInfo();
    }, 100);
});
$(".batch-delivery-mode-filter .selectpicker").on('change', fillDeliveryNoteBatchInfo);
</script>