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
 * Implements the login functionality.
 */

if(!function_exists('wordpatch_login_uri')) {
    /**
     * Construct a URI to 'login'.
     *
     * @param $wpenv_vars
     * @param null|string $next_uri
     * @return string
     */
    function wordpatch_login_uri($wpenv_vars, $next_uri = null)
    {
        return wordpatch_add_query_params(wordpatch_env_get($wpenv_vars, 'base_uri'), 'action=' . WORDPATCH_WHERE_LOGIN .
            ($next_uri !== null ? ('&next_uri=' . urlencode($next_uri)) : ''));
    }
}

if(!function_exists('wordpatch_features_login')) {
    /**
     * Returns features supported by 'login'. See full list of options inside `inc/actions.php`.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_features_login($wpenv_vars)
    {
        return array(WORDPATCH_FEATURE_PREFACE, WORDPATCH_FEATURE_PROCESS, WORDPATCH_FEATURE_RENDER);
    }
}

if(!function_exists('wordpatch_preface_login')) {
    /**
     * Implements the preface logic for the login action. Redirects the user to the next URI if they are already logged in.
     * @param $wpenv_vars
     */
    function wordpatch_preface_login($wpenv_vars)
    {
        if (wordpatch_is_logged_in($wpenv_vars)) {
            if (isset($_GET['next_uri']) && trim($_GET['next_uri']) !== '') {
                wordpatch_redirect($_GET['next_uri']);
            } else {
                wordpatch_redirect(wordpatch_dashboard_uri($wpenv_vars));
            }

            exit();
        }
    }
}

if(!function_exists('wordpatch_process_login')) {
    /**
     * Handles processing logic for the login form.
     *
     * @param $wpenv_vars
     */
    function wordpatch_process_login($wpenv_vars, $login_uri)
    {
        $next_uri = (isset($_GET['next_uri']) && trim($_GET['next_uri']) !== '')
            ? trim($_GET['next_uri']) : wordpatch_dashboard_uri($wpenv_vars);

        $user_login = trim($_POST['user_login']);
        $user_pass = trim($_POST['user_pass']);
        $rememberme = isset($_POST['rememberme']) && $_POST['rememberme'] !== '0';

        $auth_user = wordpatch_authenticate($wpenv_vars, $user_login, $user_pass);

        if ($auth_user) {
            wordpatch_set_auth_cookie($wpenv_vars, $auth_user->ID, $rememberme);

            wordpatch_redirect($next_uri);
            exit();
        }

        wordpatch_var_set('login_error', WORDPATCH_INVALID_CREDENTIALS);
    }
}

if(!function_exists('wordpatch_render_login')) {
    /**
     * Handles rendering logic for the login form.
     *
     * @param $wpenv_vars
     */
    function wordpatch_render_login($wpenv_vars)
    {
        $next_uri = (isset($_GET['next_uri']) && trim($_GET['next_uri']) !== '')
            ? trim($_GET['next_uri']) : wordpatch_dashboard_uri($wpenv_vars);

        wordpatch_render_header($wpenv_vars, array('wordpatch_login'));

        $login_error = wordpatch_var_get('login_error');
        $login_error = !$login_error ? null : $login_error;

        $site_url = wordpatch_trailingslashit(
            wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_site_url')) .
            wordpatch_unleadingslashit(wordpatch_env_get($wpenv_vars, 'wp_wordpatch_path'))
        );

        ?>
        <div id="wordpatch_loginpage" class="wordpatch_page">
            <!-- <p class="wordpatch_logintitle"><?php echo(__wte($wpenv_vars, 'LOGIN_TITLE')); ?></p> -->
            <?php
            $support_link = sprintf("<a class=\"wordpatch_link\" href=\"%s\" target=\"_blank\">%s</a>", wordpatch_support_url(),
                __wten($wpenv_vars, 'LOGIN_SUPPORT_LINK'));
            ?>
            <div class="wordpatch_login_header">
                <img class="wordpatch_login_logo" width="285" src="<?php echo htmlspecialchars($site_url . "assets/img/WordPatchLogo.svg") ?>" alt="WordPatch" />
            </div>
            <form name="wordpatch_loginform" id="wordpatch_loginform"
                  action="<?php echo(wordpatch_login_uri($wpenv_vars, $next_uri)); ?>" method="post">
                <div class="wordpatch_login_description_wrapper">
                    <p class="wordpatch_logindescription"><?php echo(__wt($wpenv_vars, 'LOGIN_DESC')); ?></p>
                </div>
                <?php if($login_error !== null) { ?>
                    <?php wordpatch_errors_maybe_draw_some($wpenv_vars, array($login_error), null, WORDPATCH_WHERE_LOGIN, array()); ?>
                <?php } ?>

                <label class="wordpatch_input_label" for="user_login">
                    <?php echo(__wten($wpenv_vars, 'LOGIN_USER')); ?><br/>
                    <input type="text" name="user_login" id="user_login" class="wordpatch_input_text" value="" size="20" placeholder="email@wordpatch.com"/>
                </label>
                <label class="wordpatch_input_label" for="user_pass">
                    <?php echo(__wten($wpenv_vars, 'LOGIN_PASS')); ?><br/>
                    <input type="password" name="user_pass" id="user_pass" class="wordpatch_input_text" value="" size="20" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;"/>
                </label>
                <div class="wordpatch_login_remember_support wordpatch_input_flex">
                    <label class="wordpatch_input_label wordpatch_input_label_checkbox wordpatch_flexme" for="rememberme">
                        <input name="rememberme" type="checkbox" id="rememberme" value="forever" /> <?php echo(__wten($wpenv_vars, 'LOGIN_REMEMBER')); ?>
                        <span class="wordpatch_input_checkbox"><i class="fa fa-check" aria-hidden="true"></i></span>
                    </label>
                    <p class="wordpatch_login_support wordpatch_flexme">
                        <?php echo(__wt($wpenv_vars, 'LOGIN_SUPPORT', $support_link)); ?>
                    </p>
                </div>
                <input type="submit" name="submit" id="submit" value="<?php echo(__wten($wpenv_vars, 'LOGIN_SUBMIT')); ?>" class="wordpatch_button wordpatch_button_blue wordpatch_login_button"/>
            </form>
        </div>
        <?php
        wordpatch_render_footer($wpenv_vars);

        if (!wordpatch_env_get($wpenv_vars, 'embed_mode')) {
            exit();
        }
    }
}
