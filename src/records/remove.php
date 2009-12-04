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
//  Artem Rodygin           2005-08-23      new-053: All the calls of DAL API functions should be moved to DBO API.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-11-17      new-176: Change eTraxis design.
//  Artem Rodygin           2006-07-12      bug-292: Sablotron fails if page contains '&' character.
//  Artem Rodygin           2006-09-26      new-318: Group permissions should be template-wide.
//  Artem Rodygin           2006-10-08      bug-358: /src/records/remove.php: Global variable $alert was used before it was defined.
//  Artem Rodygin           2007-11-27      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2008-01-11      bug-663: Author permissions are ignored.
//  Artem Rodygin           2008-02-27      new-535: Permissions to attachments removal.
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

if (!can_file_be_removed($record, $permissions))
{
    debug_write_log(DEBUG_NOTICE, 'File cannot be removed.');
    header('Location: view.php?id=' . $id);
    exit;
}

if (try_request('submitted') == 'mainform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    if (isset($_REQUEST['attachments']))
    {
        attachment_remove($id, $permissions, $_REQUEST['attachments']);
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, 'No attachments are selected.');
    }

    header('Location: view.php?id=' . $id);
    exit;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $attachname = NULL;
}

$xml = '<page' . gen_xml_page_header(record_id($id, $record['template_prefix']), NULL, 'mainform.attachments') . '>'
     . gen_xml_menu()
     . '<path>'
     . gen_xml_rec_root(try_cookie(COOKIE_SEARCH_MODE, FALSE))
     . '<pathitem url="view.php?id='   . $id . '">' . ustrprocess(get_html_resource(RES_RECORD_X_ID), record_id($id, $record['template_prefix'])) . '</pathitem>'
     . '<pathitem url="remove.php?id=' . $id . '">' . get_html_resource(RES_REMOVE_FILE_ID) . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<form name="mainform" action="remove.php?id=' . $id . '">'
     . '<group>'
     . '<listbox label="' . get_html_resource(RES_ATTACHMENTS_ID) . '" name="attachments" size="' . HTML_LISTBOX_SIZE . '">';

$list = attachment_list($id, $permissions);

while (($item = $list->fetch()))
{
    $xml .= '<listitem value="' . $item['attachment_id'] . '">' . ustr2html($item['attachment_name']) . '</listitem>';
}

$xml .= '</listbox>'
      . '</group>'
      . '<button default="true">'                . get_html_resource(RES_OK_ID)     . '</button>'
      . '<button url="view.php?id=' . $id . '">' . get_html_resource(RES_CANCEL_ID) . '</button>'
      . '</form>'
      . '</content>'
      . '</page>';

echo(xml2html($xml));

?>
