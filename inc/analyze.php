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
 * Implements the shared patch analyze functionality.
 */

if(!function_exists('wordpatch_analyze_api_request')) {
    function wordpatch_analyze_api_request($wpenv_vars, $patches) {
        $patches_b64 = array();

        foreach($patches as $pk => $pv) {
            $patches_b64[$pk] = base64_encode($pv);
        }

        $post = array(
            'patches' => $patches_b64
        );

        $api_url = wordpatch_build_api_url('v1/wordpatch/analyze');
        $result = wordpatch_do_protected_http_request($wpenv_vars, $api_url, $post, $http_error);

        if($http_error) {
            return array(
                'error' => $http_error
            );
        }

        return $result;
    }
}