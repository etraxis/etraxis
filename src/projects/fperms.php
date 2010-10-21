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
//require_once('../dbo/groups.php');
require_once('../dbo/projects.php');
require_once('../dbo/fields.php');
/**#@-*/

init_page();

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    header('Location: index.php');
    exit;
}

// check that requested field exists

$id    = ustr2int(try_request('id'));
$field = field_find($id);

if (!$field)
{
    debug_write_log(DEBUG_NOTICE, 'Field cannot be found.');
    header('Location: index.php');
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

    $field = field_find($id);

    if (!$field)
    {
        debug_write_log(DEBUG_NOTICE, 'Field cannot be found.');
        header('Location: index.php');
        exit;
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $gid = FIELD_ROLE_AUTHOR;
}

// page's title

$title = ustrprocess(get_html_resource(RES_FIELD_X_ID), ustr2html($field['field_name']));

// generate breadcrumbs and tabs

$xml = gen_context_menu('sindex.php?id=', 'findex.php?id=', 'fperms.php?id=', $field['project_id'], $field['template_id'], $field['state_id'])
     . '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_PROJECTS_ID) . '</breadcrumb>'
     . '<breadcrumb url="tindex.php?id=' . $field['project_id']  . '">' . ustrprocess(get_html_resource(RES_PROJECT_X_ID),  ustr2html($field['project_name']))  . '</breadcrumb>'
     . '<breadcrumb url="sindex.php?id=' . $field['template_id'] . '">' . ustrprocess(get_html_resource(RES_TEMPLATE_X_ID), ustr2html($field['template_name'])) . '</breadcrumb>'
     . '<breadcrumb url="findex.php?id=' . $field['state_id']    . '">' . ustrprocess(get_html_resource(RES_STATE_X_ID),    ustr2html($field['state_name']))    . '</breadcrumb>'
     . '<breadcrumb url="fperms.php?id=' . $id . '">' . $title . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<tabs>'
     . '<tab url="fview.php?id='  . $id . '"><i>'            . ustr2html($field['field_name'])       . '</i></tab>'
     . '<tab url="fperms.php?id=' . $id . '" active="true">' . get_html_resource(RES_PERMISSIONS_ID) . '</tab>'
     . '<content>';

// generate script to update permissions

$xml .= '<script>'
      . 'function update_perms () {'
      . 'switch (document.permsform.group.value) {';

// generate script to update permissions - 'author' system role

$xml .= 'case "' . FIELD_ROLE_AUTHOR . '":'
      . 'document.permsform.permissions[' . $field['author_perm'] . '].checked = true;'
      . 'break;';

// generate script to update permissions - 'responsible' system role

$xml .= 'case "' . FIELD_ROLE_RESPONSIBLE . '":'
      . 'document.permsform.permissions[' . $field['responsible_perm'] . '].checked = true;'
      . 'break;';

// generate script to update permissions - 'registered' system role

$xml .= 'case "' . FIELD_ROLE_REGISTERED . '":'
      . 'document.permsform.permissions[' . $field['registered_perm'] . '].checked = true;'
      . 'break;';

// generate script to update permissions - groups

$rs = dal_query('fields/fplist.sql', $id);

while (($row = $rs->fetch()))
{
    $xml .= 'case "' . $row['group_id'] . '":'
          . 'document.permsform.permissions[' . $row['perms'] . '].checked = true;'
          . 'break;';
}

$xml .= 'default:'
      . 'document.permsform.permissions[0].checked = true;'
      . '}}'
      . '</script>';

// generate left side

$xml .= '<form name="permsform" action="fperms.php?id=' . $id . '">'
      . '<dual>'
      . '<dualleft>'
      . '<group title="' . get_html_resource(RES_GROUPS_ID) . '">'
      . '<control name="group">'
      . '<listbox size="10" action="update_perms()">'
      . '<listitem value="' . FIELD_ROLE_AUTHOR      . ($gid == FIELD_ROLE_AUTHOR      ? '" selected="true">' : '">') . sprintf('%s (%s)', get_html_resource(RES_AUTHOR_ID),      get_html_resource(RES_ROLE_ID)) . '</listitem>'
      . '<listitem value="' . FIELD_ROLE_RESPONSIBLE . ($gid == FIELD_ROLE_RESPONSIBLE ? '" selected="true">' : '">') . sprintf('%s (%s)', get_html_resource(RES_RESPONSIBLE_ID), get_html_resource(RES_ROLE_ID)) . '</listitem>'
      . '<listitem value="' . FIELD_ROLE_REGISTERED  . ($gid == FIELD_ROLE_REGISTERED  ? '" selected="true">' : '">') . sprintf('%s (%s)', get_html_resource(RES_REGISTERED_ID),  get_html_resource(RES_ROLE_ID)) . '</listitem>';

$rs = dal_query('groups/list.sql', $field['project_id'], 'is_global, group_name');

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
      . '<group title="' . get_html_resource(RES_PERMISSIONS_ID) . '">'
      . '<control name="permissions">'
      . '<radio value="' . FIELD_RESTRICTED     . '">' . get_html_resource(RES_NONE_ID)           . '</radio>'
      . '<radio value="' . FIELD_ALLOW_TO_READ  . '">' . get_html_resource(RES_READ_ONLY_ID)      . '</radio>'
      . '<radio value="' . FIELD_ALLOW_TO_WRITE . '">' . get_html_resource(RES_READ_AND_WRITE_ID) . '</radio>'
      . '</control>';

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
