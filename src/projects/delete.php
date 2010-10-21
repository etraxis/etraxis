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
require_once('../dbo/projects.php');
/**#@-*/

init_page();

if (get_user_level() == USER_LEVEL_ADMIN)
{
    $id = ustr2int(try_request('id'));
    $project = project_find($id);

    if (!$project)
    {
        debug_write_log(DEBUG_NOTICE, 'Project cannot be found.');
    }
    elseif (is_project_removable($id))
    {
        project_delete($id);
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, 'Project is not removable.');
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
}

header('Location: index.php');

?>
