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
 * Implements the mailbox page.
 */

if(!function_exists('wordpatch_mailbox_uri')) {
    /**
     * Construct a URI to 'mailbox'.
     *
     * @param $wpenv_vars
     * @param $page
     * @param null $testmail_success
     * @return string
     */
    function wordpatch_mailbox_uri($wpenv_vars, $page = null, $testmail_success = null)
    {
        $page = $page === null ? null : max(1, (int)$page);

        return wordpatch_add_query_params(wordpatch_env_get($wpenv_vars, 'base_uri'), 'action=' . WORDPATCH_WHERE_MAILBOX .
            (($page !== null && $page) ? ('&wordpatchpage=' . $page) : '') .
            (($testmail_success !== null && $testmail_success) ? '&testmail_success=1' : ''));
    }
}

if(!function_exists('wordpatch_features_mailbox')) {
    /**
     * Returns features supported by 'mailbox'. See full list of options inside `inc/actions.php`.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_features_mailbox($wpenv_vars)
    {
        return array(WORDPATCH_FEATURE_REQUIRES_AUTH, WORDPATCH_FEATURE_RENDER);
    }
}

if(!function_exists('wordpatch_render_mailbox')) {
    /**
     * Render the mailbox page.
     *
     * @param $wpenv_vars
     */
    function wordpatch_render_mailbox($wpenv_vars)
    {
        // Calculate $where and $error_vars for use with our draw field function calls.
        $where = WORDPATCH_WHERE_MAILBOX;
        $error_vars = array();

        // Check if the mailer is configured.
        $is_mail_configured = wordpatch_calculate_mail_configured($wpenv_vars) === WORDPATCH_YES;
        $testmail_success = false;

        $page = 1;
        $total_mail = 0;
        $total_pages = 1;

        if ($is_mail_configured) {
            $page = isset($_GET['wordpatchpage']) ? max(1, (int)$_GET['wordpatchpage']) : 1;
            $total_mail = wordpatch_get_mail_count($wpenv_vars, $total_pages);
            $page = $page > $total_pages ? $total_pages : $page;

            $mailbox = wordpatch_get_mailbox($wpenv_vars, $page);

            $testmail_success = (isset($_GET['testmail_success']) && trim($_GET['testmail_success']) === '1');
        }

        wordpatch_render_header($wpenv_vars);
        ?>
        <div class="wordpatch_page">
            <?php if (!wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars))) { ?>
                <?php wordpatch_render_access_denied($wpenv_vars); ?>
            <?php } else if (!$is_mail_configured) { ?>
                <?php
                $link = sprintf("<a href=\"%s\">%s</a>", wordpatch_configmail_url($wpenv_vars),
                    __wte($wpenv_vars, 'MAILER_NOT_CONFIGURED_LINK'));
                ?>
                <?php wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'MAILER_NOT_CONFIGURED_HEADING'),
                    __wt($wpenv_vars, 'MAILER_NOT_CONFIGURED', $link)); ?>
            <?php } else { ?>
                <?php if ($testmail_success) { ?>
                    <?php
                    $link = sprintf("<a href=\"%s\">%s</a>",
                        wordpatch_mailbox_uri($wpenv_vars), __wten($wpenv_vars, 'MAILBOX_SUCCESS_TEST_MAIL_LINK'));
                    ?>
                    <?php wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'MAILBOX_SUCCESS_TEST_MAIL_HEADING'),
                        __wt($wpenv_vars, 'MAILBOX_SUCCESS_TEST_MAIL', $link)); ?>
                <?php } else { ?>
                    <div class="wordpatch_page_title_container">
                        <div class="wordpatch_page_title_left">
                            <h1 class="wordpatch_page_title"><?php echo __wte($wpenv_vars, 'MAILBOX_TITLE'); ?></h1>
                        </div>
                        <div class="wordpatch_page_title_right">
                            <div class="wordpatch_page_title_buttons">
                                <a href="<?php echo(wordpatch_testmail_uri($wpenv_vars)); ?>" class="wordpatch_button wordpatch_button_green">
                                    <?php echo(__wten($wpenv_vars, 'MAILBOX_LINK_TEST_MAIL')); ?>
                                </a>
                            </div>
                        </div>
                    </div>

                    <?php
                    if(empty($mailbox)) {
                        ?>
                        <div class="wordpatch_metabox">
                            <div class="wordpatch_metabox_body">
                                <p><?php echo(__wte($wpenv_vars, 'MAILBOX_EMPTY')); ?></p>
                            </div>
                        </div>
                        <?php
                    } else {
                        ?>
                        <div class="wordpatch_mailbox_container">
                            <div class="wordpatch_mailbox">
                                <?php foreach ($mailbox as $mail_single) { ?>
                                    <div class="wordpatch_mail_ctr">
                                        <div class="wordpatch_mail_ctr_flex">
                                            <div class="wordpatch_mail_ctr_left">
                                                <div class="wordpatch_mail_labels">
                                                    <p class="wordpatch_mail_label"><?php echo(__wten($wpenv_vars, 'MAILBOX_TABLE_SUBJECT')); ?></p>
                                                    <p class="wordpatch_mail_label"><?php echo(__wten($wpenv_vars, 'MAILBOX_TABLE_DATETIME')); ?></p>
                                                </div>
                                                <div class="wordpatch_mail_meta">
                                                    <p class="wordpatch_mail_subject">
                                                        <a href="<?php echo(wordpatch_readmail_uri($wpenv_vars, $mail_single['id'])); ?>">
                                                            <?php echo(htmlspecialchars(wordpatch_mailer_build_email_subject($wpenv_vars,
                                                            $mail_single['mail_template'], $mail_single['mail_vars']))); ?>
                                                        </a>
                                                    </p>
                                                    <p class="wordpatch_mail_datetime"><?php echo(htmlspecialchars(wordpatch_timestamp_to_readable($mail_single['datetime']))); ?></p>
                                                </div>
                                            </div>
                                            <div class="wordpatch_mail_ctr_right">
                                                <div class="wordpatch_text_right">
                                                    <a class="wordpatch_button wordpatch_job_button"
                                                       href="<?php echo(wordpatch_readmail_uri($wpenv_vars, $mail_single['id'])); ?>">
                                                        <i class="fa fa-envelope" aria-hidden="true"></i>
                                                        <span><?php echo(__wten($wpenv_vars, 'MAILBOX_TABLE_READ_LINK')); ?></span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="wordpatch_mailbox_pagination_container">
                                <p class="wordpatch_mailbox_pagination_total"><?php echo(__wte($wpenv_vars,
                                        'MAILBOX_TOTAL_RESULTS', $total_mail)); ?></p>
                                <div class="wordpatch_mailbox_pagination_links">
                                    <?php wordpatch_pagination($wpenv_vars, $page, $total_pages, WORDPATCH_WHERE_MAILBOX); ?>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                <?php } ?>
            <?php } ?>
        </div>
        <?php
        wordpatch_render_footer($wpenv_vars);
    }
}

if(!function_exists('wordpatch_mailbox_pagination_builder')) {
    function wordpatch_mailbox_pagination_builder($wpenv_vars, $page, $is_current) {
        $current_class = $is_current ? 'wordpatch_mailbox_page_link_current' : '';

        return sprintf("<a class=\"wordpatch_mailbox_page_link %s\" href=\"%s\">%d</a>",
            $current_class, wordpatch_mailbox_uri($wpenv_vars, $page), $page);
    }
}

if(!function_exists('wordpatch_mailbox_pagination_prev_builder')) {
    function wordpatch_mailbox_pagination_prev_builder($wpenv_vars, $page) {
        return sprintf("<a class=\"wordpatch_mailbox_page_link wordpatch_mailbox_page_link_previous\" href=\"%s\"><i class=\"fa fa-angle-left\"></i>&nbsp;%s</a>",
            wordpatch_mailbox_uri($wpenv_vars, $page), __wte($wpenv_vars, 'PREVIOUS_PAGE'));
    }
}


if(!function_exists('wordpatch_mailbox_pagination_next_builder')) {
    function wordpatch_mailbox_pagination_next_builder($wpenv_vars, $page) {
        return sprintf("<a class=\"wordpatch_mailbox_page_link wordpatch_mailbox_page_link_next\" href=\"%s\">%s&nbsp;<i class=\"fa fa-angle-right\"></i></a>",
            wordpatch_mailbox_uri($wpenv_vars, $page), __wte($wpenv_vars, 'NEXT_PAGE'));
    }
}