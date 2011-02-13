<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2010  Artem Rodygin
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
require_once('../dbo/views.php');
/**#@-*/

global $column_type_res;

init_page(LOAD_TAB);

$error = NO_ERROR;

// check that requested column exists

$id     = ustr2int(try_request('id'));
$column = column_find($id);

if (!$column)
{
    debug_write_log(DEBUG_NOTICE, 'Column cannot be found.');
    exit;
}

// move the column

$offset = ustr2int(try_request('offset'), -1, +1);
$count  = columns_count($column['view_id']);

if ($column['column_order'] + $offset >= 1 &&
    $column['column_order'] + $offset <= $count)
{
    dal_query('columns/setorder.sql', $column['view_id'], $column['column_order'], 0);
    dal_query('columns/setorder.sql', $column['view_id'], $column['column_order'] + $offset, $column['column_order']);
    dal_query('columns/setorder.sql', $column['view_id'], 0, $column['column_order'] + $offset);

    $xml = '<container>';

    $list = columns_list($column['view_id']);

    foreach ($list as $item)
    {
        if ($item['column_type'] >= COLUMN_TYPE_MINIMUM &&
            $item['column_type'] <= COLUMN_TYPE_MAXIMUM)
        {
            $text = get_html_resource($column_type_res[$item['column_type']]);
        }
        else
        {
            $text = ustr2html(sprintf('%s: %s (%s)',
                                      $item['state_name'],
                                      $item['field_name'],
                                      get_html_resource($column_type_res[$item['column_type']])));
        }

        $xml .= ($item['column_id'] == $id
                    ? '<listitem value="' . $item['column_id'] . '" selected="true">'
                    : '<listitem value="' . $item['column_id'] . '">')
              . $text
              . '</listitem>';
    }

    $xml .= '</container>';

    echo(xml2html($xml));
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Column cannot be moved - will be out of range.');
}

?>
