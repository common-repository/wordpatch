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
 * Implements the WordPatch dashboard.
 */

if(!function_exists('wordpatch_dashboard_uri')) {
    /**
     * Construct a URI to 'dashboard'.
     *
     * @param $wpenv_vars
     * @return string
     */
    function wordpatch_dashboard_uri($wpenv_vars)
    {
        return wordpatch_add_query_params(wordpatch_env_get($wpenv_vars, 'base_uri'), 'action=' . WORDPATCH_WHERE_DASHBOARD);
    }
}

if(!function_exists('wordpatch_features_dashboard')) {
    /**
     * Returns features supported by 'dashboard'. See full list of options inside `inc/actions.php`.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_features_dashboard($wpenv_vars)
    {
        return array(WORDPATCH_FEATURE_REQUIRES_AUTH, WORDPATCH_FEATURE_RENDER);
    }
}

if(!function_exists('wordpatch_render_dashboard')) {
    /**
     * Render the dashboard page.
     *
     * @param $wpenv_vars
     */
    function wordpatch_render_dashboard($wpenv_vars)
    {
        // Check if the DB is configured.
        $is_db_configured = wordpatch_calculate_db_configured($wpenv_vars) === WORDPATCH_YES;

        // Check if the filesystem is configured.
        $is_fs_configured = wordpatch_calculate_fs_configured($wpenv_vars) === WORDPATCH_YES;

        // Check if the rescue script is configured.
        $is_rescue_configured = wordpatch_calculate_rescue_configured($wpenv_vars) === WORDPATCH_YES;

        // Check if Wordpatch is activated.
        $is_already_activated = wordpatch_license_is_active($wpenv_vars);

        // Check if the mailer is configured.
        $is_mail_configured = wordpatch_calculate_mail_configured($wpenv_vars) === WORDPATCH_YES;

        // Check if there are some jobs configured
        $has_some_jobs = count(wordpatch_get_jobs($wpenv_vars)) > 0;

        // We should show the quickstart if the user has missed any configuration steps.
        $show_quickstart = (!$is_db_configured || !$is_fs_configured || !$is_rescue_configured || !$is_already_activated ||
            !$is_mail_configured || !$has_some_jobs);

        // Calculate the current moderation mode.
        $current_mode = wordpatch_calculate_mode($wpenv_vars);

        // Calculate the display value of the current moderation mode.
        $display_mode = wordpatch_display_mode($wpenv_vars, $current_mode);

        // Calculate the jobs URI for our link.
        $jobs_uri = wordpatch_jobs_uri($wpenv_vars);

        // Calculate the jobs page deleted state.
        $deleted = wordpatch_jobs_deleted($wpenv_vars);

        // Calculate the log IDs pending moderation (but only if we are not in the trash)
        $judgable_job_ids = $judgable_log_ids = array();

        $judgable_log_ids = wordpatch_get_judgable_log_ids($wpenv_vars, $judgable_job_ids);

        $jobs = wordpatch_get_jobs($wpenv_vars, false);
        $running_log = wordpatch_get_running_log($wpenv_vars);
        $pending_jobs = wordpatch_get_pending_jobs($wpenv_vars);
        $last_logbox = wordpatch_get_last_logbox_jobs($wpenv_vars);

        // Calculate the progress model.
        $progress_model = wordpatch_progress_check($wpenv_vars, $jobs, $running_log, $judgable_job_ids, $judgable_log_ids,
            $pending_jobs, $last_logbox);

        $recent_jobs = wordpatch_determine_recent_jobs($wpenv_vars, $jobs, $running_log, $judgable_job_ids,
            $pending_jobs, $last_logbox);

        $wordpatch_url = wordpatch_trailingslashit(
            wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_site_url')) .
            wordpatch_unleadingslashit(wordpatch_env_get($wpenv_vars, 'wp_wordpatch_path'))
        );

        wordpatch_render_header($wpenv_vars);
        ?>
        <div class="wordpatch_page">
            <?php if (!wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars))) { ?>
                <?php wordpatch_render_access_denied($wpenv_vars); ?>
            <?php } else { ?>
                <div class="jb_container jb_dashboard_ctr">
                    <div class="jb_row">
                        <div class="jb_four jb_columns jb_full_lt_1240">
                        <?php
                            if ($show_quickstart) {
                                wordpatch_quickstart($wpenv_vars, $is_db_configured, $is_fs_configured, $is_rescue_configured,
                                    $is_already_activated, $is_mail_configured);
                            }
                        ?>
                        </div>
                        <div class="<?php echo($show_quickstart ? 'jb_eight' : 'jb_twelve jb_without_quickstart'); ?> jb_columns jb_full_lt_1240">
                            <?php if (count($recent_jobs) > 0) { ?>
                            <div class="jb_row">
                                <div class="jb_twelve jb_columns wordpatch_metabox">
                                    <div class="wordpatch_metabox_header">
                                        <i class="fa fa-bolt wordpatch_metabox_icon" aria-hidden="true"></i><?php echo(__wte($wpenv_vars, 'DASH_RECENT_JOBS')); ?>
                                    </div>
                                    <div class="wordpatch_metabox_body">
                                        <?php wordpatch_render_recent_jobs($wpenv_vars, $recent_jobs, $progress_model); ?>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                            <div class="jb_row">
                                <div class="jb_container">
                                    <div class="jb_row">
                                        <div class="jb_six jb_columns jb_full_lt_720">
                                            <div class="wordpatch_metabox">
                                                <div class="wordpatch_metabox_header">
                                                    <i class="fa fa-question wordpatch_metabox_icon" aria-hidden="true"></i><?php echo(__wte($wpenv_vars, 'DASH_SUPPORT')); ?>
                                                </div>
                                                <div class="wordpatch_metabox_body">
                                                    <p><?php echo(__wte($wpenv_vars, 'DASH_SUPPORT_DESC')); ?></p>
                                                    <div class="wordpatch_button_container">
                                                        <div class="wordpatch_metabox_flex">
                                                            <a class="wordpatch_button wordpatch_button_small wordpatch_button_blue" href="<?php echo(wordpatch_support_url()) ?>" target="_blank">
                                                                <?php echo(__wte($wpenv_vars, 'DASH_SUPPORT_BUTTON')); ?>
                                                            </a>
                                                            <a class="wordpatch_button wordpatch_button_small wordpatch_button_blue" href="<?php echo(wordpatch_documentation_url()) ?>" target="_blank">
                                                                <?php echo(__wte($wpenv_vars, 'DASH_DOCUMENTATION_BUTTON')); ?>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="wordpatch_metabox wordpatch_metabox_jointbyte">
                                                <div class="wordpatch_metabox_header">
                                                    <?php
                                                    $jointbyte_bold_part = sprintf("<strong>%s</strong>",
                                                        __wten($wpenv_vars, 'DASH_JOINTBYTE_PRIMARY'));

                                                    $jointbyte_title_html = __wt($wpenv_vars, "DASH_JOINTBYTE_TITLE", $jointbyte_bold_part);
                                                    ?>
                                                    <?php echo($jointbyte_title_html); ?>
                                                </div>
                                                <div class="wordpatch_metabox_body">
                                                    <a href="<?php echo wordpatch_jointbyte_url($wpenv_vars); ?>" class="wordpatch_jointbyte_link">
                                                        <img src="<?php echo htmlspecialchars($wordpatch_url . "assets/img/JointByteLogo.svg"); ?>" width="290" alt="" />
                                                    </a>
                                                    <div class="wordpatch_jointbyte_flex">
                                                        <a href="<?php echo wordpatch_jointbyte_url($wpenv_vars); ?>" class="wordpatch_button wordpatch_button_blue wordpatch_jointbyte_button">
                                                            <?php echo(__wte($wpenv_vars, 'DASH_JOINTBYTE_BUTTON')); ?>
                                                        </a>
                                                        <div class="wordpatch_jointbyte_divider"></div>
                                                        <a href="<?php echo wordpatch_product_url($wpenv_vars); ?>">
                                                            <img src="<?php echo htmlspecialchars($wordpatch_url . "assets/img/WordPatch_Patch.svg"); ?>" class="wordpatch_jointbyte_patch" width="36" alt="WordPatch" />
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="jb_six jb_columns jb_full_lt_720">
                                            <div class="wordpatch_metabox">
                                                <div class="wordpatch_metabox_header">
                                                    <i class="fa fa-bug wordpatch_metabox_icon" aria-hidden="true"></i><?php echo(__wte($wpenv_vars, 'DASH_REPORT_A_BUG')); ?>
                                                </div>
                                                <div class="wordpatch_metabox_body">
                                                    <p><?php echo(__wte($wpenv_vars, 'DASH_REPORT_A_BUG_DESC')); ?></p>
                                                    <div class="wordpatch_button_container">
                                                        <div class="wordpatch_metabox_flex">
                                                            <a class="wordpatch_button wordpatch_button_small wordpatch_button_purple" href="<?php echo(wordpatch_support_url()) ?>" target="_blank">
                                                                <?php echo(__wte($wpenv_vars, 'DASH_BUTTON_REPORT_A_BUG')); ?>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="wordpatch_metabox">
                                                <div class="wordpatch_metabox_header">
                                                    <i class="fa fa-share-alt wordpatch_metabox_icon" aria-hidden="true"></i><?php echo(__wte($wpenv_vars, 'DASH_FOLLOW_WORDPATCH')); ?>
                                                </div>
                                                <div class="wordpatch_metabox_body">
                                                    <div class="wordpatch_metabox_flex">
                                                        <a class="wordpatch_social_flex_image" href="<?php echo jointbyte_facebook_url($wpenv_vars); ?>">
                                                            <img src="<?php echo htmlspecialchars($wordpatch_url . "assets/img/Facebook.svg"); ?>" width="48" alt="Facebook" />
                                                        </a>
                                                        <a class="wordpatch_social_flex_image" href="<?php echo wordpatch_twitter_url($wpenv_vars); ?>">
                                                            <img src="<?php echo htmlspecialchars($wordpatch_url . "assets/img/Twitter.svg"); ?>" width="48" alt="Twitter" />
                                                        </a>
                                                        <a class="wordpatch_social_flex_image" href="<?php echo jointbyte_github_url($wpenv_vars); ?>">
                                                            <img src="<?php echo htmlspecialchars($wordpatch_url . "assets/img/GitHub.svg"); ?>" width="48" alt="GitHub" />
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="jb_row">

                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
        <?php
        wordpatch_render_footer($wpenv_vars);
    }
}