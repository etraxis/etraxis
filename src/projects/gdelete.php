<?php

/**
 * @package eTraxis
 * @ignore
 */

//--------------------------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system.
//  Copyright (C) 2005-2009 by Artem Rodygin
//
//  This program is free software; you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation; either version 2 of the License, or
//  (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License along
//  with this program; if not, write to the Free Software Foundation, Inc.,
//  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
//
//--------------------------------------------------------------------------------------------------
//  Author                  Date            Description of modifications
//--------------------------------------------------------------------------------------------------
//  Artem Rodygin           2005-02-26      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-08-01      new-013: UI scenarios should be changed.
//  Artem Rodygin           2005-08-18      new-036: Groups should be editable without suspending a project.
//  Artem Rodygin           2005-08-25      new-058: Global groups should be implemented.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2006-10-08      bug-333: /src/dbo/groups.php: Unused function argument: $link.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/groups.php');
/**#@-*/

init_page();

if (get_user_level() == USER_LEVEL_ADMIN)
{
    $id    = ustr2int(try_request('id'));
    $group = group_find($id);

    $pid   = ustr2int(try_request('pid'));

    if ($group)
    {
        if ($pid != 0 && $group['is_global'])
        {
            debug_write_log(DEBUG_NOTICE, 'Global group cannot be deleted from "Projects" menu.');
            header('Location: gview.php?id=' . $id . '&pid=' . $pid);
        }
        elseif (is_group_removable($id))
        {
            group_delete($id);
            header('Location: gindex.php?id=' . ($group['is_global'] ? $pid : $group['project_id']));
        }
        else
        {
            debug_write_log(DEBUG_NOTICE, 'Group is not removable.');
            header('Location: gview.php?id=' . $id . ($group['is_global'] ? '&pid=' . $pid : NULL));
        }
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, 'Group cannot be found.');
        header('Location: index.php');
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    header('Location: index.php');
}

?>
