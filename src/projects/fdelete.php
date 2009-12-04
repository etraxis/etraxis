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
//  Artem Rodygin           2005-03-24      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-08-01      new-013: UI scenarios should be changed.
//  Artem Rodygin           2005-08-18      new-037: Any template should be locked to be modified without suspending a project.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//  Artem Rodygin           2009-10-17      new-802: [SF2704057] possibility to disable fields
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/fields.php');
/**#@-*/

init_page();

if (get_user_level() == USER_LEVEL_ADMIN)
{
    $id    = ustr2int(try_request('id'));
    $field = field_find($id);

    if ($field)
    {
        if ($field['is_locked'])
        {
            field_delete($id);
            header('Location: findex.php?id=' . $field['state_id']);
        }
        else
        {
            debug_write_log(DEBUG_NOTICE, 'Field is not removable.');
            header('Location: fview.php?id=' . $id);
        }
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, 'Field cannot be found.');
        header('Location: index.php');
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    header('Location: index.php');
}

?>
