<?php
defined('BASEPATH') or exit('No direct script access allowed');

define('WEBHOOK_CRON', true);

class Webhook_cron_model extends App_Model
{
    public $manually = false;
    public function __construct()
    {
        parent::__construct();
    }

    public function run($manually = false)
    {
        hooks()->do_action('before_webhook_cron_run', $manually);

        update_option('last_webhook_cron_run', time());

        if ($manually == true) {
            $this->manually = true;
            log_activity('Webhook Cron Invoked Manually');
        }

        $this->db->where("scheduled_at < ", "NOW()", false);
        $this->db->where_in("status", ["PENDING"]);
        $res = $this->db->get("scheduled_webhooks");
        $cron_data = $res->result();

        foreach ($cron_data as $webhook) {
            call_webhook(json_decode($webhook->request_data), $webhook->rel_type, $webhook->action, $webhook->rel_id, $webhook->secondary_id, $webhook->id);
        }

        hooks()->do_action('after_webhook_cron_run', $manually);
    }
}
