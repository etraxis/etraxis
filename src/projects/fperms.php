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
require_once('../dbo/projects.php');
require_once('../dbo/fields.php');
/**#@-*/

init_page(LOAD_TAB);

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    exit;
}

// check that requested field exists

$id    = ustr2int(try_request('id'));
$field = field_find($id);

if (!$field)
{
    debug_write_log(DEBUG_NOTICE, 'Field cannot be found.');
    exit;
}

// save changed permissions

if (try_request('submitted') == 'permsform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $gid         = ustr2int(try_request('group', FIELD_ROLE_AUTHOR), MIN_FIELD_ROLE);
    $permissions = ustr2int(try_request('permissions', FIELD_RESTRICTED), FIELD_RESTRICTED, FIELD_ALLOW_TO_WRITE);

    switch ($gid)
    {
        case FIELD_ROLE_AUTHOR:
            field_author_permission_set($id, $permissions);
            break;

        case FIELD_ROLE_RESPONSIBLE:
            field_responsible_permission_set($id, $permissions);
            break;

        case FIELD_ROLE_REGISTERED:
            field_registered_permission_set($id, $permissions);
            break;

        default:

            field_permission_remove($id, $gid);

            if ($permissions == FIELD_ALLOW_TO_READ)
            {
                field_permission_add($id, $gid, FIELD_ALLOW_TO_READ);
            }
            elseif ($permissions == FIELD_ALLOW_TO_WRITE)
            {
                field_permission_add($id, $gid, FIELD_ALLOW_TO_READ);
                field_permission_add($id, $gid, FIELD_ALLOW_TO_WRITE);
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
     . 'var permissions = new Array();'
     . sprintf('permissions["g%d"] = %d;', FIELD_ROLE_AUTHOR,      $field['author_perm'])
     . sprintf('permissions["g%d"] = %d;', FIELD_ROLE_RESPONSIBLE, $field['responsible_perm'])
     . sprintf('permissions["g%d"] = %d;', FIELD_ROLE_REGISTERED,  $field['registered_perm']);

$rs = dal_query('groups/list.sql', $field['project_id'], 'is_global, group_name');

while (($row = $rs->fetch()))
{
    $xml .= sprintf('permissions["g%d"] = 0;', $row['group_id']);
}

$rs = dal_query('fields/fplist.sql', $id);

while (($row = $rs->fetch()))
{
    $xml .= sprintf('permissions["g%d"] = %d;', $row['group_id'], $row['perms']);
}

// local JS functions

$resTitle   = get_js_resource(RES_PERMISSIONS_ID);
$resMessage = get_js_resource(RES_ALERT_SUCCESSFULLY_SAVED_ID);
$resOK      = get_js_resource(RES_OK_ID);

$xml .= <<<JQUERY

function permissionsSuccess ()
{
    var id = $("#group").val();
    permissions["g"+id] = $("input[name=permissions]:radio:checked").val();
    jqAlert("{$resTitle}", "{$resMessage}", "{$resOK}");
}

function updatePerms ()
{
    var id = $("#group").val();
    var perm = permissions["g"+id];
    $("input[name=permissions]:radio[value=" + perm + "]").prop("checked", true);
}

</script>
JQUERY;

// generate left side

$xml .= '<form name="permsform" action="fperms.php?id=' . $id . '" success="permissionsSuccess">'
      . '<dual>'
      . '<dualleft>'
      . '<group title="' . get_html_resource(RES_GROUPS_ID) . '">'
      . '<control name="group">'
      . '<listbox size="10" action="updatePerms()">'
      . '<listitem value="' . FIELD_ROLE_AUTHOR      . '">' . sprintf('%s (%s)', get_html_resource(RES_AUTHOR_ID),      get_html_resource(RES_ROLE_ID)) . '</listitem>'
      . '<listitem value="' . FIELD_ROLE_RESPONSIBLE . '">' . sprintf('%s (%s)', get_html_resource(RES_RESPONSIBLE_ID), get_html_resource(RES_ROLE_ID)) . '</listitem>'
      . '<listitem value="' . FIELD_ROLE_REGISTERED  . '">' . sprintf('%s (%s)', get_html_resource(RES_REGISTERED_ID),  get_html_resource(RES_ROLE_ID)) . '</listitem>';

$rs = dal_query('groups/list.sql', $field['project_id'], 'is_global, group_name');

while (($row = $rs->fetch()))
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
      . '<group title="' . get_html_resource(RES_PERMISSIONS_ID) . '">'
      . '<control name="permissions">'
      . '<radio value="' . FIELD_RESTRICTED     . '">' . get_html_resource(RES_NONE_ID)           . '</radio>'
      . '<radio value="' . FIELD_ALLOW_TO_READ  . '">' . get_html_resource(RES_READ_ONLY_ID)      . '</radio>'
      . '<radio value="' . FIELD_ALLOW_TO_WRITE . '">' . get_html_resource(RES_READ_AND_WRITE_ID) . '</radio>'
      . '</control>'
      . '</group>'
      . '<button default="true">' . get_html_resource(RES_SAVE_ID) . '</button>'
      . '</dualright>'
      . '</dual>'
      . '</form>';

$xml .= '<onready>'
      . '$("#group :first-child").prop("selected", true);'
      . 'updatePerms();'
      . '</onready>';

echo(xml2html($xml));

?>
