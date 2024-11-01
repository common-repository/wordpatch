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
 * The following functions perform the pre-rendering logic for each step of the mail configuration wizard.
 * These functions are called from `wordpatch_render_configmail`.
 */

if(!function_exists('wordpatch_configmail_before_render')) {
    /**
     * Performs the pre-rendering logic for each step of the mail configuration wizard.
     * Returns an array containing a list of errors to be passed into `wordpatch_configmail_render_form`.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $step_number
     * @param $steps
     * @return array
     */
    function wordpatch_configmail_before_render($wpenv_vars, $current, $step_number, $steps) {
        // Create a result array and calculate if there are any pre-configuration errors.
        return wordpatch_configmail_check_preconfiguration($wpenv_vars);
    }
}