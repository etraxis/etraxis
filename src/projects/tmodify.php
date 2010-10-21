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
require_once('../dbo/projects.php');
require_once('../dbo/templates.php');
/**#@-*/

init_page();

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    header('Location: index.php');
    exit;
}

// check that requested template exists

$id       = ustr2int(try_request('id'));
$template = template_find($id);

if (!$template)
{
    debug_write_log(DEBUG_NOTICE, 'Template cannot be found.');
    header('Location: index.php');
    exit;
}

// changed template has been submitted

if (try_request('submitted') == 'mainform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $template_name   = ustrcut($_REQUEST['template_name'],   MAX_TEMPLATE_NAME);
    $template_prefix = ustrcut($_REQUEST['template_prefix'], MAX_TEMPLATE_PREFIX);
    $critical_age    = ustrcut($_REQUEST['critical_age'],    ustrlen(MAX_TEMPLATE_DAYS_COUNT));
    $frozen_time     = ustrcut($_REQUEST['frozen_time'],     ustrlen(MAX_TEMPLATE_DAYS_COUNT));
    $description     = ustrcut($_REQUEST['description'],     MAX_TEMPLATE_DESCRIPTION);
    $guest_access    = isset($_REQUEST['guest_access']);

    $error = template_validate($template_name, $template_prefix, $critical_age, $frozen_time);

    if ($error == NO_ERROR)
    {
        $error = template_modify($id, $template['project_id'], $template_name, $template_prefix, $critical_age, $frozen_time, $description, $guest_access);

        if ($error == NO_ERROR)
        {
            header('Location: tview.php?id=' . $id);
            exit;
        }
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $error = NO_ERROR;

    $template_name   = $template['template_name'];
    $template_prefix = $template['template_prefix'];
    $critical_age    = $template['critical_age'];
    $frozen_time     = $template['frozen_time'];
    $description     = $template['description'];
    $guest_access    = $template['guest_access'];
}

// page's title

$title = ustrprocess(get_html_resource(RES_TEMPLATE_X_ID), ustr2html($template['template_name']));

// generate page

$xml = gen_context_menu('tview.php?id=', 'sview.php?id=', 'fview.php?id=', $template['project_id'], $id)
     . '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_PROJECTS_ID) . '</breadcrumb>'
     . '<breadcrumb url="tindex.php?id=' . $template['project_id'] . '">' . ustrprocess(get_html_resource(RES_PROJECT_X_ID), ustr2html($template['project_name'])) . '</breadcrumb>'
     . '<breadcrumb url="tview.php?id=' . $id . '">' . $title . '</breadcrumb>'
     . '<breadcrumb url="tmodify.php?id=' . $id . '">' . get_html_resource(RES_MODIFY_ID) . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<content>'
     . '<form name="mainform" action="tmodify.php?id=' . $id . '">'
     . '<group title="' . get_html_resource(RES_TEMPLATE_INFO_ID) . '">'
     . '<control name="template_name" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
     . '<label>' . get_html_resource(RES_TEMPLATE_NAME_ID) . '</label>'
     . '<editbox maxlen="' . MAX_TEMPLATE_NAME . '">' . ustr2html($template_name) . '</editbox>'
     . '</control>'
     . '<control name="template_prefix" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
     . '<label>' . get_html_resource(RES_TEMPLATE_PREFIX_ID) . '</label>'
     . '<editbox maxlen="' . MAX_TEMPLATE_PREFIX . '">' . ustr2html($template_prefix) . '</editbox>'
     . '</control>'
     . '<control name="critical_age">'
     . '<label>' . get_html_resource(RES_CRITICAL_AGE_ID) . '</label>'
     . '<editbox maxlen="' . ustrlen(MAX_TEMPLATE_DAYS_COUNT) . '">' . ustr2html($critical_age) . '</editbox>'
     . '</control>'
     . '<control name="frozen_time">'
     . '<label>' . get_html_resource(RES_FROZEN_TIME_ID) . '</label>'
     . '<editbox maxlen="' . ustrlen(MAX_TEMPLATE_DAYS_COUNT) . '">' . ustr2html($frozen_time) . '</editbox>'
     . '</control>'
     . '<control name="description">'
     . '<label>' . get_html_resource(RES_DESCRIPTION_ID) . '</label>'
     . '<editbox maxlen="' . MAX_TEMPLATE_DESCRIPTION . '">' . ustr2html($description) . '</editbox>'
     . '</control>'
     . '<control name="guest_access">'
     . '<label/>'
     . ($guest_access
           ? '<checkbox checked="true">'
           : '<checkbox>')
     . get_html_resource(RES_GUEST_ACCESS_ID)
     . '</checkbox>'
     . '</control>'
     . '</group>'
     . '<button default="true">' . get_html_resource(RES_OK_ID) . '</button>'
     . '<button url="tview.php?id=' . $id . '">' . get_html_resource(RES_CANCEL_ID) . '</button>'
     . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>'
     . '<note>' . ustrprocess(get_html_resource(RES_ALERT_FIELD_VALUE_OUT_OF_RANGE_ID), get_html_resource(RES_CRITICAL_AGE_ID), MIN_TEMPLATE_DAYS_COUNT, MAX_TEMPLATE_DAYS_COUNT) . '</note>'
     . '<note>' . ustrprocess(get_html_resource(RES_ALERT_FIELD_VALUE_OUT_OF_RANGE_ID), get_html_resource(RES_FROZEN_TIME_ID),  MIN_TEMPLATE_DAYS_COUNT, MAX_TEMPLATE_DAYS_COUNT) . '</note>'
     . '</form>';

// if some error was specified to display, force an alert

switch ($error)
{
    case ERROR_INCOMPLETE_FORM:
        $xml .= '<script>alert("' . get_js_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '");</script>';
        break;
    case ERROR_ALREADY_EXISTS:
        $xml .= '<script>alert("' . get_js_resource(RES_ALERT_TEMPLATE_ALREADY_EXISTS_ID) . '");</script>';
        break;
    case ERROR_INVALID_INTEGER_VALUE:
        $xml .= '<script>alert("' . get_js_resource(RES_ALERT_INVALID_INTEGER_VALUE_ID) . '");</script>';
        break;
    case ERROR_INTEGER_VALUE_OUT_OF_RANGE:
        $xml .= '<script>alert("' . ustrprocess(get_js_resource(RES_ALERT_INTEGER_VALUE_OUT_OF_RANGE_ID), MIN_TEMPLATE_DAYS_COUNT, MAX_TEMPLATE_DAYS_COUNT) . '");</script>';
        break;
    default: ;  // nop
}

$xml .= '</content>';

echo(xml2html($xml, $title));

?>
