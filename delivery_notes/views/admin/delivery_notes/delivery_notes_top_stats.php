<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div id="stats-top">

    <div class="_filters _hidden_inputs">
        <?php
        if (isset($delivery_notes_sale_agents)) {
            foreach ($delivery_notes_sale_agents as $agent) {
                echo form_hidden('sale_agent_' . $agent['sale_agent']);
            }
        }
        if (isset($delivery_note_statuses)) {
            foreach ($delivery_note_statuses as $_status) {
                $val = '';
                if ($_status == $this->input->get('status')) {
                    $val = $_status;
                }
                echo form_hidden('delivery_notes_' . $_status, $val);
            }
        }
        if (isset($delivery_notes_years)) {
            foreach ($delivery_notes_years as $year) {
                echo form_hidden('year_' . $year['year'], $year['year']);
            }
        }
        echo form_hidden('not_sent', $this->input->get('filter'));
        echo form_hidden('project_id');
        echo form_hidden('invoiced');
        echo form_hidden('not_invoiced');
        ?>
    </div>

    <div class="quick-top-stats tw-mb-6">
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 lg:tw-grid-cols-5 tw-mb-0 tw-gap-x-2">
            <?php foreach ($delivery_note_statuses as $status) {
                $percent_data = get_delivery_notes_percent_by_status(
                    $status,
                    (isset($project) ? $project->id : null)
                ); ?>
            <button type="button" data-cview="delivery_notes_<?php echo $status; ?>"
                onclick="dt_custom_view('delivery_notes_<?php echo $status; ?>','.table-delivery_notes','delivery_notes_<?php echo $status; ?>',true); return false;"
                class="tw-bg-white tw-border tw-border-solid tw-border-neutral-300/80 tw-shadow-sm tw-py-2 tw-px-3.5 tw-rounded-lg tw-text-sm hover:tw-bg-neutral-100 tw-text-neutral-600 hover:tw-text-neutral-600 focus:tw-text-neutral-600 text-left">
                <div class="tw-flex tw-items-center">
                    <span
                        class="tw-font-medium tw-text-base tw-inline-flex tw-items-center text-<?= delivery_note_status_color_class($status); ?>">
                        <?= format_delivery_note_status($status, '', false); ?>
                    </span>
                    <span class="tw-ml-2 rtl:tw-mr-2 tw-text-xs tw-text-neutral-500 tw-mt-px">
                        (<?= e($percent_data['percent']); ?>%)
                    </span>
                </div>
                <div class="tw-mt-0.5">
                    <div class="tw-text-neutral-600">
                        <span class="tw-font-semibold">
                            <?= e($percent_data['total_by_status']); ?>
                            /
                            <?= e($percent_data['total']); ?>
                        </span>
                    </div>
                </div>
            </button>
            <?php } ?>
        </div>
    </div>
</div>