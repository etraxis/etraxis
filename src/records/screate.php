<?php

/**
 * @package eTraxis
 * @ignore
 */

//--------------------------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system.
//  Copyright (C) 2005-2009 by Artem Rodygin
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
//  Artem Rodygin           2005-09-17      new-125: Email notifications advanced filter.
//  Artem Rodygin           2005-09-18      bug-130: Subscriptions should not be accessable when Email Notifications functionality is disabled.
//  Artem Rodygin           2005-09-19      bug-134: PHP Notice: Undefined variables 'subscribe_name' and 'subscribe_flags' in 'screate.php'.
//  Artem Rodygin           2005-09-27      bug-142: PHP Notice: Use of undefined constant DEFAULT_NOTIFY_FLAG
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-10-15      new-153: Users should *always* receieve notifications about records which are created by them or assigned on.
//  Artem Rodygin           2005-11-17      new-176: Change eTraxis design.
//  Artem Rodygin           2005-11-26      new-181: 'All fields marked with * should be filled in.' note is absent.
//  Artem Rodygin           2006-04-01      new-233: Email subscriptions functionality (new-125) should be conditionally "compiled".
//  Artem Rodygin           2006-06-28      new-274: "Crumbs" for creation and modification of filters or subscriptions are not clear.
//  Artem Rodygin           2006-07-12      bug-292: Sablotron fails if page contains '&' character.
//  Artem Rodygin           2006-11-15      new-374: Carbon copies in subscriptions.
//  Artem Rodygin           2007-07-04      new-533: Links between records.
//  Artem Rodygin           2007-08-06      new-551: Rework dependencies into "parent-child" relations.
//  Artem Rodygin           2007-11-13      new-622: Rename 'children' into 'subrecords'.
//  Artem Rodygin           2007-11-27      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/accounts.php');
require_once('../dbo/subscribes.php');
require_once('../dbo/events.php');
/**#@-*/

init_page();

$subscribe_name  = NULL;
$carbon_copy     = NULL;
$subscribe_flags = DEFAULT_NOTIFY_FLAG;

if (!EMAIL_NOTIFICATIONS_ENABLED)
{
    debug_write_log(DEBUG_NOTICE, 'Email Notifications functionality is disabled.');
    header('Location: index.php');
    exit;
}

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
            header('Location: subscribe.php');
            exit;
        }

        $project_name = $rs->fetch('project_name');
        $form = 'templateform';
    }
}
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
            header('Location: subscribe.php');
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
            header('Location: subscribe.php');
            exit;
        }

        $row = $rs->fetch();

        $project_name  = $row['project_name'];
        $template_name = $row['template_name'];
        $form = 'mainform';
    }
}
elseif (try_request('submitted') == 'mainform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $project_id  = ustr2int(try_request('project'));
    $template_id = ustr2int(try_request('template'));

    $subscribe_name = ustrcut($_REQUEST['subscribe_name'], MAX_SUBSCRIBE_NAME);
    $carbon_copy    = ustrcut($_REQUEST['carbon_copy'],    MAX_SUBSCRIBE_CARBON_COPY);

    $error = subscribe_validate($subscribe_name, $carbon_copy);

    if ($error == NO_ERROR)
    {
        if ($project_id == 0)
        {
            $subscribe_type  = SUBSCRIBE_TYPE_ALL_PROJECTS;
            $subscribe_param = NULL;
        }
        elseif ($template_id == 0)
        {
            $subscribe_type  = SUBSCRIBE_TYPE_ALL_TEMPLATES;
            $subscribe_param = $project_id;
        }
        else
        {
            $subscribe_type  = SUBSCRIBE_TYPE_ONE_TEMPLATE;
            $subscribe_param = $template_id;
        }

        $subscribe_flags = 0;

        $subscribe_flags |= (isset($_REQUEST['notify1'])  ? NOTIFY_RECORD_CREATED       : 0);
        $subscribe_flags |= (isset($_REQUEST['notify2'])  ? NOTIFY_RECORD_ASSIGNED      : 0);
        $subscribe_flags |= (isset($_REQUEST['notify3'])  ? NOTIFY_RECORD_MODIFIED      : 0);
        $subscribe_flags |= (isset($_REQUEST['notify4'])  ? NOTIFY_RECORD_STATE_CHANGED : 0);
        $subscribe_flags |= (isset($_REQUEST['notify5'])  ? NOTIFY_RECORD_POSTPONED     : 0);
        $subscribe_flags |= (isset($_REQUEST['notify6'])  ? NOTIFY_RECORD_RESUMED       : 0);
        $subscribe_flags |= (isset($_REQUEST['notify7'])  ? NOTIFY_COMMENT_ADDED        : 0);
        $subscribe_flags |= (isset($_REQUEST['notify8'])  ? NOTIFY_FILE_ATTACHED        : 0);
        $subscribe_flags |= (isset($_REQUEST['notify9'])  ? NOTIFY_FILE_REMOVED         : 0);
        $subscribe_flags |= (isset($_REQUEST['notify10']) ? NOTIFY_RECORD_CLONED        : 0);
        $subscribe_flags |= (isset($_REQUEST['notify11']) ? NOTIFY_SUBRECORD_ADDED      : 0);
        $subscribe_flags |= (isset($_REQUEST['notify12']) ? NOTIFY_SUBRECORD_REMOVED    : 0);

        $error = subscribe_create($subscribe_name,
                                  $carbon_copy,
                                  $subscribe_type,
                                  $subscribe_flags,
                                  $subscribe_param);

        if ($error == NO_ERROR)
        {
            header('Location: subscribe.php');
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
        case ERROR_INVALID_EMAIL:
            $alert = get_js_resource(RES_ALERT_INVALID_EMAIL_ID);
            break;
        default:
            $alert = NULL;
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
                header('Location: subscribe.php');
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
            header('Location: subscribe.php');
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

$xml = '<page' . gen_xml_page_header(get_html_resource(RES_NEW_SUBSCRIPTION_ID), isset($alert) ? $alert : NULL) . '>'
     . gen_xml_menu()
     . '<path>'
     . gen_xml_rec_root()
     . '<pathitem url="subscribe.php">' . get_html_resource(RES_SUBSCRIPTIONS_ID)    . '</pathitem>'
     . '<pathitem url="screate.php">'   . get_html_resource(RES_NEW_SUBSCRIPTION_ID) . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<form name="' . $form . '" action="screate.php">';

if ($form == 'projectform')
{
    $xml .= '<group title="' . get_html_resource(RES_PROJECT_ID) . '">'
          . '<listbox name="project" size="' . HTML_LISTBOX_SIZE . '">'
          . '<listitem value="0">' . get_html_resource(RES_ALL_PROJECTS_ID) . '</listitem>';

    $rs = dal_query('records/plist2.sql', $_SESSION[VAR_USERID]);

    while (($row = $rs->fetch()))
    {
        $xml .= '<listitem value="' . $row['project_id'] . '">' . ustr2html($row['project_name']) . '</listitem>';
    }

    $xml .= '</listbox>'
          . '</group>';
}
elseif (isset($project_id))
{
    $xml .= '<group title="' . get_html_resource(RES_PROJECT_ID) . '">'
          . '<combobox name="project">'
          . '<listitem value="' . $project_id . '">' . ustr2html($project_name) . '</listitem>'
          . '</combobox>'
          . '</group>';
}

if ($form == 'templateform')
{
    $xml .= '<group title="' . get_html_resource(RES_TEMPLATE_ID) . '">'
          . '<listbox name="template" size="' . HTML_LISTBOX_SIZE . '">'
          . '<listitem value="0">' . get_html_resource(RES_ALL_TEMPLATES_ID) . '</listitem>';

    $rs = dal_query('records/tlist2.sql', $_SESSION[VAR_USERID], $project_id);

    while (($row = $rs->fetch()))
    {
        $xml .= '<listitem value="' . $row['template_id'] . '">' . ustr2html($row['template_name']) . '</listitem>';
    }

    $xml .= '</listbox>'
          . '</group>';
}
elseif (isset($template_id))
{
    $xml .= '<group title="' . get_html_resource(RES_TEMPLATE_ID) . '">'
          . '<combobox name="template">'
          . '<listitem value="' . $template_id . '">' . ustr2html($template_name) . '</listitem>'
          . '</combobox>'
          . '</group>';
}

if ($form == 'mainform')
{
    $xml .= '<group title="' . get_html_resource(RES_GENERAL_INFO_ID) . '">'
          . '<editbox label="' . get_html_resource(RES_SUBSCRIPTION_NAME_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="subscribe_name" size="' . HTML_EDITBOX_SIZE_MEDIUM . '" maxlen="' . MAX_SUBSCRIBE_NAME        . '">' . ustr2html($subscribe_name) . '</editbox>'
          . '<editbox label="' . get_html_resource(RES_CARBON_COPY_ID)                                                              . '" name="carbon_copy"    size="' . HTML_EDITBOX_SIZE_MEDIUM . '" maxlen="' . MAX_SUBSCRIBE_CARBON_COPY . '">' . ustr2html($carbon_copy)    . '</editbox>'
          . '</group>'
          . '<group title="' . get_html_resource(RES_EVENTS_ID) . '">'
          . '<checkbox name="notify1"'  . (($subscribe_flags & NOTIFY_RECORD_CREATED)       == 0 ? '>' : ' checked="true">') . get_html_resource(RES_NOTIFY_RECORD_CREATED_ID)       . '</checkbox>'
          . '<checkbox name="notify2"'  . (($subscribe_flags & NOTIFY_RECORD_ASSIGNED)      == 0 ? '>' : ' checked="true">') . get_html_resource(RES_NOTIFY_RECORD_ASSIGNED_ID)      . '</checkbox>'
          . '<checkbox name="notify3"'  . (($subscribe_flags & NOTIFY_RECORD_MODIFIED)      == 0 ? '>' : ' checked="true">') . get_html_resource(RES_NOTIFY_RECORD_MODIFIED_ID)      . '</checkbox>'
          . '<checkbox name="notify4"'  . (($subscribe_flags & NOTIFY_RECORD_STATE_CHANGED) == 0 ? '>' : ' checked="true">') . get_html_resource(RES_NOTIFY_RECORD_STATE_CHANGED_ID) . '</checkbox>'
          . '<checkbox name="notify5"'  . (($subscribe_flags & NOTIFY_RECORD_POSTPONED)     == 0 ? '>' : ' checked="true">') . get_html_resource(RES_NOTIFY_RECORD_POSTPONED_ID)     . '</checkbox>'
          . '<checkbox name="notify6"'  . (($subscribe_flags & NOTIFY_RECORD_RESUMED)       == 0 ? '>' : ' checked="true">') . get_html_resource(RES_NOTIFY_RECORD_RESUMED_ID)       . '</checkbox>'
          . '<checkbox name="notify7"'  . (($subscribe_flags & NOTIFY_COMMENT_ADDED)        == 0 ? '>' : ' checked="true">') . get_html_resource(RES_NOTIFY_COMMENT_ADDED_ID)        . '</checkbox>'
          . '<checkbox name="notify8"'  . (($subscribe_flags & NOTIFY_FILE_ATTACHED)        == 0 ? '>' : ' checked="true">') . get_html_resource(RES_NOTIFY_FILE_ATTACHED_ID)        . '</checkbox>'
          . '<checkbox name="notify9"'  . (($subscribe_flags & NOTIFY_FILE_REMOVED)         == 0 ? '>' : ' checked="true">') . get_html_resource(RES_NOTIFY_FILE_REMOVED_ID)         . '</checkbox>'
          . '<checkbox name="notify10"' . (($subscribe_flags & NOTIFY_RECORD_CLONED)        == 0 ? '>' : ' checked="true">') . get_html_resource(RES_NOTIFY_RECORD_CLONED_ID)        . '</checkbox>'
          . '<checkbox name="notify11"' . (($subscribe_flags & NOTIFY_SUBRECORD_ADDED)      == 0 ? '>' : ' checked="true">') . get_html_resource(RES_NOTIFY_SUBRECORD_ADDED_ID)      . '</checkbox>'
          . '<checkbox name="notify12"' . (($subscribe_flags & NOTIFY_SUBRECORD_REMOVED)    == 0 ? '>' : ' checked="true">') . get_html_resource(RES_NOTIFY_SUBRECORD_REMOVED_ID)    . '</checkbox>'
          . '</group>';
}

$xml .= '<button default="true">'      . get_html_resource($form == 'mainform' ? RES_OK_ID : RES_NEXT_ID) . '</button>'
      . '<button url="subscribe.php">' . get_html_resource(RES_CANCEL_ID)                                 . '</button>'
      . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>'
      . '</form>'
      . '</content>'
      . '</page>';

echo(xml2html($xml));

?>
