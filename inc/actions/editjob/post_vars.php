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
 * Implements the helper function for grabbing post variables from the edit job page.
 */

if(!function_exists('wordpatch_editjob_post_vars')) {
    /**
     * Calculate the current post variables for the edit job form.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_editjob_post_vars($wpenv_vars, $job, $patches) {
        global $__wordpatch_post;

        $current = array();

        $current['job_title'] = isset($_POST['job_title']) ? trim($_POST['job_title']) :
            trim($job['title']);

        $current['job_path'] = isset($_POST['job_path']) ? wordpatch_sanitize_unix_file_path(trim($__wordpatch_post['job_path'])) :
            wordpatch_sanitize_unix_file_path($job['path']);

        $current['job_enabled'] = (isset($_POST['job_enabled']) && wordpatch_is_valid_job_enabled(strtolower(trim($_POST['job_enabled'])))) ?
            strtolower(trim($_POST['job_enabled'])) : ($job['enabled'] ? wordpatch_job_enabled_yes() : wordpatch_job_enabled_no());

        $current['job_enabled_int'] = $current['job_enabled'] === wordpatch_job_enabled_yes() ? 1 : 0;

        $current['job_maintenance_mode'] = (isset($_POST['job_maintenance_mode']) && wordpatch_is_valid_job_maintenance_mode(strtolower(trim($_POST['job_maintenance_mode'])))) ?
            strtolower(trim($_POST['job_maintenance_mode'])) : $job['maintenance_mode'];

        $current['job_binary_mode'] = (isset($_POST['job_binary_mode']) && wordpatch_is_valid_job_binary_mode(strtolower(trim($_POST['job_binary_mode'])))) ?
            strtolower(trim($_POST['job_binary_mode'])) : $job['binary_mode'];

        $current['job_mode'] = (isset($_POST['job_mode']) && wordpatch_is_valid_job_mode(strtolower(trim($_POST['job_mode'])))) ?
            strtolower(trim($_POST['job_mode'])) : $job['mode'];

        $current['job_retry_count'] = (isset($_POST['job_retry_count']) && wordpatch_is_valid_job_retry_count(strtolower(trim($_POST['job_retry_count'])))) ?
            strtolower(trim($_POST['job_retry_count'])) : $job['retry_count'];

        $current['job_update_cooldown'] = (isset($_POST['job_update_cooldown']) && wordpatch_is_valid_job_update_cooldown(strtolower(trim($_POST['job_update_cooldown'])))) ?
            strtolower(trim($_POST['job_update_cooldown'])) : $job['update_cooldown'];

        $current['job_timer'] = (isset($_POST['job_timer']) && wordpatch_is_valid_job_timer(strtolower(trim($_POST['job_timer'])))) ?
            strtolower(trim($_POST['job_timer'])) : $job['timer'];

        $current['job_patches'] = wordpatch_editjob_post_vars_patches($wpenv_vars, $patches);

        return $current;
    }
}

if(!function_exists('wordpatch_editjob_post_vars_patches')) {
    /**
     * Calculate the current post sort order string variable for the edit job form.
     *
     * @param $wpenv_vars
     * @param $patches
     * @return string
     */
    function wordpatch_editjob_post_vars_patches($wpenv_vars, $patches) {
        // Empty patches array means empty return string.
        if(empty($patches)) {
            return '';
        }

        // First we need to construct a default order for when the data is omitted/invalid.
        $default_order = array();

        // Loop through our patches and construct a default order
        foreach($patches as $patch) {
            $default_order[] = $patch['id'];
        }

        // Calculate the default order string and store it inside current.
        $default_job_patches = implode(',', $default_order);

        // If the patch order was not posted or it is determined to be empty, return the default order string.
        if(!isset($_POST['job_patches']) || trim($_POST['job_patches']) === '') {
            return $default_job_patches;
        }

        // Trim our posted value
        $job_patches = trim($_POST['job_patches']);

        // Explode into pieces
        $patch_pieces = explode(',', $job_patches);

        // Remove duplicates from pieces
        $patch_pieces_dedup = array();

        foreach($patch_pieces as $patch_piece) {
            if(in_array($patch_piece, $patch_pieces_dedup)) {
                continue;
            }

            $patch_pieces_dedup[] = $patch_piece;
        }

        // The count of pieces after dedup-ing must equal the count of patches. If not, return the default patch order string.
        if(count($patch_pieces_dedup) !== count($patches)) {
            return $default_job_patches;
        }

        // We will loop through our pieces and check that all exist inside $patches.
        foreach ($patch_pieces_dedup as $patch_piece) {
            // Variable for whether or not we found our patch.
            $found_patch = false;

            foreach($patches as $patch) {
                if($patch['id'] !== $patch_piece) {
                    continue;
                }

                $found_patch = true;
                break;
            }

            // If we did not find the patch to be valid, simply return the default patch order string.
            if(!$found_patch) {
                return $default_job_patches;
            }
        }

        // If everything exists, then their input was valid. Implode and store into current.
        return implode(',', $patch_pieces_dedup);
    }
}