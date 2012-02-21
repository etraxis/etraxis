<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2005-2012  Artem Rodygin
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

init_page(LOAD_INLINE);

// check that requested record exists

$id     = ustr2int(try_request('id'));
$record = record_find($id);

if (!$record)
{
    debug_write_log(DEBUG_NOTICE, 'Record cannot be found.');
    header('HTTP/1.1 307 index.php');
    exit;
}

// check that requested state exists

$state_id = ustr2int(try_request('state'));
$state    = state_find($state_id);

if (!$state)
{
    debug_write_log(DEBUG_NOTICE, 'State cannot be found.');
    header('HTTP/1.1 307 view.php?id=' . $id);
    exit;
}

// get current user's permissions

$permissions = record_get_permissions($record['template_id'], $record['creator_id'], $record['responsible_id']);

// check whether a state of specified record can be changed

if (!can_state_be_changed($record) &&
    !can_record_be_reopened($record, $permissions))
{
    debug_write_log(DEBUG_NOTICE, 'State cannot be changed.');
    header('HTTP/1.1 307 view.php?id=' . $id);
    exit;
}

// if state is final...

if ($state['state_type'] == STATE_TYPE_FINAL)
{
    // ... check whether there are no unclosed dependencies
    $rs = dal_query('depends/listuc.sql', $id);

    if ($rs->rows != 0)
    {
        debug_write_log(DEBUG_NOTICE, 'The record has unclosed dependencies.');
        header('HTTP/1.1 307 view.php?id=' . $id);
        exit;
    }

    // ... check we are not reopening closed record
    if (is_record_closed($record))
    {
        debug_write_log(DEBUG_NOTICE, 'The record cannot be reopened in a final state.');
        header('HTTP/1.1 307 view.php?id=' . $id);
        exit;
    }
}
else
{
    // ... otherwise, check whether the record can be moved to specified state from current one
    if (is_record_closed($record))
    {
        if ($state['template_id'] != $record['template_id'])
        {
            debug_write_log(DEBUG_NOTICE, 'No permissions to reopen in specified state.');
            header('HTTP/1.1 307 view.php?id=' . $id);
            exit;
        }
    }
    else
    {
        $rs = dal_query('records/tramongs.sql', $id, $_SESSION[VAR_USERID], '');

        if ($rs->rows == 0)
        {
            debug_write_log(DEBUG_NOTICE, 'No permissions to change to specified state.');
            header('HTTP/1.1 307 view.php?id=' . $id);
            exit;
        }
    }
}

// state form is submitted

if (try_request('submitted') == 'stateform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $rs = dal_query('records/efnd.sql',
                    $_SESSION[VAR_USERID],
                    is_record_closed($record) ? EVENT_RECORD_REOPENED : EVENT_RECORD_STATE_CHANGED,
                    time() - 3,
                    $state_id);

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
                              ($state['state_type'] == STATE_TYPE_FINAL),
                              is_record_closed($record));
    }

    switch ($error)
    {
        case NO_ERROR:
            header('HTTP/1.0 200 OK');
            break;

        case ERROR_INCOMPLETE_FORM:
            send_http_error(get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID));
            break;

        case ERROR_INVALID_INTEGER_VALUE:
            send_http_error(get_html_resource(RES_ALERT_INVALID_INTEGER_VALUE_ID));
            break;

        case ERROR_INVALID_FLOAT_VALUE:
            send_http_error(get_html_resource(RES_ALERT_INVALID_DECIMAL_VALUE_ID));
            break;

        case ERROR_INVALID_DATE_VALUE:
            send_http_error(get_html_resource(RES_ALERT_INVALID_DATE_VALUE_ID));
            break;

        case ERROR_INVALID_TIME_VALUE:
            send_http_error(get_html_resource(RES_ALERT_INVALID_TIME_VALUE_ID));
            break;

        case ERROR_INTEGER_VALUE_OUT_OF_RANGE:
        case ERROR_FLOAT_VALUE_OUT_OF_RANGE:
        case ERROR_DATE_VALUE_OUT_OF_RANGE:
        case ERROR_TIME_VALUE_OUT_OF_RANGE:
            send_http_error(ustrprocess(get_js_resource(RES_ALERT_FIELD_VALUE_OUT_OF_RANGE_ID), $_SESSION['FIELD_NAME'], $_SESSION['MIN_FIELD_INTEGER'], $_SESSION['MAX_FIELD_INTEGER']));
            unset($_SESSION['FIELD_NAME']);
            unset($_SESSION['MIN_FIELD_INTEGER']);
            unset($_SESSION['MAX_FIELD_INTEGER']);
            break;

        case ERROR_RECORD_NOT_FOUND:
            send_http_error(get_html_resource(RES_ALERT_RECORD_NOT_FOUND_ID));
            break;

        case ERROR_VALUE_FAILS_REGEX_CHECK:
            send_http_error(ustrprocess(get_js_resource(RES_ALERT_VALUE_FAILS_REGEX_CHECK_ID), $_SESSION['FIELD_NAME'], $_SESSION['FIELD_VALUE']));
            unset($_SESSION['FIELD_NAME']);
            unset($_SESSION['FIELD_VALUE']);
            break;

        default:
            send_http_error(get_html_resource(RES_ALERT_UNKNOWN_ERROR_ID));
    }

    exit;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $responsible_id = NULL;
}

// local JS functions

$resTitle = get_js_resource(RES_ERROR_ID);
$resOK    = get_js_resource(RES_OK_ID);

$xml = <<<JQUERY
<script>

function stateSuccess ()
{
    closeModal();
    reloadTab();
}

function stateError (XMLHttpRequest)
{
    jqAlert("{$resTitle}", XMLHttpRequest.responseText, "{$resOK}");
}

</script>
JQUERY;

// generate state form

$xml .= '<form name="stateform" action="state.php?id=' . $id . '&amp;state=' . $state_id . '" success="stateSuccess" error="stateError">';

// get list of latest values of related fields

$rs = dal_query('fields/listv.sql', $id, $state_id);

// if state is being used first time (no latest values yet), then get list of fields

if ($rs->rows == 0)
{
    $rs = dal_query('fields/list.sql', $state_id, 'field_order');
}

if ($rs->rows == 0 && $state['responsible'] != STATE_RESPONSIBLE_ASSIGN)
{
    debug_write_log(DEBUG_NOTICE, 'No fields for specified state are found.');

    $xml .= '<div>' . get_html_resource(RES_CONFIRM_CHANGE_STATE_ID) . '</div>';
}
else
{
    // if state must be assigned, generate list of accounts

    $xml .= '<group>';

    if ($state['responsible'] == STATE_RESPONSIBLE_ASSIGN)
    {
        debug_write_log(DEBUG_NOTICE, 'Record should be assigned.');

        $rs_res = dal_query('records/responsibles.sql', $state_id, $_SESSION[VAR_USERID]);

        if ($rs_res->rows != 0)
        {
            $default_responsible = (is_null($record['responsible_id']) ? $_SESSION[VAR_USERID] : $record['responsible_id']);

            $xml .= '<control name="responsible" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
                  . '<label>' . get_html_resource(RES_RESPONSIBLE_ID) . '</label>'
                  . '<combobox>';

            while (($row = $rs_res->fetch()))
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

    $flag1  = FALSE;
    $flag2  = FALSE;
    $notes  = NULL;
    $script = NULL;

    // go through the list of fields

    debug_write_log(DEBUG_NOTICE, 'Fields of specified state are being enumerated.');

    while (($row = $rs->fetch()))
    {
        $name  = 'field' . $row['field_id'];
        $value = NULL;

        // determine default value of the field

        $clone_id = is_record_cloned($id);

        if (!is_null($row['value_id']))
        {
            $value = $row['value_id'];
        }
        elseif ($clone_id != 0)
        {
            $rsv = dal_query('values/fndk.sql', $clone_id, $row['field_id']);

            if ($rsv->rows != 0)
            {
                $value = $rsv->fetch('value_id');
            }
            elseif (!is_null($row['value_id']))
            {
                $value = $row['value_id'];
            }
        }

        // adjust for current date the value of date fields

        if ($row['field_type'] == FIELD_TYPE_DATE)
        {
            $today = time();

            $row['param1'] = date_offset($today, $row['param1']);
            $row['param2'] = date_offset($today, $row['param2']);

            if (!is_null($value))
            {
                $value = ustr2int($value, $row['param1'], $row['param2']);
            }
        }

        // convert to "human reading" format
        $value = value_find($row['field_type'], $value);

        if ($row['is_required'])
        {
            $flag1 = TRUE;
        }

        // generate control for the field
        $xml .= '<control name="' . $name . '"'
              . ($row['is_required'] && $row['field_type'] != FIELD_TYPE_CHECKBOX
                    ? ' required="' . get_html_resource(RES_REQUIRED3_ID) . '"'
                    : NULL)
              . (ustrlen($row['description']) != 0
                    ? ' description="true"'
                    : NULL)
              . '>';

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

            case FIELD_TYPE_FLOAT:

                $xml .= '<label>' . ustr2html($row['field_name']) . '</label>';

                $xml .= '<editbox maxlen="' . ustrlen(MAX_FIELD_FLOAT) . '">'
                      . ustr2html(try_request($name, $value))
                      . '</editbox>';

                $notes .= '<note>'
                        . ustrprocess(get_html_resource(RES_ALERT_FIELD_VALUE_OUT_OF_RANGE_ID),
                                      ustr2html($row['field_name']),
                                      value_find(FIELD_TYPE_FLOAT, $row['param1']),
                                      value_find(FIELD_TYPE_FLOAT, $row['param2']))
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

                $xml .= '<textbox rows="' . $_SESSION[VAR_TEXTROWS] . '" maxlen="' . MAX_FIELD_MULTILINED . '">'
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

                $xml .= '<label>' . sprintf('%s (%s)', ustr2html($row['field_name']), get_date_format_str()) . '</label>';

                $xml .= '<editbox maxlen="' . ustrlen(get_date(SAMPLE_DATE)) . '">'
                      . ustr2html(try_request($name, $value))
                      . '</editbox>';

                $notes .= '<note>'
                        . ustrprocess(get_html_resource(RES_ALERT_FIELD_VALUE_OUT_OF_RANGE_ID), ustr2html($row['field_name']), get_date($row['param1']), get_date($row['param2']))
                        . '</note>';

                $script .= '$("#' . $name . '").datepicker($.datepicker.regional["' . $_SESSION[VAR_LOCALE] . '"]);';

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

        if (strlen($row['description']) != 0)
        {
            $xml .= '<description>'
                  . update_references($row['description'], BBCODE_ALL)
                  . '</description>';
        }

        $xml .= '</control>';

        if ($row['add_separator'])
        {
            $xml .= '<hr/>';
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
          . $notes
          . '<script>' . $script . '</script>';
}

$xml .= '</form>';

echo(xml2html($xml));

?>
