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

if(!function_exists('wordpatch_job_runner_process')) {
    /**
     * Responsible for running a job for the job runner. The following flow is essentially followed:
     * - The job path is validated.
     * - Related optimistic jobs that are pending judgement are auto accepted.
     * - Related pessimistic jobs that are pending judgement are auto enqueued for rejection.
     * - The job is guarded against running if there are log entries for the same job that are queued for rejection.
     * - The patch records for the job are loaded from the database, and if an error occurs the job fails.
     * - The patch files for the job are loaded from the filesystem, and if an error occurs the job fails.
     * - The patch files for the job are analyzed via API request for file references.
     * - The API request for analyzing the patch files is checked for network problems, and the log is marked to be retried if need be.
     * - The previous version of each referenced file is loaded from the filesystem.
     * - The patcher API call is performed.
     * - The patcher API result is checked for network problems, and the log is marked to be retried if need be.
     * - The patcher API result is checked for other errors, and the log is updated if an error is detected.
     * - Final directory structure is predicted using the patcher API result.
     * - Backups are created. (aka: rollbacks)
     * - Final change counts are calculated. (ie: create, edit and delete count)
     * - Changes are applied to the filesystem and if any errors occur the log is updated appropriately.
     *
     * @param $wpenv_vars
     * @param $log_info
     * @return array|mixed
     */
    function wordpatch_job_runner_process($wpenv_vars, $log_info) {
        // Start to calculate our result.
        $result = array(
            'error_list' => array(),
            'error_vars' => array()
        );

        // Validate the job path.
        $job_path = wordpatch_sanitize_unix_file_path(wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_root_dir')) .
            $log_info['path'], true, false);

        if(!@file_exists($job_path) || !@is_dir($job_path)) {
            $result['error_list'][] = WORDPATCH_INVALID_JOB_PATH;
            wordpatch_fail_job($wpenv_vars, $log_info, $result['error_list'], $result['error_vars']);
            return $result;
        }

        // Auto accept related optimistic jobs that are pending judgement.
        wordpatch_job_runner_process_accept_optimistic($wpenv_vars, $log_info);

        // Auto enqueue related pessimistic jobs for rejection that are pending judgement.
        wordpatch_job_runner_process_enqueue_reject_pessimistic($wpenv_vars, $log_info);

        // Guard against potential pending rejection conflicts.
        $result = wordpatch_job_runner_process_guard_rejects($wpenv_vars, $log_info, $result);

        if(!wordpatch_no_errors($result['error_list'])) {
            return $result;
        }

        // Load the patch records from the database.
        $pending_patches = wordpatch_get_pending_patches_by_pending_id($wpenv_vars, $log_info['pending_id']);

        // Load the patch files from the filesystem.
        $result = wordpatch_job_runner_process_load_patches($wpenv_vars, $log_info, $pending_patches, $patch_info,
            $result);

        if(!wordpatch_no_errors($result['error_list'])) {
            return $result;
        }

        // Analyze the patch files for the job via API request for file references.
        $result = wordpatch_job_runner_process_analyze_patches($wpenv_vars, $log_info, $patch_info['contents_without_bom'],
            $files_involved, $result);

        if(!wordpatch_no_errors($result['error_list'])) {
            return $result;
        }

        // Load the previous version of each referenced file from the filesystem.
        $previous_info = wordpatch_job_runner_process_load_previous($wpenv_vars, $log_info, $files_involved);

        // Perform the patcher API call.
        $binary_mode_bool = $log_info['binary_mode'] === wordpatch_job_binary_mode_yes();

        $api_result = wordpatch_patch_api_request($wpenv_vars, $patch_info['contents_binary'],
            $previous_info['relative_file_names'], $previous_info['versions'], $patch_info['types'], $binary_mode_bool);

        // Guard against patcher API result network errors.
        $result = wordpatch_job_runner_process_guard_network_error($wpenv_vars, $log_info, $api_result, $result);

        if(!wordpatch_no_errors($result['error_list'])) {
            return $result;
        }

        // Guard against patcher API result process errors.
        $result = wordpatch_job_runner_process_guard_request_error($wpenv_vars, $log_info, $api_result, $result);

        if(!wordpatch_no_errors($result['error_list'])) {
            return $result;
        }

        // Predict the final directory structure using the patcher API result.
        $directory_predictions = wordpatch_job_runner_process_predict_directories($wpenv_vars, $log_info, $api_result);

        // Create our rollbacks which essentially encompass the state of the filesystem before applying the patches.
        // This is our backup :)
        $result = wordpatch_job_runner_process_insert_rollbacks($wpenv_vars, $log_info, $api_result, $files_involved,
            $previous_info['versions'], $result, $rollback_sort_order);

        if(!wordpatch_no_errors($result['error_list'])) {
            return $result;
        }

        wordpatch_job_runner_process_insert_rollbacks_dirs($wpenv_vars, $log_info, $directory_predictions, $rollback_sort_order);

        // Update the real-time progress of the log.
        wordpatch_update_job_progress($wpenv_vars, $log_info['id'], WORDPATCH_PROGRESS_API_AMOUNT,
            WORDPATCH_PROGRESS_API_NOTE);

        // Calculate the final change counts. (ie: create, edit and delete count)
        $changes = wordpatch_job_runner_process_determine_changes($wpenv_vars, $log_info, $api_result);

        // Apply the changes to the filesystem!
        $result = wordpatch_job_runner_process_apply_changes($wpenv_vars, $log_info, $api_result, $result);

        if(!wordpatch_no_errors($result['error_list'])) {
            return $result;
        }

        // Mark the job as successful. The job will be auto-approved if criteria is satisfied.
        wordpatch_pass_job($wpenv_vars, $log_info, $changes);

        // Return our result!
        return $result;
    }
}

if(!function_exists('wordpatch_job_runner_process_accept_optimistic')) {
    function wordpatch_job_runner_process_accept_optimistic($wpenv_vars, $log_info) {
        $logs_table = wordpatch_logs_table($wpenv_vars);

        // The following query will automatically accept logs if necessary for this job where optimistic
        $esc_optimistic_mode = wordpatch_esc_sql($wpenv_vars, wordpatch_job_mode_optimistic());
        $esc_accepted = wordpatch_esc_sql($wpenv_vars, WORDPATCH_JUDGEMENT_ACCEPTED);
        $esc_job_id = wordpatch_esc_sql($wpenv_vars, $log_info['job_id']);

        $esc_pending_status = wordpatch_esc_sql($wpenv_vars, WORDPATCH_CLEANUP_STATUS_PENDING);

        $updateaccepts = "UPDATE `$logs_table` SET `cleanup_status` = '$esc_pending_status', " .
            "`judgement_decision` = '$esc_accepted', `judgement_user` = NULL, `judgement_pending` = '0', " .
            "`judgement_datetime` = UTC_TIMESTAMP() WHERE `job_id` = '$esc_job_id' AND (`running` IS NULL) AND " .
            "(`judgement_pending` IS NOT NULL AND `judgement_pending` = '1') AND (`mode` = '$esc_optimistic_mode')";

        wordpatch_db_query($wpenv_vars, $updateaccepts);
    }
}

if(!function_exists('wordpatch_job_runner_process_enqueue_reject_pessimistic')) {
    function wordpatch_job_runner_process_enqueue_reject_pessimistic($wpenv_vars, $log_info) {
        $logs_table = wordpatch_logs_table($wpenv_vars);
        $rejects_table = wordpatch_rejects_table($wpenv_vars);

        $esc_job_id = wordpatch_esc_sql($wpenv_vars, $log_info['job_id']);

        // The following query will insert rejects if necessary for this job where pessimistic
        $esc_pessimistic_mode = wordpatch_esc_sql($wpenv_vars, wordpatch_job_mode_pessimistic());

        $insertrejects = "INSERT INTO `$rejects_table` (`pending_id`, `user_id`, `job_id`, `datetime`) " .
            "SELECT `pending_id` as `pending_id`, '0' as `user_id`, `job_id` as `job_id`, UTC_TIMESTAMP() as `datetime` " .
            "FROM `$logs_table` WHERE `job_id` = '$esc_job_id' AND (`running` IS NULL) AND (`judgement_pending` IS NOT NULL AND " .
            "`judgement_pending` = '1') AND (`mode` = '$esc_pessimistic_mode') ON DUPLICATE KEY UPDATE `user_id`=`user_id`";

        wordpatch_db_query($wpenv_vars, $insertrejects);
    }
}

if(!function_exists('wordpatch_job_runner_process_guard_rejects')) {
    function wordpatch_job_runner_process_guard_rejects($wpenv_vars, $log_info, $result) {
        $rejects_table = wordpatch_rejects_table($wpenv_vars);

        $esc_pending_id = wordpatch_esc_sql($wpenv_vars, $log_info['pending_id']);

        $checkquery = "SELECT `pending_id` FROM `$rejects_table` WHERE `pending_id` = '$esc_pending_id' LIMIT 1";
        $check_rows = wordpatch_db_get_results($wpenv_vars, $checkquery);

        if(!empty($check_rows)) {
            $logs_table = wordpatch_logs_table($wpenv_vars);

            $esc_log_id = wordpatch_esc_sql($wpenv_vars, $log_info['id']);

            wordpatch_db_query($wpenv_vars, "DELETE FROM `$logs_table` WHERE `id` = '$esc_log_id'");
            $result['error_list'][] = WORDPATCH_REJECTS_IN_QUEUE;

            return $result;
        }

        return $result;
    }
}

if(!function_exists('wordpatch_job_runner_process_load_patches')) {
    function wordpatch_job_runner_process_load_patches($wpenv_vars, $log_info, $pending_patches, &$patch_info, $result) {
        $patch_info = array(
            'contents_without_bom' => array(),
            'contents_binary' => array(),
            'types' => array()
        );

        $patches_bucket = wordpatch_file_upload_bucket_patches();

        foreach($pending_patches as $patch_single) {
            $patch_file_binary = null;
            $patch_file_without_bom = null;

            $patch_file_binary = wordpatch_file_upload_read_from_location($wpenv_vars, $patches_bucket,
                $patch_single['patch_location'], $patch_read_error);

            if($patch_read_error) {
                $result['error_list'][] = $patch_read_error;
                wordpatch_fail_job($wpenv_vars, $log_info, $result['error_list'], $result['error_vars']);
                return $result;
            }

            if($patch_single['patch_type'] === wordpatch_patch_type_text()) {
                $patch_file_without_bom = $patch_file_binary; // These won't have a BOM since they will be inputted in a text box.
            } else {
                $patch_file_without_bom = wordpatch_remove_bom($patch_file_binary);
            }

            $patch_info['contents_binary'][] = $patch_file_binary;
            $patch_info['contents_without_bom'][] = $patch_file_without_bom;
            $patch_info['types'][] = $patch_single['patch_type'];
        }

        return $result;
    }
}

if(!function_exists('wordpatch_job_runner_process_analyze_patches')) {
    function wordpatch_job_runner_process_analyze_patches($wpenv_vars, $log_info, $text_patches, &$files_involved, $result)
    {
        $ignore_paths = array('dev/null', '/dev/null');

        $files_involved = array();

        $discover_result = wordpatch_analyze_api_request($wpenv_vars, $text_patches);

        if($discover_result === null || (isset($discover_result['error']) && !empty($discover_result['error']))) {
            $discover_error = ($discover_result !== null && isset($discover_result['error'])) ?
                $discover_result['error'] : WORDPATCH_UNKNOWN_HTTP_ERROR;

            if($discover_error === WORDPATCH_UNKNOWN_HTTP_ERROR || $discover_error === WORDPATCH_API_UNKNOWN_ERROR) {
                if(!wordpatch_should_retry_job($wpenv_vars, $log_info)) {
                    $result['error_list'][] = $discover_error;
                    wordpatch_fail_job($wpenv_vars, $log_info, $result['error_list'], $result['error_vars']);
                    return $result;
                }

                // Transition the log to retry state.
                wordpatch_retry_job($wpenv_vars, $log_info);
                return $result;
            }

            $result['error_list'][] = $discover_error;

            wordpatch_fail_job($wpenv_vars, $log_info, $result['error_list'], $result['error_vars']);
            return $result;
        }

        foreach ($discover_result['data']['filenames'] as $filenameSingle) {
            $potentialPath = wordpatch_sanitize_unix_file_path(base64_decode($filenameSingle));

            if (wordpatch_is_invalid_unix_path($potentialPath) || $potentialPath === '' || in_array($potentialPath, $ignore_paths)) {
                continue;
            }

            $potentialPath = wordpatch_sanitize_unix_file_path($potentialPath);

            if (wordpatch_is_invalid_unix_path($potentialPath) || $potentialPath === '' ||
                in_array($potentialPath, $ignore_paths) || in_array($potentialPath, $files_involved)) {
                continue;
            }

            $files_involved[] = $potentialPath;
        }

        return $result;
    }
}

if(!function_exists('wordpatch_job_runner_process_load_previous')) {
    function wordpatch_job_runner_process_load_previous($wpenv_vars, $log_info, $files_involved) {
        $job_path = wordpatch_sanitize_unix_file_path(wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_root_dir')) .
            $log_info['path'], true, false);

        $previous_info = array(
            'versions' => array(),
            'relative_file_names' => array()
        );

        foreach($files_involved as $file_involved) {
            $file_path = wordpatch_sanitize_unix_file_path(wordpatch_trailingslashit($job_path) . $file_involved, true, false);
            $previous_info['relative_file_names'][] = wordpatch_sanitize_unix_file_path($file_involved, true, true);

            $previous_version = null;

            if(@file_exists($file_path) && !@is_dir($file_path)) {
                $previous_version = file_get_contents($file_path);
            }

            $previous_info['versions'][] = $previous_version;
        }

        return $previous_info;
    }
}

if(!function_exists('wordpatch_job_runner_process_guard_network_error')) {
    function wordpatch_job_runner_process_guard_network_error($wpenv_vars, $log_info, $api_result, $result) {
        // If there is a network error, we should instead transition this job to retry.
        if($api_result === null) {
            if(!wordpatch_should_retry_job($wpenv_vars, $log_info)) {
                $result['error_list'][] = WORDPATCH_API_UNKNOWN_ERROR;
                wordpatch_fail_job($wpenv_vars, $log_info, $result['error_list'], $result['error_vars']);
                return $result;
            }

            // Transition the log to retry state.
            wordpatch_retry_job($wpenv_vars, $log_info);

            // We will add an error to the result but we aren't going to persist it
            $result['error_list'][] = WORDPATCH_JOB_WILL_RETRY;
            return $result;
        }

        return $result;
    }
}

if(!function_exists('wordpatch_job_runner_process_guard_request_error')) {
    function wordpatch_job_runner_process_guard_request_error($wpenv_vars, $log_info, $api_result, $result) {
        if(isset($api_result['error']) && isset($api_result['error']['message'])) {
            $result['error_list'] = array($api_result['error']['message']);
            $errorVars = array();
            foreach($api_result['error'] as $errorKey => $errorVal) {
                if($errorKey === 'message') {
                    continue;
                }

                $errorVars[$errorKey] = $errorVal;
            }

            $result['error_vars'] = (array)$errorVars;
            wordpatch_fail_job($wpenv_vars, $log_info, $result['error_list'], $result['error_vars']);
            return $result;
        }

        return $result;
    }
}

if(!function_exists('wordpatch_job_runner_process_predict_directories')) {
    function wordpatch_job_runner_process_predict_directories($wpenv_vars, $log_info, $api_result) {
        $job_path = wordpatch_sanitize_unix_file_path(wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_root_dir')) .
            $log_info['path'], true, false);

        $directory_predictions = array();

        foreach($api_result['data'] as $api_result_single) {
            $api_path_decode = wordpatch_sanitize_unix_file_path(base64_decode($api_result_single['path']));

            if ($api_result_single['change_type'] === WORDPATCH_CHANGE_TYPE_DELETE) {
                continue;
            }

            $segments = explode('/', $api_path_decode);
            array_pop($segments);
            $seg_count = count($segments);

            for ($seg_index = 0; $seg_index < $seg_count; $seg_index++) {
                $segments_current = array_slice($segments, 0, ($seg_index + 1));

                $dir_path_relative_to_job = implode('/', $segments_current);
                $dir_path = wordpatch_sanitize_unix_file_path(wordpatch_trailingslashit($job_path) . implode('/', $segments_current), true, false);

                if (!file_exists($dir_path) && !in_array($dir_path_relative_to_job, $directory_predictions)) {
                    $directory_predictions[] = $dir_path_relative_to_job;
                }
            }
        }

        // Reverse our directory predictions
        $directory_predictions = array_reverse($directory_predictions);

        return $directory_predictions;
    }
}

if(!function_exists('wordpatch_job_runner_process_insert_rollbacks_dirs')) {
    function wordpatch_job_runner_process_insert_rollbacks_dirs($wpenv_vars, $log_info, $directory_predictions, $last_sort_order) {
        $rollback_table = wordpatch_rollback_table($wpenv_vars);
        $rollback_sort_order = $last_sort_order;

        $esc_delete_empty_dir_action = wordpatch_esc_sql($wpenv_vars, WORDPATCH_ROLLBACK_ACTION_DELETE_EMPTY_DIR);
        $esc_log_id = wordpatch_esc_sql($wpenv_vars, $log_info['id']);

        foreach($directory_predictions as $directory_prediction) {
            $esc_directory_prediction = wordpatch_esc_sql($wpenv_vars, base64_encode($directory_prediction));
            $esc_directory_prediction_hash = wordpatch_esc_sql($wpenv_vars, wordpatch_sha1($directory_prediction));
            $esc_rollback_id = wordpatch_esc_sql($wpenv_vars, wordpatch_unique_id());

            $rollback_sort_order++;
            $esc_sort_order = wordpatch_esc_sql($wpenv_vars, $rollback_sort_order);

            $insertquery = "INSERT INTO `$rollback_table` (`id`, `log_id`, `path_hash`, `action`, `path`, `location`, `sort_order`) " .
                "VALUES ('$esc_rollback_id', '$esc_log_id', '$esc_directory_prediction_hash', '$esc_delete_empty_dir_action', '$esc_directory_prediction', NULL, '$esc_sort_order') " .
                "ON DUPLICATE KEY UPDATE `location` = VALUES(`location`)";

            wordpatch_db_query($wpenv_vars, $insertquery);
        }
    }
}

if(!function_exists('wordpatch_job_runner_process_insert_rollbacks')) {
    function wordpatch_job_runner_process_insert_rollbacks($wpenv_vars, $log_info, $api_result, $files_involved,
                                                           $previous_versions, $result, &$rollback_sort_order) {
        $rollback_sort_order = 0;
        $rollback_table = wordpatch_rollback_table($wpenv_vars);

        $job_path = wordpatch_sanitize_unix_file_path(wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_root_dir')) .
            $log_info['path'], true, false);

        $esc_log_id = wordpatch_esc_sql($wpenv_vars, $log_info['id']);
        $esc_create_file_action = wordpatch_esc_sql($wpenv_vars, WORDPATCH_ROLLBACK_ACTION_WRITE_FILE);
        $esc_delete_file_action = wordpatch_esc_sql($wpenv_vars, WORDPATCH_ROLLBACK_ACTION_DELETE_FILE);

        foreach($api_result['data'] as $api_result_single) {
            // No need to write to the filesystem if this file hasn't changed.
            if(isset($api_result_single['data_equal']) && $api_result_single['data_equal']) {
                continue;
            }

            $api_path_decode = base64_decode($api_result_single['path']);
            $api_path_sanitized = wordpatch_sanitize_unix_file_path($api_path_decode, true, true);

            $esc_path = wordpatch_esc_sql($wpenv_vars, base64_encode($api_path_sanitized));
            $esc_path_hash = wordpatch_esc_sql($wpenv_vars, wordpatch_sha1($api_path_sanitized));

            $file_path = wordpatch_sanitize_unix_file_path(wordpatch_trailingslashit($job_path) . $api_path_decode, true, false);

            if($api_result_single['change_type'] !== WORDPATCH_CHANGE_TYPE_DELETE) {
                // The following loop is an optimization to prevent un-necessary re-reads.
                $was_involved = false;
                $original_contents = null;

                foreach($files_involved as $involved_index => $file_involved) {
                    $file_involved = wordpatch_sanitize_unix_file_path($file_involved, true, true);

                    if($file_involved !== $api_path_sanitized) {
                        continue;
                    }

                    $was_involved = true;
                    $original_contents = $previous_versions[$involved_index];
                    break;
                }

                if(!$was_involved && (@file_exists($file_path) && !@is_dir($file_path))) {
                    $original_contents = @file_get_contents($file_path);
                }

                // Check if the contents are null and if they are, then track a delete action.
                // Otherwise, track a create action.
                $esc_rollback_id = wordpatch_esc_sql($wpenv_vars, wordpatch_unique_id());

                if($original_contents === null) {
                    $rollback_sort_order++;
                    $esc_sort_order = wordpatch_esc_sql($wpenv_vars, $rollback_sort_order);

                    $insertquery = "INSERT INTO `$rollback_table` (`id`, `log_id`, `path_hash`, `action`, `path`, `location`, `sort_order`) " .
                        "VALUES ('$esc_rollback_id', '$esc_log_id', '$esc_path_hash', '$esc_delete_file_action', '$esc_path', NULL, '$esc_sort_order') " .
                        "ON DUPLICATE KEY UPDATE `location` = VALUES(`location`)";

                    wordpatch_db_query($wpenv_vars, $insertquery);
                } else {
                    $location = wordpatch_file_upload_generate_location();

                    $write_error = wordpatch_file_upload_write_to_location($wpenv_vars, wordpatch_file_upload_bucket_rollbacks(),
                        $location, $original_contents);

                    if($write_error) {
                        $result['error_list'][] = $write_error;
                        $result['error_vars']['rollback_location'] = $location;

                        wordpatch_fail_job($wpenv_vars, $log_info, $result['error_list'], $result['error_vars']);
                        return $result;
                    }

                    $esc_location = wordpatch_esc_sql($wpenv_vars, $location);

                    $rollback_sort_order++;
                    $esc_sort_order = wordpatch_esc_sql($wpenv_vars, $rollback_sort_order);

                    $insertquery = "INSERT INTO `$rollback_table` (`id`, `log_id`, `path_hash`, `action`, `path`, `location`, `sort_order`) " .
                        "VALUES ('$esc_rollback_id', '$esc_log_id', '$esc_path_hash', '$esc_create_file_action', '$esc_path', '$esc_location', '$esc_sort_order') " .
                        "ON DUPLICATE KEY UPDATE `location` = VALUES(`location`)";
                    wordpatch_db_query($wpenv_vars, $insertquery);
                }

                continue;
            }

            // Since the change was a delete, we know the action will be create (as long as it existed and was readable before the deletion.)
            $was_involved = false;
            $original_contents = null;

            foreach($files_involved as $involved_index => $file_involved) {
                $file_involved = wordpatch_sanitize_unix_file_path($file_involved, true, true);

                if($file_involved !== $api_path_sanitized) {
                    continue;
                }

                $was_involved = true;
                $original_contents = $previous_versions[$involved_index];
                break;
            }


            if(!$was_involved && (@file_exists($file_path) && !@is_dir($file_path))) {
                $original_contents = file_get_contents($file_path);
            }

            // If there is nothing to rollback to, just skip it.
            // TODO: Maybe replace this with a delete action? Seems wrong but we'll see.
            if($original_contents === null) {
                continue;
            }

            $location = wordpatch_file_upload_generate_location();

            $write_error = wordpatch_file_upload_write_to_location($wpenv_vars, wordpatch_file_upload_bucket_rollbacks(),
                $location, $original_contents);

            if($write_error) {
                $result['error_list'][] = $write_error;
                $result['error_vars']['rollback_location'] = $location;

                wordpatch_fail_job($wpenv_vars, $log_info, $result['error_list'], $result['error_vars']);
                return $result;
            }

            $esc_location = wordpatch_esc_sql($wpenv_vars, $location);
            $esc_rollback_id = wordpatch_esc_sql($wpenv_vars, wordpatch_unique_id());

            $rollback_sort_order++;
            $esc_sort_order = wordpatch_esc_sql($wpenv_vars, $rollback_sort_order);

            $insertquery = "INSERT INTO `$rollback_table` (`id`, `log_id`, `path_hash`, `action`, `path`, `location`, `sort_order`) " .
                "VALUES ('$esc_rollback_id', '$esc_log_id', '$esc_path_hash', '$esc_create_file_action', '$esc_path', '$esc_location', '$esc_sort_order') " .
                "ON DUPLICATE KEY UPDATE `location` = VALUES(`location`)";

            wordpatch_db_query($wpenv_vars, $insertquery);
        }

        return $result;
    }
}

if(!function_exists('wordpatch_job_runner_process_determine_changes')) {
    function wordpatch_job_runner_process_determine_changes($wpenv_vars, $log_info, $api_result) {
        $job_path = wordpatch_sanitize_unix_file_path(wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_root_dir')) .
            $log_info['path'], true, false);

        $changes = array();

        foreach($api_result['data'] as $api_result_single) {
            $api_result_path = base64_decode($api_result_single['path']);

            $file_path = wordpatch_sanitize_unix_file_path(wordpatch_trailingslashit($job_path) . $api_result_path, true, false);
            $file_path_relative_to_wp_root = wordpatch_normalize_to_wproot($wpenv_vars, $file_path);

            if(!$file_path_relative_to_wp_root) {
                continue;
            }

            // No need to write to the filesystem if this file hasn't changed.
            if(isset($api_result_single['data_equal']) && $api_result_single['data_equal']) {
                continue;
            }

            $sha1 = ($api_result_single['change_type'] === WORDPATCH_CHANGE_TYPE_DELETE || $api_result_single['data'] === null) ?
                null : (sha1(base64_decode($api_result_single['data'])));

            $changes[] = array(
                'path' => $api_result_path,
                'change_type' => $api_result_single['change_type'],
                'sha1' => $sha1
            );
        }

        return $changes;
    }
}

if(!function_exists('wordpatch_job_runner_process_apply_changes')) {
    function wordpatch_job_runner_process_apply_changes($wpenv_vars, $log_info, $api_result, $result) {
        $job_path = wordpatch_sanitize_unix_file_path(wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_root_dir')) .
            $log_info['path'], true, false);

        $wp_root_dir = wordpatch_trailingslashit(wordpatch_sanitize_unix_file_path($wpenv_vars['wp_root_dir'], true, false));

        $progress_api_remainder = 100 - WORDPATCH_PROGRESS_API_AMOUNT;

        $current_result_number = 0;
        $result_count = count($api_result['data']);

        // Loop through results and perform actions
        foreach($api_result['data'] as $api_result_single) {
            ++$current_result_number;

            // Math explained here:
            // x is our progress in terms of (op_number / op_count)
            // y is the amount of progress earned up until the point of the API result
            // z is how much progress to assign for our operations
            //
            // We essentially want to solve for z:
            //  x         z
            // ___ = _________
            // 100    100 - y
            //
            // After solving for z we simply add y in order to get our new percentage.
            // We have two variables, however. One for the percentage to set before the operation and one for after.
            $progress_number_before = WORDPATCH_PROGRESS_API_AMOUNT + floor(($progress_api_remainder * floor((($current_result_number - 1) / $result_count) * 100)) / 100);
            $progress_number_after = WORDPATCH_PROGRESS_API_AMOUNT + floor(($progress_api_remainder * floor(($current_result_number / $result_count) * 100)) / 100);

            $file_path = wordpatch_sanitize_unix_file_path(wordpatch_trailingslashit($job_path) . base64_decode($api_result_single['path']), true, false);
            $file_path_relative_to_wp_root = wordpatch_normalize_to_wproot($wpenv_vars, $file_path);

            // No need to write to the filesystem if this file hasn't changed.
            if(isset($api_result_single['data_equal']) && $api_result_single['data_equal']) {
                wordpatch_update_job_progress($wpenv_vars, $log_info['id'], $progress_number_after, "\"$file_path\" has not changed");
                continue;
            }

            // TODO: WTF? How did this happen?
            if(!$file_path_relative_to_wp_root) {
                continue;
            }

            // Go through each parent directory and make it if it doesn't exist already
            $segments = explode('/', $file_path_relative_to_wp_root);
            array_pop($segments);
            $seg_count = count($segments);

            for($seg_index = 0; $seg_index < $seg_count; $seg_index++) {
                $segments_current = array_slice($segments, 0, ($seg_index + 1));

                $dir_path = wordpatch_sanitize_unix_file_path($wp_root_dir . implode('/', $segments_current), true, false);
                $dir_path_relative_to_wp_root = wordpatch_normalize_to_wproot($wpenv_vars, $dir_path);

                // TODO: Change is_dir to wordpatch_is_dir maybe??
                if(file_exists($dir_path)) {
                    if(!is_dir($dir_path)) {
                        $result['error_list'][] = WORDPATCH_FILESYSTEM_FAILED_VERIFY_DIR;
                        $result['error_vars']['dir_path'] = $dir_path;
                        $result['error_vars']['dir_path_relative'] = $dir_path_relative_to_wp_root;

                        wordpatch_fail_job($wpenv_vars, $log_info, $result['error_list'], $result['error_vars']);
                        return $result;
                    }

                    continue;
                }

                if(!wordpatch_filesystem_mkdir($wpenv_vars, $dir_path_relative_to_wp_root, 'base')) {
                    $result['error_list'][] = WORDPATCH_FILESYSTEM_FAILED_WRITE_DIR;
                    $result['error_vars']['dir_path'] = $dir_path;
                    $result['error_vars']['dir_path_relative'] = $dir_path_relative_to_wp_root;

                    wordpatch_fail_job($wpenv_vars, $log_info, $result['error_list'], $result['error_vars']);
                    return $result;
                }
            }

            // TODO: Implement directory deletion if requested enough. Currently I think it is unnecessary.

            if($api_result_single['data'] === null) {
                wordpatch_update_job_progress($wpenv_vars, $log_info['id'], $progress_number_before, "deleting \"$file_path\"");

                if(file_exists($file_path) && !wordpatch_filesystem_delete($wpenv_vars, $file_path_relative_to_wp_root, 'base')) {
                    $result['error_list'][] = WORDPATCH_FILESYSTEM_FAILED_DELETE;
                    $result['error_vars']['file_path'] = $file_path;
                    $result['error_vars']['file_path_relative'] = $file_path_relative_to_wp_root;
                } else {
                    wordpatch_update_job_progress($wpenv_vars, $log_info['id'], $progress_number_after, "deleted \"$file_path\"");
                }
            } else {
                wordpatch_update_job_progress($wpenv_vars, $log_info['id'], $progress_number_before, "writing \"$file_path\"");

                if(!wordpatch_filesystem_put_contents($wpenv_vars, $file_path_relative_to_wp_root, 'base', base64_decode($api_result_single['data']))) {
                    $result['error_list'][] = WORDPATCH_FILESYSTEM_FAILED_WRITE;
                    $result['error_vars']['file_path'] = $file_path;
                    $result['error_vars']['file_path_relative'] = $file_path_relative_to_wp_root;
                } else {
                    wordpatch_update_job_progress($wpenv_vars, $log_info['id'], $progress_number_after, "wrote \"$file_path\"");
                }
            }

            if(!empty($result['error_list'])) {
                wordpatch_fail_job($wpenv_vars, $log_info, $result['error_list'], $result['error_vars']);
                return $result;
            }
        }

        return $result;
    }
}