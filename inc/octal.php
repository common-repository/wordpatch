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
 * Implements the shared octal functionality.
 */

if(!function_exists('wordpatch_string_to_octal')) {
    function wordpatch_string_to_octal($str, $max_octal = 0777, $min_octal = 0) {
        $str = ($str === null || $str === false) ? '' : trim($str);

        if($str === '') {
            return '';
        }

        $str = (int)$str;
        return max($min_octal, intval($str, 8) & $max_octal);
    }
}

if(!function_exists('wordpatch_convert_octal_to_dec')) {
    function wordpatch_convert_octal_to_dec($octal_str) {
        return base_convert($octal_str, 8, 10);
    }
}

if(!function_exists('wordpatch_get_display_octal_from_int')) {
    function wordpatch_get_display_octal_from_int($int) {
        if(is_string($int)) {
            $int = trim($int);
        }

        if($int === '') {
            return '';
        }

        $int = (int)$int;
        return str_pad(base_convert($int, 10, 8), 4, '0', STR_PAD_LEFT);
    }
}