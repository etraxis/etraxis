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
//  Artem Rodygin           2005-03-24      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-08-01      new-013: UI scenarios should be changed.
//  Artem Rodygin           2005-08-06      new-019: Fields default values.
//  Artem Rodygin           2005-08-18      new-037: Any template should be locked to be modified without suspending a project.
//  Artem Rodygin           2005-08-23      new-053: All the calls of DAL API functions should be moved to DBO API.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-09-07      new-100: 'Date' field type should be implemented.
//  Artem Rodygin           2005-09-08      new-101: 'Duration' field type should be implemented.
//  Artem Rodygin           2005-09-12      new-105: Format of date values are being entered should depend on user locale settings.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-11-17      new-176: Change eTraxis design.
//  Artem Rodygin           2006-07-27      new-261: UI design should be adopted to slow connection.
//  Artem Rodygin           2006-11-04      new-364: Default fields values.
//  Artem Rodygin           2007-06-27      bug-527: Unexpected "Invalid integer value" error when modify existing number field.
//  Artem Rodygin           2007-09-09      new-563: Custom separators inside fields set.
//  Yury Udovichenko        2007-11-14      new-548: Custom links in text fields.
//  Artem Rodygin           2008-01-27      bug-669: PHP Notice: Undefined variable: regex_check
//  Artem Rodygin           2008-02-08      new-671: Default value for 'date' fields should be relative.
//  Artem Rodygin           2008-09-10      new-716: 'Today' value in date field range.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-04-25      new-801: Range of valid date values must be related to current date.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/fields.php');
require_once('../dbo/values.php');
/**#@-*/

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

if (!$field['is_locked'])
{
    debug_write_log(DEBUG_NOTICE, 'Template must be locked.');
    header('Location: fview.php?id=' . $id);
    exit;
}

$fields = field_count($field['state_id']);

if (try_request('submitted') == 'mainform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $field_name    = ustrcut($_REQUEST['field_name'], MAX_FIELD_NAME);
    $field_order   = ustr2int($_REQUEST['field_order'], 1, $fields);
    $is_required   = ($field['field_type'] == FIELD_TYPE_CHECKBOX ? FALSE : isset($_REQUEST['is_required']));
    $add_separator = isset($_REQUEST['add_separator']);
    $guest_access  = isset($_REQUEST['guest_access']);

    switch ($field['field_type'])
    {
        case FIELD_TYPE_NUMBER:
            $param1  = $_REQUEST['min_value'];
            $param2  = $_REQUEST['max_value'];
            $default = $_REQUEST['def_value'];
            $default = (ustrlen($default) == 0 ? NULL : intval($default));
            $error   = field_validate_number($field_name, $param1, $param2, $default);
            break;

        case FIELD_TYPE_STRING:
            $param1 = $_REQUEST['max_length'];
            $param2 = NULL;
            $error  = field_validate_string($field_name, $param1);
            break;

        case FIELD_TYPE_MULTILINED:
            $param1 = $_REQUEST['max_length'];
            $param2 = NULL;
            $error  = field_validate_multilined($field_name, $param1);
            break;

        case FIELD_TYPE_CHECKBOX:
            $param1  = NULL;
            $param2  = NULL;
            $default = ustr2int(try_request('def_value', 1), 0, 1);
            $error   = NO_ERROR;
            break;

        case FIELD_TYPE_LIST:
            $param1  = NULL;
            $param2  = NULL;
            $default = try_request('def_value');
            $default = (ustrlen($default) == 0 ? NULL : ustr2int($default, 1, MAXINT));
            $error   = NO_ERROR;
            break;

        case FIELD_TYPE_RECORD:
            $param1  = NULL;
            $param2  = NULL;
            $default = NULL;
            $error   = NO_ERROR;
            break;

        case FIELD_TYPE_DATE:
            $param1  = ustrcut($_REQUEST['min_value'], ustrlen(MIN_FIELD_DATE));
            $param2  = ustrcut($_REQUEST['max_value'], ustrlen(MIN_FIELD_DATE));
            $default = ustrcut($_REQUEST['def_value'], ustrlen(MIN_FIELD_DATE));
            $default = (ustrlen($default) == 0 ? NULL : $default);
            $error   = field_validate_date($field_name, $param1, $param2, $default);
            break;

        case FIELD_TYPE_DURATION:
            $param1  = ustrcut($_REQUEST['min_value'], ustrlen(time2ustr(MAX_FIELD_DURATION)));
            $param2  = ustrcut($_REQUEST['max_value'], ustrlen(time2ustr(MAX_FIELD_DURATION)));
            $default = ustrcut($_REQUEST['def_value'], ustrlen(time2ustr(MAX_FIELD_DURATION)));
            $default = (ustrlen($default) == 0 ? NULL : $default);
            $error   = field_validate_duration($field_name, $param1, $param2, $default);
            break;

        default: ;  // nop
    }

    if ($error == NO_ERROR)
    {
        $regex_check   = NULL;
        $regex_search  = NULL;
        $regex_replace = NULL;

        $field_param1 = $param1;
        $field_param2 = $param2;

        if ($field['field_type'] == FIELD_TYPE_STRING)
        {
            $regex_check   = ustrcut($_REQUEST['regex_check'],   MAX_FIELD_REGEX);
            $regex_search  = ustrcut($_REQUEST['regex_search'],  MAX_FIELD_REGEX);
            $regex_replace = ustrcut($_REQUEST['regex_replace'], MAX_FIELD_REGEX);
            $default       = ustrcut($_REQUEST['def_value'], $field_param1);
            $default       = (ustrlen($default) == 0 ? NULL : value_find_string($default));
        }
        elseif ($field['field_type'] == FIELD_TYPE_MULTILINED)
        {
            $regex_check   = ustrcut($_REQUEST['regex_check'],   MAX_FIELD_REGEX);
            $regex_search  = ustrcut($_REQUEST['regex_search'],  MAX_FIELD_REGEX);
            $regex_replace = ustrcut($_REQUEST['regex_replace'], MAX_FIELD_REGEX);
            $default       = ustrcut($_REQUEST['def_value'], $field_param1);
            $default       = (ustrlen($default) == 0 ? NULL : value_find_multilined($default));
        }
        elseif ($field['field_type'] == FIELD_TYPE_DATE)
        {
            $default = (is_null($default) ? NULL : ustr2int($default, MIN_FIELD_DATE, MAX_FIELD_DATE));
        }
        elseif ($field['field_type'] == FIELD_TYPE_DURATION)
        {
            $field_param1 = ustr2time($field_param1);
            $field_param2 = ustr2time($field_param2);
            $default      = (is_null($default) ? NULL : ustr2time($default));
        }

        $error = field_modify($id,
                              $field['state_id'],
                              $field_name,
                              $field['field_order'],
                              $field_order,
                              $field['field_type'],
                              $is_required,
                              $add_separator,
                              $guest_access,
                              $regex_check,
                              $regex_search,
                              $regex_replace,
                              $field_param1,
                              $field_param2,
                              $default);

        if ($error == NO_ERROR)
        {
            if ($field['field_type'] == FIELD_TYPE_LIST)
            {
                $list_items = ustrcut($_REQUEST['list_items'], MAX_FIELD_LIST_ITEMS);
                field_create_list_items($field['state_id'], $field_name, $list_items);
            }

            header('Location: fview.php?id=' . $id);
            exit;
        }
    }

    switch ($error)
    {
        case ERROR_INCOMPLETE_FORM:
            $alert = get_js_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID);
            break;
        case ERROR_ALREADY_EXISTS:
            $alert = get_js_resource(RES_ALERT_FIELD_ALREADY_EXISTS_ID);
            break;
        case ERROR_INVALID_INTEGER_VALUE:
            $alert = get_js_resource(RES_ALERT_INVALID_INTEGER_VALUE_ID);
            break;
        case ERROR_INTEGER_VALUE_OUT_OF_RANGE:
            switch ($field['field_type'])
            {
                case FIELD_TYPE_NUMBER:
                    $alert = ustrprocess(get_js_resource(RES_ALERT_INTEGER_VALUE_OUT_OF_RANGE_ID), -MAX_FIELD_INTEGER, +MAX_FIELD_INTEGER);
                    break;
                case FIELD_TYPE_STRING:
                    $alert = ustrprocess(get_js_resource(RES_ALERT_INTEGER_VALUE_OUT_OF_RANGE_ID), 1, MAX_FIELD_STRING);
                    break;
                case FIELD_TYPE_MULTILINED:
                    $alert = ustrprocess(get_js_resource(RES_ALERT_INTEGER_VALUE_OUT_OF_RANGE_ID), 1, MAX_FIELD_MULTILINED);
                    break;
                default: ;  // nop
            }
            break;
        case ERROR_INVALID_DATE_VALUE:
            $alert = get_js_resource(RES_ALERT_INVALID_DATE_VALUE_ID);
            break;
        case ERROR_DATE_VALUE_OUT_OF_RANGE:
            $alert = ustrprocess(get_js_resource(RES_ALERT_DATE_VALUE_OUT_OF_RANGE_ID), MIN_FIELD_DATE, MAX_FIELD_DATE);
            break;
        case ERROR_INVALID_TIME_VALUE:
            $alert = get_js_resource(RES_ALERT_INVALID_TIME_VALUE_ID);
            break;
        case ERROR_TIME_VALUE_OUT_OF_RANGE:
            $alert = ustrprocess(get_js_resource(RES_ALERT_TIME_VALUE_OUT_OF_RANGE_ID), time2ustr(MIN_FIELD_DURATION), time2ustr(MAX_FIELD_DURATION));
            break;
        case ERROR_MIN_MAX_VALUES:
            $alert = get_js_resource(RES_ALERT_MIN_MAX_VALUES_ID);
            break;
        case ERROR_DEFAULT_VALUE_OUT_OF_RANGE:
            $alert = ustrprocess(get_js_resource(RES_ALERT_DEFAULT_VALUE_OUT_OF_RANGE_ID), $param1, $param2);
            break;
        default:
            $alert = NULL;
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $field_name    = $field['field_name'];
    $field_order   = $field['field_order'];
    $is_required   = $field['is_required'];
    $add_separator = $field['add_separator'];
    $guest_access  = $field['guest_access'];
    $regex_check   = $field['regex_check'];
    $regex_search  = $field['regex_search'];
    $regex_replace = $field['regex_replace'];
    $param1        = $field['param1'];
    $param2        = $field['param2'];
    $default       = $field['value_id'];

    if ($field['field_type'] == FIELD_TYPE_LIST)
    {
        $list_items = field_pickup_list_items($id);
    }
    elseif ($field['field_type'] == FIELD_TYPE_DURATION)
    {
        $param1 = time2ustr($param1);
        $param2 = time2ustr($param2);
    }

    if (!is_null($default))
    {
        switch ($field['field_type'])
        {
            case FIELD_TYPE_STRING:
            case FIELD_TYPE_MULTILINED:
                $default = value_find($field['field_type'], $default);
                break;

            case FIELD_TYPE_DURATION:
                $default = time2ustr($default);
                break;

            default: ;  // nop
        }
    }
}

$xml = '<page' . gen_xml_page_header(ustrprocess(get_html_resource(RES_FIELD_X_ID), ustr2html($field['field_name'])), isset($alert) ? $alert : NULL, 'mainform.field_name') . '>'
     . gen_xml_menu()
     . '<path>'
     . '<pathitem url="index.php">'                                    . get_html_resource(RES_PROJECTS_ID)                                                    . '</pathitem>'
     . '<pathitem url="view.php?id='    . $field['project_id']  . '">' . ustrprocess(get_html_resource(RES_PROJECT_X_ID), ustr2html($field['project_name']))   . '</pathitem>'
     . '<pathitem url="tindex.php?id='  . $field['project_id']  . '">' . get_html_resource(RES_TEMPLATES_ID)                                                   . '</pathitem>'
     . '<pathitem url="tview.php?id='   . $field['template_id'] . '">' . ustrprocess(get_html_resource(RES_TEMPLATE_X_ID), ustr2html($field['template_name'])) . '</pathitem>'
     . '<pathitem url="sindex.php?id='  . $field['template_id'] . '">' . get_html_resource(RES_STATES_ID)                                                      . '</pathitem>'
     . '<pathitem url="sview.php?id='   . $field['state_id']    . '">' . ustrprocess(get_html_resource(RES_STATE_X_ID), ustr2html($field['state_name']))       . '</pathitem>'
     . '<pathitem url="findex.php?id='  . $field['state_id']    . '">' . get_html_resource(RES_FIELDS_ID)                                                      . '</pathitem>'
     . '<pathitem url="fview.php?id='   . $id                   . '">' . ustrprocess(get_html_resource(RES_FIELD_X_ID), ustr2html($field['field_name']))       . '</pathitem>'
     . '<pathitem url="fmodify.php?id=' . $id                   . '">' . get_html_resource(RES_MODIFY_ID)                                                      . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<form name="mainform" action="fmodify.php?id=' . $id . '">'
     . '<group title="' . get_html_resource(RES_FIELD_INFO_ID) . '">'
     . '<editbox label="' . get_html_resource(RES_FIELD_NAME_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="field_name"  size="' . HTML_EDITBOX_SIZE_MEDIUM . '" maxlen="' . MAX_FIELD_NAME   . '">' . ustr2html($field_name)  . '</editbox>'
     . '<editbox label="' . get_html_resource(RES_ORDER_ID)      . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="field_order" size="' . HTML_EDITBOX_SIZE_SMALL  . '" maxlen="' . ustrlen($fields) . '">' . ustr2html($field_order) . '</editbox>';

if ($field['field_type'] == FIELD_TYPE_NUMBER)
{
    $xml .= '<editbox label="' . get_html_resource(RES_MIN_VALUE_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="min_value" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . (ustrlen(MAX_FIELD_INTEGER) + 1) . '">' . ustr2html($param1)  . '</editbox>'
          . '<editbox label="' . get_html_resource(RES_MAX_VALUE_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="max_value" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . (ustrlen(MAX_FIELD_INTEGER) + 1) . '">' . ustr2html($param2)  . '</editbox>'
          . '<editbox label="' . get_html_resource(RES_DEFAULT_VALUE_ID)                                                    . '" name="def_value" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . (ustrlen(MAX_FIELD_INTEGER) + 1) . '">' . ustr2html($default) . '</editbox>';
}
elseif ($field['field_type'] == FIELD_TYPE_STRING)
{
    $xml .= '<editbox label="' . get_html_resource(RES_MAX_LENGTH_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="max_length" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(MAX_FIELD_STRING) . '">' . ustr2html($param1) . '</editbox>'
          . '<editbox label="' . get_html_resource(RES_REGEX_CHECK_ID)   . '" name="regex_check"   size="' . HTML_EDITBOX_SIZE_LONG  . '" maxlen="' . MAX_FIELD_REGEX  . '">' . ustr2html($regex_check)   . '</editbox>'
          . '<editbox label="' . get_html_resource(RES_REGEX_SEARCH_ID)  . '" name="regex_search"  size="' . HTML_EDITBOX_SIZE_LONG  . '" maxlen="' . MAX_FIELD_REGEX  . '">' . ustr2html($regex_search)  . '</editbox>'
          . '<editbox label="' . get_html_resource(RES_REGEX_REPLACE_ID) . '" name="regex_replace" size="' . HTML_EDITBOX_SIZE_LONG  . '" maxlen="' . MAX_FIELD_REGEX  . '">' . ustr2html($regex_replace) . '</editbox>'
          . '<editbox label="' . get_html_resource(RES_DEFAULT_VALUE_ID) . '" name="def_value"     size="' . HTML_EDITBOX_SIZE_LONG  . '" maxlen="' . MAX_FIELD_STRING . '">' . ustr2html($default)       . '</editbox>';
}
elseif ($field['field_type'] == FIELD_TYPE_MULTILINED)
{
    $xml .= '<editbox label="' . get_html_resource(RES_MAX_LENGTH_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="max_length" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(MAX_FIELD_MULTILINED) . '">' . ustr2html($param1) . '</editbox>'
          . '<editbox label="' . get_html_resource(RES_REGEX_CHECK_ID)   . '" name="regex_check"   size="' . HTML_EDITBOX_SIZE_LONG  . '" maxlen="' . MAX_FIELD_REGEX  . '">' . ustr2html($regex_check)   . '</editbox>'
          . '<editbox label="' . get_html_resource(RES_REGEX_SEARCH_ID)  . '" name="regex_search"  size="' . HTML_EDITBOX_SIZE_LONG  . '" maxlen="' . MAX_FIELD_REGEX  . '">' . ustr2html($regex_search)  . '</editbox>'
          . '<editbox label="' . get_html_resource(RES_REGEX_REPLACE_ID) . '" name="regex_replace" size="' . HTML_EDITBOX_SIZE_LONG  . '" maxlen="' . MAX_FIELD_REGEX  . '">' . ustr2html($regex_replace) . '</editbox>'
          . '<textbox label="' . get_html_resource(RES_DEFAULT_VALUE_ID) . '" name="def_value" width="' . HTML_TEXTBOX_WIDTH . '" height="' . HTML_TEXTBOX_HEIGHT . '" maxlen="' . MAX_FIELD_MULTILINED . '">' . ustr2html($default) . '</textbox>';
}
elseif ($field['field_type'] == FIELD_TYPE_CHECKBOX)
{
    $xml .= '<radios name="def_value" label="' . get_html_resource(RES_DEFAULT_VALUE_ID) . '">'
          . '<radio name="def_value" value="1"' . ($default != 0 ? ' checked="true">' : '>') . get_html_resource(RES_ON_ID)  . '</radio>'
          . '<radio name="def_value" value="0"' . ($default == 0 ? ' checked="true">' : '>') . get_html_resource(RES_OFF_ID) . '</radio>'
          . '</radios>';
}
elseif ($field['field_type'] == FIELD_TYPE_LIST)
{
    $xml .= '<textbox label="' . get_html_resource(RES_LIST_ITEMS_ID)    . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="list_items" width="' . HTML_TEXTBOX_WIDTH . '" height="' . HTML_TEXTBOX_HEIGHT . '" maxlen="' . MAX_FIELD_LIST_ITEMS . '">' . ustr2html($list_items) . '</textbox>'
          . '<editbox label="' . get_html_resource(RES_DEFAULT_VALUE_ID) . '"                                                        name="def_value"  size="' . HTML_EDITBOX_SIZE_SMALL . '"                                  maxlen="' . ustrlen(MAXINT)      . '">' . ustr2html($default)    . '</editbox>';
}
elseif ($field['field_type'] == FIELD_TYPE_DATE)
{
    $xml .= '<editbox label="' . get_html_resource(RES_MIN_VALUE_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="min_value" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(MIN_FIELD_DATE) . '">' . ustr2html($param1)  . '</editbox>'
          . '<editbox label="' . get_html_resource(RES_MAX_VALUE_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="max_value" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(MIN_FIELD_DATE) . '">' . ustr2html($param2)  . '</editbox>'
          . '<editbox label="' . get_html_resource(RES_DEFAULT_VALUE_ID)                                                    . '" name="def_value" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(MIN_FIELD_DATE) . '">' . ustr2html($default) . '</editbox>';
}
elseif ($field['field_type'] == FIELD_TYPE_DURATION)
{
    $xml .= '<editbox label="' . get_html_resource(RES_MIN_VALUE_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="min_value" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(time2ustr(MAX_FIELD_DURATION)) . '">' . ustr2html($param1)  . '</editbox>'
          . '<editbox label="' . get_html_resource(RES_MAX_VALUE_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="max_value" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(time2ustr(MAX_FIELD_DURATION)) . '">' . ustr2html($param2)  . '</editbox>'
          . '<editbox label="' . get_html_resource(RES_DEFAULT_VALUE_ID)                                                    . '" name="def_value" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(time2ustr(MAX_FIELD_DURATION)) . '">' . ustr2html($default) . '</editbox>';
}

if ($field['field_type'] != FIELD_TYPE_CHECKBOX)
{
    $xml .= '<checkbox name="is_required"' . ($is_required ? ' checked="true">' : '>') . get_html_resource(RES_REQUIRED2_ID) . '</checkbox>';
}

$xml .= '<checkbox name="add_separator"' . ($add_separator ? ' checked="true">' : '>') . ustrtolower(get_html_resource(RES_ADD_SEPARATOR_ID)) . '</checkbox>'
      . '<checkbox name="guest_access"'  . ($guest_access  ? ' checked="true">' : '>') . ustrtolower(get_html_resource(RES_GUEST_ACCESS_ID))  . '</checkbox>'
      . '</group>'
      . '<button default="true">'                 . get_html_resource(RES_OK_ID)     . '</button>'
      . '<button url="fview.php?id=' . $id . '">' . get_html_resource(RES_CANCEL_ID) . '</button>'
      . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>';

if ($field['field_type'] == FIELD_TYPE_NUMBER)
{
    $xml .= '<note>' . ustrprocess(get_html_resource(RES_ALERT_INTEGER_VALUE_OUT_OF_RANGE_ID), -MAX_FIELD_INTEGER, +MAX_FIELD_INTEGER) . '</note>'
          . '<note>' . get_html_resource(RES_ALERT_MIN_MAX_VALUES_ID)                                                                  . '</note>';
}
elseif ($field['field_type'] == FIELD_TYPE_STRING)
{
    $xml .= '<note>' . ustrprocess(get_html_resource(RES_ALERT_INTEGER_VALUE_OUT_OF_RANGE_ID), 1, MAX_FIELD_STRING) . '</note>';
}
elseif ($field['field_type'] == FIELD_TYPE_MULTILINED)
{
    $xml .= '<note>' . ustrprocess(get_html_resource(RES_ALERT_INTEGER_VALUE_OUT_OF_RANGE_ID), 1, MAX_FIELD_MULTILINED) . '</note>';
}
elseif ($field['field_type'] == FIELD_TYPE_DATE)
{
    $xml .= '<note>' . ustrprocess(get_html_resource(RES_ALERT_DATE_VALUE_OUT_OF_RANGE_ID),    MIN_FIELD_DATE, MAX_FIELD_DATE) . '</note>'
          . '<note>' . ustrprocess(get_html_resource(RES_ALERT_DEFAULT_VALUE_OUT_OF_RANGE_ID), MIN_FIELD_DATE, MAX_FIELD_DATE) . '</note>'
          . '<note>' . get_html_resource(RES_ALERT_MIN_MAX_VALUES_ID)                                                          . '</note>';
}
elseif ($field['field_type'] == FIELD_TYPE_DURATION)
{
    $xml .= '<note>' . ustrprocess(get_html_resource(RES_ALERT_TIME_VALUE_OUT_OF_RANGE_ID), time2ustr(MIN_FIELD_DURATION), time2ustr(MAX_FIELD_DURATION)) . '</note>'
          . '<note>' . get_html_resource(RES_ALERT_MIN_MAX_VALUES_ID)                                                                                     . '</note>';
}

$xml .= '</form>'
      . '</content>'
      . '</page>';

echo(xml2html($xml));

?>
