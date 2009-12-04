<?php

/**
 * @package eTraxis
 * @ignore
 */

//--------------------------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system.
//  Copyright (C) 2005-2009 by Artem Rodygin
//
//  This program is free software; you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation; either version 2 of the License, or
//  (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License along
//  with this program; if not, write to the Free Software Foundation, Inc.,
//  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
//
//--------------------------------------------------------------------------------------------------
//  Author                  Date            Description of modifications
//--------------------------------------------------------------------------------------------------
//  Artem Rodygin           2005-08-01      new-013: UI scenarios should be changed.
//  Artem Rodygin           2005-08-06      new-019: Fields default values.
//  Artem Rodygin           2005-08-18      new-037: Any template should be locked to be modified without suspending a project.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-09-07      new-100: 'Date' field type should be implemented.
//  Artem Rodygin           2005-09-08      new-101: 'Duration' field type should be implemented.
//  Artem Rodygin           2005-09-13      new-113: When record is being viewed the fields names and values should be aligned by top.
//  Artem Rodygin           2005-10-05      new-145: Remove autofocus from buttons.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-11-17      new-176: Change eTraxis design.
//  Artem Rodygin           2006-07-27      new-261: UI design should be adopted to slow connection.
//  Artem Rodygin           2006-10-08      bug-345: /src/projects/fview.php: Global variable $alert was used before it was defined.
//  Artem Rodygin           2006-11-04      new-364: Default fields values.
//  Artem Rodygin           2006-11-11      bug-379: Lost new-line characters in default values of multilined fields.
//  Artem Rodygin           2006-12-28      new-474: Rename field permissions to make them more clear.
//  Artem Rodygin           2007-09-09      new-563: Custom separators inside fields set.
//  Yury Udovichenko        2007-11-14      new-548: Custom links in text fields.
//  Artem Rodygin           2008-02-08      new-671: Default value for 'date' fields should be relative.
//  Artem Rodygin           2008-04-30      bug-699: Views // Names of custom columns are duplicated in the list of available columns, when there are two fields of different types with the same name.
//  Artem Rodygin           2008-06-16      bug-719: Global variable $field_type_res was used before it was defined
//  Artem Rodygin           2008-09-11      new-716: 'Today' value in date field range.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-04-24      new-817: Field permissions dialog refactoring.
//  Artem Rodygin           2009-04-25      new-801: Range of valid date values must be related to current date.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//  Artem Rodygin           2009-10-13      new-838: Disabled buttons would be better grayed out than invisible.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
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

$id    = ustr2int(try_request('id'));
$field = field_find($id);

if (!$field)
{
    debug_write_log(DEBUG_NOTICE, 'Field cannot be found.');
    header('Location: index.php');
    exit;
}

$xml = '<page' . gen_xml_page_header(ustrprocess(get_html_resource(RES_FIELD_X_ID), ustr2html($field['field_name']))) . '>'
     . gen_xml_menu()
     . '<path>'
     . '<pathitem url="index.php">'                                   . get_html_resource(RES_PROJECTS_ID)                                                    . '</pathitem>'
     . '<pathitem url="view.php?id='   . $field['project_id']  . '">' . ustrprocess(get_html_resource(RES_PROJECT_X_ID), ustr2html($field['project_name']))   . '</pathitem>'
     . '<pathitem url="tindex.php?id=' . $field['project_id']  . '">' . get_html_resource(RES_TEMPLATES_ID)                                                   . '</pathitem>'
     . '<pathitem url="tview.php?id='  . $field['template_id'] . '">' . ustrprocess(get_html_resource(RES_TEMPLATE_X_ID), ustr2html($field['template_name'])) . '</pathitem>'
     . '<pathitem url="sindex.php?id=' . $field['template_id'] . '">' . get_html_resource(RES_STATES_ID)                                                      . '</pathitem>'
     . '<pathitem url="sview.php?id='  . $field['state_id']    . '">' . ustrprocess(get_html_resource(RES_STATE_X_ID), ustr2html($field['state_name']))       . '</pathitem>'
     . '<pathitem url="findex.php?id=' . $field['state_id']    . '">' . get_html_resource(RES_FIELDS_ID)                                                      . '</pathitem>'
     . '<pathitem url="fview.php?id='  . $id                   . '">' . ustrprocess(get_html_resource(RES_FIELD_X_ID), ustr2html($field['field_name']))       . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<form name="mainform" action="findex.php?id=' . $field['state_id'] . '">'
     . '<group title="' . get_html_resource(RES_FIELD_INFO_ID) . '">'
     . '<text label="' . get_html_resource(RES_FIELD_NAME_ID) . '">' . ustr2html($field['field_name'])                          . '</text>'
     . '<text label="' . get_html_resource(RES_FIELD_TYPE_ID) . '">' . get_html_resource($field_type_res[$field['field_type']]) . '</text>'
     . '<text label="' . get_html_resource(RES_ORDER_ID)      . '">' . ustr2html($field['field_order'])                         . '</text>';

if ($field['field_type'] == FIELD_TYPE_NUMBER)
{
    $xml .= '<text label="' . get_html_resource(RES_MIN_VALUE_ID)     . '">' . ustr2html($field['param1']) . '</text>'
          . '<text label="' . get_html_resource(RES_MAX_VALUE_ID)     . '">' . ustr2html($field['param2']) . '</text>'
          . '<text label="' . get_html_resource(RES_DEFAULT_VALUE_ID) . '">' . (is_null($field['value_id']) ? get_html_resource(RES_NONE_ID) : ustr2html($field['value_id'])) . '</text>';
}
elseif ($field['field_type'] == FIELD_TYPE_STRING ||
        $field['field_type'] == FIELD_TYPE_MULTILINED)
{
    $xml .= '<text label="' . get_html_resource(RES_MAX_LENGTH_ID)    . '">' . ustr2html($field['param1']) . '</text>'
          . '<text label="' . get_html_resource(RES_REGEX_CHECK_ID)   . '">' . (is_null($field['regex_check'])   ? get_html_resource(RES_NONE_ID) : ustr2html(ustr_replace("\n", '%br;', $field['regex_check'])))   . '</text>'
          . '<text label="' . get_html_resource(RES_REGEX_SEARCH_ID)  . '">' . (is_null($field['regex_search'])  ? get_html_resource(RES_NONE_ID) : ustr2html(ustr_replace("\n", '%br;', $field['regex_search'])))  . '</text>'
          . '<text label="' . get_html_resource(RES_REGEX_REPLACE_ID) . '">' . (is_null($field['regex_replace']) ? get_html_resource(RES_NONE_ID) : ustr2html(ustr_replace("\n", '%br;', $field['regex_replace']))) . '</text>'
          . '<text label="' . get_html_resource(RES_DEFAULT_VALUE_ID) . '">' . (is_null($field['value_id'])      ? get_html_resource(RES_NONE_ID) : ustr2html(ustr_replace("\n", '%br;', value_find($field['field_type'], $field['value_id'])))) . '</text>';
}
elseif ($field['field_type'] == FIELD_TYPE_CHECKBOX)
{
    $xml .= '<text label="' . get_html_resource(RES_DEFAULT_VALUE_ID) . '">' . get_html_resource($field['value_id'] == 0 ? RES_OFF_ID : RES_ON_ID) . '</text>';
}
elseif ($field['field_type'] == FIELD_TYPE_LIST)
{
    $xml .= '<text label="' . get_html_resource(RES_LIST_ITEMS_ID)    . '">' . ustr2html(ustr_replace("\n", '%br;', field_pickup_list_items($id))) . '</text>'
          . '<text label="' . get_html_resource(RES_DEFAULT_VALUE_ID) . '">' . (is_null($field['value_id']) ? get_html_resource(RES_NONE_ID) : ustr2html($field['value_id'])) . '</text>';
}
elseif ($field['field_type'] == FIELD_TYPE_DATE)
{
    $xml .= '<text label="' . get_html_resource(RES_MIN_VALUE_ID)     . '">' . ustr2html($field['param1']) . '</text>'
          . '<text label="' . get_html_resource(RES_MAX_VALUE_ID)     . '">' . ustr2html($field['param2']) . '</text>'
          . '<text label="' . get_html_resource(RES_DEFAULT_VALUE_ID) . '">' . (is_null($field['value_id']) ? get_html_resource(RES_NONE_ID) : $field['value_id']) . '</text>';
}
elseif ($field['field_type'] == FIELD_TYPE_DURATION)
{
    $xml .= '<text label="' . get_html_resource(RES_MIN_VALUE_ID)     . '">' . time2ustr($field['param1']) . '</text>'
          . '<text label="' . get_html_resource(RES_MAX_VALUE_ID)     . '">' . time2ustr($field['param2']) . '</text>'
          . '<text label="' . get_html_resource(RES_DEFAULT_VALUE_ID) . '">' . (is_null($field['value_id']) ? get_html_resource(RES_NONE_ID) : time2ustr($field['value_id'])) . '</text>';
}

if ($field['field_type'] != FIELD_TYPE_CHECKBOX)
{
    $xml .= '<text label="' . get_html_resource(RES_REQUIRED_ID) . '">' . get_html_resource($field['is_required'] ? RES_YES_ID : RES_NO_ID) . '</text>';
}

$xml .= '<text label="' . get_html_resource(RES_ADD_SEPARATOR_ID) . '">' . get_html_resource($field['add_separator'] ? RES_YES_ID : RES_NO_ID) . '</text>'
      . '<text label="' . get_html_resource(RES_GUEST_ACCESS_ID)  . '">' . get_html_resource($field['guest_access'] ?  RES_YES_ID : RES_NO_ID) . '</text>'
      . '</group>'
      . '<button name="back" default="true">' . get_html_resource(RES_BACK_ID) . '</button>';

if ($field['is_locked'])
{
    $xml .= '<button url="fmodify.php?id=' . $id . '">' . get_html_resource(RES_MODIFY_ID) . '</button>';

    if (is_field_removable($id))
    {
        $xml .= '<button url="fdelete.php?id=' . $id . '" prompt="' . get_html_resource(RES_CONFIRM_DELETE_FIELD_ID) . '">' . get_html_resource(RES_DELETE_ID) . '</button>';
    }
    else
    {
        $xml .= '<button disabled="true">' . get_html_resource(RES_DELETE_ID) . '</button>';
    }
}
else
{
    $xml .= '<button disabled="true">' . get_html_resource(RES_MODIFY_ID) . '</button>'
          . '<button disabled="true">' . get_html_resource(RES_DELETE_ID) . '</button>';
}

$xml .= '<button url="fperms.php?id=' . $id . '">' . get_html_resource(RES_PERMISSIONS_ID) . '</button>'
      . '</form>'
      . '</content>'
      . '</page>';

echo(xml2html($xml));

?>
