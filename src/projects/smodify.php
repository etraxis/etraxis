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
require_once('../dbo/projects.php');
require_once('../dbo/states.php');
/**#@-*/

init_page();

$error = NO_ERROR;

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    header('Location: index.php');
    exit;
}

// check that requested state exists

$id    = ustr2int(try_request('id'));
$state = state_find($id);

if (!$state)
{
    debug_write_log(DEBUG_NOTICE, 'State cannot be found.');
    header('Location: index.php');
    exit;
}

if (!$state['is_locked'])
{
    debug_write_log(DEBUG_NOTICE, 'Template must be locked.');
    header('Location: sview.php?id=' . $id);
    exit;
}

// changed state has been submitted

if (try_request('submitted') == 'mainform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $state_name    = ustrcut($_REQUEST['state_name'], MAX_STATE_NAME);
    $state_abbr    = ustrcut($_REQUEST['state_abbr'], MAX_STATE_ABBR);
    $next_state_id = ustr2int(try_request('next_state', $state['next_state_id']), 0);
    $responsible   = ustr2int(try_request('responsible', $state['responsible']), 1, 3);

    $error = state_validate($state_name, $state_abbr);

    if ($error == NO_ERROR)
    {
        $error = state_modify($id,
                              $state['template_id'],
                              $state_name,
                              $state_abbr,
							  ($next_state_id == 0 ? NULL : $next_state_id),
                              $responsible);

        if ($error == NO_ERROR)
        {
            header('Location: sview.php?id=' . $id);
            exit;
        }
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $state_name    = $state['state_name'];
    $state_abbr    = $state['state_abbr'];
    $next_state_id = $state['next_state_id'];
    $responsible   = $state['responsible'];
}

// page's title

$title = ustrprocess(get_html_resource(RES_STATE_X_ID), ustr2html($state['state_name']));

// generate page

$xml = gen_context_menu('sindex.php?id=', 'sview.php?id=', 'fview.php?id=', $state['project_id'], $state['template_id'], $id)
     . '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_PROJECTS_ID) . '</breadcrumb>'
     . '<breadcrumb url="tindex.php?id=' . $state['project_id']  . '">' . ustrprocess(get_html_resource(RES_PROJECT_X_ID),  ustr2html($state['project_name']))  . '</breadcrumb>'
     . '<breadcrumb url="sindex.php?id=' . $state['template_id'] . '">' . ustrprocess(get_html_resource(RES_TEMPLATE_X_ID), ustr2html($state['template_name'])) . '</breadcrumb>'
     . '<breadcrumb url="sview.php?id=' . $id . '">' . $title . '</breadcrumb>'
     . '<breadcrumb url="smodify.php?id=' . $id . '">' . get_html_resource(RES_MODIFY_ID) . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<content>'
     . '<form name="mainform" action="smodify.php?id=' . $id . '">'
     . '<group title="' . get_html_resource(RES_STATE_INFO_ID) . '">'
     . '<control name="state_name" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
     . '<label>' . get_html_resource(RES_STATE_NAME_ID) . '</label>'
     . '<editbox maxlen="' . MAX_STATE_NAME . '">' . ustr2html($state_name) . '</editbox>'
     . '</control>'
     . '<control name="state_abbr" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
     . '<label>' . get_html_resource(RES_STATE_ABBR_ID) . '</label>'
     . '<editbox maxlen="' . MAX_STATE_ABBR . '">' . ustr2html($state_abbr) . '</editbox>'
     . '</control>';

if ($state['state_type'] != STATE_TYPE_FINAL)
{
    $states = dal_query('states/list.sql', $state['template_id'], 'state_name asc');

    if ($states->rows != 0)
    {
        $xml .= '<control name="next_state">'
              . '<label>' . get_html_resource(RES_NEXT_STATE_BY_DEFAULT_ID) . '</label>'
              . '<combobox>'
              . '<listitem value="0">' . get_html_resource(RES_NONE_ID) . '</listitem>';

        while (($row = $states->fetch()))
        {
            $xml .= ($row['state_id'] == $next_state_id
                        ? '<listitem value="' . $row['state_id'] . '" selected="true">'
                        : '<listitem value="' . $row['state_id'] . '">')
                  . ustr2html($row['state_name'])
                  . '</listitem>';
        }

        $xml .= '</combobox>'
              . '</control>';
    }

    if (is_state_removable($id))
    {
        $xml .= '<control name="responsible">'
              . '<label>' . get_html_resource(RES_RESPONSIBLE_ID) . '</label>';

        foreach ($state_responsible_res as $key => $value)
        {
            $xml .= '<radio value="' . $key . ($responsible == $key ? '" checked="true">' : '">') . get_html_resource($value) . '</radio>';
        }

        $xml .= '</control>';
    }
}

$xml .= '</group>'
      . '<button default="true">' . get_html_resource(RES_OK_ID) . '</button>'
      . '<button url="sview.php?id=' . $id . '">' . get_html_resource(RES_CANCEL_ID) . '</button>'
      . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>'
      . '</form>';

// if some error was specified to display, force an alert

switch ($error)
{
    case ERROR_INCOMPLETE_FORM:
        $xml .= '<script>alert("' . get_js_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '");</script>';
        break;
    case ERROR_ALREADY_EXISTS:
        $xml .= '<script>alert("' . get_js_resource(RES_ALERT_STATE_ALREADY_EXISTS_ID) . '");</script>';
        break;
    default: ;  // nop
}

$xml .= '</content>';

echo(xml2html($xml, $title));

?>
