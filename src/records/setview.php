<?php

/**
 * @package eTraxis
 * @ignore
 */

//--------------------------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system.
//  Copyright (C) 2006-2009 by Artem Rodygin
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
//  Artem Rodygin           2006-11-20      new-377: Custom views.
//  Artem Rodygin           2007-10-29      new-564: Filters set.
//  Artem Rodygin           2007-11-07      new-571: View should show all records of current filters set.
//  Artem Rodygin           2007-11-30      new-617: Add 'no view' and 'no filter set' to related comboboxes.
//  Artem Rodygin           2008-03-15      new-683: Filters should be sharable with groups, not with accounts.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/accounts.php');
require_once('../dbo/filters.php');
require_once('../dbo/views.php');
/**#@-*/

init_page();

$id = ustr2int(try_request('fset'), -1);

if ($id == 0)
{
    debug_write_log(DEBUG_NOTICE, 'Disable filters.');
    dal_query('filters/clearall.sql', $_SESSION[VAR_USERID]);
    account_set_fset($_SESSION[VAR_USERID]);
}
elseif (fset_find($id))
{
    debug_write_log(DEBUG_NOTICE, 'Filters set is being set.');
    account_set_fset($_SESSION[VAR_USERID], $id);
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Filters set cannot be found.');
    account_set_fset($_SESSION[VAR_USERID]);
}

$id = ustr2int(try_request('view'), -1);

if ($id == 0)
{
    debug_write_log(DEBUG_NOTICE, 'Reset view to standard.');
    dal_query('columns/cdelall.sql', $_SESSION[VAR_USERID]);
    account_set_view($_SESSION[VAR_USERID]);
}
elseif (view_find($id))
{
    debug_write_log(DEBUG_NOTICE, 'View is being set.');
    account_set_view($_SESSION[VAR_USERID], $id);
}
else
{
    debug_write_log(DEBUG_NOTICE, 'View cannot be found.');
    account_set_view($_SESSION[VAR_USERID]);
}

header('Location: index.php');

?>
