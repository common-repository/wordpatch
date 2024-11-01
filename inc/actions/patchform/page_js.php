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
 * Implements the page javascript for the patch form pages.
 */

if(!function_exists('wordpatch_patchform_draw_page_js')) {
    /**
     * Draw the javascript for the patch form pages.
     *
     * @param $wpenv_vars
     * @param null|string $patch_id
     */
    function wordpatch_patchform_draw_page_js($wpenv_vars, $patch_id = null) {
        ?>
        <script type="text/javascript">
            (function($) {
                var patchTypeText = <?php echo(json_encode(wordpatch_patch_type_text())); ?>;
                var patchTypeFile = <?php echo(json_encode(wordpatch_patch_type_file())); ?>;
                var patchTypeSimple = <?php echo(json_encode(wordpatch_patch_type_simple())); ?>;

                var updatePatchForm = function() {
                    var patchType = $('.wordpatch_patch_form :input[name=patch_type]').val();

                    if(patchType === patchTypeText) {
                        $('#wordpatch_patch_data_box').show();
                        $('#wordpatch_patch_file_box').hide();
                        $('.wordpatch_simple_patch_ctr').hide();
                    } else if(patchType === patchTypeFile) {
                        $('#wordpatch_patch_data_box').hide();
                        $('#wordpatch_patch_file_box').show();
                        $('.wordpatch_simple_patch_ctr').hide();
                    } else if(patchType === patchTypeSimple) {
                        $('#wordpatch_patch_data_box').hide();
                        $('#wordpatch_patch_file_box').hide();
                        $('.wordpatch_simple_patch_ctr').show();
                    }
                };

                $('.wordpatch_patch_form :input[name=patch_type]').change(function() {
                    updatePatchForm();
                });

                $('#wordpatch_upload_file').change(function(e) {
                    var filename = $('#wordpatch_upload_file').val().split('\\').pop();

                    $('#wordpatch_upload_filename').text(filename);
                });

                $('.wordpatch_patch_form').submit(function() {
                    var patchType = $('.wordpatch_patch_form :input[name=patch_type]').val();

                    if(patchType === patchTypeSimple) {
                        var oldValue = window['wordpatchMergeView'].leftOriginal().getDoc().getValue();
                        var newValue = window['wordpatchMergeView'].editor().getDoc().getValue();

                        $('.wordpatch_patch_form :input[name=patch_old_file]').val(window['wordpatchB64_Encode'](oldValue));
                        $('.wordpatch_patch_form :input[name=patch_new_file]').val(window['wordpatchB64_Encode'](newValue));
                    } else {
                        $('.wordpatch_patch_form :input[name=patch_old_file]').val('');
                        $('.wordpatch_patch_form :input[name=patch_new_file]').val('');
                    }

                    return true;
                });

                <?php if($patch_id) { ?>
                <?php
                $default_error = WORDPATCH_UNKNOWN_ERROR;
                $default_error_translation = wordpatch_translate_error($wpenv_vars, $default_error, WORDPATCH_WHERE_ERASEPATCH, array());
                ?>
                $('.wordpatch_existing_remove').click(function() {
                    var removeLink = $(this);

                    var reenableRemoveLink = function() {
                        removeLink.removeClass('wordpatch_existing_remove_busy');
                    };

                    if(removeLink.hasClass('wordpatch_existing_remove_busy')) {
                        return false;
                    }

                    var removeCallback = function() {
                        removeLink.addClass('wordpatch_existing_remove_busy');

                        window['wordpatchLoading_Show'](window['wordpatchLoading_GetDefaultTitle'](),
                            <?php echo(json_encode(__wt($wpenv_vars, 'PATCH_FORM_REMOVE_LOADING'))); ?>);

                        $.ajax({
                            url: <?php echo json_encode(wordpatch_erasepatch_uri($wpenv_vars, $patch_id)); ?>,
                            method: 'POST',
                            data: {
                                'submit': '1',
                                'patch_erase': <?php echo json_encode(WORDPATCH_YES); ?>
                            },
                            complete: function(xhr) {
                                // Remove the loading dialog once the request has completed
                                window['wordpatchLoading_Hide']();

                                var default_error_translation = <?php echo json_encode($default_error_translation); ?>;

                                if(!xhr || xhr.status >= 400 || $.trim(xhr.responseText) === '') {
                                    window['wordpatchErrorNotice_Show'](window['wordpatchErrorNotice_GetDefaultTitle'](),
                                        default_error_translation, reenableRemoveLink);
                                    return;
                                }

                                var responseData = $.parseJSON(xhr.responseText);

                                if(responseData['error']) {
                                    window['wordpatchErrorNotice_Show'](window['wordpatchErrorNotice_GetDefaultTitle'](),
                                        responseData['error_translation'], reenableRemoveLink);
                                    return;
                                }

                                // Update the UI (remove the size and the remove button, then show the message element)
                                $('.wordpatch_existing_size').remove();
                                $('.wordpatch_existing_remove').remove();
                                $('.wordpatch_existing_nothing').show();
                            }
                        });
                    };

                    window['wordpatchAreYouSure'](wordpatchAreYouSure_GetDefaultTitle(),
                        <?php echo(json_encode(__wt($wpenv_vars, 'PATCH_FORM_REMOVE_MESSAGE'))); ?>, removeCallback);

                    return false;
                });
                <?php } ?>

                updatePatchForm();

                $(document).ready(function() {
                    updatePatchForm();
                });
            })(jQuery);
        </script>
        <?php
    }
}