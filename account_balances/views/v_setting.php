<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>

<div id="wrapper">
    <div class="content">

        <div class="row">

            <div class="col-md-12">

                <div class="panel_s">

                    <div class="panel-heading"> <?php echo _l('settings')?> </div>

                    <div class="panel-body">


                        <div class="form-group">


                            <div class="checkbox checkbox-primary">

                                <input type="checkbox" <?php if ( get_option('account_balance_enable_multi_currency_for_invoice') == 1 ) { echo 'checked'; } ?>
                                    class="account_balance_setting" value="1" id="account_balance_enable_multi_currency_for_invoice" name="account_balance_enable_multi_currency_for_invoice">

                                <label for="account_balance_enable_multi_currency_for_invoice"><?php echo _l('account_balance_enable_multi_currency_for_invoice') ?></label>

                            </div>

                        </div>


                        <hr />


                        <div class="form-group">


                            <div class="checkbox checkbox-primary">

                                <input type="checkbox" <?php if ( get_option('account_balance_enable_multi_currency_for_estimate') == 1 ) { echo 'checked'; } ?>
                                    class="account_balance_setting" value="1" id="account_balance_enable_multi_currency_for_estimate" name="account_balance_enable_multi_currency_for_estimate">

                                <label for="account_balance_enable_multi_currency_for_estimate"><?php echo _l('account_balance_enable_multi_currency_for_estimate') ?></label>

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

        $('.account_balance_setting').change(function ( e ){

            var setting_name = $(this).attr('id');
            var setting_val = $(this).prop('checked') ? 1 : 0 ;


            $.post( admin_url+'account_balances/casebank/save_setting' , {'name' : setting_name , 'value' : setting_val} ).done(function(response) {

                response = JSON.parse(response);

                alert_float('success', response.message);

            });


        })

    })(jQuery);

</script>


</body>

</html>
