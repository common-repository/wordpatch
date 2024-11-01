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
 * Implements the job enabled functionality.
 */

if(!function_exists('wordpatch_job_enableds')) {
    /**
     * @return array
     * @qc
     */
    function wordpatch_job_enableds()
    {
        return array(
            wordpatch_job_enabled_yes(),
            wordpatch_job_enabled_no()
        );
    }
}

if(!function_exists('wordpatch_job_enabled_no')) {
    /**
     * @return string
     * @qc
     */
    function wordpatch_job_enabled_no()
    {
        return WORDPATCH_NO;
    }
}

if(!function_exists('wordpatch_job_enabled_yes')) {
    /**
     * @return string
     * @qc
     */
    function wordpatch_job_enabled_yes()
    {
        return WORDPATCH_YES;
    }
}

if(!function_exists('wordpatch_default_job_enabled')) {
    function wordpatch_default_job_enabled() {
        return wordpatch_job_enabled_yes();
    }
}

if(!function_exists('wordpatch_display_job_enabled')) {
    /**
     * @param $job_enabled
     * @return mixed
     * @qc
     */
    function wordpatch_display_job_enabled($wpenv_vars, $job_enabled)
    {
        $display_names = array(
            wordpatch_job_enabled_yes() => __wt($wpenv_vars, 'ENABLED'),
            wordpatch_job_enabled_no() => __wt($wpenv_vars, 'DISABLED')
        );

        return wordpatch_display_name($display_names, $job_enabled);
    }
}

if(!function_exists('wordpatch_is_valid_job_enabled')) {
    /**
     * @param $job_enabled
     * @return bool
     * @qc
     */
    function wordpatch_is_valid_job_enabled($job_enabled)
    {
        return in_array($job_enabled, wordpatch_job_enableds());
    }
}