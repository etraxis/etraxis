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
require_once('../dbo/projects.php');
require_once('../dbo/fields.php');
require_once('../dbo/values.php');
/**#@-*/

global $field_type_res;

init_page(LOAD_TAB);

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    exit;
}

// check that requested field exists

$id    = ustr2int(try_request('id'));
$field = field_find($id);

if (!$field)
{
    debug_write_log(DEBUG_NOTICE, 'Field cannot be found.');
    exit;
}

// local JS functions

$resTitle  = ustrprocess(get_js_resource(RES_FIELD_X_ID), ustr2html($field['field_name']));
$resOK     = get_js_resource(RES_OK_ID);
$resCancel = get_js_resource(RES_CANCEL_ID);

$xml = <<<JQUERY
<script>

function fieldModify ()
{
    jqModal("{$resTitle}", "fmodify.php?id={$id}", "{$resOK}", "{$resCancel}", "$('#modifyform').submit()");
}

</script>
JQUERY;

// generate buttons

$xml .= '<button url="sview.php?id=' . $field['state_id'] . '&amp;tab=2">' . get_html_resource(RES_BACK_ID) . '</button>'
      . '<buttonset>';

$xml .= ($field['is_locked']
            ? '<button action="fieldModify()">'
            : '<button disabled="false">')
      . get_html_resource(RES_MODIFY_ID)
      . '</button>';

$xml .= ($field['is_locked']
            ? '<button url="fdelete.php?id=' . $id . '" prompt="' . get_html_resource(RES_CONFIRM_DELETE_FIELD_ID) . '">'
            : '<button disabled="false">')
      . get_html_resource(RES_DELETE_ID)
      . '</button>'
      . '</buttonset>';

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

    case FIELD_TYPE_FLOAT:

        $xml .= '<text label="' . get_html_resource(RES_MIN_VALUE_ID)     . '">' . value_find(FIELD_TYPE_FLOAT, $field['param1']) . '</text>'
              . '<text label="' . get_html_resource(RES_MAX_VALUE_ID)     . '">' . value_find(FIELD_TYPE_FLOAT, $field['param2']) . '</text>'
              . '<text label="' . get_html_resource(RES_DEFAULT_VALUE_ID) . '">' . (is_null($field['value_id']) ? get_html_resource(RES_NONE_ID) : value_find(FIELD_TYPE_FLOAT, $field['value_id'])) . '</text>';

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

$xml .= '<text label="' . get_html_resource(RES_DESCRIPTION_ID) . '">'
      . (is_null($field['description']) ? get_html_resource(RES_NONE_ID)
                                        : ustr2html(ustr_replace("\n", '%br;', $field['description'])))
      . '</text>';

if ($field['field_type'] != FIELD_TYPE_CHECKBOX)
{
    $xml .= '<text label="' . get_html_resource(RES_REQUIRED_ID) . '">' . get_html_resource($field['is_required'] ? RES_YES_ID : RES_NO_ID) . '</text>';
}

$xml .= '<text label="' . get_html_resource(RES_GUEST_ACCESS_ID)   . '">' . get_html_resource($field['guest_access']   ? RES_YES_ID : RES_NO_ID) . '</text>'
      . '<text label="' . get_html_resource(RES_ADD_SEPARATOR_ID)  . '">' . get_html_resource($field['add_separator']  ? RES_YES_ID : RES_NO_ID) . '</text>'
      . '<text label="' . get_html_resource(RES_SHOW_IN_EMAILS_ID) . '">' . get_html_resource($field['show_in_emails'] ? RES_YES_ID : RES_NO_ID) . '</text>'
      . '</group>';

echo(xml2html($xml));

?>
