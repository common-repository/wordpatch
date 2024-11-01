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
 * Implements the internal processing functionality for the delete job form.
 */

if(!function_exists('wordpatch_deletejob_process_internal')) {
    /**
     * Internal processing for the delete job form. Responsible for validation and persistence.
     *
     * @param $wpenv_vars
     * @param $job_id
     * @return array
     */
    function wordpatch_deletejob_process_internal($wpenv_vars, $job_id) {
        // Begin to calculate the error list.
        $error_list = array();

        // Make sure this job actually exists
        $job = wordpatch_get_job_by_id($wpenv_vars, $job_id);

        // Add an error if the job already was deleted or is not currently in the trash and return early.
        if(!$job || !$job['deleted']) {
            $error_list[] = WORDPATCH_INVALID_JOB;
            return $error_list;
        }

        // Try to perform the filesystem related persistence.
        $fs_error = wordpatch_deletejob_persist_fs($wpenv_vars, $job_id);

        // Check if there was a persist error. Append if so and return early.
        if($fs_error !== null) {
            $error_list[] = $fs_error;
            return $error_list;
        }

        // Try to persist the database record.
        $db_error = wordpatch_deletejob_persist_db($wpenv_vars, $job_id);

        // Check if there was a persist error. Append if so and return early.
        if($db_error !== null) {
            $error_list[] = $db_error;
            return $error_list;
        }

        // Redirect the user since the deletion succeeded.
        wordpatch_redirect(wordpatch_jobs_uri($wpenv_vars, true, null, true));
        exit();
    }
}