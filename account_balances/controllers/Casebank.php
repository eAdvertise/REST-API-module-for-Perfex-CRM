<?php

/**
 * @property Case_model $Case_model
 */

class Casebank extends AdminController
{

    public function __construct()
    {
        parent::__construct();


        $this->load->model('Case_model');

        // checking the database tables
        $this->Case_model->check_the_db();


    }

    public function case_manage(){

        $data['title'] = _l("case_list");

        // balance
        $data['account_balances'] = $this->Case_model->account_balances();

        $this->load->view('case_manage', $data);

    }

    /**
     *
     * @note payment mode transaction history view
     *
     * @param $payment_mode_id
     * @return void
     */
    public function transaction($payment_mode_id = 0){

        $payment_mode_detail    = $this->Case_model->get_transaction( $payment_mode_id );

        if( empty( $payment_mode_detail ) )
            show_404();

        $this->load->view('payment_mode_history',  [ 'payment_mode_id' => $payment_mode_id , 'payment_mode' => $payment_mode_detail] );
    }

    /**
     * @note payment mode transaction history lists
     *
     * @param $payment_mode_id
     * @return void
     */
    public function transactions( $payment_mode_id = 0 )
    {

        $payment_mode_detail    = $this->Case_model->get_transaction( $payment_mode_id );

        if( empty( $payment_mode_detail ) )
            show_404();



        $from   = to_sql_date( $this->input->get('from') );
        $to     = to_sql_date( $this->input->get('to') );

        if ($from == $to)
        {
            $sqlDate = 'DATE(date)="' . $this->db->escape_str($from) . '"';
        }
        else
        {
            $sqlDate = '( DATE(date) BETWEEN "' . $this->db->escape_str($from) . '" AND "' . $this->db->escape_str($to) . '")';
        }

        $carryover  = $this->Case_model->transaction_history( $payment_mode_id , " DATE(date) < '".$this->db->escape_str($from)."'" );

        $history    = $this->Case_model->transaction_history( $payment_mode_id , $sqlDate );

        $payment_modes = [];
        $payment_mode_lists   = $this->Case_model->get_transaction(  );
        foreach ( $payment_mode_lists as $payment_mode_list) {
            $payment_modes[ $payment_mode_list['id'] ] = $payment_mode_list['name'];
        }

        
        // If PaymentsOnAccount is available, account_balances uses Receipts as the primary source for Money IN.
        $use_receipts = false;
        try {
            if (isset($this->app_modules) && method_exists($this->app_modules, 'is_active')) {
                $use_receipts = $this->app_modules->is_active('paymentsonaccount');
            }
        } catch (Throwable $e) {
            // ignore
        }
        if (!$use_receipts) {
            $use_receipts = $this->db->table_exists(db_prefix().'receipts');
        }

$html_data = $this->load->view('payment_mode_history_inc', [
            'payment_mode' => $payment_mode_detail ,
            'payment_modes' => $payment_modes ,
            'history' => $history ,
            'carryover' => $carryover,
            'use_receipts' => $use_receipts
        ] , true );

        echo json_encode( ["html" => $html_data]);

    }


    /**
     * bank / cash transfer main page function
     *
     * @return void
     */
    public function transfer_manage(){

        $data['title'] = _l("inter_cash_transfer");

        $data['payment_modes'] = $this->Case_model->get_transaction('');

        $this->load->view('account_balances/transfer', $data);

    }

    /**
     * bank/cash transfer lists
     *
     * @return void
     */
    public function transfer_lists()
    {
        if ($this->input->is_ajax_request())
        {

            $sTable       = db_prefix().'payment_modes_transfer';

            $aColumns = [

                $sTable.'.id as id',

                'source_mode',

                'target_mode',

                'source_amount',

                'target_amount',

                $sTable.'.description as description',

                'transfer_date',

                'date',

                'firstname',
            ];

            $where = [];


            $from_date  = to_sql_date( $this->input->post('from_date') );
            $to_date    = to_sql_date( $this->input->post('to_date') );

            if( !empty( $from_date ) )
                $where[] = "AND DATE( transfer_date ) >= '$from_date'";

            if( !empty( $to_date ) )
                $where[] = "AND DATE( transfer_date ) <= '$to_date'";

            if( $this->input->post('from_payment_mode') )
                $where[] = "AND $sTable.source_mode = ".$this->input->post('from_payment_mode');

            if( $this->input->post('to_payment_mode') )
                $where[] = "AND $sTable.target_mode = ".$this->input->post('to_payment_mode');


            $hidden_payment_modes = $this->Case_model->get_hidden_payment_modes_for_staff();

            if ( !empty( $hidden_payment_modes ) )
            {

                foreach ( $hidden_payment_modes as $hidden_payment_mode )
                {
                    $where[] = "AND $sTable.source_mode != '$hidden_payment_mode' ";
                }

            }

            $sIndexColumn = 'id';

            $join = [
                'LEFT JOIN '.db_prefix().'staff staff               ON staff.staffid = '.$sTable.'.staffid',
            ];

            $result = data_tables_init( $aColumns , $sIndexColumn , $sTable , $join , $where , [

                'lastname' ,


            ] );

            $output  = $result['output'];

            $rResult = $result['rResult'];

            $staff_has_permission       = staff_can( 'case_manage_transfer' , 'case_manage'  ) ;
            $staff_has_permission_del   = staff_can( 'case_manage_transfer_del' , 'case_manage'  ) ;


            $payment_modes = [];

            $modes = $this->Case_model->get_transaction();

            foreach ( $modes as $mode )
            {

                $mode_id = $mode['id'];
                $payment_modes["$mode_id"] = $mode ;

            }


            foreach( $rResult as $aRow )
            {

                $row = [];

                $source_mode = $this->Case_model->get_payment_mode_info( $payment_modes , $aRow["source_mode"] );
                $target_mode = $this->Case_model->get_payment_mode_info( $payment_modes , $aRow["target_mode"] );


                $record_id = $aRow['id'];

                $numberOutput = "#$record_id";
                $numberOutput .= '<div class="row-options">';

                if ( $staff_has_permission )
                    $numberOutput .= "<a class='text-info' href='#' onclick='open_payment_transfer_modal( $record_id )'>" . _l('edit') . "</a>";

                if ( $staff_has_permission_del )
                    $numberOutput .= " <a class='text-danger _delete' href='#' onclick='payment_transfer_delete( $record_id )'>" . _l('delete') . "</a>";

                $numberOutput .= '</div>';

                $row[] =  $numberOutput;
                $row[] =  $source_mode->name;
                $row[] =  $target_mode->name;
                $row[] =  app_format_money( $aRow['source_amount'] , $source_mode->payment_currency );
                $row[] =  app_format_money( $aRow['target_amount'] , $target_mode->payment_currency );

                $row[] =  $aRow['description'];
                $row[] =  _d($aRow['transfer_date']);
                $row[] =  _dt($aRow['date']);

                $row[] = $aRow['firstname']." ".$aRow['lastname'];

                $output['aaData'][] = $row;

            }

            echo json_encode($output);

            die();

        }

    }

    public function transfer_detail( $detail_id = 0 )
    {
        if( !empty( $detail_id ) )
        {

            $data = $this->db->select('*')
                ->from( db_prefix()."payment_modes_transfer" )
                ->where('id',$detail_id)->get()->row();

            echo json_encode([
                'data'    => $data,
                'success' => !empty( $data ),
                'message' => !empty( $data ) ? '' : _l("transfer_not_found"),
            ]);

        }

    }

    /**
     * Transfer record saving
     * @return void
     */
    public function transfer_save()
    {
        if( $this->input->post() )
        {

            $source_mode = $this->input->post('source_mode');

            $target_mode = $this->input->post('target_mode');

            if( $source_mode == $target_mode )
            {

                echo json_encode([
                    'success' => false,
                    'message' => _l("transfer_warning_msg"),
                ]);

                die();

            }

            $table_name = db_prefix().'payment_modes_transfer';

            $db_data = [
                'source_mode' => $this->input->post('source_mode'),
                'target_mode' => $this->input->post('target_mode'),
                'source_amount' => $this->input->post('source_amount') ,
                'target_amount' => $this->input->post('target_amount') ,
                'description' => $this->input->post('description') ,
                'transfer_date' => to_sql_date( $this->input->post('transfer_date') ),
                'staffid' => get_staff_user_id()
            ];

            $transfer_id = $this->input->post('transfer_id');

            if( empty( $transfer_id ) )
            {
                $db_data['date'] = date('Y-m-d H:i:s');
                $this->db->insert($table_name,$db_data);
            }
            else
            {
                $this->db->where('id',$transfer_id)->update($table_name,$db_data);
            }


            echo json_encode([
                'success' => true,
                'message' => _l("transfer_success_msg"),
            ]);

        }

    }

    /**
     * Transfer record deleting
     *
     * @param $detail_id
     * @return void
     */
    public function transfer_delete( $detail_id = 0 )
    {

        $this->db->where('id',$detail_id)->delete(db_prefix().'payment_modes_transfer');

        log_activity( "Transfer record deleted [ transfer record id : $detail_id ]" );

        echo json_encode([
            'success' => true,
            'message' => _l("transfer_success_msg"),
        ]);

    }

    /**
     * Bank/Cash total balance functions
     *
     */

    public function balance_manage(){

        $data['title'] = _l("account_balances_deliver");

        $data['currencies'] = $this->db->select("name")->from(db_prefix()."currencies")->get()->result_array();

        $data['payment_modes'] = $this->Case_model->get_transaction();

        $data['staff'] = $this->db->select('staffid,firstname,lastname')->from(db_prefix().'staff')->where('active',1)->where('admin',0)->get()->result_array();

        $this->load->view('account_balances/balance', $data);

    }

    public function balance_detail( $payment_id = '' )
    {
        if( !empty( $payment_id ) )
        {

            $data = $this->Case_model->get_payment_account_info( $payment_id );

            $pay_mod = [];

            if( !empty( $data->active_staff ) )
            {

                $pay_mod = json_decode( $data->active_staff , 1 );

            }

            $data->staff = $pay_mod;

            echo json_encode([
                'data'    => $data,
                'success' => !empty( $data )
            ]);

        }

    }

    /**
     * Saving the payment mode opening balance and currency
     *
     * @return void
     */
    public function account_balances_save(){

        if( $this->input->post() ){

            $account_id = $this->input->post('account_id');

            if( !empty( $account_id ) )
            {

                $table_name = db_prefix().'payment_modes_accounts';

                $staff = $this->input->post('staff');

                if ( empty( $staff ) )
                    $staff = null;
                else
                    $staff = json_encode( $staff );

                $db_data = [
                    'payment_currency'  => $this->input->post('payment_currency') ,
                    'opening_balance'   => $this->input->post('opening_balance') ,
                    'active_staff'      => $staff ,
                    'payment_id'        => $account_id ,
                ];

                if ( $this->input->post('is_public') )
                    $db_data['is_public'] = 1;
                else
                    $db_data['is_public'] = 0;


                $info = $this->Case_model->get_payment_account_info( $account_id );

                if ( empty( $info ) )
                    $this->db->insert( $table_name , $db_data );
                else
                    $this->db->where('payment_id',$account_id)->update( $table_name , $db_data );

                echo json_encode([
                    'success' => true,
                    'message' => _l("balance_success_msg"),
                ]);

            }
            else
            {
                echo json_encode([
                    'success' => false,
                    'message' => _l("balance_not_found"),
                ]);

                die();
            }

        }

    }



    /**
     * withdrawal from account balance
     */
    public function withdrawal()
    {

        $data['title'] = _l("account_withdrawal");

        $data['payment_modes'] = $this->Case_model->get_transaction();

        $data['staff'] = $this->staff_model->get('', ['active' => 1]);

        $this->load->view('account_balances/withdrawal', $data);

    }

    /**
     * Withdraw detail
     */
    public function withdrawal_detail( $detail_id = 0 )
    {

        if( !empty( $detail_id ) )
        {

            $data = $this->db->select('*')->from( db_prefix()."payment_modes_withdraw" )->where('id',$detail_id)->get()->row();

            if( !empty( $data ) )
            {
                $data->company = get_company_name( $data->client_id );
            }


            echo json_encode([
                'data'    => $data,
                'success' => !empty( $data ),
                'message' => !empty( $data ) ? '' : _l("balance_not_found"),
            ]);

        }

    }

    /**
     * Withdraw lists
     */
    public function withdrawal_lists()
    {

        if ($this->input->is_ajax_request())
        {

            $sTable       = db_prefix().'payment_modes_withdraw';

            $aColumns = [

                $sTable.'.id as id',

                'source_mode',

                'company',

                'staff.firstname',

                'amount',

                $sTable.'.date as date',

                $sTable.'.description as description',

                $sTable.'.added_date as added_date',

            ];


            $where = [];

            $from_date  = to_sql_date( $this->input->post('from_date') );
            $to_date    = to_sql_date( $this->input->post('to_date') );

            if( !empty( $from_date ) )
                $where[] = "AND DATE( $sTable.date ) >= '$from_date'";

            if( !empty( $to_date ) )
                $where[] = "AND DATE( $sTable.date ) <= '$to_date'";

            if( $this->input->post('from_payment_mode') )
                $where[] = "AND $sTable.source_mode = '".$this->input->post('from_payment_mode')."'";



            $hidden_payment_modes = $this->Case_model->get_hidden_payment_modes_for_staff();

            if ( !empty( $hidden_payment_modes ) )
            {

                foreach ( $hidden_payment_modes as $hidden_payment_mode )
                {
                    $where[] = "AND $sTable.source_mode != '$hidden_payment_mode' ";
                }

            }

            $sIndexColumn = 'id';

            $join = [
                'LEFT JOIN '.db_prefix().'clients client            ON client.userid = '.$sTable.'.client_id',
                'LEFT JOIN '.db_prefix().'staff staff               ON staff.staffid = '.$sTable.'.staff_id',
            ];

            $result = data_tables_init( $aColumns , $sIndexColumn , $sTable , $join , $where , [


                'staff.lastname' ,

                'client_id' ,

                'staffid' ,


            ] );

            $output  = $result['output'];

            $rResult = $result['rResult'];


            $staff_has_permission       = staff_can( 'case_manage_withdraw' , 'case_manage'  ) ;
            $staff_has_permission_del   = staff_can( 'case_manage_withdraw_del' , 'case_manage'  ) ;


            $payment_modes = [];

            $modes = $this->Case_model->get_transaction();

            foreach ( $modes as $mode )
            {

                $mode_id = $mode['id'];
                $payment_modes["$mode_id"] = $mode ;

            }

            foreach( $rResult as $aRow )
            {

                $row = [];

                $source_mode = $this->Case_model->get_payment_mode_info( $payment_modes , $aRow["source_mode"] );


                $record_id = $aRow['id'];

                $numberOutput = "#$record_id";
                $numberOutput .= '<div class="row-options">';

                if ( $staff_has_permission )
                    $numberOutput .= "<a class='text-info' href='#' onclick='open_withdrawal_modal( $record_id )'>" . _l('edit') . "</a>";

                if ( $staff_has_permission_del )
                    $numberOutput .= "  <a class='text-danger _delete' href='#' onclick='payment_withdrawal_delete( $record_id )'>" . _l('delete') . "</a>";

                $numberOutput .= '</div>';

                $row[] =  $numberOutput;

                $row[] =  $source_mode->name;

                $company = "<a target='_blank' href='".admin_url('clients/client/'.$aRow['client_id'])."'> ".$aRow["company"]." </a>";

                $row[] =  $company;

                $staff = "<a target='_blank' href='".admin_url('staff/member/'.$aRow['staffid'])."'> ".$aRow["firstname"].' '.$aRow["lastname"]." </a>";

                $row[] =  $staff;

                $row[] =  app_format_money( $aRow['amount'] , $source_mode->payment_currency );

                $row[] =  _dt($aRow['date']);

                $row[] =  $aRow['description'];

                $row[] =  _dt($aRow['added_date']);

                $output['aaData'][] = $row;

            }



            echo json_encode($output);

            die();

        }

    }

    /**
     * Withdraw record saving
     */
    public function withdrawal_save()
    {

        if( $this->input->post() )
        {

            $table_name = db_prefix().'payment_modes_withdraw';

            $db_data = [
                'source_mode' => $this->input->post('source_mode'),
                'client_id' => $this->input->post('client_id'),
                'staff_id' => $this->input->post('staff_id') ,
                'amount' => $this->input->post('amount') ,
                'description' => $this->input->post('description') ,
                'date' => to_sql_date( $this->input->post('date') ),
                'added_from' => get_staff_user_id()
            ];

            $record_id = $this->input->post('record_id');

            if( empty( $record_id ) )
            {
                $db_data['added_date'] = date('Y-m-d H:i:s');
                $this->db->insert($table_name,$db_data);
            }
            else
            {
                $this->db->where('id',$record_id)->update($table_name,$db_data);
            }


            echo json_encode([
                'success' => true,
                'message' => _l("transfer_success_msg"),
            ]);

        }

    }

    /**
     * Withdraw record deleting
     *
     * @param $detail_id
     * @return void
     */
    public function withdrawal_delete( $detail_id = 0 )
    {

        $this->db->where('id',$detail_id)->delete(db_prefix().'payment_modes_withdraw');

        log_activity( "Withdraw record deleted [ record id : $detail_id ]" );

        echo json_encode([
            'success' => true,
            'message' => _l("transfer_success_msg"),
        ]);

    }




    public function income(){

        $data['title'] = _l("account_entry");

        $data['payment_modes'] = $this->Case_model->get_transaction('');

        $data['staff'] = $this->staff_model->get('', ['active' => 1]);

        $this->load->view('account_balances/income', $data);
    }

    public function income_lists(){

        if ($this->input->is_ajax_request())
        {

            $sTable       = db_prefix().'payment_modes_income';

            $aColumns = [

                $sTable.'.id as id',

                'source_mode',

                'company',

                'staff.firstname',

                'amount',

                $sTable.'.date as date',

                $sTable.'.description as description',

                $sTable.'.added_date as added_date',

            ];


            $where = [];

            $from_date  = to_sql_date( $this->input->post('from_date') );
            $to_date    = to_sql_date( $this->input->post('to_date') );

            if( !empty( $from_date ) )
                $where[] = "AND DATE( $sTable.date ) >= '$from_date'";

            if( !empty( $to_date ) )
                $where[] = "AND DATE( $sTable.date ) <= '$to_date'";

            if( $this->input->post('from_payment_mode') )
                $where[] = "AND $sTable.source_mode = '".$this->input->post('from_payment_mode')."'";


            $hidden_payment_modes = $this->Case_model->get_hidden_payment_modes_for_staff();

            if ( !empty( $hidden_payment_modes ) )
            {

                foreach ( $hidden_payment_modes as $hidden_payment_mode )
                {
                    $where[] = "AND $sTable.source_mode != '$hidden_payment_mode' ";
                }

            }

            $sIndexColumn = 'id';

            $join = [
                'LEFT JOIN '.db_prefix().'clients client            ON client.userid = '.$sTable.'.client_id',
                'LEFT JOIN '.db_prefix().'staff staff               ON staff.staffid = '.$sTable.'.staff_id',
            ];

            $result = data_tables_init( $aColumns , $sIndexColumn , $sTable , $join , $where , [


                'staff.lastname' ,

                'client_id' ,

                'staffid' ,

            ] );

            $output  = $result['output'];

            $rResult = $result['rResult'];


            $staff_has_permission       = staff_can( 'case_manage_entry' , 'case_manage'  ) ;
            $staff_has_permission_del   = staff_can( 'case_manage_entry_del' , 'case_manage'  ) ;

            $payment_modes = [];

            $modes = $this->Case_model->get_transaction();

            foreach ( $modes as $mode )
            {

                $mode_id = $mode['id'];
                $payment_modes["$mode_id"] = $mode ;

            }

            foreach( $rResult as $aRow )
            {

                $row = [];

                $source_mode = $this->Case_model->get_payment_mode_info( $payment_modes , $aRow["source_mode"] );


                $record_id = $aRow['id'];

                $numberOutput = "#$record_id";
                $numberOutput .= '<div class="row-options">';

                if ( $staff_has_permission )
                    $numberOutput .= "<a class='text-info' href='#' onclick='open_income_modal( $record_id )'>" . _l('edit') . "</a>";

                if ( $staff_has_permission_del )
                    $numberOutput .= " <a class='text-danger _delete' href='#' onclick='income_delete( $record_id )'>" . _l('delete') . "</a>";

                $numberOutput .= '</div>';

                $row[] =  $numberOutput;

                $row[] =  $source_mode->name;

                $company = "<a target='_blank' href='".admin_url('clients/client/'.$aRow['client_id'])."'> ".$aRow["company"]." </a>";

                $row[] =  $company;

                $staff = "<a target='_blank' href='".admin_url('staff/member/'.$aRow['staffid'])."'> ".$aRow["firstname"].' '.$aRow["lastname"]." </a>";

                $row[] =  $staff;

                $row[] =  app_format_money( $aRow['amount'] , $source_mode->payment_currency );

                $row[] =  _dt($aRow['date']);

                $row[] =  $aRow['description'];

                $row[] =  _dt($aRow['added_date']);

                $output['aaData'][] = $row;

            }



            echo json_encode($output);

            die();

        }

    }

    public function income_save()
    {

        if( $this->input->post() )
        {

            $table_name = db_prefix().'payment_modes_income';

            $db_data = [
                'source_mode' => $this->input->post('source_mode'),
                'client_id' => $this->input->post('client_id'),
                'staff_id' => $this->input->post('staff_id') ,
                'amount' => $this->input->post('amount') ,
                'description' => $this->input->post('description') ,
                'date' => to_sql_date( $this->input->post('date') ),
                'added_from' => get_staff_user_id()
            ];

            $record_id = $this->input->post('record_id');

            if( empty( $record_id ) )
            {
                $db_data['added_date'] = date('Y-m-d H:i:s');
                $this->db->insert($table_name,$db_data);
            }
            else
            {
                $this->db->where('id',$record_id)->update($table_name,$db_data);
            }


            echo json_encode([
                'success' => true,
                'message' => _l("income_success_msg"),
            ]);

        }

    }

    public function income_delete($detail_id = 0 ){

        $this->db->where('id',$detail_id)->delete(db_prefix().'payment_modes_income');

        log_activity( "Income record deleted [ record id : $detail_id ]" );

        echo json_encode([
            'success' => true,
            'message' => _l("income_success_msg"),
        ]);

    }

    public function income_detail( $detail_id = 0 )
    {

        if( !empty( $detail_id ) )
        {

            $data = $this->db->select('*')->from( db_prefix()."payment_modes_income" )->where('id',$detail_id)->get()->row();

            if( !empty( $data ) )
            {
                $data->company = get_company_name( $data->client_id );
            }


            echo json_encode([
                'data'    => $data,
                'success' => !empty( $data ),
                'message' => !empty( $data ) ? '' : _l("income_not_found"),
            ]);

        }

    }



    /**
     * @version 1.0.5 Setting page
     */
    public function settings()
    {

        $data['title'] = _l("setting");

        $this->load->view('account_balances/v_setting', $data);

    }

    public function save_setting()
    {

        if ( $this->input->is_ajax_request() )
        {

            $name = $this->input->post('name');
            $value = $this->input->post('value');

            update_option( $name , $value , 0 );

            echo json_encode( [  'message' => _l('updated_successfully' , _l('setting') ) ] );

        }

    }

}
