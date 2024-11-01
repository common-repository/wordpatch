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
 * Implements the rescue configuration wizard, which is accessible through the WordPress admin panel.
 * In order for WordPatch to create a rescue script, it needs to understand the following:
 * - Filesystem configuration (see filesystem configuration wizard for more information)
 * - Database configuration (see filesystem configuration wizard for more information)
 *
 * WordPatch then uses the combination of this information to connect to the filesystem and create the appropriate
 * rescue script. For more information on the filesystem, see the filesystem configuration wizard.
 */

// Include the rescue configuration wizard dependencies
include_once(dirname(__FILE__) . '/configrescue/wizard.php');
include_once(dirname(__FILE__) . '/configrescue/before_render.php');
include_once(dirname(__FILE__) . '/configrescue/draw_fields.php');
include_once(dirname(__FILE__) . '/configrescue/guess_fields.php');
include_once(dirname(__FILE__) . '/configrescue/process_steps.php');
include_once(dirname(__FILE__) . '/configrescue/render_form.php');

if(!function_exists('wordpatch_configrescue_uri')) {
    /**
     * Construct a URI to 'configrescue'.
     *
     * @param $wpenv_vars
     * @return string
     */
    function wordpatch_configrescue_uri($wpenv_vars)
    {
        return wordpatch_add_query_params(wordpatch_env_get($wpenv_vars, 'base_uri'), 'action=' . WORDPATCH_WHERE_CONFIGRESCUE);
    }
}

if(!function_exists('wordpatch_configrescue_url')) {
    /**
     * Construct a URL to 'configrescue' which goes through wp_admin_url instead.
     *
     * @param $wpenv_vars
     * @return string
     */
    function wordpatch_configrescue_url($wpenv_vars)
    {
        return wordpatch_add_query_params(wordpatch_wpadmin_wordpatch_url($wpenv_vars), 'action=' . WORDPATCH_WHERE_CONFIGRESCUE);
    }
}

if(!function_exists('wordpatch_features_configrescue')) {
    /**
     * Returns features supported by 'configrescue'. See full list of options inside `inc/actions.php`.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_features_configrescue($wpenv_vars)
    {
        return array(WORDPATCH_FEATURE_REQUIRES_AUTH, WORDPATCH_FEATURE_PROCESS, WORDPATCH_FEATURE_RENDER);
    }
}

if(!function_exists('wordpatch_process_configrescue')) {
    /**
     * Handles processing logic for the rescue configuration wizard.
     *
     * @param $wpenv_vars
     */
    function wordpatch_process_configrescue($wpenv_vars)
    {
        // Make sure the user is an admin, and if not exit early.
        if (!wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars))) {
            return;
        }

        // Calculate the current post variables.
        $current = wordpatch_configrescue_post_vars($wpenv_vars, true);

        // Calculate the submit type.
        $submit_type = wordpatch_wizard_submit_type();

        // Calculate the number of steps.
        $num_steps = wordpatch_configrescue_num_steps();

        // Calculate the current step number.
        $step_number = wordpatch_the_step_number($num_steps);

        // Calculate the step numbers.
        $steps = wordpatch_configrescue_steps();

        // Create an array for our wizard error list and calculate any potential pre-configuration errors.
        $wizard_error_list = wordpatch_configrescue_check_preconfiguration($wpenv_vars);

        // Only run the rest of the processing if we do not have any errors in our error list yet.
        if(wordpatch_no_errors($wizard_error_list)) {
            // Call the correct process function depending on the step number.
            switch ($step_number) {
                case $steps['dirs']:
                    wordpatch_configrescue_process_dirs($wpenv_vars, $current, $submit_type, $step_number, $wizard_error_list);
                    break;

                case $steps['validate']:
                    wordpatch_configrescue_process_validate($wpenv_vars, $current, $submit_type, $step_number, $wizard_error_list);
                    break;
            }
        }

        // Set the wizard_error_list variable so we can use it inside our render function.
        wordpatch_var_set('wizard_error_list', $wizard_error_list);
    }
}

if(!function_exists('wordpatch_render_configrescue')) {
    /**
     * Render the rescue configuration wizard action for WordPatch.
     *
     * @param $wpenv_vars
     */
    function wordpatch_render_configrescue($wpenv_vars)
    {
        // Calculate the current post variables.
        $current = wordpatch_configrescue_post_vars($wpenv_vars, false);

        // Calculate the current step number.
        $step_number = wordpatch_the_step_number(wordpatch_configrescue_num_steps());

        // Calculate the wizard variables.
        $wizard_vars = wordpatch_configrescue_vars();

        // Calculate the step text.
        $step_text = wordpatch_configrescue_step_text($wpenv_vars, $step_number);

        // Calculate the step numbers for each step.
        $steps = wordpatch_configrescue_steps();

        // Calculate the wizard error list.
        // This is set inside `wordpatch_process_configrescue` when post data has been submitted prior.
        $wizard_error_list = wordpatch_var_get('wizard_error_list');
        $wizard_error_list = wordpatch_no_errors($wizard_error_list) ? array() : $wizard_error_list;

        // Process any pre-rendering logic so we have a chance to detect any render-time errors.
        $before_errors = wordpatch_configrescue_before_render($wpenv_vars, $current, $step_number, $steps);
        $wizard_error_list = wordpatch_extend_errors($wizard_error_list, $before_errors);

        // Calculate embed mode.
        $embed_mode = wordpatch_env_get($wpenv_vars, 'embed_mode');

        $show_form = wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars)) &&
            $embed_mode;

        $breadcrumbs = !$show_form ? array() : array(
            wordpatch_settings_breadcrumb($wpenv_vars),
            array(
                'text' => __wt($wpenv_vars, 'BREADCRUMB_CONFIGRESCUE')
            )
        );

        // Render the header.
        wordpatch_render_header($wpenv_vars, array(), $breadcrumbs);
        ?>
        <div class="wordpatch_page">
            <?php if (!wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars))) { ?>
                <?php wordpatch_render_access_denied($wpenv_vars); ?>
            <?php } else if(!$embed_mode) { ?>
                <?php wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'CONFIGRESCUE_EMBED_NOTICE_HEADING'),
                    __wte($wpenv_vars, 'CONFIGRESCUE_EMBED_NOTICE')); ?>
            <?php } else { ?>
                <?php
                wordpatch_configrescue_render_form($wpenv_vars, $current, $step_number, $wizard_vars, $step_text, $steps, $wizard_error_list);
                ?>
            <?php } ?>
        </div>
        <?php
        // Render the footer.
        wordpatch_render_footer($wpenv_vars);
    }
}

if(!function_exists('wordpatch_configrescue_check_preconfiguration')) {
    /**
     * Performs pre-configuration checks for the rescue configuration wizard.
     * Returns an array which will contain errors if there are issues with the pre-configuration state.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_configrescue_check_preconfiguration($wpenv_vars) {
        // Create an array for our result.
        $preconfig_errors = array();

        // Check if the filesystem is configured, and if not append an error to our result array.
        $is_fs_configured = wordpatch_calculate_fs_configured($wpenv_vars) === WORDPATCH_YES;

        // If the filesystem has not yet been configured, append an error to our result array.
        if(!$is_fs_configured) {
            $preconfig_errors[] = WORDPATCH_MUST_CONFIGURE_FILESYSTEM;
        }

        // Check if the database is configured, and if not append an error to our result array.
        $is_db_configured = wordpatch_calculate_db_configured($wpenv_vars) === WORDPATCH_YES;

        if(!$is_db_configured) {
            $preconfig_errors[] = WORDPATCH_MUST_CONFIGURE_DATABASE;
        }

        // Return our result array.
        return $preconfig_errors;
    }
}