<?php
/**
 * Copyright (C) 2018 yours! Ltd
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

/**
 * Implements the functionality related to WordPatch license activation.
 */

if(!function_exists('wordpatch_activate_license')) {
    function wordpatch_activate_license($wpenv_vars, $activation_key) {
        $error_list = array();

        // Purge the current license and grab the API engine using the second parameter.
        $purge_errors = wordpatch_purge_license($wpenv_vars, $apiEngine);

        // If we were unable to purge the license, append the error and return early.
        if(!wordpatch_no_errors($purge_errors)) {
            $error_list[] = WORDPATCH_PURGE_LICENSE_FAILED;
            return $error_list;
        }

        $fs_begin = wordpatch_filesystem_begin_helper($wpenv_vars);

        if(!$fs_begin) {
            $error_list[] = WORDPATCH_INVALID_FILESYSTEM_CREDENTIALS;
            return $error_list;
        }

        $default_error = WORDPATCH_UNKNOWN_HTTP_ERROR;

        $activation_key = wordpatch_get_final_activation_key($activation_key);

        // Create the standard remote identity.
        $wpApiPublicKey = WORDPATCH_PUBLIC_KEY;
        $stdRemoteIdentity = $apiEngine->storeIdentity($wpenv_vars, 'stdremote', $wpApiPublicKey);

        // Create a new internal identity.
        $internalApiIdentity = $apiEngine->createIdentity($wpenv_vars, 'internal', true, true);

        if ($stdRemoteIdentity === null || $internalApiIdentity === null) {
            $error_list[] = WORDPATCH_UNKNOWN_DATABASE_ERROR;
            wordpatch_filesystem_end();
            return $error_list;
        }

        // Construct the full rescue URL
        $full_rescue_url = wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_site_url')) .
            wordpatch_env_get($wpenv_vars, 'rescue_path');

        // Form the request
        $request = array(
            'activation_key' => $activation_key,
            'rescue_url' => $full_rescue_url,
            'public_vector' => base64_encode($internalApiIdentity->public),
            'product_key' => WORDPATCH_PRODUCT_KEY
        );

        // Cleanup the identity.
        $internalApiIdentity->cleanup($wpenv_vars);

        // Serialize and encrypt the request.
        $requestStr = json_encode($request);
        $encryptedRequest = $stdRemoteIdentity->encrypt($wpenv_vars, $requestStr);

        $apiUrl = wordpatch_build_api_url('v1/installation');

        /** @var $httpError **/
        $responseStr = wordpatch_do_http_request($apiUrl, $encryptedRequest, $httpError);

        if($httpError === WORDPATCH_UNAUTHORIZED_HTTP_ERROR) {
            $responseData = json_decode($responseStr);

            $error_list[] = (isset($responseData->error) && $responseData->error) ?
                $responseData->error->message : WORDPATCH_BAD_LICENSE;

            wordpatch_filesystem_end();

            return $error_list;
        }

        if($httpError === WORDPATCH_UNKNOWN_HTTP_ERROR) {
            $error_list[] = $default_error;
            wordpatch_filesystem_end();
            return $error_list;
        }

        // Decrypt and parse the response.
        $decryptedResponse = $internalApiIdentity->decrypt($wpenv_vars, $responseStr);

        if ($decryptedResponse === null || empty($decryptedResponse)) {
            $error_list[] = $default_error;
            wordpatch_filesystem_end();
            return $error_list;
        }

        $response = wordpatch_json_decode($decryptedResponse);

        if (!$response || $response === null || !isset($response['sig']) || !isset($response['data'])) {
            $error_list[] = $default_error;
            wordpatch_filesystem_end();
            return $error_list;
        }

        // Verify the authenticity.
        $decodedData = base64_decode($response['data'], true);
        $decodedSig = base64_decode($response['sig'], true);

        if (!$decodedData || !$decodedSig) {
            $error_list[] = $default_error;
            wordpatch_filesystem_end();
            return $error_list;
        }

        if (!$stdRemoteIdentity->verify($wpenv_vars, $decodedData, $decodedSig)) {
            $error_list[] = $default_error;
            wordpatch_filesystem_end();
            return $error_list;
        }

        // Parse the final response.
        $response = wordpatch_json_decode($decodedData);

        if (isset($response['error']) && $response['error']) {
            $error_list[] = $response['error']['message'];
            wordpatch_filesystem_end();
            return $error_list;
        }

        // Create a new remote identity based on what the server sent us.
        $apiEngine->storeIdentity($wpenv_vars, 'remote', base64_decode($response['public_vector']));

        // Store the generated license key.
        $license_key = $response['installation_key'];
        $license_expiry = $response['installation_expiry'] === null ? null : max(0, (int)$response['installation_expiry']);

        if($license_expiry === null || $license_expiry <= 0) {
            $license_expiry = 0;
        }

        wordpatch_update_option($wpenv_vars, 'wordpatch_license_key', $license_key);
        wordpatch_update_option($wpenv_vars, 'wordpatch_license_expiry', $license_expiry);

        wordpatch_filesystem_end();

        return $error_list;
    }
}

if(!function_exists('wordpatch_purge_license')) {
    /**
     * Purge the current WordPatch license. Returns a list of errors or an empty array on success.
     * Optionally outputs the API engine to the parameter $api_engine_output.
     *
     * @param $wpenv_vars
     * @param $api_engine_output
     * @return array
     */
    function wordpatch_purge_license($wpenv_vars, &$api_engine_output = false) {
        // Initialize an array for our error list.
        $purge_errors = array();

        // Connect to the filesystem.
        $fs_begin = wordpatch_filesystem_begin_helper($wpenv_vars);

        // If we are unable to connect, append the appropriate error and return early.
        if(!$fs_begin) {
            $purge_errors[] = WORDPATCH_INVALID_FILESYSTEM_CREDENTIALS;
            return $purge_errors;
        }

        // Store an empty license key and expiry into the database.
        wordpatch_update_option($wpenv_vars, 'wordpatch_license_key', '');
        wordpatch_update_option($wpenv_vars, 'wordpatch_license_expiry', '0');

        // Try to grab the API crypto engine.
        $apiEngine = WordpatchCrypto_get($wpenv_vars, "WordpatchRSACryptoEngine", array('api'));

        // If the engine is null, then append an appropriate error and return early.
        if($apiEngine === null) {
            $purge_errors[] = WORDPATCH_CRYPTO_ENGINE_ERROR;

            // Output the API engine to our parameter if it is not false.
            if($api_engine_output !== false) {
                $api_engine_output = null;
            }

            // Disconnect from the filesystem.
            wordpatch_filesystem_end();
            return $purge_errors;
        }

        // Output the API engine to our parameter if it is not false.
        if($api_engine_output !== false) {
            $api_engine_output = $apiEngine;
        }

        // Purge everything.
        $apiEngine->purge($wpenv_vars);

        // Disconnect from the filesystem.
        wordpatch_filesystem_end();

        // Return our result.
        return $purge_errors;
    }
}

if(!function_exists('wordpatch_license_details')) {
    /**
     * Calculates the current license details. This does not actually check if the license is still active.
     * If you want to check if a license seems active, check out `wordpatch_license_is_activate`.
     *
     * PS: If you need to ensure a license is truly still active, you must perform an API call and check for expiry.
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_license_details($wpenv_vars) {
        // Initialize an array for our result.
        $details = array(
            'key' => null,
            'expiry' => null
        );

        // Calculate the details.
        $details['key'] = trim(wordpatch_get_option($wpenv_vars, 'wordpatch_license_key', ''));
        $details['expiry'] = max(0, (int)wordpatch_get_option($wpenv_vars, 'wordpatch_license_expiry', '0'));

        // Return our result array.
        return $details;
    }
}

if(!function_exists('wordpatch_license_is_active')) {
    /**
     * Checks if the current installation has an active WordPatch installation.
     *
     * @param $wpenv_vars
     * @return bool
     */
    function wordpatch_license_is_active($wpenv_vars) {
        // Calculate the current timestamp.
        $timestamp = wordpatch_utc_timestamp();

        // Calculate the license details.
        $details = wordpatch_license_details($wpenv_vars);

        // If the key or the expiry is not found, we should just return false.
        if(!isset($details['key']) || !isset($details['expiry']) ||
            $details['key'] === null || $details['expiry'] === null ||
            $details['key'] === '' || !is_int($details['expiry'])) {
            return false;
        }

        // The license has expired so just return false.
        if($details['expiry'] <= $timestamp) {
            return false;
        }

        // Return true if we get to this point.
        return true;
    }
}

if(!function_exists('wordpatch_get_final_activation_key')) {
    function wordpatch_get_final_activation_key($activationKey)
    {
        // XXXX-XXXX-XXXX-XXXX-XXXX
        $len = min(strlen($activationKey), 20);
        $block_count = ceil($len / 4);

        $display_key = '';
        $first = true;

        for($block_idx = 0; $block_idx < $block_count; ++$block_idx) {
            if(!$first) {
                $display_key .= '-';
            }

            $display_key .= substr($activationKey, $block_idx * 4, 4);

            $first = false;
        }

        return strtoupper($display_key);
    }
}