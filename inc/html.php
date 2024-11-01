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
 * These functions are helpers for rendering commonly used HTML elements throughout WordPatch.
 */

if(!function_exists('wordpatch_dropdown')) {
    /**
     * Generate the dropdown information for use with the select box render helper.
     *
     * @param $wpenv_vars
     * @param $values
     * @param $display_fn_name
     * @return array
     */
    function wordpatch_dropdown($wpenv_vars, $values, $display_fn_name)
    {
        $results = array();

        foreach ($values as $value_single) {
            $results[$value_single] = call_user_func_array($display_fn_name, array($wpenv_vars, $value_single));
        }

        return $results;
    }
}

if(!function_exists('wordpatch_inputbox_render_open')) {
    function wordpatch_inputbox_render_open($wpenv_vars) {
        ?>
        <div class="wordpatch_metabox">
        <?php
    }
}

if(!function_exists('wordpatch_inputbox_render_close')) {
    function wordpatch_inputbox_render_close($wpenv_vars) {
        ?>
        </div>
        <?php
    }
}

if(!function_exists('wordpatch_inputbox_header_render_open')) {
    function wordpatch_inputbox_header_render_open($wpenv_vars) {
        ?>
        <div class="wordpatch_metabox_header">
        <?php
    }
}

if(!function_exists('wordpatch_inputbox_header_render_close')) {
    function wordpatch_inputbox_header_render_close($wpenv_vars) {
        ?>
        </div>
        <?php
    }
}

if(!function_exists('wordpatch_inputbox_body_render_open')) {
    function wordpatch_inputbox_body_render_open($wpenv_vars, $has_error) {
        ?>
        <div class="wordpatch_metabox_body <?php echo($has_error ? 'wordpatch_metabox_body_with_error' : ''); ?>">
        <?php
    }
}

if(!function_exists('wordpatch_inputbox_body_render_close')) {
    function wordpatch_inputbox_body_render_close($wpenv_vars) {
        ?>
        </div>
        <?php
    }
}

if(!function_exists('wordpatch_inputbox_desc_render_open')) {
    function wordpatch_inputbox_desc_render_open($wpenv_vars) {
        ?>
        <div class="wordpatch_inputbox_desc">
        <?php
    }
}

if(!function_exists('wordpatch_inputbox_desc_render_close')) {
    function wordpatch_inputbox_desc_render_close($wpenv_vars) {
        ?>
        </div>
        <?php
    }
}

if(!function_exists('wordpatch_inputbox_err_render_open')) {
    function wordpatch_inputbox_err_render_open($wpenv_vars) {
        ?>
        <div class="wordpatch_inputbox_err">
        <?php
    }
}

if(!function_exists('wordpatch_inputbox_err_render_close')) {
    function wordpatch_inputbox_err_render_close($wpenv_vars) {
        ?>
        </div>
        <?php
    }
}

if(!function_exists('wordpatch_label_render_open')) {
    function wordpatch_label_render_open($wpenv_vars, $for, $classes = []) {
        ?>
        <label class="wordpatch_input_label <?php echo(htmlspecialchars(implode(" ", $classes))); ?>" for="<?php echo(htmlspecialchars($for)); ?>">
        <?php
    }
}

if(!function_exists('wordpatch_label_render_close')) {
    function wordpatch_label_render_close($wpenv_vars) {
        ?>
        </label>
        <?php
    }
}

if(!function_exists('wordpatch_inputflex_render_open')) {
    function wordpatch_inputflex_render_open($wpenv_vars) {
        ?>
        <div class="wordpatch_inputflex">
        <?php
    }
}

if(!function_exists('wordpatch_inputflex_render_close')) {
    function wordpatch_inputflex_render_close($wpenv_vars) {
        ?>
        </div>
        <?php
    }
}

if(!function_exists('wordpatch_flexme_render_open')) {
    function wordpatch_flexme_render_open($wpenv_vars) {
        ?>
        <div class="wordpatch_flexme">
        <?php
    }
}

if(!function_exists('wordpatch_flexme_render_close')) {
    function wordpatch_flexme_render_close($wpenv_vars) {
        ?>
        </div>
        <?php
    }
}

if(!function_exists('wordpatch_input_text_render')) {
    function wordpatch_input_text_render($wpenv_vars, $input_key, $input_display_value) {
        $html_key = htmlspecialchars($input_key);
        ?>
        <input type="text" name="<?php echo($html_key); ?>" id="<?php echo($html_key); ?>" class="wordpatch_input_text"
               value="<?php echo(htmlspecialchars($input_display_value)); ?>"/>
        <?php
    }
}

if(!function_exists('wordpatch_input_password_render')) {
    function wordpatch_input_password_render($wpenv_vars, $input_key) {
        $html_key = htmlspecialchars($input_key);
        ?>
        <input type="password" name="<?php echo($html_key); ?>" id="<?php echo($html_key); ?>" class="wordpatch_input_text" />
        <?php
    }
}

if(!function_exists('wordpatch_input_file_render')) {
    function wordpatch_input_file_render($wpenv_vars, $input_key, $input_display_value) {
        $html_key = htmlspecialchars($input_key);
        ?>
        <?php
        $upload_key = wordpatch_upload_key();
        $esc_upload_key = htmlspecialchars($upload_key);
        ?>
        <div class="wordpatch_inputbox" id="wordpatch_patch_file_box" style="display: none">
            <div class="wordpatch_inputbox_header">Patch File</div>
            <div class="wordpatch_inputbox_body">
                <div class="wordpatch_inputbox_desc">
                    <?php echo(wordpatch_render_metadesc_patch_file($wpenv_vars)); ?>
                </div>

                <div class="wordpatch_existing_file">
                    <span class="wordpatch_existing_label">Currently uploaded file: </span>
                    <?php
                    $patch_file_exists = $patch['patch_type'] === wordpatch_patch_type_file() && $patch['patch_location'] !== '';
                    ?>
                    <?php if($patch_file_exists) { ?>
                        <?php $currently_uploaded_size = $patch['patch_size']; ?>
                        <span class="wordpatch_existing_size"><?php echo(wordpatch_size_format($currently_uploaded_size)); ?></span>
                        <a href="#" data-patch-id="<?php echo htmlspecialchars($patch['id']); ?>" class="wordpatch_existing_remove">[Delete from server]</a>
                    <?php } ?>
                    <span class="wordpatch_existing_nothing" style="<?php echo($patch_file_exists ? 'display:none' : ''); ?>">There is nothing uploaded, currently.</span>
                </div>

                <label for="<?php echo $esc_upload_key; ?>">
                    Upload Patch File:
                    <input type="file" name="<?php echo $esc_upload_key; ?>" id="<?php echo $esc_upload_key; ?>" />
                </label>
            </div>
        </div>

        <input type="text" name="<?php echo($html_key); ?>" id="<?php echo($html_key); ?>"
               value="<?php echo(htmlspecialchars($input_display_value)); ?>"/>
        <?php
    }
}

if(!function_exists('wordpatch_messagebox_render_open')) {
    function wordpatch_messagebox_render_open($wpenv_vars) {
        ?>
        <div class="wordpatch_metabox">
            <div class="wordpatch_metabox_body">
        <?php
    }
}

if(!function_exists('wordpatch_messagebox_render_close')) {
    function wordpatch_messagebox_render_close($wpenv_vars) {
        ?>
            </div>
        </div>
        <?php
    }
}

if(!function_exists('wordpatch_input_select_render_open')) {
    function wordpatch_input_select_render_open($wpenv_vars, $input_key)
    {
        $html_key = htmlspecialchars($input_key);
        ?>
        <select class="wordpatch_input_select" name="<?php echo($html_key); ?>" id="<?php echo($html_key); ?>">
        <?php
    }
}

if(!function_exists('wordpatch_input_select_render_close')) {
    function wordpatch_input_select_render_close($wpenv_vars)
    {
        ?>
        </select>
        <?php
    }
}

if(!function_exists('wordpatch_input_option_render')) {
    function wordpatch_input_option_render($wpenv_vars, $dropdown_value, $dropdown_display, $dropdown_selected)
    {
        $selected_attr = $dropdown_selected ? 'selected="selected"' : '';
        ?>
        <option value="<?php echo(htmlspecialchars($dropdown_value)); ?>" <?php echo($selected_attr); ?>>
            <?php echo(htmlspecialchars($dropdown_display)); ?></option>
        <?php
    }
}

if(!function_exists('wordpatch_errorbox_draw')) {
    function wordpatch_errorbox_draw($wpenv_vars, $error_key, $where, $error_vars) {
        ?>
        <div class="wordpatch_errorbox">
            <?php echo(htmlspecialchars(wordpatch_translate_error($wpenv_vars, $error_key, $where, $error_vars))); ?>
        </div>
        <?php
    }
}

if(!function_exists('wordpatch_log_field_render')) {
    function wordpatch_log_field_render($wpenv_vars, $title, $info, $plain_text = false) {
        if($plain_text) {
            echo(htmlspecialchars($title . "\n"));
            echo(nl2br(htmlspecialchars($info)) . "\n\n");

            return;
        }
        ?>
        <div class="wordpatch_log_field">
            <p class="wordpatch_log_field_title" style="font-weight: bold"><?php echo(htmlspecialchars($title)); ?></p>
            <p class="wordpatch_log_field_info"><?php echo(nl2br(htmlspecialchars($info))); ?></p>
        </div>
        <?php
    }
}

if(!function_exists('wordpatch_render_access_denied')) {
    function wordpatch_render_access_denied($wpenv_vars) {
        ?>
        <div class="wordpatch_page_generic_body">
            <h2 class="wordpatch_page_generic_heading"><?php echo(__wte($wpenv_vars, 'PAGE_ACCESS_DENIED_HEADING')); ?></h2>
            <p><?php echo __wte($wpenv_vars, 'PAGE_ACCESS_DENIED'); ?></p>
        </div>
        <?php
    }
}

if(!function_exists('wordpatch_render_generic_modal')) {
    function wordpatch_render_generic_modal($wpenv_vars, $heading_html, $text_html) {
        ?>
        <div class="wordpatch_page_generic_body">
            <h2 class="wordpatch_page_generic_heading"><?php echo($heading_html); ?></h2>
            <p><?php echo($text_html); ?></p>
        </div>
        <?php
    }
}

if(!function_exists('wordpatch_render_generic_modal_ex')) {
    function wordpatch_render_generic_modal_ex($wpenv_vars, $heading_html, $text_html, $check_errors, $error_list, $where, $error_vars) {
        ?>
        <div class="wordpatch_page_generic_body">
            <?php
            wordpatch_errors_maybe_draw_some($wpenv_vars, $error_list, $check_errors, $where, $error_vars);
            ?>
            <h2 class="wordpatch_page_generic_heading"><?php echo($heading_html); ?></h2>
            <p><?php echo($text_html); ?></p>
        </div>
        <?php
    }
}