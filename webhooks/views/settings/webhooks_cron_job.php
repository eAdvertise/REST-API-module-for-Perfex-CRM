<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php

$result = $this->db->query("SELECT COUNT(status) as status_count,status FROM `tblscheduled_webhooks` group by status;")->result();
if (!empty($result)) {
	$status_counts = array_column($result, "status_count", 'status');
	$total_count = array_sum($status_counts);
}
?>

<div class="horizontal-scrollable-tabs panel-full-width-tabs">
	<div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
	<div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
	<div class="horizontal-tabs">
		<ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
			<li role="presentation" class="active">
				<a href="#webhooks_cron_job_settings" aria-controls="webhooks_cron_job_settings" role="tab" data-toggle="tab">
					<?php echo _l('webhooks_cron_job_settings'); ?>
				</a>
			</li>
			<li role="presentation">
				<a href="#webhooks_cron_job_queue" aria-controls="webhooks_cron_job_queue" role="tab" data-toggle="tab">
					<?php echo _l('webhooks_cron_job_queue'); ?>
				</a>
			</li>
		</ul>
	</div>
</div>

<div class="tab-content mtop15">
	<div role="tabpanel" class="tab-pane active" id="webhooks_cron_job_settings">
		<div class="alert alert-info tw-mb-0">
			<span class="bold text-info">WEBHOOKS CRON COMMAND: wget -q -O-
				<?php echo site_url('webhooks/cron/index'); ?>
			</span><br />
			<?php if (is_admin()) { ?>
				<a href="<?= site_url('webhooks/cron/manually') ?>">Run Cron Manually</a>
			<?php } ?>
		</div>
	</div>

	<div role="tabpanel" class="tab-pane" id="webhooks_cron_job_queue">
		<div class="alert alert-danger">
			This feature requires a properly configured separate webhooks cron job. Before activating the feature, make sure that
			the <a href="<?php echo admin_url('settings?group=webhooks'); ?>">cron job</a> is configured as explanation
			in the documentation.
		</div>
		<hr />
		<div class="row">
			<div class="col-md-3 col-xs-6 md:tw-border-r md:tw-border-solid md:tw-border-neutral-300 last:tw-border-r-0">
				<div class="tw-flex tw-items-center">
					<span class="tw-font-semibold tw-mr-3 rtl:tw-ml-3 tw-text-lg"> <?= $total_count ?? 0 ?> </span>
					<span class="text-info"> <?= _l("total") ?> </span>
				</div>
			</div>
			<div class="col-md-3 col-xs-6 md:tw-border-r md:tw-border-solid md:tw-border-neutral-300 last:tw-border-r-0">
				<div class="tw-flex tw-items-center">
					<span class="tw-font-semibold tw-mr-3 rtl:tw-ml-3 tw-text-lg"> <?= $status_counts['PENDING'] ?? 0 ?> </span>
					<span class="text-warning"> <?= _l("pending") ?> </span>
				</div>
			</div>
			<div class="col-md-3 col-xs-6 md:tw-border-r md:tw-border-solid md:tw-border-neutral-300 last:tw-border-r-0">
				<div class="tw-flex tw-items-center">
					<span class="tw-font-semibold tw-mr-3 rtl:tw-ml-3 tw-text-lg"> <?= $status_counts['SUCCESS'] ?? 0 ?> </span>
					<span class="text-success"> <?= _l("success") ?> </span>
				</div>
			</div>
			<div class="col-md-3 col-xs-6 md:tw-border-r md:tw-border-solid md:tw-border-neutral-300 last:tw-border-r-0">
				<div class="tw-flex tw-items-center">
					<span class="tw-font-semibold tw-mr-3 rtl:tw-ml-3 tw-text-lg"> <?= $status_counts['FAILED'] ?? 0 ?> </span>
					<span class="text-danger"> <?= _l("failed") ?> </span>
				</div>
			</div>
		</div>

		<hr />
		<?php render_datatable([
			_l('status'),
			_l('name'),
			_l('scheduled_at'),
			_l('executed_at'),
			_l('type'),
			_l('action')
		], 'webhooks_cron_job_queue_table') ?>
	</div>
</div>