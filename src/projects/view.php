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
require_once('../dbo/projects.php');
/**#@-*/

init_page(LOAD_CONTAINER, GUEST_IS_ALLOWED);

// check that requested project exists

$id      = ustr2int(try_request('id'));
$project = project_find($id);

if (!$project)
{
    debug_write_log(DEBUG_NOTICE, 'Project cannot be found.');
    header('Location: index.php');
    exit;
}

// page's title

$title = ustrprocess(get_html_resource(RES_PROJECT_X_ID), ustr2html($project['project_name']));

// generate breadcrumbs and tabs

$xml = gen_context_menu('tview.php?id=', 'sview.php?id=', 'fview.php?id=', $id)
     . '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_PROJECTS_ID) . '</breadcrumb>'
     . '<breadcrumb url="view.php?id=' . $id . '">' . $title . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<tabs>'
     . '<tab url="project.php?id=' . $id . '" active="true">' . ustr2html($project['project_name']) . '</tab>';

if (get_user_level() == USER_LEVEL_ADMIN)
{
    $xml .= '<tab url="gindex.php?id=' . $id . '">' . get_html_resource(RES_GROUPS_ID)    . '</tab>'
          . '<tab url="tindex.php?id=' . $id . '">' . get_html_resource(RES_TEMPLATES_ID) . '</tab>';
}

$xml .= '<tab url="metrics.php?id=' . $id . '">' . get_html_resource(RES_METRICS_ID) . '</tab>'
      . '</tabs>';

echo(xml2html($xml, $title));

?>
