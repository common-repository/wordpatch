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
 * Implements the database helper functions for the settings page.
 */

if(!function_exists('wordpatch_settings_persist_db')) {
    /**
     * Persists the database records for the settings page.
     *
     * @param $wpenv_vars
     * @param $current
     */
    function wordpatch_settings_persist_db($wpenv_vars, $current) {
        // Store the language.
        wordpatch_update_option($wpenv_vars, 'wordpatch_language', $current['language']);

        // Store the mode.
        wordpatch_update_option($wpenv_vars, 'wordpatch_mode', $current['mode']);

        // Store the timer.
        wordpatch_update_option($wpenv_vars, 'wordpatch_timer', $current['timer']);

        // Store the maintenance mode.
        wordpatch_update_option($wpenv_vars, 'wordpatch_maintenance_mode', $current['maintenance_mode']);

        // Store the retry count.
        wordpatch_update_option($wpenv_vars, 'wordpatch_retry_count', $current['retry_count']);

        // Store the update cooldown.
        wordpatch_update_option($wpenv_vars, 'wordpatch_update_cooldown', $current['update_cooldown']);
    }
}