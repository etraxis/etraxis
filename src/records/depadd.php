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
//  Artem Rodygin           2007-07-04      new-533: Links between records.
//  Artem Rodygin           2007-08-06      new-551: Rework dependencies into "parent-child" relations.
//  Artem Rodygin           2007-10-17      new-602: Rename "Add child" to "Attach child".
//  Artem Rodygin           2007-11-13      new-622: Rename 'children' into 'subrecords'.
//  Artem Rodygin           2008-01-11      bug-663: Author permissions are ignored.
//  Artem Rodygin           2008-04-19      new-705: Multiple parents for subrecords.
//  Artem Rodygin           2008-04-20      new-703: Separated permissions set for current responsible.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/records.php');
/**#@-*/

init_page();

$id     = ustr2int(try_request('id'));
$record = record_find($id);

if (!$record)
{
    debug_write_log(DEBUG_NOTICE, 'Record cannot be found.');
    header('Location: index.php');
    exit;
}

$permissions = record_get_permissions($record['template_id'], $record['creator_id'], $record['responsible_id']);

if (!can_subrecord_be_added($record, $permissions))
{
    debug_write_log(DEBUG_NOTICE, 'Subrecord cannot be added.');
    header('Location: view.php?id=' . $id);
    exit;
}

if (try_request('submitted') == 'mainform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $subrecord_id  = trim(try_request('subrecord'));
    $is_dependency = isset($_REQUEST['is_dependency']);

    $error = subrecord_validate($id, $subrecord_id);

    if ($error == NO_ERROR)
    {
        $error = subrecord_add($id, intval($subrecord_id), $is_dependency);

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
            $alert = get_js_resource(RES_ALERT_SUBRECORD_ALREADY_EXISTS_ID);
            break;
        case ERROR_INVALID_INTEGER_VALUE:
            $alert = get_js_resource(RES_ALERT_INVALID_INTEGER_VALUE_ID);
            break;
        case ERROR_RECORD_NOT_FOUND:
            $alert = get_js_resource(RES_ALERT_RECORD_NOT_FOUND_ID);
            break;
        default:
            $alert = NULL;
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $subrecord_id  = NULL;
    $is_dependency = TRUE;
}

$xml = '<page' . gen_xml_page_header(record_id($id, $record['template_prefix']), isset($alert) ? $alert : NULL, 'mainform.subrecord') . '>'
     . gen_xml_menu()
     . '<path>'
     . gen_xml_rec_root(try_cookie(COOKIE_SEARCH_MODE, FALSE))
     . '<pathitem url="view.php?id='   . $id . '">' . ustrprocess(get_html_resource(RES_RECORD_X_ID), record_id($id, $record['template_prefix'])) . '</pathitem>'
     . '<pathitem url="depadd.php?id=' . $id . '">' . get_html_resource(RES_ATTACH_SUBRECORD_ID) . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<form name="mainform" action="depadd.php?id=' . $id . '">'
     . '<group>'
     . '<editbox label="' . get_html_resource(RES_SUBRECORD_ID_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="subrecord" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(MAXINT) . '">' . ustr2html($subrecord_id) . '</editbox>'
     . '<checkbox name="is_dependency"' . ($is_dependency ? ' checked="true">' : '>') . get_html_resource(RES_DEPENDENCY_ID) . '</checkbox>'
     . '</group>'
     . '<button default="true">'                . get_html_resource(RES_OK_ID)     . '</button>'
     . '<button url="view.php?id=' . $id . '">' . get_html_resource(RES_CANCEL_ID) . '</button>'
     . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>'
     . '</form>'
     . '</content>'
     . '</page>';

echo(xml2html($xml));

?>
