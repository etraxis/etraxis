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

// check that requested project exists

$pid     = ustr2int(try_request('pid'));
$project = project_find($pid);

if (!$project)
{
    debug_write_log(DEBUG_NOTICE, 'Project cannot be found.');
    header('Location: index.php');
    exit;
}

// check that requested group exists

$id    = ustr2int(try_request('id'));
$group = group_find($id);

if (!$group)
{
    debug_write_log(DEBUG_NOTICE, 'Group cannot be found.');
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

    $tid      = ustr2int(try_request('template'));
    $template = template_find($tid);

    if ($template)
    {
        $permissions = 0;

        foreach ($gperms as $gperm)
        {
            if (isset($_REQUEST[$gperm[GPERMS_CONTROL]]))
            {
                $permissions |= $gperm[GPERMS_PERMISSION];
            }
        }

        group_set_permissions($id, $tid, $permissions);
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, 'Template cannot be found.');
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $tid = 0;
}

// page's title

$title = ustrprocess(get_html_resource(RES_GROUP_X_ID), ustr2html($group['group_name']));

// generate breadcrumbs and tabs

$xml = gen_context_menu('tview.php?id=', 'sview.php?id=', 'fview.php?id=', $pid)
     . '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_PROJECTS_ID) . '</breadcrumb>'
     . '<breadcrumb url="gindex.php?id=' . $pid . '">' . ustrprocess(get_html_resource(RES_PROJECT_X_ID), ustr2html($project['project_name'])) . '</breadcrumb>'
     . '<breadcrumb url="gperms.php?pid=' . $pid . '&amp;id=' . $id . '">' . $title . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<tabs>'
     . '<tab url="gview.php?pid='    . $pid . '&amp;id=' . $id . '"><i>'            . ustr2html($group['group_name'])       . '</i></tab>'
     . '<tab url="gmembers.php?pid=' . $pid . '&amp;id=' . $id . '">'               . get_html_resource(RES_MEMBERSHIP_ID)  . '</tab>'
     . '<tab url="gperms.php?pid='   . $pid . '&amp;id=' . $id . '" active="true">' . get_html_resource(RES_PERMISSIONS_ID) . '</tab>'
     . '<content>';

// generate script to select all permissions

$xml .= '<script>'
      . 'function select_all () {';

foreach ($gperms as $gperm)
{
    $xml .= 'document.permsform.' . $gperm[GPERMS_CONTROL] . '.checked = true;';
}

$xml .= '}';

// generate script to update permissions

$xml .= 'function update_perms () {'
      . 'switch (document.permsform.template.value) {';

$rs = dal_query('templates/list.sql', $pid, 'template_name');

if ($tid == 0 && $rs->rows != 0)
{
    $tid = $rs->fetch('template_id');
    $rs->seek();
}

while (($row = $rs->fetch()))
{
    $permissions = group_get_permissions($id, $row['template_id']);

    $xml .= 'case "' . $row['template_id'] . '":';

    foreach ($gperms as $gperm)
    {
        $xml .= 'document.permsform.' . $gperm[GPERMS_CONTROL] . '.checked = ' . (($permissions & $gperm[GPERMS_PERMISSION]) == 0 ? 'false;' : 'true;');
    }

    $xml .= 'break;';
}

$xml .= '}'
      . '}'
      . '</script>';

// generate left side

$xml .= '<form name="permsform" action="gperms.php?pid=' . $pid . '&amp;id=' . $id . '">'
      . '<dual>'
      . '<dualleft>'
      . '<group title="' . get_html_resource(RES_TEMPLATES_ID) . '">'
      . '<control name="template">'
      . '<listbox size="10" action="update_perms()">';

$rs->seek();

while (($row = $rs->fetch()))
{
    $xml .= ($tid == $row['template_id']
                ? '<listitem value="' . $row['template_id'] . '" selected="true">'
                : '<listitem value="' . $row['template_id'] . '">')
          . ustr2html($row['template_name'])
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
