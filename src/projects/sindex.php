<?php

/**
 * @package eTraxis
 * @ignore
 */

//--------------------------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system.
//  Copyright (C) 2005-2010 by Artem Rodygin
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
//  Artem Rodygin           2005-03-20      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-07-23      new-011: Color scheme should be modified.
//  Artem Rodygin           2005-08-01      new-013: UI scenarios should be changed.
//  Artem Rodygin           2005-08-18      new-037: Any template should be locked to be modified without suspending a project.
//  Artem Rodygin           2005-08-23      new-053: All the calls of DAL API functions should be moved to DBO API.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-09-17      new-128: Lists are malfunctioning in Opera.
//  Artem Rodygin           2005-09-27      new-141: Source code review.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-11-08      bug-174: Generated pages should contain <!DOCTYPE> tag.
//  Artem Rodygin           2005-11-17      new-176: Change eTraxis design.
//  Artem Rodygin           2006-07-27      new-261: UI design should be adopted to slow connection.
//  Artem Rodygin           2006-10-08      bug-350: /src/projects/sindex.php: Global variables $page and $sort were used before they were defined.
//  Artem Rodygin           2007-11-26      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//  Artem Rodygin           2010-07-20      bug-949: field list not selectable over 20
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/templates.php');
require_once('../dbo/states.php');
/**#@-*/

init_page();

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    header('Location: index.php');
    exit;
}

$id       = ustr2int(try_request('id'));
$template = template_find($id);

if (!$template)
{
    debug_write_log(DEBUG_NOTICE, 'Template cannot be found.');
    header('Location: index.php');
    exit;
}

$sort = $page = NULL;
$list = state_list($id, $sort, $page);

$rec_from = $rec_to = 0;

$xml = '<page' . gen_xml_page_header(get_html_resource(RES_STATES_ID)) . '>'
     . gen_xml_menu()
     . '<path>'
     . '<pathitem url="index.php">'                                     . get_html_resource(RES_PROJECTS_ID)                                                       . '</pathitem>'
     . '<pathitem url="view.php?id='   . $template['project_id'] . '">' . ustrprocess(get_html_resource(RES_PROJECT_X_ID), ustr2html($template['project_name']))   . '</pathitem>'
     . '<pathitem url="tindex.php?id=' . $template['project_id'] . '">' . get_html_resource(RES_TEMPLATES_ID)                                                      . '</pathitem>'
     . '<pathitem url="tview.php?id='  . $id                     . '">' . ustrprocess(get_html_resource(RES_TEMPLATE_X_ID), ustr2html($template['template_name'])) . '</pathitem>'
     . '<pathitem url="sindex.php?id=' . $id                     . '">' . get_html_resource(RES_STATES_ID)                                                         . '</pathitem>'
     . '</path>'
     . '<content>';

if ($list->rows != 0)
{
    $columns = array
    (
        RES_STATE_NAME_ID,
        RES_STATE_ABBR_ID,
        RES_STATE_TYPE_ID,
        RES_RESPONSIBLE_ID,
    );

    $widths = array (NULL, NULL, NULL, 100);

    $xml .= '<list>'
          . gen_xml_bookmarks($page, $list->rows, $rec_from, $rec_to, 'sindex.php?id=' . $id . '&amp;')
          . '<hrow>';

    for ($i = 1; $i <= count($columns); $i++)
    {
        $smode = ($sort == $i ? ($i + count($columns)) : $i);
        $width = (is_null($widths[$i - 1]) ? NULL : ' width="' . $widths[$i - 1] . '"');

        $xml .= '<hcell url="sindex.php?id=' . $id . '&amp;sort=' . $smode . '&amp;page=' . $page . '"' . $width . '>'
              . get_html_resource($columns[$i - 1])
              . '</hcell>';
    }

    $xml .= '</hrow>';

    $state_type = array
    (
        STATE_TYPE_INITIAL      => RES_INITIAL_ID,
        STATE_TYPE_INTERMEDIATE => RES_INTERMEDIATE_ID,
        STATE_TYPE_FINAL        => RES_FINAL_ID,
    );

    $responsible = array
    (
        STATE_RESPONSIBLE_REMAIN => RES_REMAIN_ID,
        STATE_RESPONSIBLE_ASSIGN => RES_ASSIGN_ID,
        STATE_RESPONSIBLE_REMOVE => RES_REMOVE_ID,
    );

    $list->seek($rec_from - 1);

    for ($i = $rec_from; $i <= $rec_to; $i++)
    {
        $row = $list->fetch();

        $url = ' url="sview.php?id=' . $row['state_id'] . '"';

        $xml .= '<row'  . $url . '>'
              . '<cell' . $url . ' align="left">'   . ustr2html($row['state_name']) . '</cell>'
              . '<cell' . $url . ' align="center">' . ustr2html($row['state_abbr']) . '</cell>'
              . '<cell' . $url . ' align="left">'   . get_html_resource($state_type[$row['state_type']]) . '</cell>'
              . '<cell' . $url . ' align="left" wrap="true">' . get_html_resource($responsible[$row['responsible']]) . '</cell>'
              . '</row>';
    }

    $xml .= '</list>';
}

$xml .= '<button url="tview.php?id=' . $id . '" default="true">' . get_html_resource(RES_BACK_ID) . '</button>';

if ($template['is_locked'])
{
    $xml .= '<button url="screate.php?id=' . $id . '&amp;final=0">' . get_html_resource(RES_CREATE_INTERMEDIATE_ID) . '</button>'
          . '<button url="screate.php?id=' . $id . '&amp;final=1">' . get_html_resource(RES_CREATE_FINAL_ID)        . '</button>';
}

$xml .= '</content>'
      . '</page>';

echo(xml2html($xml));

?>
