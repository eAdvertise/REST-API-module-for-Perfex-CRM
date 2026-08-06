<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Shopify_model extends App_Model
{
    public function get_products()
    {
        return $this->db->get(db_prefix().'myshopify_products')->result_array();
    }
    public function get_customers()
    {
        return $this->db->get(db_prefix().'mymyshopify_customers')->result_array();
    }
    public function get_orders()
    {
        return $this->db->get(db_prefix().'myshopify_orders')->result_array();
    }
    public function get_order_by_id($id)
    {
        return $this->db->get_where(db_prefix().'myshopify_orders', ['id' => $id])->row_array();
    }
    public function get_categories()
    {
        return $this->db->get(db_prefix().'myshopify_categories')->result_array();
    }
    public function get_discounts()
    {
        return $this->db->get(db_prefix().'myshopify_discounts')->result_array();
    }
    public function import_product($product)
    {
        $shopify_id = $product['id'];
        $title = $product['title'];
        $price = $product['variants'][0]['price'];
        $stock = $product['variants'][0]['inventory_quantity'];

        // Get the main product image
        $image_url = !empty($product['image']['src']) ? $product['image']['src'] : NULL;

        // Shopify product link
        $shopify_url = 'https://'.get_option('my_shopify_url')."//products/" . $product['handle'];

        // Check if product exists
        $this->db->where('shopify_id', $shopify_id);
        $exists = $this->db->count_all_results(db_prefix().'myshopify_products');

        if ($exists == 0) {
            // Insert New Product
            $data = [
                'shopify_id' => $shopify_id,
                'title' => $title,
                'price' => $price,
                'stock' => $stock,
                'image_url' => $image_url,
                'shopify_url' => $shopify_url
            ];
            $this->db->insert(db_prefix().'myshopify_products', $data);
        } else {
            // Update Existing Product
            $data = [
                'title' => $title,
                'price' => $price,
                'stock' => $stock,
                'image_url' => $image_url,
                'shopify_url' => $shopify_url
            ];
            $this->db->where('shopify_id', $shopify_id);
            $this->db->update(db_prefix().'myshopify_products', $data);
        }
    }

    public function import_categories($categories)
    {
        foreach ($categories as $category) {
            $shopify_id = $category['id'];
            $name = $category['title'];
    
            // Get category image if available
            $image_url = isset($category['image']['src']) ? $category['image']['src'] : null;
    
            // Check if category exists
            $this->db->where('shopify_id', $shopify_id);
            $exists = $this->db->count_all_results(db_prefix().'myshopify_categories');
    
            if ($exists == 0) {
                // Insert new category
                $data = [
                    'shopify_id' => $shopify_id,
                    'name'        => $name,
                    'image_url'   => $image_url
                ];
                $this->db->insert(db_prefix().'myshopify_categories', $data);
            } else {
                // Update existing category
                $data = [
                    'name'        => $name,
                    'image_url'   => $image_url
                ];
                $this->db->where('shopify_id', $shopify_id);
                $this->db->update(db_prefix().'myshopify_categories', $data);
            }
        }
    }
    

    public function import_customers($customers)
    {
        foreach ($customers as $customer) {
            $data = array(
                'shopify_customer_id' => $customer['id'],
                'first_name' => $customer['first_name'],
                'last_name'  => $customer['last_name'],
                'email'      => $customer['email'],
                'phone'      => $customer['phone'],
                'created_at' => $customer['created_at']
            );
    
            // You might want to check for duplicates before inserting
            $this->db->where('shopify_customer_id', $customer['id']);
            $exists = $this->db->get(db_prefix().'myshopify_customers')->row();
    
            if (!$exists) {
                $this->db->insert(db_prefix().'myshopify_customers', $data);
            }
        }
    }
    public function delete_removed_products($shopify_ids)
    {
        if (empty($shopify_ids)) {
            return;
        }

        // Delete products that are no longer in Shopify
        $this->db->where_not_in('shopify_id', $shopify_ids);
        $this->db->delete(db_prefix().'myshopify_products');
    }
    public function import_orders($orders)
    {
        foreach ($orders as $order) {
            // Skip if already imported
            $exists = $this->db->where('shopify_order_id', $order['id'])
                            ->get(db_prefix().'myshopify_orders')->row();
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

                // Shipping Info
                'shipping_name' => $order['shipping_address']['name'] ?? '',
                'shipping_company' => $order['shipping_address']['company'] ?? '',
                'shipping_city' => $order['shipping_address']['city'] ?? '',
                'shipping_province' => $order['shipping_address']['province'] ?? '',
                'shipping_country' => $order['shipping_address']['country'] ?? '',
                'shipping_zip' => $order['shipping_address']['zip'] ?? '',
                'shipping_phone' => $order['shipping_address']['phone'] ?? '',

                // Billing Info
                'billing_name' => $order['billing_address']['name'] ?? '',
                'billing_company' => $order['billing_address']['company'] ?? '',
                'billing_city' => $order['billing_address']['city'] ?? '',
                'billing_province' => $order['billing_address']['province'] ?? '',
                'billing_country' => $order['billing_address']['country'] ?? '',
                'billing_zip' => $order['billing_address']['zip'] ?? '',
                'billing_phone' => $order['billing_address']['phone'] ?? '',

                // Totals
                'subtotal_price' => $order['subtotal_price'],
                'total_tax' => $order['total_tax'],
                'total_discounts' => $order['total_discounts'] ?? 0,
                'line_items' => json_encode($order['line_items']),
            ];

            $this->db->insert(db_prefix().'myshopify_orders', $data);
        }
    }
    public function import_discounts($discounts)
    {
        foreach ($discounts as $discount) {
            $exists = $this->db->where('shopify_discount_id', $discount['shopify_discount_id'])
                            ->get(db_prefix().'myshopify_discounts')->row();
            if ($exists) continue;

            $this->db->insert(db_prefix().'myshopify_discounts', $discount);
        }
    }


        

}
