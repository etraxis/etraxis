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
//  Artem Rodygin           2005-02-13      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-08-18      new-030: UI language should be set for each user separately.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-11-16      new-176: Change eTraxis design.
//  Artem Rodygin           2006-07-27      new-261: UI design should be adopted to slow connection.
//  Artem Rodygin           2006-10-08      bug-327: /src/accounts/create.php: Global variable $locale_info was used before it was defined.
//  Artem Rodygin           2008-11-09      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/accounts.php');
/**#@-*/

init_page();

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    header('Location: index.php');
    exit;
}

if (try_request('submitted') == 'mainform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $username    = ustrcut($_REQUEST['username'],    MAX_ACCOUNT_USERNAME);
    $fullname    = ustrcut($_REQUEST['fullname'],    MAX_ACCOUNT_FULLNAME);
    $email       = ustrcut($_REQUEST['email'],       MAX_ACCOUNT_EMAIL);
    $description = ustrcut($_REQUEST['description'], MAX_ACCOUNT_DESCRIPTION);
    $passwd1     = ustrcut($_REQUEST['passwd1'],     MAX_ACCOUNT_PASSWORD);
    $passwd2     = ustrcut($_REQUEST['passwd2'],     MAX_ACCOUNT_PASSWORD);
    $locale      = ustr2int($_REQUEST['locale']);
    $is_admin    = isset($_REQUEST['is_admin']);
    $is_disabled = isset($_REQUEST['is_disabled']);

    $error = account_validate($username,
                              $fullname,
                              $email,
                              $passwd1,
                              $passwd2);

    if ($error == NO_ERROR)
    {
        $error = account_create($username,
                                $fullname,
                                $email,
                                $passwd1,
                                $description,
                                $is_admin,
                                $is_disabled,
                                $locale);

        if ($error == NO_ERROR)
        {
            header('Location: index.php');
            exit;
        }
    }

    switch ($error)
    {
        case ERROR_INCOMPLETE_FORM:
            $alert = get_js_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID);
            break;
        case ERROR_INVALID_USERNAME:
            $alert = get_js_resource(RES_ALERT_INVALID_USERNAME_ID);
            break;
        case ERROR_ALREADY_EXISTS:
            $alert = get_js_resource(RES_ALERT_ACCOUNT_ALREADY_EXISTS_ID);
            break;
        case ERROR_INVALID_EMAIL:
            $alert = get_js_resource(RES_ALERT_INVALID_EMAIL_ID);
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

    $username    = NULL;
    $fullname    = NULL;
    $email       = NULL;
    $description = NULL;
    $locale      = LANG_DEFAULT;
    $is_admin    = FALSE;
    $is_disabled = FALSE;
}

$xml = '<page' . gen_xml_page_header(get_html_resource(RES_NEW_ACCOUNT_ID), isset($alert) ? $alert : NULL, 'mainform.username') . '>'
     . gen_xml_menu()
     . '<path>'
     . '<pathitem url="index.php">'  . get_html_resource(RES_ACCOUNTS_ID)    . '</pathitem>'
     . '<pathitem url="create.php">' . get_html_resource(RES_NEW_ACCOUNT_ID) . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<form name="mainform" action="create.php">'
     . '<group title="' . get_html_resource(RES_ACCOUNT_INFO_ID) . '">'
     . '<editbox label="' . get_html_resource(RES_USERNAME_ID)         . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="username"    size="' . HTML_EDITBOX_SIZE_MEDIUM . '" maxlen="' . MAX_ACCOUNT_USERNAME    . '">' . ustr2html($username)    . '</editbox>'
     . '<editbox label="' . get_html_resource(RES_FULLNAME_ID)         . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="fullname"    size="' . HTML_EDITBOX_SIZE_MEDIUM . '" maxlen="' . MAX_ACCOUNT_FULLNAME    . '">' . ustr2html($fullname)    . '</editbox>'
     . '<editbox label="' . get_html_resource(RES_EMAIL_ID)            . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="email"       size="' . HTML_EDITBOX_SIZE_MEDIUM . '" maxlen="' . MAX_ACCOUNT_EMAIL       . '">' . ustr2html($email)       . '</editbox>'
     . '<editbox label="' . get_html_resource(RES_DESCRIPTION_ID)      . '"                                                        name="description" size="' . HTML_EDITBOX_SIZE_LONG   . '" maxlen="' . MAX_ACCOUNT_DESCRIPTION . '">' . ustr2html($description) . '</editbox>'
     . '<passbox label="' . get_html_resource(RES_PASSWORD_ID)         . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="passwd1"     size="' . HTML_EDITBOX_SIZE_MEDIUM . '" maxlen="' . MAX_ACCOUNT_PASSWORD    . '"/>'
     . '<passbox label="' . get_html_resource(RES_PASSWORD_CONFIRM_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="passwd2"     size="' . HTML_EDITBOX_SIZE_MEDIUM . '" maxlen="' . MAX_ACCOUNT_PASSWORD    . '"/>'
     . '<combobox label="' . get_html_resource(RES_LANGUAGE_ID) . '" name="locale">';

global $locale_info;
$supported_locales = array_keys($locale_info);

foreach ($supported_locales as $item)
{
    $xml .= '<listitem value="' . $item . ($locale == $item ? '" selected="true">' : '">')
          . get_html_resource(RES_LOCALE_ID, $item)
          . '</listitem>';
}

$xml .= '</combobox>'
      . '<checkbox name="is_admin"'    . ($is_admin    ? ' checked="true">' : '>') . get_html_resource(RES_ADMINISTRATOR_ID) . '</checkbox>'
      . '<checkbox name="is_disabled"' . ($is_disabled ? ' checked="true">' : '>') . get_html_resource(RES_DISABLED_ID)      . '</checkbox>'
      . '</group>'
      . '<button default="true">'  . get_html_resource(RES_OK_ID)     . '</button>'
      . '<button url="index.php">' . get_html_resource(RES_CANCEL_ID) . '</button>'
      . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID)                                   . '</note>'
      . '<note>' . ustrprocess(get_html_resource(RES_ALERT_PASSWORD_TOO_SHORT_ID), MIN_PASSWORD_LENGTH) . '</note>'
      . '</form>'
      . '</content>'
      . '</page>';

echo(xml2html($xml));

?>
