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
require_once('../dbo/filters.php');
require_once('../dbo/views.php');
/**#@-*/

init_page();

// check that requested view exists

$id   = ustr2int(try_request('id'));
$view = view_find($id);

if (!$view)
{
    debug_write_log(DEBUG_NOTICE, 'View cannot be found.');
    header('Location: index.php');
    exit;
}

// add/remove selected filters

if (try_request('submitted') == 'disabledform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted (adding new filters).');

    if (isset($_REQUEST['filters']))
    {
        foreach ($_REQUEST['filters'] as $filter)
        {
            dal_query('views/fcreate.sql', $id, $filter);
        }
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, 'No filters are selected.');
    }

    exit;
}
elseif (try_request('submitted') == 'enabledform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted (removing selected filters).');

    if (isset($_REQUEST['filters']))
    {
        foreach ($_REQUEST['filters'] as $filter)
        {
            dal_query('views/fdelete.sql', $id, $filter);
        }
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, 'No filters are selected.');
    }

    exit;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');
}

// generate left side

$xml = '<dual>'
     . '<dualleft>'
     . '<form name="disabledform" action="filters.php?id=' . $id . '" success="reloadTab">'
     . '<group title="' . get_html_resource(RES_DISABLED2_ID) . '">'
     . '<control name="filters[]">'
     . '<listbox size="10">';

$filters = view_filters_list($id);

$rs = dal_query('filters/list.sql', $_SESSION[VAR_USERID], 'filter_name');

while (($row = $rs->fetch()))
{
    if (!in_array($row['filter_id'], $filters))
    {
        $xml .= '<listitem value="' . $row['filter_id'] . '">'
              . ustr2html(is_null($row['fullname']) ? sprintf('%s', $row['filter_name'])
                                                    : sprintf('%s (%s)', $row['filter_name'], $row['fullname']))
              . '</listitem>';
    }
}

$xml .= '</listbox>'
      . '</control>'
      . '</group>'
      . '</form>'
      . '</dualleft>';

// generate right side

$xml .= '<dualright>'
      . '<form name="enabledform" action="filters.php?id=' . $id . '" success="reloadTab">'
      . '<group title="' . get_html_resource(RES_ENABLED2_ID) . '">'
      . '<control name="filters[]">'
      . '<listbox size="10">';

$rs->seek();

while (($row = $rs->fetch()))
{
    if (in_array($row['filter_id'], $filters))
    {
        $xml .= '<listitem value="' . $row['filter_id'] . '">'
              . ustr2html(is_null($row['fullname']) ? sprintf('%s', $row['filter_name'])
                                                    : sprintf('%s (%s)', $row['filter_name'], $row['fullname']))
              . '</listitem>';
    }
}

$xml .= '</listbox>'
      . '</control>'
      . '</group>'
      . '</form>'
      . '</dualright>';

// generate buttons

$xml .= '<button action="$(\'#disabledform\').submit()">%gt;%gt;</button>'
      . '<button action="$(\'#enabledform\').submit()">%lt;%lt;</button>'
      . '</dual>';

echo(xml2html($xml));

?>
