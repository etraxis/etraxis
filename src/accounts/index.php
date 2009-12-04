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
//  Artem Rodygin           2005-02-13      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-07-04      bug-010: Missing 'require' operator.
//  Artem Rodygin           2005-07-23      new-011: Color scheme should be modified.
//  Artem Rodygin           2005-08-01      new-013: UI scenarios should be changed.
//  Artem Rodygin           2005-08-23      new-053: All the calls of DAL API functions should be moved to DBO API.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-09-12      new-106: Mark closed records in the list with different color.
//  Artem Rodygin           2005-09-17      new-128: Lists are malfunctioning in Opera.
//  Artem Rodygin           2005-09-27      new-141: Source code review.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-11-08      bug-174: Generated pages should contain <!DOCTYPE> tag.
//  Artem Rodygin           2005-11-16      new-176: Change eTraxis design.
//  Artem Rodygin           2006-07-27      new-261: UI design should be adopted to slow connection.
//  Artem Rodygin           2006-10-08      bug-328: /src/accounts/index.php: Global variables $page and $sort were used before they were defined.
//  Artem Rodygin           2006-11-20      new-392: Local users should not be extended with '@eTraxis' when LDAP is disabled.
//  Artem Rodygin           2006-12-11      bug-440: Local users should not be extended with '@eTraxis' when being modified.
//  Artem Rodygin           2007-11-26      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2008-11-09      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/accounts.php');
/**#@-*/

init_page();

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    header('Location: ../index.php');
    exit;
}

$sort = $page = NULL;
$list = account_list($sort, $page);

$rec_from = $rec_to = 0;

$xml = '<page' . gen_xml_page_header(get_html_resource(RES_ACCOUNTS_ID)) . '>'
     . gen_xml_menu()
     . '<path>'
     . '<pathitem url="index.php">' . get_html_resource(RES_ACCOUNTS_ID) . '</pathitem>'
     . '</path>'
     . '<content>';

if ($list->rows != 0)
{
    $columns = array
    (
        RES_USERNAME_ID,
        RES_FULLNAME_ID,
        RES_EMAIL_ID,
        RES_RIGHTS_ID,
        RES_DESCRIPTION_ID,
    );

    $widths = array (NULL, NULL, NULL, NULL, 100);

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

        $url = ' url="view.php?id=' . $row['account_id'] . '"';

        $style = ($row['is_disabled'] || is_account_locked($row['locks_count'], $row['lock_time']) ? ' style="hot"' : NULL);

        $xml .= '<row' . $url . '>'
              . '<cell' . $url . $style . ' align="left">'             . ustr2html(account_get_username($row['username'], FALSE)) . '</cell>'
              . '<cell' . $url . $style . ' align="left">'             . ustr2html($row['fullname'])    . '</cell>'
              . '<cell' . $url . $style . ' align="left">'             . ustr2html($row['email'])       . '</cell>'
              . '<cell' . $url . $style . ' align="left">'             . get_html_resource($row['is_admin'] == 0 ? RES_USER_ID : RES_ADMINISTRATOR_ID) . '</cell>'
              . '<cell' . $url . $style . ' align="left" wrap="true">' . ustr2html($row['description']) . '</cell>'
              . '</row>';
    }

    $xml .= '</list>';
}

$xml .= '<button url="create.php" default="true">' . get_html_resource(RES_CREATE_ID) . '</button>'
      . '</content>'
      . '</page>';

echo(xml2html($xml));

?>
