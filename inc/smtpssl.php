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
 * Implements the SMTP SSL functionality.
 */

if(!function_exists('wordpatch_smtp_ssls')) {
    /**
     * @return array
     * @qc
     */
    function wordpatch_smtp_ssls()
    {
        return array(
            wordpatch_smtp_ssl_none(),
            wordpatch_smtp_ssl_ssl(),
            wordpatch_smtp_ssl_tls(),
        );
    }
}

if(!function_exists('wordpatch_display_smtp_ssl')) {
    /**
     * @param $smtp_ssl
     * @return mixed
     * @qc
     */
    function wordpatch_display_smtp_ssl($wpenv_vars, $smtp_ssl)
    {
        $display_names = array(
            wordpatch_smtp_ssl_none() => __wt($wpenv_vars, 'SMTP_SSL_NONE'),
            wordpatch_smtp_ssl_ssl() => __wt($wpenv_vars, 'SMTP_SSL_SSL'),
            wordpatch_smtp_ssl_tls() => __wt($wpenv_vars, 'SMTP_SSL_TLS')
        );

        return wordpatch_display_name($display_names, $smtp_ssl);
    }
}

if(!function_exists('wordpatch_is_valid_smtp_ssl')) {
    /**
     * @param $smtp_ssl
     * @return bool
     * @qc
     */
    function wordpatch_is_valid_smtp_ssl($smtp_ssl)
    {
        return in_array($smtp_ssl, wordpatch_smtp_ssls());
    }
}

if(!function_exists('wordpatch_smtp_ssl_none')) {
    /**
     * @return string
     * @qc
     */
    function wordpatch_smtp_ssl_none()
    {
        return 'none';
    }
}

if(!function_exists('wordpatch_smtp_ssl_ssl')) {
    /**
     * @return string
     * @qc
     */
    function wordpatch_smtp_ssl_ssl()
    {
        return 'ssl';
    }
}

if(!function_exists('wordpatch_smtp_ssl_tls')) {
    /**
     * @return string
     * @qc
     */
    function wordpatch_smtp_ssl_tls()
    {
        return 'tls';
    }
}