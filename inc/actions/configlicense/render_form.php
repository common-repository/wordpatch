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
 * The following functions render the form for each step of the license configuration wizard.
 * These functions are called from `wordpatch_render_configlicense`.
 */

if(!function_exists('wordpatch_configlicense_render_form')) {
    /**
     * Render the license configuration wizard form.
     * Called by `wordpatch_render_configlicense`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $step_number
     * @param $wizard_vars
     * @param $step_text
     * @param $steps
     * @param $wizard_error_list
     */
    function wordpatch_configlicense_render_form($wpenv_vars, $current, $step_number, $wizard_vars, $step_text,
                                            $steps, $wizard_error_list)
    {
        ?>
        <div class="wordpatch_page_title_container">
            <div class="wordpatch_page_title_left">
                <h1 class="wordpatch_page_title"><?php echo __wte($wpenv_vars, 'CONFIGLICENSE_TITLE'); ?></h1>
            </div>
        </div>

        <form action="<?php echo(wordpatch_configlicense_uri($wpenv_vars)); ?>" method="POST">
            <?php echo(wordpatch_the_hidden_wizard_vars($step_number, $wizard_vars)); ?>
            <?php
            switch ($step_number) {
                case $steps['basic']:
                    wordpatch_configlicense_render_fields_basic($wpenv_vars, $current, $wizard_error_list);
                    break;

                case $steps['creds']:
                    wordpatch_configlicense_render_fields_creds($wpenv_vars, $current, $wizard_error_list);
                    break;

                case $steps['confirm']:
                    wordpatch_configlicense_render_fields_confirm($wpenv_vars, $current, $wizard_error_list);
                    break;
            }

            $buttons = wordpatch_configlicense_buttons($step_number, $wizard_error_list);
            wordpatch_wizard_render_buttons($wpenv_vars, $buttons);
            ?>
        </form>
        <?php
    }
}

if(!function_exists('wordpatch_configlicense_render_fields_basic')) {
    /**
     * Render the fields inside the basic step of the license configuration wizard.
     * Called by `wordpatch_configlicense_render_form`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configlicense_render_fields_basic($wpenv_vars, $current, $wizard_error_list)
    {
        // Set which errors are blocking
        $blocking_errors = wordpatch_configlicense_get_blocking_errors_basic();

        // Setup $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGLICENSE;
        $error_vars = array();

        // Try to draw any blocking errors
        $any_drawn = wordpatch_errors_maybe_draw_some($wpenv_vars, $wizard_error_list, $blocking_errors, $where,
            $error_vars);

        // If blocking errors are detected, then return out early.
        if($any_drawn) {
            return;
        }

        // Create an array for our message text
        $message_text = __wt($wpenv_vars, 'CONFIGLICENSE_BASIC');

        // If the license seems active already, change the message.
        if(in_array(WORDPATCH_LICENSE_SEEMS_ACTIVE, $wizard_error_list)) {
            $message_text = __wt($wpenv_vars, 'CONFIGLICENSE_BASIC_ALT');
        }

        // Render a messagebox open element.
        wordpatch_messagebox_render_open($wpenv_vars);

        // Render the appropriate message text.
        echo(htmlspecialchars($message_text));

        // Render a messagebox close element.
        wordpatch_messagebox_render_close($wpenv_vars);
    }
}

if(!function_exists('wordpatch_configlicense_render_fields_creds')) {
    /**
     * Render the fields inside the directories step of the license configuration wizard.
     * Called by `wordpatch_configlicense_render_form`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configlicense_render_fields_creds($wpenv_vars, $current, $wizard_error_list)
    {
        // Set which errors are blocking
        $blocking_errors = wordpatch_configlicense_get_blocking_errors_creds();

        // Setup $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGLICENSE;
        $error_vars = array();

        // Try to draw any blocking errors
        $any_drawn = wordpatch_errors_maybe_draw_some($wpenv_vars, $wizard_error_list, $blocking_errors, $where,
            $error_vars);

        // If blocking errors are detected, then return out early.
        if($any_drawn) {
            return;
        }

        // Draw the activation key field.
        wordpatch_configlicense_draw_activation_key_field($wpenv_vars, $current, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_configlicense_render_fields_confirm')) {
    /**
     * Render the fields inside the confirmation step of the license configuration wizard.
     * Called by `wordpatch_configlicense_render_form`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configlicense_render_fields_confirm($wpenv_vars, $current, $wizard_error_list)
    {
        $link = sprintf("<a href=\"%s\">%s</a>", wordpatch_configmail_uri($wpenv_vars),
            __wten($wpenv_vars, 'CONFIGLICENSE_CONFIRM_LINK'));

        $link2 = sprintf("<a href=\"%s\">%s</a>", wordpatch_dashboard_uri($wpenv_vars),
            __wten($wpenv_vars, 'CONFIGLICENSE_CONFIRM_LINK2'));

        wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'CONFIGLICENSE_CONFIRM_HEADING'),
            nl2br(__wt($wpenv_vars, 'CONFIGLICENSE_CONFIRM', $link, $link2)));
    }
}