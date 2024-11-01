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
 * Implement the dashboard widgets.
 */

if(!function_exists('wordpatch_quickstart_dashboard_widget')) {
    function wordpatch_quickstart_dashboard_widget($_, $widget) {
        $wpenv_vars = wordpatch_wordpress_env();

        $http_wordpatch = wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_wordpatch_path'));
        $http_assets = $http_wordpatch . 'assets/';

        // TODO: In production use the minified version.
        $min_str = '';

        $args = $widget['args'];
        ?>
        <link rel="stylesheet" type="text/css"
              href="<?php echo($http_assets . 'css/wordpatch' . $min_str . '.css'); ?>">
        <?php
        wordpatch_quickstart($wpenv_vars, $args['is_db_configured'], $args['is_fs_configured'], $args['is_rescue_configured'],
            $args['is_already_activated'], $args['is_mail_configured']);
    }
}

if(!function_exists('wordpatch_add_dashboard_widgets')) {
    /**
     * Responsible for adding the necessary widgets to the WordPress dashboard.
     */
    function wordpatch_add_dashboard_widgets()
    {
        global $wp_meta_boxes;

        $wpenv_vars = wordpatch_wordpress_env();

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

        // Check if there are some jobs configured
        $has_some_jobs = count(wordpatch_get_jobs($wpenv_vars)) > 0;

        // Construct the dashboard arguments.
        $dashboard_args = array(
            'is_mail_configured' => $is_mail_configured,
            'is_fs_configured' => $is_fs_configured,
            'is_db_configured' => $is_db_configured,
            'is_rescue_configured' => $is_rescue_configured,
            'is_already_activated' => $is_already_activated,
            'has_some_jobs' => $has_some_jobs
        );

        // We should show the quickstart if the user has missed any configuration steps.
        // TODO: Stop always showing the quickstart.
        $show_quickstart = true;//(!$is_db_configured || !$is_fs_configured || !$is_rescue_configured || !$is_already_activated ||
        //!$is_mail_configured || !$has_some_jobs);

        if($show_quickstart) {
            // Add the quickstart widget to the dashboard.
            wp_add_dashboard_widget('wordpatch_quickstart_dashboard_widget', 'WordPatch Quick Start',
                'wordpatch_quickstart_dashboard_widget', null, $dashboard_args);

            // Grab the core boxes.
            $core_boxes = $wp_meta_boxes['dashboard']['normal']['core'];

            // Move the WordPatch boxes to the top.
            $wordpatch_boxes = array(
                'wordpatch_quickstart_dashboard_widget' => $core_boxes['wordpatch_quickstart_dashboard_widget']
            );

            // Merge and set the new dashboard metaboxes.
            $wp_meta_boxes['dashboard']['normal']['core'] = array_merge($wordpatch_boxes, $core_boxes);
        }
    }
}