<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2006-2010  Artem Rodygin
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

// check that requested record exists

$record = record_find($id);

if (!$record)
{
    debug_write_log(DEBUG_NOTICE, 'Record cannot be found.');
    header('Location: index.php');
    exit;
}

// subscribe selected accounts

if (try_request('submitted') == 'subscribeform')
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
}

// unsubscribe selected accounts

elseif (try_request('submitted') == 'unsubscribeform')
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
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');
}

// generate breadcrumbs

$xml = '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_RECORDS_ID) . '</breadcrumb>'
     . '<breadcrumb url="view.php?id=' . $id . '">' . ustrprocess(get_html_resource(RES_RECORD_X_ID), record_id($id, $record['template_prefix'])) . '</breadcrumb>'
     . '<breadcrumb url="subscribe.php?id=' . $id . '">' . get_html_resource(RES_SUBSCRIBE_ID) . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<content>'
     . '<dual>';

// generate left side

$xml .= '<dualleft>'
      . '<form name="subscribeform" action="subscribe.php?id=' . $id . '">'
      . '<group title="' . get_html_resource(RES_MEMBERS_ID) . '">'
      . '<control name="nsubscribed[]">'
      . '<listbox size="10">';

$rs = dal_query('records/nsubscribed.sql', $id, $_SESSION[VAR_USERID]);

while (($row = $rs->fetch()))
{
    if ($row['account_id'] != $_SESSION[VAR_USERID])
    {
        $xml .= '<listitem value="' . $row['account_id'] . '">'
              . ustr2html(sprintf('%s (%s)', $row['fullname'], account_get_username($row['username'])))
              . '</listitem>';
    }
}

$xml .= '</listbox>'
      . '</control>'
      . '</group>'
      . '</form>'
      . '</dualleft>';

// generate right side

$xml .= '<dualright>'
      . '<form name="unsubscribeform" action="subscribe.php?id=' . $id . '">'
      . '<group title="' . get_html_resource(RES_SUBSCRIBED_ID) . '">'
      . '<control name="subscribed[]">'
      . '<listbox size="10">';

$rs = dal_query('records/subscribed.sql', $id, $_SESSION[VAR_USERID]);

while (($row = $rs->fetch()))
{
    if ($row['account_id'] != $_SESSION[VAR_USERID])
    {
        $xml .= '<listitem value="' . $row['account_id'] . '">'
              . ustr2html(sprintf('%s (%s)', $row['fullname'], account_get_username($row['username'])))
              . '</listitem>';
    }
}

$xml .= '</listbox>'
      . '</control>'
      . '</group>'
      . '</form>'
      . '</dualright>';

// generate buttons

$xml .= '<button action="document.subscribeform.submit()">%gt;%gt;</button>'
      . '<button action="document.unsubscribeform.submit()">%lt;%lt;</button>'
      . '</dual>'
      . '<button url="view.php?id=' . $id . '">' . get_html_resource(RES_BACK_ID) . '</button>'
      . '</content>';

echo(xml2html($xml, get_html_resource(RES_SUBSCRIBE_ID)));

?>
