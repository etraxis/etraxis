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

/**
 * @package eTraxis
 * @ignore
 */

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/projects.php');
require_once('../dbo/records.php');
/**#@-*/

init_page(GUEST_IS_ALLOWED);

$type    = try_request('type', METRICS_OPENED_RECORDS);
$id      = ustr2int(try_request('id'));
$project = project_find($id);

if (!$project)
{
    debug_write_log(DEBUG_NOTICE, 'Project cannot be found.');
    header('Location: index.php');
    exit;
}

$chart = new CChart(get_resource($type == METRICS_OPENED_RECORDS ? RES_OPENED_RECORDS_ID : RES_CREATION_VS_CLOSURE_ID));

$chart->x_axis = new CAxis(get_resource(RES_WEEK_ID));
$chart->y_axis = new CAxis(get_resource(RES_NUMBER2_ID));

$rso = record_opened($id);
$rsc = record_closed($id);

$first_week = min($rso->rows == 0 ? intval(floor(time() / SECS_IN_WEEK)) : $rso->fetch('week'),
                  $rsc->rows == 0 ? intval(floor(time() / SECS_IN_WEEK)) : $rsc->fetch('week'));

$rso->seek($rso->rows - 1);
$rsc->seek($rsc->rows - 1);

$last_week  = max($rso->rows == 0 ? intval(floor(time() / SECS_IN_WEEK)) : $rso->fetch('week'),
                  $rsc->rows == 0 ? intval(floor(time() / SECS_IN_WEEK)) : $rsc->fetch('week'));

$weeks = array();

for ($i = $first_week; $i <= $last_week; $i++)
{
    array_push($weeks, $i);

    if (($i - $first_week) % 3 == 0)
    {
        array_push($chart->x_axis->markers, get_date($i * SECS_IN_WEEK + 4 * SECS_IN_DAY));
    }
    else
    {
        array_push($chart->x_axis->markers, NULL);
    }
}

$created = array_fill(0, count($weeks), 0);
$rso->seek();

while (($row = $rso->fetch()))
{
    $created[$row['week'] - $weeks[0]] = (is_null($row['amount']) ? 0 : $row['amount']);
}

$closed = array_fill(0, count($weeks), 0);
$rsc->seek();

while (($row = $rsc->fetch()))
{
    $closed[$row['week'] - $weeks[0]] = (is_null($row['amount']) ? 0 : $row['amount']);
}

if ($type == METRICS_OPENED_RECORDS)
{
    $count  = 0;
    $opened = array();

    for ($i = 0; $i < count($created); $i++)
    {
        $count += $created[$i];
        $count -= $closed[$i];

        array_push($opened, $count);
    }

    $chart->legend                = new CLegend();
    $chart->legend->markers       = array(ustrtolower(get_resource(RES_OPENED_RECORDS_ID)));
    $chart->legend->markers_color = array(COLOR_RED);

    $chart->y_axis->init($opened);
}
else
{
    $chart->legend                = new CLegend();
    $chart->legend->markers       = array(get_resource(RES_CREATED_RECORDS_ID), get_resource(RES_CLOSED_RECORDS_ID));
    $chart->legend->markers_color = array(COLOR_RED, COLOR_BLUE);

    $chart->y_axis->init($created);
    $chart->y_axis->init($closed);
}

$chart->x_axis->rotate_text = TRUE;
$chart->x_axis->cell_width /= 2.5;

$chart->init();
$chart->drawbase();

if ($type == METRICS_OPENED_RECORDS)
{
    $chart->drawline($weeks, $opened, COLOR_RED);
}
else
{
    $chart->drawline($weeks, $created, COLOR_RED);
    $chart->drawline($weeks, $closed,  COLOR_BLUE);
}

header('Content-type: image/png');
imagepng($chart->image);
imagedestroy($chart->image);

?>
