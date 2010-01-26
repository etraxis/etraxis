<?php

/**
 * @package eTraxis
 * @ignore
 */

//--------------------------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system.
//  Copyright (C) 2006-2010 by Artem Rodygin
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
//  Artem Rodygin           2006-09-29      new-318: Group permissions should be template-wide.
//  Artem Rodygin           2006-12-14      bug-443: 'Template permissions' page doesn't work.
//  Artem Rodygin           2006-12-27      bug-470: State permissions must not be used when record is being created.
//  Artem Rodygin           2007-07-04      new-533: Links between records.
//  Artem Rodygin           2007-07-16      new-546: Confidential comments.
//  Artem Rodygin           2007-08-06      new-551: Rework dependencies into "parent-child" relations.
//  Artem Rodygin           2007-09-29      new-568: Permissions to operate with record should not depend on permission to view the record.
//  Artem Rodygin           2007-11-13      new-622: Rename 'children' into 'subrecords'.
//  Artem Rodygin           2007-11-26      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2008-01-05      new-648: Template-wide author permissions.
//  Artem Rodygin           2008-01-28      new-531: LDAP Guest users
//  Artem Rodygin           2008-02-27      new-676: [SF1898731] Delete Issues from Workflow
//  Artem Rodygin           2008-04-20      new-703: Separated permissions set for current responsible.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-01-08      new-774: 'Anyone' system role permissions.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//  Artem Rodygin           2009-07-14      bug-834: Permission to create records should be disabled for author and responsible.
//  Artem Rodygin           2009-07-14      bug-835: Template permissions could not be accessed while no group is created yet.
//  Artem Rodygin           2010-01-26      bug-894: Some pages don't work in Google Chrome.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/groups.php');
require_once('../dbo/templates.php');
require_once('../dbo/records.php');
/**#@-*/

init_page();

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    header('Location: index.php');
    exit;
}

$id       = ustr2int(try_request('id'));
$template = template_find($id);

if (!$template)
{
    debug_write_log(DEBUG_NOTICE, 'Template cannot be found.');
    header('Location: index.php');
    exit;
}

$sort = $page = NULL;
$list = group_list($template['project_id'], $sort, $page);

$gid = ustr2int(try_request('gid', TEMPLATE_ROLE_AUTHOR), MIN_TEMPLATE_ROLE);

if ($gid >= 0)
{
    $group = group_find($gid);

    if (!$group)
    {
        debug_write_log(DEBUG_NOTICE, 'Group cannot be found.');
        $gid = TEMPLATE_ROLE_AUTHOR;
    }
}

if (try_request('submitted') == 'rform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $permissions = 0;

    $permissions |= (isset($_REQUEST['permit0'])  ? PERMIT_VIEW_RECORD           : 0);
    $permissions |= (isset($_REQUEST['permit1'])  ? PERMIT_CREATE_RECORD         : 0);
    $permissions |= (isset($_REQUEST['permit2'])  ? PERMIT_MODIFY_RECORD         : 0);
    $permissions |= (isset($_REQUEST['permit3'])  ? PERMIT_POSTPONE_RECORD       : 0);
    $permissions |= (isset($_REQUEST['permit4'])  ? PERMIT_RESUME_RECORD         : 0);
    $permissions |= (isset($_REQUEST['permit5'])  ? PERMIT_REASSIGN_RECORD       : 0);
    $permissions |= (isset($_REQUEST['permit6'])  ? PERMIT_CHANGE_STATE          : 0);
    $permissions |= (isset($_REQUEST['permit7'])  ? PERMIT_ADD_COMMENTS          : 0);
    $permissions |= (isset($_REQUEST['permit8'])  ? PERMIT_CONFIDENTIAL_COMMENTS : 0);
    $permissions |= (isset($_REQUEST['permit9'])  ? PERMIT_ATTACH_FILES          : 0);
    $permissions |= (isset($_REQUEST['permit10']) ? PERMIT_REMOVE_FILES          : 0);
    $permissions |= (isset($_REQUEST['permit11']) ? PERMIT_SEND_REMINDERS        : 0);
    $permissions |= (isset($_REQUEST['permit12']) ? PERMIT_DELETE_RECORD         : 0);
    $permissions |= (isset($_REQUEST['permit13']) ? PERMIT_ADD_SUBRECORDS        : 0);
    $permissions |= (isset($_REQUEST['permit14']) ? PERMIT_REMOVE_SUBRECORDS     : 0);

    switch ($gid)
    {
        case TEMPLATE_ROLE_AUTHOR:
            $permissions &= ~PERMIT_VIEW_RECORD;
            template_author_perm_set($id, $permissions);
            break;

        case TEMPLATE_ROLE_RESPONSIBLE:
            $permissions &= ~PERMIT_VIEW_RECORD;
            template_responsible_perm_set($id, $permissions);
            break;

        case TEMPLATE_ROLE_REGISTERED:
            template_registered_perm_set($id, $permissions);
            break;

        default:
            group_set_permissions($gid, $id, $permissions);
    }

    header('Location: tperms.php?id=' . $id . '&gid=' . $gid);
    exit;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');
}

$script = '<script>function onList(){'
        . 'var elems=document.rform.getElementsByTagName(\'input\');'
        . 'for(i=0;i!=elems.length;i++)if(elems[i].type==\'checkbox\')elems[i].checked=false;'
        . 'switch(document.lform.groups.value) {'
        . 'case \'' . TEMPLATE_ROLE_AUTHOR . '\':'
        . 'document.rform.permit0.disabled=true;'
        . 'document.rform.permit0.checked=true;'
        . 'document.rform.permit1.disabled=true;'
        . 'document.rform.permit1.checked=false;'
        . (($template['author_perm'] & PERMIT_MODIFY_RECORD)         == 0 ? NULL : 'document.rform.permit2.checked=true;')
        . (($template['author_perm'] & PERMIT_POSTPONE_RECORD)       == 0 ? NULL : 'document.rform.permit3.checked=true;')
        . (($template['author_perm'] & PERMIT_RESUME_RECORD)         == 0 ? NULL : 'document.rform.permit4.checked=true;')
        . (($template['author_perm'] & PERMIT_REASSIGN_RECORD)       == 0 ? NULL : 'document.rform.permit5.checked=true;')
        . (($template['author_perm'] & PERMIT_CHANGE_STATE)          == 0 ? NULL : 'document.rform.permit6.checked=true;')
        . (($template['author_perm'] & PERMIT_ADD_COMMENTS)          == 0 ? NULL : 'document.rform.permit7.checked=true;')
        . (($template['author_perm'] & PERMIT_CONFIDENTIAL_COMMENTS) == 0 ? NULL : 'document.rform.permit8.checked=true;')
        . (($template['author_perm'] & PERMIT_ATTACH_FILES)          == 0 ? NULL : 'document.rform.permit9.checked=true;')
        . (($template['author_perm'] & PERMIT_REMOVE_FILES)          == 0 ? NULL : 'document.rform.permit10.checked=true;')
        . (($template['author_perm'] & PERMIT_SEND_REMINDERS)        == 0 ? NULL : 'document.rform.permit11.checked=true;')
        . (($template['author_perm'] & PERMIT_DELETE_RECORD)         == 0 ? NULL : 'document.rform.permit12.checked=true;')
        . (($template['author_perm'] & PERMIT_ADD_SUBRECORDS)        == 0 ? NULL : 'document.rform.permit13.checked=true;')
        . (($template['author_perm'] & PERMIT_REMOVE_SUBRECORDS)     == 0 ? NULL : 'document.rform.permit14.checked=true;')
        . 'break;'
        . 'case \'' . TEMPLATE_ROLE_RESPONSIBLE . '\':'
        . 'document.rform.permit0.disabled=true;'
        . 'document.rform.permit0.checked=true;'
        . 'document.rform.permit1.disabled=true;'
        . 'document.rform.permit1.checked=false;'
        . (($template['responsible_perm'] & PERMIT_MODIFY_RECORD)         == 0 ? NULL : 'document.rform.permit2.checked=true;')
        . (($template['responsible_perm'] & PERMIT_POSTPONE_RECORD)       == 0 ? NULL : 'document.rform.permit3.checked=true;')
        . (($template['responsible_perm'] & PERMIT_RESUME_RECORD)         == 0 ? NULL : 'document.rform.permit4.checked=true;')
        . (($template['responsible_perm'] & PERMIT_REASSIGN_RECORD)       == 0 ? NULL : 'document.rform.permit5.checked=true;')
        . (($template['responsible_perm'] & PERMIT_CHANGE_STATE)          == 0 ? NULL : 'document.rform.permit6.checked=true;')
        . (($template['responsible_perm'] & PERMIT_ADD_COMMENTS)          == 0 ? NULL : 'document.rform.permit7.checked=true;')
        . (($template['responsible_perm'] & PERMIT_CONFIDENTIAL_COMMENTS) == 0 ? NULL : 'document.rform.permit8.checked=true;')
        . (($template['responsible_perm'] & PERMIT_ATTACH_FILES)          == 0 ? NULL : 'document.rform.permit9.checked=true;')
        . (($template['responsible_perm'] & PERMIT_REMOVE_FILES)          == 0 ? NULL : 'document.rform.permit10.checked=true;')
        . (($template['responsible_perm'] & PERMIT_SEND_REMINDERS)        == 0 ? NULL : 'document.rform.permit11.checked=true;')
        . (($template['responsible_perm'] & PERMIT_DELETE_RECORD)         == 0 ? NULL : 'document.rform.permit12.checked=true;')
        . (($template['responsible_perm'] & PERMIT_ADD_SUBRECORDS)        == 0 ? NULL : 'document.rform.permit13.checked=true;')
        . (($template['responsible_perm'] & PERMIT_REMOVE_SUBRECORDS)     == 0 ? NULL : 'document.rform.permit14.checked=true;')
        . 'break;'
        . 'case \'' . TEMPLATE_ROLE_REGISTERED . '\':'
        . 'document.rform.permit0.disabled=false;'
        . 'document.rform.permit1.disabled=false;'
        . (($template['registered_perm'] & PERMIT_VIEW_RECORD)           == 0 ? NULL : 'document.rform.permit0.checked=true;')
        . (($template['registered_perm'] & PERMIT_CREATE_RECORD)         == 0 ? NULL : 'document.rform.permit1.checked=true;')
        . (($template['registered_perm'] & PERMIT_MODIFY_RECORD)         == 0 ? NULL : 'document.rform.permit2.checked=true;')
        . (($template['registered_perm'] & PERMIT_POSTPONE_RECORD)       == 0 ? NULL : 'document.rform.permit3.checked=true;')
        . (($template['registered_perm'] & PERMIT_RESUME_RECORD)         == 0 ? NULL : 'document.rform.permit4.checked=true;')
        . (($template['registered_perm'] & PERMIT_REASSIGN_RECORD)       == 0 ? NULL : 'document.rform.permit5.checked=true;')
        . (($template['registered_perm'] & PERMIT_CHANGE_STATE)          == 0 ? NULL : 'document.rform.permit6.checked=true;')
        . (($template['registered_perm'] & PERMIT_ADD_COMMENTS)          == 0 ? NULL : 'document.rform.permit7.checked=true;')
        . (($template['registered_perm'] & PERMIT_CONFIDENTIAL_COMMENTS) == 0 ? NULL : 'document.rform.permit8.checked=true;')
        . (($template['registered_perm'] & PERMIT_ATTACH_FILES)          == 0 ? NULL : 'document.rform.permit9.checked=true;')
        . (($template['registered_perm'] & PERMIT_REMOVE_FILES)          == 0 ? NULL : 'document.rform.permit10.checked=true;')
        . (($template['registered_perm'] & PERMIT_SEND_REMINDERS)        == 0 ? NULL : 'document.rform.permit11.checked=true;')
        . (($template['registered_perm'] & PERMIT_DELETE_RECORD)         == 0 ? NULL : 'document.rform.permit12.checked=true;')
        . (($template['registered_perm'] & PERMIT_ADD_SUBRECORDS)        == 0 ? NULL : 'document.rform.permit13.checked=true;')
        . (($template['registered_perm'] & PERMIT_REMOVE_SUBRECORDS)     == 0 ? NULL : 'document.rform.permit14.checked=true;')
        . 'break;';

while (($item = $list->fetch()))
{
    $permissions = group_get_permissions($item['group_id'], $id);

    $script .= 'case \'' . $item['group_id'] . '\':'
             . 'document.rform.permit0.disabled=false;'
             . 'document.rform.permit1.disabled=false;'
             . (($permissions & PERMIT_VIEW_RECORD)           == 0 ? NULL : 'document.rform.permit0.checked=true;')
             . (($permissions & PERMIT_CREATE_RECORD)         == 0 ? NULL : 'document.rform.permit1.checked=true;')
             . (($permissions & PERMIT_MODIFY_RECORD)         == 0 ? NULL : 'document.rform.permit2.checked=true;')
             . (($permissions & PERMIT_POSTPONE_RECORD)       == 0 ? NULL : 'document.rform.permit3.checked=true;')
             . (($permissions & PERMIT_RESUME_RECORD)         == 0 ? NULL : 'document.rform.permit4.checked=true;')
             . (($permissions & PERMIT_REASSIGN_RECORD)       == 0 ? NULL : 'document.rform.permit5.checked=true;')
             . (($permissions & PERMIT_CHANGE_STATE)          == 0 ? NULL : 'document.rform.permit6.checked=true;')
             . (($permissions & PERMIT_ADD_COMMENTS)          == 0 ? NULL : 'document.rform.permit7.checked=true;')
             . (($permissions & PERMIT_CONFIDENTIAL_COMMENTS) == 0 ? NULL : 'document.rform.permit8.checked=true;')
             . (($permissions & PERMIT_ATTACH_FILES)          == 0 ? NULL : 'document.rform.permit9.checked=true;')
             . (($permissions & PERMIT_REMOVE_FILES)          == 0 ? NULL : 'document.rform.permit10.checked=true;')
             . (($permissions & PERMIT_SEND_REMINDERS)        == 0 ? NULL : 'document.rform.permit11.checked=true;')
             . (($permissions & PERMIT_DELETE_RECORD)         == 0 ? NULL : 'document.rform.permit12.checked=true;')
             . (($permissions & PERMIT_ADD_SUBRECORDS)        == 0 ? NULL : 'document.rform.permit13.checked=true;')
             . (($permissions & PERMIT_REMOVE_SUBRECORDS)     == 0 ? NULL : 'document.rform.permit14.checked=true;')
             . 'break;';
}

$script .= '}}</script>';

$xml = '<page' . gen_xml_page_header(get_html_resource(RES_PERMISSIONS_ID), NULL, NULL, 'onList();') . '>'
     . $script
     . gen_xml_menu()
     . '<path>'
     . '<pathitem url="index.php">'                                      . get_html_resource(RES_PROJECTS_ID)                                                       . '</pathitem>'
     . '<pathitem url="view.php?id='   . $template['project_id']  . '">' . ustrprocess(get_html_resource(RES_PROJECT_X_ID), ustr2html($template['project_name']))   . '</pathitem>'
     . '<pathitem url="tindex.php?id=' . $template['project_id']  . '">' . get_html_resource(RES_TEMPLATES_ID)                                                      . '</pathitem>'
     . '<pathitem url="tview.php?id='  . $id                      . '">' . ustrprocess(get_html_resource(RES_TEMPLATE_X_ID), ustr2html($template['template_name'])) . '</pathitem>'
     . '<pathitem url="tperms.php?id=' . $id . '&amp;gid=' . $gid . '">' . get_html_resource(RES_PERMISSIONS_ID)                                                    . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<dualbox nobuttons="true">'
     . '<dualleft>'
     . '<group title="' . get_html_resource(RES_GROUPS_ID) . '">'
     . '<listbox dualbox="true" name="groups" size="' . HTML_LISTBOX_SIZE . '" action="onList();">'
     . '<listitem value="' . TEMPLATE_ROLE_AUTHOR      . '"' . ($gid == TEMPLATE_ROLE_AUTHOR      ? ' selected="true">' : '>') . get_html_resource(RES_AUTHOR_ID)      . ' (' . get_html_resource(RES_ROLE_ID) . ')</listitem>'
     . '<listitem value="' . TEMPLATE_ROLE_RESPONSIBLE . '"' . ($gid == TEMPLATE_ROLE_RESPONSIBLE ? ' selected="true">' : '>') . get_html_resource(RES_RESPONSIBLE_ID) . ' (' . get_html_resource(RES_ROLE_ID) . ')</listitem>'
     . '<listitem value="' . TEMPLATE_ROLE_REGISTERED  . '"' . ($gid == TEMPLATE_ROLE_REGISTERED  ? ' selected="true">' : '>') . get_html_resource(RES_REGISTERED_ID)  . ' (' . get_html_resource(RES_ROLE_ID) . ')</listitem>';

switch ($gid)
{
    case TEMPLATE_ROLE_AUTHOR:
        $permissions = $template['author_perm'];
        break;
    case TEMPLATE_ROLE_RESPONSIBLE:
        $permissions = $template['responsible_perm'];
        break;
    case TEMPLATE_ROLE_REGISTERED:
        $permissions = $template['registered_perm'];
        break;
    default:
        $permissions = group_get_permissions($gid, $id);
}

$list->seek();

while (($item = $list->fetch()))
{
    $xml .= '<listitem value="' . $item['group_id'] . '"' . ($gid == $item['group_id'] ? ' selected="true">' : '>') . ustr2html($item['group_name']) . ' (' . get_html_resource(is_null($item['project_id']) ? RES_GLOBAL_ID : RES_LOCAL_ID) . ')</listitem>';
}

$xml .= '</listbox>'
      . '</group>'
      . '</dualleft>'
      . '<dualright action="tperms.php?id=' . $id . '">'
      . '<group title="' . get_html_resource(RES_PERMISSIONS_ID) . '">'
      . '<checkbox name="permit0"'  . (($permissions & PERMIT_VIEW_RECORD)           == 0 ? '>' : ' checked="true">') . get_html_resource(RES_PERMIT_VIEW_RECORDS_ONLY_ID)     . '</checkbox>'
      . '<checkbox name="permit1"'  . (($permissions & PERMIT_CREATE_RECORD)         == 0 ? '>' : ' checked="true">') . get_html_resource(RES_PERMIT_CREATE_RECORD_ID)         . '</checkbox>'
      . '<checkbox name="permit2"'  . (($permissions & PERMIT_MODIFY_RECORD)         == 0 ? '>' : ' checked="true">') . get_html_resource(RES_PERMIT_MODIFY_RECORD_ID)         . '</checkbox>'
      . '<checkbox name="permit3"'  . (($permissions & PERMIT_POSTPONE_RECORD)       == 0 ? '>' : ' checked="true">') . get_html_resource(RES_PERMIT_POSTPONE_RECORD_ID)       . '</checkbox>'
      . '<checkbox name="permit4"'  . (($permissions & PERMIT_RESUME_RECORD)         == 0 ? '>' : ' checked="true">') . get_html_resource(RES_PERMIT_RESUME_RECORD_ID)         . '</checkbox>'
      . '<checkbox name="permit5"'  . (($permissions & PERMIT_REASSIGN_RECORD)       == 0 ? '>' : ' checked="true">') . get_html_resource(RES_PERMIT_REASSIGN_RECORD_ID)       . '</checkbox>'
      . '<checkbox name="permit6"'  . (($permissions & PERMIT_CHANGE_STATE)          == 0 ? '>' : ' checked="true">') . get_html_resource(RES_PERMIT_CHANGE_STATE_ID)          . '</checkbox>'
      . '<checkbox name="permit7"'  . (($permissions & PERMIT_ADD_COMMENTS)          == 0 ? '>' : ' checked="true">') . get_html_resource(RES_PERMIT_ADD_COMMENTS_ID)          . '</checkbox>'
      . '<checkbox name="permit8"'  . (($permissions & PERMIT_CONFIDENTIAL_COMMENTS) == 0 ? '>' : ' checked="true">') . get_html_resource(RES_PERMIT_CONFIDENTIAL_COMMENTS_ID) . '</checkbox>'
      . '<checkbox name="permit9"'  . (($permissions & PERMIT_ATTACH_FILES)          == 0 ? '>' : ' checked="true">') . get_html_resource(RES_PERMIT_ATTACH_FILES_ID)          . '</checkbox>'
      . '<checkbox name="permit10"' . (($permissions & PERMIT_REMOVE_FILES)          == 0 ? '>' : ' checked="true">') . get_html_resource(RES_PERMIT_REMOVE_FILES_ID)          . '</checkbox>'
      . '<checkbox name="permit11"' . (($permissions & PERMIT_SEND_REMINDERS)        == 0 ? '>' : ' checked="true">') . get_html_resource(RES_PERMIT_SEND_REMINDERS_ID)        . '</checkbox>'
      . '<checkbox name="permit12"' . (($permissions & PERMIT_DELETE_RECORD)         == 0 ? '>' : ' checked="true">') . get_html_resource(RES_PERMIT_DELETE_RECORD_ID)         . '</checkbox>'
      . '<checkbox name="permit13"' . (($permissions & PERMIT_ADD_SUBRECORDS)        == 0 ? '>' : ' checked="true">') . get_html_resource(RES_PERMIT_ADD_SUBRECORDS_ID)        . '</checkbox>'
      . '<checkbox name="permit14"' . (($permissions & PERMIT_REMOVE_SUBRECORDS)     == 0 ? '>' : ' checked="true">') . get_html_resource(RES_PERMIT_REMOVE_SUBRECORDS_ID)     . '</checkbox>'
      . '</group>'
      . '</dualright>'
      . '</dualbox>'
      . '<button url="tview.php?id=' . $id . '">' . get_html_resource(RES_BACK_ID) . '</button>'
      . '<button action="var elems=document.rform.getElementsByTagName(\'input\');for(i=0;i!=elems.length;i++)if(elems[i].type==\'checkbox\')elems[i].checked=true;">' . get_html_resource(RES_SELECT_ALL_ID) . '</button>'
      . '<button action="rform.action=\'tperms.php?id=' . $id . '&amp;gid=\'+lform.groups.value; rform.submit();">' . get_html_resource(RES_SAVE_ID) . '</button>'
      . '</content>'
      . '</page>';

echo(xml2html($xml));

?>
