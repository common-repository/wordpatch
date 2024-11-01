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
 * Implements the internal processing functionality for the run job form.
 */

if(!function_exists('wordpatch_runjob_process_internal')) {
    /**
     * Internal processing for the run job form. Responsible for validation and persistence.
     *
     * @param $wpenv_vars
     * @param $job_id
     * @param $user_id
     * @return array
     */
    function wordpatch_runjob_process_internal($wpenv_vars, $job_id, $user_id) {
        // Begin to calculate the error list.
        $error_list = array();

        // Make sure this job actually exists
        $job = wordpatch_get_job_by_id($wpenv_vars, $job_id);

        // Add an error if the job does not exist or is not in the trash and return early.
        if(!$job || $job['deleted']) {
            $error_list[] = WORDPATCH_INVALID_JOB;
            return $error_list;
        }

        if(!$job['enabled']) {
            $error_list[] = WORDPATCH_JOB_NOT_ENABLED;
            return $error_list;
        }

        // Try to persist the database record.
        $persist_error = wordpatch_runjob_persist_db($wpenv_vars, $job_id, $user_id);

        // Check if there was a persist error. Append if so and return early.
        if($persist_error !== null) {
            $error_list[] = $persist_error;
            return $error_list;
        }

        if(!isset($wpenv_vars['rescue_mode']) || !$wpenv_vars['rescue_mode']) {
            // Redirect to the page.
            wordpatch_redirect(wordpatch_redirect_url($wpenv_vars, $job_id));
            exit();
        }

        // Redirect to the page.
        wordpatch_redirect(wordpatch_jobs_uri($wpenv_vars, false, null, null, null, null, $job_id));
        exit();
    }
}