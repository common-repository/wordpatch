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
 * Implements the database helper functions for the delete job page.
 */

if(!function_exists('wordpatch_deletejob_persist_db')) {
    /**
     * Persists the database records for the delete job page. Returns an error or null on success.
     *
     * @param $wpenv_vars
     * @param $job_id
     * @return null|string
     */
    function wordpatch_deletejob_persist_db($wpenv_vars, $job_id) {
        // Sanitize the job ID first
        $job_id = wordpatch_sanitize_unique_id($job_id);

        // Return an error if the job ID is not valid.
        if ($job_id === '') {
            return WORDPATCH_INVALID_JOB;
        }

        // Grab the table names we are going to use.
        $jobs_table = wordpatch_jobs_table($wpenv_vars);
        $patches_table = wordpatch_patches_table($wpenv_vars);
        $pending_table = wordpatch_pending_table($wpenv_vars);
        $pending_patches_table = wordpatch_pending_patches_table($wpenv_vars);
        $logs_table = wordpatch_logs_table($wpenv_vars);

        // Escape the job ID for our queries.
        $esc_job_id = wordpatch_esc_sql($wpenv_vars, $job_id);

        // Delete from all the necessary tables.
        $result1 = wordpatch_db_query($wpenv_vars, "DELETE FROM $jobs_table WHERE `id` = '$esc_job_id'");
        $result2 = wordpatch_db_query($wpenv_vars, "DELETE FROM $patches_table WHERE `job_id` = '$esc_job_id'");
        $result3 = wordpatch_db_query($wpenv_vars, "DELETE FROM $pending_table WHERE `job_id` = '$esc_job_id'");
        $result4 = wordpatch_db_query($wpenv_vars, "DELETE FROM $pending_patches_table WHERE `job_id` = '$esc_job_id'");
        $result5 = wordpatch_db_query($wpenv_vars, "DELETE FROM $logs_table WHERE `job_id` = '$esc_job_id'");

        // If any of the queries had an issue, return an error.
        if(!$result1 || !$result2 || !$result3 || !$result4 || !$result5) {
            return WORDPATCH_UNKNOWN_DATABASE_ERROR;
        }

        return null;
    }
}