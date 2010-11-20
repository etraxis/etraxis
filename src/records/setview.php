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
require_once('../dbo/accounts.php');
require_once('../dbo/filters.php');
require_once('../dbo/views.php');
/**#@-*/

init_page();

$error = NO_ERROR;

$id = ustr2int(try_request('id'));

if ($id == 0)
{
    filters_clear();
    account_set_view();
}
else
{
    if (view_find($id))
    {
        $filters = view_filters_list($id);

        filters_clear();
        filters_set($filters);

        account_set_view($id);
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, 'View cannot be found.');
    }
}

?>
