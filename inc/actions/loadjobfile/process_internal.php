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
 * Implements the internal processing functionality for the load job file action.
 */

if(!function_exists('wordpatch_loadjobfile_process_internal')) {
    /**
     * Internal processing for the load job file action. Responsible for validation and loading.
     *
     * @param $wpenv_vars
     * @param $job
     * @param $file_path
     * @param &$file_contents
     * @return array
     */
    function wordpatch_loadjobfile_process_internal($wpenv_vars, $job, $file_path, &$file_contents) {
        // Begin to calculate the error list.
        $error_list = array();

        $job_path = wordpatch_sanitize_unix_file_path(wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_root_dir')) .
            $job['path'], true, false);

        $full_path = wordpatch_sanitize_unix_file_path(wordpatch_trailingslashit($job_path) . $file_path, true, false);

        $file_contents = null;

        if(!@file_exists($full_path)) {
            $error_list[] = WORDPATCH_FILESYSTEM_FAILED_READ;
        } else if(is_dir($full_path)) {
            $error_list[] = WORDPATCH_FILESYSTEM_FILE_IS_DIR;
        } else {
            $file_contents = file_get_contents($full_path);
        }

        // Return our calculated error list
        return $error_list;
    }
}