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
require_once('../dbo/projects.php');
/**#@-*/

init_page();

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    header('Location: index.php');
    exit;
}

// check that requested project exists

$pid     = ustr2int(try_request('pid'));
$project = project_find($pid);

if (!$project)
{
    debug_write_log(DEBUG_NOTICE, 'Project cannot be found.');
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

// page's title

$title = ustrprocess(get_html_resource(RES_GROUP_X_ID), ustr2html($group['group_name']));

// generate breadcrumbs and tabs

$xml = gen_context_menu('tview.php?id=', 'sview.php?id=', 'fview.php?id=', $pid)
     . '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_PROJECTS_ID) . '</breadcrumb>'
     . '<breadcrumb url="gindex.php?id=' . $pid . '">' . ustrprocess(get_html_resource(RES_PROJECT_X_ID), ustr2html($project['project_name'])) . '</breadcrumb>'
     . '<breadcrumb url="gview.php?pid=' . $pid . '&amp;id=' . $id . '">' . $title . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<tabs>'
     . '<tab url="gview.php?pid='    . $pid . '&amp;id=' . $id . '" active="true"><i>' . ustr2html($group['group_name'])       . '</i></tab>'
     . '<tab url="gmembers.php?pid=' . $pid . '&amp;id=' . $id . '">'                  . get_html_resource(RES_MEMBERSHIP_ID)  . '</tab>'
     . '<tab url="gperms.php?pid='   . $pid . '&amp;id=' . $id . '">'                  . get_html_resource(RES_PERMISSIONS_ID) . '</tab>'
     . '<content>';

// generate buttons

$xml .= '<button url="gindex.php?id=' . $pid . '">' . get_html_resource(RES_BACK_ID) . '</button>';

if (!$group['is_global'])
{
    $xml .= HTML_SPLITTER
          . '<button url="gmodify.php?id=' . $id . '">' . get_html_resource(RES_MODIFY_ID) . '</button>'
          . (is_group_removable($id)
                ? '<button url="gdelete.php?id=' . $id . '" prompt="' . get_html_resource(RES_CONFIRM_DELETE_GROUP_ID) . '">'
                : '<button disabled="false">')
          . get_html_resource(RES_DELETE_ID)
          . '</button>';
}

// generate group information

$xml .= '<group title="' . get_html_resource(RES_GROUP_INFO_ID) . '">'
      . '<text label="' . get_html_resource(RES_GROUP_NAME_ID)  . '">' . ustr2html($group['group_name']) . '</text>'
      . '<text label="' . get_html_resource(RES_GROUP_TYPE_ID)  . '">' . get_html_resource($group['is_global'] ? RES_GLOBAL_ID : RES_LOCAL_ID) . '</text>'
      . '<text label="' . get_html_resource(RES_DESCRIPTION_ID) . '">' . ustr2html($group['description']) . '</text>'
      . '</group>'
      . '</content>'
      . '</tabs>';

echo(xml2html($xml, $title));

?>
