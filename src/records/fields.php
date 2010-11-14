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
require_once('../dbo/views.php');
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
    if (get_user_level() == USER_LEVEL_GUEST)
    {
        save_cookie(COOKIE_URI, $_SERVER['REQUEST_URI']);
    }

    debug_write_log(DEBUG_NOTICE, 'Record cannot be displayed.');
    header('Location: index.php');
    exit;
}

// mark the record as read

record_read($id);

// page's title

$title = ustrprocess(get_html_resource(RES_RECORD_X_ID), record_id($id, $record['template_prefix']));

// generate breadcrumbs and tabs

$xml = '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_RECORDS_ID) . '</breadcrumb>'
     . '<breadcrumb url="fields.php?id=' . $id . '">' . $title . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<tabs>'
     . gen_record_tabs($record, RECORD_TAB_FIELDS)
     . '<content>';

// generate general information

$xml .= '<group title="' . get_html_resource(RES_GENERAL_INFO_ID) . '">'
      . '<text label="' . get_html_resource(RES_PROJECT_ID)  . '">' . ustr2html($record['project_name'])  . '</text>'
      . '<text label="' . get_html_resource(RES_TEMPLATE_ID) . '">' . ustr2html($record['template_name']) . '</text>'
      . '<text label="' . get_html_resource(RES_STATE_ID)    . '">' . ustr2html($record['state_name'])    . '</text>';

if (is_record_postponed($record))
{
    $xml .= '<text label="' . get_html_resource(RES_POSTPONED_ID) . '">' . get_date($record['postpone_time']) . '</text>';
}

$xml .= '<text label="' . get_html_resource(RES_AGE_ID)         . '">' . get_record_last_event($record) . '/' . get_record_age($record) . '</text>'
      . '<text label="' . get_html_resource(RES_AUTHOR_ID)      . '">' . ustr2html(sprintf('%s (%s)', $record['author_fullname'], account_get_username($record['author_username']))) . '</text>'
      . '<text label="' . get_html_resource(RES_RESPONSIBLE_ID) . '">' . (is_null($record['username']) ? get_html_resource(RES_NONE_ID) : ustr2html(sprintf('%s (%s)', $record['fullname'], account_get_username($record['username'])))) . '</text>'
      . '<text label="' . get_html_resource(RES_SUBJECT_ID)     . '">' . update_references($record['subject'], BBCODE_MINIMUM) . '</text>'
      . '</group>';

// go through the list of all states and their fields

$states = dal_query('records/elist.sql', $id);

while (($state = $states->fetch()))
{
    $fields = dal_query('records/flist.sql',
                        $id,
                        $state['state_id'],
                        $record['creator_id'],
                        is_null($record['responsible_id']) ? 0 : $record['responsible_id'],
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
