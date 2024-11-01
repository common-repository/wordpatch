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
 * Defines each constant that is used throughout WordPatch. Constants are used for all of the following:
 * - Error codes
 * - Action names (also referred to as $where when used in the context of error handling/localization)
 * - Feature names
 * - Version numbers and other important values
 *
 * PS: All constants are pluggable values (similar to hardcoded, however can be adjusted by defining elsewhere prior to
 * inclusion of this file). It is highly recommended not to make changes to this file.
 */

/**
 * Error Constants
 */
if(!defined('WORDPATCH_INVALID_CREDENTIALS')) {
    define('WORDPATCH_INVALID_CREDENTIALS', 'INVALID_CREDENTIALS');
}

if(!defined('WORDPATCH_JOB_TITLE_REQUIRED')) {
    define('WORDPATCH_JOB_TITLE_REQUIRED', 'JOB_TITLE_REQUIRED');
}

if(!defined('WORDPATCH_INVALID_RESCUE_PATH')) {
    define('WORDPATCH_INVALID_RESCUE_PATH', 'INVALID_RESCUE_PATH');
}

if(!defined('WORDPATCH_INVALID_RESCUE_FORMAT')) {
    define('WORDPATCH_INVALID_RESCUE_FORMAT', 'INVALID_RESCUE_FORMAT');
}

if(!defined('WORDPATCH_INVALID_LOCATION')) {
    define('WORDPATCH_INVALID_LOCATION', 'INVALID_LOCATION');
}

if(!defined('WORDPATCH_FILE_MISSING')) {
    define('WORDPATCH_FILE_MISSING', 'FILE_MISSING');
}

if(!defined('WORDPATCH_INVALID_RESCUE_FILENAME')) {
    define('WORDPATCH_INVALID_RESCUE_FILENAME', 'INVALID_RESCUE_FILENAME');
}

if(!defined('WORDPATCH_INVALID_RESCUE_EXTENSION')) {
    define('WORDPATCH_INVALID_RESCUE_EXTENSION', 'INVALID_RESCUE_EXTENSION');
}

if(!defined('WORDPATCH_INVALID_RESCUE_DIRECTORY')) {
    define('WORDPATCH_INVALID_RESCUE_DIRECTORY', 'INVALID_RESCUE_DIRECTORY');
}

if(!defined('WORDPATCH_UNKNOWN_ERROR')) {
    define('WORDPATCH_UNKNOWN_ERROR', 'UNKNOWN_ERROR');
}

if(!defined('WORDPATCH_UNKNOWN_HTTP_ERROR')) {
    define('WORDPATCH_UNKNOWN_HTTP_ERROR', 'UNKNOWN_HTTP_ERROR');
}

if(!defined('WORDPATCH_UNAUTHORIZED_HTTP_ERROR')) {
    define('WORDPATCH_UNAUTHORIZED_HTTP_ERROR', 'UNAUTHORIZED_HTTP_ERROR');
}

if(!defined('WORDPATCH_PATCH_TITLE_REQUIRED')) {
    define('WORDPATCH_PATCH_TITLE_REQUIRED', 'PATCH_TITLE_REQUIRED');
}

if(!defined('WORDPATCH_PATCH_PATH_REQUIRED')) {
    define('WORDPATCH_PATCH_PATH_REQUIRED', 'PATCH_PATH_REQUIRED');
}

if(!defined('WORDPATCH_PATCH_CHANGES_REQUIRED')) {
    define('WORDPATCH_PATCH_CHANGES_REQUIRED', 'PATCH_CHANGES_REQUIRED');
}

if(!defined('WORDPATCH_PATCH_DATA_REQUIRED')) {
    define('WORDPATCH_PATCH_DATA_REQUIRED', 'PATCH_DATA_REQUIRED');
}

if(!defined('WORDPATCH_PATCH_FILE_REQUIRED')) {
    define('WORDPATCH_PATCH_FILE_REQUIRED', 'PATCH_FILE_REQUIRED');
}

if(!defined('WORDPATCH_PATCH_FAILED')) {
    define('WORDPATCH_PATCH_FAILED', 'PATCH_FAILED');
}

if(!defined('WORDPATCH_FILESYSTEM_FAILED_WRITE')) {
    define('WORDPATCH_FILESYSTEM_FAILED_WRITE', 'FILESYSTEM_FAILED_WRITE');
}

if(!defined('WORDPATCH_FILESYSTEM_FAILED_DELETE')) {
    define('WORDPATCH_FILESYSTEM_FAILED_DELETE', 'FILESYSTEM_FAILED_DELETE');
}

if(!defined('WORDPATCH_FILESYSTEM_FAILED_DELETE_DIR')) {
    define('WORDPATCH_FILESYSTEM_FAILED_DELETE_DIR', 'FILESYSTEM_FAILED_DELETE_DIR');
}

if(!defined('WORDPATCH_FILESYSTEM_FAILED_VERIFY_DIR')) {
    define('WORDPATCH_FILESYSTEM_FAILED_VERIFY_DIR', 'FILESYSTEM_FAILED_VERIFY_DIR');
}

if(!defined('WORDPATCH_FILESYSTEM_FAILED_WRITE_DIR')) {
    define('WORDPATCH_FILESYSTEM_FAILED_WRITE_DIR', 'FILESYSTEM_FAILED_WRITE_DIR');
}

if(!defined('WORDPATCH_FILESYSTEM_FAILED_READ')) {
    define('WORDPATCH_FILESYSTEM_FAILED_READ', 'FILESYSTEM_FAILED_READ');
}

if(!defined('WORDPATCH_FILESYSTEM_FILE_IS_DIR')) {
    define('WORDPATCH_FILESYSTEM_FILE_IS_DIR', 'FILESYSTEM_FILE_IS_DIR');
}

if(!defined('WORDPATCH_INVALID_FILESYSTEM_CREDENTIALS')) {
    define('WORDPATCH_INVALID_FILESYSTEM_CREDENTIALS', 'INVALID_FILESYSTEM_CREDENTIALS');
}

if(!defined('WORDPATCH_INVALID_DATABASE_CREDENTIALS')) {
    define('WORDPATCH_INVALID_DATABASE_CREDENTIALS', 'INVALID_DATABASE_CREDENTIALS');
}

if(!defined('WORDPATCH_WRONG_DATABASE_CREDENTIALS')) {
    define('WORDPATCH_WRONG_DATABASE_CREDENTIALS', 'WRONG_DATABASE_CREDENTIALS');
}

if(!defined('WORDPATCH_FS_PERMISSIONS_ERROR')) {
    define('WORDPATCH_FS_PERMISSIONS_ERROR', 'FS_PERMISSIONS_ERROR');
}

if(!defined('WORDPATCH_WRONG_FILESYSTEM_CREDENTIALS')) {
    define('WORDPATCH_WRONG_FILESYSTEM_CREDENTIALS', 'WRONG_FILESYSTEM_CREDENTIALS');
}

if(!defined('WORDPATCH_FTP_BASE_REQUIRED')) {
    define('WORDPATCH_FTP_BASE_REQUIRED', 'FTP_BASE_REQUIRED');
}

if(!defined('WORDPATCH_FTP_CONTENT_DIR_REQUIRED')) {
    define('WORDPATCH_FTP_CONTENT_DIR_REQUIRED', 'FTP_CONTENT_DIR_REQUIRED');
}

if(!defined('WORDPATCH_FTP_PLUGIN_DIR_REQUIRED')) {
    define('WORDPATCH_FTP_PLUGIN_DIR_REQUIRED', 'FTP_PLUGIN_DIR_REQUIRED');
}

if(!defined('WORDPATCH_JOB_WILL_RETRY')) {
    define('WORDPATCH_JOB_WILL_RETRY', 'JOB_WILL_RETRY');
}

if(!defined('WORDPATCH_REJECTS_IN_QUEUE')) {
    define('WORDPATCH_REJECTS_IN_QUEUE', 'REJECTS_IN_QUEUE');
}

if(!defined('WORDPATCH_INVALID_JOB')) {
    define('WORDPATCH_INVALID_JOB', 'INVALID_JOB');
}

if(!defined('WORDPATCH_JOB_NOT_ENABLED')) {
    define('WORDPATCH_JOB_NOT_ENABLED', 'JOB_NOT_ENABLED');
}

if(!defined('WORDPATCH_INVALID_PATCH')) {
    define('WORDPATCH_INVALID_PATCH', 'INVALID_PATCH');
}

if(!defined('WORDPATCH_INVALID_LOG')) {
    define('WORDPATCH_INVALID_LOG', 'INVALID_LOG');
}

if(!defined('WORDPATCH_INVALID_JUDGEMENT')) {
    define('WORDPATCH_INVALID_JUDGEMENT', 'INVALID_JUDGEMENT');
}

if(!defined('WORDPATCH_NOT_PENDING_JUDGEMENT')) {
    define('WORDPATCH_NOT_PENDING_JUDGEMENT', 'NOT_PENDING_JUDGEMENT');
}

if(!defined('WORDPATCH_UNKNOWN_DATABASE_ERROR')) {
    define('WORDPATCH_UNKNOWN_DATABASE_ERROR', 'UNKNOWN_DATABASE_ERROR');
}

if(!defined('WORDPATCH_INVALID_JOB_PATH')) {
    define('WORDPATCH_INVALID_JOB_PATH', 'INVALID_JOB_PATH');
}

if(!defined('WORDPATCH_WORK_THROTTLED')) {
    define('WORDPATCH_WORK_THROTTLED', 'WORK_THROTTLED');
}

if(!defined('WORDPATCH_API_UNKNOWN_ERROR')) {
    define('WORDPATCH_API_UNKNOWN_ERROR', 'API_UNKNOWN_ERROR');
}

if(!defined('WORDPATCH_INVALID_FS_METHOD')) {
    define('WORDPATCH_INVALID_FS_METHOD', 'INVALID_FS_METHOD');
}

if(!defined('WORDPATCH_INVALID_FS_TIMEOUT')) {
    define('WORDPATCH_INVALID_FS_TIMEOUT', 'INVALID_FS_TIMEOUT');
}

if(!defined('WORDPATCH_FS_CHMOD_FILE_REQUIRED')) {
    define('WORDPATCH_FS_CHMOD_FILE_REQUIRED', 'FS_CHMOD_FILE_REQUIRED');
}

if(!defined('WORDPATCH_FTP_SSHKEY_INDECISIVE')) {
    define('WORDPATCH_FTP_SSHKEY_INDECISIVE', 'FTP_SSHKEY_INDECISIVE');
}

if(!defined('WORDPATCH_FTP_PASS_REQUIRED')) {
    define('WORDPATCH_FTP_PASS_REQUIRED', 'FTP_PASS_REQUIRED');
}

if(!defined('WORDPATCH_FS_CHMOD_DIR_REQUIRED')) {
    define('WORDPATCH_FS_CHMOD_DIR_REQUIRED', 'FS_CHMOD_DIR_REQUIRED');
}

if(!defined('WORDPATCH_FTP_HOST_REQUIRED')) {
    define('WORDPATCH_FTP_HOST_REQUIRED', 'FTP_HOST_REQUIRED');
}

if(!defined('WORDPATCH_INVALID_ACTIVATION_KEY')) {
    define('WORDPATCH_INVALID_ACTIVATION_KEY', 'INVALID_ACTIVATION_KEY');
}

if(!defined('WORDPATCH_FTP_USER_REQUIRED')) {
    define('WORDPATCH_FTP_USER_REQUIRED', 'FTP_USER_REQUIRED');
}

if(!defined('WORDPATCH_DB_HOST_REQUIRED')) {
    define('WORDPATCH_DB_HOST_REQUIRED', 'DB_HOST_REQUIRED');
}

if(!defined('WORDPATCH_DB_USER_REQUIRED')) {
    define('WORDPATCH_DB_USER_REQUIRED', 'DB_USER_REQUIRED');
}

if(!defined('WORDPATCH_DB_NAME_REQUIRED')) {
    define('WORDPATCH_DB_NAME_REQUIRED', 'DB_NAME_REQUIRED');
}

if(!defined('WORDPATCH_DB_COLLATE_INVALID')) {
    define('WORDPATCH_DB_COLLATE_INVALID', 'DB_COLLATE_INVALID');
}

if(!defined('WORDPATCH_INVALID_MAILER')) {
    define('WORDPATCH_INVALID_MAILER', 'INVALID_MAILER');
}

if(!defined('WORDPATCH_INVALID_MAIL_FROM')) {
    define('WORDPATCH_INVALID_MAIL_FROM', 'INVALID_MAIL_FROM');
}

if(!defined('WORDPATCH_MAIL_FROM_REQUIRED')) {
    define('WORDPATCH_MAIL_FROM_REQUIRED', 'MAIL_FROM_REQUIRED');
}

if(!defined('WORDPATCH_INVALID_MAIL_TO')) {
    define('WORDPATCH_INVALID_MAIL_TO', 'INVALID_MAIL_TO');
}

if(!defined('WORDPATCH_MAIL_TO_REQUIRED')) {
    define('WORDPATCH_MAIL_TO_REQUIRED', 'MAIL_TO_REQUIRED');
}

if(!defined('WORDPATCH_SMTP_HOST_REQUIRED')) {
    define('WORDPATCH_SMTP_HOST_REQUIRED', 'SMTP_HOST_REQUIRED');
}

if(!defined('WORDPATCH_SMTP_PORT_REQUIRED')) {
    define('WORDPATCH_SMTP_PORT_REQUIRED', 'SMTP_PORT_REQUIRED');
}

if(!defined('WORDPATCH_SMTP_USER_REQUIRED')) {
    define('WORDPATCH_SMTP_USER_REQUIRED', 'SMTP_USER_REQUIRED');
}

if(!defined('WORDPATCH_SMTP_PASS_REQUIRED')) {
    define('WORDPATCH_SMTP_PASS_REQUIRED', 'SMTP_PASS_REQUIRED');
}

if(!defined('WORDPATCH_INVALID_SMTP_SSL')) {
    define('WORDPATCH_INVALID_SMTP_SSL', 'INVALID_SMTP_SSL');
}

if(!defined('WORDPATCH_INVALID_SMTP_AUTH')) {
    define('WORDPATCH_INVALID_SMTP_AUTH', 'INVALID_SMTP_AUTH');
}

if(!defined('WORDPATCH_MUST_CONFIGURE_DATABASE')) {
    define('WORDPATCH_MUST_CONFIGURE_DATABASE', 'MUST_CONFIGURE_DATABASE');
}

if(!defined('WORDPATCH_MUST_CONFIGURE_FILESYSTEM')) {
    define('WORDPATCH_MUST_CONFIGURE_FILESYSTEM', 'MUST_CONFIGURE_FILESYSTEM');
}

if(!defined('WORDPATCH_MUST_CONFIGURE_RESCUE')) {
    define('WORDPATCH_MUST_CONFIGURE_RESCUE', 'MUST_CONFIGURE_RESCUE');
}

if(!defined('WORDPATCH_MUST_ACTIVATE_LICENSE')) {
    define('WORDPATCH_MUST_ACTIVATE_LICENSE', 'MUST_ACTIVATE_LICENSE');
}

if(!defined('WORDPATCH_DB_CHARSET_INVALID')) {
    define('WORDPATCH_DB_CHARSET_INVALID', 'DB_CHARSET_INVALID');
}

if(!defined('WORDPATCH_INVALID_UPLOAD_BUCKET')) {
    define('WORDPATCH_INVALID_UPLOAD_BUCKET', 'INVALID_UPLOAD_BUCKET');
}

if(!defined('WORDPATCH_NO_LICENSE')) {
    define('WORDPATCH_NO_LICENSE', 'NO_LICENSE');
}

if(!defined('WORDPATCH_BAD_LICENSE')) {
    define('WORDPATCH_BAD_LICENSE', 'BAD_LICENSE');
}

if(!defined('WORDPATCH_INACTIVE_LICENSE')) {
    define('WORDPATCH_INACTIVE_LICENSE', 'INACTIVE_LICENSE');
}

if(!defined('WORDPATCH_PRODUCT_MISMATCH')) {
    define('WORDPATCH_PRODUCT_MISMATCH', 'PRODUCT_MISMATCH');
}

if(!defined('WORDPATCH_OUT_OF_SEATS')) {
    define('WORDPATCH_OUT_OF_SEATS', 'OUT_OF_SEATS');
}

if(!defined('WORDPATCH_DOMAIN_ALREADY_ACTIVE')) {
    define('WORDPATCH_DOMAIN_ALREADY_ACTIVE', 'DOMAIN_ALREADY_ACTIVE');
}

if(!defined('WORDPATCH_LICENSE_SEEMS_ACTIVE')) {
    define('WORDPATCH_LICENSE_SEEMS_ACTIVE', 'LICENSE_SEEMS_ACTIVE');
}

if(!defined('WORDPATCH_CRYPTO_ENGINE_ERROR')) {
    define('WORDPATCH_CRYPTO_ENGINE_ERROR', 'CRYPTO_ENGINE_ERROR');
}

if(!defined('WORDPATCH_PURGE_LICENSE_FAILED')) {
    define('WORDPATCH_PURGE_LICENSE_FAILED', 'PURGE_LICENSE_FAILED');
}

if(!defined('WORDPATCH_MAILER_FAILED')) {
    define('WORDPATCH_MAILER_FAILED', 'MAILER_FAILED');
}

/**
 * Action names (aka where when in the context of error handling/localization)
 */
if(!defined('WORDPATCH_WHERE_LOGIN')) {
    define('WORDPATCH_WHERE_LOGIN', 'login');
}

if(!defined('WORDPATCH_WHERE_REDIRECT')) {
    define('WORDPATCH_WHERE_REDIRECT', 'redirect');
}

if(!defined('WORDPATCH_WHERE_LOGOUT')) {
    define('WORDPATCH_WHERE_LOGOUT', 'logout');
}

if(!defined('WORDPATCH_WHERE_PROGRESS')) {
    define('WORDPATCH_WHERE_PROGRESS', 'progress');
}

if(!defined('WORDPATCH_WHERE_HEALTH')) {
    define('WORDPATCH_WHERE_HEALTH', 'health');
}

if(!defined('WORDPATCH_WHERE_MAILBOX')) {
    define('WORDPATCH_WHERE_MAILBOX', 'mailbox');
}

if(!defined('WORDPATCH_WHERE_ACCEPTJOB')) {
    define('WORDPATCH_WHERE_ACCEPTJOB', 'acceptjob');
}

if(!defined('WORDPATCH_WHERE_REJECTJOB')) {
    define('WORDPATCH_WHERE_REJECTJOB', 'rejectjob');
}

if(!defined('WORDPATCH_WHERE_LOGS')) {
    define('WORDPATCH_WHERE_LOGS', 'logs');
}

if(!defined('WORDPATCH_WHERE_LOGDETAIL')) {
    define('WORDPATCH_WHERE_LOGDETAIL', 'logdetail');
}

if(!defined('WORDPATCH_WHERE_JOBDETAIL')) {
    define('WORDPATCH_WHERE_JOBDETAIL', 'jobdetail');
}

if(!defined('WORDPATCH_WHERE_READMAIL')) {
    define('WORDPATCH_WHERE_READMAIL', 'readmail');
}

if(!defined('WORDPATCH_WHERE_TESTMAIL')) {
    define('WORDPATCH_WHERE_TESTMAIL', 'testmail');
}

if(!defined('WORDPATCH_WHERE_LOADJOBFILE')) {
    define('WORDPATCH_WHERE_LOADJOBFILE', 'loadjobfile');
}

if(!defined('WORDPATCH_WHERE_JUDGE')) {
    define('WORDPATCH_WHERE_JUDGE', 'judge');
}

if(!defined('WORDPATCH_WHERE_WORK')) {
    define('WORDPATCH_WHERE_WORK', 'work');
}

if(!defined('WORDPATCH_WHERE_SETTINGS')) {
    define('WORDPATCH_WHERE_SETTINGS', 'settings');
}

if(!defined('WORDPATCH_WHERE_TRASHJOB')) {
    define('WORDPATCH_WHERE_TRASHJOB', 'trashjob');
}

if(!defined('WORDPATCH_WHERE_RESTOREJOB')) {
    define('WORDPATCH_WHERE_RESTOREJOB', 'restorejob');
}

if(!defined('WORDPATCH_WHERE_RUNJOB')) {
    define('WORDPATCH_WHERE_RUNJOB', 'runjob');
}

if(!defined('WORDPATCH_WHERE_CONFIGFS')) {
    define('WORDPATCH_WHERE_CONFIGFS', 'configfs');
}

if(!defined('WORDPATCH_WHERE_CONFIGLICENSE')) {
    define('WORDPATCH_WHERE_CONFIGLICENSE', 'configlicense');
}

if(!defined('WORDPATCH_WHERE_NEWPATCH')) {
    define('WORDPATCH_WHERE_NEWPATCH', 'newpatch');
}

if(!defined('WORDPATCH_WHERE_EDITPATCH')) {
    define('WORDPATCH_WHERE_EDITPATCH', 'editpatch');
}

if(!defined('WORDPATCH_WHERE_ERASEPATCH')) {
    define('WORDPATCH_WHERE_ERASEPATCH', 'erasepatch');
}

if(!defined('WORDPATCH_WHERE_DELETEPATCH')) {
    define('WORDPATCH_WHERE_DELETEPATCH', 'deletepatch');
}

if(!defined('WORDPATCH_WHERE_DELETEJOB')) {
    define('WORDPATCH_WHERE_DELETEJOB', 'deletejob');
}

if(!defined('WORDPATCH_WHERE_NEWJOB')) {
    define('WORDPATCH_WHERE_NEWJOB', 'newjob');
}

if(!defined('WORDPATCH_WHERE_EDITJOB')) {
    define('WORDPATCH_WHERE_EDITJOB', 'editjob');
}

if(!defined('WORDPATCH_WHERE_CONFIGMAIL')) {
    define('WORDPATCH_WHERE_CONFIGMAIL', 'configmail');
}

if(!defined('WORDPATCH_WHERE_CONFIGDB')) {
    define('WORDPATCH_WHERE_CONFIGDB', 'configdb');
}

if(!defined('WORDPATCH_WHERE_CONFIGRESCUE')) {
    define('WORDPATCH_WHERE_CONFIGRESCUE', 'configrescue');
}

if(!defined('WORDPATCH_WHERE_DASHBOARD')) {
    define('WORDPATCH_WHERE_DASHBOARD', 'dashboard');
}

if(!defined('WORDPATCH_WHERE_JOBS')) {
    define('WORDPATCH_WHERE_JOBS', 'jobs');
}

/**
 * Feature names
 */
if(!defined('WORDPATCH_FEATURE_PREFACE')) {
    define('WORDPATCH_FEATURE_PREFACE', 'preface');
}

if(!defined('WORDPATCH_FEATURE_REQUIRES_AUTH')) {
    define('WORDPATCH_FEATURE_REQUIRES_AUTH', 'requires_auth');
}

if(!defined('WORDPATCH_FEATURE_PROCESS')) {
    define('WORDPATCH_FEATURE_PROCESS', 'process');
}

if(!defined('WORDPATCH_FEATURE_RENDER')) {
    define('WORDPATCH_FEATURE_RENDER', 'render');
}

/**
 * Version numbers and other important values
 */
if(!defined('WORDPATCH_VERSION')) {
    define('WORDPATCH_VERSION', '1.1.7');
}

if(!defined('WORDPATCH_STALE_JOB_TIMER')) {
    define('WORDPATCH_STALE_JOB_TIMER', 300);
}

if(!defined('WORDPATCH_CLIENT_HEALTH_INTERVAL')) {
    define('WORDPATCH_CLIENT_HEALTH_INTERVAL', 10000);
}

if(!defined('WORDPATCH_PROGRESS_API_AMOUNT')) {
    define('WORDPATCH_PROGRESS_API_AMOUNT', 13);
}

if(!defined('WORDPATCH_PROGRESS_API_NOTE')) {
    define('WORDPATCH_PROGRESS_API_NOTE', 'got response from API');
}

if(!defined('WORDPATCH_CLIENT_PROGRESS_INTERVAL')) {
    define('WORDPATCH_CLIENT_PROGRESS_INTERVAL', 5000);
}

if(!defined('WORDPATCH_RCE_SALT_KEY')) {
    define('WORDPATCH_RCE_SALT_KEY', 'wordpatch_rce_salt');
}

if(!defined('WORDPATCH_RCE_PASS_KEY')) {
    define('WORDPATCH_RCE_PASS_KEY', 'wordpatch_rce_pass');
}

if(!defined('WORDPATCH_WORK_THROTTLE_KEY')) {
    define('WORDPATCH_WORK_THROTTLE_KEY', 'wordpatch_work_throttle');
}

if(!defined('WORDPATCH_WORK_THROTTLE_SECONDS')) {
    define('WORDPATCH_WORK_THROTTLE_SECONDS', 1); // TODO: Change this after testing
}

if(!defined('WORDPATCH_MAILBOX_PAGE_LIMIT')) {
    define('WORDPATCH_MAILBOX_PAGE_LIMIT', 20);
}

if(!defined('WORDPATCH_LOGBOX_PAGE_LIMIT')) {
    define('WORDPATCH_LOGBOX_PAGE_LIMIT', 10);
}

if(!defined('WORDPATCH_MAIL_TEMPLATE_FAILED_JOB')) {
    define('WORDPATCH_MAIL_TEMPLATE_FAILED_JOB', 'failed_job');
}

if(!defined('WORDPATCH_MAIL_TEMPLATE_COMPLETED_JOB')) {
    define('WORDPATCH_MAIL_TEMPLATE_COMPLETED_JOB', 'completed_job');
}

if(!defined('WORDPATCH_MAIL_TEMPLATE_TEST')) {
    define('WORDPATCH_MAIL_TEMPLATE_TEST', 'test');
}

if(!defined('WORDPATCH_FS_ISSUE_KEY')) {
    define('WORDPATCH_FS_ISSUE_KEY', 'wordpatch_fs_issue');
}

if(!defined('WORDPATCH_JUDGEMENT_ACCEPTED')) {
    define('WORDPATCH_JUDGEMENT_ACCEPTED', 'accepted');
}

if(!defined('WORDPATCH_JUDGEMENT_REJECTED')) {
    define('WORDPATCH_JUDGEMENT_REJECTED', 'rejected');
}

if(!defined('WORDPATCH_INIT_REASON_MANUAL')) {
    define('WORDPATCH_INIT_REASON_MANUAL', 'manual');
}

if(!defined('WORDPATCH_INIT_REASON_UPDATE')) {
    define('WORDPATCH_INIT_REASON_UPDATE', 'update');
}

if(!defined('WORDPATCH_INIT_SUBJECT_TYPE_CORE')) {
    define('WORDPATCH_INIT_SUBJECT_TYPE_CORE', 'core');
}

if(!defined('WORDPATCH_INIT_SUBJECT_TYPE_PLUGIN')) {
    define('WORDPATCH_INIT_SUBJECT_TYPE_PLUGIN', 'plugin');
}

if(!defined('WORDPATCH_INIT_SUBJECT_TYPE_THEME')) {
    define('WORDPATCH_INIT_SUBJECT_TYPE_THEME', 'theme');
}

if(!defined('WORDPATCH_CHANGE_TYPE_DELETE')) {
    define('WORDPATCH_CHANGE_TYPE_DELETE', 'd');
}

if(!defined('WORDPATCH_CHANGE_TYPE_EDIT')) {
    define('WORDPATCH_CHANGE_TYPE_EDIT', 'e');
}

if(!defined('WORDPATCH_CHANGE_TYPE_CREATE')) {
    define('WORDPATCH_CHANGE_TYPE_CREATE', 'c');
}

if(!defined('WORDPATCH_CLEANUP_STATUS_NONE')) {
    define('WORDPATCH_CLEANUP_STATUS_NONE', 0);
}

if(!defined('WORDPATCH_CLEANUP_STATUS_PENDING')) {
    define('WORDPATCH_CLEANUP_STATUS_PENDING', 1);
}

if(!defined('WORDPATCH_CLEANUP_STATUS_ACTIVE')) {
    define('WORDPATCH_CLEANUP_STATUS_ACTIVE', 2);
}

if(!defined('WORDPATCH_ROLLBACK_ACTION_WRITE_FILE')) {
    define('WORDPATCH_ROLLBACK_ACTION_WRITE_FILE', 'write_file');
}

if(!defined('WORDPATCH_ROLLBACK_ACTION_DELETE_FILE')) {
    define('WORDPATCH_ROLLBACK_ACTION_DELETE_FILE', 'delete_file');
}

if(!defined('WORDPATCH_ROLLBACK_ACTION_DELETE_EMPTY_DIR')) {
    define('WORDPATCH_ROLLBACK_ACTION_DELETE_EMPTY_DIR', 'delete_empty_dir');
}

if(!defined('WORDPATCH_SUBMIT_TYPE_NEXT')) {
    define('WORDPATCH_SUBMIT_TYPE_NEXT', 'next');
}

if(!defined('WORDPATCH_SUBMIT_TYPE_PREVIOUS')) {
    define('WORDPATCH_SUBMIT_TYPE_PREVIOUS', 'previous');
}

if(!defined('WORDPATCH_YES')) {
    define('WORDPATCH_YES', 'yes');
}

if(!defined('WORDPATCH_NO')) {
    define('WORDPATCH_NO', 'no');
}

if(!defined('WORDPATCH_PROGRESS_STATE_RUNNING')) {
    define('WORDPATCH_PROGRESS_STATE_RUNNING', 'running');
}

if(!defined('WORDPATCH_PROGRESS_STATE_JUDGABLE')) {
    define('WORDPATCH_PROGRESS_STATE_JUDGABLE', 'judgable');
}

if(!defined('WORDPATCH_PROGRESS_STATE_PENDING')) {
    define('WORDPATCH_PROGRESS_STATE_PENDING', 'pending');
}

if(!defined('WORDPATCH_PROGRESS_STATE_FAILED')) {
    define('WORDPATCH_PROGRESS_STATE_FAILED', 'failed');
}

if(!defined('WORDPATCH_PROGRESS_STATE_SUCCESS')) {
    define('WORDPATCH_PROGRESS_STATE_SUCCESS', 'success');
}

if(!defined('WORDPATCH_PROGRESS_STATE_IDLE')) {
    define('WORDPATCH_PROGRESS_STATE_IDLE', 'idle');
}

if(!defined('WORDPATCH_RECENT_JOBS_LIMIT')) {
    define('WORDPATCH_RECENT_JOBS_LIMIT', 3);
}

if(!defined('WORDPATCH_PUBLIC_KEY')) {
    define('WORDPATCH_PUBLIC_KEY', base64_decode("LS0tLS1CRUdJTiBSU0EgUFVCTElDIEtFWS0tLS0tDQpNSUlCQ2dLQ0FRRUErRjM1" .
        "b2pVR2xEaTE1RENLMVQ5MDdNMEVtMGl1b2hNUytYb21XRGpWSkp2eGlTdCtaZmU0DQpINXJVYVQ2VW9ncjFRdkoyY3ZGSlRMbUVmSDVkblRPcXN" .
        "mc09KcHNzcDZpUklmZlBXOXI0SlJ3VlZsZXpUbmVEDQpxUlloWjFCenR2UnZKSXk2Vk9WclV0ZTNZZDcwcWFWWlZpYnNtMkU0dGxQUWZranI4Uk" .
        "hPdFBVVkV1QWxQUTBDDQp0K0drdm80SmFLVXRtREU5cTlSWVdVQWdnYUFmMU9Pclp3VEFoNFpVdWFBVDR5UW42MGdFWm83MlZnN1JaU2FIDQpNQ" .
        "nVUVDd1MGNKVm5Ka0Rwd04rZkwvYkt0M2FXSmJiRzBaUDh1OU9VTmRXSWVucHFpMlViZEtJN0tnVEswMjFrDQpibkkzOG92NHcvREJvQUw5TXBh" .
        "Z0VMV3pFYlQyWGQ1ZFFRSURBUUFCDQotLS0tLUVORCBSU0EgUFVCTElDIEtFWS0tLS0t"));
}

if(!defined('WORDPATCH_PRODUCT_KEY')) {
    define('WORDPATCH_PRODUCT_KEY', 'wordpatch');
}

if(!defined('WORDPATCH_API_URL')) {
    define('WORDPATCH_API_URL', 'https://api.jointbyte.com/');
}

if(!defined('WORDPATCH_PATH')) {
    /**
     * This constant is just a quick way to determine the WordPatch directory.
     */
    define('WORDPATCH_PATH', dirname(dirname(__FILE__)));
}