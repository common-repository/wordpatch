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
 * Implements the shared rescue script functionality.
 */

// TODO: Should I be using SSL for users that want it?
// TODO: Combined with above... is there a global setting that calls force_ssl_admin(true)?
// TODO: FORCE_SSL_ADMIN is a thing inside wp-config

if(!function_exists('wordpatch_rescue_handle')) {
    function wordpatch_rescue_handle($wpenv_vars, $where, $login_uri) {
        // First, handle all the action logic (preface, requires_auth, process) if necessary.
        wordpatch_action_logic($wpenv_vars, $where, $login_uri);

        // Then, let's handle the rendering if necessary.
        wordpatch_action_render($wpenv_vars, $where, $login_uri);

        exit();
    }
}

if(!function_exists('wordpatch_rescue')) {
    function wordpatch_rescue($wpenv_vars)
    {
        global $__wordpatch_post;
        global $__wordpatch_files;

        $__wordpatch_post = $_POST;
        $__wordpatch_files = $_FILES;

        $wpenv_vars['base_uri'] = (isset($wpenv_vars['base_uri']) && $wpenv_vars['base_uri'] !== null) ?
            $wpenv_vars['base_uri'] : wordpatch_get_request_uri(true);

        $next_uri = (isset($_GET['next_uri']) && trim($_GET['next_uri']) !== '')
            ? trim($_GET['next_uri']) : wordpatch_dashboard_uri($wpenv_vars);

        // TODO: Make sure next_uri is safe

        $login_uri = wordpatch_login_uri($wpenv_vars, $next_uri);
        $action = wordpatch_action();

        $doing_ajax = wordpatch_simple_ajax_check();

        wordpatch_set_doing_ajax($doing_ajax);

        $secure = (wordpatch_is_ssl() || wordpatch_force_ssl_admin());

        // If https is required and request is http, redirect
        if ($secure && !wordpatch_is_ssl()) {
            if (0 === strpos($_SERVER['REQUEST_URI'], 'http')) {
                wordpatch_redirect(wordpatch_set_url_scheme($_SERVER['REQUEST_URI'], 'https'));
                exit();
            } else {
                wordpatch_redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
                exit();
            }
        }

        wordpatch_rescue_handle($wpenv_vars, $action, $login_uri);
    }
}

if(!function_exists('wordpatch_rescue_generate')) {
    function wordpatch_rescue_generate($wpenv_vars, $current_rescue_path) {
        $core_path = wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wordpatch_path')) . 'inc/core.php';

        $current_db_info = wordpatch_calculate_db_info($wpenv_vars);

        $php_code = "";
        $php_code .= "<?php\n";
        $php_code .= "include_once('" . $core_path . "');\n";
        $php_code .= "wordpatch_rescue(array(\n";
        $php_code .= "  'use_mysqli' => " . (wordpatch_env_get($wpenv_vars, 'use_mysqli') ? 'true' : 'false') . ",\n";
        $php_code .= "  'env_success' => " . (wordpatch_env_get($wpenv_vars, 'env_success') ? 'true' : 'false') . ",\n";
        $php_code .= "  'wp_root_dir' => " . wordpatch_make_double_quoted_string(wordpatch_env_get($wpenv_vars, 'wp_root_dir')) . ",\n";
        $php_code .= "  'wp_content_dir' => " . wordpatch_make_double_quoted_string(wordpatch_env_get($wpenv_vars, 'wp_content_dir')) . ",\n";
        $php_code .= "  'wp_plugin_dir' => " . wordpatch_make_double_quoted_string(wordpatch_env_get($wpenv_vars, 'wp_plugin_dir')) . ",\n";
        $php_code .= "  'base_uri' => null,\n";
        $php_code .= "  'db_handle' => null,\n";
        $php_code .= "  'db_connection_string' => array(\n";
        $php_code .= "    'db_host' => " . wordpatch_make_double_quoted_string($current_db_info['db_host']) . ",\n";
        $php_code .= "    'db_user' => " . wordpatch_make_double_quoted_string($current_db_info['db_user']) . ",\n";
        $php_code .= "    'db_password' => " . wordpatch_make_double_quoted_string($current_db_info['db_password']) . ",\n";
        $php_code .= "    'db_name' => " . wordpatch_make_double_quoted_string($current_db_info['db_name']) . ",\n";
        $php_code .= "    'db_table_prefix' => " . wordpatch_make_double_quoted_string($current_db_info['db_table_prefix']) . ",\n";
        $php_code .= "    'db_collate' => " . wordpatch_make_double_quoted_string($current_db_info['db_collate']) . ",\n";
        $php_code .= "    'db_charset' => " . wordpatch_make_double_quoted_string($current_db_info['db_charset']) . "\n";
        $php_code .= "  ),\n";
        $php_code .= "  'embed_mode' => false,\n";
        $php_code .= "  'cookie_hash' => " . wordpatch_make_double_quoted_string(wordpatch_env_get($wpenv_vars, 'cookie_hash')) . ",\n";
        $php_code .= "  'cookie_path' => " . wordpatch_make_double_quoted_string(wordpatch_env_get($wpenv_vars, 'cookie_path')) . ",\n";
        $php_code .= "  'site_cookie_path' => " . wordpatch_make_double_quoted_string(wordpatch_env_get($wpenv_vars, 'site_cookie_path')) . ",\n";
        $php_code .= "  'cookie_domain' => " . wordpatch_make_double_quoted_string(wordpatch_env_get($wpenv_vars, 'cookie_domain')) . ",\n";
        $php_code .= "  'salt_logged_in' => " . wordpatch_make_double_quoted_string(wordpatch_env_get($wpenv_vars, 'salt_logged_in')) . ",\n";
        $php_code .= "  'salt_nonce' => " . wordpatch_make_double_quoted_string(wordpatch_env_get($wpenv_vars, 'salt_nonce')) . ",\n";
        $php_code .= "  'salt_auth' => " . wordpatch_make_double_quoted_string(wordpatch_env_get($wpenv_vars, 'salt_auth')) . ",\n";
        $php_code .= "  'salt_secure_auth' => " . wordpatch_make_double_quoted_string(wordpatch_env_get($wpenv_vars, 'salt_secure_auth')) . ",\n";
        $php_code .= "  'locale' => " . wordpatch_make_double_quoted_string(wordpatch_env_get($wpenv_vars, 'locale')) . ",\n";
        $php_code .= "  'wp_site_url' => " . wordpatch_make_double_quoted_string(wordpatch_env_get($wpenv_vars, 'wp_site_url')) . ",\n";
        $php_code .= "  'wp_site_path' => " . wordpatch_make_double_quoted_string(wordpatch_env_get($wpenv_vars, 'wp_site_path')) . ",\n";
        $php_code .= "  'wp_admin_url' => " . wordpatch_make_double_quoted_string(wordpatch_env_get($wpenv_vars, 'wp_admin_url')) . ",\n";
        $php_code .= "  'wp_admin_path' => " . wordpatch_make_double_quoted_string(wordpatch_env_get($wpenv_vars, 'wp_admin_path')) . ",\n";
        $php_code .= "  'wp_wordpatch_path' => " . wordpatch_make_double_quoted_string(wordpatch_env_get($wpenv_vars, 'wp_wordpatch_path')) . ",\n";
        $php_code .= "  'rescue_mode' => true,\n";
        $php_code .= "  'rescue_path' => " . wordpatch_make_double_quoted_string($current_rescue_path) . ",\n";
        $php_code .= "  'wordpatch_path' => " . wordpatch_make_double_quoted_string(wordpatch_env_get($wpenv_vars, 'wordpatch_path')) . "\n";
        $php_code .= "));";

        return $php_code;
    }
}

if(!function_exists('wordpatch_rescue_test')) {
    function wordpatch_rescue_test($wpenv_vars, $current_rescue_path, $delete_after_creating = true, $dont_try_create = false) {
        $test_results = array(
            'error_list' => array()
        );

        // first of all, let's see if the path provided already exists. if it does, we should validate it as a rescue script instead
        $rescue_abspath = wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_root_dir')) . $current_rescue_path;
        $generated_rescue_script = wordpatch_rescue_generate($wpenv_vars, $current_rescue_path);

        if(file_exists($rescue_abspath)) {
            $existing_code = file_get_contents($rescue_abspath);

            if($existing_code !== $generated_rescue_script) {
                $test_results['error_list'][] = WORDPATCH_INVALID_RESCUE_FORMAT;
            }

            return $test_results;
        }

        // just incase we want to short-circuit creation
        if($dont_try_create) {
            $test_results['error_list'][] = WORDPATCH_INVALID_RESCUE_FORMAT;

            return false;
        }

        // okay, let's try to write it instead then
        // we should have filesystem and database access at this point if we get here
        $fs_connected = wordpatch_filesystem_begin_helper($wpenv_vars);

        if(!$fs_connected) {
            $test_results['error_list'][] = WORDPATCH_INVALID_FILESYSTEM_CREDENTIALS;
        }

        if(wordpatch_no_errors($test_results['error_list'])) {
            if(wordpatch_filesystem_exists($wpenv_vars, $current_rescue_path, 'base')) {
                $test_results['error_list'][] = WORDPATCH_WRONG_FILESYSTEM_CREDENTIALS;
            }

            if(wordpatch_no_errors($test_results['error_list'])) {
                $put_success = true;
                if(!wordpatch_filesystem_put_contents($wpenv_vars, $current_rescue_path, 'base', $generated_rescue_script)) {
                    $put_success = false;
                    $test_results['error_list'][] = WORDPATCH_FS_PERMISSIONS_ERROR;
                }

                if(wordpatch_no_errors($test_results['error_list'])) {
                    if(!file_exists($rescue_abspath) || file_get_contents($rescue_abspath) !== $generated_rescue_script) {
                        $test_results['error_list'][] = WORDPATCH_WRONG_FILESYSTEM_CREDENTIALS;
                    }

                    if($put_success && $delete_after_creating && !wordpatch_filesystem_delete($wpenv_vars, $current_rescue_path, 'base')) {
                        $test_results['error_list'][] = WORDPATCH_FS_PERMISSIONS_ERROR;
                    }
                }
            }
        }

        wordpatch_filesystem_end();

        return $test_results;
    }
}

if(!function_exists('wordpatch_rescue_validate_or_create')) {
    /**
     * Validates or creates a rescue script.
     * Optionally deletes after creating by passing $delete_after_creating as true (default).
     * Optionally never attempts to create a rescue script if it does not already exist.
     *
     * @param $wpenv_vars
     * @param $current_rescue_path
     * @param bool $delete_after_creating
     * @param bool $dont_try_create
     * @return array
     */
    function wordpatch_rescue_validate_or_create($wpenv_vars, $current_rescue_path, $delete_after_creating = true,
                                                 $dont_try_create = false) {
        // Create an array for our result.
        $error_list = array();

        // Calculate the current database information.
        $current_db_info = wordpatch_calculate_db_info($wpenv_vars);

        // Validate the database credentials.
        $validation_error = count(wordpatch_wizard_validate_db_creds($current_db_info['db_host'], $current_db_info['db_user'],
                $current_db_info['db_name'], $current_db_info['db_collate'], $current_db_info['db_charset'])) > 0;

        // If there was an issue with the database credential validation, append an error and return early.
        if($validation_error) {
            $error_list[] = WORDPATCH_INVALID_DATABASE_CREDENTIALS;
            return $error_list;
        }

        // Attempt to connect to the database.
        $db_connect_check = wordpatch_db_test($wpenv_vars, $current_db_info['db_host'], $current_db_info['db_user'],
            $current_db_info['db_password'], $current_db_info['db_name'], $current_db_info['db_table_prefix'],
            $current_db_info['db_collate'], $current_db_info['db_charset']);

        // Extend our error list with any errors that may have arisen from attempting to connect to the database.
        $error_list = wordpatch_extend_errors($error_list, $db_connect_check['error_list']);

        // If we have encountered one or more errors, return early.
        if(!wordpatch_no_errors($error_list)) {
            return $error_list;
        }

        // Calculate the current filesystem information.
        $current_fs_info = wordpatch_calculate_fs_info($wpenv_vars);

        // Validate the filesystem information.
        $validation_error = count(wordpatch_wizard_validate_fs_info($current_fs_info['fs_method'],  $current_fs_info['fs_timeout'],
                $current_fs_info['fs_chmod_file_int'], $current_fs_info['fs_chmod_dir_int'], $current_fs_info['ftp_host'],
                $current_fs_info['ftp_user'], $current_fs_info['ftp_prikey'], $current_fs_info['ftp_pubkey'],
                $current_fs_info['ftp_pass'])) > 0;

        // If there was an issue with the filesystem information validation, append an error and return early.
        if($validation_error) {
            $error_list[] = WORDPATCH_INVALID_FILESYSTEM_CREDENTIALS;
            return $error_list;
        }

        // Test if we have the ability to write to the filesystem properly.
        $fs_test = wordpatch_filesystem_test($wpenv_vars, $current_fs_info['fs_method'], $current_fs_info['ftp_base'],
            $current_fs_info['ftp_content_dir'], $current_fs_info['ftp_plugin_dir'], $current_fs_info['ftp_pubkey'],
            $current_fs_info['ftp_prikey'], $current_fs_info['ftp_user'], $current_fs_info['ftp_pass'],
            $current_fs_info['ftp_host'], $current_fs_info['ftp_ssl'], $current_fs_info['fs_chmod_file'],
            $current_fs_info['fs_chmod_dir'], $current_fs_info['fs_timeout']);

        // Extend our error list with any errors that may have arisen from attempting to write to the filesystem.
        $error_list = wordpatch_extend_errors($error_list, $fs_test['error_list']);

        // If we have encountered one or more errors, return early.
        if(!wordpatch_no_errors($error_list)) {
            return $error_list;
        }

        // Validate our rescue path by parsing it.
        $validation_error = count(wordpatch_wizard_validate_rescue_dirs($wpenv_vars, $current_rescue_path)) > 0;

        // If there was an issue with the rescue path validation, append an error and return early.
        if($validation_error) {
            $error_list[] = WORDPATCH_INVALID_RESCUE_PATH;
            return $error_list;
        }

        // Try to actually process the rescue script creation/validation of existing rescue script.
        $rescue_check = wordpatch_rescue_test($wpenv_vars, $current_rescue_path, $delete_after_creating, $dont_try_create);

        // Extend our error list with any errors that may have arisen from attempting to process the last bit.
        $error_list = wordpatch_extend_errors($error_list, $rescue_check['error_list']);

        // Return our result array.
        return $error_list;
    }
}

if(!function_exists('wordpatch_calculate_rescue_path')) {
    function wordpatch_calculate_rescue_path($wpenv_vars)
    {
        $rescue_path = wordpatch_get_option($wpenv_vars, 'wordpatch_rescue_path', '');
        $rescue_path = trim($rescue_path);

        if($rescue_path !== '') {
            return $rescue_path;
        }

        return null;
    }
}

if(!function_exists('wordpatch_calculate_rescue_configured')) {
    function wordpatch_calculate_rescue_configured($wpenv_vars)
    {
        $db_val = wordpatch_get_option($wpenv_vars, 'wordpatch_rescue_configured', WORDPATCH_NO);
        $db_val = trim($db_val);

        if($db_val !== '' && strtolower(trim($db_val)) === WORDPATCH_YES) {
            return WORDPATCH_YES;
        }

        return WORDPATCH_NO;
    }
}