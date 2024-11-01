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
 * Implements the filesystem configuration wizard, which is accessible through the WordPress admin panel and the rescue
 * panel. In order for WordPatch to write to the local installation's filesystem, it needs to understand the following:
 * - The method of which filesystem access is obtained (ie: direct filesystem access vs. FTP access)
 * - The credentials for accessing the filesystem
 * - The directory paths and other options for filesystem operations
 *
 * WordPatch then uses the combination of this information to connect to the filesystem and perform the appropriate
 * operations, such as creating directories or writing files and setting their chmod values. All filesystem methods are
 * implemented 1-to-1 with the WordPress implementation, see `inc/wordpress/wordpress-filesystem.php` for more info.
 */

// Include the filesystem configuration wizard dependencies
include_once(dirname(__FILE__) . '/configfs/wizard.php');
include_once(dirname(__FILE__) . '/configfs/before_render.php');
include_once(dirname(__FILE__) . '/configfs/draw_fields.php');
include_once(dirname(__FILE__) . '/configfs/guess_fields.php');
include_once(dirname(__FILE__) . '/configfs/process_steps.php');
include_once(dirname(__FILE__) . '/configfs/render_form.php');

if(!function_exists('wordpatch_configfs_uri')) {
    /**
     * Construct a URI to 'configfs'.
     *
     * @param $wpenv_vars
     * @return string
     */
    function wordpatch_configfs_uri($wpenv_vars)
    {
        return wordpatch_add_query_params(wordpatch_env_get($wpenv_vars, 'base_uri'), 'action=' . WORDPATCH_WHERE_CONFIGFS);
    }
}

if(!function_exists('wordpatch_configfs_url')) {
    /**
     * Construct a URL to 'configfs'.
     *
     * @param $wpenv_vars
     * @return string
     */
    function wordpatch_configfs_url($wpenv_vars)
    {
        return wordpatch_add_query_params(wordpatch_base_url($wpenv_vars), 'action=' . WORDPATCH_WHERE_CONFIGFS);
    }
}


if(!function_exists('wordpatch_features_configfs')) {
    /**
     * Returns features supported by 'configfs'. See full list of options inside `inc/actions.php`.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_features_configfs($wpenv_vars)
    {
        return array(WORDPATCH_FEATURE_REQUIRES_AUTH, WORDPATCH_FEATURE_PROCESS, WORDPATCH_FEATURE_RENDER);
    }
}

if(!function_exists('wordpatch_process_configfs')) {
    /**
     * Handles processing logic for the filesystem configuration wizard.
     *
     * @param $wpenv_vars
     */
    function wordpatch_process_configfs($wpenv_vars)
    {
        // Make sure the user is an admin, and if not exit early.
        if (!wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars))) {
            return;
        }

        // Calculate the current post variables.
        $current = wordpatch_configfs_post_vars($wpenv_vars, true);

        // Calculate the submit type.
        $submit_type = wordpatch_wizard_submit_type();

        // Calculate the number of steps.
        $num_steps = wordpatch_configfs_num_steps($current['fs_method']);

        // Calculate the current step number.
        $step_number = wordpatch_the_step_number($num_steps);

        // Calculate the step numbers.
        $steps = wordpatch_configfs_steps($current['fs_method']);

        // Create an array for our wizard error list.
        $wizard_error_list = array();

        // Call the correct process function depending on the step number.
        switch ($step_number) {
            case $steps['basic']:
                wordpatch_configfs_process_basic($wpenv_vars, $current, $submit_type, $step_number, $wizard_error_list);
                break;

            case $steps['validate']:
                wordpatch_configfs_process_validate($wpenv_vars, $current, $submit_type, $step_number, $wizard_error_list);
                break;

            case $steps['creds']:
                wordpatch_configfs_process_creds($wpenv_vars, $current, $submit_type, $step_number, $wizard_error_list);
                break;

            case $steps['dirs']:
                wordpatch_configfs_process_dirs($wpenv_vars, $current, $submit_type, $step_number, $wizard_error_list);
                break;
        }

        // Set the wizard_error_list variable so we can use it inside our render function.
        wordpatch_var_set('wizard_error_list', $wizard_error_list);
    }
}

if(!function_exists('wordpatch_render_configfs')) {
    /**
     * Render the filesystem configuration wizard action for WordPatch.
     *
     * @param $wpenv_vars
     */
    function wordpatch_render_configfs($wpenv_vars)
    {
        // Calculate the current post variables.
        $current = wordpatch_configfs_post_vars($wpenv_vars, false);

        // Calculate the current step number.
        $step_number = wordpatch_the_step_number(wordpatch_configfs_num_steps($current['fs_method']));

        // Calculate the wizard variables.
        $wizard_vars = wordpatch_configfs_vars($current['fs_method']);

        // Calculate the step text.
        $step_text = wordpatch_configfs_step_text($wpenv_vars, $step_number, $current['fs_method']);

        // Calculate the step numbers for each step.
        $steps = wordpatch_configfs_steps($current['fs_method']);

        // Calculate the wizard error list.
        // This is set inside `wordpatch_process_configfs` when post data has been submitted prior.
        $wizard_error_list = wordpatch_var_get('wizard_error_list');
        $wizard_error_list = wordpatch_no_errors($wizard_error_list) ? array() : $wizard_error_list;

        // Process any pre-rendering logic so we have a chance to detect any render-time errors.
        $before_errors = wordpatch_configfs_before_render($wpenv_vars, $current, $step_number, $steps);
        $wizard_error_list = wordpatch_extend_errors($wizard_error_list, $before_errors);

        $show_form = wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars));

        $breadcrumbs = !$show_form ? array() : array(
            wordpatch_settings_breadcrumb($wpenv_vars),
            array(
                'text' => __wt($wpenv_vars, 'BREADCRUMB_CONFIGFS')
            )
        );

        // Render the header.
        wordpatch_render_header($wpenv_vars, array(), $breadcrumbs);
        ?>
        <div class="wordpatch_page">
            <?php if (!wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars))) { ?>
                <?php wordpatch_render_access_denied($wpenv_vars); ?>
            <?php } else { ?>
                <?php
                wordpatch_configfs_render_form($wpenv_vars, $current, $step_number, $wizard_vars, $step_text, $steps, $wizard_error_list);
                ?>
            <?php } ?>
        </div>
        <?php
        // Render the footer.
        wordpatch_render_footer($wpenv_vars);
    }
}