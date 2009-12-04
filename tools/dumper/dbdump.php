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
//  Artem Rodygin           2006-09-20      Initial creation.
//  Artem Rodygin           2006-10-08      bug-320: /tools/dbdump.php: Global variable $schema was used before it was defined.
//  Artem Rodygin           2006-12-06      Path to PHP interpreter is updated.
//  Artem Rodygin           2007-07-01      bug-537: PHP Fatal error: Allowed memory size of 134217728 bytes exhausted (tried to allocate 16 bytes)
//  Artem Rodygin           2007-08-29      bug-572: /tools/dbdump.php: Assignment in condition.
//  Artem Rodygin           2007-11-26      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2009-03-11      bug-799: eTraxis doesn't work with XAMPP on Windows.
//  Artem Rodygin           2009-08-15      new-824: PHP 4 is discontinued.
//--------------------------------------------------------------------------------------------------

require_once('../../src/engine/config.php');
require_once('../../src/engine/dal.php');

require_once('schema.inc');

set_time_limit(0);

function iwrite ($handle, $value)
{
    if (is_null($value))
    {
        $str = sprintf('%c%c%c%c%c', 0x00, 0x00, 0x00, 0x00, 0x00);
    }
    else
    {
        $str = sprintf('%c%c%c%c%c', 0x01,
                       ($value & 0xFF000000) >> 24,
                       ($value & 0x00FF0000) >> 16,
                       ($value & 0x0000FF00) >> 8,
                       ($value & 0x000000FF));
    }

    if (!fwrite($handle, $str))
    {
        echo("\nERROR: can't write to dump file.\n");
        exit;
    }
}

function swrite ($handle, $value)
{
    if (is_null($value))
    {
        iwrite($handle, NULL);
    }
    else
    {
        $len = strlen($value);
        iwrite($handle, $len);

        if ($len > 0)
        {
            if (!fwrite($handle, $value))
            {
                echo("\nERROR: can't write to dump file.\n");
                exit;
            }
        }
    }
}

function write ($handle, $type, $value)
{
    switch ($type)
    {
        case 'i':
            iwrite($handle, $value);
            break;

        case 's':
        case 't':
            swrite($handle, $value);
            break;

        default: ;  // nop
    }
}

global $schema;

foreach ($schema as $table)
{
    echo(str_pad("Dumping '{$table[TABLE_NAME]}'...", 35, ' ', STR_PAD_RIGHT));

    $handle = fopen("../../dump/{$table[TABLE_NAME]}.dat", 'w');

    if ($handle == FALSE)
    {
        echo("ERROR: dump file cannot be created.\n");
        exit;
    }

    $columns = explode(' ', $table[FIELDS_SET]);

    $rs = new CRecordset("select * from {$table[TABLE_NAME]} order by {$table[FIELDS_ORDER]}");

    iwrite($handle, $rs->rows);

    while (($row = $rs->fetch()))
    {
        foreach ($columns as $column)
        {
            list($type, $name) = split(':', $column);
            write($handle, $type, $row[$name]);
        }
    }

    fclose($handle);

    echo(str_pad("{$rs->rows} record(s).\n", 20, ' ', STR_PAD_LEFT));
}

?>
