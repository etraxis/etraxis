<?php

/**
 * @package eTraxis
 * @ignore
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
//  Artem Rodygin           2005-08-11      new-008: Predefined metrics.
//  Artem Rodygin           2005-08-23      bug-051: Web-server hungs up when empty metrics are being displayed.
//  Artem Rodygin           2005-08-23      new-053: All the calls of DAL API functions should be moved to DBO API.
//  Artem Rodygin           2005-08-25      bug-059: PHP Fatal error: Call to undefined function: record_opened()
//  Artem Rodygin           2005-08-29      bug-066: Metrics of different projects contain same data.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-09-04      bug-072: Time periods in metrics are not valid.
//  Artem Rodygin           2005-09-13      new-117: While project duration is too short, its metrics charts should be extended to the future.
//  Artem Rodygin           2006-06-14      bug-268: PHP Warning: ociexecute(): OCIStmtExecute: ORA-00923: FROM keyword not found where expected
//  Artem Rodygin           2006-11-04      new-370: Charts: minimal cell width should be decreased.
//  Artem Rodygin           2006-11-07      bug-375: /src/projects/opened.php: Page is not closed.
//  Artem Rodygin           2007-07-14      new-545: Chart legend is required.
//  Artem Rodygin           2007-11-26      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2008-04-12      bug-702: Apostrophe in metrics chart title is displayed as sequence of "&#039;".
//  Artem Rodygin           2008-11-18      new-762: Forward logged in user to the page he has tried to open before authentication.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//--------------------------------------------------------------------------------------------------

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
