<?php

/**
 * @package eTraxis
 * @ignore
 */

//--------------------------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system.
//  Copyright (C) 2005-2010 by Artem Rodygin
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
//  Artem Rodygin           2005-07-20      new-009: Records filter.
//  Artem Rodygin           2005-07-31      new-006: Records search.
//  Artem Rodygin           2005-08-23      new-053: All the calls of DAL API functions should be moved to DBO API.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-10-19      new-149: User should have ability to modify his filters.
//  Artem Rodygin           2005-11-17      new-176: Change eTraxis design.
//  Artem Rodygin           2006-07-12      bug-292: Sablotron fails if page contains '&' character.
//  Artem Rodygin           2006-08-13      bug-306: Filter cannot be modified.
//  Artem Rodygin           2006-11-05      new-365: Filters sharing.
//  Artem Rodygin           2006-12-20      new-459: 'Filters' and 'Subscriptions' pages should contain ability to clear current selection.
//  Artem Rodygin           2006-12-30      new-475: Turning subscriptions on and off is not clear.
//  Artem Rodygin           2007-10-24      new-564: Filters set.
//  Artem Rodygin           2007-11-27      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2008-03-15      new-683: Filters should be sharable with groups, not with accounts.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//  Artem Rodygin           2010-01-26      bug-894: Some pages don't work in Google Chrome.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/filters.php');
/**#@-*/

init_page();

if (try_request('submitted') == 'lform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted (enabling filters).');

    if (isset($_REQUEST['filters']))
    {
        filters_set($_REQUEST['filters']);
    }

    header('Location: filter.php');
    exit;
}
elseif (try_request('submitted') == 'rform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted (disabling filters).');

    if (isset($_REQUEST['filters']))
    {
        filters_clear($_REQUEST['filters']);
    }

    header('Location: filter.php');
    exit;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');
}

$xml = '<page' . gen_xml_page_header(get_html_resource(RES_FILTERS_ID)) . '>'
     . gen_xml_menu()
     . '<path>'
     . gen_xml_rec_root()
     . '<pathitem url="filter.php">' . get_html_resource(RES_FILTERS_ID) . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<dualbox>'
     . '<dualleft action="filter.php">'
     . '<group title="' . get_html_resource(RES_DISABLED2_ID) . '">'
     . '<listbox dualbox="true" name="filters[]" size="' . HTML_LISTBOX_SIZE . '" multiple="true">';

$list = filters_list($_SESSION[VAR_USERID], FALSE);

while (($item = $list->fetch()))
{
    $xml .= '<listitem value="' . $item['filter_id'] . '">'
          . ustr2html($item['filter_name'])
          . ($item['shared'] ? ' (' . ustr2html($item['fullname']) . ')' : NULL)
          . '</listitem>';
}

$xml .= '</listbox>'
      . '<button action="window.open(\'fmodify.php?id=\'+getElementsByName(\'filters[]\')[0].value,\'_parent\');">' . get_html_resource(RES_MODIFY_ID) . '</button>'
      . '<button action="lform.action=\'fdelete.php\';lform.submit();" prompt="' . get_html_resource(RES_CONFIRM_DELETE_FILTERS_ID) . '">' . get_html_resource(RES_DELETE_ID) . '</button>'
      . '</group>'
      . '</dualleft>'
      . '<dualright action="filter.php">'
      . '<group title="' . get_html_resource(RES_ENABLED2_ID) . '">'
      . '<listbox dualbox="true" name="filters[]" size="' . HTML_LISTBOX_SIZE . '" multiple="true">';

$list = filters_list($_SESSION[VAR_USERID], TRUE);

while (($item = $list->fetch()))
{
    $xml .= '<listitem value="' . $item['filter_id'] . '">'
          . ustr2html($item['filter_name'])
          . ($item['shared'] ? ' (' . ustr2html($item['fullname']) . ')' : NULL)
          . '</listitem>';
}

$xml .= '</listbox>'
      . '<button action="window.open(\'fmodify.php?id=\'+getElementsByName(\'filters[]\')[1].value,\'_parent\');">' . get_html_resource(RES_MODIFY_ID) . '</button>'
      . '<button action="rform.action=\'fdelete.php\';rform.submit();" prompt="' . get_html_resource(RES_CONFIRM_DELETE_FILTERS_ID) . '">' . get_html_resource(RES_DELETE_ID) . '</button>'
      . '<nbsp/>'
      . '<button url="fscreate.php">' . get_html_resource(RES_SAVE_FILTERS_SET_ID) . '</button>'
      . '</group>'
      . '</dualright>'
      . '</dualbox>'
      . '<button default="true" url="index.php">' . get_html_resource(RES_BACK_ID)   . '</button>'
      . '<button url="fcreate.php">'              . get_html_resource(RES_CREATE_ID) . '</button>'
      . '<nbsp/>'
      . '<button url="fsindex.php">' . get_html_resource(RES_FILTERS_SETS_ID) . '</button>'
      . '</content>'
      . '</page>';

echo(xml2html($xml));

?>
