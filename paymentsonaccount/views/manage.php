<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
				<div class="tw-mb-3">
                    <h4 class="tw-my-0 tw-font-bold tw-text-xl">Receipts</h4>

                </div>
				<div class="_buttons tw-mb-2 tw-flex tw-items-center tw-gap-1">
                    <a href="<?php echo admin_url('paymentsonaccount/create_receipt'); ?>" class="btn btn-primary">
						Create New Receipt
					</a>
                </div>
				<div class="clearfix"></div>
				<div class="panel-table-full">
					<div class="panel_s">
						<div class="panel-body panel-table">
							<table class="table dt-table no-footer">
								<thead>
									<tr>
										<th>Receipt #</th>
										<th>Client</th>
										<th>Amount</th>
										<th>Date</th>
										<th><?php echo _l('actions'); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($receipts as $receipt) { ?>
										<tr>
											<td><a href="<?php echo admin_url('paymentsonaccount/view_receipt/' . $receipt['id']); ?>"><?php echo $receipt['receipt_number']; ?></a></td>
											<td><a href="<?php echo admin_url('clients/client/'.$receipt['client_id']);?>"><?php echo $receipt['company_name']; ?></a></td>
											<td><?php echo app_format_money($receipt['total_amount'], $receipt['currency']); ?></td>
											<td><?php echo _d($receipt['date_created']); ?></td>
											<td>
												<div class="btn-group" role="group">
													<a href="<?php echo admin_url('paymentsonaccount/view_receipt/' . $receipt['id']); ?>" class="btn btn-default btn-icon" data-toggle="tooltip" title="<?php echo _l('view'); ?>">
														<i class="fa-regular fa-eye"></i>
													</a>
													<a href="<?php echo admin_url('paymentsonaccount/receipt_pdf/' . $receipt['id']); ?>" class="btn btn-default btn-icon" data-toggle="tooltip" title="<?php echo _l('download'); ?> PDF">
														<i class="fa-regular fa-file-pdf"></i>
													</a>
													<a href="<?php echo admin_url('paymentsonaccount/send_receipt_email/' . $receipt['id']); ?>" class="btn btn-default btn-icon" data-toggle="tooltip" title="<?php echo _l('email'); ?>">
														<i class="fa-regular fa-envelope"></i>
													</a>
													<a href="<?php echo admin_url('paymentsonaccount/delete_receipt/' . $receipt['id']); ?>" class="btn btn-danger btn-icon _delete" data-toggle="tooltip" title="<?php echo _l('delete'); ?>">
														<i class="fa-regular fa-trash-can"></i>
													</a>
												</div>
											</td>
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
<?php init_tail(); ?>
<script>
(function($) {
    'use strict';
    $(function() {
        var table = $('.dt-table').DataTable();
        table.order([0, 'desc']).draw();
    });
})(jQuery);
</script>
