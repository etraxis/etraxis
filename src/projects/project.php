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
/**#@-*/

init_page(GUEST_IS_ALLOWED);

// check that requested project exists

$id      = ustr2int(try_request('id'));
$project = project_find($id);

if (!$project)
{
    debug_write_log(DEBUG_NOTICE, 'Project cannot be found.');
    header('Location: index.php');
    exit;
}

// local JS functions

$resTitle  = ustrprocess(get_js_resource(RES_PROJECT_X_ID), ustr2js($project['project_name']));
$resOK     = get_js_resource(RES_OK_ID);
$resCancel = get_js_resource(RES_CANCEL_ID);

$xml = <<<JQUERY
<script>

function projectModify ()
{
    jqModal("{$resTitle}", "modify.php?id={$id}", "{$resOK}", "{$resCancel}", "$('#modifyform').submit()");
}

function projectDisable ()
{
    $.post("disable.php?id={$id}", function() {
        reloadTab();
    });
}

</script>
JQUERY;

// generate buttons

$xml .= '<button url="index.php">' . get_html_resource(RES_BACK_ID) . '</button>';

if (get_user_level() == USER_LEVEL_ADMIN)
{
    $xml .= '<button url="pexport.php?id=' . $id . '">' . get_html_resource(RES_EXPORT_ID) . '</button>'
          . '<buttonset>'
          . '<button action="projectModify()">' . get_html_resource(RES_MODIFY_ID) . '</button>';

    $xml .= (is_project_removable($id)
                ? '<button url="delete.php?id=' . $id . '" prompt="' . get_html_resource(RES_CONFIRM_DELETE_PROJECT_ID) . '">'
                : '<button disabled="false">')
          . get_html_resource(RES_DELETE_ID)
          . '</button>';

    $xml .= '<button action="projectDisable()">'
          . get_html_resource($project['is_suspended'] ? RES_ENABLE_ID : RES_DISABLE_ID)
          . '</button>'
          . '</buttonset>';
}

// generate project information

$xml .= '<group title="' . get_html_resource(RES_PROJECT_INFO_ID) . '">'
      . '<text label="' . get_html_resource(RES_PROJECT_NAME_ID) . '">' . ustr2html($project['project_name']) . '</text>'
      . '<text label="' . get_html_resource(RES_START_TIME_ID)   . '">' . get_date($project['start_time'])    . '</text>'
      . '<text label="' . get_html_resource(RES_DESCRIPTION_ID)  . '">' . ustr2html($project['description'])  . '</text>'
      . '<text label="' . get_html_resource(RES_STATUS_ID)       . '">' . get_html_resource($project['is_suspended'] ? RES_SUSPENDED_ID : RES_ACTIVE_ID) . '</text>'
      . '</group>';

echo(xml2html($xml));

?>
