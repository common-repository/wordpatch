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
 * Implements the helper function for grabbing post variables from the edit patch page.
 */

if(!function_exists('wordpatch_editpatch_post_vars')) {
    /**
     * Calculate the current post variables for the edit patch form.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_editpatch_post_vars($wpenv_vars, $patch) {
        // Since WordPress actually sanitizes post data, we have a global for the original untouched version.
        global $__wordpatch_post;

        $current = array();

        $current['patch_title'] = isset($_POST['patch_title']) ? trim($_POST['patch_title']) : trim($patch['title']);

        $current['patch_type'] = (isset($_POST['patch_type']) && wordpatch_is_valid_patch_type(strtolower(trim($_POST['patch_type'])))) ?
            strtolower(trim($_POST['patch_type'])) : $patch['patch_type'];

        // Was a file uploaded?
        $file_uploaded = wordpatch_was_file_uploaded();
        $file_upload_success = $file_uploaded && wordpatch_did_file_upload_succeed();

        // The patch data specifically is pulled from our original untouched post data to prevent weird issues with slashes and newlines.
        $current['patch_data'] = null;

        if($current['patch_type'] === wordpatch_patch_type_text()) {
            $current['patch_data'] = isset($__wordpatch_post['patch_data']) ? $__wordpatch_post['patch_data'] : null;
        } else {
            $current['patch_data'] = $file_upload_success ? wordpatch_file_upload_read_binary() : null;
        }

        // If the value is still null, then we should load the data from the filesystem.
        if($current['patch_data'] === null) {
            // Default to an empty string just incase there is nothing to read.
            $current['patch_data'] = '';

            if($patch['patch_location'] && trim($patch['patch_location']) !== '') {
                $read_data = wordpatch_file_upload_read_from_location($wpenv_vars, wordpatch_file_upload_bucket_patches(),
                    $patch['patch_location'], $upload_read_error);

                $current['patch_data'] = $upload_read_error === null ? $read_data : '';
            }
        }

        $current['patch_file_uploaded'] = $file_uploaded;
        $current['patch_file_upload_success'] = $file_upload_success;

        if($current['patch_type'] !== wordpatch_patch_type_simple()) {
            $current['patch_old_file'] = '';
            $current['patch_new_file'] = '';
            $current['patch_path'] = '';
        } else {
            $current['patch_path'] = isset($_POST['patch_path']) ?
                wordpatch_sanitize_unix_file_path($_POST['patch_path']) : base64_decode($patch['patch_path']);

            $current['patch_old_file'] = isset($__wordpatch_post['patch_old_file']) ?
                base64_decode(trim($__wordpatch_post['patch_old_file'])) : null;

            if($current['patch_old_file'] === null && $patch['patch_location_old'] && trim($patch['patch_location_old']) !== '') {
                $read_data = wordpatch_file_upload_read_from_location($wpenv_vars, wordpatch_file_upload_bucket_edits(),
                    $patch['patch_location_old'], $upload_read_error);

                $current['patch_old_file'] = $upload_read_error === null ? $read_data : '';
            }

            $current['patch_new_file'] = isset($__wordpatch_post['patch_new_file']) ?
                base64_decode(trim($__wordpatch_post['patch_new_file'])) : null;

            if($current['patch_new_file'] === null && $patch['patch_location_new'] && trim($patch['patch_location_new']) !== '') {
                $read_data = wordpatch_file_upload_read_from_location($wpenv_vars, wordpatch_file_upload_bucket_edits(),
                    $patch['patch_location_new'], $upload_read_error);

                $current['patch_new_file'] = $upload_read_error === null ? $read_data : '';
            }
        }

        return $current;
    }
}