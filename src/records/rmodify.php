<?php

/**
 * @package eTraxis
 * @ignore
 */

//--------------------------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system.
//  Copyright (C) 2006-2009 by Artem Rodygin
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
//  Artem Rodygin           2006-06-27      new-222: Email reminders.
//  Artem Rodygin           2006-07-12      bug-292: Sablotron fails if page contains '&' character.
//  Artem Rodygin           2007-11-27      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/reminders.php');
/**#@-*/

init_page();

if (!EMAIL_NOTIFICATIONS_ENABLED)
{
    debug_write_log(DEBUG_NOTICE, 'Email Notifications functionality is disabled.');
    header('Location: index.php');
    exit;
}

$id       = ustr2int(try_request('id'));
$reminder = reminder_find($id);

if (!$reminder)
{
    debug_write_log(DEBUG_NOTICE, 'Reminder cannot be found.');
    header('Location: reminders.php');
    exit;
}

$project_id    = $reminder['project_id'];
$project_name  = $reminder['project_name'];
$template_id   = $reminder['template_id'];
$template_name = $reminder['template_name'];

if (try_request('submitted') == 'mainform')
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

        if ($error == NO_ERROR)
        {
            header('Location: reminders.php');
            exit;
        }
    }

    switch ($error)
    {
        case ERROR_INCOMPLETE_FORM:
            $alert = get_js_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID);
            break;
        case ERROR_ALREADY_EXISTS:
            $alert = get_js_resource(RES_ALERT_SUBSCRIPTION_ALREADY_EXISTS_ID);
            break;
        default:
            $alert = NULL;
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $name     = $reminder['reminder_name'];
    $subject  = $reminder['subject_text'];
    $state_id = $reminder['state_id'];
    $group_id = ($reminder['group_flag'] == REMINDER_FLAG_GROUP ? $reminder['group_id'] : $reminder['group_flag']);
}

$xml = '<page' . gen_xml_page_header(ustrprocess(get_html_resource(RES_REMINDER_X_ID), $name), isset($alert) ? $alert : NULL, 'mainform.state') . '>'
     . gen_xml_menu()
     . '<path>'
     . gen_xml_rec_root()
     . '<pathitem url="reminders.php">' . get_html_resource(RES_REMINDERS_ID) . '</pathitem>'
     . '<pathitem url="rmodify.php?id=' . $id . '">' . ustrprocess(get_html_resource(RES_REMINDER_X_ID), $name) . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<form name="mainform" action="rmodify.php?id=' . $id . '">'
     . '<group title="' . get_html_resource(RES_GENERAL_INFO_ID) . '">'
     . '<combobox label="' . get_html_resource(RES_PROJECT_ID) . '" name="project">'
     . '<listitem value="' . $project_id . '">' . ustr2html($project_name) . '</listitem>'
     . '</combobox>'
     . '<combobox label="' . get_html_resource(RES_TEMPLATE_ID) . '" name="template">'
     . '<listitem value="' . $template_id . '">' . ustr2html($template_name) . '</listitem>'
     . '</combobox>'
     . '<combobox label="' . get_html_resource(RES_STATE_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="state">';

$list = dal_query('states/list.sql', $template_id, 'state_name');

while (($row = $list->fetch()))
{
    $xml .= '<listitem value="' . $row['state_id'] . ($state_id == $row['state_id'] ? '" selected="true">' : '">') . ustr2html($row['state_name']) . '</listitem>';
}

$xml .= '</combobox>'
      . '<editbox label="'  . get_html_resource(RES_REMINDER_NAME_ID)       . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="name"    size="' . HTML_EDITBOX_SIZE_LONG . '" maxlen="' . MAX_REMINDER_NAME    . '">' . ustr2html($name)    . '</editbox>'
      . '<editbox label="'  . get_html_resource(RES_REMINDER_SUBJECT_ID)    . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="subject" size="' . HTML_EDITBOX_SIZE_LONG . '" maxlen="' . MAX_REMINDER_SUBJECT . '">' . ustr2html($subject) . '</editbox>'
      . '<combobox label="' . get_html_resource(RES_REMINDER_RECIPIENTS_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="group">'
      . '<listitem value="' . REMINDER_FLAG_AUTHOR      . ($group_id == REMINDER_FLAG_AUTHOR      ? '" selected="true">' : '">') . get_html_resource(RES_AUTHOR_ID)      . ' (' . get_html_resource(RES_ROLE_ID) . ')</listitem>'
      . '<listitem value="' . REMINDER_FLAG_RESPONSIBLE . ($group_id == REMINDER_FLAG_RESPONSIBLE ? '" selected="true">' : '">') . get_html_resource(RES_RESPONSIBLE_ID) . ' (' . get_html_resource(RES_ROLE_ID) . ')</listitem>';

$list = dal_query('groups/list.sql', $project_id, 'group_name, project_id');

while (($row = $list->fetch()))
{
    $xml .= '<listitem value="' . $row['group_id'] . ($group_id == $row['group_id'] ? '" selected="true">' : '">') . ustr2html($row['group_name']) . ' (' . get_html_resource($row['is_global'] ? RES_GLOBAL_ID : RES_LOCAL_ID) . ')</listitem>';
}

$xml .= '</combobox>'
      . '</group>'
      . '<button default="true">'      . get_html_resource(RES_OK_ID)     . '</button>'
      . '<button url="reminders.php">' . get_html_resource(RES_CANCEL_ID) . '</button>'
      . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>'
      . '</form>'
      . '</content>'
      . '</page>';

echo(xml2html($xml));

?>
