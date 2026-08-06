<?php defined('BASEPATH') || exit('No direct script access allowed');

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../third_party/node.php';
use Firebase\JWT\JWT as api_JWT;
use Firebase\JWT\Key as api_Key;
use WpOrg\Requests\Requests as api_Requests;

class api_aeiou
{
    public function validatePurchase($module_name)
    {
        $module          = get_instance()->app_modules->get($module_name);
        $verified        = false;
        $verification_id =  get_option($module_name . '_verification_id');

        return true;

        if (!empty($verification_id)) {
            $verification_id = base64_decode($verification_id);
        }

        $id_data = explode('|', $verification_id);
        $token   = get_option($module_name . '_product_token');

        if (4 == count($id_data)) {
            $verified = !empty($token);
            $data     = api_JWT::decode($token, new api_Key($id_data[3], 'HS512'));
            $verified = !empty($data)
                && basename($module['headers']['uri']) == $data->item_id
                && $data->item_id == $id_data[0]
                && $data->buyer == $id_data[2]
                && $data->purchase_code == $id_data[3];

            $seconds           = $data->check_interval ?? 0;
            $last_verification = (int) get_option($module_name . '_last_verification');

            if (!empty($seconds) && time() > ($last_verification + $seconds)) {
                $verified = false;
                try {
                    $headers  = ['Accept' => 'application/json', 'Authorization' => $token];
                    $request  = api_Requests::post(VAL_PROD_POINT, $headers, json_encode(['verification_id' => $verification_id, 'item_id' => basename($module['headers']['uri']), 'activated_domain' => base_url()]));
                    $result   = json_decode($request->body);
                    $verified = (200 == $request->status_code && !empty($result->valid));
                } catch (\Throwable $e) {
                    $verified = true;
                }
                update_option($module_name.'_last_verification', time());
            }

            if (empty($token) || !$verified) {
                $last_verification = (int) get_option($module_name . '_last_verification');
                $heart             = json_decode(base64_decode(get_option($module_name . '_heartbeat')));
                $verified          = (!empty($heart) && ($last_verification + (168 * (3000 + 600))) > time()) ?? false;
            }

            if (!$verified) {
                get_instance()->app_modules->deactivate($module_name);
            }

            return $verified;
        }
    }
}
