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
require_once('../dbo/templates.php');
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

// check that requested template exists

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

// new state has been submitted

if (try_request('submitted') == 'createform')
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
            header('HTTP/1.0 500 ' . get_html_resource(RES_ALERT_STATE_ALREADY_EXISTS_ID));
            break;

        default:
            header('HTTP/1.0 500 ' . get_html_resource(RES_ALERT_UNKNOWN_ERROR_ID));
    }

    exit;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $state_name    = NULL;
    $state_abbr    = NULL;
    $next_state_id = NULL;
    $responsible   = STATE_RESPONSIBLE_REMAIN;
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

// generate page

$xml .= '<form name="createform" action="screate.php?id=' . $id . '&amp;final=' . $is_final . '" success="createSuccess" error="createError">'
      . '<group>'
      . '<control name="state_name" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
      . '<label>' . get_html_resource(RES_STATE_NAME_ID) . '</label>'
      . '<editbox maxlen="' . MAX_STATE_NAME . '">' . ustr2html($state_name) . '</editbox>'
      . '</control>'
      . '<control name="state_abbr" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
      . '<label>' . get_html_resource(RES_STATE_ABBR_ID) . '</label>'
      . '<editbox maxlen="' . MAX_STATE_ABBR . '">' . ustr2html($state_abbr) . '</editbox>'
      . '</control>';

if (!$is_final)
{
    $states = dal_query('states/list.sql', $id, 'state_name asc');

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

    $xml .= '<control name="responsible">'
          . '<label>' . get_html_resource(RES_RESPONSIBLE_ID) . '</label>'
          . '<radio value="' . STATE_RESPONSIBLE_REMAIN . ($responsible == STATE_RESPONSIBLE_REMAIN ? '" checked="true">' : '">') . get_html_resource(RES_REMAIN_ID) . '</radio>'
          . '<radio value="' . STATE_RESPONSIBLE_ASSIGN . ($responsible == STATE_RESPONSIBLE_ASSIGN ? '" checked="true">' : '">') . get_html_resource(RES_ASSIGN_ID) . '</radio>'
          . '<radio value="' . STATE_RESPONSIBLE_REMOVE . ($responsible == STATE_RESPONSIBLE_REMOVE ? '" checked="true">' : '">') . get_html_resource(RES_REMOVE_ID) . '</radio>'
          . '</control>';
}

$xml .= '</group>'
      . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>'
      . '</form>';

echo(xml2html($xml));

?>
