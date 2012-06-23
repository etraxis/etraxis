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
 * Fields
 *
 * This module provides API to work with eTraxis fields.
 * See also {@link http://code.google.com/p/etraxis/wiki/DatabaseSchema#tbl_fields tbl_fields} database table.
 *
 * @package DBO
 * @subpackage Fields
 */

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/values.php');
/**#@-*/

//------------------------------------------------------------------------------
//  Definitions.
//------------------------------------------------------------------------------

/**#@+
 * Data restriction.
 */
define('MAX_FIELD_NAME',        50);
define('MAX_FIELD_INTEGER',     1000000000);
define('MIN_FIELD_FLOAT',       '-9999999999.9999999999');
define('MAX_FIELD_FLOAT',       '+9999999999.9999999999');
define('MAX_FIELD_STRING',      250);
define('MAX_FIELD_MULTILINED',  4000);
define('MAX_FIELD_LIST_ITEMS',  1000);
define('MAX_LISTITEM_NAME',     50);
define('MIN_FIELD_DATE',        ~MAXINT);
define('MAX_FIELD_DATE',        MAXINT);
define('MIN_FIELD_DURATION',    0);
define('MAX_FIELD_DURATION',    59999999);
define('MAX_FIELD_DESCRIPTION', 1000);
define('MAX_FIELD_REGEX',       500);
/**#@-*/

/**
 * Unix Epoch of 1977-12-29.
 * Needed to evaluate maximum length of string with date, formatted in current user's locale.
 */
define('SAMPLE_DATE', mktime(0,0,0,12,29,1977));

/**#@+
 * Field type.
 */
define('FIELD_TYPE_MINIMUM',    1);
define('FIELD_TYPE_NUMBER',     1);
define('FIELD_TYPE_STRING',     2);
define('FIELD_TYPE_MULTILINED', 3);
define('FIELD_TYPE_CHECKBOX',   4);
define('FIELD_TYPE_LIST',       5);
define('FIELD_TYPE_RECORD',     6);
define('FIELD_TYPE_DATE',       7);
define('FIELD_TYPE_DURATION',   8);
define('FIELD_TYPE_FLOAT',      9);
define('FIELD_TYPE_MAXIMUM',    9);
/**#@-*/

/**#@+
 * Field permission.
 */
define('FIELD_RESTRICTED',     0);  // no permissions
define('FIELD_ALLOW_TO_READ',  1);  // read-only permissions
define('FIELD_ALLOW_TO_WRITE', 2);  // read and write permissions
/**#@-*/

/**#@+
 * Field role.
 */
define('FIELD_ROLE_AUTHOR',      -1);
define('FIELD_ROLE_RESPONSIBLE', -2);
define('FIELD_ROLE_REGISTERED',  -3);
define('MIN_FIELD_ROLE', FIELD_ROLE_REGISTERED);
/**#@-*/

// Field type resources.
$field_type_res = array
(
    FIELD_TYPE_NUMBER     => RES_NUMBER_ID,
    FIELD_TYPE_FLOAT      => RES_DECIMAL_ID,
    FIELD_TYPE_STRING     => RES_STRING_ID,
    FIELD_TYPE_MULTILINED => RES_MULTILINED_TEXT_ID,
    FIELD_TYPE_CHECKBOX   => RES_CHECKBOX_ID,
    FIELD_TYPE_LIST       => RES_LIST_ID,
    FIELD_TYPE_RECORD     => RES_RECORD_ID,
    FIELD_TYPE_DATE       => RES_DATE_ID,
    FIELD_TYPE_DURATION   => RES_DURATION_ID,
);

//------------------------------------------------------------------------------
//  Functions.
//------------------------------------------------------------------------------

/**
 * Finds in database and returns the information about specified field.
 *
 * @param int $id Field ID.
 * @return array Array with data if field is found in database, FALSE otherwise.
 */
function field_find ($id)
{
    debug_write_log(DEBUG_TRACE, '[field_find]');
    debug_write_log(DEBUG_DUMP,  '[field_find] $id = ' . $id);

    $rs = dal_query('fields/fndid.sql', $id);

    return ($rs->rows == 0 ? FALSE : $rs->fetch());
}

/**
 * Returns {@link CRecordset DAL recordset} which contains all existing fields of specified state,
 * sorted in accordance with current sort mode.
 *
 * @param int $id State ID.
 * @param int &$sort Sort mode (used as output only). The function retrieves current sort mode from
 * client cookie ({@link COOKIE_FIELDS_SORT}) and updates it, if it's out of valid range.
 * @param int &$page Number of current page tab (used as output only). The function retrieves current
 * page from client cookie ({@link COOKIE_FIELDS_PAGE}) and updates it, if it's out of valid range.
 * @return CRecordset Recordset with list of fields.
 */
function fields_list ($id, &$sort, &$page)
{
    debug_write_log(DEBUG_TRACE, '[fields_list]');
    debug_write_log(DEBUG_DUMP,  '[fields_list] $id = ' . $id);

    $sort_modes = array
    (
        1  => 'field_order asc',
        2  => 'field_name asc',
        3  => 'field_type asc, field_name asc',
        4  => 'is_required asc, field_name asc',
        5  => 'guest_access asc, field_name asc',
        6  => 'field_order desc',
        7  => 'field_name desc',
        8  => 'field_type desc, field_name desc',
        9  => 'is_required desc, field_name desc',
        10 => 'guest_access desc, field_name desc',
    );

    $sort = try_request('sort', try_cookie(COOKIE_FIELDS_SORT, 1));
    $sort = ustr2int($sort, 1, count($sort_modes));

    $page = try_request('page', try_cookie(COOKIE_FIELDS_PAGE));
    $page = ustr2int($page, 1, MAXINT);

    save_cookie(COOKIE_FIELDS_SORT, $sort);
    save_cookie(COOKIE_FIELDS_PAGE, $page);

    return dal_query('fields/list.sql', $id, $sort_modes[$sort]);
}

/**
 * Returns number of fields for specified state.
 *
 * @param int $id State ID.
 * @return int Current number of fields.
 */
function field_count ($id)
{
    debug_write_log(DEBUG_TRACE, '[field_count]');
    debug_write_log(DEBUG_DUMP,  '[field_count] $id = ' . $id);

    $rs = dal_query('fields/count.sql', $id);

    return ($rs->rows == 0 ? 0 : $rs->fetch(0));
}

/**
 * Returns list of all local and global groups which have specified permission for specified field.
 *
 * @param int $pid Project ID.
 * @param int $fid Field ID.
 * @param int $perms Permission:
 * <ul>
 * <li>{@link FIELD_ALLOW_TO_READ}</li>
 * <li>{@link FIELD_ALLOW_TO_WRITE}</li>
 * </ul>
 * @return CRecordset Recordset with list of groups.
 */
function field_amongs ($pid, $fid, $perms)
{
    debug_write_log(DEBUG_TRACE, '[field_amongs]');
    debug_write_log(DEBUG_DUMP,  '[field_amongs] $pid   = ' . $pid);
    debug_write_log(DEBUG_DUMP,  '[field_amongs] $fid   = ' . $fid);
    debug_write_log(DEBUG_DUMP,  '[field_amongs] $perms = ' . $perms);

    return dal_query('fields/fpamongs.sql', $pid, $fid, $perms);
}

/**
 * Returns list of all local and global groups which don't have specified permission for specified field.
 *
 * @param int $pid Project ID.
 * @param int $fid Field ID.
 * @param int $perms Permission:
 * <ul>
 * <li>{@link FIELD_ALLOW_TO_READ}</li>
 * <li>{@link FIELD_ALLOW_TO_WRITE}</li>
 * </ul>
 * @return CRecordset Recordset with list of groups.
 */
function field_others ($pid, $fid, $perms)
{
    debug_write_log(DEBUG_TRACE, '[field_others]');
    debug_write_log(DEBUG_DUMP,  '[field_others] $pid   = ' . $pid);
    debug_write_log(DEBUG_DUMP,  '[field_others] $fid   = ' . $fid);
    debug_write_log(DEBUG_DUMP,  '[field_others] $perms = ' . $perms);

    return dal_query('fields/fpothers.sql', $pid, $fid, $perms);
}

/**
 * Validates general field information before creation or modification.
 *
 * @param string $field_name Field name.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - data are valid</li>
 * <li>{@link ERROR_INCOMPLETE_FORM} - at least one of required field is empty</li>
 * </ul>
 */
function field_validate ($field_name)
{
    debug_write_log(DEBUG_TRACE, '[field_validate]');
    debug_write_log(DEBUG_DUMP,  '[field_validate] $field_name = ' . $field_name);

    if (ustrlen($field_name) == 0)
    {
        debug_write_log(DEBUG_NOTICE, '[field_validate] At least one required field is empty.');
        return ERROR_INCOMPLETE_FORM;
    }

    return NO_ERROR;
}

/**
 * Validates 'Number' field information before creation or modification.
 *
 * @param string $field_name Field name.
 * @param int $min_value Minimum allowed value of the field.
 * @param int $max_value Maximum allowed value of the field.
 * @param int $def_value Default allowed value of the field (NULL by default).
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - data are valid</li>
 * <li>{@link ERROR_INCOMPLETE_FORM} - at least one of required field is empty</li>
 * <li>{@link ERROR_INVALID_INTEGER_VALUE} - at least one of specified integer values is invalid</li>
 * <li>{@link ERROR_INTEGER_VALUE_OUT_OF_RANGE} - at least one of specified integer values is less than -{@link MAX_FIELD_INTEGER}, or greater than {@link MAX_FIELD_INTEGER}</li>
 * <li>{@link ERROR_MIN_MAX_VALUES} - maximum value is less than minimum one</li>
 * <li>{@link ERROR_DEFAULT_VALUE_OUT_OF_RANGE} - default value is less than $min_value, or greater than $max_value</li>
 * </ul>
 */
function field_validate_number ($field_name, $min_value, $max_value, $def_value = NULL)
{
    debug_write_log(DEBUG_TRACE, '[field_validate_number]');
    debug_write_log(DEBUG_DUMP,  '[field_validate_number] $field_name = ' . $field_name);
    debug_write_log(DEBUG_DUMP,  '[field_validate_number] $min_value  = ' . $min_value);
    debug_write_log(DEBUG_DUMP,  '[field_validate_number] $max_value  = ' . $max_value);
    debug_write_log(DEBUG_DUMP,  '[field_validate_number] $def_value  = ' . $def_value);

    // Check that field name is not empty.
    if (ustrlen($field_name) == 0)
    {
        debug_write_log(DEBUG_NOTICE, '[field_validate_number] At least one required field is empty.');
        return ERROR_INCOMPLETE_FORM;
    }

    // Check that specified values are integer.
    if (!is_intvalue($min_value) ||
        !is_intvalue($max_value) ||
        (!is_null($def_value) && !is_intvalue($def_value)))
    {
        debug_write_log(DEBUG_NOTICE, '[field_validate_number] Invalid integer value.');
        return ERROR_INVALID_INTEGER_VALUE;
    }

    // Check that specified values are in the range of valid values.
    if ($min_value < -MAX_FIELD_INTEGER || $min_value > MAX_FIELD_INTEGER ||
        $max_value < -MAX_FIELD_INTEGER || $max_value > MAX_FIELD_INTEGER)
    {
        debug_write_log(DEBUG_NOTICE, '[field_validate_number] Integer value is out of range.');
        return ERROR_INTEGER_VALUE_OUT_OF_RANGE;
    }

    // Check that minimum value is less than maximum one.
    if ($min_value > $max_value)
    {
        debug_write_log(DEBUG_NOTICE, '[field_validate_number] Minimum value is greater then maximum one.');
        return ERROR_MIN_MAX_VALUES;
    }

    // Check that default value is in the range between minimum and maximum ones.
    if (!is_null($def_value) &&
        ($def_value < $min_value || $def_value > $max_value))
    {
        debug_write_log(DEBUG_NOTICE, '[field_validate_number] Default value is out of range.');
        return ERROR_DEFAULT_VALUE_OUT_OF_RANGE;
    }

    return NO_ERROR;
}

/**
 * Validates 'Decimal' field information before creation or modification.
 *
 * @param string $field_name Field name.
 * @param string $min_value Minimum allowed value of the field.
 * @param string $max_value Maximum allowed value of the field.
 * @param string $def_value Default allowed value of the field (NULL by default).
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - data are valid</li>
 * <li>{@link ERROR_INCOMPLETE_FORM} - at least one of required field is empty</li>
 * <li>{@link ERROR_INVALID_FLOAT_VALUE} - at least one of specified float values is invalid</li>
 * <li>{@link ERROR_FLOAT_VALUE_OUT_OF_RANGE} - at least one of specified float values is out of allowed range</li>
 * <li>{@link ERROR_MIN_MAX_VALUES} - maximum value is less than minimum one</li>
 * <li>{@link ERROR_DEFAULT_VALUE_OUT_OF_RANGE} - default value is less than $min_value, or greater than $max_value</li>
 * </ul>
 */
function field_validate_float ($field_name, $min_value, $max_value, $def_value = NULL)
{
    debug_write_log(DEBUG_TRACE, '[field_validate_float]');
    debug_write_log(DEBUG_DUMP,  '[field_validate_float] $field_name = ' . $field_name);
    debug_write_log(DEBUG_DUMP,  '[field_validate_float] $min_value  = ' . $min_value);
    debug_write_log(DEBUG_DUMP,  '[field_validate_float] $max_value  = ' . $max_value);
    debug_write_log(DEBUG_DUMP,  '[field_validate_float] $def_value  = ' . $def_value);

    // Check that field name is not empty.
    if (ustrlen($field_name) == 0)
    {
        debug_write_log(DEBUG_NOTICE, '[field_validate_float] At least one required field is empty.');
        return ERROR_INCOMPLETE_FORM;
    }

    // Check that specified values are float.
    if (!is_floatvalue($min_value) ||
        !is_floatvalue($max_value) ||
        (!is_null($def_value) && !is_floatvalue($def_value)))
    {
        debug_write_log(DEBUG_NOTICE, '[field_validate_float] Invalid float value.');
        return ERROR_INVALID_FLOAT_VALUE;
    }

    // Check that specified values are in the range of valid values.
    if (bccomp($min_value, MIN_FIELD_FLOAT) < 0 || bccomp($min_value, MAX_FIELD_FLOAT) > 0 ||
        bccomp($max_value, MIN_FIELD_FLOAT) < 0 || bccomp($max_value, MAX_FIELD_FLOAT) > 0)
    {
        debug_write_log(DEBUG_NOTICE, '[field_validate_float] Float value is out of range.');
        return ERROR_FLOAT_VALUE_OUT_OF_RANGE;
    }

    // Check that minimum value is less than maximum one.
    if (bccomp($min_value, $max_value) > 0)
    {
        debug_write_log(DEBUG_NOTICE, '[field_validate_float] Minimum value is greater then maximum one.');
        return ERROR_MIN_MAX_VALUES;
    }

    // Check that default value is in the range between minimum and maximum ones.
    if (!is_null($def_value) &&
        (bccomp($def_value, $min_value) < 0 || bccomp($def_value, $max_value) > 0))
    {
        debug_write_log(DEBUG_NOTICE, '[field_validate_float] Default value is out of range.');
        return ERROR_DEFAULT_VALUE_OUT_OF_RANGE;
    }

    return NO_ERROR;
}

/**
 * Validates 'String' field information before creation or modification.
 *
 * @param string $field_name Field name.
 * @param int $max_length Maximum allowed length of string value in this field.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - data are valid</li>
 * <li>{@link ERROR_INCOMPLETE_FORM} - at least one of required field is empty</li>
 * <li>{@link ERROR_INVALID_INTEGER_VALUE} - specified maximum length is invalid</li>
 * <li>{@link ERROR_INTEGER_VALUE_OUT_OF_RANGE} - specified maximum length is greater than {@link MAX_FIELD_STRING}</li>
 * </ul>
 */
function field_validate_string ($field_name, $max_length)
{
    debug_write_log(DEBUG_TRACE, '[field_validate_string]');
    debug_write_log(DEBUG_DUMP,  '[field_validate_string] $field_name = ' . $field_name);
    debug_write_log(DEBUG_DUMP,  '[field_validate_string] $max_length = ' . $max_length);

    // Check that field name is not empty.
    if (ustrlen($field_name) == 0)
    {
        debug_write_log(DEBUG_NOTICE, '[field_validate_string] At least one required field is empty.');
        return ERROR_INCOMPLETE_FORM;
    }

    // Check that specified values are integer.
    if (!is_intvalue($max_length))
    {
        debug_write_log(DEBUG_NOTICE, '[field_validate_string] Invalid integer value.');
        return ERROR_INVALID_INTEGER_VALUE;
    }

    // Check that specified values are in the range of valid values.
    if ($max_length < 1 || $max_length > MAX_FIELD_STRING)
    {
        debug_write_log(DEBUG_NOTICE, '[field_validate_string] Integer value is out of range.');
        return ERROR_INTEGER_VALUE_OUT_OF_RANGE;
    }

    return NO_ERROR;
}

/**
 * Validates 'Multilined text' field information before creation or modification.
 *
 * @param string $field_name Field name.
 * @param int $max_length Maximum allowed length of string value in this field.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - data are valid</li>
 * <li>{@link ERROR_INCOMPLETE_FORM} - at least one of required field is empty</li>
 * <li>{@link ERROR_INVALID_INTEGER_VALUE} - specified maximum length is invalid</li>
 * <li>{@link ERROR_INTEGER_VALUE_OUT_OF_RANGE} - specified maximum length is greater than {@link MAX_FIELD_MULTILINED}</li>
 * </ul>
 */
function field_validate_multilined ($field_name, $max_length)
{
    debug_write_log(DEBUG_TRACE, '[field_validate_multilined]');
    debug_write_log(DEBUG_DUMP,  '[field_validate_multilined] $field_name = ' . $field_name);
    debug_write_log(DEBUG_DUMP,  '[field_validate_multilined] $max_length = ' . $max_length);

    // Check that field name is not empty.
    if (ustrlen($field_name) == 0)
    {
        debug_write_log(DEBUG_NOTICE, '[field_validate_multilined] At least one required field is empty.');
        return ERROR_INCOMPLETE_FORM;
    }

    // Check that specified values are integer.
    if (!is_intvalue($max_length))
    {
        debug_write_log(DEBUG_NOTICE, '[field_validate_multilined] Invalid integer value.');
        return ERROR_INVALID_INTEGER_VALUE;
    }

    // Check that specified values are in the range of valid values.
    if ($max_length < 1 || $max_length > MAX_FIELD_MULTILINED)
    {
        debug_write_log(DEBUG_NOTICE, '[field_validate_multilined] Integer value is out of range.');
        return ERROR_INTEGER_VALUE_OUT_OF_RANGE;
    }

    return NO_ERROR;
}

/**
 * Validates 'Date' field information before creation or modification.
 *
 * @param string $field_name Field name.
 * @param int $min_value Minimum allowed value of the field.
 * @param int $max_value Maximum allowed value of the field.
 * @param int $def_value Default allowed value of the field (NULL by default).
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - data are valid</li>
 * <li>{@link ERROR_INCOMPLETE_FORM} - at least one of required field is empty</li>
 * <li>{@link ERROR_INVALID_DATE_VALUE} - at least one of specified dates is invalid integer value</li>
 * <li>{@link ERROR_DATE_VALUE_OUT_OF_RANGE} - at least one of specified date values is less than {@link MIN_FIELD_DATE}, or greater than {@link MAX_FIELD_DATE}</li>
 * <li>{@link ERROR_MIN_MAX_VALUES} - maximum value is less than minimum one</li>
 * <li>{@link ERROR_DEFAULT_VALUE_OUT_OF_RANGE} - default value is less than $min_value, or greater than $max_value</li>
 * </ul>
 */
function field_validate_date ($field_name, $min_value, $max_value, $def_value = NULL)
{
    debug_write_log(DEBUG_TRACE, '[field_validate_date]');
    debug_write_log(DEBUG_DUMP,  '[field_validate_date] $field_name = ' . $field_name);
    debug_write_log(DEBUG_DUMP,  '[field_validate_date] $min_value  = ' . $min_value);
    debug_write_log(DEBUG_DUMP,  '[field_validate_date] $max_value  = ' . $max_value);
    debug_write_log(DEBUG_DUMP,  '[field_validate_date] $def_value  = ' . $def_value);

    // Check that field name and specified values are not empty.
    if (ustrlen($field_name) == 0 ||
        ustrlen($min_value)  == 0 ||
        ustrlen($max_value)  == 0)
    {
        debug_write_log(DEBUG_NOTICE, '[field_validate_date] At least one required field is empty.');
        return ERROR_INCOMPLETE_FORM;
    }

    // Check that specified values are integer.
    if (!is_intvalue($min_value) ||
        !is_intvalue($max_value) ||
        (!is_null($def_value) && !is_intvalue($def_value)))
    {
        debug_write_log(DEBUG_NOTICE, '[field_validate_date] Invalid integer value.');
        return ERROR_INVALID_DATE_VALUE;
    }

    // Check that specified values are in the range of valid values.
    if ($min_value < MIN_FIELD_DATE || $min_value > MAX_FIELD_DATE ||
        $max_value < MIN_FIELD_DATE || $max_value > MAX_FIELD_DATE)
    {
        debug_write_log(DEBUG_NOTICE, '[field_validate_date] Integer value is out of range.');
        return ERROR_DATE_VALUE_OUT_OF_RANGE;
    }

    // Check that minimum value is less than maximum one.
    if ($min_value > $max_value)
    {
        debug_write_log(DEBUG_NOTICE, '[field_validate_date] Minimum value is greater then maximum one.');
        return ERROR_MIN_MAX_VALUES;
    }

    // Check that default value is in the range between minimum and maximum ones.
    if (!is_null($def_value) &&
        ($def_value < $min_value || $def_value > $max_value))
    {
        debug_write_log(DEBUG_NOTICE, '[field_validate_date] Default value is out of range.');
        return ERROR_DEFAULT_VALUE_OUT_OF_RANGE;
    }

    return NO_ERROR;
}

/**
 * Validates 'Duration' field information before creation or modification.
 *
 * @param string $field_name Field name.
 * @param int $min_value Minimum allowed value of the field.
 * @param int $max_value Maximum allowed value of the field.
 * @param int $def_value Default allowed value of the field (NULL by default).
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - data are valid</li>
 * <li>{@link ERROR_INCOMPLETE_FORM} - at least one of required field is empty</li>
 * <li>{@link ERROR_INVALID_TIME_VALUE} - at least one of specified duration values is invalid</li>
 * <li>{@link ERROR_TIME_VALUE_OUT_OF_RANGE} - at least one of specified duration values is less than {@link MIN_FIELD_DURATION}, or greater than {@link MAX_FIELD_DURATION}</li>
 * <li>{@link ERROR_MIN_MAX_VALUES} - maximum value is less than minimum one</li>
 * <li>{@link ERROR_DEFAULT_VALUE_OUT_OF_RANGE} - default value is less than $min_value, or greater than $max_value</li>
 * </ul>
 */
function field_validate_duration ($field_name, $min_value, $max_value, $def_value = NULL)
{
    debug_write_log(DEBUG_TRACE, '[field_validate_duration]');
    debug_write_log(DEBUG_DUMP,  '[field_validate_duration] $field_name = ' . $field_name);
    debug_write_log(DEBUG_DUMP,  '[field_validate_duration] $min_value  = ' . $min_value);
    debug_write_log(DEBUG_DUMP,  '[field_validate_duration] $max_value  = ' . $max_value);
    debug_write_log(DEBUG_DUMP,  '[field_validate_duration] $def_value  = ' . $def_value);

    // Check that field name and specified values are not empty.
    if (ustrlen($field_name) == 0 ||
        ustrlen($min_value)  == 0 ||
        ustrlen($max_value)  == 0)
    {
        debug_write_log(DEBUG_NOTICE, '[field_validate_duration] At least one required field is empty.');
        return ERROR_INCOMPLETE_FORM;
    }

    // Convert specified minimum and maximum duration values to amount of minutes.
    $min_duration = ustr2time($min_value);
    $max_duration = ustr2time($max_value);
    $def_duration = (is_null($def_value) ? NULL : ustr2time($def_value));

    if ($min_duration == -1 ||
        $max_duration == -1 ||
        $def_duration == -1)
    {
        debug_write_log(DEBUG_NOTICE, '[field_validate_duration] Invalid duration value.');
        return ERROR_INVALID_TIME_VALUE;
    }

    // Check that specified values are in the range of valid values.
    if ($min_duration < MIN_FIELD_DURATION || $min_duration > MAX_FIELD_DURATION ||
        $max_duration < MIN_FIELD_DURATION || $max_duration > MAX_FIELD_DURATION)
    {
        debug_write_log(DEBUG_NOTICE, '[field_validate_duration] Duration value is out of range.');
        return ERROR_TIME_VALUE_OUT_OF_RANGE;
    }

    if ($min_duration > $max_duration)
    {
        debug_write_log(DEBUG_NOTICE, '[field_validate_duration] Minimum value is greater then maximum one.');
        return ERROR_MIN_MAX_VALUES;
    }

    // Check that default value is in the range between minimum and maximum ones.
    if (!is_null($def_duration) &&
        ($def_duration < $min_duration || $def_duration > $max_duration))
    {
        debug_write_log(DEBUG_NOTICE, '[field_validate_duration] Default value is out of range.');
        return ERROR_DEFAULT_VALUE_OUT_OF_RANGE;
    }

    return NO_ERROR;
}

/**
 * @ignore List items related UI must be remastered via jQueryUI.
 */
function field_create_list_items ($state_id, $field_name, $list_items)
{
    debug_write_log(DEBUG_TRACE, '[field_create_list_items]');
    debug_write_log(DEBUG_DUMP,  '[field_create_list_items] $state_id   = ' . $state_id);
    debug_write_log(DEBUG_DUMP,  '[field_create_list_items] $field_name = ' . $field_name);
    debug_write_log(DEBUG_DUMP,  '[field_create_list_items] $list_items = ' . $list_items);

    $rs = dal_query('fields/fndk.sql', $state_id, ustrtolower($field_name));

    if ($rs->rows == 0)
    {
        debug_write_log(DEBUG_ERROR, '[field_create_list_items] Field not found.');
        return ERROR_NOT_FOUND;
    }

    $field_id = $rs->fetch('field_id');

    if (DATABASE_DRIVER == DRIVER_MYSQL50)
    {
        $rs = dal_query('values/mysql/lvselall.sql', $field_id);

        while (($row = $rs->fetch()))
        {
            dal_query('values/mysql/lvdelete.sql', $field_id, $row['int_value']);
        }
    }
    else
    {
        dal_query('values/lvdelall.sql', $field_id);
    }

    $items = explode("\n", $list_items);

    foreach ($items as $item)
    {
        $item = trim($item);

        if (ustrlen($item) == 0)
        {
            debug_write_log(DEBUG_NOTICE, '[field_create_list_items] Line is empty.');
            continue;
        }

        $pos = ustrpos($item, ' ');

        if ($pos === FALSE)
        {
            debug_write_log(DEBUG_NOTICE, '[field_create_list_items] Values separator not found.');
            continue;
        }

        $int_value = ustrcut(usubstr($item, 0, $pos), ustrlen(MAXINT));
        $str_value = ustrcut(usubstr($item, $pos + 1), MAX_LISTITEM_NAME);

        if (!is_intvalue($int_value))
        {
            debug_write_log(DEBUG_NOTICE, '[field_create_list_items] Invalid integer value.');
            continue;
        }

        if ($int_value < 1 || $int_value > MAXINT)
        {
            debug_write_log(DEBUG_NOTICE, '[field_create_list_items] Integer value is out of range.');
            continue;
        }

        $rs = dal_query('values/lvfndk2.sql', $field_id, $str_value);

        if ($rs->rows != 0)
        {
            debug_write_log(DEBUG_NOTICE, '[field_create_list_items] Specified list item already exists.');
            continue;
        }

        $rs = dal_query('values/lvfndk1.sql', $field_id, $int_value);

        dal_query(($rs->rows == 0 ? 'values/lvcreate.sql' : 'values/lvmodify.sql'),
                  $field_id,
                  $int_value,
                  $str_value);
    }

    return NO_ERROR;
}

/**
 * @ignore List items related UI must be remastered via jQueryUI.
 */
function field_pickup_list_items ($field_id)
{
    debug_write_log(DEBUG_TRACE, '[field_pickup_list_items]');
    debug_write_log(DEBUG_DUMP,  '[field_pickup_list_items] $field_id = ' . $field_id);

    $list_items = NULL;

    $rs = dal_query('values/lvlist.sql', $field_id);

    while (($row = $rs->fetch()))
    {
        $list_items .= sprintf("%u %s\n", $row['int_value'], $row['str_value']);
    }

    return $list_items;
}

/**
 * Creates new field.
 *
 * @param int $template_id ID of template which new field will belong to.
 * @param int $state_id ID of state which new field will belong to.
 * @param string $field_name Field name.
 * @param int $field_type Field type.
 * @param bool $is_required Whether the field is required.
 * @param bool $add_separator If TRUE, then eTraxis will add separator '<hr>' after the field, when record is being displayed.
 * @param bool $guest_access Ability of guest access to the field values.
 * @param bool $show_in_emails Whether to show the field in email notifications.
 * @param string $description Optional field description.
 * @param string $regex_check Perl-compatible regular expression, which values of the field must conform to.
 * @param string $regex_search Perl-compatible regular expression to modify values of the field, used to be searched for  (NULL by default).
 * @param string $regex_replace Perl-compatible regular expression to modify values of the field, used to replace with (NULL by default).
 * @param int $param1 First parameter of the field, specific to its type (NULL by default).
 * @param int $param2 Second parameter of the field, specific to its type (NULL by default).
 * @param int $value_id Default value of the field, specific to its type (NULL by default).
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - field is successfully created</li>
 * <li>{@link ERROR_INCOMPLETE_FORM} - at least one of required data is empty</li>
 * <li>{@link ERROR_ALREADY_EXISTS} - field with specified name already exists</li>
 * <li>{@link ERROR_NOT_FOUND} - failure on attempt to create field</li>
 * </ul>
 */
function field_create ($template_id, $state_id, $field_name, $field_type, $is_required, $add_separator, $guest_access, $show_in_emails, $description = NULL,
                       $regex_check = NULL, $regex_search = NULL, $regex_replace = NULL,
                       $param1 = NULL, $param2 = NULL, $value_id = NULL)
{
    debug_write_log(DEBUG_TRACE, '[field_create]');
    debug_write_log(DEBUG_DUMP,  '[field_create] $template_id    = ' . $template_id);
    debug_write_log(DEBUG_DUMP,  '[field_create] $state_id       = ' . $state_id);
    debug_write_log(DEBUG_DUMP,  '[field_create] $field_name     = ' . $field_name);
    debug_write_log(DEBUG_DUMP,  '[field_create] $field_type     = ' . $field_type);
    debug_write_log(DEBUG_DUMP,  '[field_create] $is_required    = ' . $is_required);
    debug_write_log(DEBUG_DUMP,  '[field_create] $add_separator  = ' . $add_separator);
    debug_write_log(DEBUG_DUMP,  '[field_create] $guest_access   = ' . $guest_access);
    debug_write_log(DEBUG_DUMP,  '[field_create] $show_in_emails = ' . $show_in_emails);
    debug_write_log(DEBUG_DUMP,  '[field_create] $description    = ' . $description);
    debug_write_log(DEBUG_DUMP,  '[field_create] $regex_check    = ' . $regex_check);
    debug_write_log(DEBUG_DUMP,  '[field_create] $regex_search   = ' . $regex_search);
    debug_write_log(DEBUG_DUMP,  '[field_create] $regex_replace  = ' . $regex_replace);
    debug_write_log(DEBUG_DUMP,  '[field_create] $param1         = ' . $param1);
    debug_write_log(DEBUG_DUMP,  '[field_create] $param2         = ' . $param2);
    debug_write_log(DEBUG_DUMP,  '[field_create] $value_id       = ' . $value_id);

    // Check that field name is not empty.
    if (ustrlen($field_name) == 0)
    {
        debug_write_log(DEBUG_NOTICE, '[field_create] At least one required field is empty.');
        return ERROR_INCOMPLETE_FORM;
    }

    // Check that there is no field with the same name in the specified state.
    $rs = dal_query('fields/fndk.sql', $state_id, ustrtolower($field_name));

    if ($rs->rows != 0)
    {
        debug_write_log(DEBUG_NOTICE, '[field_create] Field already exists.');
        return ERROR_ALREADY_EXISTS;
    }

    // Calculates field order.
    $rs = dal_query('fields/count.sql', $state_id);
    $field_order = $rs->fetch(0) + 1;

    // Create a field.
    dal_query('fields/create.sql',
              $template_id,
              $state_id,
              $field_name,
              $field_order,
              $field_type,
              bool2sql($is_required),
              bool2sql($add_separator),
              bool2sql($show_in_emails),
              bool2sql($guest_access),
              ustrlen($description)   == 0 ? NULL : $description,
              ustrlen($regex_check)   == 0 ? NULL : $regex_check,
              ustrlen($regex_search)  == 0 ? NULL : $regex_search,
              ustrlen($regex_replace) == 0 ? NULL : $regex_replace,
              is_null($param1)             ? NULL : $param1,
              is_null($param2)             ? NULL : $param2,
              is_null($value_id)           ? NULL : $value_id);

    $rs = dal_query('fields/fndk.sql', $state_id, ustrtolower($field_name));

    if ($rs->rows == 0)
    {
        debug_write_log(DEBUG_NOTICE, '[field_create] Field cannot be found.');
        return ERROR_NOT_FOUND;
    }

    $row = $rs->fetch();

    // Give permission to read to all local project groups.
    dal_query('fields/fpaddall.sql',
              $row['project_id'],
              $row['field_id'],
              FIELD_ALLOW_TO_READ);

    // Give permission to write to all local project groups.
    dal_query('fields/fpaddall.sql',
              $row['project_id'],
              $row['field_id'],
              FIELD_ALLOW_TO_WRITE);

    return NO_ERROR;
}

/**
 * Modifies specified field.
 *
 * @param int $id ID of field to be modified.
 * @param int $state_id ID of the state which the field belongs to.
 * @param int $state_name Name of the state which the field belongs to.
 * @param string $field_old_name Current field name.
 * @param string $field_new_name New field name.
 * @param int $field_old_order Current field order.
 * @param int $field_new_order New field order.
 * @param int $field_type Current field type (field type is not modifiable).
 * @param bool $is_required Whether the field is required.
 * @param bool $add_separator If TRUE, then eTraxis will add separator '<hr>' after the field, when record is being displayed.
 * @param bool $guest_access Ability of guest access to the field values.
 * @param bool $show_in_emails Whether to show the field in email notifications.
 * @param string $description Optional field description.
 * @param string $regex_check New perl-compatible regular expression, which values of the field must conform to.
 * @param string $regex_search New perl-compatible regular expression to modify values of the field, used to be searched for  (NULL by default).
 * @param string $regex_replace New perl-compatible regular expression to modify values of the field, used to replace with (NULL by default).
 * @param int $param1 New first parameter of the field, specific to its type (NULL by default).
 * @param int $param2 New second parameter of the field, specific to its type (NULL by default).
 * @param int $value_id New default value of the field, specific to its type (NULL by default).
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - field is successfully modified</li>
 * <li>{@link ERROR_INCOMPLETE_FORM} - at least one of required data is empty</li>
 * <li>{@link ERROR_ALREADY_EXISTS} - another field with specified name already exists</li>
 * </ul>
 */
function field_modify ($id, $state_id, $state_name, $field_old_name, $field_new_name, $field_old_order, $field_new_order, $field_type, $is_required, $add_separator, $guest_access, $show_in_emails, $description = NULL,
                       $regex_check = NULL, $regex_search = NULL, $regex_replace = NULL,
                       $param1 = NULL, $param2 = NULL, $value_id = NULL)
{
    debug_write_log(DEBUG_TRACE, '[field_modify]');
    debug_write_log(DEBUG_DUMP,  '[field_modify] $id              = ' . $id);
    debug_write_log(DEBUG_DUMP,  '[field_modify] $state_id        = ' . $state_id);
    debug_write_log(DEBUG_DUMP,  '[field_modify] $state_name      = ' . $state_name);
    debug_write_log(DEBUG_DUMP,  '[field_modify] $field_old_name  = ' . $field_old_name);
    debug_write_log(DEBUG_DUMP,  '[field_modify] $field_new_name  = ' . $field_new_name);
    debug_write_log(DEBUG_DUMP,  '[field_modify] $field_old_order = ' . $field_old_order);
    debug_write_log(DEBUG_DUMP,  '[field_modify] $field_new_order = ' . $field_new_order);
    debug_write_log(DEBUG_DUMP,  '[field_modify] $field_type      = ' . $field_type);
    debug_write_log(DEBUG_DUMP,  '[field_modify] $is_required     = ' . $is_required);
    debug_write_log(DEBUG_DUMP,  '[field_modify] $add_separator   = ' . $add_separator);
    debug_write_log(DEBUG_DUMP,  '[field_modify] $guest_access    = ' . $guest_access);
    debug_write_log(DEBUG_DUMP,  '[field_modify] $show_in_emails  = ' . $show_in_emails);
    debug_write_log(DEBUG_DUMP,  '[field_modify] $description     = ' . $description);
    debug_write_log(DEBUG_DUMP,  '[field_modify] $regex_check     = ' . $regex_check);
    debug_write_log(DEBUG_DUMP,  '[field_modify] $regex_search    = ' . $regex_search);
    debug_write_log(DEBUG_DUMP,  '[field_modify] $regex_replace   = ' . $regex_replace);
    debug_write_log(DEBUG_DUMP,  '[field_modify] $param1          = ' . $param1);
    debug_write_log(DEBUG_DUMP,  '[field_modify] $param2          = ' . $param2);
    debug_write_log(DEBUG_DUMP,  '[field_modify] $value_id        = ' . $value_id);

    // Check that field name is not empty.
    if (ustrlen($field_new_name) == 0)
    {
        debug_write_log(DEBUG_NOTICE, '[field_modify] At least one required field is empty.');
        return ERROR_INCOMPLETE_FORM;
    }

    // Check that there is no field with the same name, besides this one.
    $rs = dal_query('fields/fndku.sql', $id, $state_id, ustrtolower($field_new_name));

    if ($rs->rows != 0)
    {
        debug_write_log(DEBUG_NOTICE, '[field_modify] Field already exists.');
        return ERROR_ALREADY_EXISTS;
    }

    // Reorder field.
    $rs = dal_query('fields/count.sql', $state_id);

    if ($field_new_order < 1 || $field_new_order > $rs->fetch(0))
    {
        debug_write_log(DEBUG_NOTICE, '[field_modify] Field order is out of range.');
    }
    elseif ($field_old_order < $field_new_order)
    {
        debug_write_log(DEBUG_NOTICE, '[field_modify] Field order is being changed (going down).');

        dal_query('fields/setorder.sql', $state_id, $field_old_order, 0);

        for ($i = $field_old_order; $i < $field_new_order; $i++)
        {
            dal_query('fields/setorder.sql', $state_id, $i + 1, $i);
        }

        dal_query('fields/setorder.sql', $state_id, 0, $field_new_order);
    }
    elseif ($field_old_order > $field_new_order)
    {
        debug_write_log(DEBUG_NOTICE, '[field_modify] Field order is being changed (going up).');

        dal_query('fields/setorder.sql', $state_id, $field_old_order, 0);

        for ($i = $field_old_order; $i > $field_new_order; $i--)
        {
            dal_query('fields/setorder.sql', $state_id, $i - 1, $i);
        }

        dal_query('fields/setorder.sql', $state_id, 0, $field_new_order);
    }

    // Update existing views.
    dal_query('fields/views.sql',
              $state_name,
              $field_old_name,
              $field_new_name);

    // Modify the field.
    dal_query('fields/modify.sql',
              $id,
              $field_new_name,
              bool2sql($is_required),
              bool2sql($add_separator),
              bool2sql($guest_access),
              bool2sql($show_in_emails),
              ustrlen($description)   == 0 ? NULL : $description,
              ustrlen($regex_check)   == 0 ? NULL : $regex_check,
              ustrlen($regex_search)  == 0 ? NULL : $regex_search,
              ustrlen($regex_replace) == 0 ? NULL : $regex_replace,
              is_null($param1)             ? NULL : $param1,
              is_null($param2)             ? NULL : $param2,
              is_null($value_id)           ? NULL : $value_id);

    return NO_ERROR;
}

/**
 * Checks whether field can be deleted.
 *
 * @param int $id ID of field to be deleted.
 * @return bool TRUE if field can be deleted, FALSE otherwise.
 */
function is_field_removable ($id)
{
    debug_write_log(DEBUG_TRACE, '[is_field_removable]');
    debug_write_log(DEBUG_DUMP,  '[is_field_removable] $id = ' . $id);

    $rs = dal_query('fields/fvfndc.sql', $id);

    return ($rs->fetch(0) == 0);
}

/**
 * Deletes specified field if it's removable, or disables the field otherwise.
 *
 * @param int $id ID of field to be deleted.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - field is successfully deleted/disabled</li>
 * <li>{@link ERROR_NOT_FOUND} - specified field cannot be found</li>
 * </ul>
 */
function field_delete ($id)
{
    debug_write_log(DEBUG_TRACE, '[field_delete]');
    debug_write_log(DEBUG_DUMP,  '[field_delete] $id = ' . $id);

    // Find field in database.
    $field = field_find($id);

    if (!$field)
    {
        debug_write_log(DEBUG_NOTICE, '[field_delete] Field cannot be found.');
        return ERROR_NOT_FOUND;
    }

    // Get current number of fields.
    $rs = dal_query('fields/count.sql', $field['state_id']);
    $last_field = $rs->fetch(0);

    if (is_field_removable($id))
    {
        // Delete all related data.
        dal_query('fields/lvdelall.sql', $id);
        dal_query('fields/ffdelall.sql', $id);
        dal_query('fields/fpdelall.sql', $id);
        dal_query('fields/delete.sql',   $id);
    }
    else
    {
        // Disable the field.
        dal_query('fields/disable.sql', $id, time());
    }

    // Reorder rest of fields in the same state.
    for ($i = $field['field_order']; $i < $last_field; $i++)
    {
        dal_query('fields/setorder.sql', $field['state_id'], $i + 1, $i);
    }

    return NO_ERROR;
}

/**
 * Sets permissions of system role 'author' for specified field.
 *
 * @param int $fid ID of field which permissions should be set for.
 * @param int $perm New permissions set.
 * @return int Always {@link NO_ERROR}.
 */
function field_author_permission_set ($fid, $perm)
{
    debug_write_log(DEBUG_TRACE, '[field_author_permission_set]');
    debug_write_log(DEBUG_DUMP,  '[field_author_permission_set] $fid  = ' . $fid);
    debug_write_log(DEBUG_DUMP,  '[field_author_permission_set] $perm = ' . $perm);

    dal_query('fields/apset.sql', $fid, $perm);

    return NO_ERROR;
}

/**
 * Sets permissions of system role 'responsible' for specified field.
 *
 * @param int $fid ID of field which permissions should be set for.
 * @param int $perm New permissions set.
 * @return int Always {@link NO_ERROR}.
 */
function field_responsible_permission_set ($fid, $perm)
{
    debug_write_log(DEBUG_TRACE, '[field_responsible_permission_set]');
    debug_write_log(DEBUG_DUMP,  '[field_responsible_permission_set] $fid  = ' . $fid);
    debug_write_log(DEBUG_DUMP,  '[field_responsible_permission_set] $perm = ' . $perm);

    dal_query('fields/rpset.sql', $fid, $perm);

    return NO_ERROR;
}

/**
 * Sets permissions of system role 'registered' for specified field.
 *
 * @param int $fid ID of field which permissions should be set for.
 * @param int $perm New permissions set.
 * @return int Always {@link NO_ERROR}.
 */
function field_registered_permission_set ($fid, $perm)
{
    debug_write_log(DEBUG_TRACE, '[field_registered_permission_set]');
    debug_write_log(DEBUG_DUMP,  '[field_registered_permission_set] $fid  = ' . $fid);
    debug_write_log(DEBUG_DUMP,  '[field_registered_permission_set] $perm = ' . $perm);

    dal_query('fields/r2pset.sql', $fid, $perm);

    return NO_ERROR;
}

/**
 * Gives to specified group a specified permission for specified field.
 *
 * @param int $fid Field ID.
 * @param int $gid Group ID.
 * @param int $perm Permission (exactly one of the following):
 * <ul>
 * <li>{@link FIELD_ALLOW_TO_READ}</li>
 * <li>{@link FIELD_ALLOW_TO_WRITE}</li>
 * </ul>
 * @return int Always {@link NO_ERROR}.
 */
function field_permission_add ($fid, $gid, $perm)
{
    debug_write_log(DEBUG_TRACE, '[field_permission_add]');
    debug_write_log(DEBUG_DUMP,  '[field_permission_add] $fid  = ' . $fid);
    debug_write_log(DEBUG_DUMP,  '[field_permission_add] $gid  = ' . $gid);
    debug_write_log(DEBUG_DUMP,  '[field_permission_add] $perm = ' . $perm);

    dal_query('fields/fpadd.sql', $fid, $gid, $perm);

    return NO_ERROR;
}

/**
 * Revokes from specified group all permissions for specified field.
 *
 * @param int $fid Field ID.
 * @param int $gid Group ID.
 * @return int Always {@link NO_ERROR}.
 */
function field_permission_remove ($fid, $gid)
{
    debug_write_log(DEBUG_TRACE, '[field_permission_remove]');
    debug_write_log(DEBUG_DUMP,  '[field_permission_remove] $fid  = ' . $fid);
    debug_write_log(DEBUG_DUMP,  '[field_permission_remove] $gid  = ' . $gid);

    dal_query('fields/fpremove.sql', $fid, $gid);

    return NO_ERROR;
}

/**
 * Exports all fields of the specified state to XML code (see also {@link state_export}).
 *
 * @param int $id ID of state, which fields should be exported.
 * @param array &$groups Array of IDs of groups, affected by this template (used for output only).
 * @return string Generated XML code.
 */
function field_export ($id, &$groups)
{
    debug_write_log(DEBUG_TRACE, '[field_export]');
    debug_write_log(DEBUG_DUMP,  '[field_export] $id = ' . $id);

    // Allocation of field types to XML code.
    $types = array
    (
        FIELD_TYPE_NUMBER     => 'number',
        FIELD_TYPE_FLOAT      => 'float',
        FIELD_TYPE_STRING     => 'string',
        FIELD_TYPE_MULTILINED => 'multi',
        FIELD_TYPE_CHECKBOX   => 'check',
        FIELD_TYPE_LIST       => 'list',
        FIELD_TYPE_RECORD     => 'record',
        FIELD_TYPE_DATE       => 'date',
        FIELD_TYPE_DURATION   => 'duration',
    );

    // List all fields of the state.
    $rs = dal_query('fields/list.sql', $id, 'field_order');

    if ($rs->rows == 0)
    {
        return NULL;
    }

    $xml = "        <fields>\n";

    // Add XML code for each found field.
    while (($field = $rs->fetch()))
    {
        // Add XML code for general field information.
        $xml .= sprintf("          <field name=\"%s\" type=\"%s\" required=\"%s\" guest_access=\"%s\" separator=\"%s\" show_in_emails=\"%s\"",
                        ustr2html($field['field_name']),
                        $types[$field['field_type']],
                        ($field['is_required']    ? 'yes' : 'no'),
                        ($field['guest_access']   ? 'yes' : 'no'),
                        ($field['add_separator']  ? 'yes' : 'no'),
                        ($field['show_in_emails'] ? 'yes' : 'no'));

        // Add XML code for information, specific to type of the field.
        switch ($field['field_type'])
        {
            case FIELD_TYPE_NUMBER:
                $xml .= sprintf(' minimum="%d" maximum="%d"', $field['param1'], $field['param2']);
                break;

            case FIELD_TYPE_FLOAT:
                $xml .= sprintf(' minimum="%.10f" maximum="%.10f"',
                                value_find(FIELD_TYPE_FLOAT, $field['param1']),
                                value_find(FIELD_TYPE_FLOAT, $field['param2']));
                break;

            case FIELD_TYPE_STRING:
            case FIELD_TYPE_MULTILINED:
                $xml .= sprintf(' length="%u"', $field['param1']);
                break;

            case FIELD_TYPE_DATE:
                $xml .= sprintf(' minimum="%s" maximum="%s"', $field['param1'], $field['param2']);
                break;

            case FIELD_TYPE_DURATION:
                $xml .= sprintf(' minimum="%s" maximum="%s"', time2ustr($field['param1']), time2ustr($field['param2']));
                break;

            default: ;  // nop
        }

        // If default value is specified, add XML code for it.
        if (!is_null($field['value_id']))
        {
            switch ($field['field_type'])
            {
                case FIELD_TYPE_NUMBER:
                    $xml .= sprintf(' default="%d"', $field['value_id']);
                    break;

                case FIELD_TYPE_FLOAT:
                    $xml .= sprintf(' default="%.10f"', value_find(FIELD_TYPE_FLOAT, $field['value_id']));
                    break;

                case FIELD_TYPE_LIST:
                    $xml .= sprintf(' default="%u"', $field['value_id']);
                    break;

                case FIELD_TYPE_STRING:
                    $xml .= sprintf(' default="%s"', ustr2html(value_find(FIELD_TYPE_STRING, $field['value_id'])));
                    break;

                case FIELD_TYPE_CHECKBOX:
                    $xml .= sprintf(' default="%u"', ($field['value_id'] ? 'on' : 'off'));
                    break;

                case FIELD_TYPE_DATE:
                    $xml .= sprintf(' default="%s"', $field['value_id']);
                    break;

                case FIELD_TYPE_DURATION:
                    $xml .= sprintf(' default="%s"', time2ustr($field['value_id']));
                    break;

                default: ;  // nop
            }
        }

        // If RegEx values are specified, add XML code for them.
        if ($field['field_type'] == FIELD_TYPE_STRING ||
            $field['field_type'] == FIELD_TYPE_MULTILINED)
        {
            if (!is_null($field['regex_check']))
            {
                $xml .= sprintf(' regex_check="%s"', ustr2html($field['regex_check']));
            }

            if (!is_null($field['regex_search']))
            {
                $xml .= sprintf(' regex_search="%s"', ustr2html($field['regex_search']));
            }

            if (!is_null($field['regex_replace']))
            {
                $xml .= sprintf(' regex_replace="%s"', ustr2html($field['regex_replace']));
            }
        }

        $xml .= ">\n";

        // Default value of 'multilined' field is processed in a specific way (must be out in dedicated XML tags).
        if ($field['field_type'] == FIELD_TYPE_MULTILINED)
        {
            $default = value_find(FIELD_TYPE_MULTILINED, $field['value_id']);

            if (!is_null($default))
            {
                $xml .= "            <default>\n";
                $xml .= ustr2html($default) . "\n";
                $xml .= "            </default>\n";
            }
        }

        // Description of field is processed in a specific way (must be out in dedicated XML tags).
        if (strlen($field['description']) != 0)
        {
            $xml .= "            <description>\n";
            $xml .= ustr2html($field['description']) . "\n";
            $xml .= "            </description>\n";
        }

        // If field type is 'list', enumerate all its items.
        if ($field['field_type'] == FIELD_TYPE_LIST)
        {
            $rsl = dal_query('values/lvlist.sql', $field['field_id']);

            if ($rsl->rows != 0)
            {
                $xml .= "            <list>\n";

                while (($item = $rsl->fetch()))
                {
                    $xml .= "              <item value=\"{$item['int_value']}\">" . ustr2html($item['str_value']) . "</item>\n";
                }

                $xml .= "            </list>\n";
            }
        }

        // Enumerate permissions of all groups for this field.
        $rsp = dal_query('fields/fplist.sql', $field['field_id']);

        if ($field['author_perm']      != 0 ||
            $field['responsible_perm'] != 0 ||
            $field['registered_perm']  != 0 ||
            $rsp->rows                 != 0)
        {
            $xml .= "            <permissions>\n";

            if     ($field['author_perm'] == 1)
            {
                $xml .= "              <author>read</author>\n";
            }
            elseif ($field['author_perm'] == 2)
            {
                $xml .= "              <author>write</author>\n";
            }

            if     ($field['responsible_perm'] == 1)
            {
                $xml .= "              <responsible>read</responsible>\n";
            }
            elseif ($field['responsible_perm'] == 2)
            {
                $xml .= "              <responsible>write</responsible>\n";
            }

            if     ($field['registered_perm'] == 1)
            {
                $xml .= "              <registered>read</registered>\n";
            }
            elseif ($field['registered_perm'] == 2)
            {
                $xml .= "              <registered>write</registered>\n";
            }

            while (($group = $rsp->fetch()))
            {
                array_push($groups, $group['group_id']);

                $xml .= sprintf("              <group name=\"%s\" type=\"%s\">",
                                ustr2html($group['group_name']),
                                (is_null($group['project_id']) ? 'global' : 'local'));

                $xml .= ($group['perms'] == FIELD_ALLOW_TO_WRITE ? 'write' : 'read');
                $xml .= "</group>\n";
            }

            $xml .= "            </permissions>\n";
        }

        $xml .= "          </field>\n";
    }

    $xml .= "        </fields>\n";

    return $xml;
}

/**
 * Imports fields described as XML code into the specified state.
 *
 * @param int $template_id ID of destination template.
 * @param int $state_id ID of destination state.
 * @param string $xml Valid XML code.
 * @param string &$error In case of failure - the error message (used as output only).
 * @return bool Whether the import was successful.
 */
function fields_import ($template_id, $state_id, $xml, &$error)
{
    debug_write_log(DEBUG_TRACE, '[fields_import]');
    debug_write_log(DEBUG_DUMP,  '[fields_import] $template_id = ' . $template_id);
    debug_write_log(DEBUG_DUMP,  '[fields_import] $state_id    = ' . $state_id);

    // Allocation of XML code to field types.
    $types = array
    (
        'number'   => FIELD_TYPE_NUMBER,
        'float'    => FIELD_TYPE_FLOAT,
        'string'   => FIELD_TYPE_STRING,
        'multi'    => FIELD_TYPE_MULTILINED,
        'check'    => FIELD_TYPE_CHECKBOX,
        'list'     => FIELD_TYPE_LIST,
        'record'   => FIELD_TYPE_RECORD,
        'date'     => FIELD_TYPE_DATE,
        'duration' => FIELD_TYPE_DURATION,
    );

    // Enumerate fields.
    $fields = $xml->xpath('./fields/field');

    if ($fields !== FALSE)
    {
        foreach ($fields as $field)
        {
            $field_type = strval($field['type']);

            if (!array_key_exists($field_type, $types))
            {
                continue;
            }

            $field_name     = ustrcut($field['name'], MAX_FIELD_NAME);
            $field_type     = $types[$field_type];

            $is_required    = ($field['required']       == 'yes');
            $add_separator  = ($field['separator']      == 'yes');
            $guest_access   = ($field['guest_access']   == 'yes');
            $show_in_emails = ($field['show_in_emails'] == 'yes');

            $description    = isset($field->description)
                            ? ustrcut($field->description, MAX_FIELD_DESCRIPTION)
                            : NULL;

            $regex_check    = NULL;
            $regex_search   = NULL;
            $regex_replace  = NULL;
            $param1         = NULL;
            $param2         = NULL;
            $default        = NULL;

            // Validate field (number).
            if ($field_type == FIELD_TYPE_NUMBER)
            {
                $param1  = ustrcut($field['minimum'], ustrlen(MAX_FIELD_INTEGER) + 1);
                $param2  = ustrcut($field['maximum'], ustrlen(MAX_FIELD_INTEGER) + 1);
                $default = ustrcut($field['default'], ustrlen(MAX_FIELD_INTEGER) + 1);

                $default = (ustrlen($default) == 0) ? NULL : intval($default);

                switch (field_validate_number($field_name, $param1, $param2, $default))
                {
                    case NO_ERROR:
                        break;  // nop
                    case ERROR_INCOMPLETE_FORM:
                        $error = get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID);
                        return FALSE;
                    case ERROR_INVALID_INTEGER_VALUE:
                        $error = get_html_resource(RES_ALERT_INVALID_INTEGER_VALUE_ID);
                        return FALSE;
                    case ERROR_INTEGER_VALUE_OUT_OF_RANGE:
                        $error = ustrprocess(get_html_resource(RES_ALERT_INTEGER_VALUE_OUT_OF_RANGE_ID), -MAX_FIELD_INTEGER, +MAX_FIELD_INTEGER);
                        return FALSE;
                    case ERROR_MIN_MAX_VALUES:
                        $error = get_html_resource(RES_ALERT_MIN_MAX_VALUES_ID);
                        return FALSE;
                    case ERROR_DEFAULT_VALUE_OUT_OF_RANGE:
                        $error = ustrprocess(get_html_resource(RES_ALERT_DEFAULT_VALUE_OUT_OF_RANGE_ID), $param1, $param2);
                        return FALSE;
                    default:
                        debug_write_log(DEBUG_WARNING, '[fields_import] Field validation failure (number).');
                        $error = get_html_resource(RES_ALERT_UNKNOWN_ERROR_ID);
                        return FALSE;
                }
            }

            // Validate field (float).
            elseif ($field_type == FIELD_TYPE_FLOAT)
            {
                $param1  = ustrcut($field['minimum'], ustrlen(MIN_FIELD_FLOAT));
                $param2  = ustrcut($field['maximum'], ustrlen(MAX_FIELD_FLOAT));
                $default = ustrcut($field['default'], ustrlen(MAX_FIELD_FLOAT));

                $default = (ustrlen($default) == 0) ? NULL : $default;

                switch (field_validate_float($field_name, $param1, $param2, $default))
                {
                    case NO_ERROR:
                        $param1  = value_find_float($param1);
                        $param2  = value_find_float($param2);
                        $default = is_null($default) ? NULL : value_find_float($default);
                        break;  // nop
                    case ERROR_INCOMPLETE_FORM:
                        $error = get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID);
                        return FALSE;
                    case ERROR_INVALID_FLOAT_VALUE:
                        $error = get_html_resource(RES_ALERT_INVALID_DECIMAL_VALUE_ID);
                        return FALSE;
                    case ERROR_FLOAT_VALUE_OUT_OF_RANGE:
                        $error = ustrprocess(get_html_resource(RES_ALERT_DECIMAL_VALUE_OUT_OF_RANGE_ID), MIN_FIELD_FLOAT, MAX_FIELD_FLOAT);
                        return FALSE;
                    case ERROR_MIN_MAX_VALUES:
                        $error = get_html_resource(RES_ALERT_MIN_MAX_VALUES_ID);
                        return FALSE;
                    case ERROR_DEFAULT_VALUE_OUT_OF_RANGE:
                        $error = ustrprocess(get_html_resource(RES_ALERT_DEFAULT_VALUE_OUT_OF_RANGE_ID), $param1, $param2);
                        return FALSE;
                    default:
                        debug_write_log(DEBUG_WARNING, '[fields_import] Field validation failure (float).');
                        $error = get_html_resource(RES_ALERT_UNKNOWN_ERROR_ID);
                        return FALSE;
                }
            }

            // Validate field (string).
            elseif ($field_type == FIELD_TYPE_STRING)
            {
                $regex_check   = ustrcut($field['regex_check'],   MAX_FIELD_REGEX);
                $regex_search  = ustrcut($field['regex_search'],  MAX_FIELD_REGEX);
                $regex_replace = ustrcut($field['regex_replace'], MAX_FIELD_REGEX);
                $param1        = ustrcut($field['length'],        ustrlen(MAX_FIELD_STRING));

                switch (field_validate_string($field_name, $param1))
                {
                    case NO_ERROR:
                        $default = ustrcut($field['default'], $param1);
                        $default = (ustrlen($default) == 0) ? NULL : value_find_string($default);
                        break;  // nop
                    case ERROR_INCOMPLETE_FORM:
                        $error = get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID);
                        return FALSE;
                    case ERROR_INVALID_INTEGER_VALUE:
                        $error = get_html_resource(RES_ALERT_INVALID_INTEGER_VALUE_ID);
                        return FALSE;
                    case ERROR_INTEGER_VALUE_OUT_OF_RANGE:
                        $error = ustrprocess(get_html_resource(RES_ALERT_INTEGER_VALUE_OUT_OF_RANGE_ID), 1, MAX_FIELD_STRING);
                        return FALSE;
                    default:
                        debug_write_log(DEBUG_WARNING, '[fields_import] Field validation failure (string).');
                        $error = get_html_resource(RES_ALERT_UNKNOWN_ERROR_ID);
                        return FALSE;
                }
            }

            // Validate field (multilined).
            elseif ($field_type == FIELD_TYPE_MULTILINED)
            {
                $regex_check   = ustrcut($field['regex_check'],   MAX_FIELD_REGEX);
                $regex_search  = ustrcut($field['regex_search'],  MAX_FIELD_REGEX);
                $regex_replace = ustrcut($field['regex_replace'], MAX_FIELD_REGEX);
                $param1        = ustrcut($field['length'],        ustrlen(MAX_FIELD_MULTILINED));

                switch (field_validate_multilined($field_name, $param1))
                {
                    case NO_ERROR:
                        if (isset($field->default))
                        {
                            $default = ustrcut($field->default, $param1);
                            $default = (ustrlen($default) == 0) ? NULL : value_find_multilined($default);
                        }
                        break;  // nop
                    case ERROR_INCOMPLETE_FORM:
                        $error = get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID);
                        return FALSE;
                    case ERROR_INVALID_INTEGER_VALUE:
                        $error = get_html_resource(RES_ALERT_INVALID_INTEGER_VALUE_ID);
                        return FALSE;
                    case ERROR_INTEGER_VALUE_OUT_OF_RANGE:
                        $error = ustrprocess(get_html_resource(RES_ALERT_INTEGER_VALUE_OUT_OF_RANGE_ID), 1, MAX_FIELD_MULTILINED);
                        return FALSE;
                    default:
                        debug_write_log(DEBUG_WARNING, '[fields_import] Field validation failure (multilined).');
                        $error = get_html_resource(RES_ALERT_UNKNOWN_ERROR_ID);
                        return FALSE;
                }
            }

            // Validate field (check).
            elseif ($field_type == FIELD_TYPE_CHECKBOX)
            {
                $default = ($field['default'] == 'on') ? 1 : 0;
            }

            // Validate field (list).
            elseif ($field_type == FIELD_TYPE_LIST)
            {
                $default = (ustrlen($field['default']) == 0)
                         ? NULL
                         : ustr2int($field['default'], 1, MAXINT);
            }

            // Validate field (record).
            elseif ($field_type == FIELD_TYPE_RECORD)
            {
                // nop
            }

            // Validate field (date).
            elseif ($field_type == FIELD_TYPE_DATE)
            {
                $param1  = ustrcut($field['minimum'], ustrlen(MIN_FIELD_DATE));
                $param2  = ustrcut($field['maximum'], ustrlen(MIN_FIELD_DATE));
                $default = ustrcut($field['default'], ustrlen(MIN_FIELD_DATE));

                $default = (ustrlen($default) == 0) ? NULL : $default;

                switch (field_validate_date($field_name, $param1, $param2, $default))
                {
                    case NO_ERROR:
                        $default = is_null($default) ? NULL : ustr2int($default, MIN_FIELD_DATE, MAX_FIELD_DATE);
                        break;  // nop
                    case ERROR_INCOMPLETE_FORM:
                        $error = get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID);
                        return FALSE;
                    case ERROR_INVALID_DATE_VALUE:
                        $error = get_html_resource(RES_ALERT_INVALID_DATE_VALUE_ID);
                        return FALSE;
                    case ERROR_DATE_VALUE_OUT_OF_RANGE:
                        $error = ustrprocess(get_html_resource(RES_ALERT_DATE_VALUE_OUT_OF_RANGE_ID), MIN_FIELD_DATE, MAX_FIELD_DATE);
                        return FALSE;
                    case ERROR_MIN_MAX_VALUES:
                        $error = get_html_resource(RES_ALERT_MIN_MAX_VALUES_ID);
                        return FALSE;
                    case ERROR_DEFAULT_VALUE_OUT_OF_RANGE:
                        $error = ustrprocess(get_html_resource(RES_ALERT_DEFAULT_VALUE_OUT_OF_RANGE_ID), $param1, $param2);
                        return FALSE;
                    default:
                        debug_write_log(DEBUG_WARNING, '[fields_import] Field validation failure (date).');
                        $error = get_html_resource(RES_ALERT_UNKNOWN_ERROR_ID);
                        return FALSE;
                }
            }

            // Validate field (duration).
            elseif ($field_type == FIELD_TYPE_DURATION)
            {
                $param1  = ustrcut($field['minimum'], ustrlen(time2ustr(MAX_FIELD_DURATION)));
                $param2  = ustrcut($field['maximum'], ustrlen(time2ustr(MAX_FIELD_DURATION)));
                $default = ustrcut($field['default'], ustrlen(time2ustr(MAX_FIELD_DURATION)));

                $default = (ustrlen($default) == 0) ? NULL : $default;

                switch (field_validate_duration($field_name, $param1, $param2, $default))
                {
                    case NO_ERROR:
                        $param1  = ustr2time($param1);
                        $param2  = ustr2time($param2);
                        $default = is_null($default) ? NULL : ustr2time($default);
                        break;  // nop
                    case ERROR_INCOMPLETE_FORM:
                        $error = get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID);
                        return FALSE;
                    case ERROR_INVALID_TIME_VALUE:
                        $error = get_html_resource(RES_ALERT_INVALID_TIME_VALUE_ID);
                        return FALSE;
                    case ERROR_TIME_VALUE_OUT_OF_RANGE:
                        $error = ustrprocess(get_html_resource(RES_ALERT_TIME_VALUE_OUT_OF_RANGE_ID), time2ustr(MIN_FIELD_DURATION), time2ustr(MAX_FIELD_DURATION));
                        return FALSE;
                    case ERROR_MIN_MAX_VALUES:
                        $error = get_html_resource(RES_ALERT_MIN_MAX_VALUES_ID);
                        return FALSE;
                    case ERROR_DEFAULT_VALUE_OUT_OF_RANGE:
                        $error = ustrprocess(get_html_resource(RES_ALERT_DEFAULT_VALUE_OUT_OF_RANGE_ID), $param1, $param2);
                        return FALSE;
                    default:
                        debug_write_log(DEBUG_WARNING, '[fields_import] Field validation failure (duration).');
                        $error = get_html_resource(RES_ALERT_UNKNOWN_ERROR_ID);
                        return FALSE;
                }
            }

            // Create field.
            switch (field_create($template_id,
                                 $state_id,
                                 $field_name,
                                 $field_type,
                                 $is_required,
                                 $add_separator,
                                 $guest_access,
                                 $show_in_emails,
                                 $description,
                                 $regex_check,
                                 $regex_search,
                                 $regex_replace,
                                 $param1,
                                 $param2,
                                 $default))
            {
                case NO_ERROR:
                    break;  // nop
                case ERROR_INCOMPLETE_FORM:
                    $error = get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID);
                    return FALSE;
                case ERROR_ALREADY_EXISTS:
                    $error = get_html_resource(RES_ALERT_FIELD_ALREADY_EXISTS_ID);
                    return FALSE;
                default:
                    debug_write_log(DEBUG_WARNING, '[fields_import] Field validation failure.');
                    $error = get_html_resource(RES_ALERT_UNKNOWN_ERROR_ID);
                    return FALSE;
            }

            $rs = dal_query('fields/fndk.sql', $state_id, ustrtolower($field_name));

            if ($rs->rows == 0)
            {
                debug_write_log(DEBUG_WARNING, '[fields_import] Created field not found.');
                $error = get_html_resource(RES_ALERT_UNKNOWN_ERROR_ID);
                return FALSE;
            }

            $row        = $rs->fetch();
            $project_id = $row['project_id'];
            $field_id   = $row['field_id'];

            // Create list items fpr list field.
            if ($field_type == FIELD_TYPE_LIST)
            {
                $list = $field->xpath('./list/item');

                if ($list !== FALSE)
                {
                    $list_items = NULL;

                    foreach ($list as $item)
                    {
                        $list_items .= sprintf("%s %s\n", $item['value'], $item);
                    }

                    field_create_list_items($state_id, $field_name, $list_items);
                }
            }

            // Set author permissions.
            if (isset($field->permissions->author))
            {
                if ($field->permissions->author == 'read')
                {
                    field_author_permission_set($field_id, FIELD_ALLOW_TO_READ);
                }
                elseif ($field->permissions->author == 'write')
                {
                    field_author_permission_set($field_id, FIELD_ALLOW_TO_WRITE);
                }
            }

            // Set responsible permissions.
            if (isset($field->permissions->responsible))
            {
                if ($field->permissions->responsible == 'read')
                {
                    field_responsible_permission_set($field_id, FIELD_ALLOW_TO_READ);
                }
                elseif ($field->permissions->responsible == 'write')
                {
                    field_responsible_permission_set($field_id, FIELD_ALLOW_TO_WRITE);
                }
            }

            // Set registered permissions.
            if (isset($field->permissions->registered))
            {
                if ($field->permissions->registered == 'read')
                {
                    field_registered_permission_set($field_id, FIELD_ALLOW_TO_READ);
                }
                elseif ($field->permissions->registered == 'write')
                {
                    field_registered_permission_set($field_id, FIELD_ALLOW_TO_WRITE);
                }
            }

            // Remove default field permissions.
            dal_query('fields/fpdelall.sql', $field_id);

            // Enumerate groups permissions.
            $groups = $field->xpath('./permissions/group');

            if ($groups !== FALSE)
            {
                foreach ($groups as $group)
                {
                    $rs = dal_query('groups/fndk.sql',
                                    $group['type'] == 'global' ? 'is null' : '=' . $project_id,
                                    ustrtolower(ustrcut($group['name'], MAX_GROUP_NAME)));

                    if ($rs->rows != 0)
                    {
                        $group_id = $rs->fetch('group_id');

                        // Set group permissions.
                        if (strval($group) == 'read')
                        {
                            field_permission_add($field_id, $group_id, FIELD_ALLOW_TO_READ);
                        }
                        elseif (strval($group) == 'write')
                        {
                            field_permission_add($field_id, $group_id, FIELD_ALLOW_TO_READ);
                            field_permission_add($field_id, $group_id, FIELD_ALLOW_TO_WRITE);
                        }
                    }
                }
            }
        }
    }

    return TRUE;
}

?>
