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
 * Implements the read mail action.
 */

if(!function_exists('wordpatch_readmail_uri')) {
    /**
     * Construct a URI to 'readmail'.
     *
     * @param $wpenv_vars
     * @return string
     */
    function wordpatch_readmail_uri($wpenv_vars, $mail_id)
    {
        return wordpatch_add_query_params(wordpatch_env_get($wpenv_vars, 'base_uri'), 'action=' . WORDPATCH_WHERE_READMAIL .
            '&mail=' . urlencode($mail_id));
    }
}

if(!function_exists('wordpatch_features_readmail')) {
    /**
     * Returns features supported by 'readmail'. See full list of options inside `inc/actions.php`.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_features_readmail($wpenv_vars)
    {
        return array(WORDPATCH_FEATURE_REQUIRES_AUTH, WORDPATCH_FEATURE_RENDER);
    }
}

if(!function_exists('wordpatch_render_readmail')) {
    /**
     * Render the read mail page.
     *
     * @param $wpenv_vars
     */
    function wordpatch_render_readmail($wpenv_vars)
    {
        // Calculate $where and $error_vars for use with our draw field function calls.
        $where = WORDPATCH_WHERE_READMAIL;
        $error_vars = array();

        // Grab the mail ID from the request.
        $mail_id = isset($_GET['mail']) ? wordpatch_sanitize_unique_id($_GET['mail']) : '';

        // Grab the mail from the database.
        $mail = wordpatch_get_mail_by_id($wpenv_vars, $mail_id);

        $show_breadcrumbs = wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars)) &&
            $mail !== null;

        $breadcrumbs = !$show_breadcrumbs ? array() : array(
            wordpatch_mailbox_breadcrumb($wpenv_vars),
            array(
                'text' => __wt($wpenv_vars, 'BREADCRUMB_READ_MAIL')
            )
        );

        wordpatch_render_header($wpenv_vars, array(), $breadcrumbs);
        ?>
        <div class="wordpatch_page">
            <?php if (!wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars))) { ?>
                <?php wordpatch_render_access_denied($wpenv_vars); ?>
            <?php } else if(!$mail) { ?>
                <?php wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'READ_MAIL_NOTICE_HEADING'),
                    __wt($wpenv_vars, 'READ_MAIL_NOTICE')); ?>
            <?php } else { ?>
                <div class="wordpatch_page_title_container">
                    <div class="wordpatch_page_title_left">
                        <h1 class="wordpatch_page_title"><?php echo __wte($wpenv_vars, 'READ_MAIL_TITLE'); ?></h1>
                    </div>
                </div>

                <div class="wordpatch_mail_preview">
                    <?php
                    $subject = wordpatch_mailer_build_email_subject($wpenv_vars, $mail['mail_template'],
                        $mail['mail_vars']);
                    ?>
                    <?php echo(wordpatch_mailer_build_email_html($wpenv_vars, $mail['mail_template'],
                        $mail['mail_vars'], $subject, true)); ?>
                </div>
            <?php } ?>
        </div>
        <?php
        wordpatch_render_footer($wpenv_vars);
    }
}