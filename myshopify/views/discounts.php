<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="col-md-12 px-1">
            
            <div class="row">
                <div class="col-md-12" id="small-table">
                    <div class="panel_s">
                        <div class="panel-body">
                        <div class="tw-flex tw-justify-between tw-items-center">
                                    <h4 class="no-margin"><?php echo _l('shopify').' '._l('my_shopify_discounts'); ?></h4>

                                    <div class="_buttons">
                                        <a class="btn btn-primary pull-left display-block new-proposal-btn"
                                            href="<?php echo admin_url('myshopify/import_discounts'); ?>">
                                            <i
                                                class="fa-regular fa-plus tw-mr-1"></i><?php echo _l('my_shopify_import_discounts'); ?>
                                        </a>
                                    </div>
                                </div>
                                <hr class="hr-panel-heading" />
                            <div class="table-responsive">
                                <table class="table dt-table scroll-responsive">
                                    <thead>
                                        <tr>
                                            <th><?php echo _l('ID'); ?></th>
                                            <th><?php echo _l('my_shopify_code'); ?></th>
                                            <th><?php echo _l('my_shopify_title'); ?></th>
                                            <th><?php echo _l('my_shopify_discount_val'); ?></th>
                                            <th><?php echo _l('my_shopify_type'); ?></th>
                                            <th><?php echo _l('my_shopify_startdate'); ?></th>
                                            <th><?php echo _l('my_shopify_enddate'); ?></th>
                                            <th><?php echo _l('my_shopify_usage_limit'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($discounts)) {
                                            foreach ($discounts as $discount) { ?>
                                                <tr>
                                                    <td><?php echo $discount['id']; ?></td>
                                                    <td><strong><?php echo $discount['code']; ?></strong></td>
                                                    <td><?php echo $discount['title']; ?></td>
                                                    <td><?php echo $discount['value']; ?></td>
                                                    <td><?php echo ucfirst($discount['value_type']); ?></td>
                                                    <td><?php echo _dt($discount['starts_at']); ?></td>
                                                    <td><?php echo $discount['ends_at'] ? _dt($discount['ends_at']) : '—'; ?></td>
                                                    <td><?php echo $discount['usage_limit'] ?? '—'; ?></td>
                                                </tr>
                                        <?php } } ?>
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
