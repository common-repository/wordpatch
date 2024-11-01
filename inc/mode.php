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
 * Implements the mode functionality.
 */

if(!function_exists('wordpatch_job_mode_inherit')) {
    function wordpatch_job_mode_inherit()
    {
        return 'inherit';
    }
}

if(!function_exists('wordpatch_job_mode_optimistic')) {
    function wordpatch_job_mode_optimistic()
    {
        return 'optimistic';
    }
}

if(!function_exists('wordpatch_mode_optimistic')) {
    function wordpatch_mode_optimistic()
    {
        return 'optimistic';
    }
}

if(!function_exists('wordpatch_job_mode_pessimistic')) {
    function wordpatch_job_mode_pessimistic()
    {
        return 'pessimistic';
    }
}

if(!function_exists('wordpatch_mode_pessimistic')) {
    function wordpatch_mode_pessimistic()
    {
        return 'pessimistic';
    }
}

if(!function_exists('wordpatch_modes')) {
    function wordpatch_modes()
    {
        return array(wordpatch_mode_optimistic(), wordpatch_mode_pessimistic());
    }
}

if(!function_exists('wordpatch_job_modes')) {
    function wordpatch_job_modes()
    {
        return array(
            wordpatch_job_mode_inherit(),
            wordpatch_job_mode_optimistic(),
            wordpatch_job_mode_pessimistic()
        );
    }
}

if(!function_exists('wordpatch_is_valid_mode')) {
    function wordpatch_is_valid_mode($mode)
    {
        return in_array($mode, wordpatch_modes());
    }
}

if(!function_exists('wordpatch_is_valid_job_mode')) {
    function wordpatch_is_valid_job_mode($job_mode)
    {
        return in_array($job_mode, wordpatch_job_modes());
    }
}

if(!function_exists('wordpatch_default_mode')) {
    function wordpatch_default_mode() {
        return wordpatch_mode_optimistic();
    }
}

if(!function_exists('wordpatch_display_job_mode')) {
    function wordpatch_display_job_mode($wpenv_vars, $job_mode)
    {
        $display_names = array(
            wordpatch_job_mode_inherit() => __wt($wpenv_vars, 'INHERIT'),
            wordpatch_job_mode_optimistic() => __wt($wpenv_vars, 'MODE_OPTIMISTIC'),
            wordpatch_job_mode_pessimistic() => __wt($wpenv_vars, 'MODE_PESSIMISTIC')
        );

        return wordpatch_display_name($display_names, $job_mode);
    }
}

if(!function_exists('wordpatch_display_mode')) {
    function wordpatch_display_mode($wpenv_vars, $mode)
    {
        $display_names = array(
            wordpatch_mode_optimistic() => __wt($wpenv_vars, 'MODE_OPTIMISTIC'),
            wordpatch_mode_pessimistic() => __wt($wpenv_vars, 'MODE_PESSIMISTIC')
        );

        return wordpatch_display_name($display_names, $mode);
    }
}

if(!function_exists('wordpatch_default_job_mode')) {
    function wordpatch_default_job_mode() {
        return wordpatch_job_mode_inherit();
    }
}

if(!function_exists('wordpatch_calculate_mode')) {
    function wordpatch_calculate_mode($wpenv_vars)
    {
        $mode = wordpatch_get_option($wpenv_vars, 'wordpatch_mode', '');

        if(wordpatch_is_valid_mode($mode)) {
            return $mode;
        }


        return null;
    }
}