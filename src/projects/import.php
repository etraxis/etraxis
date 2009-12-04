<?php

/**
 * @package eTraxis
 * @ignore
 */

//--------------------------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system.
//  Copyright (C) 2008-2009 by Artem Rodygin
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
//  Artem Rodygin           2008-02-06      new-601: [SF1814666] Export and Import Templates
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

if (try_request('submitted') == 'mainform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $id = NULL;
    $error = (isset($_FILES['xmlfile']) ? template_import($_FILES['xmlfile'], $id) : ERROR_UPLOAD_NO_FILE);

    if ($error == NO_ERROR)
    {
        header('Location: tview.php?id='. $id);
        exit;
    }

    switch ($error)
    {
        case ERROR_UPLOAD_INI_SIZE:
            $alert = get_js_resource(RES_ALERT_UPLOAD_INI_SIZE_ID);
            break;
        case ERROR_UPLOAD_FORM_SIZE:
            $alert = ustrprocess(get_js_resource(RES_ALERT_UPLOAD_FORM_SIZE_ID), ATTACHMENTS_MAXSIZE);
            break;
        case ERROR_UPLOAD_PARTIAL:
            $alert = get_js_resource(RES_ALERT_UPLOAD_PARTIAL_ID);
            break;
        case ERROR_UPLOAD_NO_FILE:
            $alert = get_js_resource(RES_ALERT_UPLOAD_NO_FILE_ID);
            break;
        case ERROR_UPLOAD_NO_TMP_DIR:
            $alert = get_js_resource(RES_ALERT_UPLOAD_NO_TMP_DIR_ID);
            break;
        case ERROR_UPLOAD_CANT_WRITE:
            $alert = get_js_resource(RES_ALERT_UPLOAD_CANT_WRITE_ID);
            break;
        case ERROR_UPLOAD_EXTENSION:
            $alert = get_js_resource(RES_ALERT_UPLOAD_EXTENSION_ID);
            break;
        case ERROR_DATE_VALUE_OUT_OF_RANGE:
        case ERROR_DEFAULT_VALUE_OUT_OF_RANGE:
        case ERROR_INCOMPLETE_FORM:
        case ERROR_INTEGER_VALUE_OUT_OF_RANGE:
        case ERROR_INVALID_DATE_VALUE:
        case ERROR_INVALID_EMAIL:
        case ERROR_INVALID_INTEGER_VALUE:
        case ERROR_INVALID_TIME_VALUE:
        case ERROR_INVALID_USERNAME:
        case ERROR_MIN_MAX_VALUES:
        case ERROR_NOT_FOUND:
        case ERROR_TIME_VALUE_OUT_OF_RANGE:
        case ERROR_UNKNOWN:
        case ERROR_XML_PARSER:
            $alert = get_js_resource(RES_ALERT_XML_PARSER_ERROR_ID);
            break;
        default:
            $alert = NULL;
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');
}

$xml = '<page' . gen_xml_page_header(get_html_resource(RES_IMPORT_ID), isset($alert) ? $alert : NULL, 'mainform.xmlfile') . '>'
     . gen_xml_menu()
     . '<path>'
     . '<pathitem url="index.php">'  . get_html_resource(RES_PROJECTS_ID) . '</pathitem>'
     . '<pathitem url="import.php">' . get_html_resource(RES_IMPORT_ID)   . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<form name="mainform" action="import.php" upload="' . (ATTACHMENTS_MAXSIZE * 1024) . '">'
     . '<group>'
     . '<filebox label="' . get_html_resource(RES_TEMPLATE_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="xmlfile" size="' . HTML_EDITBOX_SIZE_MEDIUM . '"/>'
     . '</group>'
     . '<button default="true">'  . get_html_resource(RES_OK_ID)     . '</button>'
     . '<button url="index.php">' . get_html_resource(RES_CANCEL_ID) . '</button>'
     . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID)                                 . '</note>'
     . '<note>' . ustrprocess(get_html_resource(RES_ALERT_UPLOAD_FORM_SIZE_ID), ATTACHMENTS_MAXSIZE) . '</note>'
     . '</form>'
     . '</content>'
     . '</page>';

echo(xml2html($xml));

?>
