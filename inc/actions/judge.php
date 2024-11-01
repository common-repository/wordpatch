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
 * Implements the judge functionality.
 */

// Include the judgement page dependencies
include_once(dirname(__FILE__) . '/judge/process_internal.php');
include_once(dirname(__FILE__) . '/judge/page_js.php');

if(!function_exists('wordpatch_judge_uri')) {
    /**
     * Construct a URI to 'judge'.
     *
     * @param $wpenv_vars
     * @param $log_id
     * @return string
     */
    function wordpatch_judge_uri($wpenv_vars, $log_id)
    {
        return wordpatch_add_query_params(wordpatch_env_get($wpenv_vars, 'base_uri'), 'action=' . WORDPATCH_WHERE_JUDGE .
            '&log=' . urlencode($log_id));
    }
}

if(!function_exists('wordpatch_judge_url')) {
    /**
     * Construct a URL to 'judge'.
     *
     * @param $wpenv_vars
     * @param $log_id
     * @return string
     */
    function wordpatch_judge_url($wpenv_vars, $log_id)
    {
        $rescue_path = wordpatch_env_get($wpenv_vars, 'rescue_path');

        if(!$rescue_path) {
            return null;
        }

        $base_url = wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_site_url')) . $rescue_path;
        return wordpatch_add_query_params($base_url, 'action=' . WORDPATCH_WHERE_JUDGE .
            '&log=' . urlencode($log_id));
    }
}

if(!function_exists('wordpatch_features_judge')) {
    /**
     * Returns features supported by 'judge'. See full list of options inside `inc/actions.php`.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_features_judge($wpenv_vars)
    {
        return array(WORDPATCH_FEATURE_REQUIRES_AUTH, WORDPATCH_FEATURE_PROCESS, WORDPATCH_FEATURE_RENDER);
    }
}

if(!function_exists('wordpatch_process_judge')) {
    /**
     * Handles processing logic for the judge form.
     *
     * @param $wpenv_vars
     */
    function wordpatch_process_judge($wpenv_vars)
    {
        if (!wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars))) {
            return;
        }

        $error_list = array();

        // Grab the log ID from the request.
        $log_id = isset($_GET['log']) ? wordpatch_sanitize_unique_id($_GET['log']) : '';

        // Grab the judgement decision
        $judgement_decision = isset($_POST['judgement_decision']) ? trim($_POST['judgement_decision']) : null;

        // Grab the logbox row from the database.
        $logbox_row = wordpatch_logbox_by_id($wpenv_vars, $log_id);

        // Check if the patch is valid.
        if(!$logbox_row) {
            $error_list[] = WORDPATCH_INVALID_LOG;
        } else {
            // Try to process the judgement using the internal function.
            $error_list = wordpatch_judge_process_internal($wpenv_vars, $logbox_row, $judgement_decision);
        }

        echo json_encode(array(
            'error' => wordpatch_no_errors($error_list) ? null : $error_list[0],
            'error_translation' => wordpatch_no_errors($error_list) ? null : wordpatch_translate_error($wpenv_vars,
                $error_list[0], WORDPATCH_WHERE_JUDGE, array('log_id' => $log_id, 'logbox_row' => $logbox_row))
        ));

        exit("");
    }
}

if(!function_exists('wordpatch_render_judge')) {
    /**
     * Render the judge page.
     *
     * @param $wpenv_vars
     */
    function wordpatch_render_judge($wpenv_vars)
    {
        // Grab the log ID from the request.
        $log_id = isset($_GET['log']) ? wordpatch_sanitize_unique_id($_GET['log']) : '';

        // Grab the log from the database for our render.
        $logbox_row = wordpatch_logbox_by_id($wpenv_vars, $log_id);

        $is_pending = $logbox_row !== null && ($logbox_row['id'] === null || trim($logbox_row['id']) === '');

        // Grab the errors from the variable store.
        $judge_errors = wordpatch_var_get('judge_errors');
        $judge_errors = wordpatch_no_errors($judge_errors) ? array() : $judge_errors;

        $job = null;

        if($logbox_row !== null) {
            $job = wordpatch_get_job_by_id($wpenv_vars, $logbox_row['job_id']);
        }

        // Calculate $where and $error_vars for use with our draw field function calls.
        $where = WORDPATCH_WHERE_JUDGE;
        $error_vars = array('logbox_row' => $logbox_row, 'log_id' => $log_id);

        $show_breadcrumbs = wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars)) &&
            $logbox_row !== null && $job !== null;

        $breadcrumbs = !$show_breadcrumbs ? array() : array(
            wordpatch_jobs_breadcrumb_for_job($wpenv_vars, $job),
            wordpatch_job_breadcrumb($wpenv_vars, $job),
            wordpatch_logs_breadcrumb($wpenv_vars, $job['id']),
            wordpatch_log_breadcrumb($wpenv_vars, $log_id),
            array(
                'text' => __wt($wpenv_vars, 'BREADCRUMB_JUDGE')
            )
        );

        wordpatch_render_header($wpenv_vars, array(), $breadcrumbs);
        ?>
        <div class="wordpatch_page">
            <?php if (!wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars))) { ?>
                <?php wordpatch_render_access_denied($wpenv_vars); ?>
            <?php } else { ?>
                <?php if ($logbox_row === null) { ?>
                    <?php wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'JUDGE_NOTICE_HEADING'),
                        __wte($wpenv_vars, 'JUDGE_NOTICE')); ?>
                <?php } else if($is_pending) { ?>
                    <?php wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'JUDGE_NOTICE_HEADING'),
                        __wte($wpenv_vars, 'JUDGE_NOTICE5')); ?>
                <?php } else if($logbox_row['running']) { ?>
                    <?php wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'JUDGE_NOTICE_HEADING'),
                        __wte($wpenv_vars, 'JUDGE_NOTICE2')); ?>
                <?php } else if(!$logbox_row['success']) { ?>
                    <?php wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'JUDGE_NOTICE_HEADING'),
                        __wte($wpenv_vars, 'JUDGE_NOTICE3')); ?>
                <?php } else if(!$logbox_row['judgement_pending'] || $logbox_row['rejection_pending']) { ?>
                    <?php
                    $link = sprintf("<a href=\"%s\">%s</a>", wordpatch_logdetail_uri($wpenv_vars, $log_id),
                        __wten($wpenv_vars, 'JUDGE_NOTICE4_LINK'));
                    ?>
                    <?php wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'JUDGE_NOTICE_HEADING'),
                        __wt($wpenv_vars, 'JUDGE_NOTICE4', $link)); ?>
                <?php } else { ?>
                    <?php
                    // Draw all error since none are specific to a field.
                    wordpatch_errors_maybe_draw_some($wpenv_vars, $judge_errors, null, $where, $error_vars);

                    $na = __wt($wpenv_vars, 'NOT_AVAILABLE_ABBR');

                    $display_finish_datetime = $logbox_row['finish_datetime'] === null ?
                        $na : wordpatch_timestamp_to_readable($logbox_row['finish_datetime']);
                    ?>

                    <div class="wordpatch_page_title_container">
                        <div class="wordpatch_page_title_left">
                            <h1 class="wordpatch_page_title"><?php echo __wte($wpenv_vars, 'JUDGE_TITLE'); ?></h1>
                        </div>
                    </div>

                    <div class="wordpatch_metabox">
                        <div class="wordpatch_metabox_body">
                            <p><?php echo __wte($wpenv_vars, 'JUDGE_MESSAGE'); ?></p>
                        </div>
                    </div>

                    <div class="wordpatch_log_preview">
                        <?php
                        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_ID'), $logbox_row['id']);

                        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_TITLE'),
                            $logbox_row['title']);

                        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_PATH'),
                            wordpatch_job_display_path($logbox_row['path']));

                        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_MODE'),
                            wordpatch_display_mode($wpenv_vars, $logbox_row['mode']));

                        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_TIMER'),
                            wordpatch_display_timer($wpenv_vars, $logbox_row['timer']));

                        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_BINARY_MODE'),
                            wordpatch_display_job_binary_mode($wpenv_vars, $logbox_row['binary_mode']));

                        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_INIT_REASON'),
                            wordpatch_display_init_reason($wpenv_vars, $logbox_row['init_reason']));

                        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_INIT_SUBJECT_LIST'),
                            wordpatch_display_init_subject_list($wpenv_vars, $logbox_row['init_subject_list']));

                        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_INIT_USER'),
                            wordpatch_get_display_user($wpenv_vars, $logbox_row['init_user']));

                        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_DATETIME'),
                            wordpatch_timestamp_to_readable($logbox_row['datetime']));

                        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_FINISH_DATETIME'),
                            $display_finish_datetime);

                        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_CHANGES'),
                            wordpatch_display_changes($wpenv_vars, $logbox_row['changes']));

                        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_ERRORS'),
                            wordpatch_display_errors($wpenv_vars, WORDPATCH_WHERE_JUDGE, $logbox_row['error_list'],
                                $logbox_row['error_vars']));
                        // Since there is 3 per line, render 2 dummies.
                        ?>
                        <div class="wordpatch_log_field wordpatch_log_field_dummy"></div>
                        <div class="wordpatch_log_field wordpatch_log_field_dummy"></div>
                    </div>

                    <form name="wordpatch_judgeform" id="wordpatch_judgeform"
                          action="<?php echo(wordpatch_judge_uri($wpenv_vars, $log_id)); ?>" method="post">
                        <div class="wordpatch_page_generic_buttons">
                            <a href="#" class="wordpatch_button wordpatch_button_red wordpatch_button_judge wordpatch_button_judge_reject">
                                <?php echo __wten($wpenv_vars, 'JUDGE_REJECT'); ?></a>
                            <a href="#" class="wordpatch_button wordpatch_button_blue wordpatch_button_judge wordpatch_button_judge_accept">
                                <?php echo __wten($wpenv_vars, 'JUDGE_ACCEPT'); ?></a>
                        </div>
                    </form>
                <?php } ?>
            <?php } ?>
        </div>
        <?php wordpatch_judge_draw_page_js($wpenv_vars, $log_id); ?>
        <?php
        wordpatch_render_footer($wpenv_vars);
    }
}