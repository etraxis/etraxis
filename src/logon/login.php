<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2005-2010  Artem Rodygin
//
//  This program is free software: you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation, either version 3 of the License, or
//  (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License
//  along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
//------------------------------------------------------------------------------

/**
 * @package eTraxis
 * @ignore
 */

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/accounts.php');
/**#@-*/

session_start();

if (get_user_level() != USER_LEVEL_GUEST)
{
    debug_write_log(DEBUG_NOTICE, 'User is already authorized.');
    header('Location: ../records/index.php');
    exit;
}

// if some error was specified to display, force an alert

$alert = NULL;

if (isset($_SESSION[VAR_ERROR]))
{
    switch ($_SESSION[VAR_ERROR])
    {
        case NO_ERROR:
            $alert = NULL;
            break;
        case ERROR_UNAUTHORIZED:
            $alert = get_js_resource(RES_ALERT_USER_NOT_AUTHORIZED_ID);
            break;
        case ERROR_UNKNOWN_USERNAME:
            $alert = get_js_resource(RES_ALERT_UNKNOWN_USERNAME_ID);
            break;
        case ERROR_ACCOUNT_DISABLED:
            $alert = get_js_resource(RES_ALERT_ACCOUNT_DISABLED_ID);
            break;
        case ERROR_ACCOUNT_LOCKED:
            $alert = get_js_resource(RES_ALERT_ACCOUNT_LOCKED_ID);
            break;
        case ERROR_UNKNOWN_AUTH_TYPE:
            $alert = get_js_resource(RES_ALERT_UNKNOWN_AUTH_TYPE_ID);
            break;
        default:
            $alert = get_js_resource(RES_ALERT_UNKNOWN_ERROR_ID);
    }

    $_SESSION[VAR_ERROR] = NO_ERROR;
}

// whether we should request for credentials

if (!isset($_SESSION[VAR_REQUEST_CREDENTIALS]))
{
    $_SESSION[VAR_REQUEST_CREDENTIALS] = TRUE;
}

// request for credentials in accordance to configured authentication mode

switch (AUTH_TYPE)
{
    case AUTH_TYPE_BUILTIN:

        $redirect = '../logon/login.php';

        if (!isset($_REQUEST['username']))
        {
            debug_write_log(DEBUG_NOTICE, 'Request username and password.');

            $xml = '<content>'
                 . '<form name="loginform" action="login.php">'
                 . '<group>'
                 . '<control name="username">'
                 . '<label>' . get_html_resource(RES_USERNAME_ID) . '</label>'
                 . '<editbox maxlen="' . MAX_ACCOUNT_USERNAME . '"/>'
                 . '</control>'
                 . '<control name="password">'
                 . '<label>' . get_html_resource(RES_PASSWORD_ID) . '</label>'
                 . '<passbox maxlen="' . MAX_ACCOUNT_PASSWORD . '"/>'
                 . '</control>'
                 . '</group>'
                 . '<button default="true" action="document.loginform.submit()">' . get_html_resource(RES_OK_ID) . '</button>'
                 . '</form>';

            $xml .= '</content>';

            if (!is_null($alert))
            {
                $xml .= '<scriptonreadyitem>'
                      . sprintf('jqAlert("%s","%s","%s");',
                                get_html_resource(RES_ERROR_ID),
                                $alert,
                                get_html_resource(RES_OK_ID))
                   . '</scriptonreadyitem>';
            }

            echo(xml2html($xml, get_html_resource(RES_LOGIN_ID)));
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

        $redirect = '../records/index.php';

        if (!isset($_SERVER['PHP_AUTH_USER']) || $_SESSION[VAR_REQUEST_CREDENTIALS])
        {
            debug_write_log(DEBUG_NOTICE, 'Request username and password.');

            header('WWW-Authenticate: Basic realm="' . get_http_auth_realm() . '"');
            header('HTTP/1.0 401 Unauthorized');

            $_SESSION[VAR_REQUEST_CREDENTIALS] = FALSE;
            $_SESSION[VAR_ERROR]               = ERROR_UNAUTHORIZED;

            echo('<script type="text/javascript" src="../scripts/jquery.js"></script>' .
                 '<script type="text/javascript">' .
                 '$(document).ready(function(){' .
                 '    window.open("../records/index.php", "_parent");' .
                 '});' .
                 '</script>');

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

        $_SESSION[VAR_ERROR] = ERROR_UNKNOWN_AUTH_TYPE;
        header('Location: ../records/index.php');
        exit;
}

unset($_SESSION[VAR_REQUEST_CREDENTIALS]);

// process submitted credentials

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

if ($_SESSION[VAR_ERROR] == NO_ERROR)
{
    $_SESSION[VAR_REQUEST_CREDENTIALS] = FALSE;

    if (is_cookie_saved(COOKIE_URI))
    {
        $redirect = try_cookie(COOKIE_URI, $redirect);
        clear_cookie(COOKIE_URI);
    }
}

header('Location: ' . $redirect);

?>
