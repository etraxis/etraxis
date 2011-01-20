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

// check that requested state exists

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

// 1st step of new field has been submitted

if (try_request('submitted') == 'mainform')
{
    debug_write_log(DEBUG_NOTICE, 'Data for step #1 are submitted.');

    $field_name    = ustrcut($_REQUEST['field_name'], MAX_FIELD_NAME);
    $field_type    = ustr2int($_REQUEST['field_type'], FIELD_TYPE_MINIMUM, FIELD_TYPE_MAXIMUM);
    $is_required   = FALSE;
    $guest_access  = isset($_REQUEST['guest_access']);
    $add_separator = isset($_REQUEST['add_separator']);
    $description   = NULL;

    switch ($field_type)
    {
        case FIELD_TYPE_NUMBER:
            $form      = 'numberform';
            $min_value = NULL;
            $max_value = NULL;
            $def_value = NULL;
            break;

        case FIELD_TYPE_STRING:
            $form          = 'stringform';
            $max_length    = NULL;
            $regex_check   = NULL;
            $regex_search  = NULL;
            $regex_replace = NULL;
            $def_value     = NULL;
            break;

        case FIELD_TYPE_MULTILINED:
            $form          = 'multilinedform';
            $max_length    = NULL;
            $regex_check   = NULL;
            $regex_search  = NULL;
            $regex_replace = NULL;
            $def_value     = NULL;
            break;

        case FIELD_TYPE_CHECKBOX:
            $form      = 'checkboxform';
            $def_value = 1;
            break;

        case FIELD_TYPE_LIST:
            $form       = 'listform';
            $list_items = NULL;
            $def_value  = NULL;
            break;

        case FIELD_TYPE_RECORD:
            $form      = 'recordform';
            $min_value = NULL;
            $max_value = NULL;
            break;

        case FIELD_TYPE_DATE:
            $form      = 'dateform';
            $min_value = NULL;
            $max_value = NULL;
            $def_value = NULL;
            break;

        case FIELD_TYPE_DURATION:
            $form      = 'durationform';
            $min_value = NULL;
            $max_value = NULL;
            $def_value = NULL;
            break;

        default: ;  // nop
    }
}

// 2st step of new field has been submitted

elseif (isset($_REQUEST['submitted']))
{
    debug_write_log(DEBUG_NOTICE, 'Data for step #2 are submitted.');

    // 2nd step of new field (number) has been submitted

    if (try_request('submitted') == 'numberform')
    {
        debug_write_log(DEBUG_NOTICE, 'Data for step #2 (number) are submitted.');

        $field_name    = ustrcut($_REQUEST['field_name'], MAX_FIELD_NAME);
        $field_type    = FIELD_TYPE_NUMBER;
        $min_value     = ustrcut($_REQUEST['min_value'], ustrlen(MAX_FIELD_INTEGER) + 1);
        $max_value     = ustrcut($_REQUEST['max_value'], ustrlen(MAX_FIELD_INTEGER) + 1);
        $def_value     = ustrcut($_REQUEST['def_value'], ustrlen(MAX_FIELD_INTEGER) + 1);
        $def_value     = (ustrlen($def_value) == 0 ? NULL : intval($def_value));
        $is_required   = isset($_REQUEST['is_required']);
        $guest_access  = isset($_REQUEST['guest_access']);
        $add_separator = isset($_REQUEST['add_separator']);
        $description   = ustrcut($_REQUEST['description'], MAX_FIELD_DESCRIPTION);

        $error = field_validate_number($field_name, $min_value, $max_value, $def_value);

        if ($error == NO_ERROR)
        {
            $error = field_create($id,
                                  $field_name,
                                  $field_type,
                                  $is_required,
                                  $add_separator,
                                  $guest_access,
                                  $description,
                                  NULL, NULL, NULL,
                                  $min_value,
                                  $max_value,
                                  $def_value);
        }
    }

    // 2nd step of new field (string) has been submitted

    elseif (try_request('submitted') == 'stringform')
    {
        debug_write_log(DEBUG_NOTICE, 'Data for step #2 (string) are submitted.');

        $field_name    = ustrcut($_REQUEST['field_name'], MAX_FIELD_NAME);
        $field_type    = FIELD_TYPE_STRING;
        $max_length    = ustrcut($_REQUEST['max_length'], ustrlen(MAX_FIELD_STRING));
        $is_required   = isset($_REQUEST['is_required']);
        $guest_access  = isset($_REQUEST['guest_access']);
        $add_separator = isset($_REQUEST['add_separator']);
        $description   = ustrcut($_REQUEST['description'],   MAX_FIELD_DESCRIPTION);
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
                                  $description,
                                  $regex_check,
                                  $regex_search,
                                  $regex_replace,
                                  $max_length,
                                  NULL,
                                  $value_id);
        }
    }

    // 2nd step of new field (multilined) has been submitted

    elseif (try_request('submitted') == 'multilinedform')
    {
        debug_write_log(DEBUG_NOTICE, 'Data for step #2 (multilined text) are submitted.');

        $field_name    = ustrcut($_REQUEST['field_name'], MAX_FIELD_NAME);
        $field_type    = FIELD_TYPE_MULTILINED;
        $max_length    = ustrcut($_REQUEST['max_length'], ustrlen(MAX_FIELD_MULTILINED));
        $is_required   = isset($_REQUEST['is_required']);
        $guest_access  = isset($_REQUEST['guest_access']);
        $add_separator = isset($_REQUEST['add_separator']);
        $description   = ustrcut($_REQUEST['description'],   MAX_FIELD_DESCRIPTION);
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
                                  $description,
                                  $regex_check,
                                  $regex_search,
                                  $regex_replace,
                                  $max_length,
                                  NULL,
                                  $value_id);
        }
    }

    // 2nd step of new field (checkbox) has been submitted

    elseif (try_request('submitted') == 'checkboxform')
    {
        debug_write_log(DEBUG_NOTICE, 'Data for step #2 (checkbox) are submitted.');

        $field_name    = ustrcut($_REQUEST['field_name'], MAX_FIELD_NAME);
        $field_type    = FIELD_TYPE_CHECKBOX;
        $def_value     = ustr2int(try_request('def_value', 1), 0, 1);
        $guest_access  = isset($_REQUEST['guest_access']);
        $add_separator = isset($_REQUEST['add_separator']);
        $description   = ustrcut($_REQUEST['description'], MAX_FIELD_DESCRIPTION);

        $error = field_create($id,
                              $field_name,
                              $field_type,
                              FALSE,
                              $add_separator,
                              $guest_access,
                              $description,
                              NULL, NULL, NULL, NULL, NULL,
                              $def_value);
    }

    // 2nd step of new field (list) has been submitted

    elseif (try_request('submitted') == 'listform')
    {
        debug_write_log(DEBUG_NOTICE, 'Data for step #2 (list) are submitted.');

        $field_name    = ustrcut($_REQUEST['field_name'], MAX_FIELD_NAME);
        $field_type    = FIELD_TYPE_LIST;
        $list_items    = ustrcut($_REQUEST['list_items'], MAX_FIELD_LIST_ITEMS);
        $def_value     = try_request('def_value');
        $def_value     = (ustrlen($def_value) == 0 ? NULL : ustr2int($def_value, 1, MAXINT));
        $is_required   = isset($_REQUEST['is_required']);
        $guest_access  = isset($_REQUEST['guest_access']);
        $add_separator = isset($_REQUEST['add_separator']);
        $description   = ustrcut($_REQUEST['description'], MAX_FIELD_DESCRIPTION);

        $error = field_create($id,
                              $field_name,
                              $field_type,
                              $is_required,
                              $add_separator,
                              $guest_access,
                              $description,
                              NULL, NULL, NULL, NULL, NULL,
                              $def_value);
    }

    // 2nd step of new field (record) has been submitted

    elseif (try_request('submitted') == 'recordform')
    {
        debug_write_log(DEBUG_NOTICE, 'Data for step #2 (record) are submitted.');

        $field_name    = ustrcut($_REQUEST['field_name'], MAX_FIELD_NAME);
        $field_type    = FIELD_TYPE_RECORD;
        $is_required   = isset($_REQUEST['is_required']);
        $guest_access  = isset($_REQUEST['guest_access']);
        $add_separator = isset($_REQUEST['add_separator']);
        $description   = ustrcut($_REQUEST['description'], MAX_FIELD_DESCRIPTION);

        $error = field_create($id,
                              $field_name,
                              $field_type,
                              $is_required,
                              $add_separator,
                              $guest_access,
                              $description);
    }

    // 2nd step of new field (date) has been submitted

    elseif (try_request('submitted') == 'dateform')
    {
        debug_write_log(DEBUG_NOTICE, 'Data for step #2 (date) are submitted.');

        $field_name    = ustrcut($_REQUEST['field_name'], MAX_FIELD_NAME);
        $field_type    = FIELD_TYPE_DATE;
        $min_value     = ustrcut($_REQUEST['min_value'], ustrlen(MIN_FIELD_DATE));
        $max_value     = ustrcut($_REQUEST['max_value'], ustrlen(MIN_FIELD_DATE));
        $def_value     = ustrcut($_REQUEST['def_value'], ustrlen(MIN_FIELD_DATE));
        $def_value     = (ustrlen($def_value) == 0 ? NULL : $def_value);
        $is_required   = isset($_REQUEST['is_required']);
        $guest_access  = isset($_REQUEST['guest_access']);
        $add_separator = isset($_REQUEST['add_separator']);
        $description   = ustrcut($_REQUEST['description'], MAX_FIELD_DESCRIPTION);

        $error = field_validate_date($field_name, $min_value, $max_value, $def_value);

        if ($error == NO_ERROR)
        {
            $error = field_create($id,
                                  $field_name,
                                  $field_type,
                                  $is_required,
                                  $add_separator,
                                  $guest_access,
                                  $description,
                                  NULL, NULL, NULL,
                                  $min_value,
                                  $max_value,
                                  is_null($def_value) ? NULL : ustr2int($def_value, MIN_FIELD_DATE, MAX_FIELD_DATE));
        }
    }

    // 2nd step of new field (duration) has been submitted

    elseif (try_request('submitted') == 'durationform')
    {
        debug_write_log(DEBUG_NOTICE, 'Data for step #2 (duration) are submitted.');

        $field_name    = ustrcut($_REQUEST['field_name'], MAX_FIELD_NAME);
        $field_type    = FIELD_TYPE_DURATION;
        $min_value     = ustrcut($_REQUEST['min_value'], ustrlen(time2ustr(MAX_FIELD_DURATION)));
        $max_value     = ustrcut($_REQUEST['max_value'], ustrlen(time2ustr(MAX_FIELD_DURATION)));
        $def_value     = ustrcut($_REQUEST['def_value'], ustrlen(time2ustr(MAX_FIELD_DURATION)));
        $def_value     = (ustrlen($def_value) == 0 ? NULL : $def_value);
        $is_required   = isset($_REQUEST['is_required']);
        $guest_access  = isset($_REQUEST['guest_access']);
        $add_separator = isset($_REQUEST['add_separator']);
        $description   = ustrcut($_REQUEST['description'], MAX_FIELD_DESCRIPTION);

        $error = field_validate_duration($field_name, $min_value, $max_value, $def_value);

        if ($error == NO_ERROR)
        {
            $error = field_create($id,
                                  $field_name,
                                  $field_type,
                                  $is_required,
                                  $add_separator,
                                  $guest_access,
                                  $description,
                                  NULL, NULL, NULL,
                                  ustr2time($min_value),
                                  ustr2time($max_value),
                                  is_null($def_value) ? NULL : ustr2time($def_value));
        }
    }

    switch ($error)
    {
        case NO_ERROR:
            header('HTTP/1.0 200 OK');
            break;

        case ERROR_INCOMPLETE_FORM:
            header('HTTP/1.0 500 ' . get_js_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID));
            break;

        case ERROR_ALREADY_EXISTS:
            header('HTTP/1.0 500 ' . get_js_resource(RES_ALERT_FIELD_ALREADY_EXISTS_ID));
            break;

        case ERROR_INVALID_INTEGER_VALUE:
            header('HTTP/1.0 500 ' . get_js_resource(RES_ALERT_INVALID_INTEGER_VALUE_ID));
            break;

        case ERROR_INTEGER_VALUE_OUT_OF_RANGE:

            if (try_request('submitted') == 'numberform')
            {
                header('HTTP/1.0 500 ' . ustrprocess(get_js_resource(RES_ALERT_INTEGER_VALUE_OUT_OF_RANGE_ID), -MAX_FIELD_INTEGER, +MAX_FIELD_INTEGER));
            }
            elseif (try_request('submitted') == 'stringform')
            {
                header('HTTP/1.0 500 ' . ustrprocess(get_js_resource(RES_ALERT_INTEGER_VALUE_OUT_OF_RANGE_ID), 1, MAX_FIELD_STRING));
            }
            elseif (try_request('submitted') == 'multilinedform')
            {
                header('HTTP/1.0 500 ' . ustrprocess(get_js_resource(RES_ALERT_INTEGER_VALUE_OUT_OF_RANGE_ID), 1, MAX_FIELD_MULTILINED));
            }
            else
            {
                header('HTTP/1.0 500 ' . get_js_resource(RES_ALERT_UNKNOWN_ERROR_ID));
            }

            break;

        case ERROR_MIN_MAX_VALUES:
            header('HTTP/1.0 500 ' . get_js_resource(RES_ALERT_MIN_MAX_VALUES_ID));
            break;

        case ERROR_INVALID_DATE_VALUE:
            header('HTTP/1.0 500 ' . get_js_resource(RES_ALERT_INVALID_DATE_VALUE_ID));
            break;

        case ERROR_DATE_VALUE_OUT_OF_RANGE:
            header('HTTP/1.0 500 ' . ustrprocess(get_js_resource(RES_ALERT_DATE_VALUE_OUT_OF_RANGE_ID), MIN_FIELD_DATE, MAX_FIELD_DATE));
            break;

        case ERROR_INVALID_TIME_VALUE:
            header('HTTP/1.0 500 ' . get_js_resource(RES_ALERT_INVALID_TIME_VALUE_ID));
            break;

        case ERROR_TIME_VALUE_OUT_OF_RANGE:
            header('HTTP/1.0 500 ' . ustrprocess(get_js_resource(RES_ALERT_TIME_VALUE_OUT_OF_RANGE_ID), time2ustr(MIN_FIELD_DURATION), time2ustr(MAX_FIELD_DURATION)));
            break;

        case ERROR_DEFAULT_VALUE_OUT_OF_RANGE:
            header('HTTP/1.0 500 ' . ustrprocess(get_js_resource(RES_ALERT_DEFAULT_VALUE_OUT_OF_RANGE_ID), $min_value, $max_value));
            break;

        default:
            header('HTTP/1.0 500 ' . get_js_resource(RES_ALERT_UNKNOWN_ERROR_ID));
    }

    exit;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $error         = NO_ERROR;
    $form          = 'mainform';
    $field_name    = NULL;
    $field_type    = FIELD_TYPE_MINIMUM;
    $guest_access  = FALSE;
    $add_separator = FALSE;
}

// local JS functions

$resTitle = get_js_resource(RES_ERROR_ID);
$resOK    = get_js_resource(RES_OK_ID);

$xml = <<<JQUERY
<script>

function createSuccess ()
{
    closeModal();
    reloadTab();
}

function createError (XMLHttpRequest)
{
    jqAlert("{$resTitle}", XMLHttpRequest.statusText, "{$resOK}");
}

</script>
JQUERY;

// generate header

$xml .= '<form name="' . $form . '" action="fcreate.php?id=' . $id . '" success="createSuccess" error="createError">'
      . '<group>';

// generate common controls

$xml .= '<control name="field_name" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
      . '<label>' . get_html_resource(RES_FIELD_NAME_ID) . '</label>'
      . '<editbox maxlen="' . MAX_FIELD_NAME . '">' . ustr2html($field_name) . '</editbox>'
      . '</control>'
      . '<control name="field_type" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
      . '<label>' . get_html_resource(RES_FIELD_TYPE_ID) . '</label>'
      . '<combobox>';

if ($form == 'mainform')
{
    for ($i = FIELD_TYPE_MINIMUM; $i <= FIELD_TYPE_MAXIMUM; $i++)
    {
        $xml .= ($field_type == $i
                    ? '<listitem value="' . $i . '" selected="true">'
                    : '<listitem value="' . $i . '">')
              . get_html_resource($field_type_res[$i])
              . '</listitem>';
    }
}
else
{
    $xml .= '<listitem value="' . $field_type . '" selected="true">'
          . get_html_resource($field_type_res[$field_type])
          . '</listitem>';
}

$xml .= '</combobox>'
      . '</control>';

$notes = '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>';

// generate controls for 'number' field

if ($form == 'numberform')
{
    $xml .= '<control name="min_value" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
          . '<label>' . get_html_resource(RES_MIN_VALUE_ID) . '</label>'
          . '<editbox maxlen="' . (ustrlen(MAX_FIELD_INTEGER) + 1) . '">'
          . ustr2html($min_value)
          . '</editbox>'
          . '</control>';

    $xml .= '<control name="max_value" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
          . '<label>' . get_html_resource(RES_MAX_VALUE_ID) . '</label>'
          . '<editbox maxlen="' . (ustrlen(MAX_FIELD_INTEGER) + 1) . '">'
          . ustr2html($max_value)
          . '</editbox>'
          . '</control>';

    $xml .= '<control name="def_value">'
          . '<label>' . get_html_resource(RES_DEFAULT_VALUE_ID) . '</label>'
          . '<editbox maxlen="' . (ustrlen(MAX_FIELD_INTEGER) + 1) . '">'
          . ustr2html($def_value)
          . '</editbox>'
          . '</control>';

    $notes .= '<note>' . ustrprocess(get_html_resource(RES_ALERT_INTEGER_VALUE_OUT_OF_RANGE_ID), -MAX_FIELD_INTEGER, +MAX_FIELD_INTEGER) . '</note>'
            . '<note>' . get_html_resource(RES_ALERT_MIN_MAX_VALUES_ID) . '</note>';
}

// generate controls for 'string' field

elseif ($form == 'stringform')
{
    $xml .= '<control name="max_length" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
          . '<label>' . get_html_resource(RES_MAX_LENGTH_ID) . '</label>'
          . '<editbox maxlen="' . ustrlen(MAX_FIELD_STRING) . '">'
          . ustr2html($max_length)
          . '</editbox>'
          . '</control>';

    $xml .= '<control name="def_value">'
          . '<label>' . get_html_resource(RES_DEFAULT_VALUE_ID) . '</label>'
          . '<editbox maxlen="' . MAX_FIELD_STRING . '">'
          . ustr2html($def_value)
          . '</editbox>'
          . '</control>';

    $xml .= '<control name="regex_check">'
          . '<label>' . get_html_resource(RES_REGEX_CHECK_ID) . '</label>'
          . '<editbox maxlen="' . MAX_FIELD_REGEX . '">'
          . ustr2html($regex_check)
          . '</editbox>'
          . '</control>';

    $xml .= '<control name="regex_search">'
          . '<label>' . get_html_resource(RES_REGEX_SEARCH_ID) . '</label>'
          . '<editbox maxlen="' . MAX_FIELD_REGEX . '">'
          . ustr2html($regex_search)
          . '</editbox>'
          . '</control>';

    $xml .= '<control name="regex_replace">'
          . '<label>' . get_html_resource(RES_REGEX_REPLACE_ID) . '</label>'
          . '<editbox maxlen="' . MAX_FIELD_REGEX . '">'
          . ustr2html($regex_replace)
          . '</editbox>'
          . '</control>';

    $notes .= '<note>' . ustrprocess(get_html_resource(RES_ALERT_INTEGER_VALUE_OUT_OF_RANGE_ID), 1, MAX_FIELD_STRING) . '</note>';
}

// generate controls for 'multilined' field

elseif ($form == 'multilinedform')
{
    $xml .= '<control name="max_length" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
          . '<label>' . get_html_resource(RES_MAX_LENGTH_ID) . '</label>'
          . '<editbox maxlen="' . ustrlen(MAX_FIELD_MULTILINED) . '">'
          . ustr2html($max_length)
          . '</editbox>'
          . '</control>';

    $xml .= '<control name="def_value">'
          . '<label>' . get_html_resource(RES_DEFAULT_VALUE_ID) . '</label>'
          . '<textbox rows="' . $_SESSION[VAR_TEXTROWS] . '" maxlen="' . MAX_FIELD_MULTILINED . '">'
          . ustr2html($def_value)
          . '</textbox>'
          . '</control>';

    $xml .= '<control name="regex_check">'
          . '<label>' . get_html_resource(RES_REGEX_CHECK_ID) . '</label>'
          . '<editbox maxlen="' . MAX_FIELD_REGEX . '">'
          . ustr2html($regex_check)
          . '</editbox>'
          . '</control>';

    $xml .= '<control name="regex_search">'
          . '<label>' . get_html_resource(RES_REGEX_SEARCH_ID) . '</label>'
          . '<editbox maxlen="' . MAX_FIELD_REGEX . '">'
          . ustr2html($regex_search)
          . '</editbox>'
          . '</control>';

    $xml .= '<control name="regex_replace">'
          . '<label>' . get_html_resource(RES_REGEX_REPLACE_ID) . '</label>'
          . '<editbox maxlen="' . MAX_FIELD_REGEX . '">'
          . ustr2html($regex_replace)
          . '</editbox>'
          . '</control>';

    $notes .= '<note>' . ustrprocess(get_html_resource(RES_ALERT_INTEGER_VALUE_OUT_OF_RANGE_ID), 1, MAX_FIELD_MULTILINED) . '</note>';
}

// generate controls for 'checkbox' field

elseif ($form == 'checkboxform')
{
    $xml .= '<control name="def_value">'
          . '<label>' . get_html_resource(RES_DEFAULT_VALUE_ID) . '</label>'
          . '<radio value="1"' . ($def_value != 0 ? ' checked="true">' : '>') . get_html_resource(RES_ON_ID)  . '</radio>'
          . '<radio value="0"' . ($def_value == 0 ? ' checked="true">' : '>') . get_html_resource(RES_OFF_ID) . '</radio>'
          . '</control>';
}

// generate controls for 'list' field

elseif ($form == 'listform')
{
    $xml .= '<control name="list_items" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
          . '<label>' . get_html_resource(RES_LIST_ITEMS_ID) . '</label>'
          . '<textbox rows="' . $_SESSION[VAR_TEXTROWS] . '" maxlen="' . MAX_FIELD_LIST_ITEMS . '">'
          . ustr2html($list_items)
          . '</textbox>'
          . '</control>';

    $xml .= '<control name="def_value">'
          . '<label>' . get_html_resource(RES_DEFAULT_VALUE_ID) . '</label>'
          . '<editbox maxlen="' . ustrlen(MAXINT) . '">'
          . ustr2html($def_value)
          . '</editbox>'
          . '</control>';
}

// generate controls for 'date' field

elseif ($form == 'dateform')
{
    $xml .= '<control name="min_value" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
          . '<label>' . get_html_resource(RES_MIN_VALUE_ID) . '</label>'
          . '<editbox maxlen="' . (ustrlen(MAX_FIELD_DATE) + 1) . '">'
          . ustr2html($min_value)
          . '</editbox>'
          . '</control>';

    $xml .= '<control name="max_value" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
          . '<label>' . get_html_resource(RES_MAX_VALUE_ID) . '</label>'
          . '<editbox maxlen="' . ustrlen(MAX_FIELD_DATE) . '">'
          . ustr2html($max_value)
          . '</editbox>'
          . '</control>';

    $xml .= '<control name="def_value">'
          . '<label>' . get_html_resource(RES_DEFAULT_VALUE_ID) . '</label>'
          . '<editbox maxlen="' . (ustrlen(MAX_FIELD_DATE) + 1) . '">'
          . ustr2html($def_value)
          . '</editbox>'
          . '</control>';

    $notes .= '<note>' . ustrprocess(get_html_resource(RES_ALERT_DATE_VALUE_OUT_OF_RANGE_ID),    MIN_FIELD_DATE, MAX_FIELD_DATE) . '</note>'
            . '<note>' . ustrprocess(get_html_resource(RES_ALERT_DEFAULT_VALUE_OUT_OF_RANGE_ID), MIN_FIELD_DATE, MAX_FIELD_DATE) . '</note>'
            . '<note>' . get_html_resource(RES_ALERT_MIN_MAX_VALUES_ID) . '</note>';
}

// generate controls for 'duration' field

elseif ($form == 'durationform')
{
    $xml .= '<control name="min_value" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
          . '<label>' . get_html_resource(RES_MIN_VALUE_ID) . '</label>'
          . '<editbox maxlen="' . ustrlen(time2ustr(MAX_FIELD_DURATION)) . '">'
          . ustr2html($min_value)
          . '</editbox>'
          . '</control>';

    $xml .= '<control name="max_value" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
          . '<label>' . get_html_resource(RES_MAX_VALUE_ID) . '</label>'
          . '<editbox maxlen="' . ustrlen(time2ustr(MAX_FIELD_DURATION)) . '">'
          . ustr2html($max_value)
          . '</editbox>'
          . '</control>';

    $xml .= '<control name="def_value">'
          . '<label>' . get_html_resource(RES_DEFAULT_VALUE_ID) . '</label>'
          . '<editbox maxlen="' . ustrlen(time2ustr(MAX_FIELD_DURATION)) . '">'
          . ustr2html($def_value)
          . '</editbox>'
          . '</control>';

    $notes .= '<note>' . ustrprocess(get_html_resource(RES_ALERT_TIME_VALUE_OUT_OF_RANGE_ID), time2ustr(MIN_FIELD_DURATION), time2ustr(MAX_FIELD_DURATION)) . '</note>'
            . '<note>' . get_html_resource(RES_ALERT_MIN_MAX_VALUES_ID) . '</note>';
}

// generate common controls

if ($form != 'mainform')
{
    $xml .= '<control name="description">'
          . '<label>' . get_html_resource(RES_DESCRIPTION_ID) . '</label>'
          . '<textbox rows="' . $_SESSION[VAR_TEXTROWS] . '" maxlen="' . MAX_FIELD_DESCRIPTION . '">'
          . ustr2html($description)
          . '</textbox>'
          . '</control>';

    if ($form != 'checkboxform')
    {
        $xml .= '<control name="is_required">'
              . '<label/>'
              . ($is_required
                    ? '<checkbox checked="true">'
                    : '<checkbox>')
              . ustrtolower(get_html_resource(RES_REQUIRED2_ID))
              . '</checkbox>'
              . '</control>';
    }
}

$xml .= '<control name="guest_access">'
      . '<label/>'
      . ($guest_access
            ? '<checkbox checked="true">'
            : '<checkbox>')
      . ustrtolower(get_html_resource(RES_GUEST_ACCESS_ID))
      . '</checkbox>'
      . '</control>';

$xml .= '<control name="add_separator">'
      . '<label/>'
      . ($add_separator
            ? '<checkbox checked="true">'
            : '<checkbox>')
      . ustrtolower(get_html_resource(RES_ADD_SEPARATOR_ID))
      . '</checkbox>'
      . '</control>';

// generate footer

$xml .= '</group>'
      . $notes
      . '</form>';

echo(xml2html($xml));

?>
