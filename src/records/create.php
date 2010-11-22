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
/**#@-*/

init_page();

$error = NO_ERROR;

// check whether a cloning was requested

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

    $subject        = $record['subject'];
    $responsible_id = $record['responsible_id'];
    $project_id     = $record['project_id'];
    $template_id    = $record['template_id'];

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

// project has been selected

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

// template has been selected

elseif (try_request('submitted') == 'templateform')
{
    debug_write_log(DEBUG_NOTICE, 'Data for step #3 (final) are being requested.');

    $subject        = ($parent ? $parent['subject'] : NULL);
    $responsible_id = ($id == 0 ? NULL : $record['responsible_id']);
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

// new record has been submitted

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

            if (isset($_REQUEST['attachname']) && ATTACHMENTS_ENABLED)
            {
                $attachname = ustrcut($_REQUEST['attachname'], MAX_ATTACHMENT_NAME);
                attachment_add($record_id, $attachname, $_FILES['attachfile']);
            }

            record_read($record_id);

            header($parent ? 'Location: subrecords.php?id=' . $parent['record_id']
                           : 'Location: view.php?id=' . $record_id);
            exit;
        }
    }

    $form  = 'mainform';
    $focus = '.subject';
    $step  = 3;
}

// generate breadcrumbs

$xml = '<breadcrumbs>';

if ($id != 0)
{
    $xml .= '<breadcrumb url="index.php">' . get_html_resource(RES_RECORDS_ID) . '</breadcrumb>'
          . '<breadcrumb url="view.php?id='   . $id . '">' . ustrprocess(get_html_resource(RES_RECORD_X_ID), record_id($id, $record['template_prefix'])) . '</breadcrumb>'
          . '<breadcrumb url="create.php?id=' . $id . '">' . get_html_resource(RES_CLONE_ID) . '</breadcrumb>';
}
elseif ($parent)
{
    $xml .= '<breadcrumb url="index.php">' . get_html_resource(RES_RECORDS_ID) . '</breadcrumb>'
          . '<breadcrumb url="view.php?id='       . $parent['record_id'] . '">' . ustrprocess(get_html_resource(RES_RECORD_X_ID), record_id($parent['record_id'], $parent['template_prefix'])) . '</breadcrumb>'
          . '<breadcrumb url="create.php?parent=' . $parent['record_id'] . '">' . get_html_resource(RES_CREATE_SUBRECORD_ID) . '</breadcrumb>';
}
else
{
    $xml .= '<breadcrumb url="create.php">' . get_html_resource(RES_RECORDS_ID) . '</breadcrumb>';
}

$xml .= '</breadcrumbs>';

// generate tabs

if ($id == 0 && !$parent)
{
    $xml .= '<tabs>'
          . '<tab url="index.php?search=">' . get_html_resource(RES_RECORDS_ID) . '</tab>';

    if (ustrlen($_SESSION[VAR_SEARCH_TEXT]) != 0)
    {
        $xml .= '<tab url="index.php?search=' . urlencode($_SESSION[VAR_SEARCH_TEXT]) . '">'
              . get_html_resource(RES_SEARCH_RESULTS_ID)
              . '</tab>';
    }

    if (get_user_level() != USER_LEVEL_GUEST)
    {
        if (can_record_be_created())
        {
            $xml .= '<tab url="create.php" active="true">' . get_html_resource(RES_CREATE_ID) . '</tab>';
        }
    }
}

// generate general information

$xml .= '<content>'
      . '<form name="' . $form . '" action="create.php' . ($id == 0 ? ($parent ? '?parent=' . $parent['record_id'] : NULL) : '?id=' . $id) . '" upload="' . (ATTACHMENTS_MAXSIZE * 1024) . '">'
      . '<group title="' . get_html_resource(RES_GENERAL_INFO_ID) . '">';

if ($step == 1)
{
    debug_write_log(DEBUG_NOTICE, 'Step #1 (project) is being proceeded.');

    $xml .= '<control name="project" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
          . '<label>' . get_html_resource(RES_PROJECT_ID) . '</label>'
          . '<combobox>';

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
        $xml .= ($parent && $parent['project_id'] == $row['project_id']
                    ? '<listitem value="' . $row['project_id'] . '" selected="true">'
                    : '<listitem value="' . $row['project_id'] . '">')
              . ustr2html($row['project_name'])
              . '</listitem>';
    }

    $xml .= '</combobox>'
          . '</control>';
}
else
{
    $xml .= '<control name="project">'
          . '<label>' . get_html_resource(RES_PROJECT_ID) . '</label>'
          . '<combobox>'
          . '<listitem value="' . $project_id . '">' . ustr2html($project_name) . '</listitem>'
          . '</combobox>'
          . '</control>';

    if ($step == 2)
    {
        debug_write_log(DEBUG_NOTICE, 'Step #2 (template) is being proceeded.');

        $xml .= '<control name="template" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
              . '<label>' . get_html_resource(RES_TEMPLATE_ID) . '</label>'
              . '<combobox>';

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
            $xml .= ($parent && $parent['template_id'] == $row['template_id']
                        ? '<listitem value="' . $row['template_id'] . '" selected="true">'
                        : '<listitem value="' . $row['template_id'] . '">')
                  . ustr2html($row['template_name'])
                  . '</listitem>';
        }

        $xml .= '</combobox>'
              . '</control>';
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, 'Step #3 (final) is being proceeded.');

        $xml .= '<control name="template">'
              . '<label>' . get_html_resource(RES_TEMPLATE_ID) . '</label>'
              . '<combobox>'
              . '<listitem value="' . $template_id . '">' . ustr2html($template_name) . '</listitem>'
              . '</combobox>'
              . '</control>'
              . '<control name="subject" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
              . '<label>' . get_html_resource(RES_SUBJECT_ID) . '</label>'
              . '<editbox maxlen="' . MAX_RECORD_SUBJECT . '">' . ustr2html($subject) . '</editbox>'
              . '</control>';

        if ($responsible == STATE_RESPONSIBLE_ASSIGN)
        {
            debug_write_log(DEBUG_NOTICE, 'Record should be assigned.');

            $rs = dal_query('records/responsibles.sql', $project_id, $state_id, $_SESSION[VAR_USERID]);

            if ($rs->rows != 0)
            {
                $xml .= '<control name="responsible" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
                      . '<label>' . get_html_resource(RES_RESPONSIBLE_ID) . '</label>'
                      . '<combobox>';

                while (($row = $rs->fetch()))
                {
                    $xml .= ($row['account_id'] == $responsible_id
                                ? '<listitem value="' . $row['account_id'] . '" selected="true">'
                                : '<listitem value="' . $row['account_id'] . '">')
                          . ustr2html(sprintf('%s (%s)', $row['fullname'], account_get_username($row['username'])))
                          . '</listitem>';
                }

                $xml .= '</combobox>'
                      . '</control>';
            }
        }

        if ($parent)
        {
            $xml .= '<control name="is_dependency">'
                  . ($is_dependency
                        ? '<checkbox checked="true">'
                        : '<checkbox>')
                  . get_html_resource(RES_DEPENDENCY_ID)
                  . '</checkbox>'
                  . '</control>';
        }
    }
}

$xml .= '</group>';

// go through the list of all fields of initial state

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

            $xml .= ($row['is_required'] && $row['field_type'] != FIELD_TYPE_CHECKBOX
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

                    $flag = TRUE;

                    break;

                case FIELD_TYPE_MULTILINED:

                    $xml .= '<label>' . ustr2html($row['field_name']) . '</label>';

                    $xml .= '<textbox rows="' . HTML_TEXTBOX_MIN_HEIGHT . '" resizeable="true" maxlen="' . MAX_FIELD_MULTILINED . '">'
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

                    $onready = '<scriptonreadyitem>'
                             . '$("#' . $name . '").datepicker($.datepicker.regional["' . $_SESSION[VAR_LOCALE] . '"]);'
                             . '</scriptonreadyitem>';

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

        $xml .= '</group>';
    }

    $permissions = record_get_permissions($template_id, $_SESSION[VAR_USERID], 0);

    if (get_user_level() != USER_LEVEL_GUEST &&
        ($permissions & PERMIT_ATTACH_FILES) &&
        ATTACHMENTS_ENABLED)
    {
        $xml .= '<group title="' . get_html_resource(RES_ATTACH_FILE_ID) . '">'
              . '<control name="attachname">'
              . '<label>' . get_html_resource(RES_ATTACHMENT_NAME_ID) . '</label>'
              . '<editbox maxlen="' . MAX_ATTACHMENT_NAME . '"/>'
              . '</control>'
              . '<control name="attachfile">'
              . '<label>' . get_html_resource(RES_ATTACHMENT_FILE_ID) . '</label>'
              . '<filebox/>'
              . '</control>'
              . '</group>';

        $notes .= '<note>' . ustrprocess(get_html_resource(RES_ALERT_UPLOAD_FORM_SIZE_ID), ATTACHMENTS_MAXSIZE) . '</note>';
    }
}

if ($flag)
{
    $notes .= '<note>' . get_html_resource(RES_LINK_TO_ANOTHER_RECORD_ID) . '</note>';
}

$xml .= '<button default="true">' . get_html_resource(RES_OK_ID) . '</button>';

if ($id != 0)
{
    $xml .= '<button url="view.php?id=' . $id . '">' . get_html_resource(RES_CANCEL_ID) . '</button>';
}
elseif ($parent)
{
    $xml .= '<button url="subrecords.php?id=' . $parent['record_id'] . '">' . get_html_resource(RES_CANCEL_ID) . '</button>';
}

$xml .= $notes
      . '</form>';

// if some error was specified to display, force an alert

switch ($error)
{
    case ERROR_INCOMPLETE_FORM:
        $xml .= '<script>alert("' . get_js_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '");</script>';
        break;
    case ERROR_INVALID_INTEGER_VALUE:
        $xml .= '<script>alert("' . get_js_resource(RES_ALERT_INVALID_INTEGER_VALUE_ID) . '");</script>';
        break;
    case ERROR_INVALID_DATE_VALUE:
        $xml .= '<script>alert("' . get_js_resource(RES_ALERT_INVALID_DATE_VALUE_ID) . '");</script>';
        break;
    case ERROR_INVALID_TIME_VALUE:
        $xml .= '<script>alert("' . get_js_resource(RES_ALERT_INVALID_TIME_VALUE_ID) . '");</script>';
        break;
    case ERROR_INTEGER_VALUE_OUT_OF_RANGE:
    case ERROR_DATE_VALUE_OUT_OF_RANGE:
    case ERROR_TIME_VALUE_OUT_OF_RANGE:
        $xml .= '<script>alert("' . ustrprocess(get_js_resource(RES_ALERT_FIELD_VALUE_OUT_OF_RANGE_ID), $_SESSION['FIELD_NAME'], $_SESSION['MIN_FIELD_INTEGER'], $_SESSION['MAX_FIELD_INTEGER']) . '");</script>';
        unset($_SESSION['FIELD_NAME']);
        unset($_SESSION['MIN_FIELD_INTEGER']);
        unset($_SESSION['MAX_FIELD_INTEGER']);
        break;
    case ERROR_RECORD_NOT_FOUND:
        $xml .= '<script>alert("' . get_js_resource(RES_ALERT_RECORD_NOT_FOUND_ID) . '");</script>';
        break;
    case ERROR_VALUE_FAILS_REGEX_CHECK:
        $xml .= '<script>alert("' . ustrprocess(get_js_resource(RES_ALERT_VALUE_FAILS_REGEX_CHECK_ID), $_SESSION['FIELD_NAME'], $_SESSION['FIELD_VALUE']) . '");</script>';
        unset($_SESSION['FIELD_NAME']);
        unset($_SESSION['FIELD_VALUE']);
        break;
    default: ;  // nop
}

$xml .= '</content>';

if ($id == 0 && !$parent)
{
    $xml .= '</tabs>';
}

if (isset($onready))
{
    $xml .= $onready;
}

echo(xml2html($xml, ustrprocess(get_html_resource(RES_NEW_RECORD_ID), $step, 3)));

?>
