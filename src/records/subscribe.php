<?php

/**
 * @package eTraxis
 * @ignore
 */

//--------------------------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system.
//  Copyright (C) 2005-2010 by Artem Rodygin
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
//  Artem Rodygin           2005-09-17      new-125: Email notifications advanced filter.
//  Artem Rodygin           2005-09-18      bug-130: Subscriptions should not be accessible when Email Notifications functionality is disabled.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-10-22      new-150: User should have ability to modify his subscriptions.
//  Artem Rodygin           2005-11-17      new-176: Change eTraxis design.
//  Artem Rodygin           2006-04-01      new-233: Email subscriptions functionality (new-125) should be conditionally "compiled".
//  Artem Rodygin           2006-07-12      bug-292: Sablotron fails if page contains '&' character.
//  Artem Rodygin           2006-11-15      bug-384: The 'Modify' button of subscriptions list does not work.
//  Artem Rodygin           2006-12-20      new-459: 'Filters' and 'Subscriptions' pages should contain ability to clear current selection.
//  Artem Rodygin           2006-12-30      new-475: Turning subscriptions on and off is not clear.
//  Artem Rodygin           2007-11-27      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//  Artem Rodygin           2010-01-26      bug-894: Some pages don't work in Google Chrome.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/subscribes.php');
/**#@-*/

init_page();

if (!EMAIL_NOTIFICATIONS_ENABLED)
{
    debug_write_log(DEBUG_NOTICE, 'Email Notifications functionality is disabled.');
    header('Location: index.php');
    exit;
}

if (try_request('submitted') == 'lform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted (enabling subscriptions).');

    if (isset($_REQUEST['subscribes']))
    {
        subscribes_set($_REQUEST['subscribes']);
    }

    header('Location: subscribe.php');
    exit;
}
elseif (try_request('submitted') == 'rform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted (disabling subscriptions).');

    if (isset($_REQUEST['subscribes']))
    {
        subscribes_clear($_REQUEST['subscribes']);
    }

    header('Location: subscribe.php');
    exit;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');
}

$xml = '<page' . gen_xml_page_header(get_html_resource(RES_SUBSCRIPTIONS_ID)) . '>'
     . gen_xml_menu()
     . '<path>'
     . gen_xml_rec_root()
     . '<pathitem url="subscribe.php">' . get_html_resource(RES_SUBSCRIPTIONS_ID) . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<dualbox>'
     . '<dualleft action="subscribe.php">'
     . '<group title="' . get_html_resource(RES_DISABLED2_ID) . '">'
     . '<listbox dualbox="true" name="subscribes[]" size="' . HTML_LISTBOX_SIZE . '" multiple="true">';

$list = subscribes_list($_SESSION[VAR_USERID]);

while (($item = $list->fetch()))
{
    if (!$item['is_activated'])
    {
        $xml .= '<listitem value="' . $item['subscribe_id'] . '">' . ustr2html($item['subscribe_name']) . '</listitem>';
    }
}

$xml .= '</listbox>'
      . '<button action="window.open(\'smodify.php?id=\'+getElementsByName(\'subscribes[]\')[0].value,\'_parent\');">' . get_html_resource(RES_MODIFY_ID) . '</button>'
      . '<button action="lform.action=\'sdelete.php\';lform.submit();" prompt="' . get_html_resource(RES_CONFIRM_DELETE_SUBSCRIPTIONS_ID) . '">' . get_html_resource(RES_DELETE_ID) . '</button>'
      . '</group>'
      . '</dualleft>'
      . '<dualright action="subscribe.php">'
      . '<group title="' . get_html_resource(RES_ENABLED2_ID) . '">'
      . '<listbox dualbox="true" name="subscribes[]" size="' . HTML_LISTBOX_SIZE . '" multiple="true">';

$list->seek();

while (($item = $list->fetch()))
{
    if ($item['is_activated'])
    {
        $xml .= '<listitem value="' . $item['subscribe_id'] . '">' . ustr2html($item['subscribe_name']) . '</listitem>';
    }
}

$xml .= '</listbox>'
      . '<button action="window.open(\'smodify.php?id=\'+getElementsByName(\'subscribes[]\')[1].value,\'_parent\');">' . get_html_resource(RES_MODIFY_ID) . '</button>'
      . '<button action="rform.action=\'sdelete.php\';rform.submit();" prompt="' . get_html_resource(RES_CONFIRM_DELETE_SUBSCRIPTIONS_ID) . '">' . get_html_resource(RES_DELETE_ID) . '</button>'
      . '</group>'
      . '</dualright>'
      . '</dualbox>'
      . '<button default="true" url="index.php">' . get_html_resource(RES_BACK_ID)   . '</button>'
      . '<button url="screate.php">'              . get_html_resource(RES_CREATE_ID) . '</button>'
      . '</content>'
      . '</page>';

echo(xml2html($xml));

?>
