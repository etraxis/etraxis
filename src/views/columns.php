<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2007-2010  Artem Rodygin
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
require_once('../dbo/fields.php');
require_once('../dbo/views.php');
/**#@-*/

global $column_type_res;

init_page();

$error = NO_ERROR;

// check that requested view exists

$id   = ustr2int(try_request('id'));
$view = view_find($id);

if (!$view)
{
    debug_write_log(DEBUG_NOTICE, 'View cannot be found.');
    header('Location: index.php');
    exit;
}

// add/remove selected columns

if (try_request('submitted') == 'disabledform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted (adding new columns).');

    if (isset($_REQUEST['lcolumns']))
    {
        $error = columns_add($id, $_REQUEST['lcolumns']);
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, 'No columns are selected.');
    }
}
elseif (try_request('submitted') == 'enabledform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted (removing selected columns).');

    if (isset($_REQUEST['rcolumns']))
    {
        columns_remove($id, $_REQUEST['rcolumns']);
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, 'No columns are selected.');
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');
}

// page's title

$title = ustrprocess(get_html_resource(RES_VIEW_X_ID), ustr2html($view['view_name']));

// generate breadcrumbs and tabs

$xml = '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_VIEWS_ID) . '</breadcrumb>'
     . '<breadcrumb url="columns.php?id=' . $id . '">' . $title . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<tabs>'
     . '<tab url="view.php?id='    . $id . '"><i>'            . ustr2html($view['view_name']) . '</i></tab>'
     . '<tab url="columns.php?id=' . $id . '" active="true">' . get_html_resource(RES_COLUMNS_ID) . '</tab>'
     . '<tab url="filters.php?id=' . $id . '">'               . get_html_resource(RES_FILTERS_ID) . '</tab>'
     . '<content>'
     . '<dual>';

// split all columns of the view into 2 arrays - standard columns, and custom ones

$standard = array();
$custom   = array();

$columns = columns_list($id);

foreach ($columns as $column)
{
    if ($column['column_type'] >= COLUMN_TYPE_MINIMUM &&
        $column['column_type'] <= COLUMN_TYPE_MAXIMUM)
    {
        array_push($standard, $column['column_type']);
    }
    else
    {
        array_push($custom, sprintf('%u:%s:%s',
                                    $column['column_type'],
                                    ustr2csv($column['state_name'], ':', '\''),
                                    ustr2csv($column['field_name'], ':', '\'')));
    }
}

// generate left side

$xml .= '<dualleft>'
      . '<form name="disabledform" action="columns.php?id=' . $id . '">'
      . '<group title="' . get_html_resource(RES_DISABLED2_ID) . '">'
      . '<control name="lcolumns[]">'
      . '<listbox size="10">';

// add all possible standard columns, which are not in the view yet

for ($i = COLUMN_TYPE_MINIMUM; $i <= COLUMN_TYPE_MAXIMUM; $i++)
{
    if (!in_array($i, $standard))
    {
        $xml .= '<listitem value="' . $i . '::">'
              . get_html_resource($column_type_res[$i])
              . '</listitem>';
    }
}

// add all possible custom columns, which are not in the view yet

$fields_to_columns = array
(
    FIELD_TYPE_NUMBER     => COLUMN_TYPE_NUMBER,
    FIELD_TYPE_STRING     => COLUMN_TYPE_STRING,
    FIELD_TYPE_MULTILINED => COLUMN_TYPE_MULTILINED,
    FIELD_TYPE_CHECKBOX   => COLUMN_TYPE_CHECKBOX,
    FIELD_TYPE_RECORD     => COLUMN_TYPE_RECORD,
    FIELD_TYPE_DATE       => COLUMN_TYPE_DATE,
    FIELD_TYPE_DURATION   => COLUMN_TYPE_DURATION,
);

$flist = dal_query('columns/flist.sql', $_SESSION[VAR_USERID]);

while (($item = $flist->fetch()))
{
    // field of "list" type brings 2 kinds of column
    if ($item['field_type'] == FIELD_TYPE_LIST)
    {
        // add numeric variant of the "list" field
        $value = sprintf('%u:%s:%s',
                         COLUMN_TYPE_LIST_NUMBER,
                         ustr2csv($item['state_name'], ':', '\''),
                         ustr2csv($item['field_name'], ':', '\''));

        if (!in_array($value, $custom))
        {
            $xml .= '<listitem value="' . ustr2html($value) . '">'
                  . ustr2html(sprintf('%s: %s (%s)',
                                      $item['state_name'],
                                      $item['field_name'],
                                      get_html_resource($column_type_res[COLUMN_TYPE_LIST_NUMBER])))
                  . '</listitem>';
        }

        // add string variant of the "list" field
        $value = sprintf('%u:%s:%s',
                         COLUMN_TYPE_LIST_STRING,
                         ustr2csv($item['state_name'], ':', '\''),
                         ustr2csv($item['field_name'], ':', '\''));

        if (!in_array($value, $custom))
        {
            $xml .= '<listitem value="' . ustr2html($value) . '">'
                  . ustr2html(sprintf('%s: %s (%s)',
                                      $item['state_name'],
                                      $item['field_name'],
                                      get_html_resource($column_type_res[COLUMN_TYPE_LIST_STRING])))
                  . '</listitem>';
        }
    }
    else
    {
        // add any other field
        $value = sprintf('%u:%s:%s',
                         $fields_to_columns[$item['field_type']],
                         ustr2csv($item['state_name'], ':', '\''),
                         ustr2csv($item['field_name'], ':', '\''));

        if (!in_array($value, $custom))
        {
            $xml .= '<listitem value="' . ustr2html($value) . '">'
                  . ustr2html(sprintf('%s: %s (%s)',
                                      $item['state_name'],
                                      $item['field_name'],
                                      get_html_resource($column_type_res[$fields_to_columns[$item['field_type']]])))
                  . '</listitem>';
        }
    }
}

$xml .= '</listbox>'
      . '</control>'
      . '</group>'
      . '</form>'
      . '</dualleft>';

// generate right side

$xml .= '<dualright>'
      . '<form name="enabledform" action="columns.php?id=' . $id . '">'
      . '<group title="' . get_html_resource(RES_ENABLED2_ID) . '">'
      . '<control name="rcolumns[]">'
      . '<listbox size="10">';

foreach ($columns as $column)
{
    if ($column['column_type'] >= COLUMN_TYPE_MINIMUM &&
        $column['column_type'] <= COLUMN_TYPE_MAXIMUM)
    {
        $text = get_html_resource($column_type_res[$column['column_type']]);
    }
    else
    {
        $text = ustr2html(sprintf('%s: %s (%s)',
                                  $column['state_name'],
                                  $column['field_name'],
                                  get_html_resource($column_type_res[$column['column_type']])));
    }

    $xml .= '<listitem value="' . $column['column_id'] . '">' . $text . '</listitem>';
}

$xml .= '</listbox>'
      . '</control>'
      . '<button action="moveUp()">%and;</button>'
      . '<button action="moveDown()">%or;</button>'
      . '</group>'
      . '</form>'
      . '</dualright>';

// generate buttons

$xml .= '<button action="document.disabledform.submit()">%gt;%gt;</button>'
      . '<button action="document.enabledform.submit()">%lt;%lt;</button>'
      . '</dual>';

// if some error was specified to display, force an alert

switch ($error)
{
    case ERROR_INTEGER_VALUE_OUT_OF_RANGE:
        $xml .= '<script>alert("' . ustrprocess(get_js_resource(RES_ALERT_VIEW_CANNOT_HAVE_MORE_COLUMNS), MAX_VIEW_SIZE) . '");</script>';
        break;
    default: ;  // nop
}

$xml .= '</content>'
      . '</tabs>'
      . '<script src="move.js"></script>';

echo(xml2html($xml, $title));

?>
