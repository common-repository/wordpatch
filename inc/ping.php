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
 * Implement the shared ping functionality.
 */

if(!function_exists('wordpatch_ping_api_request')) {
    /**
     * Perform the ping API call which will cause the current installation's rescue work script to be beacon'ed shortly.
     *
     * @param $wpenv_vars
     * @return mixed
     */
    function wordpatch_ping_api_request($wpenv_vars) {
        $post = array();

        $api_url = wordpatch_build_api_url('v1/wordpatch/ping');
        $result = wordpatch_do_protected_http_request($wpenv_vars, $api_url, $post, $http_error);

        return $result;
    }
}