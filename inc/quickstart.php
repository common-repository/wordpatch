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
 * Implements the quickstart box that is displayed on the WordPatch and WordPress dashboards.
 */

if(!function_exists('wordpatch_quickstart')) {
    /**
     * Draws the quickstart box for WordPatch.
     *
     * @param $wpenv_vars
     * @param $db_configured
     * @param $fs_configured
     * @param $rescue_configured
     * @param $is_activated
     * @param $mail_configured
     */
    function wordpatch_quickstart($wpenv_vars, $db_configured, $fs_configured, $rescue_configured, $is_activated, $mail_configured)
    {
        // Calculate the current step.
        $current_step = WORDPATCH_WHERE_JOBS;

        if(!$db_configured) {
            $current_step = WORDPATCH_WHERE_CONFIGDB;
        } else if(!$fs_configured) {
            $current_step = WORDPATCH_WHERE_CONFIGFS;
        } else if(!$rescue_configured) {
            $current_step = WORDPATCH_WHERE_CONFIGRESCUE;
        } else if(!$is_activated) {
            $current_step = WORDPATCH_WHERE_CONFIGLICENSE;
        } else if(!$mail_configured) {
            $current_step = WORDPATCH_WHERE_CONFIGMAIL;
        }

        // Get the appropriate setup link.
        $setup_link = '';

        switch ($current_step)
        {
            case WORDPATCH_WHERE_CONFIGDB:
                $setup_link = wordpatch_configdb_url($wpenv_vars);
                break;

            case WORDPATCH_WHERE_CONFIGFS:
                $setup_link = wordpatch_configfs_url($wpenv_vars);
                break;

            case WORDPATCH_WHERE_CONFIGRESCUE:
                $setup_link = wordpatch_configrescue_url($wpenv_vars);
                break;

            case WORDPATCH_WHERE_CONFIGLICENSE:
                $setup_link = wordpatch_configlicense_url($wpenv_vars);
                break;

            case WORDPATCH_WHERE_CONFIGMAIL:
                $setup_link = wordpatch_configmail_url($wpenv_vars);
                break;

            case WORDPATCH_WHERE_JOBS:
                $setup_link = wordpatch_jobs_uri($wpenv_vars);
                break;
        }
        ?>
        <div class="wordpatch_metabox wordpatch_quickstart">
            <div class="wordpatch_metabox_header"><?php echo(__wte($wpenv_vars, 'QUICKSTART_TITLE')); ?></div>

            <div class="wordpatch_metabox_body">
                <ol class="wordpatch_numbered_list wordpatch_quickstart_list">
                    <li class="wordpatch_numbered_list_item <?php echo($db_configured ? 'wordpatch_quickstart_item_done ' : ''); ?>
                        <?php echo($current_step === WORDPATCH_WHERE_CONFIGDB ? 'wordpatch_quickstart_item_current_first ' : ''); ?>">
                        <?php echo(__wte($wpenv_vars, 'QUICKSTART_CONFIGDB')); ?>
                    </li>

                    <li class="wordpatch_numbered_list_item <?php echo($fs_configured ? 'wordpatch_quickstart_item_done ' : ''); ?>
                        <?php echo($current_step === WORDPATCH_WHERE_CONFIGFS ? 'wordpatch_quickstart_item_current ' : ''); ?>">
                        <?php echo(__wte($wpenv_vars, 'QUICKSTART_CONFIGFS')); ?>
                    </li>

                    <li class="wordpatch_numbered_list_item <?php echo($rescue_configured ? 'wordpatch_quickstart_item_done ' : ''); ?>
                        <?php echo($current_step === WORDPATCH_WHERE_CONFIGRESCUE ? 'wordpatch_quickstart_item_current ' : ''); ?>">
                        <?php echo(__wte($wpenv_vars, 'QUICKSTART_CONFIGRESCUE')); ?>
                    </li>

                    <li class="wordpatch_numbered_list_item <?php echo($is_activated ? 'wordpatch_quickstart_item_done ' : ''); ?>
                        <?php echo($current_step === WORDPATCH_WHERE_CONFIGLICENSE ? 'wordpatch_quickstart_item_current ' : ''); ?>">
                        <?php echo(__wte($wpenv_vars, 'QUICKSTART_CONFIGLICENSE')); ?>
                    </li>

                    <li class="wordpatch_numbered_list_item <?php echo($mail_configured ? 'wordpatch_quickstart_item_done ' : ''); ?>
                        <?php echo($current_step === WORDPATCH_WHERE_CONFIGMAIL ? 'wordpatch_quickstart_item_current ' : ''); ?>">
                        <?php echo(__wte($wpenv_vars, 'QUICKSTART_CONFIGMAIL')); ?>
                    </li>

                    <li class="wordpatch_numbered_list_item <?php echo($current_step === WORDPATCH_WHERE_JOBS ?
                        'wordpatch_quickstart_item_current ' : ''); ?>">
                        <?php echo(__wte($wpenv_vars, 'QUICKSTART_CONFIGJOBS')); ?>
                    </li>
                </ol>

                <a class="wordpatch_button <?php echo($current_step === WORDPATCH_WHERE_CONFIGDB ? 'wordpatch_button_blue' : 'wordpatch_button_red'); ?>" href="<?php echo(htmlspecialchars($setup_link)); ?>">
                    <?php echo(__wte($wpenv_vars, $current_step === WORDPATCH_WHERE_CONFIGDB ? 'QUICKSTART_GET_STARTED_NOW' : 'QUICKSTART_CONTINUE_NOW')); ?>
                </a>
            </div>
        </div>
        <?php
    }
}