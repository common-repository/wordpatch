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
 * Implements the logout functionality.
 */

if(!function_exists('wordpatch_logout_uri')) {
    /**
     * Construct a URI to 'logout'.
     *
     * @param $wpenv_vars
     * @return string
     */
    function wordpatch_logout_uri($wpenv_vars)
    {
        return wordpatch_add_query_params(wordpatch_env_get($wpenv_vars, 'base_uri'), 'action=' . WORDPATCH_WHERE_LOGOUT);
    }
}

if(!function_exists('wordpatch_features_logout')) {
    /**
     * Returns features supported by 'logout'. See full list of options inside `inc/actions.php`.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_features_logout($wpenv_vars)
    {
        return array(WORDPATCH_FEATURE_PREFACE);
    }
}

if(!function_exists('wordpatch_preface_logout')) {
    /**
     * Implements the preface logic for the logout action. Simply logs the user out and redirects
     * @param $wpenv_vars
     */
    function wordpatch_preface_logout($wpenv_vars) {
        $dashboard_uri = wordpatch_dashboard_uri($wpenv_vars);

        wordpatch_logout($wpenv_vars);
        wordpatch_redirect($dashboard_uri);
        exit();
    }
}