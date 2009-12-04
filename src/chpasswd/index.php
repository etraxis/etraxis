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
//  Artem Rodygin           2005-02-17      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-08-15      new-003: Authentication with Active Directory.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-11-16      new-176: Change eTraxis design.
//  Artem Rodygin           2006-01-24      new-204: Active Directory Support functionality (new-003) should be conditionally "compiled".
//  Artem Rodygin           2006-07-14      new-206: User password should not be stored in client cookies.
//  Artem Rodygin           2006-08-07      bug-300: Cannot login with Active Directory credentials.
//  Dmitry Gorev            2007-12-10      new-414: Passwords expiration.
//  Artem Rodygin           2008-11-09      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-04-13      new-814: Password expiration should be turnable off.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/accounts.php');
/**#@-*/

init_page();

if ($_SESSION[VAR_LDAPUSER])
{
    debug_write_log(DEBUG_NOTICE, 'Password of Active Directory account cannot be changed.');
    header('Location: ../index.php');
    exit;
}

if (try_request('submitted') == 'mainform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $passwd1 = ustrcut($_REQUEST['passwd1'], MAX_ACCOUNT_PASSWORD);
    $passwd2 = ustrcut($_REQUEST['passwd2'], MAX_ACCOUNT_PASSWORD);

    $error = password_validate($passwd1, $passwd2);

    if ($error == NO_ERROR)
    {
        $error = password_change($_SESSION[VAR_USERID], $passwd1);

        if ($error == NO_ERROR)
        {
            header('Location: ../index.php');
            exit;
        }
    }

    switch ($error)
    {
        case ERROR_INCOMPLETE_FORM:
            $alert = get_js_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID);
            break;
        case ERROR_PASSWORDS_DO_NOT_MATCH:
            $alert = get_js_resource(RES_ALERT_PASSWORDS_DO_NOT_MATCH_ID);
            break;
        case ERROR_PASSWORD_TOO_SHORT:
            $alert = ustrprocess(get_js_resource(RES_ALERT_PASSWORD_TOO_SHORT_ID), MIN_PASSWORD_LENGTH);
            break;
        default:
            $alert = NULL;
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');
}

$xml = '<page' . gen_xml_page_header(get_html_resource(RES_CHANGE_PASSWORD_ID), isset($alert) ? $alert : NULL, 'mainform.passwd1') . '>'
     . gen_xml_menu()
     . '<path>'
     . '<pathitem url="index.php">' . get_html_resource(RES_CHANGE_PASSWORD_ID) . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<form name="mainform" action="index.php">'
     . '<group>'
     . '<passbox label="' . get_html_resource(RES_PASSWORD_ID)         . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="passwd1" size="' . HTML_EDITBOX_SIZE_MEDIUM . '" maxlen="' . MAX_ACCOUNT_PASSWORD . '"></passbox>'
     . '<passbox label="' . get_html_resource(RES_PASSWORD_CONFIRM_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="passwd2" size="' . HTML_EDITBOX_SIZE_MEDIUM . '" maxlen="' . MAX_ACCOUNT_PASSWORD . '"></passbox>'
     . '</group>'
     . '<button name="ok" default="true">' . get_html_resource(RES_OK_ID) . '</button>';

if ((PASSWORD_EXPIRATION == 0) ||
    ($_SESSION[VAR_PASSWD_EXPIRE] + PASSWORD_EXPIRATION * SECS_IN_DAY > time()))
{
    $xml .= '<button name="cancel" url="javascript:history.back();">'
          . get_html_resource(RES_CANCEL_ID)
          . '</button>';
}

$xml .= '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID)                                   . '</note>'
      . '<note>' . ustrprocess(get_html_resource(RES_ALERT_PASSWORD_TOO_SHORT_ID), MIN_PASSWORD_LENGTH) . '</note>'
      . '</form>'
      . '</content>'
      . '</page>';

echo(xml2html($xml));

?>
