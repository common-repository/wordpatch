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
 * Implements the request throttler for the work action.
 */

if(!function_exists('wordpatch_work_throttler')) {
    /**
     * Persists the database records for the trash job page. Returns an error or null on success.
     *
     * @param $wpenv_vars
     * @return bool|array
     */
    function wordpatch_work_throttler($wpenv_vars) {
        // Calculate the current timestamp.
        $current_time = wordpatch_utc_timestamp();

        // Check if there is a throttle timestamp stored in the database.
        $throttle_option = wordpatch_get_option($wpenv_vars, WORDPATCH_WORK_THROTTLE_KEY);

        if($throttle_option) {
            // Since the option exists, it will be a timestamp.
            $throttle_option = max(0, (int)$throttle_option);

            // Check if there has not been enough time elapsed since the old timestamp.
            if($throttle_option > 0 && ($current_time < ($throttle_option + WORDPATCH_WORK_THROTTLE_SECONDS))) {
                // Calculate how long the server should wait before trying again.
                $throttle_wait = ($throttle_option + WORDPATCH_WORK_THROTTLE_SECONDS) - $current_time;

                // Dispatch the appropriate header to indicate we have been throttled.
                header("HTTP/1.1 429 Too Many Requests");

                // Construct our response and return it.
                return array(
                    'error' => WORDPATCH_WORK_THROTTLED,
                    'error_translation' => wordpatch_translate_error($wpenv_vars, WORDPATCH_WORK_THROTTLED, WORDPATCH_WHERE_WORK, array()),
                    'throttle_wait' => $throttle_wait
                );
            }
        }

        // By default, we should not be throttled and therefore we should update the option.
        wordpatch_update_option($wpenv_vars, WORDPATCH_WORK_THROTTLE_KEY, $current_time);

        // Return false to indicate no throttling to the work script.
        return false;
    }
}