<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>

<div id="wrapper">
    <div class="content">

        <div class="row">

            <div class="col-md-12">

                <div class="panel_s">

                    <div class="panel-body">

                            <div class="panel-table">
                                <table class="table dt-table table-hover table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th><?php echo _l('balance_heading_account')?></th>
                                            <th><?php echo _l('task_public')?></th>
                                            <th><?php echo _l('balance_heading_open_balance')?></th>
                                            <th><?php echo _l('balance_heading_currency')?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ( !empty( $payment_modes ) ) : $pay_ind = 1 ;?>

                                            <?php foreach ( $payment_modes as $payment_mode ) { $payment_id = $payment_mode['id']; ?>

                                                <tr>
                                                    <td>

                                                        <?php echo $pay_ind++?>

                                                        <div class="row-options">

                                                            <a class="text-info" style="cursor: pointer;" onclick="open_account_balances_modal( '<?php echo $payment_id?>' ); return false;"> <?php echo _l('edit'); ?> </a>

                                                        </div>

                                                    </td>
                                                    <td class="payment_name_<?php echo $payment_id?>"><?php echo $payment_mode['name']?></td>
                                                    <td><?php echo $payment_mode['is_public'] ? _l('task_public') : '' ?></td>
                                                    <td><?php echo app_format_money( $payment_mode['opening_balance'] , $payment_mode['payment_currency'] );?></td>
                                                    <td><?php echo $payment_mode['payment_currency'];?></td>
                                                </tr>

                                            <?php } ?>

                                        <?php endif; ?>
                                    </tbody>
                                </table>

                            </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<div class="modal fade" id="account_balances_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">

    <div class="modal-dialog modal-lg" role="document">

        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">
                    <span class="edit-title"><?php echo _l('balance_heading_account'); ?></span>
                </h4>
            </div>

            <?php echo form_open('account_balances/casebank/account_balances_save', ['id' => 'account_balances_form']); ?>

            <input type="hidden" name="account_id" id="account_id" />

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">

                        <?php echo render_select('payment_currency', $currencies , [ 'name' , 'name' ] , _l("balance_heading_currency") ); ?>

                        <?php echo render_input('opening_balance', _l("balance_heading_open_balance") , '' , 'number'); ?>

                        <strong>
                            Members whose payment mode is active.
                            <br >
                            <span class="text-info">This restriction is valid only within the module. On other pages, your staff can see all payment methods.</span>
                        </strong>

                        <div class="form-group">

                            <select class="selectpicker" data-toggle="<?php echo _l('staff')?>"

                                    name="staff[]" id="staff" data-actions-box="true" multiple="true" data-width="100%"

                                    data-title="<?php echo _l('staff')?>">

                                <?php foreach ($staff as $stf ) {

                                    $selected = '';

                                    ?>

                                    <option value="<?php echo $stf['staffid']; ?>" >

                                        <?php echo $stf['firstname'].' '.$stf['lastname']; ?></option>

                                    <?php

                                } ?>

                            </select>
                        </div>

                        <div class="checkbox checkbox-primary checkbox-inline">

                            <input type="checkbox" id="is_public" name="is_public" >

                            <label for="is_public" ><?php echo _l('task_public'); ?></label>

                        </div>

                    </div>
                </div>
            </div>

            <div class="modal-footer">

                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>

            </div>

            <?php echo form_close(); ?>

        </div>

    </div>

</div>

<?php init_tail(); ?>

<script>

    (function($) {
        "use strict";


        appValidateForm($('#account_balances_form'), {
            opening_balance: 'required' ,
        }, manage_account_balances_modes);


    })(jQuery);


    function manage_account_balances_modes(form)
    {

        $.post( form.action , $(form).serialize() ).done(function(response) {

            response = JSON.parse(response);

            if (response.success == true)
            {

                alert_float('success', response.message);

                $('#account_balances_modal').modal('hide');


                window.location.reload();

            }
            else
                alert_float('danger', response.message);


        });

        return false;

    }

    function open_account_balances_modal( record_id )
    {
        $('#account_balances_modal').modal('hide');

        $('#payment_currency').selectpicker('val','');

        $('#payment_currency').selectpicker("refresh");

        $('#opening_balance').val(0);

        $('#account_id').val( record_id );

        $('#is_public').attr('checked' , true );

        $('#staff').selectpicker('val', [] );

        $('#staff').selectpicker('refresh');

        $('#account_balances_modal').find('.edit-title').text( $('.payment_name_'+record_id).text() );

        $.post( admin_url+'account_balances/casebank/balance_detail/'+record_id ).done(function(response) {

            response = JSON.parse(response);

            if ( response.success == true )
            {

                if ( response.data.is_public == '0' )
                    $('#is_public').attr('checked' , false );

                $('#payment_currency').selectpicker('val', response.data.payment_currency );

                $('#payment_currency').selectpicker("refresh");

                $('#opening_balance').val( response.data.opening_balance );

                console.log(response.data.staff);

                $('#staff').selectpicker('val', response.data.staff );

                $('#staff').selectpicker('refresh');

            }

        });

        $('#account_balances_modal').modal();

    }

</script>


</body>

</html>
