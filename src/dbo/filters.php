<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2005-2011  Artem Rodygin
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
 * Filters
 *
 * This module provides API to work with user filters.
 * See also {@link http://code.google.com/p/etraxis/wiki/DatabaseSchema#tbl_filters tbl_filters} database table.
 *
 * @package DBO
 * @subpackage Filters
 */

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/accounts.php');
require_once('../dbo/fields.php');
require_once('../dbo/values.php');
/**#@-*/

//------------------------------------------------------------------------------
//  Definitions.
//------------------------------------------------------------------------------

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
define('FILTER_FLAG_ASSIGNED_TO', 0x0002);
define('FILTER_FLAG_UNCLOSED',    0x0004);
define('FILTER_FLAG_POSTPONED',   0x0008);
define('FILTER_FLAG_ACTIVE',      0x0010);
define('FILTER_FLAG_UNASSIGNED',  0x0020);
/**#@-*/

//------------------------------------------------------------------------------
//  Functions.
//------------------------------------------------------------------------------

/**
 * Finds in database and returns the information about specified filter.
 *
 * @param int $id Filter ID.
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
 * @param int $id Account ID.
 * @param bool $active Whether to return all filters, or active only.
 * @param int &$sort Sort mode (used as output only). The function retrieves current sort mode from
 * client cookie ({@link COOKIE_FILTERS_SORT}) and updates it, if it's out of valid range.
 * @param int &$page Number of current page tab (used as output only). The function retrieves current
 * page from client cookie ({@link COOKIE_FILTERS_PAGE}) and updates it, if it's out of valid range.
 * @return CRecordset Recordset with list of filters.
 */
function filters_list ($id, $active, &$sort, &$page)
{
    debug_write_log(DEBUG_TRACE, '[filters_list]');
    debug_write_log(DEBUG_DUMP,  '[filters_list] $id     = ' . $id);
    debug_write_log(DEBUG_DUMP,  '[filters_list] $active = ' . $active);

    $sort_modes = array
    (
        1 => 'filter_name asc',
        2 => 'fullname asc, username asc, filter_name asc',
        3 => 'filter_name desc',
        4 => 'fullname desc, username desc, filter_name desc',
    );

    $sort = try_request('sort', try_cookie(COOKIE_FILTERS_SORT, 1));
    $sort = ustr2int($sort, 1, count($sort_modes));

    $page = try_request('page', try_cookie(COOKIE_FILTERS_PAGE));
    $page = ustr2int($page, 1, MAXINT);

    save_cookie(COOKIE_FILTERS_SORT, $sort);
    save_cookie(COOKIE_FILTERS_PAGE, $page);

    return dal_query($active ? 'filters/lista.sql' : 'filters/list.sql', $id, $sort_modes[$sort]);
}

/**
 * Validates filter information before creation or modification.
 *
 * @param string $filter_name Filter name.
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
 * @param string $filter_name Filter name.
 * @param string $filter_type Filter type.
 * @param string $filter_flags Filter flags.
 * @param string $filter_param Filter parameter.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - filter is successfully created</li>
 * <li>{@link ERROR_ALREADY_EXISTS} - filter with specified name already exists</li>
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

    return NO_ERROR;
}

/**
 * Modifies specified filter.
 *
 * @param int $id ID of filter to be modified.
 * @param string $filter_name New filter name.
 * @param string $filter_type New filter type.
 * @param string $filter_flags New filter flags.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - filter is successfully modified</li>
 * <li>{@link ERROR_ALREADY_EXISTS} - filter with specified name already exists</li>
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

    return NO_ERROR;
}

/**
 * Disables specified filters.
 *
 * @param array $filters List of filter IDs (NULL to disable them all).
 * @return int Always {@link NO_ERROR}.
 */
function filters_clear ($filters = NULL)
{
    debug_write_log(DEBUG_TRACE, '[filters_clear]');

    if (is_null($filters))
    {
        // Disable all filters.
        dal_query('filters/clearall.sql', $_SESSION[VAR_USERID]);
    }
    else
    {
        // Disable each of specified filters.
        foreach ($filters as $filter)
        {
            dal_query('filters/clear.sql', $filter, $_SESSION[VAR_USERID]);
        }
    }

    return NO_ERROR;
}

/**
 * Checks whether a filter is activated.
 *
 * @param int $id ID of filter to be checked.
 * @return bool TRUE if template can be deleted, FALSE otherwise.
 */
function is_filter_activated ($id)
{
    debug_write_log(DEBUG_TRACE, '[is_filter_activated]');
    debug_write_log(DEBUG_DUMP,  '[is_filter_activated] $id = ' . $id);

    $rs = dal_query('filters/check.sql', $id, $_SESSION[VAR_USERID]);

    return ($rs->fetch(0) != 0);
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
        dal_query('filters/ffdelall.sql',  $filter, $_SESSION[VAR_USERID]);
        dal_query('filters/ftdelall.sql',  $filter, $_SESSION[VAR_USERID]);
        dal_query('filters/fsdelall.sql',  $filter, $_SESSION[VAR_USERID]);
        dal_query('filters/fadelall.sql',  $filter, $_SESSION[VAR_USERID]);
        dal_query('filters/fa2delall.sql', $filter);
        dal_query('filters/vdelall.sql',   $filter);
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

                    case FIELD_TYPE_FLOAT:

                        debug_write_log(DEBUG_NOTICE, "[filter_fields_set] Field type is float.");

                        $min_value = ustrcut(try_request('min_' . $name), ustrlen(MIN_FIELD_FLOAT));
                        $max_value = ustrcut(try_request('max_' . $name), ustrlen(MAX_FIELD_FLOAT));

                        if (ustrlen($min_value) == 0)
                        {
                            $min_value = NULL;
                        }

                        if (ustrlen($max_value) == 0)
                        {
                            $max_value = NULL;
                        }

                        if (!is_null($min_value) && !is_floatvalue($min_value) ||
                            !is_null($max_value) && !is_floatvalue($max_value))
                        {
                            debug_write_log(DEBUG_NOTICE, '[filter_fields_set] At least one of range values is invalid.');
                        }
                        else
                        {
                            if (!is_null($min_value) && !is_null($max_value) && (bccomp($min_value, $max_value) > 0))
                            {
                                swap($min_value, $max_value);
                            }

                            dal_query('filters/ffcreate.sql',
                                      $filter_id,
                                      $row['field_id'],
                                      is_null($min_value) ? NULL : value_find_float($min_value),
                                      is_null($max_value) ? NULL : value_find_float($max_value));
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

                        $checked = try_request('check_' . $name, 0);

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

?>
