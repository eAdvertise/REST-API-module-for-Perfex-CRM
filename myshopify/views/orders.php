<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="col-md-12 px-1">
            

            <div class="row">
                <div class="col-md-12" id="small-table">
                    <div class="panel_s">
                        <div class="panel-body">
                            <div class="panel-table-full">
                            <div class="tw-flex tw-justify-between tw-items-center">
                                    <h4 class="no-margin"><?php echo _l('shopify').' '._l('my_shopify_orders'); ?></h4>

                                    <div class="_buttons">
                                        <a class="btn btn-primary pull-left display-block new-proposal-btn"
                                            href="<?php echo admin_url('myshopify/import_orders'); ?>">
                                            <i
                                                class="fa-regular fa-plus tw-mr-1"></i><?php echo _l('my_shopify_import_orders'); ?>
                                        </a>
                                    </div>
                                </div>
                                <hr class="hr-panel-heading" />

                                <table class="table dt-table scroll-responsive">
                                    <thead>
                                        <tr>
                                            <th><?php echo _l('my_shopify_id'); ?></th>
                                            <th><?php echo _l('my_shopify_order_number'); ?></th>
                                            <th><?php echo _l('my_shopify_customer_name'); ?></th>
                                            <th><?php echo _l('my_shopify_customer_email'); ?></th>
                                            <th><?php echo _l('my_shopify_total_price'); ?></th>
                                            <th><?php echo _l('my_shopify_status'); ?></th>
                                            <th><?php echo _l('my_shopify_created'); ?></th>
                                            <th><?php echo _l('my_shopify_action'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($orders)) {
                                            foreach ($orders as $order) { ?>
                                                <tr>
                                                    <td><?php echo $order['shopify_order_id']; ?></td>
                                                    <td>#<?php echo $order['order_number']; ?></td>
                                                    <td><?php echo $order['customer_name'] ?: 'Guest'; ?></td>
                                                    <td><?php echo $order['customer_email']; ?></td>
                                                    <td><?php echo app_format_money($order['total_price'], $order['currency']); ?></td>
                                                    <td><?php echo ucfirst($order['financial_status']); ?></td>
                                                    <td><?php echo _dt($order['created_at']); ?></td>
                                                    <td>
                                                        <!-- If you plan to show more details in a modal or another page -->
                                                        <a href="<?php echo admin_url('myshopify/order_details/' . $order['id']); ?>" class="btn btn-default">
                                                            <?php echo _l('View'); ?>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php }
                                        }  ?>
                                    </tbody>
                                </table>
                                
                            </div> 
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<?php init_tail(); ?>
