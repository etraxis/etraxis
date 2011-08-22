<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2011  Artem Rodygin
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
require_once('../dbo/values.php');
/**#@-*/

// log user in via HTTP Basic Authentication

@session_start();

if (isset($_SERVER['PHP_AUTH_USER']))
{
    if (login_user($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) != NO_ERROR)
    {
        header('HTTP/1.0 401 Unauthorized');
        exit;
    }
}

init_page(LOAD_RSS);

// check that requested record exists

$id     = ustr2int(try_request('id'));
$record = record_find($id);

if (!$record)
{
    debug_write_log(DEBUG_NOTICE, 'Record cannot be found.');
    header('HTTP/1.0 404 Not Found');
    exit;
}

// get current user's permissions and verify them

$permissions = record_get_permissions($record['template_id'], $record['creator_id'], $record['responsible_id']);

if (!can_record_be_displayed($permissions))
{
    debug_write_log(DEBUG_NOTICE, 'Record cannot be displayed.');
    header('HTTP/1.0 403 Forbidden');
    exit;
}

// generate RSS feed

$feed_link   = WEBROOT . 'records/view.php?id=' . $id;
$language    = $locale_info[$_SESSION[VAR_LOCALE]][LOCALE_CODE];
$record_id   = record_id($record['record_id'], $record['template_prefix']);

$description = '<table border="0" cellspacing="0" cellpadding="5">'
             . '<tr valign="top">'
             . '<td><b>' . get_html_resource(RES_SUBJECT_ID) . ':</b></td>'
             . '<td>' . update_references($record['subject'], BBCODE_MINIMUM) . '</td>'
             . '</tr>'
             . '<tr valign="top">'
             . '<td><b>' . get_html_resource(RES_PROJECT_ID) . ':</b></td>'
             . '<td>' . ustr2html($record['project_name']) . '</td>'
             . '</tr>'
             . '<tr valign="top">'
             . '<td><b>' . get_html_resource(RES_TEMPLATE_ID) . ':</b></td>'
             . '<td>' . ustr2html($record['template_name']) . '</td>'
             . '</tr>'
             . '</table>';

$rss = <<<RSS
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/">
<channel>
<title>{$record_id}</title>
<link>{$feed_link}</link>
<description><![CDATA[{$description}]]></description>
<language>{$language}</language>
<ttl>120</ttl>
<atom:link href="{$feed_link}" rel="self" type="application/rss+xml" />
RSS;

$responsible = FALSE;

$events = dal_query('records/elist2.sql', $id);

while (($event = $events->fetch()))
{
    if ($event['event_type'] == EVENT_RECORD_ASSIGNED)
    {
        $responsible = account_find($event['event_param']);
    }
    elseif ($event['event_type'] == EVENT_RECORD_CREATED ||
            $event['event_type'] == EVENT_RECORD_STATE_CHANGED)
    {
        if ($event['responsible'] == STATE_RESPONSIBLE_REMOVE)
        {
            $responsible = FALSE;
        }
        elseif ($event['responsible'] == STATE_RESPONSIBLE_ASSIGN)
        {
            $responsible = account_find($events->fetch('event_param'));
        }
    }

    $content_keys   = array(get_html_resource(RES_ORIGINATOR_ID),
                            get_html_resource(RES_RESPONSIBLE_ID));

    $content_values = array(ustr2html(sprintf('%s (%s)', $event['fullname'], account_get_username($event['username']))),
                            $responsible ? ustr2html(sprintf('%s (%s)', $responsible['fullname'], account_get_username($responsible['username'])))
                                         : get_html_resource(RES_NONE_ID));

    switch ($event['event_type'])
    {
        case EVENT_RECORD_CREATED:
        case EVENT_RECORD_STATE_CHANGED:

            $fields = dal_query('records/flist2.sql',
                                $id,
                                $event['event_id'],
                                $event['state_id'],
                                $record['creator_id'],
                                is_null($record['responsible_id']) ? 0 : $record['responsible_id'],
                                $_SESSION[VAR_USERID],
                                FIELD_ALLOW_TO_READ);

            while (($field = $fields->fetch()))
            {
                $value = value_find($field['field_type'], $field['value_id']);

                if ($field['field_type'] == FIELD_TYPE_CHECKBOX)
                {
                    $value = get_html_resource($value ? RES_YES_ID : RES_NO_ID);
                }
                elseif ($field['field_type'] == FIELD_TYPE_LIST)
                {
                    $value = (is_null($value) ? NULL : value_find_listvalue($field['field_id'], $value));
                }
                elseif ($field['field_type'] == FIELD_TYPE_RECORD)
                {
                    $value = (is_null($value) ? NULL : 'rec#' . $value);
                }

                if (is_null($value))
                {
                    $value = get_html_resource(RES_NONE_ID);
                }
                else
                {
                    $value = str_replace('%br;', '<br/>', update_references($value, BBCODE_ALL, $field['regex_search'], $field['regex_replace']));
                    $value = mb_eregi_replace('%([A-Za-z]+);',          '&\1;', $value);
                    $value = mb_eregi_replace('%(#[0-9]{1,4});',        '&\1;', $value);
                    $value = mb_eregi_replace('%(#x[0-9A-Fa-f]{1,4});', '&\1;', $value);
                }

                array_push($content_keys,   ustr2html($field['field_name']));
                array_push($content_values, $value);
            }

            break;

        case EVENT_RECORD_MODIFIED:

            $rs = dal_query('changes/list2.sql',
                            $event['event_id'],
                            $record['creator_id'],
                            is_null($record['responsible_id']) ? 0 : $record['responsible_id'],
                            $_SESSION[VAR_USERID]);

            while (($row = $rs->fetch()))
            {
                $value = value_find($row['field_type'], $row['new_value_id']);

                if ($row['field_type'] == FIELD_TYPE_CHECKBOX)
                {
                    $value = get_html_resource($value ? RES_YES_ID : RES_NO_ID);
                }
                elseif ($row['field_type'] == FIELD_TYPE_LIST)
                {
                    $value = (is_null($value) ? NULL : value_find_listvalue($row['field_id'], $value));
                }
                elseif ($row['field_type'] == FIELD_TYPE_RECORD)
                {
                    $value = (is_null($value) ? NULL : 'rec#' . $value);
                }
                elseif ($row['field_type'] == FIELD_TYPE_DATE)
                {
                    $value = (is_null($value) ? NULL : get_date(ustr2date($value)));
                }

                if (!is_null($value))
                {
                    $value = str_replace('%br;', '<br/>', update_references($value));
                    $value = mb_eregi_replace('%([A-Za-z]+);',          '&\1;', $value);
                    $value = mb_eregi_replace('%(#[0-9]{1,4});',        '&\1;', $value);
                    $value = mb_eregi_replace('%(#x[0-9A-Fa-f]{1,4});', '&\1;', $value);
                }

                array_push($content_keys,   is_null($row['field_name']) ? get_html_resource(RES_SUBJECT_ID) : ustr2html($row['field_name']));
                array_push($content_values, is_null($value)             ? get_html_resource(RES_NONE_ID)    : $value);
            }

            break;

        case EVENT_COMMENT_ADDED:
        case EVENT_CONFIDENTIAL_COMMENT:

            $comment = comment_find($event['event_id'], $permissions);

            if (!$comment)
            {
                continue;
            }

            if ($comment['is_confidential'])
            {
                array_push($content_keys,   NULL);
                array_push($content_values, sprintf('<em>(%s)</em>', get_html_resource(RES_CONFIDENTIAL_ID)));
            }

            $value = str_replace('%br;', '<br/>', update_references($comment['comment_body']));
            $value = mb_eregi_replace('%([A-Za-z]+);',          '&\1;', $value);
            $value = mb_eregi_replace('%(#[0-9]{1,4});',        '&\1;', $value);
            $value = mb_eregi_replace('%(#x[0-9A-Fa-f]{1,4});', '&\1;', $value);

            array_push($content_keys,   NULL);
            array_push($content_values, $value);

            break;

        case EVENT_FILE_ATTACHED:
        case EVENT_FILE_REMOVED:

            if ($event['event_type'] == EVENT_FILE_ATTACHED)
            {
                $rs = dal_query('attachs/fndk.sql', $event['event_id']);
            }
            elseif ($event['event_type'] == EVENT_FILE_REMOVED)
            {
                $rs = dal_query('attachs/fndid.sql', $event['event_param']);
            }

            if ($rs->rows != 0)
            {
                $attachment = $rs->fetch();

                array_push($content_keys,   get_html_resource(RES_ATTACHMENT_NAME_ID));
                array_push($content_values, ustr2html($attachment['attachment_name']));

                array_push($content_keys,   get_html_resource(RES_SIZE_ID));
                array_push($content_values, ustrprocess(get_html_resource(RES_KB_ID), sprintf('%01.2f', $attachment['attachment_size'] / 1024)));
            }

            break;

        case EVENT_RECORD_CLONED:
        case EVENT_SUBRECORD_ADDED:
        case EVENT_SUBRECORD_REMOVED:

            $record2 = record_find($event['event_param']);

            if ($record2)
            {
                $permissions2 = record_get_permissions($record2['template_id'], $record2['creator_id'], $record2['responsible_id']);

                if (can_record_be_displayed($permissions2))
                {
                    array_push($content_keys,   record_id($record2['record_id'], $record2['template_prefix']));
                    array_push($content_values, update_references($record2['subject'], BBCODE_MINIMUM));
                }
            }

            break;

        case EVENT_RECORD_ASSIGNED:
        case EVENT_RECORD_POSTPONED:
        case EVENT_RECORD_RESUMED:

            // nop

            break;

        default:

            continue;
    }

    $guid  = md5($event['event_id']);
    $date  = date('r', $event['event_time']);
    $title = get_event_string($event['event_id'], $event['event_type'], $event['event_param']);

    $content = '<table border="0" cellspacing="0" cellpadding="5">';

    foreach ($content_keys as $i => $key)
    {
        $content .= '<tr valign="top">';

        if (is_null($key))
        {
            $content .= '<td colspan="2">' . $content_values[$i] . '</td>';
        }
        else
        {
            $content .= '<td><b>' . $key . ':</b></td>';
            $content .= '<td>' . $content_values[$i] . '</td>';
        }

        $content .= '</tr>';
    }

    $content .= '</table>';

    $rss .= <<<RSS
            <item>
            <guid isPermaLink="false">{$guid}</guid>
            <link>{$feed_link}</link>
            <pubDate>{$date}</pubDate>
            <title>{$title}</title>
            <description><![CDATA[{$content}]]></description>
            </item>
RSS;
}

$rss .= <<<RSS
</channel>
</rss>
RSS;

header('Content-Type: application/rss+xml; charset=UTF-8');
echo($rss);

?>
