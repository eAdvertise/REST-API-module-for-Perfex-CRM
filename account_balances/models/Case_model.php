<?php

class Case_model extends App_Model
{
    private $ab_charset = null;
    private $ab_collation = null;


    /**
     * Resolve DB connection charset/collation in a way that works across Perfex installs
     * (utf8 vs utf8mb4). Uses CI DB driver settings and falls back safely.
     */
    private function ab_get_union_charset()
    {
        if ($this->ab_charset !== null) {
            return $this->ab_charset;
        }

        $cs = null;
        if (isset($this->db) && isset($this->db->char_set) && !empty($this->db->char_set)) {
            $cs = $this->db->char_set;
        }

        // Normalize common variants
        if ($cs === 'utf8mb4' || $cs === 'utf8') {
            $this->ab_charset = $cs;
        } else {
            // Default to utf8mb4 when unknown (Perfex modern default), but keep safe.
            $this->ab_charset = 'utf8mb4';
        }

        return $this->ab_charset;
    }

    private function ab_get_union_collation()
    {
        if ($this->ab_collation !== null) {
            return $this->ab_collation;
        }

        $cs = $this->ab_get_union_charset();
        $coll = null;

        if (isset($this->db) && isset($this->db->dbcollat) && !empty($this->db->dbcollat)) {
            $coll = $this->db->dbcollat;
        }

        // If dbcollat doesn't match charset, pick a safe collation for that charset
        if (!is_string($coll) || $coll === '' || strpos($coll, $cs . '_') !== 0) {
            $coll = ($cs === 'utf8mb4') ? 'utf8mb4_unicode_ci' : 'utf8_unicode_ci';
        }

        $this->ab_collation = $coll;
        return $this->ab_collation;
    }

    private $payment_gateways = [];

    private $gateways = null;


    public function __construct()
    {
        parent::__construct();

        $this->check_the_db();

    }


    /**
     * Returns true when PaymentsOnAccount (paymentsonaccount) is available and we should use Receipts
     * instead of core invoice payments (tblinvoicepaymentrecords) for account balance calculations.
     *
     * We intentionally support both "module active" and "tables exist" checks, to cover environments
     * where the module is installed but the module loader state is not accessible in this context.
     */
    private function use_paymentsonaccount_receipts(): bool
    {
        // 1) Prefer module active check (Perfex app_modules)
        $CI = &get_instance();
        try {
            if (isset($CI->app_modules) && method_exists($CI->app_modules, 'is_active')) {
                if ($CI->app_modules->is_active('paymentsonaccount')) {
                    return true;
                }
            }
        } catch (Throwable $e) {
            // ignore and fallback to table existence check
        }

        // 2) Fallback: DB tables exist
        return $this->db->table_exists(db_prefix() . 'receipts');
    }



    /**
     * Payment modes total balances
     *
     * @return mixed
     */
    public function account_balances()
    {

        $payment_modes = $this->get_transaction('');

        $modes = [];

        foreach ( $payment_modes as $payment_mode )
        {

            // id  payment_currency name opening_balance

            $mode = new stdClass();

            list( $mode->total_income , $mode->total_out ) = $this->account_balance_detail( $payment_mode['id'] );

            $mode->id   = $payment_mode['id'];
            $mode->name = $payment_mode['name'];
            $mode->payment_currency = $payment_mode['payment_currency'];
            $mode->opening_balance  = $payment_mode['opening_balance'];


            $modes[] = $mode;

        }

        return $modes;

    }

    private function account_balance_detail( $payment_mode_id = '' )
    {

        $total_income   = $total_out = 0;
        // total payment / receipts
        if ($this->use_paymentsonaccount_receipts()) {

            // Primary: Receipts
            $info = $this->db->select('SUM( total_amount ) as sum_amount')
                            ->from(db_prefix().'receipts')
                            ->where('payment_mode', (string)$payment_mode_id)
                            ->get()
                            ->row();

            if (!empty($info->sum_amount)) {
                $total_income += $info->sum_amount;
            }

            // Fallback (B): include core payments that do NOT have a receipt, excluding internal allocations created from receipts.
            $this->db->select('SUM( pr.amount ) as sum_amount');
            $this->db->from(db_prefix().'invoicepaymentrecords pr');
            $this->db->join(db_prefix().'receipts r', 'r.source_payment_id = pr.id', 'left');

            if ($this->db->table_exists(db_prefix().'receipt_invoice_applications')) {
                $this->db->join(db_prefix().'receipt_invoice_applications a', 'a.payment_record_id = pr.id', 'left');
                $this->db->where('a.id IS NULL', null, false);
            }

            $this->db->where('pr.paymentmode', (string)$payment_mode_id);
            $this->db->where('r.id IS NULL', null, false);

            // exclude internal "apply receipt -> invoice" core payments
            $this->db->not_like('pr.transactionid', 'RCPT-', 'after');
            $this->db->not_like('pr.note', 'via Receipt #');
            $this->db->not_like('pr.note', 'Applied from Receipt #');

            $info = $this->db->get()->row();
            if (!empty($info->sum_amount)) {
                $total_income += $info->sum_amount;
            }

        } else {

            // Legacy: core payments (invoicepaymentrecords)
            $info = $this->db->select('SUM( amount ) as sum_amount')
                            ->from(db_prefix().'invoicepaymentrecords')
                            ->where('paymentmode', $payment_mode_id)
                            ->get()
                            ->row();

            if (!empty($info->sum_amount)) {
                $total_income += $info->sum_amount;
            }
        }
        // transfer in
        $info = $this->db->select('SUM( target_amount ) as sum_amount')
                        ->from(db_prefix().'payment_modes_transfer')
                        ->where('target_mode',$payment_mode_id)
                        ->get()
                        ->row();

        if ( !empty( $info->sum_amount ) )
            $total_income += $info->sum_amount;


        // total income
        $info = $this->db->select('SUM( amount ) as sum_amount')
                        ->from(db_prefix().'payment_modes_income')
                        ->where('source_mode',$payment_mode_id)
                        ->get()
                        ->row();

        if ( !empty( $info->sum_amount ) )
            $total_income += $info->sum_amount;



        // total_out
        $info = $this->db->select(' SUM( amount + COALESCE( '.db_prefix().'taxes.taxrate , 0 ) * amount / 100 ) as sum_amount')
                        ->from(db_prefix().'expenses')
                        ->join(db_prefix().'taxes',db_prefix().'expenses.tax = '.db_prefix().'taxes.id','left')
                        ->where('paymentmode',$payment_mode_id)
                        ->get()
                        ->row();

        if ( !empty( $info->sum_amount ) )
            $total_out += $info->sum_amount;


        // transfer_out
        $info = $this->db->select(' SUM( source_amount ) as sum_amount')
                        ->from(db_prefix().'payment_modes_transfer')
                        ->where('source_mode',$payment_mode_id)
                        ->get()
                        ->row();

        if ( !empty( $info->sum_amount ) )
            $total_out += $info->sum_amount;


        // withdraw
        $info = $this->db->select(' SUM( amount ) as sum_amount')
                        ->from(db_prefix().'payment_modes_withdraw')
                        ->where('source_mode',$payment_mode_id)
                        ->get()
                        ->row();

        if ( !empty( $info->sum_amount ) )
            $total_out += $info->sum_amount;

        return [ $total_income , $total_out ];

    }

    public function get_transaction($id = '', $where = [], $include_inactive = false, $force = false){

        if( !empty( $where ) )
            $this->db->where($where);

        if (is_numeric($id)) {
            $this->db->where('id', $id);

            $pay_mode = $this->db->get(db_prefix() . 'payment_modes')->row();

            if ( !empty( $pay_mode->id ) )
            {

                $info = $this->get_payment_account_info( $pay_mode->id  );

                if ( !empty( $info ) )
                {
                    $pay_mode->name               = $pay_mode->name." ( $info->payment_currency )" ;
                    $pay_mode->payment_currency   = $info->payment_currency ;
                    $pay_mode->opening_balance    = $info->opening_balance ;
                    $pay_mode->is_public          = $info->is_public ;
                    $pay_mode->active_staff       = !empty( $info->active_staff ) ? json_decode( $info->active_staff ) : [] ;
                }
                else
                {

                    $get_base_currency = get_base_currency();

                    if ( !empty( $get_base_currency->name ) )
                        $pay_mode->payment_currency = $get_base_currency->name;
                    else
                        $pay_mode->payment_currency   = '' ;

                    $pay_mode->opening_balance    = 0 ;
                    $pay_mode->is_public          = 1 ;
                    $pay_mode->active_staff       = [];
                }

            }

            return $pay_mode;

        }
        elseif (!empty($id))
        {

            foreach ($this->get_payment_gateways(true) as $gateway)
            {
                if ($gateway['id'] == $id) {
                    if ($gateway['active'] == 0 && $force == false) {
                        continue;
                    }

                    // The instance is already object and array_to_object is messing up

                    $currency = "";
                    $settings = $gateway['instance']->getSettings();

                    foreach ( $settings as $setting) {

                        if( $setting["label"] == 'settings_paymentmethod_currencies' )
                        {
                            $currency_str = get_option($setting['name']);

                            if( !empty( $currency_str ) )
                                $currency_arr = explode( ',' , $currency_str );

                            if( !empty( $currency_arr[0] ) )
                                $currency = $currency_arr[0];

                        }

                    }

                    unset($gateway['instance']);

                    $mode = array_to_object($gateway);

                    //$mode->opening_balance  = 0;
                    //$mode->payment_currency = $currency;


                    return $mode;
                }

            }

            $return_default = [
                'id' => '' ,
                'opening_balance' => 0 ,
                'payment_currency' => '' ,
                'name' => '' ,
            ];

            return (object)$return_default;

        }

        if ($include_inactive !== true) {
            $this->db->where('active', 1);
        }

        $payment_modes = $this->db->select("id , payment_currency , name ")
                        ->get(db_prefix() . 'payment_modes')
                        ->result_array();

        $modes = [];

        foreach ( $payment_modes as $ind => $mode )
        {

            $info = $this->get_payment_account_info( $mode['id']  );

            $add_payment_mode = true;

            if ( !empty( $info ) )
            {
                $mode['name']               = $mode['name']." ( $info->payment_currency )" ;
                $mode['payment_currency']   = $info->payment_currency ;
                $mode['opening_balance']    = $info->opening_balance ;
                $mode['is_public']          = $info->is_public ;
                $mode['active_staff']       = !empty( $info->active_staff ) ? json_decode( $info->active_staff ) : [] ;

                $add_payment_mode = $this->payment_mode_is_available_for_staff( $info );

            }
            else
            {
                $mode['payment_currency']   = '' ;
                $mode['opening_balance']    = 0 ;
                $mode['is_public']          = 1 ;
                $mode['active_staff']       = [];
            }


            if ( $add_payment_mode )
                $modes[] = $mode;


        }


        $modes = array_merge( $modes , $this->get_payment_gateways($include_inactive) );

        return $modes;

    }

    public function get_payment_gateways($includeInactive = false)
    {

        if ( is_null( $this->gateways ) )
        {

            hooks()->do_action('before_get_payment_gateways');

            $this->gateways = hooks()->apply_filters('app_payment_gateways', $this->payment_gateways);
        }

        $modes = [];
        foreach ($this->gateways as $mode)
        {

            if ($includeInactive !== true && $mode['active'] == 0)
            {
                continue;
            }

            if (!value_exists_in_array_by_key($modes, 'id', $mode['id']))
            {

                $info = $this->get_payment_account_info( $mode['id'] );

                $add_mode = true;

                if ( !empty( $info ) )
                {
                    $mode['name']               = $mode['name']." ( $info->payment_currency )" ;
                    $mode['payment_currency']   = $info->payment_currency ;
                    $mode['opening_balance']    = $info->opening_balance ;
                    $mode['is_public']          = $info->is_public ;
                    $mode['active_staff']       = !empty( $info->active_staff ) ? json_decode( $info->active_staff , 1 ) : [] ;

                    $add_mode = $this->payment_mode_is_available_for_staff( $info ) ;

                }
                else
                {
                    $mode['payment_currency']   = '' ;
                    $mode['opening_balance']    = 0 ;
                    $mode['is_public']          = 1 ;
                    $mode['active_staff']       = [];
                }


                if ( $add_mode )
                    $modes[] = $mode;


            }

        }

        return $modes;
    }


    /**
     * @Version 1.0.2
     *
     * Payment mode transaction history
     */
    public function transaction_history( $payment_mode_id , $sqlDate = '' )
    {
        
        $ab_charset = $this->ab_get_union_charset();
        $ab_collation = $this->ab_get_union_collation();
// date filter variants per table
        $sqlDate2        = str_replace( "DATE(date)" , "DATE(transfer_date)" , $sqlDate );
        $sqlDate_receipt = str_replace( "DATE(date)" , "DATE(payment_date)" , $sqlDate );

        // ===== Payments / Receipts =====
        if ($this->use_paymentsonaccount_receipts()) {

            $hasBridge = $this->db->table_exists(db_prefix().'receipt_invoice_applications');

            // Receipts (primary)
            $payment_sql = " ( SELECT 1 AS type ,
                                    payment_date as date ,
                                    total_amount as amount ,
                                    id as record_id ,
                                    CONVERT(note USING {$ab_charset}) COLLATE {$ab_collation} as description ,
                                    client_id as clientid ,
                                    CONVERT(receipt_number USING {$ab_charset}) COLLATE {$ab_collation} as ref_no ,
                                    CONVERT((SELECT company FROM ".db_prefix()."clients c WHERE c.userid = client_id) USING {$ab_charset}) COLLATE {$ab_collation} as client_name
                               FROM ".db_prefix()."receipts
                               WHERE payment_mode = '$payment_mode_id' AND $sqlDate_receipt
                             ) ";

            // Core payments fallback (B): only those NOT linked to a receipt and NOT internal allocations from receipts
            $bridgeJoin = $hasBridge ? " LEFT JOIN ".db_prefix()."receipt_invoice_applications a ON a.payment_record_id = pr.id " : "";
            $bridgeCond = $hasBridge ? " AND a.id IS NULL " : "";

            $fallback_core_payment_sql = " ( SELECT 7 AS type ,
                                                pr.date ,
                                                pr.amount ,
                                                pr.id as record_id ,
                                                CAST(pr.invoiceid AS CHAR CHARACTER SET {$ab_charset}) COLLATE {$ab_collation} as description ,
                                                (
                                                    SELECT clientid
                                                    FROM ".db_prefix()."invoices inv
                                                    WHERE inv.id = pr.invoiceid
                                                ) as clientid ,
                                                CONVERT(CONCAT('#', pr.id) USING {$ab_charset}) COLLATE {$ab_collation} as ref_no ,
                                                CONVERT((
                                                    SELECT company
                                                    FROM ".db_prefix()."clients c
                                                    WHERE c.userid = (
                                                        SELECT clientid
                                                        FROM ".db_prefix()."invoices inv2
                                                        WHERE inv2.id = pr.invoiceid
                                                    )
                                                ) USING {$ab_charset}) COLLATE {$ab_collation} as client_name
                                           FROM ".db_prefix()."invoicepaymentrecords pr
                                           LEFT JOIN ".db_prefix()."receipts r ON r.source_payment_id = pr.id
                                           $bridgeJoin
                                           WHERE pr.paymentmode = '$payment_mode_id'
                                             AND $sqlDate
                                             AND r.id IS NULL
                                             $bridgeCond
                                             AND pr.transactionid NOT LIKE 'RCPT-%'
                                             AND (pr.note IS NULL OR (pr.note NOT LIKE '%via Receipt #%' AND pr.note NOT LIKE '%Applied from Receipt #%'))
                                         ) ";

        } else {

            // Legacy core payments
            $payment_sql = " ( SELECT 1 AS type , date ,
                                    amount ,
                                    id as record_id ,
                                    CAST(invoiceid AS CHAR CHARACTER SET {$ab_charset}) COLLATE {$ab_collation} as description ,
                                    (
                                        SELECT clientid
                                        FROM ".db_prefix()."invoices inv
                                        WHERE inv.id = invoiceid
                                    ) as clientid ,
                                    CAST(NULL AS CHAR CHARACTER SET {$ab_charset}) COLLATE {$ab_collation} as ref_no ,
                                    CAST(NULL AS CHAR CHARACTER SET {$ab_charset}) COLLATE {$ab_collation} as client_name
                               FROM ".db_prefix()."invoicepaymentrecords
                               WHERE paymentmode = '$payment_mode_id' AND $sqlDate
                             ) ";

            $fallback_core_payment_sql = ""; // not used
        }

        // ===== Expense / Transfer / Withdraw / Income =====
        $expenses_sql = " ( SELECT 2 AS type , date ,
                                ( ( amount + COALESCE( ".db_prefix()."taxes.taxrate , 0 ) * amount / 100 ) ) as amount ,
                                ".db_prefix()."expenses.id as record_id ,
                                CONVERT(expense_name USING {$ab_charset}) COLLATE {$ab_collation} as description ,
                                clientid ,
                                CAST(NULL AS CHAR CHARACTER SET {$ab_charset}) COLLATE {$ab_collation} as ref_no ,
                                CAST(NULL AS CHAR CHARACTER SET {$ab_charset}) COLLATE {$ab_collation} as client_name
                            FROM ".db_prefix()."expenses
                            LEFT JOIN ".db_prefix()."taxes ON ".db_prefix()."expenses.tax = ".db_prefix()."taxes.id
                            WHERE paymentmode = '$payment_mode_id' AND $sqlDate
                          ) ";

        $transferin_sql  = " ( SELECT 3 AS type , transfer_date as date , target_amount as amount , id as record_id , CAST(source_mode AS CHAR CHARACTER SET {$ab_charset}) COLLATE {$ab_collation} as description , NULL as clientid , CAST(NULL AS CHAR CHARACTER SET {$ab_charset}) COLLATE {$ab_collation} as ref_no , CAST(NULL AS CHAR CHARACTER SET {$ab_charset}) COLLATE {$ab_collation} as client_name
                               FROM ".db_prefix()."payment_modes_transfer
                               WHERE target_mode = '$payment_mode_id' AND $sqlDate2
                             ) ";

        $transferout_sql = " ( SELECT 4 AS type , transfer_date as date , source_amount as amount , id as record_id , CAST(target_mode AS CHAR CHARACTER SET {$ab_charset}) COLLATE {$ab_collation} as description , NULL as clientid , CAST(NULL AS CHAR CHARACTER SET {$ab_charset}) COLLATE {$ab_collation} as ref_no , CAST(NULL AS CHAR CHARACTER SET {$ab_charset}) COLLATE {$ab_collation} as client_name
                               FROM ".db_prefix()."payment_modes_transfer
                               WHERE source_mode = '$payment_mode_id' AND $sqlDate2
                             ) ";

        $withdraw_sql = " ( SELECT 5 AS type , date , amount , id as record_id , CAST(source_mode AS CHAR CHARACTER SET {$ab_charset}) COLLATE {$ab_collation} as description , client_id AS clientid , CAST(NULL AS CHAR CHARACTER SET {$ab_charset}) COLLATE {$ab_collation} as ref_no , CAST(NULL AS CHAR CHARACTER SET {$ab_charset}) COLLATE {$ab_collation} as client_name
                            FROM ".db_prefix()."payment_modes_withdraw
                            WHERE source_mode = '$payment_mode_id' AND $sqlDate
                          ) ";

        $income_sql = " ( SELECT 6 AS type , date , amount , id as record_id , CAST(source_mode AS CHAR CHARACTER SET {$ab_charset}) COLLATE {$ab_collation} as description , client_id AS clientid , CAST(NULL AS CHAR CHARACTER SET {$ab_charset}) COLLATE {$ab_collation} as ref_no , CAST(NULL AS CHAR CHARACTER SET {$ab_charset}) COLLATE {$ab_collation} as client_name
                          FROM ".db_prefix()."payment_modes_income
                          WHERE source_mode = '$payment_mode_id' AND $sqlDate
                        ) ";

        $sql = " SELECT *
                 FROM (
                    $payment_sql
                    " . (!empty($fallback_core_payment_sql) ? "UNION ALL $fallback_core_payment_sql" : "") . "
                    UNION ALL
                    $expenses_sql
                    UNION ALL
                    $transferin_sql
                    UNION ALL
                    $transferout_sql
                    UNION ALL
                    $withdraw_sql
                    UNION ALL
                    $income_sql
                 ) as tbl
                 ORDER BY tbl.date ";

        return $this->db->query($sql)->result();
    }


    /**
     * @Version  1.0.4
     */
    public function check_payment_account()
    {

        $payments = $this->db->select('id,opening_balance,payment_currency')->from(db_prefix().'payment_modes')->get()->result();

        if ( !empty( $payments ) )
        {

            $table_name = db_prefix().'payment_modes_accounts';

            foreach ( $payments as $payment )
            {

                $info = $this->get_payment_account_info( $payment->id );

                if ( empty( $info ) )
                {
                    $this->db->insert($table_name , [
                        'payment_id'        => $payment->id ,
                        'payment_currency'  => $payment->payment_currency ,
                        'opening_balance'   => $payment->opening_balance ,
                        'is_public'         => 1
                    ]);
                }

            }

        }

    }


    public function get_payment_account_info( $payment_id )
    {

        $table_name = db_prefix().'payment_modes_accounts';

        return $this->db->select('*')->from($table_name)->where('payment_id',$payment_id)->get()->row();

    }

    public function payment_mode_is_available_for_staff( $info )
    {

        if ( is_admin() )
            return true;

        $add_mode = false;


        if ( $info->is_public == 1 )
        {

            $add_mode = true;

        }
        elseif ( !empty( $info->active_staff ) )
        {

            $active_staff = json_decode( $info->active_staff , 1 ) ;

            if ( !empty( $active_staff ) )
            {

                if ( in_array( get_staff_user_id() , $active_staff ) )
                    $add_mode = true;

            }

        }

        return $add_mode;

    }

    /**
     * Payment method list hidden to staff
     *
     */
    public function get_hidden_payment_modes_for_staff()
    {

        if ( is_admin() )
            return [];

        $modes = $this->db->select('active_staff , payment_id')->from(db_prefix().'payment_modes_accounts')->where('is_public',0)->get()->result();

        if ( empty( $modes ) )
            return [];

        $payment_modes = [] ;

        $staff_id = get_staff_user_id();

        foreach ( $modes as $mode )
        {

            if ( !empty( $mode->active_staff ) )
            {

                $active_staff = json_decode( $mode->active_staff , 1 ) ;

                if ( !empty( $active_staff ) )
                {

                    if ( !in_array( $staff_id , $active_staff ) )
                        $payment_modes[] = $mode->payment_id ;

                }
                else
                    $payment_modes[] = $mode->payment_id ;

            }
            else
                $payment_modes[] = $mode->payment_id ;

        }


        return $payment_modes;

    }

    public function get_payment_mode_info( $payment_mode , $mode_id )
    {

        $mode_info = [ 'name' => '' , 'payment_currency' => '' ];

        if ( !empty( $mode_id ) && !empty( $payment_mode[ $mode_id ] ) )
        {

            $mode_info = $payment_mode[ $mode_id ];

        }

        return array_to_object( $mode_info );

    }


    /**
     * Checking the database tables
     *
     * @return void
     */
    public function check_the_db()
    {

        $CI = &get_instance();

        if (!$CI->db->field_exists('opening_balance', db_prefix() . 'payment_modes'))
        {

            $CI->db->query("ALTER TABLE `".db_prefix()."payment_modes`
                            ADD COLUMN `opening_balance` decimal(15, 2) NULL AFTER `active`,
                            ADD COLUMN `payment_currency`  varchar(10) DEFAULT 'TRY' AFTER `opening_balance` " );

        }



        if (!$CI->db->table_exists(db_prefix() . 'payment_modes_transfer'))
        {

            $CI->db->query("CREATE TABLE `".db_prefix()."payment_modes_transfer` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                      `source_mode` varchar(100) DEFAULT NULL,
                      `target_mode` varchar(100) DEFAULT NULL,
                      `source_amount` decimal(15,2) DEFAULT NULL,
                      `target_amount` decimal(15,2) DEFAULT NULL,
                      `date` datetime DEFAULT NULL,
                      `staffid` int(11) DEFAULT NULL,
                      `description` varchar(500) DEFAULT NULL,
                      `transfer_date` date DEFAULT NULL,
                      PRIMARY KEY (`id`) USING BTREE,
                      KEY `id` (`id`) USING BTREE,
                      KEY `source_mode` (`source_mode`) USING BTREE,
                      KEY `target_mode` (`target_mode`) USING BTREE
                    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;"
            );

        }



        if (!$CI->db->table_exists(db_prefix() . 'payment_modes_withdraw'))
        {

            $CI->db->query("CREATE TABLE `".db_prefix()."payment_modes_withdraw` (
                          `id` int(11) NOT NULL AUTO_INCREMENT,
                          `source_mode` varchar(100) DEFAULT NULL,
                          `client_id` int(11) DEFAULT NULL,
                          `staff_id` int(11) DEFAULT NULL,
                          `amount` decimal(15,2) DEFAULT NULL,
                          `date` date DEFAULT NULL,
                          `description` varchar(500) DEFAULT NULL,
                          `added_from` int(11) DEFAULT NULL,
                          `added_date` datetime DEFAULT NULL,
                          PRIMARY KEY (`id`),
                          KEY `client_id` (`client_id`),
                          KEY `staff_id` (`staff_id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;"
            );

        }


        # Version 1.0.2

        if (!$CI->db->table_exists(db_prefix() . 'payment_modes_income'))
        {
            $CI->db->query("CREATE TABLE `".db_prefix()."payment_modes_income` (
                         `id` int(11) NOT NULL AUTO_INCREMENT,
                          `source_mode` varchar(100) DEFAULT NULL,
                          `client_id` int(11) DEFAULT NULL,
                          `staff_id` int(11) DEFAULT NULL,
                          `amount` decimal(15,2) DEFAULT NULL,
                          `date` date DEFAULT NULL,
                          `description` varchar(500) DEFAULT NULL,
                          `added_from` int(11) DEFAULT NULL,
                          `added_date` datetime DEFAULT NULL,
                          PRIMARY KEY (`id`) USING BTREE,
                          KEY `client_id` (`client_id`) USING BTREE,
                          KEY `staff_id` (`staff_id`) USING BTREE
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;"
            );
        }

        # Version 1.0.4

        if (!$CI->db->table_exists(db_prefix() . 'payment_modes_accounts'))
        {

            $CI->db->query("CREATE TABLE `".db_prefix()."payment_modes_accounts` (
                          `id` int(11) NOT NULL AUTO_INCREMENT,
                          `payment_id` varchar(100) DEFAULT NULL,
                          `payment_currency` varchar(10) DEFAULT NULL,
                          `opening_balance` decimal(15,2) DEFAULT 0.00,
                          `is_public` tinyint(4) DEFAULT 1,
                          `active_staff` varchar(255) DEFAULT NULL,
                          PRIMARY KEY (`id`),
                          KEY `payment_id` (`payment_id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;"
            );

            $this->check_payment_account();

        }


    }

}
