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
 * The following functions guess field values for each step of the mail configuration wizard.
 * These functions are called from `wordpatch_configmail_post_vars`.
 */

if(!function_exists('wordpatch_configmail_guess_mailer')) {
    /**
     * Guess the best mailer value to be used for the mail configuration wizard.
     *
     * @param $wpenv_vars
     * @return mixed|string
     */
    function wordpatch_configmail_guess_mailer($wpenv_vars)
    {
        $wpms_on = wordpatch_configmail_wpms_on($wpenv_vars);

        if($wpms_on) {
            if (defined('WPMS_MAILER') && trim(constant('WPMS_MAILER')) !== '') {
                $wpms_mailer = trim(strtolower(constant('WPMS_MAILER')));

                if (wordpatch_is_valid_mailer($wpms_mailer)) {
                    return $wpms_mailer;
                }
            }
        } else {
            $wpms_mailer_opt = wordpatch_get_option($wpenv_vars, 'mailer');

            if($wpms_mailer_opt && trim($wpms_mailer_opt) !== '') {
                $wpms_mailer = trim(strtolower($wpms_mailer_opt));

                if(wordpatch_is_valid_mailer($wpms_mailer)) {
                    return $wpms_mailer;
                }
            }
        }

        $swpsmtp_options = wordpatch_configmail_swpsmtp_options($wpenv_vars);

        if($swpsmtp_options && isset($swpsmtp_options['smtp_settings']) && isset($swpsmtp_options['smtp_settings']['host']) &&
            trim($swpsmtp_options['smtp_settings']['host']) !== '') {
            return wordpatch_mailer_smtp();
        }

        return wordpatch_mailer_mail();
    }
}

if(!function_exists('wordpatch_configmail_guess_mail_to')) {
    /**
     * Guess the best mail to value to be used for the mail configuration wizard.
     *
     * @param $wpenv_vars
     * @return mixed|string
     */
    function wordpatch_configmail_guess_mail_to($wpenv_vars)
    {
        return '';
    }
}

if(!function_exists('wordpatch_configmail_guess_mail_from')) {
    /**
     * Guess the best mail from value to be used for the mail configuration wizard.
     *
     * @param $wpenv_vars
     * @return mixed|string
     */
    function wordpatch_configmail_guess_mail_from($wpenv_vars)
    {
        $wpms_on = wordpatch_configmail_wpms_on($wpenv_vars);

        if($wpms_on) {
            if (defined('WPMS_MAIL_FROM') && trim(constant('WPMS_MAIL_FROM')) !== '') {
                return trim(constant('WPMS_MAIL_FROM'));
            }
        } else {
            $wpms_mail_from_opt = wordpatch_get_option($wpenv_vars, 'mail_from');

            if($wpms_mail_from_opt && trim($wpms_mail_from_opt) !== '') {
                return trim($wpms_mail_from_opt);
            }
        }

        $swpsmtp_options = wordpatch_configmail_swpsmtp_options($wpenv_vars);

        if($swpsmtp_options && isset($swpsmtp_options['from_email_field']) && trim($swpsmtp_options['from_email_field']) !== '') {
            return trim($swpsmtp_options['from_email_field']);
        }

        $sitename = strtolower($_SERVER['SERVER_NAME']);
        if(substr($sitename, 0, 4) === 'www.') {
            $sitename = substr($sitename, 4);
        }

        return 'wordpatch@' . $sitename;
    }
}

if(!function_exists('wordpatch_configmail_guess_smtp_host')) {
    /**
     * Guess the best SMTP host value to be used for the mail configuration wizard.
     *
     * @param $wpenv_vars
     * @return mixed|string
     */
    function wordpatch_configmail_guess_smtp_host($wpenv_vars)
    {
        $wpms_on = wordpatch_configmail_wpms_on($wpenv_vars);

        if($wpms_on) {
            if (defined('WPMS_SMTP_HOST') && trim(constant('WPMS_SMTP_HOST')) !== '') {
                return trim(constant('WPMS_SMTP_HOST'));
            }
        } else {
            $wpms_smtp_host_opt = wordpatch_get_option($wpenv_vars, 'smtp_host');

            if($wpms_smtp_host_opt && trim($wpms_smtp_host_opt) !== '') {
                return trim($wpms_smtp_host_opt);
            }
        }

        $swpsmtp_options = wordpatch_configmail_swpsmtp_options($wpenv_vars);

        if($swpsmtp_options && isset($swpsmtp_options['smtp_settings']) && isset($swpsmtp_options['smtp_settings']['host']) &&
            trim($swpsmtp_options['smtp_settings']['host']) !== '') {
            return trim($swpsmtp_options['smtp_settings']['host']);
        }

        return 'localhost';
    }
}

if(!function_exists('wordpatch_configmail_guess_smtp_port')) {
    /**
     * Guess the best SMTP port value to be used for the mail configuration wizard.
     *
     * @param $wpenv_vars
     * @return mixed|string
     */
    function wordpatch_configmail_guess_smtp_port($wpenv_vars)
    {
        $wpms_on = wordpatch_configmail_wpms_on($wpenv_vars);

        if($wpms_on) {
            if (defined('WPMS_SMTP_PORT') && trim(constant('WPMS_SMTP_PORT')) !== '' &&
                is_numeric(constant('WPMS_SMTP_PORT')) && max(0, (int)constant('WPMS_SMTP_PORT')) > 0) {
                return (int)constant('WPMS_SMTP_PORT');
            }
        } else {
            $wpms_smtp_port_opt = wordpatch_get_option($wpenv_vars, 'smtp_port');

            if($wpms_smtp_port_opt && trim($wpms_smtp_port_opt) !== '' &&
                is_numeric($wpms_smtp_port_opt) && max(0, (int)$wpms_smtp_port_opt) > 0) {
                return (int)$wpms_smtp_port_opt;
            }
        }

        $swpsmtp_options = wordpatch_configmail_swpsmtp_options($wpenv_vars);

        if($swpsmtp_options && isset($swpsmtp_options['smtp_settings']) && isset($swpsmtp_options['smtp_settings']['port']) &&
            trim($swpsmtp_options['smtp_settings']['port']) !== '' && is_numeric($swpsmtp_options['smtp_settings']['port']) &&
            max(0, (int)$swpsmtp_options['smtp_settings']['port']) > 0) {
            return (int)$swpsmtp_options['smtp_settings']['port'];
        }

        return 25;
    }
}

if(!function_exists('wordpatch_configmail_guess_smtp_ssl')) {
    /**
     * Guess the best SMTP SSL value to be used for the mail configuration wizard.
     *
     * @param $wpenv_vars
     * @return mixed|string
     */
    function wordpatch_configmail_guess_smtp_ssl($wpenv_vars)
    {
        $wpms_on = wordpatch_configmail_wpms_on($wpenv_vars);

        if($wpms_on) {
            if (defined('WPMS_SMTP_SSL') && trim(constant('WPMS_SMTP_SSL')) !== '') {
                $wpms_smtp_ssl = trim(strtolower(constant('WPMS_SMTP_SSL')));

                if ($wpms_smtp_ssl === '') {
                    $wpms_smtp_ssl = 'none';
                }

                if (wordpatch_is_valid_smtp_ssl($wpms_smtp_ssl)) {
                    return $wpms_smtp_ssl;
                }
            }
        } else {
            $wpms_smtp_ssl_opt = wordpatch_get_option($wpenv_vars, 'smtp_ssl');

            if ($wpms_smtp_ssl_opt && trim($wpms_smtp_ssl_opt) !== '') {
                $wpms_smtp_ssl = trim(strtolower($wpms_smtp_ssl_opt));

                if ($wpms_smtp_ssl === '') {
                    $wpms_smtp_ssl = 'none';
                }

                if (wordpatch_is_valid_smtp_ssl($wpms_smtp_ssl)) {
                    return $wpms_smtp_ssl;
                }
            }
        }

        $swpsmtp_options = wordpatch_configmail_swpsmtp_options($wpenv_vars);

        if($swpsmtp_options && isset($swpsmtp_options['smtp_settings']) && isset($swpsmtp_options['smtp_settings']['type_encryption']) &&
            trim($swpsmtp_options['smtp_settings']['type_encryption']) !== '') {
            $swpsmtp_smtp_ssl = trim(strtolower($swpsmtp_options['smtp_settings']['type_encryption']));

            if($swpsmtp_smtp_ssl === '') {
                $swpsmtp_smtp_ssl = 'none';
            }

            if(wordpatch_is_valid_smtp_ssl($swpsmtp_smtp_ssl)) {
                return $swpsmtp_smtp_ssl;
            }
        }

        return wordpatch_smtp_ssl_none();
    }
}

if(!function_exists('wordpatch_configmail_guess_smtp_auth')) {
    /**
     * Guess the best SMTP authentication value to be used for the mail configuration wizard.
     *
     * @param $wpenv_vars
     * @return mixed|string
     */
    function wordpatch_configmail_guess_smtp_auth($wpenv_vars)
    {
        $wpms_on = wordpatch_configmail_wpms_on($wpenv_vars);

        if($wpms_on) {
            if (defined('WPMS_SMTP_AUTH') && (is_bool(constant('WPMS_SMTP_AUTH')) || is_numeric(constant('WPMS_SMTP_AUTH')) ||
                    (is_string(constant('WPMS_SMTP_AUTH')) && trim(constant('WPMS_SMTP_AUTH')) !== ''))) {
                $wpms_smtp_auth = (is_bool(constant('WPMS_SMTP_AUTH')) || is_numeric(constant('WPMS_SMTP_AUTH'))) ?
                    ((bool)constant('WPMS_SMTP_AUTH') ? wordpatch_smtp_auth_yes() : wordpatch_smtp_auth_no()) :
                    trim(strtolower(constant('WPMS_SMTP_AUTH')));

                $wpms_smtp_auth = $wpms_smtp_auth === 'true' ? wordpatch_smtp_auth_yes() : $wpms_smtp_auth;
                $wpms_smtp_auth = $wpms_smtp_auth === 'false' ? wordpatch_smtp_auth_no() : $wpms_smtp_auth;

                if (wordpatch_is_valid_smtp_auth($wpms_smtp_auth)) {
                    return $wpms_smtp_auth;
                }
            }
        } else {
            $wpms_smtp_auth_opt = wordpatch_get_option($wpenv_vars, 'smtp_auth');

            if ($wpms_smtp_auth_opt && (is_bool($wpms_smtp_auth_opt) || is_numeric($wpms_smtp_auth_opt) ||
                    (is_string($wpms_smtp_auth_opt) && trim($wpms_smtp_auth_opt) !== ''))) {
                $wpms_smtp_auth = (is_bool($wpms_smtp_auth_opt) || is_numeric($wpms_smtp_auth_opt)) ?
                    ((bool)$wpms_smtp_auth_opt ? wordpatch_smtp_auth_yes() : wordpatch_smtp_auth_no()) :
                    trim(strtolower($wpms_smtp_auth_opt));

                $wpms_smtp_auth = $wpms_smtp_auth === 'true' ? wordpatch_smtp_auth_yes() : $wpms_smtp_auth;
                $wpms_smtp_auth = $wpms_smtp_auth === 'false' ? wordpatch_smtp_auth_no() : $wpms_smtp_auth;

                if(wordpatch_is_valid_smtp_auth($wpms_smtp_auth)) {
                    return $wpms_smtp_auth;
                }
            }
        }

        $swpsmtp_options = wordpatch_configmail_swpsmtp_options($wpenv_vars);

        if($swpsmtp_options && isset($swpsmtp_options['smtp_settings']) && isset($swpsmtp_options['smtp_settings']['autentication']) &&
            (is_bool($swpsmtp_options['smtp_settings']['autentication']) || is_numeric($swpsmtp_options['smtp_settings']['autentication']) ||
                (is_string($swpsmtp_options['smtp_settings']['autentication']) && trim($swpsmtp_options['smtp_settings']['autentication']) !== ''))) {
            $swpsmtp_smtp_auth = (is_bool($swpsmtp_options['smtp_settings']['autentication']) || is_numeric($swpsmtp_options['smtp_settings']['autentication'])) ?
                ((bool)$swpsmtp_options['smtp_settings']['autentication'] ? wordpatch_smtp_auth_yes() : wordpatch_smtp_auth_no()) :
                trim(strtolower($swpsmtp_options['smtp_settings']['autentication']));

            $swpsmtp_smtp_auth = $swpsmtp_smtp_auth === 'true' ? wordpatch_smtp_auth_yes() : $swpsmtp_smtp_auth;
            $swpsmtp_smtp_auth = $swpsmtp_smtp_auth === 'false' ? wordpatch_smtp_auth_no() : $swpsmtp_smtp_auth;

            if(wordpatch_is_valid_smtp_auth($swpsmtp_smtp_auth)) {
                return $swpsmtp_smtp_auth;
            }
        }

        return wordpatch_smtp_auth_no();
    }
}

if(!function_exists('wordpatch_configmail_guess_smtp_user')) {
    /**
     * Guess the best SMTP username value to be used for the mail configuration wizard.
     *
     * @param $wpenv_vars
     * @return mixed|string
     */
    function wordpatch_configmail_guess_smtp_user($wpenv_vars)
    {
        $wpms_on = wordpatch_configmail_wpms_on($wpenv_vars);

        if($wpms_on) {
            if (defined('WPMS_SMTP_USER') && trim(constant('WPMS_SMTP_USER')) !== '') {
                return trim(constant('WPMS_SMTP_USER'));
            }
        } else {
            $wpms_smtp_user_opt = wordpatch_get_option($wpenv_vars, 'smtp_user');

            if($wpms_smtp_user_opt && trim($wpms_smtp_user_opt) !== '') {
                return trim($wpms_smtp_user_opt);
            }
        }

        $swpsmtp_options = wordpatch_configmail_swpsmtp_options($wpenv_vars);

        if($swpsmtp_options && isset($swpsmtp_options['smtp_settings']) && isset($swpsmtp_options['smtp_settings']['username']) &&
            trim($swpsmtp_options['smtp_settings']['username']) !== '') {
            return trim($swpsmtp_options['smtp_settings']['username']);
        }

        return '';
    }
}

if(!function_exists('wordpatch_configmail_guess_smtp_pass')) {
    /**
     * Guess the best SMTP password value to be used for the mail configuration wizard.
     *
     * @param $wpenv_vars
     * @return mixed|string
     */
    function wordpatch_configmail_guess_smtp_pass($wpenv_vars)
    {
        $wpms_on = wordpatch_configmail_wpms_on($wpenv_vars);

        if($wpms_on) {
            if (defined('WPMS_SMTP_PASS') && trim(constant('WPMS_SMTP_PASS')) !== '') {
                return constant('WPMS_SMTP_PASS');
            }
        } else {
            $wpms_smtp_pass_opt = wordpatch_get_option($wpenv_vars, 'smtp_pass');

            if($wpms_smtp_pass_opt && trim($wpms_smtp_pass_opt) !== '') {
                return $wpms_smtp_pass_opt;
            }
        }

        $swpsmtp_options = wordpatch_configmail_swpsmtp_options($wpenv_vars);

        if($swpsmtp_options && isset($swpsmtp_options['smtp_settings']) && isset($swpsmtp_options['smtp_settings']['password']) &&
            trim($swpsmtp_options['smtp_settings']['password']) !== '') {
            return $swpsmtp_options['smtp_settings']['password'];
        }

        return '';
    }
}

if(!function_exists('wordpatch_configmail_wpms_on')) {
    /**
     * WPMS detection support helper.
     *
     * @param $wpenv_vars
     * @return bool
     */
    function wordpatch_configmail_wpms_on($wpenv_vars)
    {
        $wpms_on = false;

        if(defined('WPMS_ON') && constant('WPMS_ON')) {
            $wpms_on = true;
        }

        return $wpms_on;
    }
}

if(!function_exists('wordpatch_configmail_swpsmtp_password_decode')) {
    /**
     * SWPSMTP detection support helper.
     *
     * @param $password_enc
     * @return bool|string
     * @adapted swpsmtp_get_password
     */
    function wordpatch_configmail_swpsmtp_password_decode($password_enc)
    {
        $temp_password = $password_enc;
        $password = "";

        $decoded_pass = base64_decode($temp_password);
        /* no additional checks for servers that aren't configured with mbstring enabled */
        if(!function_exists('mb_detect_encoding')) {
            return $decoded_pass;
        }
        /* end of mbstring check */
        if (base64_encode($decoded_pass) === $temp_password) {  //it might be encoded
            if (false === mb_detect_encoding($decoded_pass)) {  //could not find character encoding.
                $password = $temp_password;
            } else {
                $password = base64_decode($temp_password);
            }
        } else { //not encoded
            $password = $temp_password;
        }
        return $password;
    }
}

if(!function_exists('wordpatch_configmail_swpsmtp_options')) {
    /**
     * SWPSMTP detection support helper.
     *
     * @param $wpenv_vars
     * @return array|null
     */
    function wordpatch_configmail_swpsmtp_options($wpenv_vars)
    {
        $swpsmtp_options = wordpatch_get_option($wpenv_vars, 'swpsmtp_options');

        // if for some reason $swpsmtp_options is an array, let's make it an object
        if(!$swpsmtp_options) {
            return null;
        }

        if(!is_array($swpsmtp_options)) {
            $swpsmtp_options = (array)$swpsmtp_options;
        }

        // we actually only care about a few options, so let's be selective.
        // the typo in "autentication" is deliberate
        $from_email_field = isset($swpsmtp_options['from_email_field']) ? $swpsmtp_options['from_email_field'] : '';
        $from_name_field = isset($swpsmtp_options['from_name_field']) ? $swpsmtp_options['from_name_field'] : '';

        $smtp_host = (isset($swpsmtp_options['smtp_settings']) && isset($swpsmtp_options['smtp_settings']['host'])) ?
            $swpsmtp_options['smtp_settings']['host'] : '';

        // valid values are: none,ssl,tls
        $smtp_type_enc = (isset($swpsmtp_options['smtp_settings']) && isset($swpsmtp_options['smtp_settings']['type_encryption'])) ?
            strtolower($swpsmtp_options['smtp_settings']['type_encryption']) : '';
        if(!in_array($smtp_type_enc, array('none', 'ssl', 'tls'))) {
            $smtp_type_enc = 'none';
        }

        $smtp_port = (isset($swpsmtp_options['smtp_settings']) && isset($swpsmtp_options['smtp_settings']['port'])) ?
            $swpsmtp_options['smtp_settings']['port'] : '';

        $smtp_auth = (isset($swpsmtp_options['smtp_settings']) && isset($swpsmtp_options['smtp_settings']['autentication'])) ?
            $swpsmtp_options['smtp_settings']['autentication'] : '';

        $smtp_user = (isset($swpsmtp_options['smtp_settings']) && isset($swpsmtp_options['smtp_settings']['username'])) ?
            $swpsmtp_options['smtp_settings']['username'] : '';

        $smtp_pass = (isset($swpsmtp_options['smtp_settings']) && isset($swpsmtp_options['smtp_settings']['password'])) ?
            wordpatch_swpsmtp_password_decode($swpsmtp_options['smtp_settings']['password']) : '';

        return array(
            'from_email_field' => $from_email_field,
            'from_name_field' => $from_name_field,
            'smtp_settings' => array(
                'host' => $smtp_host,
                'type_encryption' => $smtp_type_enc,
                'port' => $smtp_port,
                'autentication' => $smtp_auth,
                'username' => $smtp_user,
                'password' => $smtp_pass,
            )
        );
    }
}