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
 * The following functions render the fields for the new/edit patch forms.
 */

if(!function_exists('wordpatch_patchform_draw_patch_title_field')) {
    /**
     * Draw the patch title field within the new/edit patch form.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     * @param $where
     * @param $error_vars
     */
    function wordpatch_patchform_draw_patch_title_field($wpenv_vars, $current, $wizard_error_list, $where, $error_vars)
    {
        // Define which errors are relevant to the field.
        $field_errors = array(WORDPATCH_PATCH_TITLE_REQUIRED);

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'PATCH_FORM_TITLE_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'PATCH_FORM_TITLE_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'PATCH_FORM_TITLE_LABEL');

        // Draw the text field.
        wordpatch_wizard_draw_field_text($wpenv_vars, 'patch_title', $current['patch_title'],
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_patchform_draw_patch_path_field')) {
    /**
     * Draw the patch path field within the new/edit patch form.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     * @param $where
     * @param $error_vars
     */
    function wordpatch_patchform_draw_patch_path_field($wpenv_vars, $current, $wizard_error_list, $where, $error_vars)
    {
        // Define which errors are relevant to the field.
        $field_errors = array(WORDPATCH_PATCH_PATH_REQUIRED);

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'PATCH_FORM_PATH_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'PATCH_FORM_PATH_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'PATCH_FORM_PATH_LABEL');

        // Draw the text field.
        wordpatch_wizard_draw_field_text($wpenv_vars, 'patch_path', $current['patch_path'],
            $header_text, $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_patchform_draw_patch_simple_patch_field')) {
    /**
     * Draw the patch simple patch (code editor) within the new/edit patch form.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     * @param $where
     * @param $error_vars
     */
    function wordpatch_patchform_draw_patch_simple_patch_field($wpenv_vars, $current, $wizard_error_list, $where, $error_vars, $job_id) {
        $container_id = 'wordpatch_simple_patch_diff_ctr';

        $new_value = $current['patch_new_file'];
        $old_value = $current['patch_old_file'];

        $defaultMode = 'application/x-httpd-php';

        $modeMap = array(
            'application/x-httpd-php' => __wt($wpenv_vars, 'SIMPLE_PATCH_MODE_PHP'),
            'text/css' => __wt($wpenv_vars, 'SIMPLE_PATCH_MODE_CSS'),
            'text/x-scss' => __wt($wpenv_vars, 'SIMPLE_PATCH_MODE_SCSS'),
            'text/x-less' => __wt($wpenv_vars, 'SIMPLE_PATCH_MODE_LESS'),
            'text/javascript' => __wt($wpenv_vars, 'SIMPLE_PATCH_MODE_JAVASCRIPT'),
            'text/html' => __wt($wpenv_vars, 'SIMPLE_PATCH_MODE_HTML'),
            'text/xml' => __wt($wpenv_vars, 'SIMPLE_PATCH_MODE_XML'),
        );

        // Define which errors are relevant to the field.
        $field_errors = array(WORDPATCH_PATCH_CHANGES_REQUIRED);

        $has_error = wordpatch_errors_are_blocking($wizard_error_list, $field_errors);
        ?>
        <div class="wordpatch_metabox" id="wordpatch_patch_simple_patch_box">
            <div class="wordpatch_metabox_header"><?php echo(__wte($wpenv_vars, 'PATCH_FORM_SIMPLE_PATCH_HEADER')); ?></div>
            <div class="wordpatch_metabox_body <?php echo($has_error ? 'wordpatch_metabox_body_with_error' : ''); ?>">
                <?php echo(__wte($wpenv_vars, 'PATCH_FORM_SIMPLE_PATCH_DESC')); ?>
                <?php
                // Draw the field errors for this.
                wordpatch_wizard_inputbox_draw_field_errors($wpenv_vars, $where, $field_errors, $error_vars, $wizard_error_list);
                ?>

                <div class="wordpatch_code_editor_buttons">
                    <a href="#" class="wordpatch_button wordpatch_button_green wordpatch_button_load_old_file"><?php echo(__wten($wpenv_vars, 'SIMPLE_PATCH_LOAD_OLD_FILE')); ?></a>
                    <a href="#" class="wordpatch_button wordpatch_button_blue wordpatch_button_copy_old_file"><?php echo(__wten($wpenv_vars, 'SIMPLE_PATCH_COPY_OLD_FILE')); ?></a>
                </div>

                <div class="wordpatch_code_editor_modes">
                    <label>
                        <?php echo(__wten($wpenv_vars, 'SIMPLE_PATCH_MODE_SELECT')); ?>
                        <select class="wordpatch_code_editor_mode_select">
                            <?php foreach ($modeMap as $modeKey => $modeText) { ?>
                            <option <?php echo $defaultMode === $modeKey ? 'selected="selected"' : '' ?>
                                    value="<?php echo(htmlspecialchars($modeKey)); ?>"><?php echo(htmlspecialchars($modeText)); ?></option>
                            <?php } ?>
                        </select>
                    </label>
                </div>

                <input type="hidden" name="patch_old_file" value="" />
                <input type="hidden" name="patch_new_file" value="" />

                <div class="wordpatch-codemirror-ctr" id="<?php echo(htmlspecialchars($container_id)); ?>">
                    <div class="wordpatch-codemirror-view"></div>

                    <div class="wordpatch-codemirror-before-pane">
                        <div class="wordpatch_simple_labels_both">
                            <div class="wordpatch_simple_labels_both_left">
                                <p><?php echo(__wte($wpenv_vars, 'PATCH_FORM_SIMPLE_PATCH_OLD_FILE')); ?></p>
                            </div>
                            <div class="wordpatch_simple_labels_both_spacer"></div>
                            <div class="wordpatch_simple_labels_both_right">
                                <p><?php echo(__wte($wpenv_vars, 'PATCH_FORM_SIMPLE_PATCH_NEW_FILE')); ?></p>
                            </div>
                        </div>

                        <div class="wordpatch_simple_labels_just_left">
                            <p><?php echo(__wte($wpenv_vars, 'PATCH_FORM_SIMPLE_PATCH_OLD_FILE')); ?></p>
                        </div>
                    </div>

                    <div class="wordpatch-codemirror-after-gap">
                        <div class="wordpatch_simple_labels_just_right">
                            <p><?php echo(__wte($wpenv_vars, 'PATCH_FORM_SIMPLE_PATCH_NEW_FILE')); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
        $default_error = WORDPATCH_UNKNOWN_ERROR;
        $default_error_translation = wordpatch_translate_error($wpenv_vars, $default_error, WORDPATCH_WHERE_NEWPATCH,
            array());
        ?>
        <script type="text/javascript">
            (function($) {
                $(document).ready(function() {
                    var containerSelector = '#' + <?php echo json_encode($container_id); ?>;
                    var codemirrorView = $(containerSelector + ' .wordpatch-codemirror-view').eq(0);
                    var codemirrorViewElem = codemirrorView.eq(0)[0];

                    var extMap = {
                        'php': 'application/x-httpd-php',
                        'php3': 'application/x-httpd-php',
                        'php4': 'application/x-httpd-php',
                        'php5': 'application/x-httpd-php',
                        'php6': 'application/x-httpd-php',
                        'php7': 'application/x-httpd-php',
                        'phtml': 'application/x-httpd-php',
                        'css': 'text/css',
                        'js': 'text/javascript',
                        'jsx': 'text/javascript',
                        'scss': 'text/x-scss',
                        'sass': 'text/x-scss',
                        'less': 'text/x-less',
                        'htm': 'text/html',
                        'html': 'text/html',
                        'xhtml': 'text/html',
                        'jhtml': 'text/html',
                        'xml': 'text/xml',
                        'rss': 'text/xml',
                        'svg': 'text/xml'
                    };

                    window['wordpatchMergeView'] = window['CodeMirror'].MergeView(codemirrorViewElem, {
                        value: <?php echo json_encode($new_value); ?>,
                        origLeft: <?php echo json_encode($old_value); ?>,
                        orig: null,
                        lineNumbers: true,
                        indentUnit: 4,
                        indentWithTabs: true,
                        mode: "application/x-httpd-php",
                        revertButtons: false,
                        highlightDifferences: true,
                        allowEditingOriginals: true,
                        connect: 'align',
                        theme: 'mdn-like',
                        collapseIdentical: false,
                        showTrailingSpace: true,
                        scrollbarStyle: 'simple',
                        autoRefresh: true
                    });

                    var modeSelect = $('#wordpatch_patch_simple_patch_box .wordpatch_code_editor_mode_select');

                    var onModeChange = function() {
                        var selectedValue = $(modeSelect).val();

                        window['wordpatchMergeView'].editor().setOption('mode', selectedValue);
                        window['wordpatchMergeView'].leftOriginal().setOption('mode', selectedValue);
                    };

                    modeSelect.change(function() {
                        onModeChange();
                    });

                    var copyButton = $('#wordpatch_patch_simple_patch_box .wordpatch_button_copy_old_file');
                    var copyLocked = false;

                    copyButton.click(function() {
                        if(copyLocked) {
                            return false;
                        }

                        copyLocked = true;

                        window['wordpatchAreYouSure'](<?php echo json_encode(__wt($wpenv_vars, 'PATCH_FORM_SIMPLE_PATCH_COPY_ARE_YOU_SURE_TITLE')); ?>,
                            <?php echo json_encode(__wt($wpenv_vars, 'PATCH_FORM_SIMPLE_PATCH_COPY_ARE_YOU_SURE_MESSAGE')); ?>, function() {
                                var oldValue = window['wordpatchMergeView'].leftOriginal().getDoc().getValue();
                                window['wordpatchMergeView'].editor().getDoc().setValue(oldValue);

                                copyLocked = false;
                            }, function() {
                                copyLocked = false;
                            });

                        return false;
                    });

                    var filePathInput = $('#patch_path');

                    var autodetectExtension = function() {
                        var filePath = $.trim(filePathInput.val());

                        if(filePath === '' || filePath.indexOf('.') === -1) {
                            return;
                        }

                        var fileExt = filePath.substr(filePath.lastIndexOf('.') + 1).toLowerCase();

                        if(!extMap.hasOwnProperty(fileExt)) {
                            return;
                        }

                        modeSelect.val(extMap[fileExt]);
                        onModeChange();
                    };

                    filePathInput.change(function() {
                        autodetectExtension();
                    });

                    autodetectExtension();
                    
                    var loadOldButton = $('#wordpatch_patch_simple_patch_box .wordpatch_button_load_old_file');
                    var loadOldLocked = false;

                    loadOldButton.click(function() {
                        if(loadOldLocked) {
                            return false;
                        }

                        loadOldLocked = true;
                        var filePath = $.trim(filePathInput.val());

                        if(!filePath || filePath === '') {
                            window['wordpatchErrorNotice_Show'](<?php echo json_encode(__wt($wpenv_vars, 'PATCH_FORM_SIMPLE_PATCH_PATH_REQUIRED_TITLE')); ?>,
                                <?php echo json_encode(__wt($wpenv_vars, 'PATCH_FORM_SIMPLE_PATCH_PATH_REQUIRED_MESSAGE')); ?>, function() {
                                loadOldLocked = false;
                            });
                        } else {
                            window['wordpatchAreYouSure'](<?php echo json_encode(__wt($wpenv_vars, 'PATCH_FORM_SIMPLE_PATCH_ARE_YOU_SURE_TITLE')); ?>,
                                <?php echo json_encode(__wt($wpenv_vars, 'PATCH_FORM_SIMPLE_PATCH_ARE_YOU_SURE_MESSAGE')); ?>, function() {
                                window['wordpatchLoading_Show']();

                                $.ajax({
                                    url: <?php echo json_encode(wordpatch_loadjobfile_uri($wpenv_vars, $job_id)); ?>,
                                    method: 'POST',
                                    data: {
                                        'submit': '1',
                                        'file_path': filePath
                                    },
                                    complete: function(xhr) {
                                        // Remove the loading dialog once the request has completed
                                        window['wordpatchLoading_Hide']();

                                        var default_error_translation = <?php echo json_encode($default_error_translation); ?>;

                                        if(!xhr || xhr.status >= 400 || $.trim(xhr.responseText) === '') {
                                            window['wordpatchErrorNotice_Show'](window['wordpatchErrorNotice_GetDefaultTitle'](),
                                                default_error_translation, function() {
                                                    loadOldLocked = false;
                                                });
                                            return;
                                        }

                                        var responseData = $.parseJSON(xhr.responseText);

                                        if(responseData['error']) {
                                            window['wordpatchErrorNotice_Show'](window['wordpatchErrorNotice_GetDefaultTitle'](),
                                                responseData['error_translation'], function() {
                                                    loadOldLocked = false;
                                                });
                                            return;
                                        }

                                        var fileContents = responseData['file_contents'];

                                        if(fileContents === null || fileContents === false) {
                                            fileContents = '';
                                        } else {
                                            fileContents = window['atob'](fileContents);
                                        }

                                        window['wordpatchMergeView'].leftOriginal().getDoc().setValue(fileContents);
                                        loadOldLocked = false;
                                    }
                                });
                            }, function() {
                                loadOldLocked = false;
                            });
                        }

                        return false;
                    });

                    var codemirrorGap = codemirrorView.find('.CodeMirror-merge-gap').eq(0);
                    var afterGap = $(containerSelector + ' .wordpatch-codemirror-after-gap').eq(0);

                    codemirrorGap.after(afterGap);

                    var leftSide = $(containerSelector + ' .CodeMirror-merge-left').eq(0);
                    var beforePane = $(containerSelector + ' .wordpatch-codemirror-before-pane').eq(0);

                    leftSide.before(beforePane);
                });
            })(jQuery);
        </script>
        <?php
    }
}

if(!function_exists('wordpatch_patchform_draw_patch_type_field')) {
    /**
     * Draw the patch type field within the new/edit patch form.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     * @param $where
     * @param $error_vars
     */
    function wordpatch_patchform_draw_patch_type_field($wpenv_vars, $current, $wizard_error_list, $where, $error_vars)
    {
        // Define which errors are relevant to the field.
        $field_errors = array();

        // Calculate the header text.
        $header_text = __wt($wpenv_vars, 'PATCH_FORM_TYPE_HEADER');

        // Calculate the description text.
        $desc_text = __wt($wpenv_vars, 'PATCH_FORM_TYPE_DESC');

        // Calculate the label text.
        $label_text = __wt($wpenv_vars, 'PATCH_FORM_TYPE_LABEL');

        // Calculate the dropdown information.
        $dropdown = wordpatch_dropdown($wpenv_vars, wordpatch_patch_types(), 'wordpatch_display_patch_type');

        // Draw the select field.
        wordpatch_wizard_draw_field_select($wpenv_vars, 'patch_type', $current['patch_type'], $dropdown, $header_text,
            $desc_text, $label_text, $where, $field_errors, $error_vars, $wizard_error_list);
    }
}

if(!function_exists('wordpatch_patchform_draw_patch_data_field')) {
    /**
     * Draw the patch title field within the new/edit patch form.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     * @param $where
     * @param $error_vars
     */
    function wordpatch_patchform_draw_patch_data_field($wpenv_vars, $current, $wizard_error_list, $where, $error_vars)
    {
        // Define which errors are relevant to the field.
        $field_errors = array(WORDPATCH_PATCH_DATA_REQUIRED);

        $has_error = wordpatch_errors_are_blocking($wizard_error_list, $field_errors);
        ?>
        <div class="wordpatch_metabox" id="wordpatch_patch_data_box">
            <div class="wordpatch_metabox_header"><?php echo(__wte($wpenv_vars, 'PATCH_FORM_DATA_HEADER')); ?></div>
            <div class="wordpatch_metabox_body <?php echo($has_error ? 'wordpatch_metabox_body_with_error' : ''); ?>">
                <?php echo(__wte($wpenv_vars, 'PATCH_FORM_DATA_DESC')); ?>
                <?php
                // Draw the field errors for this.
                wordpatch_wizard_inputbox_draw_field_errors($wpenv_vars, $where, $field_errors, $error_vars, $wizard_error_list);
                ?>
                <label for="patch_data" class="wordpatch_input_label ">
                    <?php echo(__wte($wpenv_vars, 'PATCH_FORM_DATA_LABEL')); ?>
                </label>
                <textarea class="wordpatch_input_text" name="patch_data"
                          id="patch_data"><?php echo(htmlspecialchars($current['patch_type'] === wordpatch_patch_type_file() ? '' : $current['patch_data'])); ?></textarea>
            </div>
        </div>
        <?php
    }
}

if(!function_exists('wordpatch_patchform_draw_patch_file_field')) {
    /**
     * Draw the patch file field within the new/edit patch form.
     *
     * @param $wpenv_vars
     * @param $current
     * @param $wizard_error_list
     * @param $where
     * @param $error_vars
     * @param null|array $patch
     */
    function wordpatch_patchform_draw_patch_file_field($wpenv_vars, $current, $wizard_error_list, $where, $error_vars, $patch = null)
    {
        $upload_key = wordpatch_upload_key();
        $esc_upload_key = htmlspecialchars($upload_key);
        $max_upload_size = wordpatch_max_upload_size();
        $max_upload_size_display = wordpatch_size_format($max_upload_size);

        // Define which errors are relevant to the field.
        $field_errors = array(WORDPATCH_PATCH_FILE_REQUIRED);
        $max_upload_html = sprintf('<p class="patch_form_file_max">%s <span class="patch_form_file_max_num">%s</span></p>', __wt($wpenv_vars, 'PATCH_FORM_FILE_MAX'), $max_upload_size_display);
        ?>
        <div class="wordpatch_metabox" id="wordpatch_patch_file_box" style="display: none">
            <div class="wordpatch_metabox_header"><?php echo __wte($wpenv_vars, 'PATCH_FORM_FILE_HEADER'); ?></div>
            <div class="wordpatch_metabox_body wordpatch_metabox_body_multi">
                <?php echo __wt($wpenv_vars, 'PATCH_FORM_FILE_DESC'); ?>
                <?php echo $max_upload_html ?>
                <?php
                // Draw the field errors for this.
                wordpatch_wizard_inputbox_draw_field_errors($wpenv_vars, $where, $field_errors, $error_vars, $wizard_error_list);
                ?>

                <div class="wordpatch_existing_file">
                    <span class="wordpatch_existing_label"><?php echo __wte($wpenv_vars, 'PATCH_FORM_FILE_LABEL_EXISTING'); ?></span>
                    <?php
                    $patch_file_exists = $patch['id'] !== null && $patch['patch_type'] === wordpatch_patch_type_file() &&
                        $patch['patch_location'] !== '';
                    ?>
                    <?php if($patch_file_exists) { ?>
                        <?php $currently_uploaded_size = $patch['patch_size']; ?>
                        <span class="wordpatch_existing_size"><?php echo(wordpatch_size_format($currently_uploaded_size)); ?></span>
                        <a class="wordpatch_link wordpatch_existing_remove" href="#" data-patch-id="<?php echo htmlspecialchars($patch['id']); ?>">
                            <?php echo __wten($wpenv_vars, 'PATCH_FORM_FILE_DELETE'); ?></a>
                    <?php } ?>

                    <span class="wordpatch_existing_nothing" style="<?php echo($patch_file_exists ? 'display:none' : ''); ?>">
                        <?php echo __wte($wpenv_vars, 'PATCH_FORM_FILE_EMPTY'); ?></span>
                </div>

                <div class="wordpatch_upload_flex_container">
                    <label class="wordpatch_button wordpatch_button_green wordpatch_input_label_file" for="<?php echo $esc_upload_key; ?>">
                        <?php echo __wte($wpenv_vars, 'PATCH_FORM_FILE_LABEL'); ?>
                        <input type="file" name="<?php echo $esc_upload_key; ?>" id="<?php echo $esc_upload_key; ?>" />
                    </label>
                    <span id="wordpatch_upload_filename"></span>
                </div>
            </div>
        </div>
        <?php
    }
}