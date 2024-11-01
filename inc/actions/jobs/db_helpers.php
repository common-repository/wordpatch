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
 * Implements the database helper functions for the jobs page.
 */

if(!function_exists('wordpatch_jobs_persist_db')) {
    /**
     * Persists the database records for the settings page. Returns an error string or null on success.
     *
     * @param $wpenv_vars
     * @param array $order_ids
     * @return null|string
     */
    function wordpatch_jobs_persist_db($wpenv_vars, $order_ids) {
        // If the order IDs is null for some reason, return an unknown error. This should never happen.
        if($order_ids === null) {
            return WORDPATCH_UNKNOWN_ERROR;
        }

        // Calculate the count of order IDs.
        $order_ids_count = count($order_ids);

        // Calculate the jobs table name.
        $jobs_table = wordpatch_jobs_table($wpenv_vars);

        // Loop through each of the IDs and update the sort order.
        for ($sort_order = 1; $sort_order <= $order_ids_count; ++$sort_order) {
            // Grab the job ID for the current iteration.
            $job_id = $order_ids[$sort_order - 1];

            // Escape our variables for the update query.
            $esc_job_id = wordpatch_esc_sql($wpenv_vars, $job_id);
            $esc_sort_order = wordpatch_esc_sql($wpenv_vars, $sort_order);

            // Construct our update query.
            $updateorderquery = "UPDATE `$jobs_table` SET `sort_order` = '$esc_sort_order' WHERE `id` = '$esc_job_id'";

            // Call the update query.
            $updateresult = wordpatch_db_query($wpenv_vars, $updateorderquery);

            if(!$updateresult) {
                return WORDPATCH_UNKNOWN_DATABASE_ERROR;
            }
        }

        // If we get here, then we succeeded.
        return null;
    }
}