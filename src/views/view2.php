<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2010  Artem Rodygin
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
require_once('../dbo/views.php');
/**#@-*/

init_page();

// check that requested view exists

$id   = ustr2int(try_request('id'));
$view = view_find($id);

if (!$view)
{
    debug_write_log(DEBUG_NOTICE, 'View cannot be found.');
    header('Location: index.php');
    exit;
}

// local JS functions

$resTitle  = ustrprocess(get_js_resource(RES_VIEW_X_ID), ustr2js($view['view_name']));
$resOK     = get_js_resource(RES_OK_ID);
$resCancel = get_js_resource(RES_CANCEL_ID);

$xml = <<<JQUERY
<script>

function viewModify ()
{
    jqModal("{$resTitle}", "modify.php?id={$id}", "{$resOK}", "{$resCancel}", "$('#modifyform').submit()");
}

</script>
JQUERY;

// generate buttons and generate view information

$xml .= '<button url="index.php">' . get_html_resource(RES_BACK_ID) . '</button>'
      . '<buttonset>'
      . '<button action="viewModify()">' . get_html_resource(RES_MODIFY_ID) . '</button>'
      . '<button url="delete.php?id=' . $id . '" prompt="' . get_html_resource(RES_CONFIRM_DELETE_VIEWS_ID) . '">' . get_html_resource(RES_DELETE_ID) . '</button>'
      . '</buttonset>'
      . '<group title="' . get_html_resource(RES_GENERAL_INFO_ID) . '">'
      . '<text label="' . get_html_resource(RES_VIEW_NAME_ID) . '">' . ustr2html($view['view_name']) . '</text>'
      . '</group>';

echo(xml2html($xml));

?>
