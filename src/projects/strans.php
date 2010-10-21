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

init_page();

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    header('Location: index.php');
    exit;
}

// check that requested state exists

$id    = ustr2int(try_request('id'));
$state = state_find($id);

if (!$state)
{
    debug_write_log(DEBUG_NOTICE, 'State cannot be found.');
    header('Location: index.php');
    exit;
}

if ($state['state_type'] == STATE_TYPE_FINAL)
{
    debug_write_log(DEBUG_NOTICE, 'State must be intermediate.');
    header('Location: sview.php?id=' . $id);
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
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $gid = STATE_ROLE_AUTHOR;
}

// page's title

$title = ustrprocess(get_html_resource(RES_STATE_X_ID), ustr2html($state['state_name']));

// generate breadcrumbs and tabs

$xml = gen_context_menu('sindex.php?id=', 'strans.php?id=', 'fview.php?id=', $state['project_id'], $state['template_id'], $id)
     . '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_PROJECTS_ID) . '</breadcrumb>'
     . '<breadcrumb url="tindex.php?id=' . $state['project_id']  . '">' . ustrprocess(get_html_resource(RES_PROJECT_X_ID),  ustr2html($state['project_name']))  . '</breadcrumb>'
     . '<breadcrumb url="sindex.php?id=' . $state['template_id'] . '">' . ustrprocess(get_html_resource(RES_TEMPLATE_X_ID), ustr2html($state['template_name'])) . '</breadcrumb>'
     . '<breadcrumb url="strans.php?id=' . $id . '">' . $title . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<tabs>'
     . '<tab url="sview.php?id='  . $id . '"><i>' . ustr2html($state['state_name']) . '</i></tab>'
     . '<tab url="findex.php?id=' . $id . '">' . get_html_resource(RES_FIELDS_ID) . '</tab>';

if ($state['state_type'] != STATE_TYPE_FINAL)
{
    $xml .= '<tab url="strans.php?id=' . $id . '" active="true">' . get_html_resource(RES_TRANSITIONS_ID) . '</tab>';
}

$xml .= '<content>';

// generate script to update permissions

$xml .= '<script>'
      . 'function update_perms () {'
      . 'switch (document.transform.group.value) {';

// generate script to update permissions - 'author' system role

$xml .= 'case "' . STATE_ROLE_AUTHOR . '":';

$list = dal_query('states/rtlist.sql', $state['template_id'], $id, STATE_ROLE_AUTHOR);

while (($row = $list->fetch()))
{
    $xml .= 'document.transform.state' . $row['state_id'] . '.checked = ' . ($row['is_set'] == 0 ? 'false;' : 'true;');
}

$xml .= 'break;';

// generate script to update permissions - 'responsible' system role

$xml .= 'case "' . STATE_ROLE_RESPONSIBLE . '":';

$list = dal_query('states/rtlist.sql', $state['template_id'], $id, STATE_ROLE_RESPONSIBLE);

while (($row = $list->fetch()))
{
    $xml .= 'document.transform.state' . $row['state_id'] . '.checked = ' . ($row['is_set'] == 0 ? 'false;' : 'true;');
}

$xml .= 'break;';

// generate script to update permissions - 'registered' system role

$xml .= 'case "' . STATE_ROLE_REGISTERED . '":';

$list = dal_query('states/rtlist.sql', $state['template_id'], $id, STATE_ROLE_REGISTERED);

while (($row = $list->fetch()))
{
    $xml .= 'document.transform.state' . $row['state_id'] . '.checked = ' . ($row['is_set'] == 0 ? 'false;' : 'true;');
}

$xml .= 'break;';

// generate script to update permissions - groups

while (($group = $groups->fetch()))
{
    $xml .= 'case "' . $group['group_id'] . '":';

    $list = dal_query('states/gtlist.sql', $state['template_id'], $id, $group['group_id']);

    while (($row = $list->fetch()))
    {
        $xml .= 'document.transform.state' . $row['state_id'] . '.checked = ' . ($row['is_set'] == 0 ? 'false;' : 'true;');
    }

    $xml .= 'break;';
}

$xml .= '}}'
      . '</script>';

// generate left side

$xml .= '<form name="transform" action="strans.php?id=' . $id . '">'
      . '<dual>'
      . '<dualleft>'
      . '<group title="' . get_html_resource(RES_GROUPS_ID) . '">'
      . '<control name="group">'
      . '<listbox size="10" action="update_perms()">'
      . '<listitem value="' . STATE_ROLE_AUTHOR      . ($gid == STATE_ROLE_AUTHOR      ? '" selected="true">' : '">') . sprintf('%s (%s)', get_html_resource(RES_AUTHOR_ID),      get_html_resource(RES_ROLE_ID)) . '</listitem>'
      . '<listitem value="' . STATE_ROLE_RESPONSIBLE . ($gid == STATE_ROLE_RESPONSIBLE ? '" selected="true">' : '">') . sprintf('%s (%s)', get_html_resource(RES_RESPONSIBLE_ID), get_html_resource(RES_ROLE_ID)) . '</listitem>'
      . '<listitem value="' . STATE_ROLE_REGISTERED  . ($gid == STATE_ROLE_REGISTERED  ? '" selected="true">' : '">') . sprintf('%s (%s)', get_html_resource(RES_REGISTERED_ID),  get_html_resource(RES_ROLE_ID)) . '</listitem>';

$groups->seek();

while (($row = $groups->fetch()))
{
    $xml .= ($gid == $row['group_id']
                ? '<listitem value="' . $row['group_id'] . '" selected="true">'
                : '<listitem value="' . $row['group_id'] . '">')
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
      . '</form>'
      . '<script>update_perms();</script>'
      . '</content>'
      . '</tabs>';

echo(xml2html($xml, $title));

?>
