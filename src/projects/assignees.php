<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2011  Artem Rodygin
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
require_once('../dbo/states.php');
/**#@-*/

init_page(LOAD_TAB);

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    exit;
}

// check that requested state exists

$id    = ustr2int(try_request('id'));
$state = state_find($id);

if (!$state)
{
    debug_write_log(DEBUG_NOTICE, 'State cannot be found.');
    exit;
}

// check that requested state requires assignment

if ($state['responsible'] != STATE_RESPONSIBLE_ASSIGN)
{
    debug_write_log(DEBUG_NOTICE, 'State does not suppose to assign a record.');
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
            dal_query('states/saadd.sql', $id, $group);
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
            dal_query('states/sadelete.sql', $id, $group);
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
}

// generate left side

$xml = '<dual>'
     . '<dualleft>'
     . '<form name="othersform" action="assignees.php?id=' . $id . '" success="reloadTab">'
     . '<group title="' . get_html_resource(RES_OTHERS_ID) . '">'
     . '<control name="groups[]">'
     . '<listbox size="10">';

$rs = dal_query('states/saothers.sql', $id);

while (($row = $rs->fetch()))
{
    $xml .= '<listitem value="' . $row['group_id'] . '">'
          . ustr2html(sprintf('%s (%s)', $row['group_name'], get_html_resource($row['is_global'] ? RES_GLOBAL_ID : RES_LOCAL_ID)))
          . '</listitem>';
}

$xml .= '</listbox>'
      . '</control>'
      . '</group>'
      . '</form>'
      . '</dualleft>';

// generate right side

$xml .= '<dualright>'
      . '<form name="allowedform" action="assignees.php?id=' . $id . '" success="reloadTab">'
      . '<group title="' . get_html_resource(RES_ALLOWED_ID) . '">'
      . '<control name="groups[]">'
      . '<listbox size="10">';

$rs = dal_query('states/saallowed.sql', $id);

while (($row = $rs->fetch()))
{
    $xml .= '<listitem value="' . $row['group_id'] . '">'
          . ustr2html(sprintf('%s (%s)', $row['group_name'], get_html_resource($row['is_global'] ? RES_GLOBAL_ID : RES_LOCAL_ID)))
          . '</listitem>';
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
