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
     . '<breadcrumb url="tindex.php?id=' . $template['project_id'] . '">' . ustrprocess(get_html_resource(RES_PROJECT_X_ID), ustr2html($template['project_name'])) . '</breadcrumb>'
     . '<breadcrumb url="tview.php?id=' . $id . '">' . $title . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<tabs>'
     . '<tab url="tview.php?id='  . $id . '" active="true"><i>' . ustr2html($template['template_name']) . '</i></tab>'
     . '<tab url="sindex.php?id=' . $id . '">'                  . get_html_resource(RES_STATES_ID)      . '</tab>'
     . '<tab url="tperms.php?id=' . $id . '">'                  . get_html_resource(RES_PERMISSIONS_ID) . '</tab>'
     . '<content>';

// generate buttons

$xml .= '<button url="tindex.php?id=' . $template['project_id'] . '">' . get_html_resource(RES_BACK_ID) . '</button>'
      . HTML_SPLITTER
      . '<button url="texport.php?id=' . $id . '">' . get_html_resource(RES_EXPORT_ID) . '</button>'
      . HTML_SPLITTER
      . '<button url="tmodify.php?id=' . $id . '">' . get_html_resource(RES_MODIFY_ID) . '</button>'
      . '<button url="tclone.php?id='  . $id . '">' . get_html_resource(RES_CLONE_ID)  . '</button>'
      . (is_template_removable($id)
            ? '<button url="tdelete.php?id=' . $id . '" prompt="' . get_html_resource(RES_CONFIRM_DELETE_TEMPLATE_ID) . '">'
            : '<button disabled="false">')
      . get_html_resource(RES_DELETE_ID)
      . '</button>'
      . '<button url="tlock.php?id=' . $id . '">'
      . get_html_resource($template['is_locked'] ? RES_UNLOCK_ID : RES_LOCK_ID)
      . '</button>';

// generate template information

$xml .= '<group title="' . get_html_resource(RES_TEMPLATE_INFO_ID) . '">'
      . '<text label="' . get_html_resource(RES_TEMPLATE_NAME_ID)   . '">' . ustr2html($template['template_name'])   . '</text>'
      . '<text label="' . get_html_resource(RES_TEMPLATE_PREFIX_ID) . '">' . ustr2html($template['template_prefix']) . '</text>'
      . '<text label="' . get_html_resource(RES_CRITICAL_AGE_ID)    . '">' . (is_null($template['critical_age']) ? get_html_resource(RES_NONE_ID) : $template['critical_age']) . '</text>'
      . '<text label="' . get_html_resource(RES_FROZEN_TIME_ID)     . '">' . (is_null($template['frozen_time'])  ? get_html_resource(RES_NONE_ID) : $template['frozen_time'])  . '</text>'
      . '<text label="' . get_html_resource(RES_DESCRIPTION_ID)     . '">' . ustr2html($template['description']) . '</text>'
      . '<text label="' . get_html_resource(RES_GUEST_ACCESS_ID)    . '">' . get_html_resource($template['guest_access'] ? RES_YES_ID    : RES_NO_ID)     . '</text>'
      . '<text label="' . get_html_resource(RES_STATUS_ID)          . '">' . get_html_resource($template['is_locked']    ? RES_LOCKED_ID : RES_ACTIVE_ID) . '</text>'
      . '</group>'
      . '</content>'
      . '</tabs>';

echo(xml2html($xml, $title));

?>
