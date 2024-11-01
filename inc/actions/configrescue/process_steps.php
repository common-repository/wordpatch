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
 * The following functions perform the processing logic for each step of the rescue configuration wizard.
 * These functions are called from `wordpatch_process_configrescue`.
 */

if(!function_exists('wordpatch_configrescue_process_dirs')) {
    /**
     * Process the directories step of the rescue configuration wizard.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $submit_type
     * @param $step_number
     * @param $wizard_error_list
     */
    function wordpatch_configrescue_process_dirs($wpenv_vars, $current, $submit_type, $step_number, &$wizard_error_list) {
        // If the submit type is anything other than next then exit early.
        if ($submit_type !== WORDPATCH_SUBMIT_TYPE_NEXT) {
            return;
        }

        // Calculate any potential rescue path issues that can be spotted from parsing.
        $rescue_dirs_errors = wordpatch_wizard_validate_rescue_dirs($wpenv_vars, $current['rescue_path']);

        // Extend the wizard error list with the rescue path validation error.
        $wizard_error_list = wordpatch_extend_errors($wizard_error_list, $rescue_dirs_errors);

        // If there has been errors encountered, return early.
        if(!wordpatch_no_errors($wizard_error_list)) {
            return;
        }

        // Advance the user to the next step.
        wordpatch_goto_next_step($step_number);
    }
}

if(!function_exists('wordpatch_configrescue_process_validate')) {
    /**
     * Process the validate step of the rescue configuration wizard.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $submit_type
     * @param $step_number
     * @param $wizard_error_list
     */
    function wordpatch_configrescue_process_validate($wpenv_vars, $current, $submit_type, $step_number, &$wizard_error_list) {
        // Check if the user just wants to go back.
        if ($submit_type === WORDPATCH_SUBMIT_TYPE_PREVIOUS) {
            wordpatch_goto_previous_step($step_number);
            return;
        }

        // If the submit type is not next at this point then return early.
        if ($submit_type !== WORDPATCH_SUBMIT_TYPE_NEXT) {
            return;
        }

        // Validate the rescue script writing process (third parameter false indicates not to rollback write on success)
        $process_error_list = wordpatch_rescue_validate_or_create($wpenv_vars, $current['rescue_path'], false);

        // Extend our wizard error list with the errors from the rescue write validator.
        $wizard_error_list = wordpatch_extend_errors($wizard_error_list, $process_error_list);

        if(!wordpatch_no_errors($wizard_error_list)) {
            return;
        }

        // Now that we know the data itself is valid, let's grab the data we care about specifically and put it in an array.
        $db_data = wordpatch_configrescue_db_data($wpenv_vars, $current);

        // Persist the $db_data since we have succeeded.
        wordpatch_configrescue_persist_db($wpenv_vars, $db_data);

        // Send them to the confirmation step.
        wordpatch_goto_next_step($step_number);
    }
}