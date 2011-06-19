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
/**#@-*/

init_page(LOAD_TAB);

if ($_SESSION[VAR_LDAPUSER])
{
    debug_write_log(DEBUG_NOTICE, 'LDAP user cannot change a password.');
    exit;
}

$error = NO_ERROR;

// settings form is submitted

if (try_request('submitted') == 'passwordform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $password1 = ustrcut($_REQUEST['password1'], MAX_ACCOUNT_PASSWORD);
    $password2 = ustrcut($_REQUEST['password2'], MAX_ACCOUNT_PASSWORD);

    $error = password_validate($password1, $password2);

    if ($error == NO_ERROR)
    {
        $error = password_change($_SESSION[VAR_USERID], $password1);
    }

    switch ($error)
    {
        case NO_ERROR:
            header('HTTP/1.0 200 OK');
            break;

        case ERROR_INCOMPLETE_FORM:
            send_http_error(get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID));
            break;

        case ERROR_PASSWORDS_DO_NOT_MATCH:
            send_http_error(get_html_resource(RES_ALERT_PASSWORDS_DO_NOT_MATCH_ID));
            break;

        case ERROR_PASSWORD_TOO_SHORT:
            send_http_error(ustrprocess(get_html_resource(RES_ALERT_PASSWORD_TOO_SHORT_ID), MIN_PASSWORD_LENGTH));
            break;

        default:
            send_http_error(get_html_resource(RES_ALERT_UNKNOWN_ERROR_ID));
    }

    exit;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');
}

// local JS functions

$resTitle   = get_js_resource(RES_SETTINGS_ID);
$resError   = get_js_resource(RES_ERROR_ID);
$resMessage = get_js_resource(RES_ALERT_SUCCESSFULLY_SAVED_ID);
$resOK      = get_js_resource(RES_OK_ID);

$uri = ustr2html(try_cookie(COOKIE_URI, '../settings/index.php'));
clear_cookie(COOKIE_URI);

$xml = <<<JQUERY
<script>

function passwordRefresh ()
{
    window.open("{$uri}", "_parent");
}

function passwordSuccess ()
{
    jqAlert("{$resTitle}", "{$resMessage}", "{$resOK}", "passwordRefresh()");
}

function passwordError (XMLHttpRequest)
{
    jqAlert("{$resError}", XMLHttpRequest.responseText, "{$resOK}");
}

</script>
JQUERY;

// generate contents

$xml .= '<form name="passwordform" action="password.php" success="passwordSuccess" error="passwordError">'
      . '<group>'
      . '<control name="password1">'
      . '<label>' . get_html_resource(RES_PASSWORD_ID) . '</label>'
      . '<passbox maxlen="' . MAX_ACCOUNT_PASSWORD . '"/>'
      . '</control>'
      . '<control name="password2">'
      . '<label>' . get_html_resource(RES_PASSWORD_CONFIRM_ID) . '</label>'
      . '<passbox maxlen="' . MAX_ACCOUNT_PASSWORD . '"/>'
      . '</control>'
      . '</group>'
      . '<button default="true">' . get_html_resource(RES_SAVE_ID) . '</button>'
      . '<note>' . ustrprocess(get_html_resource(RES_ALERT_PASSWORD_TOO_SHORT_ID), MIN_PASSWORD_LENGTH) . '</note>'
      . '</form>';

echo(xml2html($xml));

?>
