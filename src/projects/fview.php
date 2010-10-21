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
require_once('../dbo/fields.php');
require_once('../dbo/values.php');
/**#@-*/

global $field_type_res;

init_page();

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    header('Location: index.php');
    exit;
}

// check that requested field exists

$id    = ustr2int(try_request('id'));
$field = field_find($id);

if (!$field)
{
    debug_write_log(DEBUG_NOTICE, 'Field cannot be found.');
    header('Location: index.php');
    exit;
}

// page's title

$title = ustrprocess(get_html_resource(RES_FIELD_X_ID), ustr2html($field['field_name']));

// generate breadcrumbs and tabs

$xml = gen_context_menu('sindex.php?id=', 'findex.php?id=', 'fview.php?id=', $field['project_id'], $field['template_id'], $field['state_id'])
     . '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_PROJECTS_ID) . '</breadcrumb>'
     . '<breadcrumb url="tindex.php?id=' . $field['project_id']  . '">' . ustrprocess(get_html_resource(RES_PROJECT_X_ID),  ustr2html($field['project_name']))  . '</breadcrumb>'
     . '<breadcrumb url="sindex.php?id=' . $field['template_id'] . '">' . ustrprocess(get_html_resource(RES_TEMPLATE_X_ID), ustr2html($field['template_name'])) . '</breadcrumb>'
     . '<breadcrumb url="findex.php?id=' . $field['state_id']    . '">' . ustrprocess(get_html_resource(RES_STATE_X_ID),    ustr2html($field['state_name']))    . '</breadcrumb>'
     . '<breadcrumb url="fview.php?id=' . $id . '">' . $title . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<tabs>'
     . '<tab url="fview.php?id='  . $id . '" active="true"><i>' . ustr2html($field['field_name'])       . '</i></tab>'
     . '<tab url="fperms.php?id=' . $id . '">'                  . get_html_resource(RES_PERMISSIONS_ID) . '</tab>'
     . '<content>';

// generate buttons

$xml .= '<button url="findex.php?id=' . $field['state_id'] . '">' . get_html_resource(RES_BACK_ID) . '</button>'
      . HTML_SPLITTER;

$xml .= ($field['is_locked']
            ? '<button url="fmodify.php?id=' . $id . '">'
            : '<button disabled="false">')
      . get_html_resource(RES_MODIFY_ID)
      . '</button>';

$xml .= ($field['is_locked'] && is_field_removable($id)
            ? '<button url="fdelete.php?id=' . $id . '" prompt="' . get_html_resource(RES_CONFIRM_DELETE_FIELD_ID) . '">'
            : '<button disabled="false">')
      . get_html_resource(RES_DELETE_ID)
      . '</button>';

// generate state information

$xml .= '<group title="' . get_html_resource(RES_FIELD_INFO_ID) . '">'
      . '<text label="' . get_html_resource(RES_FIELD_NAME_ID) . '">' . ustr2html($field['field_name'])                          . '</text>'
      . '<text label="' . get_html_resource(RES_ORDER_ID)      . '">' . ustr2html($field['field_order'])                         . '</text>'
      . '<text label="' . get_html_resource(RES_FIELD_TYPE_ID) . '">' . get_html_resource($field_type_res[$field['field_type']]) . '</text>';

switch ($field['field_type'])
{
    case FIELD_TYPE_NUMBER:

        $xml .= '<text label="' . get_html_resource(RES_MIN_VALUE_ID)     . '">' . ustr2html($field['param1']) . '</text>'
              . '<text label="' . get_html_resource(RES_MAX_VALUE_ID)     . '">' . ustr2html($field['param2']) . '</text>'
              . '<text label="' . get_html_resource(RES_DEFAULT_VALUE_ID) . '">' . (is_null($field['value_id']) ? get_html_resource(RES_NONE_ID) : $field['value_id']) . '</text>';

        break;

    case FIELD_TYPE_STRING:
    case FIELD_TYPE_MULTILINED:

        $xml .= '<text label="' . get_html_resource(RES_MAX_LENGTH_ID)    . '">' . ustr2html($field['param1']) . '</text>'
              . '<text label="' . get_html_resource(RES_REGEX_CHECK_ID)   . '">' . (is_null($field['regex_check'])   ? get_html_resource(RES_NONE_ID) : ustr2html(ustr_replace("\n", '%br;', $field['regex_check'])))   . '</text>'
              . '<text label="' . get_html_resource(RES_REGEX_SEARCH_ID)  . '">' . (is_null($field['regex_search'])  ? get_html_resource(RES_NONE_ID) : ustr2html(ustr_replace("\n", '%br;', $field['regex_search'])))  . '</text>'
              . '<text label="' . get_html_resource(RES_REGEX_REPLACE_ID) . '">' . (is_null($field['regex_replace']) ? get_html_resource(RES_NONE_ID) : ustr2html(ustr_replace("\n", '%br;', $field['regex_replace']))) . '</text>'
              . '<text label="' . get_html_resource(RES_DEFAULT_VALUE_ID) . '">' . (is_null($field['value_id'])      ? get_html_resource(RES_NONE_ID) : ustr2html(ustr_replace("\n", '%br;', value_find($field['field_type'], $field['value_id'])))) . '</text>';

        break;

    case FIELD_TYPE_CHECKBOX:

        $xml .= '<text label="' . get_html_resource(RES_DEFAULT_VALUE_ID) . '">' . get_html_resource($field['value_id'] == 0 ? RES_OFF_ID : RES_ON_ID) . '</text>';

        break;

    case FIELD_TYPE_LIST:

        $xml .= '<text label="' . get_html_resource(RES_LIST_ITEMS_ID)    . '">' . ustr2html(ustr_replace("\n", '%br;', field_pickup_list_items($id))) . '</text>'
              . '<text label="' . get_html_resource(RES_DEFAULT_VALUE_ID) . '">' . (is_null($field['value_id']) ? get_html_resource(RES_NONE_ID) : ustr2html($field['value_id'])) . '</text>';

        break;

    case FIELD_TYPE_DATE:

        $xml .= '<text label="' . get_html_resource(RES_MIN_VALUE_ID)     . '">' . ustr2html($field['param1']) . '</text>'
              . '<text label="' . get_html_resource(RES_MAX_VALUE_ID)     . '">' . ustr2html($field['param2']) . '</text>'
              . '<text label="' . get_html_resource(RES_DEFAULT_VALUE_ID) . '">' . (is_null($field['value_id']) ? get_html_resource(RES_NONE_ID) : $field['value_id']) . '</text>';

        break;

    case FIELD_TYPE_DURATION:

        $xml .= '<text label="' . get_html_resource(RES_MIN_VALUE_ID)     . '">' . time2ustr($field['param1']) . '</text>'
              . '<text label="' . get_html_resource(RES_MAX_VALUE_ID)     . '">' . time2ustr($field['param2']) . '</text>'
              . '<text label="' . get_html_resource(RES_DEFAULT_VALUE_ID) . '">' . (is_null($field['value_id']) ? get_html_resource(RES_NONE_ID) : time2ustr($field['value_id'])) . '</text>';

        break;

    default:

        debug_write_log(DEBUG_WARNING, 'Unknown field type = ' . $field['field_type']);
}

if ($field['field_type'] != FIELD_TYPE_CHECKBOX)
{
    $xml .= '<text label="' . get_html_resource(RES_REQUIRED_ID) . '">' . get_html_resource($field['is_required'] ? RES_YES_ID : RES_NO_ID) . '</text>';
}

$xml .= '<text label="' . get_html_resource(RES_GUEST_ACCESS_ID)  . '">' . get_html_resource($field['guest_access']  ? RES_YES_ID : RES_NO_ID) . '</text>'
      . '<text label="' . get_html_resource(RES_ADD_SEPARATOR_ID) . '">' . get_html_resource($field['add_separator'] ? RES_YES_ID : RES_NO_ID) . '</text>'
      . '</group>'
      . '</content>'
      . '</tabs>';

echo(xml2html($xml, $title));

?>
