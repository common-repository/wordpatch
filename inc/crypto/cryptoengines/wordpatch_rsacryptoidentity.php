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

if(!function_exists('WordpatchRSACryptoIdentity_create')) {
    /**
     * RSACryptoIdentity constructor.
     * @param $wpenv_vars
     * @param $secretInstance
     * @param WordpatchRSACryptoIdentity|string|null $identity
     * @param bool $create
     * @param bool $public
     * @param bool $private
     * @return WordpatchRSACryptoIdentity|null
     */
    function &WordpatchRSACryptoIdentity_create($wpenv_vars, &$secretInstance, $identity = null, $create = false, $public = false, $private = false)
    {
        $i = new WordpatchRSACryptoIdentity;
        WordpatchCryptoIdentity_init($i);

        $i->secretInstance = $secretInstance;
        $success = false;

        if (is_a($identity, 'WordpatchRSACryptoIdentity')) {
            $success = $i->_assignIdentity($wpenv_vars, $identity, $public, $private);
        } else if ($create) {
            $success = $i->_createIdentity($wpenv_vars, $identity, $public, $private);
        } else {
            $success = $i->_loadIdentity($wpenv_vars, $identity);
        }

        $ret_value = $success ? $i : null;
        return $ret_value;
    }
}

if(!class_exists('WordpatchRSACryptoIdentity')) {
    class WordpatchRSACryptoIdentity extends WordpatchCryptoIdentity
    {
        /**
         * @var
         */
        var $secretInstance;

        /**
         * @param WordpatchRSACryptoIdentity $identity
         * @param bool $public
         * @param bool $private
         * @return bool
         */
        function _assignIdentity($wpenv_vars, &$identity, $public, $private)
        {
            $this->id = $identity->getId();
            $this->alias = $identity->getAlias();
            $this->public = $public ? $identity->getPublic() : null;
            $this->private = $private ? $identity->getPrivate() : null;
            return true;
        }

        /**
         * @param string|null $alias
         * @param bool $public
         * @param bool $private
         * @return bool
         */
        function _createIdentity($wpenv_vars, $alias, $public, $private)
        {
            include_once(WORDPATCH_PHPSECLIB_DIR . 'Crypt/Wordpatch_RSA.php');
            include_once WORDPATCH_PHPSECLIB_DIR . 'Crypt/Wordpatch_Random.php';

            // TODO: `ctype_alpha` works based on the current locale. Do we need to change it?
            if ($alias !== null && (strlen($alias) < 2 || strlen($alias) > 20 || !ctype_alpha($alias))) {
                return false;
            }

            // Otherwise create a new one.
            $rsa = new Wordpatch_Crypt_RSA();

            $rsa->setPrivateKeyFormat(WORDPATCH_CRYPT_RSA_PRIVATE_FORMAT_PKCS1);
            $rsa->setPublicKeyFormat(WORDPATCH_CRYPT_RSA_PUBLIC_FORMAT_PKCS1);

            $secretKey = $this->secretInstance->getSecretKey($wpenv_vars);

            if($secretKey === null) {
                return false;
            }

            $rsa->setPassword($secretKey);

            $key = $rsa->createKey(2048);
            $pubKey = $key['publickey'];
            $privKey = $key['privatekey'];

            // Generate a new ID for this identity.
            $this->id = WordpatchCrypto_generateUuid();
            $this->alias = $alias;
            $this->public = $public ? $pubKey : null;
            $this->private = $private ? $privKey : null;

            $escIdentityId = wordpatch_esc_sql($wpenv_vars, $this->id);
            $escIdentityAlias = wordpatch_esc_sql($wpenv_vars, $this->alias);
            $encIdentityPublic = wordpatch_encode_sql($wpenv_vars, $pubKey, true);
            $encIdentityPrivate = wordpatch_encode_sql($wpenv_vars, $privKey, true);
            $escInstanceKey = wordpatch_esc_sql($wpenv_vars, $this->secretInstance->instanceKey);

            $rceIdentitiesTable = wordpatch_rce_identities_table($wpenv_vars);

            $insertQuery = "INSERT INTO `$rceIdentitiesTable` (`identity_id`, `identity_alias`, `identity_public`, `identity_private`, `identity_created`, `identity_engine_key`) " .
                "VALUES ('$escIdentityId', '$escIdentityAlias', $encIdentityPublic, $encIdentityPrivate, UTC_TIMESTAMP(), '$escInstanceKey')";

            // Store identity data in the database.
            $dbResult = wordpatch_db_query($wpenv_vars, $insertQuery);

            if (!$dbResult) {
                return false;
            }

            return true;
        }

        /**
         * @param string $identity
         * @return bool
         */
        function _loadIdentity($wpenv_vars, $identity)
        {
            if (!is_string($identity) || (strlen($identity) !== 36 && (strlen($identity) < 2 || strlen($identity) > 20))) {
                return false;
            }

            // Form our query.
            $rceIdentitiesTable = wordpatch_rce_identities_table($wpenv_vars);

            // Should be safe to escape normally since they are alphanumeric
            $escIdentity = wordpatch_esc_sql($wpenv_vars, $identity);

            // If the length is 36 the passed string is an id, not an alias.
            if (strlen($identity) === 36) {
                $query = "SELECT `identity_id`, `identity_alias`, `identity_created` FROM `$rceIdentitiesTable` WHERE `identity_id` = '$escIdentity' LIMIT 1";
            } else {
                $query = "SELECT `identity_id`, `identity_alias`, `identity_created` FROM `$rceIdentitiesTable` WHERE `identity_alias` = '$escIdentity' LIMIT 1";
            }

            // Fetch identity data.
            $dbRow = wordpatch_db_get_row($wpenv_vars, $query);

            if (!$dbRow) {
                return false;
            }

            $this->id = $dbRow['identity_id'];
            $this->alias = $dbRow['identity_alias'];

            return true;
        }

        /**
         * Remove an identity from the system and securely purge its data.
         */
        function purge($wpenv_vars)
        {
            $rceIdentitiesTable = wordpatch_rce_identities_table($wpenv_vars);
            $escIdentityId = wordpatch_esc_sql($wpenv_vars, $this->id);

            $deleteQuery = "DELETE FROM `$rceIdentitiesTable` WHERE `identity_id` = '$escIdentityId'";
            $dbResult = wordpatch_db_query($wpenv_vars, $deleteQuery);

            if (!$dbResult) {
                return;
            }

            $this->cleanup($wpenv_vars);
        }

        /**
         * @param $wpenv_vars
         * @return Wordpatch_Crypt_RSA|null
         */
        function &_getPublicRSA($wpenv_vars)
        {
            include_once(WORDPATCH_PHPSECLIB_DIR . 'Crypt/Wordpatch_RSA.php');
            include_once WORDPATCH_PHPSECLIB_DIR . 'Crypt/Wordpatch_Random.php';

            $rceIdentitiesTable = wordpatch_rce_identities_table($wpenv_vars);
            $escIdentityId = wordpatch_esc_sql($wpenv_vars, $this->id);

            $selectQuery = "SELECT `identity_public` FROM `$rceIdentitiesTable` WHERE `identity_id` = '$escIdentityId' LIMIT 1";
            $dbRow = wordpatch_db_get_row($wpenv_vars, $selectQuery);

            if (!$dbRow || empty($dbRow['identity_public'])) {
                $null_ref = null;
                return $null_ref;
            }

            $rsa = new Wordpatch_Crypt_RSA();
            $rsa->setHash('sha256');
            $rsa->setMGFHash('sha256');
            $rsa->setEncryptionMode(WORDPATCH_CRYPT_RSA_ENCRYPTION_OAEP);
            $rsa->setSignatureMode(WORDPATCH_CRYPT_RSA_SIGNATURE_PSS);
            $rsa->setSaltLength(32);

            $rsa->loadKey($dbRow['identity_public'], WORDPATCH_CRYPT_RSA_PUBLIC_FORMAT_PKCS1);

            return $rsa;
        }

        /**
         * @param $wpenv_vars
         * @return null|Wordpatch_Crypt_RSA
         */
        function &_getPrivateRSA($wpenv_vars)
        {
            include_once(WORDPATCH_PHPSECLIB_DIR . 'Crypt/Wordpatch_RSA.php');
            include_once WORDPATCH_PHPSECLIB_DIR . 'Crypt/Wordpatch_Random.php';

            $rceIdentitiesTable = wordpatch_rce_identities_table($wpenv_vars);
            $escIdentityId = wordpatch_esc_sql($wpenv_vars, $this->id);

            $selectQuery = "SELECT `identity_private` FROM `$rceIdentitiesTable` WHERE `identity_id` = '$escIdentityId' LIMIT 1";
            $dbRow = wordpatch_db_get_row($wpenv_vars, $selectQuery);

            if (!$dbRow || empty($dbRow['identity_private'])) {
                $null_ref = null;
                return $null_ref;
            }

            $rsa = new Wordpatch_Crypt_RSA();
            $rsa->setHash('sha256');
            $rsa->setMGFHash('sha256');
            $rsa->setEncryptionMode(WORDPATCH_CRYPT_RSA_ENCRYPTION_OAEP);
            $rsa->setSignatureMode(WORDPATCH_CRYPT_RSA_SIGNATURE_PSS);
            $rsa->setSaltLength(32);

            $secretKey = $this->secretInstance->getSecretKey($wpenv_vars);

            if($secretKey === null) {
                $null_ref = null;
                return $null_ref;
            }

            $rsa->setPassword($secretKey);

            $rsa->loadKey($dbRow['identity_private'], WORDPATCH_CRYPT_RSA_PRIVATE_FORMAT_PKCS1);

            return $rsa;
        }

        /**
         * Encrypts the provided data.
         *
         * @param $wpenv_vars
         * @param string $data
         * @return string|null
         */
        function encrypt($wpenv_vars, $data)
        {
            $rsa = $this->_getPublicRSA($wpenv_vars);

            if($rsa === null) {
                return null;
            }

            return $rsa->encrypt($data);
        }

        /**
         * Decrypts the provided data.
         *
         * @param $wpenv_vars
         * @param string $data
         * @return string|null
         */
        function decrypt($wpenv_vars, $data)
        {
            $rsa = $this->_getPrivateRSA($wpenv_vars);

            if($rsa === null) {
                return null;
            }

            return $rsa->decrypt($data);
        }

        /**
         * Creates a digital signature for the provided data.
         *
         * @param $wpenv_vars
         * @param string $data
         * @return string|null
         */
        function sign($wpenv_vars, $data)
        {
            $rsa = $this->_getPrivateRSA($wpenv_vars);

            if($rsa === null) {
                return null;
            }

            return $rsa->sign($data);
        }

        /**
         * Verifies the validity of the signature of the provided data.
         *
         * @param $wpenv_vars
         * @param string $data
         * @param string $signature
         * @return bool
         */
        function verify($wpenv_vars, $data, $signature)
        {
            $rsa = $this->_getPublicRSA($wpenv_vars);

            if($rsa === null) {
                return null;
            }

            return $rsa->verify($data, $signature);
        }
    }
}