<?php

/**
 * Accounts
 *
 * This module provides API to work with eTraxis accounts.
 * See also {@link http://www.etraxis.org/docs-schema.php#tbl_accounts tbl_accounts} database table.
 *
 * @package DBO
 * @subpackage Accounts
 */

//--------------------------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system.
//  Copyright (C) 2005-2010 by Artem Rodygin
//
//  This program is free software; you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation; either version 2 of the License, or
//  (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License along
//  with this program; if not, write to the Free Software Foundation, Inc.,
//  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
//
//--------------------------------------------------------------------------------------------------
//  Author                  Date            Description of modifications
//--------------------------------------------------------------------------------------------------
//  Artem Rodygin           2005-02-13      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-07-24      new-009: Records filter.
//  Artem Rodygin           2005-08-03      new-017: Email notifications filter.
//  Artem Rodygin           2005-08-15      new-003: Authentication with Active Directory.
//  Artem Rodygin           2005-08-18      new-030: UI language should be set for each user separately.
//  Artem Rodygin           2005-08-18      new-035: Customizable list size.
//  Artem Rodygin           2005-08-22      bug-043: Removable account will not be removed in some cases.
//  Artem Rodygin           2005-08-23      new-053: All the calls of DAL API functions should be moved to DBO API.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-09-02      bug-083: Allowed length of usernames and passwords should be increased up to 104 characters.
//  Artem Rodygin           2005-09-06      new-095: Newly created records should be displayed as unread.
//  Artem Rodygin           2005-09-06      bug-097: When registered AD user logged in, all the records become read.
//  Artem Rodygin           2005-09-17      new-125: Email notifications advanced filter.
//  Artem Rodygin           2005-09-18      new-129: When new account is added, default subscription should be created.
//  Artem Rodygin           2005-09-27      new-141: Source code review.
//  Artem Rodygin           2005-09-27      new-141: Source code review.
//  Artem Rodygin           2005-09-27      bug-142: PHP Notice: Use of undefined constant DEFAULT_NOTIFY_FLAG
//  Artem Rodygin           2005-10-15      new-153: Users should *always* receieve notifications about records which are created by them or assigned on.
//  Artem Rodygin           2005-10-22      bug-165: PHP Warning: odbc_exec(): SQL error: DELETE statement conflicted with COLUMN REFERENCE constraint.
//  Artem Rodygin           2005-11-13      bug-177: Multibyte string functions should be used instead of 'eregi' and 'split'.
//  Artem Rodygin           2006-01-24      new-204: Active Directory Support functionality (new-003) should be conditionally "compiled".
//  Artem Rodygin           2006-04-09      new-235: Records with new events should be marked as "unread".
//  Artem Rodygin           2006-05-07      new-251: Traceability logging review.
//  Artem Rodygin           2006-06-19      new-236: Single record subscription.
//  Artem Rodygin           2006-06-25      new-222: Email reminders.
//  Artem Rodygin           2006-07-14      new-206: User password should not be stored in client cookies.
//  Artem Rodygin           2006-08-07      bug-300: Cannot login with Active Directory credentials.
//  Artem Rodygin           2006-10-12      new-137: Custom queries.
//  Artem Rodygin           2006-10-17      new-361: Extended custom queries.
//  Artem Rodygin           2006-11-05      new-365: Filters sharing.
//  Artem Rodygin           2006-11-18      bug-389: Motorola LDAP server returns "Insufficient rights" error.
//  Artem Rodygin           2006-11-20      new-392: Local users should not be extended with '@eTraxis' when LDAP is disabled.
//  Artem Rodygin           2006-11-20      bug-393: PHP Notice: Undefined variable: is_ldapuser//  Artem Rodygin           2006-11-20      new-377: Custom views.
//  Artem Rodygin           2006-12-04      new-405: Default filter for new user.
//  Artem Rodygin           2006-12-06      bug-420: Chosen locale ID is not verified on 'Settings' page.
//  Artem Rodygin           2006-12-11      bug-440: Local users should not be extended with '@eTraxis' when being modified.
//  Artem Rodygin           2006-12-15      bug-409: User session expires too quick.
//  Artem Rodygin           2006-12-17      new-457: Default filter for new user.
//  Artem Rodygin           2006-12-20      bug-458: PHP Warning: odbc_exec(): SQL error: Incorrect syntax near ''.
//  Artem Rodygin           2006-12-20      new-460: Authentication token should contain client IP.
//  Artem Rodygin           2007-05-09      bug-524: Filters, created by default, are not properly set.
//  Artem Rodygin           2007-07-01      new-539: Existing records must not be marked as read for newly created user.
//  Artem Rodygin           2007-07-12      new-544: The 'ctype' library should not be used.
//  Artem Rodygin           2007-07-28      bug-553: Default filter for created records shows all records.
//  Artem Rodygin           2007-09-11      new-574: Filter should allow to specify several states.
//  Artem Rodygin           2007-09-12      new-576: [SF1788286] Export to CSV
//  Artem Rodygin           2007-10-29      new-564: Filters set.
//  Artem Rodygin           2007-11-07      new-571: View should show all records of current filters set.
//  Artem Rodygin           2007-11-27      new-633: The 'dbx' extension should not be used.
//  Dmitry Gorev            2007-12-10      new-414: Passwords expiration.
//  Dmitry Gorev            2007-12-18      bug-645: Account is locked for specified amount of seconds, not minutes.
//  Artem Rodygin           2008-03-14      new-683: Filters should be sharable with groups, not with accounts.
//  Artem Rodygin           2009-04-13      new-814: Password expiration should be turnable off.
//  Alexandr Permyakov      2009-05-29      new-821: Remove redundant call of 'ldap_finduser' from 'login_user'.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//  Artem Rodygin           2009-06-17      bug-825: Database gets empty strings instead of NULL values.
//  Artem Rodygin           2009-09-09      new-826: Native unicode support for Microsoft SQL Server.
//  Artem Rodygin           2010-01-26      bug-892: English grammar correction
//  Giacomo Giustozzi       2010-01-27      new-896: Export the whole project
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/filters.php');
/**#@-*/

//--------------------------------------------------------------------------------------------------
//  Definitions.
//--------------------------------------------------------------------------------------------------

/**
 * Local accounts suffix.
 */
define('ACCOUNT_SUFFIX', '@eTraxis');

/**#@+
 * Data restriction.
 */
define('MAX_ACCOUNT_USERNAME',    104);
define('MAX_ACCOUNT_PASSWORD',    104);
define('MAX_ACCOUNT_FULLNAME',    64);
define('MAX_ACCOUNT_EMAIL',       50);
define('MAX_ACCOUNT_DESCRIPTION', 100);
/**#@-*/

/**
 * Default notifications filter and flags.
 */
define('DEFAULT_NOTIFY_FLAG', 0x0000FFFF);

//--------------------------------------------------------------------------------------------------
//  Functions.
//--------------------------------------------------------------------------------------------------

/**
 * Checks whether account with specified locking info is still locked.
 *
 * @param int $locks_count Current value of '{@link http://www.etraxis.org/docs-schema.php#tbl_accounts_locks_count locks_count}' DBO field.
 * @param int $lock_time Current value of '{@link http://www.etraxis.org/docs-schema.php#tbl_accounts_lock_time lock_time}' DBO field.
 * @return bool TRUE if account is locked, FALSE otherwise.
 */
function is_account_locked ($locks_count, $lock_time)
{
    debug_write_log(DEBUG_TRACE, '[is_account_locked]');
    debug_write_log(DEBUG_DUMP,  '[is_account_locked] $locks_count = ' . $locks_count);
    debug_write_log(DEBUG_DUMP,  '[is_account_locked] $lock_time   = ' . $lock_time);

    return ($locks_count >= LOCKS_COUNT) && ($lock_time + LOCKS_TIMEOUT * 60 >= time());
}

/**
 * Increase number of failed attempts to log in ('{@link http://www.etraxis.org/docs-schema.php#tbl_accounts_locks_count locks_count}' DBO field) for specified account.
 * Account is locked when maximum allowed attempts to login is reached ({@link LOCKS_COUNT}).
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_account_id Account ID}.
 * @return int Always {@link NO_ERROR}.
 */
function account_lock ($id)
{
    debug_write_log(DEBUG_TRACE, '[account_lock]');
    debug_write_log(DEBUG_DUMP,  '[account_lock] $id = ' . $id);

    dal_query('accounts/lock.sql', $id, time());

    return NO_ERROR;
}

/**
 * Clears number of failed attempts to log in ('{@link http://www.etraxis.org/docs-schema.php#tbl_accounts_locks_count locks_count}' DBO field) for specified account.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_account_id Account ID}.
 * @return int Always {@link NO_ERROR}.
 */
function account_unlock ($id)
{
    debug_write_log(DEBUG_TRACE, '[account_unlock]');
    debug_write_log(DEBUG_DUMP,  '[account_unlock] $id = ' . $id);

    dal_query('accounts/unlock.sql', $id);

    return NO_ERROR;
}

/**
 * Looks for {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_account_id Account ID} and token,
 * which are stored in client cookies, and checks that token, stored in database for the same user, is equal.
 *
 * See also {@link account_set_token}.
 *
 * @return int {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_account_id Account ID} if valid equal token is found in database, 0 otherwise.
 */
function account_get_token ()
{
    debug_write_log(DEBUG_TRACE, '[account_get_token]');

    $id    = try_cookie(COOKIE_AUTH_USERID, 0);
    $token = try_cookie(COOKIE_AUTH_TOKEN,  0);

    $id = ustr2int($id);

    $rs = dal_query('accounts/gettoken.sql', $id, $token, time());

    return ($rs->rows == 0 ? 0 : $id);
}

/**
 * Generates a token for specified {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_account_id Account ID},
 * and saves it in the database and client cookies.
 *
 * <i>Token</i> is a special MD5 hash, specific to {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_account_id Account ID},
 * client IP address, and time of generation. Tokens are stored in both database ({@link http://www.etraxis.org/docs-schema.php#tbl_accounts_auth_token auth_token}, {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_token_expire token_expire})
 * and client cookies ({@link COOKIE_AUTH_USERID}, {@link COOKIE_AUTH_TOKEN}), and has an expiration specified in {@link SESSION_EXPIRE}.
 * When user tries to log in, eTraxis checks for token of this user in database - if it equals to
 * token stored in client cookies and is not expired yet, than user is pretended as already logged in,
 * and eTraxis does not request for user's credentials.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_account_id Account ID}.
 * @return int Always {@link NO_ERROR}.
 */
function account_set_token ($id)
{
    debug_write_log(DEBUG_TRACE, '[account_set_token]');
    debug_write_log(DEBUG_DUMP,  '[account_set_token] $id = ' . $id);

    $token = md5(WEBROOT . $id . $_SERVER['REMOTE_ADDR'] . $_SERVER['REMOTE_PORT'] . rand());

    save_cookie(COOKIE_AUTH_USERID, $id);
    save_cookie(COOKIE_AUTH_TOKEN,  $token);

    dal_query('accounts/settoken.sql', $id, $token, time() + SESSION_EXPIRE * 60);

    return NO_ERROR;
}

/**
 * Finds in database and returns the information about specified {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_account_id Account ID}.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_account_id Account ID}.
 * @return array Array with data if account is found in database, FALSE otherwise.
 */
function account_find ($id)
{
    debug_write_log(DEBUG_TRACE, '[account_find]');
    debug_write_log(DEBUG_DUMP,  '[account_find] $id = ' . $id);

    $rs = dal_query('accounts/fndid.sql', $id);

    return ($rs->rows == 0 ? FALSE : $rs->fetch());
}

/**
 * Finds in database and returns the information about account with specified {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_username user name}.
 * To distinguish eTraxis accounts from LDAP ones, user name must be appended with {@link ACCOUNT_SUFFIX} to search among eTraxis accounts
 * (LDAP accounts otherwise).
 *
 * @param string $username {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_username User name} of account.
 * @return array Array with data if account is found in database, FALSE otherwise.
 */
function account_find_username ($username)
{
    debug_write_log(DEBUG_TRACE, '[account_find_username]');
    debug_write_log(DEBUG_DUMP,  '[account_find_username] $username = ' . $username);

    $rs = dal_query('accounts/fndk.sql', ustrtolower($username));

    return ($rs->rows == 0 ? FALSE : $rs->fetch());
}

/**
 * Removes {@link ACCOUNT_SUFFIX} from specified {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_username user name}
 * if it presents and LDAP support is enabled.
 *
 * @param string $username {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_username User name}.
 * @param bool $ldap_enabled Whether LDAP support is enabled (current value of {@link LDAP_ENABLED} by default).
 * @return string Clear {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_username user name}.
 */
function account_get_username ($username, $ldap_enabled = LDAP_ENABLED)
{
    debug_write_log(DEBUG_TRACE, '[account_get_username]');
    debug_write_log(DEBUG_DUMP,  '[account_get_username] $username     = ' . $username);
    debug_write_log(DEBUG_DUMP,  '[account_get_username] $ldap_enabled = ' . $ldap_enabled);

    return ($ldap_enabled ? $username : ustr_replace(ACCOUNT_SUFFIX, NULL, $username));
}

/**
 * Returns {@link CRecordset DAL recordset} which contains all existing accounts and sorted in
 * accordance with current sort mode.
 *
 * @param int &$sort Sort mode (used as output only). The function retrieves current sort mode from
 * client cookie ({@link COOKIE_ACCOUNTS_SORT}) and updates it, if it's out of valid range.
 * @param int &$page Number of current page tab (used as output only). The function retrieves current
 * page from client cookie ({@link COOKIE_ACCOUNTS_PAGE}) and updates it, if it's out of valid range.
 * @return CRecordset Recordset with list of accounts.
 */
function account_list (&$sort, &$page)
{
    debug_write_log(DEBUG_TRACE, '[account_list]');

    $sort_modes = array
    (
        1  => 'username asc',
        2  => 'fullname asc, username asc',
        3  => 'email asc, username asc',
        4  => 'is_admin asc, username asc',
        5  => 'description asc, username asc',
        6  => 'username desc',
        7  => 'fullname desc, username desc',
        8  => 'email desc, username desc',
        9  => 'is_admin desc, username desc',
        10 => 'description desc, username desc',
    );

    $sort = try_request('sort', try_cookie(COOKIE_ACCOUNTS_SORT));
    $sort = ustr2int($sort, 1, count($sort_modes));

    $page = try_request('page', try_cookie(COOKIE_ACCOUNTS_PAGE));
    $page = ustr2int($page, 1, MAXINT);

    save_cookie(COOKIE_ACCOUNTS_SORT, $sort);
    save_cookie(COOKIE_ACCOUNTS_PAGE, $page);

    return dal_query('accounts/list.sql', $sort_modes[$sort]);
}

/**
 * Validates account information before creation or modification.
 *
 * @param string $username {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_username User name}.
 * @param string $fullname {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_fullname Full name}.
 * @param string $email {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_email Email address}.
 * @param string $passwd1 {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_passwd First password entry}.
 * @param string $passwd2 {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_passwd Second password entry}.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - data are valid</li>
 * <li>{@link ERROR_INCOMPLETE_FORM} - at least one of required field is empty</li>
 * <li>{@link ERROR_INVALID_USERNAME} - user name contains invalid characters</li>
 * <li>{@link ERROR_INVALID_EMAIL} - email address contains invalid characters</li>
 * <li>{@link ERROR_PASSWORDS_DO_NOT_MATCH} - entered password entries are not equal</li>
 * <li>{@link ERROR_PASSWORD_TOO_SHORT} - entered password is too short (see {@link MIN_PASSWORD_LENGTH})</li>
 * </ul>
 */
function account_validate ($username, $fullname, $email, $passwd1, $passwd2)
{
    debug_write_log(DEBUG_TRACE, '[account_validate]');
    debug_write_log(DEBUG_DUMP,  '[account_validate] $username = ' . $username);
    debug_write_log(DEBUG_DUMP,  '[account_validate] $fullname = ' . $fullname);
    debug_write_log(DEBUG_DUMP,  '[account_validate] $email    = ' . $email);

    if (ustrlen($username) == 0 ||
        ustrlen($fullname) == 0 ||
        ustrlen($email)    == 0 ||
        ustrlen($passwd1)  == 0 ||
        ustrlen($passwd2)  == 0)
    {
        debug_write_log(DEBUG_NOTICE, '[account_validate] At least one required field is empty.');
        return ERROR_INCOMPLETE_FORM;
    }

    if (!is_username($username))
    {
        debug_write_log(DEBUG_NOTICE, '[account_validate] Invalid username.');
        return ERROR_INVALID_USERNAME;
    }

    if (!is_email($email))
    {
        debug_write_log(DEBUG_NOTICE, '[account_validate] Invalid email.');
        return ERROR_INVALID_EMAIL;
    }

    if ($passwd1 != $passwd2)
    {
        debug_write_log(DEBUG_NOTICE, '[account_validate] Passwords do not match.');
        return ERROR_PASSWORDS_DO_NOT_MATCH;
    }

    if (ustrlen($passwd1) < MIN_PASSWORD_LENGTH)
    {
        debug_write_log(DEBUG_NOTICE, '[account_validate] Password is too short.');
        return ERROR_PASSWORD_TOO_SHORT;
    }

    return NO_ERROR;
}

/**
 * Creates new account.
 *
 * @param string $username {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_username User name}.
 * @param string $fullname {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_fullname Full name}.
 * @param string $email {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_email Email address}.
 * @param string $passwd {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_passwd Password}.
 * @param string $description Optional {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_description description}.
 * @param bool $is_admin Whether new account will have administration privileges (see '{@link http://www.etraxis.org/docs-schema.php#tbl_accounts_is_admin is_admin}' DBO field).
 * @param bool $is_disabled Whether new account should be created in disabled state (see '{@link http://www.etraxis.org/docs-schema.php#tbl_accounts_is_disabled is_disabled}' DBO field).
 * @param int $locale UI language (see '{@link http://www.etraxis.org/docs-schema.php#tbl_accounts_locale locale}' DBO field).
 * @param bool $is_ldapuser Whether new account is LDAP one (FALSE by default, see '{@link http://www.etraxis.org/docs-schema.php#tbl_accounts_is_ldapuser is_ldapuser}' DBO field).
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - account is successfully created</li>
 * <li>{@link ERROR_ALREADY_EXISTS} - account with specified {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_username user name} already exists</li>
 * <li>{@link ERROR_NOT_FOUND} - failure on attempt to create account</li>
 * </ul>
 */
function account_create ($username, $fullname, $email, $passwd, $description, $is_admin, $is_disabled, $locale, $is_ldapuser = FALSE)
{
    debug_write_log(DEBUG_TRACE, '[account_create]');
    debug_write_log(DEBUG_DUMP,  '[account_create] $username    = ' . $username);
    debug_write_log(DEBUG_DUMP,  '[account_create] $fullname    = ' . $fullname);
    debug_write_log(DEBUG_DUMP,  '[account_create] $email       = ' . $email);
    debug_write_log(DEBUG_DUMP,  '[account_create] $description = ' . $description);
    debug_write_log(DEBUG_DUMP,  '[account_create] $is_admin    = ' . $is_admin);
    debug_write_log(DEBUG_DUMP,  '[account_create] $is_disabled = ' . $is_disabled);
    debug_write_log(DEBUG_DUMP,  '[account_create] $locale      = ' . $locale);
    debug_write_log(DEBUG_DUMP,  '[account_create] $is_ldapuser = ' . $is_ldapuser);

    // Check that there is no account with the same user name.
    $rs = dal_query('accounts/fndk.sql', ustrtolower($username . ($is_ldapuser ? NULL : ACCOUNT_SUFFIX)));

    if ($rs->rows != 0)
    {
        debug_write_log(DEBUG_NOTICE, '[account_create] Account already exists.');
        return ERROR_ALREADY_EXISTS;
    }

    // Create an account.
    dal_query('accounts/create.sql',
              $username . ($is_ldapuser ? NULL : ACCOUNT_SUFFIX),
              $fullname,
              $email,
              md5($passwd),
              ustrlen($description) == 0 ? NULL : $description,
              bool2sql($is_admin),
              bool2sql($is_disabled),
              bool2sql($is_ldapuser),
              $locale,
              DEFAULT_PAGE_ROWS,
              DEFAULT_PAGE_BKMS);

    // Find newly created account.
    $rs = dal_query('accounts/fndk.sql', ustrtolower($username . ($is_ldapuser ? NULL : ACCOUNT_SUFFIX)));

    if ($rs->rows == 0)
    {
        debug_write_log(DEBUG_WARNING, '[account_create] Created account not found.');
        return ERROR_NOT_FOUND;
    }

    // Get an ID of the created account.
    $account_id = $rs->fetch('account_id');

    // Create 1st default filter for new account, which will show all records assigned to this account.
    dal_query('filters/create.sql',
              $account_id,
              get_html_resource(RES_ALL_ASSIGNED_TO_ME_ID, $locale),
              FILTER_TYPE_ALL_PROJECTS,
              FILTER_FLAG_ASSIGNED_TO,
              NULL);

    // Find 1st newly created default filter.
    $rs = dal_query('filters/fndk.sql',
                    $account_id,
                    ustrtolower(get_html_resource(RES_ALL_ASSIGNED_TO_ME_ID, $locale)));

    if ($rs->rows == 0)
    {
        debug_write_log(DEBUG_WARNING, '[account_create] Created filter #1 not found.');
    }
    else
    {
        $filter_id = $rs->fetch('filter_id');

        // Complete filter settings and active the filter.
        dal_query('filters/facreate.sql', $filter_id, FILTER_FLAG_ASSIGNED_TO, $account_id);
        dal_query('filters/set.sql', $filter_id, $account_id);
    }

    // Create 2nd default filter for new account, which will show all opened records created by this account.
    dal_query('filters/create.sql',
              $account_id,
              get_html_resource(RES_ALL_CREATED_BY_ME_ID, $locale),
              FILTER_TYPE_ALL_PROJECTS,
              FILTER_FLAG_CREATED_BY | FILTER_FLAG_UNCLOSED,
              NULL);

    // Find 2nd newly created default filter.
    $rs = dal_query('filters/fndk.sql',
                    $account_id,
                    ustrtolower(get_html_resource(RES_ALL_CREATED_BY_ME_ID, $locale)));

    if ($rs->rows == 0)
    {
        debug_write_log(DEBUG_WARNING, '[account_create] Created filter #2 not found.');
    }
    else
    {
        $filter_id = $rs->fetch('filter_id');

        // Complete filter settings and active the filter.
        dal_query('filters/facreate.sql', $filter_id, FILTER_FLAG_CREATED_BY, $account_id);
        dal_query('filters/set.sql', $filter_id, $account_id);
    }

    return NO_ERROR;
}

/**
 * Modifies specified account.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_account_id ID} of account to be modified.
 * @param string $username New {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_username user name}.
 * @param string $fullname New {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_fullname full name}.
 * @param string $email New {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_email email address}.
 * @param string $description New {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_description description}.
 * @param bool $is_admin Whether the account should have administration privileges (see '{@link http://www.etraxis.org/docs-schema.php#tbl_accounts_is_admin is_admin}' DBO field).
 * @param bool $is_disabled Whether the account should be disabled (see '{@link http://www.etraxis.org/docs-schema.php#tbl_accounts_is_disabled is_disabled}' DBO field).
 * @param int $locks_count New value of '{@link http://www.etraxis.org/docs-schema.php#tbl_accounts_locks_count locks_count}' DBO field.
 * @param bool $is_ldapuser Whether the account is LDAP one (FALSE by default, see '{@link http://www.etraxis.org/docs-schema.php#tbl_accounts_is_ldapuser is_ldapuser}' DBO field).
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - account is successfully modified</li>
 * <li>{@link ERROR_ALREADY_EXISTS} - another account with specified {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_username user name} already exists</li>
 * </ul>
 */
function account_modify ($id, $username, $fullname, $email, $description, $is_admin, $is_disabled, $locks_count, $is_ldapuser = FALSE)
{
    debug_write_log(DEBUG_TRACE, '[account_modify]');
    debug_write_log(DEBUG_DUMP,  '[account_modify] $id          = ' . $id);
    debug_write_log(DEBUG_DUMP,  '[account_modify] $username    = ' . $username);
    debug_write_log(DEBUG_DUMP,  '[account_modify] $fullname    = ' . $fullname);
    debug_write_log(DEBUG_DUMP,  '[account_modify] $email       = ' . $email);
    debug_write_log(DEBUG_DUMP,  '[account_modify] $description = ' . $description);
    debug_write_log(DEBUG_DUMP,  '[account_modify] $is_admin    = ' . $is_admin);
    debug_write_log(DEBUG_DUMP,  '[account_modify] $is_disabled = ' . $is_disabled);
    debug_write_log(DEBUG_DUMP,  '[account_modify] $locks_count = ' . $locks_count);

    // Check that there is no account with the same user name, besides this one.
    $rs = dal_query('accounts/fndku.sql', $id, ustrtolower($username . ($is_ldapuser ? NULL : ACCOUNT_SUFFIX)));

    if ($rs->rows != 0)
    {
        debug_write_log(DEBUG_NOTICE, '[account_modify] Account already exists.');
        return ERROR_ALREADY_EXISTS;
    }

    // Modify the account.
    dal_query('accounts/modify.sql',
              $id,
              $username . ($is_ldapuser ? NULL : ACCOUNT_SUFFIX),
              $fullname,
              $email,
              ustrlen($description) == 0 ? NULL : $description,
              bool2sql($is_admin),
              bool2sql($is_disabled),
              $locks_count);

    return NO_ERROR;
}

/**
 * Creates new LDAP account in eTraxis database.
 *
 * The function searches on LDAP server for specified <i>username</i>.
 * If user is found, then his display name and email address are cached in eTraxis database.
 * If <i>password</i> is specified, then function also tries to authorize on LDAP server using specified <i>username</i> and <i>password</i>.
 * If authorization is failed, NULL is returned, even when user with specified <i>username</i> was successfully found.
 * On success ID of new registered account is returned.
 *
 * @param string $username {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_username User name} for new account.
 * @param string $password Password of user.
 * @return int {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_account_id ID} of new account on success, NULL otherwise.
 */
function account_register_ldapuser ($username, $passwd = NULL)
{
    debug_write_log(DEBUG_TRACE, '[account_register_ldapuser]');
    debug_write_log(DEBUG_DUMP,  '[account_register_ldapuser] $username = ' . $username);

    // Set default encoding of multibyte String library to UTF-8.
    mb_regex_encoding('UTF-8');

    // Find a user with specified user name on LDAP server.
    $id = NULL;
    $userinfo = ldap_finduser($username, $passwd);

    if (!is_null($userinfo))
    {
        debug_write_log(DEBUG_NOTICE, 'Active Directory account is found.');

        // Check whether this LDAP user is already registered in eTraxis database.
        $rs = dal_query('accounts/fndk.sql', ustrtolower($username));

        // This LDAP user was never registered in eTraxis database before.
        if ($rs->rows == 0)
        {
            debug_write_log(DEBUG_NOTICE, 'Register Active Directory account in eTraxis database.');

            // Create an account in eTraxis database for this LDAP user.
            account_create($username,
                           $userinfo[0],
                           $userinfo[1],
                           '',
                           'Active Directory account',
                           in_array(ustrtolower($username), mb_split(',', ustrtolower(LDAP_ADMINS))),
                           0, LANG_DEFAULT, TRUE);

            $rs = dal_query('accounts/fndk.sql', ustrtolower($username));
            $id = $rs->fetch('account_id');
        }
        // This LDAP user is already registered in eTraxis database.
        else
        {
            debug_write_log(DEBUG_NOTICE, 'Update Active Directory account in eTraxis database.');

            $id = $rs->fetch('account_id');

            // Update an account of this LDAP user in eTraxis database.
            account_modify($id,
                           $username,
                           $userinfo[0],
                           $userinfo[1],
                           'Active Directory account',
                           in_array(ustrtolower($username), mb_split(',', ustrtolower(LDAP_ADMINS))),
                           0, 0, TRUE);
        }
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, 'Cannot find Active Directory account.');
    }

    return $id;
}

/**
 * Checks whether account can be deleted.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_account_id ID} of account to be deleted.
 * @return bool TRUE if account can be deleted, FALSE otherwise.
 */
function is_account_removable ($id)
{
    debug_write_log(DEBUG_TRACE, '[is_account_removable]');
    debug_write_log(DEBUG_DUMP,  '[is_account_removable] $id = ' . $id);

    $rs = dal_query('accounts/efndc.sql', $id);

    return ($rs->fetch(0) == 0);
}

/**
 * Deletes specified account.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_account_id ID} of account to be deleted.
 * @return int Always {@link NO_ERROR}.
 */
function account_delete ($id)
{
    debug_write_log(DEBUG_TRACE, '[account_delete]');
    debug_write_log(DEBUG_DUMP,  '[account_delete] $id = ' . $id);

    dal_query('records/unreadall.sql',  $id);
    dal_query('accounts/rdelall.sql',   $id);
    dal_query('accounts/rsdelall.sql',  $id);
    dal_query('accounts/sdelall.sql',   $id);
    dal_query('accounts/setview.sql',   $id, NULL);
    dal_query('accounts/cdelall.sql',   $id);
    dal_query('accounts/vcdelall.sql',  $id);
    dal_query('accounts/vdelall.sql',   $id);
    dal_query('accounts/setfset.sql',   $id, NULL);
    dal_query('accounts/fsfdelall.sql', $id);
    dal_query('accounts/fs2delall.sql', $id);
    dal_query('accounts/ffdelall.sql',  $id);
    dal_query('accounts/ftdelall.sql',  $id);
    dal_query('accounts/fsdelall.sql',  $id);
    dal_query('accounts/fadelall.sql',  $id);
    dal_query('accounts/fa2delall.sql', $id);
    dal_query('accounts/fshdelall.sql', $id);
    dal_query('accounts/fdelall.sql',   $id);
    dal_query('accounts/msdelall.sql',  $id);
    dal_query('accounts/delete.sql',    $id);

    return NO_ERROR;
}

/**
 * Validates new password which user has entered to change his current one.
 *
 * @param string $passwd1 {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_passwd First password entry}.
 * @param string $passwd2 {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_passwd Second password entry}.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - entered password is valid</li>
 * <li>{@link ERROR_INCOMPLETE_FORM} - at least one of password entries is empty</li>
 * <li>{@link ERROR_PASSWORDS_DO_NOT_MATCH} - entered password entries are not equal</li>
 * <li>{@link ERROR_PASSWORD_TOO_SHORT} - entered password is too short (see {@link MIN_PASSWORD_LENGTH})</li>
 * </ul>
 */
function password_validate ($passwd1, $passwd2)
{
    debug_write_log(DEBUG_TRACE, '[password_validate]');

    if (ustrlen($passwd1) == 0 ||
        ustrlen($passwd2) == 0)
    {
        debug_write_log(DEBUG_NOTICE, '[password_validate] At least one required field is empty.');
        return ERROR_INCOMPLETE_FORM;
    }

    if ($passwd1 != $passwd2)
    {
        debug_write_log(DEBUG_NOTICE, '[password_validate] Passwords do not match.');
        return ERROR_PASSWORDS_DO_NOT_MATCH;
    }

    if (ustrlen($passwd1) < MIN_PASSWORD_LENGTH)
    {
        debug_write_log(DEBUG_NOTICE, '[password_validate] Password is too short.');
        return ERROR_PASSWORD_TOO_SHORT;
    }

    return NO_ERROR;
}

/**
 * Change password of specified user.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_account_id ID} of user account.
 * @param string $passwd New {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_passwd password} string.
 * @return int Always {@link NO_ERROR}.
 */
function password_change ($id, $passwd)
{
    debug_write_log(DEBUG_TRACE, '[password_change]');
    debug_write_log(DEBUG_DUMP,  '[password_change] $id = ' . $id);

    dal_query('accounts/passwd.sql',
              $id,
              md5($passwd),
              time());

    return NO_ERROR;
}

/**
 * Change UI language for specified user.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_account_id ID} of user account.
 * @param int $locale New UI language (see '{@link http://www.etraxis.org/docs-schema.php#tbl_accounts_locale locale}' DBO field).
 * @return int Always {@link NO_ERROR}.
 */
function locale_change ($id, $locale)
{
    debug_write_log(DEBUG_TRACE, '[locale_change]');
    debug_write_log(DEBUG_DUMP,  '[locale_change] $id     = ' . $id);
    debug_write_log(DEBUG_DUMP,  '[locale_change] $locale = ' . $locale);

    global $locale_info;

    if (array_key_exists($locale, $locale_info))
    {
        dal_query('accounts/locale.sql', $id, $locale);
    }

    return NO_ERROR;
}

/**
 * Set current user's filters set to specified one, or disable all filters if NULL is specified.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_account_id ID} of user account.
 * @param int $fset_id {@link http://www.etraxis.org/docs-schema.php#tbl_fsets_fset_id Filters set ID} (NULL by default).
 * @return int Always {@link NO_ERROR}.
 */
function account_set_fset ($id, $fset_id = NULL)
{
    debug_write_log(DEBUG_TRACE, '[account_set_fset]');
    debug_write_log(DEBUG_DUMP,  '[account_set_fset] $id      = ' . $id);
    debug_write_log(DEBUG_DUMP,  '[account_set_fset] $fset_id = ' . $fset_id);

    // Change current user's filters set.
    dal_query('accounts/setfset.sql', $id, is_null($fset_id) ? NULL : $fset_id);

    // Activate all filters of specified filters set and disable all others.
    if (!is_null($fset_id))
    {
        dal_query('filters/clearall.sql', $_SESSION[VAR_USERID]);
        dal_query('filters/fsset.sql',    $_SESSION[VAR_USERID], $fset_id);
    }

    return NO_ERROR;
}

/**
 * Set current user's view to specified one, or restore default view if NULL is specified.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_account_id ID} of user account.
 * @param int $view_id {@link http://www.etraxis.org/docs-schema.php#tbl_views_view_id View ID} (NULL by default).
 * @return int Always {@link NO_ERROR}.
 */
function account_set_view ($id, $view_id = NULL)
{
    debug_write_log(DEBUG_TRACE, '[account_set_view]');
    debug_write_log(DEBUG_DUMP,  '[account_set_view] $id      = ' . $id);
    debug_write_log(DEBUG_DUMP,  '[account_set_view] $view_id = ' . $view_id);

    // Change current user's view.
    dal_query('accounts/setview.sql', $id, is_null($view_id) ? NULL : $view_id);

    // Add all columns of specified view and remove all others.
    if (!is_null($view_id))
    {
        dal_query('columns/cdelall.sql', $_SESSION[VAR_USERID]);
        dal_query('columns/ccreate.sql', $_SESSION[VAR_USERID], $view_id);
    }

    return NO_ERROR;
}

/**
 * Exports accounts of specified group IDs to XML code (see also {@link template_import}).
 *
 * @param array Array with {@link http://www.etraxis.org/docs-schema.php#tbl_groups_group_id Group IDs}
 * @return string Generated XML code for accounts found.
 */
function accounts_export ($groups)
{
    debug_write_log(DEBUG_TRACE, '[accounts_export]');

    // Remove duplicated group IDs.
    $groups = array_unique($groups);
    // List members of all global and local project groups.
    $rs = dal_query('groups/mamongs2.sql', implode(',', $groups));

    $xml_a = NULL;

    if ($rs->rows != 0)
    {
        $xml_a = "  <accounts>\n";

        // Add XML code for all enumerated accounts.
        while (($account = $rs->fetch()))
        {
            // Add XML code for general account information.
            $xml_a .= sprintf("    <account username=\"%s\" fullname=\"%s\" email=\"%s\" description=\"%s\" type=\"%s\" admin=\"%s\" disabled=\"%s\" locale=\"%s\"/>\n",
                              account_get_username($account['username'], FALSE),
                              ustr2html($account['fullname']),
                              ustr2html($account['email']),
                              ustr2html($account['description']),
                              ($account['is_ldapuser'] ? 'ldap' : 'local'),
                              ($account['is_admin']    ? 'yes'  : 'no'),
                              ($account['is_disabled'] ? 'yes'  : 'no'),
                              get_html_resource(RES_LOCALE_ID, $account['locale']));
        }

        $xml_a .= "  </accounts>\n";
    }

    return $xml_a;
}

?>
