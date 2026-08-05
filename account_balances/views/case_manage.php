<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<?php

$total_amounts = [];

?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">

                <div class="panel_s">

                    <div class="panel-heading">
                        <h4><?php echo _l("case_list")?></h4>
                    </div>

                    <div class="panel-body panel-table">

                        <table class="table dt-table no-footer">
                            <thead>
                                <th><?php echo _l("case")?></th>
                                <th><?php echo _l("account_balance_money_out")?></th>
                                <th><?php echo _l("account_balance_money_in")?></th>
                                <th><?php echo _l("balance")?></th>
                            </thead>
                            <tbody>
                            <?php if ( !empty( $account_balances) ) {

                                foreach ( $account_balances as $row_data )
                                {

                                    $total_income   = $row_data->total_income;

                                    $total_out      = $row_data->total_out;

                                    if ( $row_data->opening_balance > 0 )
                                        $total_income += $row_data->opening_balance;
                                    elseif ( $row_data->opening_balance < 0 )
                                        $total_out += $row_data->opening_balance;

                                    $balance = $total_income - $total_out ;

                                    $currency = $row_data->payment_currency;

                                    if( !empty( $total_amounts["$currency"] ) )
                                        $total_amounts["$currency"] += $balance;
                                    else
                                        $total_amounts["$currency"] = $balance;


                                    $paymentmode_class = $balance < 0 ? "text-danger" : "text-success";

                                    ?>
                                    <tr>
                                        <td> <a target="_blank" href="<?php echo admin_url('account_balances/casebank/transaction/'.$row_data->id)?>" class="btn"> <?php echo $row_data->name ?> </a> </td>
                                        <td class=""><?php echo app_format_money( $total_out  , $row_data->payment_currency ) ?></td>

                                        <td class=""><?php echo app_format_money( $total_income  , $row_data->payment_currency ) ?></td>

                                        <td class=""><p class="<?php echo $paymentmode_class?>"><?php echo app_format_money( $balance  , $row_data->payment_currency ) ?></p></td>
                                    </tr>
                                <?php }
                            } else { ?>
                                <tr>
                                    <td colspan="4"><?php echo  _l('not_found') ?></td>
                                </tr>
                            <?php } ?>
                            </tbody>

                            <?php if ( !empty( $account_balances) ) { ?>

                                <tfoot>

                                    <?php foreach ( $total_amounts as $tota_currency => $total_amount) {
                                        $paymentmode_class = $total_amount < 0 ? "text-danger" : "text-success";
                                        ?>
                                        <tr>
                                            <td colspan="3"></td>
                                            <td> <strong> <p class="<?php echo $paymentmode_class?>"><?php echo app_format_money( $total_amount , $tota_currency)?></p> </strong> </td>
                                        </tr>
                                    <?php } ?>

                                </tfoot>

                            <?php } ?>

                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>

</body>

</html>
