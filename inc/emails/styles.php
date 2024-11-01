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
 * Implements the functionality regarding email styles, namely the ability to inline them into an email template.
 */

if(!function_exists('wordpatch_mail_before_styles')) {
    /**
     * Returns the mailer styles to be printed before the class styles section.
     *
     * @return string
     */
    function wordpatch_mail_before_styles() {
        ob_start();
        ?>
        <style type="text/css">
            /* Take care of image borders and formatting, client hacks */
            img { max-width: 600px; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic;}
            a img { border: none; }
            table { border-collapse: collapse !important;}
            #outlook a { padding:0; }
            .ReadMsgBody { width: 100%; }
            .ExternalClass { width: 100%; }
            .backgroundTable { margin: 0 auto; padding: 0; width: 100% !important; }
            table td { border-collapse: collapse; }
            .ExternalClass * { line-height: 115%; }
            .container-for-gmail-android { min-width: 600px; }
        </style>
        <?php
        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    }
}

if(!function_exists('wordpatch_mail_class_styles')) {
    /**
     * Returns the mailer class styles associative array that will be inlined and rendered directly.
     *
     * @return array
     */
    function wordpatch_mail_class_styles() {
        // Return our calculated class styles array.
        return array(
            array(
                'classes' => array('img-elem'),
                'max-width' => '600px',
                'outline' => 'none',
                'text-decoration' => 'none',
                '-ms-interpolation-mode' => 'bicubic'
            ),
            array(
                'classes' => array('link-img'),
                'border' => 'none'
            ),
            array(
                'classes' => array('table-elem'),
                'border-collapse' => 'collapse !important'
            ),
            array(
                'classes' => array('container-for-gmail-android'),
                'min-width' => '600px'
            ),
            array(
                'classes' => array('font-elem'),
                'font-family' => 'Helvetica, Arial, sans-serif'
            ),
            array(
                'classes' => array('body-elem'),
                '-webkit-font-smoothing' => 'antialiased',
                '-webkit-text-size-adjust' => 'none',
                'width' => '100% !important',
                'margin' => '0 !important',
                'height' => '100%',
                'color' => '#676767'
            ),
            array(
                'classes' => array('td-elem'),
                'border-collapse' => 'collapse',
                'font-family' => 'Helvetica, Arial, sans-serif',
                'font-size' => '14px',
                'color' => '#777777',
                'text-align' => 'center',
                'line-height' => '21px'
            ),
            array(
                'classes' => array('link-elem'),
                'color' => '#676767',
                'text-decoration' => 'none !important'
            ),
            array(
                'classes' => array('pull-left'),
                'text-align' => 'left'
            ),
            array(
                'classes' => array('pull-right'),
                'text-align' => 'right'
            ),
            array(
                'classes' => array('header-lg', 'header-md', 'header-sm'),
                'font-size' => '32px',
                'font-weight' => '700',
                'line-height' => 'normal',
                'padding' => '35px 0 0',
                'color' => '#4d4d4d'
            ),
            array(
                'classes' => array('header-md'),
                'font-size' => '24px'
            ),
            array(
                'classes' => array('header-sm'),
                'padding' => '5px 0',
                'font-size' => '18px',
                'line-height' => '1.3'
            ),
            array(
                'classes' => array('content-padding'),
                'padding' => '20px 0 30px'
            ),
            array(
                'classes' => array('mobile-header-padding-right'),
                'width' => '290px',
                'text-align' => 'right',
                'padding-left' => '10px'
            ),
            array(
                'classes' => array('mobile-header-padding-left'),
                'width' => '290px',
                'text-align' => 'left',
                'padding-left' => '10px'
            ),
            array(
                'classes' => array('free-text'),
                'width' => '100% !important',
                'padding' => '10px 60px 0px'
            ),
            array(
                'classes' => array('block-rounded'),
                'border-radius' => '5px',
                'border' => '1px solid #e5e5e5',
                'vertical-align' => 'top',
                'width' => '260px'
            ),
            array(
                'classes' => array('button'),
                'padding' => '0'
            ),
            array(
                'classes' => array('info-block'),
                'padding' => '0 20px',
                'width' => '260px'
            ),
            array(
                'classes' => array('mini-block-container'),
                'padding' => '30px 50px',
                'width' => '500px'
            ),
            array(
                'classes' => array('mini-block'),
                'background-color' => '#ffffff',
                'width' => '498px',
                'border' => '1px solid #cccccc',
                'border-radius' => '5px',
                'padding' => '22px 18px'
            ),
            array(
                'classes' => array('info-img'),
                'width' => '258px',
                'border-radius' => '5px 5px 0 0'
            ),
            array(
                'classes' => array('force-width-img'),
                'width' => '480px',
                'height' => '1px !important'
            ),
            array(
                'classes' => array('force-width-full'),
                'width' => '600px',
                'height' => '1px !important'
            ),
            array(
                'classes' => array('user-img-elem'),
                'width' => '82px',
                'border-radius' => '5px',
                'border' => '1px solid #cccccc'
            ),
            array(
                'classes' => array('user-img'),
                'width' => '92px',
                'text-align' => 'left'
            ),
            array(
                'classes' => array('user-msg'),
                'width' => '236px',
                'font-size' => '14px',
                'text-align' => 'left',
                'font-style' => 'italic'
            ),
            array(
                'classes' => array('code-block'),
                'padding' => '10px 0',
                'border' => '1px solid #cccccc',
                'color' => '#4d4d4d',
                'font-weight' => 'bold',
                'font-size' => '18px',
                'text-align' => 'center'
            ),
            array(
                'classes' => array('force-width-gmail'),
                'min-width' => '600px',
                'height' => '0px !important',
                'line-height' => '1px !important',
                'font-size' => '1px !important'
            ),
            array(
                'classes' => array('button-width'),
                'width' => '228px'
            ),
            array(
                'classes' => array('header-link'),
                'margin-left' => '18px',
                'text-decoration' => 'underline'
            )
        );
    }
}

if(!function_exists('wordpatch_mail_class_styles_html')) {
    function wordpatch_mail_class_styles_html() {
        // Start constructing our HTML
        $html = "<style type=\"text/css\">\n";

        // Grab our class styles associative array
        $class_styles = wordpatch_mail_class_styles();

        // Loop through each class style and create the appropriate CSS string
        foreach($class_styles as $rule_info) {
            // We'll need to add commas between class names, so create a bool
            $is_first_class = true;

            // Loop each classname and append (add comma before if not first)
            foreach($rule_info['classes'] as $rule_class_single) {
                if(!$is_first_class) {
                    $html .= ", ";
                }

                $html .= ".$rule_class_single";
                $is_first_class = false;
            }

            // Add the rule's starting brace
            $html .= " {\n";

            // Add each property and ignore "classes" key
            foreach($rule_info as $prop_key => $prop_value) {
                if($prop_key === 'classes') {
                    continue;
                }

                $html .= "  $prop_key: $prop_value;\n";
            }

            // Add the rule's ending brace
            $html .= "}\n";
        }

        // Append the end style tag
        $html .= "</style>";

        // Return the calculated HTML
        return $html;
    }
}

if(!function_exists('wordpatch_mail_after_styles')) {
    /**
     * Returns the mailer styles to be printed after the class styles section.
     *
     * @return string
     */
    function wordpatch_mail_after_styles() {
        ob_start();
        ?>
        <style type="text/css" media="screen">
            @import url(http://fonts.googleapis.com/css?family=Oxygen:400,700);
        </style>

        <style type="text/css" media="screen">
            @media screen {
                /* Thanks Outlook 2013! */
                * {
                    font-family: 'Oxygen', 'Helvetica Neue', 'Arial', 'sans-serif' !important;
                }
            }
        </style>

        <style type="text/css" media="only screen and (max-width: 480px)">
            /* Mobile styles */
            @media only screen and (max-width: 480px) {
                .button {
                    display: block !important;
                }

                table[class*="container-for-gmail-android"] {
                    min-width: 290px !important;
                    width: 100% !important;
                }

                table[class*="w320"] {
                    width: 320px !important;
                }

                img[class*="force-width-gmail"] {
                    display: none !important;
                    width: 0 !important;
                    height: 0 !important;
                }

                a[class*="button-width"],
                a[class*="button-mobile"] {
                    width: 248px !important;
                }

                td[class*="mobile-header-padding-left"] {
                    width: 160px !important;
                    padding-left: 0 !important;
                }

                td[class*="mobile-header-padding-right"] {
                    width: 160px !important;
                    padding-right: 0 !important;
                }

                td[class*="header-lg"] {
                    font-size: 24px !important;
                    padding-bottom: 5px !important;
                }

                td[class*="header-md"] {
                    font-size: 18px !important;
                    padding-bottom: 5px !important;
                }

                td[class*="content-padding"] {
                    padding: 5px 0 30px !important;
                }

                td[class*="button"] {
                    padding: 15px 0 5px !important;
                }

                td[class*="free-text"] {
                    padding: 10px 18px 30px !important;
                }

                img[class*="force-width-img"],
                img[class*="force-width-full"] {
                    display: none !important;
                }

                td[class*="info-block"] {
                    display: block !important;
                    width: 280px !important;
                    padding-bottom: 40px !important;
                }

                td[class*="info-img"],
                img[class*="info-img"] {
                    width: 278px !important;
                }

                td[class*="mini-block-container"] {
                    padding: 8px 20px !important;
                    width: 280px !important;
                }

                td[class*="mini-block"] {
                    padding: 20px !important;
                }

                td[class*="user-img"] {
                    display: block !important;
                    text-align: center !important;
                    width: 100% !important;
                    padding-bottom: 10px;
                }

                td[class*="user-msg"] {
                    display: block !important;
                    padding-bottom: 20px !important;
                }
            }
        </style>
        <?php
        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    }
}

if(!function_exists('wordpatch_mail_inline_style__callback')) {
    /**
     * Regex replacement callback used by `wordpatch_mail_inline_styles`.
     *
     * @param $matches
     * @return string
     */
    function wordpatch_mail_inline_style__callback($matches) {
        // Explode the class name string
        $class_pieces = explode(' ', $matches[1]);

        // Calculate the class names array by looping through the pieces. Don't add duplicate or empty. Trim first.
        $class_names = array();

        foreach($class_pieces as $class_piece) {
            $class_piece = trim($class_piece);

            if(in_array($class_piece, $class_names)) {
                continue;
            }

            $class_names[] = $class_piece;
        }

        // Grab the existing styles attribute
        $existing_styles = isset($matches[2]) ? trim($matches[2]) : "";

        // Grab our class styles associative array
        $class_styles = wordpatch_mail_class_styles();

        // Get our list of rules
        $rule_pieces = explode(';', $existing_styles);

        // Start to calculate our rule list
        $rule_list_existing = array();

        // Parse rule pieces into our list
        foreach($rule_pieces as $rule_piece) {
            $rule_piece = trim($rule_piece);

            if($rule_piece === '') {
                continue;
            }

            $first_colon_pos = strpos($rule_piece, ':');

            if($first_colon_pos === false) {
                continue;
            }

            $piece_key = trim(substr($rule_piece, 0, $first_colon_pos));
            $piece_value = trim(substr($rule_piece, $first_colon_pos + 1));

            if($piece_key === '' || $piece_value === '') {
                continue;
            }

            $rule_list_existing[] = array(
                'key' => $piece_key,
                'value' => $piece_value
            );
        }

        // Create a list for our class styles rule list
        $rule_list_classes = array();

        // Go through each class name and find matching class style rules
        foreach($class_names as $class_name) {
            // Loop the class styles and see if we can find any matches.
            foreach($class_styles as $class_style) {
                // If the class name is not in the list, skip it.
                if(!in_array($class_name, $class_style['classes'])) {
                    continue;
                }

                foreach($class_style as $prop_key => $prop_value) {
                    if($prop_key === 'classes') {
                        continue;
                    }

                    $rule_list_classes[] = array(
                        'key' => $prop_key,
                        'value' => $prop_value
                    );
                }
            }
        }

        // Merge the two lists so we can start to construct our final list
        $both_lists = array_merge($rule_list_classes, $rule_list_existing);

        // Create an array for our final rule list
        $final_rule_list = array();

        foreach($both_lists as $rule_info) {
            // Loop through final rule list and make sure we aren't adding a duplicate.
            $already_in_list = false;

            foreach($final_rule_list as $check_rule) {
                if($check_rule['key'] === $rule_info['key']) {
                    $already_in_list = true;
                    break;
                }
            }

            if($already_in_list) {
                continue;
            }

            // Since we got here it means we should add our rule to the final list.
            $final_rule_list[] = array(
                'key' => $rule_info['key'],
                'value' => $rule_info['value']
            );
        }

        // Construct our final style string.
        $final_style = "";

        foreach($final_rule_list as $rule_info) {
            $final_style .= $rule_info['key'] . ":" . $rule_info['value'] . ";";
        }

        return "class=\"" . $matches[1] . "\" style=\"{$final_style}\"";
    }
}

if(!function_exists('wordpatch_mail_inline_styles')) {
    /**
     * Calculates the class-inlined version of $html.
     *
     * @param $html
     * @return string
     */
    function wordpatch_mail_inline_styles($html) {
        // Inline the styles using our regex replace.
        $html = preg_replace_callback("/class\\=\\\"([a-zA-Z0-9\\ \\_\\-]+)\\\" style\\=\\\"([^\\\"]+)?\\\"/",
            'wordpatch_mail_inline_style__callback', $html);

        // Return the new calculated HTML
        return $html;
    }
}