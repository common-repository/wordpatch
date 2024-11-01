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
 * Implements the FTP SSL functionality.
 */

if(!function_exists('wordpatch_display_ftp_ssl')) {
    /**
     * @param $ftp_ssl
     * @return mixed
     * @qc
     */
    function wordpatch_display_ftp_ssl($wpenv_vars, $ftp_ssl)
    {
        $display_names = array(
            wordpatch_ftp_ssl_no() => __wt($wpenv_vars, 'NO'),
            wordpatch_ftp_ssl_yes() => __wt($wpenv_vars, 'YES')
        );

        return wordpatch_display_name($display_names, $ftp_ssl);
    }
}

if(!function_exists('wordpatch_ftp_ssl_no')) {
    /**
     * @return string
     * @qc
     */
    function wordpatch_ftp_ssl_no()
    {
        return WORDPATCH_NO;
    }
}

if(!function_exists('wordpatch_ftp_ssl_yes')) {
    /**
     * @return string
     * @qc
     */
    function wordpatch_ftp_ssl_yes()
    {
        return WORDPATCH_YES;
    }
}

if(!function_exists('wordpatch_ftp_ssls')) {
    /**
     * @return array
     * @qc
     */
    function wordpatch_ftp_ssls()
    {
        return array(
            wordpatch_ftp_ssl_no(),
            wordpatch_ftp_ssl_yes()
        );
    }
}

if(!function_exists('wordpatch_is_valid_ftp_ssl')) {
    /**
     * @param $ftp_ssl
     * @return bool
     * @qc
     */
    function wordpatch_is_valid_ftp_ssl($ftp_ssl)
    {
        return in_array($ftp_ssl, wordpatch_ftp_ssls());
    }
}

if(!function_exists('wordpatch_calculate_ftp_ssl')) {
    function wordpatch_calculate_ftp_ssl($wpenv_vars)
    {
        $ftp_ssl = wordpatch_get_option($wpenv_vars, 'wordpatch_ftp_ssl', '');

        if(!wordpatch_is_valid_ftp_ssl($ftp_ssl)) {
            return null;
        }

        return $ftp_ssl;
    }
}