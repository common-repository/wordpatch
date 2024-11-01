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
 * Implements the job runner functionality for the work action.
 */

// Include the jobrunner dependencies
include_once(dirname(__FILE__) . '/jobrunner/cleanup.php');
include_once(dirname(__FILE__) . '/jobrunner/process.php');
include_once(dirname(__FILE__) . '/jobrunner/rejection.php');

if(!function_exists('wordpatch_fail_job')) {
    function wordpatch_fail_job($wpenv_vars, $log_info, $error_list, $error_vars) {
        // Call the mailer handle failed job function
        $mail_result = wordpatch_mailer_handle_failed_job($wpenv_vars, $log_info, $error_list, $error_vars);
        $mail_errors = array();

        if(!$mail_result) {
            $mail_errors[] = WORDPATCH_MAILER_FAILED;
        }

        // Extend error list with mail errors
        $error_list = wordpatch_extend_errors($error_list, $mail_errors);

        $error_list_esc = wordpatch_esc_sql($wpenv_vars, json_encode($error_list));
        $error_vars_esc = wordpatch_esc_sql($wpenv_vars, json_encode($error_vars));
        $esc_log_id = wordpatch_esc_sql($wpenv_vars, $log_info['id']);

        $logs_table = wordpatch_logs_table($wpenv_vars);

        $esc_pending_status = wordpatch_esc_sql($wpenv_vars, WORDPATCH_CLEANUP_STATUS_PENDING);

        $update_query = "UPDATE `$logs_table` SET `error_list` = '$error_list_esc', `error_vars` = '$error_vars_esc', " .
            "`success` = '0', `finish_datetime` = UTC_TIMESTAMP(), `running` = NULL, `cleanup_status` = '$esc_pending_status' " .
            "WHERE `id` = '$esc_log_id'";

        wordpatch_db_query($wpenv_vars, $update_query);

        // Remove from the pending tables finally
        $pending_table = wordpatch_pending_table($wpenv_vars);
        $pending_patches_table = wordpatch_pending_patches_table($wpenv_vars);

        $esc_pending_id = wordpatch_esc_sql($wpenv_vars, $log_info['pending_id']);

        wordpatch_db_query($wpenv_vars, "DELETE FROM `$pending_table` WHERE `id` = '$esc_pending_id'");
        wordpatch_db_query($wpenv_vars, "DELETE FROM `$pending_patches_table` WHERE `pending_id` = '$esc_pending_id'");
    }
}

if(!function_exists('wordpatch_update_job_progress')) {
    function wordpatch_update_job_progress($wpenv_vars, $log_id, $progress_percent, $progress_note) {
        $logs_table = wordpatch_logs_table($wpenv_vars);

        $progress_percent = max(0, (int)$progress_percent);
        $progress_note = trim($progress_note);

        $esc_log_id = wordpatch_esc_sql($wpenv_vars, $log_id);
        $esc_progress_percent = wordpatch_esc_sql($wpenv_vars, $progress_percent);
        $esc_progress_note = wordpatch_esc_sql($wpenv_vars, $progress_note);

        $update_query = "UPDATE $logs_table SET `progress_percent` = '$esc_progress_percent', " .
            "`progress_note` = '$esc_progress_note', `progress_datetime` = UTC_TIMESTAMP() " .
            "WHERE `id` = '$esc_log_id'";

        wordpatch_db_query($wpenv_vars, $update_query);
    }
}

if(!function_exists('wordpatch_pass_job')) {
    function wordpatch_pass_job($wpenv_vars, $log_info, $changes) {
        // Call the mailer handle passed job function
        $mail_result = wordpatch_mailer_handle_passed_job($wpenv_vars, $log_info, $changes);
        $mail_errors = array();

        if(!$mail_result) {
            $mail_errors[] = WORDPATCH_MAILER_FAILED;
        }

        $logs_table = wordpatch_logs_table($wpenv_vars);

        // We need to store the base64 encoded paths instead
        $changes_raw = array();

        foreach($changes as $change_single) {
            $changes_raw[] = array(
                'path' => base64_encode($change_single['path']),
                'change_type' => $change_single['change_type'],
                'sha1' => $change_single['sha1']
            );
        }

        $error_list_esc = wordpatch_esc_sql($wpenv_vars, json_encode($mail_errors));
        $error_vars_esc = wordpatch_esc_sql($wpenv_vars, json_encode(array()));
        $esc_log_id = wordpatch_esc_sql($wpenv_vars, $log_info['id']);
        $esc_changes = wordpatch_esc_sql($wpenv_vars, json_encode($changes_raw));

        $judgement_part = "";

        // Auto approve if nothing changed, otherwise judgement is pending
        if(empty($changes)) {
            // TODO: Investigate this. I'm pretty sure I just saw it backlash. If so, just remove it.
            $esc_accepted = wordpatch_esc_sql($wpenv_vars, WORDPATCH_JUDGEMENT_ACCEPTED);

            $esc_pending_status = wordpatch_esc_sql($wpenv_vars, WORDPATCH_CLEANUP_STATUS_PENDING);

            $judgement_part = ", `judgement_decision` = '$esc_accepted', `judgement_datetime` = UTC_TIMESTAMP(), " .
                "`judgement_user` = '0', `judgement_pending` = '0', `cleanup_status` = '$esc_pending_status' ";
        } else {
            $judgement_part = ", `judgement_pending` = '1' ";
        }

        $update_query = "UPDATE $logs_table SET `error_list` = '$error_list_esc', `error_vars` = '$error_vars_esc', " .
            "`success` = '1', `running` = NULL, `progress_percent` = '100', `progress_note` = NULL, `progress_datetime` = UTC_TIMESTAMP(), " .
            "`changes` = '$esc_changes', `finish_datetime` = UTC_TIMESTAMP(){$judgement_part} WHERE `id` = '$esc_log_id'";

        wordpatch_db_query($wpenv_vars, $update_query);

        // Remove from the pending tables finally
        $pending_table = wordpatch_pending_table($wpenv_vars);
        $pending_patches_table = wordpatch_pending_patches_table($wpenv_vars);

        $esc_pending_id = wordpatch_esc_sql($wpenv_vars, $log_info['pending_id']);

        wordpatch_db_query($wpenv_vars, "DELETE FROM `$pending_table` WHERE `id` = '$esc_pending_id'");
        wordpatch_db_query($wpenv_vars, "DELETE FROM `$pending_patches_table` WHERE `pending_id` = '$esc_pending_id'");
    }
}

if(!function_exists('wordpatch_retry_job')) {
    function wordpatch_retry_job($wpenv_vars, $log_info) {
        $esc_log_id = wordpatch_esc_sql($wpenv_vars, $log_info['id']);
        $logs_table = wordpatch_logs_table($wpenv_vars);

        $update_query = "UPDATE $logs_table SET `running` = NULL, `should_retry` = '1' WHERE `id` = '$esc_log_id'";

        wordpatch_db_query($wpenv_vars, $update_query);
    }
}

if(!function_exists('wordpatch_should_retry_job')) {
    function wordpatch_should_retry_job($wpenv_vars, $log_info) {
        $attempt_count_int = max(0, (int)$log_info['attempt_count_int']);
        $retry_count_int = max(0, (int)$log_info['retry_count_int']);

        if($attempt_count_int >= $retry_count_int) {
            return false;
        }

        return true;
    }
}

if(!function_exists('wordpatch_job_runner')) {
    /**
     * Responsible for taking the currently running log information and either:
     * - Running the job (see `wordpatch_job_runner_process` for more information)
     * - Cleaning up the job (see `wordpatch_job_runner_cleanup` for more information)
     * - Rejecting the job (see `wordpatch_job_runner_rejection` for more information)
     *
     * This function is mainly pass-through, so please see the functions mentioned above for more information.
     *
     * @param $wpenv_vars
     * @param $log_info
     * @return array
     */
    function wordpatch_job_runner($wpenv_vars, $log_info) {
        // Process this as a cleanup if that's what is occurring.
        if($log_info['cleanup_status'] === WORDPATCH_CLEANUP_STATUS_ACTIVE) {
            return wordpatch_job_runner_cleanup($wpenv_vars, $log_info);
        }

        // Process this as a reject if that's what is occurring.
        if($log_info['is_reject']) {
            return wordpatch_job_runner_rejection($wpenv_vars, $log_info);
        }

        // Process this as a job since this is not a cleanup or a rejection.
        return wordpatch_job_runner_process($wpenv_vars, $log_info);
    }
}