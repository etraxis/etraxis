<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2005-2009  Artem Rodygin
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
            header('Location: sview.php?tab=2&id=' . $field['state_id']);
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
