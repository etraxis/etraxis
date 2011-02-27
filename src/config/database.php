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

global $res_driver;

$xml = '<group>'
     . '<text label="' . get_html_resource(RES_DATABASE_TYPE_ID)   . '">' . get_html_resource($res_driver[DATABASE_DRIVER]) . '</text>'
     . '<text label="' . get_html_resource(RES_DATABASE_SERVER_ID) . '">' . ustr2html(DATABASE_HOST)                        . '</text>'
     . '<text label="' . get_html_resource(RES_DATABASE_NAME_ID)   . '">' . ustr2html(DATABASE_DBNAME)                      . '</text>'
     . '<text label="' . get_html_resource(RES_DATABASE_USER_ID)   . '">' . ustr2html(DATABASE_USERNAME)                    . '</text>'
     . '</group>';

echo(xml2html($xml));

?>
