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

if(!function_exists('wordpatch_version_api_request')) {
    /**
     * Get the latest version number from the API.
     *
     * @return string
     */
    function wordpatch_version_api_request() {
        $api_url = wordpatch_build_api_url('v1/versions/wordpatch');
        $result = wordpatch_do_http_request($api_url, WORDPATCH_VERSION, $http_error);

        if ($http_error !== null || empty($result)) {
            return WORDPATCH_VERSION;
        }

        $data = json_decode($result, true);


        if ($data === null) {
            return WORDPATCH_VERSION;
        }

        if (!isset($data['data']) || !isset($data['data']['version']) || empty($data['data']['version'])) {
            return WORDPATCH_VERSION;
        }

        return $data['data']['version'];
    }
}