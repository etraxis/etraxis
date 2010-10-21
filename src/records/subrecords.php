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
require_once('../dbo/records.php');
/**#@-*/

//global $column_type_align;
//global $column_type_res;

init_page(GUEST_IS_ALLOWED);

// check that requested record exists

$id     = ustr2int(try_request('id'));
$record = record_find($id);

if (!$record)
{
    debug_write_log(DEBUG_NOTICE, 'Record cannot be found.');
    header('Location: index.php');
    exit;
}

// get current user's permissions and verify them

$permissions = record_get_permissions($record['template_id'], $record['creator_id'], $record['responsible_id']);

if (!can_record_be_displayed($permissions))
{
    if (get_user_level() == USER_LEVEL_GUEST)
    {
        save_cookie(COOKIE_URI, $_SERVER['REQUEST_URI']);
    }

    debug_write_log(DEBUG_NOTICE, 'Record cannot be displayed.');
    header('Location: index.php');
    exit;
}

// records list is submitted

if (try_request('submitted') == 'subrecords')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    foreach ($_REQUEST as $request)
    {
        if (substr($request, 0, 3) == 'rec')
        {
            subrecord_remove($id, intval(substr($request, 3)));
        }
    }
}

// page's title

$title = ustrprocess(get_html_resource(RES_RECORD_X_ID), record_id($id, $record['template_prefix']));

// generate breadcrumbs and tabs

$xml = '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_RECORDS_ID) . '</breadcrumb>'
     . '<breadcrumb url="subrecords.php?id=' . $id . '">' . $title . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<tabs>'
     . gen_record_tabs($record, RECORD_TAB_SUBRECORDS)
     . '<content>';

// generate buttons

$xml .= (can_subrecord_be_added($record, $permissions)
            ? '<button url="create.php?parent=' . $id . '">'
            : '<button disabled="true">')
      . get_html_resource(RES_CREATE_SUBRECORD_ID)
      . '</button>';

$xml .= '<script src="addsubrec.js"/>'
      . (can_subrecord_be_added($record, $permissions)
            ? '<button action="javascript:loadAddSubrecForm(' . $id . ')">'
            : '<button disabled="true">')
      . get_html_resource(RES_ATTACH_SUBRECORD_ID)
      . '</button>';

$xml .= (can_subrecord_be_removed($record, $permissions)
            ? '<button action="document.subrecords.submit()">'
            : '<button disabled="true">')
      . get_html_resource(RES_REMOVE_SUBRECORD_ID)
      . '</button>';

$xml .= '<div id="addsubrecdiv"/>';

// generate list of records

$list = subrecords_list($id);

if ($list->rows != 0)
{
    $columns = array
    (
        RES_ID_ID,
        RES_STATE_ID,
        RES_SUBJECT_ID,
        RES_RESPONSIBLE_ID,
    );

    $xml .= '<form name="subrecords" action="subrecords.php?id=' . $id . '">'
          . '<list>'
          . '<hrow>'
          . '<hcell checkboxes="true"/>';

    foreach ($columns as $column)
    {
        $xml .= "<hcell>" . get_html_resource($column) . '</hcell>';
    }

    $xml .= '</hrow>';

    while (($row = $list->fetch()))
    {
        if (is_record_closed($row))
        {
            $color = 'grey';
        }
        elseif ($row['is_dependency'])
        {
            $color = 'red';
        }
        else
        {
            $color = NULL;
        }

        $xml .= "<row name=\"rec{$row['record_id']}\" url=\"view.php?id={$row['record_id']}\" color=\"{$color}\">"
              . '<cell align="left" nowrap="true">' . record_id($row['record_id'], $row['template_prefix']) . '</cell>'
              . '<cell align="left">' . ustr2html($row['state_abbr']) . '</cell>'
              . '<cell align="left">' . update_references($row['subject'], BBCODE_SEARCH_ONLY) . '</cell>'
              . '<cell align="left">' . (is_null($row['fullname']) ? get_html_resource(RES_NONE_ID) : ustr2html($row['fullname'])) . '</cell>'
              . '</row>';
    }

    $xml .= '</list>'
          . '</form>';
}

$xml .= '</content>'
      . '</tabs>';

echo(xml2html($xml, $title));

?>
