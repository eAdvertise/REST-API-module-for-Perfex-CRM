<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
		
			<div class="panel_s">
			  <div class="panel-body">
				<h4 class="no-margin"><?php echo _l('recurring_delivery_notes'); ?></h4>
			  </div>
			  <div class="table-responsive">
				<table class="table dt-table">
				  <thead>
					<tr>
					  <th>#</th>
					  <th><?php echo _l('client'); ?></th>
					  <th><?php echo _l('date'); ?></th>
					  <th><?php echo _l('every'); ?></th>
					  <th><?php echo _l('next_run'); ?></th>
					  <th><?php echo _l('cycles'); ?></th>
					  <th></th>
					</tr>
				  </thead>
				  <tbody>
				  <?php foreach($templates as $dn): ?>
					<tr>
					  <td><a href="<?php echo admin_url('delivery_notes/list_delivery_notes/'.$dn->id); ?>">
						<?php echo format_delivery_note_number($dn->id); ?></a></td>
					  <td><?php echo get_company_name($dn->clientid); ?></td>
					  <td><?php echo _d($dn->date); ?></td>
					  <td><?php echo (int)$dn->recurring.' '.$dn->recurring_type; ?></td>
					  <td><?php echo $dn->next_recurring_date ? _d($dn->next_recurring_date) : '-'; ?></td>
					  <td><?php echo (int)$dn->total_cycles.'/'.((int)$dn->cycles ?: '∞'); ?></td>
					  <td><a class="btn btn-default btn-sm" href="<?php echo admin_url('delivery_notes/list_delivery_notes/'.$dn->id); ?>">
						  <?php echo _l('edit'); ?></a></td>
					</tr>
				  <?php endforeach; ?>
				  </tbody>
				</table>
			  </div>
			</div>
		</div>
	</div> 
</div>
<?php init_tail(); ?>