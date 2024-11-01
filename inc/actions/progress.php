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
 * Implements the progress functionality.
 */

if(!function_exists('wordpatch_progress_uri')) {
    /**
     * Construct a URI to 'progress'.
     *
     * @param $wpenv_vars
     * @return string
     */
    function wordpatch_progress_uri($wpenv_vars)
    {
        return wordpatch_add_query_params(wordpatch_env_get($wpenv_vars, 'base_uri'), 'action=' . WORDPATCH_WHERE_PROGRESS);
    }
}

if(!function_exists('wordpatch_features_progress')) {
    /**
     * Returns features supported by 'progress'. See full list of options inside `inc/actions.php`.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_features_progress($wpenv_vars)
    {
        return array(WORDPATCH_FEATURE_PREFACE);
    }
}

if(!function_exists('wordpatch_preface_progress')) {
    /**
     * Implements the preface for the progress action. This essentially just returns a JSON response to admins which
     * contains the information necessary to render a real-time progress bar for WordPatch.
     *
     * @param $wpenv_vars
     */
    function wordpatch_preface_progress($wpenv_vars) {
        if(!wordpatch_is_logged_in($wpenv_vars) || !wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars))) {
            die(json_encode(array(
                'error' => WORDPATCH_UNKNOWN_ERROR
            )));
        }

        // We need these for our model.
        $judgable_job_ids = $judgable_log_ids = array();

        $judgable_log_ids = wordpatch_get_judgable_log_ids($wpenv_vars, $judgable_job_ids);
        $jobs = wordpatch_get_jobs($wpenv_vars, false);
        $running_log = wordpatch_get_running_log($wpenv_vars);
        $pending_jobs = wordpatch_get_pending_jobs($wpenv_vars);
        $last_logbox = wordpatch_get_last_logbox_jobs($wpenv_vars);

        // First run the general progress check function documented above.
        $progress_check = wordpatch_progress_check($wpenv_vars, $jobs, $running_log, $judgable_job_ids, $judgable_log_ids,
            $pending_jobs, $last_logbox);

        header("Content-Type: application/json;charset=utf-8");
        die(json_encode($progress_check));
    }
}