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
 * Implements the database helper functions for the restore job page.
 */

if(!function_exists('wordpatch_restorejob_persist_db')) {
    /**
     * Persists the database records for the restore job page. Returns an error or null on success.
     *
     * @param $wpenv_vars
     * @param $job_id
     * @return null|string
     */
    function wordpatch_restorejob_persist_db($wpenv_vars, $job_id) {
        // Sanitize the job ID first
        $job_id = wordpatch_sanitize_unique_id($job_id);

        // Return an error if the job ID is not valid.
        if ($job_id === '') {
            return WORDPATCH_INVALID_JOB;
        }

        // The job should go to the end of the list when restored since it is the best we can do.
        $sort_order = wordpatch_jobs_next_sort_order($wpenv_vars);

        // Calculate the jobs table name.
        $jobs_table = wordpatch_jobs_table($wpenv_vars);

        // Escape our variables for the query.
        $esc_job_id = wordpatch_esc_sql($wpenv_vars, $job_id);
        $esc_sort_order = wordpatch_esc_sql($wpenv_vars, $sort_order);

        // Execute our update query.
        $result = wordpatch_db_query($wpenv_vars, "UPDATE `$jobs_table` SET `deleted` = '0', `sort_order` = '$esc_sort_order' WHERE `id` = '$esc_job_id'");

        // Return an error if any of the queries failed.
        if(!$result) {
            return WORDPATCH_UNKNOWN_DATABASE_ERROR;
        }

        // If we got here, return null to indicate success.
        return null;
    }
}