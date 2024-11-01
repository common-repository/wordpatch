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
 * Implements the jobs page.
 */

// Include the database configuration wizard dependencies
include_once(dirname(__FILE__) . '/jobs/post_vars.php');
include_once(dirname(__FILE__) . '/jobs/db_helpers.php');
include_once(dirname(__FILE__) . '/jobs/page_js.php');
include_once(dirname(__FILE__) . '/jobs/process_internal.php');

if(!function_exists('wordpatch_jobs_uri')) {
    /**
     * Construct a URI to 'jobs'.
     *
     * @param $wpenv_vars
     * @param null|bool $deleted
     * @param null|bool $trashjob_success
     * @param null|bool $deletejob_success
     * @param null|string $restorejob_success
     * @param null|bool $updateorder_success
     * @param null|string $runjob_success
     * @return string
     */
    function wordpatch_jobs_uri($wpenv_vars, $deleted = null, $trashjob_success = null, $deletejob_success = null,
                                $restorejob_success = null, $updateorder_success = null, $runjob_success = null)
    {
        $deleted = $deleted === null ? false : ($deleted ? true : false);

        return wordpatch_add_query_params(wordpatch_env_get($wpenv_vars, 'base_uri'), 'action=' . WORDPATCH_WHERE_JOBS . (!$deleted ? '' : '&deleted=1') .
            (($trashjob_success !== null && $trashjob_success) ? '&trashjob_success=1' : '') .
            (($deletejob_success !== null && $deletejob_success) ? '&deletejob_success=1' : '') .
            (($restorejob_success !== null && trim($restorejob_success) !== '') ? ('&restorejob_success=' . urlencode($restorejob_success)) : '') .
            (($runjob_success !== null && trim($runjob_success) !== '') ? ('&runjob_success=' . urlencode($runjob_success)) : '') .
            (($updateorder_success !== null && $updateorder_success) ? '&updateorder_success=1' : ''));
    }
}

if(!function_exists('wordpatch_features_jobs')) {
    /**
     * Returns features supported by 'jobs'. See full list of options inside `inc/actions.php`.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_features_jobs($wpenv_vars)
    {
        return array(WORDPATCH_FEATURE_REQUIRES_AUTH, WORDPATCH_FEATURE_PROCESS, WORDPATCH_FEATURE_RENDER);
    }
}

if(!function_exists('wordpatch_process_jobs')) {
    /**
     * Handles processing logic for the jobs form.
     *
     * @param $wpenv_vars
     */
    function wordpatch_process_jobs($wpenv_vars)
    {
        global $__wordpatch_post;

        if (!wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars))) {
            return;
        }

        // Calculate the current post variables.
        $current = wordpatch_jobs_post_vars($wpenv_vars);

        // Try to process the job using the internal function.
        $error_list = wordpatch_jobs_process_internal($wpenv_vars, $current);

        // Set the error list variable for access in render.
        wordpatch_var_set('jobs_errors', $error_list);
    }
}

if(!function_exists('wordpatch_render_jobs')) {
    /**
     * Render the jobs page.
     *
     * @param $wpenv_vars
     */
    function wordpatch_render_jobs($wpenv_vars)
    {
        // Calculate $where and $error_vars for use with our draw field function calls.
        $where = WORDPATCH_WHERE_JOBS;
        $error_vars = array();

        // Calculate the jobs page deleted state.
        $deleted = wordpatch_jobs_deleted($wpenv_vars);

        // Calculate the page title.
        $title = $deleted ? __wt($wpenv_vars, 'JOBS_TITLE_TRASH') : __wt($wpenv_vars, 'JOBS_TITLE');

        // Grab the errors from the variable store.
        $jobs_errors = wordpatch_var_get('jobs_errors');
        $jobs_errors = wordpatch_no_errors($jobs_errors) ? array() : $jobs_errors;

        // Calculate URL variables for redirection messages.
        $trashjob_success = (isset($_GET['trashjob_success']) && trim($_GET['trashjob_success']) === '1');
        $deletejob_success = (isset($_GET['deletejob_success']) && trim($_GET['deletejob_success']) === '1');
        $restorejob_success = (isset($_GET['restorejob_success']) && trim($_GET['restorejob_success']) !== '') ?
            wordpatch_sanitize_unique_id($_GET['restorejob_success']) : null;
        $updateorder_success = (isset($_GET['updateorder_success']) && trim($_GET['updateorder_success']) === '1');
        $runjob_success = (isset($_GET['runjob_success']) && trim($_GET['runjob_success']) !== '') ?
            wordpatch_sanitize_unique_id($_GET['runjob_success']) : null;

        // Calculate the jobs list.
        $jobs = wordpatch_get_jobs($wpenv_vars, $deleted);

        // We need these for our progress model.
        $judgable_job_ids = $judgable_log_ids = array();

        $judgable_log_ids = wordpatch_get_judgable_log_ids($wpenv_vars, $judgable_job_ids);
        $running_log = wordpatch_get_running_log($wpenv_vars);
        $pending_jobs = wordpatch_get_pending_jobs($wpenv_vars);
        $last_logbox = wordpatch_get_last_logbox_jobs($wpenv_vars);

        // Calculate the progress model.
        $progress_model = wordpatch_progress_check($wpenv_vars, $jobs, $running_log, $judgable_job_ids, $judgable_log_ids,
            $pending_jobs, $last_logbox);

        // Calculate the current jobs order string from the jobs list.
        $jobs_order = wordpatch_jobs_order($wpenv_vars, $jobs);

        wordpatch_render_header($wpenv_vars);
        ?>
        <div class="wordpatch_page">
            <?php if (!wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars))) { ?>
                <?php wordpatch_render_access_denied($wpenv_vars); ?>
            <?php } else { ?>
                <?php if ($trashjob_success) { ?>
                    <?php
                    $link1 = sprintf("<a href=\"%s\">%s</a>",
                        wordpatch_jobs_uri($wpenv_vars, true), __wten($wpenv_vars, 'JOBS_SUCCESS_TRASH_LINK'));

                    $link2 = sprintf("<a href=\"%s\">%s</a>",
                        wordpatch_jobs_uri($wpenv_vars, false), __wten($wpenv_vars, 'JOBS_SUCCESS_TRASH_LINK2'));
                    ?>
                    <?php wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'JOBS_SUCCESS_TRASH_HEADING'),
                        __wt($wpenv_vars, 'JOBS_SUCCESS_TRASH', $link1, $link2)); ?>
                <?php } else if($restorejob_success) { ?>
                    <?php
                    $link1 = sprintf("<a href=\"%s\">%s</a>",
                        wordpatch_editjob_uri($wpenv_vars, $restorejob_success), __wten($wpenv_vars, 'JOBS_SUCCESS_RESTORE_LINK'));

                    $link2 = sprintf("<a href=\"%s\">%s</a>",
                        wordpatch_jobs_uri($wpenv_vars, true), __wten($wpenv_vars, 'JOBS_SUCCESS_RESTORE_LINK2'));

                    $link3 = sprintf("<a href=\"%s\">%s</a>",
                        wordpatch_jobs_uri($wpenv_vars, false), __wten($wpenv_vars, 'JOBS_SUCCESS_RESTORE_LINK3'));
                    ?>
                    <?php wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'JOBS_SUCCESS_RESTORE_HEADING'),
                        __wt($wpenv_vars, 'JOBS_SUCCESS_RESTORE', $link1, $link2, $link3)); ?>
                <?php } else if($runjob_success) { ?>
                    <?php
                    $link1 = sprintf("<a href=\"%s\">%s</a>",
                        wordpatch_editjob_uri($wpenv_vars, $runjob_success), __wten($wpenv_vars, 'JOBS_SUCCESS_RUN_LINK'));

                    $link2 = sprintf("<a href=\"%s\">%s</a>",
                        wordpatch_jobs_uri($wpenv_vars), __wten($wpenv_vars, 'JOBS_SUCCESS_RUN_LINK2'));
                    ?>
                    <?php wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'JOBS_SUCCESS_RUN_HEADING'),
                        __wt($wpenv_vars, 'JOBS_SUCCESS_RUN', $link1, $link2)); ?>
                <?php } else if($deletejob_success) { ?>
                    <?php
                    $link = sprintf("<a href=\"%s\">%s</a>",
                        wordpatch_jobs_uri($wpenv_vars, true), __wten($wpenv_vars, 'JOBS_SUCCESS_DELETE_LINK'));
                    ?>
                    <?php wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'JOBS_SUCCESS_DELETE_HEADING'),
                        __wt($wpenv_vars, 'JOBS_SUCCESS_DELETE', $link)); ?>
                <?php } else if($updateorder_success) { ?>
                    <?php
                    $link = sprintf("<a href=\"%s\">%s</a>",
                        wordpatch_jobs_uri($wpenv_vars, false), __wten($wpenv_vars, 'JOBS_SUCCESS_ORDER_LINK'));
                    ?>
                    <?php wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'JOBS_SUCCESS_ORDER_HEADING'),
                        __wt($wpenv_vars, 'JOBS_SUCCESS_ORDER', $link)); ?>
                <?php } else { ?>
                    <div class="wordpatch_page_title_container">
                        <div class="wordpatch_page_title_left">
                            <h1 class="wordpatch_page_title"><?php echo htmlspecialchars($title); ?></h1>
                        </div>
                        <div class="wordpatch_page_title_right">
                            <div class="wordpatch_page_title_buttons">
                                <?php if ($deleted) { ?>
                                    <a href="<?php echo(wordpatch_jobs_uri($wpenv_vars, false)); ?>"
                                       class="wordpatch_button wordpatch_button_blue"><?php echo(__wten($wpenv_vars, 'JOBS_LINK_RETURN')); ?></a>
                                <?php } else { ?>
                                    <a href="<?php echo(wordpatch_newjob_uri($wpenv_vars)); ?>" class="wordpatch_button wordpatch_button_green">
                                        <?php echo(__wten($wpenv_vars, 'JOBS_LINK_NEW')); ?>
                                    </a><a href="<?php echo(wordpatch_jobs_uri($wpenv_vars, true)); ?>"
                                       class="wordpatch_button wordpatch_button_gray"><?php echo(__wten($wpenv_vars, 'JOBS_LINK_TRASH')); ?>
                                    </a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                    <?php
                    // Draw all error since none are specific to a field.
                    wordpatch_errors_maybe_draw_some($wpenv_vars, $jobs_errors, null, $where, $error_vars);
                    ?>

                    <?php
                    if(empty($jobs)) {
                        $empty_text = $deleted ? __wt($wpenv_vars, 'JOBS_EMPTY_TRASH') : __wt($wpenv_vars, 'JOBS_EMPTY');
                        ?>
                        <div class="wordpatch_metabox">
                            <div class="wordpatch_metabox_body">
                                <p><?php echo(htmlspecialchars($empty_text)); ?></p>
                            </div>
                        </div>
                        <?php
                    } else {
                        ?>
                        <?php if(!$deleted) { ?>
                            <form id="wordpatch_jobs_form"
                            action="<?php echo(wordpatch_jobs_uri($wpenv_vars)); ?>" method="POST">
                            <input type="hidden" name="jobs_order"
                                   value="<?php echo(htmlspecialchars($jobs_order)); ?>"/>
                        <?php } ?>

                        <div class="wordpatch_jobs_container">
                            <div class="wordpatch_jobs">
                            <?php foreach ($jobs as $job_index => $job_single) { ?>
                                <div class="wordpatch_job_ctr" data-job-id="<?php echo($job_single['id']); ?>"
                                    data-job-order="<?php echo($job_index + 1); ?>">
                                    <div class="wordpatch_job_ctr_flex">
                                        <div class="wordpatch_job_ctr_left">
                                            <div class="wordpatch_job_labels">
                                                <p class="wordpatch_job_label"><?php echo(__wten($wpenv_vars, 'JOBS_TABLE_TITLE')); ?></p>
                                                <p class="wordpatch_job_label"><?php echo(__wten($wpenv_vars, 'JOBS_TABLE_PATH')); ?></p>
                                            </div>
                                            <div class="wordpatch_job_meta">
                                                <a href="<?php echo(wordpatch_jobdetail_uri($wpenv_vars, $job_single['id'])); ?>" class="wordpatch_job_title">
                                                    <?php echo(htmlspecialchars($job_single['title'])); ?></a>
                                                <p class="wordpatch_job_path"><?php echo(htmlspecialchars(wordpatch_job_display_path($job_single['path']))); ?></p>
                                            </div>
                                        </div>
                                        <div class="wordpatch_job_ctr_right">
                                            <div class="wordpatch_text_right">
                                                <?php if(!$deleted) { ?>
                                                <?php
                                                $job_state = wordpatch_determine_job_progress_state($progress_model, $job_single['id']);
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
                                                   href="<?php echo(wordpatch_runjob_uri($wpenv_vars, $job_single['id'])); ?>">
                                                    <i class="fa fa-play" aria-hidden="true"></i>
                                                    <span><?php echo(__wten($wpenv_vars, 'JOBS_TABLE_RUN_LINK')); ?></span>
                                                </a>
                                                <a class="wordpatch_button wordpatch_job_button"
                                                   href="<?php echo(wordpatch_editjob_uri($wpenv_vars, $job_single['id'])); ?>">
                                                    <i class="fa fa-pencil" aria-hidden="true"></i>
                                                    <span><?php echo(__wten($wpenv_vars, 'JOBS_TABLE_EDIT_LINK')); ?></span>
                                                </a>
                                                <a class="wordpatch_button wordpatch_job_button"
                                                   href="<?php echo(wordpatch_trashjob_uri($wpenv_vars, $job_single['id'])); ?>">
                                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                                    <span><?php echo(__wten($wpenv_vars, 'JOBS_TABLE_TRASH_LINK')); ?></span>
                                                </a>
                                                <a class="wordpatch_button wordpatch_job_button"
                                                   href="<?php echo(wordpatch_logs_uri($wpenv_vars, $job_single['id'])); ?>">
                                                    <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                    <span><?php echo(__wten($wpenv_vars, 'JOBS_TABLE_LOGS_LINK')); ?></span>
                                                </a>
                                                <?php } else { ?>
                                                    <a class="wordpatch_button wordpatch_job_button"
                                                       href="<?php echo(wordpatch_restorejob_uri($wpenv_vars, $job_single['id'])); ?>">
                                                        <i class="fa fa-arrow-up" aria-hidden="true"></i>
                                                        <span><?php echo(__wten($wpenv_vars, 'JOBS_TABLE_RESTORE_LINK')); ?></span>
                                                    </a>
                                                    <a class="wordpatch_button wordpatch_job_button"
                                                       href="<?php echo(wordpatch_deletejob_uri($wpenv_vars, $job_single['id'])); ?>">
                                                        <i class="fa fa-eraser" aria-hidden="true"></i>
                                                        <span><?php echo(__wten($wpenv_vars, 'JOBS_TABLE_DELETE_LINK')); ?></span>
                                                    </a>
                                                <?php } ?>
                                            </div>
                                            <?php if(!$deleted) { ?>
                                            <div class="wordpatch_job_reorder">
                                                <a class="grip"><i class="fa fa-bars" aria-hidden="true"></i></a>
                                            </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="wordpatch_job_ctr_righter">
                                        <div class="wordpatch_job_reorder_righter">
                                            <a class="grip"><i class="fa fa-bars" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            </div>
                        </div>

                        <?php if (!$deleted) { ?>
                            <div class="wordpatch_metabox">
                                <div class="wordpatch_metabox_body">
                                    <input type="submit" name="submit" class="wordpatch_button wordpatch_button_blue" value="<?php echo(__wten($wpenv_vars, 'JOBS_SUBMIT_TEXT')); ?>"/>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if(!$deleted) { ?>
                            </form>
                        <?php } ?>
                        <?php
                    }
                    ?>
                <?php } ?>
            <?php } ?>
        </div>
        <?php wordpatch_jobs_draw_page_js($wpenv_vars); ?>
        <?php
        wordpatch_render_footer($wpenv_vars);
    }
}

if(!function_exists('wordpatch_jobs_deleted')) {
    /**
     * Calculate the jobs page deleted state based on the current request.
     *
     * @param $wpenv_vars
     * @return bool
     */
    function wordpatch_jobs_deleted($wpenv_vars) {
        $deleted = isset($_GET['deleted']) ? max(0, (int)trim($_GET['deleted'])) : 0;
        return $deleted ? true : false;
    }
}

if(!function_exists('wordpatch_jobs_order')) {
    /**
     * Calculate the jobs order string from a list of job rows.
     *
     * @param $wpenv_vars
     * @param $jobs
     * @return string
     */
    function wordpatch_jobs_order($wpenv_vars, $jobs) {
        $jobs_order = "";
        $is_first = true;

        foreach($jobs as $job_single) {
            if(!$is_first) {
                $jobs_order .= ',';
            }

            $jobs_order .= $job_single['id'];
            $is_first = false;
        }

        return $jobs_order;
    }
}