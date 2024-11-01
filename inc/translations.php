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
 * Implements translation support for WordPatch.
 */

if(!function_exists('wordpatch_languages')) {
    /**
     * Calculates list of valid languages for WordPatch.
     *
     * @return array
     */
    function wordpatch_languages() {
        return array(
            wordpatch_language_english()
        );
    };
}

if(!function_exists('wordpatch_display_language')) {
    /**
     * Calculate the display name of a language.
     *
     * @param $language
     * @return mixed|null
     */
    function wordpatch_display_language($wpenv_vars, $language) {
        if(!wordpatch_is_valid_language($language)) {
            return null;
        }

        return call_user_func("wordpatch_display_language_$language");
    }
}

if(!function_exists('wordpatch_calculate_language')) {
    function wordpatch_calculate_language($wpenv_vars)
    {
        $language = wordpatch_get_option($wpenv_vars, 'wordpatch_language', '');

        if(wordpatch_is_valid_language($language)) {
            return $language;
        }


        return null;
    }
}

if(!isset($__wordpatch_phrases)) {
    /**
     * Global array used to cache the result of `wordpatch_phrases`.
     */
    $__wordpatch_phrases = array();
}

if(!function_exists('wordpatch_phrases')) {
    /**
     * Calculate the phrases for a specific language. Will return from the cache if called with the same language twice.
     *
     * @param $language
     * @return null|array
     */
    function wordpatch_phrases($language) {
        global $__wordpatch_phrases;

        if(!wordpatch_is_valid_language($language)) {
            return null;
        }

        // Return the cached result if possible.
        if(isset($__wordpatch_phrases[$language])) {
            return $__wordpatch_phrases[$language];
        }

        $__wordpatch_phrases[$language] = call_user_func("wordpatch_phrases_$language");
        return $__wordpatch_phrases[$language];
    }
}

if(!function_exists('wordpatch_default_language')) {
    /**
     * Determine the best language to fallback to if the current language has not been set.
     *
     * @return string
     */
    function wordpatch_default_language() {
        return wordpatch_language_english();
    }
}

if(!function_exists('wordpatch_is_valid_language')) {
    /**
     * Determine if a language is valid.
     *
     * @param $language
     * @return bool
     */
    function wordpatch_is_valid_language($language) {
        return in_array($language, wordpatch_languages());
    }
}

if(!isset($__wordpatch_language)) {
    /**
     * Global variable used to cache the result of `wordpatch_current_language`.
     */
    $__wordpatch_language = null;
}

if(!function_exists('wordpatch_current_language')) {
    /**
     * Get or set the current language being used for WordPatch.
     * If you pass null as $language, you will invoke the getter.
     * If you decide to invoke the setter, the old language will be returned.
     *
     * @return null|string
     */
    function wordpatch_current_language($wpenv_vars, $language = null) {
        global $__wordpatch_language;

        // If $language is null, then handle the getter.
        if($language === null) {
            // Return from the cache if it is not null.
            if($__wordpatch_language !== null) {
                return $__wordpatch_language;
            }

            // Calculate the language.
            $__wordpatch_language = wordpatch_calculate_language($wpenv_vars);

            // If the language is null for whatever reason, use the default language.
            if($__wordpatch_language === null) {
                $__wordpatch_language = wordpatch_default_language();
            }

            // Return our calculated result.
            return $__wordpatch_language;
        }

        // Otherwise handle the setter and return the old language value.
        $old_language = $__wordpatch_language;
        $__wordpatch_language = $language;
        return $old_language;
    };
}

if(!function_exists('wordpatch_translate_error')) {
    /**
     * WordPatch error translation function. You probably do not need to call this directly. See `errors.php` for helpers.
     *
     * @param $wpenv_vars
     * @param $key
     * @param $where
     * @param array $vars
     * @return null|string
     */
    function wordpatch_translate_error($wpenv_vars, $key, $where, $vars) {
        // Call the language-specific function for translation.
        $translation = wordpatch_translate_error_master($wpenv_vars, $key, $where, $vars);

        // TODO: Remove this in production.
        if($translation === null) {
            return 'TRANSLATION_MISSING_' . $key . '@' . $where;
        }

        return $translation;
    }
}

if(!function_exists('wordpatch_translate_error_master')) {
    /**
     * Translates an error to the appropriate localization.
     *
     * @param $wpenv_vars
     * @param $key
     * @param $where
     * @param array $vars
     * @return null|string
     */
    function wordpatch_translate_error_master($wpenv_vars, $key, $where, $vars = array()) {
        $possibilities = array(
            WORDPATCH_JOB_TITLE_REQUIRED => __wt($wpenv_vars, 'ERROR_JOB_TITLE_REQUIRED'),
            WORDPATCH_INVALID_RESCUE_PATH => __wt($wpenv_vars, 'ERROR_INVALID_RESCUE_PATH'),
            WORDPATCH_INVALID_RESCUE_FORMAT => __wt($wpenv_vars, 'ERROR_INVALID_RESCUE_FORMAT'),
            WORDPATCH_INVALID_LOCATION => __wt($wpenv_vars, 'ERROR_INVALID_LOCATION'),
            WORDPATCH_FILE_MISSING => __wt($wpenv_vars, 'ERROR_FILE_MISSING'),
            WORDPATCH_INVALID_RESCUE_FILENAME => __wt($wpenv_vars, 'ERROR_INVALID_RESCUE_FILENAME'),
            WORDPATCH_INVALID_RESCUE_EXTENSION => __wt($wpenv_vars, 'ERROR_INVALID_RESCUE_EXTENSION'),
            WORDPATCH_INVALID_RESCUE_DIRECTORY => __wt($wpenv_vars, 'ERROR_INVALID_RESCUE_DIRECTORY'),
            WORDPATCH_UNKNOWN_ERROR => __wt($wpenv_vars, 'ERROR_UNKNOWN_ERROR'),
            WORDPATCH_UNKNOWN_HTTP_ERROR => __wt($wpenv_vars, 'ERROR_UNKNOWN_HTTP_ERROR'),
            WORDPATCH_UNAUTHORIZED_HTTP_ERROR => __wt($wpenv_vars, 'ERROR_UNAUTHORIZED_HTTP_ERROR'),
            WORDPATCH_PATCH_TITLE_REQUIRED => __wt($wpenv_vars, 'ERROR_PATCH_TITLE_REQUIRED'),
            WORDPATCH_PATCH_PATH_REQUIRED => __wt($wpenv_vars, 'ERROR_PATCH_PATH_REQUIRED'),
            WORDPATCH_PATCH_CHANGES_REQUIRED => __wt($wpenv_vars, 'ERROR_PATCH_CHANGES_REQUIRED'),
            WORDPATCH_PATCH_DATA_REQUIRED => __wt($wpenv_vars, 'ERROR_PATCH_DATA_REQUIRED'),
            WORDPATCH_PATCH_FILE_REQUIRED => __wt($wpenv_vars, 'ERROR_PATCH_FILE_REQUIRED'),
            WORDPATCH_FILESYSTEM_FAILED_WRITE => __wt($wpenv_vars, 'ERROR_FILESYSTEM_FAILED_WRITE'),
            WORDPATCH_FILESYSTEM_FAILED_DELETE => __wt($wpenv_vars, 'ERROR_FILESYSTEM_FAILED_DELETE'),
            WORDPATCH_FILESYSTEM_FAILED_DELETE_DIR => __wt($wpenv_vars, 'ERROR_FILESYSTEM_FAILED_DELETE_DIR'),
            WORDPATCH_FILESYSTEM_FAILED_VERIFY_DIR => __wt($wpenv_vars, 'ERROR_FILESYSTEM_FAILED_VERIFY_DIR'),
            WORDPATCH_FILESYSTEM_FAILED_WRITE_DIR => __wt($wpenv_vars, 'ERROR_FILESYSTEM_FAILED_WRITE_DIR'),
            WORDPATCH_FILESYSTEM_FAILED_READ => __wt($wpenv_vars, 'ERROR_FILESYSTEM_FAILED_READ'),
            WORDPATCH_FILESYSTEM_FILE_IS_DIR => __wt($wpenv_vars, 'ERROR_FILESYSTEM_FILE_IS_DIR'),
            WORDPATCH_INVALID_CREDENTIALS => __wt($wpenv_vars, 'ERROR_INVALID_CREDENTIALS'),
            WORDPATCH_INVALID_FILESYSTEM_CREDENTIALS => __wt($wpenv_vars, 'ERROR_INVALID_FILESYSTEM_CREDENTIALS'),
            WORDPATCH_INVALID_DATABASE_CREDENTIALS => __wt($wpenv_vars, 'ERROR_INVALID_DATABASE_CREDENTIALS'),
            WORDPATCH_WRONG_DATABASE_CREDENTIALS => __wt($wpenv_vars, 'ERROR_WRONG_DATABASE_CREDENTIALS'),
            WORDPATCH_FS_PERMISSIONS_ERROR => __wt($wpenv_vars, 'ERROR_FS_PERMISSIONS_ERROR'),
            WORDPATCH_WRONG_FILESYSTEM_CREDENTIALS => __wt($wpenv_vars, 'ERROR_WRONG_FILESYSTEM_CREDENTIALS'),
            WORDPATCH_FTP_BASE_REQUIRED => __wt($wpenv_vars, 'ERROR_FTP_BASE_REQUIRED'),
            WORDPATCH_FTP_CONTENT_DIR_REQUIRED => __wt($wpenv_vars, 'ERROR_FTP_CONTENT_DIR_REQUIRED'),
            WORDPATCH_FTP_PLUGIN_DIR_REQUIRED => __wt($wpenv_vars, 'ERROR_FTP_PLUGIN_DIR_REQUIRED'),
            WORDPATCH_JOB_WILL_RETRY => __wt($wpenv_vars, 'ERROR_JOB_WILL_RETRY'),
            WORDPATCH_REJECTS_IN_QUEUE => __wt($wpenv_vars, 'ERROR_REJECTS_IN_QUEUE'),
            WORDPATCH_JOB_NOT_ENABLED => __wt($wpenv_vars, 'ERROR_JOB_NOT_ENABLED'),
            WORDPATCH_INVALID_JOB => __wt($wpenv_vars, 'ERROR_INVALID_JOB'),
            WORDPATCH_INVALID_PATCH => __wt($wpenv_vars, 'ERROR_INVALID_PATCH'),
            WORDPATCH_UNKNOWN_DATABASE_ERROR => __wt($wpenv_vars, 'ERROR_UNKNOWN_DATABASE_ERROR'),
            WORDPATCH_INVALID_JOB_PATH => __wt($wpenv_vars, 'ERROR_INVALID_JOB_PATH'),
            WORDPATCH_WORK_THROTTLED => __wt($wpenv_vars, 'ERROR_WORK_THROTTLED'),
            WORDPATCH_API_UNKNOWN_ERROR => __wt($wpenv_vars, 'ERROR_API_UNKNOWN_ERROR'),
            WORDPATCH_INVALID_FS_METHOD => __wt($wpenv_vars, 'ERROR_INVALID_FS_METHOD'),
            WORDPATCH_INVALID_FS_TIMEOUT => __wt($wpenv_vars, 'ERROR_INVALID_FS_TIMEOUT'),
            WORDPATCH_FS_CHMOD_FILE_REQUIRED => __wt($wpenv_vars, 'ERROR_FS_CHMOD_FILE_REQUIRED'),
            WORDPATCH_FTP_SSHKEY_INDECISIVE => __wt($wpenv_vars, 'ERROR_FTP_SSHKEY_INDECISIVE'),
            WORDPATCH_FTP_PASS_REQUIRED => __wt($wpenv_vars, 'ERROR_FTP_PASS_REQUIRED'),
            WORDPATCH_FS_CHMOD_DIR_REQUIRED => __wt($wpenv_vars, 'ERROR_FS_CHMOD_DIR_REQUIRED'),
            WORDPATCH_FTP_HOST_REQUIRED => __wt($wpenv_vars, 'ERROR_FTP_HOST_REQUIRED'),
            WORDPATCH_INVALID_ACTIVATION_KEY => __wt($wpenv_vars, 'ERROR_INVALID_ACTIVATION_KEY'),
            WORDPATCH_FTP_USER_REQUIRED => __wt($wpenv_vars, 'ERROR_FTP_USER_REQUIRED'),
            WORDPATCH_DB_HOST_REQUIRED => __wt($wpenv_vars, 'ERROR_DB_HOST_REQUIRED'),
            WORDPATCH_DB_USER_REQUIRED => __wt($wpenv_vars, 'ERROR_DB_USER_REQUIRED'),
            WORDPATCH_DB_NAME_REQUIRED => __wt($wpenv_vars, 'ERROR_DB_NAME_REQUIRED'),
            WORDPATCH_DB_COLLATE_INVALID => __wt($wpenv_vars, 'ERROR_DB_COLLATE_INVALID'),
            WORDPATCH_INVALID_MAILER => __wt($wpenv_vars, 'ERROR_INVALID_MAILER'),
            WORDPATCH_INVALID_MAIL_FROM => __wt($wpenv_vars, 'ERROR_INVALID_MAIL_FROM'),
            WORDPATCH_MAIL_FROM_REQUIRED => __wt($wpenv_vars, 'ERROR_MAIL_FROM_REQUIRED'),
            WORDPATCH_INVALID_MAIL_TO => __wt($wpenv_vars, 'ERROR_INVALID_MAIL_TO'),
            WORDPATCH_MAIL_TO_REQUIRED => __wt($wpenv_vars, 'ERROR_MAIL_TO_REQUIRED'),
            WORDPATCH_SMTP_HOST_REQUIRED => __wt($wpenv_vars, 'ERROR_SMTP_HOST_REQUIRED'),
            WORDPATCH_SMTP_PORT_REQUIRED => __wt($wpenv_vars, 'ERROR_SMTP_PORT_REQUIRED'),
            WORDPATCH_SMTP_USER_REQUIRED => __wt($wpenv_vars, 'ERROR_SMTP_USER_REQUIRED'),
            WORDPATCH_SMTP_PASS_REQUIRED => __wt($wpenv_vars, 'ERROR_SMTP_PASS_REQUIRED'),
            WORDPATCH_INVALID_SMTP_SSL => __wt($wpenv_vars, 'ERROR_INVALID_SMTP_SSL'),
            WORDPATCH_INVALID_SMTP_AUTH => __wt($wpenv_vars, 'ERROR_INVALID_SMTP_AUTH'),
            WORDPATCH_MUST_CONFIGURE_DATABASE => __wt($wpenv_vars, 'ERROR_MUST_CONFIGURE_DATABASE'),
            WORDPATCH_MUST_CONFIGURE_FILESYSTEM => __wt($wpenv_vars, 'ERROR_MUST_CONFIGURE_FILESYSTEM'),
            WORDPATCH_MUST_CONFIGURE_RESCUE => __wt($wpenv_vars, 'ERROR_MUST_CONFIGURE_RESCUE'),
            WORDPATCH_MUST_ACTIVATE_LICENSE => __wt($wpenv_vars, 'ERROR_MUST_ACTIVATE_LICENSE'),
            WORDPATCH_DB_CHARSET_INVALID => __wt($wpenv_vars, 'ERROR_DB_CHARSET_INVALID'),
            WORDPATCH_INVALID_UPLOAD_BUCKET => __wt($wpenv_vars, 'ERROR_INVALID_UPLOAD_BUCKET'),
            WORDPATCH_NO_LICENSE => __wt($wpenv_vars, 'ERROR_NO_LICENSE'),
            WORDPATCH_BAD_LICENSE => __wt($wpenv_vars, 'ERROR_BAD_LICENSE'),
            WORDPATCH_INACTIVE_LICENSE => __wt($wpenv_vars, 'ERROR_INACTIVE_LICENSE'),
            WORDPATCH_PRODUCT_MISMATCH => __wt($wpenv_vars, 'ERROR_PRODUCT_MISMATCH'),
            WORDPATCH_OUT_OF_SEATS => __wt($wpenv_vars, 'ERROR_OUT_OF_SEATS'),
            WORDPATCH_DOMAIN_ALREADY_ACTIVE => __wt($wpenv_vars, 'ERROR_DOMAIN_ALREADY_ACTIVE'),
            WORDPATCH_LICENSE_SEEMS_ACTIVE => __wt($wpenv_vars, 'ERROR_LICENSE_SEEMS_ACTIVE'),
            WORDPATCH_CRYPTO_ENGINE_ERROR => __wt($wpenv_vars, 'ERROR_CRYPTO_ENGINE_ERROR'),
            WORDPATCH_PURGE_LICENSE_FAILED => __wt($wpenv_vars, 'ERROR_PURGE_LICENSE_FAILED'),
            WORDPATCH_MAILER_FAILED => __wt($wpenv_vars, 'ERROR_MAILER_FAILED')
        );

        if($key === WORDPATCH_PATCH_FAILED) {
            $possibilities[WORDPATCH_PATCH_FAILED] = __wt($wpenv_vars, 'ERROR_PATCH_FAILED',
                $vars['patch_error']);
        }

        // Sample of overriding on page basic
        if($where === WORDPATCH_WHERE_EDITPATCH || $where === WORDPATCH_WHERE_NEWPATCH) {
            $possibilities[WORDPATCH_FILESYSTEM_FAILED_WRITE] = __wt($wpenv_vars, 'PATCH_FORM_UPLOAD_FAILED');
        }

        if($where === WORDPATCH_WHERE_RESTOREJOB) {
            $possibilities[WORDPATCH_INVALID_JOB] = __wt($wpenv_vars, 'ERROR_RESTORE_JOB_INVALID_JOB');
        }

        if($where === WORDPATCH_WHERE_TRASHJOB) {
            $possibilities[WORDPATCH_INVALID_JOB] = __wt($wpenv_vars, 'ERROR_TRASH_JOB_INVALID_JOB');
        }

        if($key === WORDPATCH_FTP_PASS_REQUIRED && (isset($vars['fs_method']) && $vars['fs_method'] === wordpatch_fs_method_ssh2())) {
            $possibilities[WORDPATCH_FTP_PASS_REQUIRED] = __wt($wpenv_vars, 'ERROR_SSH_PASS_REQUIRED');
        }

        if($key === WORDPATCH_FILESYSTEM_FAILED_READ && $where === WORDPATCH_WHERE_LOADJOBFILE) {
            $possibilities[WORDPATCH_FILESYSTEM_FAILED_READ] = __wt($wpenv_vars, 'ERROR_INVALID_JOB_FILE_PATH');
        }

        if(isset($possibilities[$key])) {
            return $possibilities[$key];
        }

        // By default, fallback to unknown error's localization.
        return __wt($wpenv_vars, 'UNKNOWN_ERROR');
    }
}

if(!function_exists('__wt')) {
    /**
     * WordPatch translation function.
     * The second parameter is the phrase key (which should be the same for every language).
     * The rest of the parameters are arguments to be passed into sprintf().
     *
     * Returns null if the phrase key has not been found.
     * @param $wpenv_vars
     * @param $phrase_key
     * @param null $_
     * @param null $__
     * @return mixed
     */
    function __wt($wpenv_vars, $phrase_key, $_ = null, $__ = null) {
        // Calculate the current language or grab it from the cache.
        $current_language = wordpatch_current_language($wpenv_vars);

        // Calculate the current language phrases.
        $phrases = wordpatch_phrases($current_language);

        // Formatter arguments.
        $format_args = array_slice(func_get_args(), 2);

        // Check if the phrase is defined and return null if not.
        if(!isset($phrases[$phrase_key])) {
            return $phrase_key;
        }

        // Call sprintf() with the proper parameters.
        array_unshift($format_args, $phrases[$phrase_key]);
        return call_user_func_array('sprintf', $format_args);
    }
}

if(!function_exists('__wte')) {
    /**
     * WordPatch translation function which also safely escapes text into HTML.
     * After escaping, this function replaces newlines with <br />.
     * The second parameter is the phrase key (which should be the same for every language).
     * The rest of the parameters are arguments to be passed into sprintf().
     *
     * Returns null if the phrase key has not been found.
     * @param $wpenv_vars
     * @param $phrase_key
     * @param null $_
     * @param null $__
     * @return null
     */
    function __wte($wpenv_vars, $phrase_key, $_ = null, $__ = null) {
        $args = func_get_args();

        return nl2br(htmlspecialchars(call_user_func_array('__wt', $args)));
    }
}

if(!function_exists('__wten')) {
    /**
     * WordPatch translation function which also safely escapes text into HTML.
     * After escaping, this function does not replace newlines with line breaks.
     * The second parameter is the phrase key (which should be the same for every language).
     * The rest of the parameters are arguments to be passed into sprintf().
     *
     * Returns null if the phrase key has not been found.
     * @param $wpenv_vars
     * @param $phrase_key
     * @param null $_
     * @param null $__
     * @return null
     */
    function __wten($wpenv_vars, $phrase_key, $_ = null, $__ = null) {
        $args = func_get_args();

        return htmlspecialchars(call_user_func_array('__wt', $args));
    }
}