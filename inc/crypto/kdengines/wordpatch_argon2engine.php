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

if(!function_exists('WordpatchArgon2Engine_available')) {
    /**
     * Checks if the key derivation engine is available on this system.
     * @return bool
     */
    function WordpatchArgon2Engine_available()
    {
        return function_exists('sodium_crypto_pwhash');
    }
}

if(!class_exists('WordpatchArgon2Engine')) {
    class WordpatchArgon2Engine extends WordpatchIKDEngine
    {
        /**
         * Derives a new key from the supplied components.
         * @param string $a
         * @param string $b
         * @return string
         */
        function derive($a, $b)
        {
            if (!WordpatchArgon2Engine_available()) {
                return null;
            }

            // TODO: Convert to const.
            $keyLength = 128;
            $ops = 4;
            $mem = 16 << 20; // 16 MB

            return sodium_crypto_pwhash(
                $keyLength,
                $a,
                substr($b, 0, constant('SODIUM_CRYPTO_PWHASH_SALTBYTES')),
                $ops,
                $mem
            );
        }

        /**
         * Returns the name of this engine.
         * @return string
         */
        function name()
        {
            return "Argon2";
        }

        /**
         * Returns the unique ID of this engine.
         * @return integer
         */
        function id()
        {
            return WordpatchIKDEngine_EID_ARGON2();
        }
    }
}