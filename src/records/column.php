<?php

/**
 * @package eTraxis
 * @ignore
 */

//--------------------------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system.
//  Copyright (C) 2007-2010 by Artem Rodygin
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
//  Artem Rodygin           2007-11-05      new-571: View should show all records of current filters set.
//  Artem Rodygin           2007-11-11      bug-624: dbx_error(): Too many tables; MySQL can only use 61 tables in a join
//  Artem Rodygin           2007-11-11      bug-615: Arrow buttons are broken for Windows clients.
//  Artem Rodygin           2007-11-13      bug-621: Enabled column doesn't disappear from "disabled" list, when contains '&' in its name.
//  Artem Rodygin           2007-11-27      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2008-04-30      bug-699: Views // Names of custom columns are duplicated in the list of available columns, when there are two fields of different types with the same name.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-01-24      bug-790: Can't move columns of custom view up and down in the list (Safari).
//  Artem Rodygin           2009-03-05      bug-789: Custom fields show empty values in a view (PostgreSQL).
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//  Artem Rodygin           2010-02-05      bug-912: IE6 buttons with arrows rendering problem
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/accounts.php');
require_once('../dbo/fields.php');
require_once('../dbo/views.php');
/**#@-*/

global $column_type_res;

init_page();

$id = try_request('id', 0);

if (try_request('submitted') == 'lform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted (adding columns).');

    if (isset($_REQUEST['lcolumns']))
    {
        $error = columns_set($_REQUEST['lcolumns']);

        switch ($error)
        {
            case ERROR_INTEGER_VALUE_OUT_OF_RANGE:
                $alert = ustrprocess(get_js_resource(RES_ALERT_VIEW_CANNOT_HAVE_MORE_COLUMNS), MAX_VIEW_SIZE);
                break;
            default:
                $alert = NULL;
        }
    }
}
elseif (try_request('submitted') == 'rform')
{
    $action = try_request('move');

    if ($action == 'up')
    {
        debug_write_log(DEBUG_NOTICE, 'Data are submitted (moving up).');

        $column = column_find($id);

        if ($column)
        {
            if ($column['column_order'] > 1)
            {
                dal_query('columns/setorder.sql', $_SESSION[VAR_USERID], $column['column_order'], 0);
                dal_query('columns/setorder.sql', $_SESSION[VAR_USERID], $column['column_order'] - 1, $column['column_order']);
                dal_query('columns/setorder.sql', $_SESSION[VAR_USERID], 0, $column['column_order'] - 1);

                account_set_view($_SESSION[VAR_USERID]);
            }
        }
    }
    elseif ($action == 'dn')
    {
        debug_write_log(DEBUG_NOTICE, 'Data are submitted (moving down).');

        $column = column_find($id);

        if ($column)
        {
            $count = columns_count();

            if ($column['column_order'] < $count)
            {
                dal_query('columns/setorder.sql', $_SESSION[VAR_USERID], $column['column_order'], 0);
                dal_query('columns/setorder.sql', $_SESSION[VAR_USERID], $column['column_order'] + 1, $column['column_order']);
                dal_query('columns/setorder.sql', $_SESSION[VAR_USERID], 0, $column['column_order'] + 1);

                account_set_view($_SESSION[VAR_USERID]);
            }
        }
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, 'Data are submitted (removing columns).');

        if (isset($_REQUEST['rcolumns']))
        {
            columns_clear($_REQUEST['rcolumns']);
        }
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');
}

$xml = '<page' . gen_xml_page_header(get_html_resource(RES_COLUMNS_ID), isset($alert) ? $alert : NULL) . '>'
     . gen_xml_menu()
     . '<path>'
     . gen_xml_rec_root()
     . '<pathitem url="column.php">' . get_html_resource(RES_COLUMNS_ID) . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<dualbox>'
     . '<dualleft action="column.php">'
     . '<group title="' . get_html_resource(RES_DISABLED2_ID) . '">'
     . '<listbox dualbox="true" name="lcolumns[]" size="' . HTML_LISTBOX_SIZE . '" multiple="true">';

$standard = array();
$custom   = array();

$columns = column_list();

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

for ($i = COLUMN_TYPE_MINIMUM; $i <= COLUMN_TYPE_MAXIMUM; $i++)
{
    if (!in_array($i, $standard))
    {
        $xml .= '<listitem value="' . $i . '::">'
              . get_html_resource($column_type_res[$i])
              . '</listitem>';
    }
}

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
    if ($item['field_type'] == FIELD_TYPE_LIST)
    {
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
      . '</group>'
      . '</dualleft>'
      . '<dualright action="column.php">'
      . '<group title="' . get_html_resource(RES_ENABLED2_ID) . '">'
      . '<listbox dualbox="true" name="rcolumns[]" size="' . HTML_LISTBOX_SIZE . '" multiple="true">';

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

    $xml .= '<listitem value="' . $column['column_id'] . ($column['column_id'] == $id ? '" selected="true">' : '">')
          . $text
          . '</listitem>';
}

$xml .= '</listbox>'
      . '<button action="rform.action=\'column.php?move=up&amp;id=\'+document.getElementById(\'rcolumns[]\').value; rform.submit();">' . ustr2html('%and;') . '</button>'
      . '<button action="rform.action=\'column.php?move=dn&amp;id=\'+document.getElementById(\'rcolumns[]\').value; rform.submit();">' . ustr2html('%or;')  . '</button>'
      . '<nbsp/>'
      . '<button url="vcreate.php">' . get_html_resource(RES_SAVE_VIEW_ID) . '</button>'
      . '</group>'
      . '</dualright>'
      . '</dualbox>'
      . '<button default="true" url="index.php">' . get_html_resource(RES_BACK_ID) . '</button>'
      . '<button url="vindex.php">' . get_html_resource(RES_VIEWS_ID) . '</button>'
      . '</content>'
      . '</page>';

echo(xml2html($xml));

?>
