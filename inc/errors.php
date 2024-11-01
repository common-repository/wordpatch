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
 * Implements helper functions related to handling WordPatch errors.
 */

if(!function_exists('wordpatch_errors_maybe_draw_some')) {
    /**
     * Draws any of $all_errors that exist in $which_errors. If $which_errors is null then draws all errors instead.
     *
     * Optionally outputs which errors were drawn to $which_drew_output.
     * Will return a boolean indicating if there were any errors drawn.
     *
     * @param $wpenv_vars
     * @param $all_errors
     * @param $which_errors
     * @param $where
     * @param $error_vars
     * @param null $which_drew_output
     * @return bool
     */
    function wordpatch_errors_maybe_draw_some($wpenv_vars, $all_errors, $which_errors, $where, $error_vars,
                                              &$which_drew_output = null)
    {
        $any_will_draw = wordpatch_errors_are_blocking($all_errors, $which_errors);
        $which_drew = array();

        if($any_will_draw) {
            ?>
            <div class="wordpatch_errorbox_ctr">
            <?php
        }

        foreach ($all_errors as $error_single) {
            if ($which_errors !== null && !in_array($error_single, $which_errors)) {
                continue;
            }

            wordpatch_errorbox_draw($wpenv_vars, $error_single, $where, $error_vars);
            $which_drew[] = $error_single;
        }

        if($any_will_draw) {
            ?>
            </div>
            <?php
        }

        if($which_drew_output !== null) {
            $which_drew_output = $which_drew;
        }

        if (!empty($which_drew)) {
            return true;
        }

        return false;
    }
}

if(!function_exists('wordpatch_errors_maybe_draw_others')) {
    /**
     * Draws any of $all_errors that do not exist in $which_errors.
     *
     * Optionally outputs which errors were drawn to $which_drew_output.
     * Will return a boolean indicating if there were any errors drawn.
     *
     * @param $wpenv_vars
     * @param $all_errors
     * @param $which_errors
     * @param $where
     * @param $error_vars
     * @param null $which_drew_output
     * @return bool
     */
    function wordpatch_errors_maybe_draw_others($wpenv_vars, $all_errors, $which_errors, $where, $error_vars,
                                              &$which_drew_output = null)
    {
        $will_draw_any = false;

        foreach ($all_errors as $error_single) {
            if (in_array($error_single, $which_errors)) {
                continue;
            }

            $will_draw_any = true;
            break;
        }

        if($will_draw_any) {
            ?>
            <div class="wordpatch_errorbox_ctr">
            <?php
        }

        $which_drew = array();

        foreach ($all_errors as $error_single) {
            if (in_array($error_single, $which_errors)) {
                continue;
            }

            wordpatch_errorbox_draw($wpenv_vars, $error_single, $where, $error_vars);
            $which_drew[] = $error_single;
        }

        if($will_draw_any) {
            ?>
            </div>
            <?php
        }

        if($which_drew_output !== null) {
            $which_drew_output = $which_drew;
        }

        if (!empty($which_drew)) {
            return true;
        }

        return false;
    }
}

if(!function_exists('wordpatch_errors_are_blocking')) {
    /**
     * Returns true if any of $all_errors exist in $blocking_errors. If $blocking_errors is null then returns true if
     * $all_errors is not empty.
     *
     * Optionally outputs which errors were matched to $which_blocking_output.
     *
     * @param $all_errors
     * @param $blocking_errors
     * @param null $which_blocking_output
     * @return bool
     */
    function wordpatch_errors_are_blocking($all_errors, $blocking_errors, &$which_blocking_output = null)
    {
        $which_blocking = array();

        foreach ($all_errors as $error_single) {
            if ($blocking_errors !== null && !in_array($error_single, $blocking_errors)) {
                continue;
            }

            $which_blocking[] = $error_single;
        }

        if($which_blocking_output !== null) {
            $which_blocking_output = $which_blocking;
        }

        if (!empty($which_blocking)) {
            return true;
        }

        return false;
    }
}

if(!function_exists('wordpatch_extend_errors')) {
    /**
     * Extends an error list with an additional error list and returns the result.
     *
     * @param $base_error_list
     * @param $additional_error_list
     * @return array
     */
    function wordpatch_extend_errors($base_error_list, $additional_error_list) {
        $new_error_list = array();

        foreach($base_error_list as $err_single) {
            $new_error_list[] = $err_single;
        }

        foreach($additional_error_list as $err_single) {
            $new_error_list[] = $err_single;
        }

        return $new_error_list;
    }
}

if(!function_exists('wordpatch_no_errors')) {
    /**
     * Returns true if the error list passed into it is empty.
     *
     * @param $error_list
     * @return bool
     */
    function wordpatch_no_errors($error_list) {
        if(empty($error_list)) {
            return true;
        }

        return false;
    }
}