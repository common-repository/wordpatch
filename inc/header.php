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
 * Implements the header for WordPatch which contains styles, javascript, and common UI elements.
 */

if(!function_exists('wordpatch_render_header')) {
    /**
     * Render the header for WordPatch.
     *
     * @param $wpenv_vars
     * @param $body_classes
     * @param $breadcrumbs
     */
    function wordpatch_render_header($wpenv_vars, $body_classes = array(), $breadcrumbs = array())
    {
        $http_wordpatch = wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_wordpatch_path'));
        $http_assets = $http_wordpatch . 'assets/';

        $wp_site_path = wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_site_path'));

        $codemirror_path = $wp_site_path . 'wp-includes/js/codemirror/';

        $codemirror_css_check = wordpatch_check_for_codemirror_css($wpenv_vars);
        $codemirror_js_check = wordpatch_check_for_codemirror_js($wpenv_vars);

        $min_str = '.min';
        $version_str = WORDPATCH_VERSION;
        $version_enc = urlencode($version_str);
        ?>
        <?php if (!wordpatch_env_get($wpenv_vars, 'embed_mode')) { ?>
        <html class="no-js">
        <head>
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo(__wten($wpenv_vars, 'RESCUE_TITLE')); ?></title>
        <link rel="stylesheet" type="text/css" href="<?php echo($http_assets . 'css/rescue' . $min_str . '.css?v=' . $version_enc); ?>" />
        <script src="<?php echo($http_assets . 'js/jquery-3.2.1' . $min_str . '.js?v=' . $version_enc); ?>"></script>
        <?php } ?>
        <link rel="stylesheet" type="text/css" href="<?php echo($http_assets . 'css/font-awesome' . $min_str . '.css?v=' . $version_enc); ?>" />
        <script src="<?php echo($http_assets . 'js/codemirror' . $min_str . '.js?v=' . $version_enc); ?>"></script>
        <script src="<?php echo($http_assets . 'js/codemirror-addon-merge' . $min_str . '.js?v=' . $version_enc); ?>"></script>
        <script src="<?php echo($http_assets . 'js/codemirror-addon-search' . $min_str . '.js?v=' . $version_enc); ?>"></script>
        <script src="<?php echo($http_assets . 'js/codemirror-addon-searchcursor' . $min_str . '.js?v=' . $version_enc); ?>"></script>
        <script src="<?php echo($http_assets . 'js/codemirror-addon-jump-to-line' . $min_str . '.js?v=' . $version_enc); ?>"></script>
        <script src="<?php echo($http_assets . 'js/codemirror-addon-dialog' . $min_str . '.js?v=' . $version_enc); ?>"></script>
        <script src="<?php echo($http_assets . 'js/codemirror-addon-trailingspace' . $min_str . '.js?v=' . $version_enc); ?>"></script>
        <script src="<?php echo($http_assets . 'js/codemirror-addon-simplescrollbars' . $min_str . '.js?v=' . $version_enc); ?>"></script>
        <script src="<?php echo($http_assets . 'js/codemirror-addon-autorefresh' . $min_str . '.js?v=' . $version_enc); ?>"></script>
        <script src="<?php echo($http_assets . 'js/codemirror-mode-javascript' . $min_str . '.js?v=' . $version_enc); ?>"></script>
        <script src="<?php echo($http_assets . 'js/codemirror-mode-css' . $min_str . '.js?v=' . $version_enc); ?>"></script>
        <script src="<?php echo($http_assets . 'js/codemirror-mode-clike' . $min_str . '.js?v=' . $version_enc); ?>"></script>
        <script src="<?php echo($http_assets . 'js/codemirror-mode-htmlmixed' . $min_str . '.js?v=' . $version_enc); ?>"></script>
        <script src="<?php echo($http_assets . 'js/codemirror-mode-xml' . $min_str . '.js?v=' . $version_enc); ?>"></script>
        <script src="<?php echo($http_assets . 'js/codemirror-mode-php' . $min_str . '.js?v=' . $version_enc); ?>"></script>
        <script src="<?php echo($http_assets . 'js/base64' . $min_str . '.js?v=' . $version_enc); ?>"></script>
        <script src="<?php echo($http_assets . 'js/diff_match_patch' . $min_str . '.js?v=' . $version_enc); ?>"></script>
        <script src="<?php echo($http_assets . 'js/jquery-h5sortable' . $min_str . '.js?v=' . $version_enc); ?>"></script>
        <script src="<?php echo($http_assets . 'js/rubaxa-sortable' . $min_str . '.js?v=' . $version_enc); ?>"></script>
        <link rel="stylesheet" type="text/css"
              href="<?php echo($http_assets . 'css/wordpatch' . $min_str . '.css?v=' . $version_enc); ?>">
        <link rel="stylesheet" type="text/css"
              href="<?php echo($http_assets . 'css/codemirror' . $min_str . '.css?v=' . $version_enc); ?>">
      <link rel="stylesheet" type="text/css"
              href="<?php echo($http_assets . 'css/codemirror-addon-merge' . $min_str . '.css?v=' . $version_enc); ?>">
      <link rel="stylesheet" type="text/css"
              href="<?php echo($http_assets . 'css/codemirror-addon-dialog' . $min_str . '.css?v=' . $version_enc); ?>">
      <link rel="stylesheet" type="text/css"
              href="<?php echo($http_assets . 'css/codemirror-addon-simplescrollbars' . $min_str . '.css?v=' . $version_enc); ?>">
        <link rel="stylesheet" type="text/css"
              href="<?php echo($http_assets . 'css/jointbyte' . $min_str . '.css?v=' . $version_enc); ?>">
        <?php if (!wordpatch_env_get($wpenv_vars, 'embed_mode')) { ?>
        </head>
        <body class="<?php echo implode(' ', $body_classes); ?>">
        <?php } ?>
        <?php wordpatch_render_admin_top($wpenv_vars); ?>
        <div id="wordpatch_main_wrapper">
            <?php wordpatch_render_navi($wpenv_vars, $breadcrumbs); ?>
        <?php
    }
}

if(!function_exists('wordpatch_render_admin_top')) {
    function wordpatch_render_admin_top($wpenv_vars) {
        if (!wordpatch_is_logged_in($wpenv_vars) || wordpatch_env_get($wpenv_vars, 'embed_mode')) {
            return;
        }

        $job_actions = wordpatch_job_actions($wpenv_vars);
        $dashboard_actions = wordpatch_dashboard_actions($wpenv_vars);
        $settings_actions = wordpatch_settings_actions($wpenv_vars);
        $mailbox_actions = wordpatch_mailbox_actions($wpenv_vars);

        $active_class = 'wordpatch_admin_menu_item_active';

        ?>
        <div id="wordpatch_admin_bar">
            <div class="wordpatch_admin_bar_flex">
                <div class="wordpatch_admin_bar_left">
                    <div class="wordpatch_admin_bar_item wordpatch_admin_bar_item_hamburger">
                        <a href="#" class="wordpatch_admin_bar_hamburger">
                            <i class="fa fa-bars" aria-hidden="true"></i>
                            <span class="wordpatch_screen_reader_text"><?php echo(__wten($wpenv_vars, 'ADMIN_BAR_MENU')); ?></span>
                        </a>
                    </div>
                </div>
                <div class="wordpatch_admin_bar_right">
                    <div class="wordpatch_admin_bar_item wordpatch_admin_bar_item_rescue">
                        <a href="#" class="wordpatch_admin_bar_rescue">
                            <i class="fa fa-ambulance" aria-hidden="true"></i>
                            <span class="wordpatch_admin_bar_item_text"><?php echo(__wten($wpenv_vars, 'ADMIN_BAR_RESCUE')); ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div id="wordpatch_admin_menu">
            <div class="wordpatch_admin_menu_flex">
                <div class="wordpatch_admin_menu_top">
                    <div class="wordpatch_admin_menu_item wordpatch_admin_menu_item_active wordpatch_admin_menu_item_wordpatch">
                        <a href="<?php echo wordpatch_dashboard_uri($wpenv_vars); ?>">
                            <span class="wordpatch_admin_menu_icon_ctr">
                                <i class="wordpatch_admin_menu_icon jb-icon-wordpatch" aria-hidden="true"></i>
                            </span>
                            <span class="wordpatch_admin_menu_item_text"><?php echo(__wten($wpenv_vars, 'ADMIN_MENU_WORDPATCH')); ?></span>
                        </a>
                    </div>
                    <div class="wordpatch_admin_menu_submenu">
                        <div class="wordpatch_admin_menu_item wordpatch_admin_menu_submenu_item <?php echo(in_array(wordpatch_action(), $dashboard_actions) ? $active_class : ''); ?>">
                            <a href="<?php echo wordpatch_dashboard_uri($wpenv_vars); ?>">
                                <span class="wordpatch_admin_menu_item_text"><?php echo(__wten($wpenv_vars, 'ADMIN_MENU_WORDPATCH_DASHBOARD')); ?></span>
                            </a>
                        </div>
                        <div class="wordpatch_admin_menu_item wordpatch_admin_menu_submenu_item <?php echo(in_array(wordpatch_action(), $job_actions) ? $active_class : ''); ?>">
                            <a href="<?php echo wordpatch_jobs_uri($wpenv_vars); ?>">
                                <span class="wordpatch_admin_menu_item_text"><?php echo(__wten($wpenv_vars, 'ADMIN_MENU_WORDPATCH_JOBS')); ?></span>
                            </a>
                        </div>
                        <div class="wordpatch_admin_menu_item wordpatch_admin_menu_submenu_item <?php echo(in_array(wordpatch_action(), $mailbox_actions) ? $active_class : ''); ?>">
                            <a href="<?php echo wordpatch_mailbox_uri($wpenv_vars); ?>">
                                <span class="wordpatch_admin_menu_item_text"><?php echo(__wten($wpenv_vars, 'ADMIN_MENU_WORDPATCH_MAILBOX')); ?></span>
                            </a>
                        </div>
                        <div class="wordpatch_admin_menu_item wordpatch_admin_menu_submenu_item <?php echo(in_array(wordpatch_action(), $settings_actions) ? $active_class : ''); ?>">
                            <a href="<?php echo wordpatch_settings_uri($wpenv_vars); ?>">
                                <span class="wordpatch_admin_menu_item_text"><?php echo(__wten($wpenv_vars, 'ADMIN_MENU_WORDPATCH_SETTINGS')); ?></span>
                            </a>
                        </div>
                        <div class="wordpatch_admin_menu_item wordpatch_admin_menu_submenu_item">
                        <a href="<?php echo(wordpatch_support_url()) ?>" target="_blank">
                            <span class="wordpatch_admin_menu_item_text"><?php echo(__wten($wpenv_vars, 'ADMIN_MENU_WORDPATCH_SUPPORT')); ?></span>
                        </a>
                    </div>
                    </div>
                    <div class="wordpatch_admin_menu_item wordpatch_admin_menu_item_logout">
                        <a href="<?php echo(wordpatch_logout_uri($wpenv_vars)) ?>">
                            <span class="wordpatch_admin_menu_icon_ctr">
                                <i class="fa fa-sign-out" aria-hidden="true"></i>
                            </span>
                            <span class="wordpatch_admin_menu_item_text"><?php echo(__wten($wpenv_vars, 'ADMIN_MENU_LOGOUT')); ?></span>
                        </a>
                    </div>
                    <div class="wordpatch_admin_menu_item wordpatch_admin_menu_item_wpadmin">
                        <a href="<?php echo(wordpatch_wpadmin_url($wpenv_vars)); ?>" target="_blank">
                            <span class="wordpatch_admin_menu_icon_ctr">
                                <i class="fa fa-share" aria-hidden="true"></i>
                            </span>
                            <span class="wordpatch_admin_menu_item_text"><?php echo(__wten($wpenv_vars, 'ADMIN_MENU_WPADMIN')); ?></span>
                        </a>
                    </div>
                </div>
                <div class="wordpatch_admin_menu_bottom">

                </div>
            </div>
        </div>
        <?php
    }
}

if(!function_exists('wordpatch_render_navi')) {
    function wordpatch_render_navi($wpenv_vars, $breadcrumbs) {
        if(!wordpatch_is_logged_in($wpenv_vars)) {
            return;
        }

        $site_url = wordpatch_trailingslashit(
            wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_site_url')) .
            wordpatch_unleadingslashit(wordpatch_env_get($wpenv_vars, 'wp_wordpatch_path'))
        );
        ?>
        <div id="wordpatch_navi_wrapper">
            <div id="wordpatch_navi">
                <div class="wordpatch_navi_left">
                    <a href="<?php echo wordpatch_dashboard_uri($wpenv_vars); ?>">
                        <img class="wordpatch_navi_logo" width="285" src="<?php echo htmlspecialchars($site_url . "assets/img/WordPatchLogo.svg") ?>" alt="WordPatch" />
                    </a>
                </div>
                <div class="wordpatch_navi_right">
                    <?php wordpatch_render_breadcrumbs($wpenv_vars, $breadcrumbs); ?>
                </div>
            </div>
        </div>
        <?php
    }
}