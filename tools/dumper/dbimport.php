#!/usr/local/bin/php
<?php

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
//  Artem Rodygin           2006-09-22      Initial creation.
//  Artem Rodygin           2006-10-08      bug-321: /tools/dbimport.php: Global variable $schema was used before it was defined.
//  Artem Rodygin           2006-12-01      bug-408: Database import script corrupts text values.
//  Artem Rodygin           2006-12-06      Path to PHP interpreter is updated.
//  Artem Rodygin           2006-12-09      bug-427: MySQL losts backslashes.
//  Artem Rodygin           2007-11-26      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2009-03-11      bug-799: eTraxis doesn't work with XAMPP on Windows.
//  Artem Rodygin           2009-08-15      new-824: PHP 4 is discontinued.
//--------------------------------------------------------------------------------------------------

require_once('../../src/engine/config.php');
require_once('../../src/engine/utility.php');
require_once('../../src/engine/dal.php');

require_once('schema.inc');

set_time_limit(0);

function iread ($handle)
{
    $str = fread($handle, 5);

    if (!$str)
    {
        echo("\nERROR: can't read integer from dump file.\n");
        exit;
    }

    if (ord($str[0]) == 0)
    {
        return NULL;
    }
    else
    {
        return (ord($str[1]) << 24) | (ord($str[2]) << 16) | (ord($str[3]) << 8) | ord($str[4]);
    }
}

function sread ($handle)
{
    $len = iread($handle);

    if (is_null($len))
    {
        return NULL;
    }
    elseif ($len == 0)
    {
        return '';
    }
    else
    {
        $str = fread($handle, $len);

        if (strlen($str) != $len)
        {
            echo("\nERROR: can't read string from dump file.\n");
            exit;
        }

        return $str;
    }
}

global $schema;

foreach ($schema as $table)
{
    echo(str_pad("Importing '{$table[TABLE_NAME]}'...", 37, ' ', STR_PAD_RIGHT));

    $handle = fopen("../../dump/{$table[TABLE_NAME]}.dat", 'rb');

    if ($handle == FALSE)
    {
        echo("ERROR: dump file cannot be opened.\n");
        exit;
    }

    $columns = explode(' ', $table[FIELDS_SET]);

    $lastid = 0;
    $count  = iread($handle);

    for ($i = 0; $i < $count; $i++)
    {
        $fields = array();
        $values = array();

        foreach ($columns as $k => $column)
        {
            list($type, $name) = split(':', $column);

            if ($table[AUTOINCREMENT] && $k == 0)
            {
                $id_field = $name;
                $id_value = iread($handle);
            }
            else
            {
                array_push($fields, $name);

                switch ($type)
                {
                    case 'i':
                        $value = iread($handle);
                        array_push($values, (is_null($value) ? 'NULL' : $value));
                        break;

                    case 's':
                    case 't':
                        $value = sread($handle);
                        array_push($values, (is_null($value) ? 'NULL' : '\'' . ustr2sql($value) . '\''));
                        break;

                    default: ;  // nop
                }
            }
        }

        $fields = implode(', ', $fields);
        $values = implode(', ', $values);

        $sql = "insert into {$table[TABLE_NAME]} ({$fields}) values ({$values})";

        new CRecordset($sql);
        $lastid++;

        if ($table[AUTOINCREMENT])
        {
            while ($lastid < $id_value)
            {
                new CRecordset("delete from {$table[TABLE_NAME]} where {$id_field} = {$lastid}");
                new CRecordset($sql);
                $lastid++;
            }
        }
    }

    fclose($handle);

    echo(str_pad("{$count} record(s).\n", 20, ' ', STR_PAD_LEFT));
}

?>
