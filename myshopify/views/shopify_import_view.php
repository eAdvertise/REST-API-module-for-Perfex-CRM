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
                                    <h4 class="no-margin"><?=_l('shopify')?> <?=_l('my_shopify_products')?></h4>

                                    <div class="_buttons">
                                        <a class="btn btn-primary pull-left display-block new-proposal-btn"
                                            href="<?php echo admin_url('myshopify/import'); ?>">
                                            <i
                                                class="fa-regular fa-plus tw-mr-1"></i><?php echo _l('my_shopify_import_products'); ?>
                                        </a>
                                    </div>
                                </div>
                                <hr class="hr-panel-heading" />
        <table class="table dt-table">
            <thead>
                <tr>
                    <th><?=_l('my_shopify_id')?></th>
                    <th><?=_l('my_shopify_image')?></th>
                    <th><?=_l('my_shopify_title')?></th>
                    <th><?=_l('my_shopify_price')?></th>
                    <th><?=_l('my_shopify_stock')?></th>
                    <th><?=_l('my_shopify_action')?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product) { ?>
                    <tr>
                        <td><?php echo $product['shopify_id']; ?></td>
                        <td>
                            <?php if (!empty($product['image_url'])) { ?>
                                <img src="<?php echo $product['image_url']; ?>" width="50" height="50">
                            <?php } else { ?>
                                <span><?=_l('my_shopify_no_image')?></span>
                            <?php } ?>
                        </td>
                        <td><?php echo $product['title']; ?></td>
                        <td><?php echo $product['price']; ?></td>
                        <td><?php echo $product['stock']; ?></td>
                        <td>
                            <a href="<?php echo $product['shopify_url']; ?>" target="_blank" class="btn btn-default">
                            <?=_l('my_shopify_view_on_shopify')?>
                            </a>
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
