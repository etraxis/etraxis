<?php

/**
 * Database Abstraction Layer
 *
 * This module implements eTraxis connectivity.
 *
 * @package Engine
 * @subpackage DAL
 */

//--------------------------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system.
//  Copyright (C) 2004-2010 by Artem Rodygin
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
//  Artem Rodygin           2004-11-17      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-07-31      new-006: Records search.
//  Artem Rodygin           2005-08-22      bug-041: PHP Warning: odbc_exec(): SQL error: The name 'ru0' is not permitted in this context.
//  Artem Rodygin           2005-08-29      new-068: System settings in 'config.php' should be accessable through web-interface.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-09-22      new-141: Source code review.
//  Artem Rodygin           2006-03-20      new-219: Dump of query should be moved to top of 'dal_execute' function.
//  Artem Rodygin           2006-05-12      bug-172: Extra long comments are cut when submitted.
//  Artem Rodygin           2006-05-14      bug-256: UTF-8 values are cut in MySQL database.
//  Artem Rodygin           2006-05-16      new-005: Oracle support.
//  Artem Rodygin           2006-05-26      bug-252: Sablotron fails if record contains non-ASCII characters and MSSQL connection is used.
//  Artem Rodygin           2006-05-30      bug-264: PHP Warning: dbx_error: not supported in this module
//  Artem Rodygin           2006-06-01      bug-265: PHP Warning: ociexecute(): OCIStmtExecute: ORA-00904: "R"."RECORD_ID": invalid identifier
//  Artem Rodygin           2007-02-03      bug-493: [SF1650590] doesn't work with Oracle XE (10g)
//  Artem Rodygin           2007-07-01      bug-537: PHP Fatal error: Allowed memory size of 134217728 bytes exhausted (tried to allocate 16 bytes)
//  Artem Rodygin           2007-11-26      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2008-02-26      bug-679: "User is not authorized" is permanently shown with Oracle.
//  Artem Rodygin           2008-02-28      new-294: PostgreSQL support.
//  Artem Rodygin           2008-04-24      new-708: [SF1950362] UNIX socket for PostgreSQL
//  Artem Rodygin           2008-04-24      bug-709: [SF1950363] PostgreSQL connection string has unprintable chars
//  Artem Rodygin           2009-03-11      bug-799: eTraxis doesn't work with XAMPP on Windows.
//  Artem Rodygin           2009-06-01      new-824: PHP 4 is discontinued.
//  Artem Rodygin           2009-06-17      bug-825: Database gets empty strings instead of NULL values.
//  Artem Rodygin           2009-08-17      new-826: Native unicode support for Microsoft SQL Server.
//  Artem Rodygin           2009-09-06      new-827: Microsoft SQL Server 2005/2008 support.
//  Artem Rodygin           2010-01-08      bug-889: PHP Notice: Undefined index: code/message
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/debug.php');
require_once('../engine/utility.php');
require_once('../engine/locale.php');
/**#@-*/

/**#@+
 * Supported database driver.
 */
define('DRIVER_MYSQL50', 1);  // MySQL 5.0 or later
define('DRIVER_MSSQL2K', 2);  // Microsoft SQL Server 2000 or later
define('DRIVER_ORACLE9', 3);  // Oracle 9i or later
define('DRIVER_PGSQL80', 4);  // PostgreSQL 8.0 or later
/**#@-*/

$res_driver = array
(
    DRIVER_MYSQL50 => RES_MYSQL_ID,
    DRIVER_MSSQL2K => RES_MSSQL_ID,
    DRIVER_ORACLE9 => RES_ORACLE_ID,
    DRIVER_PGSQL80 => RES_POSTGRESQL_ID,
);

//--------------------------------------------------------------------------------------------------
//  DAL recordset.
//--------------------------------------------------------------------------------------------------

/**
 * Database connection, implemented via Singleton pattern.
 * @package Engine
 * @subpackage DAL
 * @ignore
 */
class CDatabase
{
    // Static object of itself.
    private static $object = NULL;

    // Link of opened connection.
    private $link = FALSE;

    // Establishes connection to eTraxis database.
    private function __construct ()
    {
        debug_write_log(DEBUG_TRACE, '[CDatabase::__construct]');

        if (DATABASE_DRIVER == DRIVER_MYSQL50)
        {
            $this->link = mysql_connect(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD);

            if ($this->link)
            {
                if (mysql_select_db(DATABASE_DBNAME, $this->link))
                {
                    mysql_query('set names utf8', $this->link);
                }
                else
                {
                    debug_write_log(DEBUG_WARNING, '[CDatabase::__construct] Error on selecting MySQL database.');
                    mysql_close($this->link);
                    $this->link = FALSE;
                }
            }
        }
        elseif (DATABASE_DRIVER == DRIVER_MSSQL2K)
        {
            $conn_info = array
            (
                'APP'          => 'eTraxis',
                'CharacterSet' => 'UTF-8',
                'Database'     => DATABASE_DBNAME,
            );

            if (ustrlen(trim(DATABASE_USERNAME)) != 0)
            {
                $conn_info['UID'] = DATABASE_USERNAME;
                $conn_info['PWD'] = DATABASE_PASSWORD;
            }

            $this->link = sqlsrv_connect(DATABASE_HOST, $conn_info);
        }
        elseif (DATABASE_DRIVER == DRIVER_ORACLE9)
        {
            $this->link = dbx_connect(DBX_OCI8, DATABASE_HOST, DATABASE_DBNAME, DATABASE_USERNAME, DATABASE_PASSWORD);
        }
        elseif (DATABASE_DRIVER == DRIVER_PGSQL80)
        {
            if (strlen(trim(DATABASE_HOST)) == 0)
            {
                $this->link = pg_connect(sprintf('dbname=%s user=%s password=%s', DATABASE_DBNAME, DATABASE_USERNAME, DATABASE_PASSWORD));
            }
            else
            {
                $this->link = pg_connect(sprintf('host=%s dbname=%s user=%s password=%s', DATABASE_HOST, DATABASE_DBNAME, DATABASE_USERNAME, DATABASE_PASSWORD));
            }
        }
        else
        {
            debug_write_log(DEBUG_WARNING, '[CDatabase::__construct] Unknown database driver.');
            $this->link = FALSE;
        }
    }

    // Closes connection to eTraxis database.
    public function __destruct()
    {
        if (DATABASE_DRIVER == DRIVER_MYSQL50)
        {
            mysql_close($this->link);
        }
        elseif (DATABASE_DRIVER == DRIVER_MSSQL2K)
        {
            sqlsrv_close($this->link);
        }
        elseif (DATABASE_DRIVER == DRIVER_ORACLE9)
        {
            dbx_close($this->link);
        }
        elseif (DATABASE_DRIVER == DRIVER_PGSQL80)
        {
            pg_close($this->link);
        }
    }

    // Tries to connect to database.
    // Return resource for connection to eTraxis database on success, FALSE otherwise.
    public static function connect ()
    {
        if (is_null(self::$object))
        {
            self::$object = new CDatabase();
        }

        return self::$object->link;
    }
}

/**
 * DAL recordset.
 *
 * The class implements DAL recordset and several functions to work with.
 * The implementation is universal and doesn't depend on type of database.
 *
 * @package Engine
 * @subpackage DAL
 */
class CRecordset
{
    /**#@+
     * For internal use only.
     * @ignore
     */
    private $handle;  // [resource] connection
    private $result;  // [resource] query result
    private $resptr;  // [int]      recordset cursor (number of current record from 0)
    /**#@-*/

    /**
     * Number of rows in resulted recordset (read-only).
     * @var int
     */
    protected $rows;

    /**
     * Number of columns in resulted recordset (read-only).
     * @var int
     */
    protected $cols;

    /**
     * Executes specified query and constructs itself as resulted recordset.
     *
     * @param string $sql SQL-query being executed.
     */
    public function __construct ($sql)
    {
        $this->handle = CDatabase::connect();
        $this->result = FALSE;
        $this->resptr = 0;
        $this->rows   = 0;
        $this->cols   = 0;

        list($msec, $sec) = explode(' ', microtime());
        $start = (float)$msec + (float)$sec;

        if (DATABASE_DRIVER == DRIVER_MYSQL50)
        {
            $this->result = mysql_query($sql, $this->handle);

            if (is_resource($this->result))
            {
                $this->rows = mysql_num_rows($this->result);
                $this->cols = mysql_num_fields($this->result);
            }
        }
        elseif (DATABASE_DRIVER == DRIVER_MSSQL2K)
        {
            $this->result = sqlsrv_query($this->handle, $sql, NULL, array('Scrollable' => SQLSRV_CURSOR_STATIC));

            if (is_resource($this->result))
            {
                $this->rows = sqlsrv_num_rows($this->result);
                $this->cols = sqlsrv_num_fields($this->result);
            }

            $this->resptr = -1;
        }
        elseif (DATABASE_DRIVER == DRIVER_ORACLE9)
        {
            $this->result = dbx_query($this->handle, $sql, DBX_COLNAMES_LOWERCASE);

            if (is_object($this->result))
            {
                $this->rows = $this->result->rows;
                $this->cols = $this->result->cols;
            }
        }
        elseif (DATABASE_DRIVER == DRIVER_PGSQL80)
        {
            $this->result = pg_query($this->handle, $sql);

            if (is_resource($this->result))
            {
                $this->rows = pg_num_rows($this->result);
                $this->cols = pg_num_fields($this->result);
            }
        }
        else
        {
            debug_write_log(DEBUG_WARNING, '[CRecordset::__construct] Unknown database driver.');
        }

        list($msec, $sec) = explode(' ', microtime());
        $stop = (float)$msec + (float)$sec;

        debug_write_log(DEBUG_DUMP,        'SQL text = ' . $sql);
        debug_write_log(DEBUG_PERFORMANCE, 'SQL time = ' . ($stop - $start));

        if (!$this->result)
        {
            debug_write_log(DEBUG_WARNING, '[CRecordset::__construct] ' . $this->error());
        }
    }

    /**
     * Frees all resources associated with the recordset.
     */
    public function __destruct()
    {
        if (is_resource($this->result))
        {
            if (DATABASE_DRIVER == DRIVER_MYSQL50)
            {
                mysql_free_result($this->result);
            }
            elseif (DATABASE_DRIVER == DRIVER_MSSQL2K)
            {
                sqlsrv_free_stmt($this->result);
            }
            elseif (DATABASE_DRIVER == DRIVER_ORACLE9)
            {
                // nothing to do in case of DBX
            }
            elseif (DATABASE_DRIVER == DRIVER_PGSQL80)
            {
                pg_free_result($this->result);
            }
            else
            {
                debug_write_log(DEBUG_WARNING, '[CRecordset::__destruct] Unknown database driver.');
            }
        }
    }

    /**
     * @ignore
     */
    public function __get ($name)
    {
        switch ($name)
        {
            case 'rows': return $this->rows;
            case 'cols': return $this->cols;
            default:     return NULL;
        }
    }

    /**
     * Returns error message of last operation.
     *
     * @return string Error message of last operation, or NULL on failure.
     */
    public function error ()
    {
        if (DATABASE_DRIVER == DRIVER_MYSQL50)
        {
            $errno  = mysql_errno($this->handle);
            $error  = mysql_error($this->handle);
            $retval = "MySQL error {$errno}: {$error}";
        }
        elseif (DATABASE_DRIVER == DRIVER_MSSQL2K)
        {
            $error  = sqlsrv_errors(SQLSRV_ERR_ALL);
            $retval = (is_null($error)
                    ? NULL
                    : "MSSQL error {$error[0]['code']}: {$error[0]['message']}");
        }
        elseif (DATABASE_DRIVER == DRIVER_ORACLE9)
        {
            $error  = ocierror($this->handle->handle);
            $retval = "Oracle error {$error['code']}: {$error['message']}";
        }
        elseif (DATABASE_DRIVER == DRIVER_PGSQL80)
        {
            $error  = pg_last_error($this->handle);
            $retval = "PostgreSQL error: {$error}";
        }
        else
        {
            debug_write_log(DEBUG_WARNING, '[CRecordset::error] Unknown database driver.');
            return NULL;
        }

        return $retval;
    }

    /**
     * Moves cursor to specified record.
     *
     * @param int $row_number Number of record, zero-based.
     * @return bool TRUE on success, FALSE otherwise.
     */
    public function seek ($row_number = 0)
    {
        if (!$this->result)
        {
            debug_write_log(DEBUG_WARNING, '[CRecordset::seek] No stored recordset.');
            return FALSE;
        }

        if ($row_number < 0 || $row_number >= $this->rows)
        {
            debug_write_log(DEBUG_WARNING, '[CRecordset::seek] Row number is out of stored recordset.');
            return FALSE;
        }

        if (DATABASE_DRIVER == DRIVER_MYSQL50)
        {
            $retval = mysql_data_seek($this->result, $row_number);
        }
        elseif (DATABASE_DRIVER == DRIVER_MSSQL2K)
        {
            $this->resptr = $row_number;
            $retval = TRUE;
        }
        elseif (DATABASE_DRIVER == DRIVER_ORACLE9)
        {
            $this->resptr = $row_number;
            $retval = TRUE;
        }
        elseif (DATABASE_DRIVER == DRIVER_PGSQL80)
        {
            $retval = pg_result_seek($this->result, $row_number);
        }
        else
        {
            debug_write_log(DEBUG_WARNING, '[CRecordset::seek] Unknown database driver.');
            return FALSE;
        }

        if (!$retval)
        {
            debug_write_log(DEBUG_WARNING, '[CRecordset::seek] ' . $this->error());
        }

        return $retval;
    }

    /**
     * Returns next record from recordset.
     *
     * Returns the record for current cursor and then moves cursor forward to the next one.
     * The record is returned as array with two sets of keys - one set is zero-based indexes, another is names of record fields.
     *
     * @param int|string $field Optional field name or zero-based index.
     * @return mixed|array If <i>field</i> is not specified, returns whole record as an array, or FALSE if there is no more record to return.
     * If <i>field</i> is specified, then returns value of specified field (it could be both zero-based index, or field name).
     *
     * Example #1:
     * <code>
     * $rs = new CRecordset("select my_id, my_field from my_table");
     *
     * while ($row = $rs->fetch())
     * {
     *     printf("%u\t%s\n", $row["my_id"], $row["my_field"]);
     * }
     * </code>
     *
     * Example #2:
     * <code>
     * $rs = new CRecordset("select count(*) from my_table");
     * echo($rs->fetch(0));
     * </code>
     */
    public function fetch ($field = NULL)
    {
        if (!$this->result)
        {
            debug_write_log(DEBUG_WARNING, '[CRecordset::fetch] No stored recordset.');
            return FALSE;
        }

        if (DATABASE_DRIVER == DRIVER_MYSQL50)
        {
            $retval = mysql_fetch_array($this->result, MYSQL_BOTH);
        }
        elseif (DATABASE_DRIVER == DRIVER_MSSQL2K)
        {
            if ($this->resptr == -1)
            {
                $retval = sqlsrv_fetch_array($this->result, SQLSRV_FETCH_BOTH, SQLSRV_SCROLL_NEXT);
            }
            else
            {
                $retval = sqlsrv_fetch_array($this->result, SQLSRV_FETCH_BOTH, SQLSRV_SCROLL_ABSOLUTE, $this->resptr);
                $this->resptr = -1;
            }
        }
        elseif (DATABASE_DRIVER == DRIVER_ORACLE9)
        {
            if ($this->resptr == $this->result->rows)
            {
                debug_write_log(DEBUG_WARNING, '[CRecordset::fetch] No more rows to return.');
                return FALSE;
            }

            $retval = $this->result->data[$this->resptr++];
        }
        elseif (DATABASE_DRIVER == DRIVER_PGSQL80)
        {
            $retval = pg_fetch_array($this->result);
        }
        else
        {
            debug_write_log(DEBUG_WARNING, '[CRecordset::fetch] Unknown database driver.');
            return FALSE;
        }

        if (!is_array($retval))
        {
            return NULL;
        }

        return (is_null($field) ? $retval : $retval[$field]);
    }
}

//--------------------------------------------------------------------------------------------------
//  Functions.
//--------------------------------------------------------------------------------------------------

/**
 * Executes specified SQL-file from "sql" eTraxis directory.
 *
 * The function accepts variable number of arguments. It opens specified SQL-file and replaces each "%i"
 * (where <i>i</i> is a natural number) substring with related additional argument.
 *
 * @param string $query Path to file with SQL-query (path is related to "sql" directory).
 * @param mixed Value, which each "%1" substring will be replaced with.
 * @param mixed Value, which each "%2" substring will be replaced with.
 * @param mixed ... (and so on)
 * @return CRecordset Resulted {@link CRecordset DAL recordset}.
 *
 * Example of usage:
 * <code>
 * $rs = dal_query("accounts/list.sql", "username");
 *
 * while ($row = $rs->fetch())
 * {
 *     foreach ($row as $item)
 *     {
 *         echo("$item\t");
 *     }
 *
 *     echo("\n");
 * }
 * </code>
 */
function dal_query ($query)
{
    debug_write_log(DEBUG_TRACE, '[dal_query] ' . $query);

    $sql = file_get_contents(LOCALROOT . 'sql/' . $query);
    $sql = str_replace("\n", ' ', $sql);
    $sql = preg_replace('([ ]+)', ' ', $sql);

    if (DATABASE_DRIVER == DRIVER_ORACLE9)
    {
        $pos = ustrrpos($sql, 'order by');

        if ($pos !== FALSE)
        {
            $sql = usubstr($sql, 0, $pos) . preg_replace('(\s[a-z]+\.)', ' ', usubstr($sql, $pos));
        }
    }

    $count = func_num_args() - 1;

    for ($i = $count; $i >= 1; $i--)
    {
        $search  = '%' . $i;
        $replace = func_get_arg($i);

        if (strpos($sql, "'{$search}'") === FALSE)
        {
            if (is_null($replace))
            {
                $replace = 'NULL';
            }
        }
        else
        {
            if (is_null($replace))
            {
                $search  = "'{$search}'";
                $replace = 'NULL';
            }
            else
            {
                $replace = ustr2sql($replace);
            }
        }

        $sql = ustr_replace($search, $replace, $sql);
    }

    return new CRecordset($sql);
}

?>
