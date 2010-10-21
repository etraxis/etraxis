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
require_once('../dbo/fields.php');
require_once('../dbo/records.php');
require_once('../dbo/events.php');
/**#@-*/

init_page();

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

if (!can_record_be_postponed($record, $permissions))
{
    debug_write_log(DEBUG_NOTICE, 'Record cannot be postponed.');
    header('Location: view.php?id=' . $id);
    exit;
}

// get current date

$today = date_floor(time());

// postpone form is submitted

if (try_request('submitted') == 'postponeform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $value   = ustrcut(try_request('duedate'), ustrlen(get_date(SAMPLE_DATE)));
    $comment = ustrcut(try_request('comment'), MAX_COMMENT_BODY);

    if (ustrlen($value) == 0)
    {
        debug_write_log(DEBUG_NOTICE, 'Date value is not specified.');
        echo(get_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID));
    }
    else
    {
        $duedate = ustr2date($value);

        if ($duedate == -1)
        {
            debug_write_log(DEBUG_NOTICE, 'Invalid date value.');
            echo(get_resource(RES_ALERT_INVALID_DATE_VALUE_ID));
            $duedate = $today + SECS_IN_WEEK;
        }
        elseif ($duedate < ($today + SECS_IN_DAY))
        {
            debug_write_log(DEBUG_NOTICE, 'Date value is out of range.');
            echo(ustrprocess(get_resource(RES_ALERT_DATE_VALUE_OUT_OF_RANGE_ID), get_date($today + SECS_IN_DAY), get_date(MAXINT)));
        }
        else
        {
            if (ustrlen($comment) != 0)
            {
                comment_add($id, $comment);
            }

            record_postpone($id, $duedate);
            $event = event_create($id, EVENT_RECORD_POSTPONED, time(), $duedate);
            event_mail($event);
        }
    }

    exit;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $duedate = $today + SECS_IN_WEEK;
    $comment = NULL;
}

// generate postpone form

$xml = '<form name="postponeform" action="javascript:submitPostponeForm(' . $id . ')">'
     . '<group title="' . get_html_resource(RES_POSTPONE_ID) . '">'
     . '<control name="duedate" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
     . '<label>' . sprintf('%s (%s)', get_html_resource(RES_DUEDATE_ID), get_html_resource(RES_YYYY_MM_DD_ID)) . '</label>'
     . '<editbox maxlen="' . ustrlen(get_date(SAMPLE_DATE)) . '">'
     . ustr2html(get_date($duedate))
     . '</editbox>'
     . '</control>'
     . '<control name="comment">'
     . '<label>' . get_html_resource(RES_COMMENT_ID) . '</label>'
     . '<textbox rows="' . HTML_TEXTBOX_MIN_HEIGHT . '" resizeable="true" maxlen="' . MAX_COMMENT_BODY . '">'
     . ustr2html($comment)
     . '</textbox>'
     . '</control>'
     . '</group>'
     . '<button default="true">'                . get_html_resource(RES_OK_ID)     . '</button>'
     . '<button action="cancelPostponeForm()">' . get_html_resource(RES_CANCEL_ID) . '</button>'
     . '<note>' . ustrprocess(get_html_resource(RES_ALERT_DATE_VALUE_OUT_OF_RANGE_ID), get_date($today + SECS_IN_DAY), get_date(MAXINT)) . '</note>'
     . '</form>';

echo(xml2html($xml));

?>
