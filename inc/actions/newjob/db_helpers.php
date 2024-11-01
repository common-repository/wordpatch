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
 * Implements the database helper functions for the new job page.
 */

if(!function_exists('wordpatch_newjob_persist_db')) {
    /**
     * Persists the database record for the new job form. Returns an error string or null on success.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $job_id
     * @return null|string
     */
    function wordpatch_newjob_persist_db($wpenv_vars, $current, $job_id) {
        $jobs_table = wordpatch_jobs_table($wpenv_vars);

        // Determine the best sort order value for our new job.
        $sort_order = wordpatch_jobs_next_sort_order($wpenv_vars);

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

        // Run our query.
        $insertquery = "INSERT INTO $jobs_table (`id`, `title`, `path`, `enabled`, `deleted`, `maintenance_mode`, " .
            "`binary_mode`, `sort_order`, `mode`, `timer`, `update_cooldown`, `retry_count`) VALUES ('$esc_job_id', " .
            "'$esc_title', '$esc_path', '$esc_enabled_int', '0', '$esc_maintenance_mode', '$esc_binary_mode', '$sort_order', " .
            "'$esc_mode', '$esc_timer', '$esc_update_cooldown', '$esc_retry_count')";

        $insertresult = wordpatch_db_query($wpenv_vars, $insertquery);

        if (!$insertresult) {
            return WORDPATCH_UNKNOWN_DATABASE_ERROR;
        }

        return null;
    }
}