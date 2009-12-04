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
//  Artem Rodygin           2005-02-18      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-08-01      new-013: UI scenarios should be changed.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-11-17      new-176: Change eTraxis design.
//  Artem Rodygin           2006-07-27      new-261: UI design should be adopted to slow connection.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/projects.php');
/**#@-*/

init_page();

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    header('Location: index.php');
    exit;
}

$id      = ustr2int(try_request('id'));
$project = project_find($id);

if (!$project)
{
    debug_write_log(DEBUG_NOTICE, 'Project cannot be found.');
    header('Location: index.php');
    exit;
}

if (try_request('submitted') == 'mainform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $project_name = ustrcut($_REQUEST['project_name'], MAX_PROJECT_NAME);
    $description  = ustrcut($_REQUEST['description'],  MAX_PROJECT_DESCRIPTION);
    $is_suspended = isset($_REQUEST['is_suspended']);

    $error = project_validate($project_name);

    if ($error == NO_ERROR)
    {
        $error = project_modify($id, $project_name, $description, $is_suspended);

        if ($error == NO_ERROR)
        {
            header('Location: view.php?id=' . $id);
            exit;
        }
    }

    switch ($error)
    {
        case ERROR_INCOMPLETE_FORM:
            $alert = get_js_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID);
            break;
        case ERROR_ALREADY_EXISTS:
            $alert = get_js_resource(RES_ALERT_PROJECT_ALREADY_EXISTS_ID);
            break;
        default:
            $alert = NULL;
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $project_name = $project['project_name'];
    $description  = $project['description'];
    $is_suspended = $project['is_suspended'];
}

$xml = '<page' . gen_xml_page_header(ustrprocess(get_html_resource(RES_PROJECT_X_ID), ustr2html($project['project_name'])), isset($alert) ? $alert : NULL, 'mainform.project_name') . '>'
     . gen_xml_menu()
     . '<path>'
     . '<pathitem url="index.php">'                 . get_html_resource(RES_PROJECTS_ID)                                                    . '</pathitem>'
     . '<pathitem url="view.php?id='   . $id . '">' . ustrprocess(get_html_resource(RES_PROJECT_X_ID), ustr2html($project['project_name'])) . '</pathitem>'
     . '<pathitem url="modify.php?id=' . $id . '">' . get_html_resource(RES_MODIFY_ID)                                                      . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<form name="mainform" action="modify.php?id=' . $id . '">'
     . '<group title="' . get_html_resource(RES_PROJECT_INFO_ID) . '">'
     . '<editbox label="' . get_html_resource(RES_PROJECT_NAME_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="project_name" size="' . HTML_EDITBOX_SIZE_MEDIUM . '" maxlen="' . MAX_PROJECT_NAME        . '">' . ustr2html($project_name) . '</editbox>'
     . '<editbox label="' . get_html_resource(RES_DESCRIPTION_ID)  . '"                                                        name="description"  size="' . HTML_EDITBOX_SIZE_LONG   . '" maxlen="' . MAX_PROJECT_DESCRIPTION . '">' . ustr2html($description)  . '</editbox>'
     . '<checkbox name="is_suspended"' . ($is_suspended ? ' checked="true">' : '>') . get_html_resource(RES_SUSPENDED_ID) . '</checkbox>'
     . '</group>'
     . '<button default="true">'                . get_html_resource(RES_OK_ID)     . '</button>'
     . '<button url="view.php?id=' . $id . '">' . get_html_resource(RES_CANCEL_ID) . '</button>'
     . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>'
     . '</form>'
     . '</content>'
     . '</page>';

echo(xml2html($xml));

?>
