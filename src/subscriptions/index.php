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
require_once('../dbo/subscriptions.php');
/**#@-*/

init_page();

if (!EMAIL_NOTIFICATIONS_ENABLED)
{
    debug_write_log(DEBUG_NOTICE, 'Email Notifications functionality is disabled.');
    header('Location: ../index.php');
    exit;
}

// subscriptions list is submitted

if (try_request('submitted') == 'enable'  ||
    try_request('submitted') == 'disable' ||
    try_request('submitted') == 'delete')
{
    $subscriptions = array();

    foreach ($_REQUEST as $request)
    {
        if (substr($request, 0, 5) == 'subsc')
        {
            array_push($subscriptions, intval(substr($request, 5)));
        }
    }

    if (try_request('submitted') == 'enable')
    {
        debug_write_log(DEBUG_NOTICE, 'Enable selected subscriptions.');
        subscriptions_enable($subscriptions);
    }
    elseif (try_request('submitted') == 'disable')
    {
        debug_write_log(DEBUG_NOTICE, 'Disable selected subscriptions.');
        subscriptions_disable($subscriptions);
    }
    elseif (try_request('submitted') == 'delete')
    {
        debug_write_log(DEBUG_NOTICE, 'Delete selected subscriptions.');
        subscriptions_delete($subscriptions);
    }
}

// get list of subscriptions

$sort = $page = NULL;
$list = subscriptions_list($_SESSION[VAR_USERID], $sort, $page);

$from = $to = 0;

// generate breadcrumbs and tabs

$xml = '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_SUBSCRIPTIONS_ID) . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<tabs>'
     . '<tab url="index.php" active="true">' . get_html_resource(RES_SUBSCRIPTIONS_ID) . '</tab>'
     . '<tab url="create.php">'              . get_html_resource(RES_CREATE_ID)        . '</tab>'
     . '<content>';

// generate list of subscriptions

if ($list->rows != 0)
{
    $columns = array
    (
        RES_SUBSCRIPTION_NAME_ID,
        RES_CARBON_COPY_ID,
    );

    $bookmarks = gen_xml_bookmarks($page, $list->rows, $from, $to);

    $xml .= '<button action="document.subscriptions.submitted.value = \'enable\';  document.subscriptions.submit()">' . get_html_resource(RES_ENABLE_ID)  . '</button>'
          . '<button action="document.subscriptions.submitted.value = \'disable\'; document.subscriptions.submit()">' . get_html_resource(RES_DISABLE_ID) . '</button>'
          . HTML_SPLITTER
          . '<button action="document.subscriptions.submitted.value = \'delete\'; document.subscriptions.submit()" prompt="' . get_html_resource(RES_CONFIRM_DELETE_SUBSCRIPTIONS_ID) . '">' . get_html_resource(RES_DELETE_ID)  . '</button>'
          . '<form name="subscriptions" action="index.php">'
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

        $color = $row['is_activated'] ? NULL : 'grey';

        $xml .= "<row name=\"subsc{$row['subscribe_id']}\" url=\"view.php?id={$row['subscribe_id']}\" color=\"{$color}\">"
              . '<cell>' . ustr2html($row['subscribe_name']) . '</cell>'
              . '<cell>' . ustr2html($row['carbon_copy'])    . '</cell>'
              . '</row>';
    }

    $xml .= '</list>'
          . '</form>'
          . $bookmarks;
}

$xml .= '</content>'
      . '</tabs>';

echo(xml2html($xml, get_html_resource(RES_SUBSCRIPTIONS_ID)));

?>
