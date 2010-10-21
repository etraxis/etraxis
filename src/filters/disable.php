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
require_once('../engine/engine.php');
require_once('../dbo/filters.php');
/**#@-*/

init_page();

// check that requested filter exists

$id     = ustr2int(try_request('id'));
$filter = filter_find($id);

if (!$filter)
{
    debug_write_log(DEBUG_NOTICE, 'Filter cannot be found.');
    header('Location: index.php');
    exit;
}

// enable/disable

if (is_filter_activated($id))
{
    filters_clear(array($id));
}
else
{
    filters_set(array($id));
}

header('Location: view.php?id=' . $id);

?>
