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

if(!function_exists('wordpatch_calculate_fs_timeout')) {
    function wordpatch_calculate_fs_timeout($wpenv_vars)
    {
        $fs_timeout = wordpatch_get_option($wpenv_vars, 'wordpatch_fs_timeout', '');
        $fs_timeout = trim($fs_timeout);

        if($fs_timeout !== '') {
            $fs_timeout = max(0, (int)$fs_timeout);
            return $fs_timeout;
        }

        return null;
    }
}