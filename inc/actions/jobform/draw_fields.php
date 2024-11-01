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
 * The following functions render the fields for the new/edit job forms.
 */

if(!function_exists('wordpatch_jobform_draw_job_title_field')) {
    /**
     * Draw the job title field within the new/edit job form.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     * @param $where
     * @param $error_vars
     */
    function wordpatch_jobform_draw_job_title_field($wpenv_vars, $current, $wizard_error_list, $where, $error_vars)
    {
        // Define which errors are relevant to the field.
        $field_errors = array(WORDPATCH_JOB_TITLE_REQUIRED);

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'JOB_FORM_TITLE_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'JOB_FORM_TITLE_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'JOB_FORM_TITLE_LABEL');

        // Draw the text field.
        wordpatch_wizard_draw_field_text($wpenv_vars, 'job_title', $current['job_title'],
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_jobform_draw_job_path_field')) {
    /**
     * Draw the job path field within the new/edit job form.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     * @param $where
     * @param $error_vars
     */
    function wordpatch_jobform_draw_job_path_field($wpenv_vars, $current, $wizard_error_list, $where, $error_vars)
    {
        // Define which errors are relevant to the field.
        $field_errors = array();

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'JOB_FORM_PATH_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'JOB_FORM_PATH_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'JOB_FORM_PATH_LABEL');

        // Calculate the flex label text.
        $flex_label_text = wordpatch_get_display_directory($wpenv_vars['wp_root_dir']);

        // Calculate the display value.
        $display_value = wordpatch_get_display_relative_directory($current['job_path']);

        // Draw the text field.
        wordpatch_wizard_draw_field_text($wpenv_vars, 'job_path', $display_value, $header_text, $desc_text,
            $label_text, $where, $field_errors, $error_vars, $wizard_error_list, $flex_label_text);
    }
}

if(!function_exists('wordpatch_jobform_draw_job_enabled_field')) {
    /**
     * Draw the job enabled field within the new/edit job form.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     * @param $where
     * @param $error_vars
     */
    function wordpatch_jobform_draw_job_enabled_field($wpenv_vars, $current, $wizard_error_list, $where, $error_vars)
    {
        // Define which errors are relevant to the field.
        $field_errors = array();

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'JOB_FORM_ENABLED_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'JOB_FORM_ENABLED_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'JOB_FORM_ENABLED_LABEL');

        // Calculate the dropdown information.
        $dropdown = wordpatch_dropdown($wpenv_vars, wordpatch_job_enableds(), 'wordpatch_display_job_enabled');

        // Draw the select field.
        wordpatch_wizard_draw_field_select($wpenv_vars, 'job_enabled', $current['job_enabled'], $dropdown, $header_text,
            $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_jobform_draw_job_mode_field')) {
    /**
     * Draw the job moderation mode field within the new/edit job form.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     * @param $where
     * @param $error_vars
     */
    function wordpatch_jobform_draw_job_mode_field($wpenv_vars, $current, $wizard_error_list, $where, $error_vars)
    {
        // Define which errors are relevant to the field.
        $field_errors = array();

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'JOB_FORM_MODE_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'JOB_FORM_MODE_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'JOB_FORM_MODE_LABEL');

        // Calculate the dropdown information.
        $dropdown = wordpatch_dropdown($wpenv_vars, wordpatch_job_modes(), 'wordpatch_display_job_mode');

        // Draw the select field.
        wordpatch_wizard_draw_field_select($wpenv_vars, 'job_mode', $current['job_mode'], $dropdown, $header_text,
            $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_jobform_draw_job_timer_field')) {
    /**
     * Draw the job moderation timer field within the new/edit job form.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     * @param $where
     * @param $error_vars
     */
    function wordpatch_jobform_draw_job_timer_field($wpenv_vars, $current, $wizard_error_list, $where, $error_vars)
    {
        // Define which errors are relevant to the field.
        $field_errors = array();

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'JOB_FORM_TIMER_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'JOB_FORM_TIMER_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'JOB_FORM_TIMER_LABEL');

        // Calculate the dropdown information.
        $dropdown = wordpatch_dropdown($wpenv_vars, wordpatch_job_timers(), 'wordpatch_display_job_timer');

        // Draw the select field.
        wordpatch_wizard_draw_field_select($wpenv_vars, 'job_timer', $current['job_timer'], $dropdown, $header_text,
            $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_jobform_draw_job_update_cooldown_field')) {
    /**
     * Draw the job update cooldown field within the new/edit job form.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     * @param $where
     * @param $error_vars
     */
    function wordpatch_jobform_draw_job_update_cooldown_field($wpenv_vars, $current, $wizard_error_list, $where, $error_vars)
    {
        // Define which errors are relevant to the field.
        $field_errors = array();

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'JOB_FORM_UPDATE_COOLDOWN_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'JOB_FORM_UPDATE_COOLDOWN_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'JOB_FORM_UPDATE_COOLDOWN_LABEL');

        // Calculate the dropdown information.
        $dropdown = wordpatch_dropdown($wpenv_vars, wordpatch_job_update_cooldowns(), 'wordpatch_display_job_update_cooldown');

        // Draw the select field.
        wordpatch_wizard_draw_field_select($wpenv_vars, 'job_update_cooldown', $current['job_update_cooldown'], $dropdown,
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_jobform_draw_job_retry_count_field')) {
    /**
     * Draw the job retry count field within the new/edit job form.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     * @param $where
     * @param $error_vars
     */
    function wordpatch_jobform_draw_job_retry_count_field($wpenv_vars, $current, $wizard_error_list, $where, $error_vars)
    {
        // Define which errors are relevant to the field.
        $field_errors = array();

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'JOB_FORM_RETRY_COUNT_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'JOB_FORM_RETRY_COUNT_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'JOB_FORM_RETRY_COUNT_LABEL');

        // Calculate the dropdown information.
        $dropdown = wordpatch_dropdown($wpenv_vars, wordpatch_job_retry_counts(), 'wordpatch_display_job_retry_count');

        // Draw the select field.
        wordpatch_wizard_draw_field_select($wpenv_vars, 'job_retry_count', $current['job_retry_count'], $dropdown,
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_jobform_draw_job_maintenance_mode_field')) {
    /**
     * Draw the job maintenance mode field within the new/edit job form.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     * @param $where
     * @param $error_vars
     */
    function wordpatch_jobform_draw_job_maintenance_mode_field($wpenv_vars, $current, $wizard_error_list, $where, $error_vars)
    {
        // Define which errors are relevant to the field.
        $field_errors = array();

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'JOB_FORM_MAINTENANCE_MODE_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'JOB_FORM_MAINTENANCE_MODE_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'JOB_FORM_MAINTENANCE_MODE_LABEL');

        // Calculate the dropdown information.
        $dropdown = wordpatch_dropdown($wpenv_vars, wordpatch_job_maintenance_modes(), 'wordpatch_display_job_maintenance_mode');

        // Draw the select field.
        wordpatch_wizard_draw_field_select($wpenv_vars, 'job_maintenance_mode', $current['job_maintenance_mode'], $dropdown,
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_jobform_draw_job_binary_mode_field')) {
    /**
     * Draw the job binary mode field within the new/edit job form.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     * @param $where
     * @param $error_vars
     */
    function wordpatch_jobform_draw_job_binary_mode_field($wpenv_vars, $current, $wizard_error_list, $where, $error_vars)
    {
        // Define which errors are relevant to the field.
        $field_errors = array();

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'JOB_FORM_BINARY_MODE_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'JOB_FORM_BINARY_MODE_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'JOB_FORM_BINARY_MODE_LABEL');

        // Calculate the dropdown information.
        $dropdown = wordpatch_dropdown($wpenv_vars, wordpatch_job_binary_modes(), 'wordpatch_display_job_binary_mode');

        // Draw the select field.
        wordpatch_wizard_draw_field_select($wpenv_vars, 'job_binary_mode', $current['job_binary_mode'], $dropdown,
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}