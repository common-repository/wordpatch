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

include_once(dirname(__FILE__) . '/wordpress/wordpress.php');
include_once(dirname(__FILE__) . '/actions.php');
include_once(dirname(__FILE__) . '/activate.php');
include_once(dirname(__FILE__) . '/analyze.php');
include_once(dirname(__FILE__) . '/binarymode.php');
include_once(dirname(__FILE__) . '/breadcrumbs.php');
include_once(dirname(__FILE__) . '/charset.php');
include_once(dirname(__FILE__) . '/chmod.php');
include_once(dirname(__FILE__) . '/collate.php');
include_once(dirname(__FILE__) . '/constants.php');
include_once(dirname(__FILE__) . '/cooldown.php');
include_once(dirname(__FILE__) . '/database.php');
include_once(dirname(__FILE__) . '/environment.php');
include_once(dirname(__FILE__) . '/errors.php');
include_once(dirname(__FILE__) . '/filesystem.php');
include_once(dirname(__FILE__) . '/footer.php');
include_once(dirname(__FILE__) . '/fsmethod.php');
include_once(dirname(__FILE__) . '/fstimeout.php');
include_once(dirname(__FILE__) . '/ftpssl.php');
include_once(dirname(__FILE__) . '/generate.php');
include_once(dirname(__FILE__) . '/header.php');
include_once(dirname(__FILE__) . '/html.php');
include_once(dirname(__FILE__) . '/http.php');
include_once(dirname(__FILE__) . '/jobenabled.php');
include_once(dirname(__FILE__) . '/jobs.php');
include_once(dirname(__FILE__) . '/jointbyte.php');
include_once(dirname(__FILE__) . '/json.php');
include_once(dirname(__FILE__) . '/judge.php');
include_once(dirname(__FILE__) . '/license.php');
include_once(dirname(__FILE__) . '/logs.php');
include_once(dirname(__FILE__) . '/mailbox.php');
include_once(dirname(__FILE__) . '/mailer.php');
include_once(dirname(__FILE__) . '/maintenance.php');
include_once(dirname(__FILE__) . '/mode.php');
include_once(dirname(__FILE__) . '/octal.php');
include_once(dirname(__FILE__) . '/pagination.php');
include_once(dirname(__FILE__) . '/patches.php');
include_once(dirname(__FILE__) . '/patchtype.php');
include_once(dirname(__FILE__) . '/ping.php');
include_once(dirname(__FILE__) . '/progress.php');
include_once(dirname(__FILE__) . '/quickstart.php');
include_once(dirname(__FILE__) . '/rejects.php');
include_once(dirname(__FILE__) . '/rescue.php');
include_once(dirname(__FILE__) . '/retrycount.php');
include_once(dirname(__FILE__) . '/rollbacks.php');
include_once(dirname(__FILE__) . '/rsa.php');
include_once(dirname(__FILE__) . '/schedule.php');
include_once(dirname(__FILE__) . '/smtpauth.php');
include_once(dirname(__FILE__) . '/smtpssl.php');
include_once(dirname(__FILE__) . '/strings.php');
include_once(dirname(__FILE__) . '/support.php');
include_once(dirname(__FILE__) . '/timer.php');
include_once(dirname(__FILE__) . '/translations.php');
include_once(dirname(__FILE__) . '/unix.php');
include_once(dirname(__FILE__) . '/upgrader.php');
include_once(dirname(__FILE__) . '/uploads.php');
include_once(dirname(__FILE__) . '/users.php');
include_once(dirname(__FILE__) . '/vars.php');
include_once(dirname(__FILE__) . '/widgets.php');
include_once(dirname(__FILE__) . '/wizard.php');
include_once(dirname(__FILE__) . '/updates.php');
include_once(dirname(__FILE__) . '/languages/english.php');
include_once(dirname(__FILE__) . '/emails/completed_job.php');
include_once(dirname(__FILE__) . '/emails/failed_job.php');
include_once(dirname(__FILE__) . '/emails/test.php');
include_once(dirname(__FILE__) . '/emails/layout.php');
include_once(dirname(__FILE__) . '/emails/styles.php');
include_once(dirname(__FILE__) . '/crypto/wordpatch.php');
include_once(dirname(__FILE__) . '/phpseclib/wordpatch.php');
include_once(dirname(__FILE__) . '/actions/configdb.php');
include_once(dirname(__FILE__) . '/actions/configfs.php');
include_once(dirname(__FILE__) . '/actions/configlicense.php');
include_once(dirname(__FILE__) . '/actions/configmail.php');
include_once(dirname(__FILE__) . '/actions/configrescue.php');
include_once(dirname(__FILE__) . '/actions/dashboard.php');
include_once(dirname(__FILE__) . '/actions/deletejob.php');
include_once(dirname(__FILE__) . '/actions/deletepatch.php');
include_once(dirname(__FILE__) . '/actions/editjob.php');
include_once(dirname(__FILE__) . '/actions/editpatch.php');
include_once(dirname(__FILE__) . '/actions/erasepatch.php');
include_once(dirname(__FILE__) . '/actions/health.php');
include_once(dirname(__FILE__) . '/actions/jobdetail.php');
include_once(dirname(__FILE__) . '/actions/jobs.php');
include_once(dirname(__FILE__) . '/actions/judge.php');
include_once(dirname(__FILE__) . '/actions/loadjobfile.php');
include_once(dirname(__FILE__) . '/actions/logdetail.php');
include_once(dirname(__FILE__) . '/actions/login.php');
include_once(dirname(__FILE__) . '/actions/logout.php');
include_once(dirname(__FILE__) . '/actions/logs.php');
include_once(dirname(__FILE__) . '/actions/mailbox.php');
include_once(dirname(__FILE__) . '/actions/newjob.php');
include_once(dirname(__FILE__) . '/actions/newpatch.php');
include_once(dirname(__FILE__) . '/actions/progress.php');
include_once(dirname(__FILE__) . '/actions/readmail.php');
include_once(dirname(__FILE__) . '/actions/redirect.php');
include_once(dirname(__FILE__) . '/actions/restorejob.php');
include_once(dirname(__FILE__) . '/actions/runjob.php');
include_once(dirname(__FILE__) . '/actions/settings.php');
include_once(dirname(__FILE__) . '/actions/testmail.php');
include_once(dirname(__FILE__) . '/actions/trashjob.php');
include_once(dirname(__FILE__) . '/actions/work.php');

if(!function_exists('wordpatch_wpadmin_url')) {
    function wordpatch_wpadmin_url($wpenv_vars) {
        return wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_admin_url'));
    }
}

if(!function_exists('wordpatch_wpadmin_wordpatch_url')) {
    function wordpatch_wpadmin_wordpatch_url($wpenv_vars) {
        return wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_admin_url')) . 'admin.php?page=wordpatch';
    }
}

if(!function_exists('wordpatch_base_url')) {
    function wordpatch_base_url($wpenv_vars) {
        if (wordpatch_env_get($wpenv_vars, 'embed_mode')) {
            return wordpatch_wpadmin_wordpatch_url($wpenv_vars);
        }

        $rescue_path = wordpatch_env_get($wpenv_vars, 'rescue_path');

        if(!$rescue_path) {
            return null;
        }

        return wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_site_url')) . $rescue_path;
    }
}