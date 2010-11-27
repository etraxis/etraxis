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
/**#@-*/

init_page();

$error = NO_ERROR;

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    header('Location: index.php');
    exit;
}

// new project has been submitted

if (try_request('submitted') == 'mainform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $project_name = ustrcut($_REQUEST['project_name'], MAX_PROJECT_NAME);
    $description  = ustrcut($_REQUEST['description'],  MAX_PROJECT_DESCRIPTION);

    $error = project_validate($project_name, $description);

    if ($error == NO_ERROR)
    {
        $error = project_create($project_name, $description);

        if ($error == NO_ERROR)
        {
            header('Location: index.php');
            exit;
        }
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $project_name = NULL;
    $description  = NULL;
}

// generate page

$xml = '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_PROJECTS_ID) . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<tabs>'
     . '<tab url="index.php">'                . get_html_resource(RES_PROJECTS_ID) . '</tab>'
     . '<tab url="create.php" active="true">' . get_html_resource(RES_CREATE_ID)   . '</tab>'
     . '<tab url="import.php">'               . get_html_resource(RES_IMPORT_ID)   . '</tab>'
     . '<content>'
     . '<form name="mainform" action="create.php">'
     . '<group title="' . get_html_resource(RES_PROJECT_INFO_ID) . '">'
     . '<control name="project_name" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
     . '<label>' . get_html_resource(RES_PROJECT_NAME_ID) . '</label>'
     . '<editbox maxlen="' . MAX_PROJECT_NAME . '">' . ustr2html($project_name) . '</editbox>'
     . '</control>'
     . '<control name="description">'
     . '<label>' . get_html_resource(RES_DESCRIPTION_ID) . '</label>'
     . '<editbox maxlen="' . MAX_PROJECT_DESCRIPTION . '">' . ustr2html($description) . '</editbox>'
     . '</control>'
     . '</group>'
     . '<button default="true">' . get_html_resource(RES_OK_ID) . '</button>'
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
              . 'jqAlert("' . get_html_resource(RES_ERROR_ID) . '","' . get_html_resource(RES_ALERT_PROJECT_ALREADY_EXISTS_ID) . '","' . get_html_resource(RES_OK_ID) . '");'
              . '</scriptonreadyitem>';
        break;
    default: ;  // nop
}

echo(xml2html($xml, get_html_resource(RES_NEW_PROJECT_ID)));

?>
