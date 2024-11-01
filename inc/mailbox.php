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
 * Implements the shared mailbox functionality.
 */

if(!function_exists('wordpatch_get_mail_count')) {
    /**
     * Calculate the number of messages for the mailbox.
     *
     * @param $wpenv_vars
     * @param $page_count
     * @return int
     */
    function wordpatch_get_mail_count($wpenv_vars, &$page_count) {
        $logs_table = wordpatch_logs_table($wpenv_vars);
        $mailbox_table = wordpatch_mailbox_table($wpenv_vars);

        $query = "SELECT SUM(`count`) FROM (SELECT COUNT(*) as `count` FROM `$logs_table` WHERE (`running` IS NULL) " .
            "AND (`success` IS NOT NULL) AND (`finish_datetime` IS NOT NULL) UNION SELECT COUNT(*) as `count` FROM " .
            "`$mailbox_table`) `u`";

        $mail_count = max(0, (int)wordpatch_db_get_var($wpenv_vars, $query));
        $page_count = max(0, (int)ceil($mail_count / WORDPATCH_MAILBOX_PAGE_LIMIT));

        return $mail_count;
    }
}

if(!function_exists('wordpatch_mailbox_upgrade_rows')) {
    /**
     * Upgrade mailbox rows with log information if necessary.
     *
     * @param $wpenv_vars
     * @param $mailbox_rows
     */
    function wordpatch_mailbox_upgrade_rows($wpenv_vars, &$mailbox_rows) {
        // This one actually requires a follow up query to make things simple.
        $log_ids = array();
        $log_templates = array(WORDPATCH_MAIL_TEMPLATE_FAILED_JOB, WORDPATCH_MAIL_TEMPLATE_COMPLETED_JOB);

        foreach($mailbox_rows as $mailbox_row) {
            // Make sure this row is actually related to a log
            if(!in_array($mailbox_row['mail_template'], $log_templates)) {
                continue;
            }

            $log_ids[] = $mailbox_row['id'];
        }

        $log_rows = wordpatch_get_logs_by_ids($wpenv_vars, $log_ids);

        foreach($mailbox_rows as &$mailbox_row) {
            $log_row_mailbox = null;

            foreach($log_rows as $log_row) {
                if($log_row['id'] !== $mailbox_row['id']) {
                    continue;
                }

                $log_row_mailbox = $log_row;
                break;
            }

            if($log_row_mailbox === null) {
                continue;
            }

            // Swap in the variables we care about
            $mailbox_row['mail_vars'] = array(
                'log_info' => $log_row_mailbox,
                'changes' => $log_row_mailbox['changes']
            );
        }
    }
}
if(!function_exists('wordpatch_get_mailbox')) {
    /**
     * Grab a specific page of the mailbox from the database.
     *
     * @param $wpenv_vars
     * @param $page
     * @return array
     */
    function wordpatch_get_mailbox($wpenv_vars, $page) {
        $page = max(1, (int)$page);
        $mailbox_table = wordpatch_mailbox_table($wpenv_vars);
        $logs_table = wordpatch_logs_table($wpenv_vars);

        $page_offset = (($page - 1) * WORDPATCH_MAILBOX_PAGE_LIMIT);
        $page_limit = max(1, (int)WORDPATCH_MAILBOX_PAGE_LIMIT);

        $esc_failed_job = wordpatch_esc_sql($wpenv_vars, WORDPATCH_MAIL_TEMPLATE_FAILED_JOB);
        $esc_completed_job = wordpatch_esc_sql($wpenv_vars, WORDPATCH_MAIL_TEMPLATE_COMPLETED_JOB);

        $query = "(SELECT `id` as `id`, IF(`success`=0, '$esc_failed_job', '$esc_completed_job') as `mail_template`, " .
            "NULL as `mail_vars`, `finish_datetime` as `datetime`, NULL as `init_user` FROM `$logs_table` WHERE " .
            "(`running` IS NULL) AND (`success` IS NOT NULL) AND (`finish_datetime` IS NOT NULL)) UNION (SELECT " .
            "`id` as `id`, `mail_template` as `mail_template`, `mail_vars` as `mail_vars`, `datetime` as `datetime`, " .
            "`init_user` as `init_user` FROM `$mailbox_table`) ORDER BY `datetime` DESC LIMIT $page_offset, $page_limit";

        $mailbox_rows = wordpatch_db_get_results($wpenv_vars, $query);

        foreach($mailbox_rows as &$mailbox_row) {
            wordpatch_fix_mailbox_row($mailbox_row);
        }

        wordpatch_mailbox_upgrade_rows($wpenv_vars, $mailbox_rows);

        return $mailbox_rows;
    }
}

if(!function_exists('wordpatch_mailbox_add')) {
    /**
     * Insert a mailbox entry into the database.
     *
     * @param $wpenv_vars
     * @param $mail_id
     * @param $mail_template
     * @param $mail_vars
     * @param $init_user
     * @return bool
     */
    function wordpatch_mailbox_add($wpenv_vars, $mail_id, $mail_template, $mail_vars, $init_user) {
        $mail_id = wordpatch_sanitize_unique_id($mail_id);

        if($mail_id === '') {
            return false;
        }

        $init_user = $init_user === null ? null : max(0, (int)$init_user);
        $init_user = $init_user <= 0 ? null : $init_user;

        $mailbox_table = wordpatch_mailbox_table($wpenv_vars);

        $esc_id = wordpatch_esc_sql($wpenv_vars, $mail_id);
        $esc_template = wordpatch_esc_sql($wpenv_vars, $mail_template);
        $esc_vars = wordpatch_esc_sql($wpenv_vars, json_encode($mail_vars));
        $esc_init_user = wordpatch_esc_sql($wpenv_vars, $init_user);

        $insertquery = "INSERT INTO `$mailbox_table` (`id`, `mail_template`, `mail_vars`, `init_user`, `datetime`) " .
            "VALUES ('$esc_id', '$esc_template', '$esc_vars', '$esc_init_user', UTC_TIMESTAMP())";

        $result = wordpatch_db_query($wpenv_vars, $insertquery);

        if(!$result) {
            return false;
        }

        return true;
    }
}

if(!function_exists('wordpatch_get_mail_by_id')) {
    /**
     * Grab a specific piece of mail by ID.
     *
     * @param $wpenv_vars
     * @param $mail_id
     * @return array
     */
    function wordpatch_get_mail_by_id($wpenv_vars, $mail_id) {
        $mail_id = wordpatch_sanitize_unique_id($mail_id);
        $esc_mail_id = wordpatch_esc_sql($wpenv_vars, $mail_id);

        $mailbox_table = wordpatch_mailbox_table($wpenv_vars);
        $logs_table = wordpatch_logs_table($wpenv_vars);

        $esc_failed_job = wordpatch_esc_sql($wpenv_vars, WORDPATCH_MAIL_TEMPLATE_FAILED_JOB);
        $esc_completed_job = wordpatch_esc_sql($wpenv_vars, WORDPATCH_MAIL_TEMPLATE_COMPLETED_JOB);

        $query = "(SELECT `id` as `id`, IF(`success`=0, '$esc_failed_job', '$esc_completed_job') as `mail_template`, " .
            "NULL as `mail_vars`, `finish_datetime` as `datetime`, NULL as `init_user` FROM `$logs_table` WHERE " .
            "`id` = '$esc_mail_id') UNION (SELECT `id` as `id`, `mail_template` as `mail_template`, " .
            "`mail_vars` as `mail_vars`, `datetime` as `datetime`, `init_user` as `init_user` FROM `$mailbox_table` " .
            "WHERE `id` = '$esc_mail_id')";

        $mailbox_rows = wordpatch_db_get_results($wpenv_vars, $query);

        foreach($mailbox_rows as &$mailbox_row) {
            wordpatch_fix_mailbox_row($mailbox_row);
        }

        if(empty($mailbox_rows)) {
            return null;
        }

        wordpatch_mailbox_upgrade_rows($wpenv_vars, $mailbox_rows);

        return $mailbox_rows[0];
    }
}

if(!function_exists('wordpatch_fix_mailbox_row')) {
    function wordpatch_fix_mailbox_row(&$mailbox_row) {
        $mailbox_row['mail_vars'] = ($mailbox_row['mail_vars'] === null) ? array() :
            ((trim($mailbox_row['mail_vars']) === '') ? array() : wordpatch_json_decode($mailbox_row['mail_vars']));

        $mailbox_row['datetime'] = $mailbox_row['datetime'] === null ? null :
            wordpatch_convert_string_to_timestamp($mailbox_row['datetime']);
    }
}