<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2005-2011  Artem Rodygin
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
/**#@-*/

init_page(LOAD_TAB);

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    exit;
}

// check that requested group exists

$id    = ustr2int(try_request('id'));
$group = group_find($id);

if (!$group)
{
    debug_write_log(DEBUG_NOTICE, 'Group cannot be found.');
    exit;
}

if (!$group['is_global'])
{
    debug_write_log(DEBUG_NOTICE, 'Group must be global.');
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
    jqModal("{$resTitle}", "modify.php?id={$id}", "{$resOK}", "{$resCancel}", "$('#modifyform').submit()");
}

</script>
JQUERY;

// generate buttons

$xml .= '<button url="index.php">' . get_html_resource(RES_BACK_ID) . '</button>'
      . '<buttonset>'
      . '<button action="groupModify()">' . get_html_resource(RES_MODIFY_ID) . '</button>';

$xml .= (is_group_removable($id)
            ? '<button url="delete.php?id=' . $id . '" prompt="' . get_html_resource(RES_CONFIRM_DELETE_GROUP_ID) . '">'
            : '<button disabled="false">')
      . get_html_resource(RES_DELETE_ID)
      . '</button>'
      . '</buttonset>';

// generate group information

$xml .= '<group title="' . get_html_resource(RES_GROUP_INFO_ID) . '">'
      . '<text label="' . get_html_resource(RES_GROUP_NAME_ID)  . '">' . ustr2html($group['group_name'])  . '</text>'
      . '<text label="' . get_html_resource(RES_DESCRIPTION_ID) . '">' . ustr2html($group['description']) . '</text>'
      . '</group>';

echo(xml2html($xml));

?>
