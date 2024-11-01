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
 * Implements the database helper functions for the edit patch page.
 */

if(!function_exists('wordpatch_editpatch_persist_db')) {
    /**
     * Persists the database record for the new patch form. Returns an error string or null on success.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $patch
     * @param $location
     * @param $location_old
     * @param $location_new
     * @return null|string
     */
    function wordpatch_editpatch_persist_db($wpenv_vars, $current, $patch, $location, $location_old, $location_new) {
        // Calculate the patches table name.
        $patches_table = wordpatch_patches_table($wpenv_vars);

        // Get the new patch size. If nothing was uploaded use the old size.
        $patch_size = $current['patch_type'] === wordpatch_patch_type_text() ? strlen($current['patch_data']) :
            ($current['patch_file_uploaded'] ? strlen($current['patch_data']) : $patch['patch_size']);

        // Escape our variables for the query.
        $esc_patch_id = wordpatch_esc_sql($wpenv_vars, $patch['id']);
        $esc_title = wordpatch_esc_sql($wpenv_vars, base64_encode($current['patch_title']));
        $esc_patch_type = wordpatch_esc_sql($wpenv_vars, $current['patch_type']);
        $esc_patch_location = wordpatch_esc_sql($wpenv_vars, $location);
        $esc_patch_path = wordpatch_esc_sql($wpenv_vars, $current['patch_type'] === wordpatch_patch_type_simple() ?
            base64_encode($current['patch_path']) : '');
        $esc_patch_location_old = wordpatch_esc_sql($wpenv_vars, $location_old);
        $esc_patch_location_new = wordpatch_esc_sql($wpenv_vars, $location_new);
        $esc_patch_size = wordpatch_esc_sql($wpenv_vars, $patch_size);

        $editquery = "UPDATE `$patches_table` SET `title` = '$esc_title', `patch_location` = '$esc_patch_location', " .
            "`patch_location_old` = '$esc_patch_location_old', `patch_location_new` = '$esc_patch_location_new', " .
            "`patch_path` = '$esc_patch_path', `patch_size` = '$esc_patch_size', `patch_type` = '$esc_patch_type' " .
            "WHERE `id` = '$esc_patch_id'";

        $result = wordpatch_db_query($wpenv_vars, $editquery);

        if(!$result) {
            return WORDPATCH_UNKNOWN_DATABASE_ERROR;
        }

        return null;
    }
}