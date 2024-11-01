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
 * Implements the filesystem functionality.
 *
 * PS: If you really want to know how it works, you probably want to look
 * at `inc/wordpress/wordpress-filesystem.php`.
 */

if(!function_exists('wordpatch_calculate_ftp_pass')) {
    function wordpatch_calculate_ftp_pass($wpenv_vars)
    {
        $ftp_pass = wordpatch_get_option($wpenv_vars, 'wordpatch_ftp_pass', '');

        if(trim($ftp_pass) !== '') {
            return $ftp_pass;
        }

        return null;
    }
}

if(!function_exists('wordpatch_calculate_ftp_base')) {
    function wordpatch_calculate_ftp_base($wpenv_vars)
    {
        $ftp_base = wordpatch_get_option($wpenv_vars, 'wordpatch_ftp_base', '');
        $ftp_base = trim($ftp_base);

        if($ftp_base !== '') {
            return $ftp_base;
        }

        return null;
    }
}

if(!function_exists('wordpatch_calculate_ftp_content_dir')) {
    function wordpatch_calculate_ftp_content_dir($wpenv_vars)
    {
        $ftp_content_dir = wordpatch_get_option($wpenv_vars, 'wordpatch_ftp_content_dir', '');
        $ftp_content_dir = trim($ftp_content_dir);

        if($ftp_content_dir !== '') {
            return $ftp_content_dir;
        }

        return null;
    }
}

if(!function_exists('wordpatch_calculate_ftp_plugin_dir')) {
    function wordpatch_calculate_ftp_plugin_dir($wpenv_vars)
    {
        $ftp_plugin_dir = wordpatch_get_option($wpenv_vars, 'wordpatch_ftp_plugin_dir', '');
        $ftp_plugin_dir = trim($ftp_plugin_dir);

        if($ftp_plugin_dir !== '') {
            return $ftp_plugin_dir;
        }

        return null;
    }
}

if(!function_exists('wordpatch_calculate_ftp_pubkey')) {
    function wordpatch_calculate_ftp_pubkey($wpenv_vars)
    {
        $ftp_pubkey = wordpatch_get_option($wpenv_vars, 'wordpatch_ftp_pubkey', '');
        $ftp_pubkey = trim($ftp_pubkey);

        if($ftp_pubkey !== '') {
            return $ftp_pubkey;
        }

        return null;
    }
}

if(!function_exists('wordpatch_calculate_ftp_prikey')) {
    function wordpatch_calculate_ftp_prikey($wpenv_vars)
    {
        $ftp_prikey = wordpatch_get_option($wpenv_vars, 'wordpatch_ftp_prikey', '');
        $ftp_prikey = trim($ftp_prikey);

        if($ftp_prikey !== '') {
            return $ftp_prikey;
        }

        return null;
    }
}

if(!function_exists('wordpatch_calculate_ftp_user')) {
    function wordpatch_calculate_ftp_user($wpenv_vars)
    {
        $ftp_user = wordpatch_get_option($wpenv_vars, 'wordpatch_ftp_user', '');
        $ftp_user = trim($ftp_user);

        if($ftp_user !== '') {
            return $ftp_user;
        }

        return null;
    }
}

if(!function_exists('wordpatch_calculate_ftp_host')) {
    function wordpatch_calculate_ftp_host($wpenv_vars)
    {
        $ftp_host = wordpatch_get_option($wpenv_vars, 'wordpatch_ftp_host', '');
        $ftp_host = trim($ftp_host);

        if($ftp_host !== '') {
            return $ftp_host;
        }

        return null;
    }
}

if(!function_exists('wordpatch_calculate_fs_chmod_file')) {
    function wordpatch_calculate_fs_chmod_file($wpenv_vars)
    {
        $fs_chmod_file = wordpatch_get_option($wpenv_vars, 'wordpatch_fs_chmod_file', '');
        $fs_chmod_file = trim($fs_chmod_file);

        if($fs_chmod_file !== '') {
            $octal_val = base_convert($fs_chmod_file, 8, 10);
            return wordpatch_get_display_octal_from_int($octal_val);
        }

        return null;
    }
}

if(!function_exists('wordpatch_calculate_fs_chmod_dir')) {
    function wordpatch_calculate_fs_chmod_dir($wpenv_vars)
    {
        $fs_chmod_dir = wordpatch_get_option($wpenv_vars, 'wordpatch_fs_chmod_dir', '');
        $fs_chmod_dir = trim($fs_chmod_dir);

        if($fs_chmod_dir !== '') {
            $octal_val = base_convert($fs_chmod_dir, 8, 10);
            return wordpatch_get_display_octal_from_int($octal_val);
        }

        return null;
    }
}

if(!function_exists('wordpatch_calculate_fs_configured')) {
    function wordpatch_calculate_fs_configured($wpenv_vars)
    {
        $db_val = wordpatch_get_option($wpenv_vars, 'wordpatch_fs_configured', WORDPATCH_NO);
        $db_val = trim($db_val);

        if($db_val !== '' && strtolower(trim($db_val)) === WORDPATCH_YES) {
            return WORDPATCH_YES;
        }

        return WORDPATCH_NO;
    }
}

if(!function_exists('wordpatch_calculate_fs_info')) {
    function wordpatch_calculate_fs_info($wpenv_vars) {
        $fs_info = array();

        $fs_info['fs_method'] = wordpatch_calculate_fs_method($wpenv_vars);
        $fs_info['fs_timeout'] = wordpatch_calculate_fs_timeout($wpenv_vars);
        $fs_info['fs_chmod_file'] = wordpatch_calculate_fs_chmod_file($wpenv_vars);
        $fs_info['fs_chmod_file_int'] = $fs_info['fs_chmod_file'] === null ? 0 :
            wordpatch_convert_octal_to_dec($fs_info['fs_chmod_file']);
        $fs_info['fs_chmod_dir'] = wordpatch_calculate_fs_chmod_dir($wpenv_vars);
        $fs_info['fs_chmod_dir_int'] = $fs_info['fs_chmod_dir'] === null ? 0 :
            wordpatch_convert_octal_to_dec($fs_info['fs_chmod_dir']);
        $fs_info['ftp_prikey'] = wordpatch_calculate_ftp_prikey($wpenv_vars);
        $fs_info['ftp_pubkey'] = wordpatch_calculate_ftp_pubkey($wpenv_vars);
        $fs_info['ftp_pass'] = wordpatch_calculate_ftp_pass($wpenv_vars);
        $fs_info['ftp_host'] = wordpatch_calculate_ftp_host($wpenv_vars);
        $fs_info['ftp_user'] = wordpatch_calculate_ftp_user($wpenv_vars);
        $fs_info['ftp_base'] = wordpatch_calculate_ftp_base($wpenv_vars);
        $fs_info['ftp_content_dir'] = wordpatch_calculate_ftp_content_dir($wpenv_vars);
        $fs_info['ftp_plugin_dir'] = wordpatch_calculate_ftp_plugin_dir($wpenv_vars);
        $fs_info['ftp_ssl'] = wordpatch_calculate_ftp_ssl($wpenv_vars);

        return $fs_info;
    }
}

if(!function_exists('wordpatch_filesystem_begin_helper')) {
    function wordpatch_filesystem_begin_helper($wpenv_vars) {
        $current_fs_info = wordpatch_calculate_fs_info($wpenv_vars);

        $fs_begin = wordpatch_filesystem_begin($wpenv_vars, $current_fs_info['fs_method'], $current_fs_info['ftp_base'],
            $current_fs_info['ftp_content_dir'], $current_fs_info['ftp_plugin_dir'], $current_fs_info['ftp_pubkey'],
            $current_fs_info['ftp_prikey'], $current_fs_info['ftp_user'], $current_fs_info['ftp_pass'], $current_fs_info['ftp_host'],
            $current_fs_info['ftp_ssl'], $current_fs_info['fs_chmod_file'], $current_fs_info['fs_chmod_dir'], $current_fs_info['fs_timeout']);

        return $fs_begin;
    }
}

if(!function_exists('wordpatch_remove_bom')) {
    function wordpatch_remove_bom($str) {
        $bom = pack("CCC", 0xef, 0xbb, 0xbf);

        if (0 === strncmp($str, $bom, 3)) {
            $str = substr($str, 3);
        }

        return $str;
    }
}

if(!function_exists('wordpatch_normalize_to_wproot')) {
    function wordpatch_normalize_to_wproot($wpenv_vars, $file_path) {
        $file_path = wordpatch_sanitize_unix_file_path($file_path, true, false);
        $wpenv_vars_sanitized = wordpatch_trailingslashit(wordpatch_sanitize_unix_file_path($wpenv_vars['wp_root_dir'], true, false));

        if(substr($file_path, 0, strlen($wpenv_vars_sanitized)) !== $wpenv_vars_sanitized) {
            return null;
        }

        return substr($file_path, strlen($wpenv_vars_sanitized));
    }
}

if(!function_exists('wordpatch_fix_path_for_os')) {
    function wordpatch_fix_path_for_os($path, $os = null, $unix_add_leading_slash = true)
    {
        if($os === null) {
            if (strtolower(substr(PHP_OS, 0, 3)) === 'win') {
                $os = 'windows';
            } else {
                $os = 'unix';
            }
        }

        if($os === 'windows') {
            $path = str_replace('/', '\\', $path);
        } else {
            $path = str_replace('\\', '/', $path);
            if($unix_add_leading_slash) {
                if(substr($path, 0, 1) !== '/') {
                    $path = '/' . $path;
                }
            }
        }

        return $path;
    }
}

if(!function_exists('wordpatch_get_display_directory')) {
    function wordpatch_get_display_directory($path)
    {
        $path = wordpatch_sanitize_unix_file_path($path . '/', true, false);
        return wordpatch_fix_path_for_os($path) . DIRECTORY_SEPARATOR;
    }
}

if(!function_exists('wordpatch_get_display_relative_directory')) {
    function wordpatch_get_display_relative_directory($path)
    {
        $path = wordpatch_sanitize_unix_file_path($path . '/', true, true);
        $result = wordpatch_fix_path_for_os($path) . DIRECTORY_SEPARATOR;
        $result_len = strlen($result);
        $allslashes = true;
        for($x = 0; $x < $result_len; $x++) {
            $charAtX = substr($result, $x, 1);
            if($charAtX === '/' || $charAtX === '\\') {
                continue;
            }
            $allslashes = false;
            break;
        }

        if($allslashes) {
            return '';
        }

        return $result;
    }
}

if(!function_exists('wordpatch_dir_is_empty')) {
    /**
     * Checks if a directory is empty.
     *
     * @param $dir
     * @return bool
     */
    function wordpatch_dir_is_empty($dir) {
        $handle = opendir($dir);

        while (false !== ($entry = readdir($handle))) {
            if ($entry !== "." && $entry !== "..") {
                return false;
            }
        }

        return true;
    }
}
