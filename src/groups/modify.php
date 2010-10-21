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
require_once('../dbo/groups.php');
/**#@-*/

init_page();

$error = NO_ERROR;

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
    debug_write_log(DEBUG_NOTICE, 'Group cannot be found.');
    header('Location: index.php');
    exit;
}

if (!$group['is_global'])
{
    debug_write_log(DEBUG_NOTICE, 'Group must be global.');
    header('Location: index.php');
    exit;
}

// changed group has been submitted

if (try_request('submitted') == 'mainform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $group_name  = ustrcut($_REQUEST['group_name'],  MAX_GROUP_NAME);
    $description = ustrcut($_REQUEST['description'], MAX_GROUP_DESCRIPTION);

    $error = group_validate($group_name);

    if ($error == NO_ERROR)
    {
        $error = group_modify($id, 0, $group_name, $description);

        if ($error == NO_ERROR)
        {
            header('Location: view.php?id=' . $id);
            exit;
        }
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $group_name  = $group['group_name'];
    $description = $group['description'];
}

// page's title

$title = ustrprocess(get_html_resource(RES_GROUP_X_ID), ustr2html($group['group_name']));

// generate page

$xml = '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_GLOBAL_GROUPS_ID) . '</breadcrumb>'
     . '<breadcrumb url="view.php?id=' . $id . '">' . $title . '</breadcrumb>'
     . '<breadcrumb url="modify.php?id=' . $id . '">' . get_html_resource(RES_MODIFY_ID) . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<content>'
     . '<form name="mainform" action="modify.php?id=' . $id . '">'
     . '<group title="' . get_html_resource(RES_GROUP_INFO_ID) . '">'
     . '<control name="group_name" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
     . '<label>' . get_html_resource(RES_GROUP_NAME_ID) . '</label>'
     . '<editbox maxlen="' . MAX_GROUP_NAME . '">' . ustr2html($group_name) . '</editbox>'
     . '</control>'
     . '<control name="description">'
     . '<label>' . get_html_resource(RES_DESCRIPTION_ID) . '</label>'
     . '<editbox maxlen="' . MAX_GROUP_DESCRIPTION . '">' . ustr2html($description) . '</editbox>'
     . '</control>'
     . '</group>'
     . '<button default="true">'                . get_html_resource(RES_OK_ID)     . '</button>'
     . '<button url="view.php?id=' . $id . '">' . get_html_resource(RES_CANCEL_ID) . '</button>'
     . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>'
     . '</form>';

// if some error was specified to display, force an alert

switch ($error)
{
    case ERROR_INCOMPLETE_FORM:
        $xml .= '<script>alert("' . get_js_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '");</script>';
        break;
    case ERROR_ALREADY_EXISTS:
        $xml .= '<script>alert("' . get_js_resource(RES_ALERT_GROUP_ALREADY_EXISTS_ID) . '");</script>';
        break;
    default: ;  // nop
}

$xml .= '</content>';

echo(xml2html($xml, $title));

?>
