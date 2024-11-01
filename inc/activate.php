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
 * Implements the plugin activation functionality.
 */

if(!function_exists('wordpatch_activate')) {
    /**
     * Implements the activation hook for WordPatch. Responsible for creating/updating schema and default options.
     */
    function wordpatch_activate()
    {
        // Calculate the WordPress environment variables from the run-time installation.
        $wpenv_vars = wordpatch_wordpress_env();

        // Create/update the schema.
        wordpatch_create_schema($wpenv_vars);

        // Insert default settings if they haven't been created yet
        wordpatch_create_defaults($wpenv_vars);
    }
}

if(!function_exists('wordpatch_plugins_loaded_reactivate')) {
    /**
     * Implements the activation hook for WordPatch. Responsible for creating/updating schema and default options.
     */
    function wordpatch_plugins_loaded_reactivate()
    {
        // Calculate the WordPress environment variables from the run-time installation.
        $wpenv_vars = wordpatch_wordpress_env();

        // If the version matches the DB, do nothing.
        if(wordpatch_get_option($wpenv_vars, 'wordpatch_version') === WORDPATCH_VERSION) {
            return;
        }

        // Re-activate!
        wordpatch_activate();
    }
}

if(!function_exists('wordpatch_activated_plugin')) {
    /**
     * Called after any plugin has been activated. If WordPatch has been detected, we will redirect the user to either the
     * dashboard or the configuration wizard.
     *
     * @param $plugin
     */
    function wordpatch_activated_plugin($plugin) {
        if($plugin !== plugin_basename(__FILE__)) {
            return;
        }

        $wpenv_vars = wordpatch_wordpress_env();

        // TODO: Change this to take them to the wizard if they have not setup anything yet.

        wp_redirect(wordpatch_dashboard_uri($wpenv_vars));
        exit();
    }
}