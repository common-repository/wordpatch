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
 * Implements the shared rollbacks functionality.
 */

if(!function_exists('wordpatch_get_rollbacks_by_log_id')) {
    /**
     * Grab each rollback record for a specific log ID.
     *
     * @param $wpenv_vars
     * @param $log_id
     * @return array
     */
    function wordpatch_get_rollbacks_by_log_id($wpenv_vars, $log_id) {
        $log_id = wordpatch_sanitize_unique_id($log_id);

        if($log_id === '') {
            return array();
        }

        $rollback_table = wordpatch_rollback_table($wpenv_vars);

        $esc_log_id = wordpatch_esc_sql($wpenv_vars, $log_id);

        $rollbackselect = "SELECT * FROM `$rollback_table` WHERE `log_id` = '$esc_log_id' ORDER BY `sort_order` ASC";
        $rollback_rows = wordpatch_db_get_results($wpenv_vars, $rollbackselect);

        foreach($rollback_rows as &$rollback_row) {
            wordpatch_fix_rollback_row($rollback_row);
        }

        return $rollback_rows;
    }
}

if(!function_exists('wordpatch_fix_rollback_row')) {
    /**
     * Calculates a fixed version of $rollback_row which contains proper types where necessary.
     *
     * @param $rollback_row
     */
    function wordpatch_fix_rollback_row(&$rollback_row) {
        $rollback_row['sort_order'] = max(0, (int)$rollback_row['sort_order']);
        $rollback_row['path'] = ($rollback_row['path'] === null || trim($rollback_row['path']) === '') ?
            '' : trim(base64_decode($rollback_row['path']));
    }
}

if(!function_exists('wordpatch_delete_rollbacks_by_log_id')) {
    /**
     * Delete rollbacks from the database that belong to $job_id.
     *
     * @param $wpenv_vars
     * @param $log_id
     */
    function wordpatch_delete_rollbacks_by_log_id($wpenv_vars, $log_id) {
        // Calculate the rollback table name.
        $rollback_table = wordpatch_rollback_table($wpenv_vars);

        // Escape the log ID.
        $esc_log_id = wordpatch_esc_sql($wpenv_vars, $log_id);

        // Construct the delete rollback query.
        $deleterollback = "DELETE FROM `$rollback_table` WHERE `log_id` = '$esc_log_id'";

        // Run the delete query.
        wordpatch_db_query($wpenv_vars, $deleterollback);
    }
}