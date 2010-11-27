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

$error = NO_ERROR;

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    header('Location: index.php');
    exit;
}

// check that requested account exists

$id      = ustr2int(try_request('id'));
$account = account_find($id);

if (!$account)
{
    debug_write_log(DEBUG_NOTICE, 'Account cannot be found.');
    header('Location: index.php');
    exit;
}

if ($account['is_ldapuser'])
{
    debug_write_log(DEBUG_NOTICE, 'Active Directory account cannot be modified.');
    header('Location: index.php');
    exit;
}

// changed account has been submitted

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
    $is_locked   = isset($_REQUEST['is_locked']);

    $error = account_validate($username,
                              $fullname,
                              $email,
                              $passwd1,
                              $passwd2);

    if ($error == NO_ERROR)
    {
        $error = account_modify($id,
                                $username,
                                $fullname,
                                $email,
                                $description,
                                $is_admin,
                                $is_disabled,
                                ($is_locked ? $account['locks_count'] : 0));

        if ($error == NO_ERROR)
        {
            if ($passwd1 != str_repeat('*', MAX_ACCOUNT_PASSWORD))
            {
                dal_query('accounts/passwd.sql', $id, md5($passwd1), 0);
            }

            if ($locale != $account['locale'])
            {
                locale_change($id, $locale);
            }

            header('Location: view.php?id=' . $id);
            exit;
        }
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $username    = account_get_username($account['username'], FALSE);
    $fullname    = $account['fullname'];
    $email       = $account['email'];
    $description = $account['description'];
    $passwd1     = $account['passwd'];
    $passwd2     = $account['passwd'];
    $locale      = $account['locale'];
    $is_admin    = $account['is_admin'];
    $is_disabled = $account['is_disabled'];
    $is_locked   = is_account_locked($account['locks_count'], $account['lock_time']);
}

// page's title

$title = ustrprocess(get_html_resource(RES_ACCOUNT_X_ID), ustr2html(account_get_username($account['username'], FALSE)));

// generate page

$xml = '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_ACCOUNTS_ID) . '</breadcrumb>'
     . '<breadcrumb url="view.php?id=' . $id . '">' . $title . '</breadcrumb>'
     . '<breadcrumb url="modify.php?id=' . $id . '">' . get_html_resource(RES_MODIFY_ID) . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<content>'
     . '<form name="mainform" action="modify.php?id=' . $id . '">'
     . '<group title="' . get_html_resource(RES_ACCOUNT_INFO_ID) . '">'
     . '<control name="username" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
     . '<label>' . get_html_resource(RES_USERNAME_ID) . '</label>'
     . '<editbox maxlen="' . MAX_ACCOUNT_USERNAME . '">' . ustr2html($username) . '</editbox>'
     . '</control>'
     . '<control name="fullname" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
     . '<label>' . get_html_resource(RES_FULLNAME_ID) . '</label>'
     . '<editbox maxlen="' . MAX_ACCOUNT_FULLNAME . '">' . ustr2html($fullname) . '</editbox>'
     . '</control>'
     . '<control name="email" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
     . '<label>' . get_html_resource(RES_EMAIL_ID) . '</label>'
     . '<editbox maxlen="' . MAX_ACCOUNT_EMAIL . '">' . ustr2html($email) . '</editbox>'
     . '</control>'
     . '<control name="description">'
     . '<label>' . get_html_resource(RES_DESCRIPTION_ID) . '</label>'
     . '<editbox maxlen="' . MAX_ACCOUNT_DESCRIPTION . '">' . ustr2html($description) . '</editbox>'
     . '</control>'
     . '<control name="passwd1" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
     . '<label>' . get_html_resource(RES_PASSWORD_ID) . '</label>'
     . '<passbox maxlen="' . MAX_ACCOUNT_PASSWORD . '">' . str_repeat('*', MAX_ACCOUNT_PASSWORD) . '</passbox>'
     . '</control>'
     . '<control name="passwd2" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
     . '<label>' . get_html_resource(RES_PASSWORD_CONFIRM_ID) . '</label>'
     . '<passbox maxlen="' . MAX_ACCOUNT_PASSWORD . '">' . str_repeat('*', MAX_ACCOUNT_PASSWORD) . '</passbox>'
     . '</control>'
     . '<control name="locale" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
     . '<label>' . get_html_resource(RES_LANGUAGE_ID) . '</label>'
     . '<combobox>';

$supported_locales = get_supported_locales_sorted();

foreach ($supported_locales as $locale_id => $locale_name)
{
    $xml .= ($locale == $locale_id
                ? '<listitem value="' . $locale_id . '" selected="true">'
                : '<listitem value="' . $locale_id . '">')
          . $locale_name
          . '</listitem>';
}

$xml .= '</combobox>'
      . '</control>'
      . '<control name="is_admin">'
      . '<label/>'
      . ($is_admin
            ? '<checkbox checked="true">'
            : '<checkbox>')
      . get_html_resource(RES_ADMINISTRATOR_ID)
      . '</checkbox>'
      . '</control>'
      . '<control name="is_disabled">'
      . '<label/>'
      . ($is_disabled
            ? '<checkbox checked="true">'
            : '<checkbox>')
      . get_html_resource(RES_DISABLED_ID)
      . '</checkbox>'
      . '</control>'
      . (is_account_locked($account['locks_count'], $account['lock_time'])
            ? '<control name="is_locked">'
            : '<control name="is_locked" disabled="true">')
      . '<label/>'
      . ($is_locked
            ? '<checkbox checked="true">'
            : '<checkbox>')
      . get_html_resource(RES_LOCKED_ID)
      . '</checkbox>'
      . '</control>'
      . '</group>'
      . '<button default="true">'                . get_html_resource(RES_OK_ID)     . '</button>'
      . '<button url="view.php?id=' . $id . '">' . get_html_resource(RES_CANCEL_ID) . '</button>'
      . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID)                                   . '</note>'
      . '<note>' . ustrprocess(get_html_resource(RES_ALERT_PASSWORD_TOO_SHORT_ID), MIN_PASSWORD_LENGTH) . '</note>'
      . '</form>'
      . '</content>';

// if some error was specified to display, force an alert

switch ($error)
{
    case ERROR_INCOMPLETE_FORM:
        $xml .= '<scriptonreadyitem>'
              . 'jqAlert("' . get_html_resource(RES_ERROR_ID) . '","' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '","' . get_html_resource(RES_OK_ID) . '");'
              . '</scriptonreadyitem>';
        break;
    case ERROR_INVALID_USERNAME:
        $xml .= '<scriptonreadyitem>'
              . 'jqAlert("' . get_html_resource(RES_ERROR_ID) . '","' . get_html_resource(RES_ALERT_INVALID_USERNAME_ID) . '","' . get_html_resource(RES_OK_ID) . '");'
              . '</scriptonreadyitem>';
        break;
    case ERROR_ALREADY_EXISTS:
        $xml .= '<scriptonreadyitem>'
              . 'jqAlert("' . get_html_resource(RES_ERROR_ID) . '","' . get_html_resource(RES_ALERT_ACCOUNT_ALREADY_EXISTS_ID) . '","' . get_html_resource(RES_OK_ID) . '");'
              . '</scriptonreadyitem>';
        break;
    case ERROR_INVALID_EMAIL:
        $xml .= '<scriptonreadyitem>'
              . 'jqAlert("' . get_html_resource(RES_ERROR_ID) . '","' . get_html_resource(RES_ALERT_INVALID_EMAIL_ID) . '","' . get_html_resource(RES_OK_ID) . '");'
              . '</scriptonreadyitem>';
        break;
    case ERROR_PASSWORDS_DO_NOT_MATCH:
        $xml .= '<scriptonreadyitem>'
              . 'jqAlert("' . get_html_resource(RES_ERROR_ID) . '","' . get_html_resource(RES_ALERT_PASSWORDS_DO_NOT_MATCH_ID) . '","' . get_html_resource(RES_OK_ID) . '");'
              . '</scriptonreadyitem>';
        break;
    case ERROR_PASSWORD_TOO_SHORT:
        $xml .= '<scriptonreadyitem>'
              . 'jqAlert("' . get_html_resource(RES_ERROR_ID) . '","' . ustrprocess(get_html_resource(RES_ALERT_PASSWORD_TOO_SHORT_ID), MIN_PASSWORD_LENGTH) . '","' . get_html_resource(RES_OK_ID) . '");'
              . '</scriptonreadyitem>';
        break;
    default: ;  // nop
}

echo(xml2html($xml, $title));

?>
