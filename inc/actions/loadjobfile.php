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
 * Implements the load job file action.
 */

// Include the database configuration wizard dependencies
include_once(dirname(__FILE__) . '/loadjobfile/process_internal.php');

if(!function_exists('wordpatch_loadjobfile_uri')) {
    /**
     * Construct a URI to 'loadjobfile'.
     *
     * @param $wpenv_vars
     * @param $job_id
     * @return string
     */
    function wordpatch_loadjobfile_uri($wpenv_vars, $job_id)
    {
        $job_id = wordpatch_sanitize_unique_id($job_id);

        return wordpatch_add_query_params(wordpatch_env_get($wpenv_vars, 'base_uri'), 'action=' . WORDPATCH_WHERE_LOADJOBFILE . '&job=' . $job_id);
    }
}

if(!function_exists('wordpatch_features_loadjobfile')) {
    /**
     * Returns features supported by 'loadjobfile'. See full list of options inside `inc/actions.php`.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_features_loadjobfile($wpenv_vars)
    {
        return array(WORDPATCH_FEATURE_REQUIRES_AUTH, WORDPATCH_FEATURE_PROCESS);
    }
}

if(!function_exists('wordpatch_process_loadjobfile')) {
    /**
     * Handles processing logic for the load job file action.
     *
     * @param $wpenv_vars
     */
    function wordpatch_process_loadjobfile($wpenv_vars)
    {
        global $__wordpatch_post;

        if (!wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars))) {
            return;
        }

        $error_list = array();

        // Grab the job ID from the request.
        $job_id = isset($_GET['job']) ? wordpatch_sanitize_unique_id($_GET['job']) : '';

        // Grab the file path from the request.
        $file_path = isset($_POST['file_path']) ? wordpatch_sanitize_unix_file_path(trim($__wordpatch_post['file_path'])) : '';

        // Grab the job from our database.
        $job = wordpatch_get_job_by_id($wpenv_vars, $job_id);

        // Check if the job is valid.
        if(!$job || $job['deleted']) {
            $error_list[] = WORDPATCH_INVALID_JOB;
        }

        $file_contents = null;

        if(wordpatch_no_errors($error_list)) {
            // Try to process the load job file action using the internal function.
            $error_list = wordpatch_loadjobfile_process_internal($wpenv_vars, $job, $file_path, $file_contents);
        }

        echo json_encode(array(
            'file_contents' => $file_contents === null ? null : base64_encode($file_contents),
            'error' => wordpatch_no_errors($error_list) ? null : $error_list[0],
            'error_translation' => wordpatch_no_errors($error_list) ? null : wordpatch_translate_error($wpenv_vars, $error_list[0], WORDPATCH_WHERE_LOADJOBFILE,
                array('job_id' => $job_id, 'job' => $job))
        ));

        exit("");
    }
}