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
 * Implements the test mail page.
 */

// Include the testmail dependencies
include_once(dirname(__FILE__) . '/testmail/process_internal.php');
include_once(dirname(__FILE__) . '/testmail/db_helpers.php');
include_once(dirname(__FILE__) . '/testmail/mail_helpers.php');

if(!function_exists('wordpatch_testmail_uri')) {
    /**
     * Construct a URI to 'testmail'.
     *
     * @param $wpenv_vars
     * @return string
     */
    function wordpatch_testmail_uri($wpenv_vars)
    {
        return wordpatch_add_query_params(wordpatch_env_get($wpenv_vars, 'base_uri'), 'action=' . WORDPATCH_WHERE_TESTMAIL);
    }
}

if(!function_exists('wordpatch_features_testmail')) {
    /**
     * Returns features supported by 'testmail'. See full list of options inside `inc/actions.php`.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_features_testmail($wpenv_vars)
    {
        return array(WORDPATCH_FEATURE_REQUIRES_AUTH, WORDPATCH_FEATURE_PROCESS, WORDPATCH_FEATURE_RENDER);
    }
}

if(!function_exists('wordpatch_process_testmail')) {
    /**
     * Handles processing logic for the test mail page.
     *
     * @param $wpenv_vars
     */
    function wordpatch_process_testmail($wpenv_vars)
    {
        if (!wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars))) {
            return;
        }

        // Try to process the test mail using the internal function.
        $error_list = wordpatch_testmail_process_internal($wpenv_vars);

        // Set the error list variable for access in render.
        wordpatch_var_set('testmail_errors', $error_list);
    }
}

if(!function_exists('wordpatch_render_testmail')) {
    /**
     * Render the test mail page.
     *
     * @param $wpenv_vars
     */
    function wordpatch_render_testmail($wpenv_vars)
    {
        // Calculate $where and $error_vars for use with our draw field function calls.
        $where = WORDPATCH_WHERE_TESTMAIL;
        $error_vars = array();

        // Grab the test mail errors from the variable store.
        $testmail_errors = wordpatch_var_get('testmail_errors');
        $testmail_errors = wordpatch_no_errors($testmail_errors) ? array() : $testmail_errors;

        $show_form = wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars));

        $breadcrumbs = !$show_form ? array() : array(
            wordpatch_mailbox_breadcrumb($wpenv_vars),
            array(
                'text' => __wt($wpenv_vars, 'BREADCRUMB_TEST_MAIL')
            )
        );

        wordpatch_render_header($wpenv_vars, array(), $breadcrumbs);
        ?>
        <div class="wordpatch_page">
            <?php if (!wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars))) { ?>
                <?php wordpatch_render_access_denied($wpenv_vars); ?>
            <?php } else { ?>
                <?php wordpatch_render_generic_modal_ex($wpenv_vars, __wte($wpenv_vars, 'TEST_MAIL_HEADING'),
                    __wte($wpenv_vars, 'TEST_MAIL_MESSAGE'), null, $testmail_errors, $where, $error_vars); ?>
            <?php } ?>

            <?php if($show_form) { ?>
                <form name="wordpatch_testmailform" id="wordpatch_testmailform"
                      action="<?php echo(wordpatch_testmail_uri($wpenv_vars)); ?>" method="post">
                    <div class="wordpatch_page_generic_buttons">
                        <a href="<?php echo(wordpatch_mailbox_uri($wpenv_vars)); ?>" class="wordpatch_button wordpatch_button_red">
                            <i class="fa fa-arrow-left" aria-hidden="true"></i>&nbsp;
                            <?php echo __wten($wpenv_vars, 'TEST_MAIL_NO'); ?></a>
                        <button type="submit" name="submit" id="submit" class="wordpatch_button wordpatch_button_blue">
                            <?php echo __wten($wpenv_vars, 'TEST_MAIL_YES'); ?>&nbsp;<i class="fa fa-arrow-right" aria-hidden="true"></i>
                        </button>
                    </div>
                </form>
            <?php } ?>
        </div>
        <?php
        wordpatch_render_footer($wpenv_vars);
    }
}