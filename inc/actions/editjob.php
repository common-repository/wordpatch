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
 * Implements the edit job form.
 */

// Include the database configuration wizard dependencies
include_once(dirname(__FILE__) . '/jobform/draw_fields.php');
include_once(dirname(__FILE__) . '/editjob/post_vars.php');
include_once(dirname(__FILE__) . '/editjob/process_internal.php');
include_once(dirname(__FILE__) . '/editjob/db_helpers.php');
include_once(dirname(__FILE__) . '/editjob/page_js.php');

if(!function_exists('wordpatch_editjob_uri')) {
    /**
     * Construct a URI to 'editjob'.
     *
     * @param $wpenv_vars
     * @param $job_id
     * @param null|bool $newjob_success
     * @param null|bool $editjob_success
     * @param null|string $newpatch_success
     * @param null|string $editpatch_success
     * @param null|bool $deletepatch_success
     * @return string
     */
    function wordpatch_editjob_uri($wpenv_vars, $job_id, $newjob_success = null, $editjob_success = null, $newpatch_success = null,
                                   $editpatch_success = null, $deletepatch_success = null)
    {
        return wordpatch_add_query_params(wordpatch_env_get($wpenv_vars, 'base_uri'), 'action=' . WORDPATCH_WHERE_EDITJOB . '&job=' . urlencode($job_id) .
                (($newjob_success !== null && $newjob_success) ? '&newjob_success=1' : '') .
                (($editjob_success !== null && $editjob_success) ? '&editjob_success=1' : '')) .
            (($newpatch_success !== null && $newpatch_success) ? ('&newpatch_success=' . urlencode($newpatch_success)) : '') .
            (($editpatch_success !== null && $editpatch_success) ? ('&editpatch_success=' . urlencode($editpatch_success)) : '') .
            (($deletepatch_success !== null && $deletepatch_success) ? '&deletepatch_success=1' : '');
    }
}

if(!function_exists('wordpatch_features_editjob')) {
    /**
     * Returns features supported by 'editjob'. See full list of options inside `inc/actions.php`.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_features_editjob($wpenv_vars)
    {
        return array(WORDPATCH_FEATURE_REQUIRES_AUTH, WORDPATCH_FEATURE_PROCESS, WORDPATCH_FEATURE_RENDER);
    }
}

if(!function_exists('wordpatch_process_editjob')) {
    /**
     * Handles processing logic for the edit job form.
     *
     * @param $wpenv_vars
     */
    function wordpatch_process_editjob($wpenv_vars)
    {
        global $__wordpatch_post;

        if (!wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars))) {
            return;
        }

        // Grab the job ID from the request.
        $job_id = isset($_GET['job']) ? wordpatch_sanitize_unique_id($_GET['job']) : '';

        // Construct an array for errors.
        $error_list = array();

        // Grab the job from our database.
        $job = wordpatch_get_job_by_id($wpenv_vars, $job_id);

        // If the job
        if(!$job) {
            $error_list[] = WORDPATCH_INVALID_JOB;
        }

        // Grab the patches from our database.
        $patches = wordpatch_get_patches_by_job_id($wpenv_vars, $job_id);

        if(wordpatch_no_errors($error_list)) {
            // Calculate the current post variables.
            $current = wordpatch_editjob_post_vars($wpenv_vars, $job, $patches);

            // Try to process the job using the internal function.
            $error_list = wordpatch_editjob_process_internal($wpenv_vars, $current, $job_id);
        }

        // Set the error list variable for access in render.
        wordpatch_var_set('editjob_errors', $error_list);
    }
}

if(!function_exists('wordpatch_render_editjob')) {
    /**
     * Render the edit job form.
     *
     * @param $wpenv_vars
     */
    function wordpatch_render_editjob($wpenv_vars)
    {
        // Calculate $where and $error_vars for use with our draw field function calls.
        $where = WORDPATCH_WHERE_EDITJOB;
        $error_vars = array();

        // Define which errors are related to the fields.
        $field_errors = array(WORDPATCH_JOB_TITLE_REQUIRED);

        // Grab the job ID from the request.
        $job_id = isset($_GET['job']) ? wordpatch_sanitize_unique_id($_GET['job']) : '';

        // Grab the job from our database.
        $job = wordpatch_get_job_by_id($wpenv_vars, $job_id);

        // Grab the patches from our database.
        $patches = wordpatch_get_patches_by_job_id($wpenv_vars, $job_id);

        // Grab the edit job errors from the variable store.
        $editjob_errors = wordpatch_var_get('editjob_errors');
        $editjob_errors = wordpatch_no_errors($editjob_errors) ? array() : $editjob_errors;

        $newjob_success = (isset($_GET['newjob_success']) && trim($_GET['newjob_success']) === '1');
        $editjob_success = (isset($_GET['editjob_success']) && trim($_GET['editjob_success']) === '1');
        $newpatch_success = (isset($_GET['newpatch_success']) && trim($_GET['newpatch_success']) !== '') ?
            wordpatch_sanitize_unique_id($_GET['newpatch_success']) : false;
        $editpatch_success = (isset($_GET['editpatch_success']) && trim($_GET['editpatch_success']) !== '') ?
            wordpatch_sanitize_unique_id($_GET['editpatch_success']) : false;
        $deletepatch_success = (isset($_GET['deletepatch_success']) && trim($_GET['deletepatch_success']) === '1');

        $show_form = wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars)) &&
            $job !== null && !$job['deleted'];

        $breadcrumbs = !$show_form ? array() : array(
            wordpatch_jobs_breadcrumb_for_job($wpenv_vars, $job),
            wordpatch_job_breadcrumb($wpenv_vars, $job),
            array(
                'text' => __wt($wpenv_vars, 'BREADCRUMB_EDIT_JOB')
            )
        );

        wordpatch_render_header($wpenv_vars, array(), $breadcrumbs);
        ?>
        <div class="wordpatch_page">
            <?php if (!wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars))) { ?>
                <?php wordpatch_render_access_denied($wpenv_vars); ?>
            <?php } else if(!$job || $job['deleted']) { ?>
                <?php wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'EDIT_JOB_NOTICE_HEADING'),
                    __wte($wpenv_vars, 'EDIT_JOB_NOTICE')); ?>
            <?php } else { ?>
                <?php if ($newjob_success) { ?>
                    <?php
                    $link1 = sprintf("<a href=\"%s\">%s</a>",
                        wordpatch_editjob_uri($wpenv_vars, $job_id), __wten($wpenv_vars, 'EDIT_JOB_SUCCESS_NEW_LINK'));

                    $link2 = sprintf("<a href=\"%s\">%s</a>",
                        wordpatch_jobs_uri($wpenv_vars), __wten($wpenv_vars, 'EDIT_JOB_SUCCESS_NEW_LINK2'));
                    ?>
                    <?php wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'EDIT_JOB_SUCCESS_NEW_HEADING'),
                        __wt($wpenv_vars, 'EDIT_JOB_SUCCESS_NEW', $link1, $link2)); ?>
                <?php } else if ($editjob_success) { ?>
                    <?php
                    $link1 = sprintf("<a href=\"%s\">%s</a>",
                        wordpatch_editjob_uri($wpenv_vars, $job_id), __wten($wpenv_vars, 'EDIT_JOB_SUCCESS_EDIT_LINK'));

                    $link2 = sprintf("<a href=\"%s\">%s</a>",
                        wordpatch_jobs_uri($wpenv_vars), __wten($wpenv_vars, 'EDIT_JOB_SUCCESS_EDIT_LINK2'));
                    ?>
                    <?php wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'EDIT_JOB_SUCCESS_EDIT_HEADING'),
                        __wt($wpenv_vars, 'EDIT_JOB_SUCCESS_EDIT', $link1, $link2)); ?>
                <?php } else if ($newpatch_success) { ?>
                    <?php
                    $link1 = sprintf("<a href=\"%s\">%s</a>",
                        wordpatch_editjob_uri($wpenv_vars, $job_id), __wten($wpenv_vars, 'EDIT_JOB_SUCCESS_NEW_PATCH_LINK'));

                    $link2 = sprintf("<a href=\"%s\">%s</a>",
                        wordpatch_editpatch_uri($wpenv_vars, $newpatch_success), __wten($wpenv_vars, 'EDIT_JOB_SUCCESS_NEW_PATCH_LINK2'));
                    ?>
                    <?php wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'EDIT_JOB_SUCCESS_NEW_PATCH_HEADING'),
                        __wt($wpenv_vars, 'EDIT_JOB_SUCCESS_NEW_PATCH', $link1, $link2)); ?>
                <?php } else if ($editpatch_success) { ?>
                    <?php
                    $link1 = sprintf("<a href=\"%s\">%s</a>",
                        wordpatch_editjob_uri($wpenv_vars, $job_id), __wten($wpenv_vars, 'EDIT_JOB_SUCCESS_EDIT_PATCH_LINK'));

                    $link2 = sprintf("<a href=\"%s\">%s</a>",
                        wordpatch_editpatch_uri($wpenv_vars, $editpatch_success), __wten($wpenv_vars, 'EDIT_JOB_SUCCESS_EDIT_PATCH_LINK2'));
                    ?>
                    <?php wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'EDIT_JOB_SUCCESS_EDIT_PATCH_HEADING'),
                        __wt($wpenv_vars, 'EDIT_JOB_SUCCESS_EDIT_PATCH', $link1, $link2)); ?>
                <?php } else if ($deletepatch_success) { ?>
                    <?php
                    $link = sprintf("<a href=\"%s\">%s</a>",
                        wordpatch_editjob_uri($wpenv_vars, $job_id), __wten($wpenv_vars, 'EDIT_JOB_SUCCESS_DELETE_PATCH_LINK'));
                    ?>
                    <?php wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'EDIT_JOB_SUCCESS_DELETE_PATCH_HEADING'),
                        __wt($wpenv_vars, 'EDIT_JOB_SUCCESS_DELETE_PATCH', $link)); ?>
                <?php } else { ?>

                    <div class="wordpatch_page_title_container">
                        <div class="wordpatch_page_title_left">
                            <h1 class="wordpatch_page_title"><?php echo __wte($wpenv_vars, 'EDIT_JOB_TITLE'); ?></h1>
                        </div>
                    </div>
                    <?php
                    wordpatch_errors_maybe_draw_others($wpenv_vars, $editjob_errors, $field_errors, $where, $error_vars);

                    // Calculate the current post variables.
                    $current = wordpatch_editjob_post_vars($wpenv_vars, $job, $patches);

                    // Order our patches for our loop below.
                    $patches_ordered = wordpatch_get_ordered_rows($patches, explode(',', $current['job_patches']));
                    ?>
                    <form id="wordpatch_edit_job_form" action="<?php echo(wordpatch_editjob_uri($wpenv_vars, $job_id)); ?>" method="POST">
                        <input type="hidden" name="job_patches"
                               value="<?php echo(htmlspecialchars($current['job_patches'])); ?>"/>
                        <?php
                        // Draw the job title field.
                        wordpatch_jobform_draw_job_title_field($wpenv_vars, $current, $editjob_errors, $where, $error_vars);

                        // Draw the job path field.
                        wordpatch_jobform_draw_job_path_field($wpenv_vars, $current, $editjob_errors, $where, $error_vars);

                        // Draw the job enabled field.
                        wordpatch_jobform_draw_job_enabled_field($wpenv_vars, $current, $editjob_errors, $where, $error_vars);

                        // Draw the job moderation mode field.
                        wordpatch_jobform_draw_job_mode_field($wpenv_vars, $current, $editjob_errors, $where, $error_vars);

                        // Draw the job moderation timer field.
                        wordpatch_jobform_draw_job_timer_field($wpenv_vars, $current, $editjob_errors, $where, $error_vars);

                        // Draw the job update cooldown field.
                        wordpatch_jobform_draw_job_update_cooldown_field($wpenv_vars, $current, $editjob_errors, $where, $error_vars);

                        // Draw the job retry count field.
                        wordpatch_jobform_draw_job_retry_count_field($wpenv_vars, $current, $editjob_errors, $where, $error_vars);

                        // Draw the job maintenance mode field.
                        wordpatch_jobform_draw_job_maintenance_mode_field($wpenv_vars, $current, $editjob_errors, $where, $error_vars);

                        // Draw the job binary mode field.
                        wordpatch_jobform_draw_job_binary_mode_field($wpenv_vars, $current, $editjob_errors, $where, $error_vars);

                        ?>
                        <div class="wordpatch_metabox wordpatch_metabox_patches">
                            <div class="wordpatch_metabox_header2">
                                <div class="wordpatch_metabox_header_left">
                                    <?php echo(__wte($wpenv_vars,
                                        'JOB_FORM_PATCHES_TITLE')); ?>
                                </div>
                                <div class="wordpatch_metabox_header_right">
                                    <a href="<?php echo wordpatch_newpatch_uri($wpenv_vars, $job_id) ?>"
                                       class="wordpatch_button wordpatch_button_green wordpatch_newpatch"><i
                                                class="fa fa-plus"></i>&nbsp;<?php echo(__wten($wpenv_vars,
                                            'EDIT_JOB_PATCHES_NEW')); ?></a>
                                </div>
                            </div>
                            <div class="wordpatch_metabox_body">
                                <div class="wordpatch_patches_container">
                                    <?php if (empty($patches)) { ?>
                                        <p class="wordpatch_patches_empty"><?php echo(__wte($wpenv_vars,
                                                'EDIT_JOB_PATCHES_EMPTY')); ?></p>
                                    <?php } else { ?>
                                        <div class="wordpatch_patches">
                                            <?php foreach ($patches_ordered as $patch_index => $patch_single) { ?>
                                                <div class="wordpatch_patch_ctr" data-patch-id="<?php echo($patch_single['id']); ?>"
                                                     data-patch-order="<?php echo($patch_index + 1); ?>">
                                                    <div class="wordpatch_patch_ctr_flex">
                                                        <div class="wordpatch_patch_ctr_left">
                                                            <div class="wordpatch_patch_meta">
                                                                <a href="<?php echo(wordpatch_editpatch_uri($wpenv_vars, $patch_single['id'])); ?>"
                                                                   class="wordpatch_patch_title"><?php echo(htmlspecialchars($patch_single['title'])) ?></a>
                                                            </div>
                                                        </div>
                                                        <div class="wordpatch_patch_ctr_right">
                                                            <div class="wordpatch_text_right">
                                                                <a class="wordpatch_button wordpatch_job_button"
                                                                   href="<?php echo(wordpatch_editpatch_uri($wpenv_vars, $patch_single['id'])); ?>">
                                                                    <i class="fa fa-pencil"></i>
                                                                    <span><?php echo(__wten($wpenv_vars,
                                                                            'EDIT_JOB_PATCHES_EDIT_LINK')); ?></span>
                                                                </a>
                                                                <a class="wordpatch_button wordpatch_job_button"
                                                                   href="<?php echo(wordpatch_deletepatch_uri($wpenv_vars, $patch_single['id'])); ?>">
                                                                    <i class="fa fa-trash"></i>
                                                                    <span><?php echo(__wten($wpenv_vars,
                                                                            'EDIT_JOB_PATCHES_DELETE_LINK')); ?></span>
                                                                </a>
                                                            </div>
                                                            <div class="wordpatch_patch_reorder">
                                                                <a class="grip"><i class="fa fa-bars" aria-hidden="true"></i></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="wordpatch_patch_ctr_righter">
                                                        <div class="wordpatch_patch_reorder_righter">
                                                            <a class="grip"><i class="fa fa-bars"></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>

                        <div class="wordpatch_metabox">
                            <div class="wordpatch_metabox_header"><?php echo(__wte($wpenv_vars,
                                    'EDIT_JOB_SUBMIT_TITLE')); ?></div>

                            <div class="wordpatch_metabox_body">
                                <input type="submit" name="submit" class="wordpatch_button wordpatch_button_blue" value="<?php echo(__wten($wpenv_vars,
                                    'EDIT_JOB_SUBMIT_TEXT')); ?>"/>
                            </div>
                        </div>
                    </form>
                <?php } ?>
            <?php } ?>
        </div>
        <?php wordpatch_editjob_draw_page_js($wpenv_vars); ?>
        <?php
        wordpatch_render_footer($wpenv_vars);
    }
}