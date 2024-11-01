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

if(!function_exists('WordpatchRSACryptoEngine_create')) {
    function &WordpatchRSACryptoEngine_create($wpenv_vars, $key)
    {
        $e = new WordpatchRSACryptoEngine;
        $e->instanceKey = $key;

        return $e;
    }
}

class WordpatchRSACryptoEngine extends WordpatchICryptoEngine
{
    var $instanceKey;

    /**
     * Initialize the CryptoEngine.
     * @param $wpenv_vars
     */
    function init($wpenv_vars)
    {
        // no-op
    }

    /**
     * @param $wpenv_vars
     * @return WordpatchCryptoSalt
     */
    function &getSalt($wpenv_vars)
    {
        $configKey = WORDPATCH_RCE_SALT_KEY . '_' . strtolower($this->instanceKey);

        $encodedSalt = wordpatch_get_option($wpenv_vars, $configKey, null);

        if (($encodedSalt !== null && $encodedSalt !== false && $encodedSalt !== '') && !empty($encodedSalt))
        {
            // This will be b64 encoded
            $encodedSalt = base64_decode($encodedSalt);

            // Get the engine id.
            $saltParts = explode(':', $encodedSalt, 2);

            if (count($saltParts) < 2 || WordpatchKDManager_getEngineFromId($saltParts[0]) === null) {
                $encodedSalt = null;
            }
        }

        // No stored salt. Generate a new one.
        if ($encodedSalt === null || $encodedSalt === false || $encodedSalt === '')
        {
            $randomKey = WordpatchCrypto_genRandom(64);
            $encodedSalt = WordpatchKDManager_getIdFromEngine(WordpatchKDManager_getBestEngine()) . ':' . $randomKey;
            $b64EncodedSalt = base64_encode($encodedSalt);

            // Store it in the database.
            wordpatch_update_option($wpenv_vars, $configKey, $b64EncodedSalt);
        }

        // Get the real salt parts.
        $saltPieces = explode(':', $encodedSalt, 2);
        $pieceCount = count($saltPieces);

        if ($pieceCount !== 2) {
            $null_ref = null;
            return $null_ref;
        }

        // Default values for the IDE
        $engineId = $salt = null;

        // Just in case explode() shorthand doesn't work for PHP4
        if(is_array($saltPieces) && $pieceCount > 0) {
            $engineId = $saltPieces[0];
        }

        if(is_array($saltPieces) && $pieceCount > 1) {
            $salt = $saltPieces[1];
        }

        return WordpatchCryptoSalt_create($salt, WordpatchKDManager_getEngineFromId($engineId));
    }

    /**
     * @param $wpenv_vars
     * @return string
     */
    function getPass($wpenv_vars)
    {
        $constantName = WORDPATCH_RCE_PASS_KEY . '_' . strtolower($this->instanceKey);

        // Grab our location
        $rcePassLocation = wordpatch_get_option($wpenv_vars, $constantName, null);
        $secretsBucket = wordpatch_file_upload_bucket_secrets();

        if($rcePassLocation === null || $rcePassLocation === false || $rcePassLocation === '' || empty($rcePassLocation)) {
            $rcePassLocation = wordpatch_file_upload_generate_location();
            $rcePassword = WordpatchCrypto_genRandom(64);

            $writeError = wordpatch_file_upload_write_to_location($wpenv_vars, $secretsBucket, $rcePassLocation, $rcePassword);

            if($writeError) {
                // TODO: Error, unable to write to filesystem.
                return null;
            }

            wordpatch_update_option($wpenv_vars, $constantName, $rcePassLocation);

            return $rcePassword;
        }

        $readError = false;
        $rcePassword = wordpatch_file_upload_read_from_location($wpenv_vars, $secretsBucket, $rcePassLocation, $readError);

        if($readError) {
            // TODO: Error, unable to read from filesystem.
            return null;
        }

        return $rcePassword;
    }

    /**
     * @param $wpenv_vars
     * @return string|null
     */
    function getSecretKey($wpenv_vars)
    {
        $salt = $this->getSalt($wpenv_vars);
        $pass = $this->getPass($wpenv_vars);

        if($salt === null || $pass === null) {
            return null;
        }

        return WordpatchKDManager_derive($pass, $salt->data(), $salt->engine());
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
     * @param $wpenv_vars
     * @param string|null $alias The unique string alias of this identity. Must be smaller than 21 characters.
     * @param bool $public Return the public vector of this identity, if available.
     * @param bool $private Return the private vector of this identity, if available.
     * @return WordpatchRSACryptoIdentity
     */
    function &createIdentity($wpenv_vars, $alias = null, $public = false, $private = false)
    {
        return WordpatchRSACryptoIdentity_create($wpenv_vars, $this, $alias, true, $public, $private);
    }

    /**
     * Returns the public data for a specific identity.
     *
     * @param $wpenv_vars
     * @param string $identity
     * @return WordpatchRSACryptoIdentity|null
     */
    function &getIdentity($wpenv_vars, $identity)
    {
        return WordpatchRSACryptoIdentity_create($wpenv_vars, $this, $identity);
    }

    /**
     * Stores or updates an identity with the provided public and/or private vectors.
     *
     * @param $wpenv_vars
     * @param string $alias
     * @param string|null $publicVector
     * @param string|null $privateVector
     * @return WordpatchRSACryptoIdentity|null
     */
    function &storeIdentity($wpenv_vars, $alias, $publicVector, $privateVector = null)
    {
        // TODO: `ctype_alpha` works based on the current locale. Do we need to change it?
        if (strlen($alias) < 2 || strlen($alias) > 20 || !ctype_alpha($alias)) {
            $null_ref = null;
            return $null_ref;
        }

        $escIdentityId = wordpatch_esc_sql($wpenv_vars, WordpatchCrypto_generateUuid());
        $escIdentityAlias = wordpatch_esc_sql($wpenv_vars, $alias);
        $encIdentityPublic = wordpatch_encode_sql($wpenv_vars, $publicVector, true);
        $encIdentityPrivate = 'NULL';

        if ($privateVector !== null) {
            $encIdentityPrivate = wordpatch_encode_sql($wpenv_vars, $privateVector, true);
        }

        $escIdentityEngineKey = wordpatch_esc_sql($wpenv_vars, $this->instanceKey);

        $rceIdentitiesTable = wordpatch_rce_identities_table($wpenv_vars);

        $insertQuery = "INSERT INTO `$rceIdentitiesTable` (`identity_id`, `identity_alias`, `identity_public`, `identity_private`, `identity_created`, `identity_engine_key`) " .
            "VALUES ('$escIdentityId', '$escIdentityAlias', $encIdentityPublic, $encIdentityPrivate, UTC_TIMESTAMP(), '$escIdentityEngineKey') " .
            "ON DUPLICATE KEY UPDATE `identity_public` = VALUES(`identity_public`), `identity_private` = VALUES(`identity_private`)";

        $dbResult = wordpatch_db_query($wpenv_vars, $insertQuery);

        if (!$dbResult) {
            $null_ref = null;
            return $null_ref;
        }

        $retValue = $this->getIdentity($wpenv_vars, $alias);
        return $retValue;
    }

    /**
     * Remove an identity from the system and securely purge its data.
     *
     * @param $wpenv_vars
     * @param WordpatchRSACryptoIdentity|string $identity
     */
    function purgeIdentity($wpenv_vars, &$identity)
    {
        $realIdentity = is_a($identity, 'WordpatchRSACryptoIdentity')
            ? $identity
            : $this->getIdentity($wpenv_vars, $identity);

        $realIdentity->purge($wpenv_vars);
    }

    function purge($wpenv_vars)
    {
        // Purge the salt.
        $saltKey = WORDPATCH_RCE_SALT_KEY . '_' . strtolower($this->instanceKey);
        wordpatch_update_option($wpenv_vars, $saltKey, '');

        // Purge the pass.
        $passKey = WORDPATCH_RCE_PASS_KEY . '_' . strtolower($this->instanceKey);
        $rcePassLocation = wordpatch_get_option($wpenv_vars, $passKey, null);

        if ($rcePassLocation !== null && $rcePassLocation !== false && $rcePassLocation !== '' && !empty($rcePassLocation)) {
            wordpatch_file_upload_delete_location($wpenv_vars, wordpatch_file_upload_bucket_secrets(), $rcePassLocation);
        }

        wordpatch_update_option($wpenv_vars, $passKey, '');

        // Purge all identities.
        $escIdentityEngineKey = wordpatch_esc_sql($wpenv_vars, $this->instanceKey);
        $rceIdentitiesTable = wordpatch_rce_identities_table($wpenv_vars);

        $purgeQuery = "DELETE FROM `$rceIdentitiesTable` WHERE `identity_engine_key` = '$escIdentityEngineKey'";
        wordpatch_db_query($wpenv_vars, $purgeQuery);
    }
}