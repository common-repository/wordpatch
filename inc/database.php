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
 * This file implements database related functionality for WordPatch:
 * - Schema creation and database defaults
 * - Functions which return the name of database tables
 *
 */

if(!function_exists('wordpatch_create_schema_rollback')) {
    function wordpatch_create_schema_rollback($wpenv_vars, $charset_collate) {
        // Calculate the name of the rollback table
        $rollback_table = wordpatch_rollback_table($wpenv_vars);

        // Construct our query
        $rollback_sql = "CREATE TABLE {$rollback_table} (\n" .
            "id varchar(32) NOT NULL,\n" .
            "log_id varchar(32) NOT NULL,\n" .
            "path_hash varchar(50) NOT NULL,\n" .
            "action varchar(50) NOT NULL,\n" .
            "path text NOT NULL,\n" .
            "location text NULL,\n" .
            "sort_order int(11) NOT NULL,\n" .
            "PRIMARY KEY  (id),\n" .
            "UNIQUE KEY log_id_path_hash_action (log_id, path_hash, action),\n" .
            "KEY log_id (log_id),\n" .
            "KEY log_id_sort_order (log_id, sort_order)\n" .
            ") $charset_collate;";

        // Call dbDelta() using our query
        dbDelta($rollback_sql);
    }
}

if(!function_exists('wordpatch_create_schema_jobs')) {
    function wordpatch_create_schema_jobs($wpenv_vars, $charset_collate) {
        // Calculate the name of the jobs table
        $jobs_table = wordpatch_jobs_table($wpenv_vars);

        // Construct our query
        $jobs_sql = "CREATE TABLE {$jobs_table} (\n" .
            "id varchar(32) NOT NULL,\n" .
            "title text NOT NULL,\n" .
            "path text NOT NULL,\n" .
            "enabled tinyint(4) NOT NULL,\n" .
            "deleted tinyint(4) NOT NULL,\n" .
            "maintenance_mode varchar(50) NOT NULL,\n" .
            "binary_mode varchar(50) NOT NULL,\n" .
            "retry_count varchar(50) NOT NULL,\n" .
            "mode varchar(50) NOT NULL,\n" .
            "timer varchar(50) NOT NULL,\n" .
            "update_cooldown varchar(50) NOT NULL,\n" .
            "sort_order int(11) NOT NULL,\n" .
            "PRIMARY KEY  (id),\n" .
            "KEY sort_order (sort_order)\n" .
            ") $charset_collate;";

        // Call dbDelta() using our query
        dbDelta($jobs_sql);
    }
}

if(!function_exists('wordpatch_create_schema_patches')) {
    function wordpatch_create_schema_patches($wpenv_vars, $charset_collate) {
        // Calculate the name of the patches table
        $patches_table = wordpatch_patches_table($wpenv_vars);

        // Construct our query
        $patches_sql = "CREATE TABLE {$patches_table} (\n" .
            "id varchar(32) NOT NULL,\n" .
            "job_id varchar(32) NOT NULL,\n" .
            "title text NOT NULL,\n" .
            "sort_order int(11) NOT NULL,\n" .
            "patch_type varchar(50) NOT NULL,\n" .
            "patch_location text NOT NULL,\n" .
            "patch_location_old text NOT NULL,\n" .
            "patch_location_new text NOT NULL,\n" .
            "patch_path text NOT NULL,\n" .
            "patch_size int(11) NOT NULL,\n" .
            "PRIMARY KEY  (id),\n" .
            "KEY job_id_sort_order (job_id, sort_order)\n" .
            ") $charset_collate;";

        // Call dbDelta() using our query
        dbDelta($patches_sql);
    }
}

if(!function_exists('wordpatch_create_schema_mailbox')) {
    function wordpatch_create_schema_mailbox($wpenv_vars, $charset_collate) {
        // Calculate the name of the mailbox table
        $mailbox_table = wordpatch_mailbox_table($wpenv_vars);

        // Construct our query
        $patches_sql = "CREATE TABLE {$mailbox_table} (\n" .
            "id varchar(32) NOT NULL,\n" .
            "mail_template varchar(64) NOT NULL,\n" .
            "mail_vars longtext NOT NULL,\n" .
            "datetime datetime NOT NULL,\n" .
            "init_user int(11) NULL DEFAULT NULL,\n" .
            "PRIMARY KEY  (id),\n" .
            "KEY datetime (datetime)\n" .
            ") $charset_collate;";

        // Call dbDelta() using our query
        dbDelta($patches_sql);
    }
}

if(!function_exists('wordpatch_create_schema_logs')) {
    function wordpatch_create_schema_logs($wpenv_vars, $charset_collate) {
        // Calculate the name of the logs table
        $logs_table = wordpatch_logs_table($wpenv_vars);

        // Construct our query
        $logs_sql = "CREATE TABLE {$logs_table} (\n" .
            "id varchar(32) NOT NULL,\n" .
            "pending_id varchar(32) NOT NULL,\n" .
            "job_id varchar(32) NOT NULL,\n" .
            "datetime datetime NOT NULL,\n" .
            "running_datetime datetime NOT NULL,\n" .
            "finish_datetime datetime NULL DEFAULT NULL,\n" .
            "running tinyint(4) NULL DEFAULT NULL,\n" .
            "init_reason varchar(100) NOT NULL,\n" .
            "init_subject_list longtext NULL,\n" .
            "init_user int(11) NULL DEFAULT NULL,\n" .
            "changes longtext NULL,\n" .
            "judgement_decision varchar(100) NULL DEFAULT NULL,\n" .
            "judgement_datetime datetime NULL DEFAULT NULL,\n" .
            "judgement_user int(11) NULL DEFAULT NULL,\n" .
            "judgement_pending tinyint(4) NULL DEFAULT NULL,\n" .
            "cleanup_status int(11) NOT NULL,\n" .
            "success tinyint(4) NULL DEFAULT NULL,\n" .
            "error_list longtext NULL,\n" .
            "error_vars longtext NULL,\n" .
            "reject_error_list longtext NULL,\n" .
            "reject_error_vars longtext NULL,\n" .
            "cleanup_error_list longtext NULL,\n" .
            "cleanup_error_vars longtext NULL,\n" .
            "maintenance_mode varchar(50) NOT NULL,\n" .
            "binary_mode varchar(50) NOT NULL,\n" .
            "retry_count varchar(50) NOT NULL,\n" .
            "attempt_count_int int(11) NULL DEFAULT NULL,\n" .
            "retry_count_int int(11) NULL DEFAULT NULL,\n" .
            "progress_percent int(11) NULL DEFAULT NULL,\n" .
            "progress_datetime datetime NULL DEFAULT NULL,\n" .
            "progress_note text NULL,\n" .
            "should_retry tinyint(4) NOT NULL,\n" .
            "update_cooldown varchar(50) NOT NULL,\n" .
            "mode varchar(50) NOT NULL,\n" .
            "timer varchar(50) NOT NULL,\n" .
            "timer_int int(11) NOT NULL,\n" .
            "title text NOT NULL,\n" .
            "path text NOT NULL,\n" .
            "PRIMARY KEY  (id),\n" .
            "UNIQUE KEY pending_id (pending_id),\n" .
            "UNIQUE KEY running (running),\n" .
            "KEY job_id (job_id)\n" .
            ") $charset_collate;";

        // Call dbDelta() using our query
        dbDelta($logs_sql);
    }
}

if(!function_exists('wordpatch_create_schema_pending')) {
    function wordpatch_create_schema_pending($wpenv_vars, $charset_collate) {
        // Calculate the name of the pending table
        $pending_table = wordpatch_pending_table($wpenv_vars);

        // Construct our query
        $pending_sql = "CREATE TABLE {$pending_table} (\n" .
            "id varchar(32) NOT NULL,\n" .
            "schedule_id varchar(32) NULL DEFAULT NULL,\n" .
            "job_id varchar(32) NOT NULL,\n" .
            "datetime datetime NOT NULL,\n" .
            "maintenance_mode varchar(50) NOT NULL,\n" .
            "binary_mode varchar(50) NOT NULL,\n" .
            "retry_count varchar(50) NOT NULL,\n" .
            "update_cooldown varchar(50) NOT NULL,\n" .
            "mode varchar(50) NOT NULL,\n" .
            "timer varchar(50) NOT NULL,\n" .
            "timer_int int(11) NOT NULL,\n" .
            "title text NOT NULL,\n" .
            "path text NOT NULL,\n" .
            "init_reason varchar(100) NOT NULL,\n" .
            "init_subject_list longtext NULL,\n" .
            "init_user int(11) NULL DEFAULT NULL,\n" .
            "PRIMARY KEY  (id),\n" .
            "UNIQUE KEY schedule_id (schedule_id),\n" .
            "KEY job_id (job_id)\n" .
            ") $charset_collate;";

        // Call dbDelta() using our query
        dbDelta($pending_sql);
    }
}

if(!function_exists('wordpatch_create_schema_pending_patches')) {
    function wordpatch_create_schema_pending_patches($wpenv_vars, $charset_collate) {
        // Calculate the name of the pending patches table
        $pending_patches_table = wordpatch_pending_patches_table($wpenv_vars);

        // Construct our query
        $pending_patches_sql = "CREATE TABLE {$pending_patches_table} (\n" .
            "id varchar(32) NOT NULL,\n" .
            "pending_id varchar(32) NOT NULL,\n" .
            "job_id varchar(32) NOT NULL,\n" .
            "title text NOT NULL,\n" .
            "patch_type varchar(50) NOT NULL,\n" .
            "patch_location text NOT NULL,\n" .
            "patch_size int(11) NOT NULL,\n" .
            "sort_order int(11) NOT NULL,\n" .
            "PRIMARY KEY  (id),\n" .
            "KEY pending_id (pending_id),\n" .
            "KEY pending_id_sort_order (pending_id, sort_order),\n" .
            "KEY job_id (job_id)\n" .
            ") $charset_collate;";

        // Call dbDelta() using our query
        dbDelta($pending_patches_sql);
    }
}

if(!function_exists('wordpatch_create_schema_schedule')) {
    function wordpatch_create_schema_schedule($wpenv_vars, $charset_collate) {
        // Calculate the name of the schedule table
        $schedule_table = wordpatch_schedule_table($wpenv_vars);

        // Construct our query
        $schedule_sql = "CREATE TABLE {$schedule_table} (\n" .
            "id varchar(32) NOT NULL,\n" .
            "job_id varchar(32) NOT NULL,\n" .
            "init_user int(11) NULL DEFAULT NULL,\n" .
            "init_subject_list longtext NOT NULL,\n" .
            "datetime datetime NOT NULL,\n" .
            "PRIMARY KEY  (id),\n" .
            "UNIQUE KEY job_id (job_id),\n" .
            "KEY datetime (datetime)\n" .
            ") $charset_collate;";

        // Call dbDelta() using our query
        dbDelta($schedule_sql);
    }
}

if(!function_exists('wordpatch_create_schema_rejects')) {
    function wordpatch_create_schema_rejects($wpenv_vars, $charset_collate) {
        // Calculate the name of the rejects table
        $rejects_table = wordpatch_rejects_table($wpenv_vars);

        // Construct our query
        $rejects_sql = "CREATE TABLE {$rejects_table} (\n" .
            "pending_id varchar(32) NOT NULL,\n" .
            "job_id varchar(32) NOT NULL,\n" .
            "datetime datetime NOT NULL,\n" .
            "user_id int(11) NULL DEFAULT NULL,\n" .
            "UNIQUE KEY pending_id (pending_id),\n" .
            "KEY job_id (job_id),\n" .
            "KEY job_id_datetime (job_id, datetime)\n" .
            ") $charset_collate;";

        // Call dbDelta() using our query
        dbDelta($rejects_sql);
    }
}

if(!function_exists('wordpatch_create_schema_rce_identities')) {
    function wordpatch_create_schema_rce_identities($wpenv_vars, $charset_collate) {
        // Calculate the name of the RCE identities table
        $rce_identities_table = wordpatch_rce_identities_table($wpenv_vars);

        // Construct our query
        $rce_identities_sql = "CREATE TABLE {$rce_identities_table} (\n" .
            "identity_id varchar(36) NOT NULL,\n" .
            "identity_alias varchar(20) NULL DEFAULT NULL,\n" .
            "identity_public blob NULL,\n" .
            "identity_private blob NULL,\n" .
            "identity_created datetime NOT NULL,\n" .
            "identity_engine_key varchar(20) NULL DEFAULT NULL,\n" .
            "PRIMARY KEY  (identity_id),\n" .
            "UNIQUE KEY identity_alias (identity_alias),\n" .
            "KEY identity_engine_key (identity_engine_key)\n" .
            ") $charset_collate;";

        // Call dbDelta() using our query
        dbDelta($rce_identities_sql);
    }
}

if(!function_exists('wordpatch_create_schema')) {
    function wordpatch_create_schema($wpenv_vars) {
        /**
         * @var \wpdb $wpdb
         */
        global $wpdb;

        // Use the global $wpdb object to detect the charset collate
        $charset_collate = $wpdb->get_charset_collate();

        // Require 'wp-admin/includes/upgrade.php' so we can use dbDelta()
        // dbDelta() is used to create or modify a table based on altered CREATE TABLE syntax.
        require_once(wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_root_dir')) . 'wp-admin/includes/upgrade.php');

        // Create/modify the rollback table
        wordpatch_create_schema_rollback($wpenv_vars, $charset_collate);

        // Create/modify the mailbox table
        wordpatch_create_schema_mailbox($wpenv_vars, $charset_collate);

        // Create/modify the jobs table
        wordpatch_create_schema_jobs($wpenv_vars, $charset_collate);

        // Create/modify the patches table
        wordpatch_create_schema_patches($wpenv_vars, $charset_collate);

        // Create/modify the logs table
        wordpatch_create_schema_logs($wpenv_vars, $charset_collate);

        // Create/modify the pending table
        wordpatch_create_schema_pending($wpenv_vars, $charset_collate);

        // Create/modify the pending patches table
        wordpatch_create_schema_pending_patches($wpenv_vars, $charset_collate);

        // Create/modify the schedule table
        wordpatch_create_schema_schedule($wpenv_vars, $charset_collate);

        // Create/modify the rejects table
        wordpatch_create_schema_rejects($wpenv_vars, $charset_collate);

        // Create/modify the RCE identities table
        wordpatch_create_schema_rce_identities($wpenv_vars, $charset_collate);
    }
}

if(!function_exists('wordpatch_create_defaults')) {
    function wordpatch_create_defaults($wpenv_vars)
    {
        $language = wordpatch_calculate_language($wpenv_vars);

        if($language === null) {
            wordpatch_update_option($wpenv_vars, 'wordpatch_language', wordpatch_default_language());
        }

        $mode = wordpatch_calculate_mode($wpenv_vars);

        if($mode === null) {
            wordpatch_update_option($wpenv_vars, 'wordpatch_mode', wordpatch_default_mode());
        }

        $timer = wordpatch_calculate_timer($wpenv_vars);

        if($timer === null) {
            wordpatch_update_option($wpenv_vars, 'wordpatch_timer', wordpatch_default_timer());
        }

        $maintenance_mode = wordpatch_calculate_maintenance_mode($wpenv_vars);

        if($maintenance_mode === null) {
            wordpatch_update_option($wpenv_vars, 'wordpatch_maintenance_mode', wordpatch_default_maintenance_mode());
        }

        $retry_count = wordpatch_calculate_retry_count($wpenv_vars);

        if($retry_count === null) {
            wordpatch_update_option($wpenv_vars, 'wordpatch_retry_count', wordpatch_default_retry_count());
        }

        $update_cooldown = wordpatch_calculate_update_cooldown($wpenv_vars);

        if($update_cooldown === null) {
            wordpatch_update_option($wpenv_vars, 'wordpatch_update_cooldown', wordpatch_default_update_cooldown());
        }

        wordpatch_update_option($wpenv_vars, 'wordpatch_version', WORDPATCH_VERSION);
    }
}

/**
 * The following functions return the name of database tables used throughout WordPatch.
 *
 * PS: All table names are pluggable callbacks (can be adjusted by defining elsewhere prior to inclusion of this file).
 * It is highly recommended not to change these unless you are an advanced user.
 */
if(!function_exists('wordpatch_jobs_table')) {
    function wordpatch_jobs_table($wpenv_vars)
    {
        $db_prefix = wordpatch_db_table_prefix($wpenv_vars);
        return $db_prefix . 'wordpatch_jobs';
    }
}

if(!function_exists('wordpatch_rollback_table')) {
    function wordpatch_rollback_table($wpenv_vars)
    {
        $db_prefix = wordpatch_db_table_prefix($wpenv_vars);
        return $db_prefix . 'wordpatch_rollback';
    }
}

if(!function_exists('wordpatch_mailbox_table')) {
    function wordpatch_mailbox_table($wpenv_vars)
    {
        $db_prefix = wordpatch_db_table_prefix($wpenv_vars);
        return $db_prefix . 'wordpatch_mailbox';
    }
}

if(!function_exists('wordpatch_patches_table')) {
    function wordpatch_patches_table($wpenv_vars)
    {
        $db_prefix = wordpatch_db_table_prefix($wpenv_vars);
        return $db_prefix . 'wordpatch_patches';
    }
}

if(!function_exists('wordpatch_rce_identities_table')) {
    function wordpatch_rce_identities_table($wpenv_vars)
    {
        $db_prefix = wordpatch_db_table_prefix($wpenv_vars);
        return $db_prefix . 'wordpatch_rce_identities';
    }
}

if(!function_exists('wordpatch_logs_table')) {
    function wordpatch_logs_table($wpenv_vars)
    {
        $db_prefix = wordpatch_db_table_prefix($wpenv_vars);
        return $db_prefix . 'wordpatch_logs';
    }
}

if(!function_exists('wordpatch_pending_table')) {
    function wordpatch_pending_table($wpenv_vars)
    {
        $db_prefix = wordpatch_db_table_prefix($wpenv_vars);
        return $db_prefix . 'wordpatch_pending';
    }
}

if(!function_exists('wordpatch_rejects_table')) {
    function wordpatch_rejects_table($wpenv_vars)
    {
        $db_prefix = wordpatch_db_table_prefix($wpenv_vars);
        return $db_prefix . 'wordpatch_rejects';
    }
}

if(!function_exists('wordpatch_pending_patches_table')) {
    function wordpatch_pending_patches_table($wpenv_vars)
    {
        $db_prefix = wordpatch_db_table_prefix($wpenv_vars);
        return $db_prefix . 'wordpatch_pending_patches';
    }
}

if(!function_exists('wordpatch_schedule_table')) {
    function wordpatch_schedule_table($wpenv_vars)
    {
        $db_prefix = wordpatch_db_table_prefix($wpenv_vars);
        return $db_prefix . 'wordpatch_schedule';
    }
}

if(!function_exists('wordpatch_test_code')) {
    function wordpatch_test_code() {
        include_once WORDPATCH_PHPSECLIB_DIR . 'Crypt/Wordpatch_Random.php';

        $data = wordpatch_crypt_random_string(4);

        return strtoupper(bin2hex($data));
    }
}


if(!function_exists('wordpatch_unique_id')) {
    function wordpatch_unique_id() {
        include_once WORDPATCH_PHPSECLIB_DIR . 'Crypt/Wordpatch_Random.php';

        $data = wordpatch_crypt_random_string(16);

        return vsprintf('%s%s%s%s%s%s%s%s', str_split(bin2hex($data), 4));
    }
}

if(!function_exists('wordpatch_sanitize_unique_ids')) {
    function wordpatch_sanitize_unique_ids($strings) {
        $output = array();

        foreach($strings as $string) {
            $sanitized = wordpatch_sanitize_unique_id($string);

            if($sanitized === '' || in_array($sanitized, $output)) {
                continue;
            }

            $output[] = $sanitized;
        }

        return $output;
    }
}

if(!function_exists('wordpatch_sanitize_unique_id')) {
    function wordpatch_sanitize_unique_id($string) {
        return strtolower(trim($string));
    }
}

if(!function_exists('wordpatch_calculate_db_host')) {
    function wordpatch_calculate_db_host($wpenv_vars)
    {
        $db_host = wordpatch_get_option($wpenv_vars, 'wordpatch_db_host', '');
        $db_host = trim($db_host);

        if($db_host !== '') {
            return $db_host;
        }

        return null;
    }
}

if(!function_exists('wordpatch_calculate_db_info')) {
    function wordpatch_calculate_db_info($wpenv_vars) {
        $db_info = array();
        $db_info['db_host'] = wordpatch_calculate_db_host($wpenv_vars);
        $db_info['db_user'] = wordpatch_calculate_db_user($wpenv_vars);
        $db_info['db_password'] = wordpatch_calculate_db_password($wpenv_vars);
        $db_info['db_name'] = wordpatch_calculate_db_name($wpenv_vars);
        $db_info['db_table_prefix'] = wordpatch_calculate_db_table_prefix($wpenv_vars);
        $db_info['db_collate'] = wordpatch_calculate_db_collate($wpenv_vars);
        $db_info['db_charset'] = wordpatch_calculate_db_charset($wpenv_vars);
        
        return $db_info;
    }
}

if(!function_exists('wordpatch_calculate_db_configured')) {
    function wordpatch_calculate_db_configured($wpenv_vars)
    {
        $db_val = wordpatch_get_option($wpenv_vars, 'wordpatch_db_configured', WORDPATCH_NO);
        $db_val = trim($db_val);

        if($db_val !== '' && strtolower(trim($db_val)) === WORDPATCH_YES) {
            return WORDPATCH_YES;
        }

        return WORDPATCH_NO;
    }
}

if(!function_exists('wordpatch_calculate_license_key')) {
    function wordpatch_calculate_license_key($wpenv_vars)
    {
        $db_val = wordpatch_get_option($wpenv_vars, 'wordpatch_license_key', '');
        $db_val = trim($db_val);

        if($db_val !== '') {
            return $db_val;
        }

        return null;
    }
}

if(!function_exists('wordpatch_calculate_db_user')) {
    function wordpatch_calculate_db_user($wpenv_vars)
    {
        $db_val = wordpatch_get_option($wpenv_vars, 'wordpatch_db_user', '');
        $db_val = trim($db_val);

        if($db_val !== '') {
            return $db_val;
        }

        return null;
    }
}

if(!function_exists('wordpatch_calculate_db_password')) {
    function wordpatch_calculate_db_password($wpenv_vars)
    {
        $db_val = wordpatch_get_option($wpenv_vars, 'wordpatch_db_password', '');

        if(trim($db_val) !== '') {
            return $db_val;
        }

        return '';
    }
}

if(!function_exists('wordpatch_calculate_db_name')) {
    function wordpatch_calculate_db_name($wpenv_vars)
    {
        $db_name = wordpatch_get_option($wpenv_vars, 'wordpatch_db_name', '');
        $db_name = trim($db_name);

        if($db_name !== '') {
            return $db_name;
        }

        return null;
    }
}

if(!function_exists('wordpatch_calculate_db_table_prefix')) {
    function wordpatch_calculate_db_table_prefix($wpenv_vars)
    {
        $db_val = wordpatch_get_option($wpenv_vars, 'wordpatch_db_table_prefix', '');
        $db_val = trim($db_val);

        if($db_val !== '') {
            return $db_val;
        }

        return 'wp_';
    }
}

if(!function_exists('wordpatch_calculate_db_charset')) {
    function wordpatch_calculate_db_charset($wpenv_vars)
    {
        $db_val = wordpatch_get_option($wpenv_vars, 'wordpatch_db_charset', '');
        $db_val = strtolower(trim($db_val));

        if(wordpatch_is_valid_charset($db_val)) {
            return $db_val;
        }

        return null;
    }
}

if(!function_exists('wordpatch_calculate_db_collate')) {
    function wordpatch_calculate_db_collate($wpenv_vars)
    {
        $db_val = wordpatch_get_option($wpenv_vars, 'wordpatch_db_collate', '');
        $db_val = strtolower(trim($db_val));

        if(wordpatch_is_valid_collate($db_val)) {
            return $db_val;
        }

        return null;
    }
}

if(!function_exists('wordpatch_db_table_prefix')) {
    function wordpatch_db_table_prefix($wpenv_vars)
    {
        $db_connection_string = wordpatch_env_get($wpenv_vars, 'db_connection_string');

        if(isset($db_connection_string['db_table_prefix'])) {
            return $db_connection_string['db_table_prefix'];
        }

        return 'wp_';
    }
}

if(!function_exists('wordpatch_db_test')) {
    function wordpatch_db_test($wpenv_vars, $db_host, $db_user, $db_password, $db_name, $db_table_prefix, $db_collate, $db_charset) {
        $test_results = array(
            'error_list' => array(),
            'db_handle' => null,
        );

        $use_mysqli = wordpatch_env_get($wpenv_vars, 'use_mysqli');
        $env_db_handle = wordpatch_db_handle($wpenv_vars);

        // Let's try to match our current env_db_handle to the settings first to prevent a duplicate connection
        $db_connection_string = wordpatch_env_get($wpenv_vars, 'db_connection_string');

        if($db_connection_string['db_host'] === $db_host &&
            $db_connection_string['db_name'] === $db_name &&
            $db_connection_string['db_user'] === $db_user &&
            $db_connection_string['db_password'] === $db_password &&
            $db_connection_string['db_table_prefix'] === $db_table_prefix &&
            $db_connection_string['db_collate'] === $db_collate &&
            $db_connection_string['db_charset'] === $db_charset) {
            $test_results['db_handle'] = $env_db_handle;
            return $test_results;
        }

        $db_connection_result = wordpatch_db_raw_connect($wpenv_vars, $db_name, $db_host, $db_user, $db_password, $db_charset, $db_collate);

        if(!$db_connection_result['db_connected'] || !$db_connection_result['db_exists'] || !$db_connection_result['db_handle']) {
            $test_results['error_list'][] = WORDPATCH_INVALID_DATABASE_CREDENTIALS;
            return $test_results;
        }

        $db_handle = $db_connection_result['db_handle'];

        // not sure how this could happen, but why not
        if($env_db_handle === $db_handle) {
            $test_results['db_handle'] = $db_handle;
            return $test_results;
        }

        $check_read = false;
        $check_write = false;

        $read_optname = 'wordpatch_readtest_' . uniqid();
        $write_optname = 'wordpatch_writetest_' . uniqid();
        $maybe_options_table = $db_table_prefix . 'options';

        // add to the db for a read check
        $did_insert_read = wordpatch_add_option($wpenv_vars, $read_optname, 'good') ? true : false;
        $did_insert_write = false;

        if($use_mysqli) {
            // perform read check
            $db_result = mysqli_query($db_handle, "SELECT option_value FROM $maybe_options_table WHERE option_name = '$read_optname' LIMIT 1");

            if(mysqli_num_rows($db_result) > 0) {
                $read_row = mysqli_fetch_assoc($db_result);

                if($read_row && strtolower(trim($read_row['option_value'])) === 'good') {
                    $check_read = true;
                }
            }

            if($check_read) {
                // add to the db for a write check
                $insert_result = mysqli_query($db_handle, "INSERT INTO $maybe_options_table (`option_name`, `option_value`) VALUES ('$write_optname', 'good')");

                if ($insert_result && mysqli_affected_rows($db_handle) > 0) {
                    $did_insert_write = true;
                }
            }
        } else {
            // perform read check
            $db_result = mysql_query("SELECT option_value FROM $maybe_options_table WHERE option_name = '$read_optname' LIMIT 1", $db_handle);

            if(mysql_num_rows($db_result) > 0) {
                $read_row = mysql_fetch_assoc($db_result);

                if($read_row && strtolower(trim($read_row['option_value'])) === 'good') {
                    $check_read = true;
                }
            }

            if($check_read) {
                // add to the db for a write check
                $insert_result = mysql_query("INSERT INTO $maybe_options_table (`option_name`, `option_value`) VALUES ('$write_optname', 'good')", $db_handle);

                if ($insert_result && mysql_affected_rows($db_handle) > 0) {
                    $did_insert_write = true;
                }
            }
        }

        if($check_read) {
            // perform write check
            $write_option = wordpatch_get_option($wpenv_vars, $write_optname, '');

            if ($write_option && strtolower(trim($write_option)) === 'good') {
                $check_write = true;
            }
        }

        // If we originally inserted, we should delete to clean up
        if($did_insert_write) {
            if($use_mysqli) {
                mysqli_query($db_handle, "DELETE FROM $maybe_options_table WHERE `option_name` = '$write_optname'");
            } else {
                mysql_query("DELETE FROM $maybe_options_table WHERE `option_name` = '$write_optname'", $db_handle);
            }
        }

        // If we originally inserted, we should delete to clean up
        if($did_insert_read) {
            if($use_mysqli) {
                mysqli_query($env_db_handle, "DELETE FROM $maybe_options_table WHERE `option_name` = '$read_optname'");
            } else {
                mysql_query("DELETE FROM $maybe_options_table WHERE `option_name` = '$read_optname'", $env_db_handle);
            }
        }

        if(!$check_read || !$check_write) {
            $test_results['error_list'][] = WORDPATCH_WRONG_DATABASE_CREDENTIALS;
            return $test_results;
        }

        $test_results['db_handle'] = $db_handle;
        return $test_results;
    }
}

if(!function_exists('wordpatch_db_raw_connect')) {
    function wordpatch_db_raw_connect($wpenv_vars, $db_name, $db_host, $db_user, $db_password, $db_charset, $db_collate, $db_timeout = 15) {
        $use_mysqli = wordpatch_env_get($wpenv_vars, 'use_mysqli');

        $db_handle = null;
        $db_exists = false;
        $db_connected = false;

        $ret = array(
            'db_handle' => null,
            'db_exists' => false,
            'db_connected' => false,
        );

        if ($use_mysqli) {
            $db_handle = mysqli_init();

            $db_handle->set_opt(MYSQLI_OPT_CONNECT_TIMEOUT, $db_timeout);

            // mysqli_real_connect doesn't support the host param including a port or socket
            // like mysql_connect does. This duplicates how mysql_connect detects a port and/or socket file.
            $port = null;
            $socket = null;
            $host = $db_host;
            $port_or_socket = strstr($host, ':');
            if (!empty($port_or_socket)) {
                $host = substr($host, 0, strpos($host, ':'));
                $port_or_socket = substr($port_or_socket, 1);
                if (0 !== strpos($port_or_socket, '/')) {
                    $port = intval($port_or_socket);
                    $maybe_socket = strstr($port_or_socket, ':');
                    if (!empty($maybe_socket)) {
                        $socket = substr($maybe_socket, 1);
                    }
                } else {
                    $socket = $port_or_socket;
                }
            }

            if(@mysqli_real_connect($db_handle, $host, $db_user, $db_password, null, $port, $socket, 0)) {
                $db_connected = true;
            }
        } else {
            $old_timeout = ini_get('mysql.connect_timeout');
            ini_set('mysql.connect_timeout', $db_timeout);
            $db_handle = @mysql_connect($db_host, $db_user, $db_password, true, 0);
            ini_set('mysql.connect_timeout', $old_timeout);

            if($db_handle) {
                $db_connected = true;
            }
        }

        if($db_handle && $db_connected) {
            // version
            $version = wordpatch_db_get_version($db_handle, $use_mysqli);

            // init charset
            $charset_collate = wordpatch_db_determine_charset($version, $db_charset, $db_collate, $db_handle, $use_mysqli);

            $charset = $charset_collate['charset'];
            $collate = $charset_collate['collate'];

            // set charset
            wordpatch_db_set_charset($version, $collate, $charset, $db_handle, $use_mysqli);

            // select the db
            if ($use_mysqli) {
                if(mysqli_select_db($db_handle, $db_name)) {
                    $db_exists = true;
                }
            } else {
                if(mysql_select_db($db_name, $db_handle)) {
                    $db_exists = true;
                }
            }
        }

        $ret['db_handle'] = $db_connected ? $db_handle : null;
        $ret['db_exists'] = $db_exists;
        $ret['db_connected'] = $db_connected;

        return $ret;
    }
}

if(!isset($__wordpatch_db_handle_pool)) {
    $__wordpatch_db_handle_pool = null;
}

if(!function_exists('wordpatch_db_handle__get_from_pool')) {
    function wordpatch_db_handle__get_from_pool($wpenv_vars)
    {
        global $__wordpatch_db_handle_pool;

        if(!$__wordpatch_db_handle_pool || empty($__wordpatch_db_handle_pool)) {
            $__wordpatch_db_handle_pool = array();
        }

        $db_connection_string = wordpatch_env_get($wpenv_vars, 'db_connection_string');

        $use_mysqli = wordpatch_env_get($wpenv_vars, 'use_mysqli');

        $db_name = $db_connection_string['db_name'];
        $db_host = $db_connection_string['db_host'];
        $db_user = $db_connection_string['db_user'];
        $db_password = $db_connection_string['db_password'];
        $db_charset = $db_connection_string['db_charset'];
        $db_collate = $db_connection_string['db_collate'];
        $db_table_prefix = $db_connection_string['db_table_prefix'];

        foreach($__wordpatch_db_handle_pool as $pool_single) {
            if($pool_single['db_name'] === $db_name &&
                $pool_single['db_host'] === $db_host &&
                $pool_single['db_user'] === $db_user &&
                $pool_single['db_password'] === $db_password &&
                $pool_single['db_charset'] === $db_charset &&
                $pool_single['db_collate'] === $db_collate &&
                $pool_single['db_table_prefix'] === $db_table_prefix &&
                $pool_single['db_use_mysqli'] === $use_mysqli) {
                return $pool_single;
            }
        }

        return null;
    }
}

if(!function_exists('wordpatch_db_handle')) {
    function wordpatch_db_handle($wpenv_vars)
    {
        global $__wordpatch_db_handle_pool;

        if(wordpatch_env_get($wpenv_vars, 'db_handle')) {
            return wordpatch_env_get($wpenv_vars, 'db_handle');
        }

        if(!$__wordpatch_db_handle_pool || empty($__wordpatch_db_handle_pool)) {
            $__wordpatch_db_handle_pool = array();
        }

        $db_handle = null;

        $check_pool = wordpatch_db_handle__get_from_pool($wpenv_vars);

        if($check_pool) {
            return $check_pool['db_handle'];
        }

        $db_connection_string = wordpatch_env_get($wpenv_vars, 'db_connection_string');

        $use_mysqli = wordpatch_env_get($wpenv_vars, 'use_mysqli');

        $db_name = $db_connection_string['db_name'];
        $db_host = $db_connection_string['db_host'];
        $db_user = $db_connection_string['db_user'];
        $db_password = $db_connection_string['db_password'];
        $db_charset = $db_connection_string['db_charset'];
        $db_collate = $db_connection_string['db_collate'];
        $db_table_prefix = $db_connection_string['db_table_prefix'];

        if (!$db_handle) {
            $db_result = wordpatch_db_raw_connect($wpenv_vars, $db_name, $db_host, $db_user, $db_password, $db_charset, $db_collate);

            if($db_result['db_connected'] && $db_result['db_exists'] && $db_result['db_handle']) {
                $db_handle = $db_result['db_handle'];
            }
        }

        if($db_handle) {
            $__wordpatch_db_handle_pool[] = array(
                'db_name' => $db_name,
                'db_host' => $db_host,
                'db_user' => $db_user,
                'db_password' => $db_password,
                'db_charset' => $db_charset,
                'db_collate' => $db_collate,
                'db_table_prefix' => $db_table_prefix,
                'db_use_mysqli' => $use_mysqli,
                'db_handle' => $db_handle,
            );
        }

        return $db_handle;
    }
}

if(!function_exists('wordpatch_get_ordered_rows')) {
    function wordpatch_get_ordered_rows($rows, $ids) {
        $ordered_rows = array();

        foreach($ids as $id) {
            foreach($rows as $row) {
                if($row['id'] !== $id) {
                    continue;
                }

                $ordered_rows[] = $row;
                break;
            }
        }

        if(count($ordered_rows) !== count($rows)) {
            return $rows;
        }

        return $ordered_rows;
    }
}

if(!function_exists('wordpatch_get_sql_in')) {
    function wordpatch_get_sql_in($wpenv_vars, $string_values) {
        // Construct a valid IN query string
        $in_str = '';
        $is_first = true;

        foreach($string_values as $string_value_single) {
            if(!$is_first) {
                $in_str .= ',';
            }

            $esc_string_value = wordpatch_esc_sql($wpenv_vars, $string_value_single);

            $in_str .= "'{$esc_string_value}'";
            $is_first = false;
        }

        return $in_str;
    }
}