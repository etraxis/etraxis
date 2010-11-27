<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2006-2009  Artem Rodygin
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
require_once('../dbo/views.php');
/**#@-*/

init_page();

// new view has been submitted

if (try_request('submitted') == 'mainform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $view_name = ustrcut($_REQUEST['view_name'], MAX_VIEW_NAME);

    $error = view_validate($view_name);

    if ($error == NO_ERROR)
    {
        $error = view_create($_SESSION[VAR_USERID], $view_name);

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

    $error     = NO_ERROR;
    $view_name = NULL;
}

// generate page

$xml = '<breadcrumbs>'
     . '<breadcrumb url="create.php">' . get_html_resource(RES_VIEWS_ID) . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<tabs>'
     . '<tab url="index.php">'                . get_html_resource(RES_VIEWS_ID)  . '</tab>'
     . '<tab url="create.php" active="true">' . get_html_resource(RES_CREATE_ID) . '</tab>'
     . '<content>'
     . '<form name="mainform" action="create.php">'
     . '<group title="' . get_html_resource(RES_GENERAL_INFO_ID) . '">'
     . '<control name="view_name" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
     . '<label>' . get_html_resource(RES_VIEW_NAME_ID) . '</label>'
     . '<editbox maxlen="' . MAX_VIEW_NAME . '">' . ustr2html($view_name) . '</editbox>'
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
              . 'jqAlert("' . get_html_resource(RES_ERROR_ID) . '","' . get_html_resource(RES_ALERT_VIEW_ALREADY_EXISTS_ID) . '","' . get_html_resource(RES_OK_ID) . '");'
              . '</scriptonreadyitem>';
        break;
    default: ;  // nop
}

echo(xml2html($xml, get_html_resource(RES_NEW_VIEW_ID)));

?>
