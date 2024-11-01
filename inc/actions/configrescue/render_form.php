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
 * The following functions render the form for each step of the rescue configuration wizard.
 * These functions are called from `wordpatch_render_configrescue`.
 */

if(!function_exists('wordpatch_configrescue_render_form')) {
    /**
     * Render the rescue configuration wizard form.
     * Called by `wordpatch_render_configrescue`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $step_number
     * @param $wizard_vars
     * @param $step_text
     * @param $steps
     * @param $wizard_error_list
     */
    function wordpatch_configrescue_render_form($wpenv_vars, $current, $step_number, $wizard_vars, $step_text,
                                            $steps, $wizard_error_list)
    {
        ?>
        <div class="wordpatch_page_title_container">
            <div class="wordpatch_page_title_left">
                <h1 class="wordpatch_page_title"><?php echo __wte($wpenv_vars, 'CONFIGRESCUE_TITLE'); ?></h1>
            </div>
        </div>

        <form action="<?php echo(wordpatch_configrescue_uri($wpenv_vars)); ?>" method="POST">
            <?php echo(wordpatch_the_hidden_wizard_vars($step_number, $wizard_vars)); ?>
            <?php
            switch ($step_number) {
                case $steps['dirs']:
                    wordpatch_configrescue_render_fields_dirs($wpenv_vars, $current, $wizard_error_list);
                    break;

                case $steps['validate']:
                    wordpatch_configrescue_render_fields_validate($wpenv_vars, $current, $wizard_error_list);
                    break;

                case $steps['confirm']:
                    wordpatch_configrescue_render_fields_confirm($wpenv_vars, $current, $wizard_error_list);
                    break;
            }

            $buttons = wordpatch_configrescue_buttons($step_number, $wizard_error_list);
            wordpatch_wizard_render_buttons($wpenv_vars, $buttons);
            ?>
        </form>
        <?php
    }
}

if(!function_exists('wordpatch_configrescue_render_fields_dirs')) {
    /**
     * Render the fields inside the directories step of the rescue configuration wizard.
     * Called by `wordpatch_configrescue_render_form`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configrescue_render_fields_dirs($wpenv_vars, $current, $wizard_error_list)
    {
        // Set which errors are blocking
        $blocking_errors = wordpatch_configrescue_get_blocking_errors_dirs();

        // Setup $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGRESCUE;
        $error_vars = array();

        // Try to draw any blocking errors
        $any_drawn = wordpatch_errors_maybe_draw_some($wpenv_vars, $wizard_error_list, $blocking_errors, $where,
            $error_vars);

        // If blocking errors are detected, then return out early.
        if($any_drawn) {
            return;
        }

        // Draw the FTP base field.
        wordpatch_configrescue_draw_rescue_path_field($wpenv_vars, $current, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_configrescue_render_fields_validate')) {
    /**
     * Render the fields inside the validation step of the rescue configuration wizard.
     * Called by `wordpatch_configrescue_render_form`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configrescue_render_fields_validate($wpenv_vars, $current, $wizard_error_list)
    {
        // Set which errors are blocking (all errors are blocking since null)
        $blocking_errors = wordpatch_configrescue_get_blocking_errors_validate();

        // Setup $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGRESCUE;
        $error_vars = array();

        // Try to draw any blocking errors
        $any_drawn = wordpatch_errors_maybe_draw_some($wpenv_vars, $wizard_error_list, $blocking_errors, $where,
            $error_vars);

        // If blocking errors are detected, then return out early.
        if($any_drawn) {
            return;
        }

        // Calculate a preview of our rescue script for display.
        $rescue_preview_html = nl2br(preg_replace('/ /', '&nbsp;', htmlspecialchars(wordpatch_rescue_generate($wpenv_vars,
            $current['rescue_path']))));

        ?>
        <div class="wordpatch_metabox">
            <div class="wordpatch_metabox_header">
                <?php echo(__wte($wpenv_vars, 'CONFIGRESCUE_VALIDATE_HEADING')); ?>
            </div>
            <div class="wordpatch_metabox_body">
                <strong><?php echo(__wte($wpenv_vars, 'CONFIGRESCUE_VALIDATE_STRONG')); ?></strong> <?php echo(__wte($wpenv_vars, 'CONFIGRESCUE_VALIDATE')); ?>
                <br/>
                <br/>

                <div class="wordpatch_previewrescue">
                    <?php echo($rescue_preview_html); ?>
                </div>
            </div>
        </div>
        <?php
    }
}

if(!function_exists('wordpatch_configrescue_render_fields_confirm')) {
    /**
     * Render the fields inside the confirmation step of the rescue configuration wizard.
     * Called by `wordpatch_configrescue_render_form`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configrescue_render_fields_confirm($wpenv_vars, $current, $wizard_error_list)
    {
        $link = sprintf("<a href=\"%s\">%s</a>", wordpatch_configlicense_uri($wpenv_vars),
            __wten($wpenv_vars, 'CONFIGRESCUE_CONFIRM_LINK'));

        $link2 = sprintf("<a href=\"%s\">%s</a>", wordpatch_dashboard_uri($wpenv_vars),
            __wten($wpenv_vars, 'CONFIGRESCUE_CONFIRM_LINK2'));

        wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'CONFIGRESCUE_CONFIRM_HEADING'),
            nl2br(__wt($wpenv_vars, 'CONFIGRESCUE_CONFIRM', $link, $link2)));
    }
}