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
//  Artem Rodygin           2005-08-01      new-013: UI scenarios should be changed.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-11-16      new-176: Change eTraxis design.
//  Artem Rodygin           2006-07-10      bug-290: LDAP-accounts should not be editable.
//  Artem Rodygin           2006-07-14      new-206: User password should not be stored in client cookies.
//  Artem Rodygin           2006-07-27      new-261: UI design should be adopted to slow connection.
//  Artem Rodygin           2006-08-07      bug-300: Cannot login with Active Directory credentials.
//  Artem Rodygin           2006-11-20      new-392: Local users should not be extended with '@eTraxis' when LDAP is disabled.
//  Artem Rodygin           2006-12-11      bug-440: Local users should not be extended with '@eTraxis' when being modified.
//  Artem Rodygin           2008-11-09      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-03-12      new-800: Password should expire when changed by admin.
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

if (try_request('submitted') == 'mainform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $username    = ustrcut($_REQUEST['username'],    MAX_ACCOUNT_USERNAME);
    $fullname    = ustrcut($_REQUEST['fullname'],    MAX_ACCOUNT_FULLNAME);
    $email       = ustrcut($_REQUEST['email'],       MAX_ACCOUNT_EMAIL);
    $description = ustrcut($_REQUEST['description'], MAX_ACCOUNT_DESCRIPTION);
    $passwd1     = ustrcut($_REQUEST['passwd1'],     MAX_ACCOUNT_PASSWORD);
    $passwd2     = ustrcut($_REQUEST['passwd2'],     MAX_ACCOUNT_PASSWORD);
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

            header('Location: view.php?id=' . $id);
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

    $username    = account_get_username($account['username'], FALSE);
    $fullname    = $account['fullname'];
    $email       = $account['email'];
    $description = $account['description'];
    $passwd1     = $account['passwd'];
    $passwd2     = $account['passwd'];
    $is_admin    = $account['is_admin'];
    $is_disabled = $account['is_disabled'];
    $is_locked   = is_account_locked($account['locks_count'], $account['lock_time']);
}

$xml = '<page' . gen_xml_page_header(ustrprocess(get_html_resource(RES_ACCOUNT_X_ID), ustr2html(account_get_username($account['username'], FALSE))), isset($alert) ? $alert : NULL, 'mainform.username') . '>'
     . gen_xml_menu()
     . '<path>'
     . '<pathitem url="index.php">'                 . get_html_resource(RES_ACCOUNTS_ID)                                                                             . '</pathitem>'
     . '<pathitem url="view.php?id='   . $id . '">' . ustrprocess(get_html_resource(RES_ACCOUNT_X_ID), ustr2html(account_get_username($account['username'], FALSE))) . '</pathitem>'
     . '<pathitem url="modify.php?id=' . $id . '">' . get_html_resource(RES_MODIFY_ID)                                                                               . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<form name="mainform" action="modify.php?id=' . $id . '">'
     . '<group title="' . get_html_resource(RES_ACCOUNT_INFO_ID) . '">'
     . '<editbox label="' . get_html_resource(RES_USERNAME_ID)         . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="username" size="' . HTML_EDITBOX_SIZE_MEDIUM  . '" maxlen="' . MAX_ACCOUNT_USERNAME    . '">' . ustr2html($username)    . '</editbox>'
     . '<editbox label="' . get_html_resource(RES_FULLNAME_ID)         . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="fullname" size="' . HTML_EDITBOX_SIZE_MEDIUM  . '" maxlen="' . MAX_ACCOUNT_FULLNAME    . '">' . ustr2html($fullname)    . '</editbox>'
     . '<editbox label="' . get_html_resource(RES_EMAIL_ID)            . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="email" size="' . HTML_EDITBOX_SIZE_MEDIUM     . '" maxlen="' . MAX_ACCOUNT_EMAIL       . '">' . ustr2html($email)       . '</editbox>'
     . '<editbox label="' . get_html_resource(RES_DESCRIPTION_ID)      . '"                                                        name="description" size="' . HTML_EDITBOX_SIZE_LONG . '" maxlen="' . MAX_ACCOUNT_DESCRIPTION . '">' . ustr2html($description) . '</editbox>'
     . '<passbox label="' . get_html_resource(RES_PASSWORD_ID)         . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="passwd1" size="' . HTML_EDITBOX_SIZE_MEDIUM   . '" maxlen="' . MAX_ACCOUNT_PASSWORD    . '">' . str_repeat('*', MAX_ACCOUNT_PASSWORD) . '</passbox>'
     . '<passbox label="' . get_html_resource(RES_PASSWORD_CONFIRM_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="passwd2" size="' . HTML_EDITBOX_SIZE_MEDIUM   . '" maxlen="' . MAX_ACCOUNT_PASSWORD    . '">' . str_repeat('*', MAX_ACCOUNT_PASSWORD) . '</passbox>'
     . '<checkbox name="is_admin"'    . ($is_admin ? ' checked="true">' : '>')    . get_html_resource(RES_ADMINISTRATOR_ID) . '</checkbox>'
     . '<checkbox name="is_disabled"' . ($is_disabled ? ' checked="true">' : '>') . get_html_resource(RES_DISABLED_ID)      . '</checkbox>'
     . '<checkbox name="is_locked"'   . (is_account_locked($account['locks_count'], $account['lock_time']) ? NULL : ' disabled="true"') . ($is_locked ? ' checked="true"' : NULL) . '>' . get_html_resource(RES_LOCKED_ID) . '</checkbox>'
     . '</group>'
     . '<button default="true">'                . get_html_resource(RES_OK_ID)     . '</button>'
     . '<button url="view.php?id=' . $id . '">' . get_html_resource(RES_CANCEL_ID) . '</button>'
     . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID)                                   . '</note>'
     . '<note>' . ustrprocess(get_html_resource(RES_ALERT_PASSWORD_TOO_SHORT_ID), MIN_PASSWORD_LENGTH) . '</note>'
     . '</form>'
     . '</content>'
     . '</page>';

echo(xml2html($xml));

?>
