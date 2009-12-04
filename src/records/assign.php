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
//  Artem Rodygin           2005-04-10      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-07-04      new-002: Email notifications.
//  Artem Rodygin           2005-08-23      new-053: All the calls of DAL API functions should be moved to DBO API.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2006-01-24      new-203: Email notification functionality (new-002) should be conditionally "compiled".
//  Artem Rodygin           2006-09-26      new-318: Group permissions should be template-wide.
//  Artem Rodygin           2008-01-11      bug-663: Author permissions are ignored.
//  Artem Rodygin           2008-04-20      new-703: Separated permissions set for current responsible.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/accounts.php');
require_once('../dbo/events.php');
require_once('../dbo/records.php');
/**#@-*/

init_page();

$id     = ustr2int(try_request('id'));
$record = record_find($id);

if (!$record)
{
    debug_write_log(DEBUG_NOTICE, 'Record cannot be found.');
    header('Location: index.php');
    exit;
}

$id      = ustr2int(try_request('responsible'));
$account = account_find($id);

if (!$account)
{
    debug_write_log(DEBUG_NOTICE, 'Account cannot be found.');
}
else
{
    $permissions = record_get_permissions($record['template_id'], $record['creator_id'], $record['responsible_id']);

    if (can_record_be_reassigned($record, $permissions))
    {
        record_assign($record['record_id'], $id);
        $event = event_create($record['record_id'], EVENT_RECORD_ASSIGNED, time(), $id);
        event_mail($event);
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, 'Record cannot be reassigned.');
    }
}

header('Location: view.php?id=' . $record['record_id']);

?>
