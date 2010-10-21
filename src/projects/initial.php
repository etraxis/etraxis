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
require_once('../dbo/states.php');
/**#@-*/

init_page();

if (get_user_level() == USER_LEVEL_ADMIN)
{
    $id    = ustr2int(try_request('id'));
    $state = state_find($id);

    if (!$state)
    {
        debug_write_log(DEBUG_NOTICE, 'State cannot be found.');
        header('Location: index.php');
    }
    else
    {
        if ($state['is_locked'])
        {
            if ($state['state_type'] == STATE_TYPE_INTERMEDIATE)
            {
                state_set_initial($state['template_id'], $id);
            }
            else
            {
                debug_write_log(DEBUG_NOTICE, 'State must be intermediate.');
            }
        }
        else
        {
            debug_write_log(DEBUG_NOTICE, 'Template must be locked.');
        }

        header('Location: sview.php?id=' . $id);
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    header('Location: index.php');
}

?>
