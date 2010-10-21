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
require_once('../dbo/states.php');
require_once('../dbo/fields.php');
/**#@-*/

global $field_type_res;

init_page();

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    header('Location: ../index.php');
    exit;
}

// check that requested state exists

$id    = ustr2int(try_request('id'));
$state = state_find($id);

if (!$state)
{
    debug_write_log(DEBUG_NOTICE, 'State cannot be found.');
    header('Location: index.php');
    exit;
}

// get list of fields

$sort = $page = NULL;
$list = fields_list($id, $sort, $page);

$from = $to = 0;

// page's title

$title = ustrprocess(get_html_resource(RES_STATE_X_ID), ustr2html($state['state_name']));

// generate breadcrumbs and tabs

$xml = gen_context_menu('sindex.php?id=', 'findex.php?id=', 'fview.php?id=', $state['project_id'], $state['template_id'], $id)
     . '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_PROJECTS_ID) . '</breadcrumb>'
     . '<breadcrumb url="tindex.php?id=' . $state['project_id']  . '">' . ustrprocess(get_html_resource(RES_PROJECT_X_ID),  ustr2html($state['project_name']))  . '</breadcrumb>'
     . '<breadcrumb url="sindex.php?id=' . $state['template_id'] . '">' . ustrprocess(get_html_resource(RES_TEMPLATE_X_ID), ustr2html($state['template_name'])) . '</breadcrumb>'
     . '<breadcrumb url="findex.php?id=' . $id . '">' . $title . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<tabs>'
     . '<tab url="sview.php?id='  . $id . '"><i>' . ustr2html($state['state_name']) . '</i></tab>'
     . '<tab url="findex.php?id=' . $id . '" active="true">' . get_html_resource(RES_FIELDS_ID) . '</tab>';

if ($state['state_type'] != STATE_TYPE_FINAL)
{
    $xml .= '<tab url="strans.php?id=' . $id . '">' . get_html_resource(RES_TRANSITIONS_ID) . '</tab>';
}

$xml .= '<content>';

// generate buttons

$xml .= ($state['is_locked']
            ? '<button url="fcreate.php?id=' . $id . '">'
            : '<button disabled="true">')
      . get_html_resource(RES_CREATE_ID)
      . '</button>';

// generate list of fields

if ($list->rows != 0)
{
    $columns = array
    (
        RES_ORDER_ID,
        RES_FIELD_NAME_ID,
        RES_FIELD_TYPE_ID,
        RES_REQUIRED_ID,
        RES_GUEST_ACCESS_ID,
    );

    $bookmarks = gen_xml_bookmarks($page, $list->rows, $from, $to, 'findex.php?id=' . $id . '&amp;');

    $xml .= '<list>'
          . '<hrow>';

    for ($i = 1; $i <= count($columns); $i++)
    {
        $smode = ($sort == $i ? ($i + count($columns)) : $i);

        $xml .= "<hcell url=\"findex.php?id={$id}&amp;sort={$smode}&amp;page={$page}\">"
              . get_html_resource($columns[$i - 1])
              . '</hcell>';
    }

    $xml .= '</hrow>';

    $list->seek($from - 1);

    for ($i = $from; $i <= $to; $i++)
    {
        $row = $list->fetch();

        $xml .= "<row url=\"fview.php?id={$row['field_id']}\">"
              . '<cell>' . ustr2html($row['field_order']) . '</cell>'
              . '<cell>' . ustr2html($row['field_name'])  . '</cell>'
              . '<cell>' . get_html_resource($field_type_res[$row['field_type']]) . '</cell>'
              . '<cell>' . get_html_resource($row['is_required']  ? RES_YES_ID : RES_NO_ID) . '</cell>'
              . '<cell>' . get_html_resource($row['guest_access'] ? RES_YES_ID : RES_NO_ID) . '</cell>'
              . '</row>';
    }

    $xml .= '</list>'
          . $bookmarks;
}

$xml .= '</content>'
      . '</tabs>';

echo(xml2html($xml, $title));

?>
