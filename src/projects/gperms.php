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

    exit;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');
}

// JS arrays with permissions

$xml = <<<JQUERY
<script>

var perm_view         = new Array();
var perm_create       = new Array();
var perm_modify       = new Array();
var perm_postpone     = new Array();
var perm_resume       = new Array();
var perm_reassign     = new Array();
var perm_state        = new Array();
var perm_comment      = new Array();
var perm_confidential = new Array();
var perm_attach       = new Array();
var perm_remove       = new Array();
var perm_remind       = new Array();
var perm_delete       = new Array();
var perm_addsub       = new Array();
var perm_remsub       = new Array();

JQUERY;

$rs = dal_query('templates/list.sql', $pid, 'template_name');

while (($row = $rs->fetch()))
{
    $permissions = group_get_permissions($id, $row['template_id']);

    foreach ($gperms as $gperm)
    {
        $xml .= sprintf('%s["t%d"] = %s;',
                        $gperm[GPERMS_CONTROL],
                        $row['template_id'],
                        (($permissions & $gperm[GPERMS_PERMISSION]) == 0 ? 'false' : 'true'));
    }
}

// local JS functions

$resTitle   = get_js_resource(RES_PERMISSIONS_ID);
$resMessage = get_js_resource(RES_ALERT_SUCCESSFULLY_SAVED_ID);
$resOK      = get_js_resource(RES_OK_ID);

$xml .= <<<JQUERY

function permissionsSuccess ()
{
    var id = $("#template").val();

    perm_view["t"+id]         = $("#perm_view").attr("checked");
    perm_create["t"+id]       = $("#perm_create").attr("checked");
    perm_modify["t"+id]       = $("#perm_modify").attr("checked");
    perm_postpone["t"+id]     = $("#perm_postpone").attr("checked");
    perm_resume["t"+id]       = $("#perm_resume").attr("checked");
    perm_reassign["t"+id]     = $("#perm_reassign").attr("checked");
    perm_state["t"+id]        = $("#perm_state").attr("checked");
    perm_comment["t"+id]      = $("#perm_comment").attr("checked");
    perm_confidential["t"+id] = $("#perm_confidential").attr("checked");
    perm_attach["t"+id]       = $("#perm_attach").attr("checked");
    perm_remove["t"+id]       = $("#perm_remove").attr("checked");
    perm_remind["t"+id]       = $("#perm_remind").attr("checked");
    perm_delete["t"+id]       = $("#perm_delete").attr("checked");
    perm_addsub["t"+id]       = $("#perm_addsub").attr("checked");
    perm_remsub["t"+id]       = $("#perm_remsub").attr("checked");

    jqAlert("{$resTitle}", "{$resMessage}", "{$resOK}");
}

function selectAll ()
{
    $("#permsform input:checkbox").attr("checked", "checked");
}

function updatePerms ()
{
    var id = $("#template").val();

    $("#perm_view").attr("checked",         perm_view["t"+id]         ? "checked" : "");
    $("#perm_create").attr("checked",       perm_create["t"+id]       ? "checked" : "");
    $("#perm_modify").attr("checked",       perm_modify["t"+id]       ? "checked" : "");
    $("#perm_postpone").attr("checked",     perm_postpone["t"+id]     ? "checked" : "");
    $("#perm_resume").attr("checked",       perm_resume["t"+id]       ? "checked" : "");
    $("#perm_reassign").attr("checked",     perm_reassign["t"+id]     ? "checked" : "");
    $("#perm_state").attr("checked",        perm_state["t"+id]        ? "checked" : "");
    $("#perm_comment").attr("checked",      perm_comment["t"+id]      ? "checked" : "");
    $("#perm_confidential").attr("checked", perm_confidential["t"+id] ? "checked" : "");
    $("#perm_attach").attr("checked",       perm_attach["t"+id]       ? "checked" : "");
    $("#perm_remove").attr("checked",       perm_remove["t"+id]       ? "checked" : "");
    $("#perm_remind").attr("checked",       perm_remind["t"+id]       ? "checked" : "");
    $("#perm_delete").attr("checked",       perm_delete["t"+id]       ? "checked" : "");
    $("#perm_addsub").attr("checked",       perm_addsub["t"+id]       ? "checked" : "");
    $("#perm_remsub").attr("checked",       perm_remsub["t"+id]       ? "checked" : "");
}

</script>
JQUERY;

// generate left side

$xml .= '<form name="permsform" action="gperms.php?pid=' . $pid . '&amp;id=' . $id . '" success="permissionsSuccess">'
      . '<dual>'
      . '<dualleft>'
      . '<group title="' . get_html_resource(RES_TEMPLATES_ID) . '">'
      . '<control name="template">'
      . '<listbox size="10" action="updatePerms()">';

$rs->seek();

while (($row = $rs->fetch()))
{
    $xml .= '<listitem value="' . $row['template_id'] . '">'
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
      . '<button default="true">'       . get_html_resource(RES_SAVE_ID)       . '</button>'
      . '<button action="selectAll()">' . get_html_resource(RES_SELECT_ALL_ID) . '</button>'
      . '</dualright>'
      . '</dual>'
      . '</form>';

$xml .= '<onready>'
      . '$("#template :first-child").attr("selected", "selected");'
      . 'updatePerms();'
      . '</onready>';

echo(xml2html($xml));

?>
