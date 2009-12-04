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
//  Artem Rodygin           2005-06-06      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-07-31      new-006: Records search.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-11-17      new-176: Change eTraxis design.
//  Artem Rodygin           2006-01-23      new-200: User should not been requested for attachment name - current one should be always used.
//  Artem Rodygin           2006-07-24      bug-201: 'Access Forbidden' error with cyrillic named attachments.
//  Artem Rodygin           2006-09-26      new-318: Group permissions should be template-wide.
//  Artem Rodygin           2006-11-15      bug-381: Attachments of some types are not opened in valid applications.
//  Artem Rodygin           2006-12-14      new-446: Add processing of new upload errors.
//  Artem Rodygin           2008-01-11      bug-663: Author permissions are ignored.
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

if (!can_file_be_attached($record, $permissions))
{
    debug_write_log(DEBUG_NOTICE, 'File cannot be attached.');
    header('Location: view.php?id=' . $id);
    exit;
}

if (try_request('submitted') == 'mainform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $attachname = ustrcut($_REQUEST['attachname'], MAX_ATTACHMENT_NAME);

    $error = attachment_add($id, $attachname, $_FILES['attachfile']);

    if ($error == NO_ERROR)
    {
        header('Location: view.php?id=' . $id);
        exit;
    }

    switch ($error)
    {
        case ERROR_INCOMPLETE_FORM:
            $alert = get_js_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID);
            break;
        case ERROR_ALREADY_EXISTS:
            $alert = get_js_resource(RES_ALERT_ATTACHMENT_ALREADY_EXISTS_ID);
            break;
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
        default:
            $alert = NULL;
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $attachname = NULL;
}

$xml = '<page' . gen_xml_page_header(record_id($id, $record['template_prefix']), isset($alert) ? $alert : NULL, 'mainform.attachname') . '>'
     . gen_xml_menu()
     . '<path>'
     . gen_xml_rec_root(try_cookie(COOKIE_SEARCH_MODE, FALSE))
     . '<pathitem url="view.php?id='   . $id . '">' . ustrprocess(get_html_resource(RES_RECORD_X_ID), record_id($id, $record['template_prefix'])) . '</pathitem>'
     . '<pathitem url="attach.php?id=' . $id . '">' . get_html_resource(RES_ATTACH_FILE_ID) . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<form name="mainform" action="attach.php?id=' . $id . '" upload="' . (ATTACHMENTS_MAXSIZE * 1024) . '">'
     . '<group>'
     . '<editbox label="' . get_html_resource(RES_ATTACHMENT_NAME_ID) .                                                        '" name="attachname" size="' . HTML_EDITBOX_SIZE_MEDIUM . '" maxlen="' . MAX_ATTACHMENT_NAME . '">' . ustr2html($attachname) . '</editbox>'
     . '<filebox label="' . get_html_resource(RES_ATTACHMENT_FILE_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="attachfile" size="' . HTML_EDITBOX_SIZE_MEDIUM . '"/>'
     . '</group>'
     . '<button default="true">'                . get_html_resource(RES_OK_ID)     . '</button>'
     . '<button url="view.php?id=' . $id . '">' . get_html_resource(RES_CANCEL_ID) . '</button>'
     . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID)                                 . '</note>'
     . '<note>' . ustrprocess(get_html_resource(RES_ALERT_UPLOAD_FORM_SIZE_ID), ATTACHMENTS_MAXSIZE) . '</note>'
     . '</form>'
     . '</content>'
     . '</page>';

echo(xml2html($xml));

?>
