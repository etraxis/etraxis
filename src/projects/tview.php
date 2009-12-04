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
//  Artem Rodygin           2005-11-08      bug-174: Generated pages should contain <!DOCTYPE> tag.
//  Artem Rodygin           2005-11-17      new-176: Change eTraxis design.
//  Artem Rodygin           2006-07-27      new-261: UI design should be adopted to slow connection.
//  Artem Rodygin           2006-09-29      new-318: Group permissions should be template-wide.
//  Artem Rodygin           2006-10-08      bug-353: /src/projects/tview.php: Global variable $alert was used before it was defined.
//  Artem Rodygin           2007-08-02      new-139: Templates cloning.
//  Artem Rodygin           2008-01-31      new-601: [SF1814666] Export and Import Templates
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//  Artem Rodygin           2009-10-13      new-838: Disabled buttons would be better grayed out than invisible.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/templates.php');
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

$xml = '<page' . gen_xml_page_header(ustrprocess(get_html_resource(RES_TEMPLATE_X_ID), ustr2html($template['template_name']))) . '>'
     . gen_xml_menu()
     . '<path>'
     . '<pathitem url="index.php">'                                     . get_html_resource(RES_PROJECTS_ID)                                                       . '</pathitem>'
     . '<pathitem url="view.php?id='   . $template['project_id'] . '">' . ustrprocess(get_html_resource(RES_PROJECT_X_ID), ustr2html($template['project_name']))   . '</pathitem>'
     . '<pathitem url="tindex.php?id=' . $template['project_id'] . '">' . get_html_resource(RES_TEMPLATES_ID)                                                      . '</pathitem>'
     . '<pathitem url="tview.php?id='  . $id                     . '">' . ustrprocess(get_html_resource(RES_TEMPLATE_X_ID), ustr2html($template['template_name'])) . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<form name="mainform" action="tindex.php?id=' . $template['project_id'] . '">'
     . '<group title="' . get_html_resource(RES_TEMPLATE_INFO_ID) . '">'
     . '<text label="' . get_html_resource(RES_TEMPLATE_NAME_ID)   . '">'  . ustr2html($template['template_name']) . '</text>'
     . '<text label="' . get_html_resource(RES_TEMPLATE_PREFIX_ID) . '">'  . ustr2html($template['template_prefix']) . '</text>'
     . '<text label="' . get_html_resource(RES_CRITICAL_AGE_ID)    . '">'  . (is_null($template['critical_age']) ? get_html_resource(RES_NONE_ID) : ustr2html($template['critical_age'])) . '</text>'
     . '<text label="' . get_html_resource(RES_FROZEN_TIME_ID)     . '">'  . (is_null($template['frozen_time']) ? get_html_resource(RES_NONE_ID) : ustr2html($template['frozen_time'])) . '</text>'
     . '<text label="' . get_html_resource(RES_DESCRIPTION_ID)     . '">'  . ustr2html($template['description']) . '</text>'
     . '<text label="' . get_html_resource(RES_GUEST_ACCESS_ID)    . '">'  . get_html_resource($template['guest_access'] ? RES_YES_ID    : RES_NO_ID)     . '</text>'
     . '<text label="' . get_html_resource(RES_STATUS_ID)          . '">'  . get_html_resource($template['is_locked']    ? RES_LOCKED_ID : RES_ACTIVE_ID) . '</text>'
     . '</group>'
     . '<button name="back" default="true">' . get_html_resource(RES_BACK_ID) . '</button>';

if ($template['is_locked'])
{
    $xml .= '<button url="tunlock.php?id=' . $id . '">' . get_html_resource(RES_UNLOCK_ID) . '</button>'
          . '<button url="tmodify.php?id=' . $id . '">' . get_html_resource(RES_MODIFY_ID) . '</button>';

    if (is_template_removable($id))
    {
        $xml .= '<button url="tdelete.php?id=' . $id . '" prompt="' . get_html_resource(RES_CONFIRM_DELETE_TEMPLATE_ID) . '">' . get_html_resource(RES_DELETE_ID) . '</button>';
    }
    else
    {
        $xml .= '<button disabled="true">' . get_html_resource(RES_DELETE_ID) . '</button>';
    }
}
else
{
    $xml .= '<button url="tlock.php?id=' . $id . '">' . get_html_resource(RES_LOCK_ID)   . '</button>'
          . '<button disabled="true">'                . get_html_resource(RES_MODIFY_ID) . '</button>'
          . '<button disabled="true">'                . get_html_resource(RES_DELETE_ID) . '</button>';
}

$xml .= '<button url="sindex.php?id=' . $id . '">' . get_html_resource(RES_STATES_ID)      . '</button>'
      . '<button url="tperms.php?id=' . $id . '">' . get_html_resource(RES_PERMISSIONS_ID) . '</button>'
      . '<button url="tclone.php?id=' . $id . '">' . get_html_resource(RES_CLONE_ID)       . '</button>'
      . '<button url="export.php?id=' . $id . '">' . get_html_resource(RES_EXPORT_ID)      . '</button>'
      . '</form>'
      . '</content>'
      . '</page>';

echo(xml2html($xml));

?>
