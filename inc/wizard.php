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

if(!function_exists('wordpatch_wizard_buttons_any')) {
    function wordpatch_wizard_buttons_any($buttons)
    {
        $any = false;

        foreach($buttons as $_ => $v) {
            if($v) {
                $any = true;
                break;
            }
        }

        return $any;
    }
}

if(!function_exists('wordpatch_wizard_validate_rescue_dirs')) {
    function wordpatch_wizard_validate_rescue_dirs($wpenv_vars, $current_rescue_path) {
        $full_rescue_path = wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_root_dir')) . $current_rescue_path;

        $wizard_error_list = array();
        $rescue_path_info = pathinfo($full_rescue_path);

        if ($rescue_path_info && isset($rescue_path_info['extension']) &&
            isset($rescue_path_info['dirname']) && isset($rescue_path_info['filename'])) {
            $valid_php_extensions = array('php', 'php3', 'php4', 'php5', 'php7', 'phtml');
            if (!in_array(strtolower($rescue_path_info['extension']), $valid_php_extensions)) {
                $wizard_error_list[] = WORDPATCH_INVALID_RESCUE_EXTENSION;
            }

            if (!file_exists($rescue_path_info['dirname']) || !is_dir($rescue_path_info['dirname'])) {
                $wizard_error_list[] = WORDPATCH_INVALID_RESCUE_DIRECTORY;
            }

            if (trim($rescue_path_info['filename']) === '') {
                $wizard_error_list[] = WORDPATCH_INVALID_RESCUE_FILENAME;
            }
        } else {
            $wizard_error_list[] = WORDPATCH_INVALID_RESCUE_PATH;
        }

        return $wizard_error_list;
    }
}

if(!function_exists('wordpatch_wizard_validate_db_creds')) {
    function wordpatch_wizard_validate_db_creds($current_db_host, $current_db_user, $current_db_name, $current_db_collate,
                $current_db_charset) {
        // Create an empty array for our error list.
        $error_list = array();

        // Validate the database host.
        if(!$current_db_host || trim($current_db_host) === '') {
            $error_list[] = WORDPATCH_DB_HOST_REQUIRED;
        }

        // Validate the database username.
        if(!$current_db_user || trim($current_db_user) === '') {
            $error_list[] = WORDPATCH_DB_USER_REQUIRED;
        }

        // Validate the database name.
        if(!$current_db_name || trim($current_db_name) === '') {
            $error_list[] = WORDPATCH_DB_NAME_REQUIRED;
        }

        // Validate the database collation.
        if(!$current_db_collate || !wordpatch_is_valid_collate($current_db_collate)) {
            $error_list[] = WORDPATCH_DB_COLLATE_INVALID;
        }

        // Validate the database charset.
        if(!$current_db_charset || !wordpatch_is_valid_charset($current_db_charset)) {
            $error_list[] = WORDPATCH_DB_CHARSET_INVALID;
        }

        // Return our error list.
        return $error_list;
    }
}

if(!function_exists('wordpatch_wizard_validate_fs_creds')) {
    function wordpatch_wizard_validate_fs_creds($fs_method, $ftp_host, $ftp_user, $ftp_prikey, $ftp_pubkey, $ftp_pass)
    {
        // Create an empty array for our error list.
        $error_list = array();

        // Check if the filesystem method is valid, and if not append the error and return our list early.
        if (!wordpatch_is_valid_fs_method($fs_method)) {
            $error_list[] = WORDPATCH_INVALID_FS_METHOD;
            return $error_list;
        }

        // Check if the filesystem method is direct, and return early if so since there is nothing else to validate.
        if ($fs_method === wordpatch_fs_method_direct()) {
            return $error_list;
        }

        // Check if the host has been provided for FTP/SSH.
        if (!$ftp_host || trim($ftp_host) === '') {
            $error_list[] = WORDPATCH_FTP_HOST_REQUIRED;
        }

        // Check if the username has been provided for FTP/SSH.
        if (!$ftp_user || trim($ftp_user) === '') {
            $error_list[] = WORDPATCH_FTP_USER_REQUIRED;
        }

        // Perform specific validation for SSH...
        if ($fs_method === wordpatch_fs_method_ssh2()) {
            $has_prikey = $ftp_prikey && trim($ftp_prikey) !== '';
            $has_pubkey = $ftp_pubkey && trim($ftp_pubkey) !== '';
            $has_key = $has_pubkey && $has_prikey;

            if (!$has_key) {
                if ($has_prikey || $has_pubkey) {
                    $error_list[] = WORDPATCH_FTP_SSHKEY_INDECISIVE;
                } else if (!$ftp_pass || trim($ftp_pass) === '') {
                    $error_list[] = WORDPATCH_FTP_PASS_REQUIRED;
                }
            }

            return $error_list;
        }

        // If we got here, we need to validate the FTP password.
        if (!$ftp_pass || trim($ftp_pass) === '') {
            $error_list[] = WORDPATCH_FTP_PASS_REQUIRED;
        }

        // Return our error list.
        return $error_list;
    }
}

if(!function_exists('wordpatch_wizard_validate_fs_info')) {
    function wordpatch_wizard_validate_fs_info($fs_method, $fs_timeout, $fs_chmod_file_int, $fs_chmod_dir_int,
                $ftp_host, $ftp_user, $ftp_prikey, $ftp_pubkey, $ftp_pass) {
        // Init a variable for our error list.
        $error_list = array();

        // If the filesystem method specified is not valid, push the error and return our list early.
        if(!wordpatch_is_valid_fs_method($fs_method)) {
            $error_list[] = WORDPATCH_INVALID_FS_METHOD;
            return $error_list;
        }

        // Check for a valid timeout value.
        if(!$fs_timeout || $fs_timeout <= 0) {
            $error_list[] = WORDPATCH_INVALID_FS_TIMEOUT;
        }

        // Check for a valid chmod file value.
        if(!$fs_chmod_file_int || !wordpatch_is_valid_chmod_value($fs_chmod_file_int)) {
            $error_list[] = WORDPATCH_FS_CHMOD_FILE_REQUIRED;
        }

        // Check for a valid chmod directory value.
        if(!$fs_chmod_dir_int || !wordpatch_is_valid_chmod_value($fs_chmod_dir_int)) {
            $error_list[] = WORDPATCH_FS_CHMOD_DIR_REQUIRED;
        }

        // Validate the credentials next.
        $creds_errors = wordpatch_wizard_validate_fs_creds($fs_method, $ftp_host, $ftp_user, $ftp_prikey,
                $ftp_pubkey, $ftp_pass);

        // Extend the error list with the credentials error list.
        $error_list = wordpatch_extend_errors($error_list, $creds_errors);

        // Return our final list of errors.
        return $error_list;
    }
}

if(!function_exists('wordpatch_wizard_sanitize_trim')) {
    function wordpatch_wizard_sanitize_trim($contents) {
        if($contents === null) {
            return null;
        }

        return trim($contents);
    }
}

if(!function_exists('wordpatch_wizard_sanitize_activation_key')) {
    function wordpatch_wizard_sanitize_activation_key($contents) {
        if($contents === null) {
            return null;
        }

        // cap lengths of activation key to 20 after it has been sanitized
        $key = preg_replace("/[^a-z0-9]/i", '', strtolower(trim($contents)));

        if(strlen($key) > 20) {
            return substr($key, 0, 20);
        }

        return $key;
    }
}

if(!function_exists('wordpatch_wizard_sanitize_password')) {
    function wordpatch_wizard_sanitize_password($password) {
        if($password === null) {
            return null;
        }

        if(trim($password) === '') {
            return null;
        }

        return $password;
    }
}

if(!function_exists('wordpatch_wizard_sanitize_fs_method')) {
    function wordpatch_wizard_sanitize_fs_method($fs_method) {
        if(!wordpatch_is_valid_fs_method($fs_method)) {
            return null;
        }

        return $fs_method;
    }
}

if(!function_exists('wordpatch_wizard_sanitize_mailer')) {
    function wordpatch_wizard_sanitize_mailer($mailer) {
        if(!wordpatch_is_valid_mailer($mailer)) {
            return null;
        }

        return $mailer;
    }
}

if(!function_exists('wordpatch_wizard_sanitize_port')) {
    function wordpatch_wizard_sanitize_port($port) {
        $port = trim($port);
        $port = max(0, (int)$port);

        return $port;
    }
}

if(!function_exists('wordpatch_wizard_sanitize_email')) {
    function wordpatch_wizard_sanitize_email($email) {
        $email = trim($email);

        return $email;
    }
}

if(!function_exists('wordpatch_wizard_sanitize_smtp_auth')) {
    function wordpatch_wizard_sanitize_smtp_auth($smtp_auth) {
        if(!wordpatch_is_valid_smtp_auth($smtp_auth)) {
            return null;
        }

        return $smtp_auth;
    }
}

if(!function_exists('wordpatch_wizard_sanitize_smtp_ssl')) {
    function wordpatch_wizard_sanitize_smtp_ssl($smtp_ssl) {
        if(!wordpatch_is_valid_smtp_ssl($smtp_ssl)) {
            return null;
        }

        return $smtp_ssl;
    }
}

if(!function_exists('wordpatch_wizard_sanitize_collate')) {
    function wordpatch_wizard_sanitize_collate($collate) {
        if(!wordpatch_is_valid_collate($collate)) {
            return null;
        }

        return $collate;
    }
}

if(!function_exists('wordpatch_wizard_sanitize_charset')) {
    function wordpatch_wizard_sanitize_charset($charset) {
        if(!wordpatch_is_valid_charset($charset)) {
            return null;
        }

        return $charset;
    }
}

if(!function_exists('wordpatch_wizard_sanitize_ftp_ssl')) {
    function wordpatch_wizard_sanitize_ftp_ssl($ftp_ssl) {
        if(!wordpatch_is_valid_ftp_ssl($ftp_ssl)) {
            return null;
        }

        return $ftp_ssl;
    }
}

if(!function_exists('wordpatch_wizard_sanitize_fs_chmod_file')) {
    function wordpatch_wizard_sanitize_fs_chmod_file($fs_chmod_file) {
        if(trim($fs_chmod_file) === '') {
            return null;
        }

        $octal_val = base_convert($fs_chmod_file, 8, 10);
        return wordpatch_get_display_octal_from_int($octal_val);
    }
}

if(!function_exists('wordpatch_wizard_sanitize_fs_chmod_dir')) {
    function wordpatch_wizard_sanitize_fs_chmod_dir($fs_chmod_dir) {
        if(trim($fs_chmod_dir) === '') {
            return null;
        }

        $octal_val = base_convert($fs_chmod_dir, 8, 10);
        return wordpatch_get_display_octal_from_int($octal_val);
    }
}

if(!function_exists('wordpatch_wizard_sanitize_fs_timeout')) {
    function wordpatch_wizard_sanitize_fs_timeout($fs_timeout) {
        if(trim($fs_timeout) === '') {
            return null;
        }

        $fs_timeout = max(0, (int)$fs_timeout);
        return $fs_timeout;
    }
}

if(!function_exists('wordpatch_wizard_sanitize_unix_path')) {
    function wordpatch_wizard_sanitize_unix_path($unix_path) {
        if(trim($unix_path) === '') {
            return null;
        }

        return wordpatch_sanitize_absolute_unix_path($unix_path);
    }
}

if(!function_exists('wordpatch_wizard_sanitize_rescue_path')) {
    function wordpatch_wizard_sanitize_rescue_path($unix_path) {
        if(trim($unix_path) === '') {
            return null;
        }

        return wordpatch_sanitize_unix_file_path($unix_path);
    }
}

if(!function_exists('wordpatch_wizard_submit_type')) {
    function wordpatch_wizard_submit_type()
    {
        if(!isset($_POST['submit'])) {
            return null;
        }

        if($_POST['submit'] === WORDPATCH_SUBMIT_TYPE_NEXT) {
            return WORDPATCH_SUBMIT_TYPE_NEXT;
        }

        if($_POST['submit'] === WORDPATCH_SUBMIT_TYPE_PREVIOUS) {
            return WORDPATCH_SUBMIT_TYPE_PREVIOUS;
        }

        return null;
    }
}

if(!function_exists('wordpatch_wizard_persist_post_data')) {
    function wordpatch_wizard_persist_post_data($post_key, $new_value) {
        $_POST['wordpatch_' . $post_key] = $new_value;
    }
}

if(!function_exists('wordpatch_wizard_post_data')) {
    /**
     * @param $post_key
     * @param $guess_value
     * @param $existing_value
     * @param $wizard_sanitize_fn
     * @return mixed|null
     */
    function wordpatch_wizard_post_data($post_key, $guess_value, $existing_value, $wizard_sanitize_fn, $only_consider_posted) {
        $var_value = null;
        $update_post = false;

        if($only_consider_posted) {
            $guess_value = null;
            $existing_value = null;
        }

        if(isset($_POST['wordpatch_' . $post_key])) {
            $var_value = $_POST['wordpatch_' . $post_key];
            $update_post = true;
        } else if($existing_value !== null) {
            $var_value = $existing_value;
        } else {
            $var_value = $guess_value;
        }

        if($wizard_sanitize_fn !== null) {
            $var_value = call_user_func($wizard_sanitize_fn, $var_value);
        }

        if($update_post) {
            $_POST['wordpatch_' . $post_key] = $var_value;
        }

        return $var_value;
    }
}

if(!function_exists('wordpatch_the_hidden_wizard_vars')) {
    function wordpatch_the_hidden_wizard_vars($step_number, $vars_list) {
        $step_number = max(1, (int)$step_number);

        $hidden_html = "<input type=\"hidden\" name=\"step\" value=\"" . htmlspecialchars($step_number) . "\" />";
        $cur_step_name = 'step' . $step_number;

        foreach($vars_list as $step_name => $step_vars) {
            if($cur_step_name === $step_name) {
                continue;
            }

            foreach($step_vars as $step_var_single) {
                $raw_data = wordpatch_wizard_post_data($step_var_single, null, null, null, true);

                if($raw_data === null) {
                    continue;
                }

                $hidden_html .= "<input type=\"hidden\" name=\"wordpatch_" . htmlspecialchars($step_var_single) . "\" value=\"" . htmlspecialchars($raw_data) . "\" />";
            }
        }

        return $hidden_html;
    }
}

if(!function_exists('wordpatch_wizard_render_buttons')) {
    function wordpatch_wizard_render_buttons($wpenv_vars, $buttons) {
        $any_buttons = wordpatch_wizard_buttons_any($buttons);

        if(!$any_buttons) {
            return;
        }

        // TODO: If we make the Next button render after the Previous button (via HTML ordering) it will cause issues
        // when using the enter button. Use CSS to re-order instead then remove this TODO.
        ?>

        <div class="wordpatch_metabox">
            <div class="wordpatch_metabox_header"><?php echo(__wte($wpenv_vars, 'WIZARD_BUTTONS_TITLE')); ?></div>
            <div class="wordpatch_metabox_body">
                <?php if($buttons[WORDPATCH_SUBMIT_TYPE_PREVIOUS]) { ?>
                    <button type="submit" name="submit" class="wordpatch_button wordpatch_button_gray" value="<?php echo(htmlspecialchars(WORDPATCH_SUBMIT_TYPE_PREVIOUS)); ?>"><?php echo(__wten($wpenv_vars, 'PREVIOUS')); ?></button>
                <?php } ?>
                <?php if($buttons[WORDPATCH_SUBMIT_TYPE_NEXT]) { ?>
                    <button type="submit" name="submit" class="wordpatch_button wordpatch_button_blue" value="<?php echo(htmlspecialchars(WORDPATCH_SUBMIT_TYPE_NEXT)); ?>"><?php echo(__wten($wpenv_vars, 'NEXT')); ?></button>
                <?php } ?>
            </div>
        </div>
        <?php
    }
}

if(!function_exists('wordpatch_wizard_draw_field_open')) {
    function wordpatch_wizard_draw_field_open($wpenv_vars, $input_key, $header_text, $desc_text, $label_text, $has_error, $flex_label_text = null)
    {
        // Render the inputbox open element.
        wordpatch_inputbox_render_open($wpenv_vars);

        // Render the inputbox header open element.
        wordpatch_inputbox_header_render_open($wpenv_vars);

        // Render the header text
        echo(htmlspecialchars($header_text));

        // Render the inputbox header close element.
        wordpatch_inputbox_header_render_close($wpenv_vars);

        // Render the inputbox body open element.
        wordpatch_inputbox_body_render_open($wpenv_vars, $has_error);

        // Render the inputbox description open element.
        wordpatch_inputbox_desc_render_open($wpenv_vars);

        // Render the description text
        echo(nl2br(htmlspecialchars($desc_text)));

        // Render the inputbox description close element.
        wordpatch_inputbox_desc_render_close($wpenv_vars);

        // Render the label open element.
        wordpatch_label_render_open($wpenv_vars, $input_key);

        // Render the label text
        echo(htmlspecialchars($label_text));

        // Render the label close element.
        wordpatch_label_render_close($wpenv_vars);

        // Render the input flex open element.
        wordpatch_inputflex_render_open($wpenv_vars);

        // Calculate our flex label. It can be empty.
        $flex_label_text = $flex_label_text === null ? '' : trim($flex_label_text);

        if($flex_label_text !== '') {
            // Render the flex label open element.
            wordpatch_label_render_open($wpenv_vars, $input_key, [ 'wordpatch_flex_label' ]);

            // Render the label text
            echo(htmlspecialchars($flex_label_text));

            // Render the flex label close element.
            wordpatch_label_render_close($wpenv_vars);
        }

        // Render the flexme open element.
        wordpatch_flexme_render_open($wpenv_vars);
    }
}

if(!function_exists('wordpatch_wizard_draw_field_close')) {
    function wordpatch_wizard_draw_field_close($wpenv_vars, $where, $field_errors, $error_vars, $wizard_error_list)
    {
        // Render the flexme close element.
        wordpatch_flexme_render_close($wpenv_vars);

        // Render the input flex close element.
        wordpatch_inputflex_render_close($wpenv_vars);

        // Draw the field errors for this.
        wordpatch_wizard_inputbox_draw_field_errors($wpenv_vars, $where, $field_errors, $error_vars, $wizard_error_list);

        // Render the inputbox body close element.
        wordpatch_inputbox_body_render_close($wpenv_vars);

        // Render the inputbox close element
        wordpatch_inputbox_render_close($wpenv_vars);
    }
}

if(!function_exists('wordpatch_wizard_inputbox_draw_field_errors')) {
    /**
     * @param $wpenv_vars
     * @param $where
     * @param $field_errors
     * @param $error_vars
     * @param $wizard_error_list
     */
    function wordpatch_wizard_inputbox_draw_field_errors($wpenv_vars, $where, $field_errors, $error_vars, $wizard_error_list)
    {
        $has_error = wordpatch_errors_are_blocking($wizard_error_list, $field_errors);

        if(!$has_error) {
            return;
        }

        ?>
        <div class="wordpatch_inputbox_errors">
        <?php

        // Loop through each potential field error and cross check wizard error list.
        foreach ($field_errors as $field_error) {
            // If the field error is not in our error list, skip this.
            if (!in_array($field_error, $wizard_error_list)) {
                continue;
            }

            // Since we found an error we care about, render the inputbox error open element.
            wordpatch_inputbox_err_render_open($wpenv_vars);

            $error_trans = wordpatch_translate_error($wpenv_vars, $field_error, $where, $error_vars);

            $error_disp = __wt($wpenv_vars, 'ERROR_FORMAT', $error_trans);

            echo(htmlspecialchars($error_disp));

            // Render the inputbox error close element.
            wordpatch_inputbox_err_render_close($wpenv_vars);
        }

        ?>
        </div>
        <?php
    }
}

if(!function_exists('wordpatch_wizard_draw_field_text')) {
    function wordpatch_wizard_draw_field_text($wpenv_vars, $input_key, $current_display_value, $header_text,
                    $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list, $flex_label_text = null) {
        $has_error = wordpatch_errors_are_blocking($wizard_error_list, $field_errors);

        // Draw the field opening tags.
        wordpatch_wizard_draw_field_open($wpenv_vars, $input_key, $header_text, $desc_text, $label_text, $has_error, $flex_label_text);

        // Render the input text element.
        wordpatch_input_text_render($wpenv_vars, $input_key, $current_display_value);

        // Draw the field closing tags.
        wordpatch_wizard_draw_field_close($wpenv_vars, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_wizard_draw_field_password')) {
    function wordpatch_wizard_draw_field_password($wpenv_vars, $input_key, $header_text,
                    $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list, $flex_label_text = null) {
        $has_error = wordpatch_errors_are_blocking($wizard_error_list, $field_errors);

        // Draw the field opening tags.
        wordpatch_wizard_draw_field_open($wpenv_vars, $input_key, $header_text, $desc_text, $label_text, $has_error, $flex_label_text);

        // Render the input text element.
        wordpatch_input_password_render($wpenv_vars, $input_key);

        // Draw the field closing tags.
        wordpatch_wizard_draw_field_close($wpenv_vars, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_wizard_draw_field_select')) {
    function wordpatch_wizard_draw_field_select($wpenv_vars, $input_key, $current_value, $dropdown, $header_text,
                                              $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list) {
        $has_error = wordpatch_errors_are_blocking($wizard_error_list, $field_errors);

        // Draw the field opening tags.
        wordpatch_wizard_draw_field_open($wpenv_vars, $input_key, $header_text, $desc_text, $label_text, $has_error);

        // Render the input select open element.
        wordpatch_input_select_render_open($wpenv_vars, $input_key);

        // Render each of the options.
        foreach($dropdown as $dropdown_value => $dropdown_display) {
            $dropdown_selected = $current_value === $dropdown_value;
            wordpatch_input_option_render($wpenv_vars, $dropdown_value, $dropdown_display, $dropdown_selected);
        }

        // Render the input select close element.
        wordpatch_input_select_render_close($wpenv_vars);

        // Draw the field closing tags.
        wordpatch_wizard_draw_field_close($wpenv_vars, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_sanitize_step_number')) {
    function wordpatch_sanitize_step_number($step_number, $max_steps) {
        $step_number = max(1, (int)$step_number);
        $step_number = min($max_steps, $step_number);

        return $step_number;
    }
}

if(!function_exists('wordpatch_the_step_number')) {
    function wordpatch_the_step_number($max_steps) {
        $step_number = isset($_POST['step']) ? $_POST['step'] : 1;
        $step_number = wordpatch_sanitize_step_number($step_number, $max_steps);

        return $step_number;
    }
}

if(!function_exists('wordpatch_goto_next_step')) {
    function wordpatch_goto_next_step($step_number) {
        $_POST['step'] = $step_number + 1;

        return $step_number + 1;
    }
}

if(!function_exists('wordpatch_goto_previous_step')) {
    function wordpatch_goto_previous_step($step_number) {
        $new_step_number = max(1, (int)($step_number - 1));
        $_POST['step'] = $new_step_number;

        return $new_step_number;
    }
}