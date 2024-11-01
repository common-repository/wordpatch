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
 * Implements the work action which handles the following tasks:
 * - 1. Throttling Work Access
 * - 2. Freshening Expired Jobs
 * - 3. Enqueue Scheduled Jobs
 * - 4. Auto Approve Matured Optimistic Jobs
 * - 5. Enqueue Auto Rejection of Matured Pessimistic Jobs
 * - 6. Enqueue Single Actionable Job
 * - 7. Invoke Job Runner (see `inc\jobrunner.php` for more information)
 *
 * Please note that special care has been taken to ensure that this process is safe in the event of two concurrently
 * running executions. However, it does this partially by limiting the number of jobs that are allowed to run to one at
 * any given time.
 */

// Include the work dependencies
include_once(dirname(__FILE__) . '/work/approve.php');
include_once(dirname(__FILE__) . '/work/freshen.php');
include_once(dirname(__FILE__) . '/work/jobrunner.php');
include_once(dirname(__FILE__) . '/work/nextjob.php');
include_once(dirname(__FILE__) . '/work/reject.php');
include_once(dirname(__FILE__) . '/work/schedule.php');
include_once(dirname(__FILE__) . '/work/throttle.php');

if(!function_exists('wordpatch_work_url')) {
    /**
     * Calculate an absolute URL to the work action. This must go directly through the rescue script.
     *
     * @param $wpenv_vars
     * @return null|string
     */
    function wordpatch_work_url($wpenv_vars)
    {
        $rescue_path = wordpatch_env_get($wpenv_vars, 'rescue_path');

        if(!$rescue_path) {
            return null;
        }

        $base_url = wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_site_url')) . $rescue_path;
        return wordpatch_add_query_params($base_url, 'action=' . WORDPATCH_WHERE_WORK);
    }
}

if(!function_exists('wordpatch_features_work')) {
    /**
     * Returns features supported by 'work'. See full list of options inside `inc/actions.php`.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_features_work($wpenv_vars)
    {
        return array(WORDPATCH_FEATURE_PREFACE);
    }
}

if(!function_exists('wordpatch_preface_work')) {
    function wordpatch_preface_work($wpenv_vars) {
        // Prevent access if this is not being called via the rescue script.
        if(!isset($wpenv_vars['rescue_mode']) || !$wpenv_vars['rescue_mode']) {
            die(json_encode(array(
                'error' => WORDPATCH_UNKNOWN_ERROR
            )));
        }

        // If a user abort's their request, we don't want to kill the script.
        ignore_user_abort(true);

        // Give ourselves five minutes max since shared hosts would never allow a script to run longer than that.
        @set_time_limit(300);

        // Throttle access to this endpoint.
        $throttle_response = wordpatch_work_throttler($wpenv_vars);

        // If the result value is anything other than false, we should throttle them. Simply die the encoded response.
        if($throttle_response !== false) {
            die(json_encode($throttle_response));
        }

        // TODO: At this point, why not just close the connection? Work will continue if done properly.

        // Freshen log entries that have expired.
        wordpatch_work_freshen_up($wpenv_vars);

        // Select from schedule and add jobs to pending job list if necessary.
        wordpatch_work_enqueue_schedule($wpenv_vars);

        // Automatically approve any jobs that are optimistic and satisfy the requirements for auto approval.
        wordpatch_work_approve_optimistic($wpenv_vars);

        // Automatically enqueue rejection of any jobs that are in pessimistic and satisfy the requirements for auto rejection.
        wordpatch_work_enqueue_reject_pessimistic($wpenv_vars);

        // Now, try to queue a job up
        $running_log_info = wordpatch_work_enqueue_next_job($wpenv_vars);

        // If there was a job queued from the above call, we should connect to the filesystem and run the job.
        if($running_log_info) {
            // Try to connect to the filesystem.
            $fs_begin = wordpatch_filesystem_begin_helper($wpenv_vars);

            // List of errors that can be reported by the jobrunner that indicate an issue with the filesystem configuration.
            $fs_related_errors = array(
                WORDPATCH_INVALID_FILESYSTEM_CREDENTIALS,
                WORDPATCH_FILESYSTEM_FAILED_WRITE,
                WORDPATCH_FILESYSTEM_FAILED_DELETE,
                WORDPATCH_FILESYSTEM_FAILED_DELETE_DIR,
                WORDPATCH_FILESYSTEM_FAILED_WRITE_DIR,
                WORDPATCH_FILESYSTEM_FAILED_READ,
                WORDPATCH_FILESYSTEM_FAILED_VERIFY_DIR,
                WORDPATCH_FS_PERMISSIONS_ERROR
            );

            // Optimistically assume that we have not encountered an error until we actually do.
            $has_fs_related_error = false;

            // Only actually attempt to run the job if we connected to the filesystem successfully.
            if ($fs_begin) {
                // Call the jobrunner!
                $runner_result = wordpatch_job_runner($wpenv_vars, $running_log_info);

                // Disconnect from the filesystem
                wordpatch_filesystem_end();

                // If we can find an error that is inside the FS related errors array, then mark bool as true and break.
                foreach($runner_result['error_list'] as $runner_error) {
                    if(!in_array($runner_error, $fs_related_errors)) {
                        continue;
                    }

                    $has_fs_related_error = true;
                    break;
                }
            }

            // If we either had an issue connecting or the FS related errors bool has been set to true, record it.
            if(!$fs_begin || $has_fs_related_error) {
                // Get the current unix timestamp.
                $current_timestamp = wordpatch_utc_timestamp();

                // Store the FS issue option to ensure messaging will be shown.
                wordpatch_update_option($wpenv_vars, WORDPATCH_FS_ISSUE_KEY, $current_timestamp);
            }
        }

        // If there was something enqueued, then we should ping back to ensure a quicker re-entry.
        if($running_log_info) {
            wordpatch_ping_api_request($wpenv_vars);
        }

        // Echo and die! :)
        die(json_encode(array(
            'error' => null
        )));
    }
}