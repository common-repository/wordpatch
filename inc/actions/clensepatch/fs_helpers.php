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
 * Implements the filesystem helper functions for the erase/delete patch pages.
 */

if(!function_exists('wordpatch_clensepatch_persist_fs')) {
    /**
     * Persists the database records for the erase/delete patch pages. Returns an error or null on success.
     *
     * @param $wpenv_vars
     * @param $patch
     * @return null|string
     */
    function wordpatch_clensepatch_persist_fs($wpenv_vars, $patch) {
        $should_delete = false;

        if($patch['patch_location'] && trim($patch['patch_location']) !== '') {
            $should_delete = true;
        }

        if($patch['patch_location_old'] && trim($patch['patch_location_old']) !== '') {
            $should_delete = true;
        }

        if($patch['patch_location_new'] && trim($patch['patch_location_new']) !== '') {
            $should_delete = true;
        }

        // Nothing to delete, return null for soft success.
        if(!$should_delete) {
            return null;
        }

        // Attempt to connect to the filesystem.
        $fs_begin = wordpatch_filesystem_begin_helper($wpenv_vars);

        if (!$fs_begin) {
            return WORDPATCH_INVALID_FILESYSTEM_CREDENTIALS;
        }

        // Setup a variable for our potential error
        $delete_error = null;

        if($patch['patch_location'] && trim($patch['patch_location']) !== '') {
            $delete_error = wordpatch_file_upload_delete_location($wpenv_vars, wordpatch_file_upload_bucket_patches(),
                $patch['patch_location']);
        }

        // Setup a variable for our potential error
        $delete_error_old = null;

        if($patch['patch_location_old'] && trim($patch['patch_location_old']) !== '') {
            $delete_error_old = wordpatch_file_upload_delete_location($wpenv_vars, wordpatch_file_upload_bucket_edits(),
                $patch['patch_location_old']);
        }

        // Setup a variable for our potential error
        $delete_error_new = null;

        if($patch['patch_location_new'] && trim($patch['patch_location_new']) !== '') {
            $delete_error_new = wordpatch_file_upload_delete_location($wpenv_vars, wordpatch_file_upload_bucket_edits(),
                $patch['patch_location_new']);
        }

        // Disconnect from the filesystem.
        wordpatch_filesystem_end();

        // Return the potential error (will be null if all succeeded).
        return ($delete_error !== null ? $delete_error : ($delete_error_old !== null ? $delete_error_old :
            ($delete_error_new !== null ? $delete_error_new : null)));
    }
}