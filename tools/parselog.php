#!/usr/local/bin/php
<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2005-2007  Artem Rodygin
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

define('MAX_PHP_SIZE', 100000);  // bytes
define('MAX_PHP_TIME', 5.0);     // seconds
define('MAX_SQL_TIME', 3.0);     // seconds

set_time_limit(0);

$php_time_num = 0;
$php_time_sum = 0;
$php_time_max = 0;

$php_size_num = 0;
$php_size_sum = 0;
$php_size_max = 0;

$sql_time_num = 0;
$sql_time_sum = 0;
$sql_time_max = 0;

$sql_nmbr_max = 0;

$warnings = 0;
$errors   = 0;

function analyse_file ($filename)
{
    global $php_time_num;
    global $php_time_sum;
    global $php_time_max;

    global $php_size_num;
    global $php_size_sum;
    global $php_size_max;

    global $sql_time_num;
    global $sql_time_sum;
    global $sql_time_max;

    global $sql_nmbr_max;

    global $warnings;
    global $errors;

    $sql_nmbr  = 0;

    echo("LOG: {$filename}\n");

    $line = 0;

    $handle = fopen($filename, 'r');

    while (!feof($handle))
    {
        $line++;

        $str = trim(substr(fgets($handle), 22));

        $line += substr_count($str, "\r");

        switch (trim(substr($str, 0, 11)))
        {
            case '[OPENED]':
                $sql_nmbr = 0;
                break;

            case '[CLOSED]':
                $sql_nmbr_max = max($sql_nmbr_max, $sql_nmbr);
                break;

            case '[WARNING]':
                $warnings++;
                break;

            case '[ERROR]':
                echo("*** Error at line {$line}.\n");
                $errors++;
                break;

            case '[PERFORM]':

                $data  = substr($str, 11);
                $value = floatval(trim(substr($data, 11)));

                switch (trim(substr($data, 0, 9)))
                {
                    case 'PHP time':

                        $php_time_num++;
                        $php_time_sum += $value;
                        $php_time_max = max($php_time_max, $value);

                        if ($value > MAX_PHP_TIME)
                        {
                            echo("*** Too large PHP time ({$value}) at line {$line}.\n");
                        }

                        break;

                    case 'page size':

                        $php_size_num++;
                        $php_size_sum += $value;
                        $php_size_max = max($php_size_max, $value);

                        if ($value > MAX_PHP_SIZE)
                        {
                            echo("*** Too large PHP size ({$value}) at line {$line}.\n");
                        }

                        break;

                    case 'SQL time':

                        $sql_nmbr++;
                        $sql_time_num++;
                        $sql_time_sum += $value;
                        $sql_time_max = max($sql_time_max, $value);

                        if ($value > MAX_SQL_TIME)
                        {
                            echo("*** Too large SQL time ({$value}) at line {$line}.\n");
                        }

                        break;
                }

                break;
        }
    }

    fclose($handle);
}

global $argc, $argv;

if ($argc < 2)
{
    echo('USAGE: parselog.php <log>');
}
else
{
    if (is_dir("{$argv[1]}"))
    {
        $handle = opendir($argv[1]);

        if ($handle)
        {
            while (false !== ($file = readdir($handle)))
            {
                if (!is_dir("{$argv[1]}/{$file}") && substr($file, strlen($file) - 4) == '.log')
                {
                    analyse_file("{$argv[1]}/{$file}");
                }
            }

            closedir($handle);
        }
    }
    else
    {
        analyse_file($argv[1]);
    }

    $php_time_avg = ($php_time_num == 0 ? 0 : $php_time_sum / $php_time_num);
    $php_size_avg = ($php_size_num == 0 ? 0 : $php_size_sum / $php_size_num);
    $sql_time_avg = ($sql_time_num == 0 ? 0 : $sql_time_sum / $sql_time_num);

    echo("\n");
    echo("PHP Time (avg): {$php_time_avg}\n");
    echo("PHP Time (max): {$php_time_max}\n");
    echo("\n");
    echo("PHP Size (avg): {$php_size_avg}\n");
    echo("PHP Size (max): {$php_size_max}\n");
    echo("\n");
    echo("SQL Time (avg): {$sql_time_avg}\n");
    echo("SQL Time (max): {$sql_time_max}\n");
    echo("\n");
    echo("SQL Nmbr (max): {$sql_nmbr_max}\n");
    echo("\n");
    echo("Warnings: {$warnings}\n");
    echo("Errors:   {$errors}\n");
}

?>
