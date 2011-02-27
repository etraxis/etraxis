<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2005-2011  Artem Rodygin
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

init_page(LOAD_TAB);

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    header('Location: ../index.php');
    exit;
}

// generate configuration info

$xml = '<group>'
     . '<text label="' . get_html_resource(RES_LOCALROOT_ID)        . '">' . ustr2html(LOCALROOT)                           . '</text>'
     . '<text label="' . get_html_resource(RES_WEBROOT_ID)          . '">' . ustr2html(WEBROOT)                             . '</text>'
     . '<text label="' . get_html_resource(RES_DEFAULT_LANGUAGE_ID) . '">' . get_html_resource(RES_LOCALE_ID, LANG_DEFAULT) . '</text>'
     . '<text label="' . get_html_resource(RES_THEME_ID)            . '">' . ustr2html(THEME_DEFAULT)                       . '</text>'
     . '</group>';

echo(xml2html($xml));

?>
