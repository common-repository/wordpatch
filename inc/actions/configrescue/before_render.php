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
 * The following functions perform the pre-rendering logic for each step of the rescue configuration wizard.
 * These functions are called from `wordpatch_render_configrescue`.
 */
if(!function_exists('wordpatch_configrescue_before_render')) {
    /**
     * Performs the pre-rendering logic for each step of the rescue configuration wizard.
     * Returns an array containing a list of errors to be passed into `wordpatch_configrescue_render_form`.
     *
     * PS: This function additionally calls the appropriate `wordpatch_configrescue_before_render_{$step_key}`, see
     * those for more information.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $step_number
     * @param $steps
     * @return array
     */
    function wordpatch_configrescue_before_render($wpenv_vars, $current, $step_number, $steps) {
        // Create a result array and calculate if there are any pre-configuration errors.
        $before_errors = wordpatch_configrescue_check_preconfiguration($wpenv_vars);

        // Check if we have encountered any errors thus far, and if so return early.
        if(!wordpatch_no_errors($before_errors)) {
            return $before_errors;
        }

        // Call the proper `before_render` function based on the step number.
        switch($step_number) {
            case $steps['validate']:
                $before_errors = wordpatch_configrescue_before_render_validate($wpenv_vars, $current);
                break;
        }

        // Return our result array.
        return $before_errors;
    }
}

if(!function_exists('wordpatch_configrescue_before_render_validate')) {
    /**
     * Performs the pre-rendering logic for the validation step of the rescue configuration wizard.
     * Called by `wordpatch_configrescue_before_render`. See for more information.
     *
     * @param $wpenv_vars
     * @param $current
     * @return array
     */
    function wordpatch_configrescue_before_render_validate($wpenv_vars, $current) {
        // Simply validate the rescue script (which will return an error list)
        return wordpatch_rescue_validate_or_create($wpenv_vars, $current['rescue_path'], true);
    }
}