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
 * Implement the shared vars functionality.
 */

if(!isset($__wordpatch_vars)) {
    $__wordpatch_vars = null;
}

if(!function_exists('wordpatch_var_set')) {
    function wordpatch_var_set($key, $value)
    {
        global $__wordpatch_vars;

        if ($__wordpatch_vars === null) {
            $__wordpatch_vars = array();
        }

        $__wordpatch_vars[$key] = $value;
    }
}

if(!function_exists('wordpatch_var_get')) {
    function wordpatch_var_get($key)
    {
        global $__wordpatch_vars;

        if ($__wordpatch_vars === null) {
            $__wordpatch_vars = array();
        }

        if (!isset($__wordpatch_vars[$key])) {
            return null;
        }

        return $__wordpatch_vars[$key];
    }
}

if(!function_exists('wordpatch_var_exists')) {
    function wordpatch_var_exists($key)
    {
        global $__wordpatch_vars;

        if ($__wordpatch_vars === null) {
            $__wordpatch_vars = array();
        }

        $var_keys = array_keys($__wordpatch_vars);

        if (!in_array($key, $var_keys)) {
            return false;
        }

        return true;
    }
}

if(!function_exists('wordpatch_remove_duplicates')) {
    function wordpatch_remove_duplicates($input_array) {
        $new_array = array();

        foreach($input_array as $elem_single) {
            if(in_array($elem_single, $new_array)) {
                continue;
            }

            $new_array[] = $elem_single;
        }

        return $new_array;
    }
}