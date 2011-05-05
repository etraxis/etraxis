<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2005-2011  Artem Rodygin
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
require_once('../dbo/subscriptions.php');
/**#@-*/

init_page(LOAD_INLINE);

if (!EMAIL_NOTIFICATIONS_ENABLED)
{
    debug_write_log(DEBUG_NOTICE, 'Email Notifications functionality is disabled.');
    header('HTTP/1.1 307 ../index.php');
    exit;
}

// check that requested subscription exists

$id           = ustr2int(try_request('id'));
$subscription = subscription_find($id);

if (!$subscription)
{
    debug_write_log(DEBUG_NOTICE, 'Subscription cannot be found.');
    header('HTTP/1.1 307 index.php');
    exit;
}

// changed subscription has been submitted

if (try_request('submitted') == 'modifyform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

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
        $error = subscription_modify($id,
                                     $subscription_name,
                                     $carbon_copy,
                                     $subscription_flags);
    }

    switch ($error)
    {
        case NO_ERROR:
            header('HTTP/1.0 200 OK');
            break;

        case ERROR_INCOMPLETE_FORM:
            send_http_error(get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID));
            break;

        case ERROR_ALREADY_EXISTS:
            send_http_error(get_html_resource(RES_ALERT_SUBSCRIPTION_ALREADY_EXISTS_ID));
            break;

        case ERROR_INVALID_EMAIL:
            send_http_error(get_html_resource(RES_ALERT_INVALID_EMAIL_ID));
            break;

        default:
            send_http_error(get_html_resource(RES_ALERT_UNKNOWN_ERROR_ID));
    }

    exit;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $error = NO_ERROR;

    $subscription_name  = $subscription['subscribe_name'];
    $carbon_copy        = $subscription['carbon_copy'];
    $subscription_flags = $subscription['subscribe_flags'];
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
    jqAlert("{$resTitle}", XMLHttpRequest.responseText, "{$resOK}");
}

</script>
JQUERY;

// generate page

$xml .= '<form name="modifyform" action="modify.php?id=' . $id . '" success="modifySuccess" error="modifyError">'
      . '<group>';

switch ($subscription['subscribe_type'])
{
    case SUBSCRIPTION_TYPE_ALL_PROJECTS:

        $xml .= '<control name="project" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
              . '<label>' . get_html_resource(RES_PROJECT_ID) . '</label>'
              . '<combobox>'
              . '<listitem value="0">' . get_html_resource(RES_ALL_PROJECTS_ID) . '</listitem>'
              . '</combobox>'
              . '</control>';

        break;

    case SUBSCRIPTION_TYPE_ALL_TEMPLATES:

        $project = project_find($subscription['subscribe_param']);

        if (!$project)
        {
            debug_write_log(DEBUG_WARNING, 'Project cannot be found.');
            header('HTTP/1.1 307 view.php?id=' . $id);
            exit;
        }

        $xml .= '<control name="project" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
              . '<label>' . get_html_resource(RES_PROJECT_ID) . '</label>'
              . '<combobox>'
              . '<listitem value="0">' . ustr2html($project['project_name']) . '</listitem>'
              . '</combobox>'
              . '</control>'
              . '<control name="template" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
              . '<label>' . get_html_resource(RES_TEMPLATE_ID) . '</label>'
              . '<combobox>'
              . '<listitem value="0">' . get_html_resource(RES_ALL_TEMPLATES_ID) . '</listitem>'
              . '</combobox>'
              . '</control>';

        break;

    case SUBSCRIPTION_TYPE_ONE_TEMPLATE:

        $template = template_find($subscription['subscribe_param']);

        if (!$template)
        {
            debug_write_log(DEBUG_WARNING, 'Template cannot be found.');
            header('HTTP/1.1 307 view.php?id=' . $id);
            exit;
        }

        $xml .= '<control name="project" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
              . '<label>' . get_html_resource(RES_PROJECT_ID) . '</label>'
              . '<combobox>'
              . '<listitem value="0">' . ustr2html($template['project_name']) . '</listitem>'
              . '</combobox>'
              . '</control>'
              . '<control name="template" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
              . '<label>' . get_html_resource(RES_TEMPLATE_ID) . '</label>'
              . '<combobox>'
              . '<listitem value="0">' . ustr2html($template['template_name']) . '</listitem>'
              . '</combobox>'
              . '</control>';

        break;

    default:

        debug_write_log(DEBUG_WARNING, 'Unknown subscription type = ' . $subscription['subscribe_type']);
}

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

$xml .= '</group>'
      . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>'
      . '</form>';

echo(xml2html($xml));

?>
