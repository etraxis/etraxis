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

// local JS functions

$resTitle = get_js_resource(RES_ERROR_ID);
$resOK    = get_js_resource(RES_OK_ID);

$uri = try_cookie(COOKIE_URI, '../records/index.php');

$xml = <<<JQUERY
<script>

function loginSuccess ()
{
    window.open("{$uri}", "_parent");
}

function loginError (XMLHttpRequest)
{
    jqAlert("{$resTitle}", XMLHttpRequest.statusText, "{$resOK}");
}

</script>
JQUERY;

// generate contents

$xml .= '<content>'
      . '<form name="loginform" action="login.php" success="loginSuccess" error="loginError">'
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
      . '<button default="true">' . get_html_resource(RES_OK_ID) . '</button>'
      . '</form>'
      . '</content>';

echo(xml2html($xml, get_html_resource(RES_LOGIN_ID)));

?>
