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
 * Implements the database helper functions for the delete patch action.
 */

if(!function_exists('wordpatch_deletepatch_persist_db')) {
    /**
     * Persists the database records for the delete patch action. Returns an error or null on success.
     *
     * @param $wpenv_vars
     * @param $patch_id
     * @return null|string
     */
    function wordpatch_deletepatch_persist_db($wpenv_vars, $patch_id) {
        // Sanitize the patch ID first
        $patch_id = wordpatch_sanitize_unique_id($patch_id);

        // Return an error if the patch ID is not valid.
        if ($patch_id === '') {
            return WORDPATCH_INVALID_PATCH;
        }

        // Escape our patch ID for the query.
        $esc_patch_id = wordpatch_esc_sql($wpenv_vars, $patch_id);

        // Calculate the patches table name.
        $patches_table = wordpatch_patches_table($wpenv_vars);

        // Construct the delete query.
        $deletequery = "DELETE FROM `$patches_table` WHERE `id` = '$esc_patch_id'";

        // Try to delete the record.
        $result = wordpatch_db_query($wpenv_vars, $deletequery);

        // If we encounter an error, return it.
        if(!$result) {
            return WORDPATCH_UNKNOWN_DATABASE_ERROR;
        }

        // Return null to indicate success.
        return null;
    }
}