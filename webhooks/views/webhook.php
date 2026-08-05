<?php
defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <?php echo form_open($this->uri->uri_string(), ['id' => 'webhook-form']); ?>
    <div class="content">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <i class="fa fa-question-circle pull-left" data-toggle="tooltip"
                                    data-title="<?php echo _l('tootltip_request_action_name'); ?>"></i>
                                <?php echo render_input('webhook_name', '<b>' . _l('webhook_feed_name') . '</b>', $webhook->name ?? ''); ?>
                            </div>
                            <!-- Webhook Triggers -->
                            <div class="col-md-5">
                                <i class="fa fa-question-circle pull-left" data-toggle="tooltip"
                                    data-title="<?php echo _l('tootltip_request_webhook_for'); ?>"></i>
                                <?php echo render_select('webhook_for', get_webhook_triggers(), ['value', 'label', 'subtext'], '<b>' . _l('webhook_for') . '</b>', $webhook->webhook_for ?? ''); ?>
                            </div>
                            <!-- Webhook Actions -->
                            <div class="col-md-7">
                                <div class="form-group chk">
                                    <i class="fa fa-question-circle" data-toggle="tooltip"
                                        data-title="<?php echo _l('selectwebhookcondition'); ?>" data-original-title=""
                                        title=""></i>
                                    <label for="request_url" class="control-label">
                                        <?php echo _l('webhook_action');
                                        ?>
                                    </label>
                                    <br /><span class="valign"></span>
                                    <!-- create -->
                                    <div class="checkbox checkbox-inline">
                                        <input class="field_checkbox" value="add" id="webhook_action_add"
                                            type="checkbox" name="webhook_action[]" <?php echo !empty($webhook) && in_array('add', $webhook->webhook_action) ? 'checked' : ''; ?>>
                                        <label for="webhook_action_add" class="chk-label">
                                            <?php echo _l('permission_create'); ?>
                                        </label>
                                    </div>
                                    <!-- update -->
                                    <div class="checkbox checkbox-inline">
                                        <input class="field_checkbox" value="edit" id="webhook_action_edit"
                                            type="checkbox" name="webhook_action[]" <?php echo !empty($webhook) && in_array('edit', $webhook->webhook_action) ? 'checked' : ''; ?>>
                                        <label for="webhook_action_edit" class="chk-label">
                                            <?php echo _l('webhook_permission_update'); ?>
                                        </label>
                                    </div>
                                    <!-- delete -->
                                    <div class="checkbox checkbox-inline">
                                        <input class="field_checkbox" value="delete" id="webhook_action_delete"
                                            type="checkbox" name="webhook_action[]" <?php echo !empty($webhook) && in_array('delete', $webhook->webhook_action) ? 'checked' : ''; ?>>
                                        <label for="webhook_action_delete" class="chk-label">
                                            <?php echo _l('permission_delete'); ?>
                                        </label>
                                    </div>
                                    <!-- status change -->
                                    <div class="status_change_checkbox checkbox checkbox-inline hide">
                                        <input class="field_checkbox" value="status_change"
                                            id="webhook_action_status_change" type="checkbox" name="webhook_action[]"
                                            <?php echo !empty($webhook) && in_array('status_change', $webhook->webhook_action) ? 'checked' : ''; ?>>
                                        <label for="webhook_action_status_change" class="chk-label">
                                            <?php echo _l('webhook_permission_status_change'); ?>
                                        </label>
                                    </div>
                                    <!-- Accept -->
                                    <div class="accept_checkbox checkbox checkbox-inline hide">
                                        <input class="field_checkbox" value="accept" id="webhook_action_accept"
                                            type="checkbox" name="webhook_action[]" <?php echo !empty($webhook) && in_array('accept', $webhook->webhook_action) ? 'checked' : ''; ?>>
                                        <label for="webhook_action_accept" class="chk-label">
                                            <?php echo _l('webhook_permission_accept'); ?>
                                        </label>
                                    </div>
                                    <!-- Decline -->
                                    <div class="decline_checkbox checkbox checkbox-inline hide">
                                        <input class="field_checkbox" value="decline" id="webhook_action_decline"
                                            type="checkbox" name="webhook_action[]" <?php echo !empty($webhook) && in_array('decline', $webhook->webhook_action) ? 'checked' : ''; ?>>
                                        <label for="webhook_action_decline" class="chk-label">
                                            <?php echo _l('webhook_permission_decline'); ?>
                                        </label>
                                    </div>
                                    <!-- Sent -->
                                    <div class="sent_checkbox checkbox checkbox-inline hide">
                                        <input class="field_checkbox" value="sent" id="webhook_action_sent"
                                            type="checkbox" name="webhook_action[]" <?php echo !empty($webhook) && in_array('sent', $webhook->webhook_action) ? 'checked' : ''; ?>>
                                        <label for="webhook_action_sent" class="chk-label">
                                            <?php echo _l('webhook_permission_sent'); ?>
                                        </label>
                                    </div>
                                    <!-- Proposal converted to estimate -->
                                    <div class="converted_to_estimate checkbox checkbox-inline hide">
                                        <input class="field_checkbox" value="converted_to_estimate" id="webhook_action_proposal_converted_to_estimate"
                                            type="checkbox" name="webhook_action[]" <?php echo !empty($webhook) && in_array('converted_to_estimate', $webhook->webhook_action) ? 'checked' : ''; ?>>
                                        <label for="webhook_action_proposal_converted_to_estimate" class="chk-label">
                                            <?php echo _l('webhook_permission_proposal_converted_to_estimate'); ?>
                                        </label>
                                    </div>
                                    <!-- Convert to invoice -->
                                    <div class="converted_to_invoice checkbox checkbox-inline hide">
                                        <input class="field_checkbox" value="converted_to_invoice" id="webhook_action_converted_to_invoice"
                                            type="checkbox" name="webhook_action[]" <?php echo !empty($webhook) && in_array('converted_to_invoice', $webhook->webhook_action) ? 'checked' : ''; ?>>
                                        <label for="webhook_action_converted_to_invoice" class="chk-label">
                                            <?php echo _l('webhook_permission_converted_to_invoice'); ?>
                                        </label>
                                    </div>
                                    <!-- Lead converted to customer -->
                                    <div class="converted_to_customer checkbox checkbox-inline hide">
                                        <input class="field_checkbox" value="converted_to_customer" id="webhook_action_converted_to_customer"
                                            type="checkbox" name="webhook_action[]" <?php echo !empty($webhook) && in_array('converted_to_customer', $webhook->webhook_action) ? 'checked' : ''; ?>>
                                        <label for="webhook_action_converted_to_customer" class="chk-label">
                                            <?php echo _l('webhook_permission_lead_converted_to_customer'); ?>
                                        </label>
                                    </div>
                                    <!-- Lead marked as lost -->
                                    <div class="marked_as_lost checkbox checkbox-inline hide">
                                        <input class="field_checkbox" value="marked_as_lost" id="webhook_action_marked_as_lost"
                                            type="checkbox" name="webhook_action[]" <?php echo !empty($webhook) && in_array('marked_as_lost', $webhook->webhook_action) ? 'checked' : ''; ?>>
                                        <label for="webhook_action_marked_as_lost" class="chk-label">
                                            <?php echo _l('webhook_permission_lead_marked_as_lost'); ?>
                                        </label>
                                    </div>
                                    <!-- Lead marked as junk -->
                                    <div class="marked_as_junk checkbox checkbox-inline hide">
                                        <input class="field_checkbox" value="marked_as_junk" id="webhook_action_marked_as_junk"
                                            type="checkbox" name="webhook_action[]" <?php echo !empty($webhook) && in_array('marked_as_junk', $webhook->webhook_action) ? 'checked' : ''; ?>>
                                        <label for="webhook_action_marked_as_junk" class="chk-label">
                                            <?php echo _l('webhook_permission_lead_marked_as_junk'); ?>
                                        </label>
                                    </div>
                                    <!-- Invoice copied -->
                                    <div class="invoice_copied checkbox checkbox-inline hide">
                                        <input class="field_checkbox" value="invoice_copied" id="webhook_action_invoice_copied"
                                            type="checkbox" name="webhook_action[]" <?php echo !empty($webhook) && in_array('invoice_copied', $webhook->webhook_action) ? 'checked' : ''; ?>>
                                        <label for="webhook_action_invoice_copied" class="chk-label">
                                            <?php echo _l('webhook_permission_invoice_copied'); ?>
                                        </label>
                                    </div>
                                    <!-- Invoice marked as cancelled -->
                                    <div class="marked_as_cancelled checkbox checkbox-inline hide">
                                        <input class="field_checkbox" value="marked_as_cancelled" id="webhook_action_invoice_marked_as_cancelled"
                                            type="checkbox" name="webhook_action[]" <?php echo !empty($webhook) && in_array('marked_as_cancelled', $webhook->webhook_action) ? 'checked' : ''; ?>>
                                        <label for="webhook_action_invoice_marked_as_cancelled" class="chk-label">
                                            <?php echo _l('webhook_permission_invoice_marked_as_cancelled'); ?>
                                        </label>
                                    </div>
                                    <!-- Refund created -->
                                    <div class="refund_created checkbox checkbox-inline hide">
                                        <input class="field_checkbox" value="refund_created" id="refund_created"
                                            type="checkbox" name="webhook_action[]" <?php echo !empty($webhook) && in_array('refund_created', $webhook->webhook_action) ? 'checked' : ''; ?>>
                                        <label for="refund_created" class="chk-label">
                                            <?php echo _l('webhook_permission_refund_created'); ?>
                                        </label>
                                    </div>
                                    <!-- Credit applied -->
                                    <div class="credit_applied checkbox checkbox-inline hide">
                                        <input class="field_checkbox" value="credit_applied" id="credit_applied"
                                            type="checkbox" name="webhook_action[]" <?php echo !empty($webhook) && in_array('credit_applied', $webhook->webhook_action) ? 'checked' : ''; ?>>
                                        <label for="credit_applied" class="chk-label">
                                            <?php echo _l('webhook_permission_credit_applied'); ?>
                                        </label>
                                    </div>
                                    <!-- Recurring create -->
                                    <div class="recurring_create checkbox checkbox-inline hide">
                                        <input class="field_checkbox" value="recurring_create" id="webhook_action_recurring_create"
                                            type="checkbox" name="webhook_action[]" <?php echo !empty($webhook) && in_array('recurring_create', $webhook->webhook_action) ? 'checked' : ''; ?>>
                                        <label for="webhook_action_recurring_create" class="chk-label">
                                            <?php echo _l('webhook_permission_recurring_create'); ?>
                                        </label>
                                    </div>
                                    <!-- Timer started -->
                                    <div class="timer_started checkbox checkbox-inline hide">
                                        <input class="field_checkbox" value="timer_started" id="task_timer_started"
                                            type="checkbox" name="webhook_action[]" <?php echo !empty($webhook) && in_array('task_timer_started', $webhook->webhook_action) ? 'checked' : ''; ?>>
                                        <label for="task_timer_started" class="chk-label">
                                            <?php echo _l('webhook_permission_task_timer_started'); ?>
                                        </label>
                                    </div>
                                    <!-- Timer deleted -->
                                    <div class="timer_deleted checkbox checkbox-inline hide">
                                        <input class="field_checkbox" value="timer_deleted" id="task_timer_deleted"
                                            type="checkbox" name="webhook_action[]" <?php echo !empty($webhook) && in_array('task_timer_deleted', $webhook->webhook_action) ? 'checked' : ''; ?>>
                                        <label for="task_timer_deleted" class="chk-label">
                                            <?php echo _l('webhook_permission_task_timer_deleted'); ?>
                                        </label>
                                    </div>
                                    <!-- Comment added -->
                                    <div class="comment_added checkbox checkbox-inline hide">
                                        <input class="field_checkbox" value="comment_added" id="task_comment_added"
                                            type="checkbox" name="webhook_action[]" <?php echo !empty($webhook) && in_array('task_comment_added', $webhook->webhook_action) ? 'checked' : ''; ?>>
                                        <label for="task_comment_added" class="chk-label">
                                            <?php echo _l('webhook_permission_task_comment_added'); ?>
                                        </label>
                                    </div>
                                    <!-- Check list item created -->
                                    <div class="checklist_item_created checkbox checkbox-inline hide">
                                        <input class="field_checkbox" value="checklist_item_created" id="task_checklist_item_created"
                                            type="checkbox" name="webhook_action[]" <?php echo !empty($webhook) && in_array('task_checklist_item_created', $webhook->webhook_action) ? 'checked' : ''; ?>>
                                        <label for="task_checklist_item_created" class="chk-label">
                                            <?php echo _l('webhook_permission_task_checklist_item_created'); ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-5 col-md-offset-1">
                <div class="panel_s">
                    <div class="panel-body">
                        <i class="fa fa-question-circle pull-left" data-toggle="tooltip"
                            data-title="<?php echo _l('tootltip_request_url'); ?>"></i>
                        <div class="form-group" app-field-wrapper="request_url">
                            <label for="request_url" class="control-label">
                                <b><?php echo _l('request_url'); ?></b>
                            </label>
                            <div class="input-group">
                                <input type="text" id="request_url" name="request_url" class="form-control"
                                    value="<?php echo html_escape($webhook->request_url ?? ''); ?>">
                                <span class="input-group-btn">
                                    <button type="button" id="test_webhook_btn" class="btn btn-default">
                                        <?php echo _l('test_webhook'); ?>
                                    </button>
                                </span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <i class="fa fa-question-circle pull-left" data-toggle="tooltip"
                                    data-title="<?php echo _l('tootltip_request_method'); ?>"></i>
                                <?php echo render_select('request_method', get_request_method(), ['label', 'value'], _l('request_method'), $webhook->request_method ?? 'GET', [], [], '', '', false); ?>
                            </div>
                            <div class="col-md-6">
                                <i class="fa fa-question-circle pull-left" data-toggle="tooltip"
                                    data-title="<?php echo _l('tootltip_request_format'); ?>"></i>
                                <?php echo render_select('request_format', get_request_format(), ['label', 'value'], _l('request_format'), $webhook->request_format ?? 'FORM', [], [], '', '', false); ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="form-group col-md-12">
                                <label class="control-label">
                                    <i class="fa fa-question-circle" data-toggle="tooltip" data-title=""
                                        data-original-title="" title=""></i>
                                    <b>
                                        <?php echo _l('trigger_after') ?>
                                    </b>
                                </label>
                            </div>
                            <?php echo render_input('webhook_after_number', 'webhook_after_number', $webhook->webhook_after_number ?? "", 'number', ['min' => 0], [], 'col-md-4'); ?>
                            <?php echo render_select('webhook_after_type', getWebhookAfterTypes(), ['id', 'name'], 'webhook_after_type', $webhook->webhook_after_type ?? "", [], [], 'col-md-8', '', true) ?>
                            <div class="col-md-12 mbot10">
                                <label class="control-label">
                                    <?php echo _l('webhook_trigger_delay_note') ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="form-group col-md-12">
                                <label class="control-label">
                                    <i class="fa fa-question-circle" data-toggle="tooltip"
                                        data-title="<?php echo _l('tootltip_request_headers'); ?>"
                                        data-original-title="" title=""></i>
                                    <b>
                                        <?php echo _l('request_headers'); ?>
                                    </b>
                                </label>


                            </div>
                        </div>
                        <div class="request_header_label">
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="control-label">
                                        <?php echo '<b>' . _l('name') . '<b/>'; ?>
                                    </label>
                                </div>
                                <div class="col-md-7">
                                    <label class="control-label">
                                        <?php echo '<b>' . _l('Value') . '<b/>'; ?>
                                    </label>

                                </div>
                            </div>
                        </div>
                        <div class="request_header_row" id="req_header_0">
                            <div class="row">
                                <div class="col-md-4">
                                    <?php echo render_select('header[0][header_choice]', get_header_choices(), ['label', 'value'], '', '', [], [], '', 'header_choice'); ?>
                                    <?php echo render_input('header[0][header_custom_choice]', '', '', '', [], ['style' => 'display: none'], 'header_custom_choice'); ?>
                                    <span style="display: none;" class="header_custom_choice_span"
                                        id="header_custom_choice_span_0"><i class="fa fa-times"></i></span>
                                </div>
                                <div class="col-md-7">
                                    <?php echo render_input('header[0][value]', '', '', 'text', ['placeholder' => _l('press_@_key')], [], '', 'mentionable'); ?>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-sm btn-success add_row"><i
                                            class="fa fa-plus"></i></button>
                                    <button type="button" class="btn btn-sm btn-danger remove_row hidden"
                                        data-count="0"><i class="fa fa-times"></i></button>
                                </div>
                            </div>
                        </div>
                        <?php echo $webhook->request_header_html ?? ''; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="form-group col-md-12" app-field-wrapper="p_category_name">
                                <label class="control-label">
                                    <i class="fa fa-question-circle" data-toggle="tooltip"
                                        data-title="<?php echo _l('tootltip_request_body'); ?>" data-original-title=""
                                        title=""></i>
                                    <b>
                                        <?php echo _l('request_body'); ?>
                                    </b>
                                </label>
                            </div>
                        </div>
                        <span class="label label-warning">
                            <?php echo _l('field_value'); ?>
                        </span>
                        <div class="request_body_label">
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="control-label">
                                        <?php echo '<b>' . _l('Key') . '<b/>'; ?>
                                    </label>
                                </div>
                                <div class="col-md-7">
                                    <label class="control-label">
                                        <?php echo '<b>' . _l('Value') . '<b/>'; ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="request_body_row" id="req_body_0">
                            <div class="row">
                                <div class="col-md-4">
                                    <?php echo render_input('body[0][key]'); ?>
                                </div>
                                <div class="col-md-7">
                                    <?php echo render_input('body[0][value]', '', '', 'text', ['placeholder' => _l('press_@_key')], [], '', 'mentionable'); ?>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-sm btn-success add_body_row"
                                        data-toggle="tooltip"><i class="fa fa-plus"></i></button>
                                    <button type="button" class="btn btn-sm btn-danger remove_body_row hidden"
                                        data-toggle="tooltip" data-count="0"><i class="fa fa-times"></i></button>
                                </div>
                            </div>
                        </div>
                        <?php echo $webhook->request_body_html ?? ''; ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="panel_s">
                        <div class="panel-body">
                            <button group="submit" id="webhook_submit" class="btn btn-primary" data-toggle="tooltip">
                                <?php echo _l('submit'); ?>
                            </button>
                            <a class="btn btn-default" data-toggle="tooltip"
                                href="<?php echo admin_url(WEBHOOKS_MODULE); ?>">
                                <?php echo _l('close'); ?>
                            </a>
                            <?php echo form_close(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
</div>
<?php init_tail(); ?>
<script type="text/javascript">
    $(function () {
        "use strict";
        $(document).on('click', '.add_row', function(event) {
            event.preventDefault();
            var total_element = $(".request_header_row").length;
            var last_id = $(".request_header_row:last").attr('id').split("_");
            var next_id = Number(last_id[2]) + 1;
            $(`#req_header_0 .header_choice`).selectpicker('destroy');
            $("#req_header_0").clone()
                .attr('id', `req_header_${next_id}`)
                .html((i, OldHtml) => {
                    OldHtml = OldHtml.replaceAll("header[0][header_choice]", `header[${next_id}][header_choice]`);
                    OldHtml = OldHtml.replaceAll("header[0][header_custom_choice]", `header[${next_id}][header_custom_choice]`);
                    OldHtml = OldHtml.replaceAll("header_custom_choice_span_0", `header_custom_choice_span_${next_id}`);
                    OldHtml = OldHtml.replaceAll("header[0][value]", `header[${next_id}][value]`);
                    return OldHtml;
                })
                .appendTo($(".request_header_row:last").parent());
            $(`#req_header_${next_id} .add_row`).remove();
            $(`#req_header_${next_id} :input`).val("");
            $(`#req_header_0 .header_choice`).selectpicker('refresh');
            $(`#req_header_${next_id} .header_choice`).selectpicker('refresh').parents(".form-group").show();
            $(`#req_header_${next_id} .header_custom_choice`).hide();
            $(`#req_header_${next_id} #header_custom_choice_span_${next_id}`).hide();
            $(`#req_header_${next_id} .remove_row`).removeClass('hidden').data('count', next_id);

            refreshTribute();
        });
        
        $(document).on('click', '.add_body_row', function(event) {
            var total_element = $(".request_body_row").length;
            var last_id = $(".request_body_row:last").attr('id').split("_");
            var next_id = Number(last_id[2]) + 1;
            $("#req_body_0").clone()
                .attr('id', `req_body_${next_id}`)
                .html((i, OldHtml) => {
                    OldHtml = OldHtml.replaceAll("body[0][key]", `body[${next_id}][key]`);
                    OldHtml = OldHtml.replaceAll("body[0][value]", `body[${next_id}][value]`);
                    return OldHtml;
                })
                .appendTo($(".request_body_row:last").parent());
            $(`#req_body_${next_id} .add_body_row`).remove();
            $(`#req_body_${next_id} :input`).val("");
            $(`#req_body_${next_id} .remove_body_row`).removeClass('hidden').data('count', next_id);

            refreshTribute();
        });

        $(document).on('click', '.remove_row', function (event) {
            event.preventDefault();
            $('#req_header_' + $(this).data('count')).remove();
        });
        $(document).on('click', '.remove_body_row', function (event) {
            event.preventDefault();
            $('#req_body_' + $(this).data('count')).remove();
        });
        $(document).on('change', '.header_choice', function (event) {
            event.preventDefault();
            if ($(this).val() == "custom") {
                $(this).parents('.form-group').hide();
                $(this).parents('.form-group').siblings('.header_custom_choice').show();
                $(this).parents('.form-group').siblings('.header_custom_choice_span').show();
            }
        });
        $(document).on('click', '.header_custom_choice_span', function (event) {
            $(this).parent().find('.header_custom_choice').val("").hide();
            $(this).parent().find('.header_choice').selectpicker("val", "").selectpicker("refresh").parents(".form-group").show();
            $(this).hide();
        });

        $(document).on('change', '#request_method, #request_format', function (event) {
            event.preventDefault();
            if (($("#request_method").val() == "GET" || $("#request_method").val() == "DELETE") && $("#request_format").val() == "JSON") {
                alert_float("warning", "Reminder: GET / DELETE methods do not support JSON format");
                $("#webhook_submit").prop('disabled', true);
            } else {
                $("#webhook_submit").prop('disabled', false);
            }
        });

    });
</script>
