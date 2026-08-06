<?php if (count(get_included_files()) == 1) exit('No direct script access allowed');

class WarehouseLic
{
    private $current_version = 'v1.0.0';

    public function __construct()
    {
    }

    public function check_local_license_exist()
    {
        return true;
    }

    public function get_current_version()
    {
        return $this->current_version;
    }

    public function check_connection()
    {
        return true;
    }

    public function get_latest_version()
    {
        return $this->current_version;
    }

    public function activate_license($license, $client, $create_lic = true)
    {
        return ['status' => true, 'message' => 'License verification disabled for this fork.'];
    }

    public function verify_license($time_based_check = false, $license = false, $client = false)
    {
        return ['status' => true, 'message' => 'License verification disabled for this fork.'];
    }

    public function deactivate_license($license = false, $client = false)
    {
        return ['status' => true, 'message' => 'License verification disabled for this fork.'];
    }

    public function check_update()
    {
        return ['status' => false, 'message' => 'Vendor self-updates are disabled for this fork.'];
    }

    public function download_update($update_id, $type, $version, $license = false, $client = false, $db_for_import = false)
    {
        return ['status' => false, 'message' => 'Vendor self-updates are disabled for this fork.'];
    }
}
