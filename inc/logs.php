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
 * Implements the shared job log functionality.
 */

if(!function_exists('wordpatch_get_running_log')) {
    function wordpatch_get_running_log($wpenv_vars)
    {
        $rejects_table = wordpatch_rejects_table($wpenv_vars);
        $logs_table = wordpatch_logs_table($wpenv_vars);

        $partialselect = "SELECT `l`.*, IF(`r`.`pending_id` IS NOT NULL, '1', '0') as `is_reject`, " .
            "IF(`r`.`pending_id` IS NOT NULL, `r`.`user_id`, NULL) as `reject_user` FROM `$logs_table` `l` " .
            "LEFT JOIN `$rejects_table` `r` ON `r`.`pending_id` = `l`.`pending_id` WHERE `running` = '1' LIMIT 1";

        $running_log_row = wordpatch_db_get_row($wpenv_vars, $partialselect);

        if (!$running_log_row) {
            return null;
        }

        wordpatch_fix_running_log_row($running_log_row);

        return $running_log_row;
    }
}

if(!function_exists('wordpatch_fix_log_row')) {
    /**
     * Calculates a fixed version of $log_row which contains proper types where necessary.
     *
     * @param $log_row
     */
    function wordpatch_fix_log_row(&$log_row) {
        $log_row['running'] = $log_row['running'] === null ? null : max(0, (int)$log_row['running']);

        $log_row['init_user'] = $log_row['init_user'] === null ? null : max(0, (int)$log_row['init_user']);

        $log_row['init_subject_list'] = ($log_row['init_subject_list'] === null || trim($log_row['init_subject_list']) === '') ?
            null : wordpatch_json_decode(trim($log_row['init_subject_list']));

        $log_row['attempt_count_int'] = $log_row['attempt_count_int'] === null ? null :
            max(0, (int)$log_row['attempt_count_int']);

        $log_row['retry_count_int'] = $log_row['retry_count_int'] === null ? null :
            max(0, (int)$log_row['retry_count_int']);

        $log_row['error_list'] = ($log_row['error_list'] === null || trim($log_row['error_list']) === '') ?
            null : wordpatch_json_decode(trim($log_row['error_list']));

        $log_row['error_vars'] = ($log_row['error_vars'] === null || trim($log_row['error_vars']) === '') ?
            null : wordpatch_json_decode(trim($log_row['error_vars']));

        $log_row['reject_error_list'] = ($log_row['reject_error_list'] === null || trim($log_row['reject_error_list']) === '') ?
            null : wordpatch_json_decode(trim($log_row['reject_error_list']));

        $log_row['reject_error_vars'] = ($log_row['reject_error_vars'] === null || trim($log_row['reject_error_vars']) === '') ?
            null : wordpatch_json_decode(trim($log_row['reject_error_vars']));

        $log_row['cleanup_error_list'] = ($log_row['cleanup_error_list'] === null || trim($log_row['cleanup_error_list']) === '') ?
            null : wordpatch_json_decode(trim($log_row['cleanup_error_list']));

        $log_row['cleanup_error_vars'] = ($log_row['cleanup_error_vars'] === null || trim($log_row['cleanup_error_vars']) === '') ?
            null : wordpatch_json_decode(trim($log_row['cleanup_error_vars']));

        $log_row['datetime'] = $log_row['datetime'] === null ? null :
            wordpatch_convert_string_to_timestamp($log_row['datetime']);

        $log_row['finish_datetime'] = $log_row['finish_datetime'] === null ? null :
            wordpatch_convert_string_to_timestamp($log_row['finish_datetime']);

        $log_row['running_datetime'] = $log_row['running_datetime'] === null ? null :
            wordpatch_convert_string_to_timestamp($log_row['running_datetime']);

        $log_row['judgement_datetime'] = $log_row['judgement_datetime'] === null ? null :
            wordpatch_convert_string_to_timestamp($log_row['judgement_datetime']);

        $log_row['judgement_pending'] = $log_row['judgement_pending'] === null ? null :
            max(0, (int)$log_row['judgement_pending']);

        $log_row['cleanup_status'] = $log_row['cleanup_status'] === null ? null :
            max(0, (int)$log_row['cleanup_status']);

        $log_row['success'] = $log_row['success'] === null ? null :
            max(0, (int)$log_row['success']);

        $log_row['progress_percent'] = $log_row['progress_percent'] === null ? null :
            max(0, (int)$log_row['progress_percent']);

        $log_row['progress_datetime'] = $log_row['progress_datetime'] === null ? null :
            wordpatch_convert_string_to_timestamp($log_row['progress_datetime']);

        $log_row['should_retry'] = $log_row['should_retry'] === null ? null :
            max(0, (int)$log_row['should_retry']);

        $log_row['timer_int'] = $log_row['timer_int'] === null ? null :
            max(0, (int)$log_row['timer_int']);

        $log_row['title'] = ($log_row['title'] === null || trim($log_row['title']) === '') ? '' :
            trim(base64_decode($log_row['title']));

        $log_row['path'] = ($log_row['path'] === null || trim($log_row['path']) === '') ? '' :
            trim(base64_decode($log_row['path']));

        $log_row['changes'] = ($log_row['changes'] === null || trim($log_row['changes']) === '') ?
            null : wordpatch_json_decode(trim($log_row['changes']));

        if(is_array($log_row['changes'])) {
            foreach($log_row['changes'] as &$change_single) {
                $change_single['path'] = base64_decode($change_single['path']);
            }
        }
    }
}

if(!function_exists('wordpatch_fix_running_log_row')) {
    function wordpatch_fix_running_log_row(&$running_log_row) {
        wordpatch_fix_log_row($running_log_row);

        $running_log_row['is_reject'] = max(0, (int)$running_log_row['is_reject']);

        $running_log_row['reject_user'] = $running_log_row['reject_user'] === null ? null :
            max(0, (int)$running_log_row['reject_user']);
    }
}

if(!function_exists('wordpatch_fix_logbox_row')) {
    function wordpatch_fix_logbox_row(&$logbox_row) {
        wordpatch_fix_log_row($logbox_row);

        $logbox_row['rejection_pending_datetime'] = $logbox_row['rejection_pending_datetime'] === null ? null :
            wordpatch_convert_string_to_timestamp($logbox_row['rejection_pending_datetime']);

        $logbox_row['rejection_pending'] = max(0, (int)$logbox_row['rejection_pending']);

        $logbox_row['rejection_pending_user_id'] = $logbox_row['rejection_pending_user_id'] === null ? null :
            max(0, (int)$logbox_row['rejection_pending_user_id']);
    }
}

if(!function_exists('wordpatch_get_logs_by_ids')) {
    function wordpatch_get_logs_by_ids($wpenv_vars, $log_ids)
    {
        $logs_table = wordpatch_logs_table($wpenv_vars);

        // First sanitize the log_ids
        $new_log_ids = wordpatch_sanitize_unique_ids($log_ids);

        // If there are no log ids in our list, simply return an empty array
        if(empty($new_log_ids)) {
            return array();
        }

        // Construct a valid IN query string
        $in_str = wordpatch_get_sql_in($wpenv_vars, $new_log_ids);

        // Grab our result set
        $log_results = wordpatch_db_get_results($wpenv_vars, "SELECT * FROM `{$logs_table}` WHERE `id` IN({$in_str})");

        foreach($log_results as &$log_result) {
            wordpatch_fix_log_row($log_result);
        }

        // Return them ordered based on the ID list order
        return wordpatch_get_ordered_rows($log_results, $new_log_ids);
    }
}

if(!function_exists('wordpatch_get_logbox')) {
    /**
     * Grab a specific page of the logbox from the database.
     *
     * @param $wpenv_vars
     * @param $job_id
     * @param $page
     * @return array
     */
    function wordpatch_get_logbox($wpenv_vars, $job_id, $page) {
        $job_id = wordpatch_sanitize_unique_id($job_id);

        if($job_id === '') {
            return array();
        }

        $page = max(1, (int)$page);
        $logs_table = wordpatch_logs_table($wpenv_vars);
        $rejects_table = wordpatch_rejects_table($wpenv_vars);
        $pending_table = wordpatch_pending_table($wpenv_vars);

        $esc_job_id = wordpatch_esc_sql($wpenv_vars, $job_id);

        // Query that grabs all the pending entries for the job (no pagination, handled below)
        $pending_query = "SELECT NULL as `id`, `p`.`id` as `pending_id`, `p`.`job_id` as `job_id`, " .
            "`p`.`datetime` as `datetime`, NULL as `running_datetime`, NULL as `finish_datetime`, NULL as `running`, " .
            "NULL as `progress_percent`, NULL as `progress_note`, NULL as `progress_datetime`, " .
            "`p`.`init_reason` as `init_reason`, `p`.`init_user` as `init_user`, " .
            "`p`.`init_subject_list` as `init_subject_list`, NULL as `cleanup_status`, NULL as `changes`, " .
            "NULL as `judgement_decision`, NULL as `judgement_datetime`, NULL as `judgement_user`, " .
            "NULL as `judgement_pending`, NULL as `success`, NULL as `error_list`, NULL as `error_vars`, " .
            "NULL as `reject_error_list`, NULL as `reject_error_vars`, NULL as `cleanup_error_list`, " .
            "NULL as `cleanup_error_vars`, `p`.`maintenance_mode` as `maintenance_mode`, " .
            "`p`.`retry_count` as `retry_count`, NULL as `retry_count_int`, NULL as `attempt_count_int`, " .
            "NULL as `should_retry`, `p`.`update_cooldown` as `update_cooldown`, `p`.`mode` as `mode`, " .
            "`p`.`timer` as `timer`, NULL as `timer_int`, `p`.`title` as `title`, `p`.`path` as `path`, " .
            "`p`.`binary_mode` as `binary_mode`, 0 as `rejection_pending`, NULL as `rejection_pending_datetime`, " .
            "NULL as `rejection_pending_user_id` FROM `$pending_table` `p` LEFT JOIN `$logs_table` `pl` " .
            "ON `pl`.`pending_id` = `p`.`id` WHERE `p`.`job_id` = '$esc_job_id' AND " .
            "`pl`.`id` IS NULL ORDER BY `datetime` DESC";

        // Grab the pending rows
        $pending_rows = wordpatch_db_get_results($wpenv_vars, $pending_query);

        // Fix them to use the proper types where necessary
        foreach($pending_rows as &$pending_row) {
            wordpatch_fix_logbox_row($pending_row);
        }

        // Grab the number of pending rows and store into a variable
        $pending_count = count($pending_rows);

        // Calculate the page offset and limit before manipulation
        $page_offset = (($page - 1) * WORDPATCH_LOGBOX_PAGE_LIMIT);
        $page_limit = max(1, (int)WORDPATCH_LOGBOX_PAGE_LIMIT);

        // Variables for our final query offset and limit
        $query_offset = 0;
        $query_limit = $page_limit;

        $result_array = array();

        // If the page offset begins within range of the pending rows,
        // then manipulate the query limit and handle special cases.
        if($page_offset < $pending_count) {
            // Calculate the pending offset
            $pending_offset = $pending_count - $page_offset;

            // Change the query limit based on the pending offset
            $query_limit = max(0, $page_limit - $pending_offset);

            // Return early if the only thing we should show are pending rows
            if($query_limit === 0) {
                return array_slice($pending_rows, $page_offset, $page_limit);
            }

            // Grab the partial pending result and start creating our result array from it
            $result_array = array_slice($pending_rows, $page_offset, $pending_offset);
        } else {
            // Otherwise, manipulate the query offset depending on the number of pending rows
            $query_offset = $page_offset - $pending_count;
        }

        $query = "SELECT `l`.*, IF(`r`.`datetime` IS NOT NULL, 1, 0) as `rejection_pending`, " .
            "`r`.`datetime` as `rejection_pending_datetime`, `r`.`user_id` as `rejection_pending_user_id` " .
            "FROM `$logs_table` `l` LEFT JOIN `$rejects_table` `r` ON `r`.`pending_id` = `l`.`pending_id` " .
            "WHERE `l`.`job_id` = '$esc_job_id' ORDER BY `datetime` DESC LIMIT $query_offset, $query_limit";

        $log_rows = wordpatch_db_get_results($wpenv_vars, $query);

        foreach($log_rows as &$log_row) {
            wordpatch_fix_logbox_row($log_row);
            $result_array[] = $log_row;
        }

        return $result_array;
    }
}

if(!function_exists('wordpatch_logbox_by_id')) {
    function wordpatch_logbox_by_id($wpenv_vars, $log_id)
    {
        $logs_table = wordpatch_logs_table($wpenv_vars);
        $pending_table = wordpatch_pending_table($wpenv_vars);
        $rejects_table = wordpatch_rejects_table($wpenv_vars);

        $log_id = wordpatch_sanitize_unique_id($log_id);

        if($log_id === '') {
            return null;
        }

        // Grab our result set
        $esc_log_id = wordpatch_esc_sql($wpenv_vars, $log_id);

        $query = "(SELECT NULL as `id`, `p`.`id` as `pending_id`, `p`.`job_id` as `job_id`, NULL as `datetime`, " .
            "NULL as `running_datetime`, NULL as `finish_datetime`, NULL as `running`, NULL as `progress_percent`, " .
            "NULL as `progress_note`, NULL as `progress_datetime`, `p`.`init_reason` as `init_reason`, " .
            "`p`.`init_user` as `init_user`, `p`.`init_subject_list` as `init_subject_list`, NULL as `cleanup_status`, " .
            "NULL as `changes`, NULL as `judgement_decision`, NULL as `judgement_datetime`, NULL as `judgement_user`, " .
            "NULL as `judgement_pending`, NULL as `success`, NULL as `error_list`, NULL as `error_vars`, NULL as `reject_error_list`, " .
            "NULL as `reject_error_vars`, NULL as `cleanup_error_list`, NULL as `cleanup_error_vars`, " .
            "`p`.`maintenance_mode`  as `maintenance_mode`, `p`.`retry_count` as `retry_count`, NULL as `retry_count_int`, " .
            "NULL as `attempt_count_int`, NULL as `should_retry`, `p`.`update_cooldown` as `update_cooldown`, " .
            "`p`.`mode` as `mode`, `p`.`timer` as `timer`, NULL as `timer_int`, `p`.`title` as `title`, `p`.`path` as `path`, " .
            "`p`.`binary_mode` as `binary_mode`, 0 as `rejection_pending`, NULL as `rejection_pending_datetime`, " .
            "NULL as `rejection_pending_user_id` FROM `$pending_table` `p` LEFT JOIN `$logs_table` `pl` " .
            "ON `pl`.`pending_id` = `p`.`id` WHERE `p`.`id` = '$esc_log_id' AND `pl`.`id` IS NULL) " .
            "UNION (SELECT `l`.`id` as `id`, `l`.`pending_id` as `pending_id`, `l`.`job_id` as `job_id`, `l`.`datetime` as `datetime`, " .
            "`l`.`running_datetime` as `running_datetime`, `l`.`finish_datetime` as `finish_datetime`, `l`.`running` as `running`, " .
            "`l`.`progress_percent` as `progress_percent`, `l`.`progress_note` as `progress_note`, `l`.`progress_datetime` as `progress_datetime`, " .
            "`l`.`init_reason` as `init_reason`, `l`.`init_user` as `init_user`, `l`.`init_subject_list` as `init_subject_list`, " .
            "`l`.`cleanup_status` as `cleanup_status`, `l`.`changes` as `changes`, `l`.`judgement_decision` as `judgement_decision`, " .
            "`l`.`judgement_datetime` as `judgement_datetime`, `l`.`judgement_user` as `judgement_user`, `l`.`judgement_pending` as `judgement_pending`, " .
            "`l`.`success` as `success`, `l`.`error_list` as `error_list`, `l`.`error_vars` as `error_vars`, " .
            "`l`.`reject_error_list` as `reject_error_list`, `l`.`reject_error_vars` as `reject_error_vars`, `l`.`cleanup_error_list` as `cleanup_error_list`, " .
            "`l`.`cleanup_error_vars` as `cleanup_error_vars`, `l`.`maintenance_mode`  as `maintenance_mode`, `l`.`retry_count` as `retry_count`, " .
            "`l`.`retry_count_int` as `retry_count_int`, `l`.`attempt_count_int` as `attempt_count_int`, `l`.`should_retry` as `should_retry`, " .
            "`l`.`update_cooldown` as `update_cooldown`, `l`.`mode` as `mode`, `l`.`timer` as `timer`, `l`.`timer_int` as `timer_int`, " .
            "`l`.`title` as `title`, `l`.`path` as `path`, `l`.`binary_mode` as `binary_mode`, IF(`r`.`datetime` IS NOT NULL, 1, 0) as `rejection_pending`, " .
            "`r`.`datetime` as `rejection_pending_datetime`, `r`.`user_id` as `rejection_pending_user_id` FROM `$logs_table` `l` " .
            "LEFT JOIN `$rejects_table` `r` ON `r`.`pending_id` = `l`.`pending_id` WHERE `l`.`id` = '$esc_log_id' OR `l`.`pending_id` = '$esc_log_id') LIMIT 1";

        $logbox_row = wordpatch_db_get_row($wpenv_vars, $query);

        if(!$logbox_row) {
            return null;
        }

        wordpatch_fix_logbox_row($logbox_row);

        return $logbox_row;
    }
}

if(!function_exists('wordpatch_get_log_count')) {
    /**
     * Calculate the number of logs for the logbox.
     *
     * @param $wpenv_vars
     * @param $job_id
     * @param $page_count
     * @return int
     */
    function wordpatch_get_log_count($wpenv_vars, $job_id, &$page_count) {
        $job_id = wordpatch_sanitize_unique_id($job_id);

        if($job_id === '') {
            return 0;
        }

        $logs_table = wordpatch_logs_table($wpenv_vars);
        $pending_table = wordpatch_pending_table($wpenv_vars);
        $esc_job_id = wordpatch_esc_sql($wpenv_vars, $job_id);


        $query = "SELECT SUM(`u`.`count`) as `count` FROM (SELECT COUNT(`l`.`id`) as `count`, 'logs_count' as `type`" .
            "FROM `$logs_table` `l`	LEFT JOIN `$pending_table` `p` ON `p`.`id` = `l`.`pending_id` ".
            "WHERE `l`.`job_id` = '$esc_job_id' AND `p`.`id` IS NULL UNION SELECT COUNT(`p`.`id`) as `count`, " .
            "'pending_count' as `type` FROM `$pending_table` `p` WHERE `p`.`job_id` = '$esc_job_id') `u`";

        $log_count = max(0, (int)wordpatch_db_get_var($wpenv_vars, $query));
        $page_count = max(0, (int)ceil($log_count / WORDPATCH_LOGBOX_PAGE_LIMIT));

        return $log_count;
    }
}

if(!function_exists('wordpatch_get_judgable_log_count')) {
    function wordpatch_get_judgable_log_count($wpenv_vars) {
        $logs_table = wordpatch_logs_table($wpenv_vars);
        $jobs_table = wordpatch_jobs_table($wpenv_vars);
        $rejects_table = wordpatch_rejects_table($wpenv_vars);

        $query = "SELECT COUNT(*) as `count` FROM `$logs_table` `l` LEFT JOIN `$jobs_table` `j` ON " .
            "`j`.`id` = `l`.`job_id` LEFT JOIN `$rejects_table` `r` ON `r`.`pending_id` = `l`.`pending_id` WHERE " .
            "(`l`.`judgement_pending` IS NOT NULL AND `l`.`judgement_pending` = '1') " .
            "AND (`j`.`id` IS NOT NULL) AND (`j`.`deleted` = '0') AND (`r`.`pending_id` IS NULL)";

        $judgable_count = wordpatch_db_get_var($wpenv_vars, $query);

        return max(0, (int)$judgable_count);
    }
}

// TODO: Join wp_wordpatch_rejects and prevent if not null
if(!function_exists('wordpatch_get_judgable_log_ids')) {
    function wordpatch_get_judgable_log_ids($wpenv_vars, &$job_ids) {
        $logs_table = wordpatch_logs_table($wpenv_vars);
        $jobs_table = wordpatch_jobs_table($wpenv_vars);
        $rejects_table = wordpatch_rejects_table($wpenv_vars);

        $query = "SELECT `l`.`id` as `id`, `l`.`job_id` as `job_id` FROM `$logs_table` `l` LEFT JOIN `$jobs_table` `j` ON " .
            "`j`.`id` = `l`.`job_id` LEFT JOIN `$rejects_table` `r` ON `r`.`pending_id` = `l`.`pending_id` WHERE " .
            "(`l`.`judgement_pending` IS NOT NULL AND `l`.`judgement_pending` = '1') AND (`j`.`id` IS NOT NULL) AND " .
            "(`j`.`deleted` = '0') AND (`r`.`pending_id` IS NULL) ORDER BY `l`.`finish_datetime` ASC";

        $rows = wordpatch_db_get_results($wpenv_vars, $query);

        $results = array();

        $job_ids = array();

        foreach($rows as $row) {
            $results[] = $row['id'];
            $job_ids[] = $row['job_id'];
        }

        return $results;
    }
}

if(!function_exists('wordpatch_map_judgable_job_id_to_judgable_log_id')) {
    function wordpatch_map_judgable_job_id_to_judgable_log_id($wpenv_vars, $check_job_id, $judgable_job_ids, $judgable_log_ids) {
        $judgable_log_id = null;
        foreach($judgable_job_ids as $idx => $judgable_job_id) {
            if($judgable_job_id !== $check_job_id) {
                continue;
            }

            $judgable_log_id = $judgable_log_ids[$idx];
            break;
        }

        return $judgable_log_id;
    }
}

if(!function_exists('wordpatch_get_last_logbox_jobs')) {
    function wordpatch_get_last_logbox_jobs($wpenv_vars) {
        $logs_table = wordpatch_logs_table($wpenv_vars);
        $jobs_table = wordpatch_jobs_table($wpenv_vars);
        $rejects_table = wordpatch_rejects_table($wpenv_vars);

        $query = "SELECT `ol`.*, IF(`r`.`datetime` IS NOT NULL, 1, 0) as `rejection_pending`, " .
            "`r`.`datetime` as `rejection_pending_datetime`, `r`.`user_id` as `rejection_pending_user_id` " .
            "FROM `$logs_table` `ol` INNER JOIN (SELECT `l`.`job_id` as `job_id`, MAX(`l`.`datetime`) " .
            "as `latest_datetime` FROM `$logs_table` `l` LEFT JOIN `$jobs_table` `lj` ON " .
            "`lj`.`id` = `l`.`job_id` WHERE `l`.`running` IS NULL AND (`lj`.`id` IS NOT NULL AND `lj`.`deleted` = '0') " .
            "GROUP BY `job_id`) `b` ON `ol`.`job_id` = `b`.`job_id` AND `ol`.`datetime` >= `b`.`latest_datetime` " .
            "LEFT JOIN `$rejects_table` `r` ON `r`.`pending_id` = `ol`.`pending_id`";

        $rows = wordpatch_db_get_results($wpenv_vars, $query);

        foreach($rows as &$row) {
            wordpatch_fix_logbox_row($row);
        }

        usort($rows, function($a, $b) {
            if($a['datetime'] === $b['datetime']) {
                return 0;
            }

            if($a['datetime'] < $b['datetime']) {
                return 1;
            }

            return -1;
        });

        return $rows;
    }
}