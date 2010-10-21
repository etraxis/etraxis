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
require_once('../dbo/views.php');
/**#@-*/

init_page();

// check that requested view exists

$id   = ustr2int(try_request('id'));
$view = view_find($id);

if (!$view)
{
    debug_write_log(DEBUG_NOTICE, 'View cannot be found.');
    header('Location: index.php');
    exit;
}

// page's title

$title = ustrprocess(get_html_resource(RES_VIEW_X_ID), ustr2html($view['view_name']));

// generate breadcrumbs and tabs

$xml = '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_VIEWS_ID) . '</breadcrumb>'
     . '<breadcrumb url="view.php?id=' . $id . '">' . $title . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<tabs>'
     . '<tab url="view.php?id=' . $id . '" active="true"><i>' . ustr2html($view['view_name']) . '</i></tab>'
     . '<tab url="columns.php?id=' . $id . '">' . get_html_resource(RES_COLUMNS_ID) . '</tab>'
     . '<tab url="filters.php?id=' . $id . '">' . get_html_resource(RES_FILTERS_ID) . '</tab>'
     . '<content>';

// generate buttons

$xml .= '<button url="index.php">' . get_html_resource(RES_BACK_ID) . '</button>'
      . HTML_SPLITTER
      . '<button url="modify.php?id=' . $id . '">' . get_html_resource(RES_MODIFY_ID) . '</button>';

$xml .= '<button url="delete.php?id=' . $id . '" prompt="' . get_html_resource(RES_CONFIRM_DELETE_VIEWS_ID) . '">'
      . get_html_resource(RES_DELETE_ID)
      . '</button>';

// generate view information

$xml .= '<group title="' . get_html_resource(RES_GENERAL_INFO_ID) . '">'
      . '<text label="' . get_html_resource(RES_VIEW_NAME_ID) . '">' . ustr2html($view['view_name']) . '</text>'
      . '</group>'
      . '</content>'
      . '</tabs>';

echo(xml2html($xml, $title));

?>
