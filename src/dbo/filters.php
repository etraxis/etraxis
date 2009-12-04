<?php

/**
 * Filters
 *
 * This module provides API to work with user filters.
 * See also {@link http://www.etraxis.org/docs-schema.php#tbl_filters tbl_filters} database table.
 *
 * @package DBO
 * @subpackage Filters
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
//  Artem Rodygin           2005-07-24      new-009: Records filter.
//  Artem Rodygin           2005-08-22      bug-044: Removable filter will not be removed in some cases.
//  Artem Rodygin           2005-08-23      new-053: All the calls of DAL API functions should be moved to DBO API.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-09-15      new-122: User should be able to create a filter to display postponed records only.
//  Artem Rodygin           2005-09-18      new-073: Implement search folders.
//  Artem Rodygin           2005-09-27      new-141: Source code review.
//  Artem Rodygin           2005-10-19      new-149: User should have ability to modify his filters.
//  Artem Rodygin           2005-10-22      bug-163: Some filters are malfunctional.
//  Artem Rodygin           2005-12-14      bug-191: Search is malfunctional.
//  Artem Rodygin           2006-03-26      bug-229: Records filters are malfunctional.
//  Artem Rodygin           2006-04-21      bug-244: Unexpected message "Filter with entered name already exists".
//  Artem Rodygin           2006-06-29      bug-279: PHP Warning: ociexecute(): OCIStmtExecute: ORA-00904: "STR": invalid identifier
//  Artem Rodygin           2006-06-29      bug-286: dbx_error(): You have an error in your SQL syntax
//  Artem Rodygin           2006-07-23      bug-283: Search becomes case sensitive if UTF-8 values are present in the string being looked for (Oracle).
//  Artem Rodygin           2006-10-12      new-137: Custom queries.
//  Artem Rodygin           2006-10-17      new-361: Extended custom queries.
//  Artem Rodygin           2006-11-05      new-365: Filters sharing.
//  Artem Rodygin           2006-11-24      new-377: Custom views.
//  Artem Rodygin           2006-12-30      new-475: Turning subscriptions on and off is not clear.
//  Artem Rodygin           2007-06-02      bug-525: PHP Warning: ociexecute(): OCIStmtExecute: ORA-01401: inserted value too large for column
//  Artem Rodygin           2007-09-11      new-574: Filter should allow to specify several states.
//  Artem Rodygin           2007-10-29      new-564: Filters set.
//  Artem Rodygin           2007-11-08      bug-614: Variable $i was used before it was defined.
//  Artem Rodygin           2007-11-13      new-618: Extend view and filter set names up to 50 characters.
//  Yury Udovichenko        2007-11-20      new-536: Ability to hide postpone records from the list.
//  Artem Rodygin           2007-11-27      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2007-11-30      new-617: Add 'no view' and 'no filter set' to related comboboxes.
//  Artem Rodygin           2008-03-15      new-683: Filters should be sharable with groups, not with accounts.
//  Artem Rodygin           2008-03-15      new-501: Filter should allow to specify 'none' value of 'list' fields.
//  Artem Rodygin           2008-03-31      bug-690: PHP Warning: odbc_exec(): SQL error: DELETE statement conflicted with COLUMN REFERENCE constraint 'fk_filter_activation_filter_id'.
//  Artem Rodygin           2008-04-03      new-694: Filter for unassigned records.
//  Artem Rodygin           2008-09-09      new-740: Filter set should be overwritten if it already exists.
//  Artem Rodygin           2008-11-07      bug-756: PHP Warning: odbc_exec(): SQL error: Violation of UNIQUE KEY constraint 'ix_fsets'.
//  Artem Rodygin           2008-11-08      bug-759: /src/dbo/filters.php: Variable $id was used before it was defined
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//  Artem Rodygin           2009-06-17      bug-825: Database gets empty strings instead of NULL values.
//  Artem Rodygin           2009-09-09      new-826: Native unicode support for Microsoft SQL Server.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/accounts.php');
require_once('../dbo/fields.php');
require_once('../dbo/values.php');
/**#@-*/

//--------------------------------------------------------------------------------------------------
//  Definitions.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Data restriction.
 */
define('MAX_FILTER_NAME', 50);
define('MAX_FSET_NAME',   50);
/**#@-*/

/**#@+
 * Filter type.
 */
define('FILTER_TYPE_ALL_PROJECTS',  1);
define('FILTER_TYPE_ALL_TEMPLATES', 2);
define('FILTER_TYPE_ALL_STATES',    3);
define('FILTER_TYPE_SEL_STATES',    4);
/**#@-*/

/**#@+
 * Filter flag.
 */
define('FILTER_FLAG_CREATED_BY',  0x0001);
define('FILTER_FLAG_ASSIGNED_ON', 0x0002);
define('FILTER_FLAG_UNCLOSED',    0x0004);
define('FILTER_FLAG_POSTPONED',   0x0008);
define('FILTER_FLAG_ACTIVE',      0x0010);
define('FILTER_FLAG_UNASSIGNED',  0x0020);
/**#@-*/

//--------------------------------------------------------------------------------------------------
//  Functions.
//--------------------------------------------------------------------------------------------------

/**
 * Finds in database and returns the information about specified filter.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_filters_filter_id Filter ID}.
 * @return array Array with data if filter is found in database, FALSE otherwise.
 */
function filter_find ($id)
{
    debug_write_log(DEBUG_TRACE, '[filter_find]');
    debug_write_log(DEBUG_DUMP,  '[filter_find] $id = ' . $id);

    $rs = dal_query('filters/fndid.sql', $id, $_SESSION[VAR_USERID]);

    return ($rs->rows == 0 ? FALSE : $rs->fetch());
}

/**
 * Returns {@link CRecordset DAL recordset} which contains all existing filters of current user.
 *
 * @return CRecordset Recordset with list of filters.
 */
function filters_list ($id, $active = TRUE)
{
    debug_write_log(DEBUG_TRACE, '[filters_list]');
    debug_write_log(DEBUG_DUMP,  '[filters_list] $id     = ' . $id);
    debug_write_log(DEBUG_DUMP,  '[filters_list] $active = ' . $active);

    return dal_query(($active ? 'filters/lista.sql' : 'filters/list.sql'), $id);
}

/**
 * Validates filter information before creation or modification.
 *
 * @param string $filter_name {@link http://www.etraxis.org/docs-schema.php#tbl_filters_filter_name Filter name}.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - data are valid</li>
 * <li>{@link ERROR_INCOMPLETE_FORM} - at least one of required field is empty</li>
 * </ul>
 */
function filter_validate ($filter_name)
{
    debug_write_log(DEBUG_TRACE, '[filter_validate]');
    debug_write_log(DEBUG_DUMP,  '[filter_validate] $filter_name = ' . $filter_name);

    if (ustrlen($filter_name) == 0)
    {
        debug_write_log(DEBUG_NOTICE, '[filter_validate] At least one required field is empty.');
        return ERROR_INCOMPLETE_FORM;
    }

    return NO_ERROR;
}

/**
 * Creates new filter.
 *
 * @param string $filter_name {@link http://www.etraxis.org/docs-schema.php#tbl_filters_filter_name Filter name}.
 * @param string $filter_type {@link http://www.etraxis.org/docs-schema.php#tbl_filters_filter_type Filter type}.
 * @param string $filter_flags {@link http://www.etraxis.org/docs-schema.php#tbl_filters_filter_flags Filter flags}.
 * @param string $filter_param {@link http://www.etraxis.org/docs-schema.php#tbl_filters_filter_param Filter parameter}.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - filter is successfully created</li>
 * <li>{@link ERROR_ALREADY_EXISTS} - filter with specified {@link http://www.etraxis.org/docs-schema.php#tbl_filters_filter_name name} already exists</li>
 * <li>{@link ERROR_NOT_FOUND} - failure on attempt to create filter</li>
 * </ul>
 */
function filter_create ($filter_name, $filter_type, $filter_flags, $filter_param = NULL)
{
    debug_write_log(DEBUG_TRACE, '[filter_create]');
    debug_write_log(DEBUG_DUMP,  '[filter_create] $filter_name  = ' . $filter_name);
    debug_write_log(DEBUG_DUMP,  '[filter_create] $filter_type  = ' . $filter_type);
    debug_write_log(DEBUG_DUMP,  '[filter_create] $filter_flags = ' . $filter_flags);
    debug_write_log(DEBUG_DUMP,  '[filter_create] $filter_param = ' . $filter_param);

    // Check that user doesn't have another filter with the same name.
    $rs = dal_query('filters/fndk.sql', $_SESSION[VAR_USERID], ustrtolower($filter_name));

    if ($rs->rows != 0)
    {
        debug_write_log(DEBUG_NOTICE, '[filter_create] Filter already exists.');
        return ERROR_ALREADY_EXISTS;
    }

    // Create a filter.
    dal_query('filters/create.sql',
              $_SESSION[VAR_USERID],
              $filter_name,
              $filter_type,
              $filter_flags,
              is_null($filter_param) ? NULL : $filter_param);

    // Find newly created filter.
    $rs = dal_query('filters/fndk.sql', $_SESSION[VAR_USERID], ustrtolower($filter_name));

    if ($rs->rows == 0)
    {
        debug_write_log(DEBUG_WARNING, '[filter_create] Created filter not found.');
        return ERROR_NOT_FOUND;
    }

    // Enable new filter.
    dal_query('filters/set.sql', $rs->fetch('filter_id'), $_SESSION[VAR_USERID]);

    // Change current filters set to unknown.
    account_set_fset($_SESSION[VAR_USERID]);

    return NO_ERROR;
}

/**
 * Modifies specified filter.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_filters_filter_id ID} of filter to be modified.
 * @param string $filter_name New {@link http://www.etraxis.org/docs-schema.php#tbl_filters_filter_name filter name}.
 * @param string $filter_type New {@link http://www.etraxis.org/docs-schema.php#tbl_filters_filter_type filter type}.
 * @param string $filter_flags New {@link http://www.etraxis.org/docs-schema.php#tbl_filters_filter_flags filter flags}.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - filter is successfully modified</li>
 * <li>{@link ERROR_ALREADY_EXISTS} - filter with specified {@link http://www.etraxis.org/docs-schema.php#tbl_filters_filter_name name} already exists</li>
 * </ul>
 */
function filter_modify ($id, $filter_name, $filter_type, $filter_flags)
{
    debug_write_log(DEBUG_TRACE, '[filter_modify]');
    debug_write_log(DEBUG_DUMP,  '[filter_modify] $id           = ' . $id);
    debug_write_log(DEBUG_DUMP,  '[filter_modify] $filter_name  = ' . $filter_name);
    debug_write_log(DEBUG_DUMP,  '[filter_modify] $filter_type  = ' . $filter_type);
    debug_write_log(DEBUG_DUMP,  '[filter_modify] $filter_flags = ' . $filter_flags);

    // Check that user doesn't have another filter with the same name, besides this one.
    $rs = dal_query('filters/fndku.sql', $id, $_SESSION[VAR_USERID], ustrtolower($filter_name));

    if ($rs->rows != 0)
    {
        debug_write_log(DEBUG_NOTICE, '[filter_modify] Filter already exists.');
        return ERROR_ALREADY_EXISTS;
    }

    // Modify the filter.
    dal_query('filters/modify.sql',
              $id,
              $filter_name,
              $filter_type,
              $filter_flags);

    return NO_ERROR;
}

/**
 * Enables specified filters.
 *
 * @param array $filters List of filter IDs.
 * @return int Always {@link NO_ERROR}.
 */
function filters_set ($filters)
{
    debug_write_log(DEBUG_TRACE, '[filters_set]');

    // Enable each of specified filters.
    foreach ($filters as $filter)
    {
        dal_query('filters/clear.sql', $filter, $_SESSION[VAR_USERID]);
        dal_query('filters/set.sql',   $filter, $_SESSION[VAR_USERID]);
    }

    // Change current filters set to unknown.
    account_set_fset($_SESSION[VAR_USERID]);

    return NO_ERROR;
}

/**
 * Disables specified filters.
 *
 * @param array $filters List of filter IDs.
 * @return int Always {@link NO_ERROR}.
 */
function filters_clear ($filters)
{
    debug_write_log(DEBUG_TRACE, '[filters_clear]');

    // Disable each of specified filters.
    foreach ($filters as $filter)
    {
        dal_query('filters/clear.sql', $filter, $_SESSION[VAR_USERID]);
    }

    // Change current filters set to unknown.
    account_set_fset($_SESSION[VAR_USERID]);

    return NO_ERROR;
}

/**
 * Deletes specified filters.
 *
 * @param array $filters List of filter IDs.
 * @return int Always {@link NO_ERROR}.
 */
function filters_delete ($filters)
{
    debug_write_log(DEBUG_TRACE, '[filters_delete]');

    foreach ($filters as $filter)
    {
        dal_query('filters/ffsdelall.sql', $filter, $_SESSION[VAR_USERID]);
        dal_query('filters/ffdelall.sql',  $filter, $_SESSION[VAR_USERID]);
        dal_query('filters/ftdelall.sql',  $filter, $_SESSION[VAR_USERID]);
        dal_query('filters/fsdelall.sql',  $filter, $_SESSION[VAR_USERID]);
        dal_query('filters/fadelall.sql',  $filter, $_SESSION[VAR_USERID]);
        dal_query('filters/fa2delall.sql', $filter);
        dal_query('filters/fshdelall.sql', $filter, $_SESSION[VAR_USERID]);
        dal_query('filters/delete.sql',    $filter, $_SESSION[VAR_USERID]);
    }

    return NO_ERROR;
}

/**
 * @ignore
 */
function filter_states_get ($filter_id, $template_id)
{
    debug_write_log(DEBUG_TRACE, '[filter_states_set]');
    debug_write_log(DEBUG_DUMP,  '[filter_states_set] $filter_id   = ' . $filter_id);
    debug_write_log(DEBUG_DUMP,  '[filter_states_set] $template_id = ' . $template_id);

    $states = array();

    $rs = dal_query('filters/fslist.sql', $filter_id, $template_id);

    while (($row = $rs->fetch()))
    {
        array_push($states, $row['state_id']);
    }

    return $states;
}

/**
 * @ignore
 */
function filter_trans_set ($filter_id, $template_id)
{
    debug_write_log(DEBUG_TRACE, '[filter_trans_set]');
    debug_write_log(DEBUG_DUMP,  '[filter_trans_set] $filter_id   = ' . $filter_id);
    debug_write_log(DEBUG_DUMP,  '[filter_trans_set] $template_id = ' . $template_id);

    dal_query('filters/ftdelall.sql', $filter_id, $_SESSION[VAR_USERID]);
    $rs = dal_query('states/list.sql', $template_id, 'state_type, state_name');

    while (($row = $rs->fetch()))
    {
        $name = 'state' . $row['state_id'];

        if (isset($_REQUEST[$name]))
        {
            debug_write_log(DEBUG_NOTICE, "[filter_trans_set] Found filter for state #{$row['state_id']}.");

            $min_value = ustrcut(try_request('min_' . $name), ustrlen(get_date(SAMPLE_DATE)));
            $max_value = ustrcut(try_request('max_' . $name), ustrlen(get_date(SAMPLE_DATE)));

            if (ustrlen($min_value) == 0 && ustrlen($max_value) == 0)
            {
                debug_write_log(DEBUG_NOTICE, '[filter_trans_set] At least one of range values must be set.');
            }
            else
            {
                $min_date = (ustrlen($min_value) == 0 ? MIN_FIELD_DATE : ustr2date($min_value));
                $max_date = (ustrlen($max_value) == 0 ? MAX_FIELD_DATE : ustr2date($max_value));

                if ($min_date == -1 || $max_date == -1)
                {
                    debug_write_log(DEBUG_NOTICE, '[filter_trans_set] At least one of range values is invalid.');
                }
                else
                {
                    if ($min_date > $max_date)
                    {
                        swap($min_date, $max_date);
                    }

                    dal_query('filters/ftcreate.sql',
                              $filter_id,
                              $row['state_id'],
                              $min_date,
                              $max_date);
                }
            }
        }
    }
}

/**
 * @ignore
 */
function filter_fields_set ($filter_id, $template_id)
{
    debug_write_log(DEBUG_TRACE, '[filter_fields_set]');
    debug_write_log(DEBUG_DUMP,  '[filter_fields_set] $filter_id   = ' . $filter_id);
    debug_write_log(DEBUG_DUMP,  '[filter_fields_set] $template_id = ' . $template_id);

    dal_query('filters/ffdelall.sql', $filter_id, $_SESSION[VAR_USERID]);
    $rs = dal_query('states/list.sql', $template_id, 'state_type, state_name');

    while (($row = $rs->fetch()))
    {
        $rsf = dal_query('filters/flist.sql',
                         $row['state_id'],
                         $_SESSION[VAR_USERID],
                         FIELD_ALLOW_TO_READ);

        while (($row = $rsf->fetch()))
        {
            $name = 'field' . $row['field_id'];

            if (isset($_REQUEST[$name]))
            {
                debug_write_log(DEBUG_NOTICE, "[filter_fields_set] Found filter for field #{$row['field_id']}.");

                switch ($row['field_type'])
                {
                    case FIELD_TYPE_NUMBER:

                        debug_write_log(DEBUG_NOTICE, "[filter_fields_set] Field type is number.");

                        $min_value = ustrcut(try_request('min_' . $name), ustrlen(MAX_FIELD_INTEGER) + 1);
                        $max_value = ustrcut(try_request('max_' . $name), ustrlen(MAX_FIELD_INTEGER) + 1);

                        if (ustrlen($min_value) == 0)
                        {
                            $min_value = NULL;
                        }

                        if (ustrlen($max_value) == 0)
                        {
                            $max_value = NULL;
                        }

                        if (!is_null($min_value) && !is_intvalue($min_value) ||
                            !is_null($max_value) && !is_intvalue($max_value))
                        {
                            debug_write_log(DEBUG_NOTICE, '[filter_fields_set] At least one of range values is invalid.');
                        }
                        else
                        {
                            if (!is_null($min_value) && !is_null($max_value) && ($min_value > $max_value))
                            {
                                swap($min_value, $max_value);
                            }

                            dal_query('filters/ffcreate.sql',
                                      $filter_id,
                                      $row['field_id'],
                                      is_null($min_value) ? NULL : $min_value,
                                      is_null($max_value) ? NULL : $max_value);
                        }

                        break;

                    case FIELD_TYPE_STRING:
                    case FIELD_TYPE_MULTILINED:

                        debug_write_log(DEBUG_NOTICE, "[filter_fields_set] Field type is string/multilined.");

                        $value = ustrcut(try_request('edit_' . $name), MAX_FIELD_STRING);

                        if (ustrlen($value) == 0)
                        {
                            $value = NULL;
                        }

                        $param = value_find_string($value);

                        dal_query('filters/ffcreate.sql',
                                  $filter_id,
                                  $row['field_id'],
                                  is_null($param) ? NULL : $param,
                                  NULL);

                        break;

                    case FIELD_TYPE_CHECKBOX:

                        debug_write_log(DEBUG_NOTICE, "[filter_fields_set] Field type is checkbox.");

                        $checked = isset($_REQUEST['check_' . $name]);

                        dal_query('filters/ffcreate.sql',
                                  $filter_id,
                                  $row['field_id'],
                                  bool2sql($checked),
                                  NULL);

                        break;

                    case FIELD_TYPE_LIST:

                        debug_write_log(DEBUG_NOTICE, "[filter_fields_set] Field type is list.");

                        $value = ustrcut(try_request('list_' . $name), ustrlen(MAXINT));

                        if (ustrlen($value) == 0)
                        {
                            $value = NULL;
                        }

                        if (!is_null($value) && !is_intvalue($value))
                        {
                            debug_write_log(DEBUG_NOTICE, '[filter_fields_set] Invalid integer value.');
                        }
                        else
                        {
                            if (!is_null($value) && ($value < 1 || $value > MAXINT))
                            {
                                debug_write_log(DEBUG_NOTICE, '[filter_fields_set] Invalid integer range.');
                            }
                            else
                            {
                                dal_query('filters/ffcreate.sql',
                                          $filter_id,
                                          $row['field_id'],
                                          is_null($value) ? NULL : $value,
                                          NULL);
                            }
                        }

                        break;

                    case FIELD_TYPE_RECORD:

                        debug_write_log(DEBUG_NOTICE, "[filter_fields_set] Field type is record.");

                        $value = ustrcut(try_request('edit_' . $name), ustrlen(MAXINT));

                        if (ustrlen($value) == 0)
                        {
                            $value = NULL;
                        }

                        if (!is_null($value) && !is_intvalue($value))
                        {
                            debug_write_log(DEBUG_NOTICE, '[filter_fields_set] Invalid record ID.');
                        }
                        else
                        {
                            if (!is_null($value) && ($value < 1 || $value > MAXINT))
                            {
                                debug_write_log(DEBUG_NOTICE, '[filter_fields_set] Invalid integer range.');
                            }
                            else
                            {
                                dal_query('filters/ffcreate.sql',
                                          $filter_id,
                                          $row['field_id'],
                                          is_null($value) ? NULL : $value,
                                          NULL);
                            }
                        }

                        break;

                    case FIELD_TYPE_DATE:

                        debug_write_log(DEBUG_NOTICE, "[filter_fields_set] Field type is date.");

                        $min_value = ustrcut(try_request('min_' . $name), ustrlen(get_date(SAMPLE_DATE)));
                        $max_value = ustrcut(try_request('max_' . $name), ustrlen(get_date(SAMPLE_DATE)));

                        if (ustrlen($min_value) == 0)
                        {
                            $min_date = NULL;
                        }
                        else
                        {
                            $min_date = ustr2date($min_value);
                        }

                        if (ustrlen($max_value) == 0)
                        {
                            $max_date = NULL;
                        }
                        else
                        {
                            $max_date = ustr2date($max_value);
                        }

                        if (!is_null($min_date) && $min_date == -1 ||
                            !is_null($max_date) && $max_date == -1)
                        {
                            debug_write_log(DEBUG_NOTICE, '[filter_fields_set] At least one of range values is invalid.');
                        }
                        else
                        {
                            if (!is_null($min_date) && !is_null($max_date) && ($min_date > $max_date))
                            {
                                swap($min_date, $max_date);
                            }

                            dal_query('filters/ffcreate.sql',
                                      $filter_id,
                                      $row['field_id'],
                                      is_null($min_date) ? NULL : $min_date,
                                      is_null($max_date) ? NULL : $max_date);
                        }

                        break;

                    case FIELD_TYPE_DURATION:

                        debug_write_log(DEBUG_NOTICE, "[filter_fields_set] Field type is duration.");

                        $min_value = ustrcut(try_request('min_' . $name), ustrlen(time2ustr(MAX_FIELD_DURATION)));
                        $max_value = ustrcut(try_request('max_' . $name), ustrlen(time2ustr(MAX_FIELD_DURATION)));

                        if (ustrlen($min_value) == 0)
                        {
                            $min_time = NULL;
                        }
                        else
                        {
                            $min_time = ustr2time($min_value);
                        }

                        if (ustrlen($max_value) == 0)
                        {
                            $max_time = NULL;
                        }
                        else
                        {
                            $max_time = ustr2time($max_value);
                        }

                        if (!is_null($min_time) && $min_time == -1 ||
                            !is_null($max_time) && $max_time == -1)
                        {
                            debug_write_log(DEBUG_NOTICE, '[filter_fields_set] At least one of range values is invalid.');
                        }
                        else
                        {
                            if (!is_null($min_time) && !is_null($max_time) && ($min_time > $max_time))
                            {
                                swap($min_time, $max_time);
                            }

                            dal_query('filters/ffcreate.sql',
                                      $filter_id,
                                      $row['field_id'],
                                      is_null($min_time) ? NULL : $min_time,
                                      is_null($max_time) ? NULL : $max_time);
                        }

                        break;

                    default:
                        debug_write_log(DEBUG_WARNING, '[filter_fields_set] Unknown field type = ' . $row['field_type']);
                }
            }
        }
    }

    return NO_ERROR;
}

/**
 * Finds in database and returns the information about specified filters set.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_fsets_fset_id Filters set ID}.
 * @return array Array with data if filters set is found in database, FALSE otherwise.
 */
function fset_find ($id)
{
    debug_write_log(DEBUG_TRACE, '[fset_find]');
    debug_write_log(DEBUG_DUMP,  '[fset_find] $id = ' . $id);

    $rs = dal_query('filters/fsfndid.sql', $id, $_SESSION[VAR_USERID]);

    return ($rs->rows == 0 ? FALSE : $rs->fetch());
}

/**
 * Returns {@link CRecordset DAL recordset} which contains all existing filters sets of current user.
 *
 * @return CRecordset Recordset with list of filters sets.
 */
function fsets_list ()
{
    debug_write_log(DEBUG_TRACE, '[fsets_list]');

    return dal_query('filters/fs2list.sql', $_SESSION[VAR_USERID]);
}

/**
 * Validates filters set information before creation or modification.
 *
 * @param string $fset_name {@link http://www.etraxis.org/docs-schema.php#tbl_fsets_fset_name Filters set name}.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - data are valid</li>
 * <li>{@link ERROR_INCOMPLETE_FORM} - at least one of required field is empty</li>
 * </ul>
 */
function fset_validate ($fset_name)
{
    debug_write_log(DEBUG_TRACE, '[fset_validate]');
    debug_write_log(DEBUG_DUMP,  '[fset_validate] $fset_name = ' . $fset_name);

    if (ustrlen($fset_name) == 0)
    {
        debug_write_log(DEBUG_NOTICE, '[fset_validate] At least one required field is empty.');
        return ERROR_INCOMPLETE_FORM;
    }

    return NO_ERROR;
}

/**
 * Creates new filters set.
 *
 * @param string $fset_name {@link http://www.etraxis.org/docs-schema.php#tbl_fsets_fset_name Filters set name}.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - filters set is successfully created</li>
 * <li>{@link ERROR_NOT_FOUND} - failure on attempt to create filters set</li>
 * </ul>
 */
function fset_create ($fset_name)
{
    debug_write_log(DEBUG_TRACE, '[fset_create]');
    debug_write_log(DEBUG_DUMP,  '[fset_create] $fset_name = ' . $fset_name);

    // Check that user doesn't have another filters set with the same name.
    $rs = dal_query('filters/fsfndk.sql', $_SESSION[VAR_USERID], ustrtolower($fset_name));

    if ($rs->rows != 0)
    {
        debug_write_log(DEBUG_NOTICE, '[fset_create] Filters set already exists.');

        $fset_id = $rs->fetch('fset_id');

        dal_query('accounts/setfset.sql',  $fset_id, NULL);
        dal_query('filters/fsfdelall.sql', $fset_id, $_SESSION[VAR_USERID]);
        dal_query('filters/fsdelete.sql',  $fset_id, $_SESSION[VAR_USERID]);
    }

    // Create a filters set.
    dal_query('filters/fs2create.sql', $_SESSION[VAR_USERID], $fset_name);

    // Find newly created filters set.
    $rs = dal_query('filters/fsfndk.sql', $_SESSION[VAR_USERID], ustrtolower($fset_name));

    if ($rs->rows == 0)
    {
        debug_write_log(DEBUG_NOTICE, '[fset_create] Created filters set not found.');
        return ERROR_NOT_FOUND;
    }

    // Get an ID of the created filters set.
    $fset_id = $rs->fetch('fset_id');

    // Assign all currently enabled filters to the new filters set, and change current filters set to new one.
    dal_query('filters/fsfcreate.sql', $_SESSION[VAR_USERID], $fset_id);
    dal_query('accounts/setfset.sql',  $_SESSION[VAR_USERID], $fset_id);

    return NO_ERROR;
}

/**
 * Modifies specified filters set.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_fsets_fset_id ID} of filters set to be modified.
 * @param string $fset_name New {@link http://www.etraxis.org/docs-schema.php#tbl_fsets_fset_name filters set name}.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - filters set is successfully modified</li>
 * <li>{@link ERROR_ALREADY_EXISTS} - filters set with specified {@link http://www.etraxis.org/docs-schema.php#tbl_fsets_fset_name name} already exists</li>
 * </ul>
 */
function fset_modify ($id, $fset_name)
{
    debug_write_log(DEBUG_TRACE, '[fset_modify]');
    debug_write_log(DEBUG_DUMP,  '[fset_modify] $id        = ' . $id);
    debug_write_log(DEBUG_DUMP,  '[fset_modify] $fset_name = ' . $fset_name);

    // Check that user doesn't have another filters set with the same name, besides this one.
    $rs = dal_query('filters/fsfndku.sql', $id, $_SESSION[VAR_USERID], ustrtolower($fset_name));

    if ($rs->rows != 0)
    {
        debug_write_log(DEBUG_NOTICE, '[fset_modify] Filters set already exists.');
        return ERROR_ALREADY_EXISTS;
    }

    // Modify the filters set.
    dal_query('filters/fsmodify.sql', $id, $fset_name);

    return NO_ERROR;
}

/**
 * Deletes selected filters set.
 *
 * @param array $fsets List of filters sets IDs.
 * @return int Always {@link NO_ERROR}.
 */
function fsets_delete ($fsets)
{
    debug_write_log(DEBUG_TRACE, '[fsets_delete]');

    // If current filters set is in list of sets to be deleted, change it to unknown.
    if (in_array($_SESSION[VAR_FSET], $fsets))
    {
        account_set_fset($_SESSION[VAR_USERID]);
    }

    // Delete each of specified filters sets.
    foreach ($fsets as $fset)
    {
        dal_query('filters/fsfdelall.sql', $fset, $_SESSION[VAR_USERID]);
        dal_query('filters/fsdelete.sql',  $fset, $_SESSION[VAR_USERID]);
    }

    return NO_ERROR;
}

?>
