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
 * The following functions render the form for each step of the database configuration wizard.
 * These functions are called from `wordpatch_render_configdb`.
 */

if(!function_exists('wordpatch_configdb_render_form')) {
    /**
     * Render the database configuration wizard form.
     * Called by `wordpatch_render_configdb`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $step_number
     * @param $wizard_vars
     * @param $step_text
     * @param $steps
     * @param $wizard_error_list
     */
    function wordpatch_configdb_render_form($wpenv_vars, $current, $step_number, $wizard_vars, $step_text,
                                            $steps, $wizard_error_list)
    {
        ?>
        <div class="wordpatch_page_title_container">
            <div class="wordpatch_page_title_left">
                <h1 class="wordpatch_page_title"><?php echo __wte($wpenv_vars, 'CONFIGDB_TITLE'); ?></h1>
            </div>
        </div>

        <form action="<?php echo(wordpatch_configdb_uri($wpenv_vars)); ?>" method="POST">
            <?php echo(wordpatch_the_hidden_wizard_vars($step_number, $wizard_vars)); ?>
            <?php
            switch ($step_number) {
                case $steps['creds']:
                    wordpatch_configdb_render_fields_creds($wpenv_vars, $current, $wizard_error_list);
                    break;
                case $steps['validate']:
                    wordpatch_configdb_render_fields_validate($wpenv_vars, $current, $wizard_error_list);
                    break;
                case $steps['confirm']:
                    wordpatch_configdb_render_fields_confirm($wpenv_vars, $current, $wizard_error_list);
                    break;
            }

            $buttons = wordpatch_configdb_buttons($step_number, $wizard_error_list);
            wordpatch_wizard_render_buttons($wpenv_vars, $buttons);
            ?>
        </form>
        <?php
    }
}

if(!function_exists('wordpatch_configdb_render_fields_creds')) {
    /**
     * Render the fields inside the credentials step of the database configuration wizard.
     * Called by `wordpatch_configdb_render_form`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configdb_render_fields_creds($wpenv_vars, $current, $wizard_error_list)
    {
        // Draw the database host field.
        wordpatch_configdb_draw_db_host_field($wpenv_vars, $current, $wizard_error_list);

        // Draw the database name field.
        wordpatch_configdb_draw_db_name_field($wpenv_vars, $current, $wizard_error_list);

        // Draw the database user field.
        wordpatch_configdb_draw_db_user_field($wpenv_vars, $current, $wizard_error_list);

        // Draw the database password field.
        wordpatch_configdb_draw_db_password_field($wpenv_vars, $current, $wizard_error_list);

        // Draw the database table prefix field.
        wordpatch_configdb_draw_db_table_prefix_field($wpenv_vars, $current, $wizard_error_list);

        // Draw the database collate field.
        wordpatch_configdb_draw_db_collate_field($wpenv_vars, $current, $wizard_error_list);

        // Draw the database charset field.
        wordpatch_configdb_draw_db_charset_field($wpenv_vars, $current, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_configdb_render_fields_validate')) {
    /**
     * Render the fields inside the validation step of the database configuration wizard.
     * Called by `wordpatch_configdb_render_form`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configdb_render_fields_validate($wpenv_vars, $current, $wizard_error_list)
    {
        // Set which errors are blocking (null means all errors are blocking)
        $blocking_errors = null;

        // Setup $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGDB;
        $error_vars = array();

        // Try to draw any blocking errors
        $any_drawn = wordpatch_errors_maybe_draw_some($wpenv_vars, $wizard_error_list, $blocking_errors, $where,
            $error_vars);

        // If blocking errors are detected, then return out early.
        if($any_drawn) {
            return;
        }

        wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'CONFIGDB_VALIDATE_HEADING'),
            __wte($wpenv_vars, 'CONFIGDB_VALIDATE'));
    }
}

if(!function_exists('wordpatch_configdb_render_fields_confirm')) {
    /**
     * Render the fields inside the confirmation step of the database configuration wizard.
     * Called by `wordpatch_configdb_render_form`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configdb_render_fields_confirm($wpenv_vars, $current, $wizard_error_list)
    {
        $link = sprintf("<a href=\"%s\">%s</a>", wordpatch_configfs_uri($wpenv_vars),
            __wten($wpenv_vars, 'CONFIGDB_CONFIRM_LINK'));

        wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'CONFIGDB_CONFIRM_HEADING'),
            nl2br(__wt($wpenv_vars, 'CONFIGDB_CONFIRM', $link)));
    }
}