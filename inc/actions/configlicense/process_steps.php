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
 * The following functions perform the processing logic for each step of the license configuration wizard.
 * These functions are called from `wordpatch_process_configlicense`.
 */

if(!function_exists('wordpatch_configlicense_process_creds')) {
    /**
     * Process the credentials step of the license configuration wizard.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $submit_type
     * @param $step_number
     * @param $wizard_error_list
     */
    function wordpatch_configlicense_process_creds($wpenv_vars, $current, $submit_type, $step_number, &$wizard_error_list) {
        // If the submit type is not next then return early.
        if($submit_type !== WORDPATCH_SUBMIT_TYPE_NEXT) {
            return;
        }

        // Check if the activation key is valid, and if not append an error and return early.
        if(strlen($current['activation_key']) !== 20) {
            $wizard_error_list[] = WORDPATCH_INVALID_ACTIVATION_KEY;
            return;
        }

        // Calculate the current rescue path
        $current_rescue_path = wordpatch_calculate_rescue_path($wpenv_vars);

        // Validate our current rescue script and specify to not create it by passing false into the fourth argument.
        $wizard_error_list = wordpatch_rescue_validate_or_create($wpenv_vars, $current_rescue_path, false, false);

        // If we encountered one or more errors in validating our rescue script, exit early.
        if(!wordpatch_no_errors($wizard_error_list)) {
            return;
        }

        // Now since we are going to need to actually write to the filesystem, initiate a connection.
        // TODO: In the future, I'd really like to remove the "double connect" here.

        // Try to activate our license!
        $wizard_error_list = wordpatch_activate_license($wpenv_vars, $current['activation_key']);

        // If we encountered one or more errors in activating our license, exit early.
        if(!wordpatch_no_errors($wizard_error_list)) {
            return;
        }

        // Advance to the next step.
        wordpatch_goto_next_step($step_number);
    }
}


if(!function_exists('wordpatch_configlicense_process_basic')) {
    /**
     * Process the basic step of the license configuration wizard.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $submit_type
     * @param $step_number
     * @param $wizard_error_list
     */
    function wordpatch_configlicense_process_basic($wpenv_vars, $current, $submit_type, $step_number, &$wizard_error_list) {
        // If the submit type is not next then return early.
        if($submit_type !== WORDPATCH_SUBMIT_TYPE_NEXT) {
            return;
        }

        // Calculate the current rescue path
        $current_rescue_path = wordpatch_calculate_rescue_path($wpenv_vars);

        // Validate our current rescue script and specify to not create it by passing false into the fourth argument.
        $wizard_error_list = wordpatch_rescue_validate_or_create($wpenv_vars, $current_rescue_path, false, false);

        // If we encountered one or more errors in validating our rescue script, exit early.
        if(!wordpatch_no_errors($wizard_error_list)) {
            return;
        }

        // Purge our current license.
        $purge_errors = wordpatch_purge_license($wpenv_vars);

        // Check if we had any issues purging our license, which 99% of the time will mean an issue with FS credentials.
        // If this happens, we return early.
        if(!wordpatch_no_errors($purge_errors)) {
            $wizard_error_list[] = WORDPATCH_INVALID_FILESYSTEM_CREDENTIALS;
            return;
        }

        // Since we actually end up on step one again, we do not need to `goto_next_step`.
    }
}