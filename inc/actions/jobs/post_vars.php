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
 * Implements the helper function for grabbing post variables from the jobs page.
 */

if(!function_exists('wordpatch_jobs_post_vars')) {
    /**
     * Calculate the current post variables for the jobs form.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_jobs_post_vars($wpenv_vars) {
        // If the job order was not posted, return null.
        if(!isset($_POST['jobs_order'])) {
            return null;
        }

        // Calculate the number of jobs that are not deleted.
        $jobs_count = wordpatch_get_job_count($wpenv_vars, false);

        // If the job count is 0 then return null.
        if ($jobs_count === 0) {
            return null;
        }

        // Grab the posted jobs order string and trim it.
        $jobs_order = trim($_POST['jobs_order']);

        // Explode the job IDs into an array.
        $jobs_pieces = explode(',', $jobs_order);

        // Create an array for our job order IDs which is the final result array.
        $job_ids_order = array();

        // Loop through our pieces and try to create our final result array.
        foreach ($jobs_pieces as $job_id_single) {
            // Sanitize job ID single.
            $job_id_single = wordpatch_sanitize_unique_id($job_id_single);

            // If the job ID single is empty or already in the final array, skip the item.
            if ($job_id_single === '' || in_array($job_id_single, $job_ids_order)) {
                continue;
            }

            // Check if the job exists.
            $job_check = wordpatch_get_job_by_id($wpenv_vars, $job_id_single);

            // If the job doesn't exist, return null since we got a bad request.
            if (!$job_check) {
                return null;
            }

            // Add the job ID to our final result ID.
            $job_ids_order[] = $job_id_single;
        }

        // If the final result array is not the right size, then this request is invalid.
        if ($jobs_count !== count($job_ids_order)) {
            return null;
        }

        // Otherwise, return the calculated result array.
        return $job_ids_order;
    }
}