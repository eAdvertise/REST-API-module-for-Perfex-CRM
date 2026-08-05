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
                            <h4 class="no-margin"><?=_l('shopify')?> <?=_l('my_shopify_categories')?></h4>

                                    <div class="_buttons">
                                        <a class="btn btn-primary pull-left display-block new-proposal-btn"
                                            href="<?php echo admin_url('myshopify/import_categories'); ?>">
                                            <i
                                                class="fa-regular fa-plus tw-mr-1"></i><?php echo _l('my_shopify_import_categories'); ?>
                                        </a>
                                    </div>
                                </div>
                                <hr class="hr-panel-heading" />
                                <table class="table dt-table scroll-responsive">
                                    <thead>
                                        <tr>
                                            <th><?php echo _l('my_shopify_id'); ?></th>
                                            <th><?php echo _l('my_shopify_image'); ?></th>
                                            <th><?php echo _l('Category Name'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($categories)) {
                                            foreach ($categories as $category) { ?>
                                                <tr>
                                                    <td><?php echo $category['shopify_id']; ?></td>
                                                    <td>
                                                        <?php if (!empty($category['image_url'])) { ?>
                                                            <img src="<?php echo $category['image_url']; ?>" width="50" height="50">
                                                        <?php } else { ?>
                                                            <span><?=_l('my_shopify_no_image')?></span>
                                                        <?php } ?>
                                                    </td>
                                                    <td><?php echo $category['name']; ?></td>
                                                </tr>
                                            <?php }
                                        } ?>
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
