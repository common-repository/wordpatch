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
 * The following functions render the form for each step of the mail configuration wizard.
 * These functions are called from `wordpatch_render_configmail`.
 */

if(!function_exists('wordpatch_configmail_render_form')) {
    /**
     * Render the mail configuration wizard form.
     * Called by `wordpatch_render_configmail`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $step_number
     * @param $wizard_vars
     * @param $step_text
     * @param $steps
     * @param $wizard_error_list
     */
    function wordpatch_configmail_render_form($wpenv_vars, $current, $step_number, $wizard_vars, $step_text,
                                            $steps, $wizard_error_list)
    {
        ?>
        <div class="wordpatch_page_title_container">
            <div class="wordpatch_page_title_left">
                <h1 class="wordpatch_page_title"><?php echo __wte($wpenv_vars, 'CONFIGMAIL_TITLE'); ?></h1>
            </div>
        </div>

        <form action="<?php echo(wordpatch_configmail_uri($wpenv_vars)); ?>" method="POST">
            <?php echo(wordpatch_the_hidden_wizard_vars($step_number, $wizard_vars)); ?>
            <?php
            switch ($step_number) {
                case $steps['basic']:
                    wordpatch_configmail_render_fields_basic($wpenv_vars, $current, $wizard_error_list);
                    break;

                case $steps['smtp']:
                    wordpatch_configmail_render_fields_smtp($wpenv_vars, $current, $wizard_error_list);
                    break;
                    
                case $steps['creds']:
                    wordpatch_configmail_render_fields_creds($wpenv_vars, $current, $wizard_error_list);
                    break;

                case $steps['confirm']:
                    wordpatch_configmail_render_fields_confirm($wpenv_vars, $current, $wizard_error_list);
                    break;
            }

            $buttons = wordpatch_configmail_buttons($step_number, $current['mailer'], $current['smtp_auth'], $wizard_error_list);
            wordpatch_wizard_render_buttons($wpenv_vars, $buttons);
            ?>
        </form>
        <?php
    }
}

if(!function_exists('wordpatch_configmail_render_fields_basic')) {
    /**
     * Render the fields inside the basic step of the mail configuration wizard.
     * Called by `wordpatch_configmail_render_form`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configmail_render_fields_basic($wpenv_vars, $current, $wizard_error_list)
    {
        // Set which errors are blocking
        $blocking_errors = wordpatch_configmail_get_blocking_errors_basic();

        // Setup $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGMAIL;
        $error_vars = array();

        // Try to draw any blocking errors
        $any_drawn = wordpatch_errors_maybe_draw_some($wpenv_vars, $wizard_error_list, $blocking_errors, $where,
            $error_vars);

        // If blocking errors are detected, then return out early.
        if($any_drawn) {
            return;
        }

        // Draw any errors that aren't related to the fields
        $field_errors = array(WORDPATCH_INVALID_MAILER, WORDPATCH_MAIL_TO_REQUIRED, WORDPATCH_INVALID_MAIL_TO,
            WORDPATCH_MAIL_FROM_REQUIRED, WORDPATCH_INVALID_MAIL_FROM);

        wordpatch_errors_maybe_draw_others($wpenv_vars, $wizard_error_list, $field_errors, $where,
            $error_vars);

        // Draw the mailer field.
        wordpatch_configmail_draw_mailer_field($wpenv_vars, $current, $wizard_error_list);

        // Draw the mail to field.
        wordpatch_configmail_draw_mail_to_field($wpenv_vars, $current, $wizard_error_list);

        // Draw the mail from field.
        wordpatch_configmail_draw_mail_from_field($wpenv_vars, $current, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_configmail_render_fields_smtp')) {
    /**
     * Render the fields inside the SMTP step of the mail configuration wizard.
     * Called by `wordpatch_configmail_render_form`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configmail_render_fields_smtp($wpenv_vars, $current, $wizard_error_list)
    {
        // Draw the SMTP host field.
        wordpatch_configmail_draw_smtp_host_field($wpenv_vars, $current, $wizard_error_list);

        // Draw the SMTP port field.
        wordpatch_configmail_draw_smtp_port_field($wpenv_vars, $current, $wizard_error_list);

        // Draw the SMTP SSL field.
        wordpatch_configmail_draw_smtp_ssl_field($wpenv_vars, $current, $wizard_error_list);

        // Draw the SMTP authentication field.
        wordpatch_configmail_draw_smtp_auth_field($wpenv_vars, $current, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_configmail_render_fields_creds')) {
    /**
     * Render the fields inside the credentials step of the mail configuration wizard.
     * Called by `wordpatch_configmail_render_form`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configmail_render_fields_creds($wpenv_vars, $current, $wizard_error_list)
    {
        // Set which errors are related to the fields on this page so we can print the others.
        $field_errors = array(WORDPATCH_SMTP_USER_REQUIRED, WORDPATCH_SMTP_PASS_REQUIRED);

        // Setup $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGMAIL;
        $error_vars = array();

        // Try to draw any non-field errors
        wordpatch_errors_maybe_draw_others($wpenv_vars, $wizard_error_list, $field_errors, $where,
            $error_vars);

        // Draw the SMTP username field.
        wordpatch_configmail_draw_smtp_user_field($wpenv_vars, $current, $wizard_error_list);

        // Draw the SMTP password field.
        wordpatch_configmail_draw_smtp_pass_field($wpenv_vars, $current, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_configmail_render_fields_confirm')) {
    /**
     * Render the fields inside the confirmation step of the mail configuration wizard.
     * Called by `wordpatch_configmail_render_form`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configmail_render_fields_confirm($wpenv_vars, $current, $wizard_error_list)
    {
        $link = sprintf("<a href=\"https://jointbyte.freshdesk.com/\" target=\"_blank\">%s</a>",
            __wten($wpenv_vars, 'CONFIGMAIL_CONFIRM_LINK'));

        $link2 = sprintf("<a href=\"%s\">%s</a>", wordpatch_newjob_uri($wpenv_vars),
            __wten($wpenv_vars, 'CONFIGMAIL_CONFIRM_LINK2'));

        $link3 = sprintf("<a href=\"%s\">%s</a>", wordpatch_dashboard_uri($wpenv_vars),
            __wten($wpenv_vars, 'CONFIGMAIL_CONFIRM_LINK3'));

        wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'CONFIGMAIL_CONFIRM_HEADING'),
            nl2br(__wt($wpenv_vars, 'CONFIGMAIL_CONFIRM', $link, $link2, $link3)));
    }
}