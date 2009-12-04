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
//  Artem Rodygin           2005-02-26      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-08-01      new-013: UI scenarios should be changed.
//  Artem Rodygin           2005-08-15      new-003: Authentication with Active Directory.
//  Artem Rodygin           2005-08-18      new-036: Groups should be editable without suspending a project.
//  Artem Rodygin           2005-08-23      new-053: All the calls of DAL API functions should be moved to DBO API.
//  Artem Rodygin           2005-08-26      new-058: Global groups should be implemented.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-11-13      bug-177: Multibyte string functions should be used instead of 'eregi' and 'split'.
//  Artem Rodygin           2005-11-17      new-176: Change eTraxis design.
//  Artem Rodygin           2005-11-29      new-187: User controls alignment.
//  Artem Rodygin           2006-01-24      new-204: Active Directory Support functionality (new-003) should be conditionally "compiled".
//  Artem Rodygin           2006-07-14      new-206: User password should not be stored in client cookies.
//  Artem Rodygin           2006-08-07      bug-300: Cannot login with Active Directory credentials.
//  Artem Rodygin           2006-11-18      bug-389: Motorola LDAP server returns "Insufficient rights" error.
//  Artem Rodygin           2006-11-20      new-392: Local users should not be extended with '@eTraxis' when LDAP is disabled.
//  Artem Rodygin           2006-12-27      bug-464: Active Directory user cannot be added into group if local user with the same name exists.
//  Artem Rodygin           2007-09-09      bug-578: Attempt to add non-existing user to some group causes blank page.
//  Daniel Jungbluth        2007-10-08      new-594: [SF1809444] Assigning users to groups via listbox
//  Artem Rodygin           2007-10-09      new-594: [SF1809444] Assigning users to groups via listbox
//  Artem Rodygin           2007-11-26      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2007-12-19      bug-647: PHP Warning: Invalid argument supplied for foreach()
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//  Artem Rodygin           2009-08-15      bug-842: Error: document.lform.accounts is undefined
//  Artem Rodygin           2009-10-12      new-837: Replace "Groups" with "Global groups" in main menu.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/accounts.php');
require_once('../dbo/projects.php');
require_once('../dbo/groups.php');
/**#@-*/

init_page();

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    header('Location: index.php');
    exit;
}

$id    = ustr2int(try_request('id'));
$group = group_find($id);

if (!$group)
{
    debug_write_log(DEBUG_NOTICE, 'Group cannot be found.');
    header('Location: index.php');
    exit;
}

$pid     = ustr2int(try_request('pid'));
$project = project_find($pid);

if ($project && $group['is_global'])
{
    debug_write_log(DEBUG_NOTICE, 'Membership of global group cannot be modified from "Projects" menu.');
    header('Location: gview.php?id=' . $id . '&pid=' . $pid);
    exit;
}

if (try_request('submitted') == 'lform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted (adding new members).');

    if (LDAP_ENABLED && !LDAP_ENUMERATION)
    {
        $accounts = ustrcut($_REQUEST['accounts'], 1000);
        $accounts = ustr_replace("\n", ',', $accounts);

        mb_regex_encoding('UTF-8');
        $names = mb_split(',', $accounts);

        $accounts = NULL;
    }
    else
    {
        $names = try_request('accounts', array());
    }

    foreach ($names as $username)
    {
        $username = trim($username);

        if (ustrlen($username) != 0)
        {
            if (LDAP_ENABLED && !LDAP_ENUMERATION)
            {
                if (usubstr($username, ustrlen($username) - 1, 1) == '@')
                {
                    debug_write_log(DEBUG_NOTICE, 'Found @ at the end of login.');
                    $username = usubstr($username, 0, ustrlen($username) - 1);
                    $account = FALSE;
                }
                else
                {
                    $account = account_find_username($username . ACCOUNT_SUFFIX);
                }
            }
            else
            {
                $account = account_find_username($username);
            }

            if ($account)
            {
                group_membership_add($id, $account['account_id']);
            }
            else
            {
                $account_id = (LDAP_ENABLED ? account_register_ldapuser($username) : NULL);

                if (is_null($account_id))
                {
                    debug_write_log(DEBUG_NOTICE, 'Cannot find Active Directory account.');

                    if (LDAP_ENABLED && !LDAP_ENUMERATION)
                    {
                        $accounts .= $username . "\n";
                    }
                }
                else
                {
                    group_membership_add($id, $account_id);
                }
            }
        }
    }
}
elseif (try_request('submitted') == 'rform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted (removing selected members).');

    if (isset($_REQUEST['accounts']))
    {
        foreach ($_REQUEST['accounts'] as $account)
        {
            group_membership_remove($id, $account);
        }
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, 'No accounts are selected.');
    }

    header('Location: members.php?id=' . $id . ($group['is_global'] ? '&pid=' . $pid : NULL));
    exit;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $accounts = NULL;
}

$xml = '<page' . gen_xml_page_header(ustrprocess(get_html_resource(RES_GROUP_X_ID), ustr2html($group['group_name']))) . '>'
     . gen_xml_menu()
     . '<path>';

if ($group['is_global'])
{
    $xml .= '<pathitem url="../groups/index.php">' . get_html_resource(RES_GLOBAL_GROUPS_ID) . '</pathitem>';
}
else
{
    $xml .= '<pathitem url="index.php">'                                  . get_html_resource(RES_PROJECTS_ID)                                                  . '</pathitem>'
          . '<pathitem url="view.php?id='   . $group['project_id'] . '">' . ustrprocess(get_html_resource(RES_PROJECT_X_ID), ustr2html($group['project_name'])) . '</pathitem>'
          . '<pathitem url="gindex.php?id=' . $group['project_id'] . '">' . get_html_resource(RES_GROUPS_ID)                                                    . '</pathitem>';
}

$xml .= '<pathitem url="gview.php?id='   . $id . ($group['is_global'] ? '&amp;pid=' . $pid : NULL) . '">' . ustrprocess(get_html_resource(RES_GROUP_X_ID), ustr2html($group['group_name'])) . '</pathitem>'
      . '<pathitem url="members.php?id=' . $id . ($group['is_global'] ? '&amp;pid=' . $pid : NULL) . '">' . get_html_resource(RES_MEMBERSHIP_ID)                                            . '</pathitem>'
      . '</path>'
      . '<content>'
      . '<dualbox>'
      . '<dualleft action="members.php?id=' . $id . ($group['is_global'] ? '&amp;pid=' . $pid : NULL) . '">'
      . '<group title="' . get_html_resource(RES_OTHERS_ID) . '">';

if (LDAP_ENABLED && !LDAP_ENUMERATION)
{
    $xml .= '<textbox dualbox="true" name="accounts" width="' . HTML_TEXTBOX_WIDTH . '" height="' . HTML_LISTBOX_SIZE . '" maxlen="1000">' . ustr2html($accounts) . '</textbox>';
}
else
{
    $xml .= '<listbox dualbox="true" name="accounts[]" size="' . HTML_LISTBOX_SIZE . '" multiple="true">';

    $list = group_not_amongs($id);

    foreach ($list as $item)
    {
        $xml .= '<listitem value="' . $item['username'] . '">' . $item['fullname'] . ' (' . account_get_username($item['username']) . ')</listitem>';
    }

    $xml .= '</listbox>';
}

$xml .= '</group>'
      . '</dualleft>'
      . '<dualright action="members.php?id=' . $id . ($group['is_global'] ? '&amp;pid=' . $pid : NULL) . '">'
      . '<group title="' . get_html_resource(RES_MEMBERS_ID) . '">'
      . '<listbox dualbox="true" name="accounts[]" size="' . HTML_LISTBOX_SIZE . '" multiple="true">';

$list = group_amongs($id);

while (($item = $list->fetch()))
{
    $xml .= '<listitem value="' . $item['account_id'] . '">' . $item['fullname'] . ' (' . account_get_username($item['username']) . ')</listitem>';
}

$xml .= '</listbox>'
      . '</group>'
      . '</dualright>'
      . '</dualbox>'
      . '<button default="true" url="gview.php?id=' . $id . ($group['is_global'] ? '&amp;pid=' . $pid : NULL) . '">' . get_html_resource(RES_BACK_ID) . '</button>'
      . '</content>'
      . '</page>';

echo(xml2html($xml));

?>
