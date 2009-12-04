<?php

/**
 * @package eTraxis
 * @ignore
 */

//--------------------------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system.
//  Copyright (C) 2006-2009 by Artem Rodygin
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
//  Artem Rodygin           2006-11-13      new-368: User should be able to subscribe other persons.
//  Artem Rodygin           2006-11-20      new-392: Local users should not be extended with '@eTraxis' when LDAP is disabled.
//  Artem Rodygin           2007-11-27      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/accounts.php');
require_once('../dbo/records.php');
/**#@-*/

init_page();

$id = ustr2int(try_request('id'));

if (!EMAIL_NOTIFICATIONS_ENABLED)
{
    debug_write_log(DEBUG_NOTICE, 'Email Notifications functionality is disabled.');
    header('Location: view.php?id=' . $id);
    exit;
}

$record = record_find($id);

if (!$record)
{
    debug_write_log(DEBUG_NOTICE, 'Record cannot be found.');
    header('Location: index.php');
    exit;
}

if (try_request('submitted') == 'lform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted (subscribe selected).');

    if (isset($_REQUEST['nsubscribed']))
    {
        foreach ($_REQUEST['nsubscribed'] as $item)
        {
            record_subscribe($id, $item, $_SESSION[VAR_USERID]);
        }
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, 'No accounts are selected.');
    }

    header('Location: subother.php?id=' . $id);
    exit;
}
elseif (try_request('submitted') == 'rform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted (unsubscribe selected).');

    if (isset($_REQUEST['subscribed']))
    {
        foreach ($_REQUEST['subscribed'] as $item)
        {
            record_unsubscribe($id, $item, $_SESSION[VAR_USERID]);
        }
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, 'No accounts are selected.');
    }

    header('Location: subother.php?id=' . $id);
    exit;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');
}

$xml = '<page' . gen_xml_page_header(record_id($id, $record['template_prefix'])) . '>'
     . gen_xml_menu()
     . '<path>'
     . gen_xml_rec_root(try_cookie(COOKIE_SEARCH_MODE, FALSE))
     . '<pathitem url="view.php?id='     . $id . '">' . ustrprocess(get_html_resource(RES_RECORD_X_ID), record_id($id, $record['template_prefix'])) . '</pathitem>'
     . '<pathitem url="subother.php?id=' . $id . '">' . get_html_resource(RES_SUBSCRIBE_OTHERS_ID) . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<dualbox>'
     . '<dualleft action="subother.php?id=' . $id . '">'
     . '<group title="' . get_html_resource(RES_MEMBERS_ID) . '">'
     . '<listbox dualbox="true" name="nsubscribed[]" size="' . HTML_LISTBOX_SIZE . '" multiple="true">';

$rs = dal_query('records/nsubscribed.sql', $id, $_SESSION[VAR_USERID]);

while (($row = $rs->fetch()))
{
    if ($row['account_id'] != $_SESSION[VAR_USERID])
    {
        $xml .= '<listitem value="' . $row['account_id'] . '">' . ustr2html($row['fullname']) . ' (' . ustr2html(account_get_username($row['username'])) . ')</listitem>';
    }
}

$xml .= '</listbox>'
      . '</group>'
      . '</dualleft>'
      . '<dualright action="subother.php?id=' . $id . '">'
      . '<group title="' . get_html_resource(RES_SUBSCRIBED_ID) . '">'
      . '<listbox dualbox="true" name="subscribed[]" size="' . HTML_LISTBOX_SIZE . '" multiple="true">';

$rs = dal_query('records/subscribed.sql', $id, $_SESSION[VAR_USERID]);

while (($row = $rs->fetch()))
{
    if ($row['account_id'] != $_SESSION[VAR_USERID])
    {
        $xml .= '<listitem value="' . $row['account_id'] . '">' . ustr2html($row['fullname']) . ' (' . ustr2html(account_get_username($row['username'])) . ')</listitem>';
    }
}

$xml .= '</listbox>'
      . '</group>'
      . '</dualright>'
      . '</dualbox>'
      . '<button default="true" url="view.php?id=' . $id . '">' . get_html_resource(RES_BACK_ID) . '</button>'
      . '</content>'
      . '</page>';

echo(xml2html($xml));

?>
