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
 * The following functions guess field values for each step of the filesystem configuration wizard.
 * These functions are called from `wordpatch_configfs_post_vars`.
 */
if(!function_exists('wordpatch_configfs_guess_fs_method')) {
    /**
     * Guess the best filesystem method value to be used for the filesystem configuration wizard.
     *
     * @param $wpenv_vars
     * @return mixed|string
     */
    function wordpatch_configfs_guess_fs_method($wpenv_vars)
    {
        if(defined('FS_METHOD') && wordpatch_is_valid_fs_method(constant('FS_METHOD'))) {
            return constant('FS_METHOD');
        }

        return wordpatch_fs_method_direct();
    }
}

if(!function_exists('wordpatch_configfs_guess_fs_timeout')) {
    /**
     * Guess the best filesystem timeout value to be used for the filesystem configuration wizard.
     *
     * @param $wpenv_vars
     * @return mixed|string
     */
    function wordpatch_configfs_guess_fs_timeout($wpenv_vars)
    {
        if(defined('FS_TIMEOUT')) {
            $timeout_const = constant('FS_TIMEOUT');

            if($timeout_const !== null && $timeout_const !== false) {
                return max(0, (int)$timeout_const);
            }
        }

        return 30;
    }
}

if(!function_exists('wordpatch_configfs_guess_fs_chmod_file')) {
    /**
     * Guess the best filesystem CHMOD file value to be used for the filesystem configuration wizard.
     *
     * @param $wpenv_vars
     * @return mixed|string
     */
    function wordpatch_configfs_guess_fs_chmod_file($wpenv_vars)
    {
        if(defined('FS_CHMOD_FILE')) {
            $const_val = constant('FS_CHMOD_FILE');

            if($const_val !== null && $const_val !== false) {
                $const_val = (int)$const_val;
                $const_val = wordpatch_sanitize_chmod_value($const_val);

                return wordpatch_get_display_octal_from_int($const_val);
            }
        }

        $octal_val = fileperms(wordpatch_env_get($wpenv_vars, 'wp_root_dir') . '/index.php') & 0777 | 0644;
        return wordpatch_get_display_octal_from_int($octal_val);
    }
}

if(!function_exists('wordpatch_configfs_guess_fs_chmod_dir')) {
    /**
     * Guess the best filesystem CHMOD directory value to be used for the filesystem configuration wizard.
     *
     * @param $wpenv_vars
     * @return mixed|string
     */
    function wordpatch_configfs_guess_fs_chmod_dir($wpenv_vars)
    {
        if(defined('FS_CHMOD_DIR')) {
            $const_val = constant('FS_CHMOD_DIR');

            if($const_val !== null && $const_val !== false) {
                $const_val = (int)$const_val;
                $const_val = wordpatch_sanitize_chmod_value($const_val);

                return wordpatch_get_display_octal_from_int($const_val);
            }
        }

        $octal_val = fileperms(wordpatch_env_get($wpenv_vars, 'wp_root_dir')) & 0777 | 0755;
        return wordpatch_get_display_octal_from_int($octal_val);
    }
}

if(!function_exists('wordpatch_configfs_guess_ftp_ssl')) {
    /**
     * Guess the best FTP SSL value to be used for the filesystem configuration wizard.
     *
     * @param $wpenv_vars
     * @return mixed|string
     */
    function wordpatch_configfs_guess_ftp_ssl($wpenv_vars)
    {
        if(defined('FTP_SSL')) {
            $const_val = constant('FTP_SSL');

            if($const_val !== null) {
                return $const_val ? wordpatch_ftp_ssl_yes() : wordpatch_ftp_ssl_no();
            }
        }

        return wordpatch_ftp_ssl_no();
    }
}

if(!function_exists('wordpatch_configfs_guess_ftp_base')) {
    /**
     * Guess the best FTP base value to be used for the filesystem configuration wizard.
     *
     * @param $wpenv_vars
     * @return mixed|string
     */
    function wordpatch_configfs_guess_ftp_base($wpenv_vars)
    {
        if(defined('FTP_BASE')) {
            $const_val = constant('FTP_BASE');

            if($const_val !== null && trim($const_val) !== '') {
                return trim($const_val);
            }
        }

        return '';
    }
}

if(!function_exists('wordpatch_configfs_guess_ftp_content_dir')) {
    /**
     * Guess the best FTP content directory value to be used for the filesystem configuration wizard.
     *
     * @param $wpenv_vars
     * @return mixed|string
     */
    function wordpatch_configfs_guess_ftp_content_dir($wpenv_vars)
    {
        if(defined('FTP_CONTENT_DIR')) {
            $const_val = constant('FTP_CONTENT_DIR');

            if($const_val !== null && trim($const_val) !== '') {
                return trim($const_val);
            }
        }

        return '';
    }
}

if(!function_exists('wordpatch_configfs_guess_ftp_plugin_dir')) {
    /**
     * Guess the best FTP plugin directory value to be used for the filesystem configuration wizard.
     *
     * @param $wpenv_vars
     * @return mixed|string
     */
    function wordpatch_configfs_guess_ftp_plugin_dir($wpenv_vars)
    {
        if(defined('FTP_PLUGIN_DIR')) {
            $const_val = constant('FTP_PLUGIN_DIR');

            if($const_val !== null && trim($const_val) !== '') {
                return trim($const_val);
            }
        }

        return '';
    }
}

if(!function_exists('wordpatch_configfs_guess_ftp_user')) {
    /**
     * Guess the best FTP user value to be used for the filesystem configuration wizard.
     *
     * @param $wpenv_vars
     * @return mixed|string
     */
    function wordpatch_configfs_guess_ftp_user($wpenv_vars)
    {
        if(defined('FTP_USER')) {
            $const_val = constant('FTP_USER');

            if($const_val !== null && trim($const_val) !== '') {
                return trim($const_val);
            }
        }

        return '';
    }
}

if(!function_exists('wordpatch_configfs_guess_ftp_pass')) {
    /**
     * Guess the best FTP password value to be used for the filesystem configuration wizard.
     *
     * @param $wpenv_vars
     * @return mixed|string
     */
    function wordpatch_configfs_guess_ftp_pass($wpenv_vars)
    {
        if(defined('FTP_PASS') && trim(constant('FTP_PASS')) !== '') {
            return constant('FTP_PASS');
        }

        return '';
    }
}

if(!function_exists('wordpatch_configfs_guess_ftp_host')) {
    /**
     * Guess the best FTP host value to be used for the filesystem configuration wizard.
     *
     * @param $wpenv_vars
     * @return mixed|string
     */
    function wordpatch_configfs_guess_ftp_host($wpenv_vars)
    {
        if(defined('FTP_HOST')) {
            $const_val = constant('FTP_HOST');

            if($const_val !== null && trim($const_val) !== '') {
                return trim($const_val);
            }
        }

        return '';
    }
}

if(!function_exists('wordpatch_configfs_guess_ftp_pubkey')) {
    /**
     * Guess the best FTP public key value to be used for the filesystem configuration wizard.
     *
     * @param $wpenv_vars
     * @return mixed|string
     */
    function wordpatch_configfs_guess_ftp_pubkey($wpenv_vars)
    {
        if(defined('FTP_PUBKEY')) {
            $const_val = constant('FTP_PUBKEY');

            if($const_val !== null && trim($const_val) !== '') {
                return trim($const_val);
            }
        }

        return '';
    }
}

if(!function_exists('wordpatch_configfs_guess_ftp_prikey')) {
    /**
     * Guess the best FTP private key value to be used for the filesystem configuration wizard.
     *
     * @param $wpenv_vars
     * @return mixed|string
     */
    function wordpatch_configfs_guess_ftp_prikey($wpenv_vars)
    {
        if(defined('FTP_PRIKEY')) {
            $const_val = constant('FTP_PRIKEY');

            if($const_val !== null && trim($const_val) !== '') {
                return trim($const_val);
            }
        }

        return '';
    }
}