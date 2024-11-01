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
 *
 */

if(!function_exists('wordpatch_job_runner_rejection')) {
    function wordpatch_job_runner_rejection($wpenv_vars, $log_info) {
        $reject_user = max(0, (int)$log_info['reject_user']);

        if(!$reject_user) {
            $reject_user = null;
        }

        $result = array(
            'error_list' => array(),
            'error_vars' => array()
        );

        $rollback_rows = wordpatch_get_rollbacks_by_log_id($wpenv_vars, $log_info['id']);

        foreach($rollback_rows as $rollback_row) {
            switch($rollback_row['action']) {
                case WORDPATCH_ROLLBACK_ACTION_WRITE_FILE:
                    $result = wordpatch_job_runner_rejection_write_file($wpenv_vars, $log_info, $rollback_row, $result);
                    break;
                case WORDPATCH_ROLLBACK_ACTION_DELETE_FILE:
                    $result = wordpatch_job_runner_rejection_delete_file($wpenv_vars, $log_info, $rollback_row, $result);
                    break;
                case WORDPATCH_ROLLBACK_ACTION_DELETE_EMPTY_DIR:
                    $result = wordpatch_job_runner_rejection_delete_empty_dir($wpenv_vars, $log_info, $rollback_row, $result);
                    break;
            }

            if(wordpatch_no_errors($result['error_list'])) {
                continue;
            }

            wordpatch_delete_rejects_by_pending_id($wpenv_vars, $log_info['pending_id']);

            wordpatch_job_runner_rejection_update_log($wpenv_vars, $log_info['id'], $reject_user, $result);

            return $result;
        }

        wordpatch_delete_rejects_by_pending_id($wpenv_vars, $log_info['pending_id']);

        wordpatch_job_runner_rejection_update_log($wpenv_vars, $log_info['id'], $reject_user, $result);

        return $result;
    }
}

if(!function_exists('wordpatch_job_runner_rejection_update_log')) {
    /**
     * Update the log entry after job runner has performed a job rejection.
     *
     * @param $wpenv_vars
     * @param $log_id
     * @param $reject_user
     * @param $rejection_result
     */
    function wordpatch_job_runner_rejection_update_log($wpenv_vars, $log_id, $reject_user, $rejection_result) {
        // Calculate the logs table name.
        $logs_table = wordpatch_logs_table($wpenv_vars);

        // Calculate the error list/error vars and ensure we store null for both if have an empty error list.
        $error_list = empty($rejection_result['error_list']) ? null : json_encode($rejection_result['error_list']);
        $error_vars = $error_list === null ? null : json_encode($rejection_result['error_vars']);

        // Escape our query variables.
        $esc_log_id = wordpatch_esc_sql($wpenv_vars, $log_id);

        // Encode our query variables.
        $error_list_enc = wordpatch_encode_sql($wpenv_vars, $error_list);
        $error_vars_enc = wordpatch_encode_sql($wpenv_vars, $error_vars);
        $enc_reject_user = wordpatch_encode_sql($wpenv_vars, $reject_user);

        // Escape our query variables.
        $esc_rejected = wordpatch_esc_sql($wpenv_vars, WORDPATCH_JUDGEMENT_REJECTED);

        $esc_pending_status = wordpatch_esc_sql($wpenv_vars, WORDPATCH_CLEANUP_STATUS_PENDING);

        // Construct our update query.
        $updatejudgement = "UPDATE `$logs_table` SET `running` = NULL, `cleanup_status` = '$esc_pending_status', " .
            "`judgement_decision` = '$esc_rejected', `judgement_user` = $enc_reject_user, `judgement_pending` = '0', " .
            "`judgement_datetime` = UTC_TIMESTAMP(), `reject_error_list` = $error_list_enc, " .
            "`reject_error_vars` = $error_vars_enc WHERE `id` = '$esc_log_id'";

        // Perform the update query.
        wordpatch_db_query($wpenv_vars, $updatejudgement);
    }
}

if(!function_exists('wordpatch_job_runner_rejection_check_hash_mismatch')) {
    function wordpatch_job_runner_rejection_check_hash_mismatch($wpenv_vars, $log_info, $rollback_row) {
        $job_path = wordpatch_sanitize_unix_file_path(wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_root_dir')) .
            $log_info['path'], true, false);

        $file_path = wordpatch_sanitize_unix_file_path(wordpatch_trailingslashit($job_path) . $rollback_row['path'], true, false);

        // Do not rollback the file if it's current sha1() does not match the $changes array
        $hash_mismatch = true;

        foreach($log_info['changes'] as $change_single) {
            if($change_single['path'] !== $rollback_row['path']) {
                continue;
            }

            $current_sha1 = null;

            if(@file_exists($file_path)) {
                $current_contents = @file_get_contents($file_path);

                if($current_contents === false) {
                    $current_contents = null;
                }

                $current_sha1 = $current_contents === null ? null : sha1($current_contents);
            }

            if($change_single['sha1'] === $current_sha1) {
                $hash_mismatch = false;
                break;
            }
        }

        return $hash_mismatch;
    }
}
if(!function_exists('wordpatch_job_runner_rejection_write_file')) {
    function wordpatch_job_runner_rejection_write_file($wpenv_vars, $log_info, $rollback_row, $result) {
        $job_path = wordpatch_sanitize_unix_file_path(wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_root_dir')) .
            $log_info['path'], true, false);

        $file_path = wordpatch_sanitize_unix_file_path(wordpatch_trailingslashit($job_path) . $rollback_row['path'], true, false);
        $file_path_relative_to_wp_root = wordpatch_normalize_to_wproot($wpenv_vars, $file_path);

        // Do not rollback the file if it's current sha1() does not match the $changes array
        $hash_mismatch = wordpatch_job_runner_rejection_check_hash_mismatch($wpenv_vars, $log_info, $rollback_row);

        // Do not roll it back since there is a hash mismatch.
        if($hash_mismatch) {
            return $result;
        }

        $rollback_contents = null;
        $rollback_read_error = false; // Output var

        if($file_path_relative_to_wp_root !== null) {
            $rollback_contents = wordpatch_file_upload_read_from_location($wpenv_vars, wordpatch_file_upload_bucket_rollbacks(),
                $rollback_row['location'], $rollback_read_error);

            if($rollback_read_error) {
                $result['error_list'][] = WORDPATCH_FILESYSTEM_FAILED_READ;
                $result['error_vars']['rollback_location'] = $rollback_row['location'];

                return $result;
            }
        }

        if($file_path_relative_to_wp_root === null || !wordpatch_filesystem_put_contents($wpenv_vars, $file_path_relative_to_wp_root, 'base', $rollback_contents)) {
            $result['error_list'][] = WORDPATCH_FILESYSTEM_FAILED_WRITE;
            $result['error_vars']['file_path'] = $file_path;
            $result['error_vars']['file_path_relative'] = $file_path_relative_to_wp_root;

            return $result;
        }

        return $result;
    }
}

if(!function_exists('wordpatch_job_runner_rejection_delete_file')) {
    function wordpatch_job_runner_rejection_delete_file($wpenv_vars, $log_info, $rollback_row, $result) {
        $job_path = wordpatch_sanitize_unix_file_path(wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_root_dir')) .
            $log_info['path'], true, false);

        $file_path = wordpatch_sanitize_unix_file_path(wordpatch_trailingslashit($job_path) . $rollback_row['path'], true, false);
        $file_path_relative_to_wp_root = wordpatch_normalize_to_wproot($wpenv_vars, $file_path);

        // Do not rollback the file if it's current sha1() does not match the $changes array
        $hash_mismatch = wordpatch_job_runner_rejection_check_hash_mismatch($wpenv_vars, $log_info, $rollback_row);

        // Do not roll it back since there is a hash mismatch.
        if($hash_mismatch) {
            return $result;
        }

        if($file_path_relative_to_wp_root === null || !wordpatch_filesystem_delete($wpenv_vars, $file_path_relative_to_wp_root, 'base')) {
            $result['error_list'][] = WORDPATCH_FILESYSTEM_FAILED_DELETE;
            $result['error_vars']['file_path'] = $file_path;
            $result['error_vars']['file_path_relative'] = $file_path_relative_to_wp_root;

            return $result;
        }

        return $result;
    }
}

if(!function_exists('wordpatch_job_runner_rejection_delete_empty_dir')) {
    function wordpatch_job_runner_rejection_delete_empty_dir($wpenv_vars, $log_info, $rollback_row, $result) {
        $job_path = wordpatch_sanitize_unix_file_path(wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_root_dir')) .
            $log_info['path'], true, false);

        $dir_path = wordpatch_sanitize_unix_file_path(wordpatch_trailingslashit($job_path) . $rollback_row['path'], true, false);
        $dir_path_relative_to_wp_root = wordpatch_normalize_to_wproot($wpenv_vars, $dir_path);

        if(@file_exists($dir_path) && @is_dir($dir_path) && wordpatch_dir_is_empty($dir_path)) {
            if($dir_path_relative_to_wp_root === null || !wordpatch_filesystem_delete($wpenv_vars, $dir_path_relative_to_wp_root, 'base', WORDPATCH_CHANGE_TYPE_DELETE)) {
                $result['error_list'][] = WORDPATCH_FILESYSTEM_FAILED_DELETE_DIR;
                $result['error_vars']['dir_path'] = $dir_path;
                $result['error_vars']['dir_path_relative'] = $dir_path_relative_to_wp_root;

                return $result;
            }
        }

        return $result;
    }
}