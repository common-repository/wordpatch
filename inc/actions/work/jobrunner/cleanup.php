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
 * Implements the cleanup job functionality for the job runner.
 */

if(!function_exists('wordpatch_job_runner_cleanup')) {
    /**
     * Performs the job cleanup routine for the job runner.
     *
     * @param $wpenv_vars
     * @param $log_info
     * @return array
     */
    function wordpatch_job_runner_cleanup($wpenv_vars, $log_info) {
        // Begin constructing our result.
        $result = array(
            'error_list' => array(),
            'error_vars' => array()
        );

        // Grab the rollback rows from the database.
        $rollback_rows = wordpatch_get_rollbacks_by_log_id($wpenv_vars, $log_info['id']);

        // Loop through the rollback rows and attempt to delete if they have a location.
        foreach($rollback_rows as $rollback_row) {
            // Skip this if there is not a location attached.
            if($rollback_row['location'] === null || trim($rollback_row['location']) === '') {
                continue;
            }

            // Delete the file referenced by the rollback location from the filesystem.
            $delete_error = wordpatch_file_upload_delete_location($wpenv_vars, wordpatch_file_upload_bucket_rollbacks(),
                $rollback_row['location']);

            // If there are no errors, great! Go to the next iteration.
            if($delete_error === null) {
                continue;
            }

            // Append the error information to our result array.
            $result['error_list'][] = $delete_error;
            $result['error_vars']['cleanup_location'] = $rollback_row['location'];

            // Delete the rollback rows from the database.
            wordpatch_delete_rollbacks_by_log_id($wpenv_vars, $log_info['id']);

            // Update the log entry to indicate that the cleanup has finished with an error.
            wordpatch_job_runner_cleanup_update_log($wpenv_vars, $log_info['id'], $result);

            // Return our result.
            return $result;
        }

        // Delete the rollback rows from the database.
        wordpatch_delete_rollbacks_by_log_id($wpenv_vars, $log_info['id']);

        // Update the log entry to indicate that the cleanup has finished successfully.
        wordpatch_job_runner_cleanup_update_log($wpenv_vars, $log_info['id'], $result);

        // Return our result.
        return $result;
    }
}

if(!function_exists('wordpatch_job_runner_cleanup_update_log')) {
    /**
     * Update the log entry after job runner has performed a job cleanup.
     *
     * @param $wpenv_vars
     * @param $log_id
     * @param $cleanup_result
     */
    function wordpatch_job_runner_cleanup_update_log($wpenv_vars, $log_id, $cleanup_result) {
        // Calculate the logs table name.
        $logs_table = wordpatch_logs_table($wpenv_vars);

        // Calculate the error list/error vars and ensure we store null for both if have an empty error list.
        $error_list = empty($cleanup_result['error_list']) ? null : json_encode($cleanup_result['error_list']);
        $error_vars = $error_list === null ? null : json_encode($cleanup_result['error_vars']);

        // Escape our query variables.
        $esc_log_id = wordpatch_esc_sql($wpenv_vars, $log_id);

        // Encode our query variables.
        $error_list_enc = wordpatch_encode_sql($wpenv_vars, $error_list);
        $error_vars_enc = wordpatch_encode_sql($wpenv_vars, $error_vars);

        $esc_none_status = wordpatch_esc_sql($wpenv_vars, WORDPATCH_CLEANUP_STATUS_NONE);

        // Construct the query.
        $updatejudgement = "UPDATE `$logs_table` SET `running` = NULL, `cleanup_status` = '$esc_none_status'," .
            "`cleanup_error_list` = $error_list_enc, `cleanup_error_vars` = $error_vars_enc WHERE `id` = '$esc_log_id'";

        // Update the log entry!
        wordpatch_db_query($wpenv_vars, $updatejudgement);
    }
}