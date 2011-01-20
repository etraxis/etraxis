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
require_once('../dbo/filters.php');
/**#@-*/

init_page();

// filters list is submitted

if (try_request('submitted') == 'enable'  ||
    try_request('submitted') == 'disable' ||
    try_request('submitted') == 'delete')
{
    $filters = array();

    foreach ($_REQUEST as $request)
    {
        if (substr($request, 0, 6) == 'filter')
        {
            array_push($filters, intval(substr($request, 6)));
        }
    }

    if (try_request('submitted') == 'enable')
    {
        debug_write_log(DEBUG_NOTICE, 'Enable selected filters.');
        filters_set($filters);
    }
    elseif (try_request('submitted') == 'disable')
    {
        debug_write_log(DEBUG_NOTICE, 'Disable selected filters.');
        filters_clear($filters);
    }
    elseif (try_request('submitted') == 'delete')
    {
        debug_write_log(DEBUG_NOTICE, 'Delete selected filters.');
        filters_delete($filters);
    }

    exit;
}

// local JS functions

$resTitle  = get_js_resource(RES_NEW_FILTER_ID);
$resOK     = get_js_resource(RES_OK_ID);
$resNext   = get_js_resource(RES_NEXT_ID);
$resCancel = get_js_resource(RES_CANCEL_ID);

$xml = <<<JQUERY
<script>

function filterCreateStep1 ()
{
    jqModal("{$resTitle}", "create.php", "{$resNext}", "{$resCancel}", "filterCreateStep2()");
}

function filterCreateStep2 ()
{
    var project = $("#project").val();

    closeModal();

    if (project == 0)
    {
        jqModal("{$resTitle}", "create.php?" + $("#projectform").serialize(), "{$resOK}", "{$resCancel}", "$('#createform').submit()");
    }
    else
    {
        jqModal("{$resTitle}", "create.php?" + $("#projectform").serialize(), "{$resNext}", "{$resCancel}", "filterCreateStep3()");
    }
}

function filterCreateStep3 ()
{
    closeModal();
    jqModal("{$resTitle}", "create.php?" + $("#templateform").serialize(), "{$resOK}", "{$resCancel}", "$('#createform').submit()");
}

function performAction (action)
{
    $("#filters :input[name=submitted]").val(action);
    $("#filters").submit();
}

</script>
JQUERY;

// get list of filters

$sort = $page = NULL;
$list = filters_list($_SESSION[VAR_USERID], FALSE, $sort, $page);

$from = $to = 0;

// generate list of filters

$xml .= '<button action="filterCreateStep1()">' . get_html_resource(RES_CREATE_ID) . '</button>';

if ($list->rows != 0)
{
    $columns = array
    (
        RES_FILTER_NAME_ID,
        RES_OWNER_ID,
    );

    $bookmarks = gen_xml_bookmarks($page, $list->rows, $from, $to, 'list.php?');

    $xml .= '<buttonset>'
          . '<button action="performAction(\'enable\')">'  . get_html_resource(RES_ENABLE_ID)  . '</button>'
          . '<button action="performAction(\'disable\')">' . get_html_resource(RES_DISABLE_ID) . '</button>'
          . '</buttonset>'
          . '<button action="performAction(\\\'delete\\\')" prompt="' . get_js_resource(RES_CONFIRM_DELETE_FILTERS_ID) . '">' . get_html_resource(RES_DELETE_ID) . '</button>'
          . '<form name="filters" action="list.php" success="reloadTab">'
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

        if (is_null($row['fullname']))
        {
            $row['username'] = $_SESSION[VAR_USERNAME];
            $row['fullname'] = $_SESSION[VAR_FULLNAME];
        }

        $color = $row['active'] ? NULL : 'grey';

        $xml .= ($row['shared']
                    ? "<row name=\"filter{$row['filter_id']}\" color=\"{$color}\">"
                    : "<row name=\"filter{$row['filter_id']}\" url=\"view.php?id={$row['filter_id']}\" color=\"{$color}\">")
              . '<cell>' . ustr2html($row['filter_name']) . '</cell>'
              . '<cell>' . ustr2html(sprintf('%s (%s)', $row['fullname'], $row['username'])) . '</cell>'
              . '</row>';
    }

    $xml .= '</list>'
          . '</form>'
          . $bookmarks;
}

echo(xml2html($xml));

?>
