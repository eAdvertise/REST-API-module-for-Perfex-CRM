<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once __DIR__ . '/../third_party/node.php';

/**
 * One-click self-update (v3)
 *
 * Daily, fire-and-forget version check against the licensing server plus an
 * admin-triggered one-click update:
 *  - download only over HTTPS from the licensing host (host pinned - MITM/SSRF guard)
 *  - concurrent-update lock (option-based, self-expiring)
 *  - version downgrade guard (checked twice: advertised + extracted package)
 *  - package sanity check before any file is touched
 *  - OPcache reset after the swap so shared hosts serve the new code
 */
class Api_Self_Update
{
    const OPT_INFO       = 'api_update_info';
    const OPT_LAST_CHECK = 'api_update_last_check';
    const OPT_LOCK       = 'api_update_lock';
    const LOCK_SECONDS   = 600;
    const CHECK_INTERVAL = 86400;

    private $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    /** Current installed version, parsed from the api.php module header. */
    public function currentVersion()
    {
        return self::versionFromModuleFile($this->moduleDir() . '/api.php');
    }

    /** Parse "Version: X.Y.Z" from a module bootstrap file. */
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

    /** TRUE when the advertised version is a real upgrade over current. */
    public static function isUpgrade($current, $latest)
    {
        if (!is_string($current) || !is_string($latest) || $current === '' || $latest === '') {
            return false;
        }
        return version_compare($latest, $current, '>');
    }

    /** Download URLs must be HTTPS on the licensing host (pinned). */
    public static function isTrustedDownloadUrl($url)
    {
        $parts = parse_url((string)$url);
        if (empty($parts['scheme']) || strtolower($parts['scheme']) !== 'https' || empty($parts['host'])) {
            return false;
        }
        $trusted = parse_url(UPDATE_INFO_POINT);
        return isset($trusted['host']) && strcasecmp($parts['host'], $trusted['host']) === 0;
    }

    /**
     * Daily (or forced) update-info check. Never throws; stores the result in
     * options and returns it (or the cached copy).
     */
    public function checkForUpdate($force = false)
    {
        try {
            $last = (int)get_option(self::OPT_LAST_CHECK);
            if (!$force && (time() - $last) < self::CHECK_INTERVAL) {
                $cached = json_decode((string)get_option(self::OPT_INFO), true);
                return is_array($cached) ? $cached : null;
            }
            $this->setOption(self::OPT_LAST_CHECK, time());

            $payload = [
                'module'          => 'api',
                'version'         => (string)$this->currentVersion(),
                'domain'          => function_exists('site_url') ? site_url() : '',
                'verification_id' => (string)get_option('api_verification_id'),
            ];

            $ch = curl_init(UPDATE_INFO_POINT);
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => http_build_query($payload),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 5,
                CURLOPT_CONNECTTIMEOUT => 3,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_FOLLOWLOCATION => false,
            ]);
            $body = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($body === false || $code !== 200) {
                return null;
            }
            $info = json_decode($body, true);
            if (!is_array($info) || empty($info['latest_version'])) {
                return null;
            }

            $info['checked_at'] = time();
            $this->setOption(self::OPT_INFO, json_encode($info));
            return $info;
        } catch (Exception $e) {
            return null;
        }
    }

    /** Info for the admin UI: current, latest, whether an update is ready. */
    public function status($forceCheck = false)
    {
        $current = $this->currentVersion();
        $info    = $this->checkForUpdate($forceCheck);
        $latest  = is_array($info) && isset($info['latest_version']) ? $info['latest_version'] : null;

        return [
            'current'          => $current,
            'latest'           => $latest,
            'update_available' => self::isUpgrade($current, (string)$latest),
            'checked_at'       => is_array($info) && isset($info['checked_at']) ? (int)$info['checked_at'] : null,
        ];
    }

    /**
     * Perform the one-click update. Returns ['success' => bool, 'message' => ...].
     */
    public function runUpdate()
    {
        // Concurrent-update lock (self-expiring)
        $lock = (int)get_option(self::OPT_LOCK);
        if ($lock > 0 && (time() - $lock) < self::LOCK_SECONDS) {
            return ['success' => false, 'message' => 'An update is already in progress. Try again in a few minutes.'];
        }
        $this->setOption(self::OPT_LOCK, time());

        $tmpZip = null;
        $tmpDir = null;
        try {
            $current = $this->currentVersion();
            $info    = $this->checkForUpdate(true);

            if (!is_array($info) || empty($info['latest_version']) || empty($info['download_url'])) {
                throw new Exception('No update information available from the update server.');
            }
            if (!self::isUpgrade($current, $info['latest_version'])) {
                throw new Exception('Already up to date (installed ' . $current . ', latest ' . $info['latest_version'] . ').');
            }
            if (!self::isTrustedDownloadUrl($info['download_url'])) {
                throw new Exception('Download URL rejected: only HTTPS downloads from the update server are allowed.');
            }
            if (!class_exists('ZipArchive')) {
                throw new Exception('The PHP zip extension is required for one-click updates.');
            }

            // Download
            $tmpZip = tempnam(sys_get_temp_dir(), 'api_upd');
            $fh     = fopen($tmpZip, 'wb');
            $ch     = curl_init($info['download_url']);
            curl_setopt_array($ch, [
                CURLOPT_FILE           => $fh,
                CURLOPT_TIMEOUT        => 120,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_FOLLOWLOCATION => false,
            ]);
            $ok   = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err  = curl_error($ch);
            curl_close($ch);
            fclose($fh);

            if (!$ok || $code !== 200 || filesize($tmpZip) < 1024) {
                throw new Exception('Download failed' . ($err ? ': ' . $err : ' (HTTP ' . $code . ')'));
            }

            // Extract
            $tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'api_update_' . bin2hex(random_bytes(6));
            mkdir($tmpDir, 0755, true);
            $zip = new ZipArchive();
            if ($zip->open($tmpZip) !== true) {
                throw new Exception('Downloaded package is not a valid ZIP archive.');
            }
            $zip->extractTo($tmpDir);
            $zip->close();

            // Locate the module root inside the package (api.php marker)
            $sourceDir = $this->findModuleRoot($tmpDir);
            if ($sourceDir === null) {
                throw new Exception('Package sanity check failed: api.php not found inside the update.');
            }

            // Second downgrade guard against the actual package contents
            $packageVersion = self::versionFromModuleFile($sourceDir . '/api.php');
            if (!self::isUpgrade($current, (string)$packageVersion)) {
                throw new Exception('Package version (' . $packageVersion . ') is not newer than the installed version (' . $current . ').');
            }

            // Swap: overwrite-copy the package over the live module directory
            $this->copyTree($sourceDir, $this->moduleDir());

            // Make shared hosts serve the new code immediately
            if (function_exists('opcache_reset')) {
                @opcache_reset();
            }

            return [
                'success' => true,
                'message' => 'Updated from ' . $current . ' to ' . $packageVersion . '.',
                'from'    => $current,
                'to'      => $packageVersion,
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        } finally {
            if ($tmpZip !== null && is_file($tmpZip)) {
                @unlink($tmpZip);
            }
            if ($tmpDir !== null && is_dir($tmpDir)) {
                $this->removeTree($tmpDir);
            }
            $this->setOption(self::OPT_LOCK, 0);
        }
    }

    // ------------------------------------------------------------------

    private function moduleDir()
    {
        return dirname(__DIR__);
    }

    /** Find the directory inside the extracted package that holds api.php. */
    private function findModuleRoot($dir)
    {
        if (is_file($dir . '/api.php')) {
            return $dir;
        }
        foreach (['/api', '/modules/api'] as $candidate) {
            if (is_file($dir . $candidate . '/api.php')) {
                return $dir . $candidate;
            }
        }
        $entries = @scandir($dir) ?: [];
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            if (is_dir($dir . '/' . $entry) && is_file($dir . '/' . $entry . '/api.php')) {
                return $dir . '/' . $entry;
            }
        }
        return null;
    }

    private function copyTree($source, $destination)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {
            $target = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathname();
            if ($item->isDir()) {
                if (!is_dir($target)) {
                    mkdir($target, 0755, true);
                }
            } else {
                copy($item->getPathname(), $target);
            }
        }
    }

    private function removeTree($dir)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $item) {
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }
        @rmdir($dir);
    }

    private function setOption($name, $value)
    {
        if (function_exists('update_option') && update_option($name, $value)) {
            return;
        }
        if (function_exists('add_option')) {
            add_option($name, $value);
        }
    }
}
