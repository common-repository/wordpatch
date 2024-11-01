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
 * Implements the breadcrumbs.
 */

if(!function_exists('wordpatch_render_breadcrumbs')) {
    function wordpatch_render_breadcrumbs($wpenv_vars, $breadcrumbs) {
        if(empty($breadcrumbs)) {
            return;
        }

        ?>
        <div id="wordpatch_breadcrumbs_wrapper">
            <div id="wordpatch_breadcrumbs">
                <?php foreach($breadcrumbs as $breadcrumb_idx => $breadcrumb) { ?>
                    <div class="wordpatch_breadcrumb">
                        <?php if($breadcrumb_idx === (count($breadcrumbs) - 1)) { ?>
                            <span><?php echo(htmlspecialchars($breadcrumb['text'])); ?></span>
                        <?php } else { ?>
                            <a href="<?php echo($breadcrumb['link']); ?>"><?php echo(htmlspecialchars($breadcrumb['text'])); ?></a>
                            <i class="fa fa-chevron-right" aria-hidden="true"></i>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php
    }
}

if(!function_exists('wordpatch_jobs_breadcrumb_for_job')) {
    function wordpatch_jobs_breadcrumb_for_job($wpenv_vars, $job) {
        return array(
            'text' => __wt($wpenv_vars, ($job['deleted'] ? 'BREADCRUMB_JOBS_TRASH' : 'BREADCRUMB_JOBS')),
            'link' => wordpatch_jobs_uri($wpenv_vars, $job['deleted'] ? true : false)
        );
    }
}

if(!function_exists('wordpatch_job_breadcrumb')) {
    function wordpatch_job_breadcrumb($wpenv_vars, $job) {
        return array(
            'text' => $job['title'],
            'link' => wordpatch_jobdetail_uri($wpenv_vars, $job['id'])
        );
    }
}

if(!function_exists('wordpatch_mailbox_breadcrumb')) {
    function wordpatch_mailbox_breadcrumb($wpenv_vars) {
        return array(
            'text' => __wt($wpenv_vars, 'BREADCRUMB_MAILBOX'),
            'link' => wordpatch_mailbox_uri($wpenv_vars)
        );
    }
}

if(!function_exists('wordpatch_logs_breadcrumb')) {
    function wordpatch_logs_breadcrumb($wpenv_vars, $job_id) {
        return array(
            'text' => __wt($wpenv_vars, 'BREADCRUMB_LOGS'),
            'link' => wordpatch_logs_uri($wpenv_vars, $job_id)
        );
    }
}

if(!function_exists('wordpatch_log_breadcrumb')) {
    function wordpatch_log_breadcrumb($wpenv_vars, $log_id) {
        return array(
            'text' => __wt($wpenv_vars, 'BREADCRUMB_LOG_DETAIL'),
            'link' => wordpatch_logdetail_uri($wpenv_vars, $log_id)
        );
    }
}

if(!function_exists('wordpatch_jobs_breadcrumb')) {
    function wordpatch_jobs_breadcrumb($wpenv_vars) {
        return array(
            'text' => __wt($wpenv_vars, 'BREADCRUMB_JOBS'),
            'link' => wordpatch_jobs_uri($wpenv_vars, false)
        );
    }
}

if(!function_exists('wordpatch_settings_breadcrumb')) {
    function wordpatch_settings_breadcrumb($wpenv_vars) {
        return array(
            'text' => __wt($wpenv_vars, 'BREADCRUMB_SETTINGS'),
            'link' => wordpatch_settings_uri($wpenv_vars)
        );
    }
}

if(!function_exists('wordpatch_editjob_breadcrumb')) {
    function wordpatch_editjob_breadcrumb($wpenv_vars, $job) {
        return array(
            'text' => __wt($wpenv_vars, 'BREADCRUMB_EDIT_JOB'),
            'link' => wordpatch_editjob_uri($wpenv_vars, $job['id'])
        );
    }
}