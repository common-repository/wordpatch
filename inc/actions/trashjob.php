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
 * Implements the trash job functionality. Jobs that are in the trash are automatically marked as disabled.
 */

// Include the database configuration wizard dependencies
include_once(dirname(__FILE__) . '/trashjob/process_internal.php');
include_once(dirname(__FILE__) . '/trashjob/db_helpers.php');

if(!function_exists('wordpatch_trashjob_uri')) {
    /**
     * Construct a URI to 'trashjob'.
     *
     * @param $wpenv_vars
     * @param $job_id
     * @return string
     */
    function wordpatch_trashjob_uri($wpenv_vars, $job_id)
    {
        return wordpatch_add_query_params(wordpatch_env_get($wpenv_vars, 'base_uri'), 'action=' . WORDPATCH_WHERE_TRASHJOB .
            '&job=' . urlencode($job_id));
    }
}

if(!function_exists('wordpatch_features_trashjob')) {
    /**
     * Returns features supported by 'trashjob'. See full list of options inside `inc/actions.php`.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_features_trashjob($wpenv_vars)
    {
        return array(WORDPATCH_FEATURE_REQUIRES_AUTH, WORDPATCH_FEATURE_PROCESS, WORDPATCH_FEATURE_RENDER);
    }
}

if(!function_exists('wordpatch_process_trashjob')) {
    /**
     * Handles processing logic for the trash job form.
     *
     * @param $wpenv_vars
     */
    function wordpatch_process_trashjob($wpenv_vars)
    {
        if (!wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars))) {
            return;
        }

        // Grab the job ID from the request.
        $job_id = isset($_GET['job']) ? wordpatch_sanitize_unique_id($_GET['job']) : '';

        // Try to process the trash job using the internal function.
        $error_list = wordpatch_trashjob_process_internal($wpenv_vars, $job_id);

        // Set the error list variable for access in render.
        wordpatch_var_set('trashjob_errors', $error_list);
    }
}

if(!function_exists('wordpatch_render_trashjob')) {
    /**
     * Render the trash job page.
     *
     * @param $wpenv_vars
     */
    function wordpatch_render_trashjob($wpenv_vars)
    {
        // Grab the job ID from the request.
        $job_id = isset($_GET['job']) ? wordpatch_sanitize_unique_id($_GET['job']) : '';

        // Grab the job from the database for our render.
        $job = wordpatch_get_job_by_id($wpenv_vars, $job_id);

        // Grab the errors from the variable store.
        $trashjob_errors = wordpatch_var_get('trashjob_errors');
        $trashjob_errors = wordpatch_no_errors($trashjob_errors) ? array() : $trashjob_errors;

        // Calculate $where and $error_vars for use with our draw field function calls.
        $where = WORDPATCH_WHERE_TRASHJOB;
        $error_vars = array('job' => $job, 'job_id' => $job_id);

        $show_form = wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars)) &&
            $job !== null && !$job['deleted'];

        $breadcrumbs = !$show_form ? array() : array(
            wordpatch_jobs_breadcrumb_for_job($wpenv_vars, $job),
            wordpatch_job_breadcrumb($wpenv_vars, $job),
            array(
                'text' => __wt($wpenv_vars, 'BREADCRUMB_TRASH_JOB')
            )
        );

        wordpatch_render_header($wpenv_vars, array(), $breadcrumbs);
        ?>
        <div class="wordpatch_page">
            <?php if (!wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars))) { ?>
                <?php wordpatch_render_access_denied($wpenv_vars); ?>
            <?php } else if($job === null || $job['deleted']) { ?>
                <?php wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'TRASH_JOB_NOTICE_HEADING'),
                    __wte($wpenv_vars, 'TRASH_JOB_NOTICE')); ?>
            <?php } else { ?>
                <?php wordpatch_render_generic_modal_ex($wpenv_vars, __wte($wpenv_vars, 'TRASH_JOB_HEADING'),
                    __wte($wpenv_vars, 'TRASH_JOB_MESSAGE', $job['title']), null, $trashjob_errors, $where, $error_vars); ?>
            <?php } ?>

            <?php if ($show_form) { ?>
            <form name="wordpatch_trashjobform" id="wordpatch_trashjobform"
                  action="<?php echo(wordpatch_trashjob_uri($wpenv_vars, $job_id)); ?>" method="post">
                <div class="wordpatch_page_generic_buttons">
                    <a href="<?php echo(wordpatch_jobs_uri($wpenv_vars)); ?>" class="wordpatch_button wordpatch_button_gray">
                        <?php echo __wten($wpenv_vars, 'TRASH_JOB_NO'); ?></a>
                    <input type="submit" name="submit" id="submit" class="wordpatch_button wordpatch_button_red" value="<?php echo __wten($wpenv_vars, 'TRASH_JOB_YES'); ?>"/>
                </div>
            </form>
            <?php } ?>
        </div>
        <?php
        wordpatch_render_footer($wpenv_vars);
    }
}