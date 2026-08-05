<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="panel-table-full">
                <div id="vueApp">
                    <div class="col-md-12 tw-mb-3">
                        <h4 class="tw-my-0 tw-font-bold tw-text-xl"><?= _l('delivery_notes'); ?></h4>
                        <a href="#" class="delivery-notes-total tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700" onclick="slideToggle('#stats-top');return false;">
                            <?= _l('view_stats_tooltip'); ?>
                        </a>
                    </div>
                    <div class="col-md-12">
                        <?php $this->load->view('admin/delivery_notes/delivery_notes_top_stats'); ?>
                    </div>
                    <?php $this->load->view('admin/delivery_notes/list_template'); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="modal-wrapper"></div>
<?php $this->load->view('admin/includes/modals/sales_attach_file'); ?>
<script>
    var hidden_columns = [];
</script>
<?php init_tail(); ?>
<script>
    $(function() {
        init_delivery_note();
    });
</script>
</body>

</html>
