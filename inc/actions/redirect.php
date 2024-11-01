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
 * Implements the redirect action.
 */

if(!function_exists('wordpatch_redirect_url')) {
    /**
     * Calculate an absolute URL to the rescue redirect action. This must go directly through the rescue script.
     *
     * @param $wpenv_vars
     * @param $job_id
     * @return null|string
     */
    function wordpatch_redirect_url($wpenv_vars, $job_id)
    {
        $rescue_path = wordpatch_env_get($wpenv_vars, 'rescue_path');

        if(!$rescue_path) {
            return null;
        }

        $base_url = wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_site_url')) . $rescue_path;
        return wordpatch_add_query_params($base_url, 'action=' . WORDPATCH_WHERE_REDIRECT .
            '&job=' . urlencode($job_id));
    }
}

if(!function_exists('wordpatch_features_redirect')) {
    /**
     * Returns features supported by 'redirect'. See full list of options inside `inc/actions.php`.
     *
     * @param $wpenv_vars
     * @return array
     */
    function wordpatch_features_redirect($wpenv_vars)
    {
        return array(WORDPATCH_FEATURE_RENDER);
    }
}

if(!function_exists('wordpatch_render_redirect')) {
    /**
     * Render the redirect page.
     *
     * @param $wpenv_vars
     */
    function wordpatch_render_redirect($wpenv_vars)
    {
        // Calculate $where and $error_vars for use with our draw field function calls.
        $where = WORDPATCH_WHERE_REDIRECT;
        $error_vars = array();

        $job_id = isset($_GET['job']) ? wordpatch_sanitize_unique_id($_GET['job']) : '';

        $link = sprintf("<a href=\"%s\">%s</a>", wordpatch_logs_url($wpenv_vars, $job_id),
            __wt($wpenv_vars, 'REDIRECT_NEXT_LINK'));

        wordpatch_render_header($wpenv_vars);
        ?>
        <div class="wordpatch_page">
            <div class="wordpatch_page_title_container">
                <div class="wordpatch_page_title_left">
                    <h1 class="wordpatch_page_title"><?php echo __wte($wpenv_vars, 'REDIRECT_TITLE'); ?></h1>
                </div>
            </div>

            <?php wordpatch_render_generic_modal($wpenv_vars, __wte($wpenv_vars, 'REDIRECT_NEXT_HEADING'),
                __wt($wpenv_vars, 'REDIRECT_NEXT', $link)); ?>
        </div>
        <script type="text/javascript">
            (function($) {
                $(document).ready(function() {
                    setTimeout(function() {
                        document.location = <?php echo(json_encode(wordpatch_logs_url($wpenv_vars, $job_id))); ?>;
                    }, 5000);
                });
            })(jQuery);
        </script>
        <?php
        wordpatch_render_footer($wpenv_vars);
    }
}