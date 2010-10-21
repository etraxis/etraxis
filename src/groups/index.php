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

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    header('Location: ../index.php');
    exit;
}

// get list of groups

$sort = $page = NULL;
$list = groups_list(0, $sort, $page);

$from = $to = 0;

// generate breadcrumbs and tabs

$xml = '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_GLOBAL_GROUPS_ID) . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<tabs>'
     . '<tab url="index.php" active="true">' . get_html_resource(RES_GROUPS_ID) . '</tab>'
     . '<tab url="create.php">'              . get_html_resource(RES_CREATE_ID)   . '</tab>'
     . '<content>';

// generate list of groups

if ($list->rows != 0)
{
    $columns = array
    (
        RES_GROUP_NAME_ID,
        RES_DESCRIPTION_ID,
    );

    $bookmarks = gen_xml_bookmarks($page, $list->rows, $from, $to);

    $xml .= '<list>'
          . '<hrow>';

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

        $xml .= "<row url=\"view.php?id={$row['group_id']}\">"
              . '<cell>' . ustr2html($row['group_name'])  . '</cell>'
              . '<cell>' . ustr2html($row['description']) . '</cell>'
              . '</row>';
    }

    $xml .= '</list>'
          . $bookmarks;
}

$xml .= '</content>'
      . '</tabs>';

echo(xml2html($xml, get_html_resource(RES_GLOBAL_GROUPS_ID)));

?>
