<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2006-2010  Artem Rodygin
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

// views list is submitted

if (try_request('submitted') == 'delete')
{
    $views = array();

    foreach ($_REQUEST as $request)
    {
        if (substr($request, 0, 4) == 'view')
        {
            array_push($views, intval(substr($request, 4)));
        }
    }

    debug_write_log(DEBUG_NOTICE, 'Delete selected views.');
    views_delete($views);
}

// get list of views

$sort = $page = NULL;
$list = views_list($sort, $page);

$from = $to = 0;

// generate breadcrumbs and tabs

$xml = '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_VIEWS_ID) . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<tabs>'
     . '<tab url="index.php" active="true">' . get_html_resource(RES_VIEWS_ID)  . '</tab>'
     . '<tab url="create.php">'              . get_html_resource(RES_CREATE_ID) . '</tab>'
     . '<content>';

// generate list of views

if ($list->rows != 0)
{
    $columns = array
    (
        RES_VIEW_NAME_ID,
    );

    $bookmarks = gen_xml_bookmarks($page, $list->rows, $from, $to);

    $xml .= '<button action="document.views.submitted.value = \'delete\'; document.views.submit()" prompt="' . get_html_resource(RES_CONFIRM_DELETE_VIEWS_ID) . '">' . get_html_resource(RES_DELETE_ID)  . '</button>'
          . '<form name="views" action="index.php">'
          . '<list>'
          . '<hrow>'
          . '<hcell checkboxes="true"/>';

    for ($i = 1; $i <= count($columns); $i++)
    {
        $smode = ($sort == $i ? ($i + count($columns)) : $i);

        $xml .= "<hcell url=\"index.php?sort={$smode}&amp;page={$page}\">"
              . get_html_resource($columns[$i - 1])
              . '</hcell>';
    }

    $xml .= '</hrow>';

    $list->seek($from - 1);

    for ($i = $from; $i <= $to; $i++)
    {
        $row = $list->fetch();

        $xml .= "<row name=\"view{$row['view_id']}\" url=\"view.php?id={$row['view_id']}\">"
              . '<cell>' . ustr2html($row['view_name']) . '</cell>'
              . '</row>';
    }

    $xml .= '</list>'
          . '</form>'
          . $bookmarks;
}

$xml .= '</content>'
      . '</tabs>';

echo(xml2html($xml, get_html_resource(RES_VIEWS_ID)));

?>
