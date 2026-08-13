<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (isset($client)) { ?>
<h4 class="customer-profile-group-heading"><?php echo _l('poa_receipts_tab') ?: 'Receipts'; ?></h4>

<a href="<?php echo admin_url('paymentsonaccount/create_receipt?client_id=' . (int)$client->userid); ?>"
   class="btn btn-primary mbot15<?php echo $client->active == 0 ? ' disabled' : ''; ?>">
   <i class="fa-regular fa-plus tw-mr-1"></i>
   <?php echo _l('poa_create_receipt') ?: 'Create Receipt'; ?>
</a>

<?php
  $CI = &get_instance();
  $CI->load->model('paymentsonaccount/payments_on_account_model');
  $CI->load->model('payment_modes_model'); // για τα ονόματα των modes
	
  

  // Safe accessor
  $get = function($row, $key, $default = null) {
      if (is_array($row))  return array_key_exists($key, $row) ? $row[$key] : $default;
      if (is_object($row)) return isset($row->$key) ? $row->$key : $default;
      return $default;
  };

  $receipts = method_exists($CI->payments_on_account_model,'get_by_client')
           ? $CI->payments_on_account_model->get_by_client($client->userid)
           : (method_exists($CI->payments_on_account_model,'get_client_receipts')
               ? $CI->payments_on_account_model->get_client_receipts($client->userid)
               : []);
?>

<div class="table-responsive">
  <table class="table dt-table" 
       data-order-col="1"
       data-order-type="desc">
    <thead>
      <tr>
        <th>#</th>
        <th><?php echo _l('date'); ?></th>
        <th><?php echo _l('amount'); ?></th>
        <th><?php echo _l('payment_mode'); ?></th>
        <th ><?php echo _l('note'); ?></th>
        <th><?php echo _l('invoices'); ?> (Applied)</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
    <?php
		
		foreach ($receipts as $r):
        $id            = $get($r, 'id', null);
        $number        = $get($r, 'receipt_number', $id);
        $dateRaw       = $get($r, 'payment_date', $get($r, 'date', $get($r, 'date_created', null)));
        $amount        = (float) $get($r, 'total_amount', $get($r, 'amount', 0));
		$paymentm  		= $get($r, 'payment_mode', $get($r, 'paymentmode', ''));
        $payment_mode 	= $CI->payment_modes_model->get($paymentm); // array of objects
        $note          = $get($r, 'note', '');
        $appliedRaw    = $get($r, 'invoices_applied', '');
        // Μετέτρεψε το payment_mode σε φιλικό όνομα
        $payment_mode_label = $payment_mode->name;
        

        // Decode applied invoices σε ενιαία μορφή [ids...]
        $appliedIds = [];
        if (!empty($appliedRaw)) {
          $decoded = json_decode($appliedRaw, true);
          if (is_array($decoded)) {
            foreach ($decoded as $item) {
              if (is_numeric($item)) {
                $appliedIds[] = (int)$item;
              } elseif (is_array($item)) {
                if (isset($item['invoice_id']) && is_numeric($item['invoice_id'])) {
                  $appliedIds[] = (int)$item['invoice_id'];
                } elseif (isset($item['id']) && is_numeric($item['id'])) {
                  $appliedIds[] = (int)$item['id'];
                }
              }
            }
          }
        }
        $appliedIds = array_values(array_unique(array_filter($appliedIds, function($v){ return $v>0; })));
		
    ?>
      <tr>
        <td><?php echo html_escape($number); ?></td>
        <td data-order="<?= $dateRaw ? strtotime($dateRaw) : 0; ?>"><?php echo $dateRaw ? _d($dateRaw) : ''; ?></td>
        <td><?php echo app_format_money($amount, get_base_currency()); ?></td>
        <td><?php echo html_escape($payment_mode_label); ?></td>
        <td><?php echo html_escape($note); ?></td>
        <td>
          <?php if (!empty($appliedIds)): ?>
            <?php
              // Φτιάξε links στα τιμολόγια
              // Admin URL για συγκεκριμένο invoice στα Perfex: admin_url('invoices/list_invoices/{id}')
              // Αν στο σύστημά σου είναι διαφορετικό, άλλαξέ το εδώ.
              $links = [];
              foreach ($appliedIds as $iid) {
                $links[] = '<a href="'.admin_url('invoices/list_invoices/'.$iid).'" target="_blank">'
                         .  format_invoice_number($iid)
                         .  '</a>';
              }
              echo implode(', ', $links);
            ?>
          <?php else: ?>
            <span class="text-muted">—</span>
          <?php endif; ?>
        </td>
        <td>
          <?php if ($id): ?>
          <a href="<?php echo admin_url('paymentsonaccount/view_receipt/'.$id); ?>" class="btn btn-default btn-sm">
            <?php echo _l('poa_view') ?: 'View'; ?>
          </a>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php } // end isset($client) ?>
