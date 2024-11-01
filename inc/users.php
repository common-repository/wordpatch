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
 * Implements the shared user functionality.
 */

if(!function_exists('wordpatch_is_wordpatch_admin')) {
    function wordpatch_is_wordpatch_admin($wpenv_vars, $user_id, $cache = true)
    {
        $cache_found = false;
        $admin_check = wordpatch_cache_get($user_id, 'wordpatch_admin', false, $cache_found);

        if($cache && $cache_found) {
            return $admin_check;
        }

        $caps = wordpatch_get_user_capabilities($wpenv_vars, $user_id);
        $admin_check = in_array('activate_plugins', array_keys($caps));

        wordpatch_cache_set($user_id, $admin_check, 'wordpatch_admin');

        return $admin_check;
    }
}

if(!function_exists('wordpatch_get_user_capabilities')) {
    function wordpatch_get_user_capabilities($wpenv_vars, $user_id)
    {
        $user_id = max(0, (int)$user_id);

        if($user_id <= 0) {
            return array();
        }

        // @adaptednote: MU support
        $roles = wordpatch_get_roles($wpenv_vars);

        $role_names = array_keys($roles);

        $caps_key = wordpatch_db_table_prefix($wpenv_vars) . 'capabilities';
        $caps = wordpatch_get_user_meta($wpenv_vars, $user_id, $caps_key, true);

        if(!is_array($caps)) {
            return array();
        }

        //Filter out caps that are not role names and assign to $this->roles
        $user_roles = array();

        foreach(array_keys($caps) as $cap_single) {
            if(!in_array($cap_single, $role_names)) {
                continue;
            }

            $user_roles[] = $cap_single;
        }

        $allcaps = array();
        foreach($user_roles as $user_role) {
            $the_role = $roles[$user_role];
            $role_caps = is_array($the_role['capabilities']) ? $the_role['capabilities'] : array();
            $allcaps = array_merge((array)$allcaps, $role_caps);
        }
        $allcaps = array_merge($allcaps, $caps );

        return $allcaps;
    }
}

if(!function_exists('wordpatch_get_display_user')) {
    function wordpatch_get_display_user($wpenv_vars, $user_id) {
        $user_id = $user_id === null ? 0 : max(0, (int)$user_id);

        $user_wp = $user_id <= 0 ? null :
            wordpatch_get_user_by($wpenv_vars, 'id', $user_id);

        return !$user_wp ? __wt($wpenv_vars, 'SYSTEM') : sprintf("%s [#%d]", $user_wp->user_login, $user_id);
    }
}