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
require_once('../dbo/projects.php');
require_once('../dbo/templates.php');
require_once('../dbo/subscriptions.php');
require_once('../dbo/events.php');
/**#@-*/

init_page(LOAD_TAB);

if (!EMAIL_NOTIFICATIONS_ENABLED)
{
    debug_write_log(DEBUG_NOTICE, 'Email Notifications functionality is disabled.');
    exit;
}

// check that requested subscription exists

$id           = ustr2int(try_request('id'));
$subscription = subscription_find($id);

if (!$subscription)
{
    debug_write_log(DEBUG_NOTICE, 'Subscription cannot be found.');
    exit;
}

// local JS functions

$resTitle  = ustrprocess(get_js_resource(RES_SUBSCRIPTION_X_ID), ustr2js($subscription['subscribe_name']));
$resOK     = get_js_resource(RES_OK_ID);
$resCancel = get_js_resource(RES_CANCEL_ID);

$xml = <<<JQUERY
<script>

function subscriptionModify ()
{
    jqModal("{$resTitle}", "modify.php?id={$id}", "{$resOK}", "{$resCancel}", "$('#modifyform').submit()");
}

function subscriptionToggle ()
{
    $.post("disable.php?id={$id}", function () {
        reloadTab();
    });
}

</script>
JQUERY;

// generate buttons

$xml .= '<button url="index.php">' . get_html_resource(RES_BACK_ID) . '</button>'
      . '<buttonset>'
      . '<button action="subscriptionModify()">' . get_html_resource(RES_MODIFY_ID) . '</button>'
      . '<button url="delete.php?id=' . $id . '" prompt="' . get_html_resource(RES_CONFIRM_DELETE_SUBSCRIPTIONS_ID) . '">' . get_html_resource(RES_DELETE_ID) . '</button>'
      . '<button action="subscriptionToggle()">' . get_html_resource($subscription['is_activated'] ? RES_DISABLE_ID : RES_ENABLE_ID) . '</button>'
      . '</buttonset>';

// generate subscription information

$xml .= '<group>';

switch ($subscription['subscribe_type'])
{
    case SUBSCRIPTION_TYPE_ALL_PROJECTS:

        $xml .= '<text label="' . get_html_resource(RES_PROJECT_ID) . '">' . get_html_resource(RES_ALL_PROJECTS_ID) . '</text>';

        break;

    case SUBSCRIPTION_TYPE_ALL_TEMPLATES:

        $project = project_find($subscription['subscribe_param']);

        if (!$project)
        {
            debug_write_log(DEBUG_WARNING, 'Project cannot be found.');
            exit;
        }

        $xml .= '<text label="' . get_html_resource(RES_PROJECT_ID)  . '">' . ustr2html($project['project_name'])     . '</text>';
        $xml .= '<text label="' . get_html_resource(RES_TEMPLATE_ID) . '">' . get_html_resource(RES_ALL_TEMPLATES_ID) . '</text>';

        break;

    case SUBSCRIPTION_TYPE_ONE_TEMPLATE:

        $template = template_find($subscription['subscribe_param']);

        if (!$template)
        {
            debug_write_log(DEBUG_WARNING, 'Template cannot be found.');
            exit;
        }

        $xml .= '<text label="' . get_html_resource(RES_PROJECT_ID)  . '">' . ustr2html($template['project_name'])  . '</text>';
        $xml .= '<text label="' . get_html_resource(RES_TEMPLATE_ID) . '">' . ustr2html($template['template_name']) . '</text>';

        break;

    default:

        debug_write_log(DEBUG_WARNING, 'Unknown subscription type = ' . $subscription['subscribe_type']);
}

$xml .= '<text label="' . get_html_resource(RES_SUBSCRIPTION_NAME_ID) . '">' . ustr2html($subscription['subscribe_name']) . '</text>'
      . '<text label="' . get_html_resource(RES_CARBON_COPY_ID) . '">' . (is_null($subscription['carbon_copy']) ? get_html_resource(RES_NONE_ID) : ustr2html($subscription['carbon_copy'])) . '</text>'
      . '</group>'
      . '<group title="' . get_html_resource(RES_EVENTS_ID) . '">';

foreach ($notifications as $notification)
{
    $xml .= '<text label="' . get_html_resource($notification[NOTIFY_RESOURCE]) . '">'
          . get_html_resource(($subscription['subscribe_flags'] & $notification[NOTIFY_EVENT]) != 0 ? RES_YES_ID : RES_NO_ID)
          . '</text>';
}

$xml .= '</group>';

echo(xml2html($xml));

?>
