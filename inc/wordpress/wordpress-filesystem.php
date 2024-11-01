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
 * This file is not allowed to depend on WordPress.
 * Made with love (and a little bit of insanity) in Virginia. ♥
 * Feel free to use parts of this file to emulate a modern WordPress installation.
 * MU (multisite) is not yet supported and various notes have been made for future support.
 */

if(!isset($__wordpatch_filesystem_context)) {
    /**
     * Global used to wordpatch_filesystem_* functions
     */
    $__wordpatch_filesystem_context = null;
}

if(!function_exists('wordpatch_filesystem_get_context')) {
    /**
     * @return mixed
     * @qc
     */
    function wordpatch_filesystem_get_context()
    {
        global $__wordpatch_filesystem_context;
        return $__wordpatch_filesystem_context;
    }
}

if(!function_exists('wordpatch_filesystem_begin')) {
    /**
     * Begin Wordpatch filesystem operations (setup the filesystem context and connect if necessary)
     */
    function wordpatch_filesystem_begin(
        $wpenv_vars,
        $fs_method,
        $ftp_base,
        $ftp_content_dir,
        $ftp_plugin_dir,
        $ftp_pubkey,
        $ftp_prikey,
        $ftp_user,
        $ftp_pass,
        $ftp_host,
        $ftp_ssl,
        $fs_chmod_file,
        $fs_chmod_dir,
        $fs_timeout
    )
    {
        global $__wordpatch_filesystem_context;

        $__wordpatch_filesystem_context = array(
            'settings' => array(
                'fs_method' => $fs_method,
                'ftp_base' => $ftp_base,
                'ftp_content_dir' => $ftp_content_dir,
                'ftp_plugin_dir' => $ftp_plugin_dir,
                'ftp_pubkey' => $ftp_pubkey,
                'ftp_prikey' => $ftp_prikey,
                'ftp_user' => $ftp_user,
                'ftp_pass' => $ftp_pass,
                'ftp_host' => $ftp_host,
                'ftp_ssl' => $ftp_ssl,
                'fs_chmod_file' => $fs_chmod_file,
                'fs_chmod_file_int' => $fs_chmod_file === null ? 0 : wordpatch_convert_octal_to_dec($fs_chmod_file),
                'fs_chmod_dir' => $fs_chmod_dir,
                'fs_chmod_dir_int' => $fs_chmod_dir === null ? 0 : wordpatch_convert_octal_to_dec($fs_chmod_dir),
                'fs_timeout' => $fs_timeout
            ),
            'connected' => false
        );

        $ftp_ssl = ($ftp_ssl === true || $ftp_ssl == 'yes' || $ftp_ssl == 'YES' || $ftp_ssl == 'true' || $ftp_ssl == '1') ? true : false;

        if (!wordpatch_is_valid_fs_method($fs_method)) {
            return false;
        }

        if ($fs_method == 'direct') {
            $__wordpatch_filesystem_context['connected'] = true;
            return true;
        }

        if ($fs_method == 'ssh2') {
            if (!function_exists('ssh2_connect')) {
                return false;
            }

            $has_key = trim($ftp_prikey) !== '' && trim($ftp_pubkey) !== '';
            $port_pos = strpos($ftp_host, ':');
            $host_only = trim($ftp_host);
            $port_only = 22;

            if ($port_pos !== false) {
                $host_only = substr($ftp_host, 0, $port_pos);
                $port_only = max(0, (int)substr($ftp_host, $port_pos + 1));
            }

            $__wordpatch_filesystem_context['link'] = null;

            if (!$has_key) {
                $__wordpatch_filesystem_context['link'] = @ssh2_connect($host_only, $port_only);
            } else {
                $__wordpatch_filesystem_context['link'] = @ssh2_connect($host_only, $port_only, array('hostkey' => 'ssh-rsa'));
            }

            if (!$__wordpatch_filesystem_context['link']) {
                return false;
            }

            if (!$has_key) {
                if (!@ssh2_auth_password($__wordpatch_filesystem_context['link'], $ftp_user, $ftp_pass)) {
                    return false;
                }
            } else {
                if (!@ssh2_auth_pubkey_file($__wordpatch_filesystem_context['link'], $ftp_user, $ftp_pubkey, $ftp_prikey, $ftp_pass)) {
                    return false;
                }
            }

            $__wordpatch_filesystem_context['sftp_link'] = ssh2_sftp($__wordpatch_filesystem_context['link']);

            if (!$__wordpatch_filesystem_context['sftp_link']) {
                return false;
            }

            $__wordpatch_filesystem_context['connected'] = true;
            return true;
        }

        if ($fs_method == 'ftpext') {
            if (!function_exists('ftp_connect')) {
                return false;
            }

            $__wordpatch_filesystem_context['link'] = null;
            $port_pos = strpos($ftp_host, ':');
            $host_only = trim($ftp_host);
            $port_only = 21;
            $connect_timeout = $fs_timeout;

            if ($port_pos !== false) {
                $host_only = substr($ftp_host, 0, $port_pos);
                $port_only = max(0, (int)substr($ftp_host, $port_pos + 1));
            }

            if ($ftp_ssl) {
                if (!function_exists('ftp_ssl_connect')) {
                    return false;
                }

                $__wordpatch_filesystem_context['link'] = @ftp_ssl_connect($host_only, $port_only, $connect_timeout);
            } else {
                $__wordpatch_filesystem_context['link'] = @ftp_connect($host_only, $port_only, $connect_timeout);
            }

            if (!$__wordpatch_filesystem_context['link']) {
                return false;
            }

            if (!@ftp_login($__wordpatch_filesystem_context['link'], $ftp_user, $ftp_pass)) {
                return false;
            }

            @ftp_pasv($__wordpatch_filesystem_context['link'], true);

            if (@ftp_get_option($__wordpatch_filesystem_context['link'], FTP_TIMEOUT_SEC) < $connect_timeout) {
                @ftp_set_option($this->link, FTP_TIMEOUT_SEC, $connect_timeout);
            }

            $__wordpatch_filesystem_context['connected'] = true;
            return true;
        }

        if ($fs_method == 'ftpsockets') {
            $__wordpatch_filesystem_context['link'] = null;
            $port_pos = strpos($ftp_host, ':');
            $host_only = trim($ftp_host);
            $port_only = 21;
            $connect_timeout = $fs_timeout;

            if ($port_pos !== false) {
                $host_only = substr($ftp_host, 0, $port_pos);
                $port_only = max(0, (int)substr($ftp_host, $port_pos + 1));
            }

            include_once(dirname(__FILE__) . '/classes/class-ftp.php');
            $__wordpatch_filesystem_context['ftp'] = new wordpatch_ftp();
            if (!$__wordpatch_filesystem_context['ftp']) {
                return false;
            }

            $__wordpatch_filesystem_context['ftp']->setTimeout($connect_timeout);

            if (!$__wordpatch_filesystem_context['ftp']->SetServer($host_only, $port_only)) {
                return false;
            }

            if (!$__wordpatch_filesystem_context['ftp']->connect()) {
                return false;
            }

            if (!$__wordpatch_filesystem_context['ftp']->login($ftp_user, $ftp_pass)) {
                return false;
            }

            $__wordpatch_filesystem_context['ftp']->SetType(FTP_BINARY);
            $__wordpatch_filesystem_context['ftp']->Passive(true);
            $__wordpatch_filesystem_context['ftp']->setTimeout($connect_timeout);

            $__wordpatch_filesystem_context['connected'] = true;
            return true;
        }

        return false;
    }
}

if(!function_exists('wordpatch_filesystem_ssh2_run_command')) {
    function wordpatch_filesystem_ssh2_run_command($wpenv_vars, $command, $returnbool = false) {
        global $__wordpatch_filesystem_context;

        if(!$__wordpatch_filesystem_context || !$__wordpatch_filesystem_context['connected']) {
            return false;
        }

        $link = $__wordpatch_filesystem_context['link'];

        if(!($stream = ssh2_exec($link, $command))) {
            return false;
        }

        $fs_timeout = $__wordpatch_filesystem_context['settings']['fs_timeout'];

        stream_set_blocking($stream, true);
        @stream_set_timeout($stream, $fs_timeout);
        $data = stream_get_contents($stream);
        fclose($stream);

        if($returnbool) {
            return ($data === false) ? false : '' != trim($data);
        } else {
            return $data;
        }
    }
}

if(!function_exists('wordpatch_filesystem_is_dir')) {
    function wordpatch_filesystem_is_dir($wpenv_vars, $relative_path, $path_type) {
        global $__wordpatch_filesystem_context;

        if(!$__wordpatch_filesystem_context || !$__wordpatch_filesystem_context['connected']) {
            return false;
        }

        $relative_path = wordpatch_sanitize_unix_file_path($relative_path, true, true);

        $fs_method = $__wordpatch_filesystem_context['settings']['fs_method'];
        $full_path = wordpatch_filesystem_build_full_path($wpenv_vars, $relative_path, $path_type);

        if (!wordpatch_is_valid_fs_method($fs_method)) {
            return false;
        }

        if ($fs_method === wordpatch_fs_method_direct()) {
            return @is_dir($full_path);
        }

        if ($fs_method === wordpatch_fs_method_ssh2()) {
            return is_dir(wordpatch_filesystem_ssh2_sftp_path($wpenv_vars, $relative_path, $path_type));
        }

        if ($fs_method === wordpatch_fs_method_ftpext()) {
            $cwd = wordpatch_filesystem_cwd($wpenv_vars);
            $result = @ftp_chdir($__wordpatch_filesystem_context['link'], wordpatch_trailingslashit($full_path));

            if($result && $full_path == wordpatch_filesystem_cwd($wpenv_vars) || wordpatch_filesystem_cwd($wpenv_vars) != $cwd) {
                @ftp_chdir($__wordpatch_filesystem_context['link'], $cwd);
                return true;
            }

            return false;
        }

        if ($fs_method === wordpatch_fs_method_ftpsockets()) {
            $cwd = wordpatch_filesystem_cwd($wpenv_vars);

            if(wordpatch_filesystem_chdir($wpenv_vars, $full_path)) {
                wordpatch_filesystem_chdir($wpenv_vars, $cwd);
                return true;
            }

            return false;
        }

        return false;
    }
}

if(!function_exists('wordpatch_filesystem_chdir')) {
    function wordpatch_filesystem_chdir($wpenv_vars, $full_path) {
        global $__wordpatch_filesystem_context;

        if(!$__wordpatch_filesystem_context || !$__wordpatch_filesystem_context['connected']) {
            return false;
        }

        $fs_method = $__wordpatch_filesystem_context['settings']['fs_method'];

        if (!wordpatch_is_valid_fs_method($fs_method)) {
            return false;
        }

        if ($fs_method === wordpatch_fs_method_direct()) {
            return @chdir($full_path);
        }

        if ($fs_method === wordpatch_fs_method_ssh2()) {
            return wordpatch_filesystem_ssh2_run_command($wpenv_vars, 'cd ' . $full_path, true);
        }

        if ($fs_method === wordpatch_fs_method_ftpext()) {
            return @ftp_chdir($__wordpatch_filesystem_context['link'], $full_path);
        }

        if ($fs_method === wordpatch_fs_method_ftpsockets()) {
            $ftp = $__wordpatch_filesystem_context['ftp'];
            return $ftp->chdir($full_path);

        }

        return false;
    }
}

if(!function_exists('wordpatch_filesystem_cwd')) {
    function wordpatch_filesystem_cwd($wpenv_vars) {
        global $__wordpatch_filesystem_context;

        if(!$__wordpatch_filesystem_context || !$__wordpatch_filesystem_context['connected']) {
            return false;
        }

        $fs_method = $__wordpatch_filesystem_context['settings']['fs_method'];

        if (!wordpatch_is_valid_fs_method($fs_method)) {
            return false;
        }

        if ($fs_method === wordpatch_fs_method_direct()) {
            return @getcwd();
        }

        if ($fs_method === wordpatch_fs_method_ssh2()) {
            $cwd = ssh2_sftp_realpath($__wordpatch_filesystem_context['sftp_link'], '.');

            if($cwd) {
                $cwd = wordpatch_trailingslashit(trim($cwd));
            }

            return $cwd;
        }

        if ($fs_method === wordpatch_fs_method_ftpext()) {
            $cwd = @ftp_pwd($__wordpatch_filesystem_context['link']);

            if($cwd) {
                $cwd = wordpatch_trailingslashit($cwd);
            }

            return $cwd;
        }

        if ($fs_method === wordpatch_fs_method_ftpsockets()) {
            $ftp = $__wordpatch_filesystem_context['ftp'];
            $cwd = $ftp->pwd();

            if($cwd) {
                $cwd = wordpatch_trailingslashit($cwd);
            }

            return $cwd;
        }

        return false;
    }
}

if(!function_exists('wordpatch_filesystem_exists')) {
    function wordpatch_filesystem_exists($wpenv_vars, $relative_path, $path_type) {
        global $__wordpatch_filesystem_context;

        if(!$__wordpatch_filesystem_context || !$__wordpatch_filesystem_context['connected']) {
            return false;
        }

        $relative_path = wordpatch_sanitize_unix_file_path($relative_path, true, true);

        $fs_method = $__wordpatch_filesystem_context['settings']['fs_method'];
        $full_path = wordpatch_filesystem_build_full_path($wpenv_vars, $relative_path, $path_type);

        if (!wordpatch_is_valid_fs_method($fs_method)) {
            return false;
        }

        if ($fs_method === wordpatch_fs_method_direct()) {
            return @file_exists($full_path);
        }

        if ($fs_method === wordpatch_fs_method_ssh2()) {
            return file_exists(wordpatch_filesystem_ssh2_sftp_path($wpenv_vars, $relative_path, $path_type));
        }

        if ($fs_method === wordpatch_fs_method_ftpext()) {
            $list = @ftp_nlist($__wordpatch_filesystem_context['link'], $full_path);

            if(empty($list) && wordpatch_filesystem_is_dir($wpenv_vars, $relative_path, $path_type)) {
                return true;
            }

            return !empty($list);
        }

        if ($fs_method === wordpatch_fs_method_ftpsockets()) {
            $ftp = $__wordpatch_filesystem_context['ftp'];
            $list = $ftp->nlist($full_path);

            if(empty($list) && wordpatch_filesystem_is_dir($wpenv_vars, $relative_path, $path_type)) {
                return true;
            }

            return !empty($list);
        }

        return false;
    }
}

if(!function_exists('wordpatch_filesystem_build_full_path')) {
    function wordpatch_filesystem_build_full_path($wpenv_vars, $relative_path, $path_type) {
        global $__wordpatch_filesystem_context;

        $relative_path = wordpatch_sanitize_unix_file_path($relative_path, true, true);

        if(!$__wordpatch_filesystem_context ||
            !wordpatch_is_valid_fs_method($__wordpatch_filesystem_context['settings']['fs_method']) ||
            $__wordpatch_filesystem_context['settings']['fs_method'] === wordpatch_fs_method_direct())
        {
            if($path_type === 'content_dir') {
                return wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_content_dir')) . $relative_path;
            }

            if($path_type === 'plugin_dir') {
                return wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_plugin_dir')) . $relative_path;
            }

            return wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_root_dir')) . $relative_path;
        }

        if($path_type === 'content_dir') {
            return wordpatch_trailingslashit($__wordpatch_filesystem_context['settings']['ftp_content_dir']) . $relative_path;
        }

        if($path_type === 'plugin_dir') {
            return wordpatch_trailingslashit($__wordpatch_filesystem_context['settings']['ftp_plugin_dir']) . $relative_path;
        }

        return wordpatch_trailingslashit($__wordpatch_filesystem_context['settings']['ftp_base']) . $relative_path;
    }
}

if(!function_exists('wordpatch_filesystem_chmod')) {
    function wordpatch_filesystem_chmod($wpenv_vars, $relative_path, $path_type, $chmod) {
        global $__wordpatch_filesystem_context;

        if(!$__wordpatch_filesystem_context || !$__wordpatch_filesystem_context['connected']) {
            return false;
        }

        $relative_path = wordpatch_sanitize_unix_file_path($relative_path, true, true);

        $fs_method = $__wordpatch_filesystem_context['settings']['fs_method'];
        $full_path = wordpatch_filesystem_build_full_path($wpenv_vars, $relative_path, $path_type);

        if (!wordpatch_is_valid_fs_method($fs_method)) {
            return false;
        }

        if ($fs_method === wordpatch_fs_method_direct()) {
            return @chmod($full_path, $chmod);
        }

        if ($fs_method === wordpatch_fs_method_ssh2()) {
            if(!wordpatch_filesystem_exists($wpenv_vars, $relative_path, $path_type)) {
                return false;
            }

            $command = sprintf('chmod %o %s', $chmod, escapeshellarg($full_path));
            return wordpatch_filesystem_ssh2_run_command($wpenv_vars, $command, true);
        }

        if ($fs_method === wordpatch_fs_method_ftpext()) {
            if(!function_exists('ftp_chmod')) {
                return (bool)@ftp_site($__wordpatch_filesystem_context['link'], sprintf('CHMOD %o %s', $chmod, $full_path));
            }

            return (bool)@ftp_chmod($__wordpatch_filesystem_context['link'], $chmod, $full_path);
        }

        if ($fs_method === wordpatch_fs_method_ftpsockets()) {
            $ftp = $__wordpatch_filesystem_context['ftp'];
            return $ftp->chmod($full_path, $chmod);
        }

        return false;
    }
}

if(!function_exists('wordpatch_filesystem_ssh2_sftp_path')) {
    function wordpatch_filesystem_ssh2_sftp_path($wpenv_vars, $relative_path, $path_type) {
        global $__wordpatch_filesystem_context;

        if(!$__wordpatch_filesystem_context || !$__wordpatch_filesystem_context['connected']) {
            return null;
        }

        $sftp_link = $__wordpatch_filesystem_context['sftp_link'];

        $relative_path = wordpatch_sanitize_unix_file_path($relative_path, true, true);

        $path = wordpatch_filesystem_build_full_path($wpenv_vars, $relative_path, $path_type);
        if('/' === $path) {
            $path = '/./';
        }

        return 'ssh2.sftp://' . $sftp_link . '/' . ltrim($path, '/');
    }
}

if(!function_exists('wordpatch_filesystem_delete')) {
    function wordpatch_filesystem_delete(
        $wpenv_vars,
        $relative_path,
        $path_type,
        $type = false
    )
    {
        global $__wordpatch_filesystem_context;

        if(!$__wordpatch_filesystem_context || !$__wordpatch_filesystem_context['connected']) {
            return false;
        }

        $relative_path = wordpatch_sanitize_unix_file_path($relative_path, true, true);

        $fs_method = $__wordpatch_filesystem_context['settings']['fs_method'];
        $full_path = wordpatch_filesystem_build_full_path($wpenv_vars, $relative_path, $path_type);

        if (!wordpatch_is_valid_fs_method($fs_method)) {
            return false;
        }

        if ($fs_method === wordpatch_fs_method_direct()) {
            if(empty($full_path)) {
                return false;
            }

            $full_path = str_replace('\\', '/', $full_path); // for win32, occasional problems deleting files otherwise

            if('f' == $type || wordpatch_filesystem_is_file($wpenv_vars, $relative_path, $path_type)) {
                return @unlink($full_path);
            }

            if(wordpatch_filesystem_is_dir($wpenv_vars, $relative_path, $path_type)) {
                return @rmdir($full_path);
            }

            return false;
        }

        if ($fs_method === wordpatch_fs_method_ssh2()) {
            if('f' == $type || wordpatch_filesystem_is_file($wpenv_vars, $relative_path, $path_type)) {
                return ssh2_sftp_unlink($__wordpatch_filesystem_context['sftp_link'], $full_path);
            }

            return ssh2_sftp_rmdir($__wordpatch_filesystem_context['sftp_link'], $full_path);
        }

        if ($fs_method === wordpatch_fs_method_ftpext()) {
            if(empty($full_path)) {
                return false;
            }

            if('f' == $type || wordpatch_filesystem_is_file($wpenv_vars, $relative_path, $path_type)) {
                return @ftp_delete($__wordpatch_filesystem_context['link'], $full_path);
            }

            return @ftp_rmdir($__wordpatch_filesystem_context['link'], $full_path);
        }

        if($fs_method === wordpatch_fs_method_ftpsockets()) {
            if(empty($full_path)) {
                return false;
            }

            $ftp = $__wordpatch_filesystem_context['ftp'];

            if('f' == $type || wordpatch_filesystem_is_file($wpenv_vars, $relative_path, $path_type)) {
                return $ftp->delete($full_path);
            }

            return $ftp->rmdir($ftp);
        }

        return false;
    }
}

if(!function_exists('wordpatch_filesystem_chown')) {
    function wordpatch_filesystem_chown(
        $wpenv_vars,
        $relative_path,
        $path_type,
        $owner
    )
    {
        global $__wordpatch_filesystem_context;

        if(!$__wordpatch_filesystem_context || !$__wordpatch_filesystem_context['connected']) {
            return false;
        }

        $relative_path = wordpatch_sanitize_unix_file_path($relative_path, true, true);

        $fs_method = $__wordpatch_filesystem_context['settings']['fs_method'];
        $full_path = wordpatch_filesystem_build_full_path($wpenv_vars, $relative_path, $path_type);

        if (!wordpatch_is_valid_fs_method($fs_method)) {
            return false;
        }

        if ($fs_method == 'direct') {
            if(wordpatch_filesystem_exists($wpenv_vars, $relative_path, $path_type)) {
                return false;
            }

            return @chown($full_path, $owner);
        }

        if ($fs_method == 'ssh2') {
            if(wordpatch_filesystem_exists($wpenv_vars, $relative_path, $path_type)) {
                return false;
            }

            return wordpatch_filesystem_ssh2_run_command($wpenv_vars, sprintf('chown %s %s', escapeshellarg($owner), escapeshellarg($full_path)), true);
        }

        if ($fs_method == 'ftpext') {
            return false;
        }

        if($fs_method == 'ftpsockets') {
            return false;
        }

        return false;
    }
}

if(!function_exists('wordpatch_filesystem_is_file')) {
    function wordpatch_filesystem_is_file(
        $wpenv_vars,
        $relative_path,
        $path_type
    )
    {
        global $__wordpatch_filesystem_context;

        if(!$__wordpatch_filesystem_context || !$__wordpatch_filesystem_context['connected']) {
            return false;
        }

        $relative_path = wordpatch_sanitize_unix_file_path($relative_path, true, true);

        $fs_method = $__wordpatch_filesystem_context['settings']['fs_method'];
        $full_path = wordpatch_filesystem_build_full_path($wpenv_vars, $relative_path, $path_type);

        if (!wordpatch_is_valid_fs_method($fs_method)) {
            return false;
        }

        if ($fs_method == 'direct') {
            return @is_file($full_path);
        }

        if ($fs_method == 'ssh2') {
            return is_file(wordpatch_filesystem_ssh2_sftp_path($wpenv_vars, $relative_path, $path_type));
        }

        if ($fs_method == 'ftpext') {
            return wordpatch_filesystem_exists($wpenv_vars, $relative_path, $path_type) &&
                !wordpatch_filesystem_is_dir($wpenv_vars, $relative_path, $path_type);
        }

        if($fs_method == 'ftpsockets') {
            if (wordpatch_filesystem_is_dir($wpenv_vars, $relative_path, $path_type)) {
                return false;
            }

            if (wordpatch_filesystem_exists($wpenv_vars, $relative_path, $path_type)) {
                return true;
            }

            return false;
        }

        return false;
    }
}

if(!function_exists('wordpatch_filesystem_chgrp')) {
    function wordpatch_filesystem_chgrp(
        $wpenv_vars,
        $relative_path,
        $path_type,
        $group
    )
    {
        global $__wordpatch_filesystem_context;

        if(!$__wordpatch_filesystem_context || !$__wordpatch_filesystem_context['connected']) {
            return false;
        }

        $relative_path = wordpatch_sanitize_unix_file_path($relative_path, true, true);

        $fs_method = $__wordpatch_filesystem_context['settings']['fs_method'];
        $full_path = wordpatch_filesystem_build_full_path($wpenv_vars, $relative_path, $path_type);

        if (!wordpatch_is_valid_fs_method($fs_method)) {
            return false;
        }

        if ($fs_method == 'direct') {
            if(!wordpatch_filesystem_exists($wpenv_vars, $relative_path, $path_type)) {
                return false;
            }

            return @chgrp($full_path, $group);
        }

        if ($fs_method == 'ssh2') {
            if(!wordpatch_filesystem_exists($wpenv_vars, $relative_path, $path_type)) {
                return false;
            }

            return wordpatch_filesystem_ssh2_run_command($wpenv_vars, sprintf('chgrp %s %s', escapeshellarg($group), escapeshellarg($full_path)), true);
        }

        if ($fs_method == 'ftpext') {
            return false;
        }

        if($fs_method == 'ftpsockets') {
            return false;
        }

        return false;
    }
}

if(!function_exists('wordpatch_filesystem_mkdir')) {
    function wordpatch_filesystem_mkdir(
        $wpenv_vars,
        $relative_path,
        $path_type,
        $chmod = false,
        $chown = false,
        $chgrp = false
    )
    {
        global $__wordpatch_filesystem_context;

        if(!$__wordpatch_filesystem_context || !$__wordpatch_filesystem_context['connected']) {
            return false;
        }

        $relative_path = wordpatch_sanitize_unix_file_path($relative_path, true, true);

        $fs_method = $__wordpatch_filesystem_context['settings']['fs_method'];
        $fs_chmod_dir_int = $__wordpatch_filesystem_context['settings']['fs_chmod_dir_int'];
        $full_path = wordpatch_filesystem_build_full_path($wpenv_vars, $relative_path, $path_type);

        if (!wordpatch_is_valid_fs_method($fs_method)) {
            return false;
        }

        if ($fs_method === wordpatch_fs_method_direct()) {
            $full_path = wordpatch_untrailingslashit($full_path);
            if(empty($full_path)) {
                return false;
            }

            if(!$chmod) {
                $chmod = $fs_chmod_dir_int;
            }

            if(!@mkdir($full_path)) {
                return false;
            }

            wordpatch_filesystem_chmod($wpenv_vars, $relative_path, $path_type, $chmod);

            if($chown) {
                wordpatch_filesystem_chown($wpenv_vars, $relative_path, $path_type, $chown);
            }

            if($chgrp) {
                wordpatch_filesystem_chgrp($wpenv_vars, $relative_path, $path_type, $chgrp);
            }

            return true;
        }

        if ($fs_method === wordpatch_fs_method_ssh2()) {
            $full_path = wordpatch_untrailingslashit($full_path);

            if(empty($full_path)) {
                return false;
            }

            if(!$chmod) {
                $chmod = $fs_chmod_dir_int;
            }
            if(!ssh2_sftp_mkdir($__wordpatch_filesystem_context['sftp_link'], $full_path, $chmod, true)) {
                return false;
            }

            if($chown) {
                wordpatch_filesystem_chown($wpenv_vars, $relative_path, $path_type, $chown);
            }

            if($chgrp) {
                wordpatch_filesystem_chgrp($wpenv_vars, $relative_path, $path_type, $chgrp);
            }

            return true;
        }

        if ($fs_method === wordpatch_fs_method_ftpext()) {
            $full_path = wordpatch_untrailingslashit($full_path);

            if(empty($full_path)) {
                return false;
            }

            if(!@ftp_mkdir($__wordpatch_filesystem_context['link'], $full_path)) {
                return false;
            }

            // TODO: This implementation lacks the following code, because it was never in WordPress to begin with...
            // In my opinion, this is a mistake inside the WordPress codebase. To be continued.
            // if(!$chmod) {
            //    $chmod = $fs_chmod_dir_int;
            // }

            wordpatch_filesystem_chmod($wpenv_vars, $relative_path, $path_type, $chmod);
            return true;
        }

        if($fs_method === wordpatch_fs_method_ftpsockets()) {
            $full_path = wordpatch_untrailingslashit($full_path);

            if(empty($full_path)) {
                return false;
            }

            $ftp = $__wordpatch_filesystem_context['ftp'];

            if(!$ftp->mkdir($full_path)) {
                return false;
            }

            if(!$chmod) {
                $chmod = $fs_chmod_dir_int;
            }

            wordpatch_filesystem_chmod($wpenv_vars, $relative_path, $path_type, $chmod);
            return true;
        }

        return false;
    }
}

if(!function_exists('wordpatch_filesystem_put_contents')) {
    function wordpatch_filesystem_put_contents(
        $wpenv_vars,
        $relative_path,
        $path_type,
        $contents
    )
    {
        global $__wordpatch_filesystem_context;

        if(!$__wordpatch_filesystem_context || !$__wordpatch_filesystem_context['connected']) {
            return false;
        }

        $relative_path = wordpatch_sanitize_unix_file_path($relative_path, true, true);

        $fs_method = $__wordpatch_filesystem_context['settings']['fs_method'];
        $fs_chmod_file_int = $__wordpatch_filesystem_context['settings']['fs_chmod_file_int'];
        $full_path = wordpatch_filesystem_build_full_path($wpenv_vars, $relative_path, $path_type);

        if (!wordpatch_is_valid_fs_method($fs_method)) {
            return false;
        }

        if ($fs_method == 'direct') {
            $fp = @fopen($full_path, 'wb');
            if(!$fp) {
                return false;
            }

            wordpatch_mbstring_binary_safe_encoding();
            $data_length = strlen($contents);
            $bytes_written = fwrite($fp, $contents);
            wordpatch_reset_mbstring_encoding();

            fclose($fp);

            if($data_length !== $bytes_written) {
                return false;
            }

            wordpatch_filesystem_chmod($wpenv_vars, $relative_path, $path_type, $fs_chmod_file_int);
            return true;
        }

        if ($fs_method == 'ssh2') {
            $ret = file_put_contents(wordpatch_filesystem_ssh2_sftp_path($wpenv_vars, $relative_path, $path_type), $contents);

            if($ret !== strlen($contents)) {
                return false;
            }

            wordpatch_filesystem_chmod($wpenv_vars, $relative_path, $path_type, $fs_chmod_file_int);
            return true;
        }

        if ($fs_method == 'ftpext') {
            $tempfile = wordpatch_tempnam($wpenv_vars, $full_path);
            $temp = fopen($tempfile, 'wb+');

            if(!$temp) {
                unlink($tempfile);
                return false;
            }

            wordpatch_mbstring_binary_safe_encoding();

            $data_length = strlen($contents);
            $bytes_written = fwrite($temp, $contents);

            wordpatch_reset_mbstring_encoding();

            if($data_length !== $bytes_written) {
                fclose($temp);
                unlink($tempfile);
                return false;
            }

            fseek($temp, 0); // Skip back to the start of the file being written to

            $ret = @ftp_fput($__wordpatch_filesystem_context['link'], $full_path, $temp, FTP_BINARY);

            fclose($temp);
            unlink($tempfile);

            wordpatch_filesystem_chmod($wpenv_vars, $relative_path, $path_type, $fs_chmod_file_int);
            return $ret;
        }

        if($fs_method == 'ftpsockets') {
            $ftp = $__wordpatch_filesystem_context['ftp'];
            $temp = wordpatch_tempnam($wpenv_vars, $full_path);
            if(!$temphandle = @fopen($temp, 'w+')) {
                unlink($temp);
                return false;
            }

            // The FTP class uses string functions internally during file download/upload
            wordpatch_mbstring_binary_safe_encoding();

            $bytes_written = fwrite($temphandle, $contents);
            if(false === $bytes_written || $bytes_written != strlen($contents)) {
                fclose($temphandle);
                unlink($temp );

                wordpatch_reset_mbstring_encoding();
                return false;
            }

            fseek($temphandle, 0); // Skip back to the start of the file being written to

            $ret = $ftp->fput($full_path, $temphandle);

            wordpatch_reset_mbstring_encoding();

            fclose($temphandle);
            unlink($temp);

            wordpatch_filesystem_chmod($wpenv_vars, $relative_path, $path_type, $fs_chmod_file_int);
            return $ret;
        }

        return false;
    }
}

if(!function_exists('wordpatch_filesystem_end')) {
    function wordpatch_filesystem_end()
    {
        global $__wordpatch_filesystem_context;

        if(!$__wordpatch_filesystem_context || !$__wordpatch_filesystem_context['connected']) {
            return false;
        }

        $fs_method = $__wordpatch_filesystem_context['settings']['fs_method'];

        if (!wordpatch_is_valid_fs_method($fs_method)) {
            return false;
        }

        if ($fs_method == 'direct') {
            return true;
        }

        if ($fs_method == 'ssh2') {
            return true;
        }

        if ($fs_method == 'ftpext') {
            if($__wordpatch_filesystem_context['link']) {
                return ftp_close($__wordpatch_filesystem_context['link']);
            }

            return true;
        }

        if($fs_method == 'ftpsockets') {
            if($__wordpatch_filesystem_context['ftp']) {
                $__wordpatch_filesystem_context['ftp']->quit();
            }

            return true;
        }

        return false;
    }
}

if(!function_exists('wordpatch_filesystem_test')) {
    function wordpatch_filesystem_test($wpenv_vars, $current_fs_method,
                                       $current_ftp_base, $current_ftp_content_dir, $current_ftp_plugin_dir, $current_ftp_pubkey,
                                       $current_ftp_prikey, $current_ftp_user, $current_ftp_pass, $current_ftp_host, $current_ftp_ssl,
                                       $current_fs_chmod_file, $current_fs_chmod_dir, $current_fs_timeout) {
        $ret = array(
            'connect_check' => false,
            'error_list' => array(),
            'delete_success_base' => false,
            'delete_success_content_dir' => false,
            'delete_success_plugin_dir' => false,
            'testwrite_exists_base' => false,
            'testwrite_exists_content_dir' => false,
            'testwrite_exists_plugin_dir' => false,
        );

        $ret['connect_check'] = wordpatch_filesystem_begin($wpenv_vars, $current_fs_method, $current_ftp_base,
            $current_ftp_content_dir, $current_ftp_plugin_dir, $current_ftp_pubkey, $current_ftp_prikey,
            $current_ftp_user, $current_ftp_pass, $current_ftp_host, $current_ftp_ssl, $current_fs_chmod_file,
            $current_fs_chmod_dir, $current_fs_timeout);

        if(!$ret['connect_check']) {
            $ret['error_list'][] = WORDPATCH_INVALID_FILESYSTEM_CREDENTIALS;
        } else {
            $testwrite_filename = array();
            $testwrite_filename['base'] = 'wp-testwrite-' . uniqid() . '.php';
            $testwrite_filename['content_dir'] = 'wp-testwrite-content-' . uniqid() . '.php';
            $testwrite_filename['plugin_dir'] = 'wp-testwrite-plugin-' . uniqid() . '.php';

            $testwrite_contents = "<?php echo('&#9829;');";

            // TODO: Test execution permission

            $testwrite_abspath = array();
            $testwrite_abspath['base'] = wordpatch_env_get($wpenv_vars, 'wp_root_dir') . '/' . $testwrite_filename['base'];
            $testwrite_abspath['plugin_dir'] = wordpatch_env_get($wpenv_vars, 'wp_plugin_dir') . '/' . $testwrite_filename['plugin_dir'];
            $testwrite_abspath['content_dir'] = wordpatch_env_get($wpenv_vars, 'wp_content_dir') . '/' . $testwrite_filename['content_dir'];

            foreach(array('base', 'content_dir', 'plugin_dir') as $dir_key) {
                if (wordpatch_filesystem_put_contents($wpenv_vars, $testwrite_filename[$dir_key], $dir_key, $testwrite_contents)) {
                    $ret['testwrite_exists_' . $dir_key] = file_exists($testwrite_abspath[$dir_key]);
                    $ret['delete_success_' . $dir_key] = wordpatch_filesystem_delete($wpenv_vars, $testwrite_filename[$dir_key], $dir_key);

                    if (!$ret['delete_success_' . $dir_key]) {
                        $ret['error_list'][] = WORDPATCH_FS_PERMISSIONS_ERROR;
                    }

                    if (!$ret['testwrite_exists_' . $dir_key]) {
                        $ret['error_list'][] = WORDPATCH_WRONG_FILESYSTEM_CREDENTIALS;
                    }
                } else {
                    $ret['error_list'][] = WORDPATCH_FS_PERMISSIONS_ERROR;
                }
            }
        }

        wordpatch_filesystem_end();

        return $ret;
    }
}