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
 * Implements the settings page for WordPatch.
 */

// Include the database configuration wizard dependencies
include_once(dirname(__FILE__) . '/settings/post_vars.php');
include_once(dirname(__FILE__) . '/settings/process_internal.php');
include_once(dirname(__FILE__) . '/settings/db_helpers.php');
include_once(dirname(__FILE__) . '/settings/draw_fields.php');

if(!function_exists('wordpatch_settings_uri')) {
    /**
     * Construct a URI to 'settings'.
     *
     * @param $wpenv_vars
     * @param null|bool $updatesettings_success
     * @return string
     */
    function wordpatch_settings_uri($wpenv_vars, $updatesettings_success = null)
    {
        return wordpatch_add_query_params(wordpatch_env_get($wpenv_vars, 'base_uri'), 'action=' . WORDPATCH_WHERE_SETTINGS .
                (($updatesettings_success !== null && $updatesettings_success) ? '&updatesettings_success=1' : ''));
    }
}

if(!function_exists('wordpatch_features_settings')) {
    /**
     * Returns features supported by 'settings'. See full list of options inside `inc/actions.php`.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_features_settings($wpenv_vars)
    {
        return array(WORDPATCH_FEATURE_REQUIRES_AUTH, WORDPATCH_FEATURE_PROCESS, WORDPATCH_FEATURE_RENDER);
    }
}

if(!function_exists('wordpatch_process_settings')) {
    /**
     * Handles processing logic for the settings form.
     *
     * @param $wpenv_vars
     */
    function wordpatch_process_settings($wpenv_vars)
    {
        if (!wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars))) {
            return;
        }

        // Calculate the current post variables.
        $current = wordpatch_settings_post_vars($wpenv_vars);

        // Try to process the job using the internal function.
        $error_list = wordpatch_settings_process_internal($wpenv_vars, $current);

        // Set the error list variable for access in render.
        wordpatch_var_set('settings_errors', $error_list);
    }
}

if(!function_exists('wordpatch_render_settings')) {
    /**
     * Render the settings form.
     *
     * @param $wpenv_vars
     */
    function wordpatch_render_settings($wpenv_vars)
    {
        // Calculate $where and $error_vars for use with our draw field function calls.
        $where = WORDPATCH_WHERE_SETTINGS;
        $error_vars = array();

        // Define which errors are related to the fields.
        $field_errors = array();

        // Calculate the current post variables.
        $current = wordpatch_settings_post_vars($wpenv_vars);

        // Grab the settings errors from the variable store.
        $settings_errors = wordpatch_var_get('settings_errors');
        $settings_errors = wordpatch_no_errors($settings_errors) ? array() : $settings_errors;

        $updatesettings_success = (isset($_GET['updatesettings_success']) && trim($_GET['updatesettings_success']) === '1');

        wordpatch_render_header($wpenv_vars);
        ?>
        <div class="wordpatch_page">
            <?php if (!wordpatch_is_wordpatch_admin($wpenv_vars, wordpatch_get_current_user_id($wpenv_vars))) { ?>
                <?php wordpatch_render_access_denied($wpenv_vars); ?>
            <?php } else { ?>
                <?php if ($updatesettings_success) { ?>
                    <?php
                    $link = sprintf("<a href=\"%s\">%s</a>",
                        wordpatch_settings_uri($wpenv_vars), __wten($wpenv_vars, 'SETTINGS_SUCCESS_LINK'))
                    ?>
                    <?php wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'SETTINGS_SUCCESS_HEADING'),
                        __wt($wpenv_vars, 'SETTINGS_SUCCESS', $link)); ?>
                <?php } else { ?>
                    <div class="wordpatch_page_title_container">
                        <div class="wordpatch_page_title_left">
                            <h1 class="wordpatch_page_title"><?php echo __wte($wpenv_vars, 'SETTINGS_TITLE'); ?></h1>
                        </div>
                    </div>

                    <?php
                    // Check if the DB is configured.
                    $is_db_configured = wordpatch_calculate_db_configured($wpenv_vars) === WORDPATCH_YES;

                    // Check if the filesystem is configured.
                    $is_fs_configured = wordpatch_calculate_fs_configured($wpenv_vars) === WORDPATCH_YES;

                    // Check if the rescue script is configured.
                    $is_rescue_configured = wordpatch_calculate_rescue_configured($wpenv_vars) === WORDPATCH_YES;

                    // Check if Wordpatch is activated.
                    $is_already_activated = wordpatch_license_is_active($wpenv_vars);

                    // Check if the mailer is configured.
                    $is_mail_configured = wordpatch_calculate_mail_configured($wpenv_vars) === WORDPATCH_YES;

                    wordpatch_errors_maybe_draw_others($wpenv_vars, $settings_errors, $field_errors, $where, $error_vars);
                    ?>
                    <form action="<?php echo(wordpatch_settings_uri($wpenv_vars)); ?>" method="POST">
                        <?php
                        // Draw the language field.
                        wordpatch_settings_draw_language_field($wpenv_vars, $current, $settings_errors);

                        // Draw the moderation mode field.
                        wordpatch_settings_draw_mode_field($wpenv_vars, $current, $settings_errors);

                        // Draw the moderation timer field.
                        wordpatch_settings_draw_timer_field($wpenv_vars, $current, $settings_errors);

                        // Draw the update cooldown field.
                        wordpatch_settings_draw_update_cooldown_field($wpenv_vars, $current, $settings_errors);

                        // Draw the retry count field.
                        wordpatch_settings_draw_retry_count_field($wpenv_vars, $current, $settings_errors);

                        // Draw the maintenance mode field.
                        wordpatch_settings_draw_maintenance_mode_field($wpenv_vars, $current, $settings_errors);
                        ?>
                        <div class="wordpatch_metabox">
                            <div class="wordpatch_metabox_header"><?php echo(__wte($wpenv_vars, 'SETTINGS_RECONFIGURE_WORDPATCH')); ?></div>
                            <div class="wordpatch_metabox_body">
                                <div class="wordpatch_button_container">
                                    <a class="wordpatch_button <?php echo($is_db_configured ? 'wordpatch_button_blue' : 'wordpatch_button_red'); ?>" href="<?php echo(wordpatch_configdb_url($wpenv_vars)); ?>">
                                        <?php echo __wten($wpenv_vars, $is_db_configured ? 'SETTINGS_RECONFIG_DB' : 'SETTINGS_CONFIG_DB')?>
                                    </a><a class="wordpatch_button <?php echo($is_fs_configured ? 'wordpatch_button_blue' : 'wordpatch_button_red'); ?>" href="<?php echo(wordpatch_configfs_url($wpenv_vars)); ?>">
                                        <?php echo __wten($wpenv_vars, $is_fs_configured ? 'SETTINGS_RECONFIG_FS' : 'SETTINGS_CONFIG_FS')?>
                                    </a><a class="wordpatch_button <?php echo($is_rescue_configured ? 'wordpatch_button_blue' : 'wordpatch_button_red'); ?>" href="<?php echo(wordpatch_configrescue_url($wpenv_vars)); ?>">
                                        <?php echo __wten($wpenv_vars, $is_rescue_configured ? 'SETTINGS_RECONFIG_RESCUE' : 'SETTINGS_CONFIG_RESCUE')?>
                                    </a><a class="wordpatch_button <?php echo($is_already_activated ? 'wordpatch_button_blue' : 'wordpatch_button_red'); ?>" href="<?php echo(wordpatch_configlicense_url($wpenv_vars)); ?>">
                                        <?php echo __wten($wpenv_vars, $is_already_activated ? 'SETTINGS_RECONFIG_LICENSE' : 'SETTINGS_CONFIG_LICENSE')?>
                                    </a><a class="wordpatch_button <?php echo($is_mail_configured ? 'wordpatch_button_blue' : 'wordpatch_button_red'); ?>" href="<?php echo(wordpatch_configmail_url($wpenv_vars)); ?>">
                                        <?php echo __wten($wpenv_vars, $is_mail_configured ? 'SETTINGS_RECONFIG_MAIL' : 'SETTINGS_CONFIG_MAIL')?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="wordpatch_metabox">
                            <div class="wordpatch_metabox_header"><?php echo(__wte($wpenv_vars,
                                    'SETTINGS_SUBMIT_TITLE')); ?></div>

                            <div class="wordpatch_metabox_body">
                                <input type="submit" name="submit" class="wordpatch_button wordpatch_button_green" value="<?php echo(__wten($wpenv_vars,
                                    'SETTINGS_SUBMIT_TEXT')); ?>"/>
                            </div>
                        </div>
                    </form>
                <?php } ?>
            <?php } ?>
        </div>
        <?php
        wordpatch_render_footer($wpenv_vars);
    }
}