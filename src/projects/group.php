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

// local JS functions

$resTitle  = ustrprocess(get_js_resource(RES_GROUP_X_ID), ustr2js($group['group_name']));
$resOK     = get_js_resource(RES_OK_ID);
$resCancel = get_js_resource(RES_CANCEL_ID);

$xml = <<<JQUERY
<script>

function groupModify ()
{
    jqModal("{$resTitle}", "gmodify.php?id={$id}", "{$resOK}", "{$resCancel}", "$('#modifyform').submit()");
}

</script>
JQUERY;

// generate buttons

$xml .= '<button url="view.php?id=' . $pid . '&amp;tab=2">' . get_html_resource(RES_BACK_ID) . '</button>';

if (!$group['is_global'])
{
    $xml .= '<buttonset>'
          . '<button action="groupModify()">' . get_html_resource(RES_MODIFY_ID) . '</button>'
          . (is_group_removable($id)
                ? '<button url="gdelete.php?id=' . $id . '" prompt="' . get_html_resource(RES_CONFIRM_DELETE_GROUP_ID) . '">'
                : '<button disabled="false">')
          . get_html_resource(RES_DELETE_ID)
          . '</button>'
          . '</buttonset>';
}

// generate group information

$xml .= '<group title="' . get_html_resource(RES_GROUP_INFO_ID) . '">'
      . '<text label="' . get_html_resource(RES_GROUP_NAME_ID)  . '">' . ustr2html($group['group_name']) . '</text>'
      . '<text label="' . get_html_resource(RES_GROUP_TYPE_ID)  . '">' . get_html_resource($group['is_global'] ? RES_GLOBAL_ID : RES_LOCAL_ID) . '</text>'
      . '<text label="' . get_html_resource(RES_DESCRIPTION_ID) . '">' . ustr2html($group['description']) . '</text>'
      . '</group>';

echo(xml2html($xml));

?>
