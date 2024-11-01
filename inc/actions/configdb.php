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
 * Implements the database configuration wizard, which is accessible through the WordPress admin panel.
 * In order for WordPatch to write to the local installation's database, it needs to understand the following:
 * - The credentials for accessing the database (host, name, user, and password)
 * - The configuration for accessing the database (collate, charset, and table prefix)
 *
 * WordPatch then uses the combination of this information to connect to the database and perform the appropriate
 * operations, such as creating tables or writing records. All database methods are implemented 1-to-1 with the
 * WordPress implementation, see `inc/wordpress/wordpress-database.php` for more info.
 *
 * PS: The database credentials are embedded into the rescue script, therefore they must be configured before the rescue
 * script.
 */

// Include the database configuration wizard dependencies
include_once(dirname(__FILE__) . '/configdb/wizard.php');
include_once(dirname(__FILE__) . '/configdb/before_render.php');
include_once(dirname(__FILE__) . '/configdb/draw_fields.php');
include_once(dirname(__FILE__) . '/configdb/guess_fields.php');
include_once(dirname(__FILE__) . '/configdb/process_steps.php');
include_once(dirname(__FILE__) . '/configdb/render_form.php');

if(!function_exists('wordpatch_configdb_uri')) {
    /**
     * Construct a URI to 'configdb'.
     *
     * @param $wpenv_vars
     * @return string
     */
    function wordpatch_configdb_uri($wpenv_vars)
    {
        return wordpatch_add_query_params(wordpatch_env_get($wpenv_vars, 'base_uri'), 'action=' . WORDPATCH_WHERE_CONFIGDB);
    }
}

if(!function_exists('wordpatch_configdb_url')) {
    /**
     * Construct a URL to 'configdb' which goes through wp_admin_url instead.
     *
     * @param $wpenv_vars
     * @return string
     */
    function wordpatch_configdb_url($wpenv_vars)
    {
        return wordpatch_add_query_params(wordpatch_wpadmin_wordpatch_url($wpenv_vars), 'action=' . WORDPATCH_WHERE_CONFIGDB);
    }
}

if(!function_exists('wordpatch_features_configdb')) {
    /**
     * Returns features supported by 'configdb'. See full list of options inside `inc/actions.php`.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_features_configdb($wpenv_vars)
    {
        return array(WORDPATCH_FEATURE_REQUIRES_AUTH, WORDPATCH_FEATURE_PROCESS, WORDPATCH_FEATURE_RENDER);
    }
}

if(!function_exists('wordpatch_process_configdb')) {
    /**
     * Handles processing logic for the database configuration wizard.
     *
     * @param $wpenv_vars
     */
    function wordpatch_process_configdb($wpenv_vars)
    {
        // Make sure the user is an admin, and if not exit early.
        if (!wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars))) {
            return;
        }

        // Make sure the user is not in embed (rescue) mode, and it so exit early.
        if(!wordpatch_env_get($wpenv_vars, 'embed_mode')) {
            return;
        }

        // Calculate the current post variables.
        $current = wordpatch_configdb_post_vars($wpenv_vars, true);

        // Calculate the submit type.
        $submit_type = wordpatch_wizard_submit_type();

        // Calculate the number of steps.
        $num_steps = wordpatch_configdb_num_steps();

        // Calculate the current step number.
        $step_number = wordpatch_the_step_number($num_steps);

        // Calculate the step numbers.
        $steps = wordpatch_configdb_steps();

        // Create an array for our wizard error list.
        $wizard_error_list = array();

        // Call the correct process function depending on the step number.
        switch ($step_number) {
            case $steps['validate']:
                wordpatch_configdb_process_validate($wpenv_vars, $current, $submit_type, $step_number, $wizard_error_list);
                break;

            case $steps['creds']:
                wordpatch_configdb_process_creds($wpenv_vars, $current, $submit_type, $step_number, $wizard_error_list);
                break;
        }

        // Set the wizard_error_list variable so we can use it inside our render function.
        wordpatch_var_set('wizard_error_list', $wizard_error_list);
    }
}

if(!function_exists('wordpatch_render_configdb')) {
    /**
     * Render the database configuration wizard action for WordPatch.
     *
     * @param $wpenv_vars
     */
    function wordpatch_render_configdb($wpenv_vars)
    {
        // Calculate the current post variables.
        $current = wordpatch_configdb_post_vars($wpenv_vars, false);

        // Calculate the current step number.
        $step_number = wordpatch_the_step_number(wordpatch_configdb_num_steps());

        // Calculate the wizard variables.
        $wizard_vars = wordpatch_configdb_vars();

        // Calculate the step text.
        $step_text = wordpatch_configdb_step_text($wpenv_vars, $step_number);

        // Calculate the step numbers for each step.
        $steps = wordpatch_configdb_steps();

        // Calculate the wizard error list.
        // This is set inside `wordpatch_process_configdb` when post data has been submitted prior.
        $wizard_error_list = wordpatch_var_get('wizard_error_list');
        $wizard_error_list = wordpatch_no_errors($wizard_error_list) ? array() : $wizard_error_list;

        // Process any pre-rendering logic so we have a chance to detect any render-time errors.
        $before_errors = wordpatch_configdb_before_render($wpenv_vars, $current, $step_number, $steps);
        $wizard_error_list = wordpatch_extend_errors($wizard_error_list, $before_errors);

        // Calculate embed mode.
        $embed_mode = wordpatch_env_get($wpenv_vars, 'embed_mode');

        $show_form = wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars)) &&
            $embed_mode;

        $breadcrumbs = !$show_form ? array() : array(
            wordpatch_settings_breadcrumb($wpenv_vars),
            array(
                'text' => __wt($wpenv_vars, 'BREADCRUMB_CONFIGDB')
            )
        );

        // Render the header.
        wordpatch_render_header($wpenv_vars, array(), $breadcrumbs);
        ?>
        <div class="wordpatch_page">
            <?php if (!wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars))) { ?>
                <?php wordpatch_render_access_denied($wpenv_vars); ?>
            <?php } else if(!$embed_mode) { ?>
                <?php wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'CONFIGDB_EMBED_NOTICE_HEADING'),
                    __wte($wpenv_vars, 'CONFIGDB_EMBED_NOTICE')); ?>
            <?php } else { ?>
                <?php
                wordpatch_configdb_render_form($wpenv_vars, $current, $step_number, $wizard_vars, $step_text, $steps, $wizard_error_list);
                ?>
            <?php } ?>
        </div>
        <?php
        // Render the footer.
        wordpatch_render_footer($wpenv_vars);
    }
}