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
 * Implements the global and job level maintenance modes.
 */

if(!function_exists('wordpatch_plugins_loaded_maintenance')) {
    /*
     * Performs the request-level internal maintenance mode check for WordPatch.
     * Attached the the action `plugins_loaded`.
     */
    function wordpatch_plugins_loaded_maintenance()
    {
        // Since this is called from WordPress, grab the WP environment
        $wpenv_vars = wordpatch_wordpress_env();

        // This check should first grab the global setting
        $maintenance_mode = wordpatch_calculate_maintenance_mode($wpenv_vars);

        // If maintenance mode is not set, or it is disabled globally then just exit early.
        if ($maintenance_mode === null || $maintenance_mode === wordpatch_maintenance_mode_no()) {
            return;
        }

        // Next, we should calculate jobs that are either pending or busy. If they are stale, we can omit them from the
        // maintenance lock.
        $pending_job_ids = wordpatch_get_pending_job_ids($wpenv_vars);
        $busy_job_ids = wordpatch_get_busy_job_ids($wpenv_vars);

        // Combine the two lists into one
        $unique_job_ids = array();

        foreach ($pending_job_ids as $pending_job_id_single) {
            if (!in_array($pending_job_id_single, $unique_job_ids)) {
                $unique_job_ids[] = $pending_job_id_single;
            }
        }

        foreach ($busy_job_ids as $busy_job_id_single) {
            if (!in_array($busy_job_id_single, $unique_job_ids)) {
                $unique_job_ids[] = $busy_job_id_single;
            }
        }

        // Determine the job-specific maintenance mode setting by grabbing all the jobs
        $job_rows = wordpatch_get_jobs_by_ids($wpenv_vars, $unique_job_ids);
        $should_present_lock = false;

        foreach ($job_rows as $job_row_single) {
            $job_maintenance_mode = $job_row_single['maintenance_mode'];

            if ($job_maintenance_mode === wordpatch_job_maintenance_mode_yes() || $job_maintenance_mode === wordpatch_job_maintenance_mode_inherit()) {
                $should_present_lock = true;
                break;
            }
        }

        if (!$should_present_lock) {
            return;
        }

        // The below code is copied from WordPress and just SLIGHTLY adapted to use the WP env vars
        $wp_content_dir = wordpatch_env_get($wpenv_vars, 'wp_content_dir');

        if (file_exists($wp_content_dir . '/maintenance.php')) {
            require_once($wp_content_dir . '/maintenance.php');
            die();
        }

        wp_load_translations_early();

        $protocol = wp_get_server_protocol();
        header("$protocol 503 Service Unavailable", true, 503);
        header('Content-Type: text/html; charset=utf-8');
        header('Retry-After: 600');
        ?>
        <!DOCTYPE html>
        <html xmlns="http://www.w3.org/1999/xhtml"<?php if (is_rtl()) echo ' dir="rtl"'; ?>>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
            <title><?php _e('Maintenance'); ?></title>
        </head>
        <body>
        <h1><?php _e('Briefly unavailable for scheduled maintenance. Check back in a minute.'); ?></h1>
        </body>
        </html>
        <?php
        die();
    }
}

if(!function_exists('wordpatch_default_maintenance_mode')) {
    /**
     * Calculates the default maintenance mode.
     *
     * @return string
     */
    function wordpatch_default_maintenance_mode() {
        return wordpatch_maintenance_mode_yes();
    }
}

if(!function_exists('wordpatch_default_job_maintenance_mode')) {
    /**
     * Calculates the default job maintenance mode.
     *
     * @return string
     */
    function wordpatch_default_job_maintenance_mode() {
        return wordpatch_job_maintenance_mode_inherit();
    }
}

if(!function_exists('wordpatch_display_maintenance_mode')) {
    /**
     * Calculates the display maintenance mode.
     *
     * @param $wpenv_vars
     * @param $maintenance_mode
     * @return string
     */
    function wordpatch_display_maintenance_mode($wpenv_vars, $maintenance_mode)
    {
        $display_names = array(
            wordpatch_maintenance_mode_yes() => __wt($wpenv_vars, 'YES'),
            wordpatch_maintenance_mode_no() => __wt($wpenv_vars, 'NO'),
        );

        return wordpatch_display_name($display_names, $maintenance_mode);
    }
}

if(!function_exists('wordpatch_display_job_maintenance_mode')) {
    /**
     * Calculates the display job maintenance mode.
     *
     * @param $wpenv_vars
     * @param $job_maintenance_mode
     * @return string
     */
    function wordpatch_display_job_maintenance_mode($wpenv_vars, $job_maintenance_mode)
    {
        $display_names = array(
            wordpatch_job_maintenance_mode_inherit() => __wt($wpenv_vars, 'INHERIT'),
            wordpatch_job_maintenance_mode_yes() => __wt($wpenv_vars, 'YES'),
            wordpatch_job_maintenance_mode_no() => __wt($wpenv_vars, 'NO'),
        );

        return wordpatch_display_name($display_names, $job_maintenance_mode);
    }
}

if(!function_exists('wordpatch_maintenance_modes')) {
    /**
     * Calculates an array of all possible maintenance mode values.
     *
     * @return array
     */
    function wordpatch_maintenance_modes()
    {
        return array(
            wordpatch_maintenance_mode_yes(),
            wordpatch_maintenance_mode_no(),
        );
    }
}

if(!function_exists('wordpatch_is_valid_maintenance_mode')) {
    /**
     * Checks if the passed in value is a valid maintenance mode value.
     *
     * @param $maintenance_mode
     * @return bool
     */
    function wordpatch_is_valid_maintenance_mode($maintenance_mode)
    {
        return in_array($maintenance_mode, wordpatch_maintenance_modes());
    }
}

if(!function_exists('wordpatch_maintenance_mode_yes')) {
    /**
     * Calculates the maintenance mode yes value.
     *
     * @return string
     */
    function wordpatch_maintenance_mode_yes()
    {
        return WORDPATCH_YES;
    }
}

if(!function_exists('wordpatch_maintenance_mode_no')) {
    /**
     * Calculates the maintenance mode no value.
     *
     * @return string
     */
    function wordpatch_maintenance_mode_no()
    {
        return WORDPATCH_NO;
    }
}

if(!function_exists('wordpatch_job_maintenance_modes')) {
    /**
     * Calculates an array of all possible job maintenance mode values.
     *
     * @return array
     */
    function wordpatch_job_maintenance_modes()
    {
        return array(
            wordpatch_job_maintenance_mode_inherit(),
            wordpatch_job_maintenance_mode_yes(),
            wordpatch_job_maintenance_mode_no(),
        );
    }
}

if(!function_exists('wordpatch_job_maintenance_mode_yes')) {
    /**
     * Calculates the job maintenance mode yes value.
     *
     * @return string
     */
    function wordpatch_job_maintenance_mode_yes()
    {
        return WORDPATCH_YES;
    }
}

if(!function_exists('wordpatch_job_maintenance_mode_no')) {
    /**
     * Calculates the job maintenance mode no value.
     *
     * @return string
     */
    function wordpatch_job_maintenance_mode_no()
    {
        return WORDPATCH_NO;
    }
}

if(!function_exists('wordpatch_job_maintenance_mode_inherit')) {
    /**
     * Calculates the job maintenance mode inherit value.
     *
     * @return string
     */
    function wordpatch_job_maintenance_mode_inherit()
    {
        return 'inherit';
    }
}

if(!function_exists('wordpatch_is_valid_job_maintenance_mode')) {
    /**
     * Checks if the passed in value is a valid job maintenance mode value.
     *
     * @param $job_maintenance_mode
     * @return bool
     */
    function wordpatch_is_valid_job_maintenance_mode($job_maintenance_mode)
    {
        return in_array($job_maintenance_mode, wordpatch_job_maintenance_modes());
    }
}

if(!function_exists('wordpatch_calculate_maintenance_mode')) {
    function wordpatch_calculate_maintenance_mode($wpenv_vars)
    {
        $maintenance_mode = wordpatch_get_option($wpenv_vars, 'wordpatch_maintenance_mode', '');

        if(wordpatch_is_valid_maintenance_mode($maintenance_mode)) {
            return $maintenance_mode;
        }

        return null;
    }
}