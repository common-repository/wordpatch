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
 * Implements the shared chmod functionality.
 */
if(!function_exists('wordpatch_is_valid_chmod_value')) {
    function wordpatch_is_valid_chmod_value($chmod_value, $min_value = null, $max_value = null) {
        $min_value = $min_value === null ? 0 : max(0, (int)$min_value);
        $max_value = $max_value === null ? 0777 : max(0, (int)$max_value);

        if($chmod_value >= $min_value && $chmod_value <= $max_value) {
            return true;
        }

        return false;
    }
}

if(!function_exists('wordpatch_sanitize_chmod_value')) {
    function wordpatch_sanitize_chmod_value($chmod_value) {
        return max(0, (int)$chmod_value & 0777);
    }
}