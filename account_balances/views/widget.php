<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php

$account_balances = [];

if (is_staff_member()) {

    $this->load->model('account_balances/case_model');

    $account_balances = $this->case_model->account_balances();

}

$total_amounts = [];


?>

<div class="widget<?php if (count($account_balances) == 0 || !is_staff_member()) {

    echo ' hide';

} ?>" id="widget-<?php echo create_widget_id('account_balances'); ?>">

    <?php if (is_staff_member()) { ?>

        <div class="row">

            <div class="col-md-12">

                <div class="panel_s">

                    <div class="panel-body padding-10">

                        <div class="widget-dragger"></div>

                        <p

                            class="tw-font-medium tw-flex tw-items-center tw-mb-0 tw-space-x-1.5 rtl:tw-space-x-reverse tw-p-1.5">

                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"

                                 stroke="currentColor" class="tw-w-6 tw-h-6 tw-text-neutral-500">

                                <path stroke-linecap="round" stroke-linejoin="round"

                                      d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />

                            </svg>



                            <span class="tw-text-neutral-700">

                            <?php echo _l('case_account'); ?>

                        </span>

                        </p>

                        <hr class="-tw-mx-3 tw-mt-3 tw-mb-6">

                        <div class="account_balances tw-px-1 tw-pb-1">

                            <table class="table table-striped">
                                <thead>
                                <th><?php echo _l("case")?></th>
                                <th><?php echo _l("balance")?></th>
                                </thead>
                                <tbody>
                                <?php if ( !empty( $account_balances) ) {?>
                                    <?php foreach ( $account_balances as $row_data ) {
                                        $balance = $row_data->opening_balance
                                            + $row_data->total_income
                                            - $row_data->total_out ;

                                        $currency = $row_data->payment_currency;

                                        if( !empty( $total_amounts["$currency"] ) )
                                            $total_amounts["$currency"] += $balance;
                                        else
                                            $total_amounts["$currency"] = $balance;


                                        ?>
                                        <tr>
                                            <td> <a target="_blank" href="<?php echo admin_url('account_balances/casebank/transaction/'.$row_data->id)?>" class="btn"> <?php echo $row_data->name ?> </a> </td>
                                            <td class=""><?php echo app_format_money( $balance  , $row_data->payment_currency ) ?></td>
                                        </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr>
                                        <td colspan="3"><?php echo  _l('not_found') ?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>

                                <?php if ( !empty( $account_balances) ) { ?>

                                    <tfoot>

                                    <?php foreach ( $total_amounts as $tota_currency => $total_amount) { ?>
                                        <tr>
                                            <td></td>
                                            <td> <strong> <?php echo app_format_money( $total_amount , $tota_currency)?> </strong> </td>
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

    <?php } ?>

</div>
