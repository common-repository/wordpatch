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
if(!function_exists('wordpatch_remove_accents')) {
    function wordpatch_remove_accents($wpenv_vars, $string)
    {
        if(!preg_match('/[\x80-\xff]/', $string)) {
            return $string;
        }

        if(wordpatch_seems_utf8($string)) {
            $chars = array(
                // Decompositions for Latin-1 Supplement
                'ª' => 'a', 'º' => 'o',
                'À' => 'A', 'Á' => 'A',
                'Â' => 'A', 'Ã' => 'A',
                'Ä' => 'A', 'Å' => 'A',
                'Æ' => 'AE','Ç' => 'C',
                'È' => 'E', 'É' => 'E',
                'Ê' => 'E', 'Ë' => 'E',
                'Ì' => 'I', 'Í' => 'I',
                'Î' => 'I', 'Ï' => 'I',
                'Ð' => 'D', 'Ñ' => 'N',
                'Ò' => 'O', 'Ó' => 'O',
                'Ô' => 'O', 'Õ' => 'O',
                'Ö' => 'O', 'Ù' => 'U',
                'Ú' => 'U', 'Û' => 'U',
                'Ü' => 'U', 'Ý' => 'Y',
                'Þ' => 'TH','ß' => 's',
                'à' => 'a', 'á' => 'a',
                'â' => 'a', 'ã' => 'a',
                'ä' => 'a', 'å' => 'a',
                'æ' => 'ae','ç' => 'c',
                'è' => 'e', 'é' => 'e',
                'ê' => 'e', 'ë' => 'e',
                'ì' => 'i', 'í' => 'i',
                'î' => 'i', 'ï' => 'i',
                'ð' => 'd', 'ñ' => 'n',
                'ò' => 'o', 'ó' => 'o',
                'ô' => 'o', 'õ' => 'o',
                'ö' => 'o', 'ø' => 'o',
                'ù' => 'u', 'ú' => 'u',
                'û' => 'u', 'ü' => 'u',
                'ý' => 'y', 'þ' => 'th',
                'ÿ' => 'y', 'Ø' => 'O',
                // Decompositions for Latin Extended-A
                'Ā' => 'A', 'ā' => 'a',
                'Ă' => 'A', 'ă' => 'a',
                'Ą' => 'A', 'ą' => 'a',
                'Ć' => 'C', 'ć' => 'c',
                'Ĉ' => 'C', 'ĉ' => 'c',
                'Ċ' => 'C', 'ċ' => 'c',
                'Č' => 'C', 'č' => 'c',
                'Ď' => 'D', 'ď' => 'd',
                'Đ' => 'D', 'đ' => 'd',
                'Ē' => 'E', 'ē' => 'e',
                'Ĕ' => 'E', 'ĕ' => 'e',
                'Ė' => 'E', 'ė' => 'e',
                'Ę' => 'E', 'ę' => 'e',
                'Ě' => 'E', 'ě' => 'e',
                'Ĝ' => 'G', 'ĝ' => 'g',
                'Ğ' => 'G', 'ğ' => 'g',
                'Ġ' => 'G', 'ġ' => 'g',
                'Ģ' => 'G', 'ģ' => 'g',
                'Ĥ' => 'H', 'ĥ' => 'h',
                'Ħ' => 'H', 'ħ' => 'h',
                'Ĩ' => 'I', 'ĩ' => 'i',
                'Ī' => 'I', 'ī' => 'i',
                'Ĭ' => 'I', 'ĭ' => 'i',
                'Į' => 'I', 'į' => 'i',
                'İ' => 'I', 'ı' => 'i',
                'Ĳ' => 'IJ','ĳ' => 'ij',
                'Ĵ' => 'J', 'ĵ' => 'j',
                'Ķ' => 'K', 'ķ' => 'k',
                'ĸ' => 'k', 'Ĺ' => 'L',
                'ĺ' => 'l', 'Ļ' => 'L',
                'ļ' => 'l', 'Ľ' => 'L',
                'ľ' => 'l', 'Ŀ' => 'L',
                'ŀ' => 'l', 'Ł' => 'L',
                'ł' => 'l', 'Ń' => 'N',
                'ń' => 'n', 'Ņ' => 'N',
                'ņ' => 'n', 'Ň' => 'N',
                'ň' => 'n', 'ŉ' => 'n',
                'Ŋ' => 'N', 'ŋ' => 'n',
                'Ō' => 'O', 'ō' => 'o',
                'Ŏ' => 'O', 'ŏ' => 'o',
                'Ő' => 'O', 'ő' => 'o',
                'Œ' => 'OE','œ' => 'oe',
                'Ŕ' => 'R','ŕ' => 'r',
                'Ŗ' => 'R','ŗ' => 'r',
                'Ř' => 'R','ř' => 'r',
                'Ś' => 'S','ś' => 's',
                'Ŝ' => 'S','ŝ' => 's',
                'Ş' => 'S','ş' => 's',
                'Š' => 'S', 'š' => 's',
                'Ţ' => 'T', 'ţ' => 't',
                'Ť' => 'T', 'ť' => 't',
                'Ŧ' => 'T', 'ŧ' => 't',
                'Ũ' => 'U', 'ũ' => 'u',
                'Ū' => 'U', 'ū' => 'u',
                'Ŭ' => 'U', 'ŭ' => 'u',
                'Ů' => 'U', 'ů' => 'u',
                'Ű' => 'U', 'ű' => 'u',
                'Ų' => 'U', 'ų' => 'u',
                'Ŵ' => 'W', 'ŵ' => 'w',
                'Ŷ' => 'Y', 'ŷ' => 'y',
                'Ÿ' => 'Y', 'Ź' => 'Z',
                'ź' => 'z', 'Ż' => 'Z',
                'ż' => 'z', 'Ž' => 'Z',
                'ž' => 'z', 'ſ' => 's',
                // Decompositions for Latin Extended-B
                'Ș' => 'S', 'ș' => 's',
                'Ț' => 'T', 'ț' => 't',
                // Euro Sign
                '€' => 'E',
                // GBP (Pound) Sign
                '£' => '',
                // Vowels with diacritic (Vietnamese)
                // unmarked
                'Ơ' => 'O', 'ơ' => 'o',
                'Ư' => 'U', 'ư' => 'u',
                // grave accent
                'Ầ' => 'A', 'ầ' => 'a',
                'Ằ' => 'A', 'ằ' => 'a',
                'Ề' => 'E', 'ề' => 'e',
                'Ồ' => 'O', 'ồ' => 'o',
                'Ờ' => 'O', 'ờ' => 'o',
                'Ừ' => 'U', 'ừ' => 'u',
                'Ỳ' => 'Y', 'ỳ' => 'y',
                // hook
                'Ả' => 'A', 'ả' => 'a',
                'Ẩ' => 'A', 'ẩ' => 'a',
                'Ẳ' => 'A', 'ẳ' => 'a',
                'Ẻ' => 'E', 'ẻ' => 'e',
                'Ể' => 'E', 'ể' => 'e',
                'Ỉ' => 'I', 'ỉ' => 'i',
                'Ỏ' => 'O', 'ỏ' => 'o',
                'Ổ' => 'O', 'ổ' => 'o',
                'Ở' => 'O', 'ở' => 'o',
                'Ủ' => 'U', 'ủ' => 'u',
                'Ử' => 'U', 'ử' => 'u',
                'Ỷ' => 'Y', 'ỷ' => 'y',
                // tilde
                'Ẫ' => 'A', 'ẫ' => 'a',
                'Ẵ' => 'A', 'ẵ' => 'a',
                'Ẽ' => 'E', 'ẽ' => 'e',
                'Ễ' => 'E', 'ễ' => 'e',
                'Ỗ' => 'O', 'ỗ' => 'o',
                'Ỡ' => 'O', 'ỡ' => 'o',
                'Ữ' => 'U', 'ữ' => 'u',
                'Ỹ' => 'Y', 'ỹ' => 'y',
                // acute accent
                'Ấ' => 'A', 'ấ' => 'a',
                'Ắ' => 'A', 'ắ' => 'a',
                'Ế' => 'E', 'ế' => 'e',
                'Ố' => 'O', 'ố' => 'o',
                'Ớ' => 'O', 'ớ' => 'o',
                'Ứ' => 'U', 'ứ' => 'u',
                // dot below
                'Ạ' => 'A', 'ạ' => 'a',
                'Ậ' => 'A', 'ậ' => 'a',
                'Ặ' => 'A', 'ặ' => 'a',
                'Ẹ' => 'E', 'ẹ' => 'e',
                'Ệ' => 'E', 'ệ' => 'e',
                'Ị' => 'I', 'ị' => 'i',
                'Ọ' => 'O', 'ọ' => 'o',
                'Ộ' => 'O', 'ộ' => 'o',
                'Ợ' => 'O', 'ợ' => 'o',
                'Ụ' => 'U', 'ụ' => 'u',
                'Ự' => 'U', 'ự' => 'u',
                'Ỵ' => 'Y', 'ỵ' => 'y',
                // Vowels with diacritic (Chinese, Hanyu Pinyin)
                'ɑ' => 'a',
                // macron
                'Ǖ' => 'U', 'ǖ' => 'u',
                // acute accent
                'Ǘ' => 'U', 'ǘ' => 'u',
                // caron
                'Ǎ' => 'A', 'ǎ' => 'a',
                'Ǐ' => 'I', 'ǐ' => 'i',
                'Ǒ' => 'O', 'ǒ' => 'o',
                'Ǔ' => 'U', 'ǔ' => 'u',
                'Ǚ' => 'U', 'ǚ' => 'u',
                // grave accent
                'Ǜ' => 'U', 'ǜ' => 'u',
            );

            // Used for locale-specific rules
            $locale = wordpatch_env_get($wpenv_vars, 'locale');

            if('de_DE' == $locale || 'de_DE_formal' == $locale || 'de_CH' == $locale || 'de_CH_informal' == $locale) {
                $chars['Ä'] = 'Ae';
                $chars['ä'] = 'ae';
                $chars['Ö'] = 'Oe';
                $chars['ö'] = 'oe';
                $chars['Ü'] = 'Ue';
                $chars['ü'] = 'ue';
                $chars['ß'] = 'ss';
            } elseif('da_DK' === $locale) {
                $chars['Æ'] = 'Ae';
                $chars['æ'] = 'ae';
                $chars['Ø'] = 'Oe';
                $chars['ø'] = 'oe';
                $chars['Å'] = 'Aa';
                $chars['å'] = 'aa';
            } elseif('ca' === $locale) {
                $chars['l·l'] = 'll';
            } elseif('sr_RS' === $locale || 'bs_BA' === $locale) {
                $chars['Đ'] = 'DJ';
                $chars['đ'] = 'dj';
            }

            $string = strtr($string, $chars);
        } else {
            $chars = array();
            // Assume ISO-8859-1 if not UTF-8
            $chars['in'] = "\x80\x83\x8a\x8e\x9a\x9e"
                ."\x9f\xa2\xa5\xb5\xc0\xc1\xc2"
                ."\xc3\xc4\xc5\xc7\xc8\xc9\xca"
                ."\xcb\xcc\xcd\xce\xcf\xd1\xd2"
                ."\xd3\xd4\xd5\xd6\xd8\xd9\xda"
                ."\xdb\xdc\xdd\xe0\xe1\xe2\xe3"
                ."\xe4\xe5\xe7\xe8\xe9\xea\xeb"
                ."\xec\xed\xee\xef\xf1\xf2\xf3"
                ."\xf4\xf5\xf6\xf8\xf9\xfa\xfb"
                ."\xfc\xfd\xff";

            $chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";

            $string = strtr($string, $chars['in'], $chars['out']);
            $double_chars = array();
            $double_chars['in'] = array("\x8c", "\x9c", "\xc6", "\xd0", "\xde", "\xdf", "\xe6", "\xf0", "\xfe");
            $double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
            $string = str_replace($double_chars['in'], $double_chars['out'], $string);
        }

        return $string;
    }
}

if(!function_exists('wordpatch_is_ssl')) {
    function wordpatch_is_ssl() {
        if(isset($_SERVER['HTTPS'])) {
            if('on' == strtolower($_SERVER['HTTPS'])) {
                return true;
            }

            if('1' == $_SERVER['HTTPS']) {
                return true;
            }
        } elseif(isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
            return true;
        }
        return false;
    }
}

if(!function_exists('wordpatch_set_url_scheme')) {
    function wordpatch_set_url_scheme($url, $scheme = null)
    {
        if (!$scheme || !in_array($scheme, array('https', 'http'))) {
            $scheme = wordpatch_is_ssl() ? 'https' : 'http';
        }

        $url = trim($url);
        if(substr($url, 0, 2) === '//')
            $url = 'http:' . $url;

        $url = preg_replace('#^\w+://#', $scheme . '://', $url);

        return $url;
    }
}

if(!isset($__wordpatch_wp_globals)) {
    $__wordpatch_wp_globals = null;
}

if(!function_exists('wordpatch_wp_globals')) {
    function wordpatch_wp_globals($cache = true)
    {
        global $__wordpatch_wp_globals;

        if ($cache && $__wordpatch_wp_globals) {
            return $__wordpatch_wp_globals;
        }

        $__wordpatch_wp_globals['is_lynx'] = false;
        $__wordpatch_wp_globals['is_edge'] = false;
        $__wordpatch_wp_globals['is_winIE'] = false;
        $__wordpatch_wp_globals['is_chrome'] = false;
        $__wordpatch_wp_globals['is_safari'] = false;
        $__wordpatch_wp_globals['is_winIE'] = false;
        $__wordpatch_wp_globals['is_macIE'] = false;
        $__wordpatch_wp_globals['is_gecko'] = false;
        $__wordpatch_wp_globals['is_opera'] = false;
        $__wordpatch_wp_globals['is_NS4'] = false;
        $__wordpatch_wp_globals['is_iphone'] = false;
        $__wordpatch_wp_globals['is_IE'] = false;
        $__wordpatch_wp_globals['is_apache'] = false;
        $__wordpatch_wp_globals['is_nginx'] = false;
        $__wordpatch_wp_globals['is_IIS'] = false;
        $__wordpatch_wp_globals['is_iis7'] = false;
        $__wordpatch_wp_globals['is_mobile'] = false;

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            if (strpos($_SERVER['HTTP_USER_AGENT'], 'Lynx') !== false) {
                $__wordpatch_wp_globals['is_lynx'] = true;
            } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Edge') !== false) {
                $__wordpatch_wp_globals['is_edge'] = true;
            } elseif (stripos($_SERVER['HTTP_USER_AGENT'], 'chrome') !== false) {
                if (stripos($_SERVER['HTTP_USER_AGENT'], 'chromeframe') !== false) {
                    $__wordpatch_wp_globals['is_winIE'] = true;
                } else {
                    $__wordpatch_wp_globals['is_chrome'] = true;
                }
            } elseif (stripos($_SERVER['HTTP_USER_AGENT'], 'safari') !== false) {
                $__wordpatch_wp_globals['is_safari'] = true;
            } elseif ((strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') !== false) &&
                strpos($_SERVER['HTTP_USER_AGENT'], 'Win') !== false) {
                $__wordpatch_wp_globals['is_winIE'] = true;
            } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false && strpos($_SERVER['HTTP_USER_AGENT'], 'Mac') !== false) {
                $__wordpatch_wp_globals['is_macIE'] = true;
            } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Gecko') !== false) {
                $__wordpatch_wp_globals['is_gecko'] = true;
            } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Opera') !== false) {
                $__wordpatch_wp_globals['is_opera'] = true;
            } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Nav') !== false && strpos($_SERVER['HTTP_USER_AGENT'], 'Mozilla/4.') !== false) {
                $__wordpatch_wp_globals['is_NS4'] = true;
            }
        }

        if ($__wordpatch_wp_globals['is_safari'] && stripos($_SERVER['HTTP_USER_AGENT'], 'mobile') !== false) {
            $__wordpatch_wp_globals['is_iphone'] = true;
        }

        $__wordpatch_wp_globals['is_IE'] = ($__wordpatch_wp_globals['is_macIE'] || $__wordpatch_wp_globals['is_winIE']);

        $__wordpatch_wp_globals['is_apache'] = (strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false ||
            strpos($_SERVER['SERVER_SOFTWARE'], 'LiteSpeed') !== false);

        $__wordpatch_wp_globals['is_nginx'] = (strpos($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false);

        $__wordpatch_wp_globals['is_IIS'] = !$__wordpatch_wp_globals['is_apache'] && (strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== false ||
                strpos($_SERVER['SERVER_SOFTWARE'], 'ExpressionDevServer') !== false);

        $__wordpatch_wp_globals['is_iis7'] = $__wordpatch_wp_globals['is_IIS'] && intval(substr($_SERVER['SERVER_SOFTWARE'], strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS/') + 14)) >= 7;

        if (empty($_SERVER['HTTP_USER_AGENT'])) {
            $__wordpatch_wp_globals['is_mobile'] = false;
        } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false // many mobile devices (all iPhone, iPad, etc.)
            || strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false
            || strpos($_SERVER['HTTP_USER_AGENT'], 'Silk/') !== false
            || strpos($_SERVER['HTTP_USER_AGENT'], 'Kindle') !== false
            || strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false
            || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false
            || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mobi') !== false) {
            $__wordpatch_wp_globals['is_mobile'] = true;
        } else {
            $__wordpatch_wp_globals['is_mobile'] = false;
        }

        return $__wordpatch_wp_globals;
    }
}

if(!function_exists('wordpatch_is_IIS')) {
    function wordpatch_is_IIS()
    {
        if(isset($GLOBALS['is_IIS']) && ($GLOBALS['is_IIS'] === true || $GLOBALS['is_IIS'] === false)) {
            return $GLOBALS['is_IIS'];
        }

        $wp_globals = wordpatch_wp_globals();
        return $wp_globals['is_IIS'];
    }
}

if(!function_exists('wordpatch_strip_all_tags')) {
    function wordpatch_strip_all_tags($string, $remove_breaks = false) {
        $string = preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', $string);
        $string = strip_tags($string);

        if($remove_breaks) {
            $string = preg_replace('/[\r\n\t ]+/', ' ', $string);
        }

        return trim($string);
    }
}

if(!function_exists('wordpatch_mbstring_binary_safe_encoding')) {
    function wordpatch_mbstring_binary_safe_encoding($reset = false)
    {
        static $encodings = array();
        static $overloaded = null;

        if (is_null($overloaded))
            $overloaded = function_exists('mb_internal_encoding') && (ini_get('mbstring.func_overload') & 2);

        if (false === $overloaded)
            return;

        if (!$reset) {
            $encoding = mb_internal_encoding();
            array_push($encodings, $encoding);
            mb_internal_encoding('ISO-8859-1');
        }

        if ($reset && $encodings) {
            $encoding = array_pop($encodings);
            mb_internal_encoding($encoding);
        }
    }
}

if(!function_exists('wordpatch_reset_mbstring_encoding')) {
    function wordpatch_reset_mbstring_encoding() {
        wordpatch_mbstring_binary_safe_encoding(true);
    }
}

if(!function_exists('wordpatch_seems_utf8')) {
    function wordpatch_seems_utf8($str) {
        wordpatch_mbstring_binary_safe_encoding();
        $length = strlen($str);
        wordpatch_reset_mbstring_encoding();
        for ($i=0; $i < $length; $i++) {
            $c = ord($str[$i]);
            if ($c < 0x80) $n = 0; // 0bbbbbbb
            elseif (($c & 0xE0) == 0xC0) $n=1; // 110bbbbb
            elseif (($c & 0xF0) == 0xE0) $n=2; // 1110bbbb
            elseif (($c & 0xF8) == 0xF0) $n=3; // 11110bbb
            elseif (($c & 0xFC) == 0xF8) $n=4; // 111110bb
            elseif (($c & 0xFE) == 0xFC) $n=5; // 1111110b
            else return false; // Does not match any model
            for ($j=0; $j<$n; $j++) { // n bytes matching 10bbbbbb follow ?
                if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80))
                    return false;
            }
        }
        return true;
    }
}

if(!function_exists('wordpatch_sanitize_key')) {
    function wordpatch_sanitize_key($key) {
        $key = strtolower($key);
        $key = preg_replace('/[^a-z0-9_\-]/', '', $key);

        return $key;
    }
}

if(!function_exists('wordpatch_sanitize_user')) {
    function wordpatch_sanitize_user($wpenv_vars, $username, $strict = false) {
        $raw_username = $username;
        $username = wordpatch_strip_all_tags($username);
        $username = wordpatch_remove_accents($wpenv_vars, $username);
        // Kill octets
        $username = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '', $username);
        $username = preg_replace('/&.+?;/', '', $username); // Kill entities

        // If strict, reduce to ASCII for max portability.
        if($strict) {
            $username = preg_replace('|[^a-z0-9 _.\-@]|i', '', $username);
        }

        $username = trim($username);
        // Consolidate contiguous whitespace
        $username = preg_replace('|\s+|', ' ', $username);

        return $username;
    }
}

if(!function_exists('wordpatch_sanitize_redirect')) {
    function wordpatch_sanitize_redirect($location)
    {
        $regex = '/
		(
			(?: [\xC2-\xDF][\x80-\xBF]        # double-byte sequences   110xxxxx 10xxxxxx
			|   \xE0[\xA0-\xBF][\x80-\xBF]    # triple-byte sequences   1110xxxx 10xxxxxx * 2
			|   [\xE1-\xEC][\x80-\xBF]{2}
			|   \xED[\x80-\x9F][\x80-\xBF]
			|   [\xEE-\xEF][\x80-\xBF]{2}
			|   \xF0[\x90-\xBF][\x80-\xBF]{2} # four-byte sequences   11110xxx 10xxxxxx * 3
			|   [\xF1-\xF3][\x80-\xBF]{3}
			|   \xF4[\x80-\x8F][\x80-\xBF]{2}
		){1,40}                              # ...one or more times
		)/x';
        $location = preg_replace_callback($regex, '_wordpatch_sanitize_utf8_in_redirect', $location);
        $location = preg_replace('|[^a-z0-9-~+_.?#=&;,/:%!*\[\]()@]|i', '', $location);
        $location = wordpatch_kses_no_null($location);

        // remove %0d and %0a from location
        $strip = array('%0d', '%0a', '%0D', '%0A');
        return _wordpatch_deep_replace($strip, $location);
    }
}

if(!function_exists('_wordpatch_sanitize_utf8_in_redirect')) {
    function _wordpatch_sanitize_utf8_in_redirect($matches) {
        return urlencode($matches[0]);
    }
}

if(!function_exists('wordpatch_untrailingslashit')) {
    /**
     * Portable untrailingslashit()
     * @param $path
     * @param bool $normalize
     * @return string
     */
    function wordpatch_untrailingslashit($path, $normalize = true)
    {
        if ($normalize)
            $path = wordpatch_normalize_path($path);

        return rtrim($path, '/\\');
    }
}

if(!function_exists('wordpatch_unleadingslashit')) {
    /**
     * Portable unleadingslashit()
     * @param $path
     * @param bool $normalize
     * @return string
     */
    function wordpatch_unleadingslashit($path, $normalize = true)
    {
        if ($normalize)
            $path = wordpatch_normalize_path($path);

        return ltrim($path, '/\\');
    }
}

if(!function_exists('wordpatch_trailingslashit')) {
    /**
     * Portable trailingslashit()
     * @param $path
     * @param bool $normalize
     * @return string
     */
    function wordpatch_trailingslashit($path, $normalize = true)
    {
        return wordpatch_untrailingslashit($path, $normalize) . '/';
    }
}

if(!function_exists('wordpatch_leadingslashit')) {
    /**
     * Portable leadingslashit()
     * @param $path
     * @param bool $normalize
     * @return string
     */
    function wordpatch_leadingslashit($path, $normalize = true)
    {
        return '/' . wordpatch_unleadingslashit($path, $normalize);
    }
}

if(!function_exists('wordpatch_sanitize_file_name')) {
    function wordpatch_sanitize_file_name($filename) {
        $filename_raw = $filename;
        $special_chars = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}", "%", "+", chr(0));

        $filename = preg_replace("#\x{00a0}#siu", ' ', $filename);
        $filename = str_replace($special_chars, '', $filename);
        $filename = str_replace(array('%20', '+'), '-', $filename);
        $filename = preg_replace('/[\r\n\t -]+/', '-', $filename);
        $filename = trim($filename, '.-_');

        if(false === strpos($filename, '.')) {
            $mime_types = wordpatch_get_mime_types();
            $filetype = wordpatch_check_filetype('test.' . $filename, $mime_types);
            if($filetype['ext'] === $filename) {
                $filename = 'unnamed-file.' . $filetype['ext'];
            }
        }

        // Split the filename into a base and extension[s]
        $parts = explode('.', $filename);

        // Return if only one extension
        if (count($parts) <= 2) {
            return $filename;
        }

        // Process multiple extensions
        $filename = array_shift($parts);
        $extension = array_pop($parts);
        $mimes = wordpatch_get_allowed_mime_types();

        foreach((array)$parts as $part) {
            $filename .= '.' . $part;

            if(preg_match("/^[a-zA-Z]{2,5}\d?$/", $part)) {
                $allowed = false;

                foreach($mimes as $ext_preg => $mime_match) {
                    $ext_preg = '!^(' . $ext_preg . ')$!i';

                    if(preg_match($ext_preg, $part)) {
                        $allowed = true;
                        break;
                    }
                }

                if(!$allowed) {
                    $filename .= '_';
                }
            }
        }

        $filename .= '.' . $extension;
        return $filename;
    }
}

if(!function_exists('wordpatch_get_allowed_mime_types')) {
    function wordpatch_get_allowed_mime_types() {
        $t = wordpatch_get_mime_types();

        unset($t['swf'], $t['exe']);

        return $t;
    }
}

if(!function_exists('wordpatch_check_filetype')) {
    function wordpatch_check_filetype($filename, $mimes = null) {
        if(empty($mimes)) {
            $mimes = wordpatch_get_allowed_mime_types();
        }
        $type = false;
        $ext = false;

        foreach($mimes as $ext_preg => $mime_match) {
            $ext_preg = '!\.(' . $ext_preg . ')$!i';

            if(preg_match($ext_preg, $filename, $ext_matches)) {
                $type = $mime_match;
                $ext = $ext_matches[1];
                break;
            }
        }

        return compact('ext', 'type');
    }
}

if(!function_exists('wordpatch_get_mime_types')) {
    function wordpatch_get_mime_types() {
        return array(
            // Image formats.
            'jpg|jpeg|jpe' => 'image/jpeg',
            'gif' => 'image/gif',
            'png' => 'image/png',
            'bmp' => 'image/bmp',
            'tiff|tif' => 'image/tiff',
            'ico' => 'image/x-icon',
            // Video formats.
            'asf|asx' => 'video/x-ms-asf',
            'wmv' => 'video/x-ms-wmv',
            'wmx' => 'video/x-ms-wmx',
            'wm' => 'video/x-ms-wm',
            'avi' => 'video/avi',
            'divx' => 'video/divx',
            'flv' => 'video/x-flv',
            'mov|qt' => 'video/quicktime',
            'mpeg|mpg|mpe' => 'video/mpeg',
            'mp4|m4v' => 'video/mp4',
            'ogv' => 'video/ogg',
            'webm' => 'video/webm',
            'mkv' => 'video/x-matroska',
            '3gp|3gpp' => 'video/3gpp', // Can also be audio
            '3g2|3gp2' => 'video/3gpp2', // Can also be audio
            // Text formats.
            'txt|asc|c|cc|h|srt' => 'text/plain',
            'csv' => 'text/csv',
            'tsv' => 'text/tab-separated-values',
            'ics' => 'text/calendar',
            'rtx' => 'text/richtext',
            'css' => 'text/css',
            'htm|html' => 'text/html',
            'vtt' => 'text/vtt',
            'dfxp' => 'application/ttaf+xml',
            // Audio formats.
            'mp3|m4a|m4b' => 'audio/mpeg',
            'ra|ram' => 'audio/x-realaudio',
            'wav' => 'audio/wav',
            'ogg|oga' => 'audio/ogg',
            'mid|midi' => 'audio/midi',
            'wma' => 'audio/x-ms-wma',
            'wax' => 'audio/x-ms-wax',
            'mka' => 'audio/x-matroska',
            // Misc application formats.
            'rtf' => 'application/rtf',
            'js' => 'application/javascript',
            'pdf' => 'application/pdf',
            'swf' => 'application/x-shockwave-flash',
            'class' => 'application/java',
            'tar' => 'application/x-tar',
            'zip' => 'application/zip',
            'gz|gzip' => 'application/x-gzip',
            'rar' => 'application/rar',
            '7z' => 'application/x-7z-compressed',
            'exe' => 'application/x-msdownload',
            'psd' => 'application/octet-stream',
            'xcf' => 'application/octet-stream',
            // MS Office formats.
            'doc' => 'application/msword',
            'pot|pps|ppt' => 'application/vnd.ms-powerpoint',
            'wri' => 'application/vnd.ms-write',
            'xla|xls|xlt|xlw' => 'application/vnd.ms-excel',
            'mdb' => 'application/vnd.ms-access',
            'mpp' => 'application/vnd.ms-project',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'docm' => 'application/vnd.ms-word.document.macroEnabled.12',
            'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
            'dotm' => 'application/vnd.ms-word.template.macroEnabled.12',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xlsm' => 'application/vnd.ms-excel.sheet.macroEnabled.12',
            'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
            'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'xltm' => 'application/vnd.ms-excel.template.macroEnabled.12',
            'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'pptm' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
            'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
            'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
            'potm' => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
            'ppam' => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
            'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
            'sldm' => 'application/vnd.ms-powerpoint.slide.macroEnabled.12',
            'onetoc|onetoc2|onetmp|onepkg' => 'application/onenote',
            'oxps' => 'application/oxps',
            'xps' => 'application/vnd.ms-xpsdocument',
            // OpenOffice formats.
            'odt' => 'application/vnd.oasis.opendocument.text',
            'odp' => 'application/vnd.oasis.opendocument.presentation',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
            'odg' => 'application/vnd.oasis.opendocument.graphics',
            'odc' => 'application/vnd.oasis.opendocument.chart',
            'odb' => 'application/vnd.oasis.opendocument.database',
            'odf' => 'application/vnd.oasis.opendocument.formula',
            // WordPerfect formats.
            'wp|wpd' => 'application/wordperfect',
            // iWork formats.
            'key' => 'application/vnd.apple.keynote',
            'numbers' => 'application/vnd.apple.numbers',
            'pages' => 'application/vnd.apple.pages',
        );
    }
}

if(!function_exists('wordpatch_is_writable')) {
    function wordpatch_is_writable($path)
    {
        if ('WIN' === strtoupper(substr(PHP_OS, 0, 3))) {
            return wordpatch_win_is_writable($path);
        } else {
            return @is_writable($path);
        }
    }
}

if(!function_exists('wordpatch_win_is_writable')) {
    function wordpatch_win_is_writable($path) {
        if($path[strlen($path) - 1] == '/') { // if it looks like a directory, check a random file within the directory
            return wordpatch_win_is_writable($path . uniqid(mt_rand()) . '.tmp');
        } elseif(is_dir($path)) { // If it's a directory (and not a file) check a random file within the directory
            return wordpatch_win_is_writable($path . '/' . uniqid(mt_rand()) . '.tmp');
        }
        // check tmp file for read/write capabilities
        $should_delete_tmp_file = !file_exists($path);
        $f = @fopen($path, 'a');
        if($f === false) {
            return false;
        }
        fclose($f);
        if($should_delete_tmp_file) {
            unlink($path);
        }
        return true;
    }
}

if(!function_exists('wordpatch_unique_filename')) {
    function wordpatch_unique_filename($dir, $filename, $unique_filename_callback = null) {
        // Sanitize the file name before we begin processing.
        $filename = wordpatch_sanitize_file_name($filename);

        // Separate the filename into a name and extension.
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $name = pathinfo($filename, PATHINFO_BASENAME);

        if($ext) {
            $ext = '.' . $ext;
        }

        // Edge case: if file is named '.ext', treat as an empty name.
        if($name === $ext) {
            $name = '';
        }

        /*
         * Increment the file number until we have a unique file to save in $dir.
         * Use callback if supplied.
         */
        if($unique_filename_callback && is_callable($unique_filename_callback)) {
            $filename = call_user_func($unique_filename_callback, $dir, $name, $ext);
        } else {
            $number = '';

            // Change '.ext' to lower case.
            if($ext && strtolower($ext) != $ext) {
                $ext2 = strtolower($ext);
                $filename2 = preg_replace('|' . preg_quote($ext) . '$|', $ext2, $filename);

                // Check for both lower and upper case extension or image sub-sizes may be overwritten.
                while(file_exists($dir . "/$filename") || file_exists($dir . "/$filename2")) {
                    $new_number = (int) $number + 1;
                    $filename = str_replace(array("-$number$ext", "$number$ext"), "-$new_number$ext", $filename);
                    $filename2 = str_replace(array("-$number$ext2", "$number$ext2"), "-$new_number$ext2", $filename2);
                    $number = $new_number;
                }

                return $filename2;
            }

            while(file_exists( $dir . "/$filename")) {
                $new_number = (int) $number + 1;
                if('' == "$number$ext") {
                    $filename = "$filename-" . $new_number;
                } else {
                    $filename = str_replace(array("-$number$ext", "$number$ext"), "-" . $new_number . $ext, $filename);
                }
                $number = $new_number;
            }
        }

        return $filename;
    }
}

if(!function_exists('wordpatch_tempnam')) {
    function wordpatch_tempnam($wpenv_vars, $filename = '', $dir = '') {
        if(empty($dir)) {
            $dir = wordpatch_get_temp_dir($wpenv_vars);
        }

        if(empty($filename) || '.' == $filename || '/' == $filename || '\\' == $filename) {
            $filename = time();
        }

        // Use the basename of the given file without the extension as the name for the temporary directory
        $temp_filename = basename($filename);
        $temp_filename = preg_replace('|\.[^.]*$|', '', $temp_filename);

        // If the folder is falsey, use its parent directory name instead.
        if(!$temp_filename) {
            return wordpatch_tempnam($wpenv_vars, dirname($filename), $dir);
        }

        // Suffix some random data to avoid filename conflicts
        $temp_filename .= '-' . wordpatch_generate_password(6, false);
        $temp_filename .= '.tmp';
        $temp_filename = $dir . wordpatch_unique_filename($dir, $temp_filename);

        $fp = @fopen($temp_filename, 'x');
        if(!$fp && is_writable($dir) && file_exists($temp_filename)) {
            return wordpatch_tempnam($wpenv_vars, $filename, $dir);
        }

        if($fp) {
            fclose($fp);
        }

        return $temp_filename;
    }
}

if(!isset($__wordpatch_tempdir_cache)) {
    $__wordpatch_tempdir_cache = null;
}

if(!function_exists('wordpatch_get_temp_dir')) {
    function wordpatch_get_temp_dir($wpenv_vars)
    {
        global $__wordpatch_tempdir_cache;

        if($__wordpatch_tempdir_cache) {
            return $__wordpatch_tempdir_cache;
        }

        if(function_exists('sys_get_temp_dir')) {
            $temp = sys_get_temp_dir();

            if(@is_dir($temp) && wordpatch_is_writable($temp)) {
                return wordpatch_trailingslashit($temp);
            }
        }

        $temp = ini_get('upload_tmp_dir');
        if(@is_dir($temp) && wordpatch_is_writable($temp)) {
            return wordpatch_trailingslashit($temp);
        }

        $temp = wordpatch_env_get($wpenv_vars, 'wp_root_dir') . '/wp-content/';
        if(is_dir($temp) && wordpatch_is_writable($temp)) {
            return $temp;
        }

        return '/tmp/';
    }
}

if(!function_exists('wordpatch_redirect')) {
    function wordpatch_redirect($location, $status = 302)
    {
        $iis = wordpatch_is_IIS();

        if (!$location)
            return false;

        $location = wordpatch_sanitize_redirect($location);

        if (!$iis && PHP_SAPI != 'cgi-fcgi') {
            wordpatch_status_header($status); // This causes problems on IIS and some FastCGI setups
        }

        header("Location: $location", true, $status);
        return true;
    }
}

if(!function_exists('wordpatch_absint')) {
    /**
     * @param $maybeint
     * @return float|int
     * @adapted absint
     * @qc
     */
    function wordpatch_absint($maybeint) {
        return abs(intval($maybeint));
    }
}

if(!function_exists('wordpatch_minute_in_seconds')) {
    /**
     * @return int
     * @adaptedconst MINUTE_IN_SECONDS
     */
    function wordpatch_minute_in_seconds()
    {
        return 60;
    }
}

if(!function_exists('wordpatch_hour_in_seconds')) {
    /**
     * @return int
     * @adaptedconst HOUR_IN_SECONDS
     */
    function wordpatch_hour_in_seconds()
    {
        return 60 * wordpatch_minute_in_seconds();
    }
}

if(!function_exists('wordpatch_day_in_seconds')) {
    /**
     * @return int
     * @adaptedconst DAY_IN_SECONDS
     */
    function wordpatch_day_in_seconds()
    {
        return 24 * wordpatch_hour_in_seconds();
    }
}

if(!function_exists('wordpatch_week_in_seconds')) {
    /**
     * @return int
     * @adaptedconst WEEK_IN_SECONDS
     */
    function wordpatch_week_in_seconds()
    {
        return 7 * wordpatch_day_in_seconds();
    }
}

if(!function_exists('wordpatch_year_in_seconds')) {
    /**
     * @return int
     * @adaptedconst YEAR_IN_SECONDS
     */
    function wordpatch_year_in_seconds()
    {
        return 365 * wordpatch_day_in_seconds();
    }
}

if(!function_exists('wordpatch_sanitize_meta')) {
    function wordpatch_sanitize_meta($meta_key, $meta_value, $meta_type)
    {
        return $meta_value;
    }
}

if(!function_exists('wordpatch_maybe_serialize')) {
    function wordpatch_maybe_serialize($data) {
        if(is_array($data) ||is_object($data)) {
            return serialize($data);
        }

        if(wordpatch_is_serialized($data, false)) {
            return serialize($data);
        }

        return $data;
    }
}

if(!function_exists('wordpatch_rand')) {
    /**
     * @param $min
     * @param $max
     * @return int
     * @adapted wp_rand
     * @adaptednote This function is not currently always "crypt" secure. TODO maybe improve in the future with a full crypt drop in replacement.
     */
    function wordpatch_rand($min, $max) {
        if(function_exists('random_int')) {
            return call_user_func('random_int', $min, $max);
        }

        return mt_rand($min, $max);
    }
}

if(!isset($__wordpatch_hasher)) {
    $__wordpatch_hasher = null;

}

if(!function_exists('wordpatch_hasher')) {
    function wordpatch_hasher() {
        global $__wordpatch_hasher;

        if(empty($__wordpatch_hasher)) {
            require_once(dirname(__FILE__) . '/classes/class-phpass.php');
            $__wordpatch_hasher = new WordpatchPasswordHash(8, true);
        }

        return $__wordpatch_hasher;
    }
}
if(!function_exists('wordpatch_check_password')) {
    function wordpatch_check_password($password, $hash, $user_id = '') {
        $hasher = wordpatch_hasher();

        // If the hash is still md5...
        if(strlen($hash) <= 32) {
            $check = wordpatch_hash_equals($hash, md5($password));
            return $check;
        }

        $check = $hasher->CheckPassword($password, $hash);
        return $check;
    }
}

if(!function_exists('wordpatch_sha1')) {
    function wordpatch_sha1($data) {
        if(function_exists('hash')) {
            return hash('sha1', $data);
        } else {
            return sha1($data);
        }
    }

}
if(!function_exists('wordpatch_hash_hmac')) {
    /**
     * @param $algo
     * @param $data
     * @param $key
     * @param bool $raw_output
     * @return bool|string
     * @adapted hash_hmac + _hash_hmac
     */
    function wordpatch_hash_hmac($algo, $data, $key, $raw_output = false) {
        if(function_exists('hash') && function_exists('hash_hmac')) {
            return call_user_func('hash_hmac', $algo, $data, $key, $raw_output);
        }

        $packs = array('md5' => 'H32', 'sha1' => 'H40');

        if(!isset($packs[$algo])) {
            return false;
        }

        $pack = $packs[$algo];

        if(strlen($key) > 64)
            $key = pack($pack, $algo($key));

        $key = str_pad($key, 64, chr(0));

        $ipad = (substr($key, 0, 64) ^ str_repeat(chr(0x36), 64));
        $opad = (substr($key, 0, 64) ^ str_repeat(chr(0x5C), 64));

        $hmac = $algo($opad . pack($pack, $algo($ipad . $data)));

        if($raw_output)
            return pack($pack, $hmac);

        return $hmac;
    }
}

if(!function_exists('wordpatch_hash')) {
    /**
     * @param $data
     * @param string $scheme
     * @return false|string
     * @adapted wp_hash
     */
    function wordpatch_hash($wpenv_vars, $data, $scheme = 'auth')
    {
        $salt = wordpatch_env_get($wpenv_vars, 'salt_' . $scheme);
        return wordpatch_hash_hmac('md5', $data, $salt);
    }
}

if(!function_exists('wordpatch_map_deep')) {
    /**
     * @param $value
     * @param $callback
     * @return array|mixed
     * @adapted map_deep
     */
    function wordpatch_map_deep($value, $callback) {
        if(is_array($value)) {
            foreach($value as $index => $item) {
                $value[$index] = wordpatch_map_deep($item, $callback);
            }
        } elseif(is_object($value)) {
            $object_vars = get_object_vars($value);
            foreach($object_vars as $property_name => $property_value) {
                $value->$property_name = wordpatch_map_deep($property_value, $callback);
            }
        } else {
            $value = call_user_func($callback, $value);
        }

        return $value;
    }
}

if(!function_exists('wordpatch_stripslashes_from_strings_only')) {
    /**
     * @param $value
     * @return string
     * @adapted stripslashes_from_strings_only
     */
    function wordpatch_stripslashes_from_strings_only($value)
    {
        return is_string($value) ? stripslashes($value) : $value;
    }
}

if(!function_exists('wordpatch_stripslashes_deep')) {
    /**
     * @param $value
     * @return mixed
     * @adapted stripslashes_deep
     */
    function wordpatch_stripslashes_deep($value)
    {
        return wordpatch_map_deep($value, 'wordpatch_stripslashes_from_strings_only');
    }
}

if(!function_exists('wordpatch_unslash')) {
    /**
     * @param $value
     * @return mixed
     * @adapted wp_unslash
     * @qc
     */
    function wordpatch_unslash($value) {
        return wordpatch_stripslashes_deep($value);
    }
}

if(!function_exists('wordpatch_maybe_unserialize')) {
    function wordpatch_maybe_unserialize($original)
    {
        if(wordpatch_is_serialized($original)) {
            return @unserialize($original);
        }
        return $original;
    }
}

if(!function_exists('wordpatch_is_serialized')) {
    function wordpatch_is_serialized($data, $strict = true) {
        // if it isn't a string, it isn't serialized.
        if(!is_string($data)) {
            return false;
        }

        $data = trim($data);
        if('N;' == $data) {
            return true;
        }

        if(strlen($data)<4) {
            return false;
        }

        if(':' !== $data[1]) {
            return false;
        }

        if($strict) {
            $lastc = substr($data, -1);

            if(';' !== $lastc && '}' !== $lastc) {
                return false;
            }
        } else {
            $semicolon = strpos($data, ';');
            $brace = strpos($data, '}');

            // Either ; or } must exist.
            if(false === $semicolon && false === $brace) {
                return false;
            }

            // But neither must be in the first X characters.
            if(false !== $semicolon && $semicolon < 3) {
                return false;
            }

            if(false !== $brace && $brace < 4) {
                return false;
            }
        }
        $token = $data[0];
        switch($token) {
            case 's' :
                if($strict) {
                    if('"' !== substr($data, -2, 1)) {
                        return false;
                    }
                } elseif(false === strpos($data, '"')) {
                    return false;
                }
            // or else fall through
            case 'a' :
            case 'O' :
                return (bool)preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b' :
            case 'i' :
            case 'd' :
                $end = $strict ? '$' : '';
                return (bool)preg_match("/^{$token}:[0-9.E-]+;$end/", $data);
        }

        return false;
    }
}

if(!function_exists('wordpatch_is_email')) {
    function wordpatch_is_email($email, $deprecated = false) {
        if(strlen($email) < 6) {
            return false;
        }

        if(strpos($email, '@', 1) === false) {
            return false;
        }

        // Split out the local and domain parts
        list($local, $domain) = explode('@', $email, 2);

        // LOCAL PART
        // Test for invalid characters
        if(!preg_match('/^[a-zA-Z0-9!#$%&\'*+\/=?^_`{|}~\.-]+$/', $local)) {
            return false;
        }

        if(preg_match('/\.{2,}/', $domain)) {
            return false;
        }

        // Test for leading and trailing periods and whitespace
        if(trim($domain, " \t\n\r\0\x0B.") !== $domain) {
            return false;
        }

        // Split the domain into subs
        $subs = explode('.', $domain);

        // Assume the domain will have at least two subs
        if(2 > count($subs)) {
            return false;
        }

        // Loop through each sub
        foreach($subs as $sub) {
            // Test for leading and trailing hyphens and whitespace
            if(trim($sub, " \t\n\r\0\x0B-") !== $sub) {
                return false;
            }

            // Test for invalid characters
            if(!preg_match('/^[a-z0-9-]+$/i', $sub)) {
                return false;
            }
        }

        return true;
    }
}

if(!isset($__wordpatch_roles_cache)) {
    $__wordpatch_roles_cache = null;
}

if(!function_exists('wordpatch_get_roles')) {
    function wordpatch_get_roles($wpenv_vars, $cache = true)
    {
        global $__wordpatch_roles_cache;

        if($__wordpatch_roles_cache && $cache) {
            return $__wordpatch_roles_cache;
        }

        // @adaptednote: MU support

        $roles_key = wordpatch_db_table_prefix($wpenv_vars) . 'user_roles';
        $roles = wordpatch_get_option($wpenv_vars, $roles_key);

        if(is_object($roles)) {
            $roles = (array)$roles;
        }

        if(!$roles || !is_array($roles) || empty($roles)) {
            $roles = array();
        }

        $__wordpatch_roles_cache = $roles;
        return $roles;
    }
}

if(!function_exists('wordpatch_kses_no_null')) {
    /**
     * @param $string
     * @param null $options
     * @return mixed
     * @adapted wp_kses_no_null
     */
    function wordpatch_kses_no_null($string, $options = null) {
        if(!isset($options['slash_zero'])) {
            $options = array('slash_zero' => 'remove');
        }

        $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $string);

        if('remove' == $options['slash_zero']) {
            $string = preg_replace('/\\\\+0+/', '', $string);
        }

        return $string;
    }
}

if(!function_exists('_wordpatch_deep_replace')) {
    /**
     * @param $search
     * @param $subject
     * @return mixed|string
     * @adapted _deep_replace
     */
    function _wordpatch_deep_replace($search, $subject) {
        $subject = (string)$subject;
        $count = 1;

        while($count) {
            $subject = str_replace($search, '', $subject, $count);
        }

        return $subject;
    }
}

if(!function_exists('wordpatch_looks_like_bool')) {
    /**
     * Does $boolish look like a bool? (is it a number/bool or equal to the word "true"/"false")
     * @param $boolish
     * @return bool
     */
    function wordpatch_looks_like_bool($boolish)
    {
        if(is_bool($boolish) || is_numeric($boolish))
        {
            return true;
        }

        if(is_string($boolish))
        {
            if(strtolower(trim($boolish)) === 'true' || strtolower(trim($boolish)) === 'false')
            {
                return true;
            }

            return false;
        }

        return false;
    }
}

if(!function_exists('wordpatch_hash_equals')) {
    /**
     * @param $a
     * @param $b
     * @return bool
     * @adapted hash_equals
     */
    function wordpatch_hash_equals($a, $b)
    {
        $a_length = strlen($a);

        if($a_length !== strlen($b)) {
            return false;
        }

        $result = 0;

        // Do not attempt to "optimize" this.
        for($i = 0; $i < $a_length; $i++) {
            $result |= ord($a[$i]) ^ ord($b[$i]);
        }

        return $result === 0;
    }
}

if(!function_exists('wordpatch_get_server_protocol')) {
    /**
     * @return string
     * @adapted wp_get_server_protocol
     */
    function wordpatch_get_server_protocol() {
        $protocol = $_SERVER['SERVER_PROTOCOL'];

        if(!in_array($protocol, array('HTTP/1.1', 'HTTP/2', 'HTTP/2.0'))) {
            $protocol = 'HTTP/1.0';
        }

        return $protocol;
    }
}

if(!function_exists('wordpatch_generate_password')) {
    /**
     * @param int $length
     * @param bool $special_chars
     * @param bool $extra_special_chars
     * @return mixed
     * @adapted wp_generate_password
     */
    function wordpatch_generate_password($length = 12, $special_chars = true, $extra_special_chars = false)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        if($special_chars)
            $chars .= '!@#$%^&*()';

        if($extra_special_chars)
            $chars .= '-_ []{}<>~`+=,.;:/?|';

        $password = '';
        for($i = 0; $i < $length; $i++) {
            $password .= substr($chars, wordpatch_rand(0, strlen($chars) - 1), 1);
        }

        return $password;
    }
}

if(!isset($__wordpatch_force_ssl_admin_cache)) {
    $__wordpatch_force_ssl_admin_cache = null;
}

if(!function_exists('wordpatch_force_ssl_admin')) {
    function wordpatch_force_ssl_admin($force = null)
    {
        global $__wordpatch_force_ssl_admin_cache;

        if(!is_null($force)) {
            $old_forced = $__wordpatch_force_ssl_admin_cache;
            $__wordpatch_force_ssl_admin_cache = $force;
            return $old_forced;
        }

        return $__wordpatch_force_ssl_admin_cache;
    }
}

if(!isset($__wordpatch_header_to_desc_cache)) {
    $__wordpatch_header_to_desc_cache = null;
}

if(!function_exists('wordpatch_get_status_header_desc')) {
    function wordpatch_get_status_header_desc($code) {
        global $__wordpatch_header_to_desc_cache;

        $code = wordpatch_absint($code);

        if(!isset($__wordpatch_header_to_desc_cache)) {
            $__wordpatch_header_to_desc_cache = array(
                100 => 'Continue',
                101 => 'Switching Protocols',
                102 => 'Processing',
                200 => 'OK',
                201 => 'Created',
                202 => 'Accepted',
                203 => 'Non-Authoritative Information',
                204 => 'No Content',
                205 => 'Reset Content',
                206 => 'Partial Content',
                207 => 'Multi-Status',
                226 => 'IM Used',
                300 => 'Multiple Choices',
                301 => 'Moved Permanently',
                302 => 'Found',
                303 => 'See Other',
                304 => 'Not Modified',
                305 => 'Use Proxy',
                306 => 'Reserved',
                307 => 'Temporary Redirect',
                308 => 'Permanent Redirect',
                400 => 'Bad Request',
                401 => 'Unauthorized',
                402 => 'Payment Required',
                403 => 'Forbidden',
                404 => 'Not Found',
                405 => 'Method Not Allowed',
                406 => 'Not Acceptable',
                407 => 'Proxy Authentication Required',
                408 => 'Request Timeout',
                409 => 'Conflict',
                410 => 'Gone',
                411 => 'Length Required',
                412 => 'Precondition Failed',
                413 => 'Request Entity Too Large',
                414 => 'Request-URI Too Long',
                415 => 'Unsupported Media Type',
                416 => 'Requested Range Not Satisfiable',
                417 => 'Expectation Failed',
                418 => 'I\'m a teapot',
                421 => 'Misdirected Request',
                422 => 'Unprocessable Entity',
                423 => 'Locked',
                424 => 'Failed Dependency',
                426 => 'Upgrade Required',
                428 => 'Precondition Required',
                429 => 'Too Many Requests',
                431 => 'Request Header Fields Too Large',
                451 => 'Unavailable For Legal Reasons',
                500 => 'Internal Server Error',
                501 => 'Not Implemented',
                502 => 'Bad Gateway',
                503 => 'Service Unavailable',
                504 => 'Gateway Timeout',
                505 => 'HTTP Version Not Supported',
                506 => 'Variant Also Negotiates',
                507 => 'Insufficient Storage',
                510 => 'Not Extended',
                511 => 'Network Authentication Required',
            );
        }

        if(isset($__wordpatch_header_to_desc_cache[$code])) {
            return $__wordpatch_header_to_desc_cache[$code];
        } else {
            return '';
        }
    }
}

if(!function_exists('wordpatch_status_header')) {
    /**
     * @param $code
     * @param string $description
     * @adapted status_header
     */
    function wordpatch_status_header($code, $description = '') {
        if(!$description) {
            $description = wordpatch_get_status_header_desc($code);
        }

        if(empty($description)) {
            return;
        }

        $protocol = wordpatch_get_server_protocol();
        $status_header = "$protocol $code $description";

        @header($status_header, true, $code);
    }
}

if(!function_exists('wordpatch_max_upload_size')) {
    function wordpatch_max_upload_size() {
        $u_bytes = wordpatch_convert_hr_to_bytes(ini_get('upload_max_filesize'));
        $p_bytes = wordpatch_convert_hr_to_bytes(ini_get('post_max_size'));

        $ret_value = min($u_bytes, $p_bytes);

        if(!$ret_value) {
            $ret_value = 0;
        }

        return $ret_value;
    }
}

if(!function_exists('wordpatch_kb_in_bytes')) {
    function wordpatch_kb_in_bytes() {
        return 1024;
    }
}

if(!function_exists('wordpatch_mb_in_bytes')) {
    function wordpatch_mb_in_bytes() {
        return 1024 * wordpatch_kb_in_bytes();
    }
}

if(!function_exists('wordpatch_gb_in_bytes')) {
    function wordpatch_gb_in_bytes() {
        return 1024 * wordpatch_mb_in_bytes();
    }
}

if(!function_exists('wordpatch_tb_in_bytes')) {
    function wordpatch_tb_in_bytes() {
        return 1024 * wordpatch_gb_in_bytes();
    }
}

if(!function_exists('wordpatch_convert_hr_to_bytes')) {
    function wordpatch_convert_hr_to_bytes($value) {
        $value = strtolower(trim($value));
        $bytes = (int)$value;

        if(false !== strpos($value, 'g')) {
            $bytes *= wordpatch_gb_in_bytes();
        } elseif (false !== strpos($value, 'm')) {
            $bytes *= wordpatch_mb_in_bytes();
        } elseif (false !== strpos($value, 'k')) {
            $bytes *= wordpatch_kb_in_bytes();
        }

        // Deal with large (float) values which run into the maximum integer size.
        return min($bytes, PHP_INT_MAX);
    }
}

if(!function_exists('wordpatch_size_format')) {
    function wordpatch_size_format($bytes, $decimals = 0) {
        $quant = array(
            'TB' => wordpatch_tb_in_bytes(),
            'GB' => wordpatch_gb_in_bytes(),
            'MB' => wordpatch_mb_in_bytes(),
            'KB' => wordpatch_kb_in_bytes(),
            'B'  => 1,
        );

        if(0 === $bytes) {
            return number_format(0, $decimals) . ' B';
        }

        foreach($quant as $unit => $mag) {
            if(doubleval($bytes) >= $mag) {
                return number_format($bytes / $mag, $decimals) . ' ' . $unit;
            }
        }

        return false;
    }
}