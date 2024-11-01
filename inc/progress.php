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
 * Implements the shared progress bar functionality.
 */

if(!function_exists('wordpatch_progress_check')) {
    /**
     * Perform the shared progress check (called from the preface and the render progress bar functions).
     * This really just calls `wordpatch_get_progress_log` and wraps it a bit.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_progress_check($wpenv_vars, $jobs, $running_log, $judgable_job_ids, $judgable_log_ids,
                                      $pending_jobs, $last_logbox) {
        // Populate our model
        $progress_model = array(
            'job_ids' => array(),
            'judgable_log_ids' => array(),
            'judgable_job_ids' => array(),
            'running_log_id' => null,
            'running_job_id' => null,
            'pending_jobs' => array(),
            'success_log_ids' => array(),
            'success_job_ids' => array(),
            'failed_log_ids' => array(),
            'failed_job_ids' => array()
        );

        // Populate the job IDs
        foreach($jobs as $job_single) {
            $progress_model['job_ids'][] = wordpatch_sanitize_unique_id($job_single['id']);
        }

        // Populate the judgable log/job IDs
        $progress_model['judgable_log_ids'] = $judgable_log_ids;
        $progress_model['judgable_job_ids'] = $judgable_job_ids;

        // Populate the running log/job IDs
        if($running_log !== null) {
            $progress_model['running_log_id'] = wordpatch_sanitize_unique_id($running_log['id']);
            $progress_model['running_job_id'] = wordpatch_sanitize_unique_id($running_log['job_id']);
        }

        // Populate the pending job information
        foreach($pending_jobs as $pending_job) {
            $progress_model['pending_jobs'][] = array(
                'job_id' => wordpatch_sanitize_unique_id($pending_job['job_id']),
                'pending_id' => wordpatch_sanitize_unique_id($pending_job['pending_id'])
            );
        }

        // Populate the success/fail log/job IDs
        foreach($last_logbox as $logbox_single) {
            if($logbox_single['success']) {
                $progress_model['success_log_ids'][] = wordpatch_sanitize_unique_id($logbox_single['id']);
                $progress_model['success_job_ids'][] = wordpatch_sanitize_unique_id($logbox_single['job_id']);
            } else {
                $progress_model['failed_log_ids'][] = wordpatch_sanitize_unique_id($logbox_single['id']);
                $progress_model['failed_job_ids'][] = wordpatch_sanitize_unique_id($logbox_single['job_id']);
            }
        }

        return $progress_model;
    }
}

if(!function_exists('wordpatch_determine_job_progress_state')) {
    /**
     * This function is used to check a single job ID's state against the progress model.
     * There is a 1-to-1 implementation of this function available in JavaScript (named wordpatchProgress_DetermineJobState)
     *
     * @param $progress_model
     * @param $job_id
     * @return array|null
     */
    function wordpatch_determine_job_progress_state($progress_model, $job_id) {
        $job_id = wordpatch_sanitize_unique_id($job_id);

        // First make sure that the job ID is part of the progress model.
        if($job_id === '' || !in_array($job_id, $progress_model['job_ids'])) {
            return null;
        }

        // Check if the job ID matches the running job ID.
        if($progress_model['running_job_id'] === $job_id) {
            return array(
                'state' => WORDPATCH_PROGRESS_STATE_RUNNING,
                'job_id' => $job_id,
                'log_id' => $progress_model['running_log_id']
            );
        }

        // The job might be judgable, so check here.
        foreach($progress_model['judgable_job_ids'] as $judgable_idx => $judgable_job_id) {
            if($judgable_job_id !== $job_id) {
                continue;
            }

            return array(
                'state' => WORDPATCH_PROGRESS_STATE_JUDGABLE,
                'job_id' => $job_id,
                'log_id' => $progress_model['judgable_log_ids'][$judgable_idx]
            );
        }

        // Maybe the job is pending? Loop through the pending jobs to check.
        foreach($progress_model['pending_jobs'] as $pending_job) {
            if($pending_job['job_id'] !== $job_id) {
                continue;
            }

            return array(
                'state' => WORDPATCH_PROGRESS_STATE_PENDING,
                'job_id' => $job_id,
                'pending_id' => $pending_job['pending_id']
            );
        }

        // Perhaps the job succeeded?
        foreach($progress_model['success_job_ids'] as $success_idx => $success_job_id) {
            if($success_job_id !== $job_id) {
                continue;
            }

            return array(
                'state' => WORDPATCH_PROGRESS_STATE_SUCCESS,
                'job_id' => $job_id,
                'log_id' => $progress_model['success_log_ids'][$success_idx]
            );
        }

        // Perhaps the job failed?
        foreach($progress_model['failed_job_ids'] as $failed_idx => $failed_job_id) {
            if($failed_job_id !== $job_id) {
                continue;
            }

            return array(
                'state' => WORDPATCH_PROGRESS_STATE_FAILED,
                'job_id' => $job_id,
                'log_id' => $progress_model['failed_log_ids'][$failed_idx]
            );
        }

        // Otherwise, the job is idle.
        return array(
            'state' => WORDPATCH_PROGRESS_STATE_IDLE,
            'job_id' => $job_id
        );
    }
}