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
 * Implements the mailer functionality.
 */

if(!function_exists('wordpatch_mailer_send')) {
    /**
     * Attempts to send an email for WordPatch. Users must first configure their mailer which means they must first
     * activate WordPatch.
     *
     * $mailer_info should be populated with the result of `wordpatch_calculate_mailer_info`.
     * Token is null when mailer is not `jointbyte`.
     *
     * Returns an array which will be empty on success and will contain errors if there is an issue.
     *
     * @param $wpenv_vars
     * @param $mailer_info
     * @param $template
     * @param $vars
     * @return bool
     */
    function wordpatch_mailer_send($wpenv_vars, $mailer_info, $template, $vars) {
        // Check if we are configured to use the jointbyte mailer and return that function result if so.
        if($mailer_info['mailer'] === wordpatch_mailer_jointbyte()) {
            return wordpatch_mailer_send_jointbyte($wpenv_vars, $template, $vars);
        }

        // Calculate subject line.
        $subject = wordpatch_mailer_build_email_subject($wpenv_vars, $template, $vars);

        // Calculate HTML version.
        $html = wordpatch_mailer_build_email_html($wpenv_vars, $template, $vars, $subject);

        // Calculate the HTML email headers.
        $headers = array('Content-Type: text/html; charset=UTF-8');

        // Try to send off the email!
        return wordpatch_mail($wpenv_vars, $mailer_info, $mailer_info['mail_to'], $subject, $html, $headers);
    }
}

if(!function_exists('wordpatch_mailer_send_jointbyte')) {
    function wordpatch_mailer_send_jointbyte($wpenv_vars, $template, $vars) {
        // Construct our API URL for sending an email notification
        $api_url = wordpatch_build_api_url('v1/snitch/notification');

        // Calculate the post data
        $post_data = array(
            'template' => $template,
            'args' => $vars
        );

        // Perform the request.
        $response = wordpatch_do_protected_http_request($wpenv_vars, $api_url, $post_data, $http_error);

        // If there was an issue with the request, return false.
        if($http_error !== null) {
            return false;
        }

        // If there was a response error, also return false.
        if (isset($response['error']) && $response['error']) {
            return false;
        }

        // Otherwise, all is good.
        return true;
    }
}

if(!function_exists('wordpatch_mailer_build_email_subject')) {
    function wordpatch_mailer_build_email_subject($wpenv_vars, $template, $vars) {
        $prefix = "WordPatch - ";

        // Start calculating the subject
        $subject = "$prefix Notification";

        switch ($template) {
            case WORDPATCH_MAIL_TEMPLATE_FAILED_JOB:
                $subject = $prefix . wordpatch_mail_failed_job_subject($wpenv_vars, $vars);
                break;

            case WORDPATCH_MAIL_TEMPLATE_COMPLETED_JOB:
                $subject = $prefix . wordpatch_mail_completed_job_subject($wpenv_vars, $vars);
                break;

            case WORDPATCH_MAIL_TEMPLATE_TEST:
                $subject = $prefix . wordpatch_mail_test_subject($wpenv_vars, $vars);
                break;
        }

        // Return our calculated subject
        return $subject;
    }
}

if(!function_exists('wordpatch_mailer_build_email_html')) {
    /**
     * @param $wpenv_vars
     * @param $template
     * @param $vars
     * @param $subject
     * @param bool $lite
     * @return string
     */
    function wordpatch_mailer_build_email_html($wpenv_vars, $template, $vars, $subject, $lite = false) {
        // Start calculating the HTML
        $html = wordpatch_mail_layout_header_html($wpenv_vars, $vars, $subject, $lite);

        switch ($template) {
            case WORDPATCH_MAIL_TEMPLATE_FAILED_JOB:
                $html .= wordpatch_mail_failed_job_html($wpenv_vars, $vars, $subject);
                break;

            case WORDPATCH_MAIL_TEMPLATE_COMPLETED_JOB:
                $html .= wordpatch_mail_completed_job_html($wpenv_vars, $vars, $subject);
                break;

            case WORDPATCH_MAIL_TEMPLATE_TEST:
                $html .= wordpatch_mail_test_html($wpenv_vars, $vars, $subject);
                break;
        }
        // Append the footer HTML
        $html .= wordpatch_mail_layout_footer_html($vars, $subject, $lite);

        // Inline our CSS
        $html = wordpatch_mail_inline_styles($html);

        // Return our calculated HTML
        return $html;
    }
}

if(!function_exists('wordpatch_mailer_build_email_text')) {
    function wordpatch_mailer_build_email_text($wpenv_vars, $template, $vars, $subject) {
        // Start calculating the text
        $text = wordpatch_mail_layout_header_text($vars, $subject);

        switch ($template) {
            case WORDPATCH_MAIL_TEMPLATE_FAILED_JOB:
                $text .= wordpatch_mail_failed_job_text($wpenv_vars, $vars, $subject);
                break;

            case WORDPATCH_MAIL_TEMPLATE_COMPLETED_JOB:
                $text .= wordpatch_mail_completed_job_text($wpenv_vars, $vars, $subject);
                break;

            case WORDPATCH_MAIL_TEMPLATE_TEST:
                $text .= wordpatch_mail_test_text($wpenv_vars, $vars, $subject);
                break;
        }

        // Append the footer text
        $text .= wordpatch_mail_layout_footer_text($vars, $subject);

        // Return our calculated text
        return $text;
    }
}

if(!function_exists('wordpatch_mailer_handle_failed_job')) {
    /**
     * Responsible for sending out an email for failed jobs.
     *
     * @param $wpenv_vars
     * @param $log_info
     * @param $error_list
     * @param $error_vars
     * @return bool
     */
    function wordpatch_mailer_handle_failed_job($wpenv_vars, $log_info, $error_list, $error_vars) {
        // Calculate mailer info.
        $mailer_info = wordpatch_calculate_mailer_info($wpenv_vars);

        // Calculate full rescue URL.
        $full_rescue_url = wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_site_url')) .
            wordpatch_env_get($wpenv_vars, 'rescue_path');

        // The following vars are added to the log info for use with the template.
        $na = __wt($wpenv_vars, 'NOT_AVAILABLE_ABBR');

        $log_info['display_finish_datetime'] = $log_info['finish_datetime'] === null ?
            $na : wordpatch_timestamp_to_readable($log_info['finish_datetime']);

        $log_info['display_running_datetime'] = $log_info['running_datetime'] === null ?
            $na : wordpatch_timestamp_to_readable($log_info['running_datetime']);

        $log_info['display_path'] = wordpatch_job_display_path($log_info['path']);

        $log_info['display_init_reason'] = wordpatch_display_init_reason($wpenv_vars, $log_info['init_reason']);

        $log_info['display_init_subject_list'] = wordpatch_display_init_subject_list($wpenv_vars, $log_info['init_subject_list']);

        $log_info['display_init_user'] = wordpatch_get_display_user($wpenv_vars, $log_info['init_user']);

        $log_info['display_errors'] = wordpatch_display_errors($wpenv_vars, WORDPATCH_WHERE_LOGDETAIL,
            $log_info['error_list'], $log_info['error_vars']);

        $log_info['display_datetime'] = wordpatch_timestamp_to_readable($log_info['datetime']);
        
        $log_info['display_should_retry'] = wordpatch_display_should_retry($wpenv_vars, $log_info['should_retry']);

        $log_info['display_attempts'] = wordpatch_display_attempts($wpenv_vars, $log_info['retry_count'],
            $log_info['attempt_count_int']);

        return wordpatch_mailer_send($wpenv_vars, $mailer_info, WORDPATCH_MAIL_TEMPLATE_FAILED_JOB, array(
            'log_info' => $log_info
        ));
    }
}

if(!function_exists('wordpatch_mailer_handle_passed_job')) {
    /**
     * Responsible for sending out an email for passed jobs.
     *
     * @param $wpenv_vars
     * @param $log_info
     * @param $changes
     * @return bool
     */
    function wordpatch_mailer_handle_passed_job($wpenv_vars, $log_info, $changes) {
        // Calculate mailer info.
        $mailer_info = wordpatch_calculate_mailer_info($wpenv_vars);

        // Calculate full rescue URL.
        $full_rescue_url = wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_site_url')) .
            wordpatch_env_get($wpenv_vars, 'rescue_path');

        // The following vars are added to the log info for use with the template.
        $na = __wt($wpenv_vars, 'NOT_AVAILABLE_ABBR');

        $log_info['display_finish_datetime'] = $log_info['finish_datetime'] === null ?
            $na : wordpatch_timestamp_to_readable($log_info['finish_datetime']);

        $log_info['display_path'] = wordpatch_job_display_path($log_info['path']);

        $log_info['display_mode'] = wordpatch_display_mode($wpenv_vars, $log_info['mode']);

        $log_info['display_timer'] = wordpatch_display_timer($wpenv_vars, $log_info['timer']);

        $log_info['display_binary_mode'] = wordpatch_display_job_binary_mode($wpenv_vars, $log_info['binary_mode']);

        $log_info['display_init_reason'] = wordpatch_display_init_reason($wpenv_vars, $log_info['init_reason']);

        $log_info['display_init_subject_list'] = wordpatch_display_init_subject_list($wpenv_vars, $log_info['init_subject_list']);

        $log_info['display_init_user'] = wordpatch_get_display_user($wpenv_vars, $log_info['init_user']);

        $log_info['display_datetime'] = wordpatch_timestamp_to_readable($log_info['datetime']);

        $log_info['display_changes'] = wordpatch_display_changes($wpenv_vars, $changes);

        $log_info['display_errors'] = wordpatch_display_errors($wpenv_vars, WORDPATCH_WHERE_JUDGE, $log_info['error_list'],
            $log_info['error_vars']);

        return wordpatch_mailer_send($wpenv_vars, $mailer_info, WORDPATCH_MAIL_TEMPLATE_COMPLETED_JOB, array(
            'log_info' => $log_info,
            'changes' => $changes
        ));
    }
}

if(!function_exists('wordpatch_display_mailer')) {
    /**
     * @param $mailer
     * @return mixed
     * @qc
     */
    function wordpatch_display_mailer($wpenv_vars, $mailer)
    {
        $display_names = array(
            wordpatch_mailer_smtp() => __wt($wpenv_vars, 'MAILER_SMTP'),
            wordpatch_mailer_jointbyte() => __wt($wpenv_vars, 'MAILER_JOINTBYTE'),
            wordpatch_mailer_mail() => __wt($wpenv_vars, 'MAILER_MAIL'),
            wordpatch_mailer_sendmail() => __wt($wpenv_vars, 'MAILER_SENDMAIL')
        );

        return wordpatch_display_name($display_names, $mailer);
    }
}

if(!function_exists('wordpatch_is_valid_mailer')) {
    /**
     * @param $mailer
     * @return bool
     * @qc
     */
    function wordpatch_is_valid_mailer($mailer)
    {
        return in_array($mailer, wordpatch_mailers());
    }
}

if(!function_exists('wordpatch_mailer_smtp')) {
    /**
     * @return string
     * @qc
     */
    function wordpatch_mailer_smtp()
    {
        return 'smtp';
    }
}

if(!function_exists('wordpatch_mailer_jointbyte')) {
    /**
     * @return string
     * @qc
     */
    function wordpatch_mailer_jointbyte()
    {
        return 'jointbyte';
    }
}

if(!function_exists('wordpatch_mailer_mail')) {
    /**
     * @return string
     * @qc
     */
    function wordpatch_mailer_mail()
    {
        return 'mail';
    }
}

if(!function_exists('wordpatch_mailer_sendmail')) {
    /**
     * @return string
     * @qc
     */
    function wordpatch_mailer_sendmail()
    {
        return 'sendmail';
    }
}

if(!function_exists('wordpatch_mailers')) {
    /**
     * @return array
     * @qc
     */
    function wordpatch_mailers()
    {
        return array(
            wordpatch_mailer_smtp(),
            wordpatch_mailer_jointbyte(),
            wordpatch_mailer_mail(),
            wordpatch_mailer_sendmail(),
        );
    }
}

if(!function_exists('wordpatch_calculate_mailer')) {
    function wordpatch_calculate_mailer($wpenv_vars)
    {
        $mailer = wordpatch_get_option($wpenv_vars, 'wordpatch_mailer', '');

        if(!wordpatch_is_valid_mailer($mailer)) {
            return null;
        }

        return $mailer;
    }
}

if(!function_exists('wordpatch_calculate_smtp_host')) {
    function wordpatch_calculate_smtp_host($wpenv_vars)
    {
        $smtp_host = wordpatch_get_option($wpenv_vars, 'wordpatch_smtp_host', '');
        $smtp_host = trim($smtp_host);

        if($smtp_host !== '') {
            return $smtp_host;
        }

        return null;
    }
}

if(!function_exists('wordpatch_calculate_smtp_user')) {
    function wordpatch_calculate_smtp_user($wpenv_vars)
    {
        $smtp_user = wordpatch_get_option($wpenv_vars, 'wordpatch_smtp_user', '');
        $smtp_user = trim($smtp_user);

        if($smtp_user !== '') {
            return $smtp_user;
        }

        return null;
    }
}

if(!function_exists('wordpatch_calculate_smtp_pass')) {
    function wordpatch_calculate_smtp_pass($wpenv_vars)
    {
        $smtp_pass = wordpatch_get_option($wpenv_vars, 'wordpatch_smtp_pass', '');

        if(trim($smtp_pass) !== '') {
            return $smtp_pass;
        }

        return null;
    }
}

if(!function_exists('wordpatch_calculate_mail_from')) {
    function wordpatch_calculate_mail_from($wpenv_vars)
    {
        $mail_from = wordpatch_get_option($wpenv_vars, 'wordpatch_mail_from', '');
        $mail_from = trim($mail_from);

        if($mail_from !== '') {
            return $mail_from;
        }

        return null;
    }
}

if(!function_exists('wordpatch_calculate_mail_to')) {
    function wordpatch_calculate_mail_to($wpenv_vars)
    {
        $mail_to = wordpatch_get_option($wpenv_vars, 'wordpatch_mail_to', '');
        $mail_to = trim($mail_to);

        if($mail_to !== '') {
            return $mail_to;
        }

        return null;
    }
}

if(!function_exists('wordpatch_calculate_smtp_port')) {
    function wordpatch_calculate_smtp_port($wpenv_vars)
    {
        $smtp_port = wordpatch_get_option($wpenv_vars, 'wordpatch_smtp_port', '');

        if(trim($smtp_port) !== '' && is_numeric($smtp_port) && max(0, (int)$smtp_port) > 0) {
            return (int)$smtp_port;
        }

        return null;
    }
}

if(!function_exists('wordpatch_calculate_smtp_ssl')) {
    function wordpatch_calculate_smtp_ssl($wpenv_vars)
    {
        $smtp_ssl = wordpatch_get_option($wpenv_vars, 'wordpatch_smtp_ssl', '');

        if(!wordpatch_is_valid_smtp_ssl($smtp_ssl)) {
            return null;
        }

        return $smtp_ssl;
    }
}

if(!function_exists('wordpatch_calculate_smtp_auth')) {
    function wordpatch_calculate_smtp_auth($wpenv_vars)
    {
        $smtp_auth = wordpatch_get_option($wpenv_vars, 'wordpatch_smtp_auth', '');

        if(!wordpatch_is_valid_smtp_auth($smtp_auth)) {
            return null;
        }

        return $smtp_auth;
    }
}

if(!function_exists('wordpatch_calculate_mail_configured')) {
    function wordpatch_calculate_mail_configured($wpenv_vars)
    {
        $db_val = wordpatch_get_option($wpenv_vars, 'wordpatch_mail_configured', WORDPATCH_NO);
        $db_val = trim($db_val);

        if($db_val !== '' && strtolower(trim($db_val)) === WORDPATCH_YES) {
            return WORDPATCH_YES;
        }

        return WORDPATCH_NO;
    }
}

if(!function_exists('wordpatch_calculate_mailer_info')) {
    function wordpatch_calculate_mailer_info($wpenv_vars) {
        $db_info = array();
        $db_info['mailer'] = wordpatch_calculate_mailer($wpenv_vars);
        $db_info['mail_from'] = wordpatch_calculate_mail_from($wpenv_vars);
        $db_info['mail_to'] = wordpatch_calculate_mail_to($wpenv_vars);
        $db_info['smtp_host'] = wordpatch_calculate_smtp_host($wpenv_vars);
        $db_info['smtp_port'] = wordpatch_calculate_smtp_port($wpenv_vars);
        $db_info['smtp_ssl'] = wordpatch_calculate_smtp_ssl($wpenv_vars);
        $db_info['smtp_auth'] = wordpatch_calculate_smtp_auth($wpenv_vars);
        $db_info['smtp_user'] = wordpatch_calculate_smtp_user($wpenv_vars);
        $db_info['smtp_pass'] = wordpatch_calculate_smtp_pass($wpenv_vars);

        return $db_info;
    }
}