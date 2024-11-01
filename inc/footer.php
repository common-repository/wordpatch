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
 * Implements the footer for WordPatch which contains javascript and common UI elements.
 */

if(!function_exists('wordpatch_render_footer')) {
    /**
     * Render the footer for WordPatch.
     *
     * @param $wpenv_vars
     */
    function wordpatch_render_footer($wpenv_vars)
    {
        ?>
        </div>
        <div id="wordpatch_overlays">
            <div id="wordpatch_areyousure_overlay" class="wordpatch_overlay wordpatch_metabox" role="dialog">
                <div class="wordpatch_metabox_header wordpatch_overlay_header">
                    <a href="#" class="wordpatch_link wordpatch_overlay_close" aria-label="Close">
                        <i class="fa fa-window-close" aria-hidden="true"></i>
                    </a>
                    <span class="wordpatch_overlay_title wordpatch_ays_title"><?php echo(__wte($wpenv_vars, 'ARE_YOU_SURE_TITLE')); ?></span>
                    <div class="wordpatch_overlay_push"></div>
                </div>
                <div class="wordpatch_metabox_body">
                    <div class="wordpatch_overlay_icon wordpatch_overlay_icon_ays">
                        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                    </div>
                    <div class="wordpatch_ays_message">
                        <?php echo(__wte($wpenv_vars, 'ARE_YOU_SURE_MESSAGE')); ?>
                    </div>
                    <div class="wordpatch_ays_buttons">
                        <button class="wordpatch_ays_no wordpatch_button wordpatch_button_gray" name="wordpatch_ays_no"><i class="fa fa-times" aria-hidden="true"></i>&nbsp;<?php echo(__wten($wpenv_vars, 'NO')); ?></button>
                        <button class="wordpatch_ays_yes wordpatch_button wordpatch_button_blue" name="wordpatch_ays_yes"><i class="fa fa-check" aria-hidden="true"></i>&nbsp;<?php echo(__wten($wpenv_vars, 'YES')); ?></button>
                    </div>
                </div>
            </div>

            <div id="wordpatch_loading_overlay" class="wordpatch_overlay wordpatch_metabox" role="dialog">
                <div class="wordpatch_metabox_header wordpatch_overlay_header">
                    <span class="wordpatch_overlay_title wordpatch_loading_title"><?php echo(__wte($wpenv_vars, 'LOADING_TITLE')); ?></span>
                </div>
                <div class="wordpatch_metabox_body">
                    <div class="wordpatch_overlay_icon wordpatch_overlay_icon_loading">
                        <i class="fa fa-spinner fa-spin" aria-hidden="true"></i>
                    </div>
                    <div class="wordpatch_loading_message">
                        <?php echo(__wte($wpenv_vars, 'LOADING_MESSAGE')); ?>
                    </div>
                </div>
            </div>

            <div id="wordpatch_errornotice_overlay" class="wordpatch_overlay wordpatch_metabox" role="alertdialog">
                <div class="wordpatch_metabox_header wordpatch_overlay_header">
                    <a href="#" class="wordpatch_link wordpatch_overlay_close" aria-label="Close">
                        <i class="fa fa-window-close" aria-hidden="true"></i>
                    </a>
                    <span class="wordpatch_overlay_title wordpatch_errornotice_title"><?php echo(__wte($wpenv_vars, 'ERROR_TITLE')); ?></span>
                    <div class="wordpatch_overlay_push"></div>
                </div>
                <div class="wordpatch_metabox_body">
                    <div class="wordpatch_overlay_icon wordpatch_overlay_icon_error">
                        <i class="fa fa-times-circle" aria-hidden="true"></i>
                    </div>
                    <div class="wordpatch_errornotice_message"><?php echo(__wte($wpenv_vars, 'ERROR_MESSAGE')); ?></div>
                </div>
                <div class="wordpatch_errornotice_buttons">
                    <button class="wordpatch_errornotice_ok wordpatch_button wordpatch_button_gray" name="wordpatch_errornotice_ok"><i class="fa fa-check" aria-hidden="true"></i>&nbsp;<?php echo(__wten($wpenv_vars, 'OK')); ?></button>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            (function($) {
                $('body').eq(0).append($('#wordpatch_overlays'));

                // These functions return language strings for our JS functions to use.
                window['wordpatchAreYouSure_GetDefaultTitle'] = function() {
                    return <?php echo(json_encode(__wte($wpenv_vars, 'ARE_YOU_SURE_TITLE'))); ?>;
                };

                window['wordpatchAreYouSure_GetDefaultMessage'] = function() {
                    return <?php echo(json_encode(__wte($wpenv_vars, 'ARE_YOU_SURE_MESSAGE'))); ?>;
                };

                window['wordpatchLoading_GetDefaultTitle'] = function() {
                    return <?php echo(json_encode(__wte($wpenv_vars, 'LOADING_TITLE'))); ?>;
                };

                window['wordpatchLoading_GetDefaultMessage'] = function() {
                    return <?php echo(json_encode(__wte($wpenv_vars, 'LOADING_MESSAGE'))); ?>;
                };

                window['wordpatchErrorNotice_GetDefaultTitle'] = function() {
                    return <?php echo(json_encode(__wte($wpenv_vars, 'ERROR_NOTICE_TITLE'))); ?>;
                };

                window['wordpatchErrorNotice_GetDefaultMessage'] = function() {
                    return <?php echo(json_encode(__wte($wpenv_vars, 'ERROR_NOTICE_MESSAGE'))); ?>;
                };

                // Make a function that does nothing
                window['wordpatch_Noop'] = function() {};

                // Escape map for function below.
                const wordpatch__EscapeMap = {
                    '&': '&amp;',
                    '"': '&quot;',
                    '\'': '&#039;',
                    '<': '&lt;',
                    '>': '&gt;'
                };

                // Simple function to emulate htmlspecialchars() in javascript
                window['wordpatch_EscapeHtml'] = function(text)
                {
                    text = text + '';

                    var finalText = '';

                    for (var i = 0; i < text.length; ++i)
                    {
                        if (text[i] in wordpatch__EscapeMap)
                            finalText += wordpatch__EscapeMap[text[i]];
                        else
                            finalText += text[i];
                    }

                    return finalText;
                };

                // Store the overlay state
                window['wordpatch__OverlayState'] = [];

                // Internal function for checking how many overlays are open
                window['wordpatch__ShowingOverlayCount'] = function() {
                    var count = 0;

                    $.each(window['wordpatch__OverlayState'], function() {
                        var state = this;

                        if(state['showing']) {
                            count++;
                        }
                    });

                    return count;
                };

                // Internal function for updating the overlays container
                window['wordpatch__UpdateOverlays'] = function() {
                    if(window['wordpatch__ShowingOverlayCount']() > 0) {
                        $('#wordpatch_overlays').addClass('wordpatch_overlays_showing');
                        return;
                    }

                    $('#wordpatch_overlays').removeClass('wordpatch_overlays_showing');
                };

                // Internal function for adding or updating an overlay state
                window['wordpatch__AddOrUpdateOverlay'] = function(key, showing) {
                    var update = false;

                    $.each(window['wordpatch__OverlayState'], function(arrayIndex, _) {
                        var state = this;

                        if(state['key'] !== key) {
                            return;
                        }

                        window['wordpatch__OverlayState'][arrayIndex]['showing'] = showing;
                        update = true;
                    });

                    if(update) {
                        return;
                    }

                    window['wordpatch__OverlayState'].push({
                        'key': key,
                        'showing': showing
                    });
                };

                var last_focus;
                // Public function to show an overlay
                window['wordpatch_ShowOverlay'] = function(key, element) {
                    last_focus = document.activeElement;
                    window['wordpatch__AddOrUpdateOverlay'](key, true);
                    $(element).show();
                    window['wordpatch__UpdateOverlays']();
                };

                // Public function to hide an overlay
                window['wordpatch_HideOverlay'] = function(key, element) {
                    window['wordpatch__AddOrUpdateOverlay'](key, false);
                    $(element).hide();
                    window['wordpatch__UpdateOverlays']();
                    last_focus.focus();
                };

                // My dedication to my beautiful fiance :D
                window['wordpatchHanny'] = function() {
                    console.log("I love you, Hanny. <3 -Anthony");
                    console.log(" .:::.   .:::.");
                    console.log(":::::::.:::::::");
                    console.log(":::::::::::::::");
                    console.log("':::::::::::::'");
                    console.log("  ':::::::::'");
                    console.log("    ':::::'");
                    console.log("      ':'");
                };

                // My dedications to everyone.
                window['wordpatchDedications'] = function() {
                    console.log("I dedicate this to you for believing in me.");
                    console.log(" ");
                    console.log("Hannah <3");
                    console.log("Phillip Iacono");
                    console.log("Stella Iacono");
                    console.log("Michael Iacono");
                    console.log("Nicholas Iacono");
                    console.log("Vince Iacono");
                    console.log("Aidan McArthur");
                    console.log("Orfeas Zafeiris");
                    console.log("Lucas Kriebel");
                    console.log("Brad Mooberry");
                    console.log("Joshua Simons");
                    console.log("Norman Kolpas");
                    console.log(" ");
                    console.log("Built with love in Virginia, Los Angeles, and Greece (some other places too, I'm sure)");
                };

                window['wordpatchAreYouSure'] = function(title_html, message_html, yes_callback, no_callback) {
                    title_html = typeof title_html === 'undefined' ? window['wordpatchAreYouSure_GetDefaultTitle']() : title_html;
                    message_html = typeof message_html === 'undefined' ? window['wordpatchAreYouSure_GetDefaultMessage']() : message_html;

                    // TODO: @OrfeaZ if you want to make this less .html()-y, go for it.
                    $('#wordpatch_areyousure_overlay .wordpatch_ays_title').html(title_html);
                    $('#wordpatch_areyousure_overlay .wordpatch_ays_message').html(message_html);

                    yes_callback = typeof yes_callback === 'undefined' ? window['wordpatch_Noop'] : yes_callback;
                    no_callback = typeof no_callback === 'undefined' ? window['wordpatch_Noop'] : no_callback;


                    $('#wordpatch_areyousure_overlay .wordpatch_ays_yes').unbind('click').bind('click', function() {
                        window['wordpatch_HideOverlay']('areyousure', $('#wordpatch_areyousure_overlay'));
                        yes_callback();
                        return false;
                    });

                    $('#wordpatch_areyousure_overlay .wordpatch_ays_no').unbind('click').bind('click', function() {
                        window['wordpatch_HideOverlay']('areyousure', $('#wordpatch_areyousure_overlay'));
                        no_callback();
                        return false;
                    });

                    var close_button = $('#wordpatch_areyousure_overlay .wordpatch_overlay_close');
                    close_button.unbind('click').bind('click', function() {
                        window['wordpatch_HideOverlay']('areyousure', $('#wordpatch_areyousure_overlay'));
                        no_callback();
                        return false;
                    });

                    window['wordpatch_ShowOverlay']('areyousure', $('#wordpatch_areyousure_overlay'));
                    close_button.focus();
                };

                window['wordpatchErrorNotice_Show'] = function(title_html, message_html, ok_callback) {
                    title_html = typeof title_html === 'undefined' ? window['wordpatchErrorNotice_GetDefaultTitle']() : title_html;
                    message_html = typeof message_html === 'undefined' ? window['wordpatchErrorNotice_GetDefaultMessage']() : message_html;

                    ok_callback = typeof ok_callback === 'undefined' ? window['wordpatch_Noop'] : ok_callback;

                    $('#wordpatch_errornotice_overlay .wordpatch_errornotice_title').html(title_html);
                    $('#wordpatch_errornotice_overlay .wordpatch_errornotice_message').html(message_html);

                    $('#wordpatch_errornotice_overlay .wordpatch_errornotice_ok').unbind('click').bind('click', function() {
                        window['wordpatch_HideOverlay']('errornotice', $('#wordpatch_errornotice_overlay'));
                        ok_callback();
                        return false;
                    });

                    var close_button = $('#wordpatch_errornotice_overlay .wordpatch_overlay_close');
                    close_button.unbind('click').bind('click', function() {
                        window['wordpatch_HideOverlay']('errornotice', $('#wordpatch_errornotice_overlay'));
                        ok_callback();
                        return false;
                    });

                    window['wordpatch_ShowOverlay']('errornotice', $('#wordpatch_errornotice_overlay'));
                    close_button.focus();
                };

                window['wordpatchLoading_Show'] = function(title_html, message_html) {
                    title_html = typeof title_html === 'undefined' ? window['wordpatchLoading_GetDefaultTitle']() : title_html;
                    message_html = typeof message_html === 'undefined' ? window['wordpatchLoading_GetDefaultMessage']() : message_html;

                    $('#wordpatch_loading_overlay .wordpatch_loading_title').html(title_html);
                    $('#wordpatch_loading_overlay .wordpatch_loading_message').html(message_html);

                    window['wordpatch_ShowOverlay']('loading', $('#wordpatch_loading_overlay'));
                };

                window['wordpatchLoading_Hide'] = function() {
                    window['wordpatch_HideOverlay']('loading', $('#wordpatch_loading_overlay'));
                };

                <?php
                $workUrl = wordpatch_work_url($wpenv_vars);
                ?>
                <?php if($workUrl !== null) { ?>
                window['wordpatchWork_DoRequest'] = function() {
                    $.ajax({
                        url: <?php echo json_encode($workUrl); ?>,
                        method: 'GET',
                        complete: function(xhr) {
                            // Nothing :) Silence is golden.
                        }
                    });
                };
                <?php } ?>

                <?php
                $progressUri = wordpatch_progress_uri($wpenv_vars);
                ?>
                window['wordpatchProgress__OnFailedCheck'] = function(error) {
                    console.log("Oh no, a progress check failed!", error);
                };

                window['wordpatchProgress__OnSuccessfulCheck'] = function(progressModel) {
                    $('.wordpatch_job_ctr').each(function() {
                        var jobContainer = $(this);

                        var jobId = jobContainer.attr('data-job-id');

                        if(typeof jobId === 'undefined' || jobId === null || !jobId || $.trim(jobId) === '') {
                            return;
                        }

                        var jobState = window['wordpatchProgress_DetermineJobState'](progressModel, jobId);

                        jobContainer.find('.wordpatch_job_state').remove();

                        var firstButton = jobContainer.find('.wordpatch_job_button').eq(0);

                        if(jobState === null || jobState['state'] === window['wordpatchProgress_StateIdle']()) {
                            return;
                        }

                        if(jobState['state'] === window['wordpatchProgress_StateRunning']()) {
                            var runningStateHtml = window['wordpatchProgress_StateRunningHtml'](jobState);
                            firstButton.before(runningStateHtml);
                            return;
                        }

                        if(jobState['state'] === window['wordpatchProgress_StateJudgable']()) {
                            var judgableStateHtml = window['wordpatchProgress_StateJudgableHtml'](jobState);
                            firstButton.before(judgableStateHtml);
                            return;
                        }

                        if(jobState['state'] === window['wordpatchProgress_StatePending']()) {
                            var pendingStateHtml = window['wordpatchProgress_StatePendingHtml'](jobState);
                            firstButton.before(pendingStateHtml);
                            return;
                        }

                        if(jobState['state'] === window['wordpatchProgress_StateFailed']()) {
                            var failedStateHtml = window['wordpatchProgress_StateFailedHtml'](jobState);
                            firstButton.before(failedStateHtml);
                            return;
                        }

                        if(jobState['state'] === window['wordpatchProgress_StateSuccess']()) {
                            var successStateHtml = window['wordpatchProgress_StateSuccessHtml'](jobState);
                            firstButton.before(successStateHtml);
                            return;
                        }

                        console.log("Unexpected job state encountered...", jobState);
                    });
                };

                window['wordpatchProgress_StateRunning'] = function() {
                    return <?php echo json_encode(WORDPATCH_PROGRESS_STATE_RUNNING); ?>;
                };

                window['wordpatchProgress_StateJudgable'] = function() {
                    return <?php echo json_encode(WORDPATCH_PROGRESS_STATE_JUDGABLE); ?>;
                };

                window['wordpatchProgress_StatePending'] = function() {
                    return <?php echo json_encode(WORDPATCH_PROGRESS_STATE_PENDING); ?>;
                };

                window['wordpatchProgress_StateFailed'] = function() {
                    return <?php echo json_encode(WORDPATCH_PROGRESS_STATE_FAILED); ?>;
                };

                window['wordpatchProgress_StateSuccess'] = function() {
                    return <?php echo json_encode(WORDPATCH_PROGRESS_STATE_SUCCESS); ?>;
                };

                window['wordpatchProgress_StateIdle'] = function() {
                    return <?php echo json_encode(WORDPATCH_PROGRESS_STATE_IDLE); ?>;
                };

                window['wordpatch_addQueryParams'] = function(existingUri, queryStr) {
                    var queryPos = existingUri.indexOf('?');

                    if(queryPos !== -1) {
                        return existingUri + '&' + queryStr;
                    }

                    return existingUri + '?' + queryStr;
                };

                window['wordpatchUri_Base'] = function() {
                    return <?php echo(json_encode(wordpatch_env_get($wpenv_vars, 'base_uri'))); ?>;
                };

                window['wordpatchUri_LogDetail'] = function(logId) {
                    var baseUri = window['wordpatchUri_Base']();
                    return wordpatch_addQueryParams(baseUri, 'action=' +
                        <?php echo(json_encode(WORDPATCH_WHERE_LOGDETAIL)); ?> +
                            '&log=' + encodeURIComponent(logId));
                };

                window['wordpatchUri_Judge'] = function(logId) {
                    var baseUri = window['wordpatchUri_Base']();
                    return wordpatch_addQueryParams(baseUri, 'action=' +
                        <?php echo(json_encode(WORDPATCH_WHERE_JUDGE)); ?> +
                            '&log=' + encodeURIComponent(logId));
                };

                window['wordpatchProgress_StateRunningHtml'] = function(jobState) {
                    return "<a class=\"wordpatch_button wordpatch_job_button wordpatch_job_state wordpatch_status_green\"\n" +
                        "  href=\"" + window['wordpatchUri_LogDetail'](jobState['log_id']) + "\">" +
                        "  <i class=\"fa fa-circle-o\"></i>\n" +
                        "  <span>" + window['wordpatch_EscapeHtml'](<?php echo(json_encode(__wt($wpenv_vars, 'JOBS_TABLE_RUNNING_LINK'))); ?>) + "</span>\n" +
                        "</a>"
                };

                window['wordpatchProgress_StateJudgableHtml'] = function(jobState) {
                    return "<a class=\"wordpatch_button wordpatch_job_button wordpatch_job_state wordpatch_job_alert\"\n" +
                        "  href=\"" + window['wordpatchUri_Judge'](jobState['log_id']) + "\">" +
                        "  <i class=\"fa fa-warning\"></i>\n" +
                        "  <span>" + window['wordpatch_EscapeHtml'](<?php echo(json_encode(__wt($wpenv_vars, 'JOBS_TABLE_JUDGE_LINK'))); ?>) + "</span>\n" +
                        "</a>"
                };

                window['wordpatchProgress_StatePendingHtml'] = function(jobState) {
                    return "<a class=\"wordpatch_button wordpatch_job_button wordpatch_job_state wordpatch_status_blue\"\n" +
                        "  href=\"" + window['wordpatchUri_LogDetail'](jobState['pending_id']) + "\">" +
                        "  <i class=\"fa fa-clock-o\"></i>\n" +
                        "  <span>" + window['wordpatch_EscapeHtml'](<?php echo(json_encode(__wt($wpenv_vars, 'JOBS_TABLE_PENDING_LINK'))); ?>) + "</span>\n" +
                        "</a>"
                };

                window['wordpatchProgress_StateFailedHtml'] = function(jobState) {
                    return "<a class=\"wordpatch_button wordpatch_job_button wordpatch_job_state wordpatch_status_red\"\n" +
                        "  href=\"" + window['wordpatchUri_LogDetail'](jobState['log_id']) + "\">" +
                        "  <i class=\"fa fa-times-circle\"></i>\n" +
                        "  <span>" + window['wordpatch_EscapeHtml'](<?php echo(json_encode(__wt($wpenv_vars, 'JOBS_TABLE_FAILED'))); ?>) + "</span>\n" +
                        "</a>"
                };

                window['wordpatchProgress_StateSuccessHtml'] = function(jobState) {
                    return "<a class=\"wordpatch_button wordpatch_job_button wordpatch_job_state wordpatch_status_green\"\n" +
                        "  href=\"" + window['wordpatchUri_LogDetail'](jobState['log_id']) + "\">" +
                        "  <i class=\"fa fa-thumbs-up\"></i>\n" +
                        "  <span>" + window['wordpatch_EscapeHtml'](<?php echo(json_encode(__wt($wpenv_vars, 'JOBS_TABLE_SUCCESS'))); ?>) + "</span>\n" +
                        "</a>"
                };

                window['wordpatchProgress_DetermineJobState'] = function(progressModel, jobId) {
                    jobId = $.trim(jobId);

                    // First make sure that the job ID is part of the progress model.
                    if(jobId === '' || progressModel['job_ids'].indexOf(jobId) === -1) {
                        return null;
                    }

                    // Check if the job ID matches the running job ID.
                    if(progressModel['running_job_id'] === jobId) {
                        return {
                            'state': window['wordpatchProgress_StateRunning'](),
                            'job_id': jobId,
                            'log_id': progressModel['running_log_id']
                        };
                    }

                    // The job might be judgable, so check here.
                    var judgableState = null;

                    $.each(progressModel['judgable_job_ids'], function(judgableIdx, judgableJobId) {
                        if(judgableJobId !== jobId) {
                            return;
                        }

                        judgableState = {
                            'state': window['wordpatchProgress_StateJudgable'](),
                            'job_id': jobId,
                            'log_id': progressModel['judgable_log_ids'][judgableIdx]
                        }
                    });

                    if(judgableState !== null) {
                        return judgableState;
                    }

                    // Maybe the job is pending? Loop through the pending jobs to check.
                    var pendingState = null;

                    $.each(progressModel['pending_jobs'], function(_, pendingJob) {
                        if(pendingJob['job_id'] !== jobId) {
                            return;
                        }

                        pendingState = {
                            'state': window['wordpatchProgress_StatePending'](),
                            'job_id': jobId,
                            'pending_id': pendingJob['pending_id']
                        };

                        return false;
                    });

                    if(pendingState !== null) {
                        return pendingState;
                    }

                    // Perhaps the job succeeded?
                    var successState = null;

                    $.each(progressModel['success_job_ids'], function(successIdx, successJobId) {
                        if(successJobId !== jobId) {
                            return;
                        }

                        successState = {
                            'state': window['wordpatchProgress_StateSuccess'](),
                            'job_id': jobId,
                            'log_id': progressModel['success_log_ids'][successIdx]
                        };

                        return false;
                    });

                    if(successState !== null) {
                        return successState;
                    }

                    // Perhaps the job failed?
                    var failedState = null;

                    $.each(progressModel['failed_job_ids'], function(failedIdx, failedJobId) {
                        if(failedJobId !== jobId) {
                            return;
                        }

                        failedState = {
                            'state': window['wordpatchProgress_StateFailed'](),
                            'job_id': jobId,
                            'log_id': progressModel['failed_log_ids'][failedIdx]
                        };

                        return false;
                    });

                    if(failedState !== null) {
                        return failedState;
                    }

                    // Otherwise, the job is idle.
                    return {
                        'state': window['wordpatchProgress_StateIdle'](),
                        'job_id': jobId
                    };
                };

                window['wordpatchProgress_RunCheck'] = function() {
                    $.ajax({
                        url: <?php echo json_encode($progressUri); ?>,
                        method: 'GET',
                        complete: function(xhr) {
                            if(xhr.status >= 400) {
                                window['wordpatchProgress__OnFailedCheck'](<?php echo json_encode(WORDPATCH_UNKNOWN_ERROR); ?>);
                                return;
                            }

                            var responseData = $.parseJSON(xhr.responseText);

                            if(responseData['error']) {
                                window['wordpatchProgress__OnFailedCheck'](responseData['error']);
                            } else {
                                window['wordpatchProgress__OnSuccessfulCheck'](responseData);
                            }
                        }
                    });
                };

                $(document).ready(function() {
                    window['wordpatchProgress_RunCheck']();

                    setInterval(function() {
                        window['wordpatchProgress_RunCheck']();
                    }, <?php echo json_encode(WORDPATCH_CLIENT_PROGRESS_INTERVAL); ?>);
                });

                <?php
                $healthUrl = wordpatch_health_url($wpenv_vars);
                ?>
                <?php if($healthUrl !== null) { ?>
                window['wordpatchHealth__OnFailedCheck'] = function(error) {
                    console.log("Oh no, a health check failed!", error);
                };

                window['wordpatchHealth__OnSuccessfulCheck'] = function() {
                    window['wordpatchWork_DoRequest']();
                };

                window['wordpatchHealth_RunCheck'] = function() {
                    $.ajax({
                        url: <?php echo json_encode($healthUrl); ?>,
                        method: 'GET',
                        complete: function(xhr) {
                            if(xhr.status >= 400) {
                                window['wordpatchHealth__OnFailedCheck'](<?php echo json_encode(WORDPATCH_UNKNOWN_ERROR); ?>);
                                return;
                            }

                            var responseData = $.parseJSON(xhr.responseText);

                            if(responseData['error']) {
                                window['wordpatchHealth__OnFailedCheck'](responseData['error']);
                            } else {
                                window['wordpatchHealth__OnSuccessfulCheck']();
                            }
                        }
                    });
                };

                $(document).ready(function() {
                    window['wordpatchHealth_RunCheck']();

                    setInterval(function() {
                        window['wordpatchHealth_RunCheck']();
                    }, <?php echo json_encode(WORDPATCH_CLIENT_HEALTH_INTERVAL); ?>);
                });
                <?php } ?>

                $(document).ready(function() {
                    $('.wordpatch_admin_bar_hamburger').click(function() {
                        $('body').toggleClass('wordpatch_admin_hamburger_open');

                        return false;
                    });
                });
            })(jQuery);
        </script>
        <?php
        if (wordpatch_env_get($wpenv_vars, 'embed_mode')) {
            return;
        }

        ?>
        </body>
        </html>
        <?php
    }
}