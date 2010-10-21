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
require_once('../dbo/groups.php');
/**#@-*/

init_page();

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    header('Location: index.php');
    exit;
}

// check that requested group exists

$id    = ustr2int(try_request('id'));
$group = group_find($id);

if (!$group)
{
    debug_write_log(DEBUG_NOTICE, 'Account cannot be found.');
    header('Location: index.php');
    exit;
}

if (!$group['is_global'])
{
    debug_write_log(DEBUG_NOTICE, 'Group must be global.');
    header('Location: index.php');
    exit;
}

// add/remove selected accounts

if (try_request('submitted') == 'othersform')
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
elseif (try_request('submitted') == 'membersform')
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
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $accounts = NULL;
}

// page's title

$title = ustrprocess(get_html_resource(RES_GROUP_X_ID), ustr2html($group['group_name']));

// generate breadcrumbs and tabs

$xml = '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_GLOBAL_GROUPS_ID) . '</breadcrumb>'
     . '<breadcrumb url="view.php?id=' . $id . '">' . $title . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<tabs>'
     . '<tab url="view.php?id='    . $id . '"><i>'            . ustr2html($group['group_name'])      . '</i></tab>'
     . '<tab url="members.php?id=' . $id . '" active="true">' . get_html_resource(RES_MEMBERSHIP_ID) . '</tab>'
     . '<content>'
     . '<dual>';

// generate left side

$xml .= '<dualleft>'
      . '<form name="othersform" action="members.php?id=' . $id . '">'
      . '<group title="' . get_html_resource(RES_OTHERS_ID) . '">';

if (LDAP_ENABLED && !LDAP_ENUMERATION)
{
    $xml .= '<control name="accounts">'
          . '<textbox rows="10" maxlen="1000">'
          . ustr2html($accounts)
          . '</textbox>'
          . '</control>';
}
else
{
    $xml .= '<control name="accounts[]">'
          . '<listbox size="10">';

    $list = group_not_amongs($id);

    foreach ($list as $item)
    {
        $xml .= '<listitem value="' . $item['username'] . '">'
              . ustr2html(sprintf('%s (%s)', $item['fullname'], account_get_username($item['username'])))
              . '</listitem>';
    }

    $xml .= '</listbox>'
          . '</control>';
}

$xml .= '</group>'
      . '</form>'
      . '</dualleft>';

// generate right side

$xml .= '<dualright>'
      . '<form name="membersform" action="members.php?id=' . $id . '">'
      . '<group title="' . get_html_resource(RES_MEMBERS_ID) . '">'
      . '<control name="accounts[]">'
      . '<listbox size="10">';

$rs = group_amongs($id);

while (($row = $rs->fetch()))
{
    $xml .= '<listitem value="' . $row['account_id'] . '">'
          . ustr2html(sprintf('%s (%s)', $row['fullname'], account_get_username($row['username'])))
          . '</listitem>';
}

$xml .= '</listbox>'
      . '</control>'
      . '</group>'
      . '</form>'
      . '</dualright>';

// generate buttons

$xml .= '<button action="document.othersform.submit()">%gt;%gt;</button>'
      . '<button action="document.membersform.submit()">%lt;%lt;</button>';

$xml .= '</dual>'
      . '</content>'
      . '</tabs>';

echo(xml2html($xml, $title));

?>
