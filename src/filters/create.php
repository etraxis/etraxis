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
require_once('../dbo/fields.php');
require_once('../dbo/filters.php');
/**#@-*/

init_page();

$error       = NO_ERROR;
$filter_name = NULL;
$unclosed    = FALSE;
$postponed   = 0;

// project has been selected

if (try_request('submitted') == 'projectform')
{
    debug_write_log(DEBUG_NOTICE, 'Project is selected.');

    $project_id  = ustr2int(try_request('project'));
    $template_id = 0;

    if ($project_id == 0)
    {
        $project_name = get_html_resource(RES_ALL_PROJECTS_ID);
        $form = 'createform';
    }
    else
    {
        $rs = dal_query('records/pfndid2.sql', $_SESSION[VAR_USERID], $project_id);

        if ($rs->rows == 0)
        {
            debug_write_log(DEBUG_NOTICE, 'Project cannot be found.');
            exit;
        }

        $project_name = $rs->fetch('project_name');
        $form = 'templateform';
    }
}

// template has been selected

elseif (try_request('submitted') == 'templateform')
{
    debug_write_log(DEBUG_NOTICE, 'Template is selected.');

    $project_id  = ustr2int(try_request('project'));
    $template_id = ustr2int(try_request('template'));

    if ($template_id == 0)
    {
        $rs = dal_query('records/pfndid2.sql', $_SESSION[VAR_USERID], $project_id);

        if ($rs->rows == 0)
        {
            debug_write_log(DEBUG_NOTICE, 'Project cannot be found.');
            exit;
        }

        $project_name  = $rs->fetch('project_name');
        $template_name = get_html_resource(RES_ALL_TEMPLATES_ID);
        $form = 'createform';
    }
    else
    {
        $rs = dal_query('records/tfndid2.sql', $_SESSION[VAR_USERID], $project_id, $template_id);

        if ($rs->rows == 0)
        {
            debug_write_log(DEBUG_NOTICE, 'Template cannot be found.');
            exit;
        }

        $row = $rs->fetch();

        $project_name  = $row['project_name'];
        $template_name = $row['template_name'];
        $form = 'createform';
    }
}

// new filter has been submitted

elseif (try_request('submitted') == 'createform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $project_id  = ustr2int(try_request('project'));
    $template_id = ustr2int(try_request('template'));
    $states      = (isset($_REQUEST['states']) ? $_REQUEST['states'] : array());

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
        if ($project_id == 0)
        {
            $filter_type  = FILTER_TYPE_ALL_PROJECTS;
            $filter_param = NULL;
        }
        elseif ($template_id == 0)
        {
            $filter_type  = FILTER_TYPE_ALL_TEMPLATES;
            $filter_param = $project_id;
        }
        elseif (count($states) == 0)
        {
            $filter_type  = FILTER_TYPE_ALL_STATES;
            $filter_param = $template_id;
        }
        else
        {
            $filter_type  = FILTER_TYPE_SEL_STATES;
            $filter_param = $template_id;
        }

        $filter_flags  = ($unclosed ? FILTER_FLAG_UNCLOSED : 0) | ($postponed);
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

        $error = filter_create($filter_name,
                               $filter_type,
                               $filter_flags,
                               $filter_param);

        if ($error == NO_ERROR)
        {
            $rs = dal_query('filters/fndk.sql', $_SESSION[VAR_USERID], ustrtolower($filter_name));

            if ($rs->rows == 0)
            {
                debug_write_log(DEBUG_WARNING, 'Created filter cannot be found.');
            }
            else
            {
                $id = $rs->fetch('filter_id');

                if ($filter_type == FILTER_TYPE_SEL_STATES)
                {
                    foreach ($states as $item)
                    {
                        dal_query('filters/fscreate.sql', $id, $item);
                    }
                }

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
                        if ($item != 0)
                        {
                            dal_query('filters/facreate.sql', $id, FILTER_FLAG_ASSIGNED_TO, $item);
                        }
                    }
                }

                if ($template_id != 0)
                {
                    filter_trans_set($id, $template_id);
                    filter_fields_set($id, $template_id);
                }
            }

            exit;
        }
    }

    switch ($error)
    {
        case NO_ERROR:
            header('HTTP/1.0 200 OK');
            break;

        case ERROR_INCOMPLETE_FORM:
            header('HTTP/1.0 500 ' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID));
            break;

        case ERROR_ALREADY_EXISTS:
            header('HTTP/1.0 500 ' . get_html_resource(RES_ALERT_FILTER_ALREADY_EXISTS_ID));
            break;

        default:
            header('HTTP/1.0 500 ' . get_html_resource(RES_ALERT_UNKNOWN_ERROR_ID));
    }

    exit;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $form = 'projectform';
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

$xml .= '<form name="' . $form . '" action="create.php" success="createSuccess" error="createError">'
      . '<group>';

// generate project selector

if ($form == 'projectform')
{
    $xml .= '<control name="project" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
          . '<label>' . get_html_resource(RES_PROJECT_ID) . '</label>'
          . '<combobox>'
          . '<listitem value="0">' . get_html_resource(RES_ALL_PROJECTS_ID) . '</listitem>';

    $rs = dal_query('records/plist2.sql', $_SESSION[VAR_USERID]);

    while (($row = $rs->fetch()))
    {
        $xml .= '<listitem value="' . $row['project_id'] . '">'
              . ustr2html($row['project_name'])
              . '</listitem>';
    }

    $xml .= '</combobox>'
          . '</control>';
}
elseif (isset($project_id))
{
    $xml .= '<control name="project" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
          . '<label>' . get_html_resource(RES_PROJECT_ID) . '</label>'
          . '<combobox>'
          . '<listitem value="' . $project_id . '">' . ustr2html($project_name) . '</listitem>'
          . '</combobox>'
          . '</control>';
}

// generate template selector

if ($form == 'templateform')
{
    $xml .= '<control name="template" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
          . '<label>' . get_html_resource(RES_TEMPLATE_ID) . '</label>'
          . '<combobox>'
          . '<listitem value="0">' . get_html_resource(RES_ALL_TEMPLATES_ID) . '</listitem>';

    $rs = dal_query('records/tlist2.sql', $_SESSION[VAR_USERID], $project_id);

    while (($row = $rs->fetch()))
    {
        $xml .= '<listitem value="' . $row['template_id'] . '">'
              . ustr2html($row['template_name'])
              . '</listitem>';
    }

    $xml .= '</combobox>'
          . '</control>';
}
elseif (isset($template_name))
{
    $xml .= '<control name="template" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
          . '<label>' . get_html_resource(RES_TEMPLATE_ID) . '</label>'
          . '<combobox>'
          . '<listitem value="' . $template_id . '">' . ustr2html($template_name) . '</listitem>'
          . '</combobox>'
          . '</control>';
}

// generate filter name and other common options

if ($form == 'createform')
{
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
}

$xml .= '</group>';

if ($form == 'createform')
{
    // generate list of states

    if ($template_id != 0)
    {
        $xml .= '<group title="' . get_html_resource(RES_STATES_ID) . '">'
              . '<control name="states[]">'
              . '<listbox size="10">';

        $rs = dal_query('states/list.sql', $template_id, 'state_name');

        while (($row = $rs->fetch()))
        {
            $xml .= (isset($states) && in_array($row['state_id'], $states)
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
        ? dal_query('filters/members2.sql', $_SESSION[VAR_USERID])
        : dal_query('filters/members.sql',  $project_id);

    while (($row = $rs->fetch()))
    {
        $xml .= '<listitem value="' . $row['account_id'] . '">'
              . ustr2html(sprintf('%s (%s)', $row['fullname'], account_get_username($row['username'])))
              . '</listitem>';
    }

    $xml .= '</listbox>'
          . '</control>'
          . '</group>';

    // generate list of assignees

    $xml .= '<group title="' . get_html_resource(RES_SHOW_ASSIGNED_TO_ONLY_ID) . '">'
          . '<control name="assigned_to[]">'
          . '<listbox size="10">'
          . '<listitem value="0">' . get_html_resource(RES_NONE_ID) . '</listitem>';

    $rs->seek();

    while (($row = $rs->fetch()))
    {
        $xml .= '<listitem value="' . $row['account_id'] . '">'
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

                $xml .= '<control name="' . $name . '">'
                      . (isset($_REQUEST[$name])
                            ? '<label checkmark="true" checked="true">' . ustr2html($row['state_name']) . '</label>'
                            : '<label checkmark="true">'                . ustr2html($row['state_name']) . '</label>')
                      . '<control name="min_' . $name . '">'
                      . '<editbox small="true" maxlen="' . ustrlen(get_date(SAMPLE_DATE)) . '">' . try_request('min_' . $name) . '</editbox>'
                      . '</control>'
                      . '<control name="max_' . $name . '">'
                      . '<editbox small="true" maxlen="' . ustrlen(get_date(SAMPLE_DATE)) . '">' . try_request('max_' . $name) . '</editbox>'
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

                        $xml .= '<control name="' . $name . '">'
                              . (isset($_REQUEST[$name])
                                    ? '<label checkmark="true" checked="true">'
                                    : '<label checkmark="true">')
                              . ustr2html($row['field_name'])
                              . '</label>';

                        switch ($row['field_type'])
                        {
                            case FIELD_TYPE_NUMBER:

                                $xml .= '<control name="min_' . $name . '">'
                                      . '<editbox small="true" maxlen="' . (ustrlen(MAX_FIELD_INTEGER) + 1) . '">' . try_request('min_' . $name) . '</editbox>'
                                      . '</control>'
                                      . '<control name="max_' . $name . '">'
                                      . '<editbox small="true" maxlen="' . (ustrlen(MAX_FIELD_INTEGER) + 1) . '">' . try_request('max_' . $name) . '</editbox>'
                                      . '</control>';

                                break;

                            case FIELD_TYPE_STRING:
                            case FIELD_TYPE_MULTILINED:

                                $xml .= '<control name="edit_' . $name . '">'
                                      . '<editbox maxlen="' . MAX_FIELD_STRING . '">' . try_request('edit_' . $name) . '</editbox>'
                                      . '</control>';

                                break;

                            case FIELD_TYPE_CHECKBOX:

                                $xml .= '<control name="check_' . $name . '">'
                                      . '<label/>'
                                      . '<radio value="' . 1 . (try_request('check_' . $name, 0) == 1 ? '" checked="true">' : '">') . get_html_resource(RES_ON_ID)  . '</radio>'
                                      . '<radio value="' . 0 . (try_request('check_' . $name, 0) == 0 ? '" checked="true">' : '">') . get_html_resource(RES_OFF_ID) . '</radio>'
                                      . '</control>';

                                break;

                            case FIELD_TYPE_LIST:

                                $value = try_request('list_' . $name);

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
                                      . '<editbox maxlen="' . ustrlen(MAXINT) . '">' . try_request('edit_' . $name) . '</editbox>'
                                      . '</control>';

                                break;

                            case FIELD_TYPE_DATE:

                                $xml .= '<control name="min_' . $name . '">'
                                      . '<editbox small="true" maxlen="' . ustrlen(get_date(SAMPLE_DATE)) . '">' . try_request('min_' . $name) . '</editbox>'
                                      . '</control>'
                                      . '<control name="max_' . $name . '">'
                                      . '<editbox small="true" maxlen="' . ustrlen(get_date(SAMPLE_DATE)) . '">' . try_request('max_' . $name) . '</editbox>'
                                      . '</control>';

                                break;

                            case FIELD_TYPE_DURATION:

                                $xml .= '<control name="min_' . $name . '">'
                                      . '<editbox small="true" maxlen="' . ustrlen(time2ustr(MAX_FIELD_DURATION)) . '">' . try_request('min_' . $name) . '</editbox>'
                                      . '</control>'
                                      . '<control name="max_' . $name . '">'
                                      . '<editbox small="true" maxlen="' . ustrlen(time2ustr(MAX_FIELD_DURATION)) . '">' . try_request('max_' . $name) . '</editbox>'
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
}

// generate footer

$xml .= '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>'
      . '</form>';

echo(xml2html($xml));

?>
