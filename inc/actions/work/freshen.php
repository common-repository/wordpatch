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
 * Implements the freshen functionality for expired jobs during the work action.
 */

if(!function_exists('wordpatch_work_freshen_up')) {
    /**
     * Freshen any expired job log entries.
     *
     * @param $wpenv_vars
     */
    function wordpatch_work_freshen_up($wpenv_vars) {
        $logs_table = wordpatch_logs_table($wpenv_vars);
        $stale_timer = max(0, (int)WORDPATCH_STALE_JOB_TIMER);

        $esc_pending_status = wordpatch_esc_sql($wpenv_vars, WORDPATCH_CLEANUP_STATUS_PENDING);
        $esc_none_status = wordpatch_esc_sql($wpenv_vars, WORDPATCH_CLEANUP_STATUS_NONE);
        $esc_active_status = wordpatch_esc_sql($wpenv_vars, WORDPATCH_CLEANUP_STATUS_ACTIVE);

        // TODO: Should we also reset variables such as judgement_pending? Probably not needed.
        $freshen_query = "UPDATE `$logs_table` SET `running` = NULL, `cleanup_status` = IF(`cleanup_status`='$esc_active_status', " .
            "'$esc_none_status', '$esc_pending_status'), `should_retry` = '0' WHERE (`running` IS NOT NULL AND `running` = '1') " .
            "AND (UTC_TIMESTAMP() >= DATE_ADD(`datetime`, INTERVAL $stale_timer SECOND))";

        wordpatch_db_query($wpenv_vars, $freshen_query);
    }
}