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
 * Implements the filesystem helper functions for the delete job page.
 */

if(!function_exists('wordpatch_deletejob_persist_fs')) {
    /**
     * Persists the database records for the delete job page. Returns an error or null on success.
     *
     * @param $wpenv_vars
     * @param $job_id
     * @return null|string
     */
    function wordpatch_deletejob_persist_fs($wpenv_vars, $job_id) {
        // Sanitize the job ID first
        $job_id = wordpatch_sanitize_unique_id($job_id);

        // Return an error if the job ID is not valid.
        if ($job_id === '') {
            return WORDPATCH_INVALID_JOB;
        }

        // Grab the table names we are going to use.
        $patches_table = wordpatch_patches_table($wpenv_vars);
        $esc_job_id = wordpatch_esc_sql($wpenv_vars, $job_id);

        // Grab all the patch locations so we can delete them
        $patchquery = "SELECT * FROM `$patches_table` WHERE `job_id` = '$esc_job_id'";

        // Grab all the patches so we can delete them from the filesystem
        $patches = wordpatch_db_get_results($wpenv_vars, $patchquery);

        // If there are no patches in the database for this job, then there is nothing to delete. Return null for success.
        if(empty($patches)) {
            return null;
        }

        // Attempt to connect to the filesystem.
        $fs_begin = wordpatch_filesystem_begin_helper($wpenv_vars);

        if (!$fs_begin) {
            return WORDPATCH_INVALID_FILESYSTEM_CREDENTIALS;
        }

        // Setup a variable for our potential error
        $delete_error = null;

        // Loop through each patch and delete from the filesystem.
        foreach($patches as $patch_single) {
            // Skip any invalid location entries.
            if(trim($patch_single['patch_location']) === '') {
                continue;
            }

            // Attempt to delete the patch.
            $delete_error = wordpatch_file_upload_delete_location($wpenv_vars, wordpatch_file_upload_bucket_patches(),
                $patch_single['patch_location']);

            // If we encountered an error, break early.
            if($delete_error !== null) {
                break;
            }
        }

        // Disconnect from the filesystem.
        wordpatch_filesystem_end();

        // Return the potential error (will be null if all succeeded).
        return $delete_error;
    }
}