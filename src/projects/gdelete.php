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
require_once('../dbo/groups.php');
/**#@-*/

init_page();

$project_id = 0;

if (get_user_level() == USER_LEVEL_ADMIN)
{
    $id = ustr2int(try_request('id'));
    $group = group_find($id);

    if (!$group)
    {
        debug_write_log(DEBUG_NOTICE, 'Group cannot be found.');
    }
    elseif ($group['is_global'])
    {
        debug_write_log(DEBUG_NOTICE, 'Group must be local.');
    }
    elseif (is_group_removable($id))
    {
        $project_id = $group['project_id'];
        group_delete($id);
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, 'Group is not removable.');
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
}

header('Location: gindex.php?id=' . $project_id);

?>
