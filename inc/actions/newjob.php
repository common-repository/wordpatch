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
 * Implements the new job form.
 */

// Include the database configuration wizard dependencies
include_once(dirname(__FILE__) . '/jobform/draw_fields.php');
include_once(dirname(__FILE__) . '/newjob/post_vars.php');
include_once(dirname(__FILE__) . '/newjob/process_internal.php');
include_once(dirname(__FILE__) . '/newjob/db_helpers.php');

if(!function_exists('wordpatch_newjob_uri')) {
    /**
     * Construct a URI to 'newjob'.
     *
     * @param $wpenv_vars
     * @return string
     */
    function wordpatch_newjob_uri($wpenv_vars)
    {
        return wordpatch_add_query_params(wordpatch_env_get($wpenv_vars, 'base_uri'), 'action=' . WORDPATCH_WHERE_NEWJOB);
    }
}

if(!function_exists('wordpatch_features_newjob')) {
    /**
     * Returns features supported by 'newjob'. See full list of options inside `inc/actions.php`.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_features_newjob($wpenv_vars)
    {
        return array(WORDPATCH_FEATURE_REQUIRES_AUTH, WORDPATCH_FEATURE_PROCESS, WORDPATCH_FEATURE_RENDER);
    }
}

if(!function_exists('wordpatch_process_newjob')) {
    /**
     * Handles processing logic for the new job form.
     *
     * @param $wpenv_vars
     */
    function wordpatch_process_newjob($wpenv_vars)
    {
        global $__wordpatch_post;

        if (!wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars))) {
            return;
        }

        // Calculate the current post variables.
        $current = wordpatch_newjob_post_vars($wpenv_vars);

        // Try to process the job using the internal function.
        $error_list = wordpatch_newjob_process_internal($wpenv_vars, $current);

        // Set the error list variable for access in render.
        wordpatch_var_set('newjob_errors', $error_list);
    }
}

if(!function_exists('wordpatch_render_newjob')) {
    /**
     * Render the new job form.
     *
     * @param $wpenv_vars
     */
    function wordpatch_render_newjob($wpenv_vars)
    {
        // Calculate $where and $error_vars for use with our draw field function calls.
        $where = WORDPATCH_WHERE_NEWJOB;
        $error_vars = array();

        // Define which errors are related to the fields.
        $field_errors = array(WORDPATCH_JOB_TITLE_REQUIRED);

        // Calculate the current post variables.
        $current = wordpatch_newjob_post_vars($wpenv_vars);

        // Grab the new job errors from the variable store.
        $newjob_errors = wordpatch_var_get('newjob_errors');
        $newjob_errors = wordpatch_no_errors($newjob_errors) ? array() : $newjob_errors;

        $breadcrumbs = array(
            wordpatch_jobs_breadcrumb($wpenv_vars),
            array(
                'text' => __wt($wpenv_vars, 'BREADCRUMB_NEW_JOB')
            )
        );

        wordpatch_render_header($wpenv_vars, array(), $breadcrumbs);
        ?>
        <div class="wordpatch_page">
            <?php if (!wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars))) { ?>
                <?php wordpatch_render_access_denied($wpenv_vars); ?>
            <?php } else { ?>
                <div class="wordpatch_page_title_container">
                    <div class="wordpatch_page_title_left">
                        <h1 class="wordpatch_page_title"><?php echo __wte($wpenv_vars, 'NEW_JOB_TITLE'); ?></h1>
                    </div>
                </div>
                <?php
                wordpatch_errors_maybe_draw_others($wpenv_vars, $newjob_errors, $field_errors, $where, $error_vars);
                ?>
                <form action="<?php echo(wordpatch_newjob_uri($wpenv_vars)); ?>" method="POST">
                    <?php
                    // Draw the job title field.
                    wordpatch_jobform_draw_job_title_field($wpenv_vars, $current, $newjob_errors, $where, $error_vars);

                    // Draw the job path field.
                    wordpatch_jobform_draw_job_path_field($wpenv_vars, $current, $newjob_errors, $where, $error_vars);

                    // Draw the job enabled field.
                    wordpatch_jobform_draw_job_enabled_field($wpenv_vars, $current, $newjob_errors, $where, $error_vars);

                    // Draw the job moderation mode field.
                    wordpatch_jobform_draw_job_mode_field($wpenv_vars, $current, $newjob_errors, $where, $error_vars);

                    // Draw the job moderation timer field.
                    wordpatch_jobform_draw_job_timer_field($wpenv_vars, $current, $newjob_errors, $where, $error_vars);

                    // Draw the job update cooldown field.
                    wordpatch_jobform_draw_job_update_cooldown_field($wpenv_vars, $current, $newjob_errors, $where, $error_vars);

                    // Draw the job retry count field.
                    wordpatch_jobform_draw_job_retry_count_field($wpenv_vars, $current, $newjob_errors, $where, $error_vars);

                    // Draw the job maintenance mode field.
                    wordpatch_jobform_draw_job_maintenance_mode_field($wpenv_vars, $current, $newjob_errors, $where, $error_vars);

                    // Draw the job binary mode field.
                    wordpatch_jobform_draw_job_binary_mode_field($wpenv_vars, $current, $newjob_errors, $where, $error_vars);
                    ?>
                    <div class="wordpatch_metabox">
                        <div class="wordpatch_metabox_header"><?php echo(__wte($wpenv_vars,
                                'JOB_FORM_PATCHES_TITLE')); ?></div>

                        <div class="wordpatch_metabox_body">
                            <?php echo(__wte($wpenv_vars, 'NEW_JOB_PATCHES_PLACEHOLDER')); ?>
                        </div>
                    </div>

                    <div class="wordpatch_metabox">
                        <div class="wordpatch_metabox_header"><?php echo(__wte($wpenv_vars,
                                'NEW_JOB_SUBMIT_TITLE')); ?></div>

                        <div class="wordpatch_metabox_body">
                            <input type="submit" name="submit" class="wordpatch_button wordpatch_button_green" value="<?php echo(__wten($wpenv_vars,
                                'NEW_JOB_SUBMIT_TEXT')); ?>"/>
                        </div>
                    </div>
                </form>
            <?php } ?>
        </div>
        <?php
        wordpatch_render_footer($wpenv_vars);
    }
}