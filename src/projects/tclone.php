<?php

/**
 * @package eTraxis
 * @ignore
 */

//--------------------------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system.
//  Copyright (C) 2007-2009 by Artem Rodygin
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
//  Artem Rodygin           2007-08-02      new-139: Templates cloning.
//  Artem Rodygin           2007-11-26      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//  Artem Rodygin           2009-09-09      new-826: Native unicode support for Microsoft SQL Server.
//--------------------------------------------------------------------------------------------------

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

$id       = ustr2int(try_request('id'));
$template = template_find($id);

if (!$template)
{
    debug_write_log(DEBUG_NOTICE, 'Template cannot be found.');
    header('Location: index.php');
    exit;
}

if (try_request('submitted') == 'mainform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $project_id = ustr2int(try_request('project'));

    $project = project_find($project_id);

    if (!$project)
    {
        debug_write_log(DEBUG_NOTICE, 'Project cannot be found.');
        header('Location: tview.php?id=' . $id);
        exit;
    }

    $template_name   = ustrcut($_REQUEST['template_name'],   MAX_TEMPLATE_NAME);
    $template_prefix = ustrcut($_REQUEST['template_prefix'], MAX_TEMPLATE_PREFIX);
    $critical_age    = ustrcut($_REQUEST['critical_age'],    ustrlen(MAX_TEMPLATE_DAYS_COUNT));
    $frozen_time     = ustrcut($_REQUEST['frozen_time'],     ustrlen(MAX_TEMPLATE_DAYS_COUNT));
    $description     = ustrcut($_REQUEST['description'],     MAX_TEMPLATE_DESCRIPTION);
    $guest_access    = isset($_REQUEST['guest_access']);

    $error = template_validate($template_name, $template_prefix, $critical_age, $frozen_time);

    if ($error == NO_ERROR)
    {
        $error = template_create($project_id, $template_name, $template_prefix, $critical_age, $frozen_time, $description, $guest_access);

        if ($error == NO_ERROR)
        {
            $rs = dal_query('templates/fndk.sql', $project_id, ustrtolower($template_name), ustrtolower($template_prefix));

            if ($rs->rows == 0)
            {
                debug_write_log(DEBUG_WARNING, 'Created template not found.');
                header('Location: tview.php?id=' . $id);
            }
            else
            {
                $template_id = $rs->fetch('template_id');
                template_clone($id, $template_id);
                header('Location: tview.php?id=' . $template_id);
            }

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

    $project_id      = $template['project_id'];
    $template_name   = $template['template_name'];
    $template_prefix = $template['template_prefix'];
    $critical_age    = $template['critical_age'];
    $frozen_time     = $template['frozen_time'];
    $description     = $template['description'];
    $guest_access    = $template['guest_access'];
}

$xml = '<page' . gen_xml_page_header(get_html_resource(RES_NEW_TEMPLATE_ID), isset($alert) ? $alert : NULL, 'mainform.template_name') . '>'
     . gen_xml_menu()
     . '<path>'
     . '<pathitem url="index.php">'                                     . get_html_resource(RES_PROJECTS_ID)                                                       . '</pathitem>'
     . '<pathitem url="view.php?id='   . $template['project_id'] . '">' . ustrprocess(get_html_resource(RES_PROJECT_X_ID), ustr2html($template['project_name']))   . '</pathitem>'
     . '<pathitem url="tindex.php?id=' . $template['project_id'] . '">' . get_html_resource(RES_TEMPLATES_ID)                                                      . '</pathitem>'
     . '<pathitem url="tview.php?id='  . $id                     . '">' . ustrprocess(get_html_resource(RES_TEMPLATE_X_ID), ustr2html($template['template_name'])) . '</pathitem>'
     . '<pathitem url="tclone.php?id=' . $id                     . '">' . get_html_resource(RES_CLONE_ID)                                                          . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<form name="mainform" action="tclone.php?id=' . $id . '">'
     . '<group title="' . get_html_resource(RES_TEMPLATE_INFO_ID) . '">'
     . '<combobox label="' . get_html_resource(RES_PROJECT_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="project">';

$rs = dal_query('projects/list.sql', 'p.project_name');

while (($row = $rs->fetch()))
{
    $xml .= '<listitem value="' . $row['project_id'] . ($row['project_id'] == $project_id ? '" selected="true">' : '">')
          . ustr2html($row['project_name'])
          . '</listitem>';
}

$xml .= '</combobox>'
      . '<editbox label="' . get_html_resource(RES_TEMPLATE_NAME_ID)   . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="template_name"   size="' . HTML_EDITBOX_SIZE_MEDIUM . '" maxlen="' . MAX_TEMPLATE_NAME                . '">' . ustr2html($template_name)   . '</editbox>'
      . '<editbox label="' . get_html_resource(RES_TEMPLATE_PREFIX_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="template_prefix" size="' . HTML_EDITBOX_SIZE_MEDIUM . '" maxlen="' . MAX_TEMPLATE_PREFIX              . '">' . ustr2html($template_prefix) . '</editbox>'
      . '<editbox label="' . get_html_resource(RES_CRITICAL_AGE_ID)    .                                                        '" name="critical_age"    size="' . HTML_EDITBOX_SIZE_SMALL  . '" maxlen="' . ustrlen(MAX_TEMPLATE_DAYS_COUNT) . '">' . ustr2html($critical_age)    . '</editbox>'
      . '<editbox label="' . get_html_resource(RES_FROZEN_TIME_ID)     .                                                        '" name="frozen_time"     size="' . HTML_EDITBOX_SIZE_SMALL  . '" maxlen="' . ustrlen(MAX_TEMPLATE_DAYS_COUNT) . '">' . ustr2html($frozen_time)     . '</editbox>'
      . '<editbox label="' . get_html_resource(RES_DESCRIPTION_ID)     .                                                        '" name="description"     size="' . HTML_EDITBOX_SIZE_LONG   . '" maxlen="' . MAX_TEMPLATE_DESCRIPTION         . '">' . ustr2html($description)     . '</editbox>'
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
