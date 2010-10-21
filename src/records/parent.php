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

// get parent record

$rs = dal_query('depends/fnd.sql', $id);

if ($rs->rows == 0)
{
    debug_write_log(DEBUG_NOTICE, 'Record has not parent.');
    header('Location: view.php?id=' . $id);
    exit;
}

$parent = record_find($rs->fetch('parent_id'));

if (!$parent)
{
    debug_write_log(DEBUG_NOTICE, 'Parent record cannot be found.');
    header('Location: view.php?id=' . $id);
    exit;
}

// get current user's permissions and verify them

$permissions = record_get_permissions($parent['template_id'], $parent['creator_id'], $parent['responsible_id']);

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

// mark the record as read

record_read($parent['record_id']);

// page's title

$title = ustrprocess(get_html_resource(RES_RECORD_X_ID), record_id($id, $record['template_prefix']));

// generate breadcrumbs and tabs

$xml = '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_RECORDS_ID) . '</breadcrumb>'
     . '<breadcrumb url="parent.php?id=' . $id . '">' . $title . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<tabs>'
     . gen_record_tabs($record, RECORD_TAB_PARENT)
     . '<content>';

// generate general information

$xml .= '<group title="' . get_html_resource(RES_GENERAL_INFO_ID) . '">'
      . '<text label="' . get_html_resource(RES_ID_ID) . '">'
      . '<record id="' . $parent['record_id'] . '">'
      . record_id($parent['record_id'], $parent['template_prefix'])
      . '</record>'
      . '</text>';

$rs = dal_query('depends/fnd.sql', $parent['record_id']);

if ($rs->rows != 0)
{
    $grand_parent = $rs->fetch();

    $xml .= '<text label="' . get_html_resource(RES_PARENT_ID_ID) . '">'
          . '<record id="' . $grand_parent['parent_id'] . '">'
          . record_id($grand_parent['parent_id'], $grand_parent['template_prefix'])
          . '</record>'
          . '</text>';
}

$xml .= '<text label="' . get_html_resource(RES_PROJECT_ID)     . '">' . ustr2html($parent['project_name'])  . '</text>'
      . '<text label="' . get_html_resource(RES_TEMPLATE_ID)    . '">' . ustr2html($parent['template_name']) . '</text>'
      . '<text label="' . get_html_resource(RES_STATE_ID)       . '">' . ustr2html($parent['state_name'])    . '</text>';

if (is_record_postponed($parent))
{
    $xml .= '<text label="' . get_html_resource(RES_POSTPONED_ID) . '">' . get_date($parent['postpone_time']) . '</text>';
}

$xml .= '<text label="' . get_html_resource(RES_AGE_ID)         . '">' . get_record_last_event($parent) . '/' . get_record_age($parent) . '</text>'
      . '<text label="' . get_html_resource(RES_AUTHOR_ID)      . '">' . ustr2html(sprintf('%s (%s)', $parent['author_fullname'], account_get_username($parent['author_username']))) . '</text>'
      . '<text label="' . get_html_resource(RES_RESPONSIBLE_ID) . '">' . (is_null($parent['username']) ? get_html_resource(RES_NONE_ID) : ustr2html(sprintf('%s (%s)', $parent['fullname'], account_get_username($parent['username'])))) . '</text>'
      . '<text label="' . get_html_resource(RES_SUBJECT_ID)     . '">' . update_references($parent['subject'], BBCODE_MINIMUM) . '</text>'
      . '</group>';

// go through the list of all states and their fields

$states = dal_query('records/elist.sql', $parent['record_id']);

while (($state = $states->fetch()))
{
    $fields = dal_query('records/flist.sql',
                        $parent['record_id'],
                        $state['state_id'],
                        $parent['creator_id'],
                        is_null($parent['responsible_id']) ? 0 : $parent['responsible_id'],
                        $_SESSION[VAR_USERID],
                        FIELD_ALLOW_TO_READ);

    if ($fields->rows != 0)
    {
        $xml .= '<group title="' . ustr2html($state['state_name']) . '">';

        while (($field = $fields->fetch()))
        {
            $value = value_find($field['field_type'], $field['value_id']);

            if ($field['field_type'] == FIELD_TYPE_CHECKBOX)
            {
                $value = get_html_resource($value ? RES_YES_ID : RES_NO_ID);
            }
            elseif ($field['field_type'] == FIELD_TYPE_LIST)
            {
                $value = (is_null($value) ? NULL : value_find_listvalue($field['field_id'], $value));
            }
            elseif ($field['field_type'] == FIELD_TYPE_RECORD)
            {
                $value = (is_null($value) ? NULL : 'rec#' . $value);
            }

            $xml .= '<text label="' . ustr2html($field['field_name']) . '">'
                  . (is_null($value) ? get_html_resource(RES_NONE_ID) : update_references($value, BBCODE_ALL, $field['regex_search'], $field['regex_replace']))
                  . '</text>';

            if ($field['add_separator'])
            {
                $xml .= '<hr/>';
            }
        }

        $xml .= '</group>';
    }
}

$xml .= '</content>'
      . '</tabs>';

echo(xml2html($xml, $title));

?>
