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
 * Implements the shared patch functionality.
 */

if(!function_exists('wordpatch_get_patches_by_job_id')) {
    function wordpatch_get_patches_by_job_id($wpenv_vars, $job_id) {
        // Sanitize our job ID.
        $job_id = wordpatch_sanitize_unique_id($job_id);

        // If the job ID is invalid, simply return an empty array.
        if($job_id === '') {
            return array();
        }

        // Calculate the patches table name.
        $patches_table = wordpatch_patches_table($wpenv_vars);

        // Escape our job ID for the query.
        $esc_job_id = wordpatch_esc_sql($wpenv_vars, $job_id);

        // Create our select query.
        $query = "SELECT * FROM `$patches_table` WHERE `job_id` = '$esc_job_id' ORDER BY `sort_order` ASC";

        // Grab the patches into an array.
        $patches = wordpatch_db_get_results($wpenv_vars, $query);

        // Fix each row to contain ints where necessary.
        foreach($patches as &$patch) {
            wordpatch_fix_patch_row($patch);
        }

        // Return our results array.
        return $patches;
    }
}

if(!function_exists('wordpatch_get_patch_by_id')) {
    function wordpatch_get_patch_by_id($wpenv_vars, $patch_id)
    {
        $patch_id = wordpatch_sanitize_unique_id($patch_id);

        if($patch_id === '') {
            return null;
        }

        $patches_table = wordpatch_patches_table($wpenv_vars);
        $query = "SELECT * FROM $patches_table WHERE `id` = '$patch_id' LIMIT 1";

        $row = wordpatch_db_get_row($wpenv_vars, $query);

        if(!$row) {
            return null;
        }

        wordpatch_fix_patch_row($row);

        return $row;
    }
}

if(!function_exists('wordpatch_patches_next_sort_order')) {
    /**
     * Calculate the new sort order value for the patches table regarding $job_id.
     *
     * @param $wpenv_vars
     * @param $job_id
     * @return int
     */
    function wordpatch_patches_next_sort_order($wpenv_vars, $job_id) {
        // Sanitize our job ID.
        $job_id = wordpatch_sanitize_unique_id($job_id);

        // Default sort order is 1.
        $sort_order = 1;

        // If the job is invalid, just return the default.
        if($job_id === '') {
            return $sort_order;
        }

        // Calculate the patches table name.
        $patches_table = wordpatch_patches_table($wpenv_vars);

        // Escape our job ID for the query.
        $esc_job_id = wordpatch_esc_sql($wpenv_vars, $job_id);

        // Query for the largest sort order in the DB.
        $sortquery = "SELECT `sort_order` FROM `$patches_table` WHERE `job_id` = '$esc_job_id' ORDER BY `sort_order` DESC LIMIT 1";
        $last_sort_order = wordpatch_db_get_var($wpenv_vars, $sortquery);

        // If we found it, take the value and add one.
        if ($last_sort_order) {
            $sort_order = (int)$last_sort_order + 1;
        }

        // Return our calculated sort order value.
        return $sort_order;
    }
}

if(!function_exists('wordpatch_fix_patch_row')) {
    /**
     * Calculates a fixed version of $patch_row which contains proper types where necessary.
     *
     * @param $patch_row
     */
    function wordpatch_fix_patch_row(&$patch_row) {
        $patch_row['sort_order'] = max(0, (int)$patch_row['sort_order']);
        $patch_row['patch_size'] = max(0, (int)$patch_row['patch_size']);
        $patch_row['title'] = ($patch_row['title'] === null || trim($patch_row['title']) === '') ?
            '' : trim(base64_decode($patch_row['title']));
    }
}

if(!function_exists('wordpatch_fix_pending_patch_row')) {
    /**
     * Calculates a fixed version of $pending_patch_row which contains proper types where necessary.
     *
     * @param $pending_patch_row
     */
    function wordpatch_fix_pending_patch_row(&$pending_patch_row) {
        wordpatch_fix_patch_row($pending_patch_row);
    }
}

if(!function_exists('wordpatch_patch_api_request')) {
    function wordpatch_patch_api_request($wpenv_vars, $patches, $relative_file_names, $previous_versions, $patch_types,
                                         $binary_mode_bool) {
        $files = array();

        $file_index = 0;
        foreach($relative_file_names as $relative_file_name) {
            $previous_version_current = $previous_versions[$file_index] === null ?
                null : $previous_versions[$file_index];

            $files[] = array(
                'path' => base64_encode($relative_file_name),
                'data' => $previous_version_current === null ? null : base64_encode($previous_version_current),
            );

            $file_index++;
        }

        $patches_b64 = array();
        foreach($patches as $pk => $pv) {
            $patches_b64[$pk] = base64_encode($pv);
        }

        $post = array(
            'files' => $files,
            'patches' => $patches_b64,
            'binary_mode' => $binary_mode_bool,
            'patch_types' => $patch_types
        );

        $api_url = wordpatch_build_api_url('v1/wordpatch/patch');
        $result = wordpatch_do_protected_http_request($wpenv_vars, $api_url, $post, $http_error);

        return $result;
    }
}

if(!function_exists('wordpatch_get_pending_patches_by_pending_id')) {
    function wordpatch_get_pending_patches_by_pending_id($wpenv_vars, $pending_id) {
        // Get each patch in the job specified
        $pending_patches_table = wordpatch_pending_patches_table($wpenv_vars);

        $esc_pending_id = wordpatch_esc_sql($wpenv_vars, $pending_id);

        $query = "SELECT * FROM `$pending_patches_table` WHERE `pending_id` = '$esc_pending_id' ORDER BY `sort_order` ASC";

        $pending_patches = wordpatch_db_get_results($wpenv_vars, $query);

        foreach($pending_patches as &$pending_patch) {
            wordpatch_fix_pending_patch_row($pending_patch);
        }

        return $pending_patches;
    }
}