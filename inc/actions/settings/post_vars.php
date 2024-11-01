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
 * Implements the helper function for grabbing post variables from the settings page.
 */

if(!function_exists('wordpatch_settings_post_vars')) {
    /**
     * Calculate the current post variables for the settings form.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_settings_post_vars($wpenv_vars) {
        $current = array();

        // Calculate the language then override with post data if it is valid.
        $current['language'] = wordpatch_calculate_language($wpenv_vars);

        if(isset($_POST['wordpatch_language']) && wordpatch_is_valid_language($_POST['wordpatch_language'])) {
            $current['language'] = $_POST['wordpatch_language'];
        }

        // Calculate the mode then override with post data if it is valid.
        $current['mode'] = wordpatch_calculate_mode($wpenv_vars);

        if(isset($_POST['wordpatch_mode']) && wordpatch_is_valid_mode($_POST['wordpatch_mode'])) {
            $current['mode'] = $_POST['wordpatch_mode'];
        }

        // Calculate the timer then override with post data if it is valid.
        $current['timer'] = wordpatch_calculate_timer($wpenv_vars);

        if(isset($_POST['wordpatch_timer']) && wordpatch_is_valid_timer($_POST['wordpatch_timer'])) {
            $current['timer'] = $_POST['wordpatch_timer'];
        }

        // Calculate the maintenance mode then override with post data if it is valid.
        $current['maintenance_mode'] = wordpatch_calculate_maintenance_mode($wpenv_vars);

        if(isset($_POST['wordpatch_maintenance_mode']) && wordpatch_is_valid_maintenance_mode($_POST['wordpatch_maintenance_mode'])) {
            $current['maintenance_mode'] = $_POST['wordpatch_maintenance_mode'];
        }

        // Calculate the retry count then override with post data if it is valid.
        $current['retry_count'] = wordpatch_calculate_retry_count($wpenv_vars);

        if(isset($_POST['wordpatch_retry_count']) && wordpatch_is_valid_retry_count($_POST['wordpatch_retry_count'])) {
            $current['retry_count'] = $_POST['wordpatch_retry_count'];
        }

        // Calculate the update cooldown then override with post data if it is valid.
        $current['update_cooldown'] = wordpatch_calculate_update_cooldown($wpenv_vars);

        if(isset($_POST['wordpatch_update_cooldown']) && wordpatch_is_valid_update_cooldown($_POST['wordpatch_update_cooldown'])) {
            $current['update_cooldown'] = $_POST['wordpatch_update_cooldown'];
        }

        return $current;
    }
}