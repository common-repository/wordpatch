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

if(!function_exists('wordpatch_env_get')) {
    function wordpatch_env_get($wpenv_vars, $key, $default = null) {
        if(!isset($wpenv_vars[$key])) {
            return $default;
        }

        return $wpenv_vars[$key];
    }
}

if(!isset($__wordpatch_wordpress_env_cache)) {
    $__wordpatch_wordpress_env_cache = null;
}

if(!function_exists('wordpatch_wordpress_env')) {
    function wordpatch_wordpress_env() {
        /**
         * @var \wpdb $wpdb
         */
        global $wpdb;
        global $__wordpatch_wordpress_env_cache;

        if($__wordpatch_wordpress_env_cache) {
            return $__wordpatch_wordpress_env_cache;
        }

        $env_success = true;
        $db_handle = null;
        $use_mysqli = false;

        $db_connection_string = array(
            'db_host' => '',
            'db_user' => '',
            'db_password' => '',
            'db_name' => '',
            'db_table_prefix' => '',
            'db_collate' => '',
            'db_charset' => '',
        );

        if(isset($wpdb) && $wpdb) {
            $db_handle = $wpdb->dbh;

            if($db_handle) {
                $use_mysqli = isset($wpdb->use_mysqli) ? $wpdb->use_mysqli : false;
                $use_mysqli = $use_mysqli ? true : false;

                $db_connection_string['db_host'] = isset($wpdb->dbhost) ?
                    $wpdb->dbhost : (defined('DB_HOST') ? constant('DB_HOST') : '');

                $db_connection_string['db_name'] = isset($wpdb->dbname) ?
                    $wpdb->dbname : (defined('DB_NAME') ? constant('DB_NAME') : '');

                $db_connection_string['db_user'] = isset($wpdb->dbuser) ?
                    $wpdb->dbuser : (defined('DB_USER') ? constant('DB_USER') : '');

                $db_connection_string['db_password'] = isset($wpdb->dbpassword) ?
                    $wpdb->dbpassword : (defined('DB_PASSWORD') ? constant('DB_PASSWORD') : '');

                $db_connection_string['db_table_prefix'] = isset($wpdb->prefix) ? $wpdb->prefix : 'wp_';

                $db_connection_string['db_collate'] = isset($wpdb->collate) ?
                    $wpdb->collate : (defined('DB_COLLATE') ? constant('DB_COLLATE') : '');

                $db_connection_string['db_charset'] = isset($wpdb->charset) ?
                    $wpdb->charset : (defined('DB_CHARSET') ? constant('DB_CHARSET') : '');
            }
        }

        $wp_root_dir = defined('ABSPATH') ? constant('ABSPATH') : null;
        $wp_content_dir = defined('WP_CONTENT_DIR') ? constant('WP_CONTENT_DIR') : null;
        $wp_plugin_dir = defined('WP_PLUGIN_DIR') ? constant('WP_PLUGIN_DIR') : null;
        $salt_auth = function_exists('wp_salt') ? wp_salt('auth') : null;
        $salt_secure_auth = function_exists('wp_salt') ? wp_salt('secure_auth') : null;
        $salt_nonce = function_exists('wp_salt') ? wp_salt('nonce') : null;
        $salt_logged_in = function_exists('wp_salt') ? wp_salt('logged_in') : null;
        $locale = function_exists('get_locale') ? get_locale() : null;
        $base_uri = function_exists('admin_url') ? admin_url('admin.php?page=wordpatch') : null;
        $admin_url = function_exists('admin_url') ? admin_url('/') : null;
        $site_url = function_exists('site_url') ? site_url('/') : null;
        $site_path = function_exists('site_url') ? site_url('/', 'relative') : null;
        $admin_path = function_exists('admin_url') ? admin_url('/', 'relative') : null;
        $wp_wordpatch_path = function_exists('plugin_dir_url') ? plugin_dir_url(dirname(dirname(__FILE__)) . '/wordpatch.php') : null;
        $rescue_path = trim(get_option('wordpatch_rescue_path', ''));

        if($wp_wordpatch_path !== null && strpos($wp_wordpatch_path, $site_url) === 0) {
            $wp_wordpatch_path = wordpatch_leadingslashit(substr($wp_wordpatch_path, strlen($site_url)));
        }

        $wordpatch_path = wordpatch_sanitize_absolute_unix_path(WORDPATCH_PATH);

        if($wp_content_dir === null || $wp_root_dir === null || $wp_plugin_dir === null || $base_uri === null ||
            $salt_auth === null || $salt_secure_auth === null || $salt_nonce === null || $salt_logged_in === null ||
            $locale === null || $admin_url === null || $site_url === null || $site_path === null || $admin_path === null ||
            $wp_wordpatch_path === null || $wordpatch_path === null || !$db_handle) {
            $env_success = false;
        }

        $__wordpatch_wordpress_env_cache = array(
            'db_handle' => $db_handle,
            'use_mysqli' => $use_mysqli,
            'env_success' => $env_success,
            'wp_root_dir' => $wp_root_dir,
            'wp_content_dir' => $wp_content_dir,
            'wp_plugin_dir' => $wp_plugin_dir,
            'base_uri' => $base_uri,
            'db_connection_string' => $db_connection_string,
            'embed_mode' => true,
            'cookie_hash' => null,
            'cookie_path' => null,
            'site_cookie_path' => null,
            'cookie_domain' => null,
            'salt_logged_in' => $salt_logged_in,
            'salt_nonce' => $salt_nonce,
            'salt_auth' => $salt_auth,
            'salt_secure_auth' => $salt_secure_auth,
            'locale' => $locale,
            'wp_site_url' => $site_url,
            'wp_site_path' => $site_path,
            'wp_admin_url' => $admin_url,
            'wp_admin_path' => $admin_path,
            'wp_wordpatch_path' => $wp_wordpatch_path,
            'wordpatch_path' => $wordpatch_path,
            'rescue_path' => $rescue_path
        );

        // The following variables will always have a value,
        // but we need the rest of the environment first.

        if($env_success) {
            // Calculate: COOKIEHASH
            if (defined('COOKIEHASH') && !is_bool(constant('COOKIEHASH'))) {
                $__wordpatch_wordpress_env_cache['cookie_hash'] = constant('COOKIEHASH');
            }

            if ($__wordpatch_wordpress_env_cache['cookie_hash'] === null) {
                $siteurl = wordpatch_get_option($__wordpatch_wordpress_env_cache, 'siteurl');

                if ($siteurl) {
                    $__wordpatch_wordpress_env_cache['cookie_hash'] = md5($siteurl);
                } else {
                    $__wordpatch_wordpress_env_cache['cookie_hash'] = '';
                }
            }

            // Calculate: COOKIEPATH
            if (defined('COOKIEPATH') && !is_bool(constant('COOKIEPATH'))) {
                $__wordpatch_wordpress_env_cache['cookie_path'] = constant('COOKIEPATH');
            } else {
                $__wordpatch_wordpress_env_cache['cookie_path'] = preg_replace('|https?://[^/]+|i', '', wordpatch_get_option($__wordpatch_wordpress_env_cache, 'home') . '/');
            }

            // Calculate: SITECOOKIEPATH
            if (defined('SITECOOKIEPATH')) {
                $__wordpatch_wordpress_env_cache['site_cookie_path'] = constant('SITECOOKIEPATH');
            } else {
                $__wordpatch_wordpress_env_cache['site_cookie_path'] = preg_replace('|https?://[^/]+|i', '', wordpatch_get_option($__wordpatch_wordpress_env_cache, 'siteurl') . '/');
            }

            // Calculate: COOKIE_DOMAIN
            if(defined('COOKIE_DOMAIN')) {
                $__wordpatch_wordpress_env_cache['cookie_domain'] = constant('COOKIE_DOMAIN');
            } else {
                $__wordpatch_wordpress_env_cache['cookie_domain'] = false;
            }

            // Calculate: ADMIN_COOKIE_PATH
            if(defined('ADMIN_COOKIE_PATH')) {
                $__wordpatch_wordpress_env_cache['admin_cookie_path'] = constant('ADMIN_COOKIE_PATH');
            } else {
                $__wordpatch_wordpress_env_cache['admin_cookie_path'] = $__wordpatch_wordpress_env_cache['site_cookie_path']. 'wp-admin';
            }
        }

        return $__wordpatch_wordpress_env_cache;
    }
}

if(!function_exists('wordpatch_check_for_codemirror_css')) {
    /**
     * This function is used to determine if the codemirror min CSS file has already been enqueued as a WP_Styles
     * dependency. This will always return false in rescue mode.
     *
     * @param $wpenv_vars
     * @return bool
     */
    function wordpatch_check_for_codemirror_css($wpenv_vars) {
        // Check for rescue mode.
        if (!wordpatch_env_get($wpenv_vars, 'embed_mode')) {
            return true;
        }

        return wp_style_is('code-editor', 'registered') && wp_style_is('code-editor', 'enqueued');
    }
}

if(!function_exists('wordpatch_check_for_codemirror_js')) {
    /**
     * This function is used to determine if the codemirror min JS file has already been enqueued as a WP_Styles
     * dependency. This will always return false in rescue mode.
     *
     * @param $wpenv_vars
     * @return bool
     */
    function wordpatch_check_for_codemirror_js($wpenv_vars) {
        // Check for rescue mode.
        if (!wordpatch_env_get($wpenv_vars, 'embed_mode')) {
            return true;
        }

        return wp_script_is('code-editor', 'registered') && wp_script_is('code-editor', 'enqueued');
    }
}