<?php

/**
 * Events
 *
 * This module provides API to work with events of records.
 * See also {@link http://www.etraxis.org/docs-schema.php#tbl_events tbl_events} database table.
 *
 * @package DBO
 * @subpackage Events
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
//  Artem Rodygin           2005-04-10      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-07-04      new-002: Email notifications.
//  Artem Rodygin           2005-07-28      new-012: Records field 'description' should be renamed with 'subject'.
//  Artem Rodygin           2005-07-28      bug-015: PHP Notice: Undefined variable: to
//  Artem Rodygin           2005-08-02      new-017: Email notifications filter.
//  Artem Rodygin           2005-08-13      bug-024: PHP Warning: odbc_exec(): SQL error: Line 1: Incorrect syntax near '2'.
//  Artem Rodygin           2005-08-13      new-020: Clone the records.
//  Artem Rodygin           2005-08-16      bug-029: Email notifications are not received by recipients.
//  Artem Rodygin           2005-08-18      new-030: UI language should be set for each user separately.
//  Artem Rodygin           2005-08-23      bug-045: When record is being cloned wrong event is recorded.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-09-06      bug-093: Email notifications are not recieved.
//  Artem Rodygin           2005-09-13      new-113: When record is being viewed the fields names and values should be aligned by top.
//  Artem Rodygin           2005-09-13      new-115: Add record ID to the subject of email notifications.
//  Artem Rodygin           2005-09-13      new-116: Remove user login from the subject of email notifications.
//  Artem Rodygin           2005-09-14      bug-118: Email notifications are not sent.
//  Artem Rodygin           2005-09-15      new-119: Record creator should be specified in general information of email notifications.
//  Artem Rodygin           2005-09-17      new-125: Email notifications advanced filter.
//  Artem Rodygin           2005-09-18      new-131: Comment text should be present in notification when new comment has been added.
//  Artem Rodygin           2005-09-19      bug-132: Email notifications are not sent.
//  Artem Rodygin           2005-09-27      new-141: Source code review.
//  Artem Rodygin           2005-09-27      bug-142: PHP Notice: Use of undefined constant DEFAULT_NOTIFY_FLAG
//  Artem Rodygin           2005-10-05      bug-146: 'Author' in email notifications is not bold.
//  Artem Rodygin           2005-10-05      bug-151: User should recieve email notifications in language of his current settings.
//  Artem Rodygin           2005-10-09      bug-156: Email notifications are not sent.
//  Artem Rodygin           2005-10-15      new-153: Users should *always* receieve notifications about records which are created by them or assigned on.
//  Artem Rodygin           2005-10-16      bug-161: False modification event.
//  Artem Rodygin           2005-10-27      new-169: Append 'add comment' URL to email notifications.
//  Artem Rodygin           2005-10-27      bug-170: 'New line' characters are missed in email notifications.
//  Artem Rodygin           2006-01-24      new-203: Email notification functionality (new-002) should be conditionally "compiled".
//  Artem Rodygin           2006-02-10      new-197: Postpone should have a timer for autoresume.
//  Artem Rodygin           2006-05-26      new-259: Notifications should be sent to all previous responsibles, not to current one only.
//  Artem Rodygin           2006-06-19      new-236: Single record subscription.
//  Artem Rodygin           2006-06-28      new-271: Maximum execution time should be temporary unlimited during sending mail operations.
//  Artem Rodygin           2006-10-08      bug-331: /src/dbo/events.php: $item is passed by reference without being modified.
//  Artem Rodygin           2006-11-13      new-368: User should be able to subscribe other persons.
//  Artem Rodygin           2006-11-15      new-374: Carbon copies in subscriptions.
//  Artem Rodygin           2006-11-15      bug-385: PHP Warning: in_array(): Wrong datatype for second argument
//  Artem Rodygin           2006-11-15      bug-387: User receives notifications about his own events.
//  Artem Rodygin           2006-11-20      new-392: Local users should not be extended with '@eTraxis' when LDAP is disabled.
//  Artem Rodygin           2006-12-04      bug-417: SQL time is too large when no filters are applied.
//  Artem Rodygin           2006-12-11      bug-434: Email notifications are malfunctional at UNIX systems.
//  Artem Rodygin           2007-01-18      bug-488: Obsolete 'add comment' link in notifications.
//  Artem Rodygin           2007-04-03      new-512: Banner about 'no reply on autogenerated message' for notifications.
//  Artem Rodygin           2007-06-27      bug-523: Wrong language of "do not reply" banner in email notifications.
//  Artem Rodygin           2007-06-30      bug-526: Persons, subscribed by others, do not recieve notifications.
//  Artem Rodygin           2007-07-04      new-533: Links between records.
//  Artem Rodygin           2007-07-16      new-546: Confidential comments.
//  Artem Rodygin           2007-07-26      bug-547: Notifications about confidential comments are not sent.
//  Artem Rodygin           2007-08-06      new-551: Rework dependencies into "parent-child" relations.
//  Artem Rodygin           2007-11-13      new-622: Rename 'children' into 'subrecords'.
//  Artem Rodygin           2007-11-27      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2007-11-29      new-637: Subject of notifications should contain subject of records.
//  Artem Rodygin           2007-12-06      bug-643: /src/dbo/events.php: The value of variable $rec_id was never used.
//  Yury Udovichenko        2007-12-25      new-485: Text formating in comments.
//  Yury Udovichenko        2007-12-28      new-656: BBCode // List of tags, allowed in subject, should be limited.
//  Artem Rodygin           2008-01-16      bug-665: Notifications // Author permissions are ignored.
//  Denis Makovkin          2008-02-15      bug-674: [SF1893539] Incorrect charset in "Subject" email notifications
//  Artem Rodygin           2008-02-27      new-676: [SF1898731] Delete Issues from Workflow
//  Artem Rodygin           2008-06-21      new-723: Wrap calls of 'mail' function.
//  Artem Rodygin           2008-06-22      bug-718: Carbon-copies in subscriptions are ignored.
//  Artem Rodygin           2008-06-30      bug-727: Notifications are not sent via Lotus Domino SMTP server.
//  Artem Rodygin           2008-07-31      bug-736: Search tags are contained in subject of notification when event is for one of records from the search results list.
//  Artem Rodygin           2008-09-17      new-743: Include attached files in the notification.
//  Artem Rodygin           2008-11-11      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2008-11-18      bug-764: PHP Warning: odbc_exec(): SQL error: Line 1: Incorrect syntax near '='.
//  Artem Rodygin           2009-04-12      bug-815: Empty "event" field in notification about subscription.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//  Artem Rodygin           2009-06-17      bug-825: Database gets empty strings instead of NULL values.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/accounts.php');
require_once('../dbo/states.php');
/**#@-*/

//--------------------------------------------------------------------------------------------------
//  Definitions.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Event type.
 */
define('EVENT_UNUSED',               0);
define('EVENT_RECORD_CREATED',       1);
define('EVENT_RECORD_ASSIGNED',      2);
define('EVENT_RECORD_MODIFIED',      3);
define('EVENT_RECORD_STATE_CHANGED', 4);
define('EVENT_RECORD_POSTPONED',     5);
define('EVENT_RECORD_RESUMED',       6);
define('EVENT_COMMENT_ADDED',        7);
define('EVENT_FILE_ATTACHED',        8);
define('EVENT_FILE_REMOVED',         9);
define('EVENT_RECORD_CLONED',        10);
define('EVENT_SUBRECORD_ADDED',      11);
define('EVENT_SUBRECORD_REMOVED',    12);
define('EVENT_CONFIDENTIAL_COMMENT', 13);
define('EVENT_RECORD_SUBSCRIBED',    100);
define('EVENT_RECORD_UNSUBSCRIBED',  101);
/**#@-*/

/**#@+
 * Permission.
 */
define('PERMIT_CREATE_RECORD',         0x0001);
define('PERMIT_MODIFY_RECORD',         0x0002);
define('PERMIT_POSTPONE_RECORD',       0x0004);
define('PERMIT_RESUME_RECORD',         0x0008);
define('PERMIT_REASSIGN_RECORD',       0x0010);
define('PERMIT_CHANGE_STATE',          0x0020);
define('PERMIT_ADD_COMMENTS',          0x0040);
define('PERMIT_ATTACH_FILES',          0x0080);
define('PERMIT_REMOVE_FILES',          0x0100);
define('PERMIT_CONFIDENTIAL_COMMENTS', 0x0200);
define('PERMIT_SEND_REMINDERS',        0x0400);
define('PERMIT_DELETE_RECORD',         0x0800);
define('PERMIT_ADD_SUBRECORDS',        0x1000);
define('PERMIT_REMOVE_SUBRECORDS',     0x2000);
define('PERMIT_VIEW_RECORD',       0x40000000);
/**#@-*/

/**#@+
 * Notifications filter.
 */
define('NOTIFY_RECORD_CREATED',       0x0001);
define('NOTIFY_RECORD_ASSIGNED',      0x0002);
define('NOTIFY_RECORD_MODIFIED',      0x0004);
define('NOTIFY_RECORD_STATE_CHANGED', 0x0008);
define('NOTIFY_RECORD_POSTPONED',     0x0010);
define('NOTIFY_RECORD_RESUMED',       0x0020);
define('NOTIFY_COMMENT_ADDED',        0x0040);
define('NOTIFY_FILE_ATTACHED',        0x0080);
define('NOTIFY_FILE_REMOVED',         0x0100);
define('NOTIFY_RECORD_CLONED',        0x0200);
define('NOTIFY_SUBRECORD_ADDED',      0x0400);
define('NOTIFY_SUBRECORD_REMOVED',    0x0800);
/**#@-*/

// Correspondence between event types
// and notifications filter.
$notifications_filter = array
(
    EVENT_RECORD_CREATED       => NOTIFY_RECORD_CREATED,
    EVENT_RECORD_ASSIGNED      => NOTIFY_RECORD_ASSIGNED,
    EVENT_RECORD_MODIFIED      => NOTIFY_RECORD_MODIFIED,
    EVENT_RECORD_STATE_CHANGED => NOTIFY_RECORD_STATE_CHANGED,
    EVENT_RECORD_POSTPONED     => NOTIFY_RECORD_POSTPONED,
    EVENT_RECORD_RESUMED       => NOTIFY_RECORD_RESUMED,
    EVENT_COMMENT_ADDED        => NOTIFY_COMMENT_ADDED,
    EVENT_FILE_ATTACHED        => NOTIFY_FILE_ATTACHED,
    EVENT_FILE_REMOVED         => NOTIFY_FILE_REMOVED,
    EVENT_RECORD_CLONED        => NOTIFY_RECORD_CLONED,
    EVENT_SUBRECORD_ADDED      => NOTIFY_SUBRECORD_ADDED,
    EVENT_SUBRECORD_REMOVED    => NOTIFY_SUBRECORD_REMOVED,
    EVENT_CONFIDENTIAL_COMMENT => NOTIFY_COMMENT_ADDED,
);

//--------------------------------------------------------------------------------------------------
//  Functions.
//--------------------------------------------------------------------------------------------------

/**
 * Finds in database and returns the information about specified event of particular record.
 *
 * @param int $record_id {@link http://www.etraxis.org/docs-schema.php#tbl_records_record_id Record ID}.
 * @param int $type {@link http://www.etraxis.org/docs-schema.php#tbl_events_event_type Type of event}.
 * @param int $time {@link http://en.wikipedia.org/wiki/Unix_time Unix timestamp} of event.
 * @return array Array with data if event is found in database, FALSE otherwise.
 */
function event_find ($record_id, $type, $time)
{
    debug_write_log(DEBUG_TRACE, '[event_find]');
    debug_write_log(DEBUG_DUMP,  '[event_find] $record_id = ' . $record_id);
    debug_write_log(DEBUG_DUMP,  '[event_find] $type      = ' . $type);
    debug_write_log(DEBUG_DUMP,  '[event_find] $time      = ' . $time);

    $rs = dal_query('events/fndk.sql',
                    $record_id,
                    $_SESSION[VAR_USERID],
                    $type,
                    $time);

    return ($rs->rows == 0 ? FALSE : $rs->fetch());
}

/**
 * Creates new event for specified record.
 *
 * @param int $record_id {@link http://www.etraxis.org/docs-schema.php#tbl_records_record_id Record ID}.
 * @param int $type {@link http://www.etraxis.org/docs-schema.php#tbl_events_event_type Type of event}.
 * @param int $time {@link http://en.wikipedia.org/wiki/Unix_time Unix timestamp} of event.
 * @param int $param {@link http://www.etraxis.org/docs-schema.php#tbl_events_event_param Parameter of event}, depends on the event type.
 * @return array Array with data of event if it was successfully created, FALSE otherwise.
 */
function event_create ($record_id, $type, $time, $param = NULL)
{
    debug_write_log(DEBUG_TRACE, '[event_create]');
    debug_write_log(DEBUG_DUMP,  '[event_create] $record_id = ' . $record_id);
    debug_write_log(DEBUG_DUMP,  '[event_create] $type      = ' . $type);
    debug_write_log(DEBUG_DUMP,  '[event_create] $time      = ' . $time);
    debug_write_log(DEBUG_DUMP,  '[event_create] $param     = ' . $param);

    dal_query('events/create.sql',
              $record_id,
              $_SESSION[VAR_USERID],
              $type,
              $time,
              is_null($param) ? NULL : $param);

    dal_query('records/change.sql',
              $record_id,
              $time);

    return event_find($record_id, $type, $time);
}

/**
 * Deletes specified event.
 *
 * @param int $event_id {@link http://www.etraxis.org/docs-schema.php#tbl_events_event_id ID} of event to be deleted.
 * @return int Always {@link NO_ERROR}.
 */
function event_destroy ($event_id)
{
    debug_write_log(DEBUG_TRACE, '[event_destroy]');
    debug_write_log(DEBUG_DUMP,  '[event_destroy] $event_id = ' . $event_id);

    dal_query('events/delete.sql', $event_id);

    return NO_ERROR;
}

/**
 * Generates and returns string of text, describing specified event.
 *
 * @param int $event_id {@link http://www.etraxis.org/docs-schema.php#tbl_events_event_id ID} of event to be described.
 * @param int $event_type {@link http://www.etraxis.org/docs-schema.php#tbl_events_event_type Type of event}.
 * @param int $event_param {@link http://www.etraxis.org/docs-schema.php#tbl_events_event_param Parameter of event}, depends on the event type.
 * @param int $locale ID of language. If omitted, then language of current user, or (when user is not logged in) default language will be used (see {@link LANG_DEFAULT}).
 * @return string Event description, or NULL on failure.
 */
function get_event_string ($event_id, $event_type, $event_param, $locale = NULL)
{
    debug_write_log(DEBUG_TRACE, '[get_event_string]');
    debug_write_log(DEBUG_DUMP,  '[get_event_string] $event_id    = ' . $event_id);
    debug_write_log(DEBUG_DUMP,  '[get_event_string] $event_type  = ' . $event_type);
    debug_write_log(DEBUG_DUMP,  '[get_event_string] $event_param = ' . $event_param);
    debug_write_log(DEBUG_DUMP,  '[get_event_string] $locale      = ' . $locale);

    $res = NULL;

    switch ($event_type)
    {
        case EVENT_RECORD_CREATED:
            $state = state_find($event_param);
            $res = ustrprocess(get_html_resource(RES_EVENT_RECORD_CREATED_ID, $locale), ustr2html($state['state_name']));
            break;

        case EVENT_RECORD_ASSIGNED:
            $account = account_find($event_param);
            $res = ustrprocess(get_html_resource(RES_EVENT_RECORD_ASSIGNED_ID, $locale), ustr2html(is_null($locale) ? $account['fullname'] . ' (' . account_get_username($account['username']) . ')' : $account['fullname']));
            break;

        case EVENT_RECORD_MODIFIED:
            $res = get_html_resource(RES_EVENT_RECORD_MODIFIED_ID, $locale);
            break;

        case EVENT_RECORD_STATE_CHANGED:
            $state = state_find($event_param);
            $res = ustrprocess(get_html_resource(RES_EVENT_RECORD_STATE_CHANGED_ID, $locale), ustr2html($state['state_name']));
            break;

        case EVENT_RECORD_POSTPONED:
            $res = ustrprocess(get_html_resource(RES_EVENT_RECORD_POSTPONED_ID, $locale), get_date($event_param, $locale));
            break;

        case EVENT_RECORD_RESUMED:
            $res = get_html_resource(RES_EVENT_RECORD_RESUMED_ID, $locale);
            break;

        case EVENT_COMMENT_ADDED:
            $res = get_html_resource(RES_EVENT_COMMENT_ADDED_ID, $locale);
            break;

        case EVENT_FILE_ATTACHED:
            $rs2 = dal_query('attachs/fndk.sql', $event_id);
            $name = ($rs2->rows == 0 ? '?' : $rs2->fetch('attachment_name'));
            $res = ustrprocess(get_html_resource(RES_EVENT_FILE_ATTACHED_ID, $locale), ustr2html($name));
            break;

        case EVENT_FILE_REMOVED:
            $rs2 = dal_query('attachs/fndid.sql', $event_param);
            $name = ($rs2->rows == 0 ? '?' : $rs2->fetch('attachment_name'));
            $res = ustrprocess(get_html_resource(RES_EVENT_FILE_REMOVED_ID, $locale), ustr2html($name));
            break;

        case EVENT_RECORD_CLONED:
            $res = ustrprocess(get_html_resource(RES_EVENT_RECORD_CLONED_ID, $locale), str_pad($event_param, 3, '0', STR_PAD_LEFT));
            break;

        case EVENT_SUBRECORD_ADDED:
            $res = ustrprocess(get_html_resource(RES_EVENT_SUBRECORD_ADDED_ID, $locale), str_pad($event_param, 3, '0', STR_PAD_LEFT));
            break;

        case EVENT_SUBRECORD_REMOVED:
            $res = ustrprocess(get_html_resource(RES_EVENT_SUBRECORD_REMOVED_ID, $locale), str_pad($event_param, 3, '0', STR_PAD_LEFT));
            break;

        case EVENT_CONFIDENTIAL_COMMENT:
            $res = get_html_resource(RES_EVENT_CONFIDENTIAL_COMMENT_ADDED_ID, $locale);
            break;

        case EVENT_RECORD_SUBSCRIBED:
            $res = ustrprocess(get_html_resource(RES_SUBJECT_SUBSCRIBED_ID, $locale), ustr2html($event_param));
            break;

        case EVENT_RECORD_UNSUBSCRIBED:
            $res = ustrprocess(get_html_resource(RES_SUBJECT_UNSUBSCRIBED_ID, $locale), ustr2html($event_param));
            break;

        default:
            debug_write_log(DEBUG_WARNING, 'Unknown event type = ' . $event_type);
    }

    return $res;
}

/**
 * Generates and returns message body for mail notification about specified event of particular record.
 *
 * @param array $record Array with data of record (e.g. how it's returned by {@link record_find}).
 * @param array $event Array with data of event (e.g. how it's returned by {@link event_find}).
 * @param int $locale ID of language. If omitted, then language of current user, or (when user is not logged in) default language will be used (see {@link LANG_DEFAULT}).
 * @return string Generated message body.
 */
function generate_message ($record, $event, $locale = NULL)
{
    debug_write_log(DEBUG_TRACE, '[generate_message]');
    debug_write_log(DEBUG_DUMP,  '[generate_message] $locale = ' . $locale);

    $message =
        '<html>' .
        '<body>' .
        '<b><font color="red">' . get_html_resource(RES_ALERT_DO_NOT_REPLY_ID, $locale) . '</font></b><br/>' .
        '<hr/>' .
        get_html_resource(RES_GENERAL_INFO_ID, $locale) .
        '<hr/>' .
        '<table border="0" cellspacing="0" cellpadding="5">' .
        '<tr valign="top">' .
        '<td><b>' . get_html_resource(RES_PROJECT_ID, $locale) . ':</b></td>' .
        '<td>'    . ustr2html($record['project_name']) . '</td>' .
        '</tr>' .
        '<tr valign="top">' .
        '<td><b>' . get_html_resource(RES_TEMPLATE_ID, $locale) . ':</b></td>' .
        '<td>'    . ustr2html($record['template_name']) . '</td>' .
        '</tr>' .
        '<tr valign="top">' .
        '<td><b>' . get_html_resource(RES_STATE_ID, $locale) . ':</b></td>' .
        '<td>'    . ustr2html($record['state_name']) . '</td>' .
        '</tr>' .
        '<tr valign="top">' .
        '<td><b>' . get_html_resource(RES_AGE_ID, $locale) . ':</b></td>' .
        '<td>'    . get_record_age($record) . '</td>' .
        '</tr>' .
        '<tr valign="top">' .
        '<td><b>' . get_html_resource(RES_AUTHOR_ID, $locale) . ':</b></td>' .
        '<td>'  . ustr2html($record['author_fullname']) . ' (' . ustr2html(account_get_username($record['author_username'])) . ')</td>' .
        '</tr>' .
        '<tr valign="top">' .
        '<td><b>' . get_html_resource(RES_RESPONSIBLE_ID, $locale) . ':</b></td>' .
        '<td>'    . (is_null($record['username']) ? get_html_resource(RES_NONE_ID, $locale) : ustr2html($record['fullname']) . ' (' . ustr2html(account_get_username($record['username'])) . ')') . '</td>' .
        '</tr>' .
        '<tr valign="top">' .
        '<td><b>' . get_html_resource(RES_EVENT_ID, $locale) . ':</b></td>' .
        '<td>'    . get_event_string($event['event_id'], $event['event_type'], $event['event_param'], $locale) . '</td>' .
        '</tr>' .
        '</table>';

    if (!is_null($event) &&
        ($event['event_type'] == EVENT_COMMENT_ADDED || $event['event_type'] == EVENT_CONFIDENTIAL_COMMENT))
    {
        $rs = dal_query('comments/fndk.sql', $event['event_id']);

        if ($rs->rows != 0)
        {
            $message .=
                '<hr/>' .
                '<br/>' .
                str_replace('%br;', '<br/>', update_references($rs->fetch('comment_body')));
        }
    }

    $message .=
        '<hr/>' .
        '<br/><a href="' . WEBROOT . 'records/view.php?id=' . $record['record_id'] . '">' . get_html_resource(RES_VIEW_RECORD_ID, $locale) . '</a>' .
        '</body>' .
        '</html>';

    return $message;
}

/**
 * Sends mail notification about specified event to all interested parties.
 *
 * @param array $event Array with data of event (e.g. how it's returned by {@link event_find}).
 * @param int $attachment_id {@link http://www.etraxis.org/docs-schema.php#tbl_attachments_attachment_id ID} of new attachment if event is about attached file, NULL otherwise.
 * @param string $attachment_name {@link http://www.etraxis.org/docs-schema.php#tbl_attachments_attachment_name Name} of new attachment if event is about attached file, NULL otherwise.
 * @param string $attachment_type {@link http://www.etraxis.org/docs-schema.php#tbl_attachments_attachment_type MIME type} of new attachment if event is about attached file, NULL otherwise.
 * @param int $attachment_size {@link http://www.etraxis.org/docs-schema.php#tbl_attachments_attachment_size Size} of new attachment if event is about attached file, NULL otherwise.
 * @return int Always {@link NO_ERROR}.
 */
function event_mail ($event, $attachment_id = NULL, $attachment_name = NULL, $attachment_type = NULL, $attachment_size = NULL)
{
    debug_write_log(DEBUG_TRACE, '[event_mail]');

    global $notifications_filter;
    global $locale_info;

    // If debug mode is turned on full level, we go through the function even email notifications
    // functionality is disabled, but don't send the email.
    if (!EMAIL_NOTIFICATIONS_ENABLED && DEBUG_MODE != DEBUG_MODE_FULL)
    {
        debug_write_log(DEBUG_NOTICE, '[event_mail] Email notifications are disabled.');
        return NO_ERROR;
    }

    // Find info about currently logged in user, who is always an author of the event.
    $account = account_find($_SESSION[VAR_USERID]);

    if (!$account)
    {
        debug_write_log(DEBUG_WARNING, '[event_mail] Account cannot be found.');
        return ERROR_NOT_FOUND;
    }

    // Find info about record, which the event is of.
    $rs = dal_query('records/fndid.sql', $event['record_id'], time());

    if ($rs->rows == 0)
    {
        debug_write_log(DEBUG_WARNING, '[event_mail] Record not found = ' . $event['record_id']);
        return ERROR_NOT_FOUND;
    }

    // Since sending email can takes a time, disable PHP execution timeout.
    set_time_limit(0);

    $record = $rs->fetch();

    $responsibles = array();    // IDs of everyone, who was assignee of the record at least once
    $subscribes   = array();    // IDs of everyone, who is subscribed to the record
    $allowed      = array();    // email addresses of everyone, who is permitted to see this event

    // Enumerate IDs of everyone, who was assignee of the record at least once.
    $rs = dal_query('events/fnd.sql', $record['record_id'], EVENT_RECORD_ASSIGNED);

    while (($row = $rs->fetch()))
    {
        array_push($responsibles, $row[0]);
    }

    // Enumerate IDs of everyone, who is subscribed to the record.
    $rs = dal_query('records/subscribes.sql', $record['record_id']);

    while (($row = $rs->fetch()))
    {
        array_push($subscribes, $row[0]);
    }

    // Enumerate email addresses of everyone, who is permitted to see this event.
    $rs = dal_query((DATABASE_DRIVER == DRIVER_ORACLE9 ? 'groups/oracle/gplist2.sql' : 'groups/gplist2.sql'),
                    $record['record_id'],
                    ($event['event_type'] == EVENT_CONFIDENTIAL_COMMENT ? PERMIT_CONFIDENTIAL_COMMENTS : PERMIT_VIEW_RECORD));

    while (($row = $rs->fetch()))
    {
        array_push($allowed, $row['email']);
    }

    // Author of record and current responsible are permitted to see any event besides confidential comment.
    if ($event['event_type'] != EVENT_CONFIDENTIAL_COMMENT)
    {
        $author      = account_find($record['creator_id']);
        $responsible = (is_null($record['responsible_id'])
                        ? FALSE
                        : account_find($record['responsible_id']));

        if ($author)
        {
            array_push($allowed, $author['email']);
        }

        if ($responsible)
        {
            array_push($allowed, $responsible['email']);
        }
    }

    // Get IDs of all supported locales.
    $supported_locales = array_keys($locale_info);

    // Send separated notification for each locale in corresponding language.
    foreach ($supported_locales as $locale)
    {
        $to = array();  // email addresses of recipients for TO field
        $cc = array();  // email addresses of recipients for CC field

        // Enumerate all project members with specified locale in settings.
        $rs = dal_query('records/members.sql', $event['project_id'], $locale);

        while (($row = $rs->fetch()))
        {
            // Skip author of event.
            if ($row['account_id'] == $_SESSION[VAR_USERID])
            {
                debug_write_log(DEBUG_TRACE, '[event_mail] User is author of event.');
                continue;
            }

            // Submitter of the record should receive notification of each record's event.
            if ($row['account_id'] == $record['creator_id'])
            {
                debug_write_log(DEBUG_TRACE, '[event_mail] User is creator of record.');
                array_push($to, $row['email']);
            }
            // Current assignee of the record should receive notification of each record's event.
            elseif ($row['account_id'] == $record['responsible_id'])
            {
                debug_write_log(DEBUG_TRACE, '[event_mail] User is responsible of record.');
                array_push($to, $row['email']);
            }
            // Ex assignee of the record should receive notification of each record's event.
            elseif (in_array($row['account_id'], $responsibles))
            {
                debug_write_log(DEBUG_TRACE, '[event_mail] User was responsible of record.');
                array_push($to, $row['email']);
            }
            // Subscribed to the record should receive notification of each record's event.
            elseif (in_array($row['account_id'], $subscribes))
            {
                debug_write_log(DEBUG_TRACE, '[event_mail] User is subscribed to record.');
                array_push($to, $row['email']);
            }
            else
            {
                debug_write_log(DEBUG_TRACE, '[event_mail] User should not recieve the notification.');
            }
        }

        // Enumerate all subscriptions for this template.
        $rs = dal_query('subscribes/enum.sql', $record['project_id'], $record['template_id'], $locale);

        while (($row = $rs->fetch()))
        {
            // Skip author of event.
            if ($row['account_id'] == $_SESSION[VAR_USERID])
            {
                debug_write_log(DEBUG_TRACE, '[event_mail] User is author of event.');
                continue;
            }

            // If the event corresponds to the subscription, add owner of this subscription to recipients.
            if (($row['subscribe_flags'] & $notifications_filter[$event['event_type']]) != 0)
            {
                debug_write_log(DEBUG_TRACE, '[event_mail] Event satisfies the filter.');
                array_push($to, $row['email']);

                // If subscription contains specified carbon copy, add the address to CC.
                if (ustrlen($row['carbon_copy']) != 0)
                {
                    debug_write_log(DEBUG_TRACE, '[event_mail] Carbon copy is set.');
                    $cc[$row['carbon_copy']] = $row['email'];
                }
            }
        }

        $to = array_intersect($to, $allowed);       // remove from TO everyone who is not permitted to see this event
        $cc = array_intersect($cc, $to);            // remove from CC all carbon copies which are not in TO anymore
        $to = array_merge($to, array_keys($cc));    // merge TO and CC
        $to = array_unique($to);                    // remove all duplicates inside TO

        // If we still have at least one recipient - send the mail.
        if (count($to) != 0)
        {
            $recipients = implode(', ', $to);
            $rec_id     = record_id($record['record_id'], $record['template_prefix']);
            $subject    = "[{$event['project_name']}] {$rec_id}: " . update_references($record['subject'], BBCODE_OFF);
            $message    = generate_message($record, $event, $locale);

            if (EMAIL_NOTIFICATIONS_ENABLED)
            {
                debug_write_log(DEBUG_NOTICE, '[event_mail] Sending email.');

                sendmail($account['fullname'],
                         $account['email'],
                         $recipients,
                         $subject,
                         $message,
                         $attachment_id,
                         $attachment_name,
                         $attachment_type,
                         $attachment_size);
            }
            else
            {
                // If debug mode is turned on full level, we goes through the function even email
                // notifications functionality is disabled, but do not send the email.
                debug_write_log(DEBUG_NOTICE, '[event_mail] Email notifications are disabled.');
            }
        }
    }

    // Restore PHP execution timeout, disabled above.
    ini_restore('max_execution_time');

    return NO_ERROR;
}

?>
