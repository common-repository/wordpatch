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
 * Implements the filesystem helper functions for the new/edit patch forms.
 */

if(!function_exists('wordpatch_patchform_persist_fs')) {
    /**
     * Persists the filesystem data for the new/edit patch forms. Returns an error or null on success.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $location
     * @return null|string
     */
    function wordpatch_patchform_persist_fs($wpenv_vars, $current, $location, $old_location, $new_location) {
        // If nothing was uploaded then there is nothing to persist.
        if($current['patch_type'] === wordpatch_patch_type_file() && !$current['patch_file_uploaded']) {
            return null;
        }

        // Attempt to connect to the filesystem.
        $fs_begin = wordpatch_filesystem_begin_helper($wpenv_vars);

        if (!$fs_begin) {
            return WORDPATCH_INVALID_FILESYSTEM_CREDENTIALS;
        }

        // Setup a variable for our potential error
        $upload_error = wordpatch_file_upload_write_to_location($wpenv_vars, wordpatch_file_upload_bucket_patches(),
            $location, $current['patch_data']);

        $upload_error_old = null;
        $upload_error_new = null;

        if($current['patch_type'] === wordpatch_patch_type_simple()) {
            $upload_error_old = wordpatch_file_upload_write_to_location($wpenv_vars, wordpatch_file_upload_bucket_edits(),
                $old_location, $current['patch_old_file']);

            $upload_error_new = wordpatch_file_upload_write_to_location($wpenv_vars, wordpatch_file_upload_bucket_edits(),
                $new_location, $current['patch_new_file']);
        }

        // Disconnect from the filesystem.
        wordpatch_filesystem_end();

        // Return the potential error (will be null if all succeeded).
        return $upload_error !== null ?
            $upload_error : ($upload_error_old !== null ? $upload_error_old :
                ($upload_error_new !== null ? $upload_error_new : null));
    }
}