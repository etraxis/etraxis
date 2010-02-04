<?php

/**
 * @package eTraxis
 * @ignore
 */

//--------------------------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system.
//  Copyright (C) 2005-2010 by Artem Rodygin
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
//  Artem Rodygin           2005-08-09      new-008: Predefined metrics.
//  Artem Rodygin           2005-08-13      bug-026: The 'Groups' and 'Templates' buttons are malfunctional.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-09-13      new-113: When record is being viewed the fields names and values should be aligned by top.
//  Artem Rodygin           2005-10-05      new-145: Remove autofocus from buttons.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-11-17      new-176: Change eTraxis design.
//  Artem Rodygin           2006-07-27      new-261: UI design should be adopted to slow connection.
//  Artem Rodygin           2006-10-08      bug-354: /src/projects/view.php: Global variable $alert was used before it was defined.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2008-11-18      new-762: Forward logged in user to the page he has tried to open before authentication.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//  Artem Rodygin           2009-10-13      new-838: Disabled buttons would be better grayed out than invisible.
//  Giacomo Giustozzi       2010-01-27      new-896: Export the whole project
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/projects.php');
/**#@-*/

init_page(GUEST_IS_ALLOWED);

$id      = ustr2int(try_request('id'));
$project = project_find($id);

if (!$project)
{
    debug_write_log(DEBUG_NOTICE, 'Project cannot be found.');
    header('Location: index.php');
    exit;
}

$xml = '<page' . gen_xml_page_header(ustrprocess(get_html_resource(RES_PROJECT_X_ID), ustr2html($project['project_name']))) . '>'
     . gen_xml_menu()
     . '<path>'
     . '<pathitem url="index.php">'               . get_html_resource(RES_PROJECTS_ID)                                                    . '</pathitem>'
     . '<pathitem url="view.php?id=' . $id . '">' . ustrprocess(get_html_resource(RES_PROJECT_X_ID), ustr2html($project['project_name'])) . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<form name="mainform" action="index.php">'
     . '<group title="' . get_html_resource(RES_PROJECT_INFO_ID) . '">'
     . '<text label="' . get_html_resource(RES_PROJECT_NAME_ID) . '">'  . ustr2html($project['project_name']) . '</text>'
     . '<text label="' . get_html_resource(RES_START_TIME_ID)   . '">'  . get_date($project['start_time'])    . '</text>'
     . '<text label="' . get_html_resource(RES_DESCRIPTION_ID)  . '">'  . ustr2html($project['description'])  . '</text>'
     . '<text label="' . get_html_resource(RES_STATUS_ID)       . '">'  . get_html_resource($project['is_suspended'] ? RES_SUSPENDED_ID : RES_ACTIVE_ID) . '</text>'
     . '</group>'
     . '<button name="back" default="true">' . get_html_resource(RES_BACK_ID) . '</button>';

if (get_user_level() == USER_LEVEL_ADMIN)
{
    $xml .= '<button url="modify.php?id=' . $id . '">' . get_html_resource(RES_MODIFY_ID) . '</button>';

    if (is_project_removable($id) && $project['is_suspended'])
    {
        $xml .= '<button url="delete.php?id=' . $id . '" prompt="' . get_html_resource(RES_CONFIRM_DELETE_PROJECT_ID) . '">' . get_html_resource(RES_DELETE_ID) . '</button>';
    }
    else
    {
        $xml .= '<button disabled="true">' . get_html_resource(RES_DELETE_ID) . '</button>';
    }

    $xml .= '<button url="gindex.php?id='  . $id . '">' . get_html_resource(RES_GROUPS_ID)    . '</button>'
          . '<button url="tindex.php?id='  . $id . '">' . get_html_resource(RES_TEMPLATES_ID) . '</button>'
          . '<button url="pexport.php?id=' . $id . '">' . get_html_resource(RES_EXPORT_ID)    . '</button>';
}

$xml .= '<button url="metrics.php?id=' . $id . '">' . get_html_resource(RES_METRICS_ID) . '</button>'
      . '</form>'
      . '</content>'
      . '</page>';

echo(xml2html($xml));

?>
