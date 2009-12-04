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
//  Artem Rodygin           2005-08-09      new-008: Predefined metrics.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-09-21      new-136: Metrics page is scrolled down if it doesn't fit into the browser window.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-11-17      new-176: Change eTraxis design.
//  Artem Rodygin           2006-10-08      bug-349: /src/projects/metrics.php: Global variable $alert was used before it was defined.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2008-11-18      new-762: Forward logged in user to the page he has tried to open before authentication.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
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

$xml = '<page' . gen_xml_page_header(get_html_resource(RES_METRICS_ID)) . '>'
     . gen_xml_menu()
     . '<path>'
     . '<pathitem url="index.php">'                  . get_html_resource(RES_PROJECTS_ID)                                                    . '</pathitem>'
     . '<pathitem url="view.php?id='    . $id . '">' . ustrprocess(get_html_resource(RES_PROJECT_X_ID), ustr2html($project['project_name'])) . '</pathitem>'
     . '<pathitem url="metrics.php?id=' . $id . '">' . get_html_resource(RES_METRICS_ID)                                                     . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<form name="mainform" action="view.php?id=' . $id . '">'
     . '<group>'
     . '<image>opened.php?type=' . METRICS_OPENED_RECORDS      . '&amp;id=' . $id . '</image>'
     . '<image>opened.php?type=' . METRICS_CREATION_VS_CLOSURE . '&amp;id=' . $id . '</image>'
     . '</group>'
     . '<button name="back" default="true">' . get_html_resource(RES_BACK_ID) . '</button>'
     . '</form>'
     . '</content>'
     . '</page>';

echo(xml2html($xml));

?>
