<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2006-2010  Artem Rodygin
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
 * Views
 *
 * This module provides API to work with user views.
 * See also {@link http://code.google.com/p/etraxis/wiki/DatabaseSchema#tbl_views tbl_views} database table.
 *
 * @package DBO
 * @subpackage Views
 */

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/accounts.php');
require_once('../dbo/filters.php');
/**#@-*/

//------------------------------------------------------------------------------
//  Definitions.
//------------------------------------------------------------------------------

/**#@+
 * Data restriction.
 */
define('MAX_VIEW_NAME', 50);
define('MAX_VIEW_SIZE', 20);
/**#@-*/

/**#@+
 * Standard column type.
 */
define('COLUMN_TYPE_MINIMUM',       1);
define('COLUMN_TYPE_ID',            1);
define('COLUMN_TYPE_STATE_ABBR',    2);
define('COLUMN_TYPE_PROJECT',       3);
define('COLUMN_TYPE_SUBJECT',       4);
define('COLUMN_TYPE_AUTHOR',        5);
define('COLUMN_TYPE_RESPONSIBLE',   6);
define('COLUMN_TYPE_LAST_EVENT',    7);
define('COLUMN_TYPE_AGE',           8);
define('COLUMN_TYPE_CREATION_DATE', 9);
define('COLUMN_TYPE_TEMPLATE',     10);
define('COLUMN_TYPE_STATE_NAME',   11);
define('COLUMN_TYPE_LAST_STATE',   12);
define('COLUMN_TYPE_MAXIMUM',      12);
/**#@-*/

/**#@+
 * Custom column type.
 */
define('COLUMN_TYPE_NUMBER',      100);
define('COLUMN_TYPE_STRING',      101);
define('COLUMN_TYPE_MULTILINED',  102);
define('COLUMN_TYPE_CHECKBOX',    103);
define('COLUMN_TYPE_LIST_NUMBER', 104);
define('COLUMN_TYPE_LIST_STRING', 105);
define('COLUMN_TYPE_RECORD',      106);
define('COLUMN_TYPE_DATE',        107);
define('COLUMN_TYPE_DURATION',    108);
/**#@-*/

// Column type resources.
$column_type_res = array
(
    // standard
    COLUMN_TYPE_ID            => RES_ID_ID,
    COLUMN_TYPE_STATE_ABBR    => RES_STATE_ID,
    COLUMN_TYPE_PROJECT       => RES_PROJECT_ID,
    COLUMN_TYPE_SUBJECT       => RES_SUBJECT_ID,
    COLUMN_TYPE_AUTHOR        => RES_AUTHOR_ID,
    COLUMN_TYPE_RESPONSIBLE   => RES_RESPONSIBLE_ID,
    COLUMN_TYPE_LAST_EVENT    => RES_LAST_EVENT_ID,
    COLUMN_TYPE_AGE           => RES_AGE_ID,
    COLUMN_TYPE_CREATION_DATE => RES_CREATED_ID,
    COLUMN_TYPE_TEMPLATE      => RES_TEMPLATE_ID,
    COLUMN_TYPE_STATE_NAME    => RES_STATE_NAME_ID,
    COLUMN_TYPE_LAST_STATE    => RES_LAST_STATE_ID,

    // custom
    COLUMN_TYPE_NUMBER        => RES_NUMBER_ID,
    COLUMN_TYPE_STRING        => RES_STRING_ID,
    COLUMN_TYPE_MULTILINED    => RES_MULTILINED_TEXT_ID,
    COLUMN_TYPE_CHECKBOX      => RES_CHECKBOX_ID,
    COLUMN_TYPE_LIST_NUMBER   => RES_LIST_INDEXES_ID,
    COLUMN_TYPE_LIST_STRING   => RES_LIST_VALUES_ID,
    COLUMN_TYPE_RECORD        => RES_RECORD_ID,
    COLUMN_TYPE_DATE          => RES_DATE_ID,
    COLUMN_TYPE_DURATION      => RES_DURATION_ID,
);

//------------------------------------------------------------------------------
//  Functions.
//------------------------------------------------------------------------------

/**
 * Finds in database and returns the information about specified view.
 *
 * @param int $id View ID.
 * @return array Array with data if view is found in database, FALSE otherwise.
 */
function view_find ($id)
{
    debug_write_log(DEBUG_TRACE, '[view_find]');
    debug_write_log(DEBUG_DUMP,  '[view_find] $id = ' . $id);

    $rs = dal_query('views/fndid.sql', $id, $_SESSION[VAR_USERID]);

    return ($rs->rows == 0 ? FALSE : $rs->fetch());
}

/**
 * Returns {@link CRecordset DAL recordset} which contains all existing views of logged in user.
 *
 * @param int &$sort Sort mode (used as output only). The function retrieves current sort mode from
 * client cookie ({@link COOKIE_VIEWS_SORT}) and updates it, if it's out of valid range.
 * @param int &$page Number of current page tab (used as output only). The function retrieves current
 * page from client cookie ({@link COOKIE_VIEWS_PAGE}) and updates it, if it's out of valid range.
 * @return CRecordset Recordset with list of views.
 */
function views_list (&$sort, &$page)
{
    debug_write_log(DEBUG_TRACE, '[views_list]');

    $sort_modes = array
    (
        1 => 'view_name asc',
        2 => 'view_name desc',
    );

    $sort = try_request('sort', try_cookie(COOKIE_VIEWS_SORT, 1));
    $sort = ustr2int($sort, 1, count($sort_modes));

    $page = try_request('page', try_cookie(COOKIE_VIEWS_PAGE));
    $page = ustr2int($page, 1, MAXINT);

    save_cookie(COOKIE_VIEWS_SORT, $sort);
    save_cookie(COOKIE_VIEWS_PAGE, $page);

    return dal_query('views/list.sql', $_SESSION[VAR_USERID], $sort_modes[$sort]);
}

/**
 * Validates view information before creation or modification.
 *
 * @param string $view_name View name.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - data are valid</li>
 * <li>{@link ERROR_INCOMPLETE_FORM} - at least one of required field is empty</li>
 * </ul>
 */
function view_validate ($view_name)
{
    debug_write_log(DEBUG_TRACE, '[view_validate]');
    debug_write_log(DEBUG_DUMP,  '[view_validate] $view_name = ' . $view_name);

    if (ustrlen($view_name) == 0)
    {
        debug_write_log(DEBUG_NOTICE, '[view_validate] At least one required field is empty.');
        return ERROR_INCOMPLETE_FORM;
    }

    return NO_ERROR;
}

/**
 * Creates new view for specified user.
 *
 * @param int $account_id User ID.
 * @param string $view_name View name.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - view is successfully created</li>
 * <li>{@link ERROR_ALREADY_EXISTS} - view with specified name already exists</li>
 * </ul>
 */
function view_create ($account_id, $view_name)
{
    debug_write_log(DEBUG_TRACE, '[view_create]');
    debug_write_log(DEBUG_DUMP,  '[view_create] $account_id = ' . $account_id);
    debug_write_log(DEBUG_DUMP,  '[view_create] $view_name  = ' . $view_name);

    // Check that user doesn't have another view with the same name.
    $rs = dal_query('views/fndk.sql', $account_id, ustrtolower($view_name));

    if ($rs->rows != 0)
    {
        debug_write_log(DEBUG_NOTICE, '[view_create] View already exists.');
        return ERROR_ALREADY_EXISTS;
    }

    // Create a view.
    dal_query('views/create.sql',
              $account_id,
              $view_name);

    return NO_ERROR;
}

/**
 * Modifies specified view.
 *
 * @param int $id ID of view to be modified.
 * @param string $view_name New view name.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - view is successfully modified</li>
 * <li>{@link ERROR_ALREADY_EXISTS} - view with specified name already exists</li>
 * </ul>
 */
function view_modify ($id, $view_name)
{
    debug_write_log(DEBUG_TRACE, '[view_modify]');
    debug_write_log(DEBUG_DUMP,  '[view_modify] $id        = ' . $id);
    debug_write_log(DEBUG_DUMP,  '[view_modify] $view_name = ' . $view_name);

    // Check that user doesn't have another view with the same name, besides this one.
    $rs = dal_query('views/fndku.sql', $id, $_SESSION[VAR_USERID], ustrtolower($view_name));

    if ($rs->rows != 0)
    {
        debug_write_log(DEBUG_NOTICE, '[view_modify] View already exists.');
        return ERROR_ALREADY_EXISTS;
    }

    // Modify the view.
    dal_query('views/modify.sql',
              $id,
              $view_name);

    return NO_ERROR;
}

/**
 * Deletes specified views.
 *
 * @param array $views List of views IDs.
 * @return int Always {@link NO_ERROR}.
 */
function views_delete ($views)
{
    debug_write_log(DEBUG_TRACE, '[views_delete]');

    // If current view is in list of views to be deleted, change it to unknown.
    if (in_array($_SESSION[VAR_VIEW], $views))
    {
        filters_clear();
        account_set_view($_SESSION[VAR_USERID]);
    }

    // Delete each of specified views.
    foreach ($views as $view)
    {
        dal_query('views/clrview.sql', $view);
        dal_query('views/fdelall.sql', $view);
        dal_query('views/cdelall.sql', $view);
        dal_query('views/delete.sql',  $view);
    }

    return NO_ERROR;
}

/**
 * Returns array which contains IDs of all filters of specified set view.
 *
 * @param int $id ID of the view.
 * @return array List of columns of the view.
 */
function view_filters_list ($id)
{
    debug_write_log(DEBUG_TRACE, '[view_filters_list]');
    debug_write_log(DEBUG_DUMP,  '[view_filters_list] id = ' . $id);

    $list = array();

    $rs = dal_query('views/flist.sql', $id);

    while (($row = $rs->fetch()))
    {
        array_push($list, $row['filter_id']);
    }

    return $list;
}

/**
 * Finds in database and returns the information about specified column.
 *
 * @param int $id Column ID.
 * @return array Array with data if column is found in database, FALSE otherwise.
 */
function column_find ($id)
{
    debug_write_log(DEBUG_TRACE, '[column_find]');
    debug_write_log(DEBUG_DUMP,  '[column_find] $id = ' . $id);

    $rs = dal_query('columns/fndid.sql', $id, $_SESSION[VAR_USERID]);

    return ($rs->rows == 0 ? FALSE : $rs->fetch());
}

/**
 * Returns array which contains all columns of specified set view.
 *
 * @param int $id ID of the view (skip to get currently set view).
 * @return array List of columns of the view. Each item is an array with following keys:
 * <ul>
 * <li>column_id,</li>
 * <li>state_name,</li>
 * <li>field_name,</li>
 * <li>column_type,</li>
 * <li>column_order.</li>
 * </ul>
 */
function columns_list ($id = NULL)
{
    debug_write_log(DEBUG_TRACE, '[columns_list]');
    debug_write_log(DEBUG_DUMP,  '[columns_list] id = ' . $id);

    if ($id == NULL)
    {
        $id = $_SESSION[VAR_VIEW];
    }

    $columns = array();

    // Find all columns of currently set view.
    if (get_user_level() == USER_LEVEL_GUEST || is_null($id))
    {
        for ($i = COLUMN_TYPE_MINIMUM; $i <= COLUMN_TYPE_AGE; $i++)
        {
            array_push($columns, array('column_id'    => $i,
                                       'state_name'   => NULL,
                                       'field_name'   => NULL,
                                       'column_type'  => $i,
                                       'column_order' => $i));
        }
    }
    else
    {
        $rs = dal_query('columns/list.sql', $id);

        // Push all returned data from recordset to resulted array.
        while (($row = $rs->fetch()) && (count($columns) < MAX_VIEW_SIZE))
        {
            array_push($columns, array('column_id'    => $row['column_id'],
                                       'state_name'   => $row['state_name'],
                                       'field_name'   => $row['field_name'],
                                       'column_type'  => $row['column_type'],
                                       'column_order' => $row['column_order']));
        }
    }

    return $columns;
}

/**
 * Returns number of columns in the specified view.
 *
 * @param int $id ID of the view.
 * @return int Current number of columns.
 */
function columns_count ($id)
{
    debug_write_log(DEBUG_TRACE, '[columns_count]');
    debug_write_log(DEBUG_DUMP,  '[columns_cound] id = ' . $id);

    $rs = dal_query('columns/count.sql', $id);

    return $rs->fetch(0);
}

/**
 * Adds specified columns to the specified view.
 *
 * @param int $id ID of the view.
 * @param array $columns List of columns. Each item is a string, concatenated of following data, separated by colon:
 * <ul>
 * <li>column_type,</li>
 * <li>state_name,</li>
 * <li>field_name.</li>
 * </ul>
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - columns are successfully added</li>
 * <li>{@link ERROR_INTEGER_VALUE_OUT_OF_RANGE} - maximum allowed number of columns is reached</li>
 * </ul>
 */
function columns_add ($id, $columns)
{
    debug_write_log(DEBUG_TRACE, '[columns_add]');
    debug_write_log(DEBUG_DUMP,  '[columns_add] id = ' . $id);

    // Get current number of columns in the view.
    $count = columns_count($id);

    // Add each if specified columns.
    foreach ($columns as $column)
    {
        // Stop, if view already has maximum allowed number of columns.
        if ($count == MAX_VIEW_SIZE)
        {
            return ERROR_INTEGER_VALUE_OUT_OF_RANGE;
        }

        // Split string with column information into pieces.
        list($type, $state, $field) = ustr_getcsv($column, ':', '\'');

        // Add new column to the view.
        dal_query('columns/create.sql', $id, $state, $field, $type, ++$count);
    }

    return NO_ERROR;
}

/**
 * Removes specified columns from the specified view.
 *
 * @param int $id ID of the view.
 * @param array $columns List of columns IDs.
 * @return int Always {@link NO_ERROR}.
 */
function columns_remove ($id, $columns)
{
    debug_write_log(DEBUG_TRACE, '[columns_remove]');
    debug_write_log(DEBUG_DUMP,  '[columns_remove] id = ' . $id);

    // Delete each of specified columns.
    foreach ($columns as $column)
    {
        dal_query('columns/delete.sql', $column);
    }

    // Enumerate the rest of columns of currently set view.
    $rs = dal_query('columns/list.sql', $id);

    // Reorder the rest of columns of currently set view.
    for ($i = 0; ($row = $rs->fetch()) && ($i < MAX_VIEW_SIZE); $i++)
    {
        dal_query('columns/setorder.sql', $id, $row['column_order'], $i + 1);
    }

    // View cannot contain no columns ...
    if (columns_count($id) == 0)
    {
        // ... add default one if all previous were removed.
        dal_query('columns/create.sql', $id, NULL, NULL, COLUMN_TYPE_ID, 1);
    }

    return NO_ERROR;
}

?>
