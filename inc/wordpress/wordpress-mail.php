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
 * This file is not allowed to depend on WordPress.
 * Made with love (and a little bit of insanity) in Virginia. ♥
 * Feel free to use parts of this file to emulate a modern WordPress installation.
 * MU (multisite) is not yet supported and various notes have been made for future support.
 */

if(!isset($__wordpatch_phpmailer)) {
    $__wordpatch_phpmailer = null;
}

if(!function_exists('wordpatch_mail')) {
    function wordpatch_mail($wpenv_vars, $mailer_info, $to, $subject, $message, $headers = '', $attachments = array())
    {
        global $__wordpatch_phpmailer;
        $atts = compact('to', 'subject', 'message', 'headers', 'attachments');

        if(isset($atts['to'])) {
            $to = $atts['to'];
        }

        if(!is_array($to)) {
            $to = explode(',', $to);
        }

        if(isset($atts['subject'])) {
            $subject = $atts['subject'];
        }

        if(isset($atts['message'])) {
            $message = $atts['message'];
        }

        if(isset($atts['headers'])) {
            $headers = $atts['headers'];
        }

        if(isset($atts['attachments'])) {
            $attachments = $atts['attachments'];
        }

        if(!is_array($attachments)) {
            $attachments = explode("\n", str_replace("\r\n", "\n", $attachments));
        }

        if(!($__wordpatch_phpmailer instanceof WordpatchPHPMailer)) {
            require_once(dirname(__FILE__) . '/classes/class-phpmailer.php');
            require_once(dirname(__FILE__) . '/classes/class-smtp.php');
            $__wordpatch_phpmailer = new WordpatchPHPMailer(true);
        }

        $cc = $bcc = $reply_to = array();

        if(empty($headers)) {
            $headers = array();
        } else {
            if(!is_array($headers)) {
                $tempheaders = explode("\n", str_replace("\r\n", "\n", $headers));
            } else {
                $tempheaders = $headers;
            }
            $headers = array();

            if(!empty($tempheaders)) {
                foreach((array)$tempheaders as $header) {
                    if(strpos($header, ':') === false) {
                        if(false !== stripos($header, 'boundary=')) {
                            $parts = preg_split('/boundary=/i', trim($header));
                            $boundary = trim(str_replace(array("'", '"'), '', $parts[1]));
                        }
                        continue;
                    }

                    list($name, $content) = explode(':', trim($header), 2);

                    $name = trim($name);
                    $content = trim($content);

                    switch(strtolower($name)) {
                        case 'from':
                            $bracket_pos = strpos($content, '<');
                            if ($bracket_pos !== false) {
                                if ($bracket_pos > 0) {
                                    $from_name = substr($content, 0, $bracket_pos - 1);
                                    $from_name = str_replace('"', '', $from_name);
                                    $from_name = trim($from_name);
                                }

                                $from_email = substr($content, $bracket_pos + 1);
                                $from_email = str_replace('>', '', $from_email);
                                $from_email = trim($from_email);
                            } elseif ('' !== trim($content)) {
                                $from_email = trim($content);
                            }
                            break;
                        case 'content-type':
                            if(strpos($content, ';') !== false) {
                                list($type, $charset_content) = explode(';', $content);
                                $content_type = trim($type);
                                if (false !== stripos($charset_content, 'charset=')) {
                                    $charset = trim(str_replace(array('charset=', '"'), '', $charset_content));
                                } elseif (false !== stripos($charset_content, 'boundary=')) {
                                    $boundary = trim(str_replace(array('BOUNDARY=', 'boundary=', '"'), '', $charset_content));
                                    $charset = '';
                                }

                                // Avoid setting an empty $content_type.
                            } elseif ('' !== trim($content)) {
                                $content_type = trim($content);
                            }
                            break;
                        case 'cc':
                            $cc = array_merge((array)$cc, explode(',', $content));
                            break;
                        case 'bcc':
                            $bcc = array_merge((array)$bcc, explode(',', $content));
                            break;
                        case 'reply-to':
                            $reply_to = array_merge((array)$reply_to, explode(',', $content));
                            break;
                        default:
                            $headers[trim($name)] = trim($content);
                            break;
                    }
                }
            }
        }

        $__wordpatch_phpmailer->clearAllRecipients();
        $__wordpatch_phpmailer->clearAttachments();
        $__wordpatch_phpmailer->clearCustomHeaders();
        $__wordpatch_phpmailer->clearReplyTos();

        if(!isset($from_name)) {
            $from_name = $mailer_info['mail_from']; // TODO: In the future we might want to consider adding from name
        }

        if(!isset($from_email)) {
            $from_email = $mailer_info['mail_from'];
        }

        try {
            $__wordpatch_phpmailer->setFrom($from_email, $from_name, false);
        } catch (wordpatchphpmailerException $e) {
            $mail_error_data = compact('to', 'subject', 'message', 'headers', 'attachments');
            $mail_error_data['phpmailer_exception_code'] = $e->getCode();

            return false;
        }

        $__wordpatch_phpmailer->Subject = $subject;
        $__wordpatch_phpmailer->Body = $message;

        $address_headers = compact('to', 'cc', 'bcc', 'reply_to');

        foreach($address_headers as $address_header => $addresses) {
            if(empty($addresses)) {
                continue;
            }

            foreach((array)$addresses as $address) {
                try {
                    $recipient_name = '';

                    if (preg_match('/(.*)<(.+)>/', $address, $matches)) {
                        if (count($matches) == 3) {
                            $recipient_name = $matches[1];
                            $address = $matches[2];
                        }
                    }

                    switch($address_header) {
                        case 'to':
                            $__wordpatch_phpmailer->addAddress($address, $recipient_name);
                            break;
                        case 'cc':
                            $__wordpatch_phpmailer->addCc($address, $recipient_name);
                            break;
                        case 'bcc':
                            $__wordpatch_phpmailer->addBcc($address, $recipient_name);
                            break;
                        case 'reply_to':
                            $__wordpatch_phpmailer->addReplyTo($address, $recipient_name);
                            break;
                    }
                } catch (wordpatchphpmailerException $e) {
                    continue;
                }
            }
        }

        $mailer = $mailer_info['mailer'];

        if($mailer == wordpatch_mailer_smtp()) {
            $__wordpatch_phpmailer->isSMTP();
            $smtp_ssl = $mailer_info['smtp_ssl'];

            $__wordpatch_phpmailer->SMTPSecure = $smtp_ssl == wordpatch_smtp_ssl_none() ? '' : $smtp_ssl;
            $__wordpatch_phpmailer->Host = $mailer_info['smtp_host'];
            $__wordpatch_phpmailer->Port = $mailer_info['smtp_port'];

            if($mailer_info['smtp_auth'] == wordpatch_smtp_auth_yes()) {
                $__wordpatch_phpmailer->SMTPAuth = true;
                $__wordpatch_phpmailer->Username = $mailer_info['smtp_user'];
                $__wordpatch_phpmailer->Password = $mailer_info['smtp_pass'];
            } else {
                $__wordpatch_phpmailer->SMTPAuth = false;
                $__wordpatch_phpmailer->Username = '';
                $__wordpatch_phpmailer->Password = '';
            }
        } else if($mailer == wordpatch_mailer_sendmail()) {
            $__wordpatch_phpmailer->isSendmail();
        } else {
            $__wordpatch_phpmailer->isMail();
        }

        if (!isset($content_type)) {
            $content_type = 'text/plain';
        }

        $__wordpatch_phpmailer->ContentType = $content_type;

        if ('text/html' == $content_type) {
            $__wordpatch_phpmailer->isHTML(true);
        }

        if (!isset($charset)) {
            $charset = wordpatch_get_option($wpenv_vars, 'blog_charset');
            if('' == $charset) {
                $charset = 'UTF-8';
            }
        }

        $__wordpatch_phpmailer->CharSet = $charset;

        if (!empty($headers)) {
            foreach ((array)$headers as $name => $content) {
                $__wordpatch_phpmailer->addCustomHeader(sprintf('%1$s: %2$s', $name, $content));
            }

            if (false !== stripos($content_type, 'multipart') && !empty($boundary)) {
                $__wordpatch_phpmailer->addCustomHeader(sprintf("Content-Type: %s;\n\t boundary=\"%s\"", $content_type, $boundary));
            }
        }

        if(!empty($attachments)) {
            foreach($attachments as $attachment) {
                try {
                    $__wordpatch_phpmailer->addAttachment($attachment);
                } catch (wordpatchphpmailerException $e) {
                    continue;
                }
            }
        }

        // @adaptednote: MU Support

        try {
            return $__wordpatch_phpmailer->send();
        } catch (wordpatchphpmailerException $e) {
            $mail_error_data = compact('to', 'subject', 'message', 'headers', 'attachments');
            $mail_error_data['phpmailer_exception_code'] = $e->getCode();
            $mail_error_data['phpmailer_exception_message'] = $e->getMessage();
            return false;
        }
    }
}