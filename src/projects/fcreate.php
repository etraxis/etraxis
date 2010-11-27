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

    $error = field_validate($field_name);

    if ($error == NO_ERROR)
    {
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
    else
    {
        $form      = 'mainform';
        $def_value = NULL;
    }
}

// 2nd step of new field (number) has been submitted

elseif (try_request('submitted') == 'numberform')
{
    debug_write_log(DEBUG_NOTICE, 'Data for step #2 (number) are submitted.');

    $form = 'numberform';

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

        if ($error == NO_ERROR)
        {
            header('Location: findex.php?id=' . $id);
            exit;
        }
    }
}

// 2nd step of new field (string) has been submitted

elseif (try_request('submitted') == 'stringform')
{
    debug_write_log(DEBUG_NOTICE, 'Data for step #2 (string) are submitted.');

    $form      = 'stringform';
    $def_value = NULL;

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

        if ($error == NO_ERROR)
        {
            header('Location: findex.php?id=' . $id);
            exit;
        }
    }
}

// 2nd step of new field (multilined) has been submitted

elseif (try_request('submitted') == 'multilinedform')
{
    debug_write_log(DEBUG_NOTICE, 'Data for step #2 (multilined text) are submitted.');

    $form      = 'multilinedform';
    $def_value = NULL;

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

        if ($error == NO_ERROR)
        {
            header('Location: findex.php?id=' . $id);
            exit;
        }
    }
}

// 2nd step of new field (checkbox) has been submitted

elseif (try_request('submitted') == 'checkboxform')
{
    debug_write_log(DEBUG_NOTICE, 'Data for step #2 (checkbox) are submitted.');

    $form = 'checkboxform';

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

    if ($error == NO_ERROR)
    {
        header('Location: findex.php?id=' . $id);
        exit;
    }
}

// 2nd step of new field (list) has been submitted

elseif (try_request('submitted') == 'listform')
{
    debug_write_log(DEBUG_NOTICE, 'Data for step #2 (list) are submitted.');

    $form = 'listform';

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

    if ($error == NO_ERROR)
    {
        field_create_list_items($id, $field_name, $list_items);
        header('Location: findex.php?id=' . $id);
        exit;
    }
}

// 2nd step of new field (record) has been submitted

elseif (try_request('submitted') == 'recordform')
{
    debug_write_log(DEBUG_NOTICE, 'Data for step #2 (record) are submitted.');

    $form = 'recordform';

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

    if ($error == NO_ERROR)
    {
        header('Location: findex.php?id=' . $id);
        exit;
    }
}

// 2nd step of new field (date) has been submitted

elseif (try_request('submitted') == 'dateform')
{
    debug_write_log(DEBUG_NOTICE, 'Data for step #2 (date) are submitted.');

    $form = 'dateform';

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

        if ($error == NO_ERROR)
        {
            header('Location: findex.php?id=' . $id);
            exit;
        }
    }
}

// 2nd step of new field (duration) has been submitted

elseif (try_request('submitted') == 'durationform')
{
    debug_write_log(DEBUG_NOTICE, 'Data for step #2 (duration) are submitted.');

    $form = 'durationform';

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

        if ($error == NO_ERROR)
        {
            header('Location: findex.php?id=' . $id);
            exit;
        }
    }
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

// page's title

$title = ustrprocess(get_html_resource(RES_NEW_FIELD_ID), ($form == 'mainform' ? 1 : 2), 2);

// generate breadcrumbs

$xml = gen_context_menu('sindex.php?id=', 'findex.php?id=', 'fview.php?id=', $state['project_id'], $state['template_id'], $id)
     . '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_PROJECTS_ID) . '</breadcrumb>'
     . '<breadcrumb url="tindex.php?id=' . $state['project_id']  . '">' . ustrprocess(get_html_resource(RES_PROJECT_X_ID),  ustr2html($state['project_name']))  . '</breadcrumb>'
     . '<breadcrumb url="sindex.php?id=' . $state['template_id'] . '">' . ustrprocess(get_html_resource(RES_TEMPLATE_X_ID), ustr2html($state['template_name'])) . '</breadcrumb>'
     . '<breadcrumb url="findex.php?id=' . $id                   . '">' . ustrprocess(get_html_resource(RES_STATE_X_ID),    ustr2html($state['state_name']))    . '</breadcrumb>'
     . '<breadcrumb url="fcreate.php?id=' . $id . '">' . $title . '</breadcrumb>'
     . '</breadcrumbs>';

// generate common controls

$xml .= '<content>'
      . '<form name="' . $form . '" action="fcreate.php?id=' . $id . '">'
      . '<group title="' . get_html_resource(RES_FIELD_INFO_ID) . '">'
      . '<control name="field_name" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
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
          . '<textbox rows="' . HTML_TEXTBOX_MIN_HEIGHT . '" resizeable="true" maxlen="' . MAX_FIELD_MULTILINED . '">'
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
          . '<textbox rows="' . HTML_TEXTBOX_MIN_HEIGHT . '" resizeable="true" maxlen="' . MAX_FIELD_LIST_ITEMS . '">'
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
          . '<textbox rows="' . HTML_TEXTBOX_MIN_HEIGHT . '" resizeable="true" maxlen="' . MAX_FIELD_DESCRIPTION . '">'
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

// generate buttons

$xml .= '</group>'
      . '<button default="true">' . get_html_resource($form == 'mainform' ? RES_NEXT_ID : RES_OK_ID) . '</button>'
      . '<button url="findex.php?id=' . $id . '">' . get_html_resource(RES_CANCEL_ID) . '</button>'
      . $notes
      . '</form>'
      . '</content>';

// if some error was specified to display, force an alert

switch ($error)
{
    case ERROR_INCOMPLETE_FORM:
        $xml .= '<scriptonreadyitem>'
              . 'jqAlert("' . get_html_resource(RES_ERROR_ID) . '","' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '","' . get_html_resource(RES_OK_ID) . '");'
              . '</scriptonreadyitem>';
        break;

    case ERROR_ALREADY_EXISTS:
        $xml .= '<scriptonreadyitem>'
              . 'jqAlert("' . get_html_resource(RES_ERROR_ID) . '","' . get_html_resource(RES_ALERT_FIELD_ALREADY_EXISTS_ID) . '","' . get_html_resource(RES_OK_ID) . '");'
              . '</scriptonreadyitem>';
        break;

    case ERROR_INVALID_INTEGER_VALUE:
        $xml .= '<scriptonreadyitem>'
              . 'jqAlert("' . get_html_resource(RES_ERROR_ID) . '","' . get_html_resource(RES_ALERT_INVALID_INTEGER_VALUE_ID) . '","' . get_html_resource(RES_OK_ID) . '");'
              . '</scriptonreadyitem>';
        break;

    case ERROR_INTEGER_VALUE_OUT_OF_RANGE:

        if ($form == 'numberform')
        {
            $xml .= '<scriptonreadyitem>'
                  . 'jqAlert("' . get_html_resource(RES_ERROR_ID) . '","' . ustrprocess(get_html_resource(RES_ALERT_INTEGER_VALUE_OUT_OF_RANGE_ID), -MAX_FIELD_INTEGER, +MAX_FIELD_INTEGER) . '","' . get_html_resource(RES_OK_ID) . '");'
                  . '</scriptonreadyitem>';
        }
        elseif ($form == 'stringform')
        {
            $xml .= '<scriptonreadyitem>'
                  . 'jqAlert("' . get_html_resource(RES_ERROR_ID) . '","' . ustrprocess(get_html_resource(RES_ALERT_INTEGER_VALUE_OUT_OF_RANGE_ID), 1, MAX_FIELD_STRING) . '","' . get_html_resource(RES_OK_ID) . '");'
                  . '</scriptonreadyitem>';
        }
        elseif ($form == 'multilinedform')
        {
            $xml .= '<scriptonreadyitem>'
                  . 'jqAlert("' . get_html_resource(RES_ERROR_ID) . '","' . ustrprocess(get_html_resource(RES_ALERT_INTEGER_VALUE_OUT_OF_RANGE_ID), 1, MAX_FIELD_MULTILINED) . '","' . get_html_resource(RES_OK_ID) . '");'
                  . '</scriptonreadyitem>';
        }

        break;

    case ERROR_MIN_MAX_VALUES:
        $xml .= '<scriptonreadyitem>'
              . 'jqAlert("' . get_html_resource(RES_ERROR_ID) . '","' . get_html_resource(RES_ALERT_MIN_MAX_VALUES_ID) . '","' . get_html_resource(RES_OK_ID) . '");'
              . '</scriptonreadyitem>';
        break;

    case ERROR_INVALID_DATE_VALUE:
        $xml .= '<scriptonreadyitem>'
              . 'jqAlert("' . get_html_resource(RES_ERROR_ID) . '","' . get_html_resource(RES_ALERT_INVALID_DATE_VALUE_ID) . '","' . get_html_resource(RES_OK_ID) . '");'
              . '</scriptonreadyitem>';
        break;

    case ERROR_DATE_VALUE_OUT_OF_RANGE:
        $xml .= '<scriptonreadyitem>'
              . 'jqAlert("' . get_html_resource(RES_ERROR_ID) . '","' . ustrprocess(get_html_resource(RES_ALERT_DATE_VALUE_OUT_OF_RANGE_ID), MIN_FIELD_DATE, MAX_FIELD_DATE) . '","' . get_html_resource(RES_OK_ID) . '");'
              . '</scriptonreadyitem>';
        break;

    case ERROR_INVALID_TIME_VALUE:
        $xml .= '<scriptonreadyitem>'
              . 'jqAlert("' . get_html_resource(RES_ERROR_ID) . '","' . get_html_resource(RES_ALERT_INVALID_TIME_VALUE_ID) . '","' . get_html_resource(RES_OK_ID) . '");'
              . '</scriptonreadyitem>';
        break;

    case ERROR_TIME_VALUE_OUT_OF_RANGE:
        $xml .= '<scriptonreadyitem>'
              . 'jqAlert("' . get_html_resource(RES_ERROR_ID) . '","' . ustrprocess(get_html_resource(RES_ALERT_TIME_VALUE_OUT_OF_RANGE_ID), time2ustr(MIN_FIELD_DURATION), time2ustr(MAX_FIELD_DURATION)) . '","' . get_html_resource(RES_OK_ID) . '");'
              . '</scriptonreadyitem>';
        break;

    case ERROR_DEFAULT_VALUE_OUT_OF_RANGE:
        $xml .= '<scriptonreadyitem>'
              . 'jqAlert("' . get_html_resource(RES_ERROR_ID) . '","' . ustrprocess(get_html_resource(RES_ALERT_DEFAULT_VALUE_OUT_OF_RANGE_ID), $min_value, $max_value) . '","' . get_html_resource(RES_OK_ID) . '");'
              . '</scriptonreadyitem>';
        break;

    default: ;  // nop
}

echo(xml2html($xml, $title));

?>
