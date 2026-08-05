<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">

                <?php if ( staff_can( 'case_manage_transfer' , 'case_manage'  ) ) : ?>
                    <div class="tw-mb-2 sm:tw-mb-4">
                        <a href="#" class="btn btn-primary" onclick="open_payment_transfer_modal( 0 )">
                            <i class="fa-regular fa-plus tw-mr-1"></i>
                            <?php echo _l("new_transfer_record"); ?>
                        </a>
                    </div>
                <?php endif; ?>

                <div class="panel_s">
                    <div class="panel-body">

                        <div class="col-md-4">
                            <input type="hidden" name="from_date" id="from_date" value="">
                            <input type="hidden" name="to_date" id="to_date" value="">
                            <?php $this->load->view('_statement_period_select', ['onChange' => 'reload_the_table()']); ?>
                        </div>

                        <div class="col-md-4">
                            <?php echo render_select( 'from_payment_mode' , $payment_modes , [ 'id' , [ 'name'] ] ,  '' , '' , ['onchange' => 'transfer_table_load()' , 'data-none-selected-text' => _l('transfer_heading_welding_case') ] ); ?>
                        </div>

                        <div class="col-md-4">
                            <?php echo render_select( 'to_payment_mode' , $payment_modes , [ 'id' , [ 'name' ] ] , '' , '' , ['onchange' => 'transfer_table_load()' , 'data-none-selected-text' => _l('transfer_heading_target_case') ] )?>
                        </div>


                        <div class="col-md-12">
                            <div class="panel-table-full">
                                <?php render_datatable([
                                    _l('transfer_heading_id'),
                                    _l("transfer_heading_welding_case"),
                                    _l("transfer_heading_target_case"),
                                    _l("transfer_heading_source_amount"),
                                    _l("transfer_heading_target_amount"),
                                    _l("transfer_heading_description"),
                                    _l("transfer_heading_date"),
                                    _l("transfer_heading_process_date"),
                                    _l("transfer_heading_process_staff"),
                                ], 'payment-modes-transfer'); ?>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="payment_transfer_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">
                    <span class="edit-title"><?php echo _l('transfer_edit_heading'); ?></span>
                    <span class="add-title"><?php echo _l('transfer_add_heading'); ?></span>
                </h4>
            </div>

            <?php echo form_open('account_balances/casebank/transfer_save', ['id' => 'payment_transfer_form']); ?>

            <input type="hidden" name="transfer_id" id="transfer_id" />

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">

                        <?php echo render_date_input('transfer_date' ,_l("transfer_heading_date")   ); ?>

                        <?php echo render_select('source_mode', $payment_modes , [ 'id' , [ 'name'] ] , _l("transfer_heading_welding_case") , null , [] ); ?>

                        <?php echo render_select('target_mode', $payment_modes , [ 'id' , [ 'name'] ] , _l("transfer_heading_target_case") ); ?>

                        <?php echo render_input('source_amount', _l("transfer_heading_source_amount") , '' , 'number'); ?>

                        <?php echo render_input('target_amount', _l("transfer_heading_target_amount") , '' , 'number'); ?>

                        <?php echo render_textarea('description', _l("transfer_heading_description"), '', [ 'rows' => 5]); ?>


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

        $(document).ready(function (){

            appValidateForm($('#payment_transfer_form'), {
                source_mode: 'required' ,
                target_mode: 'required' ,
                source_amount: 'required',
                target_amount: 'required',
                transfer_date: 'required',
            }, manage_payment_modes);


            transfer_table_load();


            $("#target_mode,#source_mode").change(function () {

                var source_currency = $("#source_mode option:selected").attr("payment_currency");
                var target_currency = $("#target_mode option:selected").attr("payment_currency");

                if (source_currency == target_currency) {

                    $("#target_amount").val($("#source_amount").val());

                }

            });


            <?php if ( !empty( $this->input->get('transfer_id') ) ) { ?>

                open_payment_transfer_modal( <?php echo $this->input->get('transfer_id')?> );

            <?php } ?>


        });

    })(jQuery);

    function manage_payment_modes(form)
    {

        $.post( form.action , $(form).serialize() ).done(function(response) {

            response = JSON.parse(response);

            if (response.success == true)
            {

                transfer_table_load();

                alert_float('success', response.message);

                $('#payment_transfer_modal').modal('hide');

            }
            else
                alert_float('danger', response.message);


        });

        return false;

    }

    function open_payment_transfer_modal( record_id )
    {
        $('#payment_transfer_modal').modal('hide');

        $('#source_mode').selectpicker('val','');
        $('#target_mode').selectpicker('val','');

        $('#source_mode').selectpicker("refresh");
        $('#target_mode').selectpicker("refresh");

        $('#transfer_date').val('');
        $('#source_amount').val('');
        $('#target_amount').val('');
        $('#description').val('');
        $('#transfer_id').val(0);

        $('#payment_transfer_modal').find('.edit-title').hide();
        $('#payment_transfer_modal').find('.add-title').show();

        if( record_id > 0 )
        {

            $.post( admin_url+'account_balances/casebank/transfer_detail/'+record_id ).done(function(response) {

                response = JSON.parse(response);

                if ( response.success == true )
                {

                    $('#source_mode').selectpicker('val', response.data.source_mode );
                    $('#target_mode').selectpicker('val', response.data.target_mode );

                    $('#source_mode').selectpicker("refresh");
                    $('#target_mode').selectpicker("refresh");

                    $('#source_amount').val(response.data.source_amount);
                    $('#target_amount').val(response.data.target_amount);
                    $('#description').val( response.data.description );
                    $('#transfer_id').val( response.data.id );

                    $('#transfer_date').val( response.data.transfer_date );



                    $('#payment_transfer_modal').find('.edit-title').show();
                    $('#payment_transfer_modal').find('.add-title').hide();

                }

            });

        }

        $('#payment_transfer_modal').modal();

    }


    function payment_transfer_delete( record_id )
    {

        if( record_id > 0 )
        {

            $.post( admin_url+'account_balances/casebank/transfer_delete/'+record_id ).done(function(response) {

                response = JSON.parse(response);

                if (response.success == true)
                {

                    transfer_table_load();

                    alert_float('success', response.message);

                }
                else
                    alert_float('danger', response.message);

            });

        }

    }


    function reload_the_table()
    {
        var $statementPeriod = $('#range');
        var value = $statementPeriod.selectpicker('val');
        var period = new Array();
        if (value != 'period') {
            period = JSON.parse(value);
        } else {
            period[0] = $('input[name="period-from"]').val();
            period[1] = $('input[name="period-to"]').val();

            if (period[0] == '' || period[1] == '') {
                return false;
            }
        }

        $('#from_date').val(period[0]);
        $('#to_date').val(period[1]);


        transfer_table_load();
    }

    function transfer_table_load()
    {

        if ($.fn.DataTable.isDataTable('.table-payment-modes-transfer'))
        {
            $('.table-payment-modes-transfer').DataTable().destroy();
        }

        initDataTable('.table-payment-modes-transfer', admin_url+'account_balances/casebank/transfer_lists', [], [],
            {
                "from_date": '#from_date',
                "to_date": '#to_date',
                "from_payment_mode": '#from_payment_mode',
                "to_payment_mode": '#to_payment_mode',
            }
            , [0, 'desc']);
    }


</script>

</body>

</html>
