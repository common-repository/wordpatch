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
 * Implements the health functionality. This essentially checks if the rescue script is up and running.
 */

if(!function_exists('wordpatch_health_url')) {
    /**
     * Calculate an absolute URL to the rescue health action. This must go directly through the rescue script.
     *
     * @param $wpenv_vars
     * @return null|string
     */
    function wordpatch_health_url($wpenv_vars)
    {
        $rescue_path = wordpatch_env_get($wpenv_vars, 'rescue_path');

        if(!$rescue_path) {
            return null;
        }

        $base_url = wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_site_url')) . $rescue_path;
        return wordpatch_add_query_params($base_url, 'action=' . WORDPATCH_WHERE_HEALTH);
    }
}

if(!function_exists('wordpatch_features_health')) {
    /**
     * Returns features supported by 'health'. See full list of options inside `inc/actions.php`.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_features_health($wpenv_vars)
    {
        return array(WORDPATCH_FEATURE_PREFACE);
    }
}

if(!function_exists('wordpatch_preface_health')) {
    /**
     * Implements the preface for the health action. This must be accessed via rescue mode or an error will response instead.
     * The whole purpose of this endpoint is to ensure the rescue script is generally healthy enough to run a work request.
     *
     * @param $wpenv_vars
     */
    function wordpatch_preface_health($wpenv_vars) {
        if(!isset($wpenv_vars['rescue_mode']) || !$wpenv_vars['rescue_mode']) {
            // TODO: Maybe divulge detail here but it really isn't necessary.
            die(json_encode(array(
                'error' => WORDPATCH_UNKNOWN_ERROR
            )));
        }

        $calculate_rescue_path = wordpatch_sanitize_unix_file_path(wordpatch_calculate_rescue_path($wpenv_vars), true, true);
        $wpenv_rescue_path = isset($wpenv_vars['rescue_path']) ? $wpenv_vars['rescue_path'] : '';
        $wpenv_rescue_path = wordpatch_sanitize_unix_file_path($wpenv_rescue_path, true, true);

        // Is the rescue script healthy?
        // TODO: Simple additional ways to check: Ensure the mysql and filesystem are properly homed.

        $health_error = null;

        if($calculate_rescue_path === '' || $wpenv_rescue_path === '' || $calculate_rescue_path !== $wpenv_rescue_path) {
            $health_error = WORDPATCH_INVALID_RESCUE_PATH;
        }

        die(json_encode(array(
            'error' => $health_error
        )));
    }
}