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
require_once('../dbo/subscriptions.php');
/**#@-*/

init_page();

if (!EMAIL_NOTIFICATIONS_ENABLED)
{
    debug_write_log(DEBUG_NOTICE, 'Email Notifications functionality is disabled.');
    header('Location: ../index.php');
    exit;
}

$error              = NO_ERROR;
$subscription_name  = NULL;
$carbon_copy        = NULL;
$subscription_flags = DEFAULT_NOTIFY_FLAG;

// project has been selected

if (try_request('submitted') == 'projectform')
{
    debug_write_log(DEBUG_NOTICE, 'Project is selected.');

    $project_id = ustr2int(try_request('project'));

    if ($project_id == 0)
    {
        $project_name = get_html_resource(RES_ALL_PROJECTS_ID);
        $form = 'mainform';
    }
    else
    {
        $rs = dal_query('records/pfndid2.sql', $_SESSION[VAR_USERID], $project_id);

        if ($rs->rows == 0)
        {
            debug_write_log(DEBUG_NOTICE, 'Project cannot be found.');
            header('Location: index.php');
            exit;
        }

        $project_name = $rs->fetch('project_name');
        $form = 'templateform';
    }
}

// template has been selected

elseif (try_request('submitted') == 'templateform')
{
    debug_write_log(DEBUG_NOTICE, 'Template is selected.');

    $project_id  = ustr2int(try_request('project'));
    $template_id = ustr2int(try_request('template'));

    if ($template_id == 0)
    {
        $rs = dal_query('records/pfndid2.sql', $_SESSION[VAR_USERID], $project_id);

        if ($rs->rows == 0)
        {
            debug_write_log(DEBUG_NOTICE, 'Project cannot be found.');
            header('Location: index.php');
            exit;
        }

        $project_name  = $rs->fetch('project_name');
        $template_name = get_html_resource(RES_ALL_TEMPLATES_ID);
        $form = 'mainform';
    }
    else
    {
        $rs = dal_query('records/tfndid2.sql', $_SESSION[VAR_USERID], $project_id, $template_id);

        if ($rs->rows == 0)
        {
            debug_write_log(DEBUG_NOTICE, 'Template cannot be found.');
            header('Location: index.php');
            exit;
        }

        $row = $rs->fetch();

        $project_name  = $row['project_name'];
        $template_name = $row['template_name'];
        $form = 'mainform';
    }
}

// new subscription has been submitted

elseif (try_request('submitted') == 'mainform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $project_id  = ustr2int(try_request('project'));
    $template_id = ustr2int(try_request('template'));

    $subscription_name  = ustrcut($_REQUEST['subscription_name'], MAX_SUBSCRIPTION_NAME);
    $carbon_copy        = ustrcut($_REQUEST['carbon_copy'],       MAX_SUBSCRIPTION_CARBON_COPY);
    $subscription_flags = 0;

    foreach ($notifications as $notification)
    {
        $subscription_flags |= (isset($_REQUEST[$notification[NOTIFY_CONTROL]]) ? $notification[NOTIFY_EVENT] : 0);
    }

    $error = subscription_validate($subscription_name, $carbon_copy);

    if ($error == NO_ERROR)
    {
        if ($project_id == 0)
        {
            $subscription_type  = SUBSCRIPTION_TYPE_ALL_PROJECTS;
            $subscription_param = NULL;
        }
        elseif ($template_id == 0)
        {
            $subscription_type  = SUBSCRIPTION_TYPE_ALL_TEMPLATES;
            $subscription_param = $project_id;
        }
        else
        {
            $subscription_type  = SUBSCRIPTION_TYPE_ONE_TEMPLATE;
            $subscription_param = $template_id;
        }

        $error = subscription_create($subscription_name,
                                     $carbon_copy,
                                     $subscription_type,
                                     $subscription_flags,
                                     $subscription_param);

        if ($error == NO_ERROR)
        {
            header('Location: index.php');
            exit;
        }
    }

    if ($template_id == 0)
    {
        if ($project_id == 0)
        {
            $project_name = get_html_resource(RES_ALL_PROJECTS_ID);
            unset($template_id);
        }
        else
        {
            $rs = dal_query('records/pfndid2.sql', $_SESSION[VAR_USERID], $project_id);

            if ($rs->rows == 0)
            {
                debug_write_log(DEBUG_NOTICE, 'Project cannot be found.');
                header('Location: index.php');
                exit;
            }

            $project_name  = $rs->fetch('project_name');
            $template_name = get_html_resource(RES_ALL_TEMPLATES_ID);
        }
    }
    else
    {
        $rs = dal_query('records/tfndid2.sql', $_SESSION[VAR_USERID], $project_id, $template_id);

        if ($rs->rows == 0)
        {
            debug_write_log(DEBUG_NOTICE, 'Template cannot be found.');
            header('Location: index.php');
            exit;
        }

        $row = $rs->fetch();

        $project_name  = $row['project_name'];
        $template_name = $row['template_name'];
    }

    $form = 'mainform';
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $form = 'projectform';
}

// generate breadcrumbs and tabs

$xml = '<breadcrumbs>'
     . '<breadcrumb url="create.php">' . get_html_resource(RES_SUBSCRIPTIONS_ID) . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<tabs>'
     . '<tab url="index.php">'                . get_html_resource(RES_SUBSCRIPTIONS_ID) . '</tab>'
     . '<tab url="create.php" active="true">' . get_html_resource(RES_CREATE_ID)        . '</tab>';

// generate general information

$xml .= '<content>'
      . '<form name="' . $form . '" action="create.php">'
      . '<group title="' . get_html_resource(RES_GENERAL_INFO_ID) . '">';

if ($form == 'projectform')
{
    $xml .= '<control name="project" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
          . '<label>' . get_html_resource(RES_PROJECT_ID) . '</label>'
          . '<combobox>'
          . '<listitem value="0">' . get_html_resource(RES_ALL_PROJECTS_ID) . '</listitem>';

    $rs = dal_query('records/plist2.sql', $_SESSION[VAR_USERID]);

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

if ($form == 'templateform')
{
    $xml .= '<control name="template" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
          . '<label>' . get_html_resource(RES_TEMPLATE_ID) . '</label>'
          . '<combobox>'
          . '<listitem value="0">' . get_html_resource(RES_ALL_TEMPLATES_ID) . '</listitem>';

    $rs = dal_query('records/tlist2.sql', $_SESSION[VAR_USERID], $project_id);

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

if ($form == 'mainform')
{
    $xml .= '<control name="subscription_name" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
          . '<label>' . get_html_resource(RES_SUBSCRIPTION_NAME_ID) . '</label>'
          . '<editbox maxlen="' . MAX_SUBSCRIPTION_NAME . '">' . ustr2html($subscription_name) . '</editbox>'
          . '</control>'
          . '<control name="carbon_copy">'
          . '<label>' . get_html_resource(RES_CARBON_COPY_ID) . '</label>'
          . '<editbox maxlen="' . MAX_SUBSCRIPTION_CARBON_COPY . '">' . ustr2html($carbon_copy) . '</editbox>'
          . '</control>'
          . '</group>'
          . '<group title="' . get_html_resource(RES_EVENTS_ID) . '">';

    foreach ($notifications as $notification)
    {
        $xml .= '<control name="' . $notification[NOTIFY_CONTROL] . '">'
              . (($subscription_flags & $notification[NOTIFY_EVENT]) != 0
                    ? '<checkbox checked="true">'
                    : '<checkbox>')
              . get_html_resource($notification[NOTIFY_RESOURCE])
              . '</checkbox>'
              . '</control>';
    }
}

$xml .= '</group>'
      . '<button default="true">' . get_html_resource($form == 'mainform' ? RES_OK_ID : RES_NEXT_ID) . '</button>'
      . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>'
      . '</form>'
      . '</content>'
      . '</tabs>';

// if some error was specified to display, force an alert

switch ($error)
{
    case ERROR_INCOMPLETE_FORM:
        $xml .= '<scriptonreadyitem>'
              . 'jqAlert("' . get_html_resource(RES_ERROR_ID) . '","' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '","' . get_html_resource(RES_OK_ID) . '");'
              . '</scriptonreadyitem>';
        break;
    case ERROR_ALREADY_EXISTS:
        $xml .= '<scriptonreadyitem>'
              . 'jqAlert("' . get_html_resource(RES_ERROR_ID) . '","' . get_html_resource(RES_ALERT_SUBSCRIPTION_ALREADY_EXISTS_ID) . '","' . get_html_resource(RES_OK_ID) . '");'
              . '</scriptonreadyitem>';
        break;
    case ERROR_INVALID_EMAIL:
        $xml .= '<scriptonreadyitem>'
              . 'jqAlert("' . get_html_resource(RES_ERROR_ID) . '","' . get_html_resource(RES_ALERT_INVALID_EMAIL_ID) . '","' . get_html_resource(RES_OK_ID) . '");'
              . '</scriptonreadyitem>';
        break;
    default: ;  // nop
}

echo(xml2html($xml, get_html_resource(RES_NEW_SUBSCRIPTION_ID)));

?>
