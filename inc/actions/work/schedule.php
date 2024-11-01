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
 * Implements the enqueue schedule functionality for the work action.
 */

if(!function_exists('wordpatch_work_enqueue_schedule')) {
    /**
     * Enqueue any scheduled items to the pending job queue.
     *
     * @param $wpenv_vars
     */
    function wordpatch_work_enqueue_schedule($wpenv_vars) {
        // Calculate the schedule table name.
        $schedule_table = wordpatch_schedule_table($wpenv_vars);

        // Grab the schedule from the database.
        $schedule_rows = wordpatch_get_schedule($wpenv_vars);

        foreach($schedule_rows as $schedule_row) {
            // Add this to our pending jobs (using schedule ID as a key to prevent duplicates)
            wordpatch_add_pending_job($wpenv_vars, $schedule_row['job_id'], WORDPATCH_INIT_REASON_UPDATE,
                $schedule_row['init_subject_list'], $schedule_row['init_user'], $schedule_row['id']);

            // Escape the schedule ID for our query.
            $esc_schedule_id = wordpatch_esc_sql($wpenv_vars, $schedule_row['id']);

            // Execute our delete query.
            wordpatch_db_query($wpenv_vars, "DELETE FROM `$schedule_table` WHERE `id` = '$esc_schedule_id'");
        }
    }
}