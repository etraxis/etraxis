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
require_once('../dbo/states.php');
require_once('../dbo/fields.php');
require_once('../dbo/values.php');
require_once('../dbo/records.php');
require_once('../dbo/events.php');
/**#@-*/

init_page();

// check that requested record exists

$id     = ustr2int(try_request('id'));
$record = record_find($id);

if (!$record)
{
    debug_write_log(DEBUG_NOTICE, 'Record cannot be found.');
    header('Location: index.php');
    exit;
}

// check that requested state exists

$state_id = ustr2int(try_request('state'));
$state    = state_find($state_id);

if (!$state)
{
    debug_write_log(DEBUG_NOTICE, 'State cannot be found.');
    header('Location: view.php?id=' . $id);
    exit;
}

// get current user's permissions and verify them

$permissions = record_get_permissions($record['template_id'], $record['creator_id'], $record['responsible_id']);

if (!can_state_be_changed($record, $permissions))
{
    debug_write_log(DEBUG_NOTICE, 'State cannot be changed.');
    header('Location: view.php?id=' . $id);
    exit;
}

// if state is final, check whether there are no unclosed dependencies

if ($state['state_type'] == STATE_TYPE_FINAL)
{
    $rs = dal_query('depends/listuc.sql', $id);

    if ($rs->rows != 0)
    {
        debug_write_log(DEBUG_NOTICE, 'The record has unclosed dependencies.');
        header('Location: view.php?id=' . $id);
        exit;
    }
}

// check whether the record can be moved to specified state from current one

$rs = dal_query('records/tramongs.sql', $id, $_SESSION[VAR_USERID], '');

if ($rs->rows == 0)
{
    debug_write_log(DEBUG_NOTICE, 'No permissions to change to specified state.');
    header('Location: view.php?id=' . $id);
    exit;
}

// state form is submitted

if (try_request('submitted') == 'stateform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $rs = dal_query('records/efnd.sql', $_SESSION[VAR_USERID], EVENT_RECORD_STATE_CHANGED, time() - 3, $state_id);

    if ($rs->rows != 0)
    {
        debug_write_log(DEBUG_NOTICE, 'Double click issue is detected.');
        exit;
    }

    switch ($state['responsible'])
    {
        case STATE_RESPONSIBLE_REMAIN:
            $responsible_id = 0;
            break;
        case STATE_RESPONSIBLE_ASSIGN:
            $responsible_id = try_request('responsible');
            break;
        case STATE_RESPONSIBLE_REMOVE:
            $responsible_id = NULL;
            break;
        default:
            debug_write_log(DEBUG_WARNING, 'Unknown state responsible type = ' . $state['responsible']);
    }

    $error = record_validate(OPERATION_CHANGE_STATE, NULL, $id, $state_id);

    if ($error == NO_ERROR)
    {
        $error = state_change($id,
                              $state_id,
                              $responsible_id,
                              ($state['state_type'] == STATE_TYPE_FINAL));
    }

    switch ($error)
    {
        case ERROR_INCOMPLETE_FORM:
            echo(get_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID));
            break;

        case ERROR_INVALID_INTEGER_VALUE:
            echo(get_resource(RES_ALERT_INVALID_INTEGER_VALUE_ID));
            break;

        case ERROR_INVALID_DATE_VALUE:
            echo(get_resource(RES_ALERT_INVALID_DATE_VALUE_ID));
            break;

        case ERROR_INVALID_TIME_VALUE:
            echo(get_resource(RES_ALERT_INVALID_TIME_VALUE_ID));
            break;

        case ERROR_INTEGER_VALUE_OUT_OF_RANGE:
        case ERROR_DATE_VALUE_OUT_OF_RANGE:
        case ERROR_TIME_VALUE_OUT_OF_RANGE:
            echo(ustrprocess(get_resource(RES_ALERT_FIELD_VALUE_OUT_OF_RANGE_ID), $_SESSION['FIELD_NAME'], $_SESSION['MIN_FIELD_INTEGER'], $_SESSION['MAX_FIELD_INTEGER']));
            unset($_SESSION['FIELD_NAME']);
            unset($_SESSION['MIN_FIELD_INTEGER']);
            unset($_SESSION['MAX_FIELD_INTEGER']);
            break;

        case ERROR_RECORD_NOT_FOUND:
            echo(get_resource(RES_ALERT_RECORD_NOT_FOUND_ID));
            break;

        case ERROR_VALUE_FAILS_REGEX_CHECK:
            echo(ustrprocess(get_resource(RES_ALERT_VALUE_FAILS_REGEX_CHECK_ID), $_SESSION['FIELD_NAME'], $_SESSION['FIELD_VALUE']));
            unset($_SESSION['FIELD_NAME']);
            unset($_SESSION['FIELD_VALUE']);
            break;

        default: ;  // nop
    }

    exit;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $responsible_id = NULL;
}

// generate state form

$xml = '<form name="stateform" action="javascript:submitStateForm(' . $id . ',' . $state_id . ')">'
     . '<group title="' . ustr2html($state['state_name']) . '">';

// if state must be assigned, generate list of accounts

if ($state['responsible'] == STATE_RESPONSIBLE_ASSIGN)
{
    debug_write_log(DEBUG_NOTICE, 'Record should be assigned.');

    $rs = dal_query('records/responsibles.sql', $record['project_id'], $state_id, $record['creator_id']);

    if ($rs->rows != 0)
    {
        $default_responsible = (is_null($record['responsible_id']) ? $_SESSION[VAR_USERID] : $record['responsible_id']);

        $xml .= '<control name="responsible" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
              . '<label>' . get_html_resource(RES_RESPONSIBLE_ID) . '</label>'
              . '<combobox>';

        while (($row = $rs->fetch()))
        {
            $xml .= ($row['account_id'] == $default_responsible
                        ? '<listitem value="' . $row['account_id'] . '" selected="true">'
                        : '<listitem value="' . $row['account_id'] . '">')
                  . ustr2html(sprintf('%s (%s)', $row['fullname'], account_get_username($row['username'])))
                  . '</listitem>';
        }

        $xml .= '</combobox>'
              . '</control>';
    }
}

$flag1 = FALSE;
$flag2 = FALSE;
$notes = NULL;

// get list of latest values of related fields

$rs = dal_query('fields/listv.sql', $id, $state_id);

// if state is being used first time (no latest values yet), then get list of fields

if ($rs->rows == 0)
{
    $rs = dal_query('fields/list.sql', $state_id, 'field_order');
}

if ($rs->rows == 0)
{
    debug_write_log(DEBUG_NOTICE, 'No fields for specified state are found.');

    if ($state['responsible'] != STATE_RESPONSIBLE_ASSIGN)
    {
        $xml .= '<text>' . get_html_resource(RES_NO_FIELDS_ID) . '</text>';
    }
}
else
{
    // go through the list of fields

    debug_write_log(DEBUG_NOTICE, 'Fields of specified state are being enumerated.');

    while (($row = $rs->fetch()))
    {
        $name  = 'field' . $row['field_id'];
        $value = NULL;

        // determine default value of the field

        $clone_id = is_record_cloned($id);

        if ($clone_id != 0)
        {
            $rsv = dal_query('values/fndk.sql', $clone_id, $row['field_id']);

            if ($rsv->rows != 0)
            {
                $value = value_find($row['field_type'], $rsv->fetch('value_id'));
            }
            elseif (!is_null($row['value_id']))
            {
                $value = value_find($row['field_type'], ($row['field_type'] == FIELD_TYPE_DATE ? date_offset(time(), $row['value_id']) : $row['value_id']));
            }
        }
        elseif (!is_null($row['value_id']))
        {
            $value = value_find($row['field_type'], ($row['field_type'] == FIELD_TYPE_DATE ? date_offset(time(), $row['value_id']) : $row['value_id']));
        }

        if ($row['is_required'])
        {
            $flag1 = TRUE;
        }

        // generate control for the field

        $xml .= ($row['is_required']
                    ? '<control name="' . $name . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
                    : '<control name="' . $name . '">');

        switch ($row['field_type'])
        {
            case FIELD_TYPE_NUMBER:

                $xml .= '<label>' . ustr2html($row['field_name']) . '</label>';

                $xml .= '<editbox maxlen="' . (ustrlen(MAX_FIELD_INTEGER) + 1) . '">'
                      . ustr2html(try_request($name, $value))
                      . '</editbox>';

                $notes .= '<note>'
                        . ustrprocess(get_html_resource(RES_ALERT_FIELD_VALUE_OUT_OF_RANGE_ID), ustr2html($row['field_name']), $row['param1'], $row['param2'])
                        . '</note>';

                break;

            case FIELD_TYPE_STRING:

                $xml .= '<label>' . ustr2html($row['field_name']) . '</label>';

                $xml .= '<editbox maxlen="' . $row['param1'] . '">'
                      . ustr2html(try_request($name, $value))
                      . '</editbox>';

                $flag2 = TRUE;

                break;

            case FIELD_TYPE_MULTILINED:

                $xml .= '<label>' . ustr2html($row['field_name']) . '</label>';

                $xml .= '<textbox rows="' . HTML_TEXTBOX_MIN_HEIGHT . '" resizeable="true" maxlen="' . MAX_FIELD_MULTILINED . '">'
                      . ustr2html(try_request($name, $value))
                      . '</textbox>';

                $flag2 = TRUE;

                break;

            case FIELD_TYPE_CHECKBOX:

                $user_value = (try_request('submitted') == 'fieldsform')
                            ? isset($_REQUEST[$name])
                            : $value;

                $xml .= '<label/>';

                $xml .= ($user_value
                            ? '<checkbox checked="true">'
                            : '<checkbox>')
                      . ustr2html($row['field_name'])
                      . '</checkbox>';

                break;

            case FIELD_TYPE_LIST:

                $selected = try_request($name, $value);

                $xml .= '<label>' . ustr2html($row['field_name']) . '</label>';

                $xml .= '<combobox>'
                      . '<listitem value=""/>';

                $rsv = dal_query('values/lvlist.sql', $row['field_id']);

                while (($item = $rsv->fetch()))
                {
                    $xml .= ($selected == $item['int_value']
                                ? '<listitem value="' . $item['int_value'] . '" selected="true">'
                                : '<listitem value="' . $item['int_value'] . '">')
                          . ustr2html($item['str_value'])
                          . '</listitem>';
                }

                $xml .= '</combobox>';

                break;

            case FIELD_TYPE_RECORD:

                $xml .= '<label>' . ustr2html($row['field_name']) . '</label>';

                $xml .= '<editbox maxlen="' . ustrlen(MAXINT) . '">'
                      . ustr2html(try_request($name, $value))
                      . '</editbox>';

                $notes .= '<note>'
                        . ustrprocess(get_html_resource(RES_ALERT_FIELD_VALUE_OUT_OF_RANGE_ID), ustr2html($row['field_name']), 1, MAXINT)
                        . '</note>';

                break;

            case FIELD_TYPE_DATE:

                $today = time();

                $row['param1'] = date_offset($today, $row['param1']);
                $row['param2'] = date_offset($today, $row['param2']);

                $xml .= '<label>' . sprintf('%s (%s)', ustr2html($row['field_name']), get_html_resource(RES_YYYY_MM_DD_ID)) . '</label>';

                $xml .= '<editbox maxlen="' . ustrlen(get_date(SAMPLE_DATE)) . '">'
                      . ustr2html(try_request($name, $value))
                      . '</editbox>';

                $notes .= '<note>'
                        . ustrprocess(get_html_resource(RES_ALERT_FIELD_VALUE_OUT_OF_RANGE_ID), ustr2html($row['field_name']), get_date($row['param1']), get_date($row['param2']))
                        . '</note>';

                $script = '<script>'
                        . '$("#' . $name . '").datepicker($.datepicker.regional["' . $_SESSION[VAR_LOCALE] . '"]);'
                        . '</script>';

                break;

            case FIELD_TYPE_DURATION:

                $xml .= '<label>' . ustr2html($row['field_name']) . '</label>';

                $xml .= '<editbox maxlen="' . ustrlen(time2ustr(MAX_FIELD_DURATION)) . '">'
                      . ustr2html(try_request($name, $value))
                      . '</editbox>';

                $notes .= '<note>'
                        . ustrprocess(get_html_resource(RES_ALERT_FIELD_VALUE_OUT_OF_RANGE_ID), ustr2html($row['field_name']), time2ustr($row['param1']), time2ustr($row['param2']))
                        . '</note>';

                break;

            default:

                debug_write_log(DEBUG_WARNING, 'Unknown field type = ' . $row['field_type']);
        }

        $xml .= '</control>';

        if ($row['add_separator'])
        {
            $xml .= '<hr/>';
        }
    }
}

if ($flag1)
{
    $notes = '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>' . $notes;
}

if ($flag2)
{
    $notes .= '<note>' . get_html_resource(RES_LINK_TO_ANOTHER_RECORD_ID) . '</note>';
}

$xml .= '</group>'
      . '<button default="true">'             . get_html_resource(RES_OK_ID)     . '</button>'
      . '<button action="cancelStateForm()">' . get_html_resource(RES_CANCEL_ID) . '</button>'
      . $notes
      . (isset($script) ? $script : NULL)
      . '</form>';

echo(xml2html($xml));

?>
