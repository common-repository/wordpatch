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
 * The following functions guess field values for each step of the rescue configuration wizard.
 * These functions are called from `wordpatch_configrescue_post_vars`.
 */

if(!function_exists('wordpatch_configrescue_guess_rescue_path')) {
    /**
     * Guess the best rescue path value to be used for the rescue configuration wizard.
     *
     * @param $wpenv_vars
     * @return mixed|string
     */
    function wordpatch_configrescue_guess_rescue_path($wpenv_vars)
    {
        // Construct the ideal rescue script path (ie: wp-admin/wordpatch-rescue.php)
        return wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_admin_path')) . 'wordpatch-rescue.php';
    }
}