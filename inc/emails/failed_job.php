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

if(!function_exists('wordpatch_mail_failed_job_subject')) {
    function wordpatch_mail_failed_job_subject($wpenv_vars, $vars) {
        return __wt($wpenv_vars, 'FAILED_JOB_EMAIL_SUBJECT');
    }
}

if(!function_exists('wordpatch_mail_failed_job_html')) {
    function wordpatch_mail_failed_job_html($wpenv_vars, $vars, $subject)
    {
        $log_info = $vars['log_info'];
        $link = sprintf("<a href=\"%s\">%s</a>", wordpatch_logdetail_url($wpenv_vars, $log_info['id']),
            __wt($wpenv_vars, 'FAILED_JOB_EMAIL_MESSAGE2_LINK'));

        ob_start();
        ?>
        <table cellspacing="0" cellpadding="0" width="600" class="font-elem table-elem w320" style="">
            <tr class="font-elem tr-elem" style="">
                <td class="font-elem td-elem header-lg" style="">
                    <?php echo(__wt($wpenv_vars, 'FAILED_JOB_EMAIL_MESSAGE')); ?>
                </td>
            </tr>
            <tr class="font-elem tr-elem" style="">
                <td class="font-elem td-elem free-text" style="">
                    <?php echo(__wt($wpenv_vars, 'FAILED_JOB_EMAIL_MESSAGE2', $link)); ?>
                </td>
            </tr>
            <tr class="font-elem tr-elem" style="">
                <td class="font-elem td-elem free-text" style="">
                    <?php
                    $na = __wt($wpenv_vars, 'NOT_AVAILABLE_ABBR');

                    $display_finish_datetime = $log_info['finish_datetime'] === null ?
                        $na : wordpatch_timestamp_to_readable($log_info['finish_datetime']);

                    $display_running_datetime = $log_info['running_datetime'] === null ?
                        $na : wordpatch_timestamp_to_readable($log_info['running_datetime']);
                    ?>
                    <?php
                        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_TITLE'),
                            $log_info['title']);

                        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_PATH'),
                            $log_info['path']);

                        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_INIT_REASON'),
                            wordpatch_display_init_reason($wpenv_vars, $log_info['init_reason']));

                        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_INIT_SUBJECT_LIST'),
                            wordpatch_display_init_subject_list($wpenv_vars, $log_info['init_subject_list']));

                        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_INIT_USER'),
                            wordpatch_get_display_user($wpenv_vars, $log_info['init_user']));

                        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_DATETIME'),
                            wordpatch_timestamp_to_readable($log_info['datetime']));

                        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_RUNNING_DATETIME'),
                            $display_running_datetime);

                        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_FINISH_DATETIME'),
                            $display_finish_datetime);

                        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_SHOULD_RETRY'),
                            wordpatch_display_should_retry($wpenv_vars, $log_info['should_retry']));

                        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_ERRORS'),
                            wordpatch_display_errors($wpenv_vars, WORDPATCH_WHERE_LOGDETAIL, $log_info['error_list'],
                                $log_info['error_vars']));

                        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_ATTEMPTS'),
                            wordpatch_display_attempts($wpenv_vars, $log_info['retry_count'], $log_info['attempt_count_int']));
                    ?>
                </td>
            </tr>
        </table>
        <?php
        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    }
}

if(!function_exists('wordpatch_mail_failed_job_text')) {
    function wordpatch_mail_failed_job_text($wpenv_vars, $vars, $subject)
    {
        $log_info = $vars['log_info'];
        $logdetail_url = wordpatch_logdetail_url($wpenv_vars, $log_info['id']);

        ob_start();

        echo(__wt($wpenv_vars, 'FAILED_JOB_EMAIL_MESSAGE_TEXT') . "\n");
        echo(__wt($wpenv_vars, 'FAILED_JOB_EMAIL_MESSAGE2_TEXT', $logdetail_url) . "\n\n");

        $na = __wt($wpenv_vars, 'NOT_AVAILABLE_ABBR');

        $display_finish_datetime = $log_info['finish_datetime'] === null ?
            $na : wordpatch_timestamp_to_readable($log_info['finish_datetime']);

        $display_running_datetime = $log_info['running_datetime'] === null ?
            $na : wordpatch_timestamp_to_readable($log_info['running_datetime']);

        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_TITLE'),
            $log_info['title'], true);

        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_PATH'),
            $log_info['path'], true);

        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_INIT_REASON'),
            wordpatch_display_init_reason($wpenv_vars, $log_info['init_reason']), true);

        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_INIT_SUBJECT_LIST'),
            wordpatch_display_init_subject_list($wpenv_vars, $log_info['init_subject_list']), true);

        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_INIT_USER'),
            wordpatch_get_display_user($wpenv_vars, $log_info['init_user']), true);

        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_DATETIME'),
            wordpatch_timestamp_to_readable($log_info['datetime']), true);

        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_RUNNING_DATETIME'),
            $display_running_datetime, true);

        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_FINISH_DATETIME'),
            $display_finish_datetime, true);

        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_SHOULD_RETRY'),
            wordpatch_display_should_retry($wpenv_vars, $log_info['should_retry']), true);

        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_ERRORS'),
            wordpatch_display_errors($wpenv_vars, WORDPATCH_WHERE_LOGDETAIL, $log_info['error_list'],
                $log_info['error_vars']), true);

        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'LOG_DETAIL_HEADING_ATTEMPTS'),
            wordpatch_display_attempts($wpenv_vars, $log_info['retry_count'], $log_info['attempt_count_int']), true);
        ?>
        <?php
        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    }
}