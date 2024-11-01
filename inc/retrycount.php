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
 * Implements the retry count functionality.
 */

if(!function_exists('wordpatch_default_job_retry_count')) {
    function wordpatch_default_job_retry_count() {
        return wordpatch_job_retry_count_inherit();
    }
}

if(!function_exists('wordpatch_default_retry_count')) {
    function wordpatch_default_retry_count() {
        return wordpatch_retry_count_three();
    }
}

if(!function_exists('wordpatch_retry_count_one')) {
    function wordpatch_retry_count_one()
    {
        return 'one';
    }
}

if(!function_exists('wordpatch_retry_count_three')) {
    function wordpatch_retry_count_three()
    {
        return 'three';
    }
}

if(!function_exists('wordpatch_retry_count_five')) {
    function wordpatch_retry_count_five()
    {
        return 'five';
    }
}

if(!function_exists('wordpatch_retry_count_seven')) {
    function wordpatch_retry_count_seven()
    {
        return 'seven';
    }
}

if(!function_exists('wordpatch_retry_count_ten')) {
    function wordpatch_retry_count_ten()
    {
        return 'ten';
    }
}

if(!function_exists('wordpatch_job_retry_count_to_int')) {
    function wordpatch_job_retry_count_to_int($wpenv_vars, $job_retry_count)
    {
        $job_retry_count_inherit = wordpatch_job_retry_count_inherit();

        if($job_retry_count !== $job_retry_count_inherit) {
            if(wordpatch_is_valid_job_retry_count($job_retry_count)) {
                $job_retry_count_ints = array(
                    wordpatch_job_retry_count_one() => 1,
                    wordpatch_job_retry_count_three() => 3,
                    wordpatch_job_retry_count_five() => 5,
                    wordpatch_job_retry_count_seven() => 7,
                    wordpatch_job_retry_count_ten() => 10
                );

                return $job_retry_count_ints[$job_retry_count];
            }
        } else {
            $retry_count = wordpatch_calculate_retry_count($wpenv_vars);

            if($retry_count !== $job_retry_count_inherit && wordpatch_is_valid_job_retry_count($retry_count)) {
                return wordpatch_job_retry_count_to_int($wpenv_vars, $retry_count);
            }
        }


        return null;
    }
}

if(!function_exists('wordpatch_job_retry_count_inherit')) {
    function wordpatch_job_retry_count_inherit()
    {
        return 'inherit';
    }
}

if(!function_exists('wordpatch_job_retry_count_one')) {
    function wordpatch_job_retry_count_one()
    {
        return 'one';
    }
}

if(!function_exists('wordpatch_job_retry_count_three')) {
    function wordpatch_job_retry_count_three()
    {
        return 'three';
    }
}

if(!function_exists('wordpatch_job_retry_count_five')) {
    function wordpatch_job_retry_count_five()
    {
        return 'five';
    }
}

if(!function_exists('wordpatch_job_retry_count_seven')) {
    function wordpatch_job_retry_count_seven()
    {
        return 'seven';
    }
}

if(!function_exists('wordpatch_job_retry_count_ten')) {
    function wordpatch_job_retry_count_ten()
    {
        return 'ten';
    }
}

if(!function_exists('wordpatch_job_retry_counts')) {
    function wordpatch_job_retry_counts()
    {
        return array(
            wordpatch_job_retry_count_inherit(),
            wordpatch_job_retry_count_one(),
            wordpatch_job_retry_count_three(),
            wordpatch_job_retry_count_five(),
            wordpatch_job_retry_count_seven(),
            wordpatch_job_retry_count_ten(),
        );
    }
}

if(!function_exists('wordpatch_retry_counts')) {
    function wordpatch_retry_counts()
    {
        return array(
            wordpatch_retry_count_one(),
            wordpatch_retry_count_three(),
            wordpatch_retry_count_five(),
            wordpatch_retry_count_seven(),
            wordpatch_retry_count_ten(),
        );
    }
}

if(!function_exists('wordpatch_is_valid_job_retry_count')) {
    function wordpatch_is_valid_job_retry_count($job_retry_count)
    {
        return in_array($job_retry_count, wordpatch_job_retry_counts());
    }
}

if(!function_exists('wordpatch_is_valid_retry_count')) {
    function wordpatch_is_valid_retry_count($retry_count)
    {
        return in_array($retry_count, wordpatch_retry_counts());
    }
}

if(!function_exists('wordpatch_display_job_retry_count')) {
    function wordpatch_display_job_retry_count($wpenv_vars, $job_retry_count)
    {
        $display_names = array(
            wordpatch_job_retry_count_inherit() => __wt($wpenv_vars, 'INHERIT'),
            wordpatch_job_retry_count_one() => __wt($wpenv_vars, 'ONE'),
            wordpatch_job_retry_count_three() => __wt($wpenv_vars, 'THREE'),
            wordpatch_job_retry_count_five() => __wt($wpenv_vars, 'FIVE'),
            wordpatch_job_retry_count_seven() => __wt($wpenv_vars, 'SEVEN'),
            wordpatch_job_retry_count_ten() => __wt($wpenv_vars, 'TEN')
        );

        return wordpatch_display_name($display_names, $job_retry_count);
    }
}

if(!function_exists('wordpatch_display_retry_count')) {
    function wordpatch_display_retry_count($wpenv_vars, $retry_count)
    {
        $display_names = array(
            wordpatch_retry_count_one() => __wt($wpenv_vars, 'ONE'),
            wordpatch_retry_count_three() => __wt($wpenv_vars, 'THREE'),
            wordpatch_retry_count_five() => __wt($wpenv_vars, 'FIVE'),
            wordpatch_retry_count_seven() => __wt($wpenv_vars, 'SEVEN'),
            wordpatch_retry_count_ten() => __wt($wpenv_vars, 'TEN')
        );

        return wordpatch_display_name($display_names, $retry_count);
    }
}

if(!function_exists('wordpatch_calculate_retry_count')) {
    function wordpatch_calculate_retry_count($wpenv_vars)
    {
        $retry_count = wordpatch_get_option($wpenv_vars, 'wordpatch_retry_count', '');

        if(wordpatch_is_valid_retry_count($retry_count)) {
            return $retry_count;
        }

        return null;
    }
}