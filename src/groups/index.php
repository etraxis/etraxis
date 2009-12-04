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
//  Artem Rodygin           2005-08-25      new-058: Global groups should be implemented.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-09-17      new-128: Lists are malfunctioning in Opera.
//  Artem Rodygin           2005-09-27      new-141: Source code review.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-11-08      bug-174: Generated pages should contain <!DOCTYPE> tag.
//  Artem Rodygin           2005-11-16      new-176: Change eTraxis design.
//  Artem Rodygin           2006-07-27      new-261: UI design should be adopted to slow connection.
//  Artem Rodygin           2006-10-08      bug-343: /src/groups/index.php: Global variables $page and $sort were used before they were defined.
//  Artem Rodygin           2007-11-26      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2008-11-09      new-749: Guest access for unauthorized users.
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
    header('Location: ../index.php');
    exit;
}

$sort = $page = NULL;
$list = group_list(0, $sort, $page);

$rec_from = $rec_to = 0;

$xml = '<page' . gen_xml_page_header(get_html_resource(RES_GROUPS_ID)) . '>'
     . gen_xml_menu()
     . '<path>'
     . '<pathitem url="index.php">' . get_html_resource(RES_GROUPS_ID) . '</pathitem>'
     . '</path>'
     . '<content>';

if ($list->rows != 0)
{
    $columns = array
    (
        RES_GROUP_NAME_ID,
        RES_DESCRIPTION_ID,
    );

    $widths = array (NULL, 100);

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

        $url = ' url="../projects/gview.php?id=' . $row['group_id'] . '"';

        $xml .= '<row'  . $url . '>'
              . '<cell' . $url . ' align="left">'             . ustr2html($row['group_name'])  . '</cell>'
              . '<cell' . $url . ' align="left" wrap="true">' . ustr2html($row['description']) . '</cell>'
              . '</row>';
    }

    $xml .= '</list>';
}

$xml .= '<button url="../projects/gcreate.php?id=0">' . get_html_resource(RES_CREATE_ID) . '</button>'
      . '</content>'
      . '</page>';

echo(xml2html($xml));

?>
