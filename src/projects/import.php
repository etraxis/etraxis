<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2008-2011  Artem Rodygin
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
/**#@-*/

init_page(LOAD_TAB);

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    exit;
}

// XML file is submitted

if (try_request('submitted') == 'mainform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $error = NULL;

    $id = isset($_FILES['xmlfile'])
        ? project_import($_FILES['xmlfile'], $error)
        : 0;

    if ($id == 0 && ustrlen($error) != 0)
    {
        send_http_error($error);
    }
    else
    {
        /**
         * jQuery Form Plugin uses "success" callback function in both cases - success and failure
         * (see https://github.com/malsup/form/issues/107 for details).
         * It makes impossible to distinguish successful response from error messages.
         * To make the difference a successful response is prefixed with "OK ".
         * For the same reasons a workaround function "importSuccess2" is appeared (see its code below).
         */
        header('HTTP/1.0 200 OK');
        echo('OK ' . $id);
    }

    exit;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');
}

// local JS functions

$resTitle = get_js_resource(RES_ERROR_ID);
$resOK    = get_js_resource(RES_OK_ID);

$xml = <<<JQUERY
<script>

function importSuccess (data)
{
    window.open("view.php?id=" + data, "_parent");
}

function importError (XMLHttpRequest)
{
    jqAlert("{$resTitle}", XMLHttpRequest.responseText, "{$resOK}");
}

function importSuccess2 (data)
{
    if (data.substr(0,3) == "OK ")  // success
    {
        importSuccess(data.substr(3));
    }
    else    // error
    {
        jqAlert("{$resTitle}", data, "{$resOK}");
    }
}

</script>
JQUERY;

// generate page

$xml .= '<form name="mainform" action="import.php" upload="' . (ATTACHMENTS_MAXSIZE * 1024) . '" success="importSuccess2" error="importError">'
      . '<group>'
      . '<control name="xmlfile" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
      . '<label>' . get_html_resource(RES_TEMPLATE_ID) . '</label>'
      . '<filebox/>'
      . '</control>'
      . '</group>'
      . '<button default="true">' . get_html_resource(RES_OK_ID) . '</button>'
      . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>'
      . '<note>' . ustrprocess(get_html_resource(RES_ALERT_UPLOAD_FORM_SIZE_ID), ATTACHMENTS_MAXSIZE) . '</note>'
      . '</form>';

echo(xml2html($xml));

?>
