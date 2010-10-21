<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2005-2010  Artem Rodygin
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
 * Subscriptions
 *
 * This module provides API to work with user subscriptions.
 * See also {@link http://code.google.com/p/etraxis/wiki/DatabaseSchema#tbl_subscribes tbl_subscribes} database table.
 *
 * @package DBO
 * @subpackage Subscriptions
 */

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
/**#@-*/

//------------------------------------------------------------------------------
//  Definitions.
//------------------------------------------------------------------------------

/**#@+
 * Data restriction.
 */
define('MAX_SUBSCRIPTION_NAME',        25);
define('MAX_SUBSCRIPTION_CARBON_COPY', 50);
/**#@-*/

/**#@+
 * Filter type.
 */
define('SUBSCRIPTION_TYPE_ALL_PROJECTS',  1);
define('SUBSCRIPTION_TYPE_ALL_TEMPLATES', 2);
define('SUBSCRIPTION_TYPE_ONE_TEMPLATE',  3);
/**#@-*/

// Notifications data.
define('NOTIFY_CONTROL',  0);
define('NOTIFY_EVENT',    1);
define('NOTIFY_RESOURCE', 2);

$notifications = array
(
    array('notify_create',   NOTIFY_RECORD_CREATED,       RES_NOTIFY_RECORD_CREATED_ID),
    array('notify_assign',   NOTIFY_RECORD_ASSIGNED,      RES_NOTIFY_RECORD_ASSIGNED_ID),
    array('notify_modify',   NOTIFY_RECORD_MODIFIED,      RES_NOTIFY_RECORD_MODIFIED_ID),
    array('notify_state',    NOTIFY_RECORD_STATE_CHANGED, RES_NOTIFY_RECORD_STATE_CHANGED_ID),
    array('notify_postpone', NOTIFY_RECORD_POSTPONED,     RES_NOTIFY_RECORD_POSTPONED_ID),
    array('notify_resume',   NOTIFY_RECORD_RESUMED,       RES_NOTIFY_RECORD_RESUMED_ID),
    array('notify_comment',  NOTIFY_COMMENT_ADDED,        RES_NOTIFY_COMMENT_ADDED_ID),
    array('notify_attach',   NOTIFY_FILE_ATTACHED,        RES_NOTIFY_FILE_ATTACHED_ID),
    array('notify_remove',   NOTIFY_FILE_REMOVED,         RES_NOTIFY_FILE_REMOVED_ID),
    array('notify_clone',    NOTIFY_RECORD_CLONED,        RES_NOTIFY_RECORD_CLONED_ID),
    array('notify_addsub',   NOTIFY_SUBRECORD_ADDED,      RES_NOTIFY_SUBRECORD_ADDED_ID),
    array('notify_remsub',   NOTIFY_SUBRECORD_REMOVED,    RES_NOTIFY_SUBRECORD_REMOVED_ID),
);

//------------------------------------------------------------------------------
//  Functions.
//------------------------------------------------------------------------------

/**
 * Finds in database and returns the information about specified subscription.
 *
 * @param int $id Subscription ID.
 * @return array Array with data if subscription is found in database, FALSE otherwise.
 */
function subscription_find ($id)
{
    debug_write_log(DEBUG_TRACE, '[subscription_find]');
    debug_write_log(DEBUG_DUMP,  '[subscription_find] $id = ' . $id);

    $rs = dal_query('subscriptions/fndid.sql', $id, $_SESSION[VAR_USERID]);

    return ($rs->rows == 0 ? FALSE : $rs->fetch());
}

/**
 * Returns {@link CRecordset DAL recordset} which contains all existing subscriptions of specified account.
 *
 * @param int $id Account ID.
 * @param int &$sort Sort mode (used as output only). The function retrieves current sort mode from
 * client cookie ({@link COOKIE_SUBSCRIPTIONS_SORT}) and updates it, if it's out of valid range.
 * @param int &$page Number of current page tab (used as output only). The function retrieves current
 * page from client cookie ({@link COOKIE_SUBSCRIPTIONS_PAGE}) and updates it, if it's out of valid range.
 * @return CRecordset Recordset with list of subscriptions.
 */
function subscriptions_list ($id, &$sort, &$page)
{
    debug_write_log(DEBUG_TRACE, '[subscriptions_list]');
    debug_write_log(DEBUG_DUMP,  '[subscriptions_list] $id = ' . $id);

    $sort_modes = array
    (
        1 => 'subscribe_name asc',
        2 => 'carbon_copy asc, subscribe_name asc',
        3 => 'subscribe_name desc',
        4 => 'carbon_copy desc, subscribe_name desc',
    );

    $sort = try_request('sort', try_cookie(COOKIE_SUBSCRIPTIONS_SORT, 1));
    $sort = ustr2int($sort, 1, count($sort_modes));

    $page = try_request('page', try_cookie(COOKIE_SUBSCRIPTIONS_PAGE));
    $page = ustr2int($page, 1, MAXINT);

    save_cookie(COOKIE_SUBSCRIPTIONS_SORT, $sort);
    save_cookie(COOKIE_SUBSCRIPTIONS_PAGE, $page);

    return dal_query('subscriptions/list.sql', $id, $sort_modes[$sort]);
}

/**
 * Validates subscription information before creation or modification.
 *
 * @param string $subscription_name Subscription name.
 * @param string $carbon_copy Carbon copy.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - data are valid</li>
 * <li>{@link ERROR_INCOMPLETE_FORM} - at least one of required field is empty</li>
 * <li>{@link ERROR_INVALID_EMAIL} - carbon copy is not valid email address</li>
 * </ul>
 */
function subscription_validate ($subscription_name, $carbon_copy)
{
    debug_write_log(DEBUG_TRACE, '[subscription_validate]');
    debug_write_log(DEBUG_DUMP,  '[subscription_validate] $subscription_name = ' . $subscription_name);
    debug_write_log(DEBUG_DUMP,  '[subscription_validate] $carbon_copy       = ' . $carbon_copy);

    if (ustrlen($subscription_name) == 0)
    {
        debug_write_log(DEBUG_NOTICE, '[subscription_validate] At least one required field is empty.');
        return ERROR_INCOMPLETE_FORM;
    }

    if (ustrlen($carbon_copy) != 0 && !is_email($carbon_copy))
    {
        debug_write_log(DEBUG_NOTICE, '[subscription_validate] Invalid email.');
        return ERROR_INVALID_EMAIL;
    }

    return NO_ERROR;
}

/**
 * Creates new subscription.
 *
 * @param string $subscribe_name Subscription name.
 * @param string $carbon_copy Carbon copy.
 * @param int $subscribe_type Type of subscription.
 * @param int $subscribe_flags Flags of subscription.
 * @param int $subscribe_param Parameter of subscription, depends on its type.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - subscription is successfully created</li>
 * <li>{@link ERROR_ALREADY_EXISTS} - subscription with specified name already exists</li>
 * </ul>
 */
function subscription_create ($subscription_name, $carbon_copy, $subscription_type, $subscription_flags, $subscription_param = NULL)
{
    debug_write_log(DEBUG_TRACE, '[subscription_create]');
    debug_write_log(DEBUG_DUMP,  '[subscription_create] $subscription_name  = ' . $subscription_name);
    debug_write_log(DEBUG_DUMP,  '[subscription_create] $carbon_copy        = ' . $carbon_copy);
    debug_write_log(DEBUG_DUMP,  '[subscription_create] $subscription_type  = ' . $subscription_type);
    debug_write_log(DEBUG_DUMP,  '[subscription_create] $subscription_flags = ' . $subscription_flags);
    debug_write_log(DEBUG_DUMP,  '[subscription_create] $subscription_param = ' . $subscription_param);

    // Check that user doesn't have another subscription with the same name.
    $rs = dal_query('subscriptions/fndk.sql', $_SESSION[VAR_USERID], ustrtolower($subscription_name));

    if ($rs->rows != 0)
    {
        debug_write_log(DEBUG_NOTICE, '[subscription_create] Subscription already exists.');
        return ERROR_ALREADY_EXISTS;
    }

    // Create a subscription.
    dal_query('subscriptions/create.sql',
              $_SESSION[VAR_USERID],
              $subscription_name,
              ustrlen($carbon_copy) == 0 ? NULL : $carbon_copy,
              $subscription_type,
              $subscription_flags,
              is_null($subscription_param) ? NULL : $subscription_param);

    return NO_ERROR;
}

/**
 * Modifies specified subscription.
 *
 * @param int $id ID of subscription to be modified.
 * @param string $subscription_name New subscription name.
 * @param string $carbon_copy New carbon copy.
 * @param int $subscription_flags New flags of subscription.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - subscription is successfully modified</li>
 * <li>{@link ERROR_ALREADY_EXISTS} - subscription with specified name already exists</li>
 * </ul>
 */
function subscription_modify ($id, $subscription_name, $carbon_copy, $subscription_flags)
{
    debug_write_log(DEBUG_TRACE, '[subscription_modify]');
    debug_write_log(DEBUG_DUMP,  '[subscription_modify] $id                 = ' . $id);
    debug_write_log(DEBUG_DUMP,  '[subscription_modify] $subscription_name  = ' . $subscription_name);
    debug_write_log(DEBUG_DUMP,  '[subscription_modify] $carbon_copy        = ' . $carbon_copy);
    debug_write_log(DEBUG_DUMP,  '[subscription_modify] $subscription_flags = ' . $subscription_flags);

    // Check that user doesn't have another subscription with the same name, besides this one.
    $rs = dal_query('subscriptions/fndku.sql', $id, $_SESSION[VAR_USERID], ustrtolower($subscription_name));

    if ($rs->rows != 0)
    {
        debug_write_log(DEBUG_NOTICE, '[subscription_modify] Subscription already exists.');
        return ERROR_ALREADY_EXISTS;
    }

    // Modify the subscription.
    dal_query('subscriptions/modify.sql',
              $id,
              $subscription_name,
              ustrlen($carbon_copy) == 0 ? NULL : $carbon_copy,
              $subscription_flags);

    return NO_ERROR;
}

/**
 * Enables selected subscriptions.
 *
 * @param array $subscriptions List of subscriptions IDs.
 * @return int Always {@link NO_ERROR}.
 */
function subscriptions_enable ($subscriptions)
{
    debug_write_log(DEBUG_TRACE, '[subscriptions_enable]');

    foreach ($subscriptions as $subscription)
    {
        dal_query('subscriptions/set.sql', $subscription, $_SESSION[VAR_USERID]);
    }

    return NO_ERROR;
}

/**
 * Disables selected subscriptions.
 *
 * @param array $subscriptions List of subscriptions IDs.
 * @return int Always {@link NO_ERROR}.
 */
function subscriptions_disable ($subscriptions)
{
    debug_write_log(DEBUG_TRACE, '[subscriptions_disable]');

    foreach ($subscriptions as $subscription)
    {
        dal_query('subscriptions/clear.sql', $subscription, $_SESSION[VAR_USERID]);
    }

    return NO_ERROR;
}

/**
 * Deletes selected subscriptions.
 *
 * @param array $subscriptions List of subscriptions IDs.
 * @return int Always {@link NO_ERROR}.
 */
function subscriptions_delete ($subscriptions)
{
    debug_write_log(DEBUG_TRACE, '[subscriptions_delete]');

    foreach ($subscriptions as $subscription)
    {
        dal_query('subscriptions/delete.sql', $subscription);
    }

    return NO_ERROR;
}

?>
