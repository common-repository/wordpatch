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

if(!function_exists('WordpatchCryptoSalt_create')) {
    /**
     * Generates a new salt or loads an existing one.
     * @param string|null $data
     * @param string|null $engine
     * @return WordpatchCryptoSalt
     */
    function &WordpatchCryptoSalt_create($data = null, $engine = null)
    {
        $s = new WordpatchCryptoSalt;

        $s->data = !empty($data) ? $data : WordpatchCrypto_genRandom(64);
        $s->engine = !empty($engine) ? $engine : WordpatchKDManager_getBestEngine();

        return $s;
    }
}

if(!class_exists('WordpatchCryptoSalt')) {
    class WordpatchCryptoSalt
    {
        var $data;
        var $engine;

        /**
         * Returns the data of this salt.
         * @return string
         */
        function data()
        {
            return $this->data;
        }

        /**
         * Returns the supported Key Derivation engine for this salt.
         * @return string
         */
        function engine()
        {
            return $this->engine;
        }
    }
}