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
 * Implements the database helper functions for the run job page.
 */

if(!function_exists('wordpatch_runjob_persist_db')) {
    /**
     * Persists the database records for the run job page. Returns an error or null on success.
     *
     * @param $wpenv_vars
     * @param $job_id
     * @param $user_id
     * @return null|string
     */
    function wordpatch_runjob_persist_db($wpenv_vars, $job_id, $user_id) {
        // Sanitize the job ID first
        $job_id = wordpatch_sanitize_unique_id($job_id);

        // Return an error if the job ID is not valid.
        if ($job_id === '') {
            return WORDPATCH_INVALID_JOB;
        }

        // Try to add the pending job using our helper method.
        $add_success = wordpatch_add_pending_job($wpenv_vars, $job_id, WORDPATCH_INIT_REASON_MANUAL, null,
            $user_id, null);

        // If we failed to add the job to our pending list, return an error since it was a database failure.
        if(!$add_success) {
            return WORDPATCH_UNKNOWN_DATABASE_ERROR;
        }

        // If we got here, return null to indicate success.
        return null;
    }
}