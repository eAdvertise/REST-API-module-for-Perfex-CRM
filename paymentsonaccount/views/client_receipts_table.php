<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div class="panel_s">
	<div class="panel-body">
		<div class="clearfix"></div>
		<div>
			<div class="tab-content">
				<h4 class="customer-profile-group-heading">Receipts</h4>
				<div class="row">
					<div class="col-md-12">
						<table class="table table-striped table-bordered" id="client-receipts-table">
							<thead>
								<tr>
									<th>Receipt #</th>
									<th>Type</th>
									<th>Amount</th>
									<th>Date</th>
									<th>Email Status</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($receipts as $receipt) {

									$invoices = json_decode($receipt['invoices_applied'], true);
									$type = empty($invoices) ? 'On Account' : 'Invoice Payment';

									$email_sent = $this->payments_on_account_model->receipt_email_was_sent((int) $receipt['id']);
									$email_status = !$email_sent
										? '<span class="label label-danger">Not Sent</span>'
										: '<span class="label label-success">Sent</span>';

									?>
									<tr>
										<td><?php echo $receipt['receipt_number']; ?></td>
										<td><?php echo $type; ?></td>
										<td><?php echo app_format_money($receipt['total_amount'], $this->payments_on_account_model->get_client_currency_for_formatting((int) $receipt['client_id'])); ?></td>
										<td><?php echo _d($receipt['date_created']); ?></td>
										<td><?php echo $email_status; ?></td>
										<td>
											<a href="<?php echo admin_url('paymentsonaccount/view_receipt/' . $receipt['id']); ?>"
											   class="btn btn-sm btn-info">View</a>
											<a href="<?php echo admin_url('paymentsonaccount/edit_receipt/' . $receipt['id']); ?>"
											   class="btn btn-sm btn-warning">Edit</a>
											<button type="button"
													class="btn btn-sm btn-danger delete-receipt-btn"
													data-id="<?php echo $receipt['id']; ?>">
												Delete
											</button>
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
<?php init_tail(); ?>
<!-- Delete Receipt JS -->
<script>
    $(document).on('click', '.delete-receipt-btn', function () {
        var receiptId = $(this).data('id');

        if (confirm('Are you sure you want to delete this receipt?')) {
            window.location.href = admin_url + 'paymentsonaccount/delete_receipt/' + receiptId;
        }
    });
</script>

<!-- Datatable Initialization -->
<script>
    $(document).ready(function () {
        $('#client-receipts-table').DataTable({
            dom: 'Bfrtip',
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
            pageLength: 10,
            order: [[3, 'desc']],
            responsive: true
        });
    });
</script>
