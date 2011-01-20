<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2006-2011  Artem Rodygin
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
require_once('../dbo/records.php');
require_once('../dbo/views.php');
/**#@-*/

global $column_type_align;
global $column_type_res;

init_page(GUEST_IS_ALLOWED);

// get list of records

$columns = columns_list();

$sort = $page = NULL;
$list = records_list($columns, $sort, $page, $_SESSION[VAR_SEARCH_MODE], $_SESSION[VAR_SEARCH_TEXT]);

// generate HTTP headers

header('Pragma: private');
header('Cache-Control: private, must-revalidate');
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="etraxis.csv"');

// generate header of the list

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

// generate list of records

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

            case COLUMN_TYPE_STATE_ABBR:
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
                array_push($data, get_record_last_event($row));
                break;

            case COLUMN_TYPE_AGE:
                array_push($data, get_record_age($row));
                break;

            case COLUMN_TYPE_CREATION_DATE:
                array_push($data, ustr2csv(get_date($row['creation_time']), $_SESSION[VAR_DELIMITER]));
                break;

            case COLUMN_TYPE_TEMPLATE:
                array_push($data, ustr2csv($row['template_name'], $_SESSION[VAR_DELIMITER]));
                break;

            case COLUMN_TYPE_STATE_NAME:
                array_push($data, ustr2csv($row['state_name'], $_SESSION[VAR_DELIMITER]));
                break;

            case COLUMN_TYPE_LAST_STATE:
                array_push($data, ustr2csv(get_record_last_state($row), $_SESSION[VAR_DELIMITER]));
                break;

            case COLUMN_TYPE_NUMBER:
            case COLUMN_TYPE_LIST_NUMBER:
                array_push($data, $row['value' . $column['column_id']]);
                break;

            case COLUMN_TYPE_STRING:
            case COLUMN_TYPE_MULTILINED:
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
