<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Provider-neutral self-update shim.
 *
 * The original module contacted the vendor/licensing host for update metadata and
 * packages. This fork is maintained directly in this repository, so update checks
 * and one-click vendor updates are intentionally disabled.
 */
class Api_Self_Update
{
    public function currentVersion()
    {
        return self::versionFromModuleFile($this->moduleDir() . '/api.php');
    }

    public static function versionFromModuleFile($file)
    {
        if (!is_file($file)) {
            return null;
        }

        $head = (string)file_get_contents($file, false, null, 0, 2048);
        if (preg_match('/^\s*Version:\s*([0-9]+\.[0-9]+\.[0-9]+)/mi', $head, $m)) {
            return $m[1];
        }

        return null;
    }

    public static function isUpgrade($current, $latest)
    {
        return false;
    }

    public static function isTrustedDownloadUrl($url)
    {
        return false;
    }

    public function checkForUpdate($force = false)
    {
        return null;
    }

    public function status($forceCheck = false)
    {
        return [
            'current'          => $this->currentVersion(),
            'latest'           => null,
            'update_available' => false,
            'checked_at'       => null,
            'disabled'         => true,
            'message'          => 'Vendor self-updates are disabled for this fork.',
        ];
    }

    public function runUpdate()
    {
        return [
            'success' => false,
            'message' => 'Vendor self-updates are disabled for this fork. Deploy repository changes instead.',
        ];
    }

    private function moduleDir()
    {
        return dirname(__DIR__);
    }
}
