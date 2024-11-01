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
 * Implements the mail configuration wizard, which is accessible through the WordPress admin panel and the rescue
 * script.
 * In order for WordPatch to be activated, you must first configure:
 * - Filesystem configuration (see filesystem configuration wizard for more information)
 * - Database configuration (see database configuration wizard for more information)
 * - Rescue configuration (see rescue configuration wizard for more information)
 * - License configuration (see licnese configuration wizard for more information)
 *
 * WordPatch then uses the combination of this information to connect to the API server and call the activation endpoint.
 */

// Include the mail configuration wizard dependencies
include_once(dirname(__FILE__) . '/configmail/wizard.php');
include_once(dirname(__FILE__) . '/configmail/guess_fields.php');
include_once(dirname(__FILE__) . '/configmail/before_render.php');
include_once(dirname(__FILE__) . '/configmail/draw_fields.php');
include_once(dirname(__FILE__) . '/configmail/process_steps.php');
include_once(dirname(__FILE__) . '/configmail/render_form.php');

if(!function_exists('wordpatch_configmail_uri')) {
    /**
     * Construct a URI to 'configmail'.
     *
     * @param $wpenv_vars
     * @return string
     */
    function wordpatch_configmail_uri($wpenv_vars)
    {
        return wordpatch_add_query_params(wordpatch_env_get($wpenv_vars, 'base_uri'), 'action=' . WORDPATCH_WHERE_CONFIGMAIL);
    }
}

if(!function_exists('wordpatch_configmail_url')) {
    /**
     * Construct a URL to 'configmail'.
     *
     * @param $wpenv_vars
     * @return string
     */
    function wordpatch_configmail_url($wpenv_vars)
    {
        return wordpatch_add_query_params(wordpatch_base_url($wpenv_vars), 'action=' . WORDPATCH_WHERE_CONFIGMAIL);
    }
}

if(!function_exists('wordpatch_features_configmail')) {
    /**
     * Returns features supported by 'configmail'. See full list of options inside `inc/actions.php`.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_features_configmail($wpenv_vars)
    {
        return array(WORDPATCH_FEATURE_REQUIRES_AUTH, WORDPATCH_FEATURE_PROCESS, WORDPATCH_FEATURE_RENDER);
    }
}

if(!function_exists('wordpatch_process_configmail')) {
    /**
     * Handles processing logic for the mail configuration wizard.
     *
     * @param $wpenv_vars
     */
    function wordpatch_process_configmail($wpenv_vars)
    {
        // Make sure the user is an admin, and if not exit early.
        if (!wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars))) {
            return;
        }

        // Calculate the current post variables.
        $current = wordpatch_configmail_post_vars($wpenv_vars, true);

        // Calculate the submit type.
        $submit_type = wordpatch_wizard_submit_type();

        // Calculate the number of steps.
        $num_steps = wordpatch_configmail_num_steps($current['mailer'], $current['smtp_auth']);

        // Calculate the current step number.
        $step_number = wordpatch_the_step_number($num_steps);

        // Calculate the step numbers.
        $steps = wordpatch_configmail_steps($current['mailer'], $current['smtp_auth']);

        // Create an array for our wizard error list and calculate any potential pre-configuration errors.
        $wizard_error_list = wordpatch_configmail_check_preconfiguration($wpenv_vars);

        // Only run the rest of the processing if we do not have any errors in our error list yet.
        if(wordpatch_no_errors($wizard_error_list)) {
            // Call the correct process function depending on the step number.
            switch ($step_number) {
                case $steps['basic']:
                    wordpatch_configmail_process_basic($wpenv_vars, $current, $submit_type, $step_number, $wizard_error_list);
                    break;

                case $steps['smtp']:
                    wordpatch_configmail_process_smtp($wpenv_vars, $current, $submit_type, $step_number, $wizard_error_list);
                    break;

                case $steps['creds']:
                    wordpatch_configmail_process_creds($wpenv_vars, $current, $submit_type, $step_number, $wizard_error_list);
                    break;
            }
        }

        // Set the wizard_error_list variable so we can use it inside our render function.
        wordpatch_var_set('wizard_error_list', $wizard_error_list);
    }
}

if(!function_exists('wordpatch_render_configmail')) {
    /**
     * Render the mail configuration wizard action for WordPatch.
     *
     * @param $wpenv_vars
     */
    function wordpatch_render_configmail($wpenv_vars)
    {
        // Calculate the current post variables.
        $current = wordpatch_configmail_post_vars($wpenv_vars, false);

        // Calculate the step numbers for each step.
        $steps = wordpatch_configmail_steps($current['mailer'], $current['smtp_auth']);

        // Calculate the current step number.
        $step_number = wordpatch_the_step_number(wordpatch_configmail_num_steps($current['mailer'], $current['smtp_auth']));

        // Calculate the wizard variables.
        $wizard_vars = wordpatch_configmail_vars($current['mailer'], $current['smtp_auth']);

        // Calculate the step text.
        $step_text = wordpatch_configmail_step_text($wpenv_vars, $step_number, $current['mailer'], $current['smtp_auth']);

        // Calculate the wizard error list.
        // This is set inside `wordpatch_process_configmail` when post data has been submitted prior.
        $wizard_error_list = wordpatch_var_get('wizard_error_list');
        $wizard_error_list = wordpatch_no_errors($wizard_error_list) ? array() : $wizard_error_list;

        // Process any pre-rendering logic so we have a chance to detect any render-time errors.
        $before_errors = wordpatch_configmail_before_render($wpenv_vars, $current, $step_number, $steps);
        $wizard_error_list = wordpatch_extend_errors($wizard_error_list, $before_errors);

        $show_form = wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars));

        $breadcrumbs = !$show_form ? array() : array(
            wordpatch_settings_breadcrumb($wpenv_vars),
            array(
                'text' => __wt($wpenv_vars, 'BREADCRUMB_CONFIGMAIL')
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
                wordpatch_configmail_render_form($wpenv_vars, $current, $step_number, $wizard_vars, $step_text,
                    $steps, $wizard_error_list);
                ?>
            <?php } ?>
        </div>
        <?php
        // Render the footer.
        wordpatch_render_footer($wpenv_vars);
    }
}

if(!function_exists('wordpatch_configmail_check_preconfiguration')) {
    /**
     * Performs pre-configuration checks for the mail configuration wizard.
     * Returns an array which will contain errors if there are issues with the pre-configuration state.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_configmail_check_preconfiguration($wpenv_vars) {
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

        // Check if the rescue script is configured, and if not append an error to our result array.
        $is_rescue_configured = wordpatch_calculate_rescue_configured($wpenv_vars) === WORDPATCH_YES;

        if(!$is_rescue_configured) {
            $preconfig_errors[] = WORDPATCH_MUST_CONFIGURE_RESCUE;
        }

        // Check if the license is activated, and if not append an error to our result array.
        if(!wordpatch_license_is_active($wpenv_vars)) {
            $preconfig_errors[] = WORDPATCH_MUST_ACTIVATE_LICENSE;
        }

        // Return our result array.
        return $preconfig_errors;
    }
}