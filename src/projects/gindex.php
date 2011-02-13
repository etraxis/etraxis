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

init_page(LOAD_TAB);

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    exit;
}

// check that requested project exists

$id      = ustr2int(try_request('id'));
$project = project_find($id);

if (!$project)
{
    debug_write_log(DEBUG_NOTICE, 'Project cannot be found.');
    exit;
}

// local JS functions

$resTitle  = get_js_resource(RES_NEW_GROUP_ID);
$resOK     = get_js_resource(RES_OK_ID);
$resCancel = get_js_resource(RES_CANCEL_ID);

$xml = <<<JQUERY
<script>

function groupCreate ()
{
    jqModal("{$resTitle}", "gcreate.php?id={$id}", "{$resOK}", "{$resCancel}", "$('#createform').submit()");
}

</script>
JQUERY;

// get list of groups

$sort = $page = NULL;
$list = groups_list($id, $sort, $page);

$from = $to = 0;

// generate buttons

$xml .= '<button action="groupCreate()">' . get_html_resource(RES_CREATE_ID) . '</button>';

// generate list of groups

if ($list->rows != 0)
{
    $bookmarks = gen_xml_bookmarks($page, $list->rows, $from, $to, 'gindex.php?id=' . $id . '&amp;');

    $sort1 = ($sort == 1 ? 3 : 1);
    $sort2 = ($sort == 2 ? 4 : 2);

    $xml .= '<list>'
          . '<hrow>'
          . "<hcell url=\"gindex.php?id={$id}&amp;sort={$sort1}\">" . get_html_resource(RES_GROUP_NAME_ID)  . '</hcell>'
          . "<hcell/>"
          . "<hcell url=\"gindex.php?id={$id}&amp;sort={$sort2}\">" . get_html_resource(RES_DESCRIPTION_ID) . '</hcell>'
          . '</hrow>';

    $list->seek($from - 1);

    for ($i = $from; $i <= $to; $i++)
    {
        $row = $list->fetch();

        $xml .= "<row url=\"gview.php?pid={$id}&amp;id={$row['group_id']}\">"
              . '<cell>' . ustr2html($row['group_name']) . '</cell>'
              . '<cell>' . get_html_resource($row['is_global'] ? RES_GLOBAL_ID : RES_LOCAL_ID) . '</cell>'
              . '<cell>' . ustr2html($row['description']) . '</cell>'
              . '</row>';
    }

    $xml .= '</list>'
          . $bookmarks;
}

echo(xml2html($xml));

?>
