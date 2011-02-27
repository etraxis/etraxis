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
      . '<text label="' . get_html_resource(RES_STATUS_ID) . '">' . ustrtolower(get_html_resource(ATTACHMENTS_ENABLED ? RES_ENABLED2_ID : RES_DISABLED2_ID)) . '</text>';

if (ATTACHMENTS_ENABLED)
{
    $xml .= '<text label="' . get_html_resource(RES_MAX_SIZE_ID)    . '">' . ustrprocess(get_html_resource(RES_KB_ID), ATTACHMENTS_MAXSIZE) . '</text>'
          . '<text label="' . get_html_resource(RES_COMPRESSION_ID) . '">' . ustrtolower(get_html_resource(ATTACHMENTS_COMPRESSED ? RES_ENABLED2_ID : RES_DISABLED2_ID)) . '</text>'
          . '<text label="' . get_html_resource(RES_STORAGE_ID)     . '">' . ustr2html(ATTACHMENTS_PATH) . '</text>';
}

$xml .= '</group>';

echo(xml2html($xml));

?>
