<?php

/**
 * Subscriptions
 *
 * This module provides API to work with user subscriptions.
 * See also {@link http://www.etraxis.org/docs-schema.php#tbl_subscribes tbl_subscribes} database table.
 *
 * @package DBO
 * @subpackage Subscriptions
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
//  Artem Rodygin           2005-09-17      new-125: Email notifications advanced filter.
//  Artem Rodygin           2005-09-27      new-141: Source code review.
//  Artem Rodygin           2005-10-08      bug-154: PHP Warning: odbc_exec(): SQL error: Incorrect syntax near the keyword 'and'.
//  Artem Rodygin           2005-10-22      new-150: User should have ability to modify his subscriptions.
//  Artem Rodygin           2005-10-22      bug-163: Some filters are malfunctional.
//  Artem Rodygin           2006-04-21      bug-245: Unexpected message "Subscription with entered name already exists".
//  Artem Rodygin           2006-11-15      new-374: Carbon copies in subscriptions.
//  Artem Rodygin           2006-12-14      bug-444: User is able to edit subscriptions of other users.
//  Artem Rodygin           2006-12-30      new-475: Turning subscriptions on and off is not clear.
//  Artem Rodygin           2007-11-27      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//  Artem Rodygin           2009-06-17      bug-825: Database gets empty strings instead of NULL values.
//  Artem Rodygin           2009-09-09      new-826: Native unicode support for Microsoft SQL Server.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
/**#@-*/

//--------------------------------------------------------------------------------------------------
//  Definitions.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Data restriction.
 */
define('MAX_SUBSCRIBE_NAME',        25);
define('MAX_SUBSCRIBE_CARBON_COPY', 50);
/**#@-*/

/**#@+
 * Filter type.
 */
define('SUBSCRIBE_TYPE_ALL_PROJECTS',  1);
define('SUBSCRIBE_TYPE_ALL_TEMPLATES', 2);
define('SUBSCRIBE_TYPE_ONE_TEMPLATE',  3);
/**#@-*/

//--------------------------------------------------------------------------------------------------
//  Functions.
//--------------------------------------------------------------------------------------------------

/**
 * Finds in database and returns the information about specified subscription.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_subscribes_subscribe_id Subscription ID}.
 * @return array Array with data if subscription is found in database, FALSE otherwise.
 */
function subscribe_find ($id)
{
    debug_write_log(DEBUG_TRACE, '[subscribe_find]');
    debug_write_log(DEBUG_DUMP,  '[subscribe_find] $id = ' . $id);

    $rs = dal_query('subscribes/fndid.sql', $id, $_SESSION[VAR_USERID]);

    return ($rs->rows == 0 ? FALSE : $rs->fetch());
}

/**
 * Returns {@link CRecordset DAL recordset} which contains all existing subscriptions of specified account.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_account_id Account ID}.
 * @return CRecordset Recordset with list of subscriptions.
 */
function subscribes_list ($id)
{
    debug_write_log(DEBUG_TRACE, '[subscribes_list]');
    debug_write_log(DEBUG_DUMP,  '[subscribes_list] $id = ' . $id);

    return dal_query('subscribes/list.sql', $id);
}

/**
 * Validates subscription information before creation or modification.
 *
 * @param string $subscribe_name {@link http://www.etraxis.org/docs-schema.php#tbl_subscriptions_subscribe_name Subscription name}.
 * @param string $carbon_copy {@link http://www.etraxis.org/docs-schema.php#tbl_subscriptions_carbon_copy Carbon copy}.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - data are valid</li>
 * <li>{@link ERROR_INCOMPLETE_FORM} - at least one of required field is empty</li>
 * <li>{@link ERROR_INVALID_EMAIL} - carbon copy is not valid email address</li>
 * </ul>
 */
function subscribe_validate ($subscribe_name, $carbon_copy)
{
    debug_write_log(DEBUG_TRACE, '[subscribe_validate]');
    debug_write_log(DEBUG_DUMP,  '[subscribe_validate] $subscribe_name = ' . $subscribe_name);
    debug_write_log(DEBUG_DUMP,  '[subscribe_validate] $carbon_copy    = ' . $carbon_copy);

    if (ustrlen($subscribe_name) == 0)
    {
        debug_write_log(DEBUG_NOTICE, '[subscribe_validate] At least one required field is empty.');
        return ERROR_INCOMPLETE_FORM;
    }

    if (ustrlen($carbon_copy) != 0 && !is_email($carbon_copy))
    {
        debug_write_log(DEBUG_NOTICE, '[subscribe_validate] Invalid email.');
        return ERROR_INVALID_EMAIL;
    }

    return NO_ERROR;
}

/**
 * Creates new subscription.
 *
 * @param string $subscribe_name {@link http://www.etraxis.org/docs-schema.php#tbl_subscribes_subscribe_name Subscription name}.
 * @param string $carbon_copy {@link http://www.etraxis.org/docs-schema.php#tbl_subscribes_carbon_copy Carbon copy}.
 * @param int $subscribe_type {@link http://www.etraxis.org/docs-schema.php#tbl_subscribes_subscribe_type Type of subscription}.
 * @param int $subscribe_flags {@link http://www.etraxis.org/docs-schema.php#tbl_subscribes_subscribe_flags Flags of subscription}.
 * @param int $subscribe_param {@link http://www.etraxis.org/docs-schema.php#tbl_subscribes_subscribe_param Parameter of subscription}), depends on its type.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - subscription is successfully created</li>
 * <li>{@link ERROR_ALREADY_EXISTS} - subscription with specified {@link http://www.etraxis.org/docs-schema.php#tbl_subscribes_subscribe_name name} already exists</li>
 * </ul>
 */
function subscribe_create ($subscribe_name, $carbon_copy, $subscribe_type, $subscribe_flags, $subscribe_param = NULL)
{
    debug_write_log(DEBUG_TRACE, '[subscribe_create]');
    debug_write_log(DEBUG_DUMP,  '[subscribe_create] $subscribe_name  = ' . $subscribe_name);
    debug_write_log(DEBUG_DUMP,  '[subscribe_create] $carbon_copy     = ' . $carbon_copy);
    debug_write_log(DEBUG_DUMP,  '[subscribe_create] $subscribe_type  = ' . $subscribe_type);
    debug_write_log(DEBUG_DUMP,  '[subscribe_create] $subscribe_flags = ' . $subscribe_flags);
    debug_write_log(DEBUG_DUMP,  '[subscribe_create] $subscribe_param = ' . $subscribe_param);

    // Check that user doesn't have another subscription with the same name.
    $rs = dal_query('subscribes/fndk.sql', $_SESSION[VAR_USERID], ustrtolower($subscribe_name));

    if ($rs->rows != 0)
    {
        debug_write_log(DEBUG_NOTICE, '[subscribe_create] Subscription already exists.');
        return ERROR_ALREADY_EXISTS;
    }

    // Create a subscription.
    dal_query('subscribes/create.sql',
              $_SESSION[VAR_USERID],
              $subscribe_name,
              ustrlen($carbon_copy) == 0 ? NULL : $carbon_copy,
              $subscribe_type,
              $subscribe_flags,
              is_null($subscribe_param) ? NULL : $subscribe_param);

    return NO_ERROR;
}

/**
 * Modifies specified subscription.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_subscribes_subscribe_id ID} of subscription to be modified.
 * @param string $subscribe_name New {@link http://www.etraxis.org/docs-schema.php#tbl_subscribes_subscribe_name subscription name}.
 * @param string $carbon_copy New {@link http://www.etraxis.org/docs-schema.php#tbl_subscribes_carbon_copy carbon copy}.
 * @param int $subscribe_flags New {@link http://www.etraxis.org/docs-schema.php#tbl_subscribes_subscribe_flags flags of subscription}.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - subscription is successfully modified</li>
 * <li>{@link ERROR_ALREADY_EXISTS} - subscription with specified {@link http://www.etraxis.org/docs-schema.php#tbl_subscribes_subscribe_name name} already exists</li>
 * </ul>
 */
function subscribe_modify ($id, $subscribe_name, $carbon_copy, $subscribe_flags)
{
    debug_write_log(DEBUG_TRACE, '[subscribe_modify]');
    debug_write_log(DEBUG_DUMP,  '[subscribe_modify] $id              = ' . $id);
    debug_write_log(DEBUG_DUMP,  '[subscribe_modify] $subscribe_name  = ' . $subscribe_name);
    debug_write_log(DEBUG_DUMP,  '[subscribe_modify] $carbon_copy     = ' . $carbon_copy);
    debug_write_log(DEBUG_DUMP,  '[subscribe_modify] $subscribe_flags = ' . $subscribe_flags);

    // Check that user doesn't have another subscription with the same name, besides this one.
    $rs = dal_query('subscribes/fndku.sql', $id, $_SESSION[VAR_USERID], ustrtolower($subscribe_name));

    if ($rs->rows != 0)
    {
        debug_write_log(DEBUG_NOTICE, '[subscribe_modify] Subscription already exists.');
        return ERROR_ALREADY_EXISTS;
    }

    // Modify the subscription.
    dal_query('subscribes/modify.sql',
              $id,
              $subscribe_name,
              ustrlen($carbon_copy) == 0 ? NULL : $carbon_copy,
              $subscribe_flags);

    return NO_ERROR;
}

/**
 * Enables selected subscriptions.
 *
 * @param array $subscribes List of subscriptions IDs.
 * @return int Always {@link NO_ERROR}.
 */
function subscribes_set ($subscribes)
{
    debug_write_log(DEBUG_TRACE, '[subscribes_set]');

    foreach ($subscribes as $subscribe)
    {
        dal_query('subscribes/set.sql', $subscribe, $_SESSION[VAR_USERID]);
    }

    return NO_ERROR;
}

/**
 * Disables selected subscriptions.
 *
 * @param array $subscribes List of subscriptions IDs.
 * @return int Always {@link NO_ERROR}.
 */
function subscribes_clear ($subscribes)
{
    debug_write_log(DEBUG_TRACE, '[subscribes_clear]');

    foreach ($subscribes as $subscribe)
    {
        dal_query('subscribes/clear.sql', $subscribe, $_SESSION[VAR_USERID]);
    }

    return NO_ERROR;
}

/**
 * Deletes selected subscriptions.
 *
 * @param array $subscribes List of subscriptions IDs.
 * @return int Always {@link NO_ERROR}.
 */
function subscribes_delete ($subscribes)
{
    debug_write_log(DEBUG_TRACE, '[subscribes_delete]');

    foreach ($subscribes as $subscribe)
    {
        dal_query('subscribes/delete.sql', $subscribe);
    }

    return NO_ERROR;
}

?>
