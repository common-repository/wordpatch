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
 * Implements the erase patch form.
 */

// Include the database configuration wizard dependencies
include_once(dirname(__FILE__) . '/erasepatch/process_internal.php');
include_once(dirname(__FILE__) . '/erasepatch/db_helpers.php');
include_once(dirname(__FILE__) . '/clensepatch/fs_helpers.php');

if(!function_exists('wordpatch_erasepatch_uri')) {
    /**
     * Construct a URI to 'erasepatch'.
     *
     * @param $wpenv_vars
     * @param $patch_id
     * @return string
     */
    function wordpatch_erasepatch_uri($wpenv_vars, $patch_id)
    {
        $patch_id = wordpatch_sanitize_unique_id($patch_id);

        return wordpatch_add_query_params(wordpatch_env_get($wpenv_vars, 'base_uri'), 'action=' . WORDPATCH_WHERE_ERASEPATCH . '&patch=' . $patch_id);
    }
}

if(!function_exists('wordpatch_features_erasepatch')) {
    /**
     * Returns features supported by 'erasepatch'. See full list of options inside `inc/actions.php`.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_features_erasepatch($wpenv_vars)
    {
        return array(WORDPATCH_FEATURE_REQUIRES_AUTH, WORDPATCH_FEATURE_PROCESS);
    }
}

if(!function_exists('wordpatch_process_erasepatch')) {
    /**
     * Handles processing logic for the erase patch form.
     *
     * @param $wpenv_vars
     */
    function wordpatch_process_erasepatch($wpenv_vars)
    {
        if (!wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars))) {
            return;
        }

        $error_list = array();

        // Grab the patch ID from the request.
        $patch_id = isset($_GET['patch']) ? wordpatch_sanitize_unique_id($_GET['patch']) : '';

        // Grab the patch from our database.
        $patch = wordpatch_get_patch_by_id($wpenv_vars, $patch_id);

        // Check if the patch is valid.
        if(!$patch) {
            $error_list[] = WORDPATCH_INVALID_PATCH;
        } else {
            // Grab the job from our database.
            $job = wordpatch_get_job_by_id($wpenv_vars, $patch['job_id']);

            // Check if the job is valid.
            if (!$job || $job['deleted']) {
                $error_list[] = WORDPATCH_INVALID_JOB;
            }
        }

        if(wordpatch_no_errors($error_list)) {
            // Try to process the erase patch using the internal function.
            $error_list = wordpatch_erasepatch_process_internal($wpenv_vars, $patch);
        }

        echo json_encode(array(
            'error' => wordpatch_no_errors($error_list) ? null : $error_list[0],
            'error_translation' => wordpatch_no_errors($error_list) ? null : wordpatch_translate_error($wpenv_vars, $error_list[0], WORDPATCH_WHERE_ERASEPATCH,
                array('patch_id' => $patch_id, 'patch' => $patch))
        ));

        exit("");
    }
}