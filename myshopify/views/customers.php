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
                                    <h4 class="no-margin"><?=_l('shopify')?> <?=_l('my_shopify_customers')?></h4>

                                    <div class="_buttons">
                                        <a class="btn btn-primary pull-left display-block new-proposal-btn"
                                            href="<?php echo admin_url('myshopify/import_customers'); ?>">
                                            <i
                                                class="fa-regular fa-plus tw-mr-1"></i><?php echo _l('my_shopify_import_customers'); ?>
                                        </a>
                                    </div>
                                </div>
                                <hr class="hr-panel-heading" />

                                <table class="table dt-table">
                                    <thead>
                                        <tr>
                                            <th><?=_l('my_shopify_id')?></th>
                                            <th><?=_l('my_shopify_fullname')?></th>
                                            <th><?=_l('my_shopify_email')?></th>
                                            <th><?=_l('my_shopify_phone')?></th>
                                            <th><?=_l('my_shopify_created')?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($customers as $customer) { ?>
                                        <tr>
                                            <td><?php echo $customer['shopify_customer_id']; ?></td>
                                            <td><?php echo $customer['first_name'] . ' ' . $customer['last_name']; ?>
                                            </td>
                                            <td><?php echo $customer['email']; ?></td>
                                            <td><?php echo $customer['phone']; ?></td>
                                            <td><?php echo _dt($customer['created_at']); ?></td>
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
</div>
<?php init_tail(); ?>