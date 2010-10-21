<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2006-2009  Artem Rodygin
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
require_once('../dbo/records.php');
/**#@-*/

init_page();

// check that requested record exists

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

// subscribe/unsubscribe

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
