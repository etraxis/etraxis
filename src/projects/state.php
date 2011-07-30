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
require_once('../dbo/states.php');
/**#@-*/

global $state_type_res;
global $state_responsible_res;

init_page(LOAD_TAB);

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    exit;
}

// check that requested state exists

$id    = ustr2int(try_request('id'));
$state = state_find($id);

if (!$state)
{
    debug_write_log(DEBUG_NOTICE, 'State cannot be found.');
    exit;
}

// local JS functions

$resTitle  = ustrprocess(get_js_resource(RES_STATE_X_ID), ustr2js($state['state_name']));
$resOK     = get_js_resource(RES_OK_ID);
$resCancel = get_js_resource(RES_CANCEL_ID);

$xml = <<<JQUERY
<script>

function stateModify ()
{
    jqModal("{$resTitle}", "smodify.php?id={$id}", "{$resOK}", "{$resCancel}", "$('#modifyform').submit()");
}

function setInitial ()
{
    $.post("initial.php?id={$id}", function() {
        reloadTab();
    });
}

</script>
JQUERY;

// generate buttons

$xml .= '<button url="tview.php?id=' . $state['template_id'] . '&amp;tab=2">' . get_html_resource(RES_BACK_ID) . '</button>'
      . '<buttonset>';

$xml .= ($state['is_locked']
            ? '<button action="stateModify()">'
            : '<button disabled="false">')
      . get_html_resource(RES_MODIFY_ID)
      . '</button>';

$xml .= ($state['is_locked'] && is_state_removable($id)
            ? '<button url="sdelete.php?id=' . $id . '" prompt="' . get_html_resource(RES_CONFIRM_DELETE_STATE_ID) . '">'
            : '<button disabled="false">')
      . get_html_resource(RES_DELETE_ID)
      . '</button>';

if ($state['state_type'] == STATE_TYPE_INTERMEDIATE)
{
    $xml .= ($state['is_locked']
                ? '<button action="setInitial()">'
                : '<button disabled="false">')
          . get_html_resource(RES_SET_INITIAL_ID)
          . '</button>';
}

$xml .= '</buttonset>';

// generate state information

$next_state = is_null($state['next_state_id'])
            ? get_html_resource(RES_NONE_ID)
            : ustr2html($state['next_state_name']);

$xml .= '<group title="' . get_html_resource(RES_STATE_INFO_ID) . '">'
      . '<text label="' . get_html_resource(RES_STATE_NAME_ID)            . '">' . ustr2html($state['state_name'])                                  . '</text>'
      . '<text label="' . get_html_resource(RES_STATE_ABBR_ID)            . '">' . ustr2html($state['state_abbr'])                                  . '</text>'
      . '<text label="' . get_html_resource(RES_STATE_TYPE_ID)            . '">' . get_html_resource($state_type_res[$state['state_type']])         . '</text>'
      . '<text label="' . get_html_resource(RES_RESPONSIBLE_ID)           . '">' . get_html_resource($state_responsible_res[$state['responsible']]) . '</text>'
      . '<text label="' . get_html_resource(RES_NEXT_STATE_BY_DEFAULT_ID) . '">' . $next_state                                                      . '</text>'
      . '</group>';

echo(xml2html($xml));

?>
