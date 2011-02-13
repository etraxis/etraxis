<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2010  Artem Rodygin
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
require_once('../dbo/templates.php');
require_once('../dbo/filters.php');
require_once('../dbo/values.php');
/**#@-*/

init_page(LOAD_TAB);

// check that requested filter exists

$id     = ustr2int(try_request('id'));
$filter = filter_find($id);

if (!$filter)
{
    debug_write_log(DEBUG_NOTICE, 'Filter cannot be found.');
    exit;
}

// local JS functions

$resTitle  = ustrprocess(get_js_resource(RES_FILTER_X_ID), ustr2js($filter['filter_name']));
$resOK     = get_js_resource(RES_OK_ID);
$resCancel = get_js_resource(RES_CANCEL_ID);

$xml = <<<JQUERY
<script>

function filterModify ()
{
    jqModal("{$resTitle}", "modify.php?id={$id}", "{$resOK}", "{$resCancel}", "$('#modifyform').submit()");
}

function filterToggle ()
{
    $.post("disable.php?id={$id}", function () {
        reloadTab();
    });
}

</script>
JQUERY;

// generate buttons

$xml .= '<button url="index.php">' . get_html_resource(RES_BACK_ID) . '</button>'
      . '<buttonset>'
      . '<button action="filterModify()">' . get_html_resource(RES_MODIFY_ID) . '</button>';

$xml .= '<button url="delete.php?id=' . $id . '" prompt="' . get_html_resource(RES_CONFIRM_DELETE_FILTERS_ID) . '">'
      . get_html_resource(RES_DELETE_ID)
      . '</button>';

$xml .= '<button action="filterToggle()">'
      . get_html_resource(is_filter_activated($id) ? RES_DISABLE_ID : RES_ENABLE_ID)
      . '</button>'
      . '</buttonset>';

// generate general information

$xml .= '<group title="' . get_html_resource(RES_GENERAL_INFO_ID) . '">';

switch ($filter['filter_type'])
{
    case FILTER_TYPE_ALL_PROJECTS:

        $project_id  = 0;
        $template_id = 0;

        $xml .= '<text label="' . get_html_resource(RES_PROJECT_ID) . '">' . get_html_resource(RES_ALL_PROJECTS_ID) . '</text>';

        break;

    case FILTER_TYPE_ALL_TEMPLATES:

        $project = project_find($filter['filter_param']);

        if ($project)
        {
            $project_id  = $project['project_id'];
            $template_id = 0;

            $xml .= '<text label="' . get_html_resource(RES_PROJECT_ID) . '">' . ustr2html($project['project_name']) . '</text>';
        }
        else
        {
            $project_id  = 0;
            $template_id = 0;

            $xml .= '<text label="' . get_html_resource(RES_PROJECT_ID) . '"><i>' . get_html_resource(RES_ALERT_UNKNOWN_ERROR_ID) . '</i></text>';
        }

        $xml .= '<text label="' . get_html_resource(RES_TEMPLATE_ID) . '">' . get_html_resource(RES_ALL_TEMPLATES_ID) . '</text>';

        break;

    case FILTER_TYPE_ALL_STATES:

        $template = template_find($filter['filter_param']);

        if ($template)
        {
            $project_id  = $template['project_id'];
            $template_id = $template['template_id'];

            $xml .= '<text label="' . get_html_resource(RES_PROJECT_ID)  . '">' . ustr2html($template['project_name'])  . '</text>'
                  . '<text label="' . get_html_resource(RES_TEMPLATE_ID) . '">' . ustr2html($template['template_name']) . '</text>';
        }
        else
        {
            $project_id  = 0;
            $template_id = 0;

            $xml .= '<text label="' . get_html_resource(RES_PROJECT_ID)  . '"><i>' . get_html_resource(RES_ALERT_UNKNOWN_ERROR_ID) . '</i></text>'
                  . '<text label="' . get_html_resource(RES_TEMPLATE_ID) . '"><i>' . get_html_resource(RES_ALERT_UNKNOWN_ERROR_ID) . '</i></text>';
        }

        $xml .= '<text label="' . get_html_resource(RES_STATES_ID) . '">' . get_html_resource(RES_ALL_STATES_ID) . '</text>';

        break;

    case FILTER_TYPE_SEL_STATES:

        $template = template_find($filter['filter_param']);

        if ($template)
        {
            $project_id  = $template['project_id'];
            $template_id = $template['template_id'];

            $xml .= '<text label="' . get_html_resource(RES_PROJECT_ID)  . '">' . ustr2html($template['project_name'])  . '</text>'
                  . '<text label="' . get_html_resource(RES_TEMPLATE_ID) . '">' . ustr2html($template['template_name']) . '</text>';
        }
        else
        {
            $project_id  = 0;
            $template_id = 0;

            $xml .= '<text label="' . get_html_resource(RES_PROJECT_ID)  . '"><i>' . get_html_resource(RES_ALERT_UNKNOWN_ERROR_ID) . '</i></text>'
                  . '<text label="' . get_html_resource(RES_TEMPLATE_ID) . '"><i>' . get_html_resource(RES_ALERT_UNKNOWN_ERROR_ID) . '</i></text>';
        }

        $xml .= '<text label="' . get_html_resource(RES_STATES_ID) . '">';

        $states = filter_states_get($id, $template['template_id']);

        $rs = dal_query('states/list.sql', $template['template_id'], 'state_name');

        while (($row = $rs->fetch()))
        {
            if (in_array($row['state_id'], $states))
            {
                $xml .= ustr2html($row['state_name']) . '<br/>';
            }
        }

        $xml .= '</text>';

        break;

    default:

        $project_id  = 0;
        $template_id = 0;

        debug_write_log(DEBUG_WARNING, 'Unknown filter type = ' . $filter['filter_type']);
}

$unclosed = ($filter['filter_flags'] & FILTER_FLAG_UNCLOSED) == 0
          ? RES_SHOW_ALL_ID
          : RES_SHOW_UNCLOSED_ONLY_ID;

if (($filter['filter_flags'] & FILTER_FLAG_POSTPONED) != 0)
{
    $postpone = RES_SHOW_POSTPONED_ONLY_ID;
}
elseif (($filter['filter_flags'] & FILTER_FLAG_ACTIVE) != 0)
{
    $postpone = RES_SHOW_ACTIVE_ONLY_ID;
}
else
{
    $postpone = RES_SHOW_ALL_ID;
}

$xml .= '<text label="' . get_html_resource(RES_FILTER_NAME_ID)     . '">' . ustr2html($filter['filter_name']) . '</text>'
      . '<text label="' . get_html_resource(RES_STATE_TYPE_ID)      . '">' . get_html_resource($unclosed)      . '</text>'
      . '<text label="' . get_html_resource(RES_POSTPONE_STATUS_ID) . '">' . get_html_resource($postpone)      . '</text>'
      . '</group>';

// generate "show only created by" list

$xml .= '<group title="' . get_html_resource(RES_SHOW_CREATED_BY_ONLY_ID) . '">';

$flag = false;

$rs = ($project_id == 0)
    ? dal_query('filters/membersx2.sql', $_SESSION[VAR_USERID], $id, FILTER_FLAG_CREATED_BY)
    : dal_query('filters/membersx.sql',  $project_id,           $id, FILTER_FLAG_CREATED_BY);

while (($row = $rs->fetch()))
{
    if ($row['is_selected'])
    {
        $flag = true;
        $xml .= '<text>' . ustr2html(sprintf('%s (%s)', $row['fullname'], account_get_username($row['username']))) . '</text>';
    }
}

if (!$flag)
{
    $xml .= '<text>' . get_html_resource(RES_ANYONE_ID) . '</text>';
}

$xml .= '</group>';

// generate "show only assigned to" list

$xml .= '<group title="' . get_html_resource(RES_SHOW_ASSIGNED_TO_ONLY_ID) . '">';

if (($filter['filter_flags'] & FILTER_FLAG_UNASSIGNED) != 0)
{
    $flag = true;
    $xml .= '<text>' . get_html_resource(RES_NONE2_ID) . '</text>';
}
else
{
    $flag = false;
}

$rs = ($project_id == 0)
    ? dal_query('filters/membersx2.sql', $_SESSION[VAR_USERID], $id, FILTER_FLAG_ASSIGNED_TO)
    : dal_query('filters/membersx.sql',  $project_id,           $id, FILTER_FLAG_ASSIGNED_TO);

while (($row = $rs->fetch()))
{
    if ($row['is_selected'])
    {
        $flag = true;
        $xml .= '<text>' . ustr2html(sprintf('%s (%s)', $row['fullname'], account_get_username($row['username']))) . '</text>';
    }
}

if (!$flag)
{
    $xml .= '<text>' . get_html_resource(RES_ANYONE_ID) . '</text>';
}

$xml .= '</group>';

// generate "show only moved to states" list

if ($template_id != 0)
{
    $xml .= '<group title="' . get_html_resource(RES_SHOW_MOVED_TO_STATES_ONLY_ID) . '">';

    $rs = dal_query('states/list.sql', $template_id, 'state_type, state_name');

    while (($row = $rs->fetch()))
    {
        $rsd = dal_query('filters/ftfndk.sql', $id, $row['state_id']);

        if ($rsd->rows != 0)
        {
            $date = $rsd->fetch();

            $xml .= '<text label="' . ustr2html($row['state_name']) . '">'
                  . sprintf('%s - %s', get_date($date['date1']), get_date($date['date2']))
                  . '</text>';
        }
    }

    if (!isset($date))
    {
        $xml .= '<text>' . get_html_resource(RES_NONE2_ID) . '</text>';
    }

    $xml .= '</group>';
}

// generate fields list

if ($template_id != 0)
{
    $rs = dal_query('states/list.sql', $template_id, 'state_type, state_name');

    while (($row = $rs->fetch()))
    {
        $rsf = dal_query('filters/flist.sql',
                         $row['state_id'],
                         $_SESSION[VAR_USERID],
                         FIELD_ALLOW_TO_READ);

        $state_name = $row['state_name'];

        $text = NULL;

        while (($row = $rsf->fetch()))
        {
            $rsp = dal_query('filters/fffndk.sql', $id, $row['field_id']);

            if ($rsp->rows != 0)
            {
                $field  = $rsp->fetch();
                $param1 = $field['param1'];
                $param2 = $field['param2'];

                $text .= '<text label="' . ustr2html($row['field_name']) . '">';

                switch ($row['field_type'])
                {
                    case FIELD_TYPE_NUMBER:
                    case FIELD_TYPE_DATE:
                        $text .= sprintf('%s - %s', $param1, $param2);
                        break;

                    case FIELD_TYPE_STRING:
                    case FIELD_TYPE_MULTILINED:
                        $text .= ustr2html(value_find(FIELD_TYPE_STRING, $param1));
                        break;

                    case FIELD_TYPE_CHECKBOX:
                        $text .= get_html_resource($param1 ? RES_ON_ID : RES_OFF_ID);
                        break;

                    case FIELD_TYPE_LIST:
                        $text .= ustr2html(value_find_listvalue($row['field_id'], $param1));
                        break;

                    case FIELD_TYPE_RECORD:
                        $text .= sprintf('%s', $param1);
                        break;

                    case FIELD_TYPE_DURATION:
                        $text .= sprintf('%s - %s', time2ustr($param1), time2ustr($param2));
                        break;

                    default:
                        debug_write_log(DEBUG_WARNING, 'Unknown field type = ' . $row['field_type']);
                }

                $text .= '</text>';
            }
        }

        if (!is_null($text))
        {
            $xml .= '<group title="' . ustrprocess(get_html_resource(RES_FIELDS_OF_STATE_X_ID), $state_name) . '">' . $text . '</group>';
        }
    }
}

echo(xml2html($xml));

?>
