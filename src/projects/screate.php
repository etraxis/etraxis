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
//  Artem Rodygin           2005-03-06      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-08-01      new-013: UI scenarios should be changed.
//  Artem Rodygin           2005-08-18      new-037: Any template should be locked to be modified without suspending a project.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-11-17      new-176: Change eTraxis design.
//  Artem Rodygin           2006-07-27      new-261: UI design should be adopted to slow connection.
//  Artem Rodygin           2007-11-19      new-623: Default state in states list.
//  Artem Rodygin           2007-11-26      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/templates.php');
require_once('../dbo/states.php');
/**#@-*/

init_page();

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    header('Location: index.php');
    exit;
}

$id       = ustr2int(try_request('id'));
$template = template_find($id);

if (!$template)
{
    debug_write_log(DEBUG_NOTICE, 'Template cannot be found.');
    header('Location: index.php');
    exit;
}

if (!$template['is_locked'])
{
    debug_write_log(DEBUG_NOTICE, 'Template must be locked.');
    header('Location: sindex.php?id=' . $id);
    exit;
}

$is_final = ustr2int(try_request('final', 0), 0, 1);

if (try_request('submitted') == 'mainform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $state_name    = ustrcut($_REQUEST['state_name'], MAX_STATE_NAME);
    $state_abbr    = ustrcut($_REQUEST['state_abbr'], MAX_STATE_ABBR);
    $next_state_id = ustr2int(try_request('next_state'), 0);
    $responsible   = ustr2int(try_request('responsible', STATE_RESPONSIBLE_REMOVE), 1, 3);

    $error = state_validate($state_name, $state_abbr);

    if ($error == NO_ERROR)
    {
        $error = state_create($id,
                              $state_name,
                              $state_abbr,
                              ($is_final ? STATE_TYPE_FINAL : STATE_TYPE_INTERMEDIATE),
							  ($next_state_id == 0 ? NULL : $next_state_id),
                              $responsible);

        if ($error == NO_ERROR)
        {
            header('Location: sindex.php?id=' . $id);
            exit;
        }
    }

    switch ($error)
    {
        case ERROR_INCOMPLETE_FORM:
            $alert = get_js_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID);
            break;
        case ERROR_ALREADY_EXISTS:
            $alert = get_js_resource(RES_ALERT_STATE_ALREADY_EXISTS_ID);
            break;
        default:
            $alert = NULL;
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $state_name    = NULL;
    $state_abbr    = NULL;
    $next_state_id = NULL;
    $responsible   = STATE_RESPONSIBLE_REMAIN;
}

$xml = '<page' . gen_xml_page_header(get_html_resource(RES_NEW_STATE_ID), isset($alert) ? $alert : NULL, 'mainform.state_name') . '>'
     . gen_xml_menu()
     . '<path>'
     . '<pathitem url="index.php">'                                              . get_html_resource(RES_PROJECTS_ID)                                                       . '</pathitem>'
     . '<pathitem url="view.php?id='       . $template['project_id']      . '">' . ustrprocess(get_html_resource(RES_PROJECT_X_ID), ustr2html($template['project_name']))   . '</pathitem>'
     . '<pathitem url="tindex.php?id='     . $template['project_id']      . '">' . get_html_resource(RES_TEMPLATES_ID)                                                      . '</pathitem>'
     . '<pathitem url="tview.php?id='      . $id                          . '">' . ustrprocess(get_html_resource(RES_TEMPLATE_X_ID), ustr2html($template['template_name'])) . '</pathitem>'
     . '<pathitem url="sindex.php?id='     . $id                          . '">' . get_html_resource(RES_STATES_ID)                                                         . '</pathitem>'
     . '<pathitem url="screate.php?final=' . $is_final . '&amp;id=' . $id . '">' . get_html_resource(RES_NEW_STATE_ID)                                                      . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<form name="mainform" action="screate.php?final=' . $is_final . '&amp;id=' . $id . '">'
     . '<group title="' . get_html_resource(RES_STATE_INFO_ID) . '">'
     . '<editbox label="' . get_html_resource(RES_STATE_NAME_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="state_name" size="' . HTML_EDITBOX_SIZE_MEDIUM . '" maxlen="' . MAX_STATE_NAME . '">' . ustr2html($state_name) . '</editbox>'
     . '<editbox label="' . get_html_resource(RES_STATE_ABBR_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="state_abbr" size="' . HTML_EDITBOX_SIZE_MEDIUM . '" maxlen="' . MAX_STATE_ABBR . '">' . ustr2html($state_abbr) . '</editbox>';

if (!$is_final)
{
    $states = dal_query('states/list.sql', $id, 'state_type asc, state_name asc');

    if ($states->rows > 0)
    {
        $xml .= '<combobox name="next_state" label="' . get_html_resource(RES_NEXT_STATE_BY_DEFAULT_ID) . '">'
              . '<listitem name="next_state" value="0">' . get_html_resource(RES_NONE_ID) . '</listitem>';

        while (($row = $states->fetch()))
        {
            $xml .= '<listitem name="next_state" value="' . $row['state_id'] . ($row['state_id'] == $next_state_id ? '" selected="true">' : '">')
                  . $row['state_name']
                  . '</listitem>';
        }

        $xml .= '</combobox>';
    }

    $xml .= '<radios name="responsible" label="' . get_html_resource(RES_RESPONSIBLE_ID) . '">'
          . '<radio name="responsible" value="' . STATE_RESPONSIBLE_REMAIN . '"' . ($responsible == STATE_RESPONSIBLE_REMAIN ? ' checked="true">' : '>') . get_html_resource(RES_REMAIN_ID) . '</radio>'
          . '<radio name="responsible" value="' . STATE_RESPONSIBLE_ASSIGN . '"' . ($responsible == STATE_RESPONSIBLE_ASSIGN ? ' checked="true">' : '>') . get_html_resource(RES_ASSIGN_ID) . '</radio>'
          . '<radio name="responsible" value="' . STATE_RESPONSIBLE_REMOVE . '"' . ($responsible == STATE_RESPONSIBLE_REMOVE ? ' checked="true">' : '>') . get_html_resource(RES_REMOVE_ID) . '</radio>'
          . '</radios>';
}

$xml .= '</group>'
      . '<button default="true">'                  . get_html_resource(RES_OK_ID)     . '</button>'
      . '<button url="sindex.php?id=' . $id . '">' . get_html_resource(RES_CANCEL_ID) . '</button>'
      . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>'
      . '</form>'
      . '</content>'
      . '</page>';

echo(xml2html($xml));

?>
