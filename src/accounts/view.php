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
//  Artem Rodygin           2005-08-01      new-013: UI scenarios should be changed.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-09-13      new-113: When record is being viewed the fields names and values should be aligned by top.
//  Artem Rodygin           2005-10-05      new-145: Remove autofocus from buttons.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-11-16      new-176: Change eTraxis design.
//  Artem Rodygin           2006-07-10      bug-290: LDAP-accounts should not be editable.
//  Artem Rodygin           2006-07-27      new-261: UI design should be adopted to slow connection.
//  Artem Rodygin           2006-10-08      bug-329: /src/accounts/view.php: Global variable $alert was used before it was defined.
//  Artem Rodygin           2006-11-20      new-392: Local users should not be extended with '@eTraxis' when LDAP is disabled.
//  Artem Rodygin           2006-12-11      bug-440: Local users should not be extended with '@eTraxis' when being modified.
//  Artem Rodygin           2008-11-09      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//  Artem Rodygin           2009-10-13      new-838: Disabled buttons would be better grayed out than invisible.
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
    debug_write_log(DEBUG_NOTICE, 'Active Directory account cannot be viewed.');
    header('Location: index.php');
    exit;
}

$xml = '<page' . gen_xml_page_header(ustrprocess(get_html_resource(RES_ACCOUNT_X_ID), ustr2html(account_get_username($account['username'], FALSE)))) . '>'
     . gen_xml_menu()
     . '<path>'
     . '<pathitem url="index.php">'               . get_html_resource(RES_ACCOUNTS_ID) . '</pathitem>'
     . '<pathitem url="view.php?id=' . $id . '">' . ustrprocess(get_html_resource(RES_ACCOUNT_X_ID), ustr2html(account_get_username($account['username'], FALSE))) . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<form name="mainform" action="index.php">'
     . '<group title="' . get_html_resource(RES_ACCOUNT_INFO_ID) . '">'
     . '<text label="' . get_html_resource(RES_USERNAME_ID)    . '">' . ustr2html(account_get_username($account['username'], FALSE)) . '</text>'
     . '<text label="' . get_html_resource(RES_FULLNAME_ID)    . '">' . ustr2html($account['fullname'])    . '</text>'
     . '<text label="' . get_html_resource(RES_EMAIL_ID)       . '">' . ustr2html($account['email'])       . '</text>'
     . '<text label="' . get_html_resource(RES_DESCRIPTION_ID) . '">' . ustr2html($account['description']) . '</text>'
     . '<text label="' . get_html_resource(RES_RIGHTS_ID)      . '">' . get_html_resource($account['is_admin'] ? RES_ADMINISTRATOR_ID : RES_USER_ID) . '</text>'
     . '<text label="' . get_html_resource(RES_STATUS_ID)      . '">' . get_html_resource(is_account_locked($account['locks_count'], $account['lock_time']) ? RES_LOCKED_ID : ($account['is_disabled'] ? RES_DISABLED_ID : RES_ACTIVE_ID)) . '</text>'
     . '</group>'
     . '<button name="back" default="true">'      . get_html_resource(RES_BACK_ID)   . '</button>'
     . '<button url="modify.php?id=' . $id . '">' . get_html_resource(RES_MODIFY_ID) . '</button>';

if (is_account_removable($id))
{
    $xml .= '<button url="delete.php?id=' . $id . '" prompt="' . get_html_resource(RES_CONFIRM_DELETE_ACCOUNT_ID) . '">'
          . get_html_resource(RES_DELETE_ID)
          . '</button>';
}
else
{
    $xml .= '<button disabled="true">' . get_html_resource(RES_DELETE_ID) . '</button>';
}

$xml .= '</form>'
      . '</content>'
      . '</page>';

echo(xml2html($xml));

?>
