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
require_once('../dbo/accounts.php');
require_once('../dbo/projects.php');
require_once('../dbo/templates.php');
require_once('../dbo/fields.php');
require_once('../dbo/filters.php');
/**#@-*/

init_page(LOAD_INLINE);

// check that requested filter exists

$id     = ustr2int(try_request('id'));
$filter = filter_find($id);

if (!$filter)
{
    debug_write_log(DEBUG_NOTICE, 'Filter cannot be found.');
    header('HTTP/1.1 307 index.php');
    exit;
}

// changed filter has been submitted

if (try_request('submitted') == 'modifyform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $filter_name = ustrcut($_REQUEST['filter_name'], MAX_FILTER_NAME);
    $unclosed    = isset($_REQUEST['unclosed']);
    $postponed   = ustr2int(try_request('postponed', 0));

    if (!in_array($postponed, array(0, FILTER_FLAG_POSTPONED, FILTER_FLAG_ACTIVE)))
    {
        $postponed = 0;
    }

    $error = filter_validate($filter_name);

    if ($error == NO_ERROR)
    {
        $filter_type = $filter['filter_type'];

        if ($filter_type == FILTER_TYPE_ALL_STATES ||
            $filter_type == FILTER_TYPE_SEL_STATES)
        {
            $states = (isset($_REQUEST['states']) ? $_REQUEST['states'] : array());
            $filter_type = (count($states) == 0 ? FILTER_TYPE_ALL_STATES : FILTER_TYPE_SEL_STATES);
        }

        $filter_flags  = ($unclosed ? FILTER_FLAG_UNCLOSED : 0);
        $filter_flags |= ($postponed);

        if (isset($_REQUEST['created_by']) &&
            count($_REQUEST['created_by']) != 0)
        {
            $filter_flags |= FILTER_FLAG_CREATED_BY;
        }

        if (isset($_REQUEST['assigned_to']) &&
            count($_REQUEST['assigned_to']) != 0)
        {
            if (in_array(0, $_REQUEST['assigned_to']))
            {
                $filter_flags |= FILTER_FLAG_UNASSIGNED;

                if (count($_REQUEST['assigned_to']) > 1)
                {
                    $filter_flags |= FILTER_FLAG_ASSIGNED_TO;
                }
            }
            else
            {
                $filter_flags |= FILTER_FLAG_ASSIGNED_TO;
            }
        }

        $error = filter_modify($id,
                               $filter_name,
                               $filter_type,
                               $filter_flags);

        if ($error == NO_ERROR)
        {
            if ($filter_type == FILTER_TYPE_ALL_STATES ||
                $filter_type == FILTER_TYPE_SEL_STATES)
            {
                dal_query('filters/fsdelall.sql', $id, $_SESSION[VAR_USERID]);

                foreach ($states as $item)
                {
                    dal_query('filters/fscreate.sql', $id, $item);
                }
            }

            dal_query('filters/fadelall.sql',  $id, $_SESSION[VAR_USERID]);

            if (($filter_flags & FILTER_FLAG_CREATED_BY) != 0)
            {
                foreach ($_REQUEST['created_by'] as $item)
                {
                    dal_query('filters/facreate.sql', $id, FILTER_FLAG_CREATED_BY, $item);
                }
            }

            if (($filter_flags & FILTER_FLAG_ASSIGNED_TO) != 0)
            {
                foreach ($_REQUEST['assigned_to'] as $item)
                {
                    dal_query('filters/facreate.sql', $id, FILTER_FLAG_ASSIGNED_TO, $item);
                }
            }

            switch ($filter['filter_type'])
            {
                case FILTER_TYPE_ALL_PROJECTS:
                case FILTER_TYPE_ALL_TEMPLATES:

                    $template_id = 0;

                    break;

                case FILTER_TYPE_ALL_STATES:
                case FILTER_TYPE_SEL_STATES:

                    $template = template_find($filter['filter_param']);

                    if (!$template)
                    {
                        debug_write_log(DEBUG_WARNING, 'Template cannot be found.');
                        header('HTTP/1.1 307 view.php?id=' . $id);
                        exit;
                    }

                    $template_id = $template['template_id'];

                    break;

                default:

                    debug_write_log(DEBUG_WARNING, 'Unknown filter type = ' . $filter['filter_type']);
            }

            if ($template_id != 0)
            {
                filter_trans_set($id, $template_id);
                filter_fields_set($id, $template_id);
            }
        }
    }

    switch ($error)
    {
        case NO_ERROR:
            header('HTTP/1.0 200 OK');
            break;

        case ERROR_INCOMPLETE_FORM:
            send_http_error(get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID));
            break;

        case ERROR_ALREADY_EXISTS:
            send_http_error(get_html_resource(RES_ALERT_FILTER_ALREADY_EXISTS_ID));
            break;

        default:
            send_http_error(get_html_resource(RES_ALERT_UNKNOWN_ERROR_ID));
    }

    exit;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $error = NO_ERROR;

    $filter_name = $filter['filter_name'];
    $unclosed    = ($filter['filter_flags'] & FILTER_FLAG_UNCLOSED);
    $postponed   = ($filter['filter_flags'] & (FILTER_FLAG_POSTPONED | FILTER_FLAG_ACTIVE));
}

// local JS functions

$resTitle = get_js_resource(RES_ERROR_ID);
$resOK    = get_js_resource(RES_OK_ID);

$xml = <<<JQUERY
<script>

function modifySuccess ()
{
    closeModal();
    reloadTab();
}

function modifyError (XMLHttpRequest)
{
    jqAlert("{$resTitle}", XMLHttpRequest.responseText, "{$resOK}");
}

</script>
JQUERY;

// generate header

$xml .= '<form name="modifyform" action="modify.php?id=' . $id . '" success="modifySuccess" error="modifyError">'
      . '<group>';

// generate project and template selectors

switch ($filter['filter_type'])
{
    case FILTER_TYPE_ALL_PROJECTS:

        $project_id  = 0;
        $template_id = 0;

        $xml .= '<control name="project">'
              . '<label>' . get_html_resource(RES_PROJECT_ID) . '</label>'
              . '<combobox>'
              . '<listitem value="0">' . get_html_resource(RES_ALL_PROJECTS_ID) . '</listitem>'
              . '</combobox>'
              . '</control>';

        break;

    case FILTER_TYPE_ALL_TEMPLATES:

        $project = project_find($filter['filter_param']);

        if (!$project)
        {
            debug_write_log(DEBUG_WARNING, 'Project cannot be found.');
            header('HTTP/1.1 307 view.php?id=' . $id);
            exit;
        }

        $project_id  = $project['project_id'];
        $template_id = 0;

        $xml .= '<control name="project">'
              . '<label>' . get_html_resource(RES_PROJECT_ID) . '</label>'
              . '<combobox>'
              . '<listitem value="0">' . ustr2html($project['project_name']) . '</listitem>'
              . '</combobox>'
              . '</control>';

        $xml .= '<control name="template">'
              . '<label>' . get_html_resource(RES_TEMPLATE_ID) . '</label>'
              . '<combobox>'
              . '<listitem value="0">' . get_html_resource(RES_ALL_TEMPLATES_ID) . '</listitem>'
              . '</combobox>'
              . '</control>';

        break;

    case FILTER_TYPE_ALL_STATES:
    case FILTER_TYPE_SEL_STATES:

        $template = template_find($filter['filter_param']);

        if (!$template)
        {
            debug_write_log(DEBUG_WARNING, 'Template cannot be found.');
            header('HTTP/1.1 307 view.php?id=' . $id);
            exit;
        }

        $project_id  = $template['project_id'];
        $template_id = $template['template_id'];

        $xml .= '<control name="project">'
              . '<label>' . get_html_resource(RES_PROJECT_ID) . '</label>'
              . '<combobox>'
              . '<listitem value="0">' . ustr2html($template['project_name']) . '</listitem>'
              . '</combobox>'
              . '</control>';

        $xml .= '<control name="template">'
              . '<label>' . get_html_resource(RES_TEMPLATE_ID) . '</label>'
              . '<combobox>'
              . '<listitem value="0">' . ustr2html($template['template_name']) . '</listitem>'
              . '</combobox>'
              . '</control>';

        break;

    default:

        debug_write_log(DEBUG_WARNING, 'Unknown filter type = ' . $filter['filter_type']);
}

// generate filter name and other common options

$xml .= '<control name="filter_name" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
      . '<label>' . get_html_resource(RES_FILTER_NAME_ID) . '</label>'
      . '<editbox maxlen="' . MAX_FILTER_NAME . '">' . ustr2html($filter_name) . '</editbox>'
      . '</control>';

$xml .= '<control name="unclosed">'
      . '<label/>'
      . ($unclosed
            ? '<checkbox checked="true">'
            : '<checkbox>')
      . ustrtolower(get_html_resource(RES_SHOW_UNCLOSED_ONLY_ID))
      . '</checkbox>'
      . '</control>';

$xml .= '<control name="postponed">'
      . '<label>' . get_html_resource(RES_POSTPONE_STATUS_ID) . '</label>'
      . '<radio value="' . 0                     . ($postponed == 0                     ? '" checked="true">' : '">') . get_html_resource(RES_SHOW_ALL_ID)            . '</radio>'
      . '<radio value="' . FILTER_FLAG_ACTIVE    . ($postponed == FILTER_FLAG_ACTIVE    ? '" checked="true">' : '">') . get_html_resource(RES_SHOW_ACTIVE_ONLY_ID)    . '</radio>'
      . '<radio value="' . FILTER_FLAG_POSTPONED . ($postponed == FILTER_FLAG_POSTPONED ? '" checked="true">' : '">') . get_html_resource(RES_SHOW_POSTPONED_ONLY_ID) . '</radio>'
      . '</control>';

$xml .= '</group>';

// generate list of states

if ($template_id != 0)
{
    $states = filter_states_get($id, $template_id);

    $xml .= '<group title="' . get_html_resource(RES_STATES_ID) . '">'
          . '<control name="states[]">'
          . '<listbox size="10">';

    $rs = dal_query('states/list.sql', $template_id, 'state_name');

    while (($row = $rs->fetch()))
    {
        $xml .= (in_array($row['state_id'], $states)
                    ? '<listitem value="' . $row['state_id'] . '" selected="true">'
                    : '<listitem value="' . $row['state_id'] . '">')
              . ustr2html($row['state_name'])
              . '</listitem>';
    }

    $xml .= '</listbox>'
          . '</control>'
          . '</group>';
}

// generate list of submitters

$xml .= '<group title="' . get_html_resource(RES_SHOW_CREATED_BY_ONLY_ID) . '">'
      . '<control name="created_by[]">'
      . '<listbox size="10">';

$rs = ($project_id == 0)
    ? dal_query('filters/membersx2.sql', $_SESSION[VAR_USERID], $id, FILTER_FLAG_CREATED_BY)
    : dal_query('filters/membersx.sql',  $project_id,           $id, FILTER_FLAG_CREATED_BY);

while (($row = $rs->fetch()))
{
    $xml .= ($row['is_selected']
                ? '<listitem value="' . $row['account_id'] . '" selected="true">'
                : '<listitem value="' . $row['account_id'] . '">')
          . ustr2html(sprintf('%s (%s)', $row['fullname'], account_get_username($row['username'])))
          . '</listitem>';
}

$xml .= '</listbox>'
      . '</control>'
      . '</group>';

// generate list of assignees

$xml .= '<group title="' . get_html_resource(RES_SHOW_ASSIGNED_TO_ONLY_ID) . '">'
      . '<control name="assigned_to[]">'
      . '<listbox size="10">';

$xml .= (($filter['filter_flags'] & FILTER_FLAG_UNASSIGNED) == 0
            ? '<listitem value="0">'                 . get_html_resource(RES_NONE_ID) . '</listitem>'
            : '<listitem value="0" selected="true">' . get_html_resource(RES_NONE_ID) . '</listitem>');

$rs = ($project_id == 0)
    ? dal_query('filters/membersx2.sql', $_SESSION[VAR_USERID], $id, FILTER_FLAG_ASSIGNED_TO)
    : dal_query('filters/membersx.sql',  $project_id,           $id, FILTER_FLAG_ASSIGNED_TO);

while (($row = $rs->fetch()))
{
    $xml .= ($row['is_selected']
                ? '<listitem value="' . $row['account_id'] . '" selected="true">'
                : '<listitem value="' . $row['account_id'] . '">')
          . ustr2html(sprintf('%s (%s)', $row['fullname'], account_get_username($row['username'])))
          . '</listitem>';
}

$xml .= '</listbox>'
      . '</control>'
      . '</group>';

// generate template-specific options

if ($template_id != 0)
{
    $rs = dal_query('states/list.sql', $template_id, 'state_type, state_name');

    if ($rs->rows != 0)
    {
        // generate list of states with dates

        $xml .= '<group title="' . get_html_resource(RES_SHOW_MOVED_TO_STATES_ONLY_ID) . '">';

        while (($row = $rs->fetch()))
        {
            $name = 'state' . $row['state_id'];

            $rsd = dal_query('filters/ftfndk.sql', $id, $row['state_id']);

            if ($rsd->rows == 0)
            {
                $used = isset($_REQUEST[$name]);

                $date1 = NULL;
                $date2 = NULL;
            }
            else
            {
                $used = TRUE;
                $temp = $rsd->fetch();

                $date1 = get_date($temp['date1']);
                $date2 = get_date($temp['date2']);
            }

            $xml .= '<control name="' . $name . '">'
                  . ($used ? '<label checkmark="true" checked="true">' . ustr2html($row['state_name']) . '</label>'
                           : '<label checkmark="true">'                . ustr2html($row['state_name']) . '</label>')
                  . '<control name="min_' . $name . '">'
                  . '<editbox small="true" maxlen="' . ustrlen(get_date(SAMPLE_DATE)) . '">' . try_request('min_' . $name, $date1) . '</editbox>'
                  . '</control>'
                  . '<control name="max_' . $name . '">'
                  . '<editbox small="true" maxlen="' . ustrlen(get_date(SAMPLE_DATE)) . '">' . try_request('max_' . $name, $date2) . '</editbox>'
                  . '</control>'
                  . '</control>';
        }

        $xml .= '</group>';

        // generate list of fields with values

        $rs->seek();

        while (($row = $rs->fetch()))
        {
            $rsf = dal_query('filters/flist.sql',
                             $row['state_id'],
                             $_SESSION[VAR_USERID],
                             FIELD_ALLOW_TO_READ);

            if ($rsf->rows != 0)
            {
                $xml .= '<group title="' . ustrprocess(get_html_resource(RES_FIELDS_OF_STATE_X_ID), $row['state_name']) . '">';

                while (($row = $rsf->fetch()))
                {
                    $name = 'field' . $row['field_id'];

                    $rsp = dal_query('filters/fffndk.sql', $id, $row['field_id']);

                    if ($rsp->rows == 0)
                    {
                        $used = isset($_REQUEST[$name]);

                        $param1 = NULL;
                        $param2 = NULL;
                    }
                    else
                    {
                        $used = TRUE;
                        $temp = $rsp->fetch();

                        $param1 = $temp['param1'];
                        $param2 = $temp['param2'];
                    }

                    $xml .= '<control name="' . $name . '">'
                          . ($used ? '<label checkmark="true" checked="true">'
                                   : '<label checkmark="true">')
                          . ustr2html($row['field_name'])
                          . '</label>';

                    switch ($row['field_type'])
                    {
                        case FIELD_TYPE_NUMBER:

                            $xml .= '<control name="min_' . $name . '">'
                                  . '<editbox small="true" maxlen="' . (ustrlen(MAX_FIELD_INTEGER) + 1) . '">' . try_request('min_' . $name, $param1) . '</editbox>'
                                  . '</control>'
                                  . '<control name="max_' . $name . '">'
                                  . '<editbox small="true" maxlen="' . (ustrlen(MAX_FIELD_INTEGER) + 1) . '">' . try_request('max_' . $name, $param2) . '</editbox>'
                                  . '</control>';

                            break;

                        case FIELD_TYPE_FLOAT:

                            $xml .= '<control name="min_' . $name . '">'
                                  . '<editbox small="true" maxlen="' . ustrlen(MIN_FIELD_FLOAT) . '">' . try_request('min_' . $name, value_find(FIELD_TYPE_FLOAT, $param1)) . '</editbox>'
                                  . '</control>'
                                  . '<control name="max_' . $name . '">'
                                  . '<editbox small="true" maxlen="' . ustrlen(MAX_FIELD_FLOAT) . '">' . try_request('max_' . $name, value_find(FIELD_TYPE_FLOAT, $param2)) . '</editbox>'
                                  . '</control>';

                            break;

                        case FIELD_TYPE_STRING:
                        case FIELD_TYPE_MULTILINED:

                            $xml .= '<control name="edit_' . $name . '">'
                                  . '<editbox maxlen="' . MAX_FIELD_STRING . '">' . try_request('edit_' . $name, value_find(FIELD_TYPE_STRING, $param1)) . '</editbox>'
                                  . '</control>';

                            break;

                        case FIELD_TYPE_CHECKBOX:

                            $xml .= '<control name="check_' . $name . '">'
                                  . '<label/>'
                                  . '<radio value="' . 1 . (try_request('check_' . $name, $param1) != 0 ? '" checked="true">' : '">') . get_html_resource(RES_ON_ID)  . '</radio>'
                                  . '<radio value="' . 0 . (try_request('check_' . $name, $param1) == 0 ? '" checked="true">' : '">') . get_html_resource(RES_OFF_ID) . '</radio>'
                                  . '</control>';

                            break;

                        case FIELD_TYPE_LIST:

                            $value = try_request('list_' . $name, $param1);

                            $xml .= '<control name="list_' . $name . '">'
                                  . '<combobox>';

                            $rsv = dal_query('values/lvlist.sql', $row['field_id']);

                            while (($row = $rsv->fetch()))
                            {
                                $xml .= ($value == $row['int_value']
                                            ? '<listitem value="' . $row['int_value'] . '" selected="true">'
                                            : '<listitem value="' . $row['int_value'] . '">')
                                      . ustr2html($row['str_value'])
                                      . '</listitem>';
                            }

                            $xml .= '</combobox>'
                                  . '</control>';

                            break;

                        case FIELD_TYPE_RECORD:

                            $xml .= '<control name="edit_' . $name . '">'
                                  . '<editbox maxlen="' . ustrlen(MAXINT) . '">' . try_request('edit_' . $name, $param1) . '</editbox>'
                                  . '</control>';

                            break;

                        case FIELD_TYPE_DATE:

                            $xml .= '<control name="min_' . $name . '">'
                                  . '<editbox small="true" maxlen="' . ustrlen(get_date(SAMPLE_DATE)) . '">' . try_request('min_' . $name, $param1) . '</editbox>'
                                  . '</control>'
                                  . '<control name="max_' . $name . '">'
                                  . '<editbox small="true" maxlen="' . ustrlen(get_date(SAMPLE_DATE)) . '">' . try_request('max_' . $name, $param2) . '</editbox>'
                                  . '</control>';

                            break;

                        case FIELD_TYPE_DURATION:

                            $xml .= '<control name="min_' . $name . '">'
                                  . '<editbox small="true" maxlen="' . ustrlen(time2ustr(MAX_FIELD_DURATION)) . '">' . try_request('min_' . $name, $param1) . '</editbox>'
                                  . '</control>'
                                  . '<control name="max_' . $name . '">'
                                  . '<editbox small="true" maxlen="' . ustrlen(time2ustr(MAX_FIELD_DURATION)) . '">' . try_request('max_' . $name, $param2) . '</editbox>'
                                  . '</control>';

                            break;

                        default:

                            debug_write_log(DEBUG_WARNING, 'Unknown field type = ' . $row['field_type']);
                    }

                    $xml .= '</control>';
                }

                $xml .= '</group>';
            }
        }
    }
}

// generate footer

$xml .= '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>'
      . '</form>';

echo(xml2html($xml));

?>
