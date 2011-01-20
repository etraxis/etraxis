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

// reminders list is submitted

if (try_request('submitted') == 'send')
{
    debug_write_log(DEBUG_NOTICE, 'Send selected reminders.');

    foreach ($_REQUEST as $request)
    {
        if (substr($request, 0, 3) == 'rem')
        {
            $reminder = reminder_find(intval(substr($request, 3)));

            if ($reminder)
            {
                reminder_send($reminder);
            }
        }
    }

    exit;
}
elseif (try_request('submitted') == 'delete')
{
    debug_write_log(DEBUG_NOTICE, 'Delete selected reminders.');

    foreach ($_REQUEST as $request)
    {
        debug_write_log(DEBUG_NOTICE, '$request = ' . $request);

        if (substr($request, 0, 3) == 'rem')
        {
            reminder_delete(intval(substr($request, 3)));
        }
    }

    exit;
}

// get list of reminders

$sort = $page = NULL;
$list = reminders_list($_SESSION[VAR_USERID], $sort, $page);

$from = $to = 0;

// local JS functions

$resTitle1  = get_js_resource(RES_NEW_REMINDER_ID);
$resTitle2  = get_js_resource(RES_REMINDERS_ID);
$resMessage = get_js_resource(RES_ALERT_REMINDER_IS_SENT_ID);
$resOK      = get_js_resource(RES_OK_ID);
$resNext    = get_js_resource(RES_NEXT_ID);
$resCancel  = get_js_resource(RES_CANCEL_ID);

$xml = <<<JQUERY
<script>

function reminderCreateForceToStep2 ()
{
    var items = $("#projectform #project").children().length;

    if (items == 1)
    {
        reminderCreateStep2();
    }
}

function reminderCreateForceToStep3 ()
{
    var items = $("#templateform #template").children().length;

    if (items == 1)
    {
        reminderCreateStep3();
    }
}

function reminderCreateStep1 ()
{
    jqModal("{$resTitle1}", "create.php", "{$resNext}", "{$resCancel}", "reminderCreateStep2()", null, "reminderCreateForceToStep2()");
}

function reminderCreateStep2 ()
{
    closeModal();
    jqModal("{$resTitle1}", "create.php?" + $("#projectform").serialize(), "{$resNext}", "{$resCancel}", "reminderCreateStep3()", null, "reminderCreateForceToStep3()");
}

function reminderCreateStep3 ()
{
    closeModal();
    jqModal("{$resTitle1}", "create.php?" + $("#templateform").serialize(), "{$resOK}", "{$resCancel}", "$('#createform').submit()");
}

function performAction (action)
{
    $("#reminders :input[name=submitted]").val(action);
    $("#reminders").submit();
}

function onSuccess ()
{
    if ($("#reminders :input[name=submitted]").val() == "send")
    {
        jqAlert("{$resTitle2}", "{$resMessage}", "{$resOK}");
    }
    else
    {
        reloadTab();
    }
}

</script>
JQUERY;

// generate list of reminders

$xml .= '<button action="reminderCreateStep1()">' . get_html_resource(RES_CREATE_ID) . '</button>';

if ($list->rows != 0)
{
    $columns = array
    (
        RES_REMINDER_NAME_ID,
        RES_PROJECT_ID,
        RES_TEMPLATE_ID,
        RES_STATE_ID,
        RES_REMINDER_SUBJECT_ID,
    );

    $bookmarks = gen_xml_bookmarks($page, $list->rows, $from, $to, 'list.php?');

    $xml .= '<button action="performAction(\\\'send\\\')"   prompt="' . get_html_resource(RES_CONFIRM_SEND_REMINDER_ID)   . '">' . get_html_resource(RES_SEND_ID)   . '</button>'
          . '<button action="performAction(\\\'delete\\\')" prompt="' . get_html_resource(RES_CONFIRM_DELETE_REMINDER_ID) . '">' . get_html_resource(RES_DELETE_ID) . '</button>'
          . '<form name="reminders" action="list.php" success="onSuccess">'
          . '<list>'
          . '<hrow>'
          . '<hcell checkboxes="true"/>';

    for ($i = 1; $i <= count($columns); $i++)
    {
        $smode = ($sort == $i ? ($i + count($columns)) : $i);

        $xml .= "<hcell url=\"list.php?sort={$smode}\">"
              . get_html_resource($columns[$i - 1])
              . '</hcell>';
    }

    $xml .= '</hrow>';

    $list->seek($from - 1);

    for ($i = $from; $i <= $to; $i++)
    {
        $row = $list->fetch();

        $xml .= "<row name=\"rem{$row['reminder_id']}\" url=\"view.php?id={$row['reminder_id']}\">"
              . '<cell>' . ustr2html($row['reminder_name']) . '</cell>'
              . '<cell>' . ustr2html($row['project_name'])  . '</cell>'
              . '<cell>' . ustr2html($row['template_name']) . '</cell>'
              . '<cell>' . ustr2html($row['state_name'])    . '</cell>'
              . '<cell>' . ustr2html($row['subject_text'])  . '</cell>'
              . '</row>';
    }

    $xml .= '</list>'
          . '</form>'
          . $bookmarks;
}

echo(xml2html($xml));

?>
