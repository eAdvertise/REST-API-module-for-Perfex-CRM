<?php
defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <div class="row">
                                <div class="col-md-6">
                                    <h4><?php echo $title; ?></h4>
                                </div>
                                <div class="col-md-6">
                                    <a href="<?php echo admin_url(WEBHOOKS_MODULE . '/clear_webhook_log'); ?>" class="btn btn-danger pull-right"><?php echo _l('clear_activity_log'); ?></a>
                                </div>
                            </div>
                        </div>
                        <hr class="hr-panel-heading" />
                        <div class="row">
                            <div class="col-md-3">
                                <?php echo render_select('response_codes[]', getResponseCodes(), ['id', 'name'], 'response_code', '', ['data-width' => '100%', 'data-none-selected-text' => _l('all'), 'multiple' => true, 'data-actions-box' => true], [], 'no-mbot', '', false); ?>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group" id="date_created_filter">
                                    <label for="date_created"><?php echo _l('date_created'); ?></label><br />
                                    <select class="selectpicker" name="date_created" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                        <option value="" selected><?= _l('all_time') ?></option>
                                        <option value='<?php echo json_encode([_d(date('Y-m-d')),  _d(date('Y-m-d'))]); ?>'>
                                            <?php echo _l('today'); ?>
                                        </option>
                                        <option value='<?php echo json_encode([_d(date('Y-m-d', strtotime("-1 days"))), _d(date('Y-m-d', strtotime('-1 days')))]); ?>'>
                                            <?php echo _l('yesterday'); ?>
                                        </option>
                                        <option value='<?php echo json_encode([_d(date('Y-m-d', strtotime('monday this week'))), _d(date('Y-m-d', strtotime('sunday this week')))]); ?>'>
                                            <?php echo _l('this_week'); ?>
                                        </option>
                                        <?php
                                        $previous_week = strtotime("-1 week +1 day");
                                        $start_week = strtotime("last sunday midnight", $previous_week);
                                        $end_week = strtotime("next saturday", $start_week);
                                        $start_week = date("Y-m-d", $start_week);
                                        $end_week = date("Y-m-d", $end_week);
                                        $last_week = json_encode(array(_d($start_week), _d($end_week)));
                                        ?>
                                        <option value='<?= $last_week ?>'>
                                            <?= _l('last_week'); ?>
                                        </option>
                                        <option value='<?php echo json_encode([_d(date('Y-m-01')), _d(date('Y-m-t'))]); ?>'>
                                            <?php echo _l('this_month'); ?>
                                        </option>
                                        <option value='<?php echo json_encode([_d(date('Y-m-01', strtotime("-1 MONTH"))), _d(date('Y-m-t', strtotime('-1 MONTH')))]); ?>'>
                                            <?php echo _l('last_month'); ?>
                                        </option>
                                        <option value='<?php echo json_encode([_d(date('Y-m-d', strtotime(date('Y-01-01')))), _d(date('Y-m-d', strtotime(date('Y-12-31'))))]); ?>'>
                                            <?php echo _l('this_year'); ?>
                                        </option>
                                        <option value='<?php echo json_encode([_d(date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-01-01')))), _d(date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-12-31'))))]); ?>'>
                                            <?php echo _l('last_year'); ?>
                                        </option>
                                        <option value="custom"><?php echo _l('period_datepicker'); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="hide" id="date-range">
                                <div class="col-md-3">
                                    <?= render_date_input('from_date', 'from_date') ?>
                                </div>
                                <div class="col-md-3">
                                    <?= render_date_input('to_date', 'to_date', '', ['disabled' => 1]) ?>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <div class="clearfix"></div>
                        <?php render_datatable([
                            _l('webhook_feed_name'),
                            _l('response_code'),
                            _l('recorded_on'),
                            _l('actions'),
                        ], 'webhooks-logs');
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
    var ServerParams = {
        "date_created": '[name="date_created"]',
        "from_date": '[name="from_date"]',
        "to_date": '[name="to_date"]',
        "response_codes": '[name="response_codes[]"]',
        "webhooks": '[name="webhooks[]"]',
    };
    initDataTable('.table-webhooks-logs', window.location.href, undefined, undefined, ServerParams, [2, 'desc']);

    $('select[name="date_created"]').on('change', function() {
        if ($(this).val() == "custom") {
            $('#date-range').removeClass('hide');
        } else {
            $('#date-range').addClass('hide');
        }
        $('.table-webhooks-logs').DataTable().ajax.reload();
    });

    $('#from_date').on('change', function() {
        if ($(this).val() != "") {
            $('#to_date').prop('disabled', false);
        }
        if ('#to_date' != '') {
            $('.table-webhooks-logs').DataTable().ajax.reload();
        }
    });

    $('#to_date').on('change', function() {
        if ($(this).val() != "") {
            $('.table-webhooks-logs').DataTable().ajax.reload();
        }
    });

    $('select[name="response_codes[]"], select[name="webhooks[]"]').on('change', function() {
        $('.table-webhooks-logs').DataTable().ajax.reload();
    });
</script>