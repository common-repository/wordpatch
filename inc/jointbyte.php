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
 * Implements the JointByte functionality.
 */

if(!function_exists('wordpatch_jointbyte_url')) {
    function wordpatch_jointbyte_url($wpenv_vars)
    {
        return "https://jointbyte.com/";
    }
}

if(!function_exists('wordpatch_product_url')) {
    function wordpatch_product_url($wpenv_vars)
    {
        return "https://jointbyte.com/wordpatch/";
    }
}

if(!function_exists('jointbyte_facebook_url')) {
    function jointbyte_facebook_url($wpenv_vars)
    {
        return "https://www.facebook.com/JointByte/";
    }
}

if(!function_exists('jointbyte_github_url')) {
    function jointbyte_github_url($wpenv_vars)
    {
        return "https://github.com/YoursLtd";
    }
}

if(!function_exists('wordpatch_twitter_url')) {
    function wordpatch_twitter_url($wpenv_vars)
    {
        return "https://twitter.com/Word_Patch";
    }
}