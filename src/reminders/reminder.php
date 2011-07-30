<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2010-2011  Artem Rodygin
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

init_page(LOAD_TAB);

if (!EMAIL_NOTIFICATIONS_ENABLED)
{
    debug_write_log(DEBUG_NOTICE, 'Email Notifications functionality is disabled.');
    exit;
}

if (!can_reminder_be_created())
{
    debug_write_log(DEBUG_NOTICE, 'Reminders are denied.');
    exit;
}

// check that requested reminder exists

$id       = ustr2int(try_request('id'));
$reminder = reminder_find($id);

if (!$reminder)
{
    debug_write_log(DEBUG_NOTICE, 'Reminder cannot be found.');
    exit;
}

// local JS functions

$resTitle1  = ustrprocess(get_js_resource(RES_REMINDER_X_ID), ustr2js($reminder['reminder_name']));
$resTitle2  = get_js_resource(RES_REMINDERS_ID);
$resMessage = get_js_resource(RES_ALERT_REMINDER_IS_SENT_ID);
$resOK      = get_js_resource(RES_OK_ID);
$resCancel  = get_js_resource(RES_CANCEL_ID);

$xml = <<<JQUERY
<script>

function reminderModify ()
{
    jqModal("{$resTitle1}", "modify.php?id={$id}", "{$resOK}", "{$resCancel}", "$('#modifyform').submit()");
}

function reminderSend ()
{
    $.post("send.php?id={$id}", function () {
        jqAlert("{$resTitle2}", "{$resMessage}", "{$resOK}");
    });
}

</script>
JQUERY;

// generate buttons

$xml .= '<button url="index.php">' . get_html_resource(RES_BACK_ID) . '</button>'
      . '<buttonset>'
      . '<button action="reminderModify()">' . get_html_resource(RES_MODIFY_ID) . '</button>'
      . '<button action="reminderSend()" prompt="' . get_html_resource(RES_CONFIRM_SEND_REMINDER_ID) . '">' . get_html_resource(RES_SEND_ID) . '</button>'
      . '<button url="delete.php?id=' . $id . '" prompt="' . get_html_resource(RES_CONFIRM_DELETE_REMINDER_ID) . '">' . get_html_resource(RES_DELETE_ID) . '</button>'
      . '</buttonset>';

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

$xml .= '<group>'
      . '<text label="' . get_html_resource(RES_PROJECT_ID)             . '">' . ustr2html($reminder['project_name'])  . '</text>'
      . '<text label="' . get_html_resource(RES_TEMPLATE_ID)            . '">' . ustr2html($reminder['template_name']) . '</text>'
      . '<text label="' . get_html_resource(RES_STATE_ID)               . '">' . ustr2html($reminder['state_name'])    . '</text>'
      . '<text label="' . get_html_resource(RES_REMINDER_NAME_ID)       . '">' . ustr2html($reminder['reminder_name']) . '</text>'
      . '<text label="' . get_html_resource(RES_REMINDER_SUBJECT_ID)    . '">' . ustr2html($reminder['subject_text'])  . '</text>'
      . '<text label="' . get_html_resource(RES_REMINDER_RECIPIENTS_ID) . '">' . $recipients                           . '</text>'
      . '</group>';

echo(xml2html($xml));

?>
