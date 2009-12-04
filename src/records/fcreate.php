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
//  Artem Rodygin           2005-07-25      new-009: Records filter.
//  Artem Rodygin           2005-07-31      new-006: Records search.
//  Artem Rodygin           2005-08-18      new-037: Any template should be locked to be modified without suspending a project.
//  Artem Rodygin           2005-08-23      new-053: All the calls of DAL API functions should be moved to DBO API.
//  Artem Rodygin           2005-08-24      bug-056: Records filter cannot be created for suspended projects.
//  Artem Rodygin           2005-08-24      bug-075: PHP Fatal error: Call to undefined function: state_find()
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-09-02      bug-076: Global groups members are not displayed in filter parameters.
//  Artem Rodygin           2005-09-15      new-122: User should be able to create a filter to display postponed records only.
//  Artem Rodygin           2005-09-17      bug-127: Project-wide filter does not skip closed records when should.
//  Artem Rodygin           2005-09-18      new-073: Implement search folders.
//  Artem Rodygin           2005-09-19      bug-133: 'Text to be searched' field in filter creation form is marked as required.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-10-22      bug-163: Some filters are malfunctional.
//  Artem Rodygin           2005-11-17      new-176: Change eTraxis design.
//  Artem Rodygin           2005-11-26      new-181: 'All fields marked with * should be filled in.' note is absent.
//  Artem Rodygin           2006-06-28      new-274: "Crumbs" for creation and modification of filters or subscriptions are not clear.
//  Artem Rodygin           2006-07-12      bug-292: Sablotron fails if page contains '&' character.
//  Artem Rodygin           2006-07-27      new-261: UI design should be adopted to slow connection.
//  Artem Rodygin           2006-10-12      new-137: Custom queries.
//  Artem Rodygin           2006-10-17      new-361: Extended custom queries.
//  Artem Rodygin           2006-11-05      new-365: Filters sharing.
//  Artem Rodygin           2006-11-20      new-392: Local users should not be extended with '@eTraxis' when LDAP is disabled.
//  Artem Rodygin           2006-11-24      new-377: Custom views.
//  Artem Rodygin           2007-09-12      new-574: Filter should allow to specify several states.
//  Artem Rodygin           2007-10-02      bug-589: List of states (empty) is displayed when "all templates" filter is being created.
//  Yury Udovichenko        2007-11-20      new-536: Ability to hide postpone records from the list.
//  Artem Rodygin           2007-11-27      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2008-03-15      new-683: Filters should be sharable with groups, not with accounts.
//  Artem Rodygin           2008-03-15      new-501: Filter should allow to specify 'none' value of 'list' fields.
//  Artem Rodygin           2008-04-03      new-694: Filter for unassigned records.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//  Artem Rodygin           2009-09-09      new-826: Native unicode support for Microsoft SQL Server.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/accounts.php');
require_once('../dbo/states.php');
require_once('../dbo/fields.php');
require_once('../dbo/filters.php');
/**#@-*/

init_page();

$filter_name = NULL;
$unclosed    = FALSE;
$postponed   = 0;

if (try_request('submitted') == 'projectform')
{
    debug_write_log(DEBUG_NOTICE, 'Project is selected.');

    $project_id = ustr2int(try_request('project'));

    if ($project_id == 0)
    {
        $project_name = get_html_resource(RES_ALL_PROJECTS_ID);
        $form = 'mainform';
    }
    else
    {
        $rs = dal_query('records/pfndid2.sql', $_SESSION[VAR_USERID], $project_id);

        if ($rs->rows == 0)
        {
            debug_write_log(DEBUG_NOTICE, 'Project cannot be found.');
            header('Location: filter.php');
            exit;
        }

        $project_name = $rs->fetch('project_name');
        $form = 'templateform';
    }
}
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
            header('Location: filter.php');
            exit;
        }

        $project_name  = $rs->fetch('project_name');
        $template_name = get_html_resource(RES_ALL_TEMPLATES_ID);
        $form = 'mainform';
    }
    else
    {
        $rs = dal_query('records/tfndid2.sql', $_SESSION[VAR_USERID], $project_id, $template_id);

        if ($rs->rows == 0)
        {
            debug_write_log(DEBUG_NOTICE, 'Template cannot be found.');
            header('Location: filter.php');
            exit;
        }

        $row = $rs->fetch();

        $project_name  = $row['project_name'];
        $template_name = $row['template_name'];
        $form = 'stateform';
    }
}
elseif (try_request('submitted') == 'stateform')
{
    debug_write_log(DEBUG_NOTICE, 'State is selected.');

    $project_id  = ustr2int(try_request('project'));
    $template_id = ustr2int(try_request('template'));
    $states      = (isset($_REQUEST['states']) ? $_REQUEST['states'] : array());

    $rs = dal_query('records/tfndid2.sql', $_SESSION[VAR_USERID], $project_id, $template_id);

    if ($rs->rows == 0)
    {
        debug_write_log(DEBUG_NOTICE, 'Template cannot be found.');
        header('Location: filter.php');
        exit;
    }

    $row = $rs->fetch();

    $project_name  = $row['project_name'];
    $template_name = $row['template_name'];

    $form = 'mainform';
}
elseif (try_request('submitted') == 'mainform')
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

        if (isset($_REQUEST['assigned_on']) &&
            count($_REQUEST['assigned_on']) != 0)
        {
            $filter_flags |= (in_array(0, $_REQUEST['assigned_on']) ? FILTER_FLAG_UNASSIGNED : FILTER_FLAG_ASSIGNED_ON);
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

                if (($filter_flags & FILTER_FLAG_ASSIGNED_ON) != 0)
                {
                    foreach ($_REQUEST['assigned_on'] as $item)
                    {
                        dal_query('filters/facreate.sql', $id, FILTER_FLAG_ASSIGNED_ON, $item);
                    }
                }

                if ($template_id != 0)
                {
                    filter_trans_set($id, $template_id);
                    filter_fields_set($id, $template_id);
                }
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

    if ($template_id == 0)
    {
        if ($project_id == 0)
        {
            $project_name = get_html_resource(RES_ALL_PROJECTS_ID);
            unset($template_id);
        }
        else
        {
            $rs = dal_query('records/pfndid2.sql', $_SESSION[VAR_USERID], $project_id);

            if ($rs->rows == 0)
            {
                debug_write_log(DEBUG_NOTICE, 'Project cannot be found.');
                header('Location: filter.php');
                exit;
            }

            $project_name  = $rs->fetch('project_name');
            $template_name = get_html_resource(RES_ALL_TEMPLATES_ID);
        }
    }
    else
    {
        $rs = dal_query('records/tfndid2.sql', $_SESSION[VAR_USERID], $project_id, $template_id);

        if ($rs->rows == 0)
        {
            debug_write_log(DEBUG_NOTICE, 'Template cannot be found.');
            header('Location: filter.php');
            exit;
        }

        $row = $rs->fetch();

        $project_name  = $row['project_name'];
        $template_name = $row['template_name'];
    }

    $form = 'mainform';
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $form = 'projectform';
}

$xml = '<page' . gen_xml_page_header(get_html_resource(RES_NEW_FILTER_ID), isset($alert) ? $alert : NULL) . '>'
     . gen_xml_menu()
     . '<path>'
     . gen_xml_rec_root()
     . '<pathitem url="filter.php">'  . get_html_resource(RES_FILTERS_ID)    . '</pathitem>'
     . '<pathitem url="fcreate.php">' . get_html_resource(RES_NEW_FILTER_ID) . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<form name="' . $form . '" action="fcreate.php">';

if ($form == 'projectform')
{
    $xml .= '<group title="' . get_html_resource(RES_PROJECT_ID) . '">'
          . '<listbox name="project" size="' . HTML_LISTBOX_SIZE . '">'
          . '<listitem value="0">' . get_html_resource(RES_ALL_PROJECTS_ID) . '</listitem>';

    $rs = dal_query('records/plist2.sql', $_SESSION[VAR_USERID]);

    while (($row = $rs->fetch()))
    {
        $xml .= '<listitem value="' . $row['project_id'] . '">' . ustr2html($row['project_name']) . '</listitem>';
    }

    $xml .= '</listbox>'
          . '</group>';
}
elseif (isset($project_id))
{
    $xml .= '<group title="' . get_html_resource(RES_PROJECT_ID) . '">'
          . '<combobox name="project">'
          . '<listitem value="' . $project_id . '">' . ustr2html($project_name) . '</listitem>'
          . '</combobox>'
          . '</group>';
}

if ($form == 'templateform')
{
    $xml .= '<group title="' . get_html_resource(RES_TEMPLATE_ID) . '">'
          . '<listbox name="template" size="' . HTML_LISTBOX_SIZE . '">'
          . '<listitem value="0">' . get_html_resource(RES_ALL_TEMPLATES_ID) . '</listitem>';

    $rs = dal_query('records/tlist2.sql', $_SESSION[VAR_USERID], $project_id);

    while (($row = $rs->fetch()))
    {
        $xml .= '<listitem value="' . $row['template_id'] . '">' . ustr2html($row['template_name']) . '</listitem>';
    }

    $xml .= '</listbox>'
          . '</group>';
}
elseif (isset($template_id))
{
    $xml .= '<group title="' . get_html_resource(RES_TEMPLATE_ID) . '">'
          . '<combobox name="template">'
          . '<listitem value="' . $template_id . '">' . ustr2html($template_name) . '</listitem>'
          . '</combobox>'
          . '</group>';

    if ($template_id != 0)
    {
        $xml .= '<group title="' . get_html_resource(RES_STATES_ID) . '">'
              . '<listbox name="states[]" multiple="true" size="' . HTML_LISTBOX_SIZE . '">';

        $rs = dal_query('states/list.sql', $template_id, 'state_name');

        while (($row = $rs->fetch()))
        {
            if (isset($states))
            {
                $selected = in_array($row['state_id'], $states);
            }
            else
            {
                $selected = FALSE;
            }

            $xml .= '<listitem value="' . $row['state_id'] . ($selected ? '" selected="true">' : '">')
                  . ustr2html($row['state_name'])
                  . '</listitem>';
        }

        $xml .= '</listbox>'
              . '</group>';
    }
}

if ($form == 'mainform')
{
    $xml .= '<group title="' . get_html_resource(RES_GENERAL_INFO_ID) . '">'
          . '<editbox label="' . get_html_resource(RES_FILTER_NAME_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="filter_name" size="' . HTML_EDITBOX_SIZE_MEDIUM . '" maxlen="' . MAX_FILTER_NAME . '">' . ustr2html($filter_name) . '</editbox>'
          . '<checkbox name="unclosed"' . ($unclosed ? ' checked="true">' : '>') . get_html_resource(RES_SHOW_UNCLOSED_ONLY_ID) . '</checkbox>'
          . '<hr/>'
          . '<radios name="postponed" label="' . get_html_resource(RES_POSTPONE_STATUS_ID) . '">'
          . '<radio name="postponed" value="0"' . ($postponed == 0 ? ' checked="true">' : '>') . get_html_resource(RES_SHOW_ALL_ID) . '</radio>'
          . '<radio name="postponed" value="' . FILTER_FLAG_ACTIVE    . ($postponed == FILTER_FLAG_ACTIVE    ? '" checked="true">' : '">') . get_html_resource(RES_SHOW_ACTIVE_ONLY_ID)    . '</radio>'
          . '<radio name="postponed" value="' . FILTER_FLAG_POSTPONED . ($postponed == FILTER_FLAG_POSTPONED ? '" checked="true">' : '">') . get_html_resource(RES_SHOW_POSTPONED_ONLY_ID) . '</radio>'
          . '</radios>';

    $rs = dal_query('groups/list.sql', $project_id, 'is_global, group_name');

    $xml .= '</group>'
          . '<group title="' . get_html_resource(RES_SHARE_WITH_ID) . '">'
          . '<listbox name="sharing[]" multiple="true" size="' . HTML_LISTBOX_SIZE . '">';

    while (($row = $rs->fetch()))
    {
        $xml .= '<listitem value="' . $row['group_id'] . '">' . ustr2html($row['group_name']) . ' (' . get_html_resource($row['is_global'] ? RES_GLOBAL_ID : RES_LOCAL_ID) . ')</listitem>';
    }

    $xml .= '</listbox>'
          . '</group>'
          . '<group title="' . get_html_resource(RES_SHOW_CREATED_BY_ONLY_ID) . '">'
          . '<listbox name="created_by[]" multiple="true" size="' . HTML_LISTBOX_SIZE . '">';

    if ($project_id == 0)
    {
        $rs = dal_query('filters/members2.sql', $_SESSION[VAR_USERID]);
    }
    else
    {
        $rs = dal_query('filters/members.sql', $project_id);
    }

    while (($row = $rs->fetch()))
    {
        $xml .= '<listitem value="' . $row['account_id'] . '">' . ustr2html($row['fullname']) . ' (' . ustr2html(account_get_username($row['username'])) . ')</listitem>';
    }

    $xml .= '</listbox>'
          . '</group>'
          . '<group title="' . get_html_resource(RES_SHOW_ASSIGNED_ON_ONLY_ID) . '">'
          . '<listbox name="assigned_on[]" multiple="true" size="' . HTML_LISTBOX_SIZE . '">'
          . '<listitem value="0">' . get_html_resource(RES_NONE_ID) . '</listitem>';

    $rs->seek();

    while (($row = $rs->fetch()))
    {
        $xml .= '<listitem value="' . $row['account_id'] . '">' . ustr2html($row['fullname']) . ' (' . ustr2html(account_get_username($row['username'])) . ')</listitem>';
    }

    $xml .= '</listbox>'
          . '</group>';

    if (isset($template_id))
    {
        $rs = dal_query('states/list.sql', $template_id, 'state_type, state_name');

        if ($rs->rows != 0)
        {
            $xml .= '<group title="' . get_html_resource(RES_SHOW_MOVED_TO_STATES_ONLY_ID) . '">';

            while (($row = $rs->fetch()))
            {
                $name = 'state' . $row['state_id'];

                $xml .= '<fieldbox name="' . $name . '" label="' . ustr2html($row['state_name']) . (isset($_REQUEST[$name]) ? '" used="true">' : '">')
                      . '<smallbox name="min_' . $name . '" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(get_date(SAMPLE_DATE)) . '">' . try_request('min_' . $name) . '</smallbox>'
                      . '<smallbox name="max_' . $name . '" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(get_date(SAMPLE_DATE)) . '">' . try_request('max_' . $name) . '</smallbox>'
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

                        switch ($row['field_type'])
                        {
                            case FIELD_TYPE_NUMBER:

                                $xml .= '<fieldbox name="' . $name . '" label="' . ustr2html($row['field_name']) . (isset($_REQUEST[$name]) ? '" used="true">' : '">')
                                      . '<smallbox name="min_' . $name . '" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . (ustrlen(MAX_FIELD_INTEGER) + 1) . '">' . try_request('min_' . $name) . '</smallbox>'
                                      . '<smallbox name="max_' . $name . '" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . (ustrlen(MAX_FIELD_INTEGER) + 1) . '">' . try_request('max_' . $name) . '</smallbox>'
                                      . '</fieldbox>';

                                break;

                            case FIELD_TYPE_STRING:
                            case FIELD_TYPE_MULTILINED:

                                $xml .= '<fieldbox name="' . $name . '" label="' . ustr2html($row['field_name']) . (isset($_REQUEST[$name]) ? '" used="true">' : '">')
                                      . '<editbox name="edit_' . $name . '" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . MAX_FIELD_STRING . '">' . try_request('edit_' . $name) . '</editbox>'
                                      . '</fieldbox>';

                                break;

                            case FIELD_TYPE_CHECKBOX:

                                $xml .= '<fieldcheckbox name="' . $name . '" label="' . ustr2html($row['field_name']) . (isset($_REQUEST[$name]) ? '" used="true"' : '"') . (isset($_REQUEST['check_' . $name]) ? ' checked="true">' : '>')
                                      . '</fieldcheckbox>';

                                break;

                            case FIELD_TYPE_LIST:

                                $value = try_request('list_' . $name);

                                $xml .= '<fieldbox name="' . $name . '" label="' . ustr2html($row['field_name']) . (isset($_REQUEST[$name]) ? '" used="true">' : '">')
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

                                $xml .= '<fieldbox name="' . $name . '" label="' . ustr2html($row['field_name']) . (isset($_REQUEST[$name]) ? '" used="true">' : '">')
                                      . '<smallbox name="edit_' . $name . '" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(MAXINT) . '">' . try_request('edit_' . $name) . '</smallbox>'
                                      . '</fieldbox>';

                                break;

                            case FIELD_TYPE_DATE:

                                $xml .= '<fieldbox name="' . $name . '" label="' . ustr2html($row['field_name']) . (isset($_REQUEST[$name]) ? '" used="true">' : '">')
                                      . '<smallbox name="min_' . $name . '" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(get_date(SAMPLE_DATE)) . '">' . try_request('min_' . $name) . '</smallbox>'
                                      . '<smallbox name="max_' . $name . '" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(get_date(SAMPLE_DATE)) . '">' . try_request('max_' . $name) . '</smallbox>'
                                      . '</fieldbox>';

                                break;

                            case FIELD_TYPE_DURATION:

                                $xml .= '<fieldbox name="' . $name . '" label="' . ustr2html($row['field_name']) . (isset($_REQUEST[$name]) ? '" used="true">' : '">')
                                      . '<smallbox name="min_' . $name . '" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(time2ustr(MAX_FIELD_DURATION)) . '">' . try_request('min_' . $name) . '</smallbox>'
                                      . '<smallbox name="max_' . $name . '" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(time2ustr(MAX_FIELD_DURATION)) . '">' . try_request('max_' . $name) . '</smallbox>'
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
}

$xml .= '<button default="true">'   . get_html_resource($form == 'mainform' ? RES_OK_ID : RES_NEXT_ID) . '</button>'
      . '<button url="filter.php">' . get_html_resource(RES_CANCEL_ID)                                 . '</button>'
      . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>'
      . '</form>'
      . '</content>'
      . '</page>';

echo(xml2html($xml));

?>
