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

$error = NO_ERROR;

// project has been selected

if (try_request('submitted') == 'projectform')
{
    debug_write_log(DEBUG_NOTICE, 'Project is selected.');

    $project_id = ustr2int(try_request('project'));

    $rs = dal_query(DATABASE_DRIVER == DRIVER_ORACLE9 ? 'reminders/oracle/pfndid.sql' : 'reminders/pfndid.sql',
                    $_SESSION[VAR_USERID],
                    $project_id);

    if ($rs->rows == 0)
    {
        debug_write_log(DEBUG_NOTICE, 'Project cannot be found.');
        header('Location: index.php');
        exit;
    }

    $project_name = $rs->fetch('project_name');

    $form = 'templateform';
}

// template has been selected

elseif (try_request('submitted') == 'templateform')
{
    debug_write_log(DEBUG_NOTICE, 'Template is selected.');

    $name    = NULL;
    $subject = NULL;

    $project_id  = ustr2int(try_request('project'));
    $template_id = ustr2int(try_request('template'));
    $state_id    = 0;
    $group_id    = 0;

    $rs = dal_query(DATABASE_DRIVER == DRIVER_ORACLE9 ? 'reminders/oracle/tfndid.sql' : 'reminders/tfndid.sql',
                    $_SESSION[VAR_USERID],
                    $project_id,
                    $template_id);

    if ($rs->rows == 0)
    {
        debug_write_log(DEBUG_NOTICE, 'Template cannot be found.');
        header('Location: index.php');
        exit;
    }

    $row = $rs->fetch();

    $project_name  = $row['project_name'];
    $template_name = $row['template_name'];

    $form = 'createform';
}

// new reminder has been submitted

elseif (try_request('submitted') == 'createform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $name    = ustrcut($_REQUEST['name'],    MAX_REMINDER_NAME);
    $subject = ustrcut($_REQUEST['subject'], MAX_REMINDER_SUBJECT);

    $project_id  = ustr2int(try_request('project'));
    $template_id = ustr2int(try_request('template'));
    $state_id    = ustr2int(try_request('state'));
    $group_id    = ustr2int(try_request('group'), REMINDER_FLAG_RESPONSIBLE);

    $rs = dal_query(DATABASE_DRIVER == DRIVER_ORACLE9 ? 'reminders/oracle/tfndid.sql' : 'reminders/tfndid.sql',
                    $_SESSION[VAR_USERID],
                    $project_id,
                    $template_id);

    if ($rs->rows == 0)
    {
        debug_write_log(DEBUG_NOTICE, 'Template cannot be found.');
        header('Location: index.php');
        exit;
    }

    $row = $rs->fetch();

    $project_name  = $row['project_name'];
    $template_name = $row['template_name'];

    $error = reminder_validate($name, $subject);

    if ($error == NO_ERROR)
    {
        $error = reminder_create($name,
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

    $form = 'projectform';
}

// local JS functions

$resTitle = get_js_resource(RES_ERROR_ID);
$resOK    = get_js_resource(RES_OK_ID);

$xml = <<<JQUERY
<script>

function createSuccess ()
{
    closeModal();
    reloadTab();
}

function createError (XMLHttpRequest)
{
    jqAlert("{$resTitle}", XMLHttpRequest.statusText, "{$resOK}");
}

</script>
JQUERY;

// generate header

$xml .= '<form name="' . $form . '" action="create.php" success="createSuccess" error="createError">'
      . '<group>';

// generate project selector

if ($form == 'projectform')
{
    $xml .= '<control name="project" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
          . '<label>' . get_html_resource(RES_PROJECT_ID) . '</label>'
          . '<combobox>';

    $rs = dal_query(DATABASE_DRIVER == DRIVER_ORACLE9 ? 'reminders/oracle/plist.sql' : 'reminders/plist.sql',
                    $_SESSION[VAR_USERID]);

    if ($rs->rows == 1)
    {
        debug_write_log(DEBUG_NOTICE, 'One project only is found.');
    }

    while (($row = $rs->fetch()))
    {
        $xml .= '<listitem value="' . $row['project_id'] . '">'
              . ustr2html($row['project_name'])
              . '</listitem>';
    }

    $xml .= '</combobox>'
          . '</control>';
}
elseif (isset($project_id))
{
    $xml .= '<control name="project" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
          . '<label>' . get_html_resource(RES_PROJECT_ID) . '</label>'
          . '<combobox>'
          . '<listitem value="' . $project_id . '">' . ustr2html($project_name) . '</listitem>'
          . '</combobox>'
          . '</control>';
}

// generate template selector

if ($form == 'templateform')
{
    $xml .= '<control name="template" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
          . '<label>' . get_html_resource(RES_TEMPLATE_ID) . '</label>'
          . '<combobox>';

    $rs = dal_query(DATABASE_DRIVER == DRIVER_ORACLE9 ? 'reminders/oracle/tlist.sql' : 'reminders/tlist.sql',
                    $_SESSION[VAR_USERID],
                    $project_id);

    if ($rs->rows == 1)
    {
        debug_write_log(DEBUG_NOTICE, 'One template only is found.');
    }

    while (($row = $rs->fetch()))
    {
        $xml .= '<listitem value="' . $row['template_id'] . '">'
              . ustr2html($row['template_name'])
              . '</listitem>';
    }

    $xml .= '</combobox>'
          . '</control>';
}
elseif (isset($template_id))
{
    $xml .= '<control name="template" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
          . '<label>' . get_html_resource(RES_TEMPLATE_ID) . '</label>'
          . '<combobox>'
          . '<listitem value="' . $template_id . '">' . ustr2html($template_name) . '</listitem>'
          . '</combobox>'
          . '</control>';
}

// generate other reminder attributes

if ($form == 'createform')
{
    $xml .= '<control name="state" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
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
          . '<control name="subject" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
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
          . '</control>';
}

// generate footer

$xml .= '</group>'
      . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>'
      . '</form>';

echo(xml2html($xml));

?>
