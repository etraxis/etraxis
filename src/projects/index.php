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
//  Artem Rodygin           2005-02-18      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-07-23      new-011: Color scheme should be modified.
//  Artem Rodygin           2005-08-01      new-013: UI scenarios should be changed.
//  Artem Rodygin           2005-08-12      new-008: Predefined metrics.
//  Artem Rodygin           2005-08-16      bug-028: List of projects is not filtered.
//  Artem Rodygin           2005-08-23      new-053: All the calls of DAL API functions should be moved to DBO API.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-09-12      new-106: Mark closed records in the list with different color.
//  Artem Rodygin           2005-09-17      new-128: Lists are malfunctioning in Opera.
//  Artem Rodygin           2005-09-27      new-141: Source code review.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-11-08      bug-174: Generated pages should contain <!DOCTYPE> tag.
//  Artem Rodygin           2005-11-17      new-176: Change eTraxis design.
//  Artem Rodygin           2006-07-27      new-261: UI design should be adopted to slow connection.
//  Artem Rodygin           2006-10-08      bug-348: /src/projects/index.php: Global variables $page and $sort were used before they were defined.
//  Artem Rodygin           2007-11-26      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2008-01-31      new-601: [SF1814666] Export and Import Templates
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

$sort = $page = NULL;
$list = project_list($sort, $page);

$rec_from = $rec_to = 0;

$xml = '<page' . gen_xml_page_header(get_html_resource(RES_PROJECTS_ID)) . '>'
     . gen_xml_menu()
     . '<path>'
     . '<pathitem url="index.php">' . get_html_resource(RES_PROJECTS_ID) . '</pathitem>'
     . '</path>'
     . '<content>';

if ($list->rows != 0)
{
    $columns = array
    (
        RES_PROJECT_NAME_ID,
        RES_START_TIME_ID,
        RES_DESCRIPTION_ID,
    );

    $widths = array (NULL, NULL, 100);

    $xml .= '<list>'
          . gen_xml_bookmarks($page, $list->rows, $rec_from, $rec_to)
          . '<hrow>';

    for ($i = 1; $i <= count($columns); $i++)
    {
        $smode = ($sort == $i ? ($i + count($columns)) : $i);
        $width = (is_null($widths[$i - 1]) ? NULL : ' width="' . $widths[$i - 1] . '"');

        $xml .= '<hcell url="index.php?sort=' . $smode . '&amp;page=' . $page . '"' . $width . '>'
              . get_html_resource($columns[$i - 1])
              . '</hcell>';
    }

    $xml .= '</hrow>';

    $list->seek($rec_from - 1);

    for ($i = $rec_from; $i <= $rec_to; $i++)
    {
        $row = $list->fetch();

        $url = ' url="view.php?id=' . $row['project_id'] . '"';

        $style = ($row['is_suspended'] ? ' style="hot"' : NULL);

        $xml .= '<row'  . $url . '>'
              . '<cell' . $url . $style . ' align="left">'             . ustr2html($row['project_name']) . '</cell>'
              . '<cell' . $url . $style . ' align="left">'             . get_date($row['start_time'])    . '</cell>'
              . '<cell' . $url . $style . ' align="left" wrap="true">' . ustr2html($row['description'])  . '</cell>'
              . '</row>';
    }

    $xml .= '</list>';
}

if (get_user_level() == USER_LEVEL_ADMIN)
{
    $xml .= '<button url="create.php" default="true">' . get_html_resource(RES_CREATE_ID) . '</button>'
          . '<button url="import.php">'                . get_html_resource(RES_IMPORT_ID) . '</button>';
}

$xml .= '</content>'
      . '</page>';

echo(xml2html($xml));

?>
