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
 * Implements JSON helper functions.
 */

if(!function_exists('wordpatch_json_decode')) {
    /**
     * Wrapper for JSON decode that defaults to using $assoc = true.
     *
     * @param $json
     * @param bool $assoc
     * @param int $depth
     * @param int $options
     * @return array|mixed|object
     */
    function wordpatch_json_decode($json, $assoc = true, $depth = 512, $options = 0) {
        return json_decode($json, $assoc, $depth, $options);
    }
}