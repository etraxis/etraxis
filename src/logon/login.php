<?php

/**
 * @package eTraxis
 * @ignore
 */

//--------------------------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system.
//  Copyright (C) 2005-2009 by Artem Rodygin
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
//  Artem Rodygin           2005-01-08      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-08-15      new-003: Authentication with Active Directory.
//  Artem Rodygin           2005-08-23      new-053: All the calls of DAL API functions should be moved to DBO API.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-09-02      bug-082: AD user cannot login if internal eTraxis account with the same username is already exist.
//  Artem Rodygin           2005-09-27      new-141: Source code review.
//  Artem Rodygin           2006-01-24      new-204: Active Directory Support functionality (new-003) should be conditionally "compiled".
//  Artem Rodygin           2006-07-14      new-206: User password should not be stored in client cookies.
//  Artem Rodygin           2006-08-07      bug-300: Cannot login with Active Directory credentials.
//  Artem Rodygin           2006-08-14      new-311: Administrator should have ability to disable saving passwords on logging in.
//  Artem Rodygin           2006-08-20      new-313: Implement HTTP authentication.
//  Artem Rodygin           2006-09-24      new-315: User should have several attempts to login.
//  Artem Rodygin           2006-09-24      new-316: Domain should be ignored if entered while logging in.
//  Artem Rodygin           2006-10-07      bug-319: PHP Notice: Undefined index: eTraxis_Retry
//  Artem Rodygin           2006-11-18      bug-389: Motorola LDAP server returns "Insufficient rights" error.
//  Artem Rodygin           2006-12-09      bug-425: PHP Notice: Undefined index: eTraxis_Attempt
//  Artem Rodygin           2006-12-15      bug-409: User session expires too quick.
//  Artem Rodygin           2006-12-16      bug-451: '/src/logon/login.php' opens debug log twice.
//  Artem Rodygin           2006-12-16      new-452: Authentication realm should not contain version info.
//  Artem Rodygin           2006-12-27      bug-464: Active Directory user cannot be added into group if local user with the same name exists.
//  Artem Rodygin           2007-01-22      bug-490: Active Directory user can log in with empty password!
//  Artem Rodygin           2007-01-31      bug-492: [SF1647591] 'root' login non-functional.
//  Daniel Jungbluth        2007-09-04      bug-575: Login and Logout
//  Artem Rodygin           2007-10-08      bug-592: Wrong username/password cause unlimited amount of authentication requests.
//  Artem Rodygin           2007-11-30      bug-632: HTTP Authentication problem running as CGI
//  Artem Rodygin           2007-12-05      bug-642: PHP Parse error: syntax error, unexpected $end
//  Artem Rodygin           2007-12-27      new-659: Set default language
//  Artem Rodygin           2008-03-27      bug-688: Short PHP tags should not be used.
//  Artem Rodygin           2008-10-29      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2008-11-18      new-762: Forward logged in user to the page he has tried to open before authentication.
//  Artem Rodygin           2009-01-12      bug-784: Logged in user must be forwarded to the page he has tried to open before authentication.
//  Artem Rodygin           2009-01-13      new-785: Favorites icon.
//  Artem Rodygin           2009-02-25      bug-792: [SF2635842] Short PHP tags in login.php
//  Artem Rodygin           2009-02-27      bug-794: [SF2643676] Security problem when logout.
//  Artem Rodygin           2009-06-01      new-824: PHP 4 is discontinued.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/accounts.php');
/**#@-*/

define('VAR_REQUEST_CREDENTIALS', 'eTraxis_RequestCredentials');

session_start();

if (get_user_level() != USER_LEVEL_GUEST)
{
    debug_write_log(DEBUG_NOTICE, 'User is already authorized.');
    header('Location: ../records/index.php');
    exit;
}

if (!isset($_SESSION[VAR_REQUEST_CREDENTIALS]))
{
    $_SESSION[VAR_REQUEST_CREDENTIALS] = TRUE;
}

$message =
    '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>' .
    '<link rel="stylesheet" type="text/css" href="../css/etraxis.css"/>' .
    '<link rel="shortcut icon" type="image/x-icon" href="../images/favicon.ico"/>' .
    '<title>eTraxis</title>' .
    '<font color="darkred"><b>%1</b></font>';

switch (AUTH_TYPE)
{
    case AUTH_TYPE_BUILTIN:

        if (!isset($_REQUEST['username']))
        {
            debug_write_log(DEBUG_NOTICE, 'Request username and password.');
?>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<meta name="author" content="Artem Rodygin"/>
<meta name="copyright" content="Copyright (C) 2003-2009 by Artem Rodygin"/>
<link rel="stylesheet" type="text/css" href="../css/etraxis.css"/>
<link rel="shortcut icon" type="image/x-icon" href="../images/favicon.ico"/>
<title>eTraxis</title>
<body bgcolor="#FBFBFB">
<table align="center" height="100%">
<tr><td>
<form target="_parent" method="post" action="login.php">
<fieldset>
<table cellpadding="0" cellspacing="0"><tr>
<td><label for="username"><?php echo(get_html_resource(RES_USERNAME_ID)); ?>:</label></td>
<td><input class="editbox" type="text" name="username" id="username" maxlength="<?php echo(MAX_ACCOUNT_USERNAME); ?>"/></td>
</tr><tr>
<td><label for="password"><?php echo(get_html_resource(RES_PASSWORD_ID)); ?>:</label></td>
<td><input class="password" type="password" name="password" id="password" maxlength="<?php echo(MAX_ACCOUNT_PASSWORD); ?>"/></td>
</tr><tr>
<td align="center" colspan="2"><input class="button" type="submit" value="<?php echo(get_html_resource(RES_LOGIN_ID)); ?>"/></td>
</tr></table>
</fieldset>
</form>
</td></tr>
</table>
</body>
<?php
            exit;
        }
        else
        {
            debug_write_log(DEBUG_NOTICE, 'Retrieve username and password.');

            $username = ustrcut($_REQUEST['username'], MAX_ACCOUNT_USERNAME + 1);
            $passwd   = ustrcut($_REQUEST['password'], MAX_ACCOUNT_PASSWORD);
        }

        break;

    case AUTH_TYPE_BASIC:

        if (!isset($_SERVER['PHP_AUTH_USER']) || $_SESSION[VAR_REQUEST_CREDENTIALS])
        {
            debug_write_log(DEBUG_NOTICE, 'Request username and password.');

            header('WWW-Authenticate: Basic realm="' . get_http_auth_realm() . '"');
            header('HTTP/1.0 401 Unauthorized');

            $_SESSION[VAR_REQUEST_CREDENTIALS] = FALSE;

            echo(ustrprocess($message, get_html_resource(RES_ALERT_USER_NOT_AUTHORIZED_ID)));
            exit;
        }
        else
        {
            debug_write_log(DEBUG_NOTICE, 'Retrieve username and password.');

            $username = ustrcut($_SERVER['PHP_AUTH_USER'], MAX_ACCOUNT_USERNAME + 1);
            $passwd   = ustrcut($_SERVER['PHP_AUTH_PW'],   MAX_ACCOUNT_PASSWORD);
        }

        break;

    default:

        debug_write_log(DEBUG_WARNING, 'Unknown authentication type.');
        echo(ustrprocess($message, get_html_resource(RES_ALERT_UNKNOWN_AUTH_TYPE_ID)));
        exit;
}

unset($_SESSION[VAR_REQUEST_CREDENTIALS]);

debug_write_log(DEBUG_DUMP, '$username = ' . $username);

$pos = ustrpos($username, '\\');

if ($pos !== FALSE)
{
    $username = usubstr($username, $pos + 1);
}

$_SESSION[VAR_ERROR] = NO_ERROR;

if (ustrlen($username) == 0)
{
    debug_write_log(DEBUG_NOTICE, 'Empty form is submitted.');
    $_SESSION[VAR_ERROR] = ERROR_UNAUTHORIZED;
}
else
{
    $_SESSION[VAR_ERROR] = login_user($username, $passwd);
}

if ($_SESSION[VAR_ERROR] == NO_ERROR &&
    is_cookie_saved(COOKIE_URI))
{
    $redirect = try_cookie(COOKIE_URI, '../records/index.php');
    clear_cookie(COOKIE_URI);
}
else
{
    $redirect = '../records/index.php';
}

header('Location: ' . $redirect);

?>
