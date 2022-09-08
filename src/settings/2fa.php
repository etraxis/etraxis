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

init_page(LOAD_TAB);

$error = NO_ERROR;

// 2FA form is submitted

if (try_request('submitted') == '2faform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $secret = ustrcut($_REQUEST['secret'], 32);

    debug_write_log(DEBUG_DUMP, sprintf('[2FA] %s %s', $_SESSION[VAR_USERID], $_SESSION[VAR_USERNAME], $secret));

    if ($secret) {
        dal_query('accounts/set2fa.sql', $_SESSION[VAR_USERID], $secret);
    }
    else {
        dal_query('accounts/clear2fa.sql', $_SESSION[VAR_USERID]);
    }

    header('Location: ../settings/index.php');

    exit;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $isEnabled = is_2fa_enabled($_SESSION[VAR_USERID]);
}

// local JS functions

$resTitle   = get_js_resource(RES_SETTINGS_ID);
$resError   = get_js_resource(RES_ERROR_ID);
$resMessage = get_js_resource(RES_ALERT_SUCCESSFULLY_SAVED_ID);
$resOK      = get_js_resource(RES_OK_ID);

$uri = ustr2html(try_cookie(COOKIE_URI, '../settings/index.php'));
clear_cookie(COOKIE_URI);

$xml = <<<JQUERY
<script>

var is2FA = '{$isEnabled}'.length != 0;

function google2faRefresh ()
{
    window.open("{$uri}", "_parent");
}

function google2faSuccess ()
{
    jqAlert("{$resTitle}", "{$resMessage}", "{$resOK}", "google2faRefresh()");
}

function google2faError (XMLHttpRequest)
{
    jqAlert("{$resError}", XMLHttpRequest.responseText, "{$resOK}");
}

var form   = document.getElementById('2faform');
var qrcode = document.getElementById('qrcode');
var secret = document.getElementById('secret');
var label  = document.querySelector('label[for="key"]');
var key    = document.getElementById('key');

var buttonEnable  = document.getElementById('enable2FA');
var buttonDisable = document.getElementById('disable2FA');
var buttonVerify  = document.getElementById('verify2FA');

secret.style.display       = 'none';
label.style.display        = 'none';
key.style.display          = 'none';
buttonVerify.style.display = 'none';

if (is2FA) {
    buttonEnable.style.display = 'none';
}
else {
    buttonDisable.style.display = 'none';
}

if (buttonEnable) {
    buttonEnable.addEventListener('click', function () {
        $.post('2fa-generate.php', function (response) {
            secret.value = response;
            $.post('2fa-qrcode.php', { username: '{$_SESSION[VAR_USERNAME]}', secret: secret.value }, function (response) {
                qrcode.innerHTML = response;
                buttonEnable.style.display = 'none';
                buttonVerify.style.display = '';
                label.style.display        = '';
                key.style.display          = '';
                $('td:contains("Status:")').parent().hide();
            });
        });
    });
}

if (buttonDisable) {
    buttonDisable.addEventListener('click', function () {
        secret.value = '';
        form.submit();
    });
}

if (buttonVerify) {
    buttonVerify.addEventListener('click', function () {
        $.post('2fa-verify.php', { secret: secret.value, key: key.value }, function (response) {
            if (response == 0) {
                jqAlert("{$resError}", 'The entered code is wrong, please try again.', "{$resOK}");
            }
            else {
                form.submit();
            }
        });
    });
}

</script>
JQUERY;

// generate contents

$xml .= '<form name="2faform" action="2fa.php" success="google2faSuccess" error="google2faError">'
      . '<group>'
      . '<text label="' . get_html_resource(RES_STATUS_ID) . '">' . ustrtolower(get_html_resource($isEnabled ? RES_ENABLED2_ID : RES_DISABLED2_ID)) . '</text>'
      . '<div id="qrcode"></div>'
      . '<control name="secret">'
      . '<editbox></editbox>'
      . '</control>'
      . '<control name="key" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
      . '<label>2FA code</label>'
      . '<editbox maxlen="6"></editbox>'
      . '</control>'
      . '</group>'
      . '<button name="enable2FA">'  . get_html_resource(RES_ENABLE_ID)  . '</button>'
      . '<button name="disable2FA">' . get_html_resource(RES_DISABLE_ID) . '</button>'
      . '<button name="verify2FA">Verify</button>'
      . '</form>';

echo(xml2html($xml));

?>
