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
 * Implements the delete patch form.
 */

// Include the database configuration wizard dependencies
include_once(dirname(__FILE__) . '/deletepatch/process_internal.php');
include_once(dirname(__FILE__) . '/deletepatch/db_helpers.php');
include_once(dirname(__FILE__) . '/clensepatch/fs_helpers.php');

if(!function_exists('wordpatch_deletepatch_uri')) {
    /**
     * Construct a URI to 'deletepatch'.
     *
     * @param $wpenv_vars
     * @param $patch_id
     * @return string
     */
    function wordpatch_deletepatch_uri($wpenv_vars, $patch_id)
    {
        $patch_id = wordpatch_sanitize_unique_id($patch_id);

        return wordpatch_add_query_params(wordpatch_env_get($wpenv_vars, 'base_uri'), 'action=' . WORDPATCH_WHERE_DELETEPATCH .
            '&patch=' . $patch_id);
    }
}

if(!function_exists('wordpatch_features_deletepatch')) {
    /**
     * Returns features supported by 'deletepatch'. See full list of options inside `inc/actions.php`.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_features_deletepatch($wpenv_vars)
    {
        return array(WORDPATCH_FEATURE_REQUIRES_AUTH, WORDPATCH_FEATURE_PROCESS, WORDPATCH_FEATURE_RENDER);
    }
}

if(!function_exists('wordpatch_process_deletepatch')) {
    /**
     * Handles processing logic for the delete patch form.
     *
     * @param $wpenv_vars
     */
    function wordpatch_process_deletepatch($wpenv_vars)
    {
        if (!wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars))) {
            return;
        }

        $error_list = array();

        // Grab the patch ID from the request.
        $patch_id = isset($_GET['patch']) ? wordpatch_sanitize_unique_id($_GET['patch']) : '';

        // Grab the patch from our database.
        $patch = wordpatch_get_patch_by_id($wpenv_vars, $patch_id);

        // Check if the patch is valid.
        if(!$patch) {
            $error_list[] = WORDPATCH_INVALID_PATCH;
        } else {
            // Grab the job from our database.
            $job = wordpatch_get_job_by_id($wpenv_vars, $patch['job_id']);

            // Check if the job is valid.
            if (!$job || $job['deleted']) {
                $error_list[] = WORDPATCH_INVALID_JOB;
            }
        }

        if(wordpatch_no_errors($error_list)) {
            // Try to process the delete patch using the internal function.
            $error_list = wordpatch_deletepatch_process_internal($wpenv_vars, $patch);
        }

        // Set the error list variable for access in render.
        wordpatch_var_set('deletepatch_errors', $error_list);
    }
}

if(!function_exists('wordpatch_render_deletepatch')) {
    /**
     * Render the delete patch page.
     *
     * @param $wpenv_vars
     */
    function wordpatch_render_deletepatch($wpenv_vars)
    {
        // Grab the patch ID from the request.
        $patch_id = isset($_GET['patch']) ? wordpatch_sanitize_unique_id($_GET['patch']) : '';

        // Grab the patch from our database.
        $patch = wordpatch_get_patch_by_id($wpenv_vars, $patch_id);

        // Grab the job from our database.
        $job = $patch ? wordpatch_get_job_by_id($wpenv_vars, $patch['job_id']) : null;

        // Grab the errors from the variable store.
        $deletepatch_errors = wordpatch_var_get('deletepatch_errors');
        $deletepatch_errors = wordpatch_no_errors($deletepatch_errors) ? array() : $deletepatch_errors;

        // Calculate $where and $error_vars for use with our draw field function calls.
        $where = WORDPATCH_WHERE_DELETEPATCH;
        $error_vars = array('patch' => $patch, 'patch_id' => $patch_id);

        $show_form = wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars)) &&
            $patch !== null && $job !== null && !$job['deleted'];

        $breadcrumbs = !$show_form ? array() : array(
            wordpatch_jobs_breadcrumb_for_job($wpenv_vars, $job),
            wordpatch_job_breadcrumb($wpenv_vars, $job),
            wordpatch_editjob_breadcrumb($wpenv_vars, $job),
            array(
                'text' => __wt($wpenv_vars, 'BREADCRUMB_DELETE_PATCH')
            )
        );

        wordpatch_render_header($wpenv_vars, array(), $breadcrumbs);
        ?>
        <div class="wordpatch_page">
            <?php if(!wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars))) { ?>
                <?php wordpatch_render_access_denied($wpenv_vars); ?>
            <?php } else if($patch === null || $job === null || $job['deleted']) { ?>
                <?php wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'DELETE_PATCH_NOTICE_HEADING'),
                    __wte($wpenv_vars, 'DELETE_PATCH_NOTICE')); ?>
            <?php } else { ?>
                <?php wordpatch_render_generic_modal_ex($wpenv_vars, __wte($wpenv_vars, 'DELETE_PATCH_HEADING'),
                    __wt($wpenv_vars, 'DELETE_PATCH_MESSAGE', $patch['title']), null, $deletepatch_errors, $where, $error_vars); ?>
            <?php } ?>

            <?php if ($show_form) { ?>
            <form name="wordpatch_deletepatchform" id="wordpatch_deletepatchform"
                  action="<?php echo(wordpatch_deletepatch_uri($wpenv_vars, $patch_id)); ?>" method="post">
                <div class="wordpatch_page_generic_buttons">
                    <a href="<?php echo(wordpatch_editjob_uri($wpenv_vars, $patch['job_id'])); ?>" class="wordpatch_button wordpatch_button_gray">
                        <?php echo __wten($wpenv_vars, 'DELETE_PATCH_NO'); ?></a>
                    <input type="submit" name="submit" id="submit" class="wordpatch_button wordpatch_button_red" value="<?php echo __wten($wpenv_vars, 'DELETE_PATCH_YES'); ?>"/>
                </div>
            </form>
            <?php } ?>
        </div>
        <?php
        wordpatch_render_footer($wpenv_vars);
    }
}