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
 * Implements the database helper functions for the new patch page.
 */

if(!function_exists('wordpatch_newpatch_persist_db')) {
    /**
     * Persists the database record for the new patch form. Returns an error string or null on success.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $job_id
     * @param $patch_id
     * @param $location
     * @param $location_old
     * @param $location_new
     * @return null|string
     */
    function wordpatch_newpatch_persist_db($wpenv_vars, $current, $job_id, $patch_id, $location, $location_old, $location_new) {
        // Sanitize and validate the job ID.
        $job_id = wordpatch_sanitize_unique_id($job_id);

        if($job_id === '') {
            return WORDPATCH_INVALID_JOB;
        }

        // Sanitize and validate the patch ID.
        $patch_id = wordpatch_sanitize_unique_id($patch_id);

        if($patch_id === '') {
            return WORDPATCH_INVALID_PATCH;
        }

        // Calculate the patches table name.
        $patches_table = wordpatch_patches_table($wpenv_vars);

        // Determine the best sort order value for our new patch.
        $sort_order = wordpatch_patches_next_sort_order($wpenv_vars, $job_id);

        // Escape our variables for the query.
        $esc_job_id = wordpatch_esc_sql($wpenv_vars, $job_id);
        $esc_patch_id = wordpatch_esc_sql($wpenv_vars, $patch_id);
        $esc_title = wordpatch_esc_sql($wpenv_vars, base64_encode($current['patch_title']));
        $esc_patch_type = wordpatch_esc_sql($wpenv_vars, $current['patch_type']);
        $esc_patch_location = wordpatch_esc_sql($wpenv_vars, $location);
        $esc_patch_path = wordpatch_esc_sql($wpenv_vars, $current['patch_type'] === wordpatch_patch_type_simple() ?
            base64_encode($current['patch_path']) : '');
        $esc_patch_location_old = wordpatch_esc_sql($wpenv_vars, $current['patch_type'] === wordpatch_patch_type_simple() ? $location_old : '');
        $esc_patch_location_new = wordpatch_esc_sql($wpenv_vars, $current['patch_type'] === wordpatch_patch_type_simple() ? $location_new : '');
        $esc_patch_size = wordpatch_esc_sql($wpenv_vars, strlen($current['patch_data']));
        $esc_sort_order = wordpatch_esc_sql($wpenv_vars, $sort_order);

        // Calculate our insert query for the patch.
        $insertquery = "INSERT INTO $patches_table (`id`, `job_id`, `title`, `patch_type`, `patch_location`, " .
            "`patch_path`, `patch_location_old`, `patch_location_new`, `patch_size`, `sort_order`) " .
            "VALUES ('$esc_patch_id', '$esc_job_id', '$esc_title', '$esc_patch_type', '$esc_patch_location', " .
            "'$esc_patch_path', '$esc_patch_location_old', '$esc_patch_location_new', '$esc_patch_size', '$esc_sort_order')";

        // Try to insert the record.
        $result = wordpatch_db_query($wpenv_vars, $insertquery);

        // If the query failed, then return an error.
        if(!$result) {
            // TODO: Nothing we can do about this, but it'd be nice to delete the patch off the filesystem considering
            // this is basically a failure.

            return WORDPATCH_UNKNOWN_DATABASE_ERROR;
        }

        return null;
    }
}