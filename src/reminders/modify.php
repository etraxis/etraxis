<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2006-2010  Artem Rodygin
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
require_once('../dbo/projects.php');
require_once('../dbo/templates.php');
require_once('../dbo/reminders.php');
/**#@-*/

init_page(LOAD_INLINE);

if (!EMAIL_NOTIFICATIONS_ENABLED)
{
    debug_write_log(DEBUG_NOTICE, 'Email Notifications functionality is disabled.');
    header('HTTP/1.1 307 ../index.php');
    exit;
}

if (!can_reminder_be_created())
{
    debug_write_log(DEBUG_NOTICE, 'Reminders are denied.');
    header('HTTP/1.1 307 ../index.php');
    exit;
}

// check that requested reminder exists

$id       = ustr2int(try_request('id'));
$reminder = reminder_find($id);

if (!$reminder)
{
    debug_write_log(DEBUG_NOTICE, 'Reminder cannot be found.');
    header('HTTP/1.1 307 index.php');
    exit;
}

$error = NO_ERROR;

$project_id    = $reminder['project_id'];
$project_name  = $reminder['project_name'];
$template_id   = $reminder['template_id'];
$template_name = $reminder['template_name'];

// changed reminder has been submitted

if (try_request('submitted') == 'modifyform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $name    = ustrcut($_REQUEST['name'],    MAX_REMINDER_NAME);
    $subject = ustrcut($_REQUEST['subject'], MAX_REMINDER_SUBJECT);

    $state_id = ustr2int(try_request('state'));
    $group_id = ustr2int(try_request('group'), REMINDER_FLAG_RESPONSIBLE);

    $error = reminder_validate($name, $subject);

    if ($error == NO_ERROR)
    {
        $error = reminder_modify($id,
                                 $name,
                                 $subject,
                                 $state_id,
                                 ($group_id < 0 ? NULL      : $group_id),
                                 ($group_id < 0 ? $group_id : REMINDER_FLAG_GROUP));
    }

    switch ($error)
    {
        case NO_ERROR:
            header('HTTP/1.0 200 OK');
            break;

        case ERROR_INCOMPLETE_FORM:
            header('HTTP/1.0 500 ' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID));
            break;

        case ERROR_ALREADY_EXISTS:
            header('HTTP/1.0 500 ' . get_html_resource(RES_ALERT_REMINDER_ALREADY_EXISTS_ID));
            break;

        default:
            header('HTTP/1.0 500 ' . get_html_resource(RES_ALERT_UNKNOWN_ERROR_ID));
    }

    exit;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $name     = $reminder['reminder_name'];
    $subject  = $reminder['subject_text'];
    $state_id = $reminder['state_id'];
    $group_id = ($reminder['group_flag'] == REMINDER_FLAG_GROUP ? $reminder['group_id'] : $reminder['group_flag']);
}

// local JS functions

$resTitle = get_js_resource(RES_ERROR_ID);
$resOK    = get_js_resource(RES_OK_ID);

$xml = <<<JQUERY
<script>

function modifySuccess ()
{
    closeModal();
    reloadTab();
}

function modifyError (XMLHttpRequest)
{
    jqAlert("{$resTitle}", XMLHttpRequest.statusText, "{$resOK}");
}

</script>
JQUERY;

// generate page

$xml .= '<form name="modifyform" action="modify.php?id=' . $id . '" success="modifySuccess" error="modifyError">'
      . '<group>'
      . '<control name="project" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
      . '<label>' . get_html_resource(RES_PROJECT_ID) . '</label>'
      . '<combobox>'
      . '<listitem value="' . $project_id . '">' . ustr2html($project_name) . '</listitem>'
      . '</combobox>'
      . '</control>'
      . '<control name="template" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
      . '<label>' . get_html_resource(RES_TEMPLATE_ID) . '</label>'
      . '<combobox>'
      . '<listitem value="' . $template_id . '">' . ustr2html($template_name) . '</listitem>'
      . '</combobox>'
      . '</control>'
      . '<control name="state" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
      . '<label>' . get_html_resource(RES_STATE_ID) . '</label>'
      . '<combobox>';

$rs = dal_query('states/list.sql', $template_id, 'state_name');

while (($row = $rs->fetch()))
{
    $xml .= ($state_id == $row['state_id']
                ? '<listitem value="' . $row['state_id'] . '" selected="true">'
                : '<listitem value="' . $row['state_id'] . '">')
          . ustr2html($row['state_name'])
          . '</listitem>';
}

$xml .= '</combobox>'
      . '</control>'
      . '<control name="name" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
      . '<label>' . get_html_resource(RES_REMINDER_NAME_ID) . '</label>'
      . '<editbox maxlen="' . MAX_REMINDER_NAME . '">' . ustr2html($name) . '</editbox>'
      . '</control>'
      . '<control name="subject">'
      . '<label>' . get_html_resource(RES_REMINDER_SUBJECT_ID) . '</label>'
      . '<editbox maxlen="' . MAX_REMINDER_SUBJECT . '">' . ustr2html($subject) . '</editbox>'
      . '</control>'
      . '<control name="group" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
      . '<label>' . get_html_resource(RES_REMINDER_RECIPIENTS_ID) . '</label>'
      . '<combobox>'
      . '<listitem value="' . REMINDER_FLAG_AUTHOR      . ($group_id == REMINDER_FLAG_AUTHOR      ? '" selected="true">' : '">') . sprintf('%s (%s)', get_html_resource(RES_AUTHOR_ID),      get_html_resource(RES_ROLE_ID)) . '</listitem>'
      . '<listitem value="' . REMINDER_FLAG_RESPONSIBLE . ($group_id == REMINDER_FLAG_RESPONSIBLE ? '" selected="true">' : '">') . sprintf('%s (%s)', get_html_resource(RES_RESPONSIBLE_ID), get_html_resource(RES_ROLE_ID)) . '</listitem>';

$rs = dal_query('groups/list.sql', $project_id, 'is_global, group_name');

while (($row = $rs->fetch()))
{
    $xml .= ($group_id == $row['group_id']
                ? '<listitem value="' . $row['group_id'] . '" selected="true">'
                : '<listitem value="' . $row['group_id'] . '">')
          . sprintf('%s (%s)', ustr2html($row['group_name']), get_html_resource($row['is_global'] ? RES_GLOBAL_ID : RES_LOCAL_ID))
          . '</listitem>';
}

$xml .= '</combobox>'
      . '</control>'
      . '</group>'
      . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>'
      . '</form>';

echo(xml2html($xml));

?>
