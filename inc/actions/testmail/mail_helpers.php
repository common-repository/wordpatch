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
 * Implements the mailer helper functions for the test mail pages.
 */

if(!function_exists('wordpatch_testmail_persist_mail')) {
    /**
     * Send the email for the test mail page. Returns an error or null on success.
     *
     * @param $wpenv_vars
     * @return null|string
     */
    function wordpatch_testmail_persist_mail($wpenv_vars, $test_code) {
        // Calculate mailer info.
        $mailer_info = wordpatch_calculate_mailer_info($wpenv_vars);

        $vars = array(
            'test_code' => $test_code
        );

        if(!wordpatch_mailer_send($wpenv_vars, $mailer_info, WORDPATCH_MAIL_TEMPLATE_TEST, $vars)) {
            return WORDPATCH_MAILER_FAILED;
        }

        return null;
    }
}