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
 * The following functions perform the pre-rendering logic for each step of the database configuration wizard.
 * These functions are called from `wordpatch_render_configdb`.
 */

if(!function_exists('wordpatch_configdb_before_render')) {
    /**
     * Performs the pre-rendering logic for each step of the database configuration wizard.
     * Returns an array containing a list of errors to be passed into `wordpatch_configdb_render_form`.
     *
     * PS: This function simply calls the appropriate `wordpatch_configdb_before_render_{$step_key}`, see those for more
     * information.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $step_number
     * @param $steps
     * @return array
     */
    function wordpatch_configdb_before_render($wpenv_vars, $current, $step_number, $steps) {
        // Create a result array.
        $before_errors = array();

        // Call the proper `before_render` function based on the step number.
        switch($step_number) {
            case $steps['validate']:
                $before_errors = wordpatch_configdb_before_render_validate($wpenv_vars, $current);
                break;
        }

        // Return our result array.
        return $before_errors;
    }
}

if(!function_exists('wordpatch_configdb_before_render_validate')) {
    /**
     * Performs the pre-rendering logic for the validation step of the database configuration wizard.
     * Called by `wordpatch_configdb_before_render`. See for more information.
     *
     * @param $wpenv_vars
     * @param $current
     * @return array
     */
    function wordpatch_configdb_before_render_validate($wpenv_vars, $current) {
        // Create a result array.
        $before_errors = array();

        // Test the database connection.
        $db_connect_check = wordpatch_db_test($wpenv_vars, $current['db_host'], $current['db_user'],
            $current['db_password'], $current['db_name'], $current['db_table_prefix'], $current['db_collate'],
            $current['db_charset']);

        // Extend our result array with any connection errors.
        $before_errors = wordpatch_extend_errors($before_errors, $db_connect_check['error_list']);

        // Return the result array.
        return $before_errors;
    }
}