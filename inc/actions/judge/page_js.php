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
 * Implements the page javascript for the judgement page.
 */

if(!function_exists('wordpatch_judge_draw_page_js')) {
    /**
     * Draw the javascript for the judgement page.
     *
     * @param $wpenv_vars
     * @param $log_id
     */
    function wordpatch_judge_draw_page_js($wpenv_vars, $log_id) {
        ?>
        <script type="text/javascript">
            (function($) {
                <?php
                $default_error = WORDPATCH_UNKNOWN_ERROR;
                $default_error_translation = wordpatch_translate_error($wpenv_vars, $default_error, WORDPATCH_WHERE_JUDGE,
                    array());
                ?>
                $('.wordpatch_button_judge').click(function() {
                    var judgeLink = $(this);
                    var isAccept = judgeLink.hasClass('wordpatch_button_judge_accept');
                    var judgementDecision = isAccept ? <?php echo(json_encode(WORDPATCH_JUDGEMENT_ACCEPTED)); ?> :
                        <?php echo(json_encode(WORDPATCH_JUDGEMENT_REJECTED)); ?>;

                    if(judgeLink.hasClass('wordpatch_button_judge_busy')) {
                        return false;
                    }

                    var reenableJudgeLinks = function() {
                        $('.wordpatch_button_judge').removeClass('wordpatch_button_judge_busy');
                    };

                    var judgeCallback = function() {
                        $('.wordpatch_button_judge').addClass('wordpatch_button_judge_busy');

                        window['wordpatchLoading_Show'](window['wordpatchLoading_GetDefaultTitle'](),
                            <?php echo(json_encode(__wt($wpenv_vars, 'JUDGE_LOADING'))); ?>);

                        $.ajax({
                            url: <?php echo json_encode(wordpatch_judge_uri($wpenv_vars, $log_id)); ?>,
                            method: 'POST',
                            data: {
                                'submit': '1',
                                'judgement_decision': judgementDecision
                            },
                            complete: function(xhr) {
                                // Remove the loading dialog once the request has completed
                                window['wordpatchLoading_Hide']();

                                var default_error_translation = <?php echo json_encode($default_error_translation); ?>;

                                if(!xhr || xhr.status >= 400 || $.trim(xhr.responseText) === '') {
                                    window['wordpatchErrorNotice_Show'](window['wordpatchErrorNotice_GetDefaultTitle'](),
                                        default_error_translation, reenableJudgeLinks);
                                    return;
                                }

                                var responseData = $.parseJSON(xhr.responseText);

                                if(responseData['error']) {
                                    window['wordpatchErrorNotice_Show'](window['wordpatchErrorNotice_GetDefaultTitle'](),
                                        responseData['error_translation']);
                                    return;
                                }

                                document.location = <?php echo(json_encode(wordpatch_jobs_uri($wpenv_vars))); ?>;
                            }
                        });
                    };

                    <?php
                    $judgeMessageAccept = __wt($wpenv_vars, 'JUDGE_ACCEPT_ARE_YOU_SURE');
                    $judgeMessageReject = __wt($wpenv_vars, 'JUDGE_REJECT_ARE_YOU_SURE');
                    ?>
                    var judgeMessage = isAccept ? <?php echo(json_encode($judgeMessageAccept)); ?> :
                        <?php echo(json_encode($judgeMessageReject)); ?>;

                    window['wordpatchAreYouSure'](wordpatchAreYouSure_GetDefaultTitle(),
                        judgeMessage, judgeCallback);

                    return false;
                });
            })(jQuery);
        </script>
        <?php
    }
}