<?php
/**
Plugin Name: WordPatch
Plugin URI: https://wordpatch.com
Description: Keep your customizations to themes and plugins applied easily by uploading patch files that are processed automatically.
Version: 1.1.7
Author: JointByte
Author URI: https://jointbyte.com
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wordpatch
*/

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

require_once('inc/core.php');

if(!function_exists('wordpatch_page_id')) {
    /**
     * Calculate the page ID for WordPatch.
     *
     * @return string
     */
    function wordpatch_page_id() {
        return 'toplevel_page_wordpatch';
    }
}

if(!isset($__wordpatch_entry_point_called)) {
    /**
     * Global variable used to track if the WordPatch entry point has been called yet.
     */
    $__wordpatch_entry_point_called = false;
}

if(!function_exists('wordpatch_main')) {
    /**
     * The entry point for WordPatch.
     */
    function wordpatch_main()
    {
        // Global variable used to track if the WordPatch entry point has been called yet.
        global $__wordpatch_entry_point_called;

        // We should not call our entry point twice, so return if we detect the variable is already true.
        if($__wordpatch_entry_point_called) {
            return;
        }

        // Set the global variable to true so we do not call this again.
        $__wordpatch_entry_point_called = true;

        // Prevent noobs from trying to go directly to wordpatch.php
        if(!defined('ABSPATH')) {
            return;
        }

        // Register our activation hook.
        register_activation_hook(__FILE__, 'wordpatch_activate');

        // Register the actions that WordPatch needs.
        add_action('admin_menu', 'wordpatch_add_menu_items');
        add_action('load-' . wordpatch_page_id(), 'wordpatch_controller_wordpatch');
        add_action('upgrader_process_complete', 'wordpatch_upgrader_process_complete', 10, 2);
        add_action('plugins_loaded', 'wordpatch_plugins_loaded_maintenance', 10);
        add_action('plugins_loaded', 'wordpatch_plugins_loaded_reactivate', 15);
        add_action('wp_dashboard_setup', 'wordpatch_add_dashboard_widgets', 999);
        add_action('activated_plugin', 'wordpatch_activated_plugin');
        add_filter('plugin_action_links', 'wordpatch_add_action_links', 10, 5);
        add_filter('plugin_row_meta', 'wordpatch_plugin_row_meta', 10, 2);
        add_filter('parent_file', 'wordpatch_parent_file');
    }
}

if(!function_exists('wordpatch_add_menu_items')) {
    /**
     * Responsible for adding the menu items for WordPatch.
     */
    function wordpatch_add_menu_items()
    {
        add_menu_page('WordPatch', 'WordPatch', 'activate_plugins', 'wordpatch', 'wordpatch_render_page', 'data:image/svg+xml;base64,PHN2ZyBpZD0iT25fTGlnaHRfY29weV8yIiBkYXRhLW5hbWU9Ik9uIExpZ2h0IGNvcHkgMiIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB2aWV3Qm94PSIwIDAgNDA0LjE3NyA0MDQuMTc3Ij48dGl0bGU+V29yZFBhdGNoX0dzMzwvdGl0bGU+PHBhdGggZmlsbD0iYmxhY2siIGQ9Ik05ODUuNDE5LDQ3MC44NDRIOTUzLjI0OHY1OS4wMzFoMzIuMTcxYzIxLjI2NywwLDMzLjUzNS0xMC4zMjUsMzMuNTM1LTI5LjkxM0MxMDE4Ljk1NCw0ODAuNjM4LDEwMDYuNjg2LDQ3MC44NDQsOTg1LjQxOSw0NzAuODQ0WiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoLTc3Ni4zNjkgLTMyMC4wNjkpIi8+PHBhdGggZmlsbD0iYmxhY2siIGQ9Ik0xMTIxLjM0Miw0NTQuNjI0YTE2Ljc1OSwxNi43NTksMCwwLDEtOS40MTYtMi45LDE2Ljc1OSwxNi43NTksMCwwLDAsOS40MTYsMi45bDIxLjE2Ni4wMTIsMTIuNjc4LjAwOCw4LjQ3NywwYTE2LjkyNCwxNi45MjQsMCwwLDAsMTYuODgzLTE2Ljg2NHYtLjA2MWExNi45MjIsMTYuOTIyLDAsMCwwLTE2Ljg2NC0xNi44ODNsLTguNDc2LDAtMTIuNjc4LS4wMDhoMGwuMDI1LTQ0LjdhMTcuODksMTcuODksMCwwLDAtMTcuODgxLTE3LjlsLTQ0LjY5NS0uMDI2aDBsLjAwNy0xMi42NzguMDA1LTguNDcxYTE2LjkyMywxNi45MjMsMCwwLDAtMTYuODY0LTE2Ljg4M2gtLjA2MWExNi45MjMsMTYuOTIzLDAsMCwwLTE2Ljg4MywxNi44NjRsLS4wMDUsOC40NzEtLjAwOCwxMi42NzgtLjAxMiwyMS4xNzIuMDEyLTIxLjE3Mi01MC43MTItLjAzLjAwOC0xMi42NzgsMC04LjQ3MWExNi45MjQsMTYuOTI0LDAsMCwwLTE2Ljg2NC0xNi44ODNoLS4wNjFhMTYuOTIyLDE2LjkyMiwwLDAsMC0xNi44ODMsMTYuODY0bDAsOC40NzEtLjAwOCwxMi42NzgtNTAuNzEyLS4wMy4wMDctMTIuNjc4LDAtOC40NzFhMTYuOTIzLDE2LjkyMywwLDAsMC0xNi44NjMtMTYuODgzaC0uMDYyYTE2LjkyNCwxNi45MjQsMCwwLDAtMTYuODgzLDE2Ljg2NGwwLDguNDcxLS4wMDcsMTIuNjc4LTQ0LjctLjAyNmExNy44OTIsMTcuODkyLDAsMCwwLTE3LjksMTcuODgybC0uMDI2LDQ0LjcsMjEuMTY3LjAxMi0yMS4xNjctLjAxMi0xMi42NzgtLjAwOC04LjQ3NywwYTE2LjkyMiwxNi45MjIsMCwwLDAtMTYuODgzLDE2Ljg2M3YuMDYyYTE2LjkyNCwxNi45MjQsMCwwLDAsMTYuODY0LDE2Ljg4M2w4LjQ3NiwwLDEyLjY3OC4wMDdoMGwtLjAyOSw1MC43MTIsMjEuMTY2LjAxMi0yMS4xNjYtLjAxMi0xMi42NzgtLjAwNy04LjQ3NywwYTE2LjkyMiwxNi45MjIsMCwwLDAtMTYuODgzLDE2Ljg2M3YuMDYyYTE2LjkyMywxNi45MjMsMCwwLDAsMTYuODYzLDE2Ljg4M2w4LjQ3NywwLDEyLjY3OC4wMDdoMGwtLjAyOSw1MC43MTIsMjEuMTY3LjAxMi0yMS4xNjctLjAxMi0xMi42NzgtLjAwNy04LjQ3NywwYTE2LjkyMywxNi45MjMsMCwwLDAtMTYuODgzLDE2Ljg2NHYuMDYxYTE2LjkyMywxNi45MjMsMCwwLDAsMTYuODY0LDE2Ljg4M2w4LjQ3NiwwLDEyLjY3OC4wMDdoMGwtLjAyNiw0NC42ODlhMTcuODkyLDE3Ljg5MiwwLDAsMCwxNy44ODMsMTcuOWw0NC42OTMuMDI2LS4wMDcsMTIuNjc4LDAsOC40ODJhMTYuOTIzLDE2LjkyMywwLDAsMCwxNi44NjQsMTYuODgzaC4wNjFhMTYuOTIyLDE2LjkyMiwwLDAsMCwxNi44ODMtMTYuODYzbDAtOC40ODMuMDA4LTEyLjY3OCw1MC43MTIuMDMuMDEyLTIxLjE2MS0uMDEyLDIxLjE2MS0uMDA4LDEyLjY3OCwwLDguNDgyQTE2LjkyMywxNi45MjMsMCwwLDAsOTc4LjMxLDcyNC4yaC4wNjFhMTYuOTI1LDE2LjkyNSwwLDAsMCwxNi44ODQtMTYuODY0bDAtOC40ODMuMDA4LTEyLjY3OCw1MC43MTIuMDI5LS4wMDcsMTIuNjc4LDAsOC40ODNhMTYuOTIyLDE2LjkyMiwwLDAsMCwxNi44NjMsMTYuODgzaC4wNjJhMTYuOTIzLDE2LjkyMywwLDAsMCwxNi44ODMtMTYuODYzbDAtOC40ODMuMDA3LTEyLjY3OGgwbDQ0LjY5NS4wMjZhMTcuODkyLDE3Ljg5MiwwLDAsMCwxNy45LTE3Ljg4NGwuMDI1LTQ0LjY4NywxMi42NzguMDA3LDguNDc3LDBhMTYuOTIyLDE2LjkyMiwwLDAsMCwxNi44ODMtMTYuODYzdi0uMDYyYTE2LjkyMiwxNi45MjIsMCwwLDAtMTYuODYzLTE2Ljg4M2wtOC40NzcsMC0xMi42NzgtLjAwNy0yMS4xNjctLjAxMiwyMS4xNjcuMDEyLjAyOS01MC43MTItMjEuMTY3LS4wMTNhMTYuNzQsMTYuNzQsMCwwLDEtOC45LTIuNTYzLDE3LjEsMTcuMSwwLDAsMS0yLjg2NS0yLjI1NywxNy40MzEsMTcuNDMxLDAsMCwxLTEuMjM1LTEuMzQzLDE3LjQzMSwxNy40MzEsMCwwLDAsMS4yMzUsMS4zNDMsMTcuMSwxNy4xLDAsMCwwLDIuODY1LDIuMjU3LDE2Ljc0LDE2Ljc0LDAsMCwwLDguOSwyLjU2M2wyMS4xNjcuMDEzLDEyLjY3OC4wMDcsOC40NzcsMEExNi45MjIsMTYuOTIyLDAsMCwwLDExODAuNSw1MjIuM3YtLjA2MWExNi45MjIsMTYuOTIyLDAsMCwwLTE2Ljg2My0xNi44ODNsLTguNDc3LDAtMTIuNjc4LS4wMDcuMDI5LTUwLjcxM1ptLTYxLjcxMS01OC43MzVhMTYuNzU3LDE2Ljc1NywwLDAsMCwzLjM4OS4zNDZoMGExNi43MTEsMTYuNzExLDAsMCwxLTYuNTUxLTEuMzM1QTE2LjY3MSwxNi42NzEsMCwwLDAsMTA1OS42MzEsMzk1Ljg4OVptLTguNTI1LTQuNjE3YTE3LjA2OCwxNy4wNjgsMCwwLDEtMi4wNjMtMi41QTE3LjA2OCwxNy4wNjgsMCwwLDAsMTA1MS4xMDYsMzkxLjI3MlptMTEuNzY4LDI1Ni45MDZoMFptLTc1LTgxLjI0NEg5NTMuMjQ4djUyLjE0OUg5MDQuNzE5di0xODUuM2g4My4xNTVjNDguOCwwLDc2Ljg4NCwyMy4yOTUsNzYuODg0LDY0LjU5MUMxMDY0Ljc1OCw1NDEuNzg2LDEwMzYuNjc2LDU2Ni45MzQsOTg3Ljg3NCw1NjYuOTM0Wm03NS4yMDctMTcwLjdhMTYuNzI2LDE2LjcyNiwwLDAsMCw4LjAyNy0yLjAzOUExNi43MjYsMTYuNzI2LDAsMCwxLDEwNjMuMDgxLDM5Ni4yMzVabTkuNDE5LTIuODg2YTE3LjA3OCwxNy4wNzgsMCwwLDAsMy41OTMtMy4yNjNBMTcuMDc4LDE3LjA3OCwwLDAsMSwxMDcyLjUsMzkzLjM0OVptNS40MTYtNS45NTNhMTYuODg3LDE2Ljg4NywwLDAsMCwxLjI4NC0zLjAyMUExNi44ODcsMTYuODg3LDAsMCwxLDEwNzcuOTE2LDM4Ny40Wm0xLjctNC42MzVhMTYuNzY0LDE2Ljc2NCwwLDAsMCwuMzQ2LTMuMzg5QTE2Ljc2NCwxNi43NjQsMCwwLDEsMTA3OS42MTgsMzgyLjc2MVptMzguMjM2LDI0MC41NTdhMTYuNzc0LDE2Ljc3NCwwLDAsMCwzLjM5LjM0N0ExNi43NzQsMTYuNzc0LDAsMCwxLDExMTcuODU0LDYyMy4zMThabS0zLjE0My0zMi4xMzRhMTYuNjkzLDE2LjY5MywwLDAsMSwzLjE2My0uOTg1LDE2LjY5MywxNi42OTMsMCwwLDAtMy4xNjMuOTg1LDE2Ljk2MSwxNi45NjEsMCwwLDAtNC4xNjcsMi41MzJBMTYuOTYxLDE2Ljk2MSwwLDAsMSwxMTE0LjcxMSw1OTEuMTg0Wm0tOS45NC02NS41MzNhMTYuNzY4LDE2Ljc2OCwwLDAsMCwuOTg2LDMuMTYzQTE2Ljc2OCwxNi43NjgsMCwwLDEsMTEwNC43NzEsNTI1LjY1MVptMi42LTk3LjM4N2ExNi44NSwxNi44NSwwLDAsMC0yLjEzMiw0LjQxMywxNi43MzksMTYuNzM5LDAsMCwwLS43NjUsNXYwYTE2Ljc1OSwxNi43NTksMCwwLDEsMi45LTkuNDE2LDE3LjEyNywxNy4xMjcsMCwwLDEsMi4wNjYtMi41QTE3LjEyNywxNy4xMjcsMCwwLDAsMTEwNy4zNzUsNDI4LjI2NFptMTAuNTQ3LDc3LjQxNWExNi42NjEsMTYuNjYxLDAsMCwwLTMuMTYyLjk4NSwxNy4wNDIsMTcuMDQyLDAsMCwwLTYuNDU5LDQuODIxLDE3LjA0MiwxNy4wNDIsMCwwLDEsNi40NTktNC44MjEsMTYuNjYxLDE2LjY2MSwwLDAsMSwzLjE2Mi0uOTg1LDE2Ljg0NiwxNi44NDYsMCwwLDEsMy4zOS0uMzQzQTE2Ljg0NiwxNi44NDYsMCwwLDAsMTExNy45MjIsNTA1LjY3OVoiIHRyYW5zZm9ybT0idHJhbnNsYXRlKC03NzYuMzY5IC0zMjAuMDY5KSIvPjwvc3ZnPg==');

        global $submenu;
        $wpenv_vars = wordpatch_wordpress_env();

        $submenu['wordpatch'][] = array(__wt($wpenv_vars, 'ADMIN_MENU_WORDPATCH_DASHBOARD'), 'activate_plugins', wordpatch_dashboard_uri($wpenv_vars));
        $submenu['wordpatch'][] = array(__wt($wpenv_vars, 'ADMIN_MENU_WORDPATCH_JOBS'), 'activate_plugins', wordpatch_jobs_uri($wpenv_vars));
        $submenu['wordpatch'][] = array(__wt($wpenv_vars, 'ADMIN_MENU_WORDPATCH_MAILBOX'), 'activate_plugins', wordpatch_mailbox_uri($wpenv_vars));
        $submenu['wordpatch'][] = array(__wt($wpenv_vars, 'ADMIN_MENU_WORDPATCH_SETTINGS'), 'activate_plugins', wordpatch_settings_uri($wpenv_vars));

        // TODO: Figure out a non-js way to make this target=_blank
        $submenu['wordpatch'][] = array(__wt($wpenv_vars, 'ADMIN_MENU_WORDPATCH_SUPPORT'), 'activate_plugins', 'https://jointbyte.freshdesk.com/');
    }
}

if(!function_exists('wordpatch_parent_file')) {
    /**
     * Implements the controller for WordPatch.
     *
     * @param $parent_file string
     * @return string
     */
    function wordpatch_parent_file($parent_file)
    {
        global $current_screen;

        if($current_screen->base !== wordpatch_page_id()) {
            return $parent_file;
        }
        
        global $submenu_file;
        $wpenv_vars = wordpatch_wordpress_env();

        $job_actions = wordpatch_job_actions($wpenv_vars);
        $dashboard_actions = wordpatch_dashboard_actions($wpenv_vars);
        $settings_actions = wordpatch_settings_actions($wpenv_vars);
        $mailbox_actions = wordpatch_mailbox_actions($wpenv_vars);

        if (in_array(wordpatch_action(), $dashboard_actions))
            $submenu_file = wordpatch_dashboard_uri($wpenv_vars);

        if (in_array(wordpatch_action(), $job_actions))
            $submenu_file = wordpatch_jobs_uri($wpenv_vars);

        if (in_array(wordpatch_action(), $mailbox_actions))
            $submenu_file = wordpatch_mailbox_uri($wpenv_vars);

        if (in_array(wordpatch_action(), $settings_actions))
            $submenu_file = wordpatch_settings_uri($wpenv_vars);

        return $parent_file;
    }
}

if(!function_exists('wordpatch_controller_wordpatch')) {
    /**
     * Implements the controller for WordPatch.
     */
    function wordpatch_controller_wordpatch()
    {
        $wpenv_vars = wordpatch_wordpress_env();
        $action = wordpatch_action();
        $login_uri = wordpatch_login_uri($wpenv_vars);

        wordpatch_action_logic($wpenv_vars, $action, $login_uri);
    }
}

if(!function_exists('wordpatch_render_page')) {
    /**
     * Implements the page renderer for WordPatch.
     */
    function wordpatch_render_page()
    {
        $wpenv_vars = wordpatch_wordpress_env();
        $action = wordpatch_action();
        $login_uri = wordpatch_login_uri($wpenv_vars);

        wordpatch_action_render($wpenv_vars, $action, $login_uri);
    }
}

if(!function_exists('wordpatch_add_action_links')) {
    /**
     * Responsible for adding the Settings link for WordPatch in the Plugins page.
     * @param $actions
     * @param $plugin_file
     * @return array
     */
    function wordpatch_add_action_links($actions, $plugin_file)
    {
        static $plugin;

        if (!isset($plugin))
            $plugin = plugin_basename(__FILE__); // TODO: Make wordpatch_plugin_basename ?

        if ($plugin == $plugin_file) {
            // TODO: Translations
            // TODO: For settings link, should we used network_admin_url()?
            $settings = array('settings' => '<a href="admin.php?page=wordpatch&action=settings"><i class="fa fa-cog" aria-hidden="true"></i> ' . __('Settings', 'General') . '</a>');
            $site_link = array('support' => '<a href="https://jointbyte.freshdesk.com/support/home" target="_blank"><i class="fa fa-cog" aria-hidden="true"></i> Support</a>');

            $actions = array_merge($settings, $actions);
            $actions = array_merge($site_link, $actions);
        }

        return $actions;
    }
}

if(!function_exists('wordpatch_plugin_row_meta')) {
    /**
     * Responsible for adding the meta links for WordPatch in the Plugins page.
     * @param $links
     * @param $file
     * @return array
     */
    function wordpatch_plugin_row_meta( $links, $file )
    {
        static $plugin;

        if (!isset($plugin))
            $plugin = plugin_basename(__FILE__); // TODO: Make wordpatch_plugin_basename ?

        if ( $file === $plugin ) {
            $new_links = array(
                'support' => '<a href="https://jointbyte.freshdesk.com/support/home" target="_blank"><i class="fa fa-cog" aria-hidden="true"></i> Support</a>',
                'doc' => '<a href="https://jointbyte.freshdesk.com/support/home" target="_blank"><i class="fa fa-book" aria-hidden="true"></i> Documentation</a>'
            );

            $links = array_merge( $links, $new_links );
        }

        return $links;
    }
}

if(!isset($__wordpatch_post)) {
    /**
     * Global variable used to track the original POST values before WordPress sanitizes them.
     */
    $__wordpatch_post = $_POST;
}

if(!isset($__wordpatch_files)) {
    /**
     * Global variable used to track the original FILES values before WordPress sanitizes them.
     */
    $__wordpatch_files = $_FILES;
}

// Call the entry point!
wordpatch_main();