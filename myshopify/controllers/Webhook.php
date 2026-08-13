<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Webhook extends App_Controller
{
    public function index()
    {
        $raw = (string) $this->input->raw_input_stream;
        $secret = (string) get_option('my_shopify_webhook_secret');
        $signature = (string) $this->input->get_request_header('X-Shopify-Hmac-Sha256', true);

        if ($secret === '' || $signature === '' || !hash_equals(base64_encode(hash_hmac('sha256', $raw, $secret, true)), $signature)) {
            return $this->output->set_status_header(401)->set_output('Invalid signature');
        }

        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            return $this->output->set_status_header(400)->set_output('Invalid JSON');
        }

        $topic = strtolower((string) $this->input->get_request_header('X-Shopify-Topic', true));
        try {
            $this->load->library('myshopify/Myshopify_sync_service');
            $result = $this->myshopify_sync_service->handleWebhook($topic, $payload);
            hooks()->do_action('myshopify_webhook_processed', [
                'topic'        => $topic,
                'shop_domain'  => (string) $this->input->get_request_header('X-Shopify-Shop-Domain', true),
                'webhook_id'   => (string) $this->input->get_request_header('X-Shopify-Webhook-Id', true),
                'api_version'  => (string) $this->input->get_request_header('X-Shopify-Api-Version', true),
                'processed_at' => date('c'),
                'result'       => $result,
                'payload'      => $payload,
            ]);
            return $this->output->set_status_header(200)->set_output('OK');
        } catch (Throwable $e) {
            log_message('error', 'MyShopify webhook failed (' . $topic . '): ' . $e->getMessage());
            return $this->output->set_status_header(500)->set_output('Sync failed');
        }
    }
}
