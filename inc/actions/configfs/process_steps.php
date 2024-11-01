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
 * The following functions perform the processing logic for each step of the filesystem configuration wizard.
 * These functions are called from `wordpatch_process_configfs`.
 */

if(!function_exists('wordpatch_configfs_process_basic')) {
    /**
     * Process the basic step of the filesystem configuration wizard. Responsible for ensuring the basic details are
     * valid.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $submit_type
     * @param $step_number
     * @param $wizard_error_list
     */
    function wordpatch_configfs_process_basic($wpenv_vars, $current, $submit_type, $step_number, &$wizard_error_list) {
        // Check if the submit type is anything other than next, and if so return early.
        if($submit_type !== WORDPATCH_SUBMIT_TYPE_NEXT) {
            return;
        }

        // Validate the filesystem method value.
        if ($current['fs_method'] === null || !wordpatch_is_valid_fs_method($current['fs_method'])) {
            $wizard_error_list[] = WORDPATCH_INVALID_FS_METHOD;
        }

        // Validate the filesystem timeout value.
        if ($current['fs_timeout'] === null || $current['fs_timeout'] < 0) {
            $wizard_error_list[] = WORDPATCH_INVALID_FS_TIMEOUT;
        }

        // Validate the filesystem CHMOD file value.
        if ($current['fs_chmod_file_int'] <= 0 || !wordpatch_is_valid_chmod_value($current['fs_chmod_file_int'])) {
            $wizard_error_list[] = WORDPATCH_FS_CHMOD_FILE_REQUIRED;
        }

        // Validate the filesystem CHMOD directory value.
        if ($current['fs_chmod_dir_int'] <= 0 || !wordpatch_is_valid_chmod_value($current['fs_chmod_dir_int'])) {
            $wizard_error_list[] = WORDPATCH_FS_CHMOD_DIR_REQUIRED;
        }

        // If there are errors, return early.
        if(!wordpatch_no_errors($wizard_error_list)) {
            return;
        }

        // Advance the user to the next step.
        wordpatch_goto_next_step($step_number);
    }
}

if(!function_exists('wordpatch_configfs_process_validate')) {
    /**
     * Process the validate step of the filesystem configuration wizard. Responsible for ensuring the credentials are
     * valid and also persisting the final results to the database.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $submit_type
     * @param $step_number
     * @param $wizard_error_list
     */
    function wordpatch_configfs_process_validate($wpenv_vars, $current, $submit_type, $step_number, &$wizard_error_list) {
        // If $submit_type is previous, then send the user to the previous step and return.
        if($submit_type === WORDPATCH_SUBMIT_TYPE_PREVIOUS) {
            wordpatch_goto_previous_step($step_number);
            return;
        }

        // At this point, if the submit type is something other than next, it is not valid.
        if($submit_type !== WORDPATCH_SUBMIT_TYPE_NEXT) {
            return;
        }

        // Validate the current filesystem credentials.
        $validation_error = count(wordpatch_wizard_validate_fs_info($current['fs_method'], $current['fs_timeout'],
                $current['fs_chmod_file_int'], $current['fs_chmod_dir_int'], $current['ftp_host'], $current['ftp_user'],
                $current['ftp_prikey'], $current['ftp_pubkey'], $current['ftp_pass'])) > 0;

        // If we have encountered an error, add it to $wizard_error_list and exit early.
        if($validation_error) {
            $wizard_error_list[] = WORDPATCH_INVALID_FILESYSTEM_CREDENTIALS;
            return;
        }

        // Now test our ability to connect and write to the filesystem
        $fs_test = wordpatch_filesystem_test($wpenv_vars, $current['fs_method'], $current['ftp_base'],
            $current['ftp_content_dir'], $current['ftp_plugin_dir'], $current['ftp_pubkey'], $current['ftp_prikey'],
            $current['ftp_user'], $current['ftp_pass'], $current['ftp_host'], $current['ftp_ssl'], $current['fs_chmod_file'],
            $current['fs_chmod_dir'], $current['fs_timeout']);

        // Extend the error list from our filesystem test
        $wizard_error_list = wordpatch_extend_errors($wizard_error_list, $fs_test['error_list']);

        // Exit early if we have encountered one or more errors.
        if (!wordpatch_no_errors($wizard_error_list)) {
            return;
        }

        // Now that we know the data itself is valid, let's grab the data we care about specifically and put it in an array.
        $db_data = wordpatch_configfs_db_data($wpenv_vars, $current);

        // Persist the $db_data since we have succeeded.
        wordpatch_configfs_persist_db($wpenv_vars, $db_data);

        // Send them to the confirmation step.
        wordpatch_goto_next_step($step_number);
    }
}

if(!function_exists('wordpatch_configfs_process_creds')) {
    /**
     * Process the credentials step of the filesystem configuration wizard.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $submit_type
     * @param $step_number
     * @param $wizard_error_list
     */
    function wordpatch_configfs_process_creds($wpenv_vars, $current, $submit_type, $step_number, &$wizard_error_list) {
        // Check if the $submit_type is previous, and if so send the user a step back.
        if($submit_type === WORDPATCH_SUBMIT_TYPE_PREVIOUS) {
            // Before sending them back, persist null for credential post data.
            wordpatch_wizard_persist_post_data('ftp_host', null);
            wordpatch_wizard_persist_post_data('ftp_user', null);
            wordpatch_wizard_persist_post_data('ftp_pass', null);
            wordpatch_wizard_persist_post_data('ftp_pubkey', null);
            wordpatch_wizard_persist_post_data('ftp_prikey', null);
            wordpatch_wizard_persist_post_data('ftp_ssl', null);

            // Now go to the previous step and return early.
            wordpatch_goto_previous_step($step_number);
            return;
        }

        // If the submit type is anything other than next at this point, return early since it is not valid.
        if($submit_type !== WORDPATCH_SUBMIT_TYPE_NEXT) {
            return;
        }

        // Validate the credentials.
        $cred_errors = wordpatch_wizard_validate_fs_creds($current['fs_method'], $current['ftp_host'],
            $current['ftp_user'], $current['ftp_prikey'], $current['ftp_pubkey'], $current['ftp_pass']);

        // Extend our error list with the credential validate errors.
        $wizard_error_list = wordpatch_extend_errors($wizard_error_list, $cred_errors);

        // If we have encountered one or more errors, return out early.
        if(!wordpatch_no_errors($wizard_error_list)) {
            return;
        }

        // If we got here, we should advance to the next step of the wizard.
        wordpatch_goto_next_step($step_number);
    }
}

if(!function_exists('wordpatch_configfs_process_dirs')) {
    /**
     * Process the dirs step of the filesystem configuration wizard.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $submit_type
     * @param $step_number
     * @param $wizard_error_list
     */
    function wordpatch_configfs_process_dirs($wpenv_vars, $current, $submit_type, $step_number, &$wizard_error_list) {
        // Check if the submit type is previous, and if so send the user to the previous step.
        if($submit_type === WORDPATCH_SUBMIT_TYPE_PREVIOUS) {
            wordpatch_wizard_persist_post_data('ftp_base', null);
            wordpatch_wizard_persist_post_data('ftp_content_dir', null);
            wordpatch_wizard_persist_post_data('ftp_plugin_dir', null);

            wordpatch_goto_previous_step($step_number);
            return;
        }

        // At this point, if the submit type is something other than next, it is not valid.
        if($submit_type !== WORDPATCH_SUBMIT_TYPE_NEXT) {
            return;
        }

        // Validate the FTP base value.
        if(!$current['ftp_base'] || trim($current['ftp_base']) === '') {
            $wizard_error_list[] = WORDPATCH_FTP_BASE_REQUIRED;
        }

        // Validate the FTP content directory value.
        if(!$current['ftp_content_dir'] || trim($current['ftp_content_dir']) === '') {
            $wizard_error_list[] = WORDPATCH_FTP_CONTENT_DIR_REQUIRED;
        }

        // Validate the FTP plugin directory value.
        if(!$current['ftp_plugin_dir'] || trim($current['ftp_plugin_dir']) === '') {
            $wizard_error_list[] = WORDPATCH_FTP_PLUGIN_DIR_REQUIRED;
        }

        // If we have encountered one or more errors, return out early.
        if (!wordpatch_no_errors($wizard_error_list)) {
            return;
        }

        // If we got here, we should advance to the next step of the wizard.
        wordpatch_goto_next_step($step_number);
    }
}