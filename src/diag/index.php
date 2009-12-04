<?php

/**
 * @package eTraxis
 * @ignore
 */

//--------------------------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system.
//  Copyright (C) 2009 by Artem Rodygin
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
//  Artem Rodygin           2009-01-09      new-783: Script for eTraxis initial setup.
//  Artem Rodygin           2009-02-25      bug-792: [SF2635842] Short PHP tags in login.php
//  Artem Rodygin           2009-02-27      bug-794: [SF2643676] Security problem when logout.
//  Artem Rodygin           2009-06-01      new-824: PHP 4 is discontinued.
//  Artem Rodygin           2009-08-06      bug-841: The 'magic_quotes_runtime' option must be turned off.
//  Artem Rodygin           2009-09-10      new-826: Native unicode support for Microsoft SQL Server.
//  Artem Rodygin           2009-09-06      new-827: Microsoft SQL Server 2005/2008 support.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/config.php');
/**#@-*/

define('PHP_V4',       4);
define('PHP_V5',       5);
define('PHP_V6',       6);
define('PHP_OBSOLETE', 0);

define('PHP4_MINIMUM', '4.3.2');
define('PHP5_MINIMUM', '5.1.0');

define('DRIVER_MYSQL50', 1);  // MySQL 5.0 or later
define('DRIVER_MSSQL2K', 2);  // Microsoft SQL Server 2000 or later
define('DRIVER_ORACLE9', 3);  // Oracle 9i or later
define('DRIVER_PGSQL80', 4);  // PostgreSQL 8.0 or later

if (version_compare(PHP_VERSION, '6.0.0') >= 0)
{
    $php_version = PHP_V6;
}
elseif (version_compare(PHP_VERSION, '5.0.0') >= 0)
{
    $php_version = PHP_V5;
}
elseif (version_compare(PHP_VERSION, '4.0.0') >= 0)
{
    $php_version = PHP_V4;
}
else
{
    $php_version = PHP_OBSOLETE;
}

?>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<meta name="author" content="Artem Rodygin"/>
<meta name="copyright" content="Copyright (C) 2003-2009 by Artem Rodygin"/>
<link rel="stylesheet" type="text/css" href="../css/etraxis.css"/>
<link rel="shortcut icon" type="image/x-icon" href="../images/favicon.ico"/>
<title>eTraxis</title>
<body bgcolor="#FBFBFB" style="margin:10px">
<!-- General information -------------------------------------------------------------------------->
<fieldset>
<legend>General information</legend>
<table cellpadding="0" cellspacing="0"><tr>
<td><p class="label">Server OS:</p></td>
<td><p class="text"><?php echo(php_uname()); ?></p></td>
</tr><tr>
<td><p class="label">Server software:</p></td>
<td><p class="text"><?php echo($_SERVER['SERVER_SOFTWARE']); ?></p></td>
</tr><tr>
<td><p class="label">User agent:</p></td>
<td><p class="text"><?php echo($_SERVER['HTTP_USER_AGENT']); ?></p></td>
</tr></table>
</fieldset>
<!-- PHP configuration ---------------------------------------------------------------------------->
<fieldset>
<legend>PHP configuration</legend>
<table cellpadding="0" cellspacing="0"><tr>
<?php

switch ($php_version)
{
    case PHP_V4:

        $message = '<b>FAIL</b> <i>(PHP 4 is discontinued, you need ' . PHP5_MINIMUM . ' at least)</i>';

        break;

    case PHP_V5:

        if (version_compare(PHP_VERSION, PHP5_MINIMUM) >= 0)
        {
            $message = '<b>PASS</b> <i>(version ' . PHP_VERSION . ')</i>';
        }
        else
        {
            $message = '<b>FAIL</b> <i>(version ' . PHP_VERSION . ' is not supported, you need ' . PHP5_MINIMUM . ' at least)</i>';
        }

        break;

    case PHP_V6:

        $message = '<b>FAIL</b> <i>(PHP 6 is not production, eTraxis behaviour is unpredictable)</i>';

        break;

    default:

        $message = '<b>FAIL</b> <i>(version ' . PHP_VERSION . ' is obsolete or not supported)</i>';
}

?>
<td><p class="label">PHP version:</p></td>
<td><p class="<?php echo(substr($message, 3, 4) == 'PASS' ? 'text' : 'hot'); ?>"><?php echo($message); ?></p></td>
<?php

if (ini_get('safe_mode'))
{
    $message = '<b>FAIL</b> <i>(safe mode is turned on, eTraxis behaviour is unpredictable)</i>';
}
else
{
    $message = '<b>PASS</b> <i>(safe mode is disabled)</i>';
}

?>
</tr><tr>
<td><p class="label">safe_mode:</p></td>
<td><p class="<?php echo(substr($message, 3, 4) == 'PASS' ? 'text' : 'hot'); ?>"><?php echo($message); ?></p></td>
<?php

if (get_magic_quotes_gpc() == 0)
{
    $message = '<b>PASS</b> <i>(magic quotes are disabled)</i>';
}
else
{
    $message = '<b>FAIL</b> <i>(magic quotes for GET/POST/Cookie are turned on, must be disabled)</i>';
}

?>
</tr><tr>
<td><p class="label">magic_quotes_gpc:</p></td>
<td><p class="<?php echo(substr($message, 3, 4) == 'PASS' ? 'text' : 'hot'); ?>"><?php echo($message); ?></p></td>
<?php

if (get_magic_quotes_runtime() == 0)
{
    $message = '<b>PASS</b> <i>(magic quotes are disabled)</i>';
}
else
{
    $message = '<b>FAIL</b> <i>(magic quotes for runtime data are turned on, must be disabled)</i>';
}

?>
</tr><tr>
<td><p class="label">magic_quotes_runtime:</p></td>
<td><p class="<?php echo(substr($message, 3, 4) == 'PASS' ? 'text' : 'hot'); ?>"><?php echo($message); ?></p></td>
<?php

$default_charset = ini_get('default_charset');

if (strlen($default_charset) == 0 || strtolower($default_charset) == 'utf-8')
{
    $message = '<b>PASS</b> <i>(' . (strlen($default_charset) == 0 ? 'empty' : $default_charset) . ')</i>';
}
else
{
    $message = '<b>FAIL</b> <i>(should be either commented, or set to "UTF-8")</i>';
}

?>
</tr><tr>
<td><p class="label">default_charset:</p></td>
<td><p class="<?php echo(substr($message, 3, 4) == 'PASS' ? 'text' : 'hot'); ?>"><?php echo($message); ?></p></td>
<?php

if ($php_version == PHP_V5)
{
    $timezone = ini_get('date.timezone');

    if (strlen($timezone) == 0)
    {
        $message = '<b>FAIL</b> <i>(undefined, should be set to one of <a href="http://www.php.net/manual/timezones.php">available timezones</a>)</i>';
    }
    else
    {
        $message = '<b>PASS</b> <i>("' . $timezone . '")</i>';
    }

?>
</tr><tr>
<td><p class="label">date.timezone:</p></td>
<td><p class="<?php echo(substr($message, 3, 4) == 'PASS' ? 'text' : 'hot'); ?>"><?php echo($message); ?></p></td>
<?php
}

?>
</tr></table>
</fieldset>
<!-- PHP extensions ------------------------------------------------------------------------------->
<fieldset>
<legend>PHP extensions</legend>
<table cellpadding="0" cellspacing="0">
<?php

$extensions = array('iconv', 'mbstring', 'xsl',
                    php_uname('s') == 'Windows' ? 'gd2' : 'gd');

switch (DATABASE_DRIVER)
{
    case DRIVER_MYSQL50:
        array_push($extensions, 'mysql');
        break;

    case DRIVER_MSSQL2K:
        array_push($extensions, 'sqlsrv');
        break;

    case DRIVER_ORACLE9:
        array_push($extensions, 'dbx');
        array_push($extensions, 'oci8');
        break;

    case DRIVER_PGSQL80:
        array_push($extensions, 'pgsql');
        break;

    default: ;  // nop
}

if (LDAP_ENABLED)
{
    array_push($extensions, 'ldap');
}

sort($extensions);

foreach ($extensions as $extension)
{
?>
<tr><td><p class="label"><?php echo($extension); ?>:</p></td>
<?php

    if (extension_loaded($extension))
    {
        $message = '<b>PASS</b>';
    }
    else
    {
        $message = '<b>FAIL</b>';
    }

?>
<td><p class="<?php echo(substr($message, 3, 4) == 'PASS' ? 'text' : 'hot'); ?>"><?php echo($message); ?></p></td>
<?php
}

?>
</table>
</fieldset>
<!-- eTraxis configuration ------------------------------------------------------------------------>
<fieldset>
<legend>eTraxis configuration</legend>
<table cellpadding="0" cellspacing="0"><tr>
<?php

$localroot = $_SERVER['SCRIPT_FILENAME'];

$substr = 'diag/index.php';
$str = substr($localroot, - strlen($substr));

if (substr($localroot, - strlen($substr)) != $substr)
{
    $message = '<b>FAIL</b> <i>(can\'t determine valid "LOCALROOT")</i>';
}
elseif (LOCALROOT != ($localroot = substr($localroot, 0, - strlen($substr))))
{
    $message = '<b>FAIL</b> <i>("LOCALROOT" is probably wrong and should be "' . $localroot . '")</i>';
}
else
{
    $message = '<b>PASS</b> <i>("' . LOCALROOT . '")</i>';
}

?>
<td><p class="label">Local root path:</p></td>
<td><p class="<?php echo(substr($message, 3, 4) == 'PASS' ? 'text' : 'hot'); ?>"><?php echo($message); ?></p></td>
<?php

if (ATTACHMENTS_ENABLED)
{
    if (!file_exists(ATTACHMENTS_PATH))
    {
        $message = '<b>FAIL</b> <i>("' . ATTACHMENTS_PATH . '" is not found)</i>';
    }
    elseif (!is_dir(ATTACHMENTS_PATH))
    {
        $message = '<b>FAIL</b> <i>("' . ATTACHMENTS_PATH . '" is not a directory)</i>';
    }
    elseif (!is_writable(ATTACHMENTS_PATH))
    {
        $message = '<b>FAIL</b> <i>("' . ATTACHMENTS_PATH . '" is not writeable)</i>';
    }
    elseif (substr(ATTACHMENTS_PATH, -1, 1) != '/')
    {
        $message = '<b>FAIL</b> <i>("' . ATTACHMENTS_PATH . '" must be finished with "/" character)</i>';
    }
    else
    {
        $message = '<b>PASS</b> <i>("' . ATTACHMENTS_PATH . '")</i>';
    }
}
else
{
    $message = '<b>PASS</b> <i>(disabled)</i>';
}

?>
</tr><tr>
<td><p class="label">Attachments:</p></td>
<td><p class="<?php echo(substr($message, 3, 4) == 'PASS' ? 'text' : 'hot'); ?>"><?php echo($message); ?></p></td>
<?php

if (DEBUG_MODE)
{
    if (!file_exists(DEBUG_LOGS))
    {
        $message = '<b>FAIL</b> <i>("' . DEBUG_LOGS . '" is not found)</i>';
    }
    elseif (!is_dir(DEBUG_LOGS))
    {
        $message = '<b>FAIL</b> <i>("' . DEBUG_LOGS . '" is not a directory)</i>';
    }
    elseif (!is_writable(DEBUG_LOGS))
    {
        $message = '<b>FAIL</b> <i>("' . DEBUG_LOGS . '" is not writeable)</i>';
    }
    elseif (substr(DEBUG_LOGS, -1, 1) != '/')
    {
        $message = '<b>FAIL</b> <i>("' . DEBUG_LOGS . '" must be finished with "/" character)</i>';
    }
    else
    {
        $message = '<b>PASS</b> <i>("' . DEBUG_LOGS . '")</i>';
    }
}
else
{
    $message = '<b>PASS</b> <i>(disabled)</i>';
}

?>
</tr><tr>
<td><p class="label">Debug logs:</p></td>
<td><p class="<?php echo(substr($message, 3, 4) == 'PASS' ? 'text' : 'hot'); ?>"><?php echo($message); ?></p></td>
<?php

switch (DATABASE_DRIVER)
{
    case DRIVER_MYSQL50:

        $link = mysql_connect(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD);

        if ($link)
        {
            if (mysql_select_db(DATABASE_DBNAME, $link))
            {
                mysql_query('set names utf8', $link);

                $res = mysql_query('select var_value from tbl_sys_vars where var_name = \'FEATURE_LEVEL\'', $link);

                if (is_resource($res))
                {
                    $row = mysql_fetch_array($res, MYSQL_BOTH);

                    if (is_array($row))
                    {
                        if (mysql_free_result($res))
                        {
                            $message = '<b>PASS</b> <i>(MySQL / feature level ' . $row['var_value'] . ')</i>';
                        }
                        else
                        {
                            $errno = mysql_errno($link);
                            $error = mysql_error($link);

                            $message = ($errno == 0 || strlen($error) == 0)
                                     ? '<b>FAIL</b> <i>(unknown MySQL error on releasing recordset)</i>'
                                     : '<b>FAIL</b> <i>(MySQL error #' . $errno . ' on releasing recordset - ' . $error . ')</i>';
                        }
                    }
                    else
                    {
                        $errno = mysql_errno($link);
                        $error = mysql_error($link);

                        $message = ($errno == 0 || strlen($error) == 0)
                                 ? '<b>FAIL</b> <i>(unknown MySQL error on fetching data)</i>'
                                 : '<b>FAIL</b> <i>(MySQL error #' . $errno . ' on fetching data - ' . $error . ')</i>';
                    }
                }
                else
                {
                    $errno = mysql_errno($link);
                    $error = mysql_error($link);

                    $message = ($errno == 0 || strlen($error) == 0)
                             ? '<b>FAIL</b> <i>(unknown MySQL error on query database)</i>'
                             : '<b>FAIL</b> <i>(MySQL error #' . $errno . ' on query database - ' . $error . ')</i>';
                }
            }
            else
            {
                $errno = mysql_errno($link);
                $error = mysql_error($link);

                $message = ($errno == 0 || strlen($error) == 0)
                         ? '<b>FAIL</b> <i>(unknown MySQL error on selecting database)</i>'
                         : '<b>FAIL</b> <i>(MySQL error #' . $errno . ' on selecting database - ' . $error . ')</i>';
            }

            mysql_close($link);
        }
        else
        {
            $message = '<b>FAIL</b> <i>(MySQL server cannot be connected)</i>';
        }

        break;

    case DRIVER_MSSQL2K:

        $conn_info = array
        (
            'APP'          => 'eTraxis',
            'CharacterSet' => 'UTF-8',
            'Database'     => DATABASE_DBNAME,
        );

        if (strlen(trim(DATABASE_USERNAME)) != 0)
        {
            $conn_info['UID'] = DATABASE_USERNAME;
            $conn_info['PWD'] = DATABASE_PASSWORD;
        }

        $link = sqlsrv_connect(DATABASE_HOST, $conn_info);

        if ($link)
        {
            $res = sqlsrv_query($link, 'select var_value from tbl_sys_vars where var_name = \'FEATURE_LEVEL\'',
                                NULL, array('Scrollable' => SQLSRV_CURSOR_STATIC));

            if (is_resource($res))
            {
                $row = sqlsrv_fetch_array($res, SQLSRV_FETCH_BOTH, SQLSRV_SCROLL_NEXT);

                if (is_array($row))
                {
                    if (sqlsrv_free_stmt($res))
                    {
                        $message = '<b>PASS</b> <i>(Microsoft SQL Server / feature level ' . $row['var_value'] . ')</i>';
                    }
                    else
                    {
                        $error = sqlsrv_errors(SQLSRV_ERR_ALL);

                        $message = (is_null($error))
                                 ? '<b>FAIL</b> <i>(unknown Microsoft SQL Server error on releasing recordset)</i>'
                                 : sprintf('<b>FAIL</b> <i>(Microsoft SQL Server error #%d on releasing recordset - %s)</i>',
                                           $error[0]['code'],
                                           $error[0]['message']);
                    }
                }
                else
                {
                    $error = sqlsrv_errors(SQLSRV_ERR_ALL);

                    $message = (is_null($error))
                             ? '<b>FAIL</b> <i>(unknown Microsoft SQL Server error on fetching data)</i>'
                             : sprintf('<b>FAIL</b> <i>(Microsoft SQL Server error #%d on fetching data - %s)</i>',
                                       $error[0]['code'],
                                       $error[0]['message']);
                }
            }
            else
            {
                $error = sqlsrv_errors(SQLSRV_ERR_ALL);

                $message = (is_null($error))
                         ? '<b>FAIL</b> <i>(unknown Microsoft SQL Server error on query database)</i>'
                         : sprintf('<b>FAIL</b> <i>(Microsoft SQL Server error #%d on query database - %s)</i>',
                                   $error[0]['code'],
                                   $error[0]['message']);
            }

            sqlsrv_close($link);
        }
        else
        {
            $message = '<b>FAIL</b> <i>(Microsoft SQL Server cannot be connected)</i>';
        }

        break;

    case DRIVER_ORACLE9:

        $link = dbx_connect(DBX_OCI8, DATABASE_HOST, DATABASE_DBNAME, DATABASE_USERNAME, DATABASE_PASSWORD);

        if ($link)
        {
            $res = dbx_query($link, 'select var_value from tbl_sys_vars where var_name = \'FEATURE_LEVEL\'', DBX_COLNAMES_LOWERCASE);

            if (is_object($res))
            {
                $row = $res->data[0];

                if (is_array($row))
                {
                    $message = '<b>PASS</b> <i>(Oracle / feature level ' . $row['var_value'] . ')</i>';
                }
                else
                {
                    $error = ocierror($link->handle);

                    $message = (strlen($error) == 0)
                             ? '<b>FAIL</b> <i>(unknown Oracle error on query database)</i>'
                             : '<b>FAIL</b> <i>(Oracle error #' . $error['code'] . ' on fetching data - ' . $error['message'] . ')</i>';
                }
            }
            else
            {
                $error = ocierror($link->handle);

                $message = (strlen($error) == 0)
                         ? '<b>FAIL</b> <i>(unknown Oracle error on query database)</i>'
                         : '<b>FAIL</b> <i>(Oracle error #' . $error['code'] . ' on query database - ' . $error['message'] . ')</i>';
            }

            dbx_close($link);
        }
        else
        {
            $message = '<b>FAIL</b> <i>(Oracle server cannot be connected)</i>';
        }

        break;

    case DRIVER_PGSQL80:

        if (strlen(trim(DATABASE_HOST)) == 0)
        {
            $link = pg_connect(sprintf('dbname=%s user=%s password=%s', DATABASE_DBNAME, DATABASE_USERNAME, DATABASE_PASSWORD));
        }
        else
        {
            $link = pg_connect(sprintf('host=%s dbname=%s user=%s password=%s', DATABASE_HOST, DATABASE_DBNAME, DATABASE_USERNAME, DATABASE_PASSWORD));
        }

        if ($link)
        {
            $res = pg_query($link, 'select var_value from tbl_sys_vars where var_name = \'FEATURE_LEVEL\'');

            if (is_resource($res))
            {
                $row = pg_fetch_array($res);

                if (is_array($row))
                {
                    if (pg_free_result($res))
                    {
                        $message = '<b>PASS</b> <i>(PostgreSQL / feature level ' . $row['var_value'] . ')</i>';
                    }
                    else
                    {
                        $error = pg_last_error($link);

                        $message = ($error)
                                 ? '<b>FAIL</b> <i>(PostgreSQL error on releasing recordset - ' . $error . ')</i>'
                                 : '<b>FAIL</b> <i>(unknown PostgreSQL error on releasing recordset)</i>';
                    }
                }
                else
                {
                    $error = pg_last_error($link);

                    $message = ($error)
                             ? '<b>FAIL</b> <i>(PostgreSQL error on fetching data - ' . $error . ')</i>'
                             : '<b>FAIL</b> <i>(unknown PostgreSQL error on fetching data)</i>';
                }
            }
            else
            {
                $error = pg_last_error($link);

                $message = ($error)
                         ? '<b>FAIL</b> <i>(PostgreSQL error on query database - ' . $error . ')</i>'
                         : '<b>FAIL</b> <i>(unknown PostgreSQL error on query database)</i>';
            }

            pg_close($link);
        }
        else
        {
            $message = '<b>FAIL</b> <i>(PostgreSQL server cannot be connected)</i>';
        }

        break;

    default:

        $message = '<b>FAIL</b> <i>(unknown database type in "DATABASE_DRIVER")</i>';
}

?>
</tr><tr>
<td><p class="label">Database:</p></td>
<td><p class="<?php echo(substr($message, 3, 4) == 'PASS' ? 'text' : 'hot'); ?>"><?php echo($message); ?></p></td>
<?php

if (LDAP_ENABLED)
{
    $link = @ldap_connect(LDAP_HOST, LDAP_PORT);

    if ($link)
    {
        if (!@ldap_set_option($link, LDAP_OPT_PROTOCOL_VERSION, 3))
        {
            $message = '<b>FAIL</b> <i>(LDAP protocol version cannot be set - ' . ldap_err2str(ldap_errno($link)) . ')</i>';
        }
        elseif (!@ldap_set_option($link, LDAP_OPT_REFERRALS, 0))
        {
            $message = '<b>FAIL</b> <i>(LDAP protocol option cannot be set - ' . ldap_err2str(ldap_errno($link)) . ')</i>';
        }
        elseif (!@ldap_bind($link, LDAP_USERNAME, LDAP_PASSWORD))
        {
            $message = '<b>FAIL</b> <i>(can\'t bind to LDAP server as \'' . (strlen(LDAP_USERNAME) == 0 ? 'anonymous' : LDAP_USERNAME) . '\' - ' . ldap_err2str(ldap_errno($link)) . ')</i>';
        }
        else
        {
            $message = '<b>PASS</b> <i>(enabled)</i>';
        }

        ldap_close($link);
    }
    else
    {
        $message = '<b>FAIL</b> <i>(LDAP server cannot be connected)</i>';
    }
}
else
{
    $message = '<b>PASS</b> <i>(disabled)</i>';
}

?>
</tr><tr>
<td><p class="label">Active Directory:</p></td>
<td><p class="<?php echo(substr($message, 3, 4) == 'PASS' ? 'text' : 'hot'); ?>"><?php echo($message); ?></p></td>
</tr></table>
</fieldset>
<input type="button" onclick="window.open('../records/index.php','_parent');" value="Back"/>
<!------------------------------------------------------------------------------------------------->
</body>
