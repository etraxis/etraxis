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
//  Artem Rodygin           2005-03-27      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-07-28      new-012: Records field 'description' should be renamed with 'subject'.
//  Artem Rodygin           2005-07-31      new-006: Records search.
//  Artem Rodygin           2005-08-06      new-019: Fields default values.
//  Artem Rodygin           2005-08-13      new-022: New records should be viewed immediately after creation.
//  Artem Rodygin           2005-08-13      new-020: Clone the records.
//  Artem Rodygin           2005-08-13      new-023: Auto-choosing project/template when new record is being created.
//  Artem Rodygin           2005-08-17      bug-031: 'Cancel' button of 'Clone' page moves a user to wrong destination.
//  Artem Rodygin           2005-08-23      bug-045: When record is being cloned wrong event is recorded.
//  Artem Rodygin           2005-08-27      bug-063: No error message is displayed when non-existing record is specified in field of 'record' type.
//  Artem Rodygin           2005-08-30      bug-080: 'Record' type fields of some record should not accept ID of this record.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-09-07      bug-098: List of users when record is being assigned should contain only allowed users.
//  Artem Rodygin           2005-09-07      new-100: 'Date' field type should be implemented.
//  Artem Rodygin           2005-09-08      new-101: 'Duration' field type should be implemented.
//  Artem Rodygin           2005-09-12      new-105: Format of date values are being entered should depend on user locale settings.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-11-17      new-176: Change eTraxis design.
//  Artem Rodygin           2005-11-27      bug-184: Entered values of checkbox fields are lost on refresh when record is being created.
//  Artem Rodygin           2006-03-18      new-175: Implement user roles in permissions.
//  Artem Rodygin           2006-05-17      new-005: Oracle support.
//  Artem Rodygin           2006-07-12      bug-292: Sablotron fails if page contains '&' character.
//  Artem Rodygin           2006-08-13      new-305: Note with explanation of links to other records should be added where needed.
//  Artem Rodygin           2006-09-26      new-318: Group permissions should be template-wide.
//  Artem Rodygin           2006-11-04      new-364: Default fields values.
//  Artem Rodygin           2006-11-20      new-392: Local users should not be extended with '@eTraxis' when LDAP is disabled.
//  Artem Rodygin           2006-11-27      bug-396: Double click on record submitting causes two equal records creation.
//  Artem Rodygin           2006-12-23      new-463: Date field names should be extended with date format explanation.
//  Artem Rodygin           2006-12-26      bug-465: When template is locked all records created by this template must be read only.
//  Artem Rodygin           2006-12-27      bug-470: State permissions must not be used when record is being created.
//  Artem Rodygin           2007-03-24      bug-511: Selected responsible is not restored.
//  Artem Rodygin           2007-08-08      new-549: User should be able to create new dependency record.
//  Artem Rodygin           2007-08-25      bug-569: PHP Notice: Undefined variable: parent
//  Artem Rodygin           2007-09-09      new-563: Custom separators inside fields set.
//  Artem Rodygin           2007-10-23      new-600: User should be able to create new child w/out dependency.
//  Artem Rodygin           2007-11-13      new-622: Rename 'children' into 'subrecords'.
//  Yury Udovichenko        2007-11-14      new-548: Custom links in text fields.
//  Artem Rodygin           2007-11-27      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2008-01-11      bug-663: Author permissions are ignored.
//  Artem Rodygin           2008-02-08      new-671: Default value for 'date' fields should be relative.
//  Artem Rodygin           2008-02-25      bug-678: Default value of combo box field is ignored.
//  Artem Rodygin           2008-02-28      new-294: PostgreSQL support.
//  Artem Rodygin           2008-04-20      new-703: Separated permissions set for current responsible.
//  Artem Rodygin           2008-09-11      new-716: 'Today' value in date field range.
//  Artem Rodygin           2008-09-11      bug-742: Not all expected notes are present when record is being created/modified.
//  Artem Rodygin           2008-10-14      new-752: [SF2162856] Add file attachments when creating a new record
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2008-12-09      bug-773: 'Search results' link is lost in "links path" on 'clone' and 'create subrecord' operations.
//  Artem Rodygin           2009-04-25      new-801: Range of valid date values must be related to current date.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
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

$id = ustr2int(try_request('id'));

if ($id == 0)
{
    $parent        = record_find(ustr2int(try_request('parent')));
    $is_dependency = TRUE;

    if ($parent)
    {
        debug_write_log(DEBUG_NOTICE, 'Data for new subrecord creating are being requested.');
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, 'Data for new record creating are being requested.');
    }

    if (!can_record_be_created())
    {
        debug_write_log(DEBUG_NOTICE, 'Record cannot be created.');
        header('Location: index.php');
        exit;
    }

    $form  = 'projectform';
    $focus = '.project';
    $step  = 1;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data for record cloning are being requested.');

    $record        = record_find($id);
    $parent        = FALSE;
    $is_dependency = TRUE;

    if (!$record)
    {
        debug_write_log(DEBUG_NOTICE, 'Record cannot be found.');
        header('Location: index.php');
        exit;
    }

    $permissions = record_get_permissions($record['template_id'], $record['creator_id'], $record['responsible_id']);

    if (!can_record_be_created())
    {
        debug_write_log(DEBUG_NOTICE, 'Record cannot be cloned.');
        header('Location: view.php?id=' . $id);
        exit;
    }

    $subject     = $record['subject'];
    $project_id  = $record['project_id'];
    $template_id = $record['template_id'];

    if (DATABASE_DRIVER == DRIVER_ORACLE9)
    {
        $rs = dal_query('records/oracle/tfndid.sql', $_SESSION[VAR_USERID], $project_id, $template_id);
    }
    else
    {
        $rs = dal_query('records/tfndid.sql', $_SESSION[VAR_USERID], $project_id, $template_id);
    }

    if ($rs->rows == 0)
    {
        debug_write_log(DEBUG_NOTICE, 'Template cannot be found.');
        header('Location: view.php?id=' . $id);
        exit;
    }

    $row = $rs->fetch();

    $project_name  = $row['project_name'];
    $template_name = $row['template_name'];
    $state_id      = $row['state_id'];
    $state_name    = $row['state_name'];
    $responsible   = $row['responsible'];

    $form  = 'mainform';
    $focus = '.subject';
    $step  = 3;
}

if (try_request('submitted') == 'projectform')
{
    debug_write_log(DEBUG_NOTICE, 'Data for step #2 (template) are being requested.');

    $project_id = ustr2int(try_request('project'));

    if (DATABASE_DRIVER == DRIVER_ORACLE9)
    {
        $rs = dal_query('records/oracle/pfndid.sql', $_SESSION[VAR_USERID], $project_id);
    }
    else
    {
        $rs = dal_query('records/pfndid.sql', $_SESSION[VAR_USERID], $project_id);
    }

    if ($rs->rows == 0)
    {
        debug_write_log(DEBUG_NOTICE, 'Project cannot be found.');
        header('Location: index.php');
        exit;
    }

    $project_name = $rs->fetch('project_name');

    $form  = 'templateform';
    $focus = '.template';
    $step  = 2;
}
elseif (try_request('submitted') == 'templateform')
{
    debug_write_log(DEBUG_NOTICE, 'Data for step #3 (final) are being requested.');

    $subject        = ($parent ? $parent['subject'] : NULL);
    $responsible_id = NULL;
    $project_id     = ustr2int(try_request('project'));
    $template_id    = ustr2int(try_request('template'));

    if (DATABASE_DRIVER == DRIVER_ORACLE9)
    {
        $rs = dal_query('records/oracle/tfndid.sql', $_SESSION[VAR_USERID], $project_id, $template_id);
    }
    else
    {
        $rs = dal_query('records/tfndid.sql', $_SESSION[VAR_USERID], $project_id, $template_id);
    }

    if ($rs->rows == 0)
    {
        debug_write_log(DEBUG_NOTICE, 'Template cannot be found.');
        header('Location: index.php');
        exit;
    }

    $row = $rs->fetch();

    $project_name  = $row['project_name'];
    $template_name = $row['template_name'];
    $state_id      = $row['state_id'];
    $state_name    = $row['state_name'];
    $responsible   = $row['responsible'];

    $form  = 'mainform';
    $focus = '.subject';
    $step  = 3;
}
elseif (try_request('submitted') == 'mainform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $subject     = ustrcut($_REQUEST['subject'], MAX_RECORD_SUBJECT);
    $project_id  = ustr2int(try_request('project'));
    $template_id = ustr2int(try_request('template'));

    if (DATABASE_DRIVER == DRIVER_ORACLE9)
    {
        $rs = dal_query('records/oracle/tfndid.sql', $_SESSION[VAR_USERID], $project_id, $template_id);
    }
    else
    {
        $rs = dal_query('records/tfndid.sql', $_SESSION[VAR_USERID], $project_id, $template_id);
    }

    if ($rs->rows == 0)
    {
        debug_write_log(DEBUG_NOTICE, 'Template cannot be found.');
        header('Location: index.php');
        exit;
    }

    $row = $rs->fetch();

    $project_name  = $row['project_name'];
    $template_name = $row['template_name'];
    $state_id      = $row['state_id'];
    $state_name    = $row['state_name'];
    $responsible   = $row['responsible'];

    $rs = dal_query('records/efnd.sql', $_SESSION[VAR_USERID], EVENT_RECORD_CREATED, time() - 3, $state_id);

    if ($rs->rows != 0)
    {
        debug_write_log(DEBUG_NOTICE, 'Double click issue is detected.');
        header('Location: index.php');
        exit;
    }

    $responsible_id = try_request('responsible');
    $is_dependency  = isset($_REQUEST['is_dependency']);

    $error = record_validate(OPERATION_CREATE_RECORD, $subject, NULL, $state_id);

    if ($error == NO_ERROR)
    {
        $record_id = 0;

        $error = record_create($record_id,
                               $subject,
                               $state_id,
                               $responsible_id,
                               $id);

        if ($error == NO_ERROR)
        {
            if ($parent)
            {
                subrecord_add($parent['record_id'], $record_id, $is_dependency);
            }

            $attachname = ustrcut($_REQUEST['attachname'], MAX_ATTACHMENT_NAME);
            attachment_add($record_id, $attachname, $_FILES['attachfile']);

            header('Location: view.php?id=' . $record_id);
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

    $form  = 'mainform';
    $focus = '.subject';
    $step  = 3;
}

$xml = '<page' . gen_xml_page_header(ustrprocess(get_html_resource(RES_NEW_RECORD_ID), $step, 3), isset($alert) ? $alert : NULL, $form . $focus) . '>'
     . gen_xml_menu()
     . '<path>'
     . gen_xml_rec_root(($id == 0 && !$parent) ? FALSE : try_cookie(COOKIE_SEARCH_MODE, FALSE));

if ($id == 0)
{
    if ($parent)
    {
        $xml .= '<pathitem url="view.php?id='       . $parent['record_id'] . '">' . ustrprocess(get_html_resource(RES_RECORD_X_ID), record_id($parent['record_id'], $parent['template_prefix'])) . '</pathitem>'
              . '<pathitem url="create.php?parent=' . $parent['record_id'] . '">' . ustrprocess(get_html_resource(RES_NEW_RECORD_ID), $step, 3) . '</pathitem>';
    }
    else
    {
        $xml .= '<pathitem url="create.php">' . ustrprocess(get_html_resource(RES_NEW_RECORD_ID), $step, 3) . '</pathitem>';
    }
}
else
{
    $xml .= '<pathitem url="view.php?id='   . $id . '">' . ustrprocess(get_html_resource(RES_RECORD_X_ID), record_id($id, $record['template_prefix'])) . '</pathitem>'
          . '<pathitem url="create.php?id=' . $id . '">' . get_html_resource(RES_CLONE_ID) . '</pathitem>';
}

$xml .= '</path>'
      . '<content>'
      . '<form name="' . $form . '" action="create.php' . ($id == 0 ? ($parent ? '?parent=' . $parent['record_id'] : NULL) : '?id=' . $id) . '" upload="' . (ATTACHMENTS_MAXSIZE * 1024) . '">'
      . '<group title="' . get_html_resource(RES_GENERAL_INFO_ID) . '">';

if ($step == 1)
{
    debug_write_log(DEBUG_NOTICE, 'Step #1 (project) is being proceeded.');

    $xml .= '<combobox label="' . get_html_resource(RES_PROJECT_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="project">';

    if (DATABASE_DRIVER == DRIVER_ORACLE9)
    {
        $rs = dal_query('records/oracle/plist.sql', $_SESSION[VAR_USERID]);
    }
    else
    {
        $rs = dal_query('records/plist.sql', $_SESSION[VAR_USERID]);
    }

    if ($rs->rows == 1)
    {
        debug_write_log(DEBUG_NOTICE, 'One project only is found.');
        header('Location: create.php?submitted=projectform&project=' . $rs->fetch('project_id') . ($parent ? '&parent=' . $parent['record_id'] : NULL));
        exit;
    }

    while (($row = $rs->fetch()))
    {
        $xml .= '<listitem value="' . $row['project_id'] . '">' . ustr2html($row['project_name']) . '</listitem>';
    }

    $xml .= '</combobox>';
}
else
{
    $xml .= '<combobox label="' . get_html_resource(RES_PROJECT_ID) . '" name="project">'
          . '<listitem value="' . $project_id . '">' . ustr2html($project_name) . '</listitem>'
          . '</combobox>';

    if ($step == 2)
    {
        debug_write_log(DEBUG_NOTICE, 'Step #2 (template) is being proceeded.');

        $xml .= '<combobox label="' . get_html_resource(RES_TEMPLATE_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="template">';

        if (DATABASE_DRIVER == DRIVER_ORACLE9)
        {
            $rs = dal_query('records/oracle/tlist.sql', $_SESSION[VAR_USERID], $project_id);
        }
        else
        {
            $rs = dal_query('records/tlist.sql', $_SESSION[VAR_USERID], $project_id);
        }

        if ($rs->rows == 1)
        {
            debug_write_log(DEBUG_NOTICE, 'One template only is found.');
            header('Location: create.php?submitted=templateform&project=' . $project_id . '&template=' . $rs->fetch('template_id') . ($parent ? '&parent=' . $parent['record_id'] : NULL));
            exit;
        }

        while (($row = $rs->fetch()))
        {
            $xml .= '<listitem value="' . $row['template_id'] . '">' . ustr2html($row['template_name']) . '</listitem>';
        }

        $xml .= '</combobox>';
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, 'Step #3 (final) is being proceeded.');

        $xml .= '<combobox label="' . get_html_resource(RES_TEMPLATE_ID) . '" name="template">'
              . '<listitem value="' . $template_id . '">' . ustr2html($template_name) . '</listitem>'
              . '</combobox>'
              . '<editbox label="' . get_html_resource(RES_SUBJECT_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="subject" size="' . HTML_EDITBOX_SIZE_LONG . '" maxlen="' . MAX_RECORD_SUBJECT . '">' . ustr2html($subject) . '</editbox>';

        if ($responsible == STATE_RESPONSIBLE_ASSIGN)
        {
            debug_write_log(DEBUG_NOTICE, 'Record should be assigned.');

            $rs = dal_query('records/responsibles.sql', $project_id, $state_id, $_SESSION[VAR_USERID]);

            if ($rs->rows != 0)
            {
                $xml .= '<combobox label="' . get_html_resource(RES_RESPONSIBLE_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="responsible">';

                while (($row = $rs->fetch()))
                {
                    $xml .= '<listitem value="' . $row['account_id'] . ($row['account_id'] == $responsible_id ? '" selected="true">' : '">') . ustr2html($row['fullname']) . ' (' . ustr2html(account_get_username($row['username'])) . ')</listitem>';
                }

                $xml .= '</combobox>';
            }
        }

        if ($parent)
        {
            $xml .= '<checkbox name="is_dependency"' . ($is_dependency ? ' checked="true">' : '>') . get_html_resource(RES_DEPENDENCY_ID) . '</checkbox>';
        }
    }
}

$xml .= '</group>';

$flag  = FALSE;
$notes = '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>';

if ($step == 3)
{
    $rs = dal_query('fields/list.sql', $state_id, 'field_order');

    if ($rs->rows == 0)
    {
        debug_write_log(DEBUG_NOTICE, 'No fields for initial state are found.');
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, 'Fields of initial state are being enumerated.');

        $xml .= '<group title="' . ustr2html($state_name) . '">';

        while (($row = $rs->fetch()))
        {
            $name  = 'field' . $row['field_id'];
            $value = NULL;

            if ($id != 0)
            {
                $rsv = dal_query('values/fndk.sql', $id, $row['field_id']);

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
                    $flag = TRUE;
                    break;

                case FIELD_TYPE_MULTILINED:

                    $xml .= '<textbox label="' . ustr2html($row['field_name']) . ($row['is_required'] ? '" required="' . get_html_resource(RES_REQUIRED3_ID) : NULL) . '" name="' . $name . '" width="' . HTML_TEXTBOX_WIDTH . '" height="' . HTML_TEXTBOX_HEIGHT . '" maxlen="' . MAX_FIELD_MULTILINED . '">' . ustr2html(try_request($name, $value)) . '</textbox>';
                    $flag = TRUE;
                    break;

                case FIELD_TYPE_CHECKBOX:

                    $xml .= '<label/><checkbox name="' . $name . ((try_request('submitted') == 'mainform' ? isset($_REQUEST[$name]) : $value) ? '" checked="true">' : '">') . ustr2html($row['field_name']) . '</checkbox>';
                    break;

                case FIELD_TYPE_LIST:

                    $selected = try_request($name, $value);

                    $xml .= '<combobox label="' . ustr2html($row['field_name']) . ($row['is_required'] ? '" required="' . get_html_resource(RES_REQUIRED3_ID) : NULL) . '" name="' . $name . '">'
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

        $xml .= '</group>';
    }

    $xml .= '<group title="' . get_html_resource(RES_ATTACH_FILE_ID) . '">'
          . '<editbox label="' . get_html_resource(RES_ATTACHMENT_NAME_ID) . '" name="attachname" size="' . HTML_EDITBOX_SIZE_MEDIUM . '" maxlen="' . MAX_ATTACHMENT_NAME . '"/>'
          . '<filebox label="' . get_html_resource(RES_ATTACHMENT_FILE_ID) . '" name="attachfile" size="' . HTML_EDITBOX_SIZE_MEDIUM . '"/>'
          . '</group>';

    $notes .= '<note>' . ustrprocess(get_html_resource(RES_ALERT_UPLOAD_FORM_SIZE_ID), ATTACHMENTS_MAXSIZE) . '</note>';
}

if ($flag)
{
    $notes .= '<note>' . get_html_resource(RES_LINK_TO_ANOTHER_RECORD_ID) . '</note>';
}

$xml .= '<button default="true">' . get_html_resource($form == 'mainform' ? RES_OK_ID : RES_NEXT_ID) . '</button>'
      . '<button url="' . ($id == 0 ? ($parent ? 'view.php?id=' . $parent['record_id'] : 'index.php') : 'view.php?id=' . $id) . '">' . get_html_resource(RES_CANCEL_ID) . '</button>'
      . $notes
      . '</form>'
      . '</content>'
      . '</page>';

echo(xml2html($xml));

?>
