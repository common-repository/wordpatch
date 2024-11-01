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
 * Implements the binary mode functionality.
 */

if(!function_exists('wordpatch_default_job_binary_mode')) {
    function wordpatch_default_job_binary_mode() {
        return wordpatch_job_binary_mode_no();
    }
}

if(!function_exists('wordpatch_display_job_binary_mode')) {
    function wordpatch_display_job_binary_mode($wpenv_vars, $job_binary_mode)
    {
        $display_names = array(
            wordpatch_job_binary_mode_no() => __wt($wpenv_vars, 'NO'),
            wordpatch_job_binary_mode_yes() => __wt($wpenv_vars, 'YES'),
        );

        return wordpatch_display_name($display_names, $job_binary_mode);
    }
}

if(!function_exists('wordpatch_job_binary_mode_no')) {
    function wordpatch_job_binary_mode_no()
    {
        return WORDPATCH_NO;
    }
}

if(!function_exists('wordpatch_job_binary_mode_yes')) {
    function wordpatch_job_binary_mode_yes()
    {
        return WORDPATCH_YES;
    }
}

if(!function_exists('wordpatch_job_binary_modes')) {
    function wordpatch_job_binary_modes()
    {
        return array(
            wordpatch_job_binary_mode_no(),
            wordpatch_job_binary_mode_yes(),
        );
    }
}

if(!function_exists('wordpatch_is_valid_job_binary_mode')) {
    function wordpatch_is_valid_job_binary_mode($job_binary_mode)
    {
        return in_array($job_binary_mode, wordpatch_job_binary_modes());
    }
}