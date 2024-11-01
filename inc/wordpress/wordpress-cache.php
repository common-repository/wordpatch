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
 * This file is not allowed to depend on WordPress.
 * Made with love (and a little bit of insanity) in Virginia. ♥
 * Feel free to use parts of this file to emulate a modern WordPress installation.
 * MU (multisite) is not yet supported and various notes have been made for future support.
 */

if(!isset($__wordpatch_cache)) {
    $__wordpatch_cache = null;
}

if(!isset($__wordpatch_cache_suspend)) {
    $__wordpatch_cache_suspend = null;
}

if(!function_exists('wordpatch_suspend_cache_addition')) {
    function wordpatch_suspend_cache_addition($suspend = null) {
        global $__wordpatch_cache_suspend;

        if($__wordpatch_cache_suspend) {
            $__wordpatch_cache_suspend = false;
        }

        if(is_bool($suspend)) {
            $__wordpatch_cache_suspend = $suspend;
        }

        return $__wordpatch_cache_suspend;
    }
}

if(!function_exists('wordpatch_cache__exists')) {
    function wordpatch_cache__exists($key, $group) {
        global $__wordpatch_cache;
        return isset($__wordpatch_cache['cache'][$group]) &&
            (isset($__wordpatch_cache['cache'][$group][$key]) ||
                array_key_exists($key, $__wordpatch_cache['cache'][$group]));
    }
}

if(!function_exists('wordpatch_cache_set_')) {
    function wordpatch_cache_set_($key, $data, $group = 'default', $expire = 0) {
        global $__wordpatch_cache;

        if(empty($group)) {
            $group = 'default';
        }

        if($__wordpatch_cache['multisite'] && !isset($__wordpatch_cache['global_groups'][$group])) {
            $key = $__wordpatch_cache['blog_prefix'] . $key;
        }

        if(is_object($data)) {
            $data = clone $data;
        }

        $__wordpatch_cache['cache'][$group][$key] = $data;
        return true;
    }
}

if(!function_exists('wordpatch_cache_add_')) {
    function wordpatch_cache_add_($key, $data, $group = 'default', $expire = 0)
    {
        global $__wordpatch_cache;

        if(wordpatch_suspend_cache_addition()) {
            return false;
        }

        if(empty($group)) {
            $group = 'default';
        }

        $id = $key;
        if($__wordpatch_cache['multisite'] && ! isset($__wordpatch_cache['global_groups'][$group])) {
            $id = $__wordpatch_cache['blog_prefix'] . $key;
        }

        if(wordpatch_cache__exists($id, $group)) {
            return false;
        }

        return wordpatch_cache_set_($key, $data, $group, (int)$expire);
    }
}

if(!function_exists('wordpatch_cache_add')) {
    function wordpatch_cache_add($key, $data, $group = '', $expire = 0)
    {
        wordpatch_cache_init();
        return wordpatch_cache_add_($key, $data, $group, (int)$expire);
    }
}

if(!function_exists('wordpatch_cache_close')) {
    function wordpatch_cache_close()
    {
        return true;
    }
}

if(!function_exists('wordpatch_cache_decr_')) {
    function wordpatch_cache_decr_($key, $offset = 1, $group = 'default') {
        global $__wordpatch_cache;

        if(empty($group)) {
            $group = 'default';
        }

        if($__wordpatch_cache['multisite'] && !isset($__wordpatch_cache['global_groups'][$group])) {
            $key = $__wordpatch_cache['blog_prefix'] . $key;
        }

        if(!wordpatch_cache__exists($key, $group)) {
            return false;
        }

        if(!is_numeric($__wordpatch_cache['cache'][$group][$key])) {
            $__wordpatch_cache['cache'][$group][$key] = 0;
        }

        $offset = (int)$offset;

        $__wordpatch_cache['cache'][$group][$key] -= $offset;

        if($__wordpatch_cache['cache'][$group][$key] < 0) {
            $__wordpatch_cache['cache'][$group][$key] = 0;
        }

        return $__wordpatch_cache['cache'][$group][$key];
    }
}

if(!function_exists('wordpatch_cache_decr')) {
    function wordpatch_cache_decr($key, $offset = 1, $group = '')
    {
        wordpatch_cache_init();
        return wordpatch_cache_decr_($key, $offset, $group);
    }
}

if(!function_exists('wordpatch_cache_delete_')) {
    function wordpatch_cache_delete_($key, $group = 'default', $deprecated = false) {
        global $__wordpatch_cache;

        if(empty($group)) {
            $group = 'default';
        }

        if($__wordpatch_cache['multisite'] && ! isset($__wordpatch_cache['global_groups'][$group])) {
            $key = $__wordpatch_cache['blog_prefix'] . $key;
        }

        if(wordpatch_cache__exists($key, $group)) {
            return false;
        }

        unset($__wordpatch_cache['cache'][$group][$key]);
        return true;
    }
}

if(!function_exists('wordpatch_cache_delete')) {
    function wordpatch_cache_delete($key, $group = '')
    {
        wordpatch_cache_init();
        return wordpatch_cache_delete_($key, $group);
    }
}

if(!function_exists('wordpatch_cache_flush_')) {
    function wordpatch_cache_flush_() {
        global $__wordpatch_cache;
        $__wordpatch_cache['cache'] = array();

        return true;
    }
}

if(!function_exists('wordpatch_cache_flush')) {
    function wordpatch_cache_flush()
    {
        wordpatch_cache_init();
        return wordpatch_cache_flush_();
    }
}

if(!function_exists('wordpatch_cache_get_')) {
    function wordpatch_cache_get_($key, $group = 'default', $force = false, &$found = null) {
        global $__wordpatch_cache;

        if(empty($group)) {
            $group = 'default';
        }

        if($__wordpatch_cache['multisite'] && !isset($__wordpatch_cache['global_groups'][$group])) {
            $key = $__wordpatch_cache['blog_prefix'] . $key;
        }

        if(wordpatch_cache__exists($key, $group)) {
            $found = true;
            $__wordpatch_cache['cache_hits'] += 1;

            if(is_object($__wordpatch_cache['cache'][$group][$key])) {
                return clone $__wordpatch_cache['cache'][$group][$key];
            } else {
                return $__wordpatch_cache['cache'][$group][$key];
            }
        }

        $found = false;
        $__wordpatch_cache['cache_misses'] += 1;
        return false;
    }
}

if(!function_exists('wordpatch_cache_get')) {
    function wordpatch_cache_get($key, $group = '', $force = false, &$found = null)
    {
        wordpatch_cache_init();
        return wordpatch_cache_get_($key, $group, $force, $found);
    }
}

if(!function_exists('wordpatch_cache_incr_')) {
    function wordpatch_cache_incr_($key, $offset = 1, $group = 'default') {
        global $__wordpatch_cache;

        if(empty($group)) {
            $group = 'default';
        }

        if($__wordpatch_cache['multisite'] && !isset($__wordpatch_cache['global_groups'][$group])) {
            $key = $__wordpatch_cache['blog_prefix'] . $key;
        }

        if(!wordpatch_cache__exists($key, $group)) {
            return false;
        }

        if(!is_numeric($__wordpatch_cache['cache'][$group][$key])) {
            $__wordpatch_cache['cache'][$group][$key] = 0;
        }

        $offset = (int)$offset;

        $__wordpatch_cache['cache'][$group][$key] += $offset;

        if($__wordpatch_cache['cache'][$group][$key] < 0) {
            $__wordpatch_cache['cache'][$group][$key] = 0;
        }

        return $__wordpatch_cache['cache'][$group][$key];
    }
}

if(!function_exists('wordpatch_cache_incr')) {
    function wordpatch_cache_incr($key, $offset = 1, $group = '')
    {
        wordpatch_cache_init();
        return wordpatch_cache_incr_($key, $offset, $group);
    }
}

if(!function_exists('wordpatch_cache_init')) {
    function wordpatch_cache_init()
    {
        global $__wordpatch_cache;

        if(isset($__wordpatch_cache) && isset($__wordpatch_cache['cache'])) {
            return;
        }

        $__wordpatch_cache = array(
            'cache' => array(),
            'cache_hits' => 0,
            'cache_misses' => 0,
            'global_groups' => array(),
            'blog_prefix' => null,
            'multisite' => null
        );

        // @adaptednote: MU support
        $__wordpatch_cache['multisite'] = false;
        $__wordpatch_cache['blog_prefix'] = '';
    }
}

if(!function_exists('wordpatch_cache_replace_')) {
    function wordpatch_cache_replace_($key, $data, $group = 'default', $expire = 0) {
        global $__wordpatch_cache;

        if(empty($group)) {
            $group = 'default';
        }

        $id = $key;
        if($__wordpatch_cache['multisite'] && ! isset($__wordpatch_cache['global_groups'][$group])) {
            $id = $__wordpatch_cache['blog_prefix'] . $key;
        }

        if(!wordpatch_cache__exists($id, $group)) {
            return false;
        }

        return wordpatch_cache_set_($key, $data, $group, (int) $expire);
    }
}

if(!function_exists('wordpatch_cache_replace')) {
    function wordpatch_cache_replace($key, $data, $group = '', $expire = 0)
    {
        wordpatch_cache_init();
        return wordpatch_cache_replace_($key, $data, $group, (int)$expire);
    }
}

if(!function_exists('wordpatch_cache_set')) {
    function wordpatch_cache_set($key, $data, $group = '', $expire = 0)
    {
        wordpatch_cache_init();
        return wordpatch_cache_set_($key, $data, $group, (int)$expire);
    }
}

if(!function_exists('wordpatch_cache_switch_to_blog_')) {
    function wordpatch_cache_switch_to_blog_($blog_id) {
        global $__wordpatch_cache;
        // @adaptednote: MU support
        $__wordpatch_cache['blog_prefix'] = '';
    }
}

if(!function_exists('wordpatch_cache_switch_to_blog')) {
    function wordpatch_cache_switch_to_blog($blog_id)
    {
        wordpatch_cache_init();
        wordpatch_cache_switch_to_blog_($blog_id);
    }
}

if(!function_exists('wordpatch_cache_add_global_groups_')) {
    function wordpatch_cache_add_global_groups_($groups) {
        global $__wordpatch_cache;
        $groups = (array)$groups;

        $groups = array_fill_keys($groups, true);
        $__wordpatch_cache['global_groups'] = array_merge($__wordpatch_cache['global_groups'], $groups);
    }
}

if(!function_exists('wordpatch_cache_add_global_groups')) {
    function wordpatch_cache_add_global_groups($groups)
    {
        wordpatch_cache_init();
        wordpatch_cache_add_global_groups_($groups);
    }
}

if(!function_exists('wordpatch_cache_add_non_persistent_groups')) {
    function wordpatch_cache_add_non_persistent_groups($groups)
    {
        // Default cache doesn't persist so nothing to do here.
    }
}

if(!function_exists('wordpatch_cache_reset_')) {
    function wordpatch_cache_reset_() {
        global $__wordpatch_cache;
        // Clear out non-global caches since the blog ID has changed.
        foreach(array_keys($__wordpatch_cache['cache']) as $group) {
            if(!isset($__wordpatch_cache['global_groups'][$group])) {
                unset($__wordpatch_cache['cache'][$group]);
            }
        }
    }
}

if(!function_exists('wordpatch_cache_reset')) {
    function wordpatch_cache_reset()
    {
        wordpatch_cache_init();
        wordpatch_cache_reset_();
    }
}