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
 * Implements the patch type related functionality.
 */

if(!function_exists('wordpatch_patch_types')) {
    function wordpatch_patch_types()
    {
        return array(
            wordpatch_patch_type_text(),
            wordpatch_patch_type_file(),
            wordpatch_patch_type_simple()
        );
    }
}

if(!function_exists('wordpatch_patch_type_text')) {
    function wordpatch_patch_type_text()
    {
        return 'text';
    }
}

if(!function_exists('wordpatch_patch_type_file')) {
    function wordpatch_patch_type_file()
    {
        return 'file';
    }
}

if(!function_exists('wordpatch_patch_type_simple')) {
    function wordpatch_patch_type_simple()
    {
        return 'simple';
    }
}

if(!function_exists('wordpatch_is_valid_patch_type')) {
    function wordpatch_is_valid_patch_type($patch_type)
    {
        return in_array($patch_type, wordpatch_patch_types());
    }
}

if(!function_exists('wordpatch_default_patch_type')) {
    function wordpatch_default_patch_type() {
        return wordpatch_patch_type_text();
    }
}

if(!function_exists('wordpatch_display_patch_type')) {
    function wordpatch_display_patch_type($wpenv_vars, $patch_type)
    {
        $display_names = array(
            wordpatch_patch_type_text() => __wt($wpenv_vars, 'PATCH_TYPE_TEXT'),
            wordpatch_patch_type_file() => __wt($wpenv_vars, 'PATCH_TYPE_FILE'),
            wordpatch_patch_type_simple() => __wt($wpenv_vars, 'PATCH_TYPE_SIMPLE')
        );

        return wordpatch_display_name($display_names, $patch_type);
    }
}