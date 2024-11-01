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
 * Implements the header and the footer for the email template.
 */

if(!function_exists('wordpatch_mail_layout_header_html')) {
    /**
     * Calculates and returns the header HTML for emails.
     *
     * @param $vars
     * @param $subject
     * @param bool $lite
     * @return string
     */
    function wordpatch_mail_layout_header_html($wpenv_vars, $vars, $subject, $lite = false) {
        // Build our CSS string
        $css = wordpatch_mail_before_styles() . "\n";
        $css .= wordpatch_mail_class_styles_html() . "\n";
        $css .= wordpatch_mail_after_styles();

        $site_url = wordpatch_trailingslashit(
            wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_site_url')) .
            wordpatch_unleadingslashit(wordpatch_env_get($wpenv_vars, 'wp_wordpatch_path'))
        );

        ob_start();
        ?>
        <?php if(!$lite) { ?>
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml">
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1" />
            <title><?php echo(htmlspecialchars($subject)); ?></title>

            <?php echo($css); ?>
        </head>

        <body bgcolor="#f7f7f7" class="font-elem body-elem" style="">
        <?php } ?>
        <table align="center" cellpadding="0" cellspacing="0" width="100%" class="font-elem table-elem container-for-gmail-android" style="">
            <tr class="font-elem tr-elem" style="">
                <td align="left" valign="top" width="100%" class="font-elem td-elem" style="background:repeat-x url(http://s3.amazonaws.com/swu-filepicker/4E687TRe69Ld95IDWyEg_bg_top_02.jpg) #ffffff;">
                    <center class="font-elem" style="">
                        <img src="<?php echo htmlspecialchars($site_url . "assets/img/SBb2fQPrQ5ezxmqUTgCr_transparent.png") ?>" class="font-elem img-elem force-width-gmail" style="">
                        <table cellspacing="0" cellpadding="0" width="100%" bgcolor="#ffffff" background="<?php echo htmlspecialchars($site_url . "assets/img/4E687TRe69Ld95IDWyEg_bg_top_02.jpg") ?>" class="font-elem table-elem" style="background-color:transparent">
                            <tr class="font-elem tr-elem" style="">
                                <td width="100%" height="80" valign="top" class="font-elem td-elem" style="text-align: center; vertical-align:middle;">
                                    <!--[if gte mso 9]>
                                    <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="mso-width-percent:1000;height:80px; v-text-anchor:middle;">
                                        <v:fill type="tile" src="<?php echo htmlspecialchars($site_url . "assets/4E687TRe69Ld95IDWyEg_bg_top_02.jpg") ?>" color="#ffffff" />
                                        <v:textbox inset="0,0,0,0">
                                    <![endif]-->
                                    <center class="font-elem" style="">
                                        <table cellpadding="0" cellspacing="0" width="600" class="font-elem table-elem w320" style="">
                                            <tr class="font-elem tr-elem" style="">
                                                <td class="font-elem td-elem pull-left mobile-header-padding-left" style="vertical-align: middle;">
                                                    <a href="https://jointbyte.com/" class="font-elem link-elem" style="">
                                                        <img width="150" height="22" src="<?php echo htmlspecialchars($site_url . "assets/img/wp_txt_sm.png") ?>" alt="logo" class="font-elem img-elem link-img">
                                                    </a>
                                                </td>
                                                <td class="font-elem td-elem pull-right mobile-header-padding-right" style="color: #4d4d4d;">
                                                    <a href="https://jointbyte.freshdesk.com/support/home/" class="font-elem link-elem header-link" style="">Support</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </center>
                                    <!--[if gte mso 9]>
                                    </v:textbox>
                                    </v:rect>
                                    <![endif]-->
                                </td>
                            </tr>
                        </table>
                    </center>
                </td>
            </tr>
            <tr class="font-elem tr-elem" style="">
                <td align="center" valign="top" width="100%" class="font-elem td-elem content-padding" style="background-color: #f7f7f7;">
                    <center class="font-elem" style="">
        <?php
        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    }
}

if(!function_exists('wordpatch_mail_layout_footer_html')) {
    /**
     * Calculates and returns the footer HTML for emails.
     *
     * @param $vars
     * @param $subject
     * @param bool $lite
     * @return string
     */
    function wordpatch_mail_layout_footer_html($vars, $subject, $lite = false) {
        ob_start();
        ?>
                    </center>
                </td>
            </tr>
            <tr class="font-elem tr-elem" style="">
                <td align="center" valign="top" width="100%" class="font-elem td-elem" style="background-color: #f7f7f7; height: 100px;">
                    <center class="font-elem" style="">
                        <table cellspacing="0" cellpadding="0" width="600" class="font-elem table-elem w320" style="">
                            <tr class="font-elem tr-elem" style="">
                                <td class="font-elem td-elem" style="padding: 25px 0 25px">
                                    <strong class="font-elem" style="">&copy; <a href="https://jointbyte.com/">JointByte</a></strong><br />
                                    <a href="https://jointbyte.com/privacy-policy/" class="font-elem link-elem" style="">Privacy Policy</a> | <a href="https://jointbyte.com/terms-of-use/" class="font-elem link-elem" style="">Terms of Use</a><br/>
                                    You are receiving this e-mail because of an active JointByte product subscription. You can change your preferences from within that product.
                                </td>
                            </tr>
                        </table>
                    </center>
                </td>
            </tr>
        </table>
        <?php if(!$lite) { ?>
        </body>
        </html>
        <?php } ?>
        <?php
        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    }
}

if(!function_exists('wordpatch_mail_layout_header_text')) {
    /**
     * Calculates and returns the header plain text for emails.
     *
     * @param $vars
     * @param $subject
     * @return string
     */
    function wordpatch_mail_layout_header_text($vars, $subject) {
        ob_start();
        ?>
        FAQ: [link]
        Support: [link]

        <?php
        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    }
}

if(!function_exists('wordpatch_mail_layout_footer_text')) {
    /**
     * Calculates and returns the footer plain text for emails.
     *
     * @param $vars
     * @param $subject
     * @return string
     */
    function wordpatch_mail_layout_footer_text($vars, $subject) {
        ob_start();
        ?>
        yours! Ltd
        Copyright / Privacy Copy: [link]
        <?php
        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    }
}