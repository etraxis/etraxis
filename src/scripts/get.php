<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2010  Artem Rodygin
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
require_once('../engine/config.php');
/**#@-*/

// Check the requested name for validness.

$name = isset($_REQUEST['name'])
      ? $_REQUEST['name']
      : NULL;

mb_regex_encoding('UTF-8');

if (!mb_eregi('^([_0-9a-z\.\-])+$', $name))
{
    exit;
}

// Check the script file for existance.

$file = LOCALROOT . 'scripts/' . $name;

if (!is_file($file))
{
    exit;
}

// Output requested script file.

$output = file_get_contents($file);

if (extension_loaded('zlib') && !ini_get('zlib.output_compression'))
{
    $output = gzencode($output);
    header('Content-Encoding: gzip');
}

header('Content-Type: text/javascript');
header('Content-Length: ' . strlen($output));
header('Pragma: cache');
header('Cache-Control: public');
header('Last-Modified: ' . date(DATE_RFC822));
header('Expires: ' . date(DATE_RFC822, time() + 86400));

echo($output);

?>
