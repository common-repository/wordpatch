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
 * Implements the auto enqueue reject of matured pessimistic jobs from the work action.
 */

if(!function_exists('wordpatch_work_enqueue_reject_pessimistic')) {
    /**
     * Automatically enqueue rejection of jobs that are in pessimistic mode, succeeded, and have past their maturity period.
     * Rejection does not happen right away since rejections occur within the job runner due to their nature of
     * filesystem access.
     *
     * @param $wpenv_vars
     */
    function wordpatch_work_enqueue_reject_pessimistic($wpenv_vars) {
        // Calculate the logs table name.
        $logs_table = wordpatch_logs_table($wpenv_vars);

        // Calculate the rejects table name.
        $rejects_table = wordpatch_rejects_table($wpenv_vars);

        // Escape the variables needed for our query.
        $esc_pessimistic = wordpatch_esc_sql($wpenv_vars, wordpatch_job_mode_pessimistic());

        // This query should queue rejection for any logs that are past their timer and are in pessimistic mode.
        $enqueuerejectquery = "INSERT INTO `$rejects_table` (`pending_id`, `job_id`, `user_id`, `datetime`) " .
            "SELECT `pending_id` as `pending_id`, `job_id` as `job_id`, NULL as `user_id`, UTC_TIMESTAMP() as `datetime` FROM `$logs_table` " .
            "WHERE (`finish_datetime` IS NOT NULL AND (UTC_TIMESTAMP() >= DATE_ADD(`finish_datetime`, INTERVAL " .
            "`timer_int` SECOND))) AND (`running` IS NULL) AND (`judgement_pending` IS NOT NULL AND`judgement_pending` = '1') " .
            "AND (`mode` = '$esc_pessimistic') ORDER BY `finish_datetime` ASC ON DUPLICATE KEY UPDATE `user_id`=`user_id`";

        // Execute our query.
        wordpatch_db_query($wpenv_vars, $enqueuerejectquery);
    }
}