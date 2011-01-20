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
require_once('../dbo/accounts.php');
/**#@-*/

init_page();

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    header('Location: index.php');
    exit;
}

// check that requested account exists

$id      = ustr2int(try_request('id'));
$account = account_find($id);

if (!$account)
{
    debug_write_log(DEBUG_NOTICE, 'Account cannot be found.');
    header('Location: index.php');
    exit;
}

if ($account['is_ldapuser'])
{
    debug_write_log(DEBUG_NOTICE, 'Active Directory account cannot be viewed.');
    header('Location: index.php');
    exit;
}

// local JS functions

$resTitle  = ustrprocess(get_js_resource(RES_ACCOUNT_X_ID), ustr2js(account_get_username($account['username'], FALSE)));
$resOK     = get_js_resource(RES_OK_ID);
$resCancel = get_js_resource(RES_CANCEL_ID);

$xml = <<<JQUERY
<script>

function accountModify ()
{
    jqModal("{$resTitle}", "modify.php?id={$id}", "{$resOK}", "{$resCancel}", "$('#modifyform').submit()");
}

function accountToggle ()
{
    $.post("disable.php?id={$id}", function () {
        reloadTab();
    });
}

function accountUnlock ()
{
    $.post("unlock.php?id={$id}", function () {
        reloadTab();
    });
}

</script>
JQUERY;

// generate buttons

$xml .= '<button url="index.php">' . get_html_resource(RES_BACK_ID) . '</button>'
      . '<buttonset>'
      . '<button action="accountModify()">' . get_html_resource(RES_MODIFY_ID) . '</button>';

$xml .= (is_account_removable($id)
            ? '<button url="delete.php?id=' . $id . '" prompt="' . get_js_resource(RES_CONFIRM_DELETE_ACCOUNT_ID) . '">'
            : '<button disabled="false">')
      . get_html_resource(RES_DELETE_ID)
      . '</button>';

$xml .= '<button action="accountToggle()">'
      . get_html_resource($account['is_disabled'] ? RES_ENABLE_ID : RES_DISABLE_ID)
      . '</button>';

if (is_account_locked($account['locks_count'], $account['lock_time']))
{
    $xml .= '<button action="accountUnlock()">'
          . get_html_resource(RES_UNLOCK_ID)
          . '</button>';
}

$xml .= '</buttonset>';

// generate account information

$xml .= '<group title="' . get_html_resource(RES_ACCOUNT_INFO_ID) . '">'
      . '<text label="' . get_html_resource(RES_USERNAME_ID)    . '">' . ustr2html(account_get_username($account['username'], FALSE)) . '</text>'
      . '<text label="' . get_html_resource(RES_FULLNAME_ID)    . '">' . ustr2html($account['fullname']) . '</text>'
      . '<text label="' . get_html_resource(RES_EMAIL_ID)       . '">' . ustr2html($account['email']) . '</text>'
      . '<text label="' . get_html_resource(RES_DESCRIPTION_ID) . '">' . ustr2html($account['description']) . '</text>'
      . '<text label="' . get_html_resource(RES_PERMISSIONS_ID) . '">' . get_html_resource($account['is_admin'] ? RES_ADMINISTRATOR_ID : RES_USER_ID) . '</text>'
      . '<text label="' . get_html_resource(RES_STATUS_ID)      . '">' . get_html_resource(is_account_locked($account['locks_count'], $account['lock_time']) ? RES_LOCKED_ID : ($account['is_disabled'] ? RES_DISABLED_ID : RES_ACTIVE_ID)) . '</text>'
      . '</group>';

echo(xml2html($xml));

?>
