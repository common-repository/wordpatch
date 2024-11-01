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
 * This file is not allowed to depend on WordPress.
 * Made with love (and a little bit of insanity) in Virginia. ♥
 * Feel free to use parts of this file to emulate a modern WordPress installation.
 * MU (multisite) is not yet supported and various notes have been made for future support.
 */

if(!function_exists('wordpatch_db_get_version')) {
    function wordpatch_db_get_version($db_handle, $use_mysqli)
    {
        if ($use_mysqli) {
            $server_info = mysqli_get_server_info($db_handle);
        } else {
            $server_info = mysql_get_server_info($db_handle);
        }

        return preg_replace('/[^0-9.].*/', '', $server_info);
    }
}

if(!function_exists('wordpatch_db_determine_charset')) {
    function wordpatch_db_determine_charset($version, $charset, $collate, $db_handle, $use_mysqli)
    {
        if (($use_mysqli && !($db_handle instanceof mysqli)) || empty($db_handle)) {
            return compact('charset', 'collate');
        }

        if ('utf8' === $charset && wordpatch_db_has_cap($version, 'utf8mb4', $use_mysqli)) {
            $charset = 'utf8mb4';
        }

        if ('utf8mb4' === $charset && !wordpatch_db_has_cap($version, 'utf8mb4', $use_mysqli)) {
            $charset = 'utf8';
            $collate = str_replace('utf8mb4_', 'utf8_', $collate);
        }

        if ('utf8mb4' === $charset) {
            // _general_ is outdated, so we can upgrade it to _unicode_, instead.
            if (!$collate || 'utf8_general_ci' === $collate) {
                $collate = 'utf8mb4_unicode_ci';
            } else {
                $collate = str_replace('utf8_', 'utf8mb4_', $collate);
            }
        }

        // _unicode_520_ is a better collation, we should use that when it's available.
        if (wordpatch_db_has_cap($version, 'utf8mb4_520', $use_mysqli) && 'utf8mb4_unicode_ci' === $collate) {
            $collate = 'utf8mb4_unicode_520_ci';
        }

        return compact('charset', 'collate');
    }
}

if(!function_exists('wordpatch_db_has_cap')) {
    function wordpatch_db_has_cap($version, $db_cap, $use_mysqli)
    {
        switch (strtolower($db_cap)) {
            case 'collation':    // @since 2.5.0
            case 'group_concat': // @since 2.7.0
            case 'subqueries':   // @since 2.7.0
                return version_compare($version, '4.1', '>=');
            case 'set_charset':
                return version_compare($version, '5.0.7', '>=');
            case 'utf8mb4':      // @since 4.1.0
                if (version_compare($version, '5.5.3', '<')) {
                    return false;
                }
                if ($use_mysqli) {
                    $client_version = mysqli_get_client_info();
                } else {
                    $client_version = mysql_get_client_info();
                }

                /*
                 * libmysql has supported utf8mb4 since 5.5.3, same as the MySQL server.
                 * mysqlnd has supported utf8mb4 since 5.0.9.
                 */
                if (false !== strpos($client_version, 'mysqlnd')) {
                    $client_version = preg_replace('/^\D+([\d.]+).*/', '$1', $client_version);
                    return version_compare($client_version, '5.0.9', '>=');
                } else {
                    return version_compare($client_version, '5.5.3', '>=');
                }
            case 'utf8mb4_520': // @since 4.6.0
                return version_compare($version, '5.6', '>=');
        }

        return false;
    }
}

if(!function_exists('wordpatch_db_set_charset')) {
    function wordpatch_db_set_charset($version, $collate, $charset, $db_handle, $use_mysqli)
    {
        if (wordpatch_db_has_cap($version, 'collation', $use_mysqli) && !empty($charset)) {
            $set_charset_succeeded = true;

            if ($use_mysqli) {
                if (function_exists('mysqli_set_charset') && wordpatch_db_has_cap($version, 'set_charset', $use_mysqli)) {
                    $set_charset_succeeded = mysqli_set_charset($db_handle, $charset);
                }

                if ($set_charset_succeeded) {
                    $charset_esc = mysqli_real_escape_string($db_handle, $charset);
                    $query = "SET NAMES '" . $charset_esc . "'";

                    if (!empty($collate)) {
                        $collate_esc = mysqli_real_escape_string($db_handle, $collate);
                        $query .= " COLLATE '" . $collate_esc . "'";
                    }

                    mysqli_query($db_handle, $query);
                }
            } else {
                if (function_exists('mysql_set_charset') && wordpatch_db_has_cap($version, 'set_charset', $use_mysqli)) {
                    $set_charset_succeeded = mysql_set_charset($charset, $db_handle);
                }

                if ($set_charset_succeeded) {
                    $charset_esc = mysql_real_escape_string($charset, $db_handle);
                    $query = "SET NAMES '" . $charset_esc . "'";

                    if (!empty($collate)) {
                        $collate_esc = mysql_real_escape_string($collate, $db_handle);
                        $query .= " COLLATE '" . $collate_esc . "'";
                    }

                    mysql_query($query, $db_handle);
                }
            }
        }
    }
}

if(!function_exists('wordpatch_db_get_row')) {
    function wordpatch_db_get_row($wpenv_vars,  $query, $assoc = true)
    {
        $db_handle = wordpatch_db_handle($wpenv_vars);
        $use_mysqli = wordpatch_env_get($wpenv_vars, 'use_mysqli');

        if(!$db_handle) {
            return null;
        }

        if($use_mysqli) {
            $query_result = mysqli_query($db_handle, $query);
        } else {
            $query_result = mysql_query($query, $db_handle);
        }

        $row = $use_mysqli ?
            ($assoc ? mysqli_fetch_assoc($query_result) : mysqli_fetch_object($query_result)) :
            ($assoc ? mysql_fetch_assoc($query_result) : mysql_fetch_object($query_result));

        if($row) {
            return $row;
        }

        return null;
    }
}

if(!function_exists('wordpatch_db_query')) {
    function wordpatch_db_query($wpenv_vars, $query)
    {
        $db_handle = wordpatch_db_handle($wpenv_vars);
        $use_mysqli = wordpatch_env_get($wpenv_vars, 'use_mysqli');

        if(!$db_handle) {
            return null;
        }

        if($use_mysqli) {
            $query_result = mysqli_query($db_handle, $query);
        } else {
            $query_result = mysql_query($query, $db_handle);
        }

        return $query_result;
    }
}

if(!function_exists('wordpatch_db_num_rows')) {
    function wordpatch_db_num_rows($wpenv_vars, $query_result)
    {
        $use_mysqli = wordpatch_env_get($wpenv_vars, 'use_mysqli');

        if($use_mysqli) {
            return mysqli_num_rows($query_result);
        } else {
            return mysql_num_rows($query_result);
        }
    }
}

if(!function_exists('wordpatch_db_affected_rows')) {
    function wordpatch_db_affected_rows($wpenv_vars)
    {
        $db_handle = wordpatch_db_handle($wpenv_vars);
        $use_mysqli = wordpatch_env_get($wpenv_vars, 'use_mysqli');

        if($use_mysqli) {
            return mysqli_affected_rows($db_handle);
        } else {
            return mysql_affected_rows($db_handle);
        }
    }
}

if(!function_exists('wordpatch_db_get_var')) {
    function wordpatch_db_get_var($wpenv_vars, $query, $x = 0, $y = 0) {
        $query_result = wordpatch_db_query($wpenv_vars, $query);
        $use_mysqli = wordpatch_env_get($wpenv_vars, 'use_mysqli');

        $row_cols = array();
        $row = $use_mysqli ? mysqli_fetch_assoc($query_result) : mysql_fetch_assoc($query_result);

        while($row !== null && $row !== false) {
            $row_keys = array_keys($row);

            if($x < count($row_keys)) {
                $row_cols[] = $row[$row_keys[$x]];
            } else {
                $row_cols[] = '';
            }

            $row = $use_mysqli ? mysqli_fetch_assoc($query_result) : mysql_fetch_assoc($query_result);
        }

        // If there is a value return it else return null
        return (isset($row_cols[$y]) && $row_cols[$y] !== '') ? $row_cols[$y] : null;
    }
}

if(!function_exists('wordpatch_db_get_col')) {
    function wordpatch_db_get_col($wpenv_vars, $query, $col = 0) {
        $use_mysqli = wordpatch_env_get($wpenv_vars, 'use_mysqli');
        $query_result = wordpatch_db_query($wpenv_vars, $query);

        $results = array();
        $row = $use_mysqli ? mysqli_fetch_assoc($query_result) : mysql_fetch_assoc($query_result);

        while($row !== null && $row !== false) {
            $row_keys = array_keys($row);

            if($col < count($row_keys)) {
                $results[] = $row[$row_keys[$col]];
            }

            $row = $use_mysqli ? mysqli_fetch_assoc($query_result) : mysql_fetch_assoc($query_result);
        }

        return $results;
    }
}

if(!function_exists('wordpatch_db_get_results')) {
    function wordpatch_db_get_results($wpenv_vars, $query, $assoc = true)
    {
        $use_mysqli = wordpatch_env_get($wpenv_vars, 'use_mysqli');
        $query_result = wordpatch_db_query($wpenv_vars, $query);

        $results = array();
        $row = $use_mysqli ?
            ($assoc ? mysqli_fetch_assoc($query_result) : mysqli_fetch_object($query_result)) :
            ($assoc ? mysql_fetch_assoc($query_result) : mysql_fetch_object($query_result));

        while($row !== null && $row !== false) {
            $results[] = $row;

            $row = $use_mysqli ?
                ($assoc ? mysqli_fetch_assoc($query_result) : mysqli_fetch_object($query_result)) :
                ($assoc ? mysql_fetch_assoc($query_result) : mysql_fetch_object($query_result));
        }

        return $results;
    }
}

if(!function_exists('wordpatch_get_option')) {
    function wordpatch_get_option($wpenv_vars,  $option, $default = false)
    {
        $option = trim($option);
        if(empty($option)) {
            return false;
        }

        // Distinguish between `false` as a default, and not passing one.
        $passed_default = func_num_args() > 1;

        $value = wordpatch_cache_get($option, 'options');

        if(false === $value) {
            $esc_option = wordpatch_esc_sql($wpenv_vars,  $option);
            $table = wordpatch_options_table($wpenv_vars);

            $query = "SELECT option_value FROM $table WHERE option_name = '$esc_option' LIMIT 1";
            $row = wordpatch_db_get_row($wpenv_vars,  $query);

            if($row) {
                $value = $row['option_value'];
            } else {
                return $default;
            }
        }

        if('home' == $option && '' == $value) {
            return wordpatch_get_option($wpenv_vars,  'siteurl');
        }

        if(in_array($option, array('siteurl', 'home', 'category_base', 'tag_base'))) {
            $value = wordpatch_untrailingslashit($value);
        }

        return wordpatch_maybe_unserialize($value);
    }
}

if(!function_exists('wordpatch_sanitize_option')) {
    function wordpatch_sanitize_option($option, $value) {
        return $value;
    }
}

if(!function_exists('wordpatch_add_option')) {
    /**
     * @param $wpenv_vars
     * @param $option
     * @param mixed $value
     * @param string $deprecated
     * @param string $autoload
     * @return bool
     */
    function wordpatch_add_option($wpenv_vars, $option, $value = '', $deprecated = '', $autoload = 'yes')
    {
        $option = trim($option);

        if(empty($option)) {
            return false;
        }

        if(is_object($value)) {
            $value = clone $value;
        }

        $value = wordpatch_sanitize_option($option, $value);

        $serialized_value = wordpatch_maybe_serialize($value);
        $autoload = ('no' === $autoload || false === $autoload) ? 'no' : 'yes';

        $table = wordpatch_options_table($wpenv_vars);

        $esc_option = wordpatch_esc_sql($wpenv_vars, $option);
        $esc_serialized_value = wordpatch_esc_sql($wpenv_vars, $serialized_value);
        $esc_autoload = wordpatch_esc_sql($wpenv_vars, $autoload);

        $query = "INSERT INTO $table (option_name, option_value, autoload) VALUES ('$esc_option', '$esc_serialized_value', '$esc_autoload') ON DUPLICATE KEY UPDATE option_name = VALUES(option_name), option_value = VALUES(option_value), autoload = VALUES(autoload)";
        wordpatch_db_query($wpenv_vars, $query);
        $affected_rows = wordpatch_db_affected_rows($wpenv_vars);

        if(!$affected_rows) {
            return false;
        }

        return true;
    }
}

if(!function_exists('wordpatch_update_option')) {
    function wordpatch_update_option($wpenv_vars, $option, $value, $autoload = null) {
        $option = trim($option);
        if(empty($option)) {
            return false;
        }

        if(is_object($value)) {
            $value = clone $value;
        }

        $value = wordpatch_sanitize_option($option, $value);
        $old_value = wordpatch_get_option($wpenv_vars, $option);

        if($value === $old_value || wordpatch_maybe_serialize($value) === wordpatch_maybe_serialize($old_value)) {
            return false;
        }

        if(false === $old_value) {
            if(null === $autoload) {
                $autoload = 'yes';
            }

            return wordpatch_add_option($wpenv_vars, $option, $value, '', $autoload);
        }

        $serialized_value = wordpatch_maybe_serialize($value);

        $update_args = array(
            'option_value' => $serialized_value,
        );

        if (null !== $autoload ) {
            $update_args['autoload'] = ('no' === $autoload || false === $autoload) ? 'no' : 'yes';
        }

        $table = wordpatch_options_table($wpenv_vars);

        $esc_serialized_value = wordpatch_esc_sql($wpenv_vars, $serialized_value);
        $query = "UPDATE $table SET option_value = '$esc_serialized_value'";
        if (null !== $autoload ) {
            $esc_autoload = wordpatch_esc_sql($wpenv_vars, ('no' === $autoload || false === $autoload) ? 'no' : 'yes');
            $query .= ", autoload = '$esc_autoload'";
        }
        $esc_option = wordpatch_esc_sql($wpenv_vars, $option);
        $query .= " WHERE option_name = '$esc_option'";
        wordpatch_db_query($wpenv_vars, $query);
        $affected = wordpatch_db_affected_rows($wpenv_vars);

        if(!$affected) {
            return false;
        }

        return true;
    }

}

if(!function_exists('wordpatch__get_meta_table')) {
    function wordpatch__get_meta_table($wpenv_vars, $type) {
        $table_prefix = wordpatch_db_table_prefix($wpenv_vars);

        if($type === 'post') {
            return $table_prefix . 'postmeta';
        }

        if($type === 'user') {
            return $table_prefix . 'usermeta';
        }

        if($type === 'term') {
            return $table_prefix . 'termmeta';
        }

        return false;
    }
}

if(!function_exists('wordpatch_users_table')) {
    function wordpatch_users_table($wpenv_vars) {
        $db_prefix = wordpatch_db_table_prefix($wpenv_vars);
        return $db_prefix . 'users';
    }
}

if(!function_exists('wordpatch_options_table')) {
    function wordpatch_options_table($wpenv_vars) {
        $db_prefix = wordpatch_db_table_prefix($wpenv_vars);
        return $db_prefix . 'options';
    }
}

if(!function_exists('wordpatch_esc_sql')) {
    function wordpatch_esc_sql($wpenv_vars, $data) {
        $db_handle = wordpatch_db_handle($wpenv_vars);
        $use_mysqli = wordpatch_env_get($wpenv_vars, 'use_mysqli');

        if($use_mysqli) {
            return mysqli_real_escape_string($db_handle, $data);
        } else {
            return mysql_real_escape_string($data, $db_handle);
        }
    }
}

if(!function_exists('wordpatch_encode_sql')) {
    function wordpatch_encode_sql($wpenv_vars, $data, $binary_output = false) {
        if($data === null) {
            return 'null';
        }

        if($binary_output) {
            return "0x" . bin2hex($data);
        }

        return "'" . wordpatch_esc_sql($wpenv_vars,  $data) . "'";
    }
}

if(!function_exists('wordpatch_db_insert_id')) {
    function wordpatch_db_insert_id($wpenv_vars)
    {
        $db_handle = wordpatch_db_handle($wpenv_vars);
        $use_mysqli = wordpatch_env_get($wpenv_vars, 'use_mysqli');

        if($use_mysqli) {
            return mysqli_insert_id($db_handle);
        } else {
            return mysql_insert_id($db_handle);
        }
    }
}

if(!function_exists('wordpatch_add_metadata')) {
    function wordpatch_add_metadata($wpenv_vars, $meta_type, $object_id, $meta_key, $meta_value, $unique = false) {
        if(!$meta_type || !$meta_key || !is_numeric($object_id)) {
            return false;
        }

        $object_id = wordpatch_absint($object_id);
        if(!$object_id) {
            return false;
        }

        $table = wordpatch__get_meta_table($wpenv_vars, $meta_type);

        if(!$table) {
            return false;
        }

        $column = wordpatch_sanitize_key($meta_type . '_id');

        // expected_slashed ($meta_key)
        $meta_key = wordpatch_unslash($meta_key);
        $meta_value = wordpatch_unslash($meta_value);
        $meta_value = wordpatch_sanitize_meta($meta_key, $meta_value, $meta_type);

        $esc_meta_key = wordpatch_esc_sql($wpenv_vars, $meta_key);

        $query = "SELECT COUNT(*) FROM $table WHERE meta_key = '$esc_meta_key' AND $column = {$object_id}";


        if($unique && (int)wordpatch_db_get_var($wpenv_vars, $query)) {
            return false;
        }

        $_meta_value = $meta_value;
        $meta_value = wordpatch_maybe_serialize($meta_value);

        $esc_meta_value = wordpatch_esc_sql($wpenv_vars, $meta_value);

        $insert_query = "INSERT INTO $table ($column, meta_key, meta_value) VALUES ($object_id, '$esc_meta_key', '$esc_meta_value')";
        $result = wordpatch_db_query($wpenv_vars, $insert_query);

        if(!$result) {
            return false;
        }

        $mid = (int)wordpatch_db_insert_id($wpenv_vars);

        wordpatch_cache_delete($object_id, $meta_type . '_meta');

        return $mid;
    }
}

if(!function_exists('wordpatch_update_metadata')) {
    function wordpatch_update_metadata($wpenv_vars, $meta_type, $object_id, $meta_key, $meta_value, $prev_value = '') {
        if(!$meta_type || !$meta_key || !is_numeric($object_id)) {
            return false;
        }

        $object_id = wordpatch_absint($object_id);

        if(!$object_id) {
            return false;
        }

        $table = wordpatch__get_meta_table($wpenv_vars, $meta_type);

        if(!$table) {
            return false;
        }

        $column = wordpatch_sanitize_key($meta_type . '_id');
        $id_column = 'user' == $meta_type ? 'umeta_id' : 'meta_id';

        // expected_slashed ($meta_key)
        $raw_meta_key = $meta_key;
        $meta_key = wordpatch_unslash($meta_key);
        $passed_value = $meta_value;
        $meta_value = wordpatch_unslash($meta_value);
        $meta_value = wordpatch_sanitize_meta($meta_key, $meta_value, $meta_type);

        if(empty($prev_value)) {
            $old_value = wordpatch_get_metadata($wpenv_vars, $meta_type, $object_id, $meta_key);
            if(count($old_value) == 1) {
                if($old_value[0] === $meta_value) {
                    return false;
                }
            }
        }

        $esc_meta_key = wordpatch_esc_sql($wpenv_vars, $meta_key);
        $query = "SELECT $id_column FROM $table WHERE meta_key = '$esc_meta_key' AND $column = '$object_id'";
        $meta_ids = wordpatch_db_get_col($wpenv_vars, $query);
        if(empty($meta_ids)) {
            return wordpatch_add_metadata($wpenv_vars, $meta_type, $object_id, $raw_meta_key, $passed_value);
        }

        $_meta_value = $meta_value;
        $meta_value = wordpatch_maybe_serialize($meta_value);

        if(!empty($prev_value)) {
            $prev_value = wordpatch_maybe_serialize($prev_value);
        }

        $esc_meta_value = wordpatch_esc_sql($wpenv_vars, $meta_value);

        $update_query = "UPDATE $table SET meta_value = '$esc_meta_value' WHERE $column = '$object_id' AND meta_key = '$esc_meta_key'";

        if(!empty($prev_value)) {
            $prev_value_esc = wordpatch_esc_sql($wpenv_vars, $prev_value);
            $update_query .= " AND meta_value = '$prev_value_esc'";
        }

        $result = wordpatch_db_query($wpenv_vars, $update_query);
        if(!$result) {
            return false;
        }

        wordpatch_cache_delete($object_id, $meta_type . '_meta');

        return true;
    }
}

if(!function_exists('wordpatch_delete_metadata')) {
    function wordpatch_delete_metadata($wpenv_vars, $meta_type, $object_id, $meta_key, $meta_value = '', $delete_all = false) {
        if(!$meta_type || !$meta_key || !is_numeric($object_id) && !$delete_all) {
            return false;
        }

        $object_id = wordpatch_absint($object_id);
        if(!$object_id && !$delete_all) {
            return false;
        }

        $table = wordpatch__get_meta_table($wpenv_vars, $meta_type);

        if(!$table) {
            return false;
        }

        $type_column = wordpatch_sanitize_key($meta_type . '_id');
        $id_column = 'user' == $meta_type ? 'umeta_id' : 'meta_id';
        // expected_slashed ($meta_key)
        $meta_key = wordpatch_unslash($meta_key);
        $meta_value = wordpatch_unslash($meta_value);

        $_meta_value = $meta_value;
        $meta_value = wordpatch_maybe_serialize($meta_value);

        $esc_meta_key = wordpatch_esc_sql($wpenv_vars, $meta_key);

        $query = "SELECT $id_column FROM $table WHERE meta_key = '$esc_meta_key'";

        if(!$delete_all) {
            $query .= " AND $type_column = $object_id";
        }

        if('' !== $meta_value && null !== $meta_value && false !== $meta_value) {
            $esc_meta_value = wordpatch_esc_sql($wpenv_vars, $meta_value);
            $query .= " AND meta_value = '$esc_meta_value";
        }

        $meta_ids = wordpatch_db_get_col($wpenv_vars, $query);
        if(!count($meta_ids)) {
            return false;
        }

        $object_ids = array();

        if($delete_all) {
            $value_clause = '';
            if ('' !== $meta_value && null !== $meta_value && false !== $meta_value ) {
                $esc_meta_value = wordpatch_esc_sql($wpenv_vars, $meta_value);
                $value_clause = " AND meta_value = '$esc_meta_value'";
            }

            $altquery = "SELECT $type_column FROM $table WHERE meta_key = '$esc_meta_key' $value_clause";
            $object_ids = wordpatch_db_get_col($wpenv_vars, $altquery);
        }

        $query = "DELETE FROM $table WHERE $id_column IN( " . implode(',', $meta_ids) . " )";
        $delete_result = wordpatch_db_query($wpenv_vars, $query);

        $count = wordpatch_db_affected_rows($wpenv_vars);

        if(!$count) {
            return false;
        }

        if($delete_all) {
            foreach((array) $object_ids as $o_id) {
                wordpatch_cache_delete($o_id, $meta_type . '_meta');
            }
        } else {
            wordpatch_cache_delete($object_id, $meta_type . '_meta');
        }

        return true;
    }
}

if(!function_exists('wordpatch_update_user_meta')) {
    function wordpatch_update_user_meta($wpenv_vars, $user_id, $meta_key, $meta_value, $prev_value = '') {
        return wordpatch_update_metadata($wpenv_vars, 'user', $user_id, $meta_key, $meta_value, $prev_value);
    }
}

if(!function_exists('wordpatch_delete_user_meta')) {
    function wordpatch_delete_user_meta($wpenv_vars, $user_id, $meta_key, $meta_value = '') {
        return wordpatch_delete_metadata($wpenv_vars, 'user', $user_id, $meta_key, $meta_value);
    }
}

if(!function_exists('wordpatch_user_meta_session_tokens_update_sessions')) {
    function wordpatch_user_meta_session_tokens_update_sessions($wpenv_vars, $user_id, $sessions) {
        if($sessions) {
            wordpatch_update_user_meta($wpenv_vars, $user_id, 'session_tokens', $sessions );
        } else {
            wordpatch_delete_user_meta($wpenv_vars, $user_id, 'session_tokens');
        }
    }
}

if(!function_exists('wordpatch_user_meta_session_tokens_update_session')) {
    function wordpatch_user_meta_session_tokens_update_session($wpenv_vars, $user_id, $verifier, $session = null) {
        $sessions = wordpatch_user_meta_session_tokens_get_sessions($wpenv_vars, $user_id);

        if($session) {
            $sessions[$verifier] = $session;
        } else {
            unset($sessions[$verifier]);
        }

        wordpatch_user_meta_session_tokens_update_sessions($wpenv_vars, $user_id, $sessions);
    }
}

if(!function_exists('wordpatch_session_tokens_update')) {
    /**
     * @param $token
     * @adapted WP_Session_Tokens::update
     */
    function wordpatch_session_tokens_update($wpenv_vars, $user_id, $token, $session)
    {
        $verifier = wordpatch_session_tokens_hash_token($token);
        wordpatch_user_meta_session_tokens_update_session($wpenv_vars, $user_id, $verifier, $session);
    }
}

if(!function_exists('wordpatch_user_meta_session_tokens_prepare_session')) {
    function wordpatch_user_meta_session_tokens_prepare_session($session)
    {
        if(is_int($session)) {
            return array('expiration' => $session);
        }

        return $session;
    }
}

if(!function_exists('wordpatch_user_meta_session_tokens_is_still_valid')) {
    function wordpatch_session_tokens_is_still_valid($session)
    {
        return $session['expiration'] >= time();
    }
}

if(!function_exists('wordpatch_user_meta_session_tokens_get_sessions')) {
    function wordpatch_user_meta_session_tokens_get_sessions($wpenv_vars, $user_id)
    {
        $sessions = wordpatch_get_user_meta($wpenv_vars, $user_id, 'session_tokens', true);

        if(!is_array($sessions)) {
            return array();
        }

        $sessions = array_map('wordpatch_user_meta_session_tokens_prepare_session', $sessions);
        return array_filter($sessions, 'wordpatch_session_tokens_is_still_valid');
    }
}

if(!function_exists('wordpatch_user_meta_session_tokens_get_session')) {
    function wordpatch_user_meta_session_tokens_get_session($wpenv_vars, $user_id, $verifier)
    {
        $sessions = wordpatch_user_meta_session_tokens_get_sessions($wpenv_vars, $user_id);

        if(isset($sessions[$verifier])) {
            return $sessions[$verifier];
        }

        return null;
    }
}

if(!function_exists('wordpatch_session_tokens_verify')) {
    function wordpatch_session_tokens_verify($wpenv_vars, $user_id, $token)
    {
        $verifier = wordpatch_session_tokens_hash_token($token);
        return (bool) wordpatch_user_meta_session_tokens_get_session($wpenv_vars, $user_id, $verifier);
    }
}

if(!function_exists('wordpatch_get_userdata')) {
    function wordpatch_get_userdata($wpenv_vars, $user_id) {
        return wordpatch_get_user_by($wpenv_vars, 'id', $user_id);
    }
}

if(!function_exists('wordpatch_session_tokens_create')) {
    /**
     * @param $expiration
     * @return string
     * @adapted WP_Session_Tokens::create
     */
    function wordpatch_session_tokens_create($wpenv_vars, $user_id, $expiration)
    {
        $session = array();
        $session['expiration'] = $expiration;

        if(!empty($_SERVER['REMOTE_ADDR'])) {
            $session['ip'] = $_SERVER['REMOTE_ADDR'];
        }

        if(!empty($_SERVER['HTTP_USER_AGENT'])) {
            $session['ua'] = wordpatch_unslash($_SERVER['HTTP_USER_AGENT']);
        }

        $session['login'] = time();

        $token = wordpatch_generate_password(43, false, false);

        wordpatch_session_tokens_update($wpenv_vars, $user_id, $token, $session);

        return $token;
    }
}

if(!function_exists('wordpatch_generate_auth_cookie')) {
    /**
     * @adapted wp_generate_auth_cookie
     */
    function wordpatch_generate_auth_cookie($wpenv_vars, $user_id, $expiration, $scheme = 'auth', $token = '' ) {
        $user = wordpatch_get_userdata($wpenv_vars, $user_id);

        if(!$user) {
            return '';
        }

        if(!$token) {
            $token = wordpatch_session_tokens_create($wpenv_vars, $user_id, $expiration);
        }

        $pass_frag = substr($user->user_pass, 8, 4);

        $key = wordpatch_hash($wpenv_vars, $user->user_login . '|' . $pass_frag . '|' . $expiration . '|' . $token, $scheme);

        $algo = function_exists('hash') ? 'sha256' : 'sha1';
        $hash = wordpatch_hash_hmac($algo, $user->user_login . '|' . $expiration . '|' . $token, $key);

        $cookie = $user->user_login . '|' . $expiration . '|' . $token . '|' . $hash;

        return $cookie;
    }
}

if(!function_exists('wordpatch_auth_cookie')) {
    /**
     * This function returns a cookie safe for reading and writing from WP and/or pure land.
     * This function also ignores the wp-config constants on purpose (except for cookie hash)
     * @param $wpenv_vars
     * @return string
     * @adaptedconst AUTH_COOKIE
     */
    function wordpatch_auth_cookie($wpenv_vars)
    {
        return 'wordpatch_' . wordpatch_env_get($wpenv_vars, 'cookie_hash');
    }
}

if(!function_exists('wordpatch_secure_auth_cookie')) {
    /**
     * This function returns a cookie safe for reading and writing from WP and/or pure land.
     * This function also ignores the wp-config constants on purpose (except for cookie hash)
     * @param $wpenv_vars
     * @return string
     * @adaptedconst SECURE_AUTH_COOKIE
     */
    function wordpatch_secure_auth_cookie($wpenv_vars)
    {
        return 'wordpatch_sec_' . wordpatch_env_get($wpenv_vars, 'cookie_hash');
    }
}

if(!function_exists('wordpatch_logged_in_cookie')) {
    /**
     * This function returns a cookie safe for reading and writing from WP and/or pure land.
     * This function also ignores the wp-config constants on purpose (except for cookie hash)
     * @param $wpenv_vars
     * @return string
     * @adaptedconst LOGGED_IN_COOKIE
     */
    function wordpatch_logged_in_cookie($wpenv_vars)
    {
        return 'wordpatch_logged_in_' . wordpatch_env_get($wpenv_vars, 'cookie_hash');
    }
}

if(!function_exists('wordpatch_parse_auth_cookie')) {
    /**
     * @param string $cookie
     * @param string $scheme
     * @return array|bool
     * @adapted wp_parse_auth_cookie
     */
    function wordpatch_parse_auth_cookie($wpenv_vars, $cookie = '', $scheme = '')
    {
        if(empty($cookie)) {
            switch($scheme) {
                case 'auth':
                    $cookie_name = wordpatch_auth_cookie($wpenv_vars);
                    break;
                case 'secure_auth':
                    $cookie_name = wordpatch_secure_auth_cookie($wpenv_vars);
                    break;
                case "logged_in":
                    $cookie_name = wordpatch_logged_in_cookie($wpenv_vars);
                    break;
                default:
                    if (wordpatch_is_ssl()) {
                        $cookie_name = wordpatch_secure_auth_cookie($wpenv_vars);
                        $scheme = 'secure_auth';
                    } else {
                        $cookie_name = wordpatch_auth_cookie($wpenv_vars);
                        $scheme = 'auth';
                    }
            }

            if(empty($_COOKIE[$cookie_name]))
                return false;

            $cookie = $_COOKIE[$cookie_name];
        }

        $cookie_elements = explode('|', $cookie);

        if(count($cookie_elements) !== 4) {
            return false;
        }

        list($username, $expiration, $token, $hmac) = $cookie_elements;

        return compact('username', 'expiration', 'token', 'hmac', 'scheme');
    }
}

if(!function_exists('wordpatch_set_auth_cookie')) {
    function wordpatch_set_auth_cookie($wpenv_vars, $user_id, $remember = false, $secure = '', $token = '') {
        if($remember) {
            $expiration = time() + (14 * wordpatch_day_in_seconds());
            $expire = $expiration + (12 * wordpatch_hour_in_seconds());
        } else {
            $expiration = time() + (2 * wordpatch_day_in_seconds());
            $expire = 0;
        }

        if('' === $secure) {
            $secure = wordpatch_is_ssl();
        }

        $secure_logged_in_cookie = $secure &&
            'https' === parse_url(wordpatch_get_option($wpenv_vars, 'home'), PHP_URL_SCHEME);

        if ($secure) {
            $auth_cookie_name = wordpatch_secure_auth_cookie($wpenv_vars);
            $scheme = 'secure_auth';
        } else {
            $auth_cookie_name = wordpatch_auth_cookie($wpenv_vars);
            $scheme = 'auth';
        }

        if('' === $token) {
            $token = wordpatch_session_tokens_create($wpenv_vars, $user_id, $expiration);
        }

        $auth_cookie = wordpatch_generate_auth_cookie($wpenv_vars, $user_id, $expiration, $scheme, $token);
        $logged_in_cookie = wordpatch_generate_auth_cookie($wpenv_vars, $user_id, $expiration, 'logged_in', $token);

        setcookie($auth_cookie_name, $auth_cookie, $expire, wordpatch_env_get($wpenv_vars, 'admin_cookie_path'), wordpatch_env_get($wpenv_vars, 'cookie_domain'), $secure, true);

        setcookie(wordpatch_logged_in_cookie($wpenv_vars), $logged_in_cookie, $expire,
            wordpatch_env_get($wpenv_vars, 'cookie_path'), wordpatch_env_get($wpenv_vars, 'cookie_domain'), $secure_logged_in_cookie, true);

        if(wordpatch_env_get($wpenv_vars, 'cookie_path') !== wordpatch_env_get($wpenv_vars, 'site_cookie_path')) {
            setcookie(wordpatch_logged_in_cookie($wpenv_vars), $logged_in_cookie, $expire,
                wordpatch_env_get($wpenv_vars, 'site_cookie_path'), wordpatch_env_get($wpenv_vars, 'cookie_domain'), $secure_logged_in_cookie, true);
        }
    }
}

if(!function_exists('wordpatch_is_logged_in')) {
    function wordpatch_is_logged_in($wpenv_vars)
    {
        $patch_check = wordpatch_validate_auth_cookie($wpenv_vars);

        return $patch_check;
    }
}

if(!function_exists('wordpatch_get_current_user_id')) {
    function wordpatch_get_current_user_id($wpenv_vars) {
        $current_user = wordpatch_get_current_user($wpenv_vars);

        if(!$current_user) {
            return 0;
        }

        if(!is_object($current_user) || !isset($current_user->ID)) {
            return 0;
        }

        return max(0, (int)$current_user->ID);
    }
}

if(!function_exists('wordpatch_get_current_user')) {
    function wordpatch_get_current_user($wpenv_vars)
    {
        $patch_check = wordpatch_validate_auth_cookie($wpenv_vars);

        if($patch_check) {
            $patch_user = wordpatch_get_user_by($wpenv_vars, 'id', $patch_check);

            if($patch_user) {
                return $patch_user;
            }
        }

        return false;
    }
}

if(!function_exists('wordpatch_get_session_token')) {
    function wordpatch_get_session_token($wpenv_vars) {
        $cookie = wordpatch_parse_auth_cookie($wpenv_vars, '', 'logged_in');

        if(!empty($cookie['token'])) {
            return $cookie['token'];
        }

        return '';
    }
}

if(!function_exists('wordpatch_logout')) {
    function wordpatch_logout($wpenv_vars) {
        wordpatch_destroy_current_session($wpenv_vars);
        wordpatch_clear_auth_cookie($wpenv_vars);
    }
}

if(!function_exists('wordpatch_session_tokens_destroy')) {
    function wordpatch_session_tokens_destroy($wpenv_vars, $user_id, $token) {
        $verifier = wordpatch_session_tokens_hash_token($token);
        wordpatch_user_meta_session_tokens_update_session($wpenv_vars, $user_id, $verifier, null);
    }
}

if(!function_exists('wordpatch_destroy_current_session')) {
    function wordpatch_destroy_current_session($wpenv_vars) {
        $current_user_id = wordpatch_get_current_user_id($wpenv_vars);

        if(!$current_user_id) {
            return;
        }

        $token = wordpatch_get_session_token($wpenv_vars);

        if($token) {
            wordpatch_session_tokens_destroy($wpenv_vars, $current_user_id, $token);
        }
    }
}

if(!function_exists('wordpatch_clear_auth_cookie')) {
    function wordpatch_clear_auth_cookie($wpenv_vars) {
        $cookie_domain = wordpatch_env_get($wpenv_vars, 'cookie_domain');
        $admin_cookie_path = wordpatch_env_get($wpenv_vars, 'admin_cookie_path');
        $cookie_path = wordpatch_env_get($wpenv_vars, 'cookie_path');
        $site_cookie_path = wordpatch_env_get($wpenv_vars, 'site_cookie_path');

        $year_in_seconds = wordpatch_year_in_seconds();
        $time_delta = time() - $year_in_seconds;

        $logged_in_cookie = wordpatch_logged_in_cookie($wpenv_vars);

        setcookie(wordpatch_auth_cookie($wpenv_vars), ' ', $time_delta, $admin_cookie_path, $cookie_domain);
        setcookie(wordpatch_secure_auth_cookie($wpenv_vars), ' ', $time_delta, $admin_cookie_path, $cookie_domain);
        setcookie($logged_in_cookie, ' ', $time_delta, $cookie_path, $cookie_domain);
        setcookie($logged_in_cookie, ' ', $time_delta, $site_cookie_path, $cookie_domain);
    }
}

if(!function_exists('wordpatch_update_meta_cache')) {
    function wordpatch_update_meta_cache($wpenv_vars, $meta_type, $object_ids) {
        if(!$meta_type || !$object_ids) {
            return false;
        }

        $table = wordpatch__get_meta_table($wpenv_vars, $meta_type);
        if(!$table) {
            return false;
        }

        $column = wordpatch_sanitize_key($meta_type . '_id');

        if(!is_array($object_ids)) {
            $object_ids = preg_replace('|[^0-9,]|', '', $object_ids);
            $object_ids = explode(',', $object_ids);
        }

        $object_ids = array_map('intval', $object_ids);

        $cache_key = $meta_type . '_meta';
        $ids = array();
        $cache = array();
        foreach($object_ids as $id) {
            $cached_object = wordpatch_cache_get($id, $cache_key);
            if (false === $cached_object) {
                $ids[] = $id;
            } else {
                $cache[$id] = $cached_object;
            }
        }

        if(empty($ids)) {
            return $cache;
        }

        // Get meta info
        $id_list = join(',', $ids);
        $id_column = 'user' == $meta_type ? 'umeta_id' : 'meta_id';
        $meta_list = wordpatch_db_get_results($wpenv_vars, "SELECT $column, meta_key, meta_value FROM $table WHERE $column IN ($id_list) ORDER BY $id_column ASC");

        if(!empty($meta_list)) {
            foreach($meta_list as $metarow) {
                $mpid = intval($metarow[$column]);
                $mkey = $metarow['meta_key'];
                $mval = $metarow['meta_value'];

                // Force subkeys to be array type:
                if(!isset($cache[$mpid]) || !is_array($cache[$mpid])) {
                    $cache[$mpid] = array();
                }

                if(!isset($cache[$mpid][$mkey]) || !is_array($cache[$mpid][$mkey])) {
                    $cache[$mpid][$mkey] = array();
                }

                // Add a value to the current pid/key:
                $cache[$mpid][$mkey][] = $mval;
            }
        }

        foreach($ids as $id) {
            if(!isset($cache[$id])) {
                $cache[$id] = array();
            }

            wordpatch_cache_add($id, $cache[$id], $cache_key);
        }

        return $cache;
    }
}

if(!function_exists('wordpatch_get_metadata')) {
    /**
     * @param $wpenv_vars
     * @param $meta_type
     * @param $object_id
     * @param string $meta_key
     * @param bool $single
     * @return array|bool|string
     * @adapted get_metadata
     */
    function wordpatch_get_metadata($wpenv_vars, $meta_type, $object_id, $meta_key = '', $single = false) {
        if(!$meta_type || !is_numeric($object_id)) {
            return false;
        }

        $object_id = wordpatch_absint($object_id);

        if(!$object_id) {
            return false;
        }

        $meta_cache = wordpatch_cache_get($wpenv_vars, $object_id, $meta_type . '_meta');

        if(!$meta_cache) {
            $meta_cache = wordpatch_update_meta_cache($wpenv_vars, $meta_type, array($object_id));
            $meta_cache = $meta_cache[$object_id];
        }

        if(!$meta_key) {
            return $meta_cache;
        }

        if(isset($meta_cache[$meta_key])) {
            if($single)
                return wordpatch_maybe_unserialize($meta_cache[$meta_key][0]);
            else
                return array_map('wordpatch_maybe_unserialize', $meta_cache[$meta_key]);
        }

        if ($single)
            return '';
        else
            return array();
    }
}

if(!function_exists('wordpatch_get_user_meta')) {
    /**
     * @param $wpenv_vars
     * @param $user_id
     * @param string $key
     * @param bool $single
     * @return mixed
     * @adapted get_user_meta
     */
    function wordpatch_get_user_meta($wpenv_vars, $user_id, $key = '', $single = false)
    {
        return wordpatch_get_metadata($wpenv_vars, 'user', $user_id, $key, $single);
    }
}

if(!function_exists('wordpatch_update_user_caches')) {
    function wordpatch_update_user_caches($user) {
        wordpatch_cache_add($user->ID, $user, 'users');
        wordpatch_cache_add($user->user_login, $user->ID, 'userlogins');
        wordpatch_cache_add($user->user_email, $user->ID, 'useremail');
        wordpatch_cache_add($user->user_nicename, $user->ID, 'userslugs');
    }
}

if(!function_exists('wordpatch_user_get_data_by')) {
    function wordpatch_user_get_data_by($wpenv_vars,  $field, $value) {
        // 'ID' is an alias of 'id'.
        if('ID' === $field) {
            $field = 'id';
        }

        if('id' == $field) {
            // Make sure the value is numeric to avoid casting objects, for example,
            // to int 1.
            if(!is_numeric($value)) {
                return false;
            }
            $value = intval($value);
            if($value < 1) {
                return false;
            }
        } else {
            $value = trim($value);
        }

        if(!$value) {
            return false;
        }

        switch($field) {
            case 'id':
                $user_id = $value;
                $db_field = 'ID';
                break;
            case 'slug':
                $user_id = wordpatch_cache_get($value, 'userslugs');
                $db_field = 'user_nicename';
                break;
            case 'email':
                $user_id = wordpatch_cache_get($value, 'useremail');
                $db_field = 'user_email';
                break;
            case 'login':
                $value = wordpatch_sanitize_user($wpenv_vars, $value);
                $user_id = wordpatch_cache_get($value, 'userlogins');
                $db_field = 'user_login';
                break;
            default:
                return false;
        }

        if(false !== $user_id) {
            if($user = wordpatch_cache_get($user_id, 'users')) {
                return $user;
            }
        }

        $users_table = wordpatch_users_table($wpenv_vars);
        $esc_value = wordpatch_esc_sql($wpenv_vars,  $value);

        $query = "SELECT * FROM $users_table WHERE $db_field = '$esc_value'";
        $user = wordpatch_db_get_row($wpenv_vars, $query, false);
        if(!$user) {
            return false;
        }

        wordpatch_update_user_caches($user);
        return $user;
    }
}

if(!function_exists('wordpatch_get_user_by')) {
    function wordpatch_get_user_by($wpenv_vars, $field, $value) {
        $userdata = wordpatch_user_get_data_by($wpenv_vars, $field, $value);

        if(!$userdata) {
            return false;
        }

        return $userdata;
    }
}

if(!function_exists('wordpatch_validate_auth_cookie')) {
    function wordpatch_validate_auth_cookie($wpenv_vars, $cookie = '', $scheme = '')
    {
        if (!$cookie_elements = wordpatch_parse_auth_cookie($wpenv_vars, $cookie, $scheme)) {
            return false;
        }

        $scheme = $cookie_elements['scheme'];
        $username = $cookie_elements['username'];
        $hmac = $cookie_elements['hmac'];
        $token = $cookie_elements['token'];
        $expired = $expiration = $cookie_elements['expiration'];

        if (wordpatch_doing_ajax() || 'POST' == $_SERVER['REQUEST_METHOD']) {
            $expired += wordpatch_hour_in_seconds();
        }

        if ($expired < time()) {
            return false;
        }

        $user = wordpatch_get_user_by($wpenv_vars, 'login', $username);
        if (!$user) {
            return false;
        }

        $pass_frag = substr($user->user_pass, 8, 4);

        $key = wordpatch_hash($wpenv_vars, $username . '|' . $pass_frag . '|' . $expiration . '|' . $token, $scheme);

        // If ext/hash is not present, compat.php's hash_hmac() does not support sha256.
        $algo = function_exists('hash') ? 'sha256' : 'sha1';
        $hash = wordpatch_hash_hmac($algo, $username . '|' . $expiration . '|' . $token, $key);

        if(!wordpatch_hash_equals($hash, $hmac)) {
            return false;
        }

        if(!wordpatch_session_tokens_verify($wpenv_vars, $user->ID, $token)) {
            return false;
        }

        return $user->ID;
    }
}

if(!function_exists('wordpatch_authenticate')) {
    function wordpatch_authenticate($wpenv_vars, $username_or_email, $password)
    {
        $username_or_email = wordpatch_sanitize_user($wpenv_vars, $username_or_email);
        $password = trim($password); // TODO: Why is this trimmed? WordPress really does this??

        if (empty($username_or_email) || empty($password)) {
            return false;
        }

        if(wordpatch_is_email($username_or_email)) {
            $user = wordpatch_get_user_by($wpenv_vars, 'email', $username_or_email);
        } else {
            $user = wordpatch_get_user_by($wpenv_vars, 'login', $username_or_email);
        }

        if(!$user) {
            return false;
        }

        if(!wordpatch_check_password($password, $user->user_pass, $user->ID)) {
            return false;
        }

        return $user;
    }
}

if(!function_exists('wordpatch_session_tokens_hash_token')) {
    /**
     * @param $token
     * @return string
     * @adapted WP_Session_Tokens::hash_token
     */
    function wordpatch_session_tokens_hash_token($token)
    {
        if(function_exists('hash')) {
            return hash('sha256', $token);
        } else {
            return sha1($token);
        }
    }
}