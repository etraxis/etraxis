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
require_once('../dbo/groups.php');
require_once('../dbo/reminders.php');
/**#@-*/

init_page();

if (!EMAIL_NOTIFICATIONS_ENABLED)
{
    debug_write_log(DEBUG_NOTICE, 'Email Notifications functionality is disabled.');
    header('Location: ../index.php');
    exit;
}

if (!can_reminder_be_created())
{
    debug_write_log(DEBUG_NOTICE, 'Reminders are denied.');
    header('Location: ../index.php');
    exit;
}

// check that requested reminder exists

$id       = ustr2int(try_request('id'));
$reminder = reminder_find($id);

if (!$reminder)
{
    debug_write_log(DEBUG_NOTICE, 'Reminder cannot be found.');
    header('Location: index.php');
    exit;
}

// page's title

$title = ustrprocess(get_html_resource(RES_REMINDER_X_ID), ustr2html($reminder['reminder_name']));

// generate breadcrumbs and tabs

$xml = '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_REMINDERS_ID) . '</breadcrumb>'
     . '<breadcrumb url="view.php?id=' . $id . '">' . $title . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<tabs>'
     . '<tab url="view.php?id=' . $id . '" active="true"><i>' . ustr2html($reminder['reminder_name']) . '</i></tab>'
     . '<content>';

// generate buttons

$xml .= '<button url="index.php">' . get_html_resource(RES_BACK_ID) . '</button>'
      . HTML_SPLITTER
      . '<button url="modify.php?id=' . $id . '">' . get_html_resource(RES_MODIFY_ID) . '</button>';

$xml .= '<button url="send.php?id=' . $id . '" prompt="' . get_html_resource(RES_CONFIRM_SEND_REMINDER_ID) . '">'
      . get_html_resource(RES_SEND_ID)
      . '</button>';

$xml .= '<button url="delete.php?id=' . $id . '" prompt="' . get_html_resource(RES_CONFIRM_DELETE_REMINDER_ID) . '">'
      . get_html_resource(RES_DELETE_ID)
      . '</button>';

// generate reminder information

if ($reminder['group_flag'] == REMINDER_FLAG_AUTHOR)
{
    $recipients = sprintf('%s (%s)', get_html_resource(RES_AUTHOR_ID), get_html_resource(RES_ROLE_ID));
}
elseif ($reminder['group_flag'] == REMINDER_FLAG_RESPONSIBLE)
{
    $recipients = sprintf('%s (%s)', get_html_resource(RES_RESPONSIBLE_ID), get_html_resource(RES_ROLE_ID));
}
else
{
    $group = group_find($reminder['group_id']);

    $recipients = $group
                ? sprintf('%s (%s)', ustr2html($group['group_name']), get_html_resource($group['is_global'] ? RES_GLOBAL_ID : RES_LOCAL_ID))
                : get_html_resource(RES_NONE_ID);
}

$xml .= '<group title="' . get_html_resource(RES_GENERAL_INFO_ID) . '">'
      . '<text label="' . get_html_resource(RES_PROJECT_ID)             . '">' . ustr2html($reminder['project_name'])  . '</text>'
      . '<text label="' . get_html_resource(RES_TEMPLATE_ID)            . '">' . ustr2html($reminder['template_name']) . '</text>'
      . '<text label="' . get_html_resource(RES_STATE_ID)               . '">' . ustr2html($reminder['state_name'])    . '</text>'
      . '<text label="' . get_html_resource(RES_REMINDER_NAME_ID)       . '">' . ustr2html($reminder['reminder_name']) . '</text>'
      . '<text label="' . get_html_resource(RES_REMINDER_SUBJECT_ID)    . '">' . ustr2html($reminder['subject_text'])  . '</text>'
      . '<text label="' . get_html_resource(RES_REMINDER_RECIPIENTS_ID) . '">' . $recipients                           . '</text>'
      . '</group>'
      . '</content>'
      . '</tabs>';

if (try_request('sent'))
{
    $xml .= '<scriptonreadyitem>'
          . 'jqAlert("' . get_html_resource(RES_ERROR_ID) . '","' . get_html_resource(RES_ALERT_REMINDER_IS_SENT_ID) . '","' . get_html_resource(RES_OK_ID) . '");'
          . '</scriptonreadyitem>';
}

echo(xml2html($xml, $title));

?>
