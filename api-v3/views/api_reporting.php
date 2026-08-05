<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>

<link href="<?php echo base_url('modules/api/assets/main.css'); ?>" rel="stylesheet" type="text/css" />
<script src="https://code.highcharts.com/highcharts.js"></script>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="no-margin"><?php echo _l('api_reporting'); ?></h4>
                                <hr class="hr-panel-heading" />
                            </div>
                        </div>
                        
                        <!-- Filters -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-info">
                                    <div class="panel-heading">
                                        <h4 class="panel-title"><?php echo _l('filters'); ?></h4>
                                    </div>
                                    <div class="panel-body">
                                        <?php echo form_open(admin_url('api/reporting'), 'method="get"', ['id' => 'reporting-filters']); ?>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="api_key"><?php echo _l('api_key'); ?></label>
                                                    <select name="api_key" id="api_key" class="form-control">
                                                        <option value=""><?php echo _l('all_api_keys'); ?></option>
                                                        <?php foreach ($api_keys as $key) { ?>
                                                        <option value="<?php echo $key['api_key']; ?>" <?php echo ($api_key == $key['api_key']) ? 'selected' : ''; ?>>
                                                            <?php echo $key['api_key']; ?>
                                                        </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="start_date"><?php echo _l('start_date'); ?></label>
                                                    <input type="date" name="start_date" id="start_date" class="form-control" value="<?php echo $start_date; ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="end_date"><?php echo _l('end_date'); ?></label>
                                                    <input type="date" name="end_date" id="end_date" class="form-control" value="<?php echo $end_date; ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>&nbsp;</label>
                                                    <div>
                                                        <button type="submit" class="btn btn-info"><?php echo _l('apply_filters'); ?></button>
                                                        <a href="<?php echo admin_url('api/reporting/export?' . http_build_query($_GET)); ?>" class="btn btn-success"><?php echo _l('export'); ?></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php echo form_close(); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Summary Cards -->
                        <div class="row">
                            <div class="col-md-3">
                                <div class="panel panel-primary">
                                    <div class="panel-heading">
                                        <h4 class="panel-title"><?php echo _l('total_requests'); ?></h4>
                                    </div>
                                    <div class="panel-body text-center">
                                        <h2><?php echo number_format($usage_stats->total_requests ?? 0); ?></h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel panel-success">
                                    <div class="panel-heading">
                                        <h4 class="panel-title"><?php echo _l('success_rate'); ?></h4>
                                    </div>
                                    <div class="panel-body text-center">
                                        <h2><?php echo ($usage_stats->total_requests ?? 0) > 0 ? round((($usage_stats->success_requests ?? 0) / ($usage_stats->total_requests ?? 1)) * 100, 2) : 0; ?>%</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel panel-warning">
                                    <div class="panel-heading">
                                        <h4 class="panel-title"><?php echo _l('avg_response_time'); ?></h4>
                                    </div>
                                    <div class="panel-body text-center">
                                        <h2><?php echo round($usage_stats->avg_response_time ?? 0, 4); ?>s</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel panel-danger">
                                    <div class="panel-heading">
                                        <h4 class="panel-title"><?php echo _l('error_requests'); ?></h4>
                                    </div>
                                    <div class="panel-body text-center">
                                        <h2><?php echo number_format($usage_stats->error_requests ?? 0); ?></h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Charts -->
                        <div class="row">
                            <div class="col-md-8">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title"><?php echo _l('request_timeline'); ?></h4>
                                    </div>
                                    <div class="panel-body">
                                        <div id="request-timeline-chart" style="height: 400px;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title"><?php echo _l('response_codes'); ?></h4>
                                    </div>
                                    <div class="panel-body">
                                        <div id="response-codes-chart" style="height: 400px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Endpoint Statistics -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title"><?php echo _l('endpoint_statistics'); ?></h4>
                                    </div>
                                    <div class="panel-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th><?php echo _l('endpoint'); ?></th>
                                                        <th><?php echo _l('request_count'); ?></th>
                                                        <th><?php echo _l('avg_response_time'); ?></th>
                                                        <th><?php echo _l('success_count'); ?></th>
                                                        <th><?php echo _l('error_count'); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($endpoint_stats as $stat) { ?>
                                                    <tr>
                                                        <td><?php echo $stat->endpoint; ?></td>
                                                        <td><?php echo number_format($stat->request_count ?? 0); ?></td>
                                                        <td><?php echo round($stat->avg_response_time ?? 0, 4); ?>s</td>
                                                        <td><?php echo number_format($stat->success_count ?? 0); ?></td>
                                                        <td><?php echo number_format($stat->error_count ?? 0); ?></td>
                                                    </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- API Key Summary -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title"><?php echo _l('api_key_summary'); ?></h4>
                                    </div>
                                    <div class="panel-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th><?php echo _l('api_key'); ?></th>
                                                        <th><?php echo _l('total_requests'); ?></th>
                                                        <th><?php echo _l('avg_response_time'); ?></th>
                                                        <th><?php echo _l('success_requests'); ?></th>
                                                        <th><?php echo _l('error_requests'); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($api_key_summary as $summary) { ?>
                                                    <tr>
                                                        <td><?php echo $summary->api_key; ?></td>
                                                        <td><?php echo number_format($summary->total_requests ?? 0); ?></td>
                                                        <td><?php echo round($summary->avg_response_time ?? 0, 4); ?>s</td>
                                                        <td><?php echo number_format($summary->success_requests ?? 0); ?></td>
                                                        <td><?php echo number_format($summary->error_requests ?? 0); ?></td>
                                                    </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>


<script>
    $(document).ready(function() {
        // Request Timeline Chart
        var timelineData = <?php echo json_encode($hourly_usage); ?>;
        var timelineChart = Highcharts.chart('request-timeline-chart', {
            title: {
                text: '<?php echo _l('request_timeline'); ?>'
            },
            xAxis: {
                type: 'datetime',
                title: {
                    text: '<?php echo _l('time'); ?>'
                }
            },
            yAxis: {
                title: {
                    text: '<?php echo _l('requests'); ?>'
                }
            },
            series: [{
                name: '<?php echo _l('requests'); ?>',
                data: timelineData.map(function(item) {
                    return [new Date(item.hour).getTime(), parseInt(item.request_count)];
                })
            }],
            tooltip: {
                pointFormat: '{series.name}: <b>{point.y}</b>'
            }
        });
        
        // Response Codes Chart
        var responseCodesData = <?php echo json_encode($response_codes); ?>;
        var responseCodesChart = Highcharts.chart('response-codes-chart', {
            chart: {
                type: 'pie'
            },
            title: {
                text: '<?php echo _l('response_codes'); ?>'
            },
            series: [{
                name: '<?php echo _l('requests'); ?>',
                data: responseCodesData.map(function(item) {
                    return [item.response_code.toString(), parseInt(item.count)];
                })
            }],
            tooltip: {
                pointFormat: '{series.name}: <b>{point.y}</b>'
            }
        });
    });
</script>

<script src="<?php echo base_url('modules/api/assets/main.js'); ?>"></script>
