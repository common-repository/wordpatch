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
 * Implements the job detail action.
 */

if(!function_exists('wordpatch_jobdetail_uri')) {
    /**
     * Construct a URI to 'jobdetail'.
     *
     * @param $wpenv_vars
     * @return string
     */
    function wordpatch_jobdetail_uri($wpenv_vars, $job_id)
    {
        return wordpatch_add_query_params(wordpatch_env_get($wpenv_vars, 'base_uri'), 'action=' . WORDPATCH_WHERE_JOBDETAIL .
            '&job=' . urlencode($job_id));
    }
}

if(!function_exists('wordpatch_jobdetail_url')) {
    /**
     * Construct a URL to 'jobdetail'.
     *
     * @param $wpenv_vars
     * @param $job_id
     * @return string
     */
    function wordpatch_jobdetail_url($wpenv_vars, $job_id)
    {
        $rescue_path = wordpatch_env_get($wpenv_vars, 'rescue_path');

        if(!$rescue_path) {
            return null;
        }

        $base_url = wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_site_url')) . $rescue_path;
        return wordpatch_add_query_params($base_url, 'action=' . WORDPATCH_WHERE_JOBDETAIL .
            '&job=' . urlencode($job_id));
    }
}

if(!function_exists('wordpatch_features_jobdetail')) {
    /**
     * Returns features supported by 'jobdetail'. See full list of options inside `inc/actions.php`.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_features_jobdetail($wpenv_vars)
    {
        return array(WORDPATCH_FEATURE_REQUIRES_AUTH, WORDPATCH_FEATURE_RENDER);
    }
}

if(!function_exists('wordpatch_render_jobdetail')) {
    /**
     * Render the job detail page.
     *
     * @param $wpenv_vars
     */
    function wordpatch_render_jobdetail($wpenv_vars)
    {
        // Calculate $where and $error_vars for use with our draw field function calls.
        $where = WORDPATCH_WHERE_JOBDETAIL;
        $error_vars = array();

        // Grab the job ID from the request.
        $job_id = isset($_GET['job']) ? wordpatch_sanitize_unique_id($_GET['job']) : '';

        // Grab the log from the database.
        $job = wordpatch_get_job_by_id($wpenv_vars, $job_id);

        $breadcrumbs = $job === null ? array() : array(
            wordpatch_jobs_breadcrumb_for_job($wpenv_vars, $job),
            wordpatch_job_breadcrumb($wpenv_vars, $job)
        );

        $jobs = wordpatch_get_jobs($wpenv_vars, false);

        // We need these for our progress model.
        $judgable_job_ids = $judgable_log_ids = array();

        $judgable_log_ids = wordpatch_get_judgable_log_ids($wpenv_vars, $judgable_job_ids);
        $running_log = wordpatch_get_running_log($wpenv_vars);
        $pending_jobs = wordpatch_get_pending_jobs($wpenv_vars);
        $last_logbox = wordpatch_get_last_logbox_jobs($wpenv_vars);

        // Calculate the progress model.
        $progress_model = wordpatch_progress_check($wpenv_vars, $jobs, $running_log, $judgable_job_ids, $judgable_log_ids,
            $pending_jobs, $last_logbox);

        wordpatch_render_header($wpenv_vars, array(), $breadcrumbs);
        ?>
        <div class="wordpatch_page">
            <?php if (!wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars))) { ?>
                <?php wordpatch_render_access_denied($wpenv_vars); ?>
            <?php } else if(!$job) { ?>
                <?php wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'JOB_DETAIL_NOTICE_HEADING'),
                    __wte($wpenv_vars, 'JOB_DETAIL_NOTICE')); ?>
            <?php } else { ?>
                <div class="wordpatch_page_title_container">
                    <div class="wordpatch_page_title_left">
                        <h1 class="wordpatch_page_title"><?php echo htmlspecialchars($job['title']); ?></h1>
                    </div>
                </div>

                <div class="wordpatch_metabox">
                    <div class="wordpatch_metabox_body">
                        <div class="wordpatch_jobs_compact">
                            <div class="wordpatch_job_ctr" data-job-id="<?php echo($job['id']); ?>">
                                <?php if(!$job['deleted']) { ?>
                                    <?php
                                    $job_state = wordpatch_determine_job_progress_state($progress_model, $job['id']);
                                    ?>
                                    <?php if($job_state['state'] === WORDPATCH_PROGRESS_STATE_RUNNING) { ?>
                                        <a class="wordpatch_button wordpatch_job_button wordpatch_job_state wordpatch_status_green"
                                           href="<?php echo(wordpatch_logdetail_uri($wpenv_vars, $job_state['log_id'])); ?>">
                                            <i class="fa fa-circle-o" aria-hidden="true"></i>
                                            <span><?php echo(__wten($wpenv_vars, 'JOBS_TABLE_RUNNING_LINK')); ?></span>
                                        </a>
                                    <?php } else if($job_state['state'] === WORDPATCH_PROGRESS_STATE_JUDGABLE) { ?>
                                        <a class="wordpatch_button wordpatch_job_button wordpatch_job_state wordpatch_job_alert"
                                           href="<?php echo(wordpatch_judge_uri($wpenv_vars, $job_state['log_id'])); ?>">
                                            <i class="fa fa-warning" aria-hidden="true"></i>
                                            <span><?php echo(__wten($wpenv_vars, 'JOBS_TABLE_JUDGE_LINK')); ?></span>
                                        </a>
                                    <?php } else if($job_state['state'] === WORDPATCH_PROGRESS_STATE_PENDING) { ?>
                                        <a class="wordpatch_button wordpatch_job_button wordpatch_job_state wordpatch_status_blue"
                                           href="<?php echo(wordpatch_logdetail_uri($wpenv_vars, $job_state['pending_id'])); ?>">
                                            <i class="fa fa-clock-o" aria-hidden="true"></i>
                                            <span><?php echo(__wten($wpenv_vars, 'JOBS_TABLE_PENDING_LINK')); ?></span>
                                        </a>
                                    <?php } else if($job_state['state'] === WORDPATCH_PROGRESS_STATE_FAILED) { ?>
                                        <a class="wordpatch_button wordpatch_job_button wordpatch_job_state wordpatch_status_red"
                                           href="<?php echo(wordpatch_logdetail_uri($wpenv_vars, $job_state['log_id'])); ?>">
                                            <i class="fa fa-times-circle" aria-hidden="true"></i>
                                            <span><?php echo(__wten($wpenv_vars, 'JOBS_TABLE_FAILED')); ?></span>
                                        </a>
                                    <?php } else if($job_state['state'] === WORDPATCH_PROGRESS_STATE_SUCCESS) { ?>
                                        <a class="wordpatch_button wordpatch_job_button wordpatch_job_state wordpatch_status_green"
                                           href="<?php echo(wordpatch_logdetail_uri($wpenv_vars, $job_state['log_id'])); ?>">
                                            <i class="fa fa-thumbs-up" aria-hidden="true"></i>
                                            <span><?php echo(__wten($wpenv_vars, 'JOBS_TABLE_SUCCESS')); ?></span>
                                        </a>
                                    <?php } ?>
                                    <a class="wordpatch_button wordpatch_job_button"
                                       href="<?php echo(wordpatch_runjob_uri($wpenv_vars, $job['id'])); ?>">
                                        <i class="fa fa-play" aria-hidden="true"></i>
                                        <span><?php echo(__wten($wpenv_vars, 'JOBS_TABLE_RUN_LINK')); ?></span>
                                    </a>
                                    <a class="wordpatch_button wordpatch_job_button"
                                       href="<?php echo(wordpatch_editjob_uri($wpenv_vars, $job['id'])); ?>">
                                        <i class="fa fa-pencil" aria-hidden="true"></i>
                                        <span><?php echo(__wten($wpenv_vars, 'JOBS_TABLE_EDIT_LINK')); ?></span>
                                    </a>
                                    <a class="wordpatch_button wordpatch_job_button"
                                       href="<?php echo(wordpatch_trashjob_uri($wpenv_vars, $job['id'])); ?>">
                                        <i class="fa fa-trash" aria-hidden="true"></i>
                                        <span><?php echo(__wten($wpenv_vars, 'JOBS_TABLE_TRASH_LINK')); ?></span>
                                    </a>
                                    <a class="wordpatch_button wordpatch_job_button"
                                       href="<?php echo(wordpatch_logs_uri($wpenv_vars, $job['id'])); ?>">
                                        <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                        <span><?php echo(__wten($wpenv_vars, 'JOBS_TABLE_LOGS_LINK')); ?></span>
                                    </a>
                                <?php } else { ?>
                                    <a class="wordpatch_button wordpatch_job_button"
                                       href="<?php echo(wordpatch_restorejob_uri($wpenv_vars, $job['id'])); ?>">
                                        <i class="fa fa-arrow-up" aria-hidden="true"></i>
                                        <span><?php echo(__wten($wpenv_vars, 'JOBS_TABLE_RESTORE_LINK')); ?></span>
                                    </a>
                                    <a class="wordpatch_button wordpatch_job_button"
                                       href="<?php echo(wordpatch_deletejob_uri($wpenv_vars, $job['id'])); ?>">
                                        <i class="fa fa-eraser" aria-hidden="true"></i>
                                        <span><?php echo(__wten($wpenv_vars, 'JOBS_TABLE_DELETE_LINK')); ?></span>
                                    </a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wordpatch_log_preview">
                    <?php
                    wordpatch_log_field_render($wpenv_vars, __wte($wpenv_vars, 'JOB_DETAIL_PATH_HEADER'),
                        wordpatch_job_display_path($job['path']));

                    wordpatch_log_field_render($wpenv_vars, __wte($wpenv_vars, 'JOB_DETAIL_ENABLED_HEADER'),
                        wordpatch_display_job_enabled($wpenv_vars, $job['enabled'] ? wordpatch_job_enabled_yes() : wordpatch_job_enabled_no()));

                    wordpatch_log_field_render($wpenv_vars, __wte($wpenv_vars, 'JOB_DETAIL_MODE_HEADER'),
                        wordpatch_display_job_mode($wpenv_vars, $job['mode']));

                    wordpatch_log_field_render($wpenv_vars, __wte($wpenv_vars, 'JOB_DETAIL_TIMER_HEADER'),
                        wordpatch_display_job_timer($wpenv_vars, $job['timer']));

                    wordpatch_log_field_render($wpenv_vars, __wte($wpenv_vars, 'JOB_DETAIL_UPDATE_COOLDOWN_HEADER'),
                        wordpatch_display_job_update_cooldown($wpenv_vars, $job['update_cooldown']));

                    wordpatch_log_field_render($wpenv_vars, __wte($wpenv_vars, 'JOB_DETAIL_RETRY_COUNT_HEADER'),
                        wordpatch_display_job_retry_count($wpenv_vars, $job['retry_count']));

                    wordpatch_log_field_render($wpenv_vars, __wte($wpenv_vars, 'JOB_DETAIL_MAINTENANCE_MODE_HEADER'),
                        wordpatch_display_job_maintenance_mode($wpenv_vars, $job['maintenance_mode']));

                    wordpatch_log_field_render($wpenv_vars, __wte($wpenv_vars, 'JOB_DETAIL_BINARY_MODE_HEADER'),
                        wordpatch_display_job_binary_mode($wpenv_vars, $job['binary_mode']));

                    // Since there is 3 per line, render 2 dummies.
                    ?>
                    <div class="wordpatch_log_field wordpatch_log_field_dummy"></div>
                </div>
            <?php } ?>
        </div>
        <?php
        wordpatch_render_footer($wpenv_vars);
    }
}