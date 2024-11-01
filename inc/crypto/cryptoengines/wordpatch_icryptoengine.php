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

if(!class_exists('WordpatchICryptoEngine')) {
    class WordpatchICryptoEngine
    {
        /**
         * Initialize the CryptoEngine.
         */
        function init($wpenv_vars)
        {

        }

        /**
         * Creates a new secure identity used for encryption and digital signing.
         * When creating a new identity you can request to be given the public
         * and/or private vectors of that identity. You can then provide those
         * vectors as inputs in a different system to initialize the same identity.
         * Keep in mind that after initial creation you will not be able to
         * access the private and public vectors again.
         *
         * When requesting public or private data, it is recommended to call
         * `cleanup()` on the provided `CryptoIdentity` object after you have
         * finished using said data.
         *
         * @param string|null $alias The unique string alias of this identity. Must be smaller than 21 characters.
         * @param bool $public Return the public vector of this identity.
         * @param bool $private Return the private vector of this identity.
         * @return WordpatchCryptoIdentity|null
         */
        function &createIdentity($wpenv_vars, $alias = null, $public = false, $private = false)
        {
            return null;
        }

        /**
         * Returns the public data for a specific identity.
         *
         * @param string $identity
         * @return WordpatchCryptoIdentity|null
         */
        function &getIdentity($wpenv_vars, $identity)
        {
            return null;
        }

        /**
         * Stores or updates an identity with the provided public and/or
         * private vectors.
         *
         * @param string $identity
         * @param string|null $publicVector
         * @param string|null $privateVector
         * @return WordpatchCryptoIdentity|null
         */
        function &storeIdentity($wpenv_vars, $identity, $publicVector, $privateVector = null)
        {
            return null;
        }

        /**
         * Remove an identity from the system and securely purge its data.
         *
         * @param WordpatchCryptoIdentity|string &$identity
         */
        function purgeIdentity($wpenv_vars, &$identity)
        {

        }

        /**
         * Purges all relevant data for this engine.
         * @param $wpenv_vars
         */
        function purge($wpenv_vars)
        {
        }
    }
}