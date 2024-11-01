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
 * Implements the database helper functions for the edit job page.
 */

if(!function_exists('wordpatch_editjob_persist_db')) {
    /**
     * Persists the database record for the edit job form. Returns an error string or null on success.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $job_id
     * @return null|string
     */
    function wordpatch_editjob_persist_db($wpenv_vars, $current, $job_id) {
        // Calculate the relevant table names.
        $jobs_table = wordpatch_jobs_table($wpenv_vars);
        $patches_table = wordpatch_patches_table($wpenv_vars);

        // Escape the database fields.
        $esc_job_id = wordpatch_esc_sql($wpenv_vars, $job_id);
        $esc_title = wordpatch_esc_sql($wpenv_vars, base64_encode($current['job_title']));
        $esc_path = wordpatch_esc_sql($wpenv_vars, base64_encode($current['job_path']));
        $esc_maintenance_mode = wordpatch_esc_sql($wpenv_vars, $current['job_maintenance_mode']);
        $esc_binary_mode = wordpatch_esc_sql($wpenv_vars, $current['job_binary_mode']);
        $esc_mode = wordpatch_esc_sql($wpenv_vars, $current['job_mode']);
        $esc_timer = wordpatch_esc_sql($wpenv_vars, $current['job_timer']);
        $esc_update_cooldown = wordpatch_esc_sql($wpenv_vars, $current['job_update_cooldown']);
        $esc_retry_count = wordpatch_esc_sql($wpenv_vars, $current['job_retry_count']);
        $esc_enabled_int = wordpatch_esc_sql($wpenv_vars, $current['job_enabled_int']);

        // Update the basic information
        $updatequery = "UPDATE $jobs_table SET `title` = '$esc_title', `path` = '$esc_path', " .
            "`enabled` = '$esc_enabled_int', `maintenance_mode` = '$esc_maintenance_mode', `binary_mode` = '$esc_binary_mode', " .
            "`mode` = '$esc_mode', `timer` = '$esc_timer', `retry_count` = '$esc_retry_count', `update_cooldown` = '$esc_update_cooldown' " .
            "WHERE `id` = '$esc_job_id'";

        // If there is an error with the query then return an error.
        if(!wordpatch_db_query($wpenv_vars, $updatequery)) {
            return WORDPATCH_UNKNOWN_DATABASE_ERROR;
        }

        // Create an array of patch IDs by exploding the order string by comma.
        $patch_order = explode(',', $current['job_patches']);

        // Calculate the patch count.
        $patch_count = count($patch_order);

        // Loop through each of our patches in the array and perform an update for the order.
        for ($sort_order = 1; $sort_order <= $patch_count; $sort_order++) {
            // Escape the query variables.
            $esc_patch_id = wordpatch_esc_sql($wpenv_vars, $patch_order[$sort_order - 1]);
            $esc_sort_order = wordpatch_esc_sql($wpenv_vars, $sort_order);

            // Perform our update query for the individual patch sort order.
            $insertquery = "UPDATE `$patches_table` SET `sort_order` = '$esc_sort_order' WHERE `id` = '$esc_patch_id'";

            // If there is an error with the query then return an error.
            if(!wordpatch_db_query($wpenv_vars, $insertquery)) {
                return WORDPATCH_UNKNOWN_DATABASE_ERROR;
            }
        }

        // If we got here then everything was completed successfully.
        return null;
    }
}