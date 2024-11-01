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
 * The following functions render the fields for each step of the mail configuration wizard.
 * These functions are called from `wordpatch_configmail_render_fields_{$step_key}`.
 */

if(!function_exists('wordpatch_configmail_error_vars_for_draw_field')) {
    function wordpatch_configmail_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list)
    {
        return array('mailer' => $current['mailer']);
    }
}

if(!function_exists('wordpatch_configmail_draw_mailer_field')) {
    /**
     * Draw the mailer field.
     * Called by `wordpatch_configmail_render_fields_basic`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configmail_draw_mailer_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Setup our $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGMAIL;
        $error_vars = wordpatch_configmail_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list);

        // Define which errors are relevant to the field.
        $field_errors = array(WORDPATCH_INVALID_MAILER);

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'CONFIGMAIL_MAILER_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'CONFIGMAIL_MAILER_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'CONFIGMAIL_MAILER_LABEL');

        // Calculate our dropdown information.
        $dropdown = wordpatch_dropdown($wpenv_vars, wordpatch_mailers(), 'wordpatch_display_mailer');

        // Draw the select field.
        wordpatch_wizard_draw_field_select($wpenv_vars, 'wordpatch_mailer', $current['mailer'], $dropdown,
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_configmail_draw_mail_to_field')) {
    /**
     * Draw the mail to field.
     * Called by `wordpatch_configmail_render_fields_basic`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configmail_draw_mail_to_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Setup our $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGMAIL;
        $error_vars = wordpatch_configmail_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list);

        // Define which errors are relevant to the field.
        $field_errors = array(WORDPATCH_INVALID_MAIL_TO, WORDPATCH_MAIL_TO_REQUIRED);

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'CONFIGMAIL_MAIL_TO_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'CONFIGMAIL_MAIL_TO_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'CONFIGMAIL_MAIL_TO_LABEL');

        // Draw the text field.
        wordpatch_wizard_draw_field_text($wpenv_vars, 'wordpatch_mail_to', $current['mail_to'],
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_configmail_draw_mail_from_field')) {
    /**
     * Draw the mail from/reply-to field.
     * Called by `wordpatch_configmail_render_fields_basic`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configmail_draw_mail_from_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Setup our $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGMAIL;
        $error_vars = wordpatch_configmail_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list);

        // Define which errors are relevant to the field.
        $field_errors = array(WORDPATCH_INVALID_MAIL_FROM, WORDPATCH_MAIL_FROM_REQUIRED);

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'CONFIGMAIL_MAIL_FROM_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'CONFIGMAIL_MAIL_FROM_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'CONFIGMAIL_MAIL_FROM_LABEL');

        // Draw the text field.
        wordpatch_wizard_draw_field_text($wpenv_vars, 'wordpatch_mail_from', $current['mail_from'],
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_configmail_draw_smtp_host_field')) {
    /**
     * Draw the SMTP host field.
     * Called by `wordpatch_configmail_render_fields_smtp`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configmail_draw_smtp_host_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Setup our $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGMAIL;
        $error_vars = wordpatch_configmail_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list);

        // Define which errors are relevant to the field.
        $field_errors = array(WORDPATCH_SMTP_HOST_REQUIRED);

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'CONFIGMAIL_SMTP_HOST_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'CONFIGMAIL_SMTP_HOST_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'CONFIGMAIL_SMTP_HOST_LABEL');

        // Draw the text field.
        wordpatch_wizard_draw_field_text($wpenv_vars, 'wordpatch_smtp_host', $current['smtp_host'],
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_configmail_draw_smtp_port_field')) {
    /**
     * Draw the SMTP port field.
     * Called by `wordpatch_configmail_render_fields_smtp`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configmail_draw_smtp_port_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Setup our $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGMAIL;
        $error_vars = wordpatch_configmail_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list);

        // Define which errors are relevant to the field.
        $field_errors = array(WORDPATCH_SMTP_PORT_REQUIRED);

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'CONFIGMAIL_SMTP_PORT_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'CONFIGMAIL_SMTP_PORT_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'CONFIGMAIL_SMTP_PORT_LABEL');

        // Draw the text field.
        wordpatch_wizard_draw_field_text($wpenv_vars, 'wordpatch_smtp_port', $current['smtp_port'],
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_configmail_draw_smtp_ssl_field')) {
    /**
     * Draw the SMTP SSL field.
     * Called by `wordpatch_configmail_render_fields_basic`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configmail_draw_smtp_ssl_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Setup our $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGMAIL;
        $error_vars = wordpatch_configmail_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list);

        // Define which errors are relevant to the field.
        $field_errors = array(WORDPATCH_INVALID_SMTP_SSL);

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'CONFIGMAIL_SMTP_SSL_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'CONFIGMAIL_SMTP_SSL_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'CONFIGMAIL_SMTP_SSL_LABEL');

        // Calculate our dropdown information.
        $dropdown = wordpatch_dropdown($wpenv_vars, wordpatch_smtp_ssls(), 'wordpatch_display_smtp_ssl');

        // Draw the select field.
        wordpatch_wizard_draw_field_select($wpenv_vars, 'wordpatch_smtp_ssl', $current['smtp_ssl'], $dropdown,
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_configmail_draw_smtp_auth_field')) {
    /**
     * Draw the SMTP authentication field.
     * Called by `wordpatch_configmail_render_fields_basic`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configmail_draw_smtp_auth_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Setup our $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGMAIL;
        $error_vars = wordpatch_configmail_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list);

        // Define which errors are relevant to the field.
        $field_errors = array(WORDPATCH_INVALID_SMTP_AUTH);

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'CONFIGMAIL_SMTP_AUTH_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'CONFIGMAIL_SMTP_AUTH_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'CONFIGMAIL_SMTP_AUTH_LABEL');

        // Calculate our dropdown information.
        $dropdown = wordpatch_dropdown($wpenv_vars, wordpatch_smtp_auths(), 'wordpatch_display_smtp_auth');

        // Draw the select field.
        wordpatch_wizard_draw_field_select($wpenv_vars, 'wordpatch_smtp_auth', $current['smtp_auth'], $dropdown,
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_configmail_draw_smtp_user_field')) {
    /**
     * Draw the SMTP username field.
     * Called by `wordpatch_configmail_render_fields_smtp`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configmail_draw_smtp_user_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Setup our $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGMAIL;
        $error_vars = wordpatch_configmail_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list);

        // Define which errors are relevant to the field.
        $field_errors = array(WORDPATCH_SMTP_USER_REQUIRED);

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'CONFIGMAIL_SMTP_USER_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'CONFIGMAIL_SMTP_USER_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'CONFIGMAIL_SMTP_USER_LABEL');

        // Draw the text field.
        wordpatch_wizard_draw_field_text($wpenv_vars, 'wordpatch_smtp_user', $current['smtp_user'],
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_configmail_draw_smtp_pass_field')) {
    /**
     * Draw the SMTP password field.
     * Called by `wordpatch_configmail_render_fields_smtp`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configmail_draw_smtp_pass_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Setup our $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGMAIL;
        $error_vars = wordpatch_configmail_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list);

        // Define which errors are relevant to the field.
        $field_errors = array(WORDPATCH_SMTP_PASS_REQUIRED);

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'CONFIGMAIL_SMTP_PASS_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'CONFIGMAIL_SMTP_PASS_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'CONFIGMAIL_SMTP_PASS_LABEL');

        // Draw the text field.
        wordpatch_wizard_draw_field_text($wpenv_vars, 'wordpatch_smtp_pass', $current['smtp_pass'],
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}