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
//  Artem Rodygin           2005-08-01      new-013: UI scenarios should be changed.
//  Artem Rodygin           2005-08-18      new-037: Any template should be locked to be modified without suspending a project.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-09-13      new-113: When record is being viewed the fields names and values should be aligned by top.
//  Artem Rodygin           2005-10-05      new-145: Remove autofocus from buttons.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-11-17      new-176: Change eTraxis design.
//  Artem Rodygin           2006-07-27      new-261: UI design should be adopted to slow connection.
//  Artem Rodygin           2006-10-08      bug-351: /src/projects/sview.php: Global variable $alert was used before it was defined.
//  Artem Rodygin           2007-01-05      new-491: [SF1647212] Group-wide transition permission.
//  Yury Udovichenko        2007-11-19      new-623: Default state in states list.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//  Artem Rodygin           2009-10-13      new-838: Disabled buttons would be better grayed out than invisible.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/states.php');
/**#@-*/

init_page();

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    header('Location: index.php');
    exit;
}

$id    = ustr2int(try_request('id'));
$state = state_find($id);

if (!$state)
{
    debug_write_log(DEBUG_NOTICE, 'State cannot be found.');
    header('Location: index.php');
    exit;
}

$xml = '<page' . gen_xml_page_header(ustrprocess(get_html_resource(RES_STATE_X_ID), ustr2html($state['state_name']))) . '>'
     . gen_xml_menu()
     . '<path>'
     . '<pathitem url="index.php">'                                   . get_html_resource(RES_PROJECTS_ID)                                                    . '</pathitem>'
     . '<pathitem url="view.php?id='   . $state['project_id']  . '">' . ustrprocess(get_html_resource(RES_PROJECT_X_ID), ustr2html($state['project_name']))   . '</pathitem>'
     . '<pathitem url="tindex.php?id=' . $state['project_id']  . '">' . get_html_resource(RES_TEMPLATES_ID)                                                   . '</pathitem>'
     . '<pathitem url="tview.php?id='  . $state['template_id'] . '">' . ustrprocess(get_html_resource(RES_TEMPLATE_X_ID), ustr2html($state['template_name'])) . '</pathitem>'
     . '<pathitem url="sindex.php?id=' . $state['template_id'] . '">' . get_html_resource(RES_STATES_ID)                                                      . '</pathitem>'
     . '<pathitem url="sview.php?id='  . $id                   . '">' . ustrprocess(get_html_resource(RES_STATE_X_ID), ustr2html($state['state_name']))       . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<form name="mainform" action="sindex.php?id=' . $state['template_id'] . '">'
     . '<group title="' . get_html_resource(RES_STATE_INFO_ID) . '">'
     . '<text label="' . get_html_resource(RES_STATE_NAME_ID) . '">'  . ustr2html($state['state_name']) . '</text>'
     . '<text label="' . get_html_resource(RES_STATE_ABBR_ID) . '">'  . ustr2html($state['state_abbr']) . '</text>';

if ($state['state_type'] != STATE_TYPE_FINAL)
{
    $resarray = array
    (
        STATE_RESPONSIBLE_REMAIN => RES_REMAIN_ID,
        STATE_RESPONSIBLE_ASSIGN => RES_ASSIGN_ID,
        STATE_RESPONSIBLE_REMOVE => RES_REMOVE_ID,
    );

    $next_state = (is_null($state['next_state_id']) ? get_html_resource(RES_NONE_ID) : ustr2html($state['next_state_name']));

    $xml .= '<text label="' . get_html_resource(RES_NEXT_STATE_BY_DEFAULT_ID) . '">' . $next_state . '</text>'
          . '<text label="' . get_html_resource(RES_RESPONSIBLE_ID) . '">'  . get_html_resource($resarray[$state['responsible']]) . '</text>';
}

$xml .= '</group>'
      . '<button name="back" default="true">' . get_html_resource(RES_BACK_ID) . '</button>';

if ($state['is_locked'])
{
    $xml .= '<button url="smodify.php?id=' . $id . '">' . get_html_resource(RES_MODIFY_ID) . '</button>';

    if (is_state_removable($id))
    {
        $xml .= '<button url="sdelete.php?id=' . $id . '" prompt="' . get_html_resource(RES_CONFIRM_DELETE_STATE_ID) . '">' . get_html_resource(RES_DELETE_ID) . '</button>';
    }
    else
    {
        $xml .= '<button disabled="true">' . get_html_resource(RES_DELETE_ID) . '</button>';
    }
}
else
{
    $xml .= '<button disabled="true">' . get_html_resource(RES_MODIFY_ID) . '</button>'
          . '<button disabled="true">' . get_html_resource(RES_DELETE_ID) . '</button>';
}

$xml .= '<button url="findex.php?id=' . $id . '">' . get_html_resource(RES_FIELDS_ID) . '</button>'
      . '<br/>';

if ($state['state_type'] != STATE_TYPE_FINAL)
{
    $xml .= '<button url="strans.php?id=' . $id . '">' . get_html_resource(RES_TRANSITIONS_ID) . '</button>';

    if ($state['is_locked'] && $state['state_type'] == STATE_TYPE_INTERMEDIATE)
    {
        $xml .= '<button url="initial.php?id=' . $id . '">' . get_html_resource(RES_SET_INITIAL_ID) . '</button>';
    }
    else
    {
        $xml .= '<button disabled="true">' . get_html_resource(RES_SET_INITIAL_ID) . '</button>';
    }
}

$xml .= '</form>'
      . '</content>'
      . '</page>';

echo(xml2html($xml));

?>
