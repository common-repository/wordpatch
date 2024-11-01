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

if(!function_exists('wordpatch_mail_test_subject')) {
    function wordpatch_mail_test_subject($wpenv_vars, $vars) {
        return __wt($wpenv_vars, 'TEST_EMAIL_SUBJECT', $vars['test_code']);
    }
}

if(!function_exists('wordpatch_mail_test_html')) {
    function wordpatch_mail_test_html($wpenv_vars, $vars, $subject)
    {
        ob_start();
        ?>
        <table cellspacing="0" cellpadding="0" width="600" class="font-elem table-elem w320" style="">
            <tr class="font-elem tr-elem" style="">
                <td class="font-elem td-elem header-lg" style="">
                    <?php echo(__wte($wpenv_vars, 'TEST_EMAIL_MESSAGE')); ?>
                </td>
            </tr>
            <tr class="font-elem tr-elem" style="">
                <td class="font-elem td-elem free-text" style="">
                    <?php echo(__wte($wpenv_vars, 'TEST_EMAIL_MESSAGE2', $vars['test_code'])); ?>
                </td>
            </tr>
        </table>
        <?php
        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    }
}

if(!function_exists('wordpatch_mail_test_text')) {
    function wordpatch_mail_test_text($wpenv_vars, $vars, $subject)
    {
        ob_start();

        echo(__wt($wpenv_vars, 'TEST_EMAIL_MESSAGE') . "\n");
        echo(__wt($wpenv_vars, 'TEST_EMAIL_MESSAGE2', $vars['test_code']) . "\n");

        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    }
}