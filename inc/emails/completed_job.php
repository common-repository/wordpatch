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

if(!function_exists('wordpatch_mail_completed_job_subject')) {
    function wordpatch_mail_completed_job_subject($wpenv_vars, $vars) {
        return __wt($wpenv_vars, 'COMPLETED_JOB_EMAIL_SUBJECT');
    }
}

if(!function_exists('wordpatch_mail_completed_job_html')) {
    function wordpatch_mail_completed_job_html($wpenv_vars, $vars, $subject)
    {
        $log_info = $vars['log_info'];
        $changes = $vars['changes'];
        $link = sprintf("<a href=\"%s\">%s</a>", wordpatch_judge_url($wpenv_vars, $log_info['id']),
            __wt($wpenv_vars, 'COMPLETED_JOB_EMAIL_MESSAGE2_LINK'));

        ob_start();
        ?>
        <table cellspacing="0" cellpadding="0" width="600" class="font-elem table-elem w320" style="">
            <tr class="font-elem tr-elem" style="">
                <td class="font-elem td-elem header-lg" style="">
                    <?php echo(__wt($wpenv_vars, 'COMPLETED_JOB_EMAIL_MESSAGE')); ?>
                </td>
            </tr>
            <tr class="font-elem tr-elem" style="">
                <td class="font-elem td-elem free-text" style="">
                    <?php echo(__wt($wpenv_vars, 'COMPLETED_JOB_EMAIL_MESSAGE2', $link)); ?>
                </td>
            </tr>
            <tr class="font-elem tr-elem" style="">
                <td class="font-elem td-elem free-text" style="">
                    <?php
                    $na = __wt($wpenv_vars, 'NOT_AVAILABLE_ABBR');

                    $display_finish_datetime = $log_info['finish_datetime'] === null ?
                        $na : wordpatch_timestamp_to_readable($log_info['finish_datetime']);

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_ID'), $log_info['id']);

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_TITLE'),
                        $log_info['title']);

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_PATH'),
                        $log_info['path']);

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_MODE'),
                        wordpatch_display_mode($wpenv_vars, $log_info['mode']));

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_TIMER'),
                        wordpatch_display_timer($wpenv_vars, $log_info['timer']));

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_BINARY_MODE'),
                        wordpatch_display_job_binary_mode($wpenv_vars, $log_info['binary_mode']));

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_INIT_REASON'),
                        wordpatch_display_init_reason($wpenv_vars, $log_info['init_reason']));

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_INIT_SUBJECT_LIST'),
                        wordpatch_display_init_subject_list($wpenv_vars, $log_info['init_subject_list']));

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_INIT_USER'),
                        wordpatch_get_display_user($wpenv_vars, $log_info['init_user']));

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_DATETIME'),
                        wordpatch_timestamp_to_readable($log_info['datetime']));

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_FINISH_DATETIME'),
                        $display_finish_datetime);

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_CHANGES'),
                        wordpatch_display_changes($wpenv_vars, $changes));

                    wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_ERRORS'),
                        wordpatch_display_errors($wpenv_vars, WORDPATCH_WHERE_JUDGE, $log_info['error_list'],
                            $log_info['error_vars']));
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

if(!function_exists('wordpatch_mail_completed_job_text')) {
    function wordpatch_mail_completed_job_text($wpenv_vars, $vars, $subject)
    {
        $log_info = $vars['log_info'];
        $judge_url = wordpatch_judge_url($wpenv_vars, $log_info['id']);

        ob_start();

        echo(__wt($wpenv_vars, 'COMPLETED_JOB_EMAIL_MESSAGE_TEXT') . "\n");
        echo(__wt($wpenv_vars, 'COMPLETED_JOB_EMAIL_MESSAGE2_TEXT', $judge_url) . "\n\n");

        $na = __wt($wpenv_vars, 'NOT_AVAILABLE_ABBR');

        $display_finish_datetime = $log_info['finish_datetime'] === null ?
            $na : wordpatch_timestamp_to_readable($log_info['finish_datetime']);

        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_ID'), $log_info['id'], true);

        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_TITLE'),
            $log_info['title'], true);

        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_PATH'),
            $log_info['path'], true);

        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_MODE'),
            wordpatch_display_mode($wpenv_vars, $log_info['mode']), true);

        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_TIMER'),
            wordpatch_display_timer($wpenv_vars, $log_info['timer']), true);

        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_BINARY_MODE'),
            wordpatch_display_job_binary_mode($wpenv_vars, $log_info['binary_mode']), true);

        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_INIT_REASON'),
            wordpatch_display_init_reason($wpenv_vars, $log_info['init_reason']), true);

        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_INIT_SUBJECT_LIST'),
            wordpatch_display_init_subject_list($wpenv_vars, $log_info['init_subject_list']), true);

        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_INIT_USER'),
            wordpatch_get_display_user($wpenv_vars, $log_info['init_user']), true);

        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_DATETIME'),
            wordpatch_timestamp_to_readable($log_info['datetime']), true);

        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_FINISH_DATETIME'),
            $display_finish_datetime, true);

        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_CHANGES'),
            wordpatch_display_changes($wpenv_vars, $log_info['changes']), true);

        wordpatch_log_field_render($wpenv_vars, __wt($wpenv_vars, 'JUDGE_HEADING_ERRORS'),
            wordpatch_display_errors($wpenv_vars, WORDPATCH_WHERE_JUDGE, $log_info['error_list'],
                $log_info['error_vars']), true);
        ?>
        <?php
        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    }
}