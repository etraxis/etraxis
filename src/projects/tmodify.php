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
//  Artem Rodygin           2005-02-27      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-08-01      new-013: UI scenarios should be changed.
//  Artem Rodygin           2005-08-18      new-037: Any template should be locked to be modified without suspending a project.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-11-08      bug-174: Generated pages should contain <!DOCTYPE> tag.
//  Artem Rodygin           2005-11-17      new-176: Change eTraxis design.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/templates.php');
/**#@-*/

init_page();

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    header('Location: index.php');
    exit;
}

$id       = ustr2int(try_request('id'));
$template = template_find($id);

if (!$template)
{
    debug_write_log(DEBUG_NOTICE, 'Template cannot be found.');
    header('Location: index.php');
    exit;
}

if (!$template['is_locked'])
{
    debug_write_log(DEBUG_NOTICE, 'Template must be locked.');
    header('Location: tview.php?id=' . $id);
    exit;
}

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

    switch ($error)
    {
        case ERROR_INCOMPLETE_FORM:
            $alert = get_js_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID);
            break;
        case ERROR_ALREADY_EXISTS:
            $alert = get_js_resource(RES_ALERT_TEMPLATE_ALREADY_EXISTS_ID);
            break;
        case ERROR_INVALID_INTEGER_VALUE:
            $alert = get_js_resource(RES_ALERT_INVALID_INTEGER_VALUE_ID);
            break;
        case ERROR_INTEGER_VALUE_OUT_OF_RANGE:
            $alert = ustrprocess(get_js_resource(RES_ALERT_INTEGER_VALUE_OUT_OF_RANGE_ID), MIN_TEMPLATE_DAYS_COUNT, MAX_TEMPLATE_DAYS_COUNT);
            break;
        default:
            $alert = NULL;
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $template_name   = $template['template_name'];
    $template_prefix = $template['template_prefix'];
    $critical_age    = $template['critical_age'];
    $frozen_time     = $template['frozen_time'];
    $description     = $template['description'];
    $guest_access    = $template['guest_access'];
}

$xml = '<page' . gen_xml_page_header(ustrprocess(get_html_resource(RES_TEMPLATE_X_ID), ustr2html($template['template_name'])), isset($alert) ? $alert : NULL, 'mainform.template_name') . '>'
     . gen_xml_menu()
     . '<path>'
     . '<pathitem url="index.php">'                                      . get_html_resource(RES_PROJECTS_ID)                                                       . '</pathitem>'
     . '<pathitem url="view.php?id='    . $template['project_id'] . '">' . ustrprocess(get_html_resource(RES_PROJECT_X_ID), ustr2html($template['project_name']))   . '</pathitem>'
     . '<pathitem url="tindex.php?id='  . $template['project_id'] . '">' . get_html_resource(RES_TEMPLATES_ID)                                                      . '</pathitem>'
     . '<pathitem url="tview.php?id='   . $id                     . '">' . ustrprocess(get_html_resource(RES_TEMPLATE_X_ID), ustr2html($template['template_name'])) . '</pathitem>'
     . '<pathitem url="tmodify.php?id=' . $id                     . '">' . get_html_resource(RES_MODIFY_ID)                                                         . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<form name="mainform" action="tmodify.php?id=' . $id . '">'
     . '<group title="' . get_html_resource(RES_TEMPLATE_INFO_ID) . '">'
     . '<editbox label="' . get_html_resource(RES_TEMPLATE_NAME_ID)   . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="template_name"   size="' . HTML_EDITBOX_SIZE_MEDIUM . '" maxlen="' . MAX_TEMPLATE_NAME                . '">' . ustr2html($template_name)   . '</editbox>'
     . '<editbox label="' . get_html_resource(RES_TEMPLATE_PREFIX_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="template_prefix" size="' . HTML_EDITBOX_SIZE_MEDIUM . '" maxlen="' . MAX_TEMPLATE_PREFIX              . '">' . ustr2html($template_prefix) . '</editbox>'
     . '<editbox label="' . get_html_resource(RES_CRITICAL_AGE_ID)    . '"                                                        name="critical_age"    size="' . HTML_EDITBOX_SIZE_SMALL  . '" maxlen="' . ustrlen(MAX_TEMPLATE_DAYS_COUNT) . '">' . ustr2html($critical_age)    . '</editbox>'
     . '<editbox label="' . get_html_resource(RES_FROZEN_TIME_ID)     . '"                                                        name="frozen_time"     size="' . HTML_EDITBOX_SIZE_SMALL  . '" maxlen="' . ustrlen(MAX_TEMPLATE_DAYS_COUNT) . '">' . ustr2html($frozen_time)     . '</editbox>'
     . '<editbox label="' . get_html_resource(RES_DESCRIPTION_ID)     . '"                                                        name="description"     size="' . HTML_EDITBOX_SIZE_LONG   . '" maxlen="' . MAX_TEMPLATE_DESCRIPTION         . '">' . ustr2html($description)     . '</editbox>'
     . '<checkbox name="guest_access"' . ($guest_access ? ' checked="true">' : '>') . get_html_resource(RES_GUEST_ACCESS_ID) . '</checkbox>'
     . '</group>'
     . '<button default="true">'                 . get_html_resource(RES_OK_ID)     . '</button>'
     . '<button url="tview.php?id=' . $id . '">' . get_html_resource(RES_CANCEL_ID) . '</button>'
     . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>'
     . '<note>' . ustrprocess(get_html_resource(RES_ALERT_FIELD_VALUE_OUT_OF_RANGE_ID), get_html_resource(RES_CRITICAL_AGE_ID), MIN_TEMPLATE_DAYS_COUNT, MAX_TEMPLATE_DAYS_COUNT) . '</note>'
     . '<note>' . ustrprocess(get_html_resource(RES_ALERT_FIELD_VALUE_OUT_OF_RANGE_ID), get_html_resource(RES_FROZEN_TIME_ID),  MIN_TEMPLATE_DAYS_COUNT, MAX_TEMPLATE_DAYS_COUNT) . '</note>'
     . '</form>'
     . '</content>'
     . '</page>';

echo(xml2html($xml));

?>
