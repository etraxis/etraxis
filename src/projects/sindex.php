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
require_once('../dbo/templates.php');
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

// check that requested template exists

$id       = ustr2int(try_request('id'));
$template = template_find($id);

if (!$template)
{
    debug_write_log(DEBUG_NOTICE, 'Template cannot be found.');
    exit;
}

// local JS functions

$resTitle  = get_js_resource(RES_NEW_STATE_ID);
$resOK     = get_js_resource(RES_OK_ID);
$resCancel = get_js_resource(RES_CANCEL_ID);

$xml = <<<JQUERY
<script>

function stateCreate (final)
{
    jqModal("{$resTitle}", "screate.php?id={$id}&amp;final=" + final, "{$resOK}", "{$resCancel}", "$('#createform').submit()");
}

</script>
JQUERY;

// get list of states

$sort = $page = NULL;
$list = states_list($id, $sort, $page);

$from = $to = 0;

// generate buttons

$xml .= '<buttonset>';

$xml .= ($template['is_locked']
            ? '<button action="stateCreate(0)">'
            : '<button disabled="true">')
      . get_html_resource(RES_CREATE_INTERMEDIATE_ID)
      . '</button>';

$xml .= ($template['is_locked']
            ? '<button action="stateCreate(1)">'
            : '<button disabled="true">')
      . get_html_resource(RES_CREATE_FINAL_ID)
      . '</button>';

$xml .= '</buttonset>';

// generate list of states

if ($list->rows != 0)
{
    $columns = array
    (
        RES_STATE_NAME_ID,
        RES_STATE_ABBR_ID,
        RES_STATE_TYPE_ID,
        RES_RESPONSIBLE_ID,
        RES_NEXT_STATE_BY_DEFAULT_ID,
    );

    $bookmarks = gen_xml_bookmarks($page, $list->rows, $from, $to, 'sindex.php?id=' . $id . '&amp;');

    $xml .= '<list>'
          . '<hrow>';

    for ($i = 1; $i <= count($columns); $i++)
    {
        $smode = ($sort == $i ? ($i + count($columns)) : $i);

        $xml .= "<hcell url=\"sindex.php?id={$id}&amp;sort={$smode}\">"
              . get_html_resource($columns[$i - 1])
              . '</hcell>';
    }

    $xml .= '</hrow>';

    $list->seek($from - 1);

    for ($i = $from; $i <= $to; $i++)
    {
        $row = $list->fetch();

        $xml .= "<row url=\"sview.php?id={$row['state_id']}\">"
              . '<cell>' . ustr2html($row['state_name']) . '</cell>'
              . '<cell>' . ustr2html($row['state_abbr']) . '</cell>'
              . '<cell>' . get_html_resource($state_type_res[$row['state_type']]) . '</cell>'
              . '<cell>' . get_html_resource($state_responsible_res[$row['responsible']]) . '</cell>'
              . '<cell>' . (is_null($row['next_state']) ? sprintf('<i>%s</i>', get_html_resource(RES_NONE_ID)) : ustr2html($row['next_state'])) . '</cell>'
              . '</row>';
    }

    $xml .= '</list>'
          . $bookmarks;
}

echo(xml2html($xml));

?>
