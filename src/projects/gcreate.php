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
//  Artem Rodygin           2005-02-26      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-08-01      new-013: UI scenarios should be changed.
//  Artem Rodygin           2005-08-18      new-036: Groups should be editable without suspending a project.
//  Artem Rodygin           2005-08-25      new-058: Global groups should be implemented.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-11-17      new-176: Change eTraxis design.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//  Artem Rodygin           2009-10-12      new-837: Replace "Groups" with "Global groups" in main menu.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/projects.php');
require_once('../dbo/groups.php');
/**#@-*/

init_page();

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    header('Location: index.php');
    exit;
}

$id      = ustr2int(try_request('id'));
$project = project_find($id);

if (!$project)
{
    debug_write_log(DEBUG_NOTICE, 'Project cannot be found.');
}

if (try_request('submitted') == 'mainform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $group_name  = ustrcut($_REQUEST['group_name'],  MAX_GROUP_NAME);
    $description = ustrcut($_REQUEST['description'], MAX_GROUP_DESCRIPTION);

    $error = group_validate($group_name);

    if ($error == NO_ERROR)
    {
        $error = group_create($id, $group_name, $description);

        if ($error == NO_ERROR)
        {
            header('Location: gindex.php?id=' . $id);
            exit;
        }
    }

    switch ($error)
    {
        case ERROR_INCOMPLETE_FORM:
            $alert = get_js_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID);
            break;
        case ERROR_ALREADY_EXISTS:
            $alert = get_js_resource(RES_ALERT_GROUP_ALREADY_EXISTS_ID);
            break;
        default:
            $alert = NULL;
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $group_name  = NULL;
    $description = NULL;
}

$xml = '<page' . gen_xml_page_header(get_html_resource(RES_NEW_GROUP_ID), isset($alert) ? $alert : NULL, 'mainform.group_name') . '>'
     . gen_xml_menu()
     . '<path>';

if ($project)
{
    $xml .= '<pathitem url="index.php">' . get_html_resource(RES_PROJECTS_ID) . '</pathitem>'
          . '<pathitem url="view.php?id=' . $id . '">' . ustrprocess(get_html_resource(RES_PROJECT_X_ID), ustr2html($project['project_name'])) . '</pathitem>'
          . '<pathitem url="gindex.php?id=' . $id . '">' . get_html_resource(RES_GROUPS_ID) . '</pathitem>';
}
else
{
    $xml .= '<pathitem url="gindex.php?id=' . $id . '">' . get_html_resource(RES_GLOBAL_GROUPS_ID) . '</pathitem>';
}

$xml .= '<pathitem url="gcreate.php?id=' . $id . '">' . get_html_resource(RES_NEW_GROUP_ID) . '</pathitem>'
      . '</path>'
      . '<content>'
      . '<form name="mainform" action="gcreate.php?id=' . $id . '">'
      . '<group title="' . get_html_resource(RES_GROUP_INFO_ID) . '">'
      . '<editbox label="' . get_html_resource(RES_GROUP_NAME_ID)  . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="group_name"  size="' . HTML_EDITBOX_SIZE_MEDIUM . '" maxlen="' . MAX_GROUP_NAME        . '">' . ustr2html($group_name)  . '</editbox>'
      . '<editbox label="' . get_html_resource(RES_DESCRIPTION_ID) . '"                                                        name="description" size="' . HTML_EDITBOX_SIZE_LONG   . '" maxlen="' . MAX_GROUP_DESCRIPTION . '">' . ustr2html($description) . '</editbox>'
      . '</group>'
      . '<button default="true">'                  . get_html_resource(RES_CREATE_ID) . '</button>'
      . '<button url="gindex.php?id=' . $id . '">' . get_html_resource(RES_CANCEL_ID) . '</button>'
      . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>'
      . '</form>'
      . '</content>'
      . '</page>';

echo(xml2html($xml));

?>
