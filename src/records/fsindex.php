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
//  Artem Rodygin           2007-10-29      new-564: Filters set.
//  Artem Rodygin           2007-11-27      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/filters.php');
/**#@-*/

init_page();

$xml = '<page' . gen_xml_page_header(get_html_resource(RES_VIEWS_ID)) . '>'
     . gen_xml_menu()
     . '<path>'
     . gen_xml_rec_root()
     . '<pathitem url="filter.php">'  . get_html_resource(RES_FILTERS_ID)      . '</pathitem>'
     . '<pathitem url="fsindex.php">' . get_html_resource(RES_FILTERS_SETS_ID) . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<form name="mainform" action="filter.php">'
     . '<group title="' . get_html_resource(RES_FILTERS_SETS_ID) . '">'
     . '<listbox name="fsets[]" size="' . HTML_LISTBOX_SIZE . '" multiple="true">';

$list = fsets_list();

while (($item = $list->fetch()))
{
    $xml .= '<listitem value="' . $item['fset_id'] . '">' . ustr2html($item['fset_name']) . '</listitem>';
}

$xml .= '</listbox>'
      . '<button action="window.open(\'fsmodify.php?id=\'+mainform.elements[2].value,\'_parent\');">' . get_html_resource(RES_MODIFY_ID) . '</button>'
      . '<button action="mainform.action=\'fsdelete.php\';mainform.submit();" prompt="' . get_html_resource(RES_CONFIRM_DELETE_FILTERS_SETS_ID) . '">' . get_html_resource(RES_DELETE_ID) . '</button>'
      . '</group>'
      . '<button default="true">' . get_html_resource(RES_BACK_ID) . '</button>'
      . '</form>'
      . '</content>'
      . '</page>';

echo(xml2html($xml));

?>
