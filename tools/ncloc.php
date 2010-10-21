#!/usr/local/bin/php
<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2005-2009  Artem Rodygin
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

// Supported file types.
$handlers = array
(
    'c'    => 'ncloc_c',    // C (source)
    'cc'   => 'ncloc_cpp',  // C++ (source)
    'cpp'  => 'ncloc_cpp',  // C++ (source)
    'css'  => 'ncloc_c',    // CSS
    'h'    => 'ncloc_cpp',  // C (header)
    'htm'  => 'ncloc_xml',  // HTML
    'html' => 'ncloc_xml',  // HTML
    'hpp'  => 'ncloc_cpp',  // C++ (header)
    'java' => 'ncloc_cpp',  // Java
    'js'   => 'ncloc_cpp',  // JavaScript
    'php'  => 'ncloc_php',  // PHP
    'qrc'  => 'ncloc_xml',  // QT resource
    'rb'   => 'ncloc_php',  // Ruby
    'sh'   => 'ncloc_sh',   // shell
    'sql'  => 'ncloc_sql',  // SQL
    'xml'  => 'ncloc_xml',  // XML
    'xsl'  => 'ncloc_xml',  // XSLT
);

//  /* ... */
function ncloc_c ($contents)
{
    $contents = preg_replace('!/\*(.*?)\*/!isu', NULL, $contents);

    return $contents;
}

//  /* ... */
//  // ... <EOL>
function ncloc_cpp ($contents)
{
    $contents = preg_replace('!/\*(.*?)\*/!isu', NULL, $contents);
    $contents = preg_replace('!//(.*?)\n!isu',   NULL, $contents);

    return $contents;
}

//  /* ... */
//  // ... <EOL>
//  # ... <EOL>
function ncloc_php ($contents)
{
    $contents = preg_replace('!/\*(.*?)\*/!isu', NULL, $contents);
    $contents = preg_replace('!//(.*?)\n!isu',   NULL, $contents);
    $contents = preg_replace('!#(.*?)\n!isu',    NULL, $contents);

    return $contents;
}

//  # ... <EOL>
function ncloc_sh ($contents)
{
    $contents = preg_replace('!#(.*?)\n!isu', NULL, $contents);

    return $contents;
}

//  /* ... */
//  -- ... <EOL>
function ncloc_sql ($contents)
{
    $contents = preg_replace('!/\*(.*?)\*/!isu', NULL, $contents);
    $contents = preg_replace('!\-\-(.*?)\n!isu', NULL, $contents);

    return $contents;
}

//  <!-- ... -->
function ncloc_xml ($contents)
{
    $contents = preg_replace('!<\!\-\-(.*?)\-\->!isu', NULL, $contents);

    return $contents;
}

function ncloc_file ($path, &$delta)
{
    global $handlers;

    if (is_dir($path))
    {
        $handle = opendir($path);

        if ($handle)
        {
            while (false !== ($file = readdir($handle)))
            {
                if ($file != '.' && $file != '..')
                {
                    ncloc_file("{$path}/{$file}", $delta);
                }
            }

            closedir($handle);
        }
    }
    else
    {
        $path_parts = pathinfo($path);

        if (isset($path_parts['extension']) && in_array($path_parts['extension'], array_keys($handlers)))
        {
            $contents = file_get_contents($path);
            $contents = $handlers[$path_parts['extension']]($contents);

            $ncloc = 0;
            $lines = explode("\n", $contents);

            foreach ($lines as $line)
            {
                if (strlen(trim($line)) != 0) $ncloc++;
            }

            $delta[$path_parts['extension']] += $ncloc;
        }
    }
}

global $argc, $argv;

if ($argc < 2)
{
    echo('USAGE: ncloc.php <path1> <path2> ...');
}
else
{
    $delta = array_fill_keys(array_keys($handlers), 0);

    for ($i = 1; $i < $argc; $i++)
    {
        ncloc_file($argv[$i], $delta);
    }

    $total = array_sum($delta);
    $dline = "NCLOC (total) = {$total} 100.0%";

    if ($total != 0)
    {
        echo(str_repeat('-', strlen($dline)) . "\n");
    }

    foreach ($handlers as $extension => $handler)
    {
        if ($delta[$extension])
        {
            $percent = round($delta[$extension] * 100 / $total, 1);

            if (strpos($percent, '.') === FALSE)
            {
                $percent = $percent . '.0';
            }

            echo("NCLOC ({$extension}) " . str_repeat(' ', strlen('total') - strlen($extension)) . "= " .
                 str_pad($delta[$extension], strlen($total), ' ', STR_PAD_LEFT) .
                 " " . str_pad($percent, strlen('100.0'), ' ', STR_PAD_LEFT) . "%\n");
        }
    }

    if ($total != 0)
    {
        echo(str_repeat('-', strlen($dline)) . "\n");
    }

    echo("NCLOC (total) = {$total}\n");
}

?>
