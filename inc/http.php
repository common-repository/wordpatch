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

if(!function_exists('wordpatch_get_response_code_from_http_headers')) {
    function wordpatch_get_response_code_from_http_headers($response_headers) {
        foreach($response_headers as $header_line) {
            if(!preg_match("/^HTTP\\/[0-9\\.]+\\s+([0-9]+)/", $header_line, $pattern_matches)) {
                continue;
            }

            return max(0, (int)$pattern_matches[1]);
        }

        return 0;
    }
}

if(!function_exists('wordpatch_do_http_request')) {
    /**
     * This function sends an HTTP request with post data and simply returns the result.
     * If there is an error in the network-layer, there will be a null result and $error_output will be populated with
     * either WORDPATCH_UNKNOWN_HTTP_ERROR or WORDPATCH_UNAUTHORIZED_HTTP_ERROR.
     * @param string $url
     * @param string $post_data
     * @param $error_output
     * @return string|null
     */
    function wordpatch_do_http_request($url, $post_data, &$error_output) {
        if(function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
            curl_setopt($ch, CURLOPT_TIMEOUT, 120);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/octet-stream',
                    'Content-Length: ' . strlen($post_data))
            );

            // TODO: @spacenerd Certificate pinning
            // TODO: CURLOPT_PINNEDPUBLICKEY
            // TODO: Do we need to worry about CURLOPT_CAINFO or CURLOPT_SSLCERT?
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            $result = curl_exec($ch);

            $curl_error = curl_errno($ch);
            $http_code = 0;

            if(!$curl_error) {
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            }

            curl_close($ch);

            if($curl_error) {
                $error_output = WORDPATCH_UNKNOWN_HTTP_ERROR;
                return null;
            }

            if($http_code === 401) {
                $error_output = WORDPATCH_UNAUTHORIZED_HTTP_ERROR;
                return $result;
            }

            if($http_code !== 200) {
                $error_output = WORDPATCH_UNKNOWN_HTTP_ERROR;
                return null;
            }

            $error_output = null;
            return $result;
        }

        // TODO: Cert pinning

        // use key 'http' even if you send the request to https://...
        // TODO: @spacenerd Add some options to make cert pinning work (see: http://php.net/manual/en/context.ssl.php)
        $options = array(
            'http' => array(
                'header'  => "Content-Type: application/octet-stream\r\nContent-Length: " . strlen($post_data) . "\r\n",
                'method'  => 'POST',
                'timeout' => 15,
                'content' => $post_data,
                'ignore_errors' => true,
            ),
        );

        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if(!$result || !is_string($result)) {
            $error_output = WORDPATCH_UNKNOWN_HTTP_ERROR;
            return null;
        }

        /**
         * @var $http_response_header - Magic variable: http://php.net/manual/en/reserved.variables.httpresponseheader.php
         */
        $http_code = wordpatch_get_response_code_from_http_headers($http_response_header);

        if($http_code === 401) {
            $error_output = WORDPATCH_UNAUTHORIZED_HTTP_ERROR;
            return $result;
        }

        if($http_code !== 200) {
            $error_output = WORDPATCH_UNKNOWN_HTTP_ERROR;
            return null;
        }

        $error_output = null;
        return $result;
    }
}

if(!function_exists('wordpatch_do_protected_http_request')) {
// function for handling further requests post activation (ie. patch)
    function wordpatch_do_protected_http_request($wpenv_vars, $url, $post_data, &$error_output)
    {
        $default_error = WORDPATCH_UNKNOWN_HTTP_ERROR;

        $apiEngine = WordpatchCrypto_get($wpenv_vars, "WordpatchRSACryptoEngine", array('api'));

        if ($apiEngine === null) {
            $error_output = WORDPATCH_NO_LICENSE;
            return null;
        }

        $internalId = $apiEngine->getIdentity($wpenv_vars, 'internal');
        $remoteId = $apiEngine->getIdentity($wpenv_vars, 'remote');

        if ($internalId === null || $remoteId === null) {
            $error_output = WORDPATCH_NO_LICENSE;
            return null;
        }

        $licenseKey = trim(wordpatch_get_option($wpenv_vars, 'wordpatch_license_key', ''));

        if ($licenseKey === '' || $licenseKey === false || $licenseKey === null) {
            $error_output = WORDPATCH_NO_LICENSE;
            return null;
        }

        // Serialize and sign the request data.
        $requestData = json_encode($post_data);
        $sigData = $internalId->sign($wpenv_vars, $requestData);

        // Form the internal request.
        $internalRequest = array(
            'data' => base64_encode($requestData),
            'sig' => base64_encode($sigData),
            'key' => $licenseKey
        );

        // Encode the final request.
        $finalRequestData = json_encode($internalRequest);

        $responseStr = wordpatch_do_http_request($url, $finalRequestData, $httpError);

        if ($httpError === WORDPATCH_UNAUTHORIZED_HTTP_ERROR) {
            $responseData = trim($responseStr) === '' ? array() : json_decode($responseStr);

            $actual_error = (isset($responseData->error) && $responseData->error) ?
                $responseData->error->message : WORDPATCH_NO_LICENSE;

            $error_output = $actual_error;

            if($actual_error === WORDPATCH_INACTIVE_LICENSE || $actual_error === WORDPATCH_NO_LICENSE) {
                wordpatch_update_option($wpenv_vars, 'wordpatch_license_inactive', '1');
            }

            wordpatch_filesystem_end();

            return $responseData;
        }

        wordpatch_update_option($wpenv_vars, 'wordpatch_license_inactive', '0');

        if ($httpError === WORDPATCH_UNKNOWN_HTTP_ERROR) {
            $error_output = $default_error;
            wordpatch_filesystem_end();
            return null;
        }

        // Parse the response.
        $response = wordpatch_json_decode($responseStr);

        if (!$response || $response === null || !isset($response['sig']) || !isset($response['data'])) {
            $error_output = $default_error;
            return null;
        }

        // Verify the authenticity.
        $decodedData = base64_decode($response['data'], true);
        $decodedSig = base64_decode($response['sig'], true);

        if (!$decodedData || !$decodedSig) {
            $error_output = $default_error;
            return null;
        }

        if (!$remoteId->verify($wpenv_vars, $decodedData, $decodedSig)) {
            $error_output = $default_error;
            return null;
        }

        // Parse the final response.
        $response = wordpatch_json_decode($decodedData);

        $error_output = null;
        return $response;
    }
}

if(!function_exists('wordpatch_build_api_url')) {
    function wordpatch_build_api_url($action) {
        return WORDPATCH_API_URL . $action;
    }
}

if(!isset($__wordpatch_doing_ajax)) {
    $__wordpatch_doing_ajax = false;
}

if(!function_exists('wordpatch_set_doing_ajax')) {
    function wordpatch_set_doing_ajax($doing_ajax)
    {
        global $__wordpatch_doing_ajax;
        $__wordpatch_doing_ajax = $doing_ajax;
    }
}

if(!function_exists('wordpatch_simple_ajax_check')) {
    function wordpatch_simple_ajax_check()
    {
        if(defined('DOING_AJAX') && constant('DOING_AJAX')) {
            return true;
        }

        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            return true;
        }

        if(isset($_POST['is_ajax']) && $_POST['is_ajax'] === '1') {
            return true;
        }

        return false;
    }
}

if(!function_exists('wordpatch_doing_ajax')) {
    function wordpatch_doing_ajax() {
        global $__wordpatch_doing_ajax;
        return $__wordpatch_doing_ajax;
    }
}

if(!function_exists('wordpatch_get_request_uri'))  {
    /**
     * Calculate the current request URI.
     *
     * @param $strip_query_string
     * @return string
     */
    function wordpatch_get_request_uri($strip_query_string)
    {
        $request_uri = $_SERVER['REQUEST_URI'];

        if(!$strip_query_string) {
            return $request_uri;
        }

        $query_pos = strpos($request_uri, '?');

        if($query_pos === false) {
            return $request_uri;
        }

        return substr($request_uri, 0, $query_pos);
    }
}