#!/usr/local/bin/php
<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2009  Artem Rodygin
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

global $argc, $argv;

if ($argc < 3)
{
    echo("USAGE: xml2xslt.php <xml-file> <xslt-file>\n");
}
elseif (is_file($argv[1]))
{
    $xml = file_get_contents($argv[1]);

    if (version_compare(PHP_VERSION, '5.0.0') >= 0)
    {
        $code = new DOMDocument();
        $xslt = new XSLTProcessor();

        $code->load($argv[2]);
        $xslt->importStyleSheet($code);
        $code->loadXML($xml);

        $html = $xslt->transformToXML($code);
    }
    else
    {
        $arguments = array('/_xml' => '&', '%', $xml);

        $xslt = xslt_create();
        $html = xslt_process($xslt, 'arg:/_xml',
                             'file://' . $argv[2],
                             NULL, $arguments);

        xslt_free($xslt);
    }

    echo($html);
}

?>
