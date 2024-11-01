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
 * Implements the internal processing functionality for the test mail page.
 */

if(!function_exists('wordpatch_testmail_process_internal')) {
    /**
     * Internal processing for the test mail page. Responsible for sending and persistence.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_testmail_process_internal($wpenv_vars) {
        // Begin to calculate the error list.
        $error_list = array();

        // Create an ID for the test mail.
        $mail_id = wordpatch_unique_id();

        // Create a test code for the test mail.
        $test_code = wordpatch_test_code();

        // Try to persist the database record.
        $persist_error = wordpatch_testmail_persist_db($wpenv_vars, $mail_id, $test_code);

        // If there is an error from persisting, append it to our list and return early.
        if ($persist_error !== null) {
            $error_list[] = $persist_error;
            return $error_list;
        }

        // Try to send the mail
        $mail_error = wordpatch_testmail_persist_mail($wpenv_vars, $test_code);

        // If there is an error from persisting, append it to our list and return early.
        if ($mail_error !== null) {
            $error_list[] = $mail_error;
            return $error_list;
        }

        // Redirect to the mailbox page.
        wordpatch_redirect(wordpatch_mailbox_uri($wpenv_vars, null, true));
        exit();
    }
}