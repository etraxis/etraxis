<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2006-2009  Artem Rodygin
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
require_once('../dbo/views.php');
/**#@-*/

init_page(LOAD_INLINE);

// new view has been submitted

if (try_request('submitted') == 'createform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $view_name = ustrcut($_REQUEST['view_name'], MAX_VIEW_NAME);

    $error = view_validate($view_name);

    if ($error == NO_ERROR)
    {
        $error = view_create($_SESSION[VAR_USERID], $view_name);
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
            header('HTTP/1.0 500 ' . get_html_resource(RES_ALERT_VIEW_ALREADY_EXISTS_ID));
            break;

        default:
            header('HTTP/1.0 500 ' . get_html_resource(RES_ALERT_UNKNOWN_ERROR_ID));
    }

    exit;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $error     = NO_ERROR;
    $view_name = NULL;
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

$xml .= '<form name="createform" action="create.php" success="createSuccess" error="createError">'
      . '<group>'
      . '<control name="view_name" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
      . '<label>' . get_html_resource(RES_VIEW_NAME_ID) . '</label>'
      . '<editbox maxlen="' . MAX_VIEW_NAME . '">' . ustr2html($view_name) . '</editbox>'
      . '</control>'
      . '</group>'
      . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>'
      . '</form>';

echo(xml2html($xml));

?>
