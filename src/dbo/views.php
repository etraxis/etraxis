<?php

/**
 * Views
 *
 * This module provides API to work with user views.
 * See also {@link http://www.etraxis.org/docs-schema.php#tbl_views tbl_views} database table.
 *
 * @package DBO
 * @subpackage Views
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
//  Artem Rodygin           2006-12-14      bug-444: User is able to edit subscriptions of other users.
//  Artem Rodygin           2007-02-03      new-496: [SF1650934] to show value of "list" instead of index in "records" list
//  Artem Rodygin           2007-03-04      bug-502: Can't delete a view when it's set.
//  Artem Rodygin           2007-09-12      new-579: Rework "state abbreviation" into "state short name".
//  Artem Rodygin           2007-11-05      new-571: View should show all records of current filters set.
//  Artem Rodygin           2007-11-08      bug-613: PHP Warning: odbc_exec(): SQL error: Incorrect syntax near ','.
//  Artem Rodygin           2007-11-11      bug-624: dbx_error(): Too many tables; MySQL can only use 61 tables in a join
//  Artem Rodygin           2007-11-13      new-599: Separated "Age" in custom views.
//  Artem Rodygin           2007-11-13      new-618: Extend view and filter set names up to 50 characters.
//  Artem Rodygin           2007-11-15      bug-627: PHP Warning: odbc_exec(): SQL error: Incorrect syntax near 'limit'.
//  Artem Rodygin           2007-11-27      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2007-11-29      bug-635: Variable $i was used before it was defined
//  Artem Rodygin           2007-11-30      new-617: Add 'no view' and 'no filter set' to related comboboxes.
//  Artem Rodygin           2007-12-03      bug-638: PHP Warning: odbc_exec(): SQL error: Violation of UNIQUE KEY constraint 'ix_def_columns_name'.
//  Artem Rodygin           2008-04-30      bug-699: Views // Names of custom columns are duplicated in the list of available columns, when there are two fields of different types with the same name.
//  Artem Rodygin           2008-05-01      new-715: Show creation time in the list of records.
//  Artem Rodygin           2008-11-06      new-758: View should be overwritten if it already exists.
//  Artem Rodygin           2008-11-14      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2008-11-17      bug-761: Unable to delete current view.
//  Artem Rodygin           2008-11-18      bug-763: PHP Warning: odbc_exec(): SQL error: INSERT statement conflicted with COLUMN FOREIGN KEY constraint 'fk_def_columns_account_id'.
//  Artem Rodygin           2009-03-05      bug-789: Custom fields show empty values in a view (PostgreSQL).
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//  Artem Rodygin           2009-06-17      bug-825: Database gets empty strings instead of NULL values.
//  Artem Rodygin           2009-09-09      new-826: Native unicode support for Microsoft SQL Server.
//  Artem Rodygin           2009-10-01      new-845: Template name as standard column type.
//  Artem Rodygin           2009-10-25      new-851: State name as standard column type.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/accounts.php');
/**#@-*/

//--------------------------------------------------------------------------------------------------
//  Definitions.
//--------------------------------------------------------------------------------------------------

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
define('COLUMN_TYPE_MAXIMUM',      11);
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

//--------------------------------------------------------------------------------------------------
//  Functions.
//--------------------------------------------------------------------------------------------------

/**
 * Finds in database and returns the information about specified view.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_views_view_id View ID}.
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
 * Returns {@link CRecordset DAL recordset} which contains all existing views of current user.
 *
 * @return CRecordset Recordset with list of views.
 */
function view_list ()
{
    debug_write_log(DEBUG_TRACE, '[view_list]');

    return dal_query('views/list.sql', $_SESSION[VAR_USERID]);
}

/**
 * Validates view information before creation or modification.
 *
 * @param string $view_name {@link http://www.etraxis.org/docs-schema.php#tbl_views_view_name View name}.
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
 * Creates new view.
 *
 * @param string $view_name {@link http://www.etraxis.org/docs-schema.php#tbl_views_view_name View name}.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - view is successfully created</li>
 * <li>{@link ERROR_NOT_FOUND} - failure on attempt to create view</li>
 * </ul>
 */
function view_create ($view_name)
{
    debug_write_log(DEBUG_TRACE, '[view_create]');
    debug_write_log(DEBUG_DUMP,  '[view_create] $view_name = ' . $view_name);

    // Check that user doesn't have another view with the same name.
    $rs = dal_query('views/fndk.sql', $_SESSION[VAR_USERID], ustrtolower($view_name));

    if ($rs->rows != 0)
    {
        debug_write_log(DEBUG_NOTICE, '[view_create] View already exists.');

        $view_id = $rs->fetch('view_id');

        dal_query('views/clrview.sql', $view_id);
        dal_query('views/cdelall.sql', $view_id);
        dal_query('views/delete.sql',  $view_id);
    }

    // Create a view.
    dal_query('views/create.sql',
              $_SESSION[VAR_USERID],
              $view_name);

    // Find newly created view.
    $rs = dal_query('views/fndk.sql', $_SESSION[VAR_USERID], ustrtolower($view_name));

    if ($rs->rows == 0)
    {
        debug_write_log(DEBUG_NOTICE, '[view_create] Created view not found.');
        return ERROR_NOT_FOUND;
    }

    // Get an ID of the created view.
    $view_id = $rs->fetch('view_id');

    // Copy all columns of the currently set view to the new one, and set new view as current.
    dal_query('views/ccreate.sql', $_SESSION[VAR_USERID], $view_id);
    account_set_view($_SESSION[VAR_USERID], $view_id);

    return NO_ERROR;
}

/**
 * Modifies specified view.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_views_view_id ID} of view to be modified.
 * @param string $view_name New {@link http://www.etraxis.org/docs-schema.php#tbl_views_view_name view name}.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - view is successfully modified</li>
 * <li>{@link ERROR_ALREADY_EXISTS} - view with specified {@link http://www.etraxis.org/docs-schema.php#tbl_views_view_name name} already exists</li>
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
        account_set_view($_SESSION[VAR_USERID]);
    }

    // Delete each of specified views.
    foreach ($views as $view)
    {
        dal_query('views/clrview.sql', $view);
        dal_query('views/cdelall.sql', $view);
        dal_query('views/delete.sql',  $view);
    }

    return NO_ERROR;
}

/**
 * Finds in database and returns the information about specified column.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_view_columns_column_id Column ID}.
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
 * Returns array which contains all columns of currently set view.
 *
 * @return array List of columns of the current view. Each item is an array with following keys:
 * {@link http://www.etraxis.org/docs-schema.php#tbl_def_columns_column_id column_id},
 * {@link http://www.etraxis.org/docs-schema.php#tbl_def_columns_state_name state_name},
 * {@link http://www.etraxis.org/docs-schema.php#tbl_def_columns_field_name field_name},
 * {@link http://www.etraxis.org/docs-schema.php#tbl_def_columns_column_type column_type},
 * {@link http://www.etraxis.org/docs-schema.php#tbl_def_columns_column_order column_order}.
 */
function column_list ()
{
    debug_write_log(DEBUG_TRACE, '[column_list]');

    $columns = array();

    // Find all columns of currently set view.
    if (get_user_level() == USER_LEVEL_GUEST)
    {
        for ($i = COLUMN_TYPE_MINIMUM; $i <= COLUMN_TYPE_MAXIMUM; $i++)
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
        $rs = dal_query('columns/list.sql', $_SESSION[VAR_USERID]);

        // If there are no columns at the moment ...
        if ($rs->rows == 0)
        {
            // ... then create a default set of columns ...
            for ($i = COLUMN_TYPE_MINIMUM; $i <= COLUMN_TYPE_MAXIMUM; $i++)
            {
                dal_query('columns/create.sql', $_SESSION[VAR_USERID], NULL, NULL, $i, $i);
            }

            // ... and query the list of columns again.
            $rs = dal_query('columns/list.sql', $_SESSION[VAR_USERID]);
        }

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
 * Returns number of columns in the currently set view.
 *
 * @return int Current number of columns.
 */
function columns_count ()
{
    debug_write_log(DEBUG_TRACE, '[columns_count]');

    $rs = dal_query('columns/count.sql', $_SESSION[VAR_USERID]);

    return $rs->fetch(0);
}

/**
 * Adds specified columns to the currently set view.
 *
 * @param array $columns List of columns. Each item is a string, concatenated of following data, separated by colon:
 * {@link http://www.etraxis.org/docs-schema.php#tbl_def_columns_column_type column_type},
 * {@link http://www.etraxis.org/docs-schema.php#tbl_def_columns_state_name state_name},
 * {@link http://www.etraxis.org/docs-schema.php#tbl_def_columns_field_name field_name}.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - columns are successfully added</li>
 * <li>{@link ERROR_INTEGER_VALUE_OUT_OF_RANGE} - maximum allowed number of columns is reached</li>
 * </ul>
 */
function columns_set ($columns)
{
    debug_write_log(DEBUG_TRACE, '[columns_set]');

    // Set current view to "unknown" (NULL).
    account_set_view($_SESSION[VAR_USERID]);

    // Get current number of columns in the view.
    $count = columns_count();

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
        dal_query('columns/create.sql', $_SESSION[VAR_USERID], $state, $field, $type, ++$count);
    }

    return NO_ERROR;
}

/**
 * Removes specified columns from the currently set view.
 *
 * @param array $columns List of columns IDs.
 * @return int Always {@link NO_ERROR}.
 */
function columns_clear ($columns)
{
    debug_write_log(DEBUG_TRACE, '[columns_clear]');

    // Delete each of specified columns.
    foreach ($columns as $column)
    {
        dal_query('columns/delete.sql', $column, $_SESSION[VAR_USERID]);
    }

    // Enumerate the rest of columns of currently set view.
    $rs = dal_query('columns/list.sql', $_SESSION[VAR_USERID]);

    // Reorder the rest of columns of currently set view.
    for ($i = 0; ($row = $rs->fetch()) && ($i < MAX_VIEW_SIZE); $i++)
    {
        dal_query('columns/setorder.sql', $_SESSION[VAR_USERID], $row['column_order'], $i + 1);
    }

    // Set current view to "unknown" (NULL).
    account_set_view($_SESSION[VAR_USERID]);

    return NO_ERROR;
}

?>
