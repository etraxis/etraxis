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
//  Artem Rodygin           2005-04-10      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-07-04      new-002: Email notifications.
//  Artem Rodygin           2005-08-23      new-053: All the calls of DAL API functions should be moved to DBO API.
//  Artem Rodygin           2005-08-30      bug-078: Record cannot be postponed.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-09-15      new-123: User should be prompted for optional comment when a record is being postponed.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-11-17      new-176: Change eTraxis design.
//  Artem Rodygin           2006-01-24      new-203: Email notification functionality (new-002) should be conditionally "compiled".
//  Artem Rodygin           2006-02-10      new-197: Postpone should have a timer for autoresume.
//  Artem Rodygin           2006-02-17      bug-211: PHP Warning: date(): Windows does not support dates prior to midnight (00:00:00), January 1, 1970
//  Artem Rodygin           2006-03-19      bug-214: Wrong notice about 'record_validate' in debug logs of 'postpone.php'.
//  Artem Rodygin           2006-09-26      new-318: Group permissions should be template-wide.
//  Artem Rodygin           2006-12-23      new-463: Date field names should be extended with date format explanation.
//  Artem Rodygin           2007-02-25      bug-497: Cannot postpone record till tomorrow.
//  Artem Rodygin           2008-01-11      bug-663: Author permissions are ignored.
//  Artem Rodygin           2008-04-20      new-703: Separated permissions set for current responsible.
//  Artem Rodygin           2008-09-11      new-716: 'Today' value in date field range.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-04-25      new-801: Range of valid date values must be related to current date.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//  Giacomo Giustozzi       2010-02-10      new-913: Resizable text boxes
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/fields.php');
require_once('../dbo/events.php');
require_once('../dbo/records.php');
/**#@-*/

init_page();

$id     = ustr2int(try_request('id'));
$record = record_find($id);

if (!$record)
{
    debug_write_log(DEBUG_NOTICE, 'Record cannot be found.');
    header('Location: index.php');
    exit;
}

$permissions = record_get_permissions($record['template_id'], $record['creator_id'], $record['responsible_id']);

if (!can_record_be_postponed($record, $permissions))
{
    debug_write_log(DEBUG_NOTICE, 'Record cannot be postponed.');
    header('Location: view.php?id=' . $id);
    exit;
}

$today = date_floor(time());

if (try_request('submitted') == 'mainform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $alert = NULL;

    $value   = ustrcut($_REQUEST['duedate'], ustrlen(get_date(SAMPLE_DATE)));
    $comment = ustrcut($_REQUEST['comment'], MAX_COMMENT_BODY);

    if (ustrlen($value) == 0)
    {
        debug_write_log(DEBUG_NOTICE, 'Date value is not specified.');
        $alert = get_js_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID);
    }
    else
    {
        $duedate = ustr2date($value);

        if ($duedate == -1)
        {
            debug_write_log(DEBUG_NOTICE, 'Invalid date value.');
            $alert = get_js_resource(RES_ALERT_INVALID_DATE_VALUE_ID);
            $duedate = $today + SECS_IN_WEEK;
        }
        elseif ($duedate < ($today + SECS_IN_DAY))
        {
            debug_write_log(DEBUG_NOTICE, 'Date value is out of range.');
            $alert = ustrprocess(get_js_resource(RES_ALERT_DATE_VALUE_OUT_OF_RANGE_ID), get_date($today + SECS_IN_DAY), get_date(MAXINT));
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

            header('Location: view.php?id=' . $id);
            exit;
        }
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $duedate = $today + SECS_IN_WEEK;
    $comment = NULL;
}

$xml = '<page' . gen_xml_page_header(record_id($id, $record['template_prefix']), isset($alert) ? $alert : NULL, 'mainform.duedate') . '>'
     . gen_xml_menu()
     . '<path>'
     . gen_xml_rec_root(try_cookie(COOKIE_SEARCH_MODE, FALSE))
     . '<pathitem url="view.php?id='     . $id . '">' . ustrprocess(get_html_resource(RES_RECORD_X_ID), record_id($id, $record['template_prefix'])) . '</pathitem>'
     . '<pathitem url="postpone.php?id=' . $id . '">' . get_html_resource(RES_POSTPONE_ID) . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<form name="mainform" action="postpone.php?id=' . $id . '">'
     . '<group>'
     . '<editbox label="' . sprintf('%s (%s)', get_html_resource(RES_DUEDATE_ID), get_html_resource(RES_YYYY_MM_DD_ID)) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="duedate" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(get_date(SAMPLE_DATE)) . '">' . ustr2html(get_date($duedate)) . '</editbox>'
     . '<textbox label="' . get_html_resource(RES_COMMENT_ID) . '" name="comment" width="' . HTML_TEXTBOX_WIDTH . '" height="' . HTML_TEXTBOX_MIN_HEIGHT . '" resizeable="true" maxlen="' . MAX_COMMENT_BODY . '">' . ustr2html($comment) . '</textbox>'
     . '</group>'
     . '<button default="true">'                . get_html_resource(RES_OK_ID)     . '</button>'
     . '<button url="view.php?id=' . $id . '">' . get_html_resource(RES_CANCEL_ID) . '</button>'
     . '<note>' . ustrprocess(get_html_resource(RES_ALERT_DATE_VALUE_OUT_OF_RANGE_ID), get_date($today + SECS_IN_DAY), get_date(MAXINT)) . '</note>'
     . '</form>'
     . '</content>'
     . '</page>';

echo(xml2html($xml));

?>
