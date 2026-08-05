<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>

<link href="<?php echo base_url('modules/api/assets/main.css'); ?>" rel="stylesheet" type="text/css" />

<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
                  <h4 class="no-margin"><?php echo _l('api_settings'); ?></h4>
                  <hr class="hr-panel-heading" />
                  
                  <?php echo form_open(admin_url('api/save_settings')); ?>
                  
                  <!-- JSON Response Normalization Section -->
                  <div class="panel panel-info" style="margin-top: 20px;">
                     <div class="panel-heading">
                        <h4 class="panel-title">
                           <i class="fa fa-code"></i> <?php echo _l('json_response_normalization'); ?>
                        </h4>
                     </div>
                     <div class="panel-body">
                        <div class="row">
                           <div class="col-md-12">
                              <div class="checkbox checkbox-primary">
                                 <input type="hidden" name="api_enable_transformers" value="0">
                                 <input type="checkbox" name="api_enable_transformers" id="api_enable_transformers" value="1" 
                                        <?php echo (get_option('api_enable_transformers') == '1') ? 'checked' : ''; ?>>
                                 <label for="api_enable_transformers">
                                    <?php echo _l('enable_json_normalization'); ?>
                                 </label>
                              </div>
                              <small class="help-block"><?php echo _l('enable_json_normalization_help'); ?></small>
                           </div>
                        </div>
                     </div>
                  </div>
                  
                  <!-- v3 Platform Settings Section -->
                  <div class="panel panel-primary" style="margin-top: 20px;">
                     <div class="panel-heading">
                        <h4 class="panel-title">
                           <i class="fa fa-rocket"></i> <?php echo _l('api_v3_settings'); ?>
                        </h4>
                     </div>
                     <div class="panel-body">
                        <div class="row">
                           <div class="col-md-6">
                              <div class="checkbox checkbox-primary">
                                 <input type="hidden" name="api_mcp_enabled" value="0">
                                 <input type="checkbox" name="api_mcp_enabled" id="api_mcp_enabled" value="1"
                                        <?php echo (get_option('api_mcp_enabled') == '1') ? 'checked' : ''; ?>>
                                 <label for="api_mcp_enabled">
                                    <?php echo _l('api_mcp_enabled_label'); ?>
                                 </label>
                              </div>
                              <small class="help-block"><?php echo _l('api_mcp_enabled_help'); ?></small>

                              <div class="checkbox checkbox-primary" style="margin-top: 15px;">
                                 <input type="hidden" name="api_staff_visibility" value="0">
                                 <input type="checkbox" name="api_staff_visibility" id="api_staff_visibility" value="1"
                                        <?php echo (get_option('api_staff_visibility') == '1') ? 'checked' : ''; ?>>
                                 <label for="api_staff_visibility">
                                    <?php echo _l('api_staff_visibility_label'); ?>
                                 </label>
                              </div>
                              <small class="help-block"><?php echo _l('api_staff_visibility_help'); ?></small>

                              <div class="form-group" style="margin-top: 15px;">
                                 <label for="api_auth_throttle_limit"><?php echo _l('api_auth_throttle_limit_label'); ?></label>
                                 <input type="number" class="form-control" name="api_auth_throttle_limit" id="api_auth_throttle_limit"
                                        min="0" max="1000"
                                        value="<?php echo get_option('api_auth_throttle_limit') !== '' ? get_option('api_auth_throttle_limit') : '20'; ?>">
                                 <small class="help-block"><?php echo _l('api_auth_throttle_limit_help'); ?></small>
                              </div>

                              <div class="form-group" style="margin-top: 15px;">
                                 <label for="api_thirdparty_allowed_tables"><?php echo _l('api_thirdparty_allowed_tables_label'); ?></label>
                                 <textarea class="form-control" rows="2" name="api_thirdparty_allowed_tables" id="api_thirdparty_allowed_tables" placeholder="tblmodule_x, tblmodule_y"><?php echo get_option('api_thirdparty_allowed_tables'); ?></textarea>
                                 <small class="help-block"><?php echo _l('api_thirdparty_allowed_tables_help'); ?></small>
                              </div>
                           </div>
                           <div class="col-md-6">
                              <div class="form-group">
                                 <label for="api_webhook_delivery_mode"><?php echo _l('api_webhook_delivery_mode_label'); ?></label>
                                 <select class="form-control" name="api_webhook_delivery_mode" id="api_webhook_delivery_mode">
                                    <option value="immediate" <?php echo (get_option('api_webhook_delivery_mode') != 'queue') ? 'selected' : ''; ?>><?php echo _l('api_webhook_mode_immediate'); ?></option>
                                    <option value="queue" <?php echo (get_option('api_webhook_delivery_mode') == 'queue') ? 'selected' : ''; ?>><?php echo _l('api_webhook_mode_queue'); ?></option>
                                 </select>
                                 <small class="help-block"><?php echo _l('api_webhook_delivery_mode_help'); ?></small>
                              </div>

                              <div class="checkbox checkbox-primary" style="margin-top: 15px;">
                                 <input type="hidden" name="api_webhook_ssrf_strict" value="0">
                                 <input type="checkbox" name="api_webhook_ssrf_strict" id="api_webhook_ssrf_strict" value="1"
                                        <?php echo (get_option('api_webhook_ssrf_strict') == '1') ? 'checked' : ''; ?>>
                                 <label for="api_webhook_ssrf_strict">
                                    <?php echo _l('api_webhook_ssrf_strict_label'); ?>
                                 </label>
                              </div>
                              <small class="help-block"><?php echo _l('api_webhook_ssrf_strict_help'); ?></small>

                              <div class="checkbox checkbox-primary" style="margin-top: 15px;">
                                 <input type="hidden" name="api_webhook_ssl_verify" value="0">
                                 <input type="checkbox" name="api_webhook_ssl_verify" id="api_webhook_ssl_verify" value="1"
                                        <?php echo (get_option('api_webhook_ssl_verify') !== '0') ? 'checked' : ''; ?>>
                                 <label for="api_webhook_ssl_verify">
                                    <?php echo _l('api_webhook_ssl_verify_label'); ?>
                                 </label>
                              </div>
                              <small class="help-block"><?php echo _l('api_webhook_ssl_verify_help'); ?></small>
                           </div>
                        </div>
                     </div>
                  </div>

                  <!-- Middleware Settings Section -->
                  <div class="panel panel-info" style="margin-top: 20px;">
                     <div class="panel-heading">
                        <h4 class="panel-title">
                           <i class="fa fa-shield"></i> <?php echo _l('middleware_settings'); ?>
                        </h4>
                     </div>
                     <div class="panel-body">
                        <small class="help-block" style="margin-bottom: 20px;"><?php echo _l('middleware_settings_help'); ?></small>
                        
                        <?php
                        // Load middleware config
                        $middleware_config = get_option('api_middleware_config');
                        if (!empty($middleware_config)) {
                            $middleware_config = json_decode($middleware_config, true);
                        } else {
                            $middleware_config = [
                                'request_logger' => ['enabled' => true],
                                'response_cache' => ['enabled' => false, 'ttl' => 300],
                                'ip_whitelist' => ['enabled' => false, 'ips' => []],
                                'ip_blacklist' => ['enabled' => false, 'ips' => []],
                                'security_headers' => ['enabled' => false],
                                'request_size_limit' => ['enabled' => false, 'max_size_mb' => 10]
                            ];
                        }
                        ?>
                        
                        <!-- Request Logging -->
                        <div class="row" style="margin-bottom: 20px;">
                           <div class="col-md-12">
                              <div class="checkbox checkbox-primary">
                                 <input type="hidden" name="middleware_request_logger" value="0">
                                 <input type="checkbox" name="middleware_request_logger" id="middleware_request_logger" value="1" 
                                        <?php echo (isset($middleware_config['request_logger']['enabled']) && $middleware_config['request_logger']['enabled']) ? 'checked' : ''; ?>>
                                 <label for="middleware_request_logger">
                                    <?php echo _l('enable_request_logging'); ?>
                                 </label>
                              </div>
                              <small class="help-block"><?php echo _l('enable_request_logging_help'); ?></small>
                           </div>
                        </div>
                        
                        <!-- Response Caching -->
                        <div class="row" style="margin-bottom: 20px;">
                           <div class="col-md-12">
                              <div class="checkbox checkbox-primary">
                                 <input type="hidden" name="middleware_response_cache" value="0">
                                 <input type="checkbox" name="middleware_response_cache" id="middleware_response_cache" value="1" 
                                        <?php echo (isset($middleware_config['response_cache']['enabled']) && $middleware_config['response_cache']['enabled']) ? 'checked' : ''; ?>>
                                 <label for="middleware_response_cache">
                                    <?php echo _l('enable_response_caching'); ?>
                                 </label>
                              </div>
                              <small class="help-block"><?php echo _l('enable_response_caching_help'); ?></small>
                              <div class="form-group" style="margin-top: 10px; margin-left: 25px;">
                                 <label for="middleware_cache_ttl"><?php echo _l('cache_ttl'); ?></label>
                                 <input type="number" name="middleware_cache_ttl" id="middleware_cache_ttl" class="form-control" 
                                        value="<?php echo isset($middleware_config['response_cache']['ttl']) ? $middleware_config['response_cache']['ttl'] : 300; ?>" 
                                        min="60" max="3600" step="60">
                                 <small class="help-block"><?php echo _l('cache_ttl_help'); ?></small>
                              </div>
                           </div>
                        </div>
                        
                        <!-- IP Whitelist -->
                        <div class="row" style="margin-bottom: 20px;">
                           <div class="col-md-12">
                              <div class="checkbox checkbox-primary">
                                 <input type="hidden" name="middleware_ip_whitelist" value="0">
                                 <input type="checkbox" name="middleware_ip_whitelist" id="middleware_ip_whitelist" value="1" 
                                        <?php echo (isset($middleware_config['ip_whitelist']['enabled']) && $middleware_config['ip_whitelist']['enabled']) ? 'checked' : ''; ?>>
                                 <label for="middleware_ip_whitelist">
                                    <?php echo _l('enable_ip_whitelist'); ?>
                                 </label>
                              </div>
                              <small class="help-block"><?php echo _l('enable_ip_whitelist_help'); ?></small>
                              <div class="form-group" style="margin-top: 10px; margin-left: 25px;">
                                 <label for="middleware_ip_whitelist_ips"><?php echo _l('ip_whitelist'); ?></label>
                                 <textarea name="middleware_ip_whitelist_ips" id="middleware_ip_whitelist_ips" class="form-control" rows="4" 
                                           placeholder="192.168.1.1&#10;10.0.0.0/24"><?php echo isset($middleware_config['ip_whitelist']['ips']) ? implode("\n", $middleware_config['ip_whitelist']['ips']) : ''; ?></textarea>
                                 <small class="help-block"><?php echo _l('ip_whitelist_help'); ?></small>
                              </div>
                           </div>
                        </div>
                        
                        <!-- IP Blacklist -->
                        <div class="row" style="margin-bottom: 20px;">
                           <div class="col-md-12">
                              <div class="checkbox checkbox-primary">
                                 <input type="hidden" name="middleware_ip_blacklist" value="0">
                                 <input type="checkbox" name="middleware_ip_blacklist" id="middleware_ip_blacklist" value="1" 
                                        <?php echo (isset($middleware_config['ip_blacklist']['enabled']) && $middleware_config['ip_blacklist']['enabled']) ? 'checked' : ''; ?>>
                                 <label for="middleware_ip_blacklist">
                                    <?php echo _l('enable_ip_blacklist'); ?>
                                 </label>
                              </div>
                              <small class="help-block"><?php echo _l('enable_ip_blacklist_help'); ?></small>
                              <div class="form-group" style="margin-top: 10px; margin-left: 25px;">
                                 <label for="middleware_ip_blacklist_ips"><?php echo _l('ip_blacklist'); ?></label>
                                 <textarea name="middleware_ip_blacklist_ips" id="middleware_ip_blacklist_ips" class="form-control" rows="4" 
                                           placeholder="192.168.1.100&#10;10.0.0.0/8"><?php echo isset($middleware_config['ip_blacklist']['ips']) ? implode("\n", $middleware_config['ip_blacklist']['ips']) : ''; ?></textarea>
                                 <small class="help-block"><?php echo _l('ip_blacklist_help'); ?></small>
                              </div>
                           </div>
                        </div>
                        
                        <!-- Security Headers -->
                        <div class="row" style="margin-bottom: 20px;">
                           <div class="col-md-12">
                              <div class="checkbox checkbox-primary">
                                 <input type="hidden" name="middleware_security_headers" value="0">
                                 <input type="checkbox" name="middleware_security_headers" id="middleware_security_headers" value="1" 
                                        <?php echo (isset($middleware_config['security_headers']['enabled']) && $middleware_config['security_headers']['enabled']) ? 'checked' : ''; ?>>
                                 <label for="middleware_security_headers">
                                    <?php echo _l('enable_security_headers'); ?>
                                 </label>
                              </div>
                              <small class="help-block"><?php echo _l('enable_security_headers_help'); ?></small>
                           </div>
                        </div>
                        
                        <!-- Request Size Limit -->
                        <div class="row" style="margin-bottom: 20px;">
                           <div class="col-md-12">
                              <div class="checkbox checkbox-primary">
                                 <input type="hidden" name="middleware_request_size_limit" value="0">
                                 <input type="checkbox" name="middleware_request_size_limit" id="middleware_request_size_limit" value="1" 
                                        <?php echo (isset($middleware_config['request_size_limit']['enabled']) && $middleware_config['request_size_limit']['enabled']) ? 'checked' : ''; ?>>
                                 <label for="middleware_request_size_limit">
                                    <?php echo _l('enable_request_size_limit'); ?>
                                 </label>
                              </div>
                              <small class="help-block"><?php echo _l('enable_request_size_limit_help'); ?></small>
                              <div class="form-group" style="margin-top: 10px; margin-left: 25px;">
                                 <label for="middleware_max_request_size_mb"><?php echo _l('max_request_size_mb'); ?></label>
                                 <input type="number" name="middleware_max_request_size_mb" id="middleware_max_request_size_mb" class="form-control" 
                                        value="<?php echo isset($middleware_config['request_size_limit']['max_size_mb']) ? $middleware_config['request_size_limit']['max_size_mb'] : 10; ?>" 
                                        min="1" max="100" step="1">
                                 <small class="help-block"><?php echo _l('max_request_size_mb_help'); ?></small>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  
                  <div class="row" style="margin-top: 20px;">
                     <div class="col-md-12">
                        <button type="submit" class="btn btn-info">
                           <i class="fa fa-save"></i> <?php echo _l('save_settings'); ?>
                        </button>
                     </div>
                  </div>
                  
                  <?php echo form_close(); ?>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

<?php init_tail(); ?>

<script src="<?php echo base_url('modules/api/assets/main.js'); ?>"></script>
