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
 * The following functions render the fields for each step of the filesystem configuration wizard.
 * These functions are called from `wordpatch_configfs_render_fields_{$step_key}`.
 */

if(!function_exists('wordpatch_configfs_error_vars_for_draw_field')) {
    function wordpatch_configfs_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list)
    {
        return array('current_fs_method' => $current['fs_method']);
    }
}

if(!function_exists('wordpatch_configfs_draw_fs_method_field')) {
    /**
     * Draw the filesystem method field.
     * Called by `wordpatch_configfs_render_fields_basic`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configfs_draw_fs_method_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Setup our $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGFS;
        $error_vars = wordpatch_configfs_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list);

        // Define which errors are relevant to the field.
        $field_errors = array(WORDPATCH_INVALID_FS_METHOD);

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'CONFIGFS_FS_METHOD_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'CONFIGFS_FS_METHOD_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'CONFIGFS_FS_METHOD_LABEL');

        // Calculate our dropdown information.
        $dropdown = wordpatch_dropdown($wpenv_vars, wordpatch_fs_methods(), 'wordpatch_display_fs_method');

        // Draw the select field.
        wordpatch_wizard_draw_field_select($wpenv_vars, 'wordpatch_fs_method', $current['fs_method'], $dropdown,
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_configfs_draw_fs_timeout_field')) {
    /**
     * Draw the filesystem timeout field.
     * Called by `wordpatch_configfs_render_fields_basic`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configfs_draw_fs_timeout_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Setup our $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGFS;
        $error_vars = wordpatch_configfs_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list);

        // Define which errors are relevant to the field.
        $field_errors = array(WORDPATCH_INVALID_FS_TIMEOUT);

        // Calculate the display timeout value.
        $display_fs_timeout = $current['fs_timeout'] === null ? '' : $current['fs_timeout'];

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'CONFIGFS_FS_TIMEOUT_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'CONFIGFS_FS_TIMEOUT_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'CONFIGFS_FS_TIMEOUT_LABEL');

        // Draw the text field.
        wordpatch_wizard_draw_field_text($wpenv_vars, 'wordpatch_fs_timeout', $display_fs_timeout,
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_configfs_draw_fs_chmod_file_field')) {
    /**
     * Draw the filesystem CHMOD file field.
     * Called by `wordpatch_configfs_render_fields_basic`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configfs_draw_fs_chmod_file_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Setup our $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGFS;
        $error_vars = wordpatch_configfs_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list);

        // Define which errors are relevant to the field.
        $field_errors = array(WORDPATCH_FS_CHMOD_FILE_REQUIRED);

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'CONFIGFS_FS_CHMOD_FILE_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'CONFIGFS_FS_CHMOD_FILE_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'CONFIGFS_FS_CHMOD_FILE_LABEL');

        // Draw the text field.
        wordpatch_wizard_draw_field_text($wpenv_vars, 'wordpatch_fs_chmod_file', $current['fs_chmod_file'],
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_configfs_draw_fs_chmod_dir_field')) {
    /**
     * Draw the filesystem CHMOD directory field.
     * Called by `wordpatch_configfs_render_fields_basic`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configfs_draw_fs_chmod_dir_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Setup our $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGFS;
        $error_vars = wordpatch_configfs_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list);

        // Define which errors are relevant to the field.
        $field_errors = array(WORDPATCH_FS_CHMOD_DIR_REQUIRED);

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'CONFIGFS_FS_CHMOD_DIR_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'CONFIGFS_FS_CHMOD_DIR_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'CONFIGFS_FS_CHMOD_DIR_LABEL');

        // Draw the text field.
        wordpatch_wizard_draw_field_text($wpenv_vars, 'wordpatch_fs_chmod_dir', $current['fs_chmod_dir'],
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_configfs_draw_ftp_host_field')) {
    /**
     * Draw the FTP host field.
     * Called by `wordpatch_configfs_render_fields_creds`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configfs_draw_ftp_host_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Setup our $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGFS;
        $error_vars = wordpatch_configfs_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list);

        // Define which errors are relevant to the field.
        $field_errors = array(WORDPATCH_FTP_HOST_REQUIRED);

        // Calculate the header text.
        $header_text = $current['fs_method'] === wordpatch_fs_method_ssh2() ?
            __wt($wpenv_vars, 'CONFIGFS_SSH_HOST_HEADER') : __wt($wpenv_vars, 'CONFIGFS_FTP_HOST_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'CONFIGFS_FTP_HOST_DESC');

        if($current['fs_method'] === wordpatch_fs_method_ssh2()) {
            $desc_text = __wt($wpenv_vars, 'CONFIGFS_SSH_HOST_DESC');
        }

        // Calculate the label text.
        $label_text = $current['fs_method'] === wordpatch_fs_method_ssh2() ?
            __wt($wpenv_vars, 'CONFIGFS_SSH_HOST_LABEL') : __wt($wpenv_vars, 'CONFIGFS_FTP_HOST_LABEL');

        // Draw the text field.
        wordpatch_wizard_draw_field_text($wpenv_vars, 'wordpatch_ftp_host', $current['ftp_host'],
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_configfs_draw_ftp_user_field')) {
    /**
     * Draw the FTP user field.
     * Called by `wordpatch_configfs_render_fields_creds`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configfs_draw_ftp_user_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Setup our $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGFS;
        $error_vars = wordpatch_configfs_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list);

        // Define which errors are relevant to the field.
        $field_errors = array(WORDPATCH_FTP_USER_REQUIRED);

        // Calculate the header text.
        $header_text = $current['fs_method'] === wordpatch_fs_method_ssh2() ?
            __wt($wpenv_vars, 'CONFIGFS_SSH_USER_HEADER') : __wt($wpenv_vars, 'CONFIGFS_FTP_USER_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'CONFIGFS_FTP_USER_DESC');

        if($current['fs_method'] === wordpatch_fs_method_ssh2()) {
            $desc_text = __wt($wpenv_vars, 'CONFIGFS_SSH_USER_DESC');
        }

        // Calculate the label text.
        $label_text = $current['fs_method'] === wordpatch_fs_method_ssh2() ?
            __wt($wpenv_vars, 'CONFIGFS_SSH_USER_LABEL') : __wt($wpenv_vars, 'CONFIGFS_FTP_USER_LABEL');

        // Draw the text field.
        wordpatch_wizard_draw_field_text($wpenv_vars, 'wordpatch_ftp_user', $current['ftp_user'],
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_configfs_draw_ftp_pass_field')) {
    /**
     * Draw the FTP pass field.
     * Called by `wordpatch_configfs_render_fields_creds`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configfs_draw_ftp_pass_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Setup our $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGFS;
        $error_vars = wordpatch_configfs_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list);

        // Define which errors are relevant to the field.
        $field_errors = array(WORDPATCH_FTP_PASS_REQUIRED);

        // Calculate the header text.
        $header_text = $current['fs_method'] === wordpatch_fs_method_ssh2() ?
            __wt($wpenv_vars, 'CONFIGFS_SSH_PASS_HEADER') : __wt($wpenv_vars, 'CONFIGFS_FTP_PASS_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'CONFIGFS_FTP_PASS_DESC');

        if($current['fs_method'] === wordpatch_fs_method_ssh2()) {
            $desc_text = __wt($wpenv_vars, 'CONFIGFS_SSH_PASS_DESC');
        }

        // Calculate the label text.
        $label_text = $current['fs_method'] === wordpatch_fs_method_ssh2() ?
            __wt($wpenv_vars, 'CONFIGFS_SSH_PASS_LABEL') : __wt($wpenv_vars, 'CONFIGFS_FTP_PASS_LABEL');

        // Draw the text field.
        wordpatch_wizard_draw_field_password($wpenv_vars, 'wordpatch_ftp_pass',
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_configfs_draw_ftp_pubkey_field')) {
    /**
     * Draw the SSH public key field.
     * Called by `wordpatch_configfs_render_fields_creds`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configfs_draw_ftp_pubkey_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Setup our $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGFS;
        $error_vars = wordpatch_configfs_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list);

        // Define which errors are relevant to the field.
        $field_errors = array(WORDPATCH_FTP_SSHKEY_INDECISIVE);

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'CONFIGFS_SSH_PUBKEY_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'CONFIGFS_SSH_PUBKEY_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'CONFIGFS_SSH_PUBKEY_LABEL');

        // Draw the text field.
        wordpatch_wizard_draw_field_text($wpenv_vars, 'wordpatch_ftp_pubkey', $current['ftp_pubkey'],
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_configfs_draw_ftp_prikey_field')) {
    /**
     * Draw the SSH private key field.
     * Called by `wordpatch_configfs_render_fields_creds`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configfs_draw_ftp_prikey_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Setup our $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGFS;
        $error_vars = wordpatch_configfs_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list);

        // Define which errors are relevant to the field.
        $field_errors = array(WORDPATCH_FTP_SSHKEY_INDECISIVE);

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'CONFIGFS_SSH_PRIKEY_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'CONFIGFS_SSH_PRIKEY_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'CONFIGFS_SSH_PRIKEY_LABEL');

        // Draw the text field.
        wordpatch_wizard_draw_field_text($wpenv_vars, 'wordpatch_ftp_prikey', $current['ftp_prikey'],
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_configfs_draw_ftp_ssl_field')) {
    /**
     * Draw the FTP SSL field.
     * Called by `wordpatch_configfs_render_fields_creds`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configfs_draw_ftp_ssl_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Setup our $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGFS;
        $error_vars = wordpatch_configfs_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list);

        // Define which errors are relevant to the field.
        $field_errors = array();

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'CONFIGFS_FTP_SSL_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'CONFIGFS_FTP_SSL_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'CONFIGFS_FTP_SSL_LABEL');

        // Calculate our dropdown information.
        $dropdown = wordpatch_dropdown($wpenv_vars, wordpatch_ftp_ssls(), 'wordpatch_display_ftp_ssl');

        // Draw the select field.
        wordpatch_wizard_draw_field_select($wpenv_vars, 'wordpatch_ftp_ssl', $current['ftp_ssl'], $dropdown,
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_configfs_draw_ftp_base_field')) {
    /**
     * Draw the FTP base field.
     * Called by `wordpatch_configfs_render_fields_dirs`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configfs_draw_ftp_base_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Setup our $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGFS;
        $error_vars = wordpatch_configfs_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list);

        // Define which errors are relevant to the field.
        $field_errors = array(WORDPATCH_FTP_BASE_REQUIRED);

        // Calculate the header text.
        $header_text = $current['fs_method'] === wordpatch_fs_method_ssh2() ?
            __wt($wpenv_vars, 'CONFIGFS_SSH_BASE_HEADER') : __wt($wpenv_vars, 'CONFIGFS_FTP_BASE_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'CONFIGFS_FTP_BASE_DESC');

        if($current['fs_method'] === wordpatch_fs_method_ssh2()) {
            $desc_text = __wt($wpenv_vars, 'CONFIGFS_SSH_BASE_DESC');
        }

        // Calculate the label text.
        $label_text = $current['fs_method'] === wordpatch_fs_method_ssh2() ?
            __wt($wpenv_vars, 'CONFIGFS_SSH_BASE_LABEL') : __wt($wpenv_vars, 'CONFIGFS_FTP_BASE_LABEL');

        // Draw the text field.
        wordpatch_wizard_draw_field_text($wpenv_vars, 'wordpatch_ftp_base', $current['ftp_base'],
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_configfs_draw_ftp_content_dir_field')) {
    /**
     * Draw the FTP content directory field.
     * Called by `wordpatch_configfs_render_fields_dirs`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configfs_draw_ftp_content_dir_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Setup our $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGFS;
        $error_vars = wordpatch_configfs_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list);

        // Define which errors are relevant to the field.
        $field_errors = array(WORDPATCH_FTP_CONTENT_DIR_REQUIRED);

        // Calculate the header text.
        $header_text = $current['fs_method'] === wordpatch_fs_method_ssh2() ?
            __wt($wpenv_vars, 'CONFIGFS_SSH_CONTENT_DIR_HEADER') : __wt($wpenv_vars, 'CONFIGFS_FTP_CONTENT_DIR_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'CONFIGFS_FTP_CONTENT_DIR_DESC');

        if($current['fs_method'] === wordpatch_fs_method_ssh2()) {
            $desc_text = __wt($wpenv_vars, 'CONFIGFS_SSH_CONTENT_DIR_DESC');
        }

        // Calculate the label text.
        $label_text = $current['fs_method'] === wordpatch_fs_method_ssh2() ?
            __wt($wpenv_vars, 'CONFIGFS_SSH_CONTENT_DIR_LABEL') : __wt($wpenv_vars, 'CONFIGFS_FTP_CONTENT_DIR_LABEL');

        // Draw the text field.
        wordpatch_wizard_draw_field_text($wpenv_vars, 'wordpatch_ftp_content_dir', $current['ftp_content_dir'],
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_configfs_draw_ftp_plugin_dir_field')) {
    /**
     * Draw the FTP plugin directory field.
     * Called by `wordpatch_configfs_render_fields_dirs`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configfs_draw_ftp_plugin_dir_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Setup our $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGFS;
        $error_vars = wordpatch_configfs_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list);

        // Define which errors are relevant to the field.
        $field_errors = array(WORDPATCH_FTP_PLUGIN_DIR_REQUIRED);

        // Calculate the header text.
        $header_text = $current['fs_method'] === wordpatch_fs_method_ssh2() ?
            __wt($wpenv_vars, 'CONFIGFS_SSH_PLUGIN_DIR_HEADER') : __wt($wpenv_vars, 'CONFIGFS_FTP_PLUGIN_DIR_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'CONFIGFS_FTP_PLUGIN_DIR_DESC');

        if($current['fs_method'] === wordpatch_fs_method_ssh2()) {
            $desc_text = __wt($wpenv_vars, 'CONFIGFS_SSH_PLUGIN_DIR_DESC');
        }

        // Calculate the label text.
        $label_text = $current['fs_method'] === wordpatch_fs_method_ssh2() ?
            __wt($wpenv_vars, 'CONFIGFS_SSH_PLUGIN_DIR_LABEL') : __wt($wpenv_vars, 'CONFIGFS_FTP_PLUGIN_DIR_LABEL');

        // Draw the text field.
        wordpatch_wizard_draw_field_text($wpenv_vars, 'wordpatch_ftp_plugin_dir', $current['ftp_plugin_dir'],
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}