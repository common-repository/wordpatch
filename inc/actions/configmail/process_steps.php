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
 * The following functions perform the processing logic for each step of the mail configuration wizard.
 * These functions are called from `wordpatch_process_configmail`.
 */

if(!function_exists('wordpatch_configmail_process_basic')) {
    /**
     * Process the basic step of the mail configuration wizard.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $submit_type
     * @param $step_number
     * @param $wizard_error_list
     */
    function wordpatch_configmail_process_basic($wpenv_vars, $current, $submit_type, $step_number, &$wizard_error_list) {
        // If the submit type is not next then return early.
        if($submit_type !== WORDPATCH_SUBMIT_TYPE_NEXT) {
            return;
        }

        // Check if the mailer is valid, and if not append an error.
        if(!$current['mailer'] || !wordpatch_is_valid_mailer($current['mailer'])) {
            $wizard_error_list[] = WORDPATCH_INVALID_MAILER;
        }

        // Check if the mail from is valid, and if not append an error.
        $mail_from = !$current['mail_from'] ? '' : trim($current['mail_from']);

        if($mail_from === '') {
            $wizard_error_list[] = WORDPATCH_MAIL_FROM_REQUIRED;
        } else if(!filter_var($mail_from, FILTER_VALIDATE_EMAIL)) {
            $wizard_error_list[] = WORDPATCH_INVALID_MAIL_FROM;
        }

        // Check if the mail to is valid, and if not append an error.
        $mail_to = !$current['mail_to'] ? '' : trim($current['mail_to']);

        if($mail_to === '') {
            $wizard_error_list[] = WORDPATCH_MAIL_TO_REQUIRED;
        } else if(!filter_var($mail_to, FILTER_VALIDATE_EMAIL)) {
            $wizard_error_list[] = WORDPATCH_INVALID_MAIL_TO;
        }

        // If we have encountered an error thus far, return early.
        if(!wordpatch_no_errors($wizard_error_list)) {
            return;
        }

        // If we are not using the JointByte mailer, just go to the next step and return early.
        if($current['mailer'] !== wordpatch_mailer_jointbyte()) {
            // Check if we are actually on the last step (if it is not `jointbyte` and not `smtp` mailer)
            if($current['mailer'] !== wordpatch_mailer_smtp()) {
                // Now that we know the data itself is valid, let's grab the data we care about specifically and put it in an array.
                $db_data = wordpatch_configmail_db_data($wpenv_vars, $current);

                // Persist the $db_data since we have succeeded.
                wordpatch_configmail_persist_db($wpenv_vars, $db_data);
            }

            // Advance to the next step.
            wordpatch_goto_next_step($step_number);
            return;
        }

        // Try to persist the information to the API server
        $persist_error = wordpatch_configmail_persist_jointbyte($wpenv_vars, $current['mail_to'], $current['mail_from']);

        // Check for errors and return early if we find one.
        if($persist_error !== null) {
            $wizard_error_list[] = $persist_error;
            return;
        }

        // Now that we know the data itself is valid, let's grab the data we care about specifically and put it in an array.
        $db_data = wordpatch_configmail_db_data($wpenv_vars, $current);

        // Persist the $db_data since we have succeeded.
        wordpatch_configmail_persist_db($wpenv_vars, $db_data);

        // Advance to the next step.
        wordpatch_goto_next_step($step_number);
    }
}

if(!function_exists('wordpatch_configmail_persist_jointbyte')) {
    function wordpatch_configmail_persist_jointbyte($wpenv_vars, $mail_to, $mail_from) {
        // Construct our API URL for sending an email notification
        $api_url = wordpatch_build_api_url('v1/snitch');

        // Calculate the post data
        $post_data = array(
            'mail' => $mail_to,
            'reply_to' => $mail_from
        );

        // Perform the request.
        $response = wordpatch_do_protected_http_request($wpenv_vars, $api_url, $post_data, $http_error);

        // If there was an issue with the request, return false.
        if($http_error !== null) {
            return $http_error;
        }

        // If there was a response error, also return false.
        if (isset($response['error']) && $response['error'] && isset($response['error']['message']) && $response['error']['message']) {
            return $response['error']['message'];
        }

        // Otherwise, all is good.
        return null;
    }
}
if(!function_exists('wordpatch_configmail_process_smtp')) {
    /**
     * Process the SMTP step of the mail configuration wizard.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $submit_type
     * @param $step_number
     * @param $wizard_error_list
     */
    function wordpatch_configmail_process_smtp($wpenv_vars, $current, $submit_type, $step_number, &$wizard_error_list) {
        // If the submit type is previous, then simply send the user back to the previous step after clearing it's data.
        if($submit_type === WORDPATCH_SUBMIT_TYPE_PREVIOUS) {
            wordpatch_wizard_persist_post_data('smtp_host', null);
            wordpatch_wizard_persist_post_data('smtp_port', null);
            wordpatch_wizard_persist_post_data('smtp_ssl', null);
            wordpatch_wizard_persist_post_data('smtp_auth', null);

            // Send the user backwards
            wordpatch_goto_previous_step($step_number);

            return;
        }

        // If the submit type is not next then return early.
        if($submit_type !== WORDPATCH_SUBMIT_TYPE_NEXT) {
            return;
        }

        // Validate the SMTP host.
        if(!$current['smtp_host'] || $current['smtp_host'] === '') {
            $wizard_error_list[] = WORDPATCH_SMTP_HOST_REQUIRED;
        }

        // Validate the SMTP port.
        if(!$current['smtp_port'] || $current['smtp_port'] <= 0) {
            $wizard_error_list[] = WORDPATCH_SMTP_PORT_REQUIRED;
        }

        // Validate the SMTP SSL.
        if(!$current['smtp_ssl'] || !wordpatch_is_valid_smtp_ssl($current['smtp_ssl'])) {
            $wizard_error_list[] = WORDPATCH_INVALID_SMTP_SSL;
        }

        // Validate the SMTP authentication.
        if(!$current['smtp_auth'] || !wordpatch_is_valid_smtp_auth($current['smtp_auth'])) {
            $wizard_error_list[] = WORDPATCH_INVALID_SMTP_AUTH;
        }

        // If we have encountered an error thus far, return early.
        if(!wordpatch_no_errors($wizard_error_list)) {
            return;
        }

        // If authentication is disabled, let's persist the update.
        if($current['smtp_auth'] === wordpatch_smtp_auth_no()) {
            // Now that we know the data itself is valid, let's grab the data we care about specifically and put it in an array.
            $db_data = wordpatch_configmail_db_data($wpenv_vars, $current);

            // Persist the $db_data since we have succeeded.
            wordpatch_configmail_persist_db($wpenv_vars, $db_data);
        }

        // Advance to the next step.
        wordpatch_goto_next_step($step_number);
    }
}

if(!function_exists('wordpatch_configmail_process_creds')) {
    /**
     * Process the credentials step of the mail configuration wizard.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $submit_type
     * @param $step_number
     * @param $wizard_error_list
     */
    function wordpatch_configmail_process_creds($wpenv_vars, $current, $submit_type, $step_number, &$wizard_error_list) {
        // If the submit type is previous, then simply send the user back to the previous step after clearing it's data.
        if($submit_type === WORDPATCH_SUBMIT_TYPE_PREVIOUS) {
            wordpatch_wizard_persist_post_data('smtp_user', null);
            wordpatch_wizard_persist_post_data('smtp_pass', null);

            // Send the user backwards
            wordpatch_goto_previous_step($step_number);

            return;
        }

        // If the submit type is not next then return early.
        if($submit_type !== WORDPATCH_SUBMIT_TYPE_NEXT) {
            return;
        }

        // Validate the SMTP username.
        if(!$current['smtp_user'] || $current['smtp_user'] === '') {
            $wizard_error_list[] = WORDPATCH_SMTP_USER_REQUIRED;
        }

        // Validate the SMTP password.
        if(!$current['smtp_pass'] || $current['smtp_pass'] === '') {
            $wizard_error_list[] = WORDPATCH_SMTP_PASS_REQUIRED;
        }

        // If we have encountered an error thus far, return early.
        if(!wordpatch_no_errors($wizard_error_list)) {
            return;
        }

        // Now that we know the data itself is valid, let's grab the data we care about specifically and put it in an array.
        $db_data = wordpatch_configmail_db_data($wpenv_vars, $current);

        // Persist the $db_data since we have succeeded.
        wordpatch_configmail_persist_db($wpenv_vars, $db_data);

        // Send them to the confirmation step.
        wordpatch_goto_next_step($step_number);
    }
}