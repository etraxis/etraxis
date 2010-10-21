<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2005-2009  Artem Rodygin
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
require_once('../dbo/accounts.php');
require_once('../dbo/events.php');
require_once('../dbo/records.php');
/**#@-*/

init_page(GUEST_IS_ALLOWED);

// check that requested record exists

$id     = ustr2int(try_request('id'));
$record = record_find($id);

if (!$record)
{
    debug_write_log(DEBUG_NOTICE, 'Record cannot be found.');
    header('Location: index.php');
    exit;
}

// get current user's permissions and verify them

$permissions = record_get_permissions($record['template_id'], $record['creator_id'], $record['responsible_id']);

if (!can_record_be_displayed($permissions))
{
    if (get_user_level() == USER_LEVEL_GUEST)
    {
        save_cookie(COOKIE_URI, $_SERVER['REQUEST_URI']);
    }

    debug_write_log(DEBUG_NOTICE, 'Record cannot be displayed.');
    header('Location: index.php');
    exit;
}

// mark the record as read

record_read($id);

// page's title

$title = ustrprocess(get_html_resource(RES_RECORD_X_ID), record_id($id, $record['template_prefix']));

// generate breadcrumbs and tabs

$xml = '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_RECORDS_ID) . '</breadcrumb>'
     . '<breadcrumb url="history.php?id=' . $id . '">' . $title . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<tabs>'
     . gen_record_tabs($record, RECORD_TAB_HISTORY)
     . '<content>';

// get the history

$sort = $page = NULL;
$list = history_list($id, $permissions, $sort, $page);

if ($list->rows == 0)
{
    debug_write_log(DEBUG_WARNING, 'History is empty.');
    header('Location: view.php?id=' . $id);
    exit;
}

// generate list header

$columns = array
(
    RES_TIMESTAMP_ID,
    RES_ORIGINATOR_ID,
    RES_DESCRIPTION_ID,
);

$rec_from = $rec_to = 0;

$bookmarks = gen_xml_bookmarks($page, $list->rows, $rec_from, $rec_to, 'history.php?id=' . $id . '&amp;');

$xml .= '<list>'
      . '<hrow>';

for ($i = 1; $i <= count($columns); $i++)
{
    $smode = ($sort == $i ? ($i + count($columns)) : $i);

    $xml .= "<hcell url=\"history.php?id={$id}&amp;sort={$smode}&amp;page={$page}\">"
          . get_html_resource($columns[$i - 1])
          . '</hcell>';
}

$xml .= '</hrow>';

// go through the history

$list->seek($rec_from - 1);

for ($i = $rec_from; $i <= $rec_to; $i++)
{
    $row = $list->fetch();

    $event = get_event_string($row['event_id'], $row['event_type'], $row['event_param']);

    $xml .= '<row>'
          . '<cell>' . get_datetime($row['event_time']) . '</cell>'
          . '<cell>' . ustr2html(sprintf('%s (%s)', $row['fullname'], account_get_username($row['username']))) . '</cell>'
          . '<cell>' . $event . '</cell>'
          . '</row>';
}

$xml .= '</list>'
      . $bookmarks
      . '</content>'
      . '</tabs>';

echo(xml2html($xml, $title));

?>
