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
require_once('../dbo/accounts.php');
/**#@-*/

init_page(LOAD_INLINE);

$error = NO_ERROR;

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    header('HTTP/1.1 307 index.php');
    exit;
}

$accounts = dal_query('accounts/count.sql');

if (MAX_ACCOUNTS_NUMBER != 0 && $accounts->fetch(0) >= MAX_ACCOUNTS_NUMBER)
{
    debug_write_log(DEBUG_NOTICE, 'Maximum amount of accounts is already reached.');
    header('HTTP/1.1 307 index.php');
    exit;
}

// new account has been submitted

if (try_request('submitted') == 'createform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $username    = ustrcut($_REQUEST['username'],    MAX_ACCOUNT_USERNAME);
    $fullname    = ustrcut($_REQUEST['fullname'],    MAX_ACCOUNT_FULLNAME);
    $email       = ustrcut($_REQUEST['email'],       MAX_ACCOUNT_EMAIL);
    $description = ustrcut($_REQUEST['description'], MAX_ACCOUNT_DESCRIPTION);
    $passwd1     = ustrcut($_REQUEST['passwd1'],     MAX_ACCOUNT_PASSWORD);
    $passwd2     = ustrcut($_REQUEST['passwd2'],     MAX_ACCOUNT_PASSWORD);
    $locale      = ustr2int($_REQUEST['locale']);
    $is_admin    = isset($_REQUEST['is_admin']);
    $is_disabled = isset($_REQUEST['is_disabled']);

    $error = account_validate($username,
                              $fullname,
                              $email,
                              $passwd1,
                              $passwd2);

    if ($error == NO_ERROR)
    {
        $error = account_create($username,
                                $fullname,
                                $email,
                                $passwd1,
                                $description,
                                $is_admin,
                                $is_disabled,
                                $locale);
    }

    switch ($error)
    {
        case NO_ERROR:
            header('HTTP/1.0 200 OK');
            break;

        case ERROR_INCOMPLETE_FORM:
            send_http_error(get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID));
            break;

        case ERROR_INVALID_USERNAME:
            send_http_error(get_html_resource(RES_ALERT_INVALID_USERNAME_ID));
            break;

        case ERROR_ALREADY_EXISTS:
            send_http_error(get_html_resource(RES_ALERT_ACCOUNT_ALREADY_EXISTS_ID));
            break;

        case ERROR_INVALID_EMAIL:
            send_http_error(get_html_resource(RES_ALERT_INVALID_EMAIL_ID));
            break;

        case ERROR_PASSWORDS_DO_NOT_MATCH:
            send_http_error(get_html_resource(RES_ALERT_PASSWORDS_DO_NOT_MATCH_ID));
            break;

        case ERROR_PASSWORD_TOO_SHORT:
            send_http_error(ustrprocess(get_html_resource(RES_ALERT_PASSWORD_TOO_SHORT_ID), MIN_PASSWORD_LENGTH));
            break;

        default:
            send_http_error(get_html_resource(RES_ALERT_UNKNOWN_ERROR_ID));
    }

    exit;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $username    = NULL;
    $fullname    = NULL;
    $email       = NULL;
    $description = NULL;
    $locale      = LANG_DEFAULT;
    $is_admin    = FALSE;
    $is_disabled = FALSE;
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

$xml .= '<form name="createform" action="create.php" success="createSuccess" error="createError">'
      . '<group>'
      . '<control name="username" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
      . '<label>' . get_html_resource(RES_USERNAME_ID) . '</label>'
      . '<editbox maxlen="' . MAX_ACCOUNT_USERNAME . '">' . ustr2html($username) . '</editbox>'
      . '</control>'
      . '<control name="fullname" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
      . '<label>' . get_html_resource(RES_FULLNAME_ID) . '</label>'
      . '<editbox maxlen="' . MAX_ACCOUNT_FULLNAME . '">' . ustr2html($fullname) . '</editbox>'
      . '</control>'
      . '<control name="email" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
      . '<label>' . get_html_resource(RES_EMAIL_ID) . '</label>'
      . '<editbox maxlen="' . MAX_ACCOUNT_EMAIL . '">' . ustr2html($email) . '</editbox>'
      . '</control>'
      . '<control name="description">'
      . '<label>' . get_html_resource(RES_DESCRIPTION_ID) . '</label>'
      . '<editbox maxlen="' . MAX_ACCOUNT_DESCRIPTION . '">' . ustr2html($description) . '</editbox>'
      . '</control>'
      . '<control name="passwd1" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
      . '<label>' . get_html_resource(RES_PASSWORD_ID) . '</label>'
      . '<passbox maxlen="' . MAX_ACCOUNT_PASSWORD . '"/>'
      . '</control>'
      . '<control name="passwd2" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
      . '<label>' . get_html_resource(RES_PASSWORD_CONFIRM_ID) . '</label>'
      . '<passbox maxlen="' . MAX_ACCOUNT_PASSWORD . '"/>'
      . '</control>'
      . '<control name="locale" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
      . '<label>' . get_html_resource(RES_LANGUAGE_ID) . '</label>'
      . '<combobox>';

$supported_locales = get_supported_locales_sorted();

foreach ($supported_locales as $locale_id => $locale_name)
{
    $xml .= ($locale == $locale_id
                ? '<listitem value="' . $locale_id . '" selected="true">'
                : '<listitem value="' . $locale_id . '">')
          . $locale_name
          . '</listitem>';
}

$xml .= '</combobox>'
      . '</control>'
      . '<control name="is_admin">'
      . '<label/>'
      . ($is_admin
            ? '<checkbox checked="true">'
            : '<checkbox>')
      . get_html_resource(RES_ADMINISTRATOR_ID)
      . '</checkbox>'
      . '</control>'
      . '<control name="is_disabled">'
      . '<label/>'
      . ($is_disabled
            ? '<checkbox checked="true">'
            : '<checkbox>')
      . get_html_resource(RES_DISABLED_ID)
      . '</checkbox>'
      . '</control>'
      . '</group>'
      . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID)                                   . '</note>'
      . '<note>' . ustrprocess(get_html_resource(RES_ALERT_PASSWORD_TOO_SHORT_ID), MIN_PASSWORD_LENGTH) . '</note>'
      . '</form>';

echo(xml2html($xml));

?>
