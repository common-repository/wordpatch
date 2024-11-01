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
 * Implements the helper function for grabbing post variables from the new job page.
 */

if(!function_exists('wordpatch_newjob_post_vars')) {
    /**
     * Calculate the current post variables for the new job form.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_newjob_post_vars($wpenv_vars) {
        global $__wordpatch_post;

        $current = array();

        $current['job_title'] = isset($_POST['job_title']) ? trim($_POST['job_title']) : '';

        $current['job_path'] = isset($_POST['job_path']) ? wordpatch_sanitize_unix_file_path(trim($__wordpatch_post['job_path'])) : '';

        $current['job_enabled'] = (isset($_POST['job_enabled']) && wordpatch_is_valid_job_enabled(strtolower(trim($_POST['job_enabled'])))) ?
            strtolower(trim($_POST['job_enabled'])) : wordpatch_default_job_enabled();

        $current['job_enabled_int'] = $current['job_enabled'] === wordpatch_job_enabled_yes() ? 1 : 0;

        $current['job_maintenance_mode'] = (isset($_POST['job_maintenance_mode']) && wordpatch_is_valid_job_maintenance_mode(strtolower(trim($_POST['job_maintenance_mode'])))) ?
            strtolower(trim($_POST['job_maintenance_mode'])) : wordpatch_default_job_maintenance_mode();

        $current['job_binary_mode'] = (isset($_POST['job_binary_mode']) && wordpatch_is_valid_job_binary_mode(strtolower(trim($_POST['job_binary_mode'])))) ?
            strtolower(trim($_POST['job_binary_mode'])) : wordpatch_default_job_binary_mode();

        $current['job_mode'] = (isset($_POST['job_mode']) && wordpatch_is_valid_job_mode(strtolower(trim($_POST['job_mode'])))) ?
            strtolower(trim($_POST['job_mode'])) : wordpatch_default_job_mode();

        $current['job_retry_count'] = (isset($_POST['job_retry_count']) && wordpatch_is_valid_job_retry_count(strtolower(trim($_POST['job_retry_count'])))) ?
            strtolower(trim($_POST['job_retry_count'])) : wordpatch_default_job_retry_count();

        $current['job_update_cooldown'] = (isset($_POST['job_update_cooldown']) && wordpatch_is_valid_job_update_cooldown(strtolower(trim($_POST['job_update_cooldown'])))) ?
            strtolower(trim($_POST['job_update_cooldown'])) : wordpatch_default_job_update_cooldown();

        $current['job_timer'] = (isset($_POST['job_timer']) && wordpatch_is_valid_job_timer(strtolower(trim($_POST['job_timer'])))) ?
            strtolower(trim($_POST['job_timer'])) : wordpatch_default_job_timer();

        return $current;
    }
}