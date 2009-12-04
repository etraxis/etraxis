<?php

/**
 * @package eTraxis
 * @ignore
 */

//--------------------------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system.
//  Copyright (C) 2005-2009 by Artem Rodygin
//
//  This program is free software; you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation; either version 2 of the License, or
//  (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License along
//  with this program; if not, write to the Free Software Foundation, Inc.,
//  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
//
//--------------------------------------------------------------------------------------------------
//  Author                  Date            Description of modifications
//--------------------------------------------------------------------------------------------------
//  Artem Rodygin           2005-03-25      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-08-01      new-013: UI scenarios should be changed.
//  Artem Rodygin           2005-08-13      bug-025: Not only project teams are shown in state and field permissions.
//  Artem Rodygin           2005-08-18      new-037: Any template should be locked to be modified without suspending a project.
//  Artem Rodygin           2005-08-23      new-053: All the calls of DAL API functions should be moved to DBO API.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-09-05      bug-089: Group names are not extended with 'global' / 'local'.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-11-17      new-176: Change eTraxis design.
//  Artem Rodygin           2005-11-29      new-187: User controls alignment.
//  Artem Rodygin           2006-03-16      new-175: Implement user roles in permissions.
//  Artem Rodygin           2006-04-21      new-247: The 'responsible' user role should be obliterated.
//  Artem Rodygin           2006-12-28      new-474: Rename field permissions to make them more clear.
//  Artem Rodygin           2007-01-05      new-491: [SF1647212] Group-wide transition permission.
//  Artem Rodygin           2007-11-26      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2008-01-28      new-531: LDAP Guest users
//  Artem Rodygin           2008-04-20      new-703: Separated permissions set for current responsible.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-01-08      new-774: 'Anyone' system role permissions.
//  Artem Rodygin           2009-04-24      new-817: Field permissions dialog refactoring.
//  Artem Rodygin           2009-05-29      bug-822: Field permissions for system roles are displayed wrong.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//  Artem Rodygin           2009-07-14      bug-835: Template permissions could not be accessed while no group is created yet.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/groups.php');
require_once('../dbo/fields.php');
/**#@-*/

init_page();

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    header('Location: index.php');
    exit;
}

$id    = ustr2int(try_request('id'));
$field = field_find($id);

if (!$field)
{
    debug_write_log(DEBUG_NOTICE, 'Field cannot be found.');
    header('Location: index.php');
    exit;
}

$sort = $page = NULL;
$list = group_list($field['project_id'], $sort, $page);

$gid = ustr2int(try_request('gid', FIELD_ROLE_AUTHOR), MIN_FIELD_ROLE);

if ($gid >= 0)
{
    $group = group_find($gid);

    if (!$group)
    {
        debug_write_log(DEBUG_NOTICE, 'Group cannot be found.');
        $gid = FIELD_ROLE_AUTHOR;
    }
}

if (try_request('submitted') == 'rform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

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

    header('Location: fperms.php?id=' . $id . '&gid=' . $gid);
    exit;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');
}

$script = '<script>function onList(){'
        . 'document.rform.permissions[0].checked=false;'
        . 'document.rform.permissions[1].checked=false;'
        . 'document.rform.permissions[2].checked=false;'
        . 'switch(document.lform.groups.value) {'
        . 'case \'' . FIELD_ROLE_AUTHOR . '\':'
        . (($field['author_perm'] == FIELD_RESTRICTED)     ? 'document.rform.permissions[0].checked=true;' : NULL)
        . (($field['author_perm'] == FIELD_ALLOW_TO_READ)  ? 'document.rform.permissions[1].checked=true;' : NULL)
        . (($field['author_perm'] == FIELD_ALLOW_TO_WRITE) ? 'document.rform.permissions[2].checked=true;' : NULL)
        . 'break;'
        . 'case \'' . FIELD_ROLE_RESPONSIBLE . '\':'
        . (($field['responsible_perm'] == FIELD_RESTRICTED)     ? 'document.rform.permissions[0].checked=true;' : NULL)
        . (($field['responsible_perm'] == FIELD_ALLOW_TO_READ)  ? 'document.rform.permissions[1].checked=true;' : NULL)
        . (($field['responsible_perm'] == FIELD_ALLOW_TO_WRITE) ? 'document.rform.permissions[2].checked=true;' : NULL)
        . 'break;'
        . 'case \'' . FIELD_ROLE_REGISTERED . '\':'
        . (($field['registered_perm'] == FIELD_RESTRICTED)     ? 'document.rform.permissions[0].checked=true;' : NULL)
        . (($field['registered_perm'] == FIELD_ALLOW_TO_READ)  ? 'document.rform.permissions[1].checked=true;' : NULL)
        . (($field['registered_perm'] == FIELD_ALLOW_TO_WRITE) ? 'document.rform.permissions[2].checked=true;' : NULL)
        . 'break;';

$rs = dal_query('fields/fplist.sql', $id);

while (($row = $rs->fetch()))
{
    $script .= 'case \'' . $row['group_id'] . '\':'
             . 'document.rform.permissions[' . $row['perms'] . '].checked=true;'
             . 'break;';
}

$script .= 'default:'
         . 'document.rform.permissions[0].checked=true;'
         . '}}</script>';

$xml = '<page' . gen_xml_page_header(ustrprocess(get_html_resource(RES_FIELD_X_ID), ustr2html($field['field_name']))) . ' init="onList();">'
     . $script
     . gen_xml_menu()
     . '<path>'
     . '<pathitem url="index.php">'                                      . get_html_resource(RES_PROJECTS_ID)                                                    . '</pathitem>'
     . '<pathitem url="view.php?id='   . $field['project_id']     . '">' . ustrprocess(get_html_resource(RES_PROJECT_X_ID), ustr2html($field['project_name']))   . '</pathitem>'
     . '<pathitem url="tindex.php?id=' . $field['project_id']     . '">' . get_html_resource(RES_TEMPLATES_ID)                                                   . '</pathitem>'
     . '<pathitem url="tview.php?id='  . $field['template_id']    . '">' . ustrprocess(get_html_resource(RES_TEMPLATE_X_ID), ustr2html($field['template_name'])) . '</pathitem>'
     . '<pathitem url="sindex.php?id=' . $field['template_id']    . '">' . get_html_resource(RES_STATES_ID)                                                      . '</pathitem>'
     . '<pathitem url="sview.php?id='  . $field['state_id']       . '">' . ustrprocess(get_html_resource(RES_STATE_X_ID), ustr2html($field['state_name']))       . '</pathitem>'
     . '<pathitem url="findex.php?id=' . $field['state_id']       . '">' . get_html_resource(RES_FIELDS_ID)                                                      . '</pathitem>'
     . '<pathitem url="fview.php?id='  . $id                      . '">' . ustrprocess(get_html_resource(RES_FIELD_X_ID), ustr2html($field['field_name']))       . '</pathitem>'
     . '<pathitem url="fperms.php?id=' . $id . '&amp;gid=' . $gid . '">' . get_html_resource(RES_PERMISSIONS_ID)                                                 . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<dualbox nobuttons="true">'
     . '<dualleft>'
     . '<group title="' . get_html_resource(RES_GROUPS_ID) . '">'
     . '<listbox dualbox="true" name="groups" size="' . HTML_LISTBOX_SIZE . '" action="onList();">'
     . '<listitem value="' . FIELD_ROLE_AUTHOR      . '"' . ($gid == FIELD_ROLE_AUTHOR      ? ' selected="true">' : '>') . get_html_resource(RES_AUTHOR_ID)      . ' (' . get_html_resource(RES_ROLE_ID) . ')</listitem>'
     . '<listitem value="' . FIELD_ROLE_RESPONSIBLE . '"' . ($gid == FIELD_ROLE_RESPONSIBLE ? ' selected="true">' : '>') . get_html_resource(RES_RESPONSIBLE_ID) . ' (' . get_html_resource(RES_ROLE_ID) . ')</listitem>'
     . '<listitem value="' . FIELD_ROLE_REGISTERED  . '"' . ($gid == FIELD_ROLE_REGISTERED  ? ' selected="true">' : '>') . get_html_resource(RES_REGISTERED_ID)  . ' (' . get_html_resource(RES_ROLE_ID) . ')</listitem>';

$list->seek();

while (($item = $list->fetch()))
{
    $xml .= '<listitem value="' . $item['group_id'] . '"' . ($gid == $item['group_id'] ? ' selected="true">' : '>')
          . ustr2html($item['group_name']) . ' (' . get_html_resource(is_null($item['project_id']) ? RES_GLOBAL_ID : RES_LOCAL_ID) . ')'
          . '</listitem>';
}

$xml .= '</listbox>'
      . '</group>'
      . '</dualleft>'
      . '<dualright action="tperms.php?id=' . $id . '">'
      . '<group title="' . get_html_resource(RES_PERMISSIONS_ID) . '">'
      . '<radios name="permissions">'
      . '<radio name="permissions" value="' . FIELD_RESTRICTED     . '">' . get_html_resource(RES_NONE_ID)           . '</radio>'
      . '<radio name="permissions" value="' . FIELD_ALLOW_TO_READ  . '">' . get_html_resource(RES_READ_ONLY_ID)      . '</radio>'
      . '<radio name="permissions" value="' . FIELD_ALLOW_TO_WRITE . '">' . get_html_resource(RES_READ_AND_WRITE_ID) . '</radio>'
      . '</radios>'
      . '</group>'
      . '</dualright>'
      . '</dualbox>'
      . '<button url="fview.php?id=' . $id . '">' . get_html_resource(RES_BACK_ID) . '</button>'
      . '<button action="rform.action=\'fperms.php?id=' . $id . '&amp;gid=\'+lform.groups.value; rform.submit();">' . get_html_resource(RES_SAVE_ID) . '</button>'
      . '</content>'
      . '</page>';

echo(xml2html($xml));

?>
