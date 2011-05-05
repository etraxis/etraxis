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
require_once('../dbo/fields.php');
require_once('../dbo/values.php');
require_once('../dbo/records.php');
/**#@-*/

init_page(LOAD_INLINE);

$error = NO_ERROR;

// check that requested record exists

$id     = ustr2int(try_request('id'));
$record = record_find($id);

if (!$record)
{
    debug_write_log(DEBUG_NOTICE, 'Record cannot be found.');
    header('HTTP/1.1 307 index.php');
    exit;
}

// get current user's permissions and verify them

$permissions = record_get_permissions($record['template_id'], $record['creator_id'], $record['responsible_id']);

if (!can_record_be_modified($record, $permissions))
{
    debug_write_log(DEBUG_NOTICE, 'Record cannot be modified.');
    header('HTTP/1.1 307 view.php?id=' . $id);
    exit;
}

// modification form is submitted

if (try_request('submitted') == 'modifyform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $subject = ustrcut($_REQUEST['subject'], MAX_RECORD_SUBJECT);

    $rs = dal_query('records/elist.sql', $id);

    while (($row = $rs->fetch()) && ($error == NO_ERROR))
    {
        $error = record_validate(OPERATION_MODIFY_RECORD, $subject, $id, $row['state_id'], $record['creator_id'], $record['responsible_id']);
    }

    if ($error == NO_ERROR)
    {
        $error = record_modify($id, $subject, $record['creator_id'], $record['responsible_id']);
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
            send_http_error(ustrprocess(get_html_resource(RES_ALERT_FIELD_VALUE_OUT_OF_RANGE_ID), $_SESSION['FIELD_NAME'], $_SESSION['MIN_FIELD_INTEGER'], $_SESSION['MAX_FIELD_INTEGER']));
            unset($_SESSION['FIELD_NAME']);
            unset($_SESSION['MIN_FIELD_INTEGER']);
            unset($_SESSION['MAX_FIELD_INTEGER']);
            break;

        case ERROR_RECORD_NOT_FOUND:
            send_http_error(get_html_resource(RES_ALERT_RECORD_NOT_FOUND_ID));
            break;

        case ERROR_VALUE_FAILS_REGEX_CHECK:
            send_http_error(ustrprocess(get_html_resource(RES_ALERT_VALUE_FAILS_REGEX_CHECK_ID), $_SESSION['FIELD_NAME'], $_SESSION['FIELD_VALUE']));
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

    $subject = $record['subject'];
}

// local JS functions

$resTitle = get_js_resource(RES_ERROR_ID);
$resOK    = get_js_resource(RES_OK_ID);

$xml = <<<JQUERY
<script>

function modifySuccess ()
{
    closeModal();
    $("#tabs").tabs("load", 2);
    reloadTab();
}

function modifyError (XMLHttpRequest)
{
    jqAlert("{$resTitle}", XMLHttpRequest.responseText, "{$resOK}");
}

</script>
JQUERY;

// generate general information

$xml .= '<form name="modifyform" action="modify.php?id=' . $id . '" success="modifySuccess" error="modifyError">'
      . '<group>'
      . '<control name="subject" required="' . get_html_resource(RES_REQUIRED3_ID) . '" description="true">'
      . '<label>' . get_html_resource(RES_SUBJECT_ID) . '</label>'
      . '<editbox maxlen="' . MAX_RECORD_SUBJECT . '">' . ustr2html($subject) . '</editbox>'
      . '<description headline="' . get_html_resource(RES_DESCRIPTION_ID) . '">'
      . get_html_resource(RES_ALERT_SPECIFY_SHORT_DESCRIPTION_ID)
      . '</description>'
      . '</control>'
      . '</group>';

// go through the list of all states and their fields

$flag   = FALSE;
$script = NULL;
$notes  = '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>';

$states = dal_query('records/elist.sql', $id);

while (($state = $states->fetch()))
{
    $fields = dal_query('records/flist.sql',
                        $id,
                        $state['state_id'],
                        $record['creator_id'],
                        is_null($record['responsible_id']) ? 0 : $record['responsible_id'],
                        $_SESSION[VAR_USERID],
                        FIELD_ALLOW_TO_WRITE);

    if ($fields->rows != 0)
    {
        $xml .= '<group title="' . ustr2html($state['state_name']) . '">';

        while (($field = $fields->fetch()))
        {
            $name  = 'field' . $field['field_id'];
            $value = value_find($field['field_type'], $field['value_id']);

            $xml .= '<control name="' . $name . '"'
                  . ($field['is_required']
                        ? ' required="' . get_html_resource(RES_REQUIRED3_ID) . '"'
                        : NULL)
                  . (ustrlen($field['description']) != 0
                        ? ' description="true"'
                        : NULL)
                  . '>';

            switch ($field['field_type'])
            {
                case FIELD_TYPE_NUMBER:

                    $xml .= '<label>' . ustr2html($field['field_name']) . '</label>';

                    $xml .= '<editbox maxlen="' . (ustrlen(MAX_FIELD_INTEGER) + 1) . '">'
                          . ustr2html(try_request($name, $value))
                          . '</editbox>';

                    $notes .= '<note>'
                            . ustrprocess(get_html_resource(RES_ALERT_FIELD_VALUE_OUT_OF_RANGE_ID), ustr2html($field['field_name']), $field['param1'], $field['param2'])
                            . '</note>';

                    break;

                case FIELD_TYPE_FLOAT:

                    $xml .= '<label>' . ustr2html($field['field_name']) . '</label>';

                    $xml .= '<editbox maxlen="' . ustrlen(MAX_FIELD_FLOAT) . '">'
                          . ustr2html(try_request($name, $value))
                          . '</editbox>';

                    $notes .= '<note>'
                            . ustrprocess(get_html_resource(RES_ALERT_FIELD_VALUE_OUT_OF_RANGE_ID),
                                          ustr2html($field['field_name']),
                                          value_find(FIELD_TYPE_FLOAT, $field['param1']),
                                          value_find(FIELD_TYPE_FLOAT, $field['param2']))
                            . '</note>';

                    break;

                case FIELD_TYPE_STRING:

                    $xml .= '<label>' . ustr2html($field['field_name']) . '</label>';

                    $xml .= '<editbox maxlen="' . $field['param1'] . '">'
                          . ustr2html(try_request($name, $value))
                          . '</editbox>';

                    $flag = TRUE;

                    break;

                case FIELD_TYPE_MULTILINED:

                    $xml .= '<label>' . ustr2html($field['field_name']) . '</label>';

                    $xml .= '<textbox rows="' . $_SESSION[VAR_TEXTROWS] . '" maxlen="' . MAX_FIELD_MULTILINED . '">'
                          . ustr2html(try_request($name, $value))
                          . '</textbox>';

                    $flag = TRUE;

                    break;

                case FIELD_TYPE_CHECKBOX:

                    $user_value = (try_request('submitted') == 'mainform')
                                ? isset($_REQUEST[$name])
                                : $value;

                    $xml .= '<label/>';

                    $xml .= ($user_value
                                ? '<checkbox checked="true">'
                                : '<checkbox>')
                          . ustr2html($field['field_name'])
                          . '</checkbox>';

                    break;

                case FIELD_TYPE_LIST:

                    $selected = try_request($name, $value);

                    $xml .= '<label>' . ustr2html($field['field_name']) . '</label>';

                    $xml .= '<combobox>'
                          . '<listitem value=""/>';

                    $list = dal_query('values/lvlist.sql', $field['field_id']);

                    while (($item = $list->fetch()))
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

                    $xml .= '<label>' . ustr2html($field['field_name']) . '</label>';

                    $xml .= '<editbox maxlen="' . ustrlen(MAXINT) . '">'
                          . ustr2html(try_request($name, $value))
                          . '</editbox>';

                    $notes .= '<note>'
                            . ustrprocess(get_html_resource(RES_ALERT_FIELD_VALUE_OUT_OF_RANGE_ID), ustr2html($field['field_name']), 1, MAXINT)
                            . '</note>';

                    break;

                case FIELD_TYPE_DATE:

                    $event_time = $state['event_time'];

                    $field['param1'] = date_offset($event_time, $field['param1']);
                    $field['param2'] = date_offset($event_time, $field['param2']);

                    $xml .= '<label>' . sprintf('%s (%s)', ustr2html($field['field_name']), get_html_resource(RES_YYYY_MM_DD_ID)) . '</label>';

                    $xml .= '<editbox maxlen="' . ustrlen(get_date(SAMPLE_DATE)) . '">'
                          . ustr2html(try_request($name, $value))
                          . '</editbox>';

                    $notes .= '<note>'
                            . ustrprocess(get_html_resource(RES_ALERT_FIELD_VALUE_OUT_OF_RANGE_ID), ustr2html($field['field_name']), get_date($field['param1']), get_date($field['param2']))
                            . '</note>';

                    $script .= '$("#' . $name . '").datepicker($.datepicker.regional["' . $_SESSION[VAR_LOCALE] . '"]);';

                    break;

                case FIELD_TYPE_DURATION:

                    $xml .= '<label>' . ustr2html($field['field_name']) . '</label>';

                    $xml .= '<editbox maxlen="' . ustrlen(time2ustr(MAX_FIELD_DURATION)) . '">'
                          . ustr2html(try_request($name, $value))
                          . '</editbox>';

                    $notes .= '<note>'
                            . ustrprocess(get_html_resource(RES_ALERT_FIELD_VALUE_OUT_OF_RANGE_ID), ustr2html($field['field_name']), time2ustr($field['param1']), time2ustr($field['param2']))
                            . '</note>';

                    break;

                default:

                    debug_write_log(DEBUG_WARNING, 'Unknown field type = ' . $field['field_type']);
            }

            if (ustrlen($field['description']) != 0)
            {
                $xml .= '<description headline="' . get_html_resource(RES_DESCRIPTION_ID) . '">'
                      . update_references($field['description'], BBCODE_ALL)
                      . '</description>';
            }

            $xml .= '</control>';

            if ($field['add_separator'])
            {
                $xml .= '<hr/>';
            }
        }

        $xml .= '</group>';
    }
}

if ($flag)
{
    $notes .= '<note>' . get_html_resource(RES_LINK_TO_ANOTHER_RECORD_ID) . '</note>';
}

$xml .= $notes
      . '</form>'
      . '<script>'
      . $script
      . '</script>';

echo(xml2html($xml));

?>
