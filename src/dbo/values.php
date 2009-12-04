<?php

/**
 * Values
 *
 * This module provides API to work with values of custom fields.
 * See also {@link http://www.etraxis.org/docs-schema.php#tbl_field_values tbl_field_values} database table.
 *
 * @package DBO
 * @subpackage Values
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
//  Artem Rodygin           2005-08-27      bug-062: List values are not set.
//  Artem Rodygin           2005-09-07      new-100: 'Date' field type should be implemented.
//  Artem Rodygin           2005-09-08      new-101: 'Duration' field type should be implemented.
//  Artem Rodygin           2005-09-12      new-105: Format of date values are being entered should depend on user locale settings.
//  Artem Rodygin           2005-09-27      new-141: Source code review.
//  Artem Rodygin           2005-11-13      bug-173: MySQL database deployment failures.
//  Artem Rodygin           2005-12-11      bug-180: Previous field values are lost.
//  Artem Rodygin           2006-05-07      new-251: Traceability logging review.
//  Artem Rodygin           2006-06-25      bug-269: Multilined text values are cut to 1000 characters.
//  Artem Rodygin           2006-08-13      bug-301: dbx_error(): You can't specify target table 'tbl_field_values' for update in FROM clause
//  Artem Rodygin           2006-11-24      new-377: Custom views.
//  Artem Rodygin           2007-11-26      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2008-06-21      bug-721: [SF1982395] DBX error when saving multi-lined textbox
//  Artem Rodygin           2008-12-09      bug-770: MySQL server hangs up on searching.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//  Artem Rodygin           2009-06-17      bug-825: Database gets empty strings instead of NULL values.
//  Artem Rodygin           2009-08-31      new-826: Native unicode support for Microsoft SQL Server.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/fields.php');
/**#@-*/

//--------------------------------------------------------------------------------------------------
//  Functions.
//--------------------------------------------------------------------------------------------------

/**
 * Finds in database and returns a custom field value by its ID.
 *
 * @param int $field_type {@link http://www.etraxis.org/docs-schema.php#tbl_fields_field_type Field type}.
 * @param int $value_id {@link http://www.etraxis.org/docs-schema.php#tbl_field_values_value_id Value ID}.
 * @return mixed Custom field value if it's found in database, NULL otherwise.
 */
function value_find ($field_type, $value_id)
{
    debug_write_log(DEBUG_TRACE, '[value_find]');
    debug_write_log(DEBUG_DUMP,  '[value_find] $field_type = ' . $field_type);
    debug_write_log(DEBUG_DUMP,  '[value_find] $value_id   = ' . $value_id);

    $value = NULL;

    if (!is_null($value_id))
    {
        switch ($field_type)
        {
            case FIELD_TYPE_NUMBER:
            case FIELD_TYPE_LIST:
            case FIELD_TYPE_RECORD:

                $value = $value_id;
                break;

            case NULL:
            case FIELD_TYPE_STRING:

                $rs = dal_query('values/sfndid.sql', $value_id);

                if ($rs->rows == 0)
                {
                    debug_write_log(DEBUG_ERROR, '[value_find] String value cannot be found.');
                }
                else
                {
                    $value = $rs->fetch('string_value');
                }

                break;

            case FIELD_TYPE_MULTILINED:

                $rs = dal_query('values/tfndid.sql', $value_id);

                if ($rs->rows == 0)
                {
                    debug_write_log(DEBUG_ERROR, '[value_find] Value cannot be found.');
                }
                else
                {
                    $value = $rs->fetch('text_value');
                }

                break;

            case FIELD_TYPE_CHECKBOX:

                $value = (bool) $value_id;
                break;

            case FIELD_TYPE_DATE:

                $value = get_date($value_id);
                break;

            case FIELD_TYPE_DURATION:

                $value = time2ustr($value_id);
                break;

            default:

                debug_write_log(DEBUG_WARNING, '[value_find] Unknown field type = ' . $field_type);
        }
    }

    return $value;
}

/**
 * Finds in database and returns a {@link http://www.etraxis.org/docs-schema.php#tbl_list_values_str_value text value} of list item,
 * which is specified by its {@link http://www.etraxis.org/docs-schema.php#tbl_list_values_int_value integer value}.
 *
 * @param int $field_id {@link http://www.etraxis.org/docs-schema.php#tbl_fields_field_id Field ID}.
 * @param int $int_value {@link http://www.etraxis.org/docs-schema.php#tbl_field_values_value_id Value ID}.
 * @return string Text value of list item, if it's found in database, NULL otherwise.
 */
function value_find_listvalue ($field_id, $int_value)
{
    debug_write_log(DEBUG_TRACE, '[value_find_listvalue]');
    debug_write_log(DEBUG_DUMP,  '[value_find_listvalue] $field_id  = ' . $field_id);
    debug_write_log(DEBUG_DUMP,  '[value_find_listvalue] $int_value = ' . $int_value);

    $res = NULL;

    if (!is_null($int_value))
    {
        $rs = dal_query('values/lvfndk1.sql', $field_id, $int_value);

        if ($rs->rows == 0)
        {
            debug_write_log(DEBUG_ERROR, '[value_find_listvalue] Value cannot be found.');
        }
        else
        {
            $res = $rs->fetch('str_value');
        }
    }

    return $res;
}

/**
 * Creates in database an integer value for specified field per specified event.
 *
 * @param int $event_id {@link http://www.etraxis.org/docs-schema.php#tbl_events_event_id Event ID}.
 * @param int $field_id {@link http://www.etraxis.org/docs-schema.php#tbl_fields_field_id Field ID}.
 * @param int $field_type {@link http://www.etraxis.org/docs-schema.php#tbl_fields_field_type Field type}.
 * @param int $value Integer value to be stored in database (could be NULL).
 * @return int Always {@link NO_ERROR}.
 */
function value_create_number ($event_id, $field_id, $field_type = FIELD_TYPE_NUMBER, $value = NULL)
{
    debug_write_log(DEBUG_TRACE, '[value_create_number]');
    debug_write_log(DEBUG_DUMP,  '[value_create_number] $event_id   = ' . $event_id);
    debug_write_log(DEBUG_DUMP,  '[value_create_number] $field_id   = ' . $field_id);
    debug_write_log(DEBUG_DUMP,  '[value_create_number] $field_type = ' . $field_type);
    debug_write_log(DEBUG_DUMP,  '[value_create_number] $value      = ' . $value);

    dal_query('values/create.sql',
              $event_id,
              $field_id,
              $field_type,
              is_null($value) ? NULL : $value);

    return NO_ERROR;
}

/**
 * Modifies in database current integer value of specified field for specified record per specified event.
 *
 * @param int $record_id {@link http://www.etraxis.org/docs-schema.php#tbl_records_record_id Record ID}.
 * @param int $event_id {@link http://www.etraxis.org/docs-schema.php#tbl_events_event_id Event ID}.
 * @param int $field_id {@link http://www.etraxis.org/docs-schema.php#tbl_fields_field_id Field ID}.
 * @param int $value New integer value (could be NULL).
 * @return int Always {@link NO_ERROR}.
 */
function value_modify_number ($record_id, $event_id, $field_id, $value = NULL)
{
    debug_write_log(DEBUG_TRACE, '[value_modify_number]');
    debug_write_log(DEBUG_DUMP,  '[value_modify_number] $record_id = ' . $record_id);
    debug_write_log(DEBUG_DUMP,  '[value_modify_number] $event_id  = ' . $event_id);
    debug_write_log(DEBUG_DUMP,  '[value_modify_number] $field_id  = ' . $field_id);
    debug_write_log(DEBUG_DUMP,  '[value_modify_number] $value     = ' . $value);

    $rs = dal_query('values/fndk.sql', $record_id, $field_id);

    if ($rs->rows != 0)
    {
        $value_id = $rs->fetch('value_id');

        // If current value and new one are different - register changes.
        if ($value_id != $value)
        {
            debug_write_log(DEBUG_NOTICE, '[value_modify_number] Register changes.');

            dal_query('changes/create.sql',
                      $event_id,
                      $field_id,
                      is_null($value_id) ? NULL : $value_id,
                      is_null($value)    ? NULL : $value);
        }
    }

    $rs = dal_query('values/efndid.sql',
                    $record_id,
                    $field_id);

    dal_query('values/modify.sql',
              $rs->fetch('event_id'),
              $field_id,
              is_null($value) ? NULL : $value);

    return NO_ERROR;
}

/**
 * Finds in database specified {@link http://www.etraxis.org/docs-schema.php#tbl_string_values_string_value single string value} and returns its ID.
 * If specified value doesn't exist in database, creates it {@link http://www.etraxis.org/docs-schema.php#tbl_string_values there} and returns its ID.
 *
 * @param string $value {@link http://www.etraxis.org/docs-schema.php#tbl_string_values_string_value String value}.
 * @return int ID of string value, NULL on error.
 */
function value_find_string ($value)
{
    debug_write_log(DEBUG_TRACE, '[value_find_string]');
    debug_write_log(DEBUG_DUMP,  '[value_find_string] $value = ' . $value);

    $id = NULL;

    if (!is_null($value))
    {
        $rs = dal_query('values/sfndk.sql', md5($value));

        // Value doesn't exist - must be created.
        if ($rs->rows == 0)
        {
            debug_write_log(DEBUG_NOTICE, '[value_find_string] Register string value.');

            dal_query('values/screate.sql', md5($value), $value);
            $rs = dal_query('values/sfndk.sql', md5($value));
        }

        // Value should exist by this moment.
        if ($rs->rows == 0)
        {
            debug_write_log(DEBUG_ERROR, '[value_find_string] Value cannot be found.');
        }
        else
        {
            $id = $rs->fetch('value_id');
        }
    }

    return $id;
}

/**
 * Creates in database a single string value for specified field per specified event.
 *
 * @param int $event_id {@link http://www.etraxis.org/docs-schema.php#tbl_events_event_id Event ID}.
 * @param int $field_id {@link http://www.etraxis.org/docs-schema.php#tbl_fields_field_id Field ID}.
 * @param int $field_type {@link http://www.etraxis.org/docs-schema.php#tbl_fields_field_type Field type}.
 * @param string $value String value to be stored in database (could be NULL).
 * @return int Always {@link NO_ERROR}.
 */
function value_create_string ($event_id, $field_id, $field_type = FIELD_TYPE_STRING, $value = NULL)
{
    debug_write_log(DEBUG_TRACE, '[value_create_string]');
    debug_write_log(DEBUG_DUMP,  '[value_create_string] $event_id   = ' . $event_id);
    debug_write_log(DEBUG_DUMP,  '[value_create_string] $field_id   = ' . $field_id);
    debug_write_log(DEBUG_DUMP,  '[value_create_string] $field_type = ' . $field_type);
    debug_write_log(DEBUG_DUMP,  '[value_create_string] $value      = ' . $value);

    $id = value_find_string($value);

    dal_query('values/create.sql',
              $event_id,
              $field_id,
              $field_type,
              is_null($id) ? NULL : $id);

    return NO_ERROR;
}

/**
 * Modifies in database current single string value of specified field for specified record per specified event.
 *
 * @param int $record_id {@link http://www.etraxis.org/docs-schema.php#tbl_records_record_id Record ID}.
 * @param int $event_id {@link http://www.etraxis.org/docs-schema.php#tbl_events_event_id Event ID}.
 * @param int $field_id {@link http://www.etraxis.org/docs-schema.php#tbl_fields_field_id Field ID}.
 * @param string $value New string value (could be NULL).
 * @return int Always {@link NO_ERROR}.
 */
function value_modify_string ($record_id, $event_id, $field_id, $value = NULL)
{
    debug_write_log(DEBUG_TRACE, '[value_modify_string]');
    debug_write_log(DEBUG_DUMP,  '[value_modify_string] $record_id = ' . $record_id);
    debug_write_log(DEBUG_DUMP,  '[value_modify_string] $event_id  = ' . $event_id);
    debug_write_log(DEBUG_DUMP,  '[value_modify_string] $field_id  = ' . $field_id);
    debug_write_log(DEBUG_DUMP,  '[value_modify_string] $value     = ' . $value);

    $id = value_find_string($value);

    $rs = dal_query('values/fndk.sql', $record_id, $field_id);

    if ($rs->rows != 0)
    {
        $value_id = $rs->fetch('value_id');

        // If current value and new one are different - register changes.
        if ($value_id != $id)
        {
            debug_write_log(DEBUG_NOTICE, '[value_modify_string] Register changes.');

            dal_query('changes/create.sql',
                      $event_id,
                      $field_id,
                      is_null($value_id) ? NULL : $value_id,
                      is_null($id)       ? NULL : $id);
        }
    }

    $rs = dal_query('values/efndid.sql',
                    $record_id,
                    $field_id);

    dal_query('values/modify.sql',
              $rs->fetch('event_id'),
              $field_id,
              is_null($id) ? NULL : $id);

    return NO_ERROR;
}

/**
 * Finds in database specified {@link http://www.etraxis.org/docs-schema.php#tbl_text_values_text_value multilined text value} and returns its ID.
 * If specified value doesn't exist in database, creates it {@link http://www.etraxis.org/docs-schema.php#tbl_text_values there} and returns its ID.
 *
 * @param string $value {@link http://www.etraxis.org/docs-schema.php#tbl_text_values_text_value Text value}.
 * @return int ID of text value, NULL on error.
 */
function value_find_multilined ($value)
{
    debug_write_log(DEBUG_TRACE, '[value_find_multilined]');
    debug_write_log(DEBUG_DUMP,  '[value_find_multilined] $value = ' . $value);

    $id = NULL;

    if (!is_null($value))
    {
        $token = md5($value);

        $rs = dal_query('values/tfndk.sql', "'{$token}'");

        // Value doesn't exist - must be created.
        if ($rs->rows == 0)
        {
            debug_write_log(DEBUG_NOTICE, '[value_find_multilined] Register text value.');

            // Oracle BLOB needs specific processing.
            if (DATABASE_DRIVER == DRIVER_ORACLE9)
            {
                $handle = CDatabase::connect();
                $sql = file_get_contents(LOCALROOT . 'sql/values/oracle/tcreate.sql');

                $stid = ociparse($handle, $sql);
                $clob = ocinewdescriptor($handle, OCI_D_LOB);

                ocibindbyname($stid, ":value_token", $token);
                ocibindbyname($stid, ":text_value",  $clob, -1, OCI_B_CLOB);

                ociexecute($stid, OCI_DEFAULT);
                $clob->save($value);
                ocicommit($handle);
            }
            else
            {
                dal_query('values/tcreate.sql', "'{$token}'", $value);
            }

            $rs = dal_query('values/tfndk.sql', "'{$token}'");
        }

        // Value should exist by this moment.
        if ($rs->rows == 0)
        {
            debug_write_log(DEBUG_ERROR, '[value_find_multilined] Value cannot be found.');
        }
        else
        {
            $id = $rs->fetch('value_id');
        }
    }

    return $id;
}

/**
 * Creates in database a multilined text value for specified field per specified event.
 *
 * @param int $event_id {@link http://www.etraxis.org/docs-schema.php#tbl_events_event_id Event ID}.
 * @param int $field_id {@link http://www.etraxis.org/docs-schema.php#tbl_fields_field_id Field ID}.
 * @param int $field_type {@link http://www.etraxis.org/docs-schema.php#tbl_fields_field_type Field type}.
 * @param string $value Text value to be stored in database (could be NULL).
 * @return int Always {@link NO_ERROR}.
 */
function value_create_multilined ($event_id, $field_id, $field_type = FIELD_TYPE_MULTILINED, $value = NULL)
{
    debug_write_log(DEBUG_TRACE, '[value_create_multilined]');
    debug_write_log(DEBUG_DUMP,  '[value_create_multilined] $event_id   = ' . $event_id);
    debug_write_log(DEBUG_DUMP,  '[value_create_multilined] $field_id   = ' . $field_id);
    debug_write_log(DEBUG_DUMP,  '[value_create_multilined] $field_type = ' . $field_type);
    debug_write_log(DEBUG_DUMP,  '[value_create_multilined] $value      = ' . $value);

    $id = value_find_multilined($value);

    dal_query('values/create.sql',
              $event_id,
              $field_id,
              $field_type,
              is_null($id) ? NULL : $id);

    return NO_ERROR;
}

/**
 * Modifies in database current multilined text value of specified field for specified record per specified event.
 *
 * @param int $record_id {@link http://www.etraxis.org/docs-schema.php#tbl_records_record_id Record ID}.
 * @param int $event_id {@link http://www.etraxis.org/docs-schema.php#tbl_events_event_id Event ID}.
 * @param int $field_id {@link http://www.etraxis.org/docs-schema.php#tbl_fields_field_id Field ID}.
 * @param string $value New text value (could be NULL).
 * @return int Always {@link NO_ERROR}.
 */
function value_modify_multilined ($record_id, $event_id, $field_id, $value = NULL)
{
    debug_write_log(DEBUG_TRACE, '[value_modify_multilined]');
    debug_write_log(DEBUG_DUMP,  '[value_modify_multilined] $record_id = ' . $record_id);
    debug_write_log(DEBUG_DUMP,  '[value_modify_multilined] $event_id  = ' . $event_id);
    debug_write_log(DEBUG_DUMP,  '[value_modify_multilined] $field_id  = ' . $field_id);
    debug_write_log(DEBUG_DUMP,  '[value_modify_multilined] $value     = ' . $value);

    $id = value_find_multilined($value);

    $rs = dal_query('values/fndk.sql', $record_id, $field_id);

    if ($rs->rows != 0)
    {
        $value_id = $rs->fetch('value_id');

        // If current value and new one are different - register changes.
        if ($value_id != $id)
        {
            debug_write_log(DEBUG_NOTICE, '[value_modify_multilined] Register changes.');

            dal_query('changes/create.sql',
                      $event_id,
                      $field_id,
                      is_null($value_id) ? NULL : $value_id,
                      is_null($id)       ? NULL : $id);
        }
    }

    $rs = dal_query('values/efndid.sql',
                    $record_id,
                    $field_id);

    dal_query('values/modify.sql',
              $rs->fetch('event_id'),
              $field_id,
              is_null($id) ? NULL : $id);

    return NO_ERROR;
}

?>
