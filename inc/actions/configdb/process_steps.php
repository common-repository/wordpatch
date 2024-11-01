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
 * The following functions perform the processing logic for each step of the database configuration wizard.
 * These functions are called from `wordpatch_process_configdb`.
 */

if(!function_exists('wordpatch_configdb_process_creds')) {
    /**
     * Process the credentials step of the database configuration wizard.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $submit_type
     * @param $step_number
     * @param $wizard_error_list
     */
    function wordpatch_configdb_process_creds($wpenv_vars, $current, $submit_type, $step_number, &$wizard_error_list) {
        // Check that the submit type is next before continuing.
        if($submit_type !== WORDPATCH_SUBMIT_TYPE_NEXT) {
            return;
        }

        // Validate the database credentials.
        $creds_error_list = wordpatch_wizard_validate_db_creds($current['db_host'], $current['db_user'],
            $current['db_name'], $current['db_collate'], $current['db_charset']);

        // Extend our error list.
        $wizard_error_list = wordpatch_extend_errors($wizard_error_list, $creds_error_list);

        // If there are errors, return out early.
        if(!wordpatch_no_errors($wizard_error_list)) {
            return;
        }

        // Advance to the next step.
        wordpatch_goto_next_step($step_number);
    }
}

if(!function_exists('wordpatch_configdb_process_validate')) {
    /**
     * Process the validation step of the database configuration wizard.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $submit_type
     * @param $step_number
     * @param $wizard_error_list
     */
    function wordpatch_configdb_process_validate($wpenv_vars, $current, $submit_type, $step_number, &$wizard_error_list) {
        // If $submit_type is previous, then send the user to the previous step and return.
        if($submit_type === WORDPATCH_SUBMIT_TYPE_PREVIOUS) {
            wordpatch_goto_previous_step($step_number);
            return;
        }

        // At this point, if the submit type is something other than next, it is not valid.
        if($submit_type !== WORDPATCH_SUBMIT_TYPE_NEXT) {
            return;
        }

        // Validate the database credentials.
        $validation_error = count(wordpatch_wizard_validate_db_creds($current['db_host'], $current['db_user'],
                $current['db_name'], $current['db_collate'], $current['db_charset'])) > 0;

        // Return out early with our error if we failed to validate.
        if($validation_error) {
            $wizard_error_list[] = WORDPATCH_INVALID_DATABASE_CREDENTIALS;
            return;
        }

        // Attempt to connect to the database.
        $db_connect_check = wordpatch_db_test($wpenv_vars, $current['db_host'], $current['db_user'], $current['db_password'],
            $current['db_name'], $current['db_table_prefix'], $current['db_collate'], $current['db_charset']);

        // Extend the wizard error list with the database connection error list.
        $wizard_error_list = wordpatch_extend_errors($wizard_error_list, $db_connect_check['error_list']);

        // Check if we have encountered any errors and exit early if so.
        if (!wordpatch_no_errors($wizard_error_list)) {
            return;
        }

        // Now that we know the data itself is valid, let's grab the data we care about specifically and put it in an array.
        $db_data = wordpatch_configdb_db_data($wpenv_vars, $current);

        // Persist the $db_data since we have succeeded.
        wordpatch_configdb_persist_db($wpenv_vars, $db_data);

        // Send them to the confirmation step.
        wordpatch_goto_next_step($step_number);
    }
}