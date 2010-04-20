<?php

/**
 * @package eTraxis
 * @ignore
 */

//--------------------------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system.
//  Copyright (C) 2005-2010 by Artem Rodygin
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
//  Artem Rodygin           2005-04-21      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-07-04      bug-010: Missing 'require' operator.
//  Artem Rodygin           2005-07-31      new-006: Records search.
//  Artem Rodygin           2005-08-06      new-019: Fields default values.
//  Artem Rodygin           2005-08-27      bug-063: No error message is displayed when non-existing record is specified in field of 'record' type.
//  Artem Rodygin           2005-08-30      bug-080: 'Record' type fields of some record should not accept ID of this record.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-09-07      bug-098: List of users when record is being assigned should contain only allowed users.
//  Artem Rodygin           2005-09-07      new-100: 'Date' field type should be implemented.
//  Artem Rodygin           2005-09-08      new-101: 'Duration' field type should be implemented.
//  Artem Rodygin           2005-09-12      new-105: Format of date values are being entered should depend on user locale settings.
//  Artem Rodygin           2005-09-15      new-120: Default field values of cloned records.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-11-17      new-176: Change eTraxis design.
//  Artem Rodygin           2006-03-18      new-175: Implement user roles in permissions.
//  Artem Rodygin           2006-07-12      bug-292: Sablotron fails if page contains '&' character.
//  Artem Rodygin           2006-07-27      new-261: UI design should be adopted to slow connection.
//  Artem Rodygin           2006-08-13      new-305: Note with explanation of links to other records should be added where needed.
//  Artem Rodygin           2006-09-26      new-318: Group permissions should be template-wide.
//  Artem Rodygin           2006-11-04      new-364: Default fields values.
//  Artem Rodygin           2006-11-20      new-392: Local users should not be extended with '@eTraxis' when LDAP is disabled.
//  Artem Rodygin           2006-12-23      new-463: Date field names should be extended with date format explanation.
//  Artem Rodygin           2007-03-24      bug-511: Selected responsible is not restored.
//  Artem Rodygin           2007-07-04      new-533: Links between records.
//  Artem Rodygin           2007-09-09      new-563: Custom separators inside fields set.
//  Yury Udovichenko        2007-11-14      new-548: Custom links in text fields.
//  Artem Rodygin           2007-11-27      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2008-01-11      bug-663: Author permissions are ignored.
//  Artem Rodygin           2008-02-08      new-671: Default value for 'date' fields should be relative.
//  Artem Rodygin           2008-02-25      bug-678: Default value of combo box field is ignored.
//  Artem Rodygin           2008-04-20      new-703: Separated permissions set for current responsible.
//  Artem Rodygin           2008-06-20      new-725: Extend combo box.
//  Artem Rodygin           2008-09-11      new-716: 'Today' value in date field range.
//  Artem Rodygin           2008-09-11      bug-742: Not all expected notes are present when record is being created/modified.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-04-25      new-801: Range of valid date values must be related to current date.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//  Artem Rodygin           2009-07-29      bug-825: Database gets empty strings instead of NULL values.
//  Artem Rodygin           2009-07-29      new-833: Default responsible should be current user, when possible.
//  Giacomo Giustozzi       2010-02-10      new-913: Resizable text boxes
//  Giacomo Giustozzi       2010-02-12      new-918: Add Subject to Change State page
//  Artem Rodygin           2010-04-19      new-928: Inline state changing.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/accounts.php');
require_once('../dbo/states.php');
require_once('../dbo/fields.php');
require_once('../dbo/values.php');
require_once('../dbo/records.php');
/**#@-*/

init_page();

$id     = ustr2int(try_request('id'));
$record = record_find($id);

if (!$record)
{
    debug_write_log(DEBUG_NOTICE, 'Record cannot be found.');
    header('Location: index.php');
    exit;
}

$state_id = ustr2int(try_request('state'));
$state    = state_find($state_id);

if (!$state)
{
    debug_write_log(DEBUG_NOTICE, 'State cannot be found.');
    header('Location: view.php?id=' . $id);
    exit;
}

$permissions = record_get_permissions($record['template_id'], $record['creator_id'], $record['responsible_id']);

if (!can_state_be_changed($record, $permissions))
{
    debug_write_log(DEBUG_NOTICE, 'State cannot be changed.');
    header('Location: view.php?id=' . $id);
    exit;
}

$rs = dal_query('depends/listuc.sql', $id);

if ($rs->rows != 0 && $state['state_type'] == STATE_TYPE_FINAL)
{
    debug_write_log(DEBUG_NOTICE, 'The record has unclosed dependencies.');
    header('Location: view.php?id=' . $id);
    exit;
}

$rs = dal_query('records/tramongs.sql', $id, $_SESSION[VAR_USERID], '');

if ($rs->rows == 0)
{
    debug_write_log(DEBUG_NOTICE, 'No permissions to change to specified state.');
    header('Location: view.php?id=' . $id);
    exit;
}

if (try_request('submitted') == 'fieldsform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    json2request($HTTP_RAW_POST_DATA);

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

        if ($error == NO_ERROR)
        {
            header('Location: view.php?id=' . $id);
            exit;
        }
    }

    switch ($error)
    {
        case ERROR_INCOMPLETE_FORM:
            header('HTTP/1.0 200 ' . get_js_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID));
            break;

        case ERROR_INVALID_INTEGER_VALUE:
            header('HTTP/1.0 200 ' . get_js_resource(RES_ALERT_INVALID_INTEGER_VALUE_ID));
            break;

        case ERROR_INVALID_DATE_VALUE:
            header('HTTP/1.0 200 ' . get_js_resource(RES_ALERT_INVALID_DATE_VALUE_ID));
            break;

        case ERROR_INVALID_TIME_VALUE:
            header('HTTP/1.0 200 ' . get_js_resource(RES_ALERT_INVALID_TIME_VALUE_ID));
            break;

        case ERROR_INTEGER_VALUE_OUT_OF_RANGE:
        case ERROR_DATE_VALUE_OUT_OF_RANGE:
        case ERROR_TIME_VALUE_OUT_OF_RANGE:
            header('HTTP/1.0 200 ' . ustrprocess(get_js_resource(RES_ALERT_FIELD_VALUE_OUT_OF_RANGE_ID), $_SESSION['FIELD_NAME'], $_SESSION['MIN_FIELD_INTEGER'], $_SESSION['MAX_FIELD_INTEGER']));
            unset($_SESSION['FIELD_NAME']);
            unset($_SESSION['MIN_FIELD_INTEGER']);
            unset($_SESSION['MAX_FIELD_INTEGER']);
            break;

        case ERROR_RECORD_NOT_FOUND:
            header('HTTP/1.0 200 ' . get_js_resource(RES_ALERT_RECORD_NOT_FOUND_ID));
            break;

        case ERROR_VALUE_FAILS_REGEX_CHECK:
            header('HTTP/1.0 200 ' . ustrprocess(get_js_resource(RES_ALERT_VALUE_FAILS_REGEX_CHECK_ID), $_SESSION['FIELD_NAME'], $_SESSION['FIELD_VALUE']));
            unset($_SESSION['FIELD_NAME']);
            unset($_SESSION['FIELD_VALUE']);
            break;

        default: ;  // nop
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $responsible_id = NULL;
}

$xml = '<form name="fieldsform" action="javascript:submitFields()">'
     . '<group title="' . ustr2html($state['state_name']) . '">';

if ($state['responsible'] == STATE_RESPONSIBLE_ASSIGN)
{
    debug_write_log(DEBUG_NOTICE, 'Record should be assigned.');

    $rs = dal_query('records/responsibles.sql', $record['project_id'], $state_id, $record['creator_id']);

    if ($rs->rows != 0)
    {
        $default_responsible = (is_null($record['responsible_id']) ? $_SESSION[VAR_USERID] : $record['responsible_id']);

        $xml .= '<combobox label="' . get_html_resource(RES_RESPONSIBLE_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="responsible" extended="true">';

        while (($row = $rs->fetch()))
        {
            $xml .= '<listitem value="' . $row['account_id'] . ($row['account_id'] == $default_responsible ? '" selected="true">' : '">') . ustr2html($row['fullname']) . ' (' . ustr2html(account_get_username($row['username'])) . ')</listitem>';
        }

        $xml .= '</combobox>';
    }
}

$flag1 = FALSE;
$flag2 = FALSE;
$notes = NULL;

$rs = dal_query('fields/listv.sql', $id, $state_id);

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
    debug_write_log(DEBUG_NOTICE, 'Fields of specified state are being enumerated.');

    while (($row = $rs->fetch()))
    {
        $name  = 'field' . $row['field_id'];
        $value = NULL;

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

        switch ($row['field_type'])
        {
            case FIELD_TYPE_NUMBER:

                $xml .= '<editbox label="' . ustr2html($row['field_name']) . ($row['is_required'] ? '" required="' . get_html_resource(RES_REQUIRED3_ID) : NULL) . '" name="' . $name . '" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . (ustrlen(MAX_FIELD_INTEGER) + 1) . '">' . ustr2html(try_request($name, $value)) . '</editbox>';
                $notes .= '<note>' . ustrprocess(get_html_resource(RES_ALERT_FIELD_VALUE_OUT_OF_RANGE_ID), $row['field_name'], $row['param1'], $row['param2']) . '</note>';
                break;

            case FIELD_TYPE_STRING:

                $xml .= '<editbox label="' . ustr2html($row['field_name']) . ($row['is_required'] ? '" required="' . get_html_resource(RES_REQUIRED3_ID) : NULL) . '" name="' . $name . '" size="' . HTML_EDITBOX_SIZE_LONG . '" maxlen="' . $row['param1'] . '">' . ustr2html(try_request($name, $value)) . '</editbox>';
                $flag2 = TRUE;
                break;

            case FIELD_TYPE_MULTILINED:

                $xml .= '<textbox label="' . ustr2html($row['field_name']) . ($row['is_required'] ? '" required="' . get_html_resource(RES_REQUIRED3_ID) : NULL) . '" name="' . $name . '" width="' . HTML_TEXTBOX_WIDTH . '" height="' . HTML_TEXTBOX_MIN_HEIGHT . '" resizeable="true" maxlen="' . MAX_FIELD_MULTILINED . '">' . ustr2html(try_request($name, $value)) . '</textbox>';
                $flag2 = TRUE;
                break;

            case FIELD_TYPE_CHECKBOX:

                $xml .= '<checkbox name="' . $name . ((try_request('submitted') == 'fieldsform' ? isset($_REQUEST[$name]) : $value) ? '" checked="true">' : '">') . ustr2html($row['field_name']) . '</checkbox>';
                break;

            case FIELD_TYPE_LIST:

                $selected = try_request($name, $value);

                $xml .= '<combobox label="' . ustr2html($row['field_name']) . ($row['is_required'] ? '" required="' . get_html_resource(RES_REQUIRED3_ID) : NULL) . '" name="' . $name . '">' . ustr2html(try_request($name))
                      . '<listitem value=""></listitem>';

                $rsv = dal_query('values/lvlist.sql', $row['field_id']);

                while (($item = $rsv->fetch()))
                {
                    $xml .= '<listitem value="' . $item['int_value'] . ($selected == $item['int_value'] ? '" selected="true">' : '">')
                          . ustr2html($item['str_value'])
                          . '</listitem>';
                }

                $xml .= '</combobox>';

                break;

            case FIELD_TYPE_RECORD:

                $xml .= '<editbox label="' . ustr2html($row['field_name']) . ($row['is_required'] ? '" required="' . get_html_resource(RES_REQUIRED3_ID) : NULL) . '" name="' . $name . '" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(MAXINT) . '">' . ustr2html(try_request($name, $value)) . '</editbox>';
                $notes .= '<note>' . ustrprocess(get_html_resource(RES_ALERT_FIELD_VALUE_OUT_OF_RANGE_ID), $row['field_name'], 1, MAXINT) . '</note>';
                break;

            case FIELD_TYPE_DATE:

                $today = time();

                $row['param1'] = date_offset($today, $row['param1']);
                $row['param2'] = date_offset($today, $row['param2']);

                $xml .= '<editbox label="' . sprintf('%s (%s)', ustr2html($row['field_name']), get_html_resource(RES_YYYY_MM_DD_ID)) . ($row['is_required'] ? '" required="' . get_html_resource(RES_REQUIRED3_ID) : NULL) . '" name="' . $name . '" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(get_date(SAMPLE_DATE)) . '">' . ustr2html(try_request($name, $value)) . '</editbox>';
                $notes .= '<note>' . ustrprocess(get_html_resource(RES_ALERT_FIELD_VALUE_OUT_OF_RANGE_ID), $row['field_name'], get_date($row['param1']), get_date($row['param2'])) . '</note>';
                break;

            case FIELD_TYPE_DURATION:

                $xml .= '<editbox label="' . ustr2html($row['field_name']) . ($row['is_required'] ? '" required="' . get_html_resource(RES_REQUIRED3_ID) : NULL) . '" name="' . $name . '" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(time2ustr(MAX_FIELD_DURATION)) . '">' . ustr2html(try_request($name, $value)) . '</editbox>';
                $notes .= '<note>' . ustrprocess(get_html_resource(RES_ALERT_FIELD_VALUE_OUT_OF_RANGE_ID), $row['field_name'], time2ustr($row['param1']), time2ustr($row['param2'])) . '</note>';
                break;

            default:
                debug_write_log(DEBUG_WARNING, 'Unknown field type = ' . $row['field_type']);
        }

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
      . '<button default="true">'           . get_html_resource(RES_OK_ID)     . '</button>'
      . '<button action="cancelFields();">' . get_html_resource(RES_CANCEL_ID) . '</button>'
      . $notes
      . '</form>';

echo(xml2html($xml));

?>
