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
require_once('../dbo/accounts.php');
require_once('../dbo/states.php');
require_once('../dbo/fields.php');
require_once('../dbo/values.php');
require_once('../dbo/records.php');
require_once('../dbo/events.php');
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
     . '<breadcrumb url="events.php?id=' . $id . '">' . $title . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<tabs>'
     . gen_record_tabs($record, RECORD_TAB_EVENTS)
     . '<content>';

// go through the list of all state changing events and their fields

$responsible = FALSE;

$events = dal_query('records/elist2.sql', $id);

while (($event = $events->fetch()))
{
    if ($event['event_type'] == EVENT_RECORD_ASSIGNED)
    {
        $responsible = account_find($event['event_param']);
        $group_title = 'Reassigned';
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

        $group_title = ustr2html($event['state_name']);
    }
    else
    {
        continue;
    }

    $group_title .= ' - ' . get_datetime($event['event_time'])
                  . ' - ' . ustr2html(sprintf('%s (%s)', $event['fullname'], account_get_username($event['username'])));

    $xml .= '<group title="' . $group_title . '">'
          . '<text label="' . get_html_resource(RES_RESPONSIBLE_ID) . '">'
          . ($responsible ? ustr2html(sprintf('%s (%s)', $responsible['fullname'], account_get_username($responsible['username'])))
                          : get_html_resource(RES_NONE_ID))
          . '</text>';

    if ($event['event_type'] == EVENT_RECORD_CREATED ||
        $event['event_type'] == EVENT_RECORD_STATE_CHANGED)
    {
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

            $xml .= '<text label="' . ustr2html($field['field_name']) . '">'
                  . (is_null($value) ? get_html_resource(RES_NONE_ID) : update_references($value, BBCODE_ALL, $field['regex_search'], $field['regex_replace']))
                  . '</text>';

            if ($field['add_separator'])
            {
                $xml .= '<hr/>';
            }
        }
    }

    $xml .= '</group>';
}

$xml .= '</content>'
      . '</tabs>';

echo(xml2html($xml, $title));

?>
