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
 * Implements the upgrader functionality.
 */

if(!function_exists('wordpatch_upgrader_process_complete')) {
    /**
     * @param mixed $first_arg
     * @param mixed $second_arg
     */
    function wordpatch_upgrader_process_complete($first_arg, $second_arg)
    {
        $wpenv_vars = wordpatch_wordpress_env();

        $meta_arg = (is_array($first_arg) && isset($first_arg['type'])) ? $first_arg : $second_arg;
        $meta_arg = !is_array($meta_arg) ? array() : $meta_arg;

        // possible type values are: 'core', 'plugin', 'theme', 'translation'
        $valid_types = array(
            WORDPATCH_INIT_SUBJECT_TYPE_CORE,
            WORDPATCH_INIT_SUBJECT_TYPE_PLUGIN,
            WORDPATCH_INIT_SUBJECT_TYPE_THEME
        );

        if(!isset($meta_arg['type']) || !in_array($meta_arg['type'], $valid_types)) {
            return;
        }

        $subject_list = array();

        if($meta_arg['type'] === WORDPATCH_INIT_SUBJECT_TYPE_CORE) {
            $subject_list = wordpatch_add_to_upgrade_subject_list($subject_list, WORDPATCH_INIT_SUBJECT_TYPE_CORE,
                WORDPATCH_INIT_SUBJECT_TYPE_CORE);
        }

        if(isset($meta_arg['plugin']) && is_string($meta_arg['plugin'])) {
            $subject_list = wordpatch_add_to_upgrade_subject_list($subject_list, WORDPATCH_INIT_SUBJECT_TYPE_PLUGIN,
                $meta_arg['plugin']);
        }

        if(isset($meta_arg['plugins']) && is_array($meta_arg['plugins']) && !empty($meta_arg['plugins'])) {
            foreach($meta_arg['plugins'] as $plugin_single) {
                $subject_list = wordpatch_add_to_upgrade_subject_list($subject_list, WORDPATCH_INIT_SUBJECT_TYPE_PLUGIN,
                    $plugin_single);
            }
        }

        if(isset($meta_arg['theme']) && is_string($meta_arg['theme'])) {
            $subject_list = wordpatch_add_to_upgrade_subject_list($subject_list, WORDPATCH_INIT_SUBJECT_TYPE_THEME,
                $meta_arg['theme']);
        }

        if(isset($meta_arg['themes']) && is_array($meta_arg['themes']) && !empty($meta_arg['themes'])) {
            foreach($meta_arg['themes'] as $theme_single) {
                $subject_list = wordpatch_add_to_upgrade_subject_list($subject_list, WORDPATCH_INIT_SUBJECT_TYPE_THEME,
                    $theme_single);
            }
        }

        // Notice we use a clever hack below for appending our JSON-encoded subject list.
        // Reversing the subject order allows us to reverse them one more time at the very end before displaying our logs.
        // This is helpful for showing the order of events.
        $subject_list = array_reverse($subject_list);

        $is_cron = wp_doing_cron();
        $is_cron = $is_cron ? true : false;

        $current_user_id = get_current_user_id();
        $credited_user_id = ($is_cron || (!$current_user_id || $current_user_id <= 0)) ?
            0 : (int)$current_user_id;
        $credited_user_id = $credited_user_id <= 0 ? null : $credited_user_id;

        $jobs_table = wordpatch_jobs_table($wpenv_vars);
        $patches_table = wordpatch_patches_table($wpenv_vars);

        // Loop through each job that is enabled and update the schedule entry.
        $selectquery = "SELECT IF(MAX(`p`.`id`) IS NOT NULL,'1','0') as `has_patch`, `j`.* FROM `$jobs_table` `j` " .
            "LEFT JOIN `$patches_table` `p` ON `p`.`job_id` = `j`.`id` WHERE `j`.`enabled` = '1' AND `j`.`deleted` = '0' " .
            "GROUP BY `j`.`id` ORDER BY `j`.`sort_order` ASC";

        $job_rows = wordpatch_db_get_results($wpenv_vars, $selectquery);

        // TODO: Hook this up
        $ping_after = false;

        // Calculate the global setting for update_cooldown
        $update_cooldown = wordpatch_calculate_update_cooldown($wpenv_vars);

        foreach($job_rows as $job_row_single) {
            $job_id = wordpatch_sanitize_unique_id($job_row_single['id']);

            // Skip jobs that do not have any patches.
            if(!$job_row_single['has_patch']) {
                continue;
            }

            // Calculate this job's update_cooldown
            $job_update_cooldown = ($job_row_single['update_cooldown'] === wordpatch_job_update_cooldown_inherit() ||
                !wordpatch_is_valid_job_update_cooldown($job_row_single['update_cooldown'])) ? $update_cooldown : $job_row_single['update_cooldown'];

            $update_cooldown_seconds = max(0, (int)wordpatch_job_update_cooldown_seconds($job_update_cooldown));

            // If it is set to asap then we should add it to pending and ping afterwards.
            if($job_update_cooldown === wordpatch_job_update_cooldown_asap() || !$update_cooldown_seconds) {
                wordpatch_add_pending_job($wpenv_vars, $job_id, WORDPATCH_INIT_REASON_UPDATE, $subject_list,
                    $credited_user_id, null);
                continue;
            }

            // If it is not set to asap, formulate the proper insert schedule query.
            $esc_job_id = wordpatch_esc_sql($wpenv_vars, $job_id);
            $enc_init_user = wordpatch_encode_sql($wpenv_vars, $credited_user_id);
            $esc_subject_list = wordpatch_esc_sql($wpenv_vars, json_encode($subject_list));

            $schedule_table = wordpatch_schedule_table($wpenv_vars);

            $esc_id = wordpatch_esc_sql($wpenv_vars, wordpatch_unique_id());

            $insertquery = "INSERT INTO `$schedule_table` (`id`, `job_id`, `init_user`, `init_subject_list`, `datetime`) " .
                "VALUES ('$esc_id', '$esc_job_id', $enc_init_user, '$esc_subject_list', DATE_ADD(UTC_TIMESTAMP(), INTERVAL $update_cooldown_seconds SECOND)) " .
                "ON DUPLICATE KEY UPDATE `init_subject_list` = IF(LENGTH(`init_subject_list`) <= '2',VALUES(`init_subject_list`), " .
                "IF(LENGTH(VALUES(`init_subject_list`)) <= '2', `init_subject_list`, CONCAT(\"[\", SUBSTR(VALUES(`init_subject_list`), " .
                "'2', LENGTH(VALUES(`init_subject_list`)) - '2'), \",\", SUBSTR(`init_subject_list`, '2', " .
                "LENGTH(`init_subject_list`) - '2'), \"]\"))), `datetime` = IF(`datetime`>VALUES(`datetime`),`datetime`,VALUES(`datetime`)), " .
                "`init_user` = IF(`init_user` IS NULL, VALUES(`init_user`), `init_user`)";

            wordpatch_db_query($wpenv_vars, $insertquery);
        }

        if($ping_after) {
            wordpatch_ping_api_request($wpenv_vars);
        }
    }
}

if(!function_exists('wordpatch_add_to_upgrade_subject_list')) {
    /**
     * Add to the upgrade subject list and ignore duplicates.
     *
     * @param $subject_list
     * @param $subject_type
     * @param $subject_path
     * @return array
     */
    function wordpatch_add_to_upgrade_subject_list($subject_list, $subject_type, $subject_path) {
        $subject_type = strtolower(trim($subject_type));
        $subject_path = wordpatch_sanitize_unix_file_path($subject_path, true, true);

        if(!in_array($subject_type, array('core', 'theme', 'plugin'))) {
            return $subject_list;
        }

        foreach($subject_list as $subject_single) {
            if($subject_single['type'] === $subject_type && $subject_single['path'] === $subject_path) {
                return $subject_list;
            }
        }

        $subject_list[] = array(
            'type' => $subject_type,
            'path' => $subject_path
        );

        return $subject_list;
    }
}