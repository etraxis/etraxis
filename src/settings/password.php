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

init_page();

if ($_SESSION[VAR_LDAPUSER])
{
    debug_write_log(DEBUG_NOTICE, 'LDAP user cannot change a password.');
    header('Location: index.php');
    exit;
}

$error = NO_ERROR;

// settings form is submitted

if (try_request('submitted') == 'mainform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $passwd1 = ustrcut($_REQUEST['passwd1'], MAX_ACCOUNT_PASSWORD);
    $passwd2 = ustrcut($_REQUEST['passwd2'], MAX_ACCOUNT_PASSWORD);

    $error = password_validate($passwd1, $passwd2);

    if ($error == NO_ERROR)
    {
        $error = password_change($_SESSION[VAR_USERID], $passwd1);
    }

    if ($error == NO_ERROR)
    {
        header('Location: ../index.php');
        exit;
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');
}

// generate breadcrumbs

$xml = '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_SETTINGS_ID) . '</breadcrumb>'
     . '</breadcrumbs>';

// generate tabs

$xml .= '<tabs>'
      . '<tab url="index.php">'                  . get_html_resource(RES_APPEARANCE_ID)      . '</tab>'
      . '<tab url="csv.php">'                    . get_html_resource(RES_CSV_ID)             . '</tab>'
      . '<tab url="password.php" active="true">' . get_html_resource(RES_CHANGE_PASSWORD_ID) . '</tab>';

// generate contents

$xml .= '<content>'
      . '<form name="mainform" action="password.php">'
      . '<group>'
      . '<control name="passwd1">'
      . '<label>' . get_html_resource(RES_PASSWORD_ID) . '</label>'
      . '<passbox maxlen="' . MAX_ACCOUNT_PASSWORD . '"/>'
      . '</control>'
      . '<control name="passwd2">'
      . '<label>' . get_html_resource(RES_PASSWORD_CONFIRM_ID) . '</label>'
      . '<passbox maxlen="' . MAX_ACCOUNT_PASSWORD . '"/>'
      . '</control>'
      . '</group>'
      . '<button default="true">' . get_html_resource(RES_SAVE_ID) . '</button>'
      . '<note>' . ustrprocess(get_html_resource(RES_ALERT_PASSWORD_TOO_SHORT_ID), MIN_PASSWORD_LENGTH) . '</note>'
      . '</form>';

// if some error was specified to display, force an alert

switch ($error)
{
    case ERROR_INCOMPLETE_FORM:
        $xml .= '<script>alert("' . get_js_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '");</script>';
        break;
    case ERROR_PASSWORDS_DO_NOT_MATCH:
        $xml .= "<script>alert('" . get_js_resource(RES_ALERT_PASSWORDS_DO_NOT_MATCH_ID) . "');</script>";
        break;
    case ERROR_PASSWORD_TOO_SHORT:
        $xml .= "<script>alert('" . ustrprocess(get_js_resource(RES_ALERT_PASSWORD_TOO_SHORT_ID), MIN_PASSWORD_LENGTH) . "');</script>";
        break;
    default: ;  // nop
}

$xml .= '</content>'
      . '</tabs>';

echo(xml2html($xml, get_html_resource(RES_SETTINGS_ID)));

?>
