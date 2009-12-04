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
//  Artem Rodygin           2007-11-13      new-622: Rename 'children' into 'subrecords'.
//  Artem Rodygin           2007-11-27      new-633: The 'dbx' extension should not be used.
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

if (!can_subrecord_be_removed($record, $permissions))
{
    debug_write_log(DEBUG_NOTICE, 'Subrecord cannot be removed.');
    header('Location: view.php?id=' . $id);
    exit;
}

if (try_request('submitted') == 'mainform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    if (isset($_REQUEST['subrecord']))
    {
        subrecord_remove($id, $_REQUEST['subrecord']);
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, 'No subrecord is selected.');
    }

    header('Location: view.php?id=' . $id);
    exit;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $subrecord_id = NULL;
}

$xml = '<page' . gen_xml_page_header(record_id($id, $record['template_prefix']), NULL, 'mainform.subrecord') . '>'
     . gen_xml_menu()
     . '<path>'
     . gen_xml_rec_root(try_cookie(COOKIE_SEARCH_MODE, FALSE))
     . '<pathitem url="view.php?id='   . $id . '">' . ustrprocess(get_html_resource(RES_RECORD_X_ID), record_id($id, $record['template_prefix'])) . '</pathitem>'
     . '<pathitem url="deprem.php?id=' . $id . '">' . get_html_resource(RES_REMOVE_SUBRECORD_ID) . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<form name="mainform" action="deprem.php?id=' . $id . '">'
     . '<group>'
     . '<listbox label="' . get_html_resource(RES_SUBRECORDS_ID) . '" name="subrecord" size="' . HTML_LISTBOX_SIZE . '">';

$list = subrecords_list($id);

while (($item = $list->fetch()))
{
    $xml .= '<listitem value="' . $item['record_id'] . '">'
          . record_id($item['record_id'], $item['template_prefix'])
          . '</listitem>';
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
