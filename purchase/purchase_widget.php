<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$CI = &get_instance();
$summary = [
    'pur_request' => ['label' => _l('pur_request'), 'url' => admin_url('purchase/purchase_request')],
    'pur_orders' => ['label' => _l('pur_order'), 'url' => admin_url('purchase/purchase_order')],
    'pur_invoices' => ['label' => _l('purchase_invoice'), 'url' => admin_url('purchase/invoices')],
];

foreach ($summary as $table => &$item) {
    $item['count'] = $CI->db->table_exists(db_prefix() . $table)
        ? (int) $CI->db->count_all(db_prefix() . $table)
        : 0;
}
unset($item);
?>
<div class="widget relative" id="purchase-dashboard-widget">
    <div class="panel_s">
        <div class="panel-body padding-10">
            <div class="widget-dragger"></div>
            <h4 class="no-margin mtop5"><?php echo _l('purchase'); ?></h4>
            <hr class="hr-panel-heading" />
            <div class="row">
                <?php foreach ($summary as $item) { ?>
                    <div class="col-md-4 col-sm-4 col-xs-12 text-center">
                        <a href="<?php echo $item['url']; ?>">
                            <h3 class="bold no-margin"><?php echo (int) $item['count']; ?></h3>
                            <span class="text-muted"><?php echo $item['label']; ?></span>
                        </a>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
