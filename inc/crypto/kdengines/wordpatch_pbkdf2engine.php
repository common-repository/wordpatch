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

if(!function_exists('WordpatchPBKDF2Engine_available')) {
    /**
     * Checks if the key derivation engine is available on this system.
     * @return bool
     */
    function WordpatchPBKDF2Engine_available()
    {
        return true;
    }
}

if(!function_exists('WordpatchPBKDF2Engine')) {
    class WordpatchPBKDF2Engine extends WordpatchIKDEngine
    {
        /**
         * Derives a new key from the supplied components.
         * @param string $a
         * @param string $b
         * @return string
         */
        function derive($a, $b)
        {
            // TODO: Convert to const.
            $keyLength = 128;
            $iterations = 2048;

            // Use the built-in implementation if it exists.
            if (function_exists('hash_pbkdf2')) {
                return hash_pbkdf2('sha256', $a, $b, $iterations, $keyLength, true);
            }

            // Otherwise derive the key manually with the help of phpseclib.
            $hash = new Wordpatch_Crypt_Hash('sha256');
            $hash->setKey($a);

            $key = '';
            $i = 1;

            while (strlen($key) < $keyLength) {
                $f = $u = $hash->hash($b . pack('N', $i++));

                for ($j = 2; $j <= $iterations; ++$j) {
                    $u = $hash->hash($u);
                    $f ^= $u;
                }

                $key .= $f;
            }

            return substr($key, 0, $keyLength);
        }

        /**
         * Returns the name of this engine.
         * @return string
         */
        function name()
        {
            return "PBKDF2";
        }

        /**
         * Returns the unique ID of this engine.
         * @return integer
         */
        function id()
        {
            return WordpatchIKDEngine_EID_PBKDF2();
        }
    }
}