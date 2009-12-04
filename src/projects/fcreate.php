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
//  Artem Rodygin           2005-03-23      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-08-01      new-013: UI scenarios should be changed.
//  Artem Rodygin           2005-08-06      new-019: Fields default values.
//  Artem Rodygin           2005-08-18      new-037: Any template should be locked to be modified without suspending a project.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-09-07      new-100: 'Date' field type should be implemented.
//  Artem Rodygin           2005-09-08      new-101: 'Duration' field type should be implemented.
//  Artem Rodygin           2005-09-12      new-105: Format of date values are being entered should depend on user locale settings.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-11-17      new-176: Change eTraxis design.
//  Artem Rodygin           2006-07-27      new-261: UI design should be adopted to slow connection.
//  Artem Rodygin           2006-11-04      new-364: Default fields values.
//  Artem Rodygin           2006-12-09      bug-424: PHP Notice: Undefined variable: def_value
//  Artem Rodygin           2007-09-09      new-563: Custom separators inside fields set.
//  Yury Udovichenko        2007-11-14      new-548: Custom links in text fields.
//  Artem Rodygin           2007-11-14      bug-625: PHP Notice: Undefined variable: add_separator
//  Artem Rodygin           2008-02-08      bug-672: Newly created 'date' field always has range of values from '1/1/1970' till '1/1/1970'.
//  Artem Rodygin           2008-02-08      new-671: Default value for 'date' fields should be relative.
//  Artem Rodygin           2008-04-30      bug-699: Views // Names of custom columns are duplicated in the list of available columns, when there are two fields of different types with the same name.
//  Artem Rodygin           2008-06-16      bug-719: Global variable $field_type_res was used before it was defined
//  Artem Rodygin           2008-09-10      new-716: 'Today' value in date field range.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-04-25      new-801: Range of valid date values must be related to current date.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/states.php');
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
$state = state_find($id);

if (!$state)
{
    debug_write_log(DEBUG_NOTICE, 'State cannot be found.');
    header('Location: index.php');
    exit;
}

if (!$state['is_locked'])
{
    debug_write_log(DEBUG_NOTICE, 'Template must be locked.');
    header('Location: findex.php?id=' . $id);
    exit;
}

if (try_request('submitted') == 'mainform')
{
    debug_write_log(DEBUG_NOTICE, 'Data for step #1 are submitted.');

    $field_name    = ustrcut($_REQUEST['field_name'], MAX_FIELD_NAME);
    $field_type    = ustr2int($_REQUEST['field_type'], FIELD_TYPE_MINIMUM, FIELD_TYPE_MAXIMUM);
    $is_required   = FALSE;
    $add_separator = FALSE;
    $guest_access  = FALSE;

    $error = field_validate($field_name);

    if ($error == NO_ERROR)
    {
        switch ($field_type)
        {
            case FIELD_TYPE_NUMBER:
                $form      = 'numberform';
                $focus     = '.min_value';
                $min_value = NULL;
                $max_value = NULL;
                $def_value = NULL;
                break;

            case FIELD_TYPE_STRING:
                $form          = 'stringform';
                $focus         = '.max_length';
                $max_length    = NULL;
                $regex_check   = NULL;
                $regex_search  = NULL;
                $regex_replace = NULL;
                $def_value     = NULL;
                break;

            case FIELD_TYPE_MULTILINED:
                $form          = 'multilinedform';
                $focus         = '.max_length';
                $max_length    = NULL;
                $regex_check   = NULL;
                $regex_search  = NULL;
                $regex_replace = NULL;
                $def_value     = NULL;
                break;

            case FIELD_TYPE_CHECKBOX:
                $form      = 'checkboxform';
                $focus     = '.field_name';
                $def_value = 1;
                break;

            case FIELD_TYPE_LIST:
                $form       = 'listform';
                $focus      = '.list_items';
                $list_items = NULL;
                $def_value  = NULL;
                break;

            case FIELD_TYPE_RECORD:
                $form      = 'recordform';
                $focus     = '.is_required';
                $min_value = NULL;
                $max_value = NULL;
                break;

            case FIELD_TYPE_DATE:
                $form      = 'dateform';
                $focus     = '.min_value';
                $min_value = NULL;
                $max_value = NULL;
                $def_value = NULL;
                break;

            case FIELD_TYPE_DURATION:
                $form      = 'durationform';
                $focus     = '.min_value';
                $min_value = NULL;
                $max_value = NULL;
                $def_value = NULL;
                break;

            default: ;  // nop
        }
    }
    else
    {
        $form      = 'mainform';
        $focus     = '.field_name';
        $def_value = NULL;

        switch ($error)
        {
            case ERROR_INCOMPLETE_FORM:
                $alert = get_js_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID);
                break;
            default:
                $alert = NULL;
        }
    }
}
elseif (try_request('submitted') == 'numberform')
{
    debug_write_log(DEBUG_NOTICE, 'Data for step #2 (number) are submitted.');

    $form  = 'numberform';
    $focus = '.min_value';

    $field_name    = ustrcut($_REQUEST['field_name'], MAX_FIELD_NAME);
    $field_type    = FIELD_TYPE_NUMBER;
    $min_value     = ustrcut($_REQUEST['min_value'], ustrlen(MAX_FIELD_INTEGER) + 1);
    $max_value     = ustrcut($_REQUEST['max_value'], ustrlen(MAX_FIELD_INTEGER) + 1);
    $def_value     = ustrcut($_REQUEST['def_value'], ustrlen(MAX_FIELD_INTEGER) + 1);
    $def_value     = (ustrlen($def_value) == 0 ? NULL : intval($def_value));
    $is_required   = isset($_REQUEST['is_required']);
    $add_separator = isset($_REQUEST['add_separator']);
    $guest_access  = isset($_REQUEST['guest_access']);

    $error = field_validate_number($field_name, $min_value, $max_value, $def_value);

    if ($error == NO_ERROR)
    {
        $error = field_create($id,
                              $field_name,
                              $field_type,
                              $is_required,
                              $add_separator,
                              $guest_access,
                              NULL, NULL, NULL,
                              $min_value,
                              $max_value,
                              $def_value);

        if ($error == NO_ERROR)
        {
            header('Location: findex.php?id=' . $id);
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
            $alert = ustrprocess(get_js_resource(RES_ALERT_INTEGER_VALUE_OUT_OF_RANGE_ID), -MAX_FIELD_INTEGER, +MAX_FIELD_INTEGER);
            break;
        case ERROR_MIN_MAX_VALUES:
            $alert = get_js_resource(RES_ALERT_MIN_MAX_VALUES_ID);
            break;
        case ERROR_DEFAULT_VALUE_OUT_OF_RANGE:
            $alert = ustrprocess(get_js_resource(RES_ALERT_DEFAULT_VALUE_OUT_OF_RANGE_ID), $min_value, $max_value);
            break;
        default:
            $alert = NULL;
    }
}
elseif (try_request('submitted') == 'stringform')
{
    debug_write_log(DEBUG_NOTICE, 'Data for step #2 (string) are submitted.');

    $form      = 'stringform';
    $focus     = '.max_length';
    $def_value = NULL;

    $field_name    = ustrcut($_REQUEST['field_name'], MAX_FIELD_NAME);
    $field_type    = FIELD_TYPE_STRING;
    $max_length    = ustrcut($_REQUEST['max_length'], ustrlen(MAX_FIELD_STRING));
    $is_required   = isset($_REQUEST['is_required']);
    $add_separator = isset($_REQUEST['add_separator']);
    $guest_access  = isset($_REQUEST['guest_access']);
    $regex_check   = ustrcut($_REQUEST['regex_check'],   MAX_FIELD_REGEX);
    $regex_search  = ustrcut($_REQUEST['regex_search'],  MAX_FIELD_REGEX);
    $regex_replace = ustrcut($_REQUEST['regex_replace'], MAX_FIELD_REGEX);

    $error = field_validate_string($field_name, $max_length);

    if ($error == NO_ERROR)
    {
        $def_value = ustrcut($_REQUEST['def_value'], $max_length);
        $value_id  = (ustrlen($def_value) == 0 ? NULL : value_find_string($def_value));

        $error = field_create($id,
                              $field_name,
                              $field_type,
                              $is_required,
                              $add_separator,
                              $guest_access,
                              $regex_check,
                              $regex_search,
                              $regex_replace,
                              $max_length,
                              NULL,
                              $value_id);

        if ($error == NO_ERROR)
        {
            header('Location: findex.php?id=' . $id);
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
            $alert = ustrprocess(get_js_resource(RES_ALERT_INTEGER_VALUE_OUT_OF_RANGE_ID), 1, MAX_FIELD_STRING);
            break;
        default:
            $alert = NULL;
    }
}
elseif (try_request('submitted') == 'multilinedform')
{
    debug_write_log(DEBUG_NOTICE, 'Data for step #2 (multilined text) are submitted.');

    $form      = 'multilinedform';
    $focus     = '.max_length';
    $def_value = NULL;

    $field_name    = ustrcut($_REQUEST['field_name'], MAX_FIELD_NAME);
    $field_type    = FIELD_TYPE_MULTILINED;
    $max_length    = ustrcut($_REQUEST['max_length'], ustrlen(MAX_FIELD_MULTILINED));
    $is_required   = isset($_REQUEST['is_required']);
    $add_separator = isset($_REQUEST['add_separator']);
    $guest_access  = isset($_REQUEST['guest_access']);
    $regex_check   = ustrcut($_REQUEST['regex_check'],   MAX_FIELD_REGEX);
    $regex_search  = ustrcut($_REQUEST['regex_search'],  MAX_FIELD_REGEX);
    $regex_replace = ustrcut($_REQUEST['regex_replace'], MAX_FIELD_REGEX);

    $error = field_validate_multilined($field_name, $max_length);

    if ($error == NO_ERROR)
    {
        $def_value = ustrcut($_REQUEST['def_value'], $max_length);
        $value_id  = (ustrlen($def_value) == 0 ? NULL : value_find_multilined($def_value));

        $error = field_create($id,
                              $field_name,
                              $field_type,
                              $is_required,
                              $add_separator,
                              $guest_access,
                              $regex_check,
                              $regex_search,
                              $regex_replace,
                              $max_length,
                              NULL,
                              $value_id);

        if ($error == NO_ERROR)
        {
            header('Location: findex.php?id=' . $id);
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
            $alert = ustrprocess(get_js_resource(RES_ALERT_INTEGER_VALUE_OUT_OF_RANGE_ID), 1, MAX_FIELD_MULTILINED);
            break;
        default:
            $alert = NULL;
    }
}
elseif (try_request('submitted') == 'checkboxform')
{
    debug_write_log(DEBUG_NOTICE, 'Data for step #2 (checkbox) are submitted.');

    $form  = 'checkboxform';
    $focus = '.field_name';

    $field_name    = ustrcut($_REQUEST['field_name'], MAX_FIELD_NAME);
    $field_type    = FIELD_TYPE_CHECKBOX;
    $def_value     = ustr2int(try_request('def_value', 1), 0, 1);
    $add_separator = isset($_REQUEST['add_separator']);
    $guest_access  = isset($_REQUEST['guest_access']);

    $error = field_create($id,
                          $field_name,
                          $field_type,
                          FALSE,
                          $add_separator,
                          $guest_access,
                          NULL, NULL, NULL, NULL, NULL,
                          $def_value);

    if ($error == NO_ERROR)
    {
        header('Location: findex.php?id=' . $id);
        exit;
    }

    switch ($error)
    {
        case ERROR_INCOMPLETE_FORM:
            $alert = get_js_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID);
            break;
        case ERROR_ALREADY_EXISTS:
            $alert = get_js_resource(RES_ALERT_FIELD_ALREADY_EXISTS_ID);
            break;
        default:
            $alert = NULL;
    }
}
elseif (try_request('submitted') == 'listform')
{
    debug_write_log(DEBUG_NOTICE, 'Data for step #2 (list) are submitted.');

    $form  = 'listform';
    $focus = '.list_items';

    $field_name    = ustrcut($_REQUEST['field_name'], MAX_FIELD_NAME);
    $field_type    = FIELD_TYPE_LIST;
    $list_items    = ustrcut($_REQUEST['list_items'], MAX_FIELD_LIST_ITEMS);
    $def_value     = try_request('def_value');
    $def_value     = (ustrlen($def_value) == 0 ? NULL : ustr2int($def_value, 1, MAXINT));
    $is_required   = isset($_REQUEST['is_required']);
    $add_separator = isset($_REQUEST['add_separator']);
    $guest_access  = isset($_REQUEST['guest_access']);

    $error = field_create($id,
                          $field_name,
                          $field_type,
                          $is_required,
                          $add_separator,
                          $guest_access,
                          NULL, NULL, NULL, NULL, NULL,
                          $def_value);

    if ($error == NO_ERROR)
    {
        field_create_list_items($id, $field_name, $list_items);
        header('Location: findex.php?id=' . $id);
        exit;
    }

    switch ($error)
    {
        case ERROR_INCOMPLETE_FORM:
            $alert = get_js_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID);
            break;
        case ERROR_ALREADY_EXISTS:
            $alert = get_js_resource(RES_ALERT_FIELD_ALREADY_EXISTS_ID);
            break;
        default:
            $alert = NULL;
    }
}
elseif (try_request('submitted') == 'recordform')
{
    debug_write_log(DEBUG_NOTICE, 'Data for step #2 (record) are submitted.');

    $form  = 'recordform';
    $focus = '.is_required';

    $field_name    = ustrcut($_REQUEST['field_name'], MAX_FIELD_NAME);
    $field_type    = FIELD_TYPE_RECORD;
    $is_required   = isset($_REQUEST['is_required']);
    $add_separator = isset($_REQUEST['add_separator']);
    $guest_access  = isset($_REQUEST['guest_access']);

    $error = field_create($id,
                          $field_name,
                          $field_type,
                          $is_required,
                          $add_separator,
                          $guest_access);

    if ($error == NO_ERROR)
    {
        header('Location: findex.php?id=' . $id);
        exit;
    }

    switch ($error)
    {
        case ERROR_INCOMPLETE_FORM:
            $alert = get_js_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID);
            break;
        case ERROR_ALREADY_EXISTS:
            $alert = get_js_resource(RES_ALERT_FIELD_ALREADY_EXISTS_ID);
            break;
        default:
            $alert = NULL;
    }
}
elseif (try_request('submitted') == 'dateform')
{
    debug_write_log(DEBUG_NOTICE, 'Data for step #2 (date) are submitted.');

    $form  = 'dateform';
    $focus = '.min_value';

    $field_name    = ustrcut($_REQUEST['field_name'], MAX_FIELD_NAME);
    $field_type    = FIELD_TYPE_DATE;
    $min_value     = ustrcut($_REQUEST['min_value'], ustrlen(MIN_FIELD_DATE));
    $max_value     = ustrcut($_REQUEST['max_value'], ustrlen(MIN_FIELD_DATE));
    $def_value     = ustrcut($_REQUEST['def_value'], ustrlen(MIN_FIELD_DATE));
    $def_value     = (ustrlen($def_value) == 0 ? NULL : $def_value);
    $is_required   = isset($_REQUEST['is_required']);
    $add_separator = isset($_REQUEST['add_separator']);
    $guest_access  = isset($_REQUEST['guest_access']);

    $error = field_validate_date($field_name, $min_value, $max_value, $def_value);

    if ($error == NO_ERROR)
    {
        $error = field_create($id,
                              $field_name,
                              $field_type,
                              $is_required,
                              $add_separator,
                              $guest_access,
                              NULL, NULL, NULL,
                              $min_value,
                              $max_value,
                              is_null($def_value) ? NULL : ustr2int($def_value, MIN_FIELD_DATE, MAX_FIELD_DATE));

        if ($error == NO_ERROR)
        {
            header('Location: findex.php?id=' . $id);
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
        case ERROR_INVALID_DATE_VALUE:
            $alert = get_js_resource(RES_ALERT_INVALID_DATE_VALUE_ID);
            break;
        case ERROR_DATE_VALUE_OUT_OF_RANGE:
            $alert = ustrprocess(get_js_resource(RES_ALERT_DATE_VALUE_OUT_OF_RANGE_ID), MIN_FIELD_DATE, MAX_FIELD_DATE);
            break;
        case ERROR_MIN_MAX_VALUES:
            $alert = get_js_resource(RES_ALERT_MIN_MAX_VALUES_ID);
            break;
        case ERROR_DEFAULT_VALUE_OUT_OF_RANGE:
            $alert = ustrprocess(get_js_resource(RES_ALERT_DEFAULT_VALUE_OUT_OF_RANGE_ID), $min_value, $max_value);
            break;
        default:
            $alert = NULL;
    }
}
elseif (try_request('submitted') == 'durationform')
{
    debug_write_log(DEBUG_NOTICE, 'Data for step #2 (duration) are submitted.');

    $form  = 'durationform';
    $focus = '.min_value';

    $field_name    = ustrcut($_REQUEST['field_name'], MAX_FIELD_NAME);
    $field_type    = FIELD_TYPE_DURATION;
    $min_value     = ustrcut($_REQUEST['min_value'], ustrlen(time2ustr(MAX_FIELD_DURATION)));
    $max_value     = ustrcut($_REQUEST['max_value'], ustrlen(time2ustr(MAX_FIELD_DURATION)));
    $def_value     = ustrcut($_REQUEST['def_value'], ustrlen(time2ustr(MAX_FIELD_DURATION)));
    $def_value     = (ustrlen($def_value) == 0 ? NULL : $def_value);
    $is_required   = isset($_REQUEST['is_required']);
    $add_separator = isset($_REQUEST['add_separator']);
    $guest_access  = isset($_REQUEST['guest_access']);

    $error = field_validate_duration($field_name, $min_value, $max_value, $def_value);

    if ($error == NO_ERROR)
    {
        $error = field_create($id,
                              $field_name,
                              $field_type,
                              $is_required,
                              $add_separator,
                              $guest_access,
                              NULL, NULL, NULL,
                              ustr2time($min_value),
                              ustr2time($max_value),
                              is_null($def_value) ? NULL : ustr2time($def_value));

        if ($error == NO_ERROR)
        {
            header('Location: findex.php?id=' . $id);
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
            $alert = ustrprocess(get_js_resource(RES_ALERT_DEFAULT_VALUE_OUT_OF_RANGE_ID), $min_value, $max_value);
            break;
        default:
            $alert = NULL;
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $form  = 'mainform';
    $focus = '.field_name';

    $field_name    = NULL;
    $field_type    = FIELD_TYPE_MINIMUM;
    $add_separator = FALSE;
    $guest_access  = FALSE;
}

$xml = '<page' . gen_xml_page_header(ustrprocess(get_html_resource(RES_NEW_FIELD_ID), ($form == 'mainform' ? 1 : 2), 2), isset($alert) ? $alert : NULL, $form . $focus) . '>'
     . gen_xml_menu()
     . '<path>'
     . '<pathitem url="index.php">'                                    . get_html_resource(RES_PROJECTS_ID)                                                    . '</pathitem>'
     . '<pathitem url="view.php?id='    . $state['project_id']  . '">' . ustrprocess(get_html_resource(RES_PROJECT_X_ID), ustr2html($state['project_name']))   . '</pathitem>'
     . '<pathitem url="tindex.php?id='  . $state['project_id']  . '">' . get_html_resource(RES_TEMPLATES_ID)                                                   . '</pathitem>'
     . '<pathitem url="tview.php?id='   . $state['template_id'] . '">' . ustrprocess(get_html_resource(RES_TEMPLATE_X_ID), ustr2html($state['template_name'])) . '</pathitem>'
     . '<pathitem url="sindex.php?id='  . $state['template_id'] . '">' . get_html_resource(RES_STATES_ID)                                                      . '</pathitem>'
     . '<pathitem url="sview.php?id='   . $id                   . '">' . ustrprocess(get_html_resource(RES_STATE_X_ID), ustr2html($state['state_name']))       . '</pathitem>'
     . '<pathitem url="findex.php?id='  . $id                   . '">' . get_html_resource(RES_FIELDS_ID)                                                      . '</pathitem>'
     . '<pathitem url="fcreate.php?id=' . $id                   . '">' . ustrprocess(get_html_resource(RES_NEW_FIELD_ID), ($form == 'mainform' ? 1 : 2), 2)    . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<form name="' . $form . '" action="fcreate.php?id=' . $id . '">'
     . '<group title="' . get_html_resource(RES_FIELD_INFO_ID) . '">'
     . '<editbox  label="' . get_html_resource(RES_FIELD_NAME_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="field_name" size="' . HTML_EDITBOX_SIZE_MEDIUM . '" maxlen="' . MAX_FIELD_NAME . '">' . ustr2html($field_name) . '</editbox>'
     . '<combobox label="' . get_html_resource(RES_FIELD_TYPE_ID) . '" name="field_type"' . ($form == 'mainform' ? '>' : ' disabled="true">');

for ($i = FIELD_TYPE_MINIMUM; $i <= FIELD_TYPE_MAXIMUM; $i++)
{
    $xml .= '<listitem value="' . $i . '"' . ($field_type == $i ? ' selected="true">' : '>')
          . get_html_resource($field_type_res[$i])
          . '</listitem>';
}

$xml .= '</combobox>';

if ($form == 'numberform')
{
    $xml .= '<editbox label="' . get_html_resource(RES_MIN_VALUE_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="min_value" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . (ustrlen(MAX_FIELD_INTEGER) + 1) . '">' . ustr2html($min_value) . '</editbox>'
          . '<editbox label="' . get_html_resource(RES_MAX_VALUE_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="max_value" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . (ustrlen(MAX_FIELD_INTEGER) + 1) . '">' . ustr2html($max_value) . '</editbox>'
          . '<editbox label="' . get_html_resource(RES_DEFAULT_VALUE_ID)                                                    . '" name="def_value" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . (ustrlen(MAX_FIELD_INTEGER) + 1) . '">' . ustr2html($def_value) . '</editbox>';
}
elseif ($form == 'stringform')
{
    $xml .= '<editbox label="' . get_html_resource(RES_MAX_LENGTH_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="max_length" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(MAX_FIELD_STRING) . '">' . ustr2html($max_length) . '</editbox>'
          . '<editbox label="' . get_html_resource(RES_DEFAULT_VALUE_ID)                                                     . '" name="def_value"  size="' . HTML_EDITBOX_SIZE_LONG  . '" maxlen="' . MAX_FIELD_STRING          . '">' . ustr2html($def_value)  . '</editbox>';
}
elseif ($form == 'multilinedform')
{
    $xml .= '<editbox label="' . get_html_resource(RES_MAX_LENGTH_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="max_length" size="' . HTML_EDITBOX_SIZE_SMALL                                  . '" maxlen="' . ustrlen(MAX_FIELD_MULTILINED) . '">' . ustr2html($max_length) . '</editbox>'
          . '<textbox label="' . get_html_resource(RES_DEFAULT_VALUE_ID)                                                     . '" name="def_value"  width="' . HTML_TEXTBOX_WIDTH . '" height="' . HTML_TEXTBOX_HEIGHT . '" maxlen="' . MAX_FIELD_MULTILINED          . '">' . ustr2html($def_value)  . '</textbox>';
}
elseif ($form == 'checkboxform')
{
    $xml .= '<radios name="def_value" label="' . get_html_resource(RES_DEFAULT_VALUE_ID) . '">'
          . '<radio name="def_value" value="1"' . ($def_value != 0 ? ' checked="true">' : '>') . get_html_resource(RES_ON_ID)  . '</radio>'
          . '<radio name="def_value" value="0"' . ($def_value == 0 ? ' checked="true">' : '>') . get_html_resource(RES_OFF_ID) . '</radio>'
          . '</radios>';
}
elseif ($form == 'listform')
{
    $xml .= '<textbox label="' . get_html_resource(RES_LIST_ITEMS_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="list_items" width="' . HTML_TEXTBOX_WIDTH . '" height="' . HTML_TEXTBOX_HEIGHT . '" maxlen="' . MAX_FIELD_LIST_ITEMS . '">' . ustr2html($list_items) . '</textbox>'
          . '<editbox label="' . get_html_resource(RES_DEFAULT_VALUE_ID)                                                     . '" name="def_value"  size="' . HTML_EDITBOX_SIZE_SMALL . '"                                  maxlen="' . ustrlen(MAXINT)      . '">' . ustr2html($def_value)  . '</editbox>';
}
elseif ($form == 'dateform')
{
    $xml .= '<editbox label="' . get_html_resource(RES_MIN_VALUE_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="min_value" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(MIN_FIELD_DATE) . '">' . ustr2html($min_value) . '</editbox>'
          . '<editbox label="' . get_html_resource(RES_MAX_VALUE_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="max_value" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(MIN_FIELD_DATE) . '">' . ustr2html($max_value) . '</editbox>'
          . '<editbox label="' . get_html_resource(RES_DEFAULT_VALUE_ID)                                                    . '" name="def_value" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(MIN_FIELD_DATE) . '">' . ustr2html($def_value) . '</editbox>';
}
elseif ($form == 'durationform')
{
    $xml .= '<editbox label="' . get_html_resource(RES_MIN_VALUE_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="min_value" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(time2ustr(MAX_FIELD_DURATION)) . '">' . ustr2html($min_value) . '</editbox>'
          . '<editbox label="' . get_html_resource(RES_MAX_VALUE_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="max_value" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(time2ustr(MAX_FIELD_DURATION)) . '">' . ustr2html($max_value) . '</editbox>'
          . '<editbox label="' . get_html_resource(RES_DEFAULT_VALUE_ID)                                                    . '" name="def_value" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(time2ustr(MAX_FIELD_DURATION)) . '">' . ustr2html($def_value) . '</editbox>';
}

if ($form != 'mainform' &&
    $form != 'checkboxform')
{
    $xml .= '<checkbox name="is_required"' . ($is_required ? ' checked="true">' : '>') . get_html_resource(RES_REQUIRED2_ID) . '</checkbox>';
}

$xml .= '<checkbox name="add_separator"' . ($add_separator ? ' checked="true">' : '>') . ustrtolower(get_html_resource(RES_ADD_SEPARATOR_ID)) . '</checkbox>'
      . '<checkbox name="guest_access"'  . ($guest_access  ? ' checked="true">' : '>') . ustrtolower(get_html_resource(RES_GUEST_ACCESS_ID))  . '</checkbox>';

if (($form == 'stringform') || ($form == 'multilinedform'))
{
    $xml .= '<editbox label="' . get_html_resource(RES_REGEX_CHECK_ID)   . '" name="regex_check"   size="' . HTML_EDITBOX_SIZE_LONG  . '" maxlen="' . MAX_FIELD_REGEX . '">' . ustr2html($regex_check)   . '</editbox>'
          . '<editbox label="' . get_html_resource(RES_REGEX_SEARCH_ID)  . '" name="regex_search"  size="' . HTML_EDITBOX_SIZE_LONG  . '" maxlen="' . MAX_FIELD_REGEX . '">' . ustr2html($regex_search)  . '</editbox>'
          . '<editbox label="' . get_html_resource(RES_REGEX_REPLACE_ID) . '" name="regex_replace" size="' . HTML_EDITBOX_SIZE_LONG  . '" maxlen="' . MAX_FIELD_REGEX . '">' . ustr2html($regex_replace) . '</editbox>';
}

$xml .= '</group>'
      . '<button default="true">'                  . get_html_resource($form == 'mainform' ? RES_NEXT_ID : RES_OK_ID) . '</button>'
      . '<button url="findex.php?id=' . $id . '">' . get_html_resource(RES_CANCEL_ID)                                 . '</button>'
      . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>';

if ($form == 'numberform')
{
    $xml .= '<note>' . ustrprocess(get_html_resource(RES_ALERT_INTEGER_VALUE_OUT_OF_RANGE_ID), -MAX_FIELD_INTEGER, +MAX_FIELD_INTEGER) . '</note>'
          . '<note>' . get_html_resource(RES_ALERT_MIN_MAX_VALUES_ID)                                                                  . '</note>';
}
elseif ($form == 'stringform')
{
    $xml .= '<note>' . ustrprocess(get_html_resource(RES_ALERT_INTEGER_VALUE_OUT_OF_RANGE_ID), 1, MAX_FIELD_STRING) . '</note>';
}
elseif ($form == 'multilinedform')
{
    $xml .= '<note>' . ustrprocess(get_html_resource(RES_ALERT_INTEGER_VALUE_OUT_OF_RANGE_ID), 1, MAX_FIELD_MULTILINED) . '</note>';
}
elseif ($form == 'dateform')
{
    $xml .= '<note>' . ustrprocess(get_html_resource(RES_ALERT_DATE_VALUE_OUT_OF_RANGE_ID),    MIN_FIELD_DATE, MAX_FIELD_DATE) . '</note>'
          . '<note>' . ustrprocess(get_html_resource(RES_ALERT_DEFAULT_VALUE_OUT_OF_RANGE_ID), MIN_FIELD_DATE, MAX_FIELD_DATE) . '</note>'
          . '<note>' . get_html_resource(RES_ALERT_MIN_MAX_VALUES_ID) . '</note>';
}
elseif ($form == 'durationform')
{
    $xml .= '<note>' . ustrprocess(get_html_resource(RES_ALERT_TIME_VALUE_OUT_OF_RANGE_ID), time2ustr(MIN_FIELD_DURATION), time2ustr(MAX_FIELD_DURATION)) . '</note>'
          . '<note>' . get_html_resource(RES_ALERT_MIN_MAX_VALUES_ID)                                                                                     . '</note>';
}

$xml .= '</form>'
      . '</content>'
      . '</page>';

echo(xml2html($xml));

?>
