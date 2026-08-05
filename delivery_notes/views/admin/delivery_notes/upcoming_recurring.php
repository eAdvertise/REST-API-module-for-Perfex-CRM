<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="panel_s">
            <div class="panel-body">
                <h4 class="no-margin"><?php echo _l('upcoming_recurring_delivery_notes'); ?></h4>
                <hr class="hr-panel-heading" />
                <div class="table-responsive">
                    <table class="table dt-table">
                        <thead>
                            <tr>
                                <th><?php echo _l('delivery_note_number'); ?></th>
                                <th><?php echo _l('client'); ?></th>
                                <th><?php echo _l('original_date'); ?></th>
                                <th><?php echo _l('next_delivery_date'); ?></th>
                                <th><?php echo _l('recurring_type'); ?></th>
                                <th><?php echo _l('cycles_done'); ?></th>
                                <th><?php echo _l('cycles_total'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($delivery_notes as $note) { ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo admin_url('delivery_notes/list_delivery_notes/' . $note->id); ?>">
                                            <?php echo format_delivery_note_number($note->id); ?>
                                        </a>
                                    </td>
                                    <td><?php echo get_company_name($note->clientid); ?></td>
                                    <td><?php echo _d($note->date); ?></td>
                                    <td><span class="label label-success"><?php echo _d($note->next_date); ?></span></td>
                                    <td><?php echo $note->recurring . ' ' . $note->recurring_type; ?></td>
                                    <td><?php echo $note->total_cycles; ?></td>
                                    <td><?php echo $note->cycles == 0 ? '∞' : $note->cycles; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <hr />
                <a href="<?php echo admin_url('delivery_notes'); ?>" class="btn btn-default">
                    <?php echo _l('back_to_delivery_notes'); ?>
                </a>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
