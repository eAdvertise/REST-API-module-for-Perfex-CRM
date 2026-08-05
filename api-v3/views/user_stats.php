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
                                <h4 class="no-margin"><?php echo _l('user_statistics'); ?></h4>
                                <hr class="hr-panel-heading" />
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="user_select" class="control-label"><?php echo _l('select_api_user'); ?></label>
                                    <select class="form-control" id="user_select" name="user_id">
                                        <option value=""><?php echo _l('select_api_user'); ?></option>
                                        <?php foreach ($api_users as $user) { ?>
                                            <option value="<?php echo $user['id']; ?>" <?php echo ($user_id == $user['id']) ? 'selected' : ''; ?>>
                                                <?php echo $user['name'] . ' (' . $user['user'] . ')'; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="days_select" class="control-label"><?php echo _l('time_period'); ?></label>
                                    <select class="form-control" id="days_select" name="days">
                                        <option value="7"><?php echo _l('last_7_days'); ?></option>
                                        <option value="30" selected><?php echo _l('last_30_days'); ?></option>
                                        <option value="90"><?php echo _l('last_90_days'); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label">&nbsp;</label>
                                    <button type="button" id="load_stats" class="btn btn-primary btn-block"><?php echo _l('load_statistics'); ?></button>
                                </div>
                            </div>
                        </div>
                        
                        <div id="stats_content" style="display: none;">
                            <!-- Quota Summary -->
                            <div class="row" id="quota_summary">
                                <div class="col-md-12">
                                    <h5><?php echo _l('quota_summary'); ?></h5>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="panel_s">
                                                <div class="panel-body text-center">
                                                    <h3 class="text-primary" id="total_requests">0</h3>
                                                    <p class="text-muted"><?php echo _l('total_requests'); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="panel_s">
                                                <div class="panel-body text-center">
                                                    <h3 class="text-success" id="success_requests">0</h3>
                                                    <p class="text-muted"><?php echo _l('success_requests'); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="panel_s">
                                                <div class="panel-body text-center">
                                                    <h3 class="text-danger" id="error_requests">0</h3>
                                                    <p class="text-muted"><?php echo _l('error_requests'); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="panel_s">
                                                <div class="panel-body text-center">
                                                    <h3 class="text-info" id="avg_response_time">0ms</h3>
                                                    <p class="text-muted"><?php echo _l('avg_response_time'); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Usage Chart -->
                            <div class="row">
                                <div class="col-md-12">
                                    <h5><?php echo _l('usage_over_time'); ?></h5>
                                    <div id="usage_chart" style="height: 300px;"></div>
                                </div>
                            </div>
                            
                            <!-- Top Endpoints -->
                            <div class="row">
                                <div class="col-md-12">
                                    <h5><?php echo _l('top_endpoints'); ?></h5>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th><?php echo _l('endpoint'); ?></th>
                                                    <th><?php echo _l('request_count'); ?></th>
                                                    <th><?php echo _l('success_count'); ?></th>
                                                    <th><?php echo _l('error_count'); ?></th>
                                                    <th><?php echo _l('avg_response_time'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody id="top_endpoints_table">
                                                <tr>
                                                    <td colspan="5" class="text-center"><?php echo _l('no_data_available'); ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div id="no_user_selected" class="text-center" style="display: none;">
                            <p class="text-muted"><?php echo _l('select_api_user_to_view_statistics'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
    // Ensure jQuery is available
    (function() {
        function initUserStats() {
            if (typeof jQuery === 'undefined') {
                console.error('jQuery is not loaded. Retrying in 100ms...');
                setTimeout(initUserStats, 100);
                return;
            }
            
            jQuery(document).ready(function($) {
                // Load stats if user is pre-selected
                <?php if ($user_id) { ?>
                    loadUserStats();
                <?php } ?>
                
                $('#load_stats').click(function() {
                    loadUserStats();
                });
                
                function loadUserStats() {
                    var userId = $('#user_select').val();
                    var days = $('#days_select').val();
                    
                    if (!userId) {
                        $('#stats_content').hide();
                        $('#no_user_selected').show();
                        return;
                    }
                    
                    $('#no_user_selected').hide();
                    $('#stats_content').show();
                    
                    $.ajax({
                        url: '<?php echo admin_url('api/get_user_stats_data'); ?>',
                        type: 'POST',
                        data: {
                            user_id: userId,
                            days: days
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.error) {
                                alert('<?php echo _l('error_loading_statistics'); ?>');
                                return;
                            }
                            
                            // Update quota summary
                            if (response.quota_summary) {
                                $('#total_requests').text(response.quota_summary.total_requests || 0);
                                $('#success_requests').text(response.quota_summary.success_requests || 0);
                                $('#error_requests').text(response.quota_summary.error_requests || 0);
                                $('#avg_response_time').text((response.quota_summary.avg_response_time || 0) + 'ms');
                            }
                            
                            // Update top endpoints table
                            updateTopEndpointsTable(response.top_endpoints || []);
                            
                            // Draw usage chart
                            drawUsageChart(response.quota_stats || []);
                        },
                        error: function() {
                            alert('<?php echo _l('error_loading_statistics'); ?>');
                        }
                    });
                }
                
                function updateTopEndpointsTable(endpoints) {
                    var tbody = $('#top_endpoints_table');
                    tbody.empty();
                    
                    if (endpoints.length === 0) {
                        tbody.append('<tr><td colspan="5" class="text-center"><?php echo _l('no_data_available'); ?></td></tr>');
                        return;
                    }
                    
                    endpoints.forEach(function(endpoint) {
                        tbody.append(
                            '<tr>' +
                            '<td>' + endpoint.endpoint + '</td>' +
                            '<td>' + endpoint.request_count + '</td>' +
                            '<td>' + endpoint.success_count + '</td>' +
                            '<td>' + endpoint.error_count + '</td>' +
                            '<td>' + endpoint.avg_response_time + 'ms</td>' +
                            '</tr>'
                        );
                    });
                }
                
                function drawUsageChart(stats) {
                    if (typeof Highcharts === 'undefined') {
                        console.log('Highcharts not loaded');
                        return;
                    }
                    
                    var categories = [];
                    var requests = [];
                    var errors = [];
                    
                    stats.forEach(function(stat) {
                        categories.push(stat.date);
                        requests.push(parseInt(stat.request_count));
                        errors.push(parseInt(stat.error_count));
                    });
                    
                    Highcharts.chart('usage_chart', {
                        chart: {
                            type: 'line'
                        },
                        title: {
                            text: '<?php echo _l('usage_over_time'); ?>'
                        },
                        xAxis: {
                            categories: categories
                        },
                        yAxis: {
                            title: {
                                text: '<?php echo _l('request_count'); ?>'
                            }
                        },
                        series: [{
                            name: '<?php echo _l('total_requests'); ?>',
                            data: requests,
                            color: '#1f77b4'
                        }, {
                            name: '<?php echo _l('error_requests'); ?>',
                            data: errors,
                            color: '#d62728'
                        }]
                    });
                }
            });
        }
        
        // Start the initialization
        initUserStats();
    })();
</script>

<script src="<?php echo base_url('modules/api/assets/main.js'); ?>"></script>
