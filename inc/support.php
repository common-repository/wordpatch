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
 * Implements the support functionality for Wordpatch. Support is provided by the JointByte team through Freshdesk.
 */

if(!function_exists('wordpatch_support_url')) {
    function wordpatch_support_url() {
        return 'https://jointbyte.freshdesk.com';
    }
}

if(!function_exists('wordpatch_documentation_url')) {
    function wordpatch_documentation_url() {
        return 'https://jointbyte.freshdesk.com/support/solutions/35000132023';
    }
}