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
 * Implements the database helper functions for the test mail page.
 */

if(!function_exists('wordpatch_testmail_persist_db')) {
    /**
     * Persists the database record for the test mail page. Returns an error string or null on success.
     *
     * @param $wpenv_vars
     * @param $mail_id
     * @return null|string
     */
    function wordpatch_testmail_persist_db($wpenv_vars, $mail_id, $test_code) {
        $init_user = wordpatch_get_current_user_id($wpenv_vars);

        $vars = array(
            'test_code' => $test_code
        );

        if(!wordpatch_mailbox_add($wpenv_vars, $mail_id, WORDPATCH_MAIL_TEMPLATE_TEST, $vars, $init_user)) {
            return WORDPATCH_UNKNOWN_DATABASE_ERROR;
        }

        return null;
    }
}