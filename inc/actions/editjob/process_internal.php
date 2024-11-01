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
 * Implements the internal processing functionality for the edit job form.
 */

if(!function_exists('wordpatch_editjob_process_internal')) {
    /**
     * Internal processing for the edit job form. Responsible for validation and persistence.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $job_id
     * @return array
     */
    function wordpatch_editjob_process_internal($wpenv_vars, $current, $job_id) {
        // Begin to calculate the error list.
        $error_list = array();

        // Make sure this job actually exists
        $job = wordpatch_get_job_by_id($wpenv_vars, $job_id);

        // Add an error if the job does not exist or has been trashed and return early.
        if(!$job || $job['deleted']) {
            $error_list[] = WORDPATCH_INVALID_JOB;
            return $error_list;
        }

        // Validate the job title, and if we detect it is empty append an error and return early.
        if($current['job_title'] === '') {
            $error_list[] = WORDPATCH_JOB_TITLE_REQUIRED;
            return $error_list;
        }

        // Try to persist the database record.
        $persist_error = wordpatch_editjob_persist_db($wpenv_vars, $current, $job_id);

        // If there is an error from persisting, append it to our list and return early.
        if ($persist_error !== null) {
            $error_list[] = $persist_error;
            return $error_list;
        }

        // Redirect to the edit job page.
        wordpatch_redirect(wordpatch_editjob_uri($wpenv_vars, $job_id, null, true));
        exit();
    }
}