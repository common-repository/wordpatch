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

if(!function_exists('wordpatch_is_valid_job_timer')) {
    function wordpatch_is_valid_job_timer($job_timer)
    {
        return in_array($job_timer, wordpatch_job_timers());
    }
}

if(!function_exists('wordpatch_is_valid_timer')) {
    function wordpatch_is_valid_timer($timer)
    {
        return in_array($timer, wordpatch_timers());
    }
}

if(!function_exists('wordpatch_timer_one_day')) {
    function wordpatch_timer_one_day()
    {
        return 'one_day';
    }
}

if(!function_exists('wordpatch_timer_two_days')) {
    function wordpatch_timer_two_days()
    {
        return 'two_days';
    }
}

if(!function_exists('wordpatch_timer_three_days')) {
    function wordpatch_timer_three_days()
    {
        return 'three_days';
    }
}

if(!function_exists('wordpatch_timer_five_days')) {
    function wordpatch_timer_five_days()
    {
        return 'five_days';
    }
}

if(!function_exists('wordpatch_timer_one_week')) {
    function wordpatch_timer_one_week()
    {
        return 'one_week';
    }
}

if(!function_exists('wordpatch_timer_two_weeks')) {
    function wordpatch_timer_two_weeks()
    {
        return 'two_weeks';
    }
}

if(!function_exists('wordpatch_timer_one_month')) {
    function wordpatch_timer_one_month()
    {
        return 'one_month';
    }
}

if(!function_exists('wordpatch_job_timer_inherit')) {
    function wordpatch_job_timer_inherit()
    {
        return 'inherit';
    }
}

if(!function_exists('wordpatch_job_timer_one_day')) {
    function wordpatch_job_timer_one_day()
    {
        return 'one_day';
    }
}

if(!function_exists('wordpatch_job_timer_two_days')) {
    function wordpatch_job_timer_two_days()
    {
        return 'two_days';
    }
}

if(!function_exists('wordpatch_job_timer_three_days')) {
    function wordpatch_job_timer_three_days()
    {
        return 'three_days';
    }
}

if(!function_exists('wordpatch_job_timer_five_days')) {
    function wordpatch_job_timer_five_days()
    {
        return 'five_days';
    }
}

if(!function_exists('wordpatch_job_timer_one_week')) {
    function wordpatch_job_timer_one_week()
    {
        return 'one_week';
    }
}

if(!function_exists('wordpatch_job_timer_two_weeks')) {
    function wordpatch_job_timer_two_weeks()
    {
        return 'two_weeks';
    }
}

if(!function_exists('wordpatch_job_timer_one_month')) {
    function wordpatch_job_timer_one_month()
    {
        return 'one_month';
    }
}

if(!function_exists('wordpatch_job_timers')) {
    function wordpatch_job_timers()
    {
        return array(
            wordpatch_job_timer_inherit(),
            wordpatch_job_timer_one_day(),
            wordpatch_job_timer_two_days(),
            wordpatch_job_timer_three_days(),
            wordpatch_job_timer_five_days(),
            wordpatch_job_timer_one_week(),
            wordpatch_job_timer_two_weeks(),
            wordpatch_job_timer_one_month(),
        );
    }
}

if(!function_exists('wordpatch_timers')) {
    function wordpatch_timers()
    {
        return array(
            wordpatch_timer_one_day(),
            wordpatch_timer_two_days(),
            wordpatch_timer_three_days(),
            wordpatch_timer_five_days(),
            wordpatch_timer_one_week(),
            wordpatch_timer_two_weeks(),
            wordpatch_timer_one_month(),
        );
    }
}

if(!function_exists('wordpatch_render_metadesc_job_timer')) {
    function wordpatch_render_metadesc_job_timer($wpenv_vars)
    {
        return "Which timer length would you like to use for your selected moderation mode?";
    }
}

if(!function_exists('wordpatch_job_timer_seconds')) {
    function wordpatch_job_timer_seconds($job_timer) {
        $day_in_seconds = wordpatch_day_in_seconds();

        $seconds_map = array(
            wordpatch_job_timer_one_day() => $day_in_seconds,
            wordpatch_job_timer_two_days() => $day_in_seconds * 2,
            wordpatch_job_timer_three_days() => $day_in_seconds * 3,
            wordpatch_job_timer_five_days() => $day_in_seconds * 5,
            wordpatch_job_timer_one_week() => $day_in_seconds * 7,
            wordpatch_job_timer_two_weeks() => $day_in_seconds * 14,
            wordpatch_job_timer_one_month() => $day_in_seconds * 30,
        );

        if(isset($seconds_map[$job_timer])) {
            return $seconds_map[$job_timer];
        }

        return null;
    }
}

if(!function_exists('wordpatch_display_job_timer')) {
    function wordpatch_display_job_timer($wpenv_vars, $job_timer)
    {
        $display_names = array(
            wordpatch_job_timer_inherit() => 'Inherit',
            wordpatch_job_timer_one_day() => 'One Day (24 hours exactly)',
            wordpatch_job_timer_two_days() => 'Two Days (48 hours exactly)',
            wordpatch_job_timer_three_days() => 'Three Days (72 hours exactly)',
            wordpatch_job_timer_five_days() => 'Five Days (120 hours exactly)',
            wordpatch_job_timer_one_week() => 'One Week (7 days exactly)',
            wordpatch_job_timer_two_weeks() => 'Two Weeks (14 days exactly)',
            wordpatch_job_timer_one_month() => 'One Month (30 days exactly)',
        );

        return wordpatch_display_name($display_names, $job_timer);
    }
}

if(!function_exists('wordpatch_display_timer')) {
    function wordpatch_display_timer($wpenv_vars, $timer)
    {
        $display_names = array(
            wordpatch_timer_one_day() => 'One Day (24 hours exactly)',
            wordpatch_timer_two_days() => 'Two Days (48 hours exactly)',
            wordpatch_timer_three_days() => 'Three Days (72 hours exactly)',
            wordpatch_timer_five_days() => 'Five Days (120 hours exactly)',
            wordpatch_timer_one_week() => 'One Week (7 days exactly)',
            wordpatch_timer_two_weeks() => 'Two Weeks (14 days exactly)',
            wordpatch_timer_one_month() => 'One Month (30 days exactly)',
        );

        return wordpatch_display_name($display_names, $timer);
    }
}

if(!function_exists('wordpatch_default_job_timer')) {
    function wordpatch_default_job_timer() {
        return wordpatch_job_timer_inherit();
    }
}

if(!function_exists('wordpatch_default_timer')) {
    function wordpatch_default_timer() {
        return wordpatch_timer_one_week();
    }
}

if(!function_exists('wordpatch_calculate_timer')) {
    function wordpatch_calculate_timer($wpenv_vars)
    {
        $timer = wordpatch_get_option($wpenv_vars, 'wordpatch_timer', '');

        if(wordpatch_is_valid_timer($timer)) {
            return $timer;
        }

        return null;
    }
}