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
 * The following functions render the fields for each step of the database configuration wizard.
 * These functions are called from `wordpatch_configdb_render_fields_{$step_key}`.
 */
if(!function_exists('wordpatch_configdb_error_vars_for_draw_field')) {
    function wordpatch_configdb_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list)
    {
        return array();
    }
}

if(!function_exists('wordpatch_configdb_draw_db_host_field')) {
    /**
     * Draw the database host field.
     * Called by `wordpatch_configdb_render_fields_creds`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configdb_draw_db_host_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Setup our $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGDB;
        $error_vars = wordpatch_configdb_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list);

        // Define which errors are relevant to the field.
        $field_errors = array(WORDPATCH_DB_HOST_REQUIRED);

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'CONFIGDB_DB_HOST_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'CONFIGDB_DB_HOST_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'CONFIGDB_DB_HOST_LABEL');

        // Draw the text field.
        wordpatch_wizard_draw_field_text($wpenv_vars, 'wordpatch_db_host', $current['db_host'],
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_configdb_draw_db_name_field')) {
    /**
     * Draw the database name field.
     * Called by `wordpatch_configdb_render_fields_creds`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configdb_draw_db_name_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Setup our $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGDB;
        $error_vars = wordpatch_configdb_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list);

        // Define which errors are relevant to the field.
        $field_errors = array();

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'CONFIGDB_DB_NAME_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'CONFIGDB_DB_NAME_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'CONFIGDB_DB_NAME_LABEL');

        // Draw the text field.
        wordpatch_wizard_draw_field_text($wpenv_vars, 'wordpatch_db_name', $current['db_name'],
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_configdb_draw_db_user_field')) {
    /**
     * Draw the database username field.
     * Called by `wordpatch_configdb_render_fields_creds`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configdb_draw_db_user_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Setup our $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGDB;
        $error_vars = wordpatch_configdb_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list);

        // Define which errors are relevant to the field.
        $field_errors = array(WORDPATCH_DB_USER_REQUIRED);

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'CONFIGDB_DB_USER_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'CONFIGDB_DB_USER_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'CONFIGDB_DB_USER_LABEL');

        // Draw the text field.
        wordpatch_wizard_draw_field_text($wpenv_vars, 'wordpatch_db_user', $current['db_user'],
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_configdb_draw_db_password_field')) {
    /**
     * Draw the database password field.
     * Called by `wordpatch_configdb_render_fields_creds`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configdb_draw_db_password_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Setup our $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGDB;
        $error_vars = wordpatch_configdb_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list);

        // Define which errors are relevant to the field.
        $field_errors = array();

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'CONFIGDB_DB_PASSWORD_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'CONFIGDB_DB_PASSWORD_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'CONFIGDB_DB_PASSWORD_LABEL');

        // Draw the text field.
        wordpatch_wizard_draw_field_password($wpenv_vars, 'wordpatch_db_password',
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_configdb_draw_db_table_prefix_field')) {
    /**
     * Draw the database table prefix field.
     * Called by `wordpatch_configdb_render_fields_creds`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configdb_draw_db_table_prefix_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Setup our $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGDB;
        $error_vars = wordpatch_configdb_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list);

        // Define which errors are relevant to the field.
        $field_errors = array();

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'CONFIGDB_DB_TABLE_PREFIX_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'CONFIGDB_DB_TABLE_PREFIX_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'CONFIGDB_DB_TABLE_PREFIX_LABEL');

        // Draw the text field.
        wordpatch_wizard_draw_field_text($wpenv_vars, 'wordpatch_db_table_prefix', $current['db_table_prefix'],
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_configdb_draw_db_charset_field')) {
    /**
     * Draw the database charset field.
     * Called by `wordpatch_configdb_render_fields_creds`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configdb_draw_db_charset_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Setup our $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGDB;
        $error_vars = wordpatch_configdb_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list);

        // Define which errors are relevant to the field.
        $field_errors = array(WORDPATCH_DB_CHARSET_INVALID);

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'CONFIGDB_DB_CHARSET_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'CONFIGDB_DB_CHARSET_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'CONFIGDB_DB_CHARSET_LABEL');

        // Calculate our dropdown information.
        $dropdown = wordpatch_dropdown($wpenv_vars, wordpatch_charsets(), 'wordpatch_display_self');

        // Draw the select field.
        wordpatch_wizard_draw_field_select($wpenv_vars, 'wordpatch_db_charset', $current['db_charset'], $dropdown,
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_configdb_draw_db_collate_field')) {
    /**
     * Draw the database collation field.
     * Called by `wordpatch_configdb_render_fields_creds`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configdb_draw_db_collate_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Setup our $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGDB;
        $error_vars = wordpatch_configdb_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list);

        // Define which errors are relevant to the field.
        $field_errors = array(WORDPATCH_DB_COLLATE_INVALID);

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'CONFIGDB_DB_COLLATE_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'CONFIGDB_DB_COLLATE_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'CONFIGDB_DB_COLLATE_LABEL');

        // Calculate our dropdown information.
        $dropdown = wordpatch_dropdown($wpenv_vars, wordpatch_collates(), 'wordpatch_display_self');

        // Draw the select field.
        wordpatch_wizard_draw_field_select($wpenv_vars, 'wordpatch_db_collate', $current['db_collate'], $dropdown,
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}