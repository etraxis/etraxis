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
//  Artem Rodygin           2005-10-22      new-149: User should have ability to modify his filters.
//  Artem Rodygin           2005-11-17      new-176: Change eTraxis design.
//  Artem Rodygin           2005-11-26      new-181: 'All fields marked with * should be filled in.' note is absent.
//  Artem Rodygin           2006-06-28      new-274: "Crumbs" for creation and modification of filters or subscriptions are not clear.
//  Artem Rodygin           2006-07-12      bug-292: Sablotron fails if page contains '&' character.
//  Artem Rodygin           2006-07-27      new-261: UI design should be adopted to slow connection.
//  Artem Rodygin           2006-10-15      new-137: Custom queries.
//  Artem Rodygin           2006-10-17      new-361: Extended custom queries.
//  Artem Rodygin           2006-11-05      new-365: Filters sharing.
//  Artem Rodygin           2006-11-20      new-392: Local users should not be extended with '@eTraxis' when LDAP is disabled.
//  Artem Rodygin           2006-11-24      new-377: Custom views.
//  Artem Rodygin           2007-09-12      new-574: Filter should allow to specify several states.
//  Artem Rodygin           2007-10-01      bug-588: Modification of filter removes all specified field values.
//  Yury Udovichenko        2007-11-20      new-536: Ability to hide postpone records from the list.
//  Artem Rodygin           2007-11-27      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2008-03-15      new-683: Filters should be sharable with groups, not with accounts.
//  Artem Rodygin           2008-03-15      new-501: Filter should allow to specify 'none' value of 'list' fields.
//  Artem Rodygin           2008-04-03      new-694: Filter for unassigned records.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//  Artem Rodygin           2010-01-26      bug-892: English grammar correction
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/accounts.php');
require_once('../dbo/projects.php');
require_once('../dbo/templates.php');
require_once('../dbo/states.php');
require_once('../dbo/fields.php');
require_once('../dbo/filters.php');
/**#@-*/

init_page();

$id     = ustr2int(try_request('id'));
$filter = filter_find($id);

if (!$filter)
{
    debug_write_log(DEBUG_NOTICE, 'Filter cannot be found.');
    header('Location: filter.php');
    exit;
}

if (try_request('submitted') == 'mainform')
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
            $filter_flags |= (in_array(0, $_REQUEST['assigned_to']) ? FILTER_FLAG_UNASSIGNED : FILTER_FLAG_ASSIGNED_TO);
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

            dal_query('filters/fshdelall.sql', $id, $_SESSION[VAR_USERID]);
            dal_query('filters/fadelall.sql',  $id, $_SESSION[VAR_USERID]);

            if (isset($_REQUEST['sharing']))
            {
                foreach ($_REQUEST['sharing'] as $item)
                {
                    dal_query('filters/fshcreate.sql', $id, $item);
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
                        header('Location: filter.php');
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

            header('Location: filter.php');
            exit;
        }
    }

    switch ($error)
    {
        case ERROR_INCOMPLETE_FORM:
            $alert = get_js_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID);
            break;
        case ERROR_ALREADY_EXISTS:
            $alert = get_js_resource(RES_ALERT_FILTER_ALREADY_EXISTS_ID);
            break;
        default:
            $alert = NULL;
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $filter_name = $filter['filter_name'];
    $unclosed    = ($filter['filter_flags'] & FILTER_FLAG_UNCLOSED);
    $postponed   = ($filter['filter_flags'] & (FILTER_FLAG_POSTPONED | FILTER_FLAG_ACTIVE));
}

$xml = '<page' . gen_xml_page_header(ustrprocess(get_html_resource(RES_FILTER_X_ID), ustr2html($filter['filter_name'])), isset($alert) ? $alert : NULL, 'mainform.filter_name') . '>'
     . gen_xml_menu()
     . '<path>'
     . gen_xml_rec_root()
     . '<pathitem url="filter.php">' . get_html_resource(RES_FILTERS_ID) . '</pathitem>'
     . '<pathitem url="fmodify.php?id=' . $id . '">' . ustrprocess(get_html_resource(RES_FILTER_X_ID), ustr2html($filter['filter_name'])) . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<form name="mainform" action="fmodify.php?id=' . $id . '">'
     . '<group title="' . get_html_resource(RES_GENERAL_INFO_ID) . '">';

switch ($filter['filter_type'])
{
    case FILTER_TYPE_ALL_PROJECTS:

        $project_id  = 0;
        $template_id = 0;

        $xml .= '<combobox label="' . get_html_resource(RES_PROJECT_ID) . '" name="project">'
              . '<listitem value="0">' . get_html_resource(RES_ALL_PROJECTS_ID) . '</listitem>'
              . '</combobox>';

        break;

    case FILTER_TYPE_ALL_TEMPLATES:

        $project = project_find($filter['filter_param']);

        if (!$project)
        {
            debug_write_log(DEBUG_WARNING, 'Project cannot be found.');
            header('Location: filter.php');
            exit;
        }

        $project_id  = $project['project_id'];
        $template_id = 0;

        $xml .= '<combobox label="' . get_html_resource(RES_PROJECT_ID) . '" name="project">'
              . '<listitem value="0">' . ustr2html($project['project_name']) . '</listitem>'
              . '</combobox>'
              . '<combobox label="' . get_html_resource(RES_TEMPLATE_ID) . '" name="template">'
              . '<listitem value="0">' . get_html_resource(RES_ALL_TEMPLATES_ID) . '</listitem>'
              . '</combobox>';

        break;

    case FILTER_TYPE_ALL_STATES:
    case FILTER_TYPE_SEL_STATES:

        $template = template_find($filter['filter_param']);

        if (!$template)
        {
            debug_write_log(DEBUG_WARNING, 'Template cannot be found.');
            header('Location: filter.php');
            exit;
        }

        $project_id  = $template['project_id'];
        $template_id = $template['template_id'];

        $states = filter_states_get($id, $template_id);

        $xml .= '<combobox label="' . get_html_resource(RES_PROJECT_ID) . '" name="project">'
              . '<listitem value="0">' . ustr2html($template['project_name']) . '</listitem>'
              . '</combobox>'
              . '<combobox label="' . get_html_resource(RES_TEMPLATE_ID) . '" name="template">'
              . '<listitem value="0">' . ustr2html($template['template_name']) . '</listitem>'
              . '</combobox>'
              . '<listbox label="' . get_html_resource(RES_STATES_ID) . '" name="states[]" multiple="true" size="' . HTML_LISTBOX_SIZE . '">';

        $rs = dal_query('states/list.sql', $template_id, 'state_name');

        while (($row = $rs->fetch()))
        {
            $xml .= '<listitem value="' . $row['state_id'] . (in_array($row['state_id'], $states) ? '" selected="true">' : '">')
                  . ustr2html($row['state_name'])
                  . '</listitem>';
        }

        $xml .= '</listbox>';

        break;

    default:
        debug_write_log(DEBUG_WARNING, 'Unknown filter type = ' . $filter['filter_type']);
}

$xml .= '<editbox label="' . get_html_resource(RES_FILTER_NAME_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="filter_name" size="' . HTML_EDITBOX_SIZE_MEDIUM . '" maxlen="' . MAX_FILTER_NAME . '">' . ustr2html($filter_name) . '</editbox>'
      . '<checkbox name="unclosed"'  . ($unclosed  ? ' checked="true">' : '>') . get_html_resource(RES_SHOW_UNCLOSED_ONLY_ID) . '</checkbox>'
      . '<hr/>'
      . '<radios name="postponed" label="' . get_html_resource(RES_POSTPONE_STATUS_ID) . '">'
      . '<radio name="postponed" value="0"' . ($postponed == 0 ? ' checked="true">' : '>') . get_html_resource(RES_SHOW_ALL_ID) . '</radio>'
      . '<radio name="postponed" value="' . FILTER_FLAG_ACTIVE    . ($postponed == FILTER_FLAG_ACTIVE    ? '" checked="true">' : '">') . get_html_resource(RES_SHOW_ACTIVE_ONLY_ID)    . '</radio>'
      . '<radio name="postponed" value="' . FILTER_FLAG_POSTPONED . ($postponed == FILTER_FLAG_POSTPONED ? '" checked="true">' : '">') . get_html_resource(RES_SHOW_POSTPONED_ONLY_ID) . '</radio>'
      . '</radios>'
      . '</group>'
      . '<group title="' . get_html_resource(RES_SHARE_WITH_ID) . '">'
      . '<listbox name="sharing[]" multiple="true" size="' . HTML_LISTBOX_SIZE . '">';

$rs = dal_query('filters/sharing.sql', $id, $project_id);

while (($row = $rs->fetch()))
{
    $xml .= '<listitem value="' . $row['group_id'] . ($row['is_selected'] ? '" selected="true">' : '">') . ustr2html($row['group_name']) . ' (' . get_html_resource($row['is_global'] ? RES_GLOBAL_ID : RES_LOCAL_ID) . ')</listitem>';
}

$xml .= '</listbox>'
      . '</group>'
      . '<group title="' . get_html_resource(RES_SHOW_CREATED_BY_ONLY_ID) . '">'
      . '<listbox name="created_by[]" multiple="true" size="' . HTML_LISTBOX_SIZE . '">';

if ($project_id == 0)
{
    $rs = dal_query('filters/membersx2.sql', $_SESSION[VAR_USERID], $id, FILTER_FLAG_CREATED_BY);
}
else
{
    $rs = dal_query('filters/membersx.sql', $project_id, $id, FILTER_FLAG_CREATED_BY);
}

while (($row = $rs->fetch()))
{
    $xml .= '<listitem value="' . $row['account_id'] . ($row['is_selected'] ? '" selected="true">' : '">') . ustr2html($row['fullname']) . ' (' . ustr2html(account_get_username($row['username'])) . ')</listitem>';
}

$xml .= '</listbox>'
      . '</group>'
      . '<group title="' . get_html_resource(RES_SHOW_ASSIGNED_TO_ONLY_ID) . '">'
      . '<listbox name="assigned_to[]" multiple="true" size="' . HTML_LISTBOX_SIZE . '">'
      . '<listitem value="0"' . (($filter['filter_flags'] & FILTER_FLAG_UNASSIGNED) == 0 ? '>' : ' selected="true">') . get_html_resource(RES_NONE_ID) . '</listitem>';

if ($project_id == 0)
{
    $rs = dal_query('filters/membersx2.sql', $_SESSION[VAR_USERID], $id, FILTER_FLAG_ASSIGNED_TO);
}
else
{
    $rs = dal_query('filters/membersx.sql', $project_id, $id, FILTER_FLAG_ASSIGNED_TO);
}

while (($row = $rs->fetch()))
{
    $xml .= '<listitem value="' . $row['account_id'] . ($row['is_selected'] ? '" selected="true">' : '">') . ustr2html($row['fullname']) . ' (' . ustr2html(account_get_username($row['username'])) . ')</listitem>';
}

$xml .= '</listbox>'
      . '</group>';

if ($template_id != 0)
{
    $rs = dal_query('states/list.sql', $template_id, 'state_type, state_name');

    if ($rs->rows != 0)
    {
        $xml .= '<group title="' . get_html_resource(RES_SHOW_MOVED_TO_STATES_ONLY_ID) . '">';

        while (($row = $rs->fetch()))
        {
            $name = 'state' . $row['state_id'];

            $rsd = dal_query('filters/ftfndk.sql', $id, $row['state_id']);

            if ($rsd->rows == 0)
            {
                $used  = isset($_REQUEST[$name]);
                $date1 = NULL;
                $date2 = NULL;
            }
            else
            {
                $used  = TRUE;
                $temp  = $rsd->fetch();
                $date1 = get_date($temp['date1']);
                $date2 = get_date($temp['date2']);
            }

            $xml .= '<fieldbox name="' . $name . '" label="' . ustr2html($row['state_name']) . ($used ? '" used="true">' : '">')
                  . '<smallbox name="min_' . $name . '" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(get_date(SAMPLE_DATE)) . '">' . try_request('min_' . $name, $date1) . '</smallbox>'
                  . '<smallbox name="max_' . $name . '" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(get_date(SAMPLE_DATE)) . '">' . try_request('max_' . $name, $date2) . '</smallbox>'
                  . '</fieldbox>';
        }

        $xml .= '</group>';

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
                        $used   = isset($_REQUEST[$name]);
                        $param1 = NULL;
                        $param2 = NULL;
                    }
                    else
                    {
                        $used   = TRUE;
                        $temp   = $rsp->fetch();
                        $param1 = $temp['param1'];
                        $param2 = $temp['param2'];
                    }

                    switch ($row['field_type'])
                    {
                        case FIELD_TYPE_NUMBER:

                            $xml .= '<fieldbox name="' . $name . '" label="' . ustr2html($row['field_name']) . ($used ? '" used="true">' : '">')
                                  . '<smallbox name="min_' . $name . '" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . (ustrlen(MAX_FIELD_INTEGER) + 1) . '">' . try_request('min_' . $name, $param1) . '</smallbox>'
                                  . '<smallbox name="max_' . $name . '" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . (ustrlen(MAX_FIELD_INTEGER) + 1) . '">' . try_request('max_' . $name, $param2) . '</smallbox>'
                                  . '</fieldbox>';

                            break;

                        case FIELD_TYPE_STRING:
                        case FIELD_TYPE_MULTILINED:

                            $xml .= '<fieldbox name="' . $name . '" label="' . ustr2html($row['field_name']) . ($used ? '" used="true">' : '">')
                                  . '<editbox name="edit_' . $name . '" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . MAX_FIELD_STRING . '">' . try_request('edit_' . $name, value_find(FIELD_TYPE_STRING, $param1)) . '</editbox>'
                                  . '</fieldbox>';

                            break;

                        case FIELD_TYPE_CHECKBOX:

                            $xml .= '<fieldcheckbox name="' . $name . '" label="' . ustr2html($row['field_name']) . ($used ? '" used="true"' : '"') . (isset($_REQUEST['check_' . $name]) || $param1 ? ' checked="true">' : '>')
                                  . '</fieldcheckbox>';

                            break;

                        case FIELD_TYPE_LIST:

                            $value = try_request('list_' . $name, $param1);

                            $xml .= '<fieldbox name="' . $name . '" label="' . ustr2html($row['field_name']) . ($used ? '" used="true">' : '">')
                                  . '<combobox name="list_' . $name . '">'
                                  . '<listitem value=""></listitem>';

                            $rsv = dal_query('values/lvlist.sql', $row['field_id']);

                            while (($row = $rsv->fetch()))
                            {
                                $xml .= '<listitem value="' . $row['int_value'] . ($value == $row['int_value'] ? '" selected="true">' : '">')
                                      . ustr2html($row['str_value'])
                                      . '</listitem>';
                            }

                            $xml .= '</combobox>'
                                  . '</fieldbox>';

                            break;

                        case FIELD_TYPE_RECORD:

                            $xml .= '<fieldbox name="' . $name . '" label="' . ustr2html($row['field_name']) . ($used ? '" used="true">' : '">')
                                  . '<smallbox name="edit_' . $name . '" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(MAXINT) . '">' . try_request('edit_' . $name, $param1) . '</smallbox>'
                                  . '</fieldbox>';

                            break;

                        case FIELD_TYPE_DATE:

                            $xml .= '<fieldbox name="' . $name . '" label="' . ustr2html($row['field_name']) . ($used ? '" used="true">' : '">')
                                  . '<smallbox name="min_' . $name . '" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(get_date(SAMPLE_DATE)) . '">' . try_request('min_' . $name, $param1) . '</smallbox>'
                                  . '<smallbox name="max_' . $name . '" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(get_date(SAMPLE_DATE)) . '">' . try_request('max_' . $name, $param2) . '</smallbox>'
                                  . '</fieldbox>';

                            break;

                        case FIELD_TYPE_DURATION:

                            $xml .= '<fieldbox name="' . $name . '" label="' . ustr2html($row['field_name']) . ($used ? '" used="true">' : '">')
                                  . '<smallbox name="min_' . $name . '" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(time2ustr(MAX_FIELD_DURATION)) . '">' . try_request('min_' . $name, $param1) . '</smallbox>'
                                  . '<smallbox name="max_' . $name . '" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(time2ustr(MAX_FIELD_DURATION)) . '">' . try_request('max_' . $name, $param2) . '</smallbox>'
                                  . '</fieldbox>';

                            break;

                        default:
                            debug_write_log(DEBUG_WARNING, 'Unknown field type = ' . $row['field_type']);
                    }
                }

                $xml .= '</group>';
            }
        }
    }
}

$xml .= '<button default="true">'   . get_html_resource(RES_OK_ID)     . '</button>'
      . '<button url="filter.php">' . get_html_resource(RES_CANCEL_ID) . '</button>'
      . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>'
      . '</form>'
      . '</content>'
      . '</page>';

echo(xml2html($xml));

?>
