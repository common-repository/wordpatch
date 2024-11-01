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

if(!function_exists('wordpatch_upload_key')) {
    function wordpatch_upload_key() {
        return 'wordpatch_upload_file';
    }
}

if(!function_exists('wordpatch_was_file_uploaded')) {
    function wordpatch_was_file_uploaded() {
        global $__wordpatch_files;

        $upload_key = wordpatch_upload_key();
        $no_file_error = defined('UPLOAD_ERR_NO_FILE') ? constant('UPLOAD_ERR_NO_FILE') : 4;

        if(!isset($__wordpatch_files) || !isset($__wordpatch_files[$upload_key]) ||
            (isset($__wordpatch_files[$upload_key]['error']) && $__wordpatch_files[$upload_key]['error'] === $no_file_error)) {
            return false;
        }

        return true;
    }
}

if(!function_exists('wordpatch_did_file_upload_succeed')) {
    function wordpatch_did_file_upload_succeed() {
        global $__wordpatch_files;

        $upload_key = wordpatch_upload_key();
        if(!isset($__wordpatch_files) || !isset($__wordpatch_files[$upload_key]) ||
            !isset($__wordpatch_files[$upload_key]['tmp_name']) || !isset($__wordpatch_files[$upload_key]['error']) ||
            $__wordpatch_files[$upload_key]['error'] !== 0 || !file_exists($__wordpatch_files[$upload_key]['tmp_name'])) {
            return false;
        }

        return true;
    }
}

if(!function_exists('wordpatch_file_upload_tmp_name')) {
    function wordpatch_file_upload_tmp_name() {
        global $__wordpatch_files;

        $upload_key = wordpatch_upload_key();
        if(!isset($__wordpatch_files) || !isset($__wordpatch_files[$upload_key]) ||
            !isset($__wordpatch_files[$upload_key]['tmp_name'])) {
            return null;
        }

        return $__wordpatch_files[$upload_key]['tmp_name'];
    }
}

if(!function_exists('wordpatch_file_upload_read_binary')) {
    function wordpatch_file_upload_read_binary() {
        $upload_tmp_name = wordpatch_file_upload_tmp_name();
        $upload_handle = fopen($upload_tmp_name, "rb");
        wordpatch_mbstring_binary_safe_encoding();
        $temp_filesize = filesize($upload_tmp_name);
        $upload_contents = fread($upload_handle, $temp_filesize);
        wordpatch_reset_mbstring_encoding();
        fclose($upload_handle);

        return $upload_contents;
    }
}

if(!function_exists('wordpatch_file_upload_write_to_location')) {
    function wordpatch_file_upload_write_to_location($wpenv_vars, $upload_bucket, $upload_location, $upload_data) {
        if(!wordpatch_is_file_upload_bucket_valid($upload_bucket)) {
            return WORDPATCH_INVALID_UPLOAD_BUCKET;
        }

        $write_error = null;

        // Make sure the uploads dir exists
        $our_uploads_dir = wordpatch_our_uploads_dir($wpenv_vars);
        $content_dir = wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_content_dir'));
        $wordpatch_uploads_dir = $content_dir . "$our_uploads_dir/";
        $bucket_dir = $wordpatch_uploads_dir . "$upload_bucket/";

        if(!file_exists($wordpatch_uploads_dir) && !wordpatch_filesystem_mkdir($wpenv_vars, $our_uploads_dir, 'content_dir')) {
            $write_error = WORDPATCH_FILESYSTEM_FAILED_WRITE_DIR;
        } else {
            // Next create the bucket dir
            $bucket_rel = "$our_uploads_dir/$upload_bucket";
            if(!file_exists($bucket_dir) && !wordpatch_filesystem_mkdir($wpenv_vars, $bucket_rel, 'content_dir')) {
                $write_error = WORDPATCH_FILESYSTEM_FAILED_WRITE_DIR;
            } else {
                // Cool, now break down the location real quick
                $upload_location_info = wordpatch_file_upload_analyze_location($upload_location);
                $dir_count = count($upload_location_info['dirs']);

                for ($dir_index = 0; $dir_index < $dir_count; $dir_index++) {
                    $dir_path_rel = wordpatch_trailingslashit("$bucket_rel/" .
                        implode('/', array_slice($upload_location_info['dirs'], 0, ($dir_index + 1))));
                    $dir_path = $content_dir . $dir_path_rel;

                    if (!file_exists($dir_path) && !wordpatch_filesystem_mkdir($wpenv_vars, $dir_path_rel, 'content_dir')) {
                        $write_error = WORDPATCH_FILESYSTEM_FAILED_WRITE_DIR;
                        break;
                    }
                }

                if (!$write_error) {
                    // Nice, now let's try to write our file there
                    $write_contents = "<?php\ndie();\n//" . base64_encode($upload_data);

                    $file_path_rel = wordpatch_trailingslashit("$our_uploads_dir/$upload_bucket/" . implode('/', $upload_location_info['dirs'])) .
                        $upload_location_info['guid'] . '.php';

                    if (!wordpatch_filesystem_put_contents($wpenv_vars, $file_path_rel, 'content_dir', $write_contents)) {
                        $write_error = WORDPATCH_FILESYSTEM_FAILED_WRITE;
                    }
                }
            }
        }

        return $write_error;
    }
}

if(!function_exists('wordpatch_file_upload_read_from_location')) {
    function wordpatch_file_upload_read_from_location($wpenv_vars, $upload_bucket, $upload_location, &$output_error) {
        if(!wordpatch_is_file_upload_bucket_valid($upload_bucket)) {
            if($output_error !== null) {
                $output_error = WORDPATCH_INVALID_UPLOAD_BUCKET;
            }

            return null;
        }

        $read_error = null;
        $upload_location_info = wordpatch_file_upload_analyze_location($upload_location);

        if(!$upload_location_info) {
            $read_error = WORDPATCH_INVALID_LOCATION;

            if($output_error !== null) {
                $output_error = $read_error;
            }

            return null;
        }

        $our_uploads_dir = wordpatch_our_uploads_dir($wpenv_vars);
        $content_dir = wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_content_dir'));

        // Let's try to read the file
        $file_path_rel = wordpatch_trailingslashit("$our_uploads_dir/$upload_bucket/" . implode('/', $upload_location_info['dirs'])) .
            $upload_location_info['guid'] . '.php';
        $file_path = $content_dir . $file_path_rel;

        if(!@file_exists($file_path)) {
            $read_error = WORDPATCH_FILE_MISSING;

            if($output_error !== null) {
                $output_error = $read_error;
            }

            return null;
        }

        $file_contents = @file_get_contents($file_path);

        if($file_contents === false) {
            $read_error = WORDPATCH_FILESYSTEM_FAILED_READ;

            if($output_error !== null) {
                $output_error = $read_error;
            }

            return null;
        }

        $output_error = null;

        // 15 = length of magic function header
        return base64_decode(substr($file_contents, 15));
    }
}

if(!function_exists('wordpatch_file_upload_delete_location')) {
    function wordpatch_file_upload_delete_location($wpenv_vars, $upload_bucket, $upload_location) {
        if(!wordpatch_is_file_upload_bucket_valid($upload_bucket)) {
            return WORDPATCH_INVALID_UPLOAD_BUCKET;
        }

        $delete_error = null;

        // Make sure the uploads dir exists
        $our_uploads_dir = wordpatch_our_uploads_dir($wpenv_vars);
        $content_dir = wordpatch_trailingslashit(wordpatch_env_get($wpenv_vars, 'wp_content_dir'));
        $wordpatch_uploads_dir = $content_dir . "$our_uploads_dir/";
        $bucket_dir = $wordpatch_uploads_dir . "$upload_bucket/";

        if(!file_exists($wordpatch_uploads_dir) && !wordpatch_filesystem_mkdir($wpenv_vars, $our_uploads_dir, 'content_dir')) {
            $delete_error = WORDPATCH_FILESYSTEM_FAILED_WRITE_DIR;
        } else {
            // Next create the bucket dir
            $bucket_rel = "$our_uploads_dir/$upload_bucket";
            if(!file_exists($bucket_dir) && !wordpatch_filesystem_mkdir($wpenv_vars, $bucket_rel, 'content_dir')) {
                $delete_error = WORDPATCH_FILESYSTEM_FAILED_WRITE_DIR;
            } else {
                // Cool, now break down the location real quick
                $upload_location_info = wordpatch_file_upload_analyze_location($upload_location);
                $dir_count = count($upload_location_info['dirs']);

                // Let's try to delete the file
                $file_path_rel = wordpatch_trailingslashit("$bucket_rel/" . implode('/', $upload_location_info['dirs'])) .
                    $upload_location_info['guid'] . '.php';
                $file_path = $content_dir . $file_path_rel;

                if (file_exists($file_path) && !wordpatch_filesystem_delete($wpenv_vars, $file_path_rel, 'content_dir', 'f')) {
                    $delete_error = WORDPATCH_FILESYSTEM_FAILED_DELETE;
                }
            }
        }

        // Return errors (if any)
        return $delete_error;
    }
}

if(!function_exists('wordpatch_file_upload_analyze_location')) {
    function wordpatch_file_upload_analyze_location($upload_location) {
        if(strlen($upload_location) <= 32) {
            return null;
        }

        $guid_part = substr($upload_location, strlen($upload_location) - 32);
        $dir_parts = substr($upload_location, 0, strlen($upload_location) - 32);
        $dir_parts_len = strlen($dir_parts);

        $dir_pieces = array();
        for($dir_index = 0; $dir_index < $dir_parts_len; $dir_index += 2) {
            $dir_pieces[] = substr($dir_parts, $dir_index, 2);
        }

        return array(
            'guid' => $guid_part,
            'dirs' => $dir_pieces
        );
    }
}

if(!function_exists('wordpatch_file_upload_generate_location__rand_dir')) {
    function wordpatch_file_upload_generate_location__rand_dir() {
        include_once('phpseclib/wordpatch.php');
        include_once(WORDPATCH_PHPSECLIB_DIR . 'Crypt/Wordpatch_Random.php');

        $data = wordpatch_crypt_random_string(1);
        return strtolower(bin2hex($data));
    }
}

if(!function_exists('wordpatch_file_upload_generate_location__rand_name')) {
    function wordpatch_file_upload_generate_location__rand_name() {
        include_once('phpseclib/wordpatch.php');
        include_once(WORDPATCH_PHPSECLIB_DIR . 'Crypt/Wordpatch_Random.php');

        $data = wordpatch_crypt_random_string(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return strtolower(bin2hex($data));
    }
}

if(!function_exists('wordpatch_file_upload_generate_location')) {
    function wordpatch_file_upload_generate_location() {
        return wordpatch_file_upload_generate_location__rand_dir() . wordpatch_file_upload_generate_location__rand_dir() .
            wordpatch_file_upload_generate_location__rand_name();
    }
}

if(!function_exists('wordpatch_our_uploads_dir')) {
    function wordpatch_our_uploads_dir($wpenv_vars) {
        return 'wordpatchuploads';
    }
}

if(!function_exists('wordpatch_is_file_upload_bucket_valid')) {
    function wordpatch_is_file_upload_bucket_valid($upload_bucket) {
        if($upload_bucket === wordpatch_file_upload_bucket_patches() || $upload_bucket === wordpatch_file_upload_bucket_rollbacks() ||
            $upload_bucket === wordpatch_file_upload_bucket_secrets() || $upload_bucket === wordpatch_file_upload_bucket_edits()) {
            return true;
        }

        return false;
    }
}

if(!function_exists('wordpatch_file_upload_bucket_secrets')) {
    function wordpatch_file_upload_bucket_secrets() {
        return 'secrets';
    }
}

if(!function_exists('wordpatch_file_upload_bucket_patches')) {
    function wordpatch_file_upload_bucket_patches() {
        return 'patches';
    }
}

if(!function_exists('wordpatch_file_upload_bucket_edits')) {
    function wordpatch_file_upload_bucket_edits() {
        return 'edits';
    }
}

if(!function_exists('wordpatch_file_upload_bucket_rollbacks')) {
    function wordpatch_file_upload_bucket_rollbacks() {
        return 'rollbacks';
    }
}