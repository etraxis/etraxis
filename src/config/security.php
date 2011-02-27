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
     . '<text label="' . get_html_resource(RES_MIN_PASSWORD_LENGTH_ID) . '">' . ustr2html(MIN_PASSWORD_LENGTH) . '</text>'
     . '<text label="' . get_html_resource(RES_PASSWORD_EXPIRATION_ID) . '">' . ustr2html(PASSWORD_EXPIRATION) . '</text>'
     . '<text label="' . get_html_resource(RES_SESSION_EXPIRATION_ID)  . '">' . ustr2html(SESSION_EXPIRE)      . '</text>'
     . '<text label="' . get_html_resource(RES_LOCKS_COUNT_ID)         . '">' . ustr2html(LOCKS_COUNT)         . '</text>'
     . '<text label="' . get_html_resource(RES_LOCKS_TIMEOUT_ID)       . '">' . ustr2html(LOCKS_TIMEOUT)       . '</text>'
     . '</group>';

echo(xml2html($xml));

?>
