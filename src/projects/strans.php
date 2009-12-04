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
//  Artem Rodygin           2005-03-20      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-08-01      new-013: UI scenarios should be changed.
//  Artem Rodygin           2005-08-18      new-037: Any template should be locked to be modified without suspending a project.
//  Artem Rodygin           2005-08-23      new-053: All the calls of DAL API functions should be moved to DBO API.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-11-17      new-176: Change eTraxis design.
//  Artem Rodygin           2005-11-29      new-187: User controls alignment.
//  Artem Rodygin           2006-07-12      bug-292: Sablotron fails if page contains '&' character.
//  Artem Rodygin           2007-01-05      new-491: [SF1647212] Group-wide transition permission.
//  Artem Rodygin           2007-11-26      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2008-01-28      new-531: LDAP Guest users
//  Artem Rodygin           2008-04-20      new-703: Separated permissions set for current responsible.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-01-08      new-774: 'Anyone' system role permissions.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/groups.php');
require_once('../dbo/states.php');
/**#@-*/

init_page();

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    header('Location: index.php');
    exit;
}

$id = ustr2int(try_request('id'));
$state = state_find($id);

if (!$state)
{
    debug_write_log(DEBUG_NOTICE, 'State cannot be found.');
    header('Location: index.php');
    exit;
}

$gid = ustr2int(try_request('gid', STATE_ROLE_AUTHOR), MIN_STATE_ROLE);

if ($gid >= 0)
{
    if (!group_find($gid))
    {
        debug_write_log(DEBUG_NOTICE, 'Group cannot be found.');
        $gid = STATE_ROLE_AUTHOR;
    }
}

$sort = $page = NULL;
$list = group_list($state['project_id'], $sort, $page);

if (try_request('submitted') == 'rform' && $state['is_locked'])
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $sort = $page = NULL;
    $states = state_list($state['template_id'], $sort, $page);

    dal_query(($gid < 0 ? 'states/rtdelete.sql' : 'states/gtdelete.sql'), $id, $gid);

    while (($item = $states->fetch()))
    {
        if (isset($_REQUEST['state' . $item['state_id']]))
        {
            dal_query(($gid < 0 ? 'states/rtadd.sql' : 'states/gtadd.sql'), $id, $item['state_id'], $gid);
        }
    }

    header('Location: strans.php?id=' . $id . '&gid=' . $gid);
    exit;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');
}

$script = '<script>function onList(){'
        . 'for(i=2;i!=document.rform.length;i++)document.rform.elements[i].checked=false;'
        . 'switch(document.lform.groups.value){';

$states = dal_query('states/rtlist.sql', $state['template_id'], $id, STATE_ROLE_AUTHOR);

$script .= 'case \'' . STATE_ROLE_AUTHOR . '\':';

while (($item = $states->fetch()))
{
    $script .= ($item['is_set'] == 0 ? NULL : 'document.rform.state' . $item['state_id'] . '.checked=true;');
}

$script .= 'break;';

$states = dal_query('states/rtlist.sql', $state['template_id'], $id, STATE_ROLE_RESPONSIBLE);

$script .= 'case \'' . STATE_ROLE_RESPONSIBLE . '\':';

while (($item = $states->fetch()))
{
    $script .= ($item['is_set'] == 0 ? NULL : 'document.rform.state' . $item['state_id'] . '.checked=true;');
}

$script .= 'break;';

$states = dal_query('states/rtlist.sql', $state['template_id'], $id, STATE_ROLE_REGISTERED);

$script .= 'case \'' . STATE_ROLE_REGISTERED . '\':';

while (($item = $states->fetch()))
{
    $script .= ($item['is_set'] == 0 ? NULL : 'document.rform.state' . $item['state_id'] . '.checked=true;');
}

$script .= 'break;';

while (($item = $list->fetch()))
{
    $states = dal_query('states/gtlist.sql', $state['template_id'], $id, $item['group_id']);

    $script .= 'case \'' . $item['group_id'] . '\':';

    $states->seek();

    while (($item = $states->fetch()))
    {
        $script .= ($item['is_set'] == 0 ? NULL : 'document.rform.state' . $item['state_id'] . '.checked=true;');
    }

    $script .= 'break;';
}

$script .= '}}</script>';

$xml = '<page' . gen_xml_page_header(ustrprocess(get_html_resource(RES_STATE_X_ID), ustr2html($state['state_name']))) . '>'
     . $script
     . gen_xml_menu()
     . '<path>'
     . '<pathitem url="index.php">'                                   . get_html_resource(RES_PROJECTS_ID)                                                    . '</pathitem>'
     . '<pathitem url="view.php?id='   . $state['project_id']  . '">' . ustrprocess(get_html_resource(RES_PROJECT_X_ID), ustr2html($state['project_name']))   . '</pathitem>'
     . '<pathitem url="tindex.php?id=' . $state['project_id']  . '">' . get_html_resource(RES_TEMPLATES_ID)                                                   . '</pathitem>'
     . '<pathitem url="tview.php?id='  . $state['template_id'] . '">' . ustrprocess(get_html_resource(RES_TEMPLATE_X_ID), ustr2html($state['template_name'])) . '</pathitem>'
     . '<pathitem url="sindex.php?id=' . $state['template_id'] . '">' . get_html_resource(RES_STATES_ID)                                                      . '</pathitem>'
     . '<pathitem url="sview.php?id='  . $id                   . '">' . ustrprocess(get_html_resource(RES_STATE_X_ID), ustr2html($state['state_name']))       . '</pathitem>'
     . '<pathitem url="strans.php?id=' . $id                   . '">' . get_html_resource(RES_TRANSITIONS_ID)                                                 . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<dualbox nobuttons="true">'
     . '<dualleft action="strans.php?id=' . $id . '">'
     . '<group title="' . get_html_resource(RES_GROUPS_ID) . '">'
     . '<listbox dualbox="true" name="groups" size="' . HTML_LISTBOX_SIZE . '" action="onList();">'
     . '<listitem value="' . STATE_ROLE_AUTHOR      . '"' . ($gid == STATE_ROLE_AUTHOR      ? ' selected="true">' : '>') . sprintf('%s (%s)', get_html_resource(RES_AUTHOR_ID),      get_html_resource(RES_ROLE_ID)) . '</listitem>'
     . '<listitem value="' . STATE_ROLE_RESPONSIBLE . '"' . ($gid == STATE_ROLE_RESPONSIBLE ? ' selected="true">' : '>') . sprintf('%s (%s)', get_html_resource(RES_RESPONSIBLE_ID), get_html_resource(RES_ROLE_ID)) . '</listitem>'
     . '<listitem value="' . STATE_ROLE_REGISTERED  . '"' . ($gid == STATE_ROLE_REGISTERED  ? ' selected="true">' : '>') . sprintf('%s (%s)', get_html_resource(RES_REGISTERED_ID),  get_html_resource(RES_ROLE_ID)) . '</listitem>';

$list->seek();

while (($item = $list->fetch()))
{
    $xml .= '<listitem value="' . $item['group_id'] . '"' . ($gid == $item['group_id'] ? ' selected="true">' : '>') . ustr2html($item['group_name'])  . ' (' . get_html_resource(is_null($item['project_id']) ? RES_GLOBAL_ID : RES_LOCAL_ID) . ')</listitem>';
}

$xml .= '</listbox>'
      . '</group>'
      . '</dualleft>'
      . '<dualright action="strans.php?id=' . $id . '">'
      . '<group title="' . get_html_resource(RES_TRANSITIONS_ID) . '">';

$states = dal_query(($gid < 0 ? 'states/rtlist.sql' : 'states/gtlist.sql'), $state['template_id'], $id, $gid);

while (($item = $states->fetch()))
{
    $xml .= '<checkbox name="state' . $item['state_id'] . '"'  . ($state['is_locked'] ? NULL : ' readonly="true"') . ($item['is_set'] == 0 ? '>' : ' checked="true">') . ustr2html($item['state_name']) . '</checkbox>';
}

$xml .= '</group>'
      . '</dualright>'
      . '</dualbox>'
      . '<button url="sview.php?id=' . $id . '">' . get_html_resource(RES_BACK_ID) . '</button>';

if ($state['is_locked'])
{
    $xml .= '<button action="rform.action=\'strans.php?id=' . $id . '&amp;gid=\'+lform.groups.value; rform.submit();">' . get_html_resource(RES_SAVE_ID) . '</button>';
}

$xml .= '</content>'
      . '</page>';

echo(xml2html($xml));

?>
