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

init_page(LOAD_TAB);

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

    exit;
}

// get list of views

$sort = $page = NULL;
$list = views_list($sort, $page);

$from = $to = 0;

// local JS functions

$resTitle  = get_js_resource(RES_NEW_VIEW_ID);
$resOK     = get_js_resource(RES_OK_ID);
$resCancel = get_js_resource(RES_CANCEL_ID);

$xml = <<<JQUERY
<script>

function viewCreate ()
{
    jqModal("{$resTitle}", "create.php", "{$resOK}", "{$resCancel}", "$('#createform').submit()");
}

function performAction (action)
{
    $("#views :input[name=submitted]").val(action);
    $("#views").submit();
}

</script>
JQUERY;

// generate list of views

$xml .= '<button action="viewCreate()">' . get_html_resource(RES_CREATE_ID) . '</button>';

if ($list->rows != 0)
{
    $columns = array
    (
        RES_VIEW_NAME_ID,
    );

    $bookmarks = gen_xml_bookmarks($page, $list->rows, $from, $to, 'list.php?');

    $xml .= '<button action="performAction(\\\'delete\\\')" prompt="' . get_js_resource(RES_CONFIRM_DELETE_VIEWS_ID) . '">' . get_html_resource(RES_DELETE_ID) . '</button>'
          . '<form name="views" action="list.php" success="reloadTab">'
          . '<list>'
          . '<hrow>'
          . '<hcell checkboxes="true"/>';

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

        $xml .= "<row name=\"view{$row['view_id']}\" url=\"view.php?id={$row['view_id']}\">"
              . '<cell>' . ustr2html($row['view_name']) . '</cell>'
              . '</row>';
    }

    $xml .= '</list>'
          . '</form>'
          . $bookmarks;
}

echo(xml2html($xml));

?>
