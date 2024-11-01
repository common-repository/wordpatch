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

if(!function_exists('WordpatchCryptoIdentity_init')) {
    /**
     * CryptoIdentity constructor.
     * @param WordpatchCryptoIdentity &$cryptoIdentity
     * @param string|null $id
     * @param string|null $alias
     * @param string|null $public
     * @param string|null $private
     */
    function WordpatchCryptoIdentity_init(&$cryptoIdentity, $id = null, $alias = null, $public = null, $private = null)
    {
        $cryptoIdentity->id = $id;
        $cryptoIdentity->alias = $alias;
        $cryptoIdentity->public = $public;
        $cryptoIdentity->private = $private;
    }
}

if(!class_exists('WordpatchCryptoIdentity')) {
    class WordpatchCryptoIdentity
    {
        var $id;
        var $alias;
        var $public;
        var $private;

        /**
         * Returns the ID of the identity.
         * @return string
         */
        function getId()
        {
            return $this->id;
        }

        /**
         * Returns the string alias of the identity, if available.
         * @return string|null
         */
        function getAlias()
        {
            return $this->alias;
        }

        /**
         * Returns the public data of the identity, if available.
         * @return string|null
         */
        function getPublic()
        {
            return $this->public;
        }

        /**
         * Returns the private data of the identity, if available.
         * @return string|null
         */
        function getPrivate()
        {
            return $this->private;
        }

        /**
         * Cleans up any sensitive identity data.
         */
        function cleanup($wpenv_vars)
        {
            $this->public = null;
            $this->private = null;
        }

        /**
         * Encrypts the provided data.
         *
         * @param string $data
         * @return string|null
         */
        function encrypt($wpenv_vars, $data)
        {
            return null;
        }

        /**
         * Decrypts the provided data.
         *
         * @param string $data
         * @return string|null
         */
        function decrypt($wpenv_vars, $data)
        {
            return null;
        }

        /**
         * Creates a digital signature for the provided data.
         *
         * @param string $data
         * @return string|null
         */
        function sign($wpenv_vars, $data)
        {
            return null;
        }

        /**
         * Verifies the validity of the signature of the provided data.
         *
         * @param string $data
         * @param string $signature
         * @return bool
         */
        function verify($wpenv_vars, $data, $signature)
        {
            return false;
        }
    }
}