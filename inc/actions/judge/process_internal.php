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
 * Implements the internal processing functionality for the judge form.
 */

if(!function_exists('wordpatch_judge_process_internal')) {
    /**
     * Internal processing for the judge form. Responsible for validation and persistence.
     *
     * @param $wpenv_vars
     * @param $logbox_row
     * @param $judgement_decision
     * @return array
     */
    function wordpatch_judge_process_internal($wpenv_vars, $logbox_row, $judgement_decision) {
        // Begin to calculate the error list.
        $error_list = array();

        if($logbox_row['running'] || !$logbox_row['success'] || !$logbox_row['judgement_pending'] ||
            $logbox_row['rejection_pending']) {
            $error_list[] = WORDPATCH_NOT_PENDING_JUDGEMENT;
            return $error_list;
        }

        // Make sure the judgement decision is valid
        if(!in_array($judgement_decision, array(WORDPATCH_JUDGEMENT_ACCEPTED, WORDPATCH_JUDGEMENT_REJECTED))) {
            $error_list[] = WORDPATCH_INVALID_JUDGEMENT;
            return $error_list;
        }

        $current_user_id = max(0, (int)wordpatch_get_current_user_id($wpenv_vars));

        $logs_table = wordpatch_logs_table($wpenv_vars);
        $rejects_table = wordpatch_rejects_table($wpenv_vars);

        $esc_current_user = wordpatch_esc_sql($wpenv_vars, $current_user_id);
        $esc_log_id = wordpatch_esc_sql($wpenv_vars, $logbox_row['id']);

        if($judgement_decision === WORDPATCH_JUDGEMENT_ACCEPTED) {
            $esc_pending_status = wordpatch_esc_sql($wpenv_vars, WORDPATCH_CLEANUP_STATUS_PENDING);
            $esc_accepted = wordpatch_esc_sql($wpenv_vars, WORDPATCH_JUDGEMENT_ACCEPTED);

            $updatequery = "UPDATE `$logs_table` SET `cleanup_status` = '$esc_pending_status', " .
                "`judgement_decision` = '$esc_accepted', `judgement_user` = '$esc_current_user', `judgement_pending` = '0', " .
                "`judgement_datetime` = UTC_TIMESTAMP() WHERE `id` = '$esc_log_id' AND `judgement_pending` = '1'";

            wordpatch_db_query($wpenv_vars, $updatequery);
        } else {
            $insertrejects = "INSERT INTO `$rejects_table` (`pending_id`, `user_id`, `job_id`, `datetime`) " .
                "SELECT `pending_id` as `pending_id`, '$esc_current_user' as `user_id`, `job_id` as `job_id`, UTC_TIMESTAMP() as `datetime` " .
                "FROM `$logs_table` WHERE `id` = '$esc_log_id' AND (`judgement_pending` = '1') ON DUPLICATE KEY UPDATE `user_id`=`user_id`";

            wordpatch_db_query($wpenv_vars, $insertrejects);
        }

        // Return our calculated error list
        return $error_list;
    }
}