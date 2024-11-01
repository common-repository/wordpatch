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
 * Primarily responsible for defining logic related to the WordPatch request framework that is utilized by the admin
 * panel and the rescue interface alike.
 */

if(!isset($__wordpatch_action)) {
    /**
     * Global variable used to cache the return value of `wordpatch_action`.
     */
    $__wordpatch_action = null;
}

if(!function_exists('wordpatch_action')) {
    /**
     * Returns the action for the current request. Local caching will be used in the event of multiple calls.
     * If the action is invalid, 'dashboard' will be returned and cached instead.
     *
     * PS: The cache for this function is kept inside the global variable `$__wordpatch_action`.
     *
     * @return string
     */
    function wordpatch_action()
    {
        // Expose our global variable cache to this function
        global $__wordpatch_action;

        // If the function has been called before, simply return the cached value.
        if($__wordpatch_action !== null) {
            return $__wordpatch_action;
        }

        // Calculate $action using the GET variable named 'action'.
        $action = isset($_GET['action']) ? strtolower(trim($_GET['action'])) : 'dashboard';

        // Whitelist of allowed actions.
        $allowed_actions = array(WORDPATCH_WHERE_SETTINGS, WORDPATCH_WHERE_LOGIN, WORDPATCH_WHERE_LOGOUT, WORDPATCH_WHERE_JOBS,
            WORDPATCH_WHERE_NEWJOB, WORDPATCH_WHERE_EDITJOB, WORDPATCH_WHERE_REDIRECT, WORDPATCH_WHERE_TRASHJOB, WORDPATCH_WHERE_RESTOREJOB,
            WORDPATCH_WHERE_DASHBOARD, WORDPATCH_WHERE_CONFIGDB, WORDPATCH_WHERE_CONFIGFS, WORDPATCH_WHERE_CONFIGLICENSE,
            WORDPATCH_WHERE_CONFIGMAIL, WORDPATCH_WHERE_CONFIGRESCUE, WORDPATCH_WHERE_DELETEJOB, WORDPATCH_WHERE_RUNJOB,
            WORDPATCH_WHERE_JUDGE, WORDPATCH_WHERE_NEWPATCH, WORDPATCH_WHERE_EDITPATCH, WORDPATCH_WHERE_ERASEPATCH, WORDPATCH_WHERE_DELETEPATCH,
            WORDPATCH_WHERE_WORK, WORDPATCH_WHERE_PROGRESS, WORDPATCH_WHERE_HEALTH, WORDPATCH_WHERE_MAILBOX, WORDPATCH_WHERE_ACCEPTJOB,
            WORDPATCH_WHERE_REJECTJOB, WORDPATCH_WHERE_LOGS, WORDPATCH_WHERE_LOGDETAIL, WORDPATCH_WHERE_JOBDETAIL, WORDPATCH_WHERE_READMAIL,
            WORDPATCH_WHERE_LOADJOBFILE, WORDPATCH_WHERE_TESTMAIL);

        // Fallback to the 'dashboard' action if the one specified is not valid.
        if(!in_array($action, $allowed_actions)) {
            $action = WORDPATCH_WHERE_DASHBOARD;
        }

        // Store the the cache variable and return the determined action.
        $__wordpatch_action = $action;
        return $action;
    }
}

if(!function_exists('wordpatch_action_logic')) {
    /**
     * Perform the logic for the current request action.
     * 1. The logic first checks which features are enabled for the action by calling the function with the following
     * naming convention:
     * - wordpatch_features_{$action}
     * - For example, for 'dashboard', the function would be prototyped wordpatch_features_dashboard($wpenv_vars)
     * - This function should return an array of strings. Valid options are: 'preface', 'requires_auth', 'process',
     * and 'render'.
     *
     * 2. If it is determined that the 'preface' feature is enabled, the preface is called with the following naming
     * convention:
     * - wordpatch_preface_{$action}
     * - For example, for 'logout', the function would be prototyped wordpatch_preface_logout($wpenv_vars)
     * - This function does not need to return anything.
     * - You should use this feature to perform logic before the 'requires_auth' feature (described in #3).
     * - If you want your preface to prevent further execution, simply call exit() inside of it.
     *
     * 3. Next, if it is determined that the 'requires_auth' feature is enabled and the user is not logged in, the user
     * is redirected to $login_uri.
     *
     * 4. Lastly, if it is determined that the 'process' feature is enabled and the user has submitted post data,
     * the process is called with the following naming convention:
     * - wordpatch_process_{$action}
     * - For example, for 'editjob', the function would be prototyped wordpatch_process_editjob($wpenv_vars, $login_uri)
     * - Please note that this function has an additional parameter (which must exist but may be ignored): $login_uri
     * - This function does not need to return anything.
     * - You should use this feature to perform logic after the 'requires_auth' feature (described in #3).
     * - If you want your preface to prevent further execution, simply call exit() inside of it.
     *
     * 5. `wordpatch_action_logic` does not return any value(s). It is called from both the rescue interface and the
     * admin panel.
     *
     * PS: For more information about the 'render' feature referenced in #1, please see the documentation for:
     * - wordpatch_action_render($wpenv_vars, $action, $login_uri)
     * - The function referenced above is called after `wordpatch_action_logic`.
     * - Calling exit() within 'preface' or 'process' will prevent calls to `wordpatch_action_render`.
     *
     * @param $wpenv_vars
     * @param $action
     * @param $login_uri
     */
    function wordpatch_action_logic($wpenv_vars, $action, $login_uri) {
        // First, query the features supported by this action.
        $features_fn_name = "wordpatch_features_$action";
        $action_features = call_user_func_array($features_fn_name, array($wpenv_vars));

        // If a preface is supported, call it.
        if(in_array(WORDPATCH_FEATURE_PREFACE, $action_features)) {
            $handler_fn_name = "wordpatch_preface_$action";
            call_user_func_array($handler_fn_name, array($wpenv_vars));
        }

        // Check if requires_auth is supported, and if so we should redirect to the login URI if we aren't logged in.
        if(in_array(WORDPATCH_FEATURE_REQUIRES_AUTH, $action_features) && !wordpatch_is_logged_in($wpenv_vars)) {
            wordpatch_redirect($login_uri);
            exit();
        }

        // If a process is supported, and a post request has been submitted, process it.
        if(in_array(WORDPATCH_FEATURE_PROCESS, $action_features) && isset($_POST['submit'])) {
            $process_fn_name = "wordpatch_process_$action";
            call_user_func_array($process_fn_name, array($wpenv_vars, $login_uri));
        }
    }
}

if(!function_exists('wordpatch_action_render')) {
    /**
     * Perform the rendering logic for the current request action.
     * 1. The logic first checks which features are enabled for the action by calling the function with the following
     * naming convention:
     * - wordpatch_features_{$action}
     * - For example, for 'dashboard', the function would be prototyped wordpatch_features_dashboard($wpenv_vars)
     * - This function should return an array of strings. Valid options are: 'preface', 'requires_auth', 'process',
     * and 'render'.
     *
     * 2. If it is determined that the 'render' feature is enabled, the render function is called with the following
     * naming convention:
     * - wordpatch_render_{$action}
     * - For example, for 'editjob', the function would be prototyped wordpatch_render_editjob($wpenv_vars, $login_uri)
     * - Please note that this function has an additional parameter (which must exist but may be ignored): $login_uri
     * - This function does not need to return anything. You should echo inside of it instead to render HTML.
     * - You should use this feature to perform most rendering related tasks. If you need more control, take a look at
     * the 'preface' or 'process' features provided within `wordpatch_action_logic`.
     * - You should not call header(), exit(), or die() within this function since output has already started being
     * sent to the browser. Instead you should use either the 'preface' or 'process' features provided within
     * `wordpatch_action_logic`.
     *
     * 3. `wordpatch_action_render` does not return any value(s). It is called from both the rescue interface and the
     * admin panel.
     *
     * PS: For more information about the 'preface', 'requires_auth', and 'process' features referenced in #1, please
     * see the documentation for:
     * - wordpatch_action_logic($wpenv_vars, $action, $login_uri)
     * - The function referenced above is called before `wordpatch_action_render`.
     *
     * @param $wpenv_vars
     * @param $action
     * @param $login_uri
     */
    function wordpatch_action_render($wpenv_vars, $action, $login_uri) {
        // First, query the features supported by this where.
        $features_fn_name = "wordpatch_features_$action";
        $action_features = call_user_func_array($features_fn_name, array($wpenv_vars));

        // If a render is supported, call it.
        if(in_array(WORDPATCH_FEATURE_RENDER, $action_features)) {
            $render_fn_name = "wordpatch_render_$action";
            call_user_func_array($render_fn_name, array($wpenv_vars, $login_uri));
        }
    }
}

if(!function_exists('wordpatch_job_actions')) {
    function wordpatch_job_actions($wpenv_vars) {
        return array(
            WORDPATCH_WHERE_JOBS,
            WORDPATCH_WHERE_EDITJOB,
            WORDPATCH_WHERE_NEWJOB,
            WORDPATCH_WHERE_RUNJOB,
            WORDPATCH_WHERE_JOBDETAIL,
            WORDPATCH_WHERE_TRASHJOB,
            WORDPATCH_WHERE_NEWPATCH,
            WORDPATCH_WHERE_EDITPATCH,
            WORDPATCH_WHERE_DELETEPATCH,
            WORDPATCH_WHERE_LOGS,
            WORDPATCH_WHERE_LOGDETAIL,
            WORDPATCH_WHERE_REDIRECT,
            WORDPATCH_WHERE_JUDGE
        );
    }
}

if(!function_exists('wordpatch_dashboard_actions')) {
    function wordpatch_dashboard_actions($wpenv_vars) {
        return array(
            WORDPATCH_WHERE_DASHBOARD
        );
    }
}

if(!function_exists('wordpatch_settings_actions')) {
    function wordpatch_settings_actions($wpenv_vars) {
        return array(
            WORDPATCH_WHERE_SETTINGS,
            WORDPATCH_WHERE_CONFIGDB,
            WORDPATCH_WHERE_CONFIGFS,
            WORDPATCH_WHERE_CONFIGLICENSE,
            WORDPATCH_WHERE_CONFIGMAIL,
            WORDPATCH_WHERE_CONFIGRESCUE
        );
    }
}

if(!function_exists('wordpatch_mailbox_actions')) {
    function wordpatch_mailbox_actions($wpenv_vars) {
        return array(
            WORDPATCH_WHERE_MAILBOX,
            WORDPATCH_WHERE_TESTMAIL,
            WORDPATCH_WHERE_READMAIL
        );
    }
}