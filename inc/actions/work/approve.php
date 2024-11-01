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
 * Implements the auto approval of matured optimistic jobs from the work action.
 */

if(!function_exists('wordpatch_work_approve_optimistic')) {
    /**
     * Automatically approve jobs that are in optimistic mode, succeeded, and have past their maturity period.
     * This happens right away (ie: this is not enqueue, unlike rejections of pessimistic jobs) since there is no
     * immediate need to touch the filesystem.
     *
     * @param $wpenv_vars
     */
    function wordpatch_work_approve_optimistic($wpenv_vars) {
        // Calculate the logs table name.
        $logs_table = wordpatch_logs_table($wpenv_vars);

        // Escape the variables needed for our query.
        $esc_accepted = wordpatch_esc_sql($wpenv_vars, WORDPATCH_JUDGEMENT_ACCEPTED);
        $esc_optimistic = wordpatch_esc_sql($wpenv_vars, wordpatch_job_mode_optimistic());

        $esc_pending_status = wordpatch_esc_sql($wpenv_vars, WORDPATCH_CLEANUP_STATUS_PENDING);

        // This query should approve any logs that are past their timer and are in optimistic mode.
        $approvequery = "UPDATE `$logs_table` SET `cleanup_status` = '$esc_pending_status', `judgement_decision` = '$esc_accepted', " .
            "`judgement_datetime` = UTC_TIMESTAMP(), `judgement_pending` = '0', `judgement_user` = NULL WHERE " .
            "(`finish_datetime` IS NOT NULL AND (UTC_TIMESTAMP() >= DATE_ADD(`finish_datetime`, INTERVAL " .
            "`timer_int` SECOND))) AND (`running` IS NULL) AND (`judgement_pending` IS NOT NULL AND`judgement_pending` = '1') " .
            "AND (`mode` = '$esc_optimistic') ORDER BY `finish_datetime` ASC";

        // Execute our auto approval query.
        wordpatch_db_query($wpenv_vars, $approvequery);
    }
}