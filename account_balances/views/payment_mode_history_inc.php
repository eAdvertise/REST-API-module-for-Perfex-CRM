
<?php

$report_data    = [ 'labels' => '' , 'datasets' => [] ];

$report_labels  = [];

$chart_data     = [];

?>


<div class="table-responsive">

    <table class="table dt-table">

        <thead>
            <tr>
                <th><b>#</b></th>
                <th><b><?php echo _l('spamfilter_type'); ?></b></th>
                <th><b><?php echo _l('statement_heading_date'); ?></b></th>
                <th><b><?php echo _l('statement_heading_details'); ?></b></th>
                <th class="text-right"><b><?php echo _l('account_balance_money_out'); ?></b></th>
                <th class="text-right"><b><?php echo _l('account_balance_money_in'); ?></b></th>
                <th class="text-right"><b><?php echo _l('statement_heading_balance'); ?></b></th>
            </tr>
        </thead>

        <tbody>

        <?php

        $index = 1;
        $balance = 0;
        $total_in = 0;
        $total_out = 0;

        if (isset($use_receipts) && $use_receipts) {

            // Receipts are currently treated as base-currency only
            $get_base_currency = get_base_currency();
            $currency = !empty($get_base_currency->name) ? $get_base_currency->name : '';

        } elseif ( empty( $payment_mode->payment_currency ) ) {

            $get_base_currency = get_base_currency();

            if ( !empty( $get_base_currency->name ) )
                $currency = $get_base_currency->name;
            else
                $currency = '';

        } else {
            $currency = $payment_mode->payment_currency;
        }


        if( !empty( $payment_mode->opening_balance ) ) {

            $balance = $payment_mode->opening_balance;

            if( $balance < 0 )
                $total_out = abs($balance);
            else
                $total_in = $balance;

            ?>

            <tr>
                <td><?php echo $index++?></td>
                <td>-</td>
                <td>-</td>
                <td><?php echo _l('cash_opening_deliver_balance'); ?></td>
                <td class="text-right"><?php echo $balance < 0 ? app_format_money( abs($balance) , $currency ) : '' ?></td>
                <td class="text-right"><?php echo $balance > 0 ? app_format_money( $balance , $currency ) : '' ?></td>
                <td class="text-right"><?php echo app_format_money( $balance , $currency ) ?></td>
            </tr>

        <?php } ?>


        <?php if ( !empty( $carryover ) ) :

            $carryover_balance = 0;

            foreach ( $carryover as $item )
            {

                if ( in_array( $item->type , [2, 4, 5] ) )
                {
                    $carryover_balance  -= $item->amount;
                    $balance            -= $item->amount;
                }
                else
                {
                    $carryover_balance  += $item->amount;
                    $balance            += $item->amount;
                }

            }

            if( !empty( $carryover_balance ) ) : ?>

                <tr>
                    <td><?php echo $index++?></td>
                    <td>-</td>
                    <td>-</td>
                    <td><?php echo _l('account_balance_carryover'); ?></td>
                    <td class="text-right"><?php echo $carryover_balance < 0 ? app_format_money( abs($carryover_balance) , $currency ) : '' ?></td>
                    <td class="text-right"><?php echo $carryover_balance > 0 ? app_format_money( $carryover_balance , $currency ) : '' ?></td>
                    <td class="text-right"><?php echo app_format_money( $balance , $currency ) ?></td>
                </tr>

            <?php endif; ?>

        <?php endif; ?>


        <?php if( !empty( $history ) ) : ?>

            <?php foreach ( $history as $item ) {

                $transaction_type   = '';
                $desc               = $item->description;

                switch ( $item->type )
                {

                    case 1 : // Receipt (PaymentsOnAccount) OR Payment (legacy)

                        // If ref_no is present (receipt_number), this is a Receipt row
                        if (isset($item->ref_no) && !empty($item->ref_no)) {

                            $transaction_type = _l('Receipts') . ' <a target="_blank" href="'.admin_url('paymentsonaccount/view_receipt/'.$item->record_id).'">#'.$item->ref_no.' </a> ';

                            $desc_parts = [];
                            if (!empty($item->clientid)) {
                                $client_label = !empty($item->client_name) ? $item->client_name : '#'.$item->clientid;
                                $desc_parts[] = _l('customer') . ' <a target="_blank" href="'.admin_url('clients/client/'.$item->clientid).'">'.$client_label.' </a> ';
                            }
                            if (!empty($item->description)) {
                                $desc_parts[] = $item->description;
                            }
                            $desc = implode(' - ', $desc_parts);

                        } else {

                            // Legacy core payment (invoicepaymentrecords)
                            $transaction_type = _l('payment'). ' <a target="_blank" href="'.admin_url('payments/payment/'.$item->record_id).'">#'.$item->record_id.' </a> ';

                            $desc = _l('invoice'). ' <a target="_blank" href="'.admin_url('invoices#'.$item->description).'">#'.format_invoice_number($item->description).' </a> ';
                        }

                        break;

                    case 7 : // Core payment fallback (no receipt found)

                        $transaction_type = _l('payment'). ' <a target="_blank" href="'.admin_url('payments/payment/'.$item->record_id).'">#'.$item->record_id.' </a> ';

                        $desc = _l('invoice'). ' <a target="_blank" href="'.admin_url('invoices#'.$item->description).'">#'.format_invoice_number($item->description).' </a> ';

                        break;



                    case 2: // Expense

                        $transaction_type = _l('expense'). ' <a target="_blank" href="'.admin_url('expenses/list_expenses/'.$item->record_id).'">#'.$item->record_id.' </a>';

                        break;


                    case 3: // transfer in

                        $transaction_type = _l('transfer_of_money_from_cash'). ' <a target="_blank" href="'.admin_url('account_balances/casebank/transfer_manage?transfer_id='.$item->record_id).'">#'.$item->record_id.' </a>';

                        $desc = _l("transfer_heading_welding_case").' : '.( !empty( $payment_modes[$item->description] ) ? $payment_modes[$item->description] : '' ) ;

                        break;

                    case 4: // transfer out

                        $transaction_type = _l('money_transfer_to_cash'). ' <a target="_blank" href="'.admin_url('account_balances/casebank/transfer_manage?transfer_id='.$item->record_id).'">#'.$item->record_id.' </a>';

                        $desc = _l("transfer_heading_target_case").' : '.( !empty( $payment_modes[$item->description] ) ? $payment_modes[$item->description] : '' ) ;

                        break;

                    case 5: // withdraw

                        $transaction_type = _l('money_withdraw'). ' <a target="_blank" href="'.admin_url('account_balances/casebank/withdrawal?withdraw_id='.$item->record_id).'">#'.$item->record_id.' </a>';

                        $desc = ( !empty( $payment_modes[$item->description] ) ? $payment_modes[$item->description] : '' ) . " " ._l("money_withdraw");

                        break;


                    case 6: // income

                        $transaction_type = _l('account_entry'). ' <a target="_blank" href="'.admin_url('account_balances/casebank/income?income_id='.$item->record_id).'">#'.$item->record_id.' </a>';

                        $desc = ( !empty( $payment_modes[$item->description] ) ? $payment_modes[$item->description] : '' ) . " " ._l("account_entry");

                        break;


                }

                if( !empty( $item->clientid ) )
                {

                    $client_info = get_client( $item->clientid );
                    $client_link = admin_url('clients/client/'.$item->clientid);

                    $desc .= " | "._l('client')." <a target='_blank' href='$client_link'> $client_info->company </a> ";

                }

                ?>

                <tr>
                    <td><?php echo $index++?></td>
                    <td><?php echo $transaction_type?></td>
                    <td><?php echo _d( date('Y-m-d' , strtotime( $item->date ) ) )?></td>
                    <td><?php echo $desc; ?></td>
                    <td class="text-right">
                        <?php if( in_array( $item->type, [ 2 , 4 , 5 ] ) )
                        {
                            echo app_format_money( $item->amount , $currency );
                            $balance    -= $item->amount;
                            $total_out  += $item->amount;
                        }
                        ?>
                    </td>
                    <td class="text-right">
                        <?php if( in_array( $item->type, [ 1 , 3 , 6 ] ) )
                        {
                            echo app_format_money( $item->amount , $currency );
                            $balance    += $item->amount;
                            $total_in   += $item->amount;
                        }
                        ?>
                    </td>
                    <td class="text-right"><?php echo app_format_money( $balance , $currency ) ?></td>
                </tr>

            <?php

                // Data is being prepared for the chart.
                $date_year  = date("Y", strtotime( $item->date ) );
                $date_week  = date("W", strtotime( $item->date ) );
                $report_key = $date_year."-".$date_week;

                $report_labels[ $report_key ] = _l('week')." #$date_week";

                $chart_data[ $report_key ] = $balance;


            } ?>

        <?php endif; ?>

        </tbody>

        <tfoot class="statement_tfoot">
            <tr>
                <td colspan="4" class="text-right">
                    <b><?php echo _l('balance_due'); ?></b>
                </td>
                <td class="text-right">
                    <b><?php echo app_format_money($total_out, $currency); ?></b>
                </td>
                <td class="text-right">
                    <b><?php echo app_format_money($total_in, $currency); ?></b>
                </td>
                <td class="text-right">
                    <b><?php echo app_format_money($balance, $currency); ?></b>
                </td>
            </tr>
        </tfoot>

    </table>

</div>


<?php

if ( !empty( $chart_data ) )
{

    $report_lb = [];
    $report_dt = [];

    foreach ( $report_labels as $report_key => $report_label )
    {
        $report_lb[] = $report_label;
    }

    foreach ( $chart_data as $chart_dt )
    {
        $report_dt[] = $chart_dt;
    }

    $report_data["labels"] = $report_lb;

    $report_data["datasets"][] =  [

        'label' => _l('statement_heading_balance') ,

        'data' => $report_dt ,

        'backgroundColor' => 'rgba(37,155,35,0.2)' ,

        'borderColor' => '#84c529' ,

        'borderWidth' => 2 ,

        'tension' => false ,

        'fill' => true

    ];


    $currency_data = get_currency($currency);
    $query_currency_symbol = !empty($currency_data->symbol) ? $currency_data->symbol : $currency;

    ?>


    <div class="panel_s">

        <div class="panel-heading">
            <span class="title"> <?php echo _l('account_week_balance_title')?> </span>
        </div>

        <div class="panel-body">

            <div class="relative" style="max-height:600px;">

                <canvas class="chart" height="600" id="payment_mode_week_chart"></canvas>

            </div>

        </div>

    </div>


    <script>

        (function($) {
            "use strict";

            $(function() {

                var customerReportChar = new Chart($('#payment_mode_week_chart'), {

                    type: 'line',

                    data: <?php echo json_encode($report_data); ?>,

                    options: {

                        maintainAspectRatio: false,

                        tooltips: {

                            callbacks: {

                                label: function(tooltipItem, data) {

                                    return '<?php echo $query_currency_symbol?> ' + format_money(tooltipItem.yLabel , '<?php echo $query_currency_symbol?>' )

                                }

                            }

                        },

                        scales: {
                            yAxes: [{

                                ticks: {

                                    callback: function(value) {

                                        return '<?php echo $query_currency_symbol?> ' + format_money( value , '<?php echo $query_currency_symbol?>' )

                                    },

                                    beginAtZero: true,

                                }

                            }]

                        },

                    }

                });


            });

        })(jQuery);


    </script>

<?php } ?>


