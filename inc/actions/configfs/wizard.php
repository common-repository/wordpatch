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
 * The following functions define the majority of the filesystem configuration shared functionality related to
 * configuration wizards.
 */

if(!function_exists('wordpatch_configfs_num_steps')) {
    /**
     * Returns the number of steps in the filesystem configuration wizard based on the currently selected filesystem
     * method. Every filesystem method other than direct will cause this function to return 5.
     *
     * @param $current_fs_method
     * @return int
     */
    function wordpatch_configfs_num_steps($current_fs_method) {
        return $current_fs_method === wordpatch_fs_method_direct() ? 3 : 5;
    }
}

if(!function_exists('wordpatch_configfs_step_text')) {
    /**
     * Calculate the step text for each step of the filesystem configuration wizard.
     *
     * @param $wpenv_vars
     * @param $step_number
     * @param $current_fs_method
     * @return string
     */
    function wordpatch_configfs_step_text($wpenv_vars, $step_number, $current_fs_method) {
        // Calculate the step numbers for each step.
        $steps = wordpatch_configfs_steps($current_fs_method);

        // Create a variable for our step text result defaulting to an empty string.
        $step_text = "";

        // Calculate the step text based on the step number.
        switch ($step_number) {
            case $steps['basic']:
                $step_text = __wt($wpenv_vars, 'CONFIGFS_STEP_BASIC', $steps['basic']);
                break;
            case $steps['dirs']:
                $step_text = __wt($wpenv_vars, 'CONFIGFS_STEP_DIRS', $steps['dirs']);
                break;
            case $steps['creds']:
                $step_text = __wt($wpenv_vars, 'CONFIGFS_STEP_CREDS', $steps['creds']);
                break;
            case $steps['validate']:
                $step_text = __wt($wpenv_vars, 'CONFIGFS_STEP_VALIDATE', $steps['validate']);
                break;
            case $steps['confirm']:
                $step_text = __wt($wpenv_vars, 'CONFIGFS_STEP_CONFIRM', $steps['confirm']);
                break;
        }

        // Return the step text result.
        return $step_text;
    }
}

if(!function_exists('wordpatch_configfs_vars')) {
    /**
     * Calculate the wizard variables for each step of the filesystem configuration wizard.
     * Results are returned as an associative array with the key convention:
     * - $result['step' . $step_number][$var_key]
     *
     * @param $current_fs_method
     * @return array
     */
    function wordpatch_configfs_vars($current_fs_method)
    {
        // Calculate the step numbers for each step.
        $steps = wordpatch_configfs_steps($current_fs_method);

        // Begin to construct our variables result array.
        $vars = array(
            'step1' => array(
                'fs_method',
                'fs_timeout',
                'fs_chmod_file',
                'fs_chmod_dir'
            )
        );

        // Check if the credentials step is not null, and populate credential variables if so.
        if($steps['creds'] !== null) {
            $vars['step' . $steps['creds']] = array('ftp_host', 'ftp_user', 'ftp_pass');

            // Check if the filesystem method is `ssh2`, and populate credential variables if so.
            if($current_fs_method === wordpatch_fs_method_ssh2()) {
                $vars['step' . $steps['creds']][] = 'ftp_pubkey';
                $vars['step' . $steps['creds']][] = 'ftp_prikey';
            }

            // Check if the filesystem method is `ftpext`, and populate credential variables if so.
            if($current_fs_method === wordpatch_fs_method_ftpext()) {
                $vars['step' . $steps['creds']][] = 'ftp_ssl';
            }
        }

        // Check if the directories step is not null, and populate directory variables if so.
        if($steps['dirs'] !== null) {
            $vars['step' . $steps['dirs']] = array('ftp_base', 'ftp_content_dir', 'ftp_plugin_dir');
        }

        // Return our variables result array.
        return $vars;
    }
}

if(!function_exists('wordpatch_configfs_post_vars')) {
    /**
     * Constructs and returns and associative array of filesystem configuration wizard post variables.
     * The parameter $only_consider_posted can be used to strictly only consider post data.
     * If you pass false for $only_consider_posted, then values will be pre-calculated or guessed if possible.
     *
     * @param $wpenv_vars
     * @param $only_consider_posted
     * @return array
     */
    function wordpatch_configfs_post_vars($wpenv_vars, $only_consider_posted) {
        // Create an empty array for our results
        $current = array();

        // Calculate the filesystem method value.
        $current['fs_method'] = wordpatch_wizard_post_data('fs_method', wordpatch_configfs_guess_fs_method($wpenv_vars),
            wordpatch_calculate_fs_method($wpenv_vars), 'wordpatch_wizard_sanitize_fs_method', $only_consider_posted);

        // Calculate the filesystem timeout value.
        $current['fs_timeout'] = wordpatch_wizard_post_data('fs_timeout', wordpatch_configfs_guess_fs_timeout($wpenv_vars),
            wordpatch_calculate_fs_timeout($wpenv_vars), 'wordpatch_wizard_sanitize_fs_timeout', $only_consider_posted);

        // Calculate the filesystem CHMOD file value.
        $current['fs_chmod_file'] = wordpatch_wizard_post_data('fs_chmod_file', wordpatch_configfs_guess_fs_chmod_file($wpenv_vars),
            wordpatch_calculate_fs_chmod_file($wpenv_vars), 'wordpatch_wizard_sanitize_fs_chmod_file', $only_consider_posted);

        // Calculate the filesystem CHMOD directory value.
        $current['fs_chmod_dir'] = wordpatch_wizard_post_data('fs_chmod_dir', wordpatch_configfs_guess_fs_chmod_dir($wpenv_vars),
            wordpatch_calculate_fs_chmod_dir($wpenv_vars), 'wordpatch_wizard_sanitize_fs_chmod_dir', $only_consider_posted);

        // Calculate the FTP host value.
        $current['ftp_host'] = wordpatch_wizard_post_data('ftp_host', wordpatch_configfs_guess_ftp_host($wpenv_vars),
            wordpatch_calculate_ftp_host($wpenv_vars), 'wordpatch_wizard_sanitize_trim', $only_consider_posted);

        // Calculate the FTP user value.
        $current['ftp_user'] = wordpatch_wizard_post_data('ftp_user', wordpatch_configfs_guess_ftp_user($wpenv_vars),
            wordpatch_calculate_ftp_user($wpenv_vars), 'wordpatch_wizard_sanitize_trim', $only_consider_posted);

        // Calculate the FTP pass value.
        $current['ftp_pass'] = wordpatch_wizard_post_data('ftp_pass', wordpatch_configfs_guess_ftp_pass($wpenv_vars),
            wordpatch_calculate_ftp_pass($wpenv_vars), 'wordpatch_wizard_sanitize_password', $only_consider_posted);

        // Calculate the FTP public key value.
        $current['ftp_pubkey'] = wordpatch_wizard_post_data('ftp_pubkey', wordpatch_configfs_guess_ftp_pubkey($wpenv_vars),
            wordpatch_calculate_ftp_pubkey($wpenv_vars), 'wordpatch_wizard_sanitize_unix_path', $only_consider_posted);

        // Calculate the FTP private key value.
        $current['ftp_prikey'] = wordpatch_wizard_post_data('ftp_prikey', wordpatch_configfs_guess_ftp_prikey($wpenv_vars),
            wordpatch_calculate_ftp_prikey($wpenv_vars), 'wordpatch_wizard_sanitize_unix_path', $only_consider_posted);

        // Calculate the FTP SSL value.
        $current['ftp_ssl'] = wordpatch_wizard_post_data('ftp_ssl', wordpatch_configfs_guess_ftp_ssl($wpenv_vars),
            wordpatch_calculate_ftp_ssl($wpenv_vars), 'wordpatch_wizard_sanitize_ftp_ssl', $only_consider_posted);

        // Calculate the FTP base value.
        $current['ftp_base'] = wordpatch_wizard_post_data('ftp_base', wordpatch_configfs_guess_ftp_base($wpenv_vars),
            wordpatch_calculate_ftp_base($wpenv_vars), 'wordpatch_wizard_sanitize_unix_path', $only_consider_posted);

        // Calculate the FTP content directory value.
        $current['ftp_content_dir'] = wordpatch_wizard_post_data('ftp_content_dir', wordpatch_configfs_guess_ftp_content_dir($wpenv_vars),
            wordpatch_calculate_ftp_content_dir($wpenv_vars), 'wordpatch_wizard_sanitize_unix_path', $only_consider_posted);

        // Calculate the FTP plugin directory value.
        $current['ftp_plugin_dir'] = wordpatch_wizard_post_data('ftp_plugin_dir', wordpatch_configfs_guess_ftp_plugin_dir($wpenv_vars),
            wordpatch_calculate_ftp_plugin_dir($wpenv_vars), 'wordpatch_wizard_sanitize_unix_path', $only_consider_posted);

        // Calculate the filesystem CHMOD file value as an integer.
        $current['fs_chmod_file_int'] = $current['fs_chmod_file'] === null ?
            0 : wordpatch_convert_octal_to_dec($current['fs_chmod_file']);

        // Calculate the filesystem CHMOD directory value as an integer.
        $current['fs_chmod_dir_int'] = $current['fs_chmod_dir'] === null ?
            0 : wordpatch_convert_octal_to_dec($current['fs_chmod_dir']);

        // Return our calculate result array.
        return $current;
    }
}

if(!function_exists('wordpatch_configfs_buttons')) {
    /**
     * Calculates the button state for the filesystem configuration wizard.
     * Called by `wordpatch_configfs_render_form`.
     *
     * @param $step_number
     * @param $current_fs_method
     * @param $wizard_error_list
     * @return array
     */
    function wordpatch_configfs_buttons($step_number, $current_fs_method, $wizard_error_list) {
        // Calculate the step numbers for each step.
        $steps = wordpatch_configfs_steps($current_fs_method);

        // Check if step number equals the basic step number and return the proper state, if necessary.
        if($step_number === $steps['basic']) {
            return array(
                WORDPATCH_SUBMIT_TYPE_NEXT => true,
                WORDPATCH_SUBMIT_TYPE_PREVIOUS => false
            );
        }

        // Check if step number equals the credentials step number and return the proper state, if necessary.
        if($step_number === $steps['creds']) {
            return array(
                WORDPATCH_SUBMIT_TYPE_NEXT => true,
                WORDPATCH_SUBMIT_TYPE_PREVIOUS => true
            );
        }

        // Check if step number equals the directories step number and return the proper state, if necessary.
        if($step_number === $steps['dirs']) {
            // Calculate if there should be a next button.
            $blocking_errors = wordpatch_configfs_get_blocking_errors_dirs();
            $no_next = wordpatch_errors_are_blocking($wizard_error_list, $blocking_errors);

            return array(
                WORDPATCH_SUBMIT_TYPE_NEXT => $no_next ? false : true,
                WORDPATCH_SUBMIT_TYPE_PREVIOUS => true
            );
        }

        // Check if step number equals the validate step number and return the proper state, if necessary.
        if($step_number === $steps['validate']) {
            // Calculate if there should be a next button.
            $blocking_errors = wordpatch_configfs_get_blocking_errors_validate();
            $no_next = wordpatch_errors_are_blocking($wizard_error_list, $blocking_errors);

            return array(
                WORDPATCH_SUBMIT_TYPE_NEXT => $no_next ? false : true,
                WORDPATCH_SUBMIT_TYPE_PREVIOUS => true
            );
        }

        // By default, return a state with no button.
        return array(
            WORDPATCH_SUBMIT_TYPE_NEXT => false,
            WORDPATCH_SUBMIT_TYPE_PREVIOUS => false
        );
    }
}

if(!function_exists('wordpatch_configfs_steps')) {
    /**
     * Calculates the step numbers for each step of the filesystem configuration wizard.
     * Return value is an associative array with the key being the step key and the value being the step number.
     *
     * @param $current_fs_method
     * @return array
     */
    function wordpatch_configfs_steps($current_fs_method) {
        return array(
            'basic' => 1,
            'validate' => $current_fs_method === wordpatch_fs_method_direct() ? 2 : 4,
            'creds' => $current_fs_method === wordpatch_fs_method_direct() ? null : 2,
            'dirs' => $current_fs_method === wordpatch_fs_method_direct() ? null : 3,
            'confirm' => $current_fs_method === wordpatch_fs_method_direct() ? 3 : 5
        );
    }
}

if(!function_exists('wordpatch_configfs_db_data')) {
    /**
     * Construct values to be written to the database after the filesystem configuration wizard has completed.
     *
     * @param $wpenv_vars
     * @param $current
     * @return array
     */
    function wordpatch_configfs_db_data($wpenv_vars, $current)
    {
        // Start constructing $db_data and default some stuff to empty
        $db_data = array(
            'fs_method' => $current['fs_method'],
            'fs_timeout' => $current['fs_timeout'],
            'fs_chmod_file' => $current['fs_chmod_file'],
            'fs_chmod_dir' => $current['fs_chmod_dir'],
            'ftp_host' => '',
            'ftp_user' => '',
            'ftp_pass' => '',
            'ftp_pubkey' => '',
            'ftp_prikey' => '',
            'ftp_ssl' => '',
            'ftp_base' => '',
            'ftp_content_dir' => '',
            'ftp_plugin_dir' => ''
        );

        // Return early if we are using the direct filesystem method
        if ($current['fs_method'] === wordpatch_fs_method_direct()) {
            return $db_data;
        }

        // The rest of the filesystem methods share the following fields.
        $db_data['ftp_base'] = $current['ftp_base'];
        $db_data['ftp_content_dir'] = $current['ftp_content_dir'];
        $db_data['ftp_plugin_dir'] = $current['ftp_plugin_dir'];
        $db_data['ftp_host'] = $current['ftp_host'];
        $db_data['ftp_user'] = $current['ftp_user'];
        $db_data['ftp_pass'] = $current['ftp_pass'];

        // Check for the ssh2 filesystem method. Populate the necessary fields and return early.
        if ($current['fs_method'] === wordpatch_fs_method_ssh2()) {
            $db_data['ftp_pubkey'] = $current['ftp_pubkey'];
            $db_data['ftp_prikey'] = $current['ftp_prikey'];
            $db_data['ftp_ssl'] = $current['ftp_ssl'];

            return $db_data;
        }

        // Check for the ftpext filesystem method. Populate the necessary fields and return early.
        if ($current['fs_method'] === wordpatch_fs_method_ftpext()) {
            $db_data['ftp_ssl'] = $current['ftp_ssl'];

            return $db_data;
        }

        // If we get here, simply return $db_data
        return $db_data;
    }
}

if(!function_exists('wordpatch_configfs_persist_db')) {
    /**
     * Persists the finalized wizard data upon completion of the filesystem configuration wizard.
     *
     * @param $wpenv_vars
     * @param $db_data
     */
    function wordpatch_configfs_persist_db($wpenv_vars, $db_data) {
        // Store the filesystem method value in the database.
        wordpatch_update_option($wpenv_vars, 'wordpatch_fs_method', $db_data['fs_method']);

        // Store the filesystem timeout value in the database.
        wordpatch_update_option($wpenv_vars, 'wordpatch_fs_timeout', $db_data['fs_timeout']);

        // Store the filesystem CHMOD file value in the database.
        wordpatch_update_option($wpenv_vars, 'wordpatch_fs_chmod_file', $db_data['fs_chmod_file']);

        // Store the filesystem CHMOD directory value in the database.
        wordpatch_update_option($wpenv_vars, 'wordpatch_fs_chmod_dir', $db_data['fs_chmod_dir']);

        // Store the FTP base value in the database.
        wordpatch_update_option($wpenv_vars, 'wordpatch_ftp_base', $db_data['ftp_base']);

        // Store the FTP content directory value in the database.
        wordpatch_update_option($wpenv_vars, 'wordpatch_ftp_content_dir', $db_data['ftp_content_dir']);

        // Store the FTP plugin directory value in the database.
        wordpatch_update_option($wpenv_vars, 'wordpatch_ftp_plugin_dir', $db_data['ftp_plugin_dir']);

        // Store the SSH public key value in the database.
        wordpatch_update_option($wpenv_vars, 'wordpatch_ftp_pubkey', $db_data['ftp_pubkey']);

        // Store the SSH private key value in the database.
        wordpatch_update_option($wpenv_vars, 'wordpatch_ftp_prikey', $db_data['ftp_prikey']);

        // Store the FTP user value in the database.
        wordpatch_update_option($wpenv_vars, 'wordpatch_ftp_user', $db_data['ftp_user']);

        // Store the FTP password value in the database.
        wordpatch_update_option($wpenv_vars, 'wordpatch_ftp_pass', $db_data['ftp_pass']);

        // Store the FTP host value in the database.
        wordpatch_update_option($wpenv_vars, 'wordpatch_ftp_host', $db_data['ftp_host']);

        // Store the FTP SSL value in the database.
        wordpatch_update_option($wpenv_vars, 'wordpatch_ftp_ssl', $db_data['ftp_ssl']);

        // Store the FTP configured state in the database.
        wordpatch_update_option($wpenv_vars, 'wordpatch_fs_configured', WORDPATCH_YES);
    }
}

if(!function_exists('wordpatch_configfs_get_blocking_errors_dirs')) {
    /**
     * Calculates the list of blocking errors for the directories step of the filesystem configuration wizard.
     *
     * @return array
     */
    function wordpatch_configfs_get_blocking_errors_dirs() {
        // Return the list of blocking errors
        return array(WORDPATCH_INVALID_FILESYSTEM_CREDENTIALS);
    }
}

if(!function_exists('wordpatch_configfs_get_blocking_errors_validate')) {
    /**
     * Calculates the list of blocking errors for the validation step of the filesystem configuration wizard.
     *
     * @return array
     */
    function wordpatch_configfs_get_blocking_errors_validate() {
        // Return null since all errors are blocking
        return null;
    }
}