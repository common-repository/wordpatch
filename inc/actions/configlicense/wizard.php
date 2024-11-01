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
 * The following functions define the majority of the license configuration shared functionality related to
 * configuration wizards.
 */

if(!function_exists('wordpatch_configlicense_num_steps')) {
    /**
     * Returns the number of steps in the license configuration wizard.
     * This should return 3.
     *
     * @return int
     */
    function wordpatch_configlicense_num_steps() {
        return 3;
    }
}

if(!function_exists('wordpatch_configlicense_step_text')) {
    /**
     * Calculate the step text for each step of the license configuration wizard.
     *
     * @param $wpenv_vars
     * @param $step_number
     * @return string
     */
    function wordpatch_configlicense_step_text($wpenv_vars, $step_number) {
        // Calculate the step numbers for each step.
        $steps = wordpatch_configlicense_steps();

        // Create a variable for our step text result defaulting to an empty string.
        $step_text = "";

        // Calculate the step text based on the step number.
        switch ($step_number) {
            case $steps['basic']:
                $step_text = __wt($wpenv_vars, 'CONFIGLICENSE_STEP_BASIC', $steps['basic']);
                break;
            case $steps['creds']:
                $step_text = __wt($wpenv_vars, 'CONFIGLICENSE_STEP_CREDS', $steps['creds']);
                break;
            case $steps['confirm']:
                $step_text = __wt($wpenv_vars, 'CONFIGLICENSE_STEP_CONFIRM', $steps['confirm']);
                break;
        }

        // Return the step text result.
        return $step_text;
    }
}

if(!function_exists('wordpatch_configlicense_vars')) {
    /**
     * Calculate the wizard variables for each step of the license configuration wizard.
     * Results are returned as an associative array with the key convention:
     * - $result['step' . $step_number][$var_key]
     *
     * @return array
     */
    function wordpatch_configlicense_vars()
    {
        // Calculate the step numbers for each step.
        $steps = wordpatch_configlicense_steps();

        // Begin to construct our variables result array.
        $vars = array(
            'step' . $steps['creds'] => array(
                'activation_key'
            )
        );

        // Return our variables result array.
        return $vars;
    }
}

if(!function_exists('wordpatch_configlicense_post_vars')) {
    /**
     * Constructs and returns and associative array of license configuration wizard post variables.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_configlicense_post_vars($wpenv_vars) {
        // Create an empty array for our results
        $current = array();

        // Calculate the activate key.
        $current['activation_key'] = wordpatch_wizard_post_data('activation_key', null, null,
            'wordpatch_wizard_sanitize_activation_key', true);

        // Return our calculate result array.
        return $current;
    }
}

if(!function_exists('wordpatch_configlicense_buttons')) {
    /**
     * Calculates the button state for the license configuration wizard.
     * Called by `wordpatch_configlicense_render_form`.
     *
     * @param $step_number
     * @param $wizard_error_list
     * @return array
     */
    function wordpatch_configlicense_buttons($step_number, $wizard_error_list) {
        // Calculate the step numbers for each step.
        $steps = wordpatch_configlicense_steps();

        // Check if step number equals the basic step number and return the proper state, if necessary.
        if($step_number === $steps['basic']) {
            // Calculate if there should be a next button.
            $blocking_errors = wordpatch_configlicense_get_blocking_errors_basic();
            $no_next = wordpatch_errors_are_blocking($wizard_error_list, $blocking_errors);

            // Calculate and return our state
            return array(
                WORDPATCH_SUBMIT_TYPE_NEXT => $no_next ? false : true,
                WORDPATCH_SUBMIT_TYPE_PREVIOUS => false,
            );
        }

        // Check if step number equals the credentials step number and return the proper state, if necessary.
        if($step_number === $steps['creds']) {
            // Calculate if there should be a next button.
            $blocking_errors = wordpatch_configlicense_get_blocking_errors_creds();
            $no_next = wordpatch_errors_are_blocking($wizard_error_list, $blocking_errors);

            // Calculate and return our state
            return array(
                WORDPATCH_SUBMIT_TYPE_NEXT => $no_next ? false : true,
                WORDPATCH_SUBMIT_TYPE_PREVIOUS => false,
            );
        }

        // By default, return a state with no button.
        return array(
            WORDPATCH_SUBMIT_TYPE_NEXT => false,
            WORDPATCH_SUBMIT_TYPE_PREVIOUS => false
        );
    }
}

if(!function_exists('wordpatch_configlicense_steps')) {
    /**
     * Calculates the step numbers for each step of the license configuration wizard.
     * Return value is an associative array with the key being the step key and the value being the step number.
     *
     * @return array
     */
    function wordpatch_configlicense_steps() {
        return array(
            'basic' => 1,
            'creds' => 2,
            'confirm' => 3
        );
    }
}

if(!function_exists('wordpatch_configlicense_get_blocking_errors_basic')) {
    /**
     * Calculates the list of blocking errors for the basic step of the license configuration wizard.
     *
     * @return array
     */
    function wordpatch_configlicense_get_blocking_errors_basic() {
        // Return the list of blocking errors
        return array(
            WORDPATCH_MUST_CONFIGURE_DATABASE,
            WORDPATCH_MUST_CONFIGURE_FILESYSTEM,
            WORDPATCH_MUST_CONFIGURE_RESCUE,
            WORDPATCH_INVALID_RESCUE_EXTENSION,
            WORDPATCH_INVALID_RESCUE_FILENAME,
            WORDPATCH_INVALID_RESCUE_DIRECTORY,
            WORDPATCH_INVALID_RESCUE_PATH,
            WORDPATCH_INVALID_RESCUE_FORMAT
        );
    }
}

if(!function_exists('wordpatch_configlicense_get_blocking_errors_creds')) {
    /**
     * Calculates the list of blocking errors for the credentials step of the license configuration wizard.
     *
     * @return array
     */
    function wordpatch_configlicense_get_blocking_errors_creds() {
        // Return the list of blocking errors
        return array(
            WORDPATCH_MUST_CONFIGURE_DATABASE,
            WORDPATCH_MUST_CONFIGURE_FILESYSTEM,
            WORDPATCH_MUST_CONFIGURE_RESCUE,
            WORDPATCH_INVALID_FILESYSTEM_CREDENTIALS,
            WORDPATCH_WRONG_FILESYSTEM_CREDENTIALS,
            WORDPATCH_FILESYSTEM_FAILED_WRITE,
            WORDPATCH_FILESYSTEM_FAILED_DELETE,
            WORDPATCH_FILESYSTEM_FAILED_READ,
            WORDPATCH_FS_PERMISSIONS_ERROR,
            WORDPATCH_INVALID_DATABASE_CREDENTIALS,
            WORDPATCH_WRONG_DATABASE_CREDENTIALS,
            WORDPATCH_UNKNOWN_DATABASE_ERROR,
            WORDPATCH_INVALID_RESCUE_FORMAT,
            WORDPATCH_UNKNOWN_HTTP_ERROR,
            WORDPATCH_UNAUTHORIZED_HTTP_ERROR
        );
    }
}