<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2005-2011  Artem Rodygin
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
require __DIR__ . '/../../google2fa/vendor/autoload.php';
/**#@-*/

@session_start();

if (get_user_level() != USER_LEVEL_GUEST)
{
    debug_write_log(DEBUG_NOTICE, 'User is already authorized.');
    header('Location: ../records/index.php');
    exit;
}

// process submitted credentials

$username = ustrcut($_REQUEST['username'], MAX_ACCOUNT_USERNAME + 1);
$password = ustrcut($_REQUEST['password'], MAX_ACCOUNT_PASSWORD);

debug_write_log(DEBUG_DUMP, '$username = ' . $username);

$pos = ustrpos($username, '\\');

if ($pos !== FALSE)
{
    $username = usubstr($username, $pos + 1);
}

if (ustrlen($username) == 0)
{
    debug_write_log(DEBUG_NOTICE, 'Empty form is submitted.');
    send_http_error(get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID));
}
else
{
    $error = login_user($username, $password);

    switch ($error)
    {
        case NO_ERROR:

            $rs = dal_query('accounts/get2fa.sql', $_SESSION[VAR_USERID]);
            $secret = $rs->fetch('google2fa_secret');

            if ($secret != null)
            {
                $key = ustrcut($_REQUEST['code'], 6);
                $google2fa = new \PragmaRX\Google2FA\Google2FA();

                if (!$google2fa->verifyKey($secret, $key))
                {
                    send_http_error('The entered 2FA code is wrong.');

                    clear_cookie(COOKIE_AUTH_USERID);
                    clear_cookie(COOKIE_AUTH_TOKEN);

                    close_session();
                }
                else
                {
                    header('HTTP/1.0 200 OK');
                }
            }
            else
            {
                header('HTTP/1.0 200 OK');
            }

            break;

        case ERROR_UNKNOWN_USERNAME:
            send_http_error(get_html_resource(RES_ALERT_UNKNOWN_USERNAME_ID));
            break;

        case ERROR_ACCOUNT_DISABLED:
            send_http_error(get_html_resource(RES_ALERT_ACCOUNT_DISABLED_ID));
            break;

        case ERROR_ACCOUNT_LOCKED:
            send_http_error(get_html_resource(RES_ALERT_ACCOUNT_LOCKED_ID));
            break;

        default:
            send_http_error(get_html_resource(RES_ALERT_UNKNOWN_ERROR_ID));
    }
}

?>
