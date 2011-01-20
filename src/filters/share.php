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
require_once('../dbo/projects.php');
require_once('../dbo/templates.php');
require_once('../dbo/filters.php');
/**#@-*/

init_page();

// check that requested filter exists

$id     = ustr2int(try_request('id'));
$filter = filter_find($id);

if (!$filter)
{
    debug_write_log(DEBUG_NOTICE, 'Filter cannot be found.');
    header('Location: ../index.php');
    exit;
}

// add/remove selected groups

if (try_request('submitted') == 'othersform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted (adding new groups).');

    if (isset($_REQUEST['groups']))
    {
        foreach ($_REQUEST['groups'] as $group)
        {
            dal_query('filters/fshcreate.sql', $id, $group);
        }
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, 'No groups are selected.');
    }

    exit;
}
elseif (try_request('submitted') == 'allowedform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted (removing selected groups).');

    if (isset($_REQUEST['groups']))
    {
        foreach ($_REQUEST['groups'] as $group)
        {
            dal_query('filters/fshdelete.sql', $id, $group);
        }
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, 'No groups are selected.');
    }

    exit;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    // determine filter's project
    switch ($filter['filter_type'])
    {
        case FILTER_TYPE_ALL_PROJECTS:

            $project_id = 0;

            break;

        case FILTER_TYPE_ALL_TEMPLATES:

            $project    = project_find($filter['filter_param']);
            $project_id = ($project ? $project['project_id'] : 0);

            break;

        case FILTER_TYPE_ALL_STATES:
        case FILTER_TYPE_SEL_STATES:

            $template   = template_find($filter['filter_param']);
            $project_id = ($template ? $template['project_id'] : 0);

            break;

        default:

            $project_id = 0;

            debug_write_log(DEBUG_WARNING, 'Unknown filter type = ' . $filter['filter_type']);
    }
}

// generate left side

$xml = '<dual>'
     . '<dualleft>'
     . '<form name="othersform" action="share.php?id=' . $id . '" success="reloadTab">'
     . '<group title="' . get_html_resource(RES_OTHERS_ID) . '">'
     . '<control name="groups[]">'
     . '<listbox size="10">';

$rs = dal_query('filters/sharing.sql', $id, $project_id);

while (($row = $rs->fetch()))
{
    if (!$row['is_selected'])
    {
        $xml .= '<listitem value="' . $row['group_id'] . '">'
              . sprintf('%s (%s)', ustr2html($row['group_name']), get_html_resource($row['is_global'] ? RES_GLOBAL_ID : RES_LOCAL_ID))
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
      . '<form name="allowedform" action="share.php?id=' . $id . '" success="reloadTab">'
      . '<group title="' . get_html_resource(RES_ALLOWED_ID) . '">'
      . '<control name="groups[]">'
      . '<listbox size="10">';

$rs->seek();

while (($row = $rs->fetch()))
{
    if ($row['is_selected'])
    {
        $xml .= '<listitem value="' . $row['group_id'] . '">'
              . sprintf('%s (%s)', ustr2html($row['group_name']), get_html_resource($row['is_global'] ? RES_GLOBAL_ID : RES_LOCAL_ID))
              . '</listitem>';
    }
}

$xml .= '</listbox>'
      . '</control>'
      . '</group>'
      . '</form>'
      . '</dualright>';

// generate buttons

$xml .= '<button action="$(\'#othersform\').submit()">%gt;%gt;</button>'
      . '<button action="$(\'#allowedform\').submit()">%lt;%lt;</button>'
      . '</dual>';

echo(xml2html($xml));

?>
