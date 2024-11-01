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
 * Implements the shared job scheduling functionality.
 */

if(!function_exists('wordpatch_get_schedule')) {
    function wordpatch_get_schedule($wpenv_vars) {
        // Calculate the schedule table name.
        $schedule_table = wordpatch_schedule_table($wpenv_vars);

        // Construct our select query and ensure to order by datetime ascending.
        $scheduleselect = "SELECT * FROM `$schedule_table` WHERE `datetime` <= UTC_TIMESTAMP() ORDER BY `datetime` ASC";

        // Grab the results from the datbase.
        $schedule_rows = wordpatch_db_get_results($wpenv_vars, $scheduleselect);

        // Loop through our results and fix them to contain integers where necessary.
        foreach($schedule_rows as &$schedule_row) {
            wordpatch_fix_schedule_row($schedule_row);
        }

        // Return the calculated schedule rows.
        return $schedule_rows;
    }
}

if(!function_exists('wordpatch_fix_schedule_row')) {
    /**
     * Calculates a fixed version of $schedule_row which contains integers where necessary.
     *
     * @param $schedule_row
     */
    function wordpatch_fix_schedule_row(&$schedule_row) {
        $schedule_row['init_user'] = $schedule_row['init_user'] === null ? null :
            max(0, (int)$schedule_row['init_user']);

        $schedule_row['init_subject_list'] = ($schedule_row['init_subject_list'] === null || trim($schedule_row['init_subject_list']) === '') ?
            array() : wordpatch_json_decode(trim($schedule_row['init_subject_list']));
    }
}