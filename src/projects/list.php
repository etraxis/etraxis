<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2005-2011  Artem Rodygin
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

init_page(LOAD_TAB, GUEST_IS_ALLOWED);

// local JS functions

$resTitle  = get_js_resource(RES_NEW_PROJECT_ID);
$resOK     = get_js_resource(RES_OK_ID);
$resCancel = get_js_resource(RES_CANCEL_ID);

$xml = <<<JQUERY
<script>

function projectCreate ()
{
    jqModal("{$resTitle}", "create.php", "{$resOK}", "{$resCancel}", "$('#createform').submit()");
}

</script>
JQUERY;

// get list of projects

$sort = $page = NULL;
$list = projects_list($sort, $page);

$from = $to = 0;

// generate buttons

if (get_user_level() == USER_LEVEL_ADMIN)
{
    $xml .= '<button action="projectCreate()">' . get_html_resource(RES_CREATE_ID) . '</button>';
}

// generate list of projects

if ($list->rows != 0)
{
    $columns = array
    (
        RES_PROJECT_NAME_ID,
        RES_START_TIME_ID,
        RES_DESCRIPTION_ID,
    );

    $bookmarks = gen_xml_bookmarks($page, $list->rows, $from, $to, 'list.php?');

    $xml .= '<list>'
          . '<hrow>';

    for ($i = 1; $i <= count($columns); $i++)
    {
        $smode = ($sort == $i ? ($i + count($columns)) : $i);

        $xml .= "<hcell url=\"list.php?sort={$smode}\">"
              . get_html_resource($columns[$i - 1])
              . '</hcell>';
    }

    $xml .= '</hrow>';

    $list->seek($from - 1);

    for ($i = $from; $i <= $to; $i++)
    {
        $row = $list->fetch();

        if ($row['is_suspended'])
        {
            $color = 'grey';
        }
        else
        {
            $color = NULL;
        }

        $xml .= "<row url=\"view.php?id={$row['project_id']}\" color=\"{$color}\">"
              . '<cell>' . ustr2html($row['project_name']) . '</cell>'
              . '<cell>' . get_date($row['start_time'])    . '</cell>'
              . '<cell>' . ustr2html($row['description'])  . '</cell>'
              . '</row>';
    }

    $xml .= '</list>'
          . $bookmarks;
}

echo(xml2html($xml));

?>
