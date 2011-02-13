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
require_once('../dbo/fields.php');
require_once('../dbo/records.php');
require_once('../dbo/views.php');
/**#@-*/

init_page(LOAD_CONTAINER, GUEST_IS_ALLOWED);

// process search mode, if one is specified

if (isset($_REQUEST['search']))
{
    debug_write_log(DEBUG_NOTICE, 'REQUEST["search"] is set.');

    $search_text = ustrcut($_REQUEST['search'], MAX_SEARCH_TEXT);

    if (ustrlen($search_text) == 0)
    {
        $_SESSION[VAR_SEARCH_MODE] = FALSE;
    }
    else
    {
        $_SESSION[VAR_SEARCH_MODE] = TRUE;
        $_SESSION[VAR_SEARCH_TEXT] = $search_text;
    }
}

// generate breadcrumbs and tabs

$xml = '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_RECORDS_ID) . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<tabs>'
     . '<tab url="list.php?search=">' . get_html_resource(RES_RECORDS_ID) . '</tab>';

if (ustrlen($_SESSION[VAR_SEARCH_TEXT]) != 0)
{
    $xml .= '<tab url="list.php?search=' . urlencode($_SESSION[VAR_SEARCH_TEXT]) . '">'
          . get_html_resource(RES_SEARCH_RESULTS_ID)
          . '</tab>';
}

$xml .= '</tabs>';

echo(xml2html($xml, get_html_resource(RES_RECORDS_ID)));

?>
