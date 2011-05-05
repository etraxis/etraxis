<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2005-2011  Artem Rodygin
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
/**#@-*/

init_page(LOAD_INLINE);

$error = NO_ERROR;

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    header('HTTP/1.1 307 index.php');
    exit;
}

$templates = dal_query('templates/count.sql');

if (MAX_TEMPLATES_NUMBER != 0 && $templates->fetch(0) >= MAX_TEMPLATES_NUMBER)
{
    debug_write_log(DEBUG_NOTICE, 'Maximum amount of templates is already reached.');
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

// new template has been submitted

if (try_request('submitted') == 'createform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $template_name   = ustrcut($_REQUEST['template_name'],   MAX_TEMPLATE_NAME);
    $template_prefix = ustrcut($_REQUEST['template_prefix'], MAX_TEMPLATE_PREFIX);
    $critical_age    = ustrcut($_REQUEST['critical_age'],    ustrlen(MAX_TEMPLATE_DAYS_COUNT));
    $frozen_time     = ustrcut($_REQUEST['frozen_time'],     ustrlen(MAX_TEMPLATE_DAYS_COUNT));
    $description     = ustrcut($_REQUEST['description'],     MAX_TEMPLATE_DESCRIPTION);
    $guest_access    = isset($_REQUEST['guest_access']);

    $error = template_validate($template_name, $template_prefix, $critical_age, $frozen_time);

    if ($error == NO_ERROR)
    {
        $error = template_create($id, $template_name, $template_prefix, $critical_age, $frozen_time, $description, $guest_access);
    }

    switch ($error)
    {
        case NO_ERROR:
            header('HTTP/1.0 200 OK');
            break;

        case ERROR_INCOMPLETE_FORM:
            send_http_error(get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID));
            break;

        case ERROR_ALREADY_EXISTS:
            send_http_error(get_html_resource(RES_ALERT_TEMPLATE_ALREADY_EXISTS_ID));
            break;

        case ERROR_INVALID_INTEGER_VALUE:
            send_http_error(get_html_resource(RES_ALERT_INVALID_INTEGER_VALUE_ID));
            break;

        case ERROR_INTEGER_VALUE_OUT_OF_RANGE:
            send_http_error(ustrprocess(get_html_resource(RES_ALERT_INTEGER_VALUE_OUT_OF_RANGE_ID), MIN_TEMPLATE_DAYS_COUNT, MAX_TEMPLATE_DAYS_COUNT));
            break;

        default:
            send_http_error(get_html_resource(RES_ALERT_UNKNOWN_ERROR_ID));
    }

    exit;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $template_name   = NULL;
    $template_prefix = NULL;
    $critical_age    = NULL;
    $frozen_time     = NULL;
    $description     = NULL;
    $guest_access    = FALSE;
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
    jqAlert("{$resTitle}", XMLHttpRequest.responseText, "{$resOK}");
}

</script>
JQUERY;

// generate page

$xml .= '<form name="createform" action="tcreate.php?id=' . $id . '" success="createSuccess" error="createError">'
      . '<group>'
      . '<control name="template_name" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
      . '<label>' . get_html_resource(RES_TEMPLATE_NAME_ID) . '</label>'
      . '<editbox maxlen="' . MAX_TEMPLATE_NAME . '">' . ustr2html($template_name) . '</editbox>'
      . '</control>'
      . '<control name="template_prefix" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
      . '<label>' . get_html_resource(RES_TEMPLATE_PREFIX_ID) . '</label>'
      . '<editbox maxlen="' . MAX_TEMPLATE_PREFIX . '">' . ustr2html($template_prefix) . '</editbox>'
      . '</control>'
      . '<control name="critical_age">'
      . '<label>' . get_html_resource(RES_CRITICAL_AGE_ID) . '</label>'
      . '<editbox maxlen="' . ustrlen(MAX_TEMPLATE_DAYS_COUNT) . '">' . ustr2html($critical_age) . '</editbox>'
      . '</control>'
      . '<control name="frozen_time">'
      . '<label>' . get_html_resource(RES_FROZEN_TIME_ID) . '</label>'
      . '<editbox maxlen="' . ustrlen(MAX_TEMPLATE_DAYS_COUNT) . '">' . ustr2html($frozen_time) . '</editbox>'
      . '</control>'
      . '<control name="description">'
      . '<label>' . get_html_resource(RES_DESCRIPTION_ID) . '</label>'
      . '<editbox maxlen="' . MAX_TEMPLATE_DESCRIPTION . '">' . ustr2html($description) . '</editbox>'
      . '</control>'
      . '<control name="guest_access">'
      . '<label/>'
      . ($guest_access
            ? '<checkbox checked="true">'
            : '<checkbox>')
      . get_html_resource(RES_GUEST_ACCESS_ID)
      . '</checkbox>'
      . '</control>'
      . '</group>'
      . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>'
      . '<note>' . ustrprocess(get_html_resource(RES_ALERT_FIELD_VALUE_OUT_OF_RANGE_ID), get_html_resource(RES_CRITICAL_AGE_ID), MIN_TEMPLATE_DAYS_COUNT, MAX_TEMPLATE_DAYS_COUNT) . '</note>'
      . '<note>' . ustrprocess(get_html_resource(RES_ALERT_FIELD_VALUE_OUT_OF_RANGE_ID), get_html_resource(RES_FROZEN_TIME_ID),  MIN_TEMPLATE_DAYS_COUNT, MAX_TEMPLATE_DAYS_COUNT) . '</note>'
      . '</form>';

echo(xml2html($xml));

?>
