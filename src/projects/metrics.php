<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2005-2012  Artem Rodygin
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
 * @package eTraxis
 * @ignore
 */

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/projects.php');
/**#@-*/

init_page(LOAD_TAB, GUEST_IS_ALLOWED);

// check that requested project exists

$id      = ustr2int(try_request('id'));
$project = project_find($id);

if (!$project)
{
    debug_write_log(DEBUG_NOTICE, 'Project cannot be found.');
    exit;
}

// prepare charts data

$first_week = intval(floor($project['start_time'] / SECS_IN_WEEK));
$last_week  = intval(floor(time()                 / SECS_IN_WEEK));

$created = array_fill($first_week, $last_week - $first_week + 1, 0);
$closed  = array_fill($first_week, $last_week - $first_week + 1, 0);

$rso = record_opened($id);
$rsc = record_closed($id);

while (($row = $rso->fetch()))
{
    if (!is_null($row['amount']))
    {
        $created[$row['week']] = $row['amount'];
    }
}

while (($row = $rsc->fetch()))
{
    if (!is_null($row['amount']))
    {
        $closed[$row['week']] = $row['amount'];
    }
}

$opened = array();
$count  = 0;

for ($i = $first_week; $i <= $last_week; $i++)
{
    $count += $created[$i];
    $count -= $closed[$i];

    $opened[$i] = $count;
}

// determine best maximum value and tick interval

$maxvalue = max(max($opened), max($created), max($closed));
$interval = ceil($maxvalue / 20);

$digit = intval(substr($interval, 0, 1));

switch ($digit)
{
    case 3:
    case 4:
        $digit = 5;
        break;
    case 6:
    case 7:
    case 8:
    case 9:
        $digit = 10;
        break;
}

$interval = intval($digit . str_pad('', ustrlen($interval) - 1, '0', STR_PAD_RIGHT));
$maxvalue = ceil($maxvalue / max($interval, 1)) * $interval;

// prepare date formatting string

global $locale_info;

$lang   = (isset($_SESSION[VAR_LOCALE]) ? $_SESSION[VAR_LOCALE] : LANG_DEFAULT);
$format = $locale_info[$lang][LOCALE_DATE_FORMAT];

$format = str_replace('d', '%d',  $format);
$format = str_replace('j', '%#d', $format);
$format = str_replace('m', '%m',  $format);
$format = str_replace('n', '%#m', $format);
$format = str_replace('Y', '%Y',  $format);
$format = str_replace('y', '%y',  $format);

// generate charts

$xml = '<div id="chart"></div>';

$titleChart   = get_html_resource(RES_CREATION_VS_CLOSURE_ID);
$titleOpened  = ustrtolower(get_html_resource(RES_OPENED_RECORDS_ID));
$titleCreated = get_html_resource(RES_CREATED_RECORDS_ID);
$titleClosed  = get_html_resource(RES_CLOSED_RECORDS_ID);

if (stripos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== FALSE)
{
    $xml .= '<script type="text/javascript" src="../scripts/get.php?name=excanvas.min.js"></script>';
}

$xml .= '<script type="text/javascript" src="../scripts/get.php?name=jqplot/jquery.jqplot.min.js"></script>'
      . '<script type="text/javascript" src="../scripts/get.php?name=jqplot/jqplot.cursor.min.js"></script>'
      . '<script type="text/javascript" src="../scripts/get.php?name=jqplot/jqplot.dateAxisRenderer.min.js"></script>'
      . '<script type="text/javascript" src="../scripts/get.php?name=jqplot/jqplot.highlighter.min.js"></script>'
      . '<script>'
      . 'var dataOpened  = [];'
      . 'var dataCreated = [];'
      . 'var dataClosed  = [];';

for ($i = $first_week; $i <= $last_week; $i++)
{
    $date = date('Y-m-d', $i * SECS_IN_WEEK + 4 * SECS_IN_DAY);

    $xml .= sprintf("dataOpened.push(['%s',%d]);",  $date, $opened[$i]);
    $xml .= sprintf("dataCreated.push(['%s',%d]);", $date, $created[$i]);
    $xml .= sprintf("dataClosed.push(['%s',%d]);",  $date, $closed[$i]);
}

$xml .= <<<jqPlot

    $('#chart').css('height', 0);   // workaround for excanvas bug in IE

    var chart = $.jqplot('chart', [dataOpened, dataCreated, dataClosed], {

        title: '{$titleChart}',

        legend: {
            show: true,
            location: 'nw'
        },

        axes: {
            xaxis: {
                renderer: $.jqplot.DateAxisRenderer,
                tickOptions: {formatString:'{$format}'}
            },
            yaxis: {
                min: 0,
                max: {$maxvalue},
                tickInterval: {$interval},
                tickOptions: {formatString:'%d'}
            }
        },

        cursor: {
            show: true,
            showVerticalLine: true,
            showHorizontalLine: false,
            showCursorLegend: false,
            showTooltip: false,
            zoom: false
        },

        highlighter: {
            show: true,
            sizeAdjust: 1
        },

        series: [
            {label:'{$titleOpened}',  showMarker:false},
            {label:'{$titleCreated}', showMarker:false, color:'#FF6347'},
            {label:'{$titleClosed}',  showMarker:false, color:'#1E90FF'}
        ]

    });

jqPlot;

$xml .= '</script>';

echo(xml2html($xml));

?>
