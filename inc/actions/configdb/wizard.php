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
 * The following functions define the majority of the database configuration shared functionality related to
 * configuration wizards.
 */

if(!function_exists('wordpatch_configdb_num_steps')) {
    /**
     * Returns the number of steps in the database configuration wizard.
     * This will always return 3.
     *
     * @return int
     */
    function wordpatch_configdb_num_steps() {
        return 3;
    }
}

if(!function_exists('wordpatch_configdb_step_text')) {
    /**
     * Calculate the step text for each step of the database configuration wizard.
     *
     * @param $wpenv_vars
     * @param $step_number
     * @return string
     */
    function wordpatch_configdb_step_text($wpenv_vars, $step_number) {
        // Calculate the step numbers for each step.
        $steps = wordpatch_configdb_steps();

        // Create a variable for our step text result defaulting to an empty string.
        $step_text = "";

        // Calculate the step text based on the step number.
        switch ($step_number) {
            case $steps['creds']:
                $step_text = __wt($wpenv_vars, 'CONFIGDB_STEP_CREDS', $steps['creds']);
                break;
            case $steps['validate']:
                $step_text = __wt($wpenv_vars, 'CONFIGDB_STEP_VALIDATE', $steps['validate']);
                break;
            case $steps['confirm']:
                $step_text = __wt($wpenv_vars, 'CONFIGDB_STEP_CONFIRM', $steps['confirm']);
                break;
        }

        // Return the step text result.
        return $step_text;
    }
}

if(!function_exists('wordpatch_configdb_vars')) {
    /**
     * Calculate the wizard variables for each step of the database configuration wizard.
     * Results are returned as an associative array with the key convention:
     * - $result['step' . $step_number][$var_key]
     *
     * @return array
     */
    function wordpatch_configdb_vars()
    {
        // Calculate the step numbers for each step.
        $steps = wordpatch_configdb_steps();

        // Begin to construct our variables result array.
        $vars = array(
            'step' . $steps['creds'] => array(
                'db_host',
                'db_user',
                'db_password',
                'db_name',
                'db_table_prefix',
                'db_collate',
                'db_charset',
            ),
            'step' . $steps['validate'] => array(),
            'step' . $steps['confirm'] => array(),
        );

        // Return our variables result array.
        return $vars;

    }
}

if(!function_exists('wordpatch_configdb_post_vars')) {
    /**
     * Constructs and returns and associative array of database configuration wizard post variables.
     * The parameter $only_consider_posted can be used to strictly only consider post data.
     * If you pass false for $only_consider_posted, then values will be pre-calculated or guessed if possible.
     *
     * @param $wpenv_vars
     * @param $only_consider_posted
     * @return array
     */
    function wordpatch_configdb_post_vars($wpenv_vars, $only_consider_posted) {
        // Create an empty array for our results
        $current = array();

        // Calculate the database host value.
        $current['db_host'] = wordpatch_wizard_post_data('db_host', wordpatch_configdb_guess_db_host($wpenv_vars),
            wordpatch_calculate_db_host($wpenv_vars), 'wordpatch_wizard_sanitize_trim', $only_consider_posted);

        // Calculate the database user value.
        $current['db_user'] = wordpatch_wizard_post_data('db_user', wordpatch_configdb_guess_db_user($wpenv_vars),
            wordpatch_calculate_db_user($wpenv_vars), 'wordpatch_wizard_sanitize_trim', $only_consider_posted);

        // Calculate the database password value.
        $current['db_password'] = wordpatch_wizard_post_data('db_password', wordpatch_configdb_guess_db_password($wpenv_vars),
            wordpatch_calculate_db_password($wpenv_vars), 'wordpatch_wizard_sanitize_password', $only_consider_posted);

        // Calculate the database name value.
        $current['db_name'] = wordpatch_wizard_post_data('db_name', wordpatch_configdb_guess_db_name($wpenv_vars),
            wordpatch_calculate_db_name($wpenv_vars), 'wordpatch_wizard_sanitize_trim', $only_consider_posted);

        // Calculate the database table prefix value.
        $current['db_table_prefix'] = wordpatch_wizard_post_data('db_table_prefix', wordpatch_configdb_guess_db_table_prefix($wpenv_vars),
            wordpatch_calculate_db_table_prefix($wpenv_vars), 'wordpatch_wizard_sanitize_trim', $only_consider_posted);

        // Calculate the database collation value.
        $current['db_collate'] = wordpatch_wizard_post_data('db_collate', wordpatch_configdb_guess_db_collate($wpenv_vars),
            wordpatch_calculate_db_collate($wpenv_vars), 'wordpatch_wizard_sanitize_collate', $only_consider_posted);

        // Calculate the database charset value.
        $current['db_charset'] = wordpatch_wizard_post_data('db_charset', wordpatch_configdb_guess_db_charset($wpenv_vars),
            wordpatch_calculate_db_charset($wpenv_vars), 'wordpatch_wizard_sanitize_charset', $only_consider_posted);

        // Return our calculate result array.
        return $current;
    }
}

if(!function_exists('wordpatch_configdb_buttons')) {
    /**
     * Calculates the button state for the database configuration wizard.
     * Called by `wordpatch_configdb_render_form`.
     *
     * @param $step_number
     * @param $wizard_error_list
     * @return array
     */
    function wordpatch_configdb_buttons($step_number, $wizard_error_list) {
        // Calculate the step numbers for each step.
        $steps = wordpatch_configdb_steps();

        // Check if step number equals the credentials step number and return the proper state, if necessary.
        if($step_number === $steps['creds']) {
            return array(
                WORDPATCH_SUBMIT_TYPE_NEXT => true,
                WORDPATCH_SUBMIT_TYPE_PREVIOUS => false,
            );
        }

        // Check if step number equals the validate step number and return the proper state, if necessary.
        if($step_number === $steps['validate']) {
            $no_next = count($wizard_error_list) > 0;

            return array(
                WORDPATCH_SUBMIT_TYPE_NEXT => $no_next ? false : true,
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

if(!function_exists('wordpatch_configdb_steps')) {
    /**
     * Calculates the step numbers for each step of the database configuration wizard.
     * Return value is an associative array with the key being the step key and the value being the step number.
     *
     * @return array
     */
    function wordpatch_configdb_steps() {
        return array(
            'creds' => 1,
            'validate' => 2,
            'confirm' => 3
        );
    }
}

if(!function_exists('wordpatch_configdb_db_data')) {
    /**
     * Construct values to be written to the database after the database configuration wizard has completed.
     *
     * @param $wpenv_vars
     * @param $current
     * @return array
     */
    function wordpatch_configdb_db_data($wpenv_vars, $current)
    {
        // Start constructing $db_data and default some stuff to empty
        $db_data = array(
            'db_host' => $current['db_host'],
            'db_user' => $current['db_user'],
            'db_password' => $current['db_password'],
            'db_name' => $current['db_name'],
            'db_table_prefix' => $current['db_table_prefix'],
            'db_collate' => $current['db_collate'],
            'db_charset' => $current['db_charset'],
        );

        // If we get here, simply return $db_data
        return $db_data;
    }
}

if(!function_exists('wordpatch_configdb_persist_db')) {
    /**
     * Persists the finalized wizard data upon completion of the database configuration wizard.
     *
     * @param $wpenv_vars
     * @param $db_data
     */
    function wordpatch_configdb_persist_db($wpenv_vars, $db_data) {
        // Store the database host in the database.
        wordpatch_update_option($wpenv_vars, 'wordpatch_db_host', $db_data['db_host']);

        // Store the database username in the database.
        wordpatch_update_option($wpenv_vars, 'wordpatch_db_user', $db_data['db_user']);

        // Store the database password in the database.
        wordpatch_update_option($wpenv_vars, 'wordpatch_db_password', $db_data['db_password']);

        // Store the database name in the database.
        wordpatch_update_option($wpenv_vars, 'wordpatch_db_name', $db_data['db_name']);

        // Store the database table prefix in the database.
        wordpatch_update_option($wpenv_vars, 'wordpatch_db_table_prefix', $db_data['db_table_prefix']);

        // Store the database collation in the database.
        wordpatch_update_option($wpenv_vars, 'wordpatch_db_collate', $db_data['db_collate']);

        // Store the database charset in the database.
        wordpatch_update_option($wpenv_vars, 'wordpatch_db_charset', $db_data['db_charset']);

        // Store the database configured state in the database.
        wordpatch_update_option($wpenv_vars, 'wordpatch_db_configured', WORDPATCH_YES);
    }
}