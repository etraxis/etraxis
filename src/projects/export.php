<?php

/**
 * @package eTraxis
 * @ignore
 */

//--------------------------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system.
//  Copyright (C) 2008-2009 by Artem Rodygin
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
//  Artem Rodygin           2008-02-01      new-601: [SF1814666] Export and Import Templates
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/templates.php');
/**#@-*/

init_page();

$id = ustr2int(try_request('id'));

if (get_user_level() == USER_LEVEL_ADMIN)
{
    $xml = template_export($id);

    if (is_null($xml))
    {
        debug_write_log(DEBUG_NOTICE, 'Template cannot be found.');
        header('Location: index.php');
    }
    else
    {
        header('Pragma: private');
        header('Cache-Control: private, must-revalidate');
        header('Content-type: text/xml');
        header('Content-Disposition: attachment; filename=template-' . str_pad($id, 3, '0', STR_PAD_LEFT) . '.xml');

        echo($xml);
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    header('Location: tview.php?id=' . $id);
}

?>
