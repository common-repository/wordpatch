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
 * The following functions guess field values for each step of the database configuration wizard.
 * These functions are called from `wordpatch_configdb_post_vars`.
 */

if(!function_exists('wordpatch_configdb_guess_db_host')) {
    /**
     * Guess the best database host value to be used for the database configuration wizard.
     *
     * @param $wpenv_vars
     * @return mixed|string
     */
    function wordpatch_configdb_guess_db_host($wpenv_vars)
    {
        $db_connection_string = wordpatch_env_get($wpenv_vars, 'db_connection_string');

        return $db_connection_string['db_host'];
    }
}

if(!function_exists('wordpatch_configdb_guess_db_user')) {
    /**
     * Guess the best database username value to be used for the database configuration wizard.
     *
     * @param $wpenv_vars
     * @return mixed|string
     */
    function wordpatch_configdb_guess_db_user($wpenv_vars)
    {
        $db_connection_string = wordpatch_env_get($wpenv_vars, 'db_connection_string');

        return $db_connection_string['db_user'];
    }
}

if(!function_exists('wordpatch_configdb_guess_db_name')) {
    /**
     * Guess the best database name value to be used for the database configuration wizard.
     *
     * @param $wpenv_vars
     * @return mixed|string
     */
    function wordpatch_configdb_guess_db_name($wpenv_vars)
    {
        $db_connection_string = wordpatch_env_get($wpenv_vars, 'db_connection_string');

        return $db_connection_string['db_name'];
    }
}

if(!function_exists('wordpatch_configdb_guess_db_table_prefix')) {
    /**
     * Guess the best database table prefix value to be used for the database configuration wizard.
     *
     * @param $wpenv_vars
     * @return mixed|string
     */
    function wordpatch_configdb_guess_db_table_prefix($wpenv_vars)
    {
        $db_connection_string = wordpatch_env_get($wpenv_vars, 'db_connection_string');

        return $db_connection_string['db_table_prefix'];
    }
}

if(!function_exists('wordpatch_configdb_guess_db_collate')) {
    /**
     * Guess the best database collation value to be used for the database configuration wizard.
     *
     * @param $wpenv_vars
     * @return mixed|string
     */
    function wordpatch_configdb_guess_db_collate($wpenv_vars)
    {
        $db_connection_string = wordpatch_env_get($wpenv_vars, 'db_connection_string');
        $collate = strtolower(trim($db_connection_string['db_collate']));

        if(wordpatch_is_valid_collate($collate)) {
            return $collate;
        }

        return 'utf8_general_ci';
    }
}

if(!function_exists('wordpatch_configdb_guess_db_charset')) {
    /**
     * Guess the best database charset value to be used for the database configuration wizard.
     *
     * @param $wpenv_vars
     * @return mixed|string
     */
    function wordpatch_configdb_guess_db_charset($wpenv_vars)
    {
        $db_connection_string = wordpatch_env_get($wpenv_vars, 'db_connection_string');
        $charset = strtolower(trim($db_connection_string['db_charset']));

        if(wordpatch_is_valid_charset($charset)) {
            return $charset;
        }

        return 'utf8';
    }
}

if(!function_exists('wordpatch_configdb_guess_db_password')) {
    /**
     * Guess the best database password value to be used for the database configuration wizard.
     *
     * @param $wpenv_vars
     * @return mixed|string
     */
    function wordpatch_configdb_guess_db_password($wpenv_vars)
    {
        $db_connection_string = wordpatch_env_get($wpenv_vars, 'db_connection_string');

        return $db_connection_string['db_password'];
    }
}