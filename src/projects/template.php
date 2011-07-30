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
require_once('../dbo/projects.php');
require_once('../dbo/templates.php');
/**#@-*/

init_page(LOAD_TAB);

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    exit;
}

// check that requested template exists

$id       = ustr2int(try_request('id'));
$template = template_find($id);

if (!$template)
{
    debug_write_log(DEBUG_NOTICE, 'Template cannot be found.');
    exit;
}

// local JS functions

$resTitle  = ustrprocess(get_js_resource(RES_TEMPLATE_X_ID), ustr2js($template['template_name']));
$resClone  = get_js_resource(RES_CLONE_ID);
$resOK     = get_js_resource(RES_OK_ID);
$resCancel = get_js_resource(RES_CANCEL_ID);

$xml = <<<JQUERY
<script>

function templateModify ()
{
    jqModal("{$resTitle}", "tmodify.php?id={$id}", "{$resOK}", "{$resCancel}", "$('#modifyform').submit()");
}

function templateClone ()
{
    jqModal("{$resClone}", "tclone.php?id={$id}", "{$resOK}", "{$resCancel}", "$('#cloneform').submit()");
}

function templateLock ()
{
    $.post("tlock.php?id={$id}", function() {
        reloadTab();
    });
}

</script>
JQUERY;

// generate buttons
$rs = dal_query('templates/count.sql');

$xml .= '<button url="view.php?id=' . $template['project_id'] . '&amp;tab=3">' . get_html_resource(RES_BACK_ID) . '</button>'
      . '<button url="texport.php?id=' . $id . '">' . get_html_resource(RES_EXPORT_ID) . '</button>'
      . '<buttonset>'
      . '<button action="templateModify()">' . get_html_resource(RES_MODIFY_ID) . '</button>'
      . (MAX_TEMPLATES_NUMBER == 0 || $rs->fetch(0) < MAX_TEMPLATES_NUMBER
            ? '<button action="templateClone()">'
            : '<button disabled="true">')
      . get_html_resource(RES_CLONE_ID)
      . '</button>'
      . (is_template_removable($id) && $template['is_locked']
            ? '<button url="tdelete.php?id=' . $id . '" prompt="' . get_html_resource(RES_CONFIRM_DELETE_TEMPLATE_ID) . '">'
            : '<button disabled="false">')
      . get_html_resource(RES_DELETE_ID)
      . '</button>'
      . '<button action="templateLock()">'
      . get_html_resource($template['is_locked'] ? RES_UNLOCK_ID : RES_LOCK_ID)
      . '</button>'
      . '</buttonset>';

// generate template information

$xml .= '<group title="' . get_html_resource(RES_TEMPLATE_INFO_ID) . '">'
      . '<text label="' . get_html_resource(RES_TEMPLATE_NAME_ID)   . '">' . ustr2html($template['template_name'])   . '</text>'
      . '<text label="' . get_html_resource(RES_TEMPLATE_PREFIX_ID) . '">' . ustr2html($template['template_prefix']) . '</text>'
      . '<text label="' . get_html_resource(RES_CRITICAL_AGE_ID)    . '">' . (is_null($template['critical_age']) ? get_html_resource(RES_NONE_ID) : $template['critical_age']) . '</text>'
      . '<text label="' . get_html_resource(RES_FROZEN_TIME_ID)     . '">' . (is_null($template['frozen_time'])  ? get_html_resource(RES_NONE_ID) : $template['frozen_time'])  . '</text>'
      . '<text label="' . get_html_resource(RES_DESCRIPTION_ID)     . '">' . ustr2html($template['description']) . '</text>'
      . '<text label="' . get_html_resource(RES_GUEST_ACCESS_ID)    . '">' . get_html_resource($template['guest_access'] ? RES_YES_ID    : RES_NO_ID)     . '</text>'
      . '<text label="' . get_html_resource(RES_STATUS_ID)          . '">' . get_html_resource($template['is_locked']    ? RES_LOCKED_ID : RES_ACTIVE_ID) . '</text>'
      . '</group>';

echo(xml2html($xml));

?>
