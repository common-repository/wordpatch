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
 * Implements the shared job reject functionality.
 */

if(!function_exists('wordpatch_delete_rejects_by_pending_id')) {
    /**
     * Delete the rejects from the database that belong to $pending_id.
     *
     * @param $wpenv_vars
     * @param $pending_id
     */
    function wordpatch_delete_rejects_by_pending_id($wpenv_vars, $pending_id) {
        // Calculate the rejects table name.
        $rejects_table = wordpatch_rejects_table($wpenv_vars);

        // Escape the pending ID.
        $esc_pending_id = wordpatch_esc_sql($wpenv_vars, $pending_id);

        // Construct our deletion query.
        $deletereject = "DELETE FROM `$rejects_table` WHERE `pending_id` = '$esc_pending_id'";

        // Perform the delete query!
        wordpatch_db_query($wpenv_vars, $deletereject);
    }
}