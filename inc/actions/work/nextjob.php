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
 * Implements the function that enqueues the next job for the work action.
 */

if(!function_exists('wordpatch_work_enqueue_next_job')) {
    /**
     * Enqueue the next job for the jobrunner. This is called by `wordpatch_preface_work`.
     * The next job is determined by a number of factors, which are highlighted at the beginning of the large query below.
     * This function will either return false meaning there is nothing to do, or it will return an associative array
     * containing the data that will be passed into the jobrunner.
     *
     * @param $wpenv_vars
     * @return bool|array
     */
    function wordpatch_work_enqueue_next_job($wpenv_vars) {
        // Calculate the logs table name.
        $logs_table = wordpatch_logs_table($wpenv_vars);

        // Calculate the pending table name.
        $pending_table = wordpatch_pending_table($wpenv_vars);

        // Calculate the rejects table name.
        $rejects_table = wordpatch_rejects_table($wpenv_vars);

        // Create a new log ID if this is an insert. This might not be used so the variable name is particular.
        $new_log_id = wordpatch_unique_id();

        // Escape the log ID for our query.
        $esc_new_log_id = wordpatch_esc_sql($wpenv_vars, $new_log_id);

        $esc_pending_status = wordpatch_esc_sql($wpenv_vars, WORDPATCH_CLEANUP_STATUS_PENDING);
        $esc_none_status = wordpatch_esc_sql($wpenv_vars, WORDPATCH_CLEANUP_STATUS_NONE);
        $esc_active_status = wordpatch_esc_sql($wpenv_vars, WORDPATCH_CLEANUP_STATUS_ACTIVE);

        /**
         * The following query is designed to queue a single job into the logs table. The following rules are followed:
         * - There will only ever be a single job running at a time.
         * - If there is not a job running, then the following logic is performed:
         *    - If there is a pending log rejection, run it.
         *    - Else, if there is a job that previously had a network failure, try to run it.
         *    - Else, if there is a pending job run that.
         *    - Else, check if there are any jobs pending cleanup, and if so run them.
         */
        $insertquery = "INSERT INTO `$logs_table` (`id`, `pending_id`, `job_id`, `datetime`, `running_datetime`, `finish_datetime`, " .
            "`running`, `progress_percent`, `progress_note`, `progress_datetime`, `init_reason`, `init_user`, `init_subject_list`, " .
            "`cleanup_status`, `changes`, `judgement_decision`, `judgement_datetime`, `judgement_user`, `judgement_pending`, " .
            "`success`, `error_list`, `error_vars`, `reject_error_list`, `reject_error_vars`, `cleanup_error_list`, " .
            "`cleanup_error_vars`, `maintenance_mode`, `retry_count`, `retry_count_int`, `attempt_count_int`, `should_retry`, " .
            "`update_cooldown`, `mode`, `timer`, `timer_int`, `title`, `path`, `binary_mode`) " .
            "SELECT " .
            "'$esc_new_log_id' as `id`, " .
            "`ur`.`pending_id` as `pending_id`, " .
            "IF(`pj`.`job_id` IS NULL, '', `pj`.`job_id`)  as `job_id`, " .
            "UTC_TIMESTAMP() as `datetime`, " .
            "UTC_TIMESTAMP() as `running_datetime`, " .
            "NULL as `finish_datetime`, " .
            "'1' as `running`, " .
            "'0' as `progress_percent`, " .
            "NULL as `progress_note`, " .
            "NULL as `progress_datetime`, " .
            "IF(`pj`.`init_reason` IS NULL, '', `pj`.`init_reason`) as `init_reason`, " .
            "IF(`pj`.`init_user` IS NULL, 0, `pj`.`init_user`) as `init_user`, " .
            "IF(`pj`.`init_subject_list` IS NULL, '', `pj`.`init_subject_list`) as `init_subject_list`, " .
            "'$esc_none_status' as `cleanup_status`, " .
            "NULL as `changes`, " .
            "NULL as `judgement_decision`, " .
            "NULL as `judgement_datetime`, " .
            "NULL as `judgement_user`, " .
            "NULL as `judgement_pending`, " .
            "NULL as `success`, " .
            "NULL as `error_list`, " .
            "NULL as `error_vars`, " .
            "NULL as `reject_error_list`, " .
            "NULL as `reject_error_vars`, " .
            "NULL as `cleanup_error_list`, " .
            "NULL as `cleanup_error_vars`, " .
            "IF(`pj`.`maintenance_mode` IS NULL, '', `pj`.`maintenance_mode`) as `maintenance_mode`, " .
            "IF(`pj`.`retry_count` IS NULL, '', `pj`.`retry_count`) as `retry_count`, " .
            "NULL as `retry_count_int`, " .
            "NULL as `attempt_count_int`, " .
            "'0' as `should_retry`, " .
            "IF(`pj`.`update_cooldown` IS NULL, '', `pj`.`update_cooldown`) as `update_cooldown`, " .
            "IF(`pj`.`mode` IS NULL, '', `pj`.`mode`) as `mode`, " .
            "IF(`pj`.`timer` IS NULL, '', `pj`.`timer`) as `timer`, " .
            "IF(`pj`.`timer_int` IS NULL, 0, `pj`.`timer_int`) as `timer_int`, " .
            "IF(`pj`.`title` IS NULL, '', `pj`.`title`) as `title`, " .
            "IF(`pj`.`path` IS NULL, '', `pj`.`path`) as `path`, " .
            "IF(`pj`.`binary_mode` IS NULL, '', `pj`.`binary_mode`) as `binary_mode` " .
            "FROM " .
            "( " .
            "	SELECT " .
            "		IF(`pt`.`running_log_id` != '', '', IF(`pt`.`reject_pending_id` != '', `pt`.`reject_pending_id`, " .
            "       IF(`pt`.`retry_pending_id` != '', `pt`.`retry_pending_id`, IF(`pt`.`next_pending_id` != '', " .
            "       `pt`.`next_pending_id`, IF(`pt`.`cleanup_pending_id` != '', `pt`.`cleanup_pending_id`, ''))))) as `pending_id` " .
            "	FROM " .
            "	( " .
            "		(SELECT '3' as `weight`, '' as `retry_pending_id`, '' as `next_pending_id`, '' as `running_log_id`, " .
            "       `lr`.`pending_id` as `reject_pending_id`, '' as `cleanup_pending_id` FROM `$rejects_table` `lr` " .
            "       LEFT JOIN `$logs_table` `jl` ON `jl`.`pending_id` = `lr`.`pending_id` WHERE (`jl`.`id` IS NOT NULL) " .
            "       AND (`jl`.`judgement_pending` IS NOT NULL AND `jl`.`judgement_pending` = '1') ORDER BY `lr`.`datetime` ASC LIMIT 1) " .
            "		UNION " .
            "		(SELECT '2' as `weight`, `pending_id` as `retry_pending_id`, '' as `next_pending_id`, '' as `running_log_id`, " .
            "       '' as `reject_pending_id`, '' as `cleanup_pending_id` FROM `$logs_table` `rl` WHERE `should_retry` = '1' LIMIT 1) " .
            "		UNION " .
            "		(SELECT '1' as `weight`, '' as `retry_pending_id`, `np`.`id` as `next_pending_id`, '' as `running_log_id`, " .
            "       '' as `reject_pending_id`, '' as `cleanup_pending_id` FROM `$pending_table` `np` " .
            "       LEFT JOIN `$logs_table` `nl` ON `nl`.`pending_id` = `np`.`id` WHERE `nl`.`id` IS NULL ORDER BY `np`.`datetime` ASC LIMIT 1) " .
            "		UNION " .
            "		(SELECT '0' as `weight`, '' as `retry_pending_id`, '' as `next_pending_id`, '' as `running_log_id`, " .
            "       '' as `reject_pending_id`, `pending_id` as `cleanup_pending_id` FROM `$logs_table` `rl` WHERE `cleanup_status` = '$esc_pending_status' LIMIT 1) " .
            "		UNION " .
            "		(SELECT '-1' as `weight`, '' as `retry_pending_id`, '' as `next_pending_id`, `id` as `running_log_id`, " .
            "       '' as `reject_pending_id`, '' as `cleanup_pending_id` FROM `$logs_table` `yl` WHERE `running` = '1' LIMIT 1) " .
            "	) `pt` " .
            "	ORDER BY `pt`.`weight` DESC " .
            "	LIMIT 1 " .
            ") `ur` " .
            "LEFT JOIN `$pending_table` `pj` ON `pj`.`id` = `ur`.`pending_id` " .
            "WHERE `ur`.`pending_id` != '' " .
            "LIMIT 1 " .
            "ON DUPLICATE KEY UPDATE `running` = '1', `should_retry` = '0', `judgement_pending` = '0', `cleanup_status` = IF(`cleanup_status`='$esc_pending_status', " .
            "'$esc_active_status', `cleanup_status`), `running_datetime` = VALUES(`running_datetime`)";

        // Try to run our big ol' query.
        $query_result = wordpatch_db_query($wpenv_vars, $insertquery);

        // If we do not have a result, then return false because something went wrong.
        if(!$query_result) {
            return false;
        }

        // We only really care if there is an affected row.
        $affected_rows = wordpatch_db_affected_rows($wpenv_vars);

        // If there is not an affected row, return false because nothing was queued.
        if(!$affected_rows) {
            return false;
        }

        // Grab the currently running log information that was either created or updated using the query above.
        $running_log_info = wordpatch_get_running_log($wpenv_vars);

        // We don't want to update the log if we are just queue-ing a reject or cleanup.
        if($running_log_info['is_reject'] || $running_log_info['cleanup_status'] === WORDPATCH_CLEANUP_STATUS_ACTIVE) {
            return $running_log_info;
        }

        // Using the log information, calculate retry count and attempt count integers.
        $old_attempt_count_int = max(0, (int)$running_log_info['attempt_count_int']);

        $retry_count_int = wordpatch_job_retry_count_to_int($wpenv_vars, $running_log_info['retry_count']);
        $attempt_count_int = $old_attempt_count_int + 1;

        // Escape and update the log with the integer values
        $esc_retry_count_int = wordpatch_esc_sql($wpenv_vars, $retry_count_int);
        $esc_attempt_count_int = wordpatch_esc_sql($wpenv_vars, $attempt_count_int);
        $esc_log_id = wordpatch_esc_sql($wpenv_vars, $running_log_info['id']);

        $updatequery = "UPDATE `$logs_table` SET `retry_count_int` = '$esc_retry_count_int', " .
            "`attempt_count_int` = '$esc_attempt_count_int', `progress_percent` = '1', `progress_note` = NULL, " .
            "`progress_datetime` = UTC_TIMESTAMP() WHERE `id` = '$esc_log_id'";

        // Update the log with the appropriate information.
        wordpatch_db_query($wpenv_vars, $updatequery);

        // Update the log information so we can use this in the jobrunner.
        $running_log_info['attempt_count_int'] = $attempt_count_int;
        $running_log_info['retry_count_int'] = $retry_count_int;

        // Return the running log information.
        return $running_log_info;
    }
}