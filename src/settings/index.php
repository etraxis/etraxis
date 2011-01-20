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
/**#@-*/

init_page();

// generate breadcrumbs and tabs

$xml = '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_SETTINGS_ID) . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<tabs>'
     . '<tab url="appearance.php">' . get_html_resource(RES_APPEARANCE_ID) . '</tab>'
     . '<tab url="csv.php">'        . get_html_resource(RES_CSV_ID)        . '</tab>';

if (!$_SESSION[VAR_LDAPUSER])
{
    $xml .= '<tab url="password.php">' . get_html_resource(RES_CHANGE_PASSWORD_ID) . '</tab>';
}

$xml .= '</tabs>';

echo(xml2html($xml, get_html_resource(RES_SETTINGS_ID)));

?>
