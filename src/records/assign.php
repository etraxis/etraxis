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
require_once('../dbo/accounts.php');
require_once('../dbo/events.php');
require_once('../dbo/records.php');
/**#@-*/

init_page();

// check that requested record exists

$id     = ustr2int(try_request('id'));
$record = record_find($id);

if (!$record)
{
    debug_write_log(DEBUG_NOTICE, 'Record cannot be found.');
    header('Location: index.php');
    exit;
}

// check that requested assignee exists

$id      = ustr2int(try_request('responsible'));
$account = account_find($id);

if (!$account)
{
    debug_write_log(DEBUG_NOTICE, 'Account cannot be found.');
}
else
{
    // get current user's permissions and verify them

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

?>
