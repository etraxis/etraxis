<?php

/**
 * @package eTraxis
 * @ignore
 */

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
//  Artem Rodygin           2006-11-07      new-366: Export to CSV.
//  Artem Rodygin           2006-11-15      bug-382: CVS-file is opened in browser without prompt to save it.
//  Artem Rodygin           2006-11-15      bug-386: PHP Warning: Cannot modify header information
//  Artem Rodygin           2006-11-25      new-377: Custom views.
//  Artem Rodygin           2006-11-26      bug-404: Invalid values of 'date' and 'duration' fields in view.
//  Artem Rodygin           2006-11-30      bug-403: Export to CSV doesn't work.
//  Artem Rodygin           2006-12-01      bug-410: View does not show values of custom fields.
//  Artem Rodygin           2007-02-03      new-496: [SF1650934] to show value of "list" instead of index in "records" list
//  Artem Rodygin           2007-09-12      new-576: [SF1788286] Export to CSV
//  Artem Rodygin           2007-09-13      new-566: Choose encoding for record dump and export of records list.
//  Artem Rodygin           2007-11-05      new-571: View should show all records of current filters set.
//  Artem Rodygin           2007-11-13      new-599: Separated "Age" in custom views.
//  Artem Rodygin           2007-11-27      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2008-04-30      bug-714: Empty date fields show '12/31/1969' in a custom view.
//  Artem Rodygin           2008-05-01      new-715: Show creation time in the list of records.
//  Artem Rodygin           2008-11-18      new-762: Forward logged in user to the page he has tried to open before authentication.
//  Artem Rodygin           2009-03-30      bug-811: Multilined text is cut on export to CSV.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//  Artem Rodygin           2009-10-01      new-845: Template name as standard column type.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/records.php');
require_once('../dbo/views.php');
/**#@-*/

global $column_type_align;
global $column_type_res;

init_page(GUEST_IS_ALLOWED);

$search_mode = try_cookie(COOKIE_SEARCH_MODE, FALSE);
$search_text = try_cookie(COOKIE_SEARCH_TEXT);

$columns = column_list();

$sort = $page = NULL;
$list = record_list($columns, $sort, $page, $search_mode, $search_text);

header('Pragma: private');
header('Cache-Control: private, must-revalidate');
header('Content-type: text/csv');
header('Content-Disposition: attachment; filename=etraxis.csv');

$data = array();

foreach ($columns as $column)
{
    if ($column['column_type'] >= COLUMN_TYPE_MINIMUM &&
        $column['column_type'] <= COLUMN_TYPE_MAXIMUM)
    {
        $title = get_html_resource($column_type_res[$column['column_type']]);
    }
    else
    {
        $title = $column['field_name'];
    }

    array_push($data, ustr2csv($title, $_SESSION[VAR_DELIMITER]));
}

$csv = implode($_SESSION[VAR_DELIMITER], $data) . $_SESSION[VAR_LINE_ENDINGS];

if ($_SESSION[VAR_ENCODING] != 'UTF-8')
{
    $csv = iconv('UTF-8', $_SESSION[VAR_ENCODING], $csv);
}

echo($csv);

while (($row = $list->fetch()))
{
    $data = array();

    foreach ($columns as $column)
    {
        switch ($column['column_type'])
        {
            case COLUMN_TYPE_ID:
                array_push($data, ustr2csv(record_id($row['record_id'], $row['template_prefix']), $_SESSION[VAR_DELIMITER]));
                break;

            case COLUMN_TYPE_STATE:
                array_push($data, ustr2csv($row['state_abbr'], $_SESSION[VAR_DELIMITER]));
                break;

            case COLUMN_TYPE_PROJECT:
                array_push($data, ustr2csv($row['project_name'], $_SESSION[VAR_DELIMITER]));
                break;

            case COLUMN_TYPE_SUBJECT:
                array_push($data, ustr2csv($row['subject'], $_SESSION[VAR_DELIMITER]));
                break;

            case COLUMN_TYPE_AUTHOR:
                array_push($data, ustr2csv($row['author_fullname'], $_SESSION[VAR_DELIMITER]));
                break;

            case COLUMN_TYPE_RESPONSIBLE:
                array_push($data, ustr2csv($row['responsible_fullname'], $_SESSION[VAR_DELIMITER]));
                break;

            case COLUMN_TYPE_LAST_EVENT:
                array_push($data, ustr2csv(get_record_last_event($row), $_SESSION[VAR_DELIMITER]));
                break;

            case COLUMN_TYPE_AGE:
                array_push($data, ustr2csv(get_record_age($row), $_SESSION[VAR_DELIMITER]));
                break;

            case COLUMN_TYPE_CREATION_DATE:
                array_push($data, ustr2csv(get_date($row['creation_time']), $_SESSION[VAR_DELIMITER]));
                break;

            case COLUMN_TYPE_TEMPLATE:
                array_push($data, ustr2csv($row['template_name'], $_SESSION[VAR_DELIMITER]));
                break;

            case COLUMN_TYPE_NUMBER:
            case COLUMN_TYPE_STRING:
            case COLUMN_TYPE_MULTILINED:
            case COLUMN_TYPE_LIST_NUMBER:
            case COLUMN_TYPE_LIST_STRING:
                array_push($data, ustr2csv($row['value' . $column['column_id']], $_SESSION[VAR_DELIMITER]));
                break;

            case COLUMN_TYPE_CHECKBOX:

                if (is_null($row['value' . $column['column_id']]))
                {
                    array_push($data, NULL);
                }
                else
                {
                    array_push($data, ustr2csv(bool2sql($row['value' . $column['column_id']]), $_SESSION[VAR_DELIMITER]));
                }

                break;

            case COLUMN_TYPE_RECORD:

                if (is_null($row['value' . $column['column_id']]))
                {
                    array_push($data, NULL);
                }
                else
                {
                    array_push($data, ustr2csv(record_id($row['value' . $column['column_id']]), $_SESSION[VAR_DELIMITER]));
                }

                break;

            case COLUMN_TYPE_DATE:

                if (is_null($row['value' . $column['column_id']]))
                {
                    array_push($data, NULL);
                }
                else
                {
                    array_push($data, ustr2csv(get_date($row['value' . $column['column_id']]), $_SESSION[VAR_DELIMITER]));
                }

                break;

            case COLUMN_TYPE_DURATION:

                if (is_null($row['value' . $column['column_id']]))
                {
                    array_push($data, NULL);
                }
                else
                {
                    array_push($data, ustr2csv(time2ustr($row['value' . $column['column_id']]), $_SESSION[VAR_DELIMITER]));
                }

                break;

            default:
                debug_write_log(DEBUG_WARNING, 'Unknown column type.');
                array_push($data, NULL);
        }
    }

    $csv = implode($_SESSION[VAR_DELIMITER], $data) . $_SESSION[VAR_LINE_ENDINGS];

    if ($_SESSION[VAR_ENCODING] != 'UTF-8')
    {
        $csv = iconv('UTF-8', $_SESSION[VAR_ENCODING], $csv);
    }

    echo($csv);
}

?>
