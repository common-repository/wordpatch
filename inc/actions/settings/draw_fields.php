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
 * The following functions render the fields for the settings form.
 */

if(!function_exists('wordpatch_settings_draw_language_field')) {
    /**
     * Draw the language field within the settings form.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_settings_draw_language_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Calculate $where and $error_vars
        $where = WORDPATCH_WHERE_SETTINGS;
        $error_vars = array();

        // Define which errors are relevant to the field.
        $field_errors = array();

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'SETTINGS_LANGUAGE_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'SETTINGS_LANGUAGE_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'SETTINGS_LANGUAGE_LABEL');

        // Calculate the dropdown information.
        $dropdown = wordpatch_dropdown($wpenv_vars, wordpatch_languages(), 'wordpatch_display_language');

        // Draw the select field.
        wordpatch_wizard_draw_field_select($wpenv_vars, 'wordpatch_language', $current['language'], $dropdown, $header_text,
            $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_settings_draw_mode_field')) {
    /**
     * Draw the moderation mode field within the settings form.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_settings_draw_mode_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Calculate $where and $error_vars
        $where = WORDPATCH_WHERE_SETTINGS;
        $error_vars = array();

        // Define which errors are relevant to the field.
        $field_errors = array();

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'SETTINGS_MODE_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'SETTINGS_MODE_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'SETTINGS_MODE_LABEL');

        // Calculate the dropdown information.
        $dropdown = wordpatch_dropdown($wpenv_vars, wordpatch_modes(), 'wordpatch_display_mode');

        // Draw the select field.
        wordpatch_wizard_draw_field_select($wpenv_vars, 'wordpatch_mode', $current['mode'], $dropdown, $header_text,
            $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_settings_draw_timer_field')) {
    /**
     * Draw the moderation timer field within the settings form.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_settings_draw_timer_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Calculate $where and $error_vars
        $where = WORDPATCH_WHERE_SETTINGS;
        $error_vars = array();

        // Define which errors are relevant to the field.
        $field_errors = array();

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'SETTINGS_TIMER_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'SETTINGS_TIMER_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'SETTINGS_TIMER_LABEL');

        // Calculate the dropdown information.
        $dropdown = wordpatch_dropdown($wpenv_vars, wordpatch_timers(), 'wordpatch_display_timer');

        // Draw the select field.
        wordpatch_wizard_draw_field_select($wpenv_vars, 'wordpatch_timer', $current['timer'], $dropdown, $header_text,
            $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_settings_draw_update_cooldown_field')) {
    /**
     * Draw the update cooldown field within the settings form.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_settings_draw_update_cooldown_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Calculate $where and $error_vars
        $where = WORDPATCH_WHERE_SETTINGS;
        $error_vars = array();

        // Define which errors are relevant to the field.
        $field_errors = array();

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'SETTINGS_UPDATE_COOLDOWN_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'SETTINGS_UPDATE_COOLDOWN_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'SETTINGS_UPDATE_COOLDOWN_LABEL');

        // Calculate the dropdown information.
        $dropdown = wordpatch_dropdown($wpenv_vars, wordpatch_update_cooldowns(), 'wordpatch_display_update_cooldown');

        // Draw the select field.
        wordpatch_wizard_draw_field_select($wpenv_vars, 'wordpatch_update_cooldown', $current['update_cooldown'], $dropdown, $header_text,
            $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_settings_draw_retry_count_field')) {
    /**
     * Draw the retry count field within the settings form.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_settings_draw_retry_count_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Calculate $where and $error_vars
        $where = WORDPATCH_WHERE_SETTINGS;
        $error_vars = array();

        // Define which errors are relevant to the field.
        $field_errors = array();

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'SETTINGS_RETRY_COUNT_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'SETTINGS_RETRY_COUNT_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'SETTINGS_RETRY_COUNT_LABEL');

        // Calculate the dropdown information.
        $dropdown = wordpatch_dropdown($wpenv_vars, wordpatch_retry_counts(), 'wordpatch_display_retry_count');

        // Draw the select field.
        wordpatch_wizard_draw_field_select($wpenv_vars, 'wordpatch_retry_count', $current['retry_count'], $dropdown, $header_text,
            $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_settings_draw_maintenance_mode_field')) {
    /**
     * Draw the maintenance mode field within the settings form.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_settings_draw_maintenance_mode_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Calculate $where and $error_vars
        $where = WORDPATCH_WHERE_SETTINGS;
        $error_vars = array();

        // Define which errors are relevant to the field.
        $field_errors = array();

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'SETTINGS_MAINTENANCE_MODE_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'SETTINGS_MAINTENANCE_MODE_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'SETTINGS_MAINTENANCE_MODE_LABEL');

        // Calculate the dropdown information.
        $dropdown = wordpatch_dropdown($wpenv_vars, wordpatch_maintenance_modes(), 'wordpatch_display_maintenance_mode');

        // Draw the select field.
        wordpatch_wizard_draw_field_select($wpenv_vars, 'wordpatch_maintenance_mode', $current['maintenance_mode'], $dropdown, $header_text,
            $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}