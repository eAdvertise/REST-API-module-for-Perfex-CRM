<?php

/**
 * Verify the purchase code with the external API.
 *
 * @param string $p_code The purchase code to verify.
 * @return mixed The API response or false on failure.
 */
function myshopifys_verify($p_code,$product)
{
    if (empty($p_code)) {
        return false; // Early return if the purchase code is empty
    }

    $api_url = 'https://verify.hopperstack.com';
    $query_params = http_build_query([
        'purchase_code' => $p_code,
        'url'           => site_url(),
        'item'          => $product
    ]);

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL            => $api_url . '?' . $query_params,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 10, // Set a timeout to avoid long waits
        CURLOPT_SSL_VERIFYPEER  => true, // Ensure SSL verification
    ]);

    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    if (curl_errno($curl)) {
        log_message('error', 'cURL error: ' . curl_error($curl)); // Log any cURL errors
        $response = false;
    } elseif ($http_code !== 200) {
        log_message('error', 'API request failed with HTTP code: ' . $http_code);
        $response = false;
    }

    curl_close($curl);

    return $response;
}
