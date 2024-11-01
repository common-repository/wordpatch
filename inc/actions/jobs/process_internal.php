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
 * Implements the internal processing functionality for the jobs form.
 */

if(!function_exists('wordpatch_jobs_process_internal')) {
    /**
     * Internal processing for the jobs form. Responsible for validation and persistence.
     *
     * @param $wpenv_vars
     * @param $current
     * @return array
     */
    function wordpatch_jobs_process_internal($wpenv_vars, $current) {
        // Begin to calculate the error list.
        $error_list = array();

        // Calculate the posted order IDs.
        $order_ids = wordpatch_jobs_post_vars($wpenv_vars);

        // If the order IDs is null, they did not post it properly. Append an error and return early.
        if($order_ids === null) {
            $error_list[] = WORDPATCH_UNKNOWN_ERROR;
            return $error_list;
        }

        // Try to persist the database record.
        $persist_error = wordpatch_jobs_persist_db($wpenv_vars, $order_ids);

        // Check if there was a persist error. Append if so and return early.
        if($persist_error !== null) {
            $error_list[] = $persist_error;
            return $error_list;
        }

        // Redirect to the page.
        wordpatch_redirect(wordpatch_jobs_uri($wpenv_vars, false, null, null, null, true));
        exit();
    }
}