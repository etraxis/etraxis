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
//  Artem Rodygin           2006-06-19      new-236: Single record subscription.
//  Artem Rodygin           2006-11-12      bug-380: Single record subscription functionality (new-236) should be conditionally "compiled".
//  Artem Rodygin           2006-11-13      new-368: User should be able to subscribe other persons.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/accounts.php');
require_once('../dbo/records.php');
/**#@-*/

init_page();

$id = ustr2int(try_request('id'));

if (!EMAIL_NOTIFICATIONS_ENABLED)
{
    debug_write_log(DEBUG_NOTICE, 'Email Notifications functionality is disabled.');
    header('Location: view.php?id=' . $id);
    exit;
}

$record = record_find($id);

if (!$record)
{
    debug_write_log(DEBUG_NOTICE, 'Record cannot be found.');
    header('Location: index.php');
    exit;
}

if (is_record_subscribed($record['record_id'], $_SESSION[VAR_USERID]))
{
    record_unsubscribe($record['record_id'], $_SESSION[VAR_USERID], $_SESSION[VAR_USERID]);
}
else
{
    record_subscribe($record['record_id'], $_SESSION[VAR_USERID], $_SESSION[VAR_USERID]);
}

header('Location: view.php?id=' . $record['record_id']);

?>
