<?php

/**
 * @package eTraxis
 * @ignore
 */

//--------------------------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system.
//  Copyright (C) 2006-2009 by Artem Rodygin
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
//  Artem Rodygin           2006-11-20      new-377: Custom views.
//  Artem Rodygin           2007-11-07      new-571: View should show all records of current filters set.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/views.php');
/**#@-*/

init_page();

$id   = ustr2int(try_request('id'));
$view = view_find($id);

if (!$view)
{
    debug_write_log(DEBUG_NOTICE, 'View cannot be found.');
    header('Location: vindex.php');
    exit;
}

if (try_request('submitted') == 'mainform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $view_name = ustrcut($_REQUEST['view_name'], MAX_VIEW_NAME);

    $error = view_validate($view_name);

    if ($error == NO_ERROR)
    {
        $error = view_modify($id, $view_name);

        if ($error == NO_ERROR)
        {
            header('Location: vindex.php');
            exit;
        }
    }

    switch ($error)
    {
        case ERROR_INCOMPLETE_FORM:
            $alert = get_js_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID);
            break;
        case ERROR_ALREADY_EXISTS:
            $alert = get_js_resource(RES_ALERT_VIEW_ALREADY_EXISTS_ID);
            break;
        default:
            $alert = NULL;
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $view_name = $view['view_name'];
}

$xml = '<page' . gen_xml_page_header(ustrprocess(get_html_resource(RES_VIEW_X_ID), ustr2html($view['view_name'])), isset($alert) ? $alert : NULL, 'mainform.view_name') . '>'
     . gen_xml_menu()
     . '<path>'
     . gen_xml_rec_root()
     . '<pathitem url="column.php">' . get_html_resource(RES_COLUMNS_ID) . '</pathitem>'
     . '<pathitem url="vindex.php">' . get_html_resource(RES_VIEWS_ID)   . '</pathitem>'
     . '<pathitem url="vmodify.php?id=' . $id . '">' . get_html_resource(RES_MODIFY_ID) . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<form name="mainform" action="vmodify.php?id=' . $id . '">'
     . '<group title="' . get_html_resource(RES_GENERAL_INFO_ID) . '">'
     . '<editbox label="' . get_html_resource(RES_VIEW_NAME_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="view_name" size="' . HTML_EDITBOX_SIZE_MEDIUM . '" maxlen="' . MAX_VIEW_NAME . '">' . ustr2html($view_name) . '</editbox>'
     . '</group>'
     . '<button default="true">'   . get_html_resource(RES_OK_ID)     . '</button>'
     . '<button url="vindex.php">' . get_html_resource(RES_CANCEL_ID) . '</button>'
     . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>'
     . '</form>'
     . '</content>'
     . '</page>';

echo(xml2html($xml));

?>
