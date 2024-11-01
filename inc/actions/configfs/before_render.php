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
 * The following functions perform the pre-rendering logic for each step of the filesystem configuration wizard.
 * These functions are called from `wordpatch_render_configfs`.
 */
if(!function_exists('wordpatch_configfs_before_render')) {
    /**
     * Performs the pre-rendering logic for each step of the filesystem configuration wizard.
     * Returns an array containing a list of errors to be passed into `wordpatch_configfs_render_form`.
     *
     * PS: This function simply calls the appropriate `wordpatch_configfs_before_render_{$step_key}`, see those for more
     * information.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $step_number
     * @param $steps
     * @return array
     */
    function wordpatch_configfs_before_render($wpenv_vars, $current, $step_number, $steps) {
        // Create a result array.
        $before_errors = array();

        // Call the proper `before_render` function based on the step number.
        switch($step_number) {
            case $steps['dirs']:
                $before_errors = wordpatch_configfs_before_render_dirs($wpenv_vars, $current);
                break;
            case $steps['validate']:
                $before_errors = wordpatch_configfs_before_render_validate($wpenv_vars, $current);
                break;
        }

        // Return our result array.
        return $before_errors;
    }
}

if(!function_exists('wordpatch_configfs_before_render_dirs')) {
    /**
     * Performs the pre-rendering logic for the directories step of the filesystem configuration wizard.
     * Called by `wordpatch_configfs_before_render`. See for more information.
     *
     * @param $wpenv_vars
     * @param $current
     * @return array
     */
    function wordpatch_configfs_before_render_dirs($wpenv_vars, $current) {
        // Create a result array.
        $before_errors = array();

        // Test the filesystem connection.
        $connect_check = wordpatch_filesystem_begin($wpenv_vars, $current['fs_method'], '',
            '', '', $current['ftp_pubkey'], $current['ftp_prikey'], $current['ftp_user'], $current['ftp_pass'],
            $current['ftp_host'], $current['ftp_ssl'], $current['fs_chmod_file'], $current['fs_chmod_dir'],
            $current['fs_timeout']);

        // Append an error to our result array if we were unable to connect to the filesystem.
        if(!$connect_check) {
            $before_errors[] = WORDPATCH_INVALID_FILESYSTEM_CREDENTIALS;
        }

        // End our filesystem connection.
        wordpatch_filesystem_end();

        // Return the result array.
        return $before_errors;
    }
}

if(!function_exists('wordpatch_configfs_before_render_validate')) {
    /**
     * Performs the pre-rendering logic for the validation step of the filesystem configuration wizard.
     * Called by `wordpatch_configfs_before_render`. See for more information.
     *
     * @param $wpenv_vars
     * @param $current
     * @return array
     */
    function wordpatch_configfs_before_render_validate($wpenv_vars, $current) {
        $fs_test = wordpatch_filesystem_test($wpenv_vars, $current['fs_method'], $current['ftp_base'],
            $current['ftp_content_dir'], $current['ftp_plugin_dir'], $current['ftp_pubkey'], $current['ftp_prikey'],
            $current['ftp_user'], $current['ftp_pass'], $current['ftp_host'], $current['ftp_ssl'], $current['fs_chmod_file'],
            $current['fs_chmod_dir'], $current['fs_timeout']);

        return $fs_test['error_list'];
    }
}