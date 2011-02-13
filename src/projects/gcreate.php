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
require_once('../dbo/groups.php');
require_once('../dbo/projects.php');
/**#@-*/

init_page(LOAD_INLINE);

$error = NO_ERROR;

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    header('HTTP/1.1 307 index.php');
    exit;
}

// check that requested project exists

$id      = ustr2int(try_request('id'));
$project = project_find($id);

if (!$project)
{
    debug_write_log(DEBUG_NOTICE, 'Project cannot be found.');
    header('HTTP/1.1 307 index.php');
    exit;
}

// new group has been submitted

if (try_request('submitted') == 'createform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $group_name  = ustrcut($_REQUEST['group_name'],  MAX_GROUP_NAME);
    $description = ustrcut($_REQUEST['description'], MAX_GROUP_DESCRIPTION);

    $error = group_validate($group_name);

    if ($error == NO_ERROR)
    {
        $error = group_create($id, $group_name, $description);
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
            header('HTTP/1.0 500 ' . get_html_resource(RES_ALERT_GROUP_ALREADY_EXISTS_ID));
            break;

        default:
            header('HTTP/1.0 500 ' . get_html_resource(RES_ALERT_UNKNOWN_ERROR_ID));
    }

    exit;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $group_name  = NULL;
    $description = NULL;
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

$xml .= '<form name="createform" action="gcreate.php?id=' . $id . '" success="createSuccess" error="createError">'
      . '<group>'
      . '<control name="group_name" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
      . '<label>' . get_html_resource(RES_GROUP_NAME_ID) . '</label>'
      . '<editbox maxlen="' . MAX_GROUP_NAME . '">' . ustr2html($group_name) . '</editbox>'
      . '</control>'
      . '<control name="description">'
      . '<label>' . get_html_resource(RES_DESCRIPTION_ID) . '</label>'
      . '<editbox maxlen="' . MAX_GROUP_DESCRIPTION . '">' . ustr2html($description) . '</editbox>'
      . '</control>'
      . '</group>'
      . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>'
      . '</form>';

echo(xml2html($xml));

?>
