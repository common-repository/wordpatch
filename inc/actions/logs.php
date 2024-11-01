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
 * Implements the logs page.
 */

if(!function_exists('wordpatch_logs_uri')) {
    /**
     * Construct a URI to 'logs'.
     *
     * @param $wpenv_vars
     * @param $job_id
     * @param $page
     * @return string
     */
    function wordpatch_logs_uri($wpenv_vars, $job_id, $page = null)
    {
        $job_id = wordpatch_sanitize_unique_id($job_id);
        $page = $page === null ? null : max(1, (int)$page);

        return wordpatch_add_query_params(wordpatch_env_get($wpenv_vars, 'base_uri'), 'action=' . WORDPATCH_WHERE_LOGS .
            '&job=' . urlencode($job_id) . (($page !== null && $page) ? ('&wordpatchpage=' . $page) : ''));
    }
}

if(!function_exists('wordpatch_logs_url')) {
    /**
     * Calculate an absolute URL to the rescue logs action. This must go directly through the rescue script.
     *
     * @param $wpenv_vars
     * @param $job_id
     * @return null|string
     */
    function wordpatch_logs_url($wpenv_vars, $job_id)
    {
        $rescue_path = wordpatch_env_get($wpenv_vars, 'rescue_path');

        if(!$rescue_path) {
            return null;
        }

        $base_url = wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_site_url')) . $rescue_path;
        return wordpatch_add_query_params($base_url, 'action=' . WORDPATCH_WHERE_LOGS . '&job=' . urlencode($job_id));
    }
}

if(!function_exists('wordpatch_features_logs')) {
    /**
     * Returns features supported by 'logs'. See full list of options inside `inc/actions.php`.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_features_logs($wpenv_vars)
    {
        return array(WORDPATCH_FEATURE_REQUIRES_AUTH, WORDPATCH_FEATURE_RENDER);
    }
}

if(!function_exists('wordpatch_render_logs')) {
    /**
     * Render the logs page.
     *
     * @param $wpenv_vars
     */
    function wordpatch_render_logs($wpenv_vars)
    {
        // Grab the job ID from the request.
        $job_id = isset($_GET['job']) ? wordpatch_sanitize_unique_id($_GET['job']) : '';

        // Grab the job from the database for our render.
        $job = wordpatch_get_job_by_id($wpenv_vars, $job_id);

        $show_breadcrumbs = wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars)) &&
            $job !== null && !$job['deleted'];

        $breadcrumbs = !$show_breadcrumbs ? array() : array(
            wordpatch_jobs_breadcrumb_for_job($wpenv_vars, $job),
            wordpatch_job_breadcrumb($wpenv_vars, $job),
            wordpatch_logs_breadcrumb($wpenv_vars, $job_id)
        );

        wordpatch_render_header($wpenv_vars, array(), $breadcrumbs);
        ?>
        <div class="wordpatch_page">
            <?php if (!wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars))) { ?>
                <?php wordpatch_render_access_denied($wpenv_vars); ?>
            <?php } else if(!$job || $job['deleted']) { ?>
                <?php wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'LOGS_NOTICE_HEADING'),
                    __wte($wpenv_vars, 'LOGS_NOTICE')); ?>
            <?php } else { ?>
                <?php
                $page = isset($_GET['wordpatchpage']) ? max(1, (int)$_GET['wordpatchpage']) : 1;
                $total_logs = wordpatch_get_log_count($wpenv_vars, $job_id, $total_pages);
                $page = $page > $total_pages ? $total_pages : $page;

                $logbox = wordpatch_get_logbox($wpenv_vars, $job_id, $page);

                // We need these for our model.
                $judgable_job_ids = $judgable_log_ids = array();

                $judgable_log_ids = wordpatch_get_judgable_log_ids($wpenv_vars, $judgable_job_ids);
                $running_log = wordpatch_get_running_log($wpenv_vars);
                ?>
                <div class="wordpatch_page_title_container">
                    <div class="wordpatch_page_title_left">
                        <h1 class="wordpatch_page_title"><?php echo __wte($wpenv_vars, 'LOGS_TITLE'); ?></h1>
                    </div>
                </div>

                <?php
                if(empty($logbox)) {
                    ?>
                    <div class="wordpatch_metabox">
                        <div class="wordpatch_metabox_body">
                            <p><?php echo(__wte($wpenv_vars, 'LOGS_EMPTY')); ?></p>
                        </div>
                    </div>
                    <?php
                } else {
                    ?>
                    <div class="wordpatch_logbox_container">
                        <div class="wordpatch_logs">
                            <?php foreach ($logbox as $log_single) { ?>
                                <div class="wordpatch_log_ctr">
                                    <div class="wordpatch_log_ctr_flex">
                                        <div class="wordpatch_log_ctr_left">
                                            <div class="wordpatch_log_meta">
                                                <p class="wordpatch_log_datetime"><?php echo(htmlspecialchars(wordpatch_timestamp_to_readable($log_single['datetime']))); ?></p>
                                            </div>
                                        </div>
                                        <div class="wordpatch_log_ctr_right">
                                            <div class="wordpatch_text_right">
                                                <?php if($log_single['id'] === null) { ?>
                                                    <a class="wordpatch_button wordpatch_job_button wordpatch_job_state wordpatch_status_blue"
                                                       href="<?php echo(wordpatch_logdetail_uri($wpenv_vars, $log_single['pending_id'])); ?>">
                                                        <i class="fa fa-clock-o" aria-hidden="true"></i>
                                                        <span><?php echo(__wten($wpenv_vars, 'LOGS_TABLE_PENDING_LINK')); ?></span>
                                                    </a>
                                                <?php } else if($running_log !== null && $log_single['id'] === $running_log['id']) { ?>
                                                    <a class="wordpatch_button wordpatch_job_button wordpatch_job_state wordpatch_status_green"
                                                       href="<?php echo(wordpatch_logdetail_uri($wpenv_vars, $log_single['id'])); ?>">
                                                        <i class="fa fa-circle-o" aria-hidden="true"></i>
                                                        <span><?php echo(__wten($wpenv_vars, 'LOGS_TABLE_RUNNING_LINK')); ?></span>
                                                    </a>
                                                <?php } else if(in_array($log_single['id'], $judgable_log_ids)) { ?>
                                                    <a class="wordpatch_button wordpatch_job_button wordpatch_job_state wordpatch_job_alert"
                                                       href="<?php echo(wordpatch_judge_uri($wpenv_vars, $log_single['id'])); ?>">
                                                        <i class="fa fa-warning" aria-hidden="true"></i>
                                                        <span><?php echo(__wten($wpenv_vars, 'LOGS_TABLE_JUDGE_LINK')); ?></span>
                                                    </a>
                                                <?php } else if(!$log_single['success']) { ?>
                                                    <a class="wordpatch_button wordpatch_job_button wordpatch_job_state wordpatch_status_red"
                                                       href="<?php echo(wordpatch_logdetail_uri($wpenv_vars, $log_single['id'])); ?>">
                                                        <i class="fa fa-times-circle" aria-hidden="true"></i>
                                                        <span><?php echo(__wten($wpenv_vars, 'LOGS_TABLE_FAILED')); ?></span>
                                                    </a>
                                                <?php } else { ?>
                                                    <a class="wordpatch_button wordpatch_job_button wordpatch_job_state wordpatch_status_green"
                                                       href="<?php echo(wordpatch_logdetail_uri($wpenv_vars, $log_single['id'])); ?>">
                                                        <i class="fa fa-thumbs-up" aria-hidden="true"></i>
                                                        <span><?php echo(__wten($wpenv_vars, 'LOGS_TABLE_SUCCESS')); ?></span>
                                                    </a>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="wordpatch_logs_pagination_container">
                        <p class="wordpatch_logs_pagination_total"><?php echo(__wte($wpenv_vars,
                                'LOGS_TOTAL_RESULTS', $total_logs)); ?></p>
                        <div class="wordpatch_logs_pagination_links">
                            <?php wordpatch_pagination($wpenv_vars, $page, $total_pages, WORDPATCH_WHERE_LOGS); ?>
                        </div>
                    </div>
                    <?php
                }
                ?>
            <?php } ?>
        </div>
        <?php
        wordpatch_render_footer($wpenv_vars);
    }
}

if(!function_exists('wordpatch_logbox_pagination_builder')) {
    function wordpatch_logs_pagination_builder($wpenv_vars, $page, $is_current) {
        // Grab the job ID from the request.
        $job_id = isset($_GET['job']) ? wordpatch_sanitize_unique_id($_GET['job']) : '';

        $current_class = $is_current ? 'wordpatch_logs_page_link_current' : '';

        return sprintf("<a class=\"wordpatch_logs_page_link %s\" href=\"%s\">%d</a>",
            $current_class, wordpatch_logs_uri($wpenv_vars, $job_id, $page), $page);
    }
}

if(!function_exists('wordpatch_logs_pagination_prev_builder')) {
    function wordpatch_logs_pagination_prev_builder($wpenv_vars, $page) {
        // Grab the job ID from the request.
        $job_id = isset($_GET['job']) ? wordpatch_sanitize_unique_id($_GET['job']) : '';

        return sprintf("<a class=\"wordpatch_logs_page_link wordpatch_logs_page_link_previous\" href=\"%s\"><i class=\"fa fa-angle-left\"></i>&nbsp;%s</a>",
            wordpatch_logs_uri($wpenv_vars, $job_id, $page), __wte($wpenv_vars, 'PREVIOUS_PAGE'));
    }
}


if(!function_exists('wordpatch_logs_pagination_next_builder')) {
    function wordpatch_logs_pagination_next_builder($wpenv_vars, $page) {
        // Grab the job ID from the request.
        $job_id = isset($_GET['job']) ? wordpatch_sanitize_unique_id($_GET['job']) : '';

        return sprintf("<a class=\"wordpatch_logs_page_link wordpatch_logs_page_link_next\" href=\"%s\">%s&nbsp;<i class=\"fa fa-angle-right\"></i></a>",
            wordpatch_logs_uri($wpenv_vars, $job_id, $page), __wte($wpenv_vars, 'NEXT_PAGE'));
    }
}