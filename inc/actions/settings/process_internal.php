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
 * Implements the internal processing functionality for the settings form.
 */

if(!function_exists('wordpatch_settings_process_internal')) {
    /**
     * Internal processing for the settings form. Responsible for validation and persistence.
     *
     * @param $wpenv_vars
     * @param $current
     * @return array
     */
    function wordpatch_settings_process_internal($wpenv_vars, $current) {
        // Begin to calculate the error list.
        $error_list = array();

        // Try to persist the database record.
        wordpatch_settings_persist_db($wpenv_vars, $current);

        // Redirect to the edit job page.
        wordpatch_redirect(wordpatch_settings_uri($wpenv_vars, true));
        exit();
    }
}