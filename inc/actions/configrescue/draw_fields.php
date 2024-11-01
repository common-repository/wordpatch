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
 * The following functions render the fields for each step of the rescue configuration wizard.
 * These functions are called from `wordpatch_configrescue_render_fields_{$step_key}`.
 */

if(!function_exists('wordpatch_configrescue_error_vars_for_draw_field')) {
    function wordpatch_configrescue_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list)
    {
        return array();
    }
}

if(!function_exists('wordpatch_configrescue_draw_rescue_path_field')) {
    /**
     * Draw the rescue path field.
     * Called by `wordpatch_configrescue_render_fields_dirs`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configrescue_draw_rescue_path_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Setup our $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGRESCUE;
        $error_vars = wordpatch_configrescue_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list);

        // Define which errors are relevant to the field.
        $field_errors = array(WORDPATCH_INVALID_RESCUE_PATH, WORDPATCH_INVALID_RESCUE_DIRECTORY,
            WORDPATCH_INVALID_RESCUE_FILENAME, WORDPATCH_INVALID_RESCUE_EXTENSION);

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'CONFIGRESCUE_RESCUE_PATH_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'CONFIGRESCUE_RESCUE_PATH_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'CONFIGRESCUE_RESCUE_PATH_LABEL');

        // Draw the text field.
        wordpatch_wizard_draw_field_text($wpenv_vars, 'wordpatch_rescue_path', $current['rescue_path'],
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}