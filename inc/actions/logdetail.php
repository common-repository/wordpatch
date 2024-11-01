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
 * Implements the log detail action.
 */

if(!function_exists('wordpatch_logdetail_uri')) {
    /**
     * Construct a URI to 'logdetail'.
     *
     * @param $wpenv_vars
     * @return string
     */
    function wordpatch_logdetail_uri($wpenv_vars, $log_id)
    {
        return wordpatch_add_query_params(wordpatch_env_get($wpenv_vars, 'base_uri'), 'action=' . WORDPATCH_WHERE_LOGDETAIL .
            '&log=' . urlencode($log_id));
    }
}

if(!function_exists('wordpatch_logdetail_url')) {
    /**
     * Construct a URL to 'logdetail'.
     *
     * @param $wpenv_vars
     * @param $log_id
     * @return string
     */
    function wordpatch_logdetail_url($wpenv_vars, $log_id)
    {
        $rescue_path = wordpatch_env_get($wpenv_vars, 'rescue_path');

        if(!$rescue_path) {
            return null;
        }

        $base_url = wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_site_url')) . $rescue_path;
        return wordpatch_add_query_params($base_url, 'action=' . WORDPATCH_WHERE_LOGDETAIL .
            '&log=' . urlencode($log_id));
    }
}

if(!function_exists('wordpatch_features_logdetail')) {
    /**
     * Returns features supported by 'logdetail'. See full list of options inside `inc/actions.php`.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_features_logdetail($wpenv_vars)
    {
        return array(WORDPATCH_FEATURE_REQUIRES_AUTH, WORDPATCH_FEATURE_RENDER);
    }
}

if(!function_exists('wordpatch_render_logdetail')) {
    /**
     * Render the log detail page.
     *
     * @param $wpenv_vars
     */
    function wordpatch_render_logdetail($wpenv_vars)
    {
        // Calculate $where and $error_vars for use with our draw field function calls.
        $where = WORDPATCH_WHERE_LOGDETAIL;
        $error_vars = array();

        // Grab the log ID from the request.
        $log_id = isset($_GET['log']) ? wordpatch_sanitize_unique_id($_GET['log']) : '';

        // Grab the log from the database.
        $logbox_row = wordpatch_logbox_by_id($wpenv_vars, $log_id);
        $judgable_log_ids = wordpatch_get_judgable_log_ids($wpenv_vars, $judgable_job_ids);

        $job = null;

        if($logbox_row !== null) {
            $job = wordpatch_get_job_by_id($wpenv_vars, $logbox_row['job_id']);
        }

        $show_breadcrumbs = wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars)) &&
            $logbox_row !== null && $job !== null;

        $breadcrumbs = !$show_breadcrumbs ? array() : array(
            wordpatch_jobs_breadcrumb_for_job($wpenv_vars, $job),
            wordpatch_job_breadcrumb($wpenv_vars, $job),
            wordpatch_logs_breadcrumb($wpenv_vars, $job['id']),
            array(
                'text' => __wt($wpenv_vars, 'BREADCRUMB_LOG_DETAIL')
            )
        );

        wordpatch_render_header($wpenv_vars, array(), $breadcrumbs);
        ?>
        <div class="wordpatch_page">
            <?php if (!wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars))) { ?>
                <?php wordpatch_render_access_denied($wpenv_vars); ?>
            <?php } else if(!$logbox_row) { ?>
                <?php wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'LOG_DETAIL_NOTICE_HEADING'),
                    __wte($wpenv_vars, 'LOG_DETAIL_NOTICE')); ?>
            <?php } else { ?>
                <?php
                $na = __wt($wpenv_vars, 'NOT_AVAILABLE_ABBR');

                $display_finish_datetime = $logbox_row['finish_datetime'] === null ?
                    $na : wordpatch_timestamp_to_readable($logbox_row['finish_datetime']);

                $display_running_datetime = $logbox_row['running_datetime'] === null ?
                    $na : wordpatch_timestamp_to_readable($logbox_row['running_datetime']);

                $display_success = $logbox_row['running'] ? $na :
                    ($logbox_row['success'] ? __wt($wpenv_vars, 'YES') : __wt($wpenv_vars, 'NO'));

                $rollback_rows = wordpatch_get_rollbacks_by_log_id($wpenv_vars, $log_id);
                ?>
                <div class="wordpatch_page_title_container">
                    <div class="wordpatch_page_title_left">
                        <h1 class="wordpatch_page_title"><?php echo __wte($wpenv_vars, 'LOG_DETAIL_TITLE'); ?></h1>
                    </div>
                    <?php if(in_array($log_id, $judgable_log_ids)) { ?>
                        <div class="wordpatch_page_title_right">
                            <div class="wordpatch_page_title_buttons">
                                <a href="<?php echo(wordpatch_judge_uri($wpenv_vars, $log_id)); ?>" class="wordpatch_button wordpatch_button_orange">
                                    <i class="fa fa-gavel" aria-hidden="true"></i>&nbsp;<?php echo(__wte($wpenv_vars, 'LOG_DETAIL_JUDGE')); ?>
                                </a>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <div class="wordpatch_log_preview">
                    <?php
                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_ID'), $logbox_row['id']);

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_PENDING_ID'),
                        $logbox_row['pending_id']);

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_JOB_ID'),
                        $logbox_row['job_id']);

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_TITLE'),
                        $logbox_row['title']);

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_PATH'),
                        wordpatch_job_display_path($logbox_row['path']));

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_MODE'),
                        wordpatch_display_mode($wpenv_vars, $logbox_row['mode']));

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_TIMER'),
                        wordpatch_display_timer($wpenv_vars, $logbox_row['timer']));

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_BINARY_MODE'),
                        wordpatch_display_job_binary_mode($wpenv_vars, $logbox_row['binary_mode']));

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_MAINTENANCE_MODE'),
                        wordpatch_display_job_maintenance_mode($wpenv_vars, $logbox_row['maintenance_mode']));

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_UPDATE_COOLDOWN'),
                        wordpatch_display_job_update_cooldown($wpenv_vars, $logbox_row['update_cooldown']));

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_INIT_REASON'),
                        wordpatch_display_init_reason($wpenv_vars, $logbox_row['init_reason']));

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_INIT_SUBJECT_LIST'),
                        wordpatch_display_init_subject_list($wpenv_vars, $logbox_row['init_subject_list']));

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_INIT_USER'),
                        wordpatch_get_display_user($wpenv_vars, $logbox_row['init_user']));

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_DATETIME'),
                        wordpatch_timestamp_to_readable($logbox_row['datetime']));

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_RUNNING_DATETIME'),
                        $display_running_datetime);

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_FINISH_DATETIME'),
                        $display_finish_datetime);

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_SUCCESS'),
                        $display_success);

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_SHOULD_RETRY'),
                        wordpatch_display_should_retry($wpenv_vars, $logbox_row['should_retry']));

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_ERRORS'),
                        wordpatch_display_errors($wpenv_vars, WORDPATCH_WHERE_LOGDETAIL, $logbox_row['error_list'],
                            $logbox_row['error_vars']));

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_RUNNING'),
                        wordpatch_display_running($wpenv_vars, $logbox_row['running']));

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_ATTEMPTS'),
                        wordpatch_display_attempts($wpenv_vars, $logbox_row['retry_count'], $logbox_row['attempt_count_int']));

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_PROGRESS'),
                        wordpatch_display_progress($wpenv_vars, $logbox_row['progress_percent'],
                            $logbox_row['progress_note'], $logbox_row['progress_datetime']));

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_CHANGES'),
                        wordpatch_display_changes($wpenv_vars, $logbox_row['changes']));

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_JUDGEMENT_PENDING'),
                        wordpatch_display_judgement_pending($wpenv_vars, $logbox_row['judgement_pending']));

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_JUDGEMENT_DECISION'),
                        wordpatch_display_judgement_decision($wpenv_vars, $logbox_row['judgement_decision']));

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_JUDGEMENT_DATETIME'),
                        wordpatch_display_judgement_datetime($wpenv_vars, $logbox_row['judgement_datetime']));

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_JUDGEMENT_USER'),
                        wordpatch_display_judgement_user($wpenv_vars, $logbox_row['judgement_decision'], $logbox_row['judgement_user']));

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_REJECT_ERRORS'),
                        wordpatch_display_errors($wpenv_vars, WORDPATCH_WHERE_LOGDETAIL, $logbox_row['reject_error_list'],
                            $logbox_row['reject_error_list']));

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_CLEANUP_STATUS'),
                        wordpatch_display_cleanup_status($wpenv_vars, $logbox_row['cleanup_status']));

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_CLEANUP_ERRORS'),
                        wordpatch_display_errors($wpenv_vars, WORDPATCH_WHERE_LOGDETAIL, $logbox_row['cleanup_error_list'],
                            $logbox_row['cleanup_error_list']));

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_REJECTION_PENDING'),
                        wordpatch_display_rejection_pending($wpenv_vars, $logbox_row['rejection_pending'],
                            $logbox_row['rejection_pending_datetime'], $logbox_row['rejection_pending_user_id']));

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_BACKUPS'),
                        wordpatch_display_backups($wpenv_vars, $logbox_row, $rollback_rows));

                    // Since there is 3 per line, render a dummy.
                    ?>
                    <div class="wordpatch_log_field wordpatch_log_field_dummy"></div>
                </div>
            <?php } ?>
        </div>
        <?php
        wordpatch_render_footer($wpenv_vars);
    }
}