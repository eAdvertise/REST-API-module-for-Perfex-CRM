<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-8">
        <div class="panel_s">
          <div class="panel-body">
            <h4><?=_l('my_shopify_order')?> #<?php echo $order['order_number']; ?></h4>
            <hr>

            <div class="row">
              <div class="col-md-6">
              <h5><strong><?=_l('my_shopify_shipping_information')?></strong></h5>
              <p><strong><?=_l('my_shopify_shipping_name')?>:</strong> <?= $order['shipping_name']; ?></p>
                <p><strong><?=_l('my_shopify_shipping_company')?>:</strong> <?= $order['shipping_company'] ?: '-'; ?></p>
                <p><strong><?=_l('my_shopify_shipping_city')?>:</strong> <?= $order['shipping_city']; ?></p>
                <p><strong><?=_l('my_shopify_shipping_state')?>:</strong> <?= $order['shipping_province']; ?></p>
                <p><strong><?=_l('my_shopify_shipping_country')?>:</strong> <?= $order['shipping_country']; ?></p>
                <p><strong><?=_l('my_shopify_shipping_postal_code')?>:</strong> <?= $order['shipping_zip']; ?></p>
                <p><strong><?=_l('my_shopify_shipping_phone')?>:</strong> <?= $order['shipping_phone']; ?></p>
              </div>

              <div class="col-md-6">
                <h5><strong><?=_l('my_shopify_billing_information')?></strong></h5>
                <p><strong><?=_l('my_shopify_billing_name')?>:</strong> <?= $order['billing_name']; ?></p>
                <p><strong><?=_l('my_shopify_billing_company')?>:</strong> <?= $order['billing_company'] ?: '-'; ?></p>
                <p><strong><?=_l('my_shopify_billing_city')?>:</strong> <?= $order['billing_city']; ?></p>
                <p><strong><?=_l('my_shopify_billing_state')?>:</strong> <?= $order['billing_province']; ?></p>
                <p><strong><?=_l('my_shopify_billing_country')?>:</strong> <?= $order['billing_country']; ?></p>
                <p><strong><?=_l('my_shopify_billing_postal_code')?>:</strong> <?= $order['billing_zip']; ?></p>
                <p><strong><?=_l('my_shopify_billing_phone')?>:</strong> <?= $order['billing_phone']; ?></p>
              </div>
            </div>

            <hr>

            <h5><strong><?=_l('my_shopify_items')?></strong></h5>
            <div class="table-responsive">
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th><?=_l('my_shopify_item')?></th>
                    <th><?=_l('my_shopify_quantity')?></th>
                    <th><?=_l('my_shopify_price')?></th>
                    <th><?=_l('my_shopify_total')?></th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $line_items = json_decode($order['line_items'], true);
                  foreach ($line_items as $item): ?>
                    <tr>
                      <td><?= $item['name']; ?></td>
                      <td><?= $item['quantity']; ?></td>
                      <td><?= app_format_money($item['price'], $order['currency']); ?></td>
                      <td><?= app_format_money($item['price'] * $item['quantity'], $order['currency']); ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="panel_s">
          <div class="panel-body">
            <h5><?=_l('my_shopify_order_summary')?>#<?php echo $order['order_number']; ?></h5>
            <table class="table">
              <tr>
                <th>Description</th>
                <th class="text-right"><?=_l('my_shopify_price')?></th>
              </tr>
              <tr>
                <td><?=_l('my_shopify_grand_total')?>:</td>
                <td class="text-right"><?= app_format_money($order['subtotal_price'], $order['currency']); ?></td>
              </tr>
              <tr>
                <td><?=_l('my_shopify_discount')?>:</td>
                <td class="text-right"><?= app_format_money($order['total_discounts'], $order['currency']); ?></td>
              </tr>
              <tr>
                <td><?=_l('my_shopify_tax')?>:</td>
                <td class="text-right"><?= app_format_money($order['total_tax'], $order['currency']); ?></td>
              </tr>
              <tr>
                <th><?=_l('my_shopify_total')?>:</th>
                <th class="text-right"><?= app_format_money($order['total_price'], $order['currency']); ?></th>
              </tr>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
<?php init_tail(); ?>
