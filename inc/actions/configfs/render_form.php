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
 * The following functions render the form for each step of the filesystem configuration wizard.
 * These functions are called from `wordpatch_render_configfs`.
 */

if(!function_exists('wordpatch_configfs_render_form')) {
    /**
     * Render the filesystem configuration wizard form.
     * Called by `wordpatch_render_configfs`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $step_number
     * @param $wizard_vars
     * @param $step_text
     * @param $steps
     * @param $wizard_error_list
     */
    function wordpatch_configfs_render_form($wpenv_vars, $current, $step_number, $wizard_vars, $step_text,
                                            $steps, $wizard_error_list)
    {
        ?>
        <div class="wordpatch_page_title_container">
            <div class="wordpatch_page_title_left">
                <h1 class="wordpatch_page_title"><?php echo __wte($wpenv_vars, 'CONFIGFS_TITLE'); ?></h1>
            </div>
        </div>

        <form action="<?php echo(wordpatch_configfs_uri($wpenv_vars)); ?>" method="POST">
            <?php echo(wordpatch_the_hidden_wizard_vars($step_number, $wizard_vars)); ?>
            <?php
            switch ($step_number) {
                case $steps['basic']:
                    wordpatch_configfs_render_fields_basic($wpenv_vars, $current, $wizard_error_list);
                    break;
                case $steps['dirs']:
                    wordpatch_configfs_render_fields_dirs($wpenv_vars, $current, $wizard_error_list);
                    break;
                case $steps['creds']:
                    wordpatch_configfs_render_fields_creds($wpenv_vars, $current, $wizard_error_list);
                    break;
                case $steps['validate']:
                    wordpatch_configfs_render_fields_validate($wpenv_vars, $current, $wizard_error_list);
                    break;
                case $steps['confirm']:
                    wordpatch_configfs_render_fields_confirm($wpenv_vars, $current, $wizard_error_list);
                    break;
            }

            $buttons = wordpatch_configfs_buttons($step_number, $current['fs_method'], $wizard_error_list);
            wordpatch_wizard_render_buttons($wpenv_vars, $buttons);
            ?>
        </form>
        <?php
    }
}

if(!function_exists('wordpatch_configfs_render_fields_basic')) {
    /**
     * Render the fields inside the basic step of the filesystem configuration wizard.
     * Called by `wordpatch_configfs_render_form`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configfs_render_fields_basic($wpenv_vars, $current, $wizard_error_list) {
        // Draw the filesystem method field.
        wordpatch_configfs_draw_fs_method_field($wpenv_vars, $current, $wizard_error_list);

        // Draw the filesystem timeout field.
        wordpatch_configfs_draw_fs_timeout_field($wpenv_vars, $current, $wizard_error_list);

        // Draw the filesystem CHMOD file field.
        wordpatch_configfs_draw_fs_chmod_file_field($wpenv_vars, $current, $wizard_error_list);

        // Draw the filesystem CHMOD directory field.
        wordpatch_configfs_draw_fs_chmod_dir_field($wpenv_vars, $current, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_configfs_render_fields_creds')) {
    /**
     * Render the fields inside the credentials step of the filesystem configuration wizard.
     * Called by `wordpatch_configfs_render_form`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configfs_render_fields_creds($wpenv_vars, $current, $wizard_error_list)
    {
        // Draw the FTP/SSH host field.
        wordpatch_configfs_draw_ftp_host_field($wpenv_vars, $current, $wizard_error_list);

        // Draw the FTP/SSH user field.
        wordpatch_configfs_draw_ftp_user_field($wpenv_vars, $current, $wizard_error_list);

        // Draw the FTP/SSH password field.
        wordpatch_configfs_draw_ftp_pass_field($wpenv_vars, $current, $wizard_error_list);

        // Check if the filesystem method is `ssh2`.
        if ($current['fs_method'] === wordpatch_fs_method_ssh2()) {
            // Draw the SSH public key field.
            wordpatch_configfs_draw_ftp_pubkey_field($wpenv_vars, $current, $wizard_error_list);

            // Draw the SSH public key field.
            wordpatch_configfs_draw_ftp_prikey_field($wpenv_vars, $current, $wizard_error_list);
        }

        // Check if the filesystem method is `ftpext`.
        if ($current['fs_method'] === wordpatch_fs_method_ftpext()) {
            // Draw the FTP SSL field.
            wordpatch_configfs_draw_ftp_ssl_field($wpenv_vars, $current, $wizard_error_list);
        }
    }
}

if(!function_exists('wordpatch_configfs_render_fields_dirs')) {
    /**
     * Render the fields inside the directories step of the filesystem configuration wizard.
     * Called by `wordpatch_configfs_render_form`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configfs_render_fields_dirs($wpenv_vars, $current, $wizard_error_list)
    {
        // Set which errors are blocking
        $blocking_errors = wordpatch_configfs_get_blocking_errors_dirs();

        // Setup $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGFS;
        $error_vars = array('current_fs_method' => $current['fs_method']);

        // Try to draw any blocking errors
        $any_drawn = wordpatch_errors_maybe_draw_some($wpenv_vars, $wizard_error_list, $blocking_errors, $where,
            $error_vars);

        // If blocking errors are detected, then return out early.
        if($any_drawn) {
            return;
        }

        // Render a messagebox open element.
        wordpatch_messagebox_render_open($wpenv_vars);

        // Render the directories message.
        echo(__wte($wpenv_vars, 'CONFIGFS_DIRS'));

        // Render a messagebox close element.
        wordpatch_messagebox_render_close($wpenv_vars);

        // Draw the FTP base field.
        wordpatch_configfs_draw_ftp_base_field($wpenv_vars, $current, $wizard_error_list);

        // Draw the FTP content directory field.
        wordpatch_configfs_draw_ftp_content_dir_field($wpenv_vars, $current, $wizard_error_list);

        // Draw the FTP plugin directory field.
        wordpatch_configfs_draw_ftp_plugin_dir_field($wpenv_vars, $current, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_configfs_render_fields_validate')) {
    /**
     * Render the fields inside the validation step of the filesystem configuration wizard.
     * Called by `wordpatch_configfs_render_form`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configfs_render_fields_validate($wpenv_vars, $current, $wizard_error_list)
    {
        // Set which errors are blocking (null means all errors are blocking)
        $blocking_errors = wordpatch_configfs_get_blocking_errors_validate();

        // Setup $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGFS;
        $error_vars = array('current_fs_method' => $current['fs_method']);

        // Try to draw any blocking errors
        $any_drawn = wordpatch_errors_maybe_draw_some($wpenv_vars, $wizard_error_list, $blocking_errors, $where,
            $error_vars);

        // If blocking errors are detected, then return out early.
        if($any_drawn) {
            return;
        }

        wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'CONFIGFS_VALIDATE_HEADING'),
            __wte($wpenv_vars, 'CONFIGFS_VALIDATE'));
    }
}

if(!function_exists('wordpatch_configfs_render_fields_confirm')) {
    /**
     * Render the fields inside the confirmation step of the filesystem configuration wizard.
     * Called by `wordpatch_configfs_render_form`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configfs_render_fields_confirm($wpenv_vars, $current, $wizard_error_list)
    {
        $link = sprintf("<a href=\"%s\">%s</a>", wordpatch_configrescue_uri($wpenv_vars),
            __wten($wpenv_vars, 'CONFIGFS_CONFIRM_LINK'));

        wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'CONFIGFS_CONFIRM_HEADING'),
            nl2br(__wt($wpenv_vars, 'CONFIGFS_CONFIRM', $link)));
    }
}
