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
 * Implements the shared functionality regarding filesystem methods.
 * WordPatch allows for direct, SSH, FTP via extension, and FTP via socket (same exact options as WordPress).
 */

if(!function_exists('wordpatch_fs_methods')) {
    /**
     * Calculates an array of all possible filesystem method values.
     *
     * @return array
     */
    function wordpatch_fs_methods()
    {
        return array(
            wordpatch_fs_method_direct(),
            wordpatch_fs_method_ssh2(),
            wordpatch_fs_method_ftpext(),
            wordpatch_fs_method_ftpsockets(),
        );
    }
}

if(!function_exists('wordpatch_is_valid_fs_method')) {
    /**
     * Checks if a filesystem method value is valid.
     *
     * @param $fs_method
     * @return bool
     */
    function wordpatch_is_valid_fs_method($fs_method)
    {
        return in_array($fs_method, wordpatch_fs_methods());
    }
}

if(!function_exists('wordpatch_fs_method_direct')) {
    /**
     * Calculates the filesystem method direct value.
     *
     * @return string
     */
    function wordpatch_fs_method_direct()
    {
        return 'direct';
    }
}

if(!function_exists('wordpatch_fs_method_ssh2')) {
    /**
     * Calculates the filesystem method ssh2 value.
     *
     * @return string
     */
    function wordpatch_fs_method_ssh2()
    {
        return 'ssh2';
    }
}

if(!function_exists('wordpatch_fs_method_ftpext')) {
    /**
     * Calculates the filesystem method ftpext value.
     *
     * @return string
     */
    function wordpatch_fs_method_ftpext()
    {
        return 'ftpext';
    }
}

if(!function_exists('wordpatch_fs_method_ftpsockets')) {
    /**
     * Calculates the filesystem method ftpsockets value.
     *
     * @return string
     */
    function wordpatch_fs_method_ftpsockets()
    {
        return 'ftpsockets';
    }
}

if(!function_exists('wordpatch_display_fs_method')) {
    /**
     * Calculates the display filesystem method text.
     *
     * @param $wpenv_vars
     * @param $fs_method
     * @return string
     */
    function wordpatch_display_fs_method($wpenv_vars, $fs_method)
    {
        $display_names = array(
            wordpatch_fs_method_direct() => __wt($wpenv_vars, 'FS_METHOD_DIRECT'),
            wordpatch_fs_method_ssh2() => __wt($wpenv_vars, 'FS_METHOD_SSH2'),
            wordpatch_fs_method_ftpext() => __wt($wpenv_vars, 'FS_METHOD_FTPEXT'),
            wordpatch_fs_method_ftpsockets() => __wt($wpenv_vars, 'FS_METHOD_FTPSOCKETS')
        );

        return wordpatch_display_name($display_names, $fs_method);
    }
}

if(!function_exists('wordpatch_calculate_fs_method')) {
    function wordpatch_calculate_fs_method($wpenv_vars)
    {
        $fs_method = wordpatch_get_option($wpenv_vars, 'wordpatch_fs_method', '');

        if(!wordpatch_is_valid_fs_method($fs_method)) {
            return null;
        }

        return $fs_method;
    }
}