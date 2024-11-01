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
 * Implements the shared strings functionality.
 */

if(!function_exists('__wordpatch_make_double_quoted_string_replace')) {
    function __wordpatch_make_double_quoted_string_replace($matches) {
        $match = $matches[0];

        $escapeMe = substr($match, 0, 1);

        $escapeMap = array(
            "\f" => "\\f",
            "\n" => "\\n",
            "\r" => "\\r",
            "\t" => "\\t",
            "\v" => "\\v",
            "\\" => "\\\\",
            "\"" => "\\\"",
            "'" => "\\\\'",
            "$" => "\\$",
        );

        foreach($escapeMap as $replace => $replaceWith) {
            if($replace !== $escapeMe) {
                continue;
            }

            return $replaceWith;
        }

        return $match;
    }
}

if(!function_exists('wordpatch_make_double_quoted_string')) {
    function wordpatch_make_double_quoted_string($input_str) {
        $string = "\"" . preg_replace_callback("/([\\n\\v\\f\\t\\r\\\\\\'\\\"\\$])/", '__wordpatch_make_double_quoted_string_replace', $input_str) . "\"";

        return $string;
    }
}

if(!function_exists('wordpatch_convert_string_to_timestamp')) {
    function wordpatch_convert_string_to_timestamp($string, $timezone = 'UTC') {
        $oldTZ = date_default_timezone_get();
        date_default_timezone_set($timezone);

        $value = strtotime($string);

        // Set the timezone back
        date_default_timezone_set($oldTZ);

        return $value;
    }
}

if(!function_exists('wordpatch_timestamp_to_readable')) {
    function wordpatch_timestamp_to_readable($timestamp = null) {
        if($timestamp === null) {
            $timestamp = wordpatch_utc_timestamp();
        }

        return date('F j, Y @ g:i a', $timestamp);
    }
}

if(!function_exists('wordpatch_add_query_params')) {
    function wordpatch_add_query_params($existing_uri, $query_str) {
        $query_pos = strpos($existing_uri, '?');

        if($query_pos !== false) {
            return $existing_uri . '&' . $query_str;
        }

        return $existing_uri . '?' . $query_str;
    }
}

if(!function_exists('wordpatch_display_name')) {
    function wordpatch_display_name($options, $key, $default = '')
    {
        return isset($options[$key]) ? $options[$key] : $default;
    }
}

if(!function_exists('wordpatch_display_self')) {
    function wordpatch_display_self($wpenv_vars, $self) {
        return $self;
    }
}