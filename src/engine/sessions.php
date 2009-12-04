<?php

/**
 * Sessions
 *
 * This module implements user sessions in eTraxis.
 *
 * @package Engine
 * @subpackage Sessions
 */

//--------------------------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system.
//  Copyright (C) 2004-2009 by Artem Rodygin
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
//  Artem Rodygin           2004-11-22      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-08-15      new-003: Authentication with Active Directory.
//  Artem Rodygin           2005-08-18      new-030: UI language should be set for each user separately.
//  Artem Rodygin           2005-08-18      new-035: Customizable list size.
//  Artem Rodygin           2005-09-22      new-141: Source code review.
//  Artem Rodygin           2005-10-13      new-157: Name of logged in user should be displayed.
//  Artem Rodygin           2006-01-24      new-204: Active Directory Support functionality (new-003) should be conditionally "compiled".
//  Artem Rodygin           2006-08-20      new-313: Implement HTTP authentication.
//  Artem Rodygin           2006-09-24      new-315: User should have several attempts to login.
//  Artem Rodygin           2006-11-08      new-366: Export to CSV.
//  Artem Rodygin           2006-11-20      new-392: Local users should not be extended with '@eTraxis' when LDAP is disabled.
//  Artem Rodygin           2006-11-20      new-377: Custom views.
//  Artem Rodygin           2006-12-15      bug-409: User session expires too quick.
//  Artem Rodygin           2007-06-24      bug-529: Largest amount of records (1000) is displayed more than 30 seconds.
//  Artem Rodygin           2007-09-12      bug-581: PHP Warning: session_destroy(): Session object destruction failed
//  Artem Rodygin           2007-09-12      new-576: [SF1788286] Export to CSV
//  Artem Rodygin           2007-09-13      new-566: Choose encoding for record dump and export of records list.
//  Artem Rodygin           2007-10-02      new-513: Apply current filter set to search results.
//  Artem Rodygin           2007-10-29      new-564: Filters set.
//  Artem Rodygin           2007-11-26      new-633: The 'dbx' extension should not be used.
//  Dmitry Gorev            2007-12-10      new-414: Passwords expiration.
//  Dmitry Gorev            2007-12-18      bug-645: Account is locked for specified amount of seconds, not minutes.
//  Artem Rodygin           2008-04-19      new-704: Show name of user who is logged in.
//  Artem Rodygin           2008-06-17      bug-724: Import MYSQL41.SQL failed
//  Artem Rodygin           2008-10-29      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2008-11-18      new-762: Forward logged in user to the page he has tried to open before authentication.
//  Artem Rodygin           2008-11-19      bug-766: PHP Notice: Undefined index: eTraxis_FullName
//  Artem Rodygin           2009-01-12      bug-784: Logged in user must be forwarded to the page he has tried to open before authentication.
//  Artem Rodygin           2009-01-13      new-785: Favorites icon.
//  Artem Rodygin           2009-02-26      bug-792: [SF2635842] Short PHP tags in login.php
//  Artem Rodygin           2009-03-11      bug-799: eTraxis doesn't work with XAMPP on Windows.
//  Artem Rodygin           2009-04-13      new-814: Password expiration should be turnable off.
//  Alexandr Permyakov      2009-05-29      new-821: Remove redundant call of 'ldap_finduser' from 'login_user'.
//  Artem Rodygin           2009-06-01      new-824: PHP 4 is discontinued.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/debug.php');
require_once('../engine/locale.php');
require_once('../engine/cookies.php');
require_once('../engine/dal.php');
require_once('../engine/ldap.php');
require_once('../dbo/accounts.php');
/**#@-*/

//--------------------------------------------------------------------------------------------------
//  Definitions.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Session variable.
 */
define('VAR_ERROR',         'eTraxis_Error');
define('VAR_USERID',        'eTraxis_UserID');
define('VAR_USERNAME',      'eTraxis_UserName');
define('VAR_FULLNAME',      'eTraxis_FullName');
define('VAR_PASSWD_EXPIRE', 'eTraxis_PasswdExpire');
define('VAR_ISADMIN',       'eTraxis_IsAdmin');
define('VAR_LDAPUSER',      'eTraxis_LdapUser');
define('VAR_PAGEROWS',      'eTraxis_PageRows');
define('VAR_PAGEBKMS',      'eTraxis_PageBkms');
define('VAR_DELIMITER',     'eTraxis_Delimiter');
define('VAR_ENCODING',      'eTraxis_Encoding');
define('VAR_LINE_ENDINGS',  'eTraxis_LineEndings');
define('VAR_FSET',          'eTraxis_FiltersSet');
define('VAR_VIEW',          'eTraxis_View');
define('VAR_USE_FILTERS',   'eTraxis_UseFilter');
/**#@-*/

/**#@+
 * User level.
 */
define('USER_LEVEL_GUEST',  1);
define('USER_LEVEL_NORMAL', 2);
define('USER_LEVEL_ADMIN',  3);
/**#@-*/

/**
 * ID for guest.
 */
define('GUEST_USER_ID', 0);

/**
 * Flag that guest is allowed to access a page.
 */
define('GUEST_IS_ALLOWED', TRUE);

// Encodings.
$encodings = array
(
    1  => 'UTF-8',
    2  => 'UCS-2',
    3  => 'ISO-8859-1',
    4  => 'ISO-8859-2',
    5  => 'ISO-8859-3',
    6  => 'ISO-8859-4',
    7  => 'ISO-8859-5',
    8  => 'ISO-8859-6',
    9  => 'ISO-8859-7',
    10 => 'ISO-8859-8',
    11 => 'ISO-8859-9',
    12 => 'ISO-8859-10',
    13 => 'ISO-8859-13',
    14 => 'ISO-8859-14',
    15 => 'ISO-8859-15',
    16 => 'KOI8-R',
    17 => 'Windows-1251',
    18 => 'Windows-1252',
);

// Line endings.
$line_endings_names = array
(
    1 => 'Windows',
    2 => 'Unix',
    3 => 'Mac',
);

$line_endings_chars = array
(
    1 => "\r\n",
    2 => "\n",
    3 => "\r",
);

/**#@+
 * Default settings.
 */
define('DEFAULT_PAGE_ROWS',    20);
define('DEFAULT_PAGE_BKMS',    10);
define('DEFAULT_DELIMITER',    0x2C);
define('DEFAULT_ENCODING',     1);
define('DEFAULT_LINE_ENDINGS', 1);
/**#@-*/

/**#@+
 * List size restriction.
 */
define('MIN_PAGE_SIZE', 10);
define('MAX_PAGE_SIZE', 100);
/**#@-*/

//--------------------------------------------------------------------------------------------------
//  Functions.
//--------------------------------------------------------------------------------------------------

/**
 * Creates (initializes) anonymous session before user is authorized.
 *
 * @return string The session ID for the newly created session.
 */
function create_session ()
{
    error_reporting(E_ALL);
    set_magic_quotes_runtime(0);

    if (DEBUG_MODE == DEBUG_MODE_OFF)
    {
        assert_options(ASSERT_ACTIVE,     0);
    }
    else
    {
        assert_options(ASSERT_ACTIVE,     1);
        assert_options(ASSERT_WARNING,    1);
        assert_options(ASSERT_BAIL,       0);
        assert_options(ASSERT_QUIET_EVAL, 0);
        assert_options(ASSERT_CALLBACK,   NULL);
    }

    return session_id();
}

/**
 * Opens new session (preliminary created with {@link create_session}) for successfully authorized user (user becomes logged in).
 *
 * @param int $userid Account ID of authorized user (see <i>account_id</i> of <i>tbl_accounts</i> database table).
 * @return string The session ID.
 */
function open_session ($userid)
{
    debug_write_log(DEBUG_TRACE, '[open_session]');
    debug_write_log(DEBUG_DUMP,  '[open_session] $userid = ' . $userid);

    global $encodings;
    global $line_endings_chars;

    $_SESSION[VAR_USERID]        = $userid;
    $_SESSION[VAR_USERNAME]      = get_html_resource(RES_GUEST_ID);
    $_SESSION[VAR_FULLNAME]      = get_html_resource(RES_GUEST_ID);
    $_SESSION[VAR_PASSWD_EXPIRE] = 0;
    $_SESSION[VAR_ISADMIN]       = FALSE;
    $_SESSION[VAR_LDAPUSER]      = FALSE;
    $_SESSION[VAR_LOCALE]        = LANG_DEFAULT;
    $_SESSION[VAR_PAGEROWS]      = DEFAULT_PAGE_ROWS;
    $_SESSION[VAR_PAGEBKMS]      = DEFAULT_PAGE_BKMS;
    $_SESSION[VAR_DELIMITER]     = chr(DEFAULT_DELIMITER);
    $_SESSION[VAR_ENCODING]      = $encodings[DEFAULT_ENCODING];
    $_SESSION[VAR_LINE_ENDINGS]  = $line_endings_chars[DEFAULT_LINE_ENDINGS];
    $_SESSION[VAR_FSET]          = NULL;
    $_SESSION[VAR_VIEW]          = NULL;
    $_SESSION[VAR_USE_FILTERS]   = FALSE;

    return session_id();
}

/**
 * Closes current session (user becomes logged off).
 */
function close_session ()
{
    unset($_SESSION[VAR_ERROR]);
    unset($_SESSION[VAR_USERID]);
    unset($_SESSION[VAR_USERNAME]);
    unset($_SESSION[VAR_FULLNAME]);
    unset($_SESSION[VAR_PASSWD_EXPIRE]);
    unset($_SESSION[VAR_ISADMIN]);
    unset($_SESSION[VAR_LDAPUSER]);
    unset($_SESSION[VAR_LOCALE]);
    unset($_SESSION[VAR_PAGEROWS]);
    unset($_SESSION[VAR_PAGEBKMS]);
    unset($_SESSION[VAR_DELIMITER]);
    unset($_SESSION[VAR_ENCODING]);
    unset($_SESSION[VAR_LINE_ENDINGS]);
    unset($_SESSION[VAR_FSET]);
    unset($_SESSION[VAR_VIEW]);
    unset($_SESSION[VAR_USE_FILTERS]);

    @session_destroy();
}

/**
 * Tries to log user in eTraxis with specified credentials.
 *
 * @param string $username {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_username User name}.
 * @param string $passwd {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_passwd Password}.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - user is successfully authenticated</li>
 * <li>{@link ERROR_UNKNOWN_USERNAME} - unknown user name or bad password</li>
 * <li>{@link ERROR_ACCOUNT_DISABLED} - account is disabled</li>
 * <li>{@link ERROR_ACCOUNT_LOCKED} - account is locked out</li>
 * </ul>
 */
function login_user ($username, $passwd)
{
    $error = NO_ERROR;

    // If '@' is specified at the end of user name, suppress looking for account in eTraxis database.
    if (usubstr($username, ustrlen($username) - 1, 1) == '@')
    {
        debug_write_log(DEBUG_NOTICE, 'Found @ at the end of login.');
        $username = usubstr($username, 0, ustrlen($username) - 1);
        $account = FALSE;
    }
    else
    {
        // Search account in eTraxis database.
        $account = account_find_username($username . ACCOUNT_SUFFIX);
    }

    // If account is not found in eTraxis database (or wasn't searched at all),
    // try to search it in Active Directory.
    if (!$account)
    {
        debug_write_log(DEBUG_NOTICE, 'Unknown user name.');

        if (ustrlen($passwd) == 0)
        {
            debug_write_log(DEBUG_NOTICE, 'Empty password is submitted.');
            $error = ERROR_UNKNOWN_USERNAME;
        }
        elseif (LDAP_ENABLED)
        {
            debug_write_log(DEBUG_NOTICE, 'Trying to find Active Directory account.');

            $id = account_register_ldapuser($username, $passwd);

            if (is_null($id))
            {
                debug_write_log(DEBUG_NOTICE, 'Cannot find Active Directory account.');
                $error = ERROR_UNKNOWN_USERNAME;
            }
            else
            {
                account_set_token($id);
                open_session($id);
            }
        }
        else
        {
            debug_write_log(DEBUG_NOTICE, 'LDAP support is disabled.');
            $error = ERROR_UNKNOWN_USERNAME;
        }
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, 'User name is found in eTraxis database.');

        // Check status of account and provided password.
        if ($account['is_disabled'])
        {
            debug_write_log(DEBUG_NOTICE, 'Account is disabled.');
            $error = ERROR_ACCOUNT_DISABLED;
        }
        elseif (is_account_locked($account['locks_count'], $account['lock_time']))
        {
            debug_write_log(DEBUG_NOTICE, 'Account is locked out.');
            $error = ERROR_ACCOUNT_LOCKED;
        }
        elseif ($account['passwd'] != md5($passwd))
        {
            debug_write_log(DEBUG_NOTICE, 'Bad password.');
            account_lock($account['account_id']);
            $error = ERROR_UNKNOWN_USERNAME;
        }
        else
        {
            account_unlock($account['account_id']);
            account_set_token($account['account_id']);
            open_session($account['account_id']);
        }
    }

    return $error;
}

/**
 * Returns current user level.
 *
 * @return int User level:
 * <ul>
 * <li>{@link USER_LEVEL_GUEST} - user is not logged in and has guest permissions only</li>
 * <li>{@link USER_LEVEL_NORMAL} - user is logged in with usual permissions</li>
 * <li>{@link USER_LEVEL_ADMIN} - user is logged in with administrative permissions</li>
 * </ul>
 */
function get_user_level ()
{
    // If somewhy this variable is not set yet, force to set it.
    if (!isset($_SESSION[VAR_USERID]))
    {
        $_SESSION[VAR_USERID] = 0;
    }

    // Now we know for sure that the variable exists even if user is not logged in at all.
    if ($_SESSION[VAR_USERID] != 0)
    {
        return $_SESSION[VAR_ISADMIN] ? USER_LEVEL_ADMIN : USER_LEVEL_NORMAL;
    }
    else
    {
        return USER_LEVEL_GUEST;
    }
}

/**
 * Performs required initialization before execution of any PHP page.
 *
 * Must be called once and at the very beginning of each PHP page.
 *
 * @param int $guest_is_allowed Flag that guest is allowed to access the page.
 */
function init_page ($guest_is_allowed = FALSE)
{
    global $encodings;
    global $line_endings_chars;

    session_start();

    if (get_user_level() == USER_LEVEL_GUEST)
    {
        create_session();
        open_session(account_get_token(NULL));
    }

    if (get_user_level() == USER_LEVEL_GUEST)
    {
        debug_write_log(DEBUG_NOTICE, '[init_page] User is not authorized.');

        // Force the guest to log in
        if (!$guest_is_allowed)
        {
            save_cookie(COOKIE_URI, $_SERVER['REQUEST_URI']);
            debug_write_log(DEBUG_NOTICE, '[init_page] Guest must be logged in.');
            header('Location: ' . WEBROOT . 'logon/login.php');
        	exit;
        }
    }
    else
    {
        $rs = dal_query('accounts/fndid2.sql',
                        $_SESSION[VAR_USERID],
                        time(),
                        LOCKS_COUNT,
                        time() - LOCKS_TIMEOUT * 60);

        if ($rs->rows == 0)
        {
            debug_write_log(DEBUG_NOTICE, '[init_page] Specified user cannot be authorized.');
            open_session(GUEST_USER_ID);
        }
        else
        {
            $account = $rs->fetch();

            $_SESSION[VAR_USERNAME]      = account_get_username($account['username']);
            $_SESSION[VAR_FULLNAME]      = $account['fullname'];
            $_SESSION[VAR_PASSWD_EXPIRE] = $account['passwd_expire'];
            $_SESSION[VAR_ISADMIN]       = $account['is_admin'];
            $_SESSION[VAR_LDAPUSER]      = $account['is_ldapuser'];
            $_SESSION[VAR_LOCALE]        = $account['locale'];
            $_SESSION[VAR_PAGEROWS]      = $account['page_rows'];
            $_SESSION[VAR_PAGEBKMS]      = $account['page_bkms'];
            $_SESSION[VAR_DELIMITER]     = chr($account['csv_delim']);
            $_SESSION[VAR_ENCODING]      = $encodings[$account['csv_encoding']];
            $_SESSION[VAR_LINE_ENDINGS]  = $line_endings_chars[$account['csv_line_ends']];
            $_SESSION[VAR_FSET]          = $account['fset_id'];
            $_SESSION[VAR_VIEW]          = $account['view_id'];

            if ((strpos($_SERVER['PHP_SELF'], '/chpasswd/index.php') === FALSE            ) &&
                (PASSWORD_EXPIRATION != 0                                                 ) &&
                ($_SESSION[VAR_PASSWD_EXPIRE] + PASSWORD_EXPIRATION * SECS_IN_DAY < time()) &&
                (!$_SESSION[VAR_LDAPUSER]                                                 ))
            {
                debug_write_log(DEBUG_NOTICE, '[init_page] Password is expired.');
                header('Location: ' . WEBROOT . 'chpasswd/index.php');
            	exit;
            }
        }
    }
}

?>
