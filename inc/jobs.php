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
 * Implements the shared job functionality.
 */

if(!function_exists('wordpatch_get_pending_job_ids')) {
    /**
     * Calculate a list of pending job IDs. Returns an empty array if there are no jobs pending currently.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_get_pending_job_ids($wpenv_vars) {
        // Calculate the name of the pending table.
        $pending_table = wordpatch_pending_table($wpenv_vars);

        // Query for the pending jobs.
        $pending_jobs = wordpatch_db_get_results($wpenv_vars, "SELECT `job_id` FROM {$pending_table} ORDER BY `datetime` ASC");

        // Create an array for the IDs.
        $pending_job_ids = array();

        // Loops through each result and add to the result array if it has not been added yet.
        foreach($pending_jobs as $pending_job_single) {
            // Skip this item if we have already added it to our array.
            if(in_array($pending_job_single['job_id'], $pending_job_ids)) {
                continue;
            }

            $pending_job_ids[] = $pending_job_single['job_id'];
        }

        // Return our array of IDs.
        return $pending_job_ids;
    }
}

if(!function_exists('wordpatch_get_pending_jobs')) {
    function wordpatch_get_pending_jobs($wpenv_vars)
    {
        $pending_table = wordpatch_pending_table($wpenv_vars);
        $jobs_table = wordpatch_jobs_table($wpenv_vars);
        $rejects_table = wordpatch_rejects_table($wpenv_vars);

        $query = "SELECT `p`.`job_id` as `job_id`, `p`.`id` as `pending_id`, `p`.`datetime` as `datetime` " .
            "FROM `$pending_table` `p` LEFT JOIN `$jobs_table` `pj` ON `pj`.`id` = `p`.`job_id` " .
            "LEFT JOIN `$rejects_table` `pr` ON `pr`.`pending_id` = `p`.`id` WHERE " .
            "(`pj`.`id` IS NOT NULL AND `pj`.`deleted` = '0') AND (`pr`.`pending_id` IS NULL) " .
            "ORDER BY `p`.`datetime` ASC";

        // Grab our result set
        $pending_results = wordpatch_db_get_results($wpenv_vars, $query);

        foreach($pending_results as &$pending_result) {
            $pending_result['job_id'] = wordpatch_sanitize_unique_id($pending_result['job_id']);
            $pending_result['pending_id'] = wordpatch_sanitize_unique_id($pending_result['pending_id']);
        }

        return $pending_results;
    }
}

if(!function_exists('wordpatch_get_busy_job_ids')) {
    /**
     * Calculate a list of busy job IDs. Jobs are busy if they are running and they are not yet stale.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_get_busy_job_ids($wpenv_vars) {
        // Calculate the log table name.
        $logs_table = wordpatch_logs_table($wpenv_vars);

        // Escape the variables for our query.
        $stale_timer = max(0, (int)WORDPATCH_STALE_JOB_TIMER);

        // Construct our select query.
        $selectquery = "SELECT `job_id` FROM {$logs_table} WHERE (`running` IS NOT NULL AND `running` = '1') AND " .
            "(UTC_TIMESTAMP() < DATE_ADD(`datetime`, INTERVAL $stale_timer SECOND)) ORDER BY `datetime` ASC";

        // Query for the busy jobs.
        $busy_jobs = wordpatch_db_get_results($wpenv_vars, $selectquery);

        // Create an array for our results.
        $busy_job_ids = array();

        // Loops through each result and add to the result array if it has not been added yet.
        foreach($busy_jobs as $busy_job_single) {
            // Skip this item if we have already added it to our array.
            if(in_array($busy_job_single['job_id'], $busy_job_ids)) {
                continue;
            }

            $busy_job_ids[] = $busy_job_single['job_id'];
        }

        // Return our array of IDs.
        return $busy_job_ids;
    }
}

if(!function_exists('wordpatch_fix_job_row')) {
    /**
     * Calculates a fixed version of $job_row which contains integers where necessary.
     *
     * @param $job_row
     */
    function wordpatch_fix_job_row(&$job_row) {
        $job_row['enabled'] = max(0, (int)$job_row['enabled']);
        $job_row['deleted'] = max(0, (int)$job_row['deleted']);
        $job_row['sort_order'] = max(0, (int)$job_row['sort_order']);
        $job_row['title'] = ($job_row['title'] === null || trim($job_row['title']) === '') ?
            '' : trim(base64_decode($job_row['title']));
        $job_row['path'] = ($job_row['path'] === null || trim($job_row['path']) === '') ?
            '' : trim(base64_decode($job_row['path']));
    }
}

if(!function_exists('wordpatch_get_job_by_id')) {
    /**
     * Get a job by ID. Returns null if the job is not found.
     *
     * @param $wpenv_vars
     * @param $job_id
     * @return null|array
     */
    function wordpatch_get_job_by_id($wpenv_vars, $job_id)
    {
        // Sanitize the job ID.
        $job_id = wordpatch_sanitize_unique_id($job_id);

        // If the job ID is empty, just return null.
        if($job_id === '') {
            return null;
        }

        // Calculate the job table name.
        $jobs_table = wordpatch_jobs_table($wpenv_vars);

        // Escape the job ID for our query.
        $esc_job_id = wordpatch_esc_sql($wpenv_vars, $job_id);

        // Construct the select query for our job.
        $query = "SELECT * FROM $jobs_table WHERE `id` = '$esc_job_id' LIMIT 1";

        // Grab the row from the database.
        $row = wordpatch_db_get_row($wpenv_vars, $query);

        // Return null if the row was not found.
        if(!$row) {
            return null;
        }

        // Fix the row to contain ints where necessary.
        wordpatch_fix_job_row($row);

        // Return the job row.
        return $row;
    }
}

if(!function_exists('wordpatch_get_job_from_jobs')) {
    function wordpatch_get_job_from_jobs($wpenv_vars, $jobs, $job_id) {
        foreach($jobs as $job_single) {
            if($job_single['id'] === $job_id) {
                return $job_single;
            }
        }

        return null;
    }
}

if(!function_exists('wordpatch_maybe_add_job_to_list')) {
    function wordpatch_maybe_add_job_to_list($wpenv_vars, &$list, $job, $limit = false) {
        if($job === null) {
            return;
        }

        if($limit !== false && count($list) >= $limit) {
            return;
        }

        $check_job = wordpatch_get_job_from_jobs($wpenv_vars, $list, $job['id']);

        if($check_job !== null) {
            return;
        }

        $list[] = $job;
    }
}

if(!function_exists('wordpatch_determine_recent_jobs')) {
    function wordpatch_determine_recent_jobs($wpenv_vars, $jobs, $running_log, $judgable_job_ids, $pending_jobs, $last_logbox) {
        $recent_limit = max(0, (int)WORDPATCH_RECENT_JOBS_LIMIT);

        $recent_jobs = array();

        if($running_log !== null) {
            $running_job = wordpatch_get_job_from_jobs($wpenv_vars, $jobs, $running_log['job_id']);

            wordpatch_maybe_add_job_to_list($wpenv_vars, $recent_jobs, $running_job, $recent_limit);
        }

        foreach($judgable_job_ids as $judgable_job_id) {
            $judgable_job = wordpatch_get_job_from_jobs($wpenv_vars, $jobs, $judgable_job_id);

            wordpatch_maybe_add_job_to_list($wpenv_vars, $recent_jobs, $judgable_job, $recent_limit);
        }

        foreach($pending_jobs as $pending_job) {
            $pending_job_actual = wordpatch_get_job_from_jobs($wpenv_vars, $jobs, $pending_job['job_id']);

            wordpatch_maybe_add_job_to_list($wpenv_vars, $recent_jobs, $pending_job_actual, $recent_limit);
        }

        foreach($last_logbox as $last_logbox_single) {
            $single_job = wordpatch_get_job_from_jobs($wpenv_vars, $jobs, $last_logbox_single['job_id']);

            wordpatch_maybe_add_job_to_list($wpenv_vars, $recent_jobs, $single_job, $recent_limit);
        }

        return $recent_jobs;
    }
}

if(!function_exists('wordpatch_get_jobs')) {
    /**
     * Calculate a list of all the jobs that exist in the system. Optionally can limit based on deleted state.
     * Will be returned in sort order.
     *
     * @param $wpenv_vars
     * @param null|bool $deleted
     * @return array
     */
    function wordpatch_get_jobs($wpenv_vars, $deleted = null) {
        // Calculate the jobs table name.
        $jobs_table = wordpatch_jobs_table($wpenv_vars);

        // Calculate our where portion of the query based on the delete parameter.
        $where_str = "";

        if($deleted !== null) {
            // Escape the variables for our query.
            $esc_deleted = wordpatch_esc_sql($wpenv_vars, $deleted ? '1' : '0');

            $where_str = "WHERE `deleted` = '$esc_deleted'";
        }

        // Construct our select query.
        $selectquery = "SELECT * FROM `$jobs_table` $where_str ORDER BY `sort_order` ASC";

        // Grab the results from the database.
        $job_rows = wordpatch_db_get_results($wpenv_vars, $selectquery);

        // Fix each row to contain ints where necessary.
        foreach($job_rows as &$job_row) {
            wordpatch_fix_job_row($job_row);
        }

        // Return our results array.
        return $job_rows;
    }
}

if(!function_exists('wordpatch_get_job_count')) {
    /**
     * Calculates the number of jobs that exist in the database. Optionally filters based on the deleted state.
     *
     * @param $wpenv_vars
     * @param null $deleted
     * @return mixed
     */
    function wordpatch_get_job_count($wpenv_vars, $deleted = null) {
        // Calculate the where portion of the select query based on $delete.
        $where_str = "";

        // If deleted is not null, then construct that part of the query.
        if($deleted !== null) {
            $esc_deleted = wordpatch_esc_sql($wpenv_vars, $deleted ? '1' : '0');

            $where_str = "WHERE `deleted` = '$esc_deleted'";
        }

        // Calculate the jobs table name.
        $jobs_table = wordpatch_jobs_table($wpenv_vars);

        // Calculate the number of jobs.
        return max(0, (int)wordpatch_db_get_var($wpenv_vars, "SELECT COUNT(*) FROM `$jobs_table` $where_str"));
    }
}

if(!function_exists('wordpatch_jobs_next_sort_order')) {
    /**
     * Calculate the new sort order value for the jobs table.
     *
     * @param $wpenv_vars
     * @return int
     */
    function wordpatch_jobs_next_sort_order($wpenv_vars) {
        // Default sort order is 1.
        $sort_order = 1;

        // Calculate the job table name.
        $jobs_table = wordpatch_jobs_table($wpenv_vars);

        // Query for the largest sort order in the DB.
        $sortquery = "SELECT sort_order FROM $jobs_table WHERE `deleted` = '0' ORDER BY `sort_order` DESC LIMIT 1";
        $last_sort_order = wordpatch_db_get_var($wpenv_vars, $sortquery);

        // If we found it, take the value and add one.
        if ($last_sort_order) {
            $sort_order = (int)$last_sort_order + 1;
        }

        // Return our calculated sort order value.
        return $sort_order;
    }
}

if(!function_exists('wordpatch_display_running')) {
    function wordpatch_display_running($wpenv_vars, $running) {
        return $running ? __wt($wpenv_vars, 'JOB_RUNNING') : __wt($wpenv_vars, 'JOB_NOT_RUNNING');
    }
}

if(!function_exists('wordpatch_display_progress')) {
    function wordpatch_display_progress($wpenv_vars, $progress_percent, $progress_note, $progress_datetime) {
        $progress_percent = $progress_percent === null ? 0 : max(0, (int)$progress_percent);
        $progress_note = $progress_note === null ? '' : trim($progress_note);
        $progress_datetime = $progress_datetime === null ? null : max(0, (int)$progress_datetime);

        if($progress_datetime === null || $progress_percent <= 0) {
            return __wt($wpenv_vars, 'DISPLAY_PROGRESS_NONE');
        }

        $percent_str = $progress_percent . '%';

        if($progress_percent >= 100) {
            $finish_datetime_str = __wt($wpenv_vars, 'DISPLAY_PROGRESS_FINISH_DATETIME_FORMAT',
                wordpatch_timestamp_to_readable($progress_datetime));

            return sprintf("%s\n%s", $percent_str, $finish_datetime_str);
        }

        $note_str = $progress_note === '' ? __wt($wpenv_vars, 'DISPLAY_PROGRESS_NOTE_NONE') : $progress_note;

        $datetime_str = __wt($wpenv_vars, 'DISPLAY_PROGRESS_DATETIME_FORMAT',
            wordpatch_timestamp_to_readable($progress_datetime));

        return sprintf("%s\n%s\n%s", $percent_str, $note_str, $datetime_str);
    }
}

if(!function_exists('wordpatch_display_attempts')) {
    function wordpatch_display_attempts($wpenv_vars, $retry_count, $attempt_count_int) {
        $retry_count_int = wordpatch_job_retry_count_to_int($wpenv_vars, $retry_count);

        return sprintf('%d / %d', $attempt_count_int, $retry_count_int);
    }
}

if(!function_exists('wordpatch_display_should_retry')) {
    function wordpatch_display_should_retry($wpenv_vars, $should_retry) {
        return $should_retry ? __wt($wpenv_vars, 'YES') : __wt($wpenv_vars, 'NO');
    }
}

if(!function_exists('wordpatch_display_init_reason')) {
    /**
     * @param $wpenv_vars
     * @param $init_reason
     * @return mixed
     */
    function wordpatch_display_init_reason($wpenv_vars, $init_reason) {
        return WORDPATCH_INIT_REASON_MANUAL === $init_reason ?
            __wt($wpenv_vars, 'INIT_REASON_MANUAL') : __wt($wpenv_vars, 'INIT_REASON_UPDATE');
    }
}

if(!function_exists('wordpatch_dedup_init_subject_list')) {
    function wordpatch_dedup_init_subject_list($wpenv_vars, $init_subject_list) {
        $subject_list = array();

        foreach($init_subject_list as $single) {
            $already_found = false;

            foreach($subject_list as $subject) {
                if($subject['type'] === $single['type'] && $subject['path'] === $single['path']) {
                    $already_found = true;
                    break;
                }
            }

            if($already_found) {
                continue;
            }

            $subject_list[] = array(
                'type' => $single['type'],
                'path' => $single['path']
            );
        }

        return $subject_list;
    }
}

if(!function_exists('wordpatch_display_init_subject_list')) {
    /**
     * @param $wpenv_vars
     * @param $init_subject_list
     * @return mixed
     */
    function wordpatch_display_init_subject_list($wpenv_vars, $init_subject_list) {
        $init_subject_list = $init_subject_list === null ? array() :
            wordpatch_dedup_init_subject_list($wpenv_vars, $init_subject_list);

        $display_list = "";

        $is_first = true;

        foreach($init_subject_list as $subject) {
            if(!$is_first) {
                $display_list .= "\n";
            }

            $display_list .= __wt($wpenv_vars, 'INIT_SUBJECT_LIST_LINE_' . strtoupper($subject['type']), $subject['path']);
            $is_first = false;
        }

        if(trim($display_list) === '') {
            return __wt($wpenv_vars, 'INIT_SUBJECT_LIST_EMPTY');
        }

        return $display_list;
    }
}

if(!function_exists('wordpatch_display_init_subject_type')) {
    function wordpatch_display_init_subject_type($wpenv_vars, $init_subject_type) {
        return __wt($wpenv_vars, 'INIT_SUBJECT_TYPE_' . strtoupper($init_subject_type));
    }
}

if(!function_exists('wordpatch_display_changes')) {
    /**
     * @param $wpenv_vars
     * @param $changes
     * @return mixed
     */
    function wordpatch_display_changes($wpenv_vars, $changes) {
        $changes = empty($changes) ? array() : $changes;

        $display_list = "";

        $is_first = true;

        foreach($changes as $change) {
            if(!$is_first) {
                $display_list .= "\n";
            }

            $display_change_type = wordpatch_display_change_type($wpenv_vars, $change['change_type']);
            $display_list .= sprintf("%s [%s] (SHA1: %s)", $change['path'], $display_change_type, $change['sha1']);

            $is_first = false;
        }

        if(trim($display_list) === '') {
            return __wt($wpenv_vars, 'DISPLAY_CHANGES_NONE');
        }

        return $display_list;
    }
}

if(!function_exists('wordpatch_display_judgement_pending')) {
    function wordpatch_display_judgement_pending($wpenv_vars, $judgement_pending) {
        return $judgement_pending ? __wt($wpenv_vars, 'YES') : __wt($wpenv_vars, 'NO');
    }
}

if(!function_exists('wordpatch_display_judgement_datetime')) {
    function wordpatch_display_judgement_datetime($wpenv_vars, $judgement_datetime) {
        return $judgement_datetime === null ? __wt($wpenv_vars, 'NOT_AVAILABLE_ABBR') :
            wordpatch_timestamp_to_readable($judgement_datetime);
    }
}

if(!function_exists('wordpatch_display_errors')) {
    /**
     * @param $wpenv_vars
     * @param $error_list
     * @param $error_vars
     * @return mixed
     */
    function wordpatch_display_errors($wpenv_vars, $where, $error_list, $error_vars) {
        $error_list = empty($error_list) ? array() : $error_list;
        $error_vars = empty($error_vars) ? array() : $error_vars;

        $display_list = "";

        $is_first = true;

        foreach($error_list as $error_single) {
            if(!$is_first) {
                $display_list .= "\n";
            }

            $display_list .= wordpatch_translate_error($wpenv_vars, $error_single, $where, $error_vars);

            $is_first = false;
        }

        if(trim($display_list) === '') {
            return __wt($wpenv_vars, 'DISPLAY_ERRORS_NONE');
        }

        return $display_list;
    }
}

if(!function_exists('wordpatch_display_judgement_user')) {
    function wordpatch_display_judgement_user($wpenv_vars, $judgement_decision, $judgement_user) {
        return $judgement_decision === null ? __wt($wpenv_vars, 'NOT_AVAILABLE_ABBR') :
            wordpatch_get_display_user($wpenv_vars, $judgement_user);
    }
}

if(!function_exists('wordpatch_display_rejection_pending')) {
    function wordpatch_display_rejection_pending($wpenv_vars, $rejection_pending, $rejection_pending_datetime,
                                                 $rejection_pending_user_id) {
        if(!$rejection_pending) {
            return __wt($wpenv_vars, 'NO');
        }

        return __wt($wpenv_vars, 'REJECTION_PENDING_FORMAT', wordpatch_timestamp_to_readable($rejection_pending_datetime),
            wordpatch_get_display_user($wpenv_vars, $rejection_pending_user_id));
    }
}

if(!function_exists('wordpatch_display_backups')) {
    function wordpatch_display_backups($wpenv_vars, $logbox_row, $rollback_rows) {
        $rollback_rows = empty($rollback_rows) ? array() : $rollback_rows;

        $display_list = "";

        $is_first = true;

        foreach($rollback_rows as $rollback_row) {
            if(!$is_first) {
                $display_list .= "\n";
            }

            if($rollback_row['action'] === WORDPATCH_ROLLBACK_ACTION_WRITE_FILE) {
                $display_list .= __wt($wpenv_vars, 'DISPLAY_BACKUP_FORMAT_WRITE_FILE', $rollback_row['path'], $rollback_row['location']);
            } else if($rollback_row['action'] === WORDPATCH_ROLLBACK_ACTION_DELETE_FILE) {
                $display_list .= __wt($wpenv_vars, 'DISPLAY_BACKUP_FORMAT_DELETE_FILE', $rollback_row['path']);
            } else if($rollback_row['action'] === WORDPATCH_ROLLBACK_ACTION_DELETE_EMPTY_DIR) {
                $display_list .= __wt($wpenv_vars, 'DISPLAY_BACKUP_FORMAT_DELETE_EMPTY_DIR', $rollback_row['path']);
            }

            $is_first = false;
        }

        if(trim($display_list) === '') {
            return __wt($wpenv_vars, 'DISPLAY_BACKUPS_NONE');
        }

        return $display_list;
    }
}

if(!function_exists('wordpatch_display_rollback_action')) {
    function wordpatch_display_rollback_action($wpenv_vars, $rollback_action) {
        if($rollback_action === WORDPATCH_ROLLBACK_ACTION_WRITE_FILE) {
            return __wt($wpenv_vars, 'DISPLAY_ROLLBACK_ACTION_WRITE_FILE');
        }

        if($rollback_action === WORDPATCH_ROLLBACK_ACTION_DELETE_FILE) {
            return __wt($wpenv_vars, 'DISPLAY_ROLLBACK_ACTION_DELETE_FILE');
        }

        if($rollback_action === WORDPATCH_ROLLBACK_ACTION_DELETE_EMPTY_DIR) {
            return __wt($wpenv_vars, 'DISPLAY_ROLLBACK_ACTION_DELETE_EMPTY_DIR');
        }

        return __wt($wpenv_vars, 'NOT_AVAILABLE_ABBR');
    }
}

if(!function_exists('wordpatch_display_cleanup_status')) {
    function wordpatch_display_cleanup_status($wpenv_vars, $cleanup_status) {
        if($cleanup_status === WORDPATCH_CLEANUP_STATUS_NONE) {
            return __wt($wpenv_vars, 'CLEANUP_STATUS_NONE');
        }

        if($cleanup_status === WORDPATCH_CLEANUP_STATUS_PENDING) {
            return __wt($wpenv_vars, 'CLEANUP_STATUS_PENDING');
        }

        if($cleanup_status === WORDPATCH_CLEANUP_STATUS_ACTIVE) {
            return __wt($wpenv_vars, 'CLEANUP_STATUS_ACTIVE');
        }

        return __wt($wpenv_vars, 'NOT_AVAILABLE_ABBR');
    }
}

if(!function_exists('wordpatch_display_judgement_decision')) {
    function wordpatch_display_judgement_decision($wpenv_vars, $judgement_decision) {
        return $judgement_decision === null ? __wt($wpenv_vars, 'NOT_AVAILABLE_ABBR') :
            ($judgement_decision === WORDPATCH_JUDGEMENT_REJECTED ? __wt($wpenv_vars, 'JUDGEMENT_DECISION_REJECTED') :
                __wt($wpenv_vars, 'JUDGEMENT_DECISION_ACCEPTED'));
    }
}

if(!function_exists('wordpatch_display_change_type')) {
    /**
     * @param $wpenv_vars
     * @param $change_type
     * @return mixed
     */
    function wordpatch_display_change_type($wpenv_vars, $change_type) {
        if($change_type === WORDPATCH_CHANGE_TYPE_CREATE) {
            return __wt($wpenv_vars, 'CHANGE_TYPE_CREATE');
        }

        if($change_type === WORDPATCH_CHANGE_TYPE_DELETE) {
            return __wt($wpenv_vars, 'CHANGE_TYPE_DELETE');
        }

        if($change_type === WORDPATCH_CHANGE_TYPE_EDIT) {
            return __wt($wpenv_vars, 'CHANGE_TYPE_EDIT');
        }

        return __wt($wpenv_vars, 'NOT_AVAILABLE_ABBR');
    }
}

if(!function_exists('wordpatch_add_pending_job')) {
    /**
     * Add a pending job to the database. Returns a boolean indicating success.
     *
     * @param $wpenv_vars
     * @param $job_id
     * @param $init_reason
     * @param $init_subject_list
     * @param $init_user
     * @param $schedule_id
     * @return bool
     */
    function wordpatch_add_pending_job($wpenv_vars, $job_id, $init_reason, $init_subject_list, $init_user, $schedule_id) {
        $pending_table = wordpatch_pending_table($wpenv_vars);
        $jobs_table = wordpatch_jobs_table($wpenv_vars);

        $job_id = wordpatch_sanitize_unique_id($job_id);
        $schedule_id = wordpatch_sanitize_unique_id($schedule_id);

        if($schedule_id === '') {
            $schedule_id = null;
        }

        if($job_id === '') {
            return false;
        }

        $job_info = wordpatch_get_job_by_id($wpenv_vars, $job_id);

        if(!$job_info) {
            return false;
        }

        // Insert into pending table and pull job information from the jobs table.
        $init_user = max(0, (int)$init_user);
        $init_user = $init_user <= 0 ? null : $init_user;

        $maintenance_mode = $job_info['maintenance_mode'];
        if($maintenance_mode === wordpatch_job_maintenance_mode_inherit()) {
            $maintenance_mode = wordpatch_calculate_maintenance_mode($wpenv_vars);
        }

        $retry_count = $job_info['retry_count'];
        if($retry_count === wordpatch_job_retry_count_inherit()) {
            $retry_count = wordpatch_calculate_retry_count($wpenv_vars);
        }

        $update_cooldown = $job_info['update_cooldown'];
        if($update_cooldown === wordpatch_job_update_cooldown_inherit()) {
            $update_cooldown = wordpatch_calculate_update_cooldown($wpenv_vars);
        }

        $mode = $job_info['mode'];
        if($mode === wordpatch_job_mode_inherit()) {
            $mode = wordpatch_calculate_mode($wpenv_vars);
        }

        $timer = $job_info['timer'];
        if($timer === wordpatch_job_timer_inherit()) {
            $timer = wordpatch_calculate_timer($wpenv_vars);
        }

        $timer_int = wordpatch_job_timer_seconds($timer);
        $timer_int = max(0, (int)$timer_int);

        // Create an ID for our pending record
        $pending_id = wordpatch_unique_id();

        $esc_pending_id = wordpatch_esc_sql($wpenv_vars, $pending_id);
        $enc_init_user = wordpatch_encode_sql($wpenv_vars, $init_user);
        $esc_init_reason = wordpatch_esc_sql($wpenv_vars, $init_reason);
        $esc_job_id = wordpatch_esc_sql($wpenv_vars, $job_id);
        $esc_maintenance_mode = wordpatch_esc_sql($wpenv_vars, $maintenance_mode);
        $esc_retry_count = wordpatch_esc_sql($wpenv_vars, $retry_count);
        $esc_update_cooldown = wordpatch_esc_sql($wpenv_vars, $update_cooldown);
        $esc_mode = wordpatch_esc_sql($wpenv_vars, $mode);
        $esc_timer = wordpatch_esc_sql($wpenv_vars, $timer);
        $esc_timer_int = wordpatch_esc_sql($wpenv_vars, $timer_int);
        $enc_init_subject_list = wordpatch_encode_sql($wpenv_vars, ($init_subject_list === null || !is_array($init_subject_list)) ?
            null : json_encode($init_subject_list));
        $enc_schedule_id = wordpatch_encode_sql($wpenv_vars, $schedule_id);

        $insertquery = "INSERT INTO `$pending_table` (`id`, `job_id`, `schedule_id`, `init_reason`, `init_subject_list`, " .
            "`init_user`, `datetime`, `maintenance_mode`, `retry_count`, `update_cooldown`, `binary_mode`, `mode`, `timer`, " .
            "`timer_int`, `title`, `path`) SELECT '$esc_pending_id' as `id`, `j`.`id` as `job_id`, $enc_schedule_id as `schedule_id`, " .
            "'$esc_init_reason' as `init_reason`, $enc_init_subject_list as `init_subject_list`, " .
            "$enc_init_user as `init_user`, UTC_TIMESTAMP() as `datetime`, '$esc_maintenance_mode' as `maintenance_mode`, " .
            "'$esc_retry_count' as `retry_count`, '$esc_update_cooldown' as `update_cooldown`, `j`.`binary_mode` as `binary_mode`, " .
            "'$esc_mode' as `mode`, '$esc_timer' as `timer`, '$esc_timer_int' as `timer_int`, `j`.`title` as `title`, `j`.`path` as `path` " .
            "FROM `$jobs_table` `j` WHERE `j`.`id` = '$esc_job_id'";

        $query_result = wordpatch_db_query($wpenv_vars, $insertquery);

        // This is actually a success, it just means there was a duplicate key (schedule_id)
        if(!$query_result) {
            return true;
        }

        $affected_rows = wordpatch_db_affected_rows($wpenv_vars);

        if(!$affected_rows) {
            return false;
        }

        // Grab the patches for this job
        $patches = wordpatch_get_patches_by_job_id($wpenv_vars, $job_id);

        $pending_patches_table = wordpatch_pending_patches_table($wpenv_vars);
        
        // Loop through each patch and insert a pending patch
        foreach($patches as $patch) {
            // Create an IDD for the pending patches entry
            $pending_patches_id = wordpatch_unique_id();

            $esc_pending_patches_id = wordpatch_esc_sql($wpenv_vars, $pending_patches_id);
            $esc_patch_title = wordpatch_esc_sql($wpenv_vars, base64_encode($patch['title']));
            $esc_patch_type = wordpatch_esc_sql($wpenv_vars, $patch['patch_type']);
            $esc_patch_location = wordpatch_esc_sql($wpenv_vars, $patch['patch_location']);
            $esc_patch_size = wordpatch_esc_sql($wpenv_vars, $patch['patch_size']);
            $esc_sort_order = wordpatch_esc_sql($wpenv_vars, $patch['sort_order']);

            $insertquery2 = "INSERT INTO `$pending_patches_table` (`id`, `pending_id`, `title`, `patch_type`, `patch_location`, " .
                "`patch_size`, `sort_order`, `job_id`) VALUES ('$esc_pending_patches_id', '$esc_pending_id', '$esc_patch_title', " .
                "'$esc_patch_type', '$esc_patch_location', '$esc_patch_size', '$esc_sort_order', '$esc_job_id')";

            wordpatch_db_query($wpenv_vars, $insertquery2);
        }

        return true;
    }
}

if(!function_exists('wordpatch_get_jobs_by_ids')) {
    function wordpatch_get_jobs_by_ids($wpenv_vars, $job_ids)
    {
        $jobs_table = wordpatch_jobs_table($wpenv_vars);

        // First sanitize the job_ids
        $new_job_ids = wordpatch_sanitize_unique_ids($job_ids);

        // If there are no job ids in our list, simply return an empty array
        if(empty($new_job_ids)) {
            return array();
        }

        // Construct a valid IN query string
        $in_str = wordpatch_get_sql_in($wpenv_vars, $new_job_ids);

        // Grab our result set
        $job_results = wordpatch_db_get_results($wpenv_vars, "SELECT * FROM `{$jobs_table}` WHERE `id` IN({$in_str})");

        foreach($job_results as &$job_result) {
            wordpatch_fix_job_row($job_result);
        }

        // Return them ordered based on the ID list order
        return wordpatch_get_ordered_rows($job_results, $new_job_ids);
    }
}

if(!function_exists('wordpatch_render_recent_jobs')) {
    function wordpatch_render_recent_jobs($wpenv_vars, $recent_jobs, $progress_model) {
?>
        <div class="wordpatch_jobs_container">
            <div class="wordpatch_jobs wordpatch_jobs_compact">
                <?php foreach ($recent_jobs as $job_single) { ?>
                    <div class="wordpatch_job_ctr" data-job-id="<?php echo($job_single['id']); ?>">
                        <div class="wordpatch_job_ctr_flex">
                            <div class="wordpatch_job_ctr_left">
                                <div class="wordpatch_job_labels">
                                    <p class="wordpatch_job_label"><?php echo(__wten($wpenv_vars, 'JOBS_TABLE_TITLE')); ?></p>
                                    <p class="wordpatch_job_label"><?php echo(__wten($wpenv_vars, 'JOBS_TABLE_PATH')); ?></p>
                                </div>
                                <div class="wordpatch_job_meta">
                                    <a href="<?php echo(wordpatch_jobdetail_uri($wpenv_vars, $job_single['id'])); ?>" class="wordpatch_job_title">
                                        <?php echo(htmlspecialchars($job_single['title'])); ?></a>
                                    <p class="wordpatch_job_path"><?php echo(htmlspecialchars(wordpatch_job_display_path($job_single['path']))); ?></p>
                                </div>
                            </div>
                            <div class="wordpatch_job_ctr_right">
                                <div class="wordpatch_text_right">
                                    <?php
                                    $job_state = wordpatch_determine_job_progress_state($progress_model, $job_single['id']);
                                    ?>
                                    <?php if($job_state['state'] === WORDPATCH_PROGRESS_STATE_RUNNING) { ?>
                                        <a class="wordpatch_button wordpatch_job_button wordpatch_job_state wordpatch_status_green"
                                           href="<?php echo(wordpatch_logdetail_uri($wpenv_vars, $job_state['log_id'])); ?>">
                                            <i class="fa fa-circle-o" aria-hidden="true"></i>
                                            <span><?php echo(__wten($wpenv_vars, 'JOBS_TABLE_RUNNING_LINK')); ?></span>
                                        </a>
                                    <?php } else if($job_state['state'] === WORDPATCH_PROGRESS_STATE_JUDGABLE) { ?>
                                        <a class="wordpatch_button wordpatch_job_button wordpatch_job_state wordpatch_job_alert"
                                           href="<?php echo(wordpatch_judge_uri($wpenv_vars, $job_state['log_id'])); ?>">
                                            <i class="fa fa-warning" aria-hidden="true"></i>
                                            <span><?php echo(__wten($wpenv_vars, 'JOBS_TABLE_JUDGE_LINK')); ?></span>
                                        </a>
                                    <?php } else if($job_state['state'] === WORDPATCH_PROGRESS_STATE_PENDING) { ?>
                                        <a class="wordpatch_button wordpatch_job_button wordpatch_job_state wordpatch_status_blue"
                                           href="<?php echo(wordpatch_logdetail_uri($wpenv_vars, $job_state['pending_id'])); ?>">
                                            <i class="fa fa-clock-o" aria-hidden="true"></i>
                                            <span><?php echo(__wten($wpenv_vars, 'JOBS_TABLE_PENDING_LINK')); ?></span>
                                        </a>
                                    <?php } else if($job_state['state'] === WORDPATCH_PROGRESS_STATE_FAILED) { ?>
                                        <a class="wordpatch_button wordpatch_job_button wordpatch_job_state wordpatch_status_red"
                                           href="<?php echo(wordpatch_logdetail_uri($wpenv_vars, $job_state['log_id'])); ?>">
                                            <i class="fa fa-times-circle" aria-hidden="true"></i>
                                            <span><?php echo(__wten($wpenv_vars, 'JOBS_TABLE_FAILED')); ?></span>
                                        </a>
                                    <?php } else if($job_state['state'] === WORDPATCH_PROGRESS_STATE_SUCCESS) { ?>
                                        <a class="wordpatch_button wordpatch_job_button wordpatch_job_state wordpatch_status_green"
                                           href="<?php echo(wordpatch_logdetail_uri($wpenv_vars, $job_state['log_id'])); ?>">
                                            <i class="fa fa-thumbs-up" aria-hidden="true"></i>
                                            <span><?php echo(__wten($wpenv_vars, 'JOBS_TABLE_SUCCESS')); ?></span>
                                        </a>
                                    <?php } ?>
                                    <a class="wordpatch_button wordpatch_job_button"
                                       href="<?php echo(wordpatch_logs_uri($wpenv_vars, $job_single['id'])); ?>">
                                        <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                        <span><?php echo(__wten($wpenv_vars, 'JOBS_TABLE_LOGS_LINK')); ?></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
<?php
    }
}

if(!function_exists('wordpatch_job_display_path')) {
    function wordpatch_job_display_path($path) {
        $path = wordpatch_sanitize_unix_file_path($path);

        if($path === '') {
            return '/';
        }

        return $path;
    }
}