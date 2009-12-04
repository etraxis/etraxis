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
//  Artem Rodygin           2005-10-22      new-150: User should have ability to modify his subscriptions.
//  Artem Rodygin           2005-11-17      new-176: Change eTraxis design.
//  Artem Rodygin           2005-11-26      new-181: 'All fields marked with * should be filled in.' note is absent.
//  Artem Rodygin           2006-04-01      new-233: Email subscriptions functionality (new-125) should be conditionally "compiled".
//  Artem Rodygin           2006-06-28      new-274: "Crumbs" for creation and modification of filters or subscriptions are not clear.
//  Artem Rodygin           2006-11-15      new-374: Carbon copies in subscriptions.
//  Artem Rodygin           2007-07-04      new-533: Links between records.
//  Artem Rodygin           2007-08-06      new-551: Rework dependencies into "parent-child" relations.
//  Artem Rodygin           2007-11-13      new-622: Rename 'children' into 'subrecords'.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/projects.php');
require_once('../dbo/templates.php');
require_once('../dbo/subscribes.php');
require_once('../dbo/events.php');
/**#@-*/

init_page();

if (!EMAIL_NOTIFICATIONS_ENABLED)
{
    debug_write_log(DEBUG_NOTICE, 'Email Notifications functionality is disabled.');
    header('Location: index.php');
    exit;
}

$id        = ustr2int(try_request('id'));
$subscribe = subscribe_find($id);

if (!$subscribe)
{
    debug_write_log(DEBUG_NOTICE, 'Subscription cannot be found.');
    header('Location: subscribe.php');
    exit;
}

if (try_request('submitted') == 'mainform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $subscribe_name = ustrcut($_REQUEST['subscribe_name'], MAX_SUBSCRIBE_NAME);
    $carbon_copy    = ustrcut($_REQUEST['carbon_copy'],    MAX_SUBSCRIBE_CARBON_COPY);

    $subscribe_flags  = (isset($_REQUEST['notify1'])  ? NOTIFY_RECORD_CREATED       : 0);
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

    $error = subscribe_validate($subscribe_name, $carbon_copy);

    if ($error == NO_ERROR)
    {
        $error = subscribe_modify($id,
                                  $subscribe_name,
                                  $carbon_copy,
                                  $subscribe_flags);

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
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $subscribe_name  = $subscribe['subscribe_name'];
    $carbon_copy     = $subscribe['carbon_copy'];
    $subscribe_flags = $subscribe['subscribe_flags'];
}

$xml = '<page' . gen_xml_page_header(ustrprocess(get_html_resource(RES_SUBSCRIPTION_X_ID), ustr2html($subscribe['subscribe_name'])), isset($alert) ? $alert : NULL, 'mainform.subscribe_name') . '>'
     . gen_xml_menu()
     . '<path>'
     . gen_xml_rec_root()
     . '<pathitem url="subscribe.php">' . get_html_resource(RES_SUBSCRIPTIONS_ID) . '</pathitem>'
     . '<pathitem url="smodify.php?id=' . $id . '">' . ustrprocess(get_html_resource(RES_SUBSCRIPTION_X_ID), ustr2html($subscribe['subscribe_name'])) . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<form name="mainform" action="smodify.php?id=' . $id . '">'
     . '<group title="' . get_html_resource(RES_GENERAL_INFO_ID) . '">';

switch ($subscribe['subscribe_type'])
{
    case SUBSCRIBE_TYPE_ALL_PROJECTS:

        $xml .= '<combobox label="' . get_html_resource(RES_PROJECT_ID) . '" name="project">'
              . '<listitem value="0">' . get_html_resource(RES_ALL_PROJECTS_ID) . '</listitem>'
              . '</combobox>';

        break;

    case SUBSCRIBE_TYPE_ALL_TEMPLATES:

        $project = project_find($subscribe['subscribe_param']);

        if (!$project)
        {
            debug_write_log(DEBUG_WARNING, 'Project cannot be found.');
            header('Location: subscribe.php');
            exit;
        }

        $xml .= '<combobox label="' . get_html_resource(RES_PROJECT_ID) . '" name="project">'
              . '<listitem value="0">' . ustr2html($project['project_name']) . '</listitem>'
              . '</combobox>'
              . '<combobox label="' . get_html_resource(RES_TEMPLATE_ID) . '" name="template">'
              . '<listitem value="0">' . get_html_resource(RES_ALL_TEMPLATES_ID) . '</listitem>'
              . '</combobox>';

        break;

    case SUBSCRIBE_TYPE_ONE_TEMPLATE:

        $template = template_find($subscribe['subscribe_param']);

        if (!$template)
        {
            debug_write_log(DEBUG_WARNING, 'Template cannot be found.');
            header('Location: subscribe.php');
            exit;
        }

        $xml .= '<combobox label="' . get_html_resource(RES_PROJECT_ID) . '" name="project">'
              . '<listitem value="0">' . ustr2html($template['project_name']) . '</listitem>'
              . '</combobox>'
              . '<combobox label="' . get_html_resource(RES_TEMPLATE_ID) . '" name="template">'
              . '<listitem value="0">' . ustr2html($template['template_name']) . '</listitem>'
              . '</combobox>';

        break;

    default:
        debug_write_log(DEBUG_WARNING, 'Unknown subscription type = ' . $subscribe['subscribe_type']);
}

$xml .= '<editbox label="' . get_html_resource(RES_SUBSCRIPTION_NAME_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="subscribe_name" size="' . HTML_EDITBOX_SIZE_MEDIUM . '" maxlen="' . MAX_SUBSCRIBE_NAME        . '">' . ustr2html($subscribe_name) . '</editbox>'
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
      . '</group>'
      . '<button default="true">'      . get_html_resource(RES_OK_ID)     . '</button>'
      . '<button url="subscribe.php">' . get_html_resource(RES_CANCEL_ID) . '</button>'
      . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>'
      . '</form>'
      . '</content>'
      . '</page>';

echo(xml2html($xml));

?>
