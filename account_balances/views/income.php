<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>

    <div id="wrapper">
        <div class="content">
            <div class="row">
                <div class="col-md-12">

                    <?php if ( staff_can( 'case_manage_entry' , 'case_manage'  ) ) {  ?>

                        <div class="tw-mb-2 sm:tw-mb-4">
                            <a href="#" class="btn btn-primary" onclick="open_income_modal( 0 )">
                                <i class="fa-regular fa-plus tw-mr-1"></i>
                                <?php echo _l("account_income_new"); ?>
                            </a>
                        </div>

                    <?php } ?>

                    <div class="panel_s">
                        <div class="panel-body">

                            <div class="col-md-4">
                                <input type="hidden" name="from_date" id="from_date" value="">
                                <input type="hidden" name="to_date" id="to_date" value="">
                                <?php $this->load->view('_statement_period_select', ['onChange' => 'reload_the_table()']); ?>
                            </div>

                            <div class="col-md-4">
                                <?php echo render_select( 'from_payment_mode' , $payment_modes , [ 'id' , [ 'name'] ] ,  '' , '' , ['onchange' => 'income_table_load()' , 'data-none-selected-text' => _l('case') ] ); ?>
                            </div>

                            <div class="col-md-12">
                                <div class="panel-table-full">
                                    <?php render_datatable([
                                        _l('transfer_heading_id'),
                                        _l("case"),
                                        _l("client"),
                                        _l("staff"),
                                        _l("statement_heading_amount"),
                                        _l("statement_heading_date"),
                                        _l("transfer_heading_description"),
                                        _l("transfer_heading_process_date"),
                                    ], 'income'); ?>

                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="income_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">
                        <span><?php echo _l('account_entry'); ?></span>
                    </h4>
                </div>

                <?php echo form_open('account_balances/casebank/income_save', ['id' => 'income_form']); ?>

                <input type="hidden" name="record_id" id="record_id" />

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">

                            <?php echo render_date_input('date' ,_l("statement_heading_date")   ); ?>

                            <?php echo render_select('source_mode', $payment_modes , [ 'id' , [ 'name'] ] , _l("case") , null , [] ); ?>

                            <div class="form-group">
                                <label for="client_id"><?php echo _l('client')?></label>
                                <select id="client_id" name="client_id" data-live-search="true" data-width="100%" class="ajax-search" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                </select>
                            </div>

                            <?php echo render_select( 'staff_id' , $staff , [ 'staffid' , [ 'firstname' , 'lastname' ] ] , _l('staff') ) ?>

                            <?php echo render_input('amount', _l("statement_heading_amount") , '' , 'number'); ?>

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

            appValidateForm($('#income_form'), {
                source_mode: 'required' ,
                date: 'required' ,
                amount: 'required',
            }, manage_income_modes);


            income_table_load();

            init_ajax_search('customer', $('[name="client_id"].ajax-search'));



            <?php if ( !empty( $this->input->get('income_id') ) ) { ?>

            open_income_modal( <?php echo $this->input->get('income_id')?> );

            <?php } ?>

        });

    })(jQuery);

    function manage_income_modes(form)
    {

        $.post( form.action , $(form).serialize() ).done(function(response) {

            response = JSON.parse(response);

            if (response.success == true)
            {

                income_table_load();

                alert_float('success', response.message);

                $('#income_modal').modal('hide');

            }
            else
                alert_float('danger', response.message);


        });

        return false;

    }

    function open_income_modal( record_id )
    {
        $('#income_modal').modal('hide');

        $('#source_mode').selectpicker('val','');
        $('#client_id').selectpicker('val','');
        $('#staff_id').selectpicker('val','');

        $('#source_mode').selectpicker("refresh");
        $('#client_id').selectpicker("refresh");
        $('#staff_id').selectpicker("refresh");

        $('#date').val('');
        $('#amount').val('');
        $('#description').val('');
        $('#record_id').val(0);

        if( record_id > 0 )
        {

            $.post( admin_url+'account_balances/casebank/income_detail/'+record_id ).done(function(response) {

                response = JSON.parse(response);

                if ( response.success == true )
                {

                    $('#source_mode').selectpicker('val', response.data.source_mode );

                    $('#staff_id').selectpicker('val', response.data.staff_id );

                    $("#client_id").html("<option value='"+ response.data.client_id +"'> "+response.data.company +"</option>").promise().done(function (){

                        $('#client_id').selectpicker("refresh");
                        $('#client_id').selectpicker('val', response.data.client_id  );

                        $('#client_id').parent('div.ajax-search').find('.filter-option-inner-inner').text(response.data.company );

                    });


                    $('#source_mode').selectpicker("refresh");
                    $('#staff_id').selectpicker("refresh");

                    $('#amount').val( response.data.amount );

                    $('#description').val( response.data.description );
                    $('#record_id').val( response.data.id );

                    $('#date').val( response.data.date );

                }

            });

        }

        $('#income_modal').modal();

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


        income_table_load();
    }

    function income_table_load()
    {

        if ($.fn.DataTable.isDataTable('.table-income'))
        {
            $('.table-income').DataTable().destroy();
        }

        initDataTable('.table-income', admin_url+'account_balances/casebank/income_lists', [], [],
            {
                "from_date": '#from_date',
                "to_date": '#to_date',
                "from_payment_mode": '#from_payment_mode',
            }
            , [0, 'desc']);
    }

    function income_delete( record_id )
    {

        if( record_id > 0 )
        {

            $.post( admin_url+'account_balances/casebank/income_delete/'+record_id ).done(function(response) {

                response = JSON.parse(response);

                if (response.success == true)
                {

                    income_table_load();

                    alert_float('success', response.message);

                }
                else
                    alert_float('danger', response.message);

            });

        }

    }



</script>

</body>

</html>
