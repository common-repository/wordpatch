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
 * Implements the shared RSA functionality.
 */

if(!function_exists('wordpatch_generate_key_pair')) {
    /**
     * Generate an RSA key pair.
     *
     * @return array
     */
    function wordpatch_generate_key_pair()
    {
        include_once('phpseclib/wordpatch.php');
        include_once(WORDPATCH_PHPSECLIB_DIR . 'Crypt/Wordpatch_RSA.php');

        $rsa = new Wordpatch_Crypt_RSA();

        $rsa->setPrivateKeyFormat(WORDPATCH_CRYPT_RSA_PRIVATE_FORMAT_PKCS1);
        $rsa->setPublicKeyFormat(WORDPATCH_CRYPT_RSA_PUBLIC_FORMAT_PKCS1);

        $results = $rsa->createKey(2048);

        return array(
            'privatekey' => $results['privatekey'],
            'publickey' => $results['publickey'],
        );
    }
}