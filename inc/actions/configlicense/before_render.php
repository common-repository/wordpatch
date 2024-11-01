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
 * The following functions perform the pre-rendering logic for each step of the license configuration wizard.
 * These functions are called from `wordpatch_render_configlicense`.
 */

if(!function_exists('wordpatch_configlicense_before_render')) {
    /**
     * Performs the pre-rendering logic for each step of the license configuration wizard.
     * Returns an array containing a list of errors to be passed into `wordpatch_configlicense_render_form`.
     *
     * PS: This function additionally calls the appropriate `wordpatch_configlicense_before_render_{$step_key}`, see
     * those for more information.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $step_number
     * @param $steps
     * @return array
     */
    function wordpatch_configlicense_before_render($wpenv_vars, $current, $step_number, $steps) {
        // Create a result array and calculate if there are any pre-configuration errors.
        $before_errors = wordpatch_configlicense_check_preconfiguration($wpenv_vars);

        // Check if we have encountered any errors thus far, and if so return early.
        if(!wordpatch_no_errors($before_errors)) {
            return $before_errors;
        }

        // Call the proper `before_render` function based on the step number.
        switch($step_number) {
            case $steps['basic']:
                $before_errors = wordpatch_configlicense_before_render_basic($wpenv_vars, $current);
                break;
        }

        // Return our result array.
        return $before_errors;
    }
}

if(!function_exists('wordpatch_configlicense_before_render_basic')) {
    /**
     * Performs the pre-rendering logic for the basic step of the license configuration wizard.
     * Called by `wordpatch_configlicense_before_render`. See for more information.
     *
     * @param $wpenv_vars
     * @param $current
     * @return array
     */
    function wordpatch_configlicense_before_render_basic($wpenv_vars, $current) {
        // Create a result array for errors.
        $before_errors = array();

        // If the license seems active already, append an error.
        if(wordpatch_license_is_active($wpenv_vars)) {
            $before_errors[] = WORDPATCH_LICENSE_SEEMS_ACTIVE;
        }

        // Return the result array.
        return $before_errors;
    }
}