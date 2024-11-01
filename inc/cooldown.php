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
 * Defines the cooldown for running jobs after WordPress applies upgrades to core, themes, or plugins.
 * For example, you might configure a global cooldown of 5 minutes, which would ensure that WordPatch would wait 5 minutes
 * after the last detected upgrade before queuing enabled jobs to be ran.
 *
 * You are able to set a global cooldown, as well as a cooldown on a per job basis. This level of flexibility is
 * just in-case certain jobs may be more or less prone to race conditions than others.
 *
 * The global cooldown is configured through the WordPatch settings page and job cooldowns are configured on the
 * new/edit job pages. By default, jobs inherit the global cooldown setting. The default global cooldown is 3 minutes.
 *
 * PS: If you would like to see cooldowns longer than thirty minutes, please file a feature request. We would be happy
 * to add more to future versions.
 */

/**
 * Define each of the core global update cooldown constant values.
 *
 * PS: All constants are pluggable callbacks (similar to hardcoded, however can be adjusted by defining elsewhere prior to
 * inclusion of this file). It is highly recommended not to override these callbacks unless you are an advanced user.
 */
if(!function_exists('wordpatch_update_cooldown_asap')) {
    function wordpatch_update_cooldown_asap()
    {
        return 'asap';
    }
}

if(!function_exists('wordpatch_update_cooldown_one_minute')) {
    function wordpatch_update_cooldown_one_minute()
    {
        return 'one_minute';
    }
}

if(!function_exists('wordpatch_update_cooldown_two_minutes')) {
    function wordpatch_update_cooldown_two_minutes()
    {
        return 'two_minutes';
    }
}

if(!function_exists('wordpatch_update_cooldown_three_minutes')) {
    function wordpatch_update_cooldown_three_minutes()
    {
        return 'three_minutes';
    }
}

if(!function_exists('wordpatch_update_cooldown_five_minutes')) {
    function wordpatch_update_cooldown_five_minutes()
    {
        return 'five_minutes';
    }
}

if(!function_exists('wordpatch_update_cooldown_seven_minutes')) {
    function wordpatch_update_cooldown_seven_minutes()
    {
        return 'seven_minutes';
    }
}

if(!function_exists('wordpatch_update_cooldown_ten_minutes')) {
    function wordpatch_update_cooldown_ten_minutes()
    {
        return 'ten_minutes';
    }
}

if(!function_exists('wordpatch_update_cooldown_fifteen_minutes')) {
    function wordpatch_update_cooldown_fifteen_minutes()
    {
        return 'fifteen_minutes';
    }
}

if(!function_exists('wordpatch_update_cooldown_twenty_minutes')) {
    function wordpatch_update_cooldown_twenty_minutes()
    {
        return 'twenty_minutes';
    }
}

if(!function_exists('wordpatch_update_cooldown_thirty_minutes')) {
    function wordpatch_update_cooldown_thirty_minutes()
    {
        return 'thirty_minutes';
    }
}

if(!function_exists('wordpatch_update_cooldowns')) {
    /**
     * Returns an array of valid global update cooldown constant values. If you decide to add more own cooldown options,
     * start here.
     *
     * @return array
     */
    function wordpatch_update_cooldowns()
    {
        return array(
            wordpatch_update_cooldown_asap(),
            wordpatch_update_cooldown_one_minute(),
            wordpatch_update_cooldown_two_minutes(),
            wordpatch_update_cooldown_three_minutes(),
            wordpatch_update_cooldown_five_minutes(),
            wordpatch_update_cooldown_seven_minutes(),
            wordpatch_update_cooldown_ten_minutes(),
            wordpatch_update_cooldown_fifteen_minutes(),
            wordpatch_update_cooldown_twenty_minutes(),
            wordpatch_update_cooldown_thirty_minutes(),
        );
    }
}

if(!function_exists('wordpatch_is_valid_update_cooldown')) {
    /**
     * This function checks if $update_cooldown exists inside the return array of `wordpatch_update_cooldowns`.
     * Use this to check if a global update cooldown constant value is valid.
     *
     * @param $update_cooldown
     * @return bool
     */
    function wordpatch_is_valid_update_cooldown($update_cooldown)
    {
        return in_array($update_cooldown, wordpatch_update_cooldowns());
    }
}

/**
 * Define each of the core job level update cooldown constant values.
 *
 * PS: All constants are pluggable callbacks (similar to hardcoded, however can be adjusted by defining elsewhere prior to
 * inclusion of this file). It is highly recommended not to override these callbacks unless you are an advanced user.
 */
if(!function_exists('wordpatch_job_update_cooldown_inherit')) {
    function wordpatch_job_update_cooldown_inherit()
    {
        return 'inherit';
    }
}

if(!function_exists('wordpatch_job_update_cooldown_asap')) {
    function wordpatch_job_update_cooldown_asap()
    {
        return 'asap';
    }
}

if(!function_exists('wordpatch_job_update_cooldown_one_minute')) {
    function wordpatch_job_update_cooldown_one_minute()
    {
        return 'one_minute';
    }
}

if(!function_exists('wordpatch_job_update_cooldown_two_minutes')) {
    function wordpatch_job_update_cooldown_two_minutes()
    {
        return 'two_minutes';
    }
}

if(!function_exists('wordpatch_job_update_cooldown_three_minutes')) {
    function wordpatch_job_update_cooldown_three_minutes()
    {
        return 'three_minutes';
    }
}

if(!function_exists('wordpatch_job_update_cooldown_five_minutes')) {
    function wordpatch_job_update_cooldown_five_minutes()
    {
        return 'five_minutes';
    }
}

if(!function_exists('wordpatch_job_update_cooldown_seven_minutes')) {
    function wordpatch_job_update_cooldown_seven_minutes()
    {
        return 'seven_minutes';
    }
}

if(!function_exists('wordpatch_job_update_cooldown_ten_minutes')) {
    function wordpatch_job_update_cooldown_ten_minutes()
    {
        return 'ten_minutes';
    }
}

if(!function_exists('wordpatch_job_update_cooldown_fifteen_minutes')) {
    function wordpatch_job_update_cooldown_fifteen_minutes()
    {
        return 'fifteen_minutes';
    }
}

if(!function_exists('wordpatch_job_update_cooldown_twenty_minutes')) {
    function wordpatch_job_update_cooldown_twenty_minutes()
    {
        return 'twenty_minutes';
    }
}

if(!function_exists('wordpatch_job_update_cooldown_thirty_minutes')) {
    function wordpatch_job_update_cooldown_thirty_minutes()
    {
        return 'thirty_minutes';
    }
}

if(!function_exists('wordpatch_job_update_cooldowns')) {
    /**
     * Returns an array of valid job levle update cooldown constant values. If you decide to add more own job cooldown
     * options, start here.
     *
     * @return array
     */
    function wordpatch_job_update_cooldowns()
    {
        return array(
            wordpatch_job_update_cooldown_inherit(),
            wordpatch_job_update_cooldown_asap(),
            wordpatch_job_update_cooldown_one_minute(),
            wordpatch_job_update_cooldown_two_minutes(),
            wordpatch_job_update_cooldown_three_minutes(),
            wordpatch_job_update_cooldown_five_minutes(),
            wordpatch_job_update_cooldown_seven_minutes(),
            wordpatch_job_update_cooldown_ten_minutes(),
            wordpatch_job_update_cooldown_fifteen_minutes(),
            wordpatch_job_update_cooldown_twenty_minutes(),
            wordpatch_job_update_cooldown_thirty_minutes(),
        );
    }
}

if(!function_exists('wordpatch_is_valid_job_update_cooldown')) {
    /**
     * This function checks if $job_update_cooldown exists inside the return array of `wordpatch_job_update_cooldowns`.
     * Use this to check if a job level update cooldown constant value is valid.
     *
     * @param $job_update_cooldown
     * @return bool
     */
    function wordpatch_is_valid_job_update_cooldown($job_update_cooldown)
    {
        return in_array($job_update_cooldown, wordpatch_job_update_cooldowns());
    }
}

if(!function_exists('wordpatch_job_update_cooldown_seconds')) {
    /**
     * This function returns the number of seconds for the corresponding $job_update_cooldown.
     * For a full list of valid parameter values, please see the `wordpatch_job_update_cooldowns` function.
     *
     * If the value passed into $job_update_cooldown is not valid, null will be returned instead.
     *
     * PS: Please note, you should not pass in 'inherit' to this function. It will cause it to return null.
     *
     * @param $job_update_cooldown
     * @return mixed|null
     */
    function wordpatch_job_update_cooldown_seconds($job_update_cooldown) {
        // First calculate the number of seconds in a single minute
        $minute_in_seconds = wordpatch_minute_in_seconds();

        // Construct a map with all the valid calculations
        $seconds_map = array(
            wordpatch_job_update_cooldown_one_minute() => $minute_in_seconds,
            wordpatch_job_update_cooldown_two_minutes() => $minute_in_seconds * 2,
            wordpatch_job_update_cooldown_three_minutes() => $minute_in_seconds * 3,
            wordpatch_job_update_cooldown_five_minutes() => $minute_in_seconds * 5,
            wordpatch_job_update_cooldown_seven_minutes() => $minute_in_seconds * 7,
            wordpatch_job_update_cooldown_ten_minutes() => $minute_in_seconds * 10,
            wordpatch_job_update_cooldown_fifteen_minutes() => $minute_in_seconds * 15,
            wordpatch_job_update_cooldown_twenty_minutes() => $minute_in_seconds * 20,
            wordpatch_job_update_cooldown_thirty_minutes() => $minute_in_seconds * 30
        );

        // Return the value in seconds if set.
        if(isset($seconds_map[$job_update_cooldown])) {
            return $seconds_map[$job_update_cooldown];
        }

        // Otherwise, return null since $job_update_cooldown is not valid.
        return null;
    }
}

if(!function_exists('wordpatch_display_job_update_cooldown')) {
    /**
     * This function returns a safe string to display on the frontend to represent $job_update_cooldown.
     * For a full list of valid parameter values, please see the `wordpatch_job_update_cooldowns` function.
     * In the future, it should return a language localization key rather than an English string.
     *
     * PS: An empty string will be returned if $job_update_cooldown is not valid.
     *
     * @param $job_update_cooldown
     * @return string
     */
    function wordpatch_display_job_update_cooldown($wpenv_vars, $job_update_cooldown)
    {
        // Construct a map of all the valid display names.
        $display_names = array(
            wordpatch_job_update_cooldown_inherit() => 'Inherit',
            wordpatch_job_update_cooldown_asap() => 'ASAP',
            wordpatch_job_update_cooldown_one_minute() => 'One Minute',
            wordpatch_job_update_cooldown_two_minutes() => 'Two Minutes',
            wordpatch_job_update_cooldown_three_minutes() => 'Three Minutes',
            wordpatch_job_update_cooldown_five_minutes() => 'Five Minutes',
            wordpatch_job_update_cooldown_seven_minutes() => 'Seven Minutes',
            wordpatch_job_update_cooldown_ten_minutes() => 'Ten Minutes',
            wordpatch_job_update_cooldown_fifteen_minutes() => 'Fifteen Minutes',
            wordpatch_job_update_cooldown_twenty_minutes() => 'Twenty Minutes',
            wordpatch_job_update_cooldown_thirty_minutes() => 'Thirty Minutes'
        );

        // Calculate the display name using our helper function.
        return wordpatch_display_name($display_names, $job_update_cooldown);
    }
}

if(!function_exists('wordpatch_display_update_cooldown')) {
    /**
     * This function returns a safe string to display on the frontend to represent $update_cooldown.
     * For a full list of valid parameter values, please see the `wordpatch_update_cooldowns` function.
     * In the future, it should return a language localization key rather than an English string.
     *
     * PS: An empty string will be returned if $update_cooldown is not valid.
     *
     * @param $update_cooldown
     * @return string
     */
    function wordpatch_display_update_cooldown($wpenv_vars, $update_cooldown)
    {
        // Construct a map of all the valid display names.
        $display_names = array(
            wordpatch_update_cooldown_asap() => 'ASAP',
            wordpatch_update_cooldown_one_minute() => 'One Minute',
            wordpatch_update_cooldown_two_minutes() => 'Two Minutes',
            wordpatch_update_cooldown_three_minutes() => 'Three Minutes',
            wordpatch_update_cooldown_five_minutes() => 'Five Minutes',
            wordpatch_update_cooldown_seven_minutes() => 'Seven Minutes',
            wordpatch_update_cooldown_ten_minutes() => 'Ten Minutes',
            wordpatch_update_cooldown_fifteen_minutes() => 'Fifteen Minutes',
            wordpatch_update_cooldown_twenty_minutes() => 'Twenty Minutes',
            wordpatch_update_cooldown_thirty_minutes() => 'Thirty Minutes'
        );

        // Calculate the display name using our helper function.
        return wordpatch_display_name($display_names, $update_cooldown);
    }
}

if(!function_exists('wordpatch_render_metadesc_job_update_cooldown')) {
    /**
     * This function returns the meta description for the job update cooldown field within the add/edit job page.
     * In the future, it should be adjusted to support more than just the English language.
     *
     * @param $wpenv_vars
     * @return string
     */
    function wordpatch_render_metadesc_job_update_cooldown($wpenv_vars)
    {
        return "How long should WordPatch wait before running this job after a WordPress update?";
    }
}

if(!function_exists('wordpatch_default_job_update_cooldown')) {
    /**
     * This function returns the default job level update cooldown constant value to be used when creating new jobs.
     *
     * PS: This function might also be called to provide a fallback in the event of an invalid $job_update_cooldown.
     *
     * @return string
     */
    function wordpatch_default_job_update_cooldown() {
        return wordpatch_job_update_cooldown_inherit();
    }
}

if(!function_exists('wordpatch_default_update_cooldown')) {
    /**
     * This function returns the default global update cooldown constant value to be used.
     *
     * PS: This function might also be called to provide a fallback in the event of an invalid $update_cooldown.
     *
     * @return string
     */
    function wordpatch_default_update_cooldown() {
        return wordpatch_update_cooldown_three_minutes();
    }
}

if(!function_exists('wordpatch_calculate_update_cooldown')) {
    function wordpatch_calculate_update_cooldown($wpenv_vars)
    {
        $update_cooldown = wordpatch_get_option($wpenv_vars, 'wordpatch_update_cooldown', '');

        if(wordpatch_is_valid_update_cooldown($update_cooldown)) {
            return $update_cooldown;
        }

        return null;
    }
}