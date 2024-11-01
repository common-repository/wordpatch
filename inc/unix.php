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

if(!function_exists('wordpatch__sanitize_unix_file_path__replace_double_slashes')) {
    function wordpatch__sanitize_unix_file_path__replace_double_slashes($file_path)
    {
        // Are there any instances of // found? If not, we are done.
        if (strpos($file_path, '//') === false) {
            return $file_path;
        }

        // Replace all the // with / that we can see in this run-through.
        $new_path = str_replace('//', '/', $file_path);

        // Return the result if we got rid of them all.
        if (strpos($new_path, '//') === false) {
            return $new_path;
        }

        // Otherwise, re-enter this function with the new value.
        return wordpatch__sanitize_unix_file_path__replace_double_slashes($new_path);
    }
}

if(!function_exists('wordpatch__sanitize_unix_file_path__rid_of')) {
    function wordpatch__sanitize_unix_file_path__rid_of($file_path, $rid_of)
    {
        if(strlen($file_path) <= 0 || !is_string($file_path)) {
            return $file_path;
        }

        $file_path_new = str_replace($rid_of, '', $file_path);

        if($file_path_new !== $file_path) {
            return wordpatch__sanitize_unix_file_path__rid_of($file_path_new, $rid_of);
        }

        return $file_path_new;
    }
}

if(!function_exists('wordpatch__sanitize_unix_file_path__rid_of_trailing_dots')) {
    function wordpatch__sanitize_unix_file_path__rid_of_trailing_dots($file_path)
    {
        if(!is_string($file_path)) {
            return $file_path;
        }

        $file_path = trim($file_path);

        if(strlen($file_path) <= 0) {
            return $file_path;
        }

        $last_char_index = strlen($file_path) - 1;
        $last_char = substr($file_path, $last_char_index, 1);

        if($last_char !== '.') {
            return $file_path;
        }

        $file_path_new = substr($file_path, 0, strlen($file_path) - 1);
        return wordpatch__sanitize_unix_file_path__rid_of_trailing_dots($file_path_new);
    }
}

if(!function_exists('wordpatch__sanitize_unix_file_path__rid_of_leading_dots')) {
    function wordpatch__sanitize_unix_file_path__rid_of_leading_dots($file_path)
    {
        if(!is_string($file_path)) {
            return $file_path;
        }

        $file_path = trim($file_path);

        if(strlen($file_path) <= 0) {
            return $file_path;
        }

        $first_char = substr($file_path, 0, 1);

        if($first_char !== '.') {
            return $file_path;
        }

        $file_path_new = substr($file_path, 1);
        return wordpatch__sanitize_unix_file_path__rid_of_leading_dots($file_path_new);
    }
}

if(!function_exists('wordpatch_convert_encoded_unicode_from_unix_patch__callback')) {
    function wordpatch_convert_encoded_unicode_from_unix_patch__callback($matches) {
        $octal_val = substr($matches[0], 1);

        if(base_convert($octal_val, 8, 10) > base_convert('0377', 8, 10)) {
            return $matches[0];
        }

        return pack("H*" , base_convert($octal_val, 8, 16));
    }
}

if(!function_exists('wordpatch_convert_encoded_unicode_from_unix_patch')) {
    function wordpatch_convert_encoded_unicode_from_unix_patch($encoded_string) {
        wordpatch_mbstring_binary_safe_encoding();
        $converted_string = preg_replace_callback("/(\\\\[0-7]+)/", 'wordpatch_convert_encoded_unicode_from_unix_patch__callback', $encoded_string);
        wordpatch_reset_mbstring_encoding();

        return $converted_string;
    }
}
if(!function_exists('wordpatch_sanitize_unix_file_path')) {
    /**
     * This isn't perfect but is designed to be strict and disallow things that are naughty (ie: ../ and ./ or trailing/leading dots)
     * @param $file_path
     * @param bool $remove_trailing_slash
     * @param bool $remove_leading_slash
     * @return bool|mixed|string
     */
    function wordpatch_sanitize_unix_file_path($file_path, $remove_trailing_slash = true, $remove_leading_slash = true)
    {
        // Cap the path to 2048 chars to prevent people from abusing this algorithm
        if (strlen($file_path) > 2048) {
            $file_path = substr($file_path, 0, 2048);
        }

        // First replace \ with /
        $new_path = str_replace("\\", "/", $file_path);

        // This might have resulted in an empty string. Return it if so.
        if (strlen($new_path) <= 0)
            return $new_path;

        // Now get rid of all the // instances (replace them with /)
        $new_path = wordpatch__sanitize_unix_file_path__replace_double_slashes($new_path);

        // This might have resulted in an empty string. Return it if so.
        if (strlen($new_path) <= 0)
            return $new_path;

        // Remove all instances of ../
        $new_path = wordpatch__sanitize_unix_file_path__rid_of($new_path, '../');

        // Remove all instances of ./
        $new_path = wordpatch__sanitize_unix_file_path__rid_of($new_path, './');

        // Remove dots from end of string
        $new_path = wordpatch__sanitize_unix_file_path__rid_of_trailing_dots($new_path);

        // Remove dots from beginning of string
        $new_path = wordpatch__sanitize_unix_file_path__rid_of_leading_dots($new_path);

        // Get rid of / from the beginning of the string
        if($remove_leading_slash) {
            if (substr($new_path, 0, 1) === '/')
                $new_path = substr($new_path, 1);
        }

        // Get rid of / from the end of the string
        if($remove_trailing_slash) {
            if (substr($new_path, strlen($new_path) - 1, 1) === '/')
                $new_path = substr($new_path, 0, strlen($new_path) - 1);
        }

        return trim($new_path);
    }
}

if(!function_exists('wordpatch_get_components_from_unix_path')) {
    function wordpatch_get_components_from_unix_path($file_path)
    {
        $components = explode('/', $file_path);
        $nonEmptyComponents = array();

        foreach ($components as $componentSingle) {
            if (trim($componentSingle) === '') {
                continue;
            }

            $nonEmptyComponents[] = $componentSingle;
        }

        return $nonEmptyComponents;
    }
}

if(!function_exists('wordpatch_remove_front_components_from_unix_path')) {
    function wordpatch_remove_front_components_from_unix_path($file_path, $remove_front_components)
    {
        $remove_front_components = max(0, (int)$remove_front_components);

        if ($remove_front_components <= 0) {
            return $file_path;
        }

        $components = wordpatch_get_components_from_unix_path($file_path);

        if (count($components) - $remove_front_components <= 0) {
            return '';
        }

        $actualComponents = array();

        for ($i = 0 + $remove_front_components; $i < count($components); $i++) {
            $actualComponents[] = $components[$i];
        }

        return implode('/', $actualComponents);
    }
}

if(!function_exists('wordpatch_is_invalid_unix_path')) {
    function wordpatch_is_invalid_unix_path($filePath)
    {
        return $filePath === '' || !$filePath || $filePath === '.' ||
            substr($filePath, strlen($filePath) - 1, 1) === '.' ||
            strpos($filePath, '..') !== false ||
            strpos($filePath, './') !== false;
    }
}

if(!function_exists('wordpatch_sanitize_absolute_unix_path')) {
    function wordpatch_sanitize_absolute_unix_path($unix_path) {
        $unix_path = trim($unix_path);
        // what kind of path type?
        $path_type = 0; // 0 = unix-y, 1 = windows-y, 2 = network-y
        $first_two = substr($unix_path, 0, 2);

        if($first_two === '//' || $first_two === '\\\\') {
            $path_type = 2;
        }

        $unix_path_alt = wordpatch_sanitize_unix_file_path($unix_path, true, true);
        if(preg_match("/^[A-Za-z]:[\\\\\\/]/", $unix_path_alt)) {
            $path_type = 1;
        }

        if($path_type === 0) {
            return '/' . $unix_path_alt;
        }

        if($path_type === 1) {
            return $unix_path_alt;
        }

        if($path_type === 2) {
            return '//' . $unix_path_alt;
        }

        return '/' . $unix_path_alt;
    }
}

if(!function_exists('wordpatch_utc_timestamp')) {
    /**
     * Calculates the current unix timestamp in UTC.
     */
    function wordpatch_utc_timestamp() {
        // Grab the old default timezone.
        $old_ts = date_default_timezone_get();

        // Temporarily change the timezone to UTC.
        date_default_timezone_set('UTC');

        // Grab our current timestamp.
        $timestamp = time();

        // Set the timezone back to the old timezone.
        date_default_timezone_set($old_ts);

        // Return the timstamp
        return $timestamp;
    }
}

if(!function_exists('wordpatch_normalize_path')) {
    /**
     * Portable path normalizer (replace "\" with "/")
     * @param $path
     * @return mixed
     */
    function wordpatch_normalize_path($path)
    {
        return str_replace('\\', '/', $path);
    }
}