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

if(!isset($__WordpatchCrypto_bootstrapEngines)) {
    /**
     * Add the engines you wish to bootstrap during app initialization.
     *
     * @var array $bootstrapEngines
     */
    $__WordpatchCrypto_bootstrapEngines = array(
        array(
            'engine' => 'WordpatchRSACryptoEngine',
            'arguments' => array('db')
        ),
        array(
            'engine' => 'WordpatchRSACryptoEngine',
            'arguments' => array('api')
        )
    );
}

if(!isset($__WordpatchCrypto_engines)) {
    /**
     * @var $__WordpatchCrypto_engines array
     */
    $__WordpatchCrypto_engines = array();
}

if(!function_exists('WordpatchCrypto_get')) {
    /**
     * Returns a Crypto Engine instance for the provided engine type.
     *
     * Example usage:
     * ```
     * $crypto = WordpatchCrypto_get($wpenv_vars, 'WordpatchRSACryptoEngine');
     * ```
     *
     * @param $engineClass
     * @param array $arguments
     * @return WordpatchICryptoEngine|null
     */
    function &WordpatchCrypto_get($wpenv_vars, $engineClass, $arguments = array())
    {
        global $__WordpatchCrypto_engines;

        $engineKey = serialize(array($engineClass, $arguments));

        // Return engine if already initialized.
        if (array_key_exists($engineKey, $__WordpatchCrypto_engines)) {
            return $__WordpatchCrypto_engines[$engineKey];
        }

        // Otherwise check if it's really a CryptoEngine.
        if (!is_subclass_of($engineClass, 'WordpatchICryptoEngine')) {
            $null_ref = null;
            return $null_ref;
        }

        // If it is, create it, initialize it, store it, and return it.
        /** @var WordpatchICryptoEngine $engine */
        $createFnName = $engineClass . '_create';
        array_unshift($arguments, $wpenv_vars); // This adds $wpenv_vars to the function call as necessary
        $engine = call_user_func_array($createFnName, $arguments);

        $engine->init($wpenv_vars);

        $__WordpatchCrypto_engines[$engineKey] = $engine;
        return $engine;
    }
}

if(!function_exists('WordpatchCrypto_init')) {
    /**
     * Initialize the Crypto engine.
     */
    function WordpatchCrypto_init($wpenv_vars)
    {
        global $__WordpatchCrypto_bootstrapEngines;

        // Bootstrap the various crypto engines.
        foreach ($__WordpatchCrypto_bootstrapEngines as $_ => $info) {
            WordpatchCrypto_get($wpenv_vars, $info['engine'], $info['arguments']);
        }
    }
}

if(!function_exists('WordpatchCrypto_genRandom')) {
    /**
     * Generates a string consisting of random bytes.
     *
     * @param integer $length
     * @return string
     */
    function WordpatchCrypto_genRandom($length)
    {
        include_once WORDPATCH_PHPSECLIB_DIR . 'Crypt/Wordpatch_Random.php';

        return wordpatch_crypt_random_string($length);
    }
}

if(!function_exists('WordpatchCrypto_generateUuid')) {
    /**
     * Generates a new UUIDv4.
     * @return string
     */
    function WordpatchCrypto_generateUuid()
    {
        $data = WordpatchCrypto_genRandom(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}