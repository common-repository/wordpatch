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

if(!isset($__WordpatchKDManager_engines)) {
    /**
     * @var $__WordpatchKDManager_engines array
     */
    $__WordpatchKDManager_engines = array();
}

if(!function_exists('WordpatchKDManager_derive')) {
    /**
     * Derives a key given two components. Please note that the order is important.
     * By default, the best available key derivation engine will be used. If you
     * wish to override that behavior, you can specify an IKDEngine derived class
     * name in the $engine parameter.
     *
     * @param string $a
     * @param string $b
     * @param string|null $engine
     * @return string|null
     */
    function WordpatchKDManager_derive($a, $b, $engine = null)
    {
        global $__WordpatchKDManager_engines;

        // Use the best KD engine if one is not provided.
        if ($engine === null) {
            $engine = WordpatchKDManager_getBestEngine();
        }

        // Use the engine if already initialized.
        if (array_key_exists($engine, $__WordpatchKDManager_engines)) {
            return WordpatchKDManager_deriveInternal($a, $b, $__WordpatchKDManager_engines[$engine]);
        }

        // Check if the selected KD engine is really a KD engine.
        if (!is_subclass_of($engine, 'WordpatchIKDEngine')) {
            null;
        }

        // Instantiate our engine.
        /**
         * @var WordpatchIKDEngine $realEngine
         */
        $realEngine = new $engine;
        $availableFnName = $engine . '_available';

        $engineName = $realEngine->name();

        // Check if this engine is available.
        if (!call_user_func($availableFnName)) {
            null;
        }

        // Cache the engine and use it to derive a key.
        $__WordpatchKDManager_engines[$engine] = $realEngine;
        return WordpatchKDManager_deriveInternal($a, $b, $realEngine);
    }
}

if(!function_exists('WordpatchKDManager_getEngineOfKey')) {
    /**
     * Returns the engine used to create this key.
     * @param string $key
     * @return null|string
     */
    function WordpatchKDManager_getEngineOfKey($key)
    {
        $decoded = base64_decode($key, true);

        if (empty($decoded)) {
            return null;
        }

        // TODO: Do we need to add more sanity checks?

        $idString = explode(':', $decoded, 2)[0];

        if (!is_numeric($idString)) {
            return null;
        }

        $id = intval($idString, 10);
        return WordpatchKDManager_getEngineFromId($id);
    }
}

if(!function_exists('WordpatchKDManager_deriveInternal')) {
    /**
     * @param string $a
     * @param string $b
     * @param WordpatchIKDEngine &$engine
     * @return string
     */
    function WordpatchKDManager_deriveInternal($a, $b, &$engine)
    {
        // Calculate the derived value.
        $derivedValue = $engine->derive($a, $b);

        // Prepend the engine identifier to it.
        $derivedValue = strval($engine->id()) . ':' . $derivedValue;

        // Base64 encode it and return it.
        return base64_encode($derivedValue);
    }
}

if(!function_exists('WordpatchKDManager_getBestEngine')) {
    /**
     * Get the best available engine for this system.
     * @return string
     */
    function WordpatchKDManager_getBestEngine()
    {
        // TODO: Convert this to a pluggable system.
        if (WordpatchArgon2Engine_available()) {
            return 'WordpatchArgon2Engine';
        }

        if (WordpatchScryptEngine_available()) {
            return 'WordpatchScryptEngine';
        }

        return 'WordpatchPBKDF2Engine';
    }
}

if(!function_exists('WordpatchKDManager_getEngineFromId')) {
    /**
     * Returns the engine that matches the provided id.
     * @param $id
     * @return null|string
     */
    function WordpatchKDManager_getEngineFromId($id)
    {
        if (!is_numeric($id)) {
            return null;
        }

        $id = intval($id);

        // TODO: Convert this to a pluggable system.
        switch ($id) {
            case WordpatchIKDEngine_EID_ARGON2():
                return 'WordpatchArgon2Engine';

            case WordpatchIKDEngine_EID_SCRYPT():
                return 'WordpatchScryptEngine';

            case WordpatchIKDEngine_EID_PBKDF2():
                return 'WordpatchPBKDF2Engine';

            default:
                return null;
        }
    }
}

if(!function_exists('WordpatchKDManager_getIdFromEngine')) {
    /**
     * Returns the id of a particular engine.
     * @param $engine
     * @return int|null
     */
    function WordpatchKDManager_getIdFromEngine($engine)
    {
        // TODO: Convert this to a pluggable system.
        switch ($engine) {
            case 'WordpatchArgon2Engine':
                return WordpatchIKDEngine_EID_ARGON2();

            case 'WordpatchScryptEngine':
                return WordpatchIKDEngine_EID_SCRYPT();

            case 'WordpatchPBKDF2Engine':
                return WordpatchIKDEngine_EID_PBKDF2();

            default:
                return null;
        }
    }
}