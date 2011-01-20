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
require_once('../dbo/templates.php');
/**#@-*/

init_page();

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    header('Location: index.php');
    exit;
}

// check that requested template exists

$id       = ustr2int(try_request('id'));
$template = template_find($id);

if (!$template)
{
    debug_write_log(DEBUG_NOTICE, 'Template cannot be found.');
    header('Location: index.php');
    exit;
}

// page's title

$title = ustrprocess(get_html_resource(RES_TEMPLATE_X_ID), ustr2html($template['template_name']));

// generate breadcrumbs and tabs

$xml = gen_context_menu('tview.php?id=', 'sview.php?id=', 'fview.php?id=', $template['project_id'], $id)
     . '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_PROJECTS_ID) . '</breadcrumb>'
     . '<breadcrumb url="view.php?id=' . $template['project_id'] . '">' . ustrprocess(get_html_resource(RES_PROJECT_X_ID), ustr2html($template['project_name'])) . '</breadcrumb>'
     . '<breadcrumb url="tview.php?id=' . $id . '">' . $title . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<tabs>'
     . '<tab url="template.php?id=' . $id . '">' . ustr2html($template['template_name']) . '</tab>'
     . '<tab url="sindex.php?id='   . $id . '">' . get_html_resource(RES_STATES_ID)      . '</tab>'
     . '<tab url="tperms.php?id='   . $id . '">' . get_html_resource(RES_PERMISSIONS_ID) . '</tab>'
     . '</tabs>';

echo(xml2html($xml, $title));

?>
