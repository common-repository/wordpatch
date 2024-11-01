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
 * The following functions render the fields for each step of the license configuration wizard.
 * These functions are called from `wordpatch_configlicense_render_fields_{$step_key}`.
 */

if(!function_exists('wordpatch_configlicense_error_vars_for_draw_field')) {
    function wordpatch_configlicense_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list)
    {
        return array();
    }
}

if(!function_exists('wordpatch_configlicense_draw_activation_key_field')) {
    /**
     * Draw the activation key field.
     * Called by `wordpatch_configlicense_render_fields_dirs`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     */
    function wordpatch_configlicense_draw_activation_key_field($wpenv_vars, $current, $wizard_error_list)
    {
        // Setup our $where and $error_vars
        $where = WORDPATCH_WHERE_CONFIGLICENSE;
        $error_vars = wordpatch_configlicense_error_vars_for_draw_field($wpenv_vars, $current, $wizard_error_list);

        // Define which errors are relevant to the field.
        $field_errors = array(WORDPATCH_INVALID_ACTIVATION_KEY, WORDPATCH_BAD_LICENSE, WORDPATCH_NO_LICENSE,
            WORDPATCH_INACTIVE_LICENSE, WORDPATCH_PRODUCT_MISMATCH, WORDPATCH_DOMAIN_ALREADY_ACTIVE, WORDPATCH_OUT_OF_SEATS);

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'CONFIGLICENSE_ACTIVATION_KEY_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'CONFIGLICENSE_ACTIVATION_KEY_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'CONFIGLICENSE_ACTIVATION_KEY_LABEL');

        // Calculate the final key.
        $final_key = wordpatch_get_final_activation_key($current['activation_key']);

        // Draw the text field.
        wordpatch_wizard_draw_field_text($wpenv_vars, 'wordpatch_activation_key', $final_key,
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}