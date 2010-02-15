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
//  Artem Rodygin           2005-04-23      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-07-04      bug-010: Missing 'require' operator.
//  Artem Rodygin           2005-07-28      new-012: Records field 'description' should be renamed with 'subject'.
//  Artem Rodygin           2005-07-31      new-006: Records search.
//  Artem Rodygin           2005-08-27      bug-063: No error message is displayed when non-existing record is specified in field of 'record' type.
//  Artem Rodygin           2005-08-30      bug-080: 'Record' type fields of some record should not accept ID of this record.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-09-07      new-100: 'Date' field type should be implemented.
//  Artem Rodygin           2005-09-08      new-101: 'Duration' field type should be implemented.
//  Artem Rodygin           2005-09-12      new-105: Format of date values are being entered should depend on user locale settings.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-11-17      new-176: Change eTraxis design.
//  Artem Rodygin           2006-03-19      new-175: Implement user roles in permissions.
//  Artem Rodygin           2006-03-20      bug-218: Server is overloaded.
//  Artem Rodygin           2006-03-25      bug-225: User is remained on record modification page when 'OK' button has been clicked.
//  Artem Rodygin           2006-04-21      new-247: The 'responsible' user role should be obliterated.
//  Artem Rodygin           2006-07-12      bug-292: Sablotron fails if page contains '&' character.
//  Artem Rodygin           2006-07-27      new-261: UI design should be adopted to slow connection.
//  Artem Rodygin           2006-08-13      new-305: Note with explanation of links to other records should be added where needed.
//  Artem Rodygin           2006-09-26      new-318: Group permissions should be template-wide.
//  Artem Rodygin           2006-12-23      new-463: Date field names should be extended with date format explanation.
//  Artem Rodygin           2007-09-09      new-563: Custom separators inside fields set.
//  Yury Udovichenko        2007-11-14      new-548: Custom links in text fields.
//  Artem Rodygin           2007-11-27      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2008-01-11      bug-663: Author permissions are ignored.
//  Artem Rodygin           2008-04-20      new-703: Separated permissions set for current responsible.
//  Artem Rodygin           2008-09-11      new-716: 'Today' value in date field range.
//  Artem Rodygin           2008-09-11      bug-742: Not all expected notes are present when record is being created/modified.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-04-25      new-801: Range of valid date values must be related to current date.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//  Giacomo Giustozzi       2010-02-10      new-913: Resizable text boxes
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
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

$permissions = record_get_permissions($record['template_id'], $record['creator_id'], $record['responsible_id']);

if (!can_record_be_modified($record, $permissions))
{
    debug_write_log(DEBUG_NOTICE, 'Record cannot be modified.');
    header('Location: view.php?id=' . $id);
    exit;
}

if (try_request('submitted') == 'mainform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $subject = ustrcut($_REQUEST['subject'], MAX_RECORD_SUBJECT);

    $rs = dal_query('records/elist.sql', $id);

    $error = NO_ERROR;

    while (($row = $rs->fetch()) && ($error == NO_ERROR))
    {
        $error = record_validate(OPERATION_MODIFY_RECORD, $subject, $id, $row['state_id'], $record['creator_id'], $record['responsible_id']);
    }

    if ($error == NO_ERROR)
    {
        $error = record_modify($id, $subject, $record['creator_id'], $record['responsible_id']);

        if ($error == NO_ERROR)
        {
            header('Location: view.php?id=' . $id);
            exit;
        }
    }

    switch ($error)
    {
        case ERROR_INCOMPLETE_FORM:
            $alert = get_js_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID);
            break;
        case ERROR_INVALID_INTEGER_VALUE:
            $alert = get_js_resource(RES_ALERT_INVALID_INTEGER_VALUE_ID);
            break;
        case ERROR_INVALID_DATE_VALUE:
            $alert = get_js_resource(RES_ALERT_INVALID_DATE_VALUE_ID);
            break;
        case ERROR_INVALID_TIME_VALUE:
            $alert = get_js_resource(RES_ALERT_INVALID_TIME_VALUE_ID);
            break;
        case ERROR_INTEGER_VALUE_OUT_OF_RANGE:
        case ERROR_DATE_VALUE_OUT_OF_RANGE:
        case ERROR_TIME_VALUE_OUT_OF_RANGE:
            $alert = ustrprocess(get_js_resource(RES_ALERT_FIELD_VALUE_OUT_OF_RANGE_ID), $_SESSION['FIELD_NAME'], $_SESSION['MIN_FIELD_INTEGER'], $_SESSION['MAX_FIELD_INTEGER']);
            unset($_SESSION['FIELD_NAME']);
            unset($_SESSION['MIN_FIELD_INTEGER']);
            unset($_SESSION['MAX_FIELD_INTEGER']);
            break;
        case ERROR_RECORD_NOT_FOUND:
            $alert = get_js_resource(RES_ALERT_RECORD_NOT_FOUND_ID);
            break;
        case ERROR_VALUE_FAILS_REGEX_CHECK:
            $alert = ustrprocess(get_js_resource(RES_ALERT_VALUE_FAILS_REGEX_CHECK_ID), $_SESSION['FIELD_NAME'], $_SESSION['FIELD_VALUE']);
            unset($_SESSION['FIELD_NAME']);
            unset($_SESSION['FIELD_VALUE']);
            break;
        default:
            $alert = NULL;
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $subject = $record['subject'];
}

$xml = '<page' . gen_xml_page_header(record_id($id, $record['template_prefix']), isset($alert) ? $alert : NULL, 'mainform.subject') . '>'
     . gen_xml_menu()
     . '<path>'
     . gen_xml_rec_root(try_cookie(COOKIE_SEARCH_MODE, FALSE))
     . '<pathitem url="view.php?id='   . $id . '">' . ustrprocess(get_html_resource(RES_RECORD_X_ID), record_id($id, $record['template_prefix'])) . '</pathitem>'
     . '<pathitem url="modify.php?id=' . $id . '">' . get_html_resource(RES_MODIFY_ID) . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<form name="mainform" action="modify.php?id=' . $id . '">'
     . '<group title="' . get_html_resource(RES_GENERAL_INFO_ID) . '">'
     . '<editbox label="' . get_html_resource(RES_SUBJECT_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="subject" size="' . HTML_EDITBOX_SIZE_LONG . '" maxlen="' . MAX_RECORD_SUBJECT . '">' . ustr2html($subject) . '</editbox>'
     . '</group>';

$flag  = FALSE;
$notes = '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>';

$rs = dal_query('records/elist.sql', $id);

while (($row = $rs->fetch()))
{
    $rsf = dal_query('records/flist.sql',
                     $id,
                     $row['state_id'],
                     $record['creator_id'],
                     is_null($record['responsible_id']) ? 0 : $record['responsible_id'],
                     $_SESSION[VAR_USERID],
                     FIELD_ALLOW_TO_WRITE);

    $event_time = $row['event_time'];

    if ($rsf->rows != 0)
    {
        $xml .= '<group title="' . ustr2html($row['state_name']) . '">';

        while (($row = $rsf->fetch()))
        {
            $name  = 'field' . $row['field_id'];
            $value = value_find($row['field_type'], $row['value_id']);

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
                    $flag = TRUE;
                    break;

                case FIELD_TYPE_MULTILINED:

                    $xml .= '<textbox label="' . ustr2html($row['field_name']) . ($row['is_required'] ? '" required="' . get_html_resource(RES_REQUIRED3_ID) : NULL) . '" name="' . $name . '" width="' . HTML_TEXTBOX_WIDTH . '" height="' . HTML_TEXTBOX_MIN_HEIGHT . '" resizeable="true" maxlen="' . MAX_FIELD_MULTILINED . '">' . ustr2html(try_request($name, $value)) . '</textbox>';
                    $flag = TRUE;
                    break;

                case FIELD_TYPE_CHECKBOX:

                    $xml .= '<checkbox name="' . $name . ($value ? '" checked="true">' : '">') . ustr2html($row['field_name']) . '</checkbox>';
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

                    $row['param1'] = date_offset($event_time, $row['param1']);
                    $row['param2'] = date_offset($event_time, $row['param2']);

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

        $xml .= '</group>';
    }
}

if ($flag)
{
    $notes .= '<note>' . get_html_resource(RES_LINK_TO_ANOTHER_RECORD_ID) . '</note>';
}

$xml .= '<button default="true">'                . get_html_resource(RES_OK_ID)     . '</button>'
      . '<button url="view.php?id=' . $id . '">' . get_html_resource(RES_CANCEL_ID) . '</button>'
      . $notes
      . '</form>'
      . '</content>'
      . '</page>';

echo(xml2html($xml));

?>
