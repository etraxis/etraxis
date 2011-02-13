<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2005-2010  Artem Rodygin
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
require_once('../dbo/groups.php');
require_once('../dbo/projects.php');
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

if ($state['state_type'] == STATE_TYPE_FINAL)
{
    debug_write_log(DEBUG_NOTICE, 'State must be intermediate.');
    exit;
}

// get lists of groups and states

$groups = dal_query('groups/list.sql', $state['project_id'],  'is_global, group_name');
$states = dal_query('states/list.sql', $state['template_id'], 'state_type, state_name');

// save changed transitions

if (try_request('submitted') == 'transform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $gid = ustr2int(try_request('group', STATE_ROLE_AUTHOR), MIN_STATE_ROLE);

    dal_query(($gid < 0 ? 'states/rtdelete.sql' : 'states/gtdelete.sql'), $id, $gid);

    while (($row = $states->fetch()))
    {
        if (isset($_REQUEST['state' . $row['state_id']]))
        {
            dal_query(($gid < 0 ? 'states/rtadd.sql' : 'states/gtadd.sql'), $id, $row['state_id'], $gid);
        }
    }

    exit;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');
}

// JS array with permissions

$xml = '<script>'
     . 'var transitions = new Array();';

$roles = array(STATE_ROLE_AUTHOR,
               STATE_ROLE_RESPONSIBLE,
               STATE_ROLE_REGISTERED);

foreach ($roles as $role)
{
    $xml .= sprintf('transitions["g%d"] = new Array();', $role);

    $list = dal_query('states/rtlist.sql', $state['template_id'], $id, $role);

    while (($row = $list->fetch()))
    {
        $xml .= sprintf('transitions["g%d"]["state%d"] = %s;',
                        $role,
                        $row['state_id'],
                        ($row['is_set'] == 0 ? 'false' : 'true'));
    }
}

while (($group = $groups->fetch()))
{
    $xml .= sprintf('transitions["g%d"] = new Array();', $group['group_id']);

    $list = dal_query('states/gtlist.sql', $state['template_id'], $id, $group['group_id']);

    while (($row = $list->fetch()))
    {
        $xml .= sprintf('transitions["g%d"]["state%d"] = %s;',
                        $group['group_id'],
                        $row['state_id'],
                        ($row['is_set'] == 0 ? 'false' : 'true'));
    }
}

// local JS functions

$resTitle   = get_js_resource(RES_TRANSITIONS_ID);
$resMessage = get_js_resource(RES_ALERT_SUCCESSFULLY_SAVED_ID);
$resOK      = get_js_resource(RES_OK_ID);

$xml .= <<<JQUERY

function transitionsSuccess ()
{
    var id = $("#group").val();

    $("#transform input:checkbox").each(function () {
        var name = $(this).attr("name");
        transitions["g"+id][name] = $(this).attr("checked");
    });

    jqAlert("{$resTitle}", "{$resMessage}", "{$resOK}");
}

function updateTrans ()
{
    var id = $("#group").val();

    $("#transform input:checkbox").each(function () {
        var name = $(this).attr("name");
        $(this).attr("checked", transitions["g"+id][name] ? "checked" : "");
    });
}

</script>
JQUERY;

// generate left side

$xml .= '<form name="transform" action="strans.php?id=' . $id . '" success="transitionsSuccess">'
      . '<dual>'
      . '<dualleft>'
      . '<group title="' . get_html_resource(RES_GROUPS_ID) . '">'
      . '<control name="group">'
      . '<listbox size="10" action="updateTrans()">'
      . '<listitem value="' . STATE_ROLE_AUTHOR      . '">' . sprintf('%s (%s)', get_html_resource(RES_AUTHOR_ID),      get_html_resource(RES_ROLE_ID)) . '</listitem>'
      . '<listitem value="' . STATE_ROLE_RESPONSIBLE . '">' . sprintf('%s (%s)', get_html_resource(RES_RESPONSIBLE_ID), get_html_resource(RES_ROLE_ID)) . '</listitem>'
      . '<listitem value="' . STATE_ROLE_REGISTERED  . '">' . sprintf('%s (%s)', get_html_resource(RES_REGISTERED_ID),  get_html_resource(RES_ROLE_ID)) . '</listitem>';

$groups->seek();

while (($row = $groups->fetch()))
{
    $xml .= '<listitem value="' . $row['group_id'] . '">'
          . ustr2html(sprintf('%s (%s)', $row['group_name'], get_html_resource($row['is_global'] ? RES_GLOBAL_ID : RES_LOCAL_ID)))
          . '</listitem>';
}

$xml .= '</listbox>'
      . '</control>'
      . '</group>'
      . '</dualleft>';

// generate right side

$xml .= '<dualright>'
      . '<group title="' . get_html_resource(RES_STATES_ID) . '">';

$states->seek();

while (($row = $states->fetch()))
{
    $xml .= '<control name="state' . $row['state_id'] . '">'
          . '<checkbox>' . ustr2html($row['state_name']) . '</checkbox>'
          . '</control>';
}

$xml .= '</group>'
      . '<button default="true">' . get_html_resource(RES_SAVE_ID) . '</button>'
      . '</dualright>'
      . '</dual>'
      . '</form>';

$xml .= '<onready>'
      . '$("#group :first-child").attr("selected", "selected");'
      . 'updateTrans();'
      . '</onready>';

echo(xml2html($xml));

?>
