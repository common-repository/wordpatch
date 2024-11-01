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
 * The following functions define the majority of the mail configuration shared functionality related to
 * configuration wizards.
 */

if(!function_exists('wordpatch_configmail_num_steps')) {
    /**
     * Returns the number of steps in the mail configuration wizard.
     * This should return 2 for most mailers and 4 for the SMTP mailer.
     *
     * @param $current_mailer
     * @return int
     */
    function wordpatch_configmail_num_steps($current_mailer, $current_smtp_auth) {
        return $current_mailer === wordpatch_mailer_smtp() ?
            ($current_smtp_auth === wordpatch_smtp_auth_yes() ? 4 : 3) : 2;
    }
}

if(!function_exists('wordpatch_configmail_step_text')) {
    /**
     * Calculate the step text for each step of the mail configuration wizard.
     *
     * @param $wpenv_vars
     * @param $step_number
     * @param $current_mailer
     * @param $current_smtp_auth
     * @return string
     */
    function wordpatch_configmail_step_text($wpenv_vars, $step_number, $current_mailer, $current_smtp_auth) {
        // Calculate the step numbers for each step.
        $steps = wordpatch_configmail_steps($current_mailer, $current_smtp_auth);

        // Create a variable for our step text result defaulting to an empty string.
        $step_text = "";

        // Calculate the step text based on the step number.
        switch ($step_number) {
            case $steps['basic']:
                $step_text = __wt($wpenv_vars, 'CONFIGMAIL_STEP_BASIC', $steps['basic']);
                break;
            case $steps['smtp']:
                $step_text = __wt($wpenv_vars, 'CONFIGMAIL_STEP_SMTP', $steps['smtp']);
                break;
            case $steps['creds']:
                $step_text = __wt($wpenv_vars, 'CONFIGMAIL_STEP_CREDS', $steps['creds']);
                break;
            case $steps['confirm']:
                $step_text = __wt($wpenv_vars, 'CONFIGMAIL_STEP_CONFIRM', $steps['confirm']);
                break;
        }

        // Return the step text result.
        return $step_text;
    }
}

if(!function_exists('wordpatch_configmail_vars')) {
    /**
     * Calculate the wizard variables for each step of the mail configuration wizard.
     * Results are returned as an associative array with the key convention:
     * - $result['step' . $step_number][$var_key]
     *
     * @param $current_mailer
     * @param $current_smtp_auth
     * @return array
     */
    function wordpatch_configmail_vars($current_mailer, $current_smtp_auth)
    {
        // Calculate the step numbers for each step.
        $steps = wordpatch_configmail_steps($current_mailer, $current_smtp_auth);

        // Begin to construct our variables result array.
        $vars = array(
            'step' . $steps['basic'] => array(
                'mailer',
                'mail_from',
                'mail_to'
            ),
            'step' . $steps['confirm'] => array()
        );

        // Populate the SMTP step variables list.
        if($steps['smtp'] !== null) {
            $vars['step' . $steps['smtp']] = array(
                'smtp_host',
                'smtp_port',
                'smtp_ssl',
                'smtp_auth'
            );
        }

        // Populate the SMTP credentials variables list.
        if($steps['creds'] !== null) {
            $vars['step' . $steps['creds']] = array(
                'smtp_user',
                'smtp_pass'
            );
        }

        // Return our variables result array.
        return $vars;
    }
}

if(!function_exists('wordpatch_configmail_post_vars')) {
    /**
     * Constructs and returns and associative array of mail configuration wizard post variables.
     * The parameter $only_consider_posted can be used to strictly only consider post data.
     * If you pass false for $only_consider_posted, then values will be pre-calculated or guessed if possible.
     *
     * @param $wpenv_vars
     * @param $only_consider_posted
     * @return array
     */
    function wordpatch_configmail_post_vars($wpenv_vars, $only_consider_posted) {
        // Create an empty array for our results
        $current = array();

        // Calculate the mailer.
        $current['mailer'] = wordpatch_wizard_post_data('mailer', wordpatch_configmail_guess_mailer($wpenv_vars),
            wordpatch_calculate_mailer($wpenv_vars), 'wordpatch_wizard_sanitize_mailer', $only_consider_posted);

        // Calculate the mail to.
        $current['mail_to'] = wordpatch_wizard_post_data('mail_to', wordpatch_configmail_guess_mail_to($wpenv_vars),
            wordpatch_calculate_mail_to($wpenv_vars), 'wordpatch_wizard_sanitize_email', $only_consider_posted);

        // Calculate the mail from.
        $current['mail_from'] = wordpatch_wizard_post_data('mail_from', wordpatch_configmail_guess_mail_from($wpenv_vars),
            wordpatch_calculate_mail_from($wpenv_vars), 'wordpatch_wizard_sanitize_email', $only_consider_posted);

        // Calculate the SMTP host.
        $current['smtp_host'] = wordpatch_wizard_post_data('smtp_host', wordpatch_configmail_guess_smtp_host($wpenv_vars),
            wordpatch_calculate_smtp_host($wpenv_vars), 'wordpatch_wizard_sanitize_trim', $only_consider_posted);

        // Calculate the SMTP port.
        $current['smtp_port'] = wordpatch_wizard_post_data('smtp_port', wordpatch_configmail_guess_smtp_port($wpenv_vars),
            wordpatch_calculate_smtp_port($wpenv_vars), 'wordpatch_wizard_sanitize_port', $only_consider_posted);

        // Calculate the SMTP SSL.
        $current['smtp_ssl'] = wordpatch_wizard_post_data('smtp_ssl', wordpatch_configmail_guess_smtp_ssl($wpenv_vars),
            wordpatch_calculate_smtp_ssl($wpenv_vars), 'wordpatch_wizard_sanitize_smtp_ssl', $only_consider_posted);

        // Calculate the SMTP authentication.
        $current['smtp_auth'] = wordpatch_wizard_post_data('smtp_auth', wordpatch_configmail_guess_smtp_auth($wpenv_vars),
            wordpatch_calculate_smtp_auth($wpenv_vars), 'wordpatch_wizard_sanitize_smtp_auth', $only_consider_posted);

        // Calculate the SMTP username.
        $current['smtp_user'] = wordpatch_wizard_post_data('smtp_user', wordpatch_configmail_guess_smtp_user($wpenv_vars),
            wordpatch_calculate_smtp_user($wpenv_vars), 'wordpatch_wizard_sanitize_trim', $only_consider_posted);

        // Calculate the SMTP password.
        $current['smtp_pass'] = wordpatch_wizard_post_data('smtp_pass', wordpatch_configmail_guess_smtp_pass($wpenv_vars),
            wordpatch_calculate_smtp_pass($wpenv_vars), 'wordpatch_wizard_sanitize_password', $only_consider_posted);

        // Return our calculate result array.
        return $current;
    }
}

if(!function_exists('wordpatch_configmail_buttons')) {
    /**
     * Calculates the button state for the mail configuration wizard.
     * Called by `wordpatch_configmail_render_form`.
     *
     * @param $step_number
     * @param $current_mailer
     * @param $wizard_error_list
     * @return array
     */
    function wordpatch_configmail_buttons($step_number, $current_mailer, $current_smtp_auth, $wizard_error_list) {
        // Calculate the step numbers for each step.
        $steps = wordpatch_configmail_steps($current_mailer, $current_smtp_auth);

        // Check if step number equals the basic step number and return the proper state, if necessary.
        if($step_number === $steps['basic']) {
            // Calculate if there should be a next button.
            $blocking_errors = wordpatch_configmail_get_blocking_errors_basic();
            $no_next = wordpatch_errors_are_blocking($wizard_error_list, $blocking_errors);

            // Calculate and return our state
            return array(
                WORDPATCH_SUBMIT_TYPE_NEXT => $no_next ? false : true,
                WORDPATCH_SUBMIT_TYPE_PREVIOUS => false,
            );
        }

        // Check if step number equals the SMTP step number and return the proper state, if necessary.
        if($step_number === $steps['smtp']) {
            // Calculate and return our state
            return array(
                WORDPATCH_SUBMIT_TYPE_NEXT => true,
                WORDPATCH_SUBMIT_TYPE_PREVIOUS => true
            );
        }

        // Check if step number equals the credentials step number and return the proper state, if necessary.
        if($step_number === $steps['creds']) {
            // Calculate and return our state
            return array(
                WORDPATCH_SUBMIT_TYPE_NEXT => true,
                WORDPATCH_SUBMIT_TYPE_PREVIOUS => true
            );
        }

        // By default, return a state with no button.
        return array(
            WORDPATCH_SUBMIT_TYPE_NEXT => false,
            WORDPATCH_SUBMIT_TYPE_PREVIOUS => false
        );
    }
}

if(!function_exists('wordpatch_configmail_steps')) {
    /**
     * Calculates the step numbers for each step of the mail configuration wizard.
     * Return value is an associative array with the key being the step key and the value being the step number.
     *
     * @param $current_mailer
     * @param $current_smtp_auth
     * @return array
     */
    function wordpatch_configmail_steps($current_mailer, $current_smtp_auth) {
        $confirm_step = 2;

        if($current_mailer === wordpatch_mailer_smtp()) {
            $confirm_step = $current_smtp_auth === wordpatch_smtp_auth_yes() ? 4 : 3;
        }

        return array(
            'basic' => 1,
            'smtp' => $current_mailer === wordpatch_mailer_smtp() ? 2 : null,
            'creds' => ($current_mailer === wordpatch_mailer_smtp() && $current_smtp_auth === wordpatch_smtp_auth_yes()) ? 3 : null,
            'confirm' => $confirm_step
        );
    }
}

if(!function_exists('wordpatch_configmail_db_data')) {
    /**
     * Construct values to be written to the database after the mail configuration wizard has completed.
     *
     * @param $wpenv_vars
     * @param $current
     * @return array
     */
    function wordpatch_configmail_db_data($wpenv_vars, $current)
    {
        // Start constructing $db_data
        $db_data = array(
            'mailer' => $current['mailer'],
            'mail_from' => $current['mail_from'],
            'mail_to' => $current['mail_to'],
            'smtp_host' => '',
            'smtp_port' => '',
            'smtp_ssl' => '',
            'smtp_auth' => '',
            'smtp_user' => '',
            'smtp_pass' => '',
        );

        // If we are using the SMTP mailer there are more options
        if($current['mailer'] === wordpatch_mailer_smtp()) {
            $db_data['smtp_host'] = $current['smtp_host'];
            $db_data['smtp_port'] = $current['smtp_port'];
            $db_data['smtp_ssl'] = $current['smtp_ssl'];
            $db_data['smtp_auth'] = $current['smtp_auth'];

            // Using authentication also means a username and password.
            if($current['smtp_auth'] === wordpatch_smtp_auth_yes()) {
                $db_data['smtp_user'] = $current['smtp_user'];
                $db_data['smtp_pass'] = $current['smtp_pass'];
            }
        }

        // If we get here, simply return $db_data
        return $db_data;
    }
}

if(!function_exists('wordpatch_configmail_persist_db')) {
    /**
     * Persists the finalized wizard data upon completion of the mail configuration wizard.
     *
     * @param $wpenv_vars
     * @param $db_data
     */
    function wordpatch_configmail_persist_db($wpenv_vars, $db_data) {
        // Store the mailer value in the database.
        wordpatch_update_option($wpenv_vars, 'wordpatch_mailer', $db_data['mailer']);

        // Store the mail from value in the database.
        wordpatch_update_option($wpenv_vars, 'wordpatch_mail_from', $db_data['mail_from']);

        // Store the mail to value in the database.
        wordpatch_update_option($wpenv_vars, 'wordpatch_mail_to', $db_data['mail_to']);

        // Store the SMTP host value in the database.
        wordpatch_update_option($wpenv_vars, 'wordpatch_smtp_host', $db_data['smtp_host']);

        // Store the SMTP port value in the database.
        wordpatch_update_option($wpenv_vars, 'wordpatch_smtp_port', $db_data['smtp_port']);

        // Store the SMTP SSL value in the database.
        wordpatch_update_option($wpenv_vars, 'wordpatch_smtp_ssl', $db_data['smtp_ssl']);

        // Store the SMTP authentication value in the database.
        wordpatch_update_option($wpenv_vars, 'wordpatch_smtp_auth', $db_data['smtp_auth']);

        // Store the SMTP username value in the database.
        wordpatch_update_option($wpenv_vars, 'wordpatch_smtp_user', $db_data['smtp_user']);

        // Store the SMTP password value in the database.
        wordpatch_update_option($wpenv_vars, 'wordpatch_smtp_pass', $db_data['smtp_pass']);

        // Store the mailer configured state in the database.
        wordpatch_update_option($wpenv_vars, 'wordpatch_mail_configured', WORDPATCH_YES);
    }
}

if(!function_exists('wordpatch_configmail_get_blocking_errors_basic')) {
    /**
     * Calculates the list of blocking errors for the basic step of the mail configuration wizard.
     *
     * @return array
     */
    function wordpatch_configmail_get_blocking_errors_basic() {
        // Return the list of blocking errors
        return array(WORDPATCH_MUST_CONFIGURE_DATABASE, WORDPATCH_MUST_CONFIGURE_FILESYSTEM,
            WORDPATCH_MUST_CONFIGURE_RESCUE, WORDPATCH_MUST_ACTIVATE_LICENSE);
    }
}