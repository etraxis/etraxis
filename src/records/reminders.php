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
//  Artem Rodygin           2006-06-25      new-222: Email reminders.
//  Artem Rodygin           2006-06-28      bug-273: 'Reminders' button should be disabled if no reminder can be created or send.
//  Artem Rodygin           2006-06-28      new-272: When reminder is sent a notification should be displayed to user.
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

if (!can_reminder_be_created())
{
    debug_write_log(DEBUG_NOTICE, 'Reminder cannot be created.');
    header('Location: index.php');
    exit;
}

if (try_request('sent'))
{
    $alert = get_js_resource(RES_ALERT_REMINDER_IS_SENT_ID);
}

$xml = '<page' . gen_xml_page_header(get_html_resource(RES_REMINDERS_ID), isset($alert) ? $alert : NULL) . '>'
     . gen_xml_menu()
     . '<path>'
     . gen_xml_rec_root()
     . '<pathitem url="reminders.php">' . get_html_resource(RES_REMINDERS_ID) . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<form name="mainform" action="index.php">'
     . '<group title="' . get_html_resource(RES_REMINDERS_ID) . '">'
     . '<listbox name="reminder" size="' . HTML_LISTBOX_SIZE . '">';

$list = reminders_list($_SESSION[VAR_USERID]);

while (($item = $list->fetch()))
{
    $xml .= '<listitem value="' . $item['reminder_id'] . '">' . ustr2html($item['reminder_name']) . '</listitem>';
}

$xml .= '</listbox>'
      . '</group>'
      . '<button default="true">' . get_html_resource(RES_BACK_ID) . '</button>';

if ($list->rows != 0)
{
    $xml .= '<button action="window.open(\'remind.php?id=\'+mainform.reminder.value,\'_parent\');" prompt="' . get_html_resource(RES_CONFIRM_SEND_REMINDER_ID) . '">' . get_html_resource(RES_SEND_ID) . '</button>';
}

$xml .= '<br/>';

if (can_reminder_be_created())
{
    $xml .= '<button url="rcreate.php">' . get_html_resource(RES_CREATE_ID) . '</button>';
}

if ($list->rows != 0)
{
    $xml .= '<button action="window.open(\'rmodify.php?id=\'+mainform.reminder.value,\'_parent\');">' . get_html_resource(RES_MODIFY_ID) . '</button>'
          . '<button action="window.open(\'rdelete.php?id=\'+mainform.reminder.value,\'_parent\');" prompt="' . get_html_resource(RES_CONFIRM_DELETE_REMINDER_ID) . '">' . get_html_resource(RES_DELETE_ID) . '</button>';
}

$xml .= '</form>'
      . '</content>'
      . '</page>';

echo(xml2html($xml));

?>
