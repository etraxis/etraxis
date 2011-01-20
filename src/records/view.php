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
        debug_write_log(DEBUG_NOTICE, 'Guest must be logged in.');
        save_cookie(COOKIE_URI, $_SERVER['REQUEST_URI']);
        header('Location: ../logon/index.php');
        exit;
    }

    debug_write_log(DEBUG_NOTICE, 'Record cannot be displayed.');
    header('Location: index.php');
    exit;
}

// page's title

$title = ustrprocess(get_html_resource(RES_RECORD_X_ID), record_id($id, $record['template_prefix']));

// count some of records data

$rs = dal_query('comments/list.sql', $record['record_id'], ($permissions & PERMIT_CONFIDENTIAL_COMMENTS) ? EVENT_CONFIDENTIAL_COMMENT : EVENT_UNUSED);
$comments = $rs->rows;

$rs = dal_query('attachs/list.sql', $record['record_id'], 'attachment_id');
$attachments = $rs->rows;

$rs = dal_query('depends/parents.sql', $record['record_id']);
$parents = $rs->rows;

$rs = dal_query('depends/list.sql', $record['record_id']);
$subrecords = $rs->rows;

// generate breadcrumbs and tabs

$xml = '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_RECORDS_ID) . '</breadcrumb>'
     . '<breadcrumb url="view.php?id=' . $id . '">' . $title . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<tabs>'
     . '<tab url="record.php?id='      . $record['record_id'] . '">' . record_id($record['record_id'], $record['template_prefix'])             . '</tab>'
     . '<tab url="history.php?id='     . $record['record_id'] . '">' . get_html_resource(RES_HISTORY_ID)                                       . '</tab>'
     . '<tab url="changes.php?id='     . $record['record_id'] . '">' . get_html_resource(RES_CHANGES_ID)                                       . '</tab>'
     . '<tab url="fields.php?id='      . $record['record_id'] . '">' . get_html_resource(RES_FIELDS_ID)                                        . '</tab>'
     . '<tab url="comments.php?id='    . $record['record_id'] . '">' . sprintf('%s (%u)', get_html_resource(RES_COMMENTS_ID), $comments)       . '</tab>'
     . '<tab url="attachments.php?id=' . $record['record_id'] . '">' . sprintf('%s (%u)', get_html_resource(RES_ATTACHMENTS_ID), $attachments) . '</tab>'
     . '<tab url="parents.php?id='     . $record['record_id'] . '">' . sprintf('%s (%u)', get_html_resource(RES_PARENT_RECORDS_ID), $parents)  . '</tab>'
     . '<tab url="subrecords.php?id='  . $record['record_id'] . '">' . sprintf('%s (%u)', get_html_resource(RES_SUBRECORDS_ID), $subrecords)   . '</tab>'
     . '</tabs>';

echo(xml2html($xml, $title));

?>
