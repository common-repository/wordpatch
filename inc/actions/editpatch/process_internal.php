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
 * Implements the internal processing functionality for the edit patch form.
 */

if(!function_exists('wordpatch_editpatch_process_internal')) {
    /**
     * Internal processing for the edit patch form. Responsible for validation and persistence.
     *
     * @param $wpenv_vars
     * @param $current
     * @return array
     */
    function wordpatch_editpatch_process_internal($wpenv_vars, $current, $patch) {
        // Begin to calculate the error list.
        $error_list = array();

        // Validate the patch title.
        if ($current['patch_title'] === '') {
            $error_list[] = WORDPATCH_PATCH_TITLE_REQUIRED;
        }

        // Validate the patch data.
        if ($current['patch_type'] === wordpatch_patch_type_text() && $current['patch_data'] === '') {
            $error_list[] = WORDPATCH_PATCH_DATA_REQUIRED;
        }

        $silent_resub = $current['patch_type'] === wordpatch_patch_type_file() &&
            (isset($current['patch_data']) && $current['patch_data'] !== null && !empty($current['patch_data']));

        // Validate the patch file.
        if($current['patch_type'] === wordpatch_patch_type_file() &&
            (!$silent_resub && (!$current['patch_file_uploaded'] || !wordpatch_did_file_upload_succeed()))) {
            $error_list[] = WORDPATCH_PATCH_FILE_REQUIRED;
        }

        // Validate the patch path (simple patch).
        if($current['patch_type'] === wordpatch_patch_type_simple() && ($current['patch_path'] === '' || $current['patch_path'] === '/')) {
            $error_list[] = WORDPATCH_PATCH_PATH_REQUIRED;
        }

        // Validate the patch changes (simple patch).
        if($current['patch_type'] === wordpatch_patch_type_simple() && ($current['patch_old_file'] === $current['patch_new_file'])) {
            $error_list[] = WORDPATCH_PATCH_CHANGES_REQUIRED;
        }

        // If we have encountered validation errors, return early.
        if(!wordpatch_no_errors($error_list)) {
            return $error_list;
        }

        // Generate the patch.
        if($current['patch_type'] === wordpatch_patch_type_simple()) {
            $generateResponse = wordpatch_generate_api_request($wpenv_vars, $current['patch_old_file'],
                $current['patch_new_file'], $current['patch_path']);

            if(isset($generateResponse['error']) && $generateResponse['error'] !== null) {
                $error_list[] = $generateResponse['error'];
                return $error_list;
            }

            if(!isset($generateResponse['data']) || !isset($generateResponse['data']['patch_contents'])) {
                $error_list[] = WORDPATCH_UNKNOWN_ERROR;
                return $error_list;
            }

            $current['patch_data'] = base64_decode($generateResponse['data']['patch_contents']);
        }

        // Create a location for the patch file.
        $patch_location = ($patch['patch_location'] && trim($patch['patch_location']) !== '') ?
            trim($patch['patch_location']) : wordpatch_file_upload_generate_location();

        // Create a location for the old file (simple patch)
        $patch_location_old = ($patch['patch_location_old'] && trim($patch['patch_location_old']) !== '') ?
            trim($patch['patch_location_old']) : wordpatch_file_upload_generate_location();

        // Create a location for the new file (simple patch)
        $patch_location_new = ($patch['patch_location_new'] && trim($patch['patch_location_new']) !== '') ?
            trim($patch['patch_location_new']) : wordpatch_file_upload_generate_location();

        // Try to persist the database record.
        $fs_error = wordpatch_patchform_persist_fs($wpenv_vars, $current, $patch_location, $patch_location_old,
            $patch_location_new);

        // If there is an error from persisting, append it to our list and return early.
        if ($fs_error !== null) {
            $error_list[] = $fs_error;
            return $error_list;
        }

        // Try to persist the database record.
        $db_error = wordpatch_editpatch_persist_db($wpenv_vars, $current, $patch, $patch_location, $patch_location_old,
            $patch_location_new);

        // If there is an error from persisting, append it to our list and return early.
        if ($db_error !== null) {
            $error_list[] = $db_error;
            return $error_list;
        }

        // Redirect to the edit job page.
        wordpatch_redirect(wordpatch_editjob_uri($wpenv_vars, $patch['job_id'], null, null, null, $patch['id']));
        exit();
    }
}