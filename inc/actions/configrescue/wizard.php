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
 * The following functions define the majority of the rescue configuration shared functionality related to
 * configuration wizards.
 */

if(!function_exists('wordpatch_configrescue_num_steps')) {
    /**
     * Returns the number of steps in the rescue configuration wizard.
     * This should always return 3.
     *
     * @return int
     */
    function wordpatch_configrescue_num_steps() {
        return 3;
    }
}

if(!function_exists('wordpatch_configrescue_step_text')) {
    /**
     * Calculate the step text for each step of the rescue configuration wizard.
     *
     * @param $wpenv_vars
     * @param $step_number
     * @return string
     */
    function wordpatch_configrescue_step_text($wpenv_vars, $step_number) {
        // Calculate the step numbers for each step.
        $steps = wordpatch_configrescue_steps();

        // Create a variable for our step text result defaulting to an empty string.
        $step_text = "";

        // Calculate the step text based on the step number.
        switch ($step_number) {
            case $steps['dirs']:
                $step_text = __wt($wpenv_vars, 'CONFIGRESCUE_STEP_DIRS', $steps['dirs']);
                break;
            case $steps['validate']:
                $step_text = __wt($wpenv_vars, 'CONFIGRESCUE_STEP_VALIDATE', $steps['validate']);
                break;
            case $steps['confirm']:
                $step_text = __wt($wpenv_vars, 'CONFIGRESCUE_STEP_CONFIRM', $steps['confirm']);
                break;
        }

        // Return the step text result.
        return $step_text;
    }
}

if(!function_exists('wordpatch_configrescue_vars')) {
    /**
     * Calculate the wizard variables for each step of the rescue configuration wizard.
     * Results are returned as an associative array with the key convention:
     * - $result['step' . $step_number][$var_key]
     *
     * @return array
     */
    function wordpatch_configrescue_vars()
    {
        // Calculate the step numbers for each step.
        $steps = wordpatch_configrescue_steps();

        // Begin to construct our variables result array.
        $vars = array(
            'step' . $steps['dirs'] => array(
                'rescue_path',
            ),
            'step' . $steps['validate'] => array(),
            'step' . $steps['confirm'] => array(),
        );

        // Return our variables result array.
        return $vars;
    }
}

if(!function_exists('wordpatch_configrescue_post_vars')) {
    /**
     * Constructs and returns and associative array of rescue configuration wizard post variables.
     * The parameter $only_consider_posted can be used to strictly only consider post data.
     * If you pass false for $only_consider_posted, then values will be pre-calculated or guessed if possible.
     *
     * @param $wpenv_vars
     * @param $only_consider_posted
     * @return array
     */
    function wordpatch_configrescue_post_vars($wpenv_vars, $only_consider_posted) {
        // Create an empty array for our results
        $current = array();

        // Calculate the rescue path.
        $current['rescue_path'] = wordpatch_wizard_post_data('rescue_path', wordpatch_configrescue_guess_rescue_path($wpenv_vars),
            wordpatch_calculate_rescue_path($wpenv_vars), 'wordpatch_wizard_sanitize_rescue_path', $only_consider_posted);

        // Return our calculate result array.
        return $current;
    }
}

if(!function_exists('wordpatch_configrescue_buttons')) {
    /**
     * Calculates the button state for the rescue configuration wizard.
     * Called by `wordpatch_configrescue_render_form`.
     *
     * @param $step_number
     * @param $wizard_error_list
     * @return array
     */
    function wordpatch_configrescue_buttons($step_number, $wizard_error_list) {
        // Calculate the step numbers for each step.
        $steps = wordpatch_configrescue_steps();

        // Check if step number equals the directories step number and return the proper state, if necessary.
        if($step_number === $steps['dirs']) {
            // Calculate if there should be a next button.
            $blocking_errors = wordpatch_configrescue_get_blocking_errors_dirs();
            $no_next = wordpatch_errors_are_blocking($wizard_error_list, $blocking_errors);

            // Calculate and return our state
            return array(
                WORDPATCH_SUBMIT_TYPE_NEXT => $no_next ? false : true,
                WORDPATCH_SUBMIT_TYPE_PREVIOUS => false,
            );
        }

        if($step_number === $steps['validate']) {
            // Calculate if there should be a next button.
            $blocking_errors = wordpatch_configrescue_get_blocking_errors_validate();
            $no_next = wordpatch_errors_are_blocking($wizard_error_list, $blocking_errors);

            // Calculate and return our state
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

if(!function_exists('wordpatch_configrescue_steps')) {
    /**
     * Calculates the step numbers for each step of the rescue configuration wizard.
     * Return value is an associative array with the key being the step key and the value being the step number.
     *
     * @return array
     */
    function wordpatch_configrescue_steps() {
        return array(
            'dirs' => 1,
            'validate' => 2,
            'confirm' => 3
        );
    }
}

if(!function_exists('wordpatch_configrescue_db_data')) {
    /**
     * Construct values to be written to the database after the rescue configuration wizard has completed.
     *
     * @param $wpenv_vars
     * @param $current
     * @return array
     */
    function wordpatch_configrescue_db_data($wpenv_vars, $current)
    {
        // Start constructing $db_data
        $db_data = array(
            'rescue_path' => $current['rescue_path'],
        );

        // If we get here, simply return $db_data
        return $db_data;
    }
}

if(!function_exists('wordpatch_configrescue_persist_db')) {
    /**
     * Persists the finalized wizard data upon completion of the rescue configuration wizard.
     *
     * @param $wpenv_vars
     * @param $db_data
     */
    function wordpatch_configrescue_persist_db($wpenv_vars, $db_data) {
        // Store the rescue path value in the database.
        wordpatch_update_option($wpenv_vars, 'wordpatch_rescue_path', $db_data['rescue_path']);

        // Store the rescue configured state in the database.
        wordpatch_update_option($wpenv_vars, 'wordpatch_rescue_configured', WORDPATCH_YES);
    }
}

if(!function_exists('wordpatch_configrescue_get_blocking_errors_dirs')) {
    /**
     * Calculates the list of blocking errors for the directories step of the rescue configuration wizard.
     *
     * @return array
     */
    function wordpatch_configrescue_get_blocking_errors_dirs() {
        // Return the list of blocking errors
        return array(WORDPATCH_MUST_CONFIGURE_DATABASE, WORDPATCH_MUST_CONFIGURE_FILESYSTEM);
    }
}

if(!function_exists('wordpatch_configrescue_get_blocking_errors_validate')) {
    /**
     * Calculates the list of blocking errors for the validation step of the rescue configuration wizard.
     *
     * @return array
     */
    function wordpatch_configrescue_get_blocking_errors_validate() {
        // Return null since all errors are blocking
        return null;
    }
}