<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2010  Artem Rodygin
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

// check that requested account exists

$id      = ustr2int(try_request('id'));
$account = account_find($id);

if (!$account)
{
    debug_write_log(DEBUG_NOTICE, 'Account cannot be found.');
    header('Location: index.php');
    exit;
}

// add to/remove from selected groups

if (try_request('submitted') == 'othersform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted (adding to new groups).');

    if (isset($_REQUEST['groups']))
    {
        foreach ($_REQUEST['groups'] as $group)
        {
            group_membership_add($group, $id);
        }
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, 'No groups are selected.');
    }
}
elseif (try_request('submitted') == 'groupsform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted (removing from selected groups).');

    if (isset($_REQUEST['groups']))
    {
        foreach ($_REQUEST['groups'] as $group)
        {
            group_membership_remove($group, $id);
        }
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, 'No groups are selected.');
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');
}

// page's title

$title = ustrprocess(get_html_resource(RES_ACCOUNT_X_ID), ustr2html(account_get_username($account['username'], FALSE)));

// generate breadcrumbs and tabs

$xml = '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_ACCOUNTS_ID) . '</breadcrumb>'
     . '<breadcrumb url="view.php?id=' . $id . '">' . $title . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<tabs>'
     . '<tab url="view.php?id='   . $id . '"><i>'            . ustr2html($account['fullname'])      . '</i></tab>'
     . '<tab url="groups.php?id=' . $id . '" active="true">' . get_html_resource(RES_MEMBERSHIP_ID) . '</tab>'
     . '<content>'
     . '<dual>';

// generate left side

$xml .= '<dualleft>'
      . '<form name="othersform" action="groups.php?id=' . $id . '">'
      . '<group title="' . get_html_resource(RES_OTHERS_ID) . '">'
      . '<control name="groups[]">'
      . '<listbox size="10">';

$rs = dal_query('accounts/glist2.sql', $id);

while (($row = $rs->fetch()))
{
    $xml .= '<listitem value="' . $row['group_id'] . '">'
          . ustr2html($row['group_name'])
          . '</listitem>';
}

$xml .= '</listbox>'
      . '</control>'
      . '</group>'
      . '</form>'
      . '</dualleft>';

// generate right side

$xml .= '<dualright>'
      . '<form name="groupsform" action="groups.php?id=' . $id . '">'
      . '<group title="' . get_html_resource(RES_GLOBAL_GROUPS_ID) . '">'
      . '<control name="groups[]">'
      . '<listbox size="10">';

$rs = dal_query('accounts/glist.sql', $id);

while (($row = $rs->fetch()))
{
    $xml .= '<listitem value="' . $row['group_id'] . '">'
          . ustr2html($row['group_name'])
          . '</listitem>';
}

$xml .= '</listbox>'
      . '</control>'
      . '</group>'
      . '</form>'
      . '</dualright>';

// generate buttons

$xml .= '<button action="document.othersform.submit()">%gt;%gt;</button>'
      . '<br/>'
      . '<button action="document.groupsform.submit()">%lt;%lt;</button>';

$xml .= '</dual>'
      . '</content>'
      . '</tabs>';

echo(xml2html($xml, $title));

?>
