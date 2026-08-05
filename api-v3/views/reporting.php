<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>

<link href="<?php echo base_url('modules/api/assets/main.css'); ?>" rel="stylesheet" type="text/css" />

<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body padding-10">
                  <div class="tw-flex tw-justify-between tw-items-center tw-p-1.5">
                     <p class="tw-font-semibold tw-flex tw-items-center tw-mb-0 tw-space-x-1.5 rtl:tw-space-x-reverse">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="tw-w-6 tw-h-6 tw-text-neutral-500">
                           <path stroke-linecap="round" stroke-linejoin="round" d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375a9 9 0 019 9v.375M10.125 2.25A3.375 3.375 0 0113.5 5.625v1.5c0 .621.504 1.125 1.125 1.125h1.5a3.375 3.375 0 013.375 3.375M9 15l2.25 2.25L15 12" />
                        </svg>

                        <span class="tw-text-neutral-700">
                           <?php echo _l('all_apis'); ?>
                        </span>
                     </p>
                     <div class="tw-divide-x tw-divide-solid tw-divide-neutral-300 tw-space-x-2 tw-flex tw-items-center">
                        <div class="dropdown pull-right mright10">
                           <a href="#" id="ApisChartMode" class="dropdown-toggle tw-pl-2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <span id="Api-chart-mode" data-active-chart="hourly">
                                 <?php echo _l('hourly') ?>
                              </span>
                              <i class="fa fa-caret-down" aria-hidden="true"></i>
                           </a>
                           <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="ApisChartMode">
                              <li>
                                 <a href="#" data-mode="hourly" onclick="update_apis_statistics(this); return false;">
                                    <?php echo _l('hourly') ?>
                                 </a>
                              </li>
                              <li>
                                 <a href="#" data-mode="daily" onclick="update_apis_statistics(this); return false;">
                                    <?php echo _l('daily') ?>
                                 </a>
                              </li>
                              <li>
                                 <a href="#" data-mode="weekly" onclick="update_apis_statistics(this); return false;">
                                    <?php echo _l('weekly') ?>
                                 </a>
                              </li>
                              <li>
                                 <a href="#" data-mode="monthly" onclick="update_apis_statistics(this); return false;">
                                    <?php echo _l('monthly') ?>
                                 </a>
                              </li>
                           </ul>
                        </div>
                     </div>
                  </div>

                  <hr class="-tw-mx-3 tw-mt-2 tw-mb-4">

                  <canvas height="130" class="all-apis-chart" id="all-apis-chart"></canvas>
               </div>
            </div>
         </div>
      </div>
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body padding-10">
                  <div class="tw-flex tw-justify-between tw-items-center tw-p-1.5">
                     <p class="tw-font-semibold tw-flex tw-items-center tw-mb-0 tw-space-x-1.5 rtl:tw-space-x-reverse">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="tw-w-6 tw-h-6 tw-text-neutral-500">
                           <path stroke-linecap="round" stroke-linejoin="round" d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375a9 9 0 019 9v.375M10.125 2.25A3.375 3.375 0 0113.5 5.625v1.5c0 .621.504 1.125 1.125 1.125h1.5a3.375 3.375 0 013.375 3.375M9 15l2.25 2.25L15 12" />
                        </svg>

                        <span class="tw-text-neutral-700">
                           <?php echo _l('endpoints'); ?>
                        </span>
                     </p>
                     <div class="tw-divide-x tw-divide-solid tw-divide-neutral-300 tw-space-x-2 tw-flex tw-items-center">
                        <div class="dropdown pull-right mright10">
                           <a href="#" id="EndpointsChartUri" class="dropdown-toggle tw-pl-2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <span id="Endpoint-chart-uri" data-active-chart="<?php if (count($endpoints)) { echo $endpoints[0]; } ?>">
                                 <?php if (count($endpoints)) { echo $endpoints[0]; } ?>
                              </span>
                              <i class="fa fa-caret-down" aria-hidden="true"></i>
                           </a>
                           <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="EndpointsChartUri">
                              <?php foreach ($endpoints as $endpoint) { ?>
                                 <li>
                                    <a href="#" data-uri="<?php echo $endpoint ?>" onclick="update_endpoints_statistics(this); return false;">
                                       <?php echo $endpoint ?>
                                    </a>
                                 </li>
                              <?php } ?>
                           </ul>
                        </div>
                        <div class="dropdown pull-right mright10">
                           <a href="#" id="EndpointsChartMode" class="dropdown-toggle tw-pl-2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <span id="Endpoint-chart-mode" data-active-chart="hourly">
                                 <?php echo _l('hourly') ?>
                              </span>
                              <i class="fa fa-caret-down" aria-hidden="true"></i>
                           </a>
                           <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="EndpointsChartMode">
                              <li>
                                 <a href="#" data-mode="hourly" onclick="update_endpoints_statistics(null, this); return false;">
                                    <?php echo _l('hourly') ?>
                                 </a>
                              </li>
                              <li>
                                 <a href="#" data-mode="daily" onclick="update_endpoints_statistics(null, this); return false;">
                                    <?php echo _l('daily') ?>
                                 </a>
                              </li>
                              <li>
                                 <a href="#" data-mode="weekly" onclick="update_endpoints_statistics(null, this); return false;">
                                    <?php echo _l('weekly') ?>
                                 </a>
                              </li>
                              <li>
                                 <a href="#" data-mode="monthly" onclick="update_endpoints_statistics(null, this); return false;">
                                    <?php echo _l('monthly') ?>
                                 </a>
                              </li>
                           </ul>
                        </div>
                     </div>
                  </div>

                  <hr class="-tw-mx-3 tw-mt-2 tw-mb-4">

                  <canvas height="130" class="all-endpoints-chart" id="all-endpoints-chart"></canvas>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

<?php init_tail(); ?>

<script src="<?php echo base_url('modules/api/assets/main.js'); ?>"></script>

<script>
   var apis_statistics;
   var endpoints_statistics;

   function update_apis_statistics(el) {
      let mode = $(el).data('mode');
      let $chartNameWrapper = $('#Api-chart-mode');
      $chartNameWrapper.data('active-chart', mode);
      $chartNameWrapper.text($(el).text());

      if (typeof(apis_statistics) !== 'undefined') {
         apis_statistics.destroy();
      }

      $.get(admin_url + 'api/statistics/' + mode, function(response) {
         apis_statistics = new Chart($('#all-apis-chart'), {
            type: 'bar',
            data: response,
            options: {
               responsive: true,
               scales: {
                     yAxes: [{
                        ticks: {
                           beginAtZero: true,
                        }
                     }]
               },
            },
         });
      }, 'json');
   }

   function update_endpoints_statistics(uri_el, mode_el = null) {
      console.log("Update Endpoints");
      let uri, mode;
      if (uri_el) {
         uri = $(uri_el).data('uri');

         let $chartUriWrapper = $('#Endpoint-chart-uri');
         $chartUriWrapper.data('active-chart', uri);
         $chartUriWrapper.text($(uri_el).text());
      } else {
         uri = $('#Endpoint-chart-uri').data("active-chart");
      }

      if (mode_el) {
         mode = $(mode_el).data('mode');

         let $chartModeWrapper = $('#Endpoint-chart-mode');
         $chartModeWrapper.data('active-chart', mode);
         $chartModeWrapper.text($(mode_el).text());
      } else {
         mode = $('#Endpoint-chart-mode').data("active-chart");
      }

      if (typeof(endpoints_statistics) !== 'undefined') {
         endpoints_statistics.destroy();
      }

      $.get(admin_url + 'api/statistics/' + mode + "/" + encodeURIComponent(uri), function(response) {
         endpoints_statistics = new Chart($('#all-endpoints-chart'), {
            type: 'bar',
            data: response,
            options: {
               responsive: true,
               scales: {
                     yAxes: [{
                        ticks: {
                           beginAtZero: true,
                        }
                     }]
               },
            },
         });
      }, 'json');
   }

   $(document).ready(function() {
      update_apis_statistics($('[aria-labelledby="ApisChartMode"] [data-mode="hourly"]'));
      update_endpoints_statistics($('[aria-labelledby="EndpointsChartUri"] li:first-child a'));
   });
</script>