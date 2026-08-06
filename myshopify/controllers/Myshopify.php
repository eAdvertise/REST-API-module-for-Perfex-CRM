<?php

defined('BASEPATH') or exit('No direct script access allowed');
use PHPShopify\ShopifySDK;

class Myshopify extends AdminController
{
    protected $access_token;
    protected $shop_url;

    public function __construct()
    {
        parent::__construct();
        $this->load->model(['shopify_model','settings_model']);


        // Load Shopify credentials from settings
        $this->access_token = get_option('my_shopify_access_token');
        $this->shop_url = get_option('my_shopify_url');
        
    }

    /**
     * Display the list of imported Shopify products.
     */
    public function products()
    {
        if(get_option('myshopify_purchase_is_valid') != 1){
            set_alert('danger', 'MyShopify verification are missing. Please verify them first.');
            redirect(admin_url('myshopify/verify'));
        }
        $data['products'] = $this->shopify_model->get_products();
        $this->load->view('shopify_import_view', $data);
    }

    /**
     * Display the list of imported Shopify customers.
     */
    public function customers()
    {
        if(get_option('myshopify_purchase_is_valid') != 1){
            set_alert('danger', 'MyShopify verification are missing. Please verify them first.');
            redirect(admin_url('myshopify/verify'));
        }
        $data['customers'] = $this->shopify_model->get_customers();
        $this->load->view('customers', $data);
    }

    /**
     * Display the list of imported Shopify discount codes.
     */
    public function discounts()
    {
        if(get_option('myshopify_purchase_is_valid') != 1){
            set_alert('danger', 'MyShopify verification are missing. Please verify them first.');
            redirect(admin_url('myshopify/verify'));
        }
        $data['title'] = 'Shopify Discount Codes';
        $data['discounts'] = $this->shopify_model->get_discounts();
        $this->load->view('myshopify/discounts', $data);
    }

    /**
     * Display the list of imported Shopify orders.
     */
    public function orders()
    {
        if(get_option('myshopify_purchase_is_valid') != 1){
            set_alert('danger', 'MyShopify verification are missing. Please verify them first.');
            redirect(admin_url('myshopify/verify'));
        }
        $data['orders'] = $this->shopify_model->get_orders();
        $this->load->view('orders', $data);
    }

    /**
     * Display detailed information for a specific Shopify order.
     *
     * @param int $id Shopify order ID
     */
    public function order_details($id)
    {
        $order = $this->shopify_model->get_order_by_id($id);

        if (!$order) {
            show_404();
        }

        $data['order'] = $order;
        $data['title'] = 'Shopify Order Details';
        $this->load->view('order_details', $data);
    }

    /**
     * Display the list of imported Shopify product categories.
     */
    public function categories()
    {
        if(get_option('myshopify_purchase_is_valid') != 1){
            set_alert('danger', 'MyShopify verification are missing. Please verify them first.');
            redirect(admin_url('myshopify/verify'));
        }
        $data['categories'] = $this->shopify_model->get_categories();
        $this->load->view('shopify_category', $data);
    }
    /**
     * Import all Shopify products via API and store in database.
     */
    public function import()
    {
        if (!$this->access_token || !$this->shop_url) {
            set_alert('danger', 'Shopify settings are missing. Please configure them first.');
            redirect(admin_url());
        }
        $shopify = new ShopifySDK([
            'ShopUrl' => $this->shop_url,
            'AccessToken' => $this->access_token,
        ]);

        $products = $shopify->Product->get();

        foreach ($products as $product) {
            $this->shopify_model->import_product($product);
        }

        set_alert('success', 'Shopify products imported successfully!');
        redirect(admin_url('myshopify/products'));
    }

    /**
     * Import all Shopify customers using pagination and store them.
     */
    public function import_customers()
    {
        if (!$this->access_token || !$this->shop_url) {
            set_alert('danger', 'Shopify settings are missing. Please configure them first.');
            redirect(admin_url());
        }
        $endpoint = "https://{$this->shop_url}/admin/api/2025-04/customers.json?limit=250";
        $all_customers = [];

        try {
            do {
                // Perform paginated request
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $endpoint);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Content-Type: application/json",
                    "X-Shopify-Access-Token: {$this->access_token}"
                ]);
                curl_setopt($ch, CURLOPT_HEADER, true);
                $response = curl_exec($ch);

                $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                $header = substr($response, 0, $header_size);
                $body = substr($response, $header_size);

                curl_close($ch);

                $json = json_decode($body, true);
                if (!empty($json['customers'])) {
                    $all_customers = array_merge($all_customers, $json['customers']);
                }

                // Get next page link
                if (preg_match('/<([^>]+)>; rel="next"/', $header, $matches)) {
                    $endpoint = $matches[1];
                } else {
                    $endpoint = null;
                }

            } while ($endpoint);

            if (!empty($all_customers)) {
                $this->shopify_model->import_customers($all_customers);
                set_alert('success', 'All customers imported successfully.');
            } else {
                set_alert('warning', 'No customers found.');
            }

        } catch (Exception $e) {
            set_alert('danger', 'Import failed: ' . $e->getMessage());
        }

        redirect(admin_url('myshopify/customers'));
    }

    /**
     * Import all Shopify orders with pagination and store them.
     */
    public function import_orders()
    {
        if (!$this->access_token || !$this->shop_url) {
            set_alert('danger', 'Shopify settings are missing. Please configure them first.');
            redirect(admin_url());
        }
        $endpoint = "https://{$this->shop_url}/admin/api/2025-04/orders.json?limit=250";
        $all_orders = [];

        try {
            do {
                // Perform paginated request
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $endpoint);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Content-Type: application/json",
                    "X-Shopify-Access-Token: {$this->access_token}"
                ]);
                curl_setopt($ch, CURLOPT_HEADER, true);
                $response = curl_exec($ch);

                $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                $header = substr($response, 0, $header_size);
                $body = substr($response, $header_size);

                curl_close($ch);

                $json = json_decode($body, true);
                if (!empty($json['orders'])) {
                    $all_orders = array_merge($all_orders, $json['orders']);
                }

                if (preg_match('/<([^>]+)>; rel="next"/', $header, $matches)) {
                    $endpoint = $matches[1];
                } else {
                    $endpoint = null;
                }

            } while ($endpoint);

            // Save orders
            if (!empty($all_orders)) {
                foreach ($all_orders as $order) {
                    $exists = $this->db->where('shopify_order_id', $order['id'])->get('tblmyshopify_orders')->row();
                    if ($exists) {
                        continue;
                    }

                    $data = [
                        'shopify_order_id' => $order['id'],
                        'order_number' => $order['order_number'],
                        'customer_email' => $order['email'] ?? '',
                        'total_price' => $order['total_price'],
                        'currency' => $order['currency'],
                        'financial_status' => $order['financial_status'],
                        'created_at' => $order['created_at'],
                        'shipping_name' => $order['shipping_address']['name'] ?? '',
                        'shipping_company' => $order['shipping_address']['company'] ?? '',
                        'shipping_city' => $order['shipping_address']['city'] ?? '',
                        'shipping_province' => $order['shipping_address']['province'] ?? '',
                        'shipping_country' => $order['shipping_address']['country'] ?? '',
                        'shipping_zip' => $order['shipping_address']['zip'] ?? '',
                        'shipping_phone' => $order['shipping_address']['phone'] ?? '',
                        'billing_name' => $order['billing_address']['name'] ?? '',
                        'billing_company' => $order['billing_address']['company'] ?? '',
                        'billing_city' => $order['billing_address']['city'] ?? '',
                        'billing_province' => $order['billing_address']['province'] ?? '',
                        'billing_country' => $order['billing_address']['country'] ?? '',
                        'billing_zip' => $order['billing_address']['zip'] ?? '',
                        'billing_phone' => $order['billing_address']['phone'] ?? '',
                        'subtotal_price' => $order['subtotal_price'],
                        'total_tax' => $order['total_tax'],
                        'total_discounts' => $order['total_discounts'] ?? 0,
                        'line_items' => json_encode($order['line_items']),
                    ];

                    $this->db->insert('tblmyshopify_orders', $data);
                }

                set_alert('success', 'All orders imported successfully.');
            } else {
                set_alert('warning', 'No orders found.');
            }

        } catch (Exception $e) {
            set_alert('danger', 'Import failed: ' . $e->getMessage());
        }

        redirect(admin_url('myshopify/orders'));
    }

    /**
     * Import Shopify collections (custom and smart) as categories.
     */
    public function import_categories()
    {
        if (!$this->access_token || !$this->shop_url) {
            set_alert('danger', 'Shopify settings are missing. Please configure them first.');
            redirect(admin_url());
        }
        $shopify = new ShopifySDK([
            'ShopUrl' => $this->shop_url,
            'AccessToken' => $this->access_token,
        ]);

        try {
            $custom_collections = $shopify->CustomCollection()->get([
                'limit' => 250,
                'fields' => 'id,title,image'
            ]);

            $smart_collections = $shopify->SmartCollection()->get([
                'limit' => 250,
                'fields' => 'id,title,image'
            ]);

            $categories = array_merge($custom_collections, $smart_collections);

            if (!empty($categories)) {
                $this->shopify_model->import_categories($categories);
                set_alert('success', 'Categories Imported Successfully');
            } else {
                set_alert('warning', 'No Categories Found');
            }
        } catch (Exception $e) {
            set_alert('danger', 'Shopify API Error: ' . $e->getMessage());
        }

        redirect(admin_url('myshopify/categories'));
    }

    /**
     * Import all Shopify discount codes associated with price rules.
     */
    public function import_discounts()
    {
        if (!$this->access_token || !$this->shop_url) {
            set_alert('danger', 'Shopify settings are missing. Please configure them first.');
            redirect(admin_url());
        }
        $price_rules_endpoint = "https://{$this->shop_url}/admin/api/2025-04/price_rules.json";
        $discount_codes_endpoint = "https://{$this->shop_url}/admin/api/2025-04/price_rules/{id}/discount_codes.json";
        $all_discounts = [];

        try {
            // Fetch price rules
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $price_rules_endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/json",
                "X-Shopify-Access-Token: {$this->access_token}"
            ]);
            $price_rules_response = curl_exec($ch);
            curl_close($ch);

            $price_rules = json_decode($price_rules_response, true)['price_rules'];

            foreach ($price_rules as $rule) {
                $discount_url = str_replace('{id}', $rule['id'], $discount_codes_endpoint);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $discount_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Content-Type: application/json",
                    "X-Shopify-Access-Token: {$this->access_token}"
                ]);
                $discount_response = curl_exec($ch);
                curl_close($ch);

                $discount_codes = json_decode($discount_response, true)['discount_codes'] ?? [];

                foreach ($discount_codes as $code) {
                    $all_discounts[] = [
                        'shopify_discount_id' => $code['id'],
                        'code' => $code['code'],
                        'price_rule_id' => $rule['id'],
                        'title' => $rule['title'],
                        'value' => $rule['value'],
                        'value_type' => $rule['value_type'],
                        'starts_at' => $rule['starts_at'],
                        'ends_at' => $rule['ends_at'],
                        'usage_limit' => $rule['usage_limit'],
                    ];
                }
            }

            if (!empty($all_discounts)) {
                $this->shopify_model->import_discounts($all_discounts);
                set_alert('success', 'Discounts imported successfully.');
            } else {
                set_alert('warning', 'No discounts found.');
            }

        } catch (Exception $e) {
            set_alert('danger', 'Import failed: ' . $e->getMessage());
        }

        redirect(admin_url('myshopify/discounts'));
    }

  /**
     * Verify MyShopify settings.
     *
     * @return void
     */
    public function verify()
    {
        if ($this->input->post()) {
            $post_data = $this->input->post();
            $purchase_code = $post_data['settings']['myshopify_purchase_code'];

            if (myshopifys_verify($purchase_code, MYSHOPIFY_MODULE_NAME)) {
                $post_data['settings']['myshopify_purchase_is_valid'] = 1;
                $success = $this->settings_model->update($post_data);

                set_alert($success ? 'success' : 'danger', $success ? _l('Settings updated') : _l('An error occurred while updating settings'));
            } else {
                set_alert('danger', _l('Purchase code is invalid'));
            }

            redirect(admin_url('myshopify/verify'));
        }

        $data['title'] = _l('Verify MyShopify');
        $this->load->view('admin/verify', $data);
    }

}
