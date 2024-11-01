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
 * Implements the new patch form.
 */

// Include the database configuration wizard dependencies
include_once(dirname(__FILE__) . '/patchform/draw_fields.php');
include_once(dirname(__FILE__) . '/patchform/page_js.php');
include_once(dirname(__FILE__) . '/patchform/fs_helpers.php');
include_once(dirname(__FILE__) . '/editpatch/post_vars.php');
include_once(dirname(__FILE__) . '/editpatch/process_internal.php');
include_once(dirname(__FILE__) . '/editpatch/db_helpers.php');

if(!function_exists('wordpatch_editpatch_uri')) {
    /**
     * Construct a URI to 'editpatch'.
     *
     * @param $wpenv_vars
     * @param $patch_id
     * @return string
     */
    function wordpatch_editpatch_uri($wpenv_vars, $patch_id)
    {
        $patch_id = wordpatch_sanitize_unique_id($patch_id);

        return wordpatch_add_query_params(wordpatch_env_get($wpenv_vars, 'base_uri'), 'action=' . WORDPATCH_WHERE_EDITPATCH . '&patch=' . $patch_id);
    }
}

if(!function_exists('wordpatch_features_editpatch')) {
    /**
     * Returns features supported by 'editpatch'. See full list of options inside `inc/actions.php`.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_features_editpatch($wpenv_vars)
    {
        return array(WORDPATCH_FEATURE_REQUIRES_AUTH, WORDPATCH_FEATURE_PROCESS, WORDPATCH_FEATURE_RENDER);
    }
}

if(!function_exists('wordpatch_process_editpatch')) {
    /**
     * Handles processing logic for the new patch form.
     *
     * @param $wpenv_vars
     */
    function wordpatch_process_editpatch($wpenv_vars)
    {
        global $__wordpatch_post;

        if (!wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars))) {
            return;
        }

        $error_list = array();

        // Grab the patch ID from the request.
        $patch_id = isset($_GET['patch']) ? wordpatch_sanitize_unique_id($_GET['patch']) : '';

        // Grab the patch from our database.
        $patch = wordpatch_get_patch_by_id($wpenv_vars, $patch_id);

        // If the job
        if(!$patch) {
            $error_list[] = WORDPATCH_INVALID_PATCH;
        } else {
            // Grab the job from our database.
            $job = wordpatch_get_job_by_id($wpenv_vars, $patch['job_id']);

            // If the job
            if (!$job || $job['deleted']) {
                $error_list[] = WORDPATCH_INVALID_JOB;
            }
        }

        if(wordpatch_no_errors($error_list)) {
            // Calculate the current post variables.
            $current = wordpatch_editpatch_post_vars($wpenv_vars, $patch);

            // Try to process the new patch using the internal function.
            $error_list = wordpatch_editpatch_process_internal($wpenv_vars, $current, $patch);
        }

        // Set the error list variable for access in render.
        wordpatch_var_set('editpatch_errors', $error_list);
    }
}

if(!function_exists('wordpatch_render_editpatch')) {
    /**
     * Render the new patch form.
     *
     * @param $wpenv_vars
     */
    function wordpatch_render_editpatch($wpenv_vars)
    {
        // Grab the patch ID from the request.
        $patch_id = isset($_GET['patch']) ? wordpatch_sanitize_unique_id($_GET['patch']) : '';

        // Grab the patch from our database.
        $patch = wordpatch_get_patch_by_id($wpenv_vars, $patch_id);

        // Grab the job from our database.
        $job = $patch ? wordpatch_get_job_by_id($wpenv_vars, $patch['job_id']) : null;

        // Calculate $where and $error_vars for use with our draw field function calls.
        $where = WORDPATCH_WHERE_EDITPATCH;
        $error_vars = array('patch_id' => $patch_id, 'patch' => $patch);

        // Define which errors are related to the fields.
        $field_errors = array(WORDPATCH_PATCH_TITLE_REQUIRED, WORDPATCH_PATCH_DATA_REQUIRED, WORDPATCH_PATCH_FILE_REQUIRED,
            WORDPATCH_PATCH_PATH_REQUIRED, WORDPATCH_PATCH_CHANGES_REQUIRED);

        // Grab the new patch errors from the variable store.
        $editpatch_errors = wordpatch_var_get('editpatch_errors');
        $editpatch_errors = wordpatch_no_errors($editpatch_errors) ? array() : $editpatch_errors;

        // Calculate the max upload size.
        $max_upload_size = wordpatch_max_upload_size();

        $show_form = wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars)) &&
            $patch !== null && $job !== null && !$job['deleted'];

        $breadcrumbs = !$show_form ? array() : array(
            wordpatch_jobs_breadcrumb_for_job($wpenv_vars, $job),
            wordpatch_job_breadcrumb($wpenv_vars, $job),
            wordpatch_editjob_breadcrumb($wpenv_vars, $job),
            array(
                'text' => __wt($wpenv_vars, 'BREADCRUMB_EDIT_PATCH')
            )
        );

        wordpatch_render_header($wpenv_vars, array(), $breadcrumbs);
        ?>
        <div class="wordpatch_page">
            <?php if (!wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars))) { ?>
                <?php wordpatch_render_access_denied($wpenv_vars); ?>
            <?php } else if($patch === null || $job === null || $job['deleted']) { ?>
                <?php wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'EDIT_PATCH_NOTICE_HEADING'),
                    __wte($wpenv_vars, 'EDIT_PATCH_NOTICE')); ?>
            <?php } else { ?>
                <div class="wordpatch_page_title_container">
                    <div class="wordpatch_page_title_left">
                        <h1 class="wordpatch_page_title"><?php echo __wte($wpenv_vars, 'EDIT_PATCH_TITLE'); ?></h1>
                    </div>
                </div>
                <?php
                wordpatch_errors_maybe_draw_others($wpenv_vars, $editpatch_errors, $field_errors, $where, $error_vars);

                // Calculate the current post variables.
                $current = wordpatch_editpatch_post_vars($wpenv_vars, $patch);
                ?>
                <form id="wordpatch_edit_patch_form" class="wordpatch_patch_form"
                      action="<?php echo(wordpatch_editpatch_uri($wpenv_vars, $patch_id)); ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo($max_upload_size); ?>" />
                    <?php
                    // Draw the patch title field.
                    wordpatch_patchform_draw_patch_title_field($wpenv_vars, $current, $editpatch_errors, $where, $error_vars);

                    // Draw the patch type field.
                    wordpatch_patchform_draw_patch_type_field($wpenv_vars, $current, $editpatch_errors, $where, $error_vars);

                    // Draw the patch data field.
                    wordpatch_patchform_draw_patch_data_field($wpenv_vars, $current, $editpatch_errors, $where, $error_vars);

                    // Draw the patch file field.
                    wordpatch_patchform_draw_patch_file_field($wpenv_vars, $current, $editpatch_errors, $where, $error_vars, $patch);
                    ?>

                    <div class="wordpatch_simple_patch_ctr" style="display: none">
                        <?php
                        // Draw the patch path field.
                        wordpatch_patchform_draw_patch_path_field($wpenv_vars, $current, $editpatch_errors, $where, $error_vars);

                        // Draw the patch old file field.
                        wordpatch_patchform_draw_patch_simple_patch_field($wpenv_vars, $current, $editpatch_errors,
                            $where, $error_vars, $patch['job_id']);
                        ?>
                    </div>

                    <div class="wordpatch_metabox">
                        <div class="wordpatch_metabox_header"><?php echo(__wte($wpenv_vars,
                                'EDIT_PATCH_SUBMIT_TITLE')); ?></div>

                        <div class="wordpatch_metabox_body">
                            <input class="wordpatch_button wordpatch_button_blue" type="submit" name="submit" value="<?php echo(__wten($wpenv_vars,
                                'EDIT_PATCH_SUBMIT_TEXT')); ?>"/>
                        </div>
                    </div>
                </form>
            <?php } ?>
        </div>
        <?php wordpatch_patchform_draw_page_js($wpenv_vars, $patch_id); ?>
        <?php
        wordpatch_render_footer($wpenv_vars);
    }
}