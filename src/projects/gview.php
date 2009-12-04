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
//  Artem Rodygin           2005-08-18      new-036: Groups should be editable without suspending a project.
//  Artem Rodygin           2005-08-25      new-058: Global groups should be implemented.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-09-13      new-113: When record is being viewed the fields names and values should be aligned by top.
//  Artem Rodygin           2005-10-05      new-145: Remove autofocus from buttons.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-11-17      new-176: Change eTraxis design.
//  Artem Rodygin           2006-07-27      new-261: UI design should be adopted to slow connection.
//  Artem Rodygin           2006-09-29      new-318: Group permissions should be template-wide.
//  Artem Rodygin           2006-10-08      bug-333: /src/dbo/groups.php: Unused function argument: $link.
//  Artem Rodygin           2006-10-08      bug-347: /src/projects/gview.php: Global variable $alert was used before it was defined.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
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

$xml = '<page' . gen_xml_page_header(ustrprocess(get_html_resource(RES_GROUP_X_ID), ustr2html($group['group_name']))) . '>'
     . gen_xml_menu()
     . '<path>';

if ($project || !$group['is_global'])
{
    $xml .= '<pathitem url="index.php">'                                                                        . get_html_resource(RES_PROJECTS_ID)                                                                                        . '</pathitem>'
          . '<pathitem url="view.php?id='   . ($project ? $project['project_id'] : $group['project_id']) . '">' . ustrprocess(get_html_resource(RES_PROJECT_X_ID), ustr2html($project ? $project['project_name'] : $group['project_name'])) . '</pathitem>'
          . '<pathitem url="gindex.php?id=' . ($project ? $project['project_id'] : $group['project_id']) . '">' . get_html_resource(RES_GROUPS_ID)                                                                                          . '</pathitem>';
}
else
{
    $xml .= '<pathitem url="../groups/index.php">' . get_html_resource(RES_GROUPS_ID) . '</pathitem>';
}

$xml .= '<pathitem url="gview.php?id=' . $id . ($group['is_global'] ? '&amp;pid=' . $pid : NULL) . '">' . ustrprocess(get_html_resource(RES_GROUP_X_ID), ustr2html($group['group_name'])) . '</pathitem>'
      . '</path>'
      . '<content>'
      . '<form name="mainform" action="gindex.php?id=' . ($group['is_global'] ? $pid : $group['project_id']) . '">'
      . '<group title="' . get_html_resource(RES_GROUP_INFO_ID) . '">'
      . '<text label="' . get_html_resource(RES_GROUP_NAME_ID)  . '">'  . ustr2html($group['group_name']) . '</text>'
      . '<text label="' . get_html_resource(RES_GROUP_TYPE_ID)  . '">'  . get_html_resource($group['is_global'] ? RES_GLOBAL_ID : RES_LOCAL_ID) . '</text>'
      . '<text label="' . get_html_resource(RES_DESCRIPTION_ID) . '">'  . ustr2html($group['description']) . '</text>'
      . '</group>'
      . '<button name="back" default="true">' . get_html_resource(RES_BACK_ID) . '</button>';

if (!$project || !$group['is_global'])
{
    $xml .= '<button url="gmodify.php?id=' . $id . ($group['is_global'] ? '&amp;pid=' . $pid : NULL) . '">' . get_html_resource(RES_MODIFY_ID) . '</button>';

    if (is_group_removable($id))
    {
        $xml .= '<button url="gdelete.php?id=' . $id . ($group['is_global'] ? '&amp;pid=' . $pid : NULL) . '" prompt="' . get_html_resource(RES_CONFIRM_DELETE_GROUP_ID) . '">' . get_html_resource(RES_DELETE_ID) . '</button>';
    }

    $xml .= '<button url="members.php?id=' . $id . ($group['is_global'] ? '&amp;pid=' . $pid : NULL) . '">' . get_html_resource(RES_MEMBERSHIP_ID) . '</button>';
}

$xml .= '</form>'
      . '</content>'
      . '</page>';

echo(xml2html($xml));

?>
