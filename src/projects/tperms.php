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
require_once('../dbo/templates.php');
require_once('../dbo/events.php');
/**#@-*/

init_page();

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    header('Location: index.php');
    exit;
}

// check that requested template exists

$id       = ustr2int(try_request('id'));
$template = template_find($id);

if (!$template)
{
    debug_write_log(DEBUG_NOTICE, 'Template cannot be found.');
    header('Location: index.php');
    exit;
}

// permissions data

define('GPERMS_CONTROL',    0);
define('GPERMS_PERMISSION', 1);
define('GPERMS_RESOURCE',   2);

$gperms = array
(
    array('perm_view',         PERMIT_VIEW_RECORD,           RES_PERMIT_VIEW_RECORDS_ONLY_ID),
    array('perm_create',       PERMIT_CREATE_RECORD,         RES_PERMIT_CREATE_RECORD_ID),
    array('perm_modify',       PERMIT_MODIFY_RECORD,         RES_PERMIT_MODIFY_RECORD_ID),
    array('perm_postpone',     PERMIT_POSTPONE_RECORD,       RES_PERMIT_POSTPONE_RECORD_ID),
    array('perm_resume',       PERMIT_RESUME_RECORD,         RES_PERMIT_RESUME_RECORD_ID),
    array('perm_reassign',     PERMIT_REASSIGN_RECORD,       RES_PERMIT_REASSIGN_RECORD_ID),
    array('perm_state',        PERMIT_CHANGE_STATE,          RES_PERMIT_CHANGE_STATE_ID),
    array('perm_comment',      PERMIT_ADD_COMMENTS,          RES_PERMIT_ADD_COMMENTS_ID),
    array('perm_confidential', PERMIT_CONFIDENTIAL_COMMENTS, RES_PERMIT_CONFIDENTIAL_COMMENTS_ID),
    array('perm_attach',       PERMIT_ATTACH_FILES,          RES_PERMIT_ATTACH_FILES_ID),
    array('perm_remove',       PERMIT_REMOVE_FILES,          RES_PERMIT_REMOVE_FILES_ID),
    array('perm_remind',       PERMIT_SEND_REMINDERS,        RES_PERMIT_SEND_REMINDERS_ID),
    array('perm_delete',       PERMIT_DELETE_RECORD,         RES_PERMIT_DELETE_RECORD_ID),
    array('perm_addsub',       PERMIT_ADD_SUBRECORDS,        RES_PERMIT_ADD_SUBRECORDS_ID),
    array('perm_remsub',       PERMIT_REMOVE_SUBRECORDS,     RES_PERMIT_REMOVE_SUBRECORDS_ID),
);

// save changed permissions

if (try_request('submitted') == 'permsform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $permissions = 0;

    foreach ($gperms as $gperm)
    {
        if (isset($_REQUEST[$gperm[GPERMS_CONTROL]]))
        {
            $permissions |= $gperm[GPERMS_PERMISSION];
        }
    }

    $gid = ustr2int(try_request('group', TEMPLATE_ROLE_AUTHOR), MIN_TEMPLATE_ROLE);

    switch ($gid)
    {
        case TEMPLATE_ROLE_AUTHOR:
            $permissions &= ~(PERMIT_VIEW_RECORD | PERMIT_CREATE_RECORD);
            template_author_perm_set($id, $permissions);
            break;

        case TEMPLATE_ROLE_RESPONSIBLE:
            $permissions &= ~(PERMIT_VIEW_RECORD | PERMIT_CREATE_RECORD);
            template_responsible_perm_set($id, $permissions);
            break;

        case TEMPLATE_ROLE_REGISTERED:
            template_registered_perm_set($id, $permissions);
            break;

        default:
            group_set_permissions($gid, $id, $permissions);
    }

    $template = template_find($id);

    if (!$template)
    {
        debug_write_log(DEBUG_NOTICE, 'Template cannot be found.');
        header('Location: index.php');
        exit;
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $gid = TEMPLATE_ROLE_AUTHOR;
}

// page's title

$title = ustrprocess(get_html_resource(RES_TEMPLATE_X_ID), ustr2html($template['template_name']));

// generate breadcrumbs and tabs

$xml = gen_context_menu('tperms.php?id=', 'sview.php?id=', 'fview.php?id=', $template['project_id'], $id)
     . '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_PROJECTS_ID) . '</breadcrumb>'
     . '<breadcrumb url="tindex.php?id=' . $template['project_id'] . '">' . ustrprocess(get_html_resource(RES_PROJECT_X_ID), ustr2html($template['project_name'])) . '</breadcrumb>'
     . '<breadcrumb url="tperms.php?id=' . $id . '">' . $title . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<tabs>'
     . '<tab url="tview.php?id='  . $id . '"><i>'            . ustr2html($template['template_name']) . '</i></tab>'
     . '<tab url="sindex.php?id=' . $id . '">'               . get_html_resource(RES_STATES_ID)      . '</tab>'
     . '<tab url="tperms.php?id=' . $id . '" active="true">' . get_html_resource(RES_PERMISSIONS_ID) . '</tab>'
     . '<content>';

// generate script to select all permissions

$xml .= '<script>'
      . 'function select_all () {';

foreach ($gperms as $gperm)
{
    $xml .= 'if (!document.permsform.' . $gperm[GPERMS_CONTROL] . '.disabled) document.permsform.' . $gperm[GPERMS_CONTROL] . '.checked = true;';
}

$xml .= '}';

// generate script to update permissions

$xml .= 'function update_perms () {'
      . 'switch (document.permsform.group.value) {';

// generate script to update permissions - 'author' system role

$xml .= 'case "' . TEMPLATE_ROLE_AUTHOR . '":';

foreach ($gperms as $gperm)
{
    $xml .= 'document.permsform.' . $gperm[GPERMS_CONTROL] . '.checked = ' . (($template['author_perm'] & $gperm[GPERMS_PERMISSION]) == 0 ? 'false;' : 'true;');
}

$xml .= 'break;';

// generate script to update permissions - 'responsible' system role

$xml .= 'case "' . TEMPLATE_ROLE_RESPONSIBLE . '":';

foreach ($gperms as $gperm)
{
    $xml .= 'document.permsform.' . $gperm[GPERMS_CONTROL] . '.checked = ' . (($template['responsible_perm'] & $gperm[GPERMS_PERMISSION]) == 0 ? 'false;' : 'true;');
}

$xml .= 'break;';

// generate script to update permissions - 'registered' system role

$xml .= 'case "' . TEMPLATE_ROLE_REGISTERED . '":';

foreach ($gperms as $gperm)
{
    $xml .= 'document.permsform.' . $gperm[GPERMS_CONTROL] . '.checked = ' . (($template['registered_perm'] & $gperm[GPERMS_PERMISSION]) == 0 ? 'false;' : 'true;');
}

$xml .= 'break;';

// generate script to update permissions - groups

$rs = dal_query('groups/list.sql', $template['project_id'], 'is_global, group_name');

while (($row = $rs->fetch()))
{
    $permissions = group_get_permissions($row['group_id'], $id);

    $xml .= 'case "' . $row['group_id'] . '":';

    foreach ($gperms as $gperm)
    {
        $xml .= 'document.permsform.' . $gperm[GPERMS_CONTROL] . '.checked = ' . (($permissions & $gperm[GPERMS_PERMISSION]) == 0 ? 'false;' : 'true;');
    }

    $xml .= 'break;';
}

// generate script to update permissions - specific conditions for system roles

$xml .= '}'
      . 'if (document.permsform.group.value == ' . TEMPLATE_ROLE_AUTHOR . ' || document.permsform.group.value == ' . TEMPLATE_ROLE_RESPONSIBLE . ') {'
      . 'document.permsform.perm_view.checked = true;'
      . 'document.permsform.perm_create.checked = false;'
      . 'document.permsform.perm_view.disabled = true;'
      . 'document.permsform.perm_create.disabled = true;'
      . '} else {'
      . 'document.permsform.perm_view.disabled = false;'
      . 'document.permsform.perm_create.disabled = false;'
      . '}'
      . '}'
      . '</script>';

// generate left side

$xml .= '<form name="permsform" action="tperms.php?id=' . $id . '">'
      . '<dual>'
      . '<dualleft>'
      . '<group title="' . get_html_resource(RES_GROUPS_ID) . '">'
      . '<control name="group">'
      . '<listbox size="10" action="update_perms()">'
      . '<listitem value="' . TEMPLATE_ROLE_AUTHOR      . ($gid == TEMPLATE_ROLE_AUTHOR      ? '" selected="true">' : '">') . sprintf('%s (%s)', get_html_resource(RES_AUTHOR_ID),      get_html_resource(RES_ROLE_ID)) . '</listitem>'
      . '<listitem value="' . TEMPLATE_ROLE_RESPONSIBLE . ($gid == TEMPLATE_ROLE_RESPONSIBLE ? '" selected="true">' : '">') . sprintf('%s (%s)', get_html_resource(RES_RESPONSIBLE_ID), get_html_resource(RES_ROLE_ID)) . '</listitem>'
      . '<listitem value="' . TEMPLATE_ROLE_REGISTERED  . ($gid == TEMPLATE_ROLE_REGISTERED  ? '" selected="true">' : '">') . sprintf('%s (%s)', get_html_resource(RES_REGISTERED_ID),  get_html_resource(RES_ROLE_ID)) . '</listitem>';

$rs->seek();

while (($row = $rs->fetch()))
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
      . '<group title="' . get_html_resource(RES_PERMISSIONS_ID) . '">';

foreach ($gperms as $gperm)
{
    $xml .= '<control name="' . $gperm[GPERMS_CONTROL] . '">'
          . '<checkbox>' . get_html_resource($gperm[GPERMS_RESOURCE]) . '</checkbox>'
          . '</control>';
}

$xml .= '</group>'
      . '<button default="true">'        . get_html_resource(RES_SAVE_ID)       . '</button>'
      . '<button action="select_all()">' . get_html_resource(RES_SELECT_ALL_ID) . '</button>'
      . '</dualright>'
      . '</dual>'
      . '</form>'
      . '<script>update_perms();</script>'
      . '</content>'
      . '</tabs>';

echo(xml2html($xml, $title));

?>
