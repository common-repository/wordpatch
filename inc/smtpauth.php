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

if(!function_exists('wordpatch_smtp_auth_no')) {
    /**
     * @return string
     * @qc
     */
    function wordpatch_smtp_auth_no()
    {
        return WORDPATCH_NO;
    }
}

if(!function_exists('wordpatch_smtp_auth_yes')) {
    /**
     * @return string
     * @qc
     */
    function wordpatch_smtp_auth_yes()
    {
        return WORDPATCH_YES;
    }
}

if(!function_exists('wordpatch_display_smtp_auth')) {
    /**
     * @param $smtp_auth
     * @return mixed
     * @qc
     */
    function wordpatch_display_smtp_auth($wpenv_vars, $smtp_auth)
    {
        $display_names = array(
            wordpatch_smtp_auth_no() => 'No',
            wordpatch_smtp_auth_yes() => 'Yes',
        );

        return wordpatch_display_name($display_names, $smtp_auth);
    }
}

if(!function_exists('wordpatch_is_valid_smtp_auth')) {
    /**
     * @param $smtp_auth
     * @return bool
     * @qc
     */
    function wordpatch_is_valid_smtp_auth($smtp_auth)
    {
        return in_array($smtp_auth, wordpatch_smtp_auths());
    }
}

if(!function_exists('wordpatch_smtp_auths')) {
    /**
     * @return array
     * @qc
     */
    function wordpatch_smtp_auths()
    {
        return array(
            wordpatch_smtp_auth_no(),
            wordpatch_smtp_auth_yes(),
        );
    }
}