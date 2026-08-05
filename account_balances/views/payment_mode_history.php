<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">

                <div class="panel_s">

                    <div class="panel-heading">

                        <?php echo  "$payment_mode->name - " . _l("case_history")?>

                    </div>


                    <div class="panel-body">

                        <div class="row">
                            <div class="col-md-4">
                                <?php $this->load->view('_statement_period_select', ['onChange' => 'render_customer_statement()']); ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12" id="transaction_content_html">

                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>


<script>

    (function($) {
        "use strict";

        $(function() {
            render_customer_statement();
        });

    })(jQuery);

    function render_customer_statement() {
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

        var statementUrl = admin_url + 'account_balances/casebank/transactions/<?php echo $payment_mode_id?>';
        var statementUrlParams = new Array();

        statementUrlParams['from'] = period[0];
        statementUrlParams['to'] = period[1];
        statementUrl = buildUrl(statementUrl, statementUrlParams);

        $.get(statementUrl, function(response) {


            $('#transaction_content_html').html(response.html).promise().done(function (){
                initDataTableInline();
            });

        }, 'json').fail(function(response) {
            alert_float('danger', response.responseText);
        });
    }
</script>

</body>

</html>
