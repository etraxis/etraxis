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
require_once('../dbo/accounts.php');
require_once('../dbo/fields.php');
require_once('../dbo/values.php');
require_once('../dbo/records.php');
/**#@-*/

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
    debug_write_log(DEBUG_NOTICE, 'Record cannot be displayed.');
    header('Location: index.php');
    exit;
}

// mark the record as read

record_read($id);

// get the list of changes

$sort = $page = NULL;
$list = changes_list($id,
                     $record['creator_id'],
                     is_null($record['responsible_id']) ? 0 : $record['responsible_id'],
                     $sort, $page);

$xml = NULL;

if ($list->rows == 0)
{
    debug_write_log(DEBUG_NOTICE, 'List of changes is empty.');

    $xml .= '<text>' . get_html_resource(RES_NONE2_ID) . '</text>';
}
else
{
    // generate list header

    $columns = array
    (
        RES_TIMESTAMP_ID,
        RES_ORIGINATOR_ID,
        RES_FIELD_NAME_ID,
        RES_OLD_VALUE_ID,
        RES_NEW_VALUE_ID,
    );

    $rec_from = $rec_to = 0;

    $bookmarks = gen_xml_bookmarks($page, $list->rows, $rec_from, $rec_to, 'changes.php?id=' . $id . '&amp;');

    $xml .= '<list>'
          . '<hrow>';

    for ($i = 1; $i <= count($columns); $i++)
    {
        if ($i < 4)
        {
            $smode = ($sort == $i ? ($i + count($columns)) : $i);

            $xml .= "<hcell url=\"changes.php?id={$id}&amp;sort={$smode}\">"
                  . get_html_resource($columns[$i - 1])
                  . '</hcell>';
        }
        else
        {
            $xml .= "<hcell>"
                  . get_html_resource($columns[$i - 1])
                  . '</hcell>';
        }
    }

    $xml .= '</hrow>';

    // go through the list of changes

    $list->seek($rec_from - 1);

    for ($i = $rec_from; $i <= $rec_to; $i++)
    {
        $row = $list->fetch();

        $old_value = value_find($row['field_type'], $row['old_value_id']);
        $new_value = value_find($row['field_type'], $row['new_value_id']);

        if ($row['field_type'] == FIELD_TYPE_CHECKBOX)
        {
            $old_value = get_html_resource($old_value ? RES_YES_ID : RES_NO_ID);
            $new_value = get_html_resource($new_value ? RES_YES_ID : RES_NO_ID);
        }
        elseif ($row['field_type'] == FIELD_TYPE_LIST)
        {
            $old_value = (is_null($old_value) ? NULL : value_find_listvalue($row['field_id'], $old_value));
            $new_value = (is_null($new_value) ? NULL : value_find_listvalue($row['field_id'], $new_value));
        }
        elseif ($row['field_type'] == FIELD_TYPE_RECORD)
        {
            $old_value = (is_null($old_value) ? NULL : 'rec#' . $old_value);
            $new_value = (is_null($new_value) ? NULL : 'rec#' . $new_value);
        }
        elseif ($row['field_type'] == FIELD_TYPE_DATE)
        {
            $old_value = (is_null($old_value) ? NULL : get_date(ustr2date($old_value)));
            $new_value = (is_null($new_value) ? NULL : get_date(ustr2date($new_value)));
        }

        $xml .= '<row>'
              . '<cell>' . get_datetime($row['event_time']) . '</cell>'
              . '<cell>' . ustr2html(sprintf('%s (%s)', $row['fullname'], account_get_username($row['username']))) . '</cell>'
              . '<cell>' . (is_null($row['field_name']) ? get_html_resource(RES_SUBJECT_ID) : ustr2html($row['field_name'])) . '</cell>'
              . '<cell>' . (is_null($old_value) ? get_html_resource(RES_NONE_ID) : update_references($old_value)) . '</cell>'
              . '<cell>' . (is_null($new_value) ? get_html_resource(RES_NONE_ID) : update_references($new_value)) . '</cell>'
              . '</row>';
    }

    $xml .= '</list>'
          . $bookmarks;
}

echo(xml2html($xml));

?>
