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
//  Artem Rodygin           2005-08-29      new-068: System settings in 'config.php' should be accessable through web-interface.
//  Artem Rodygin           2005-08-31      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-09-05      bug-092: JScript error on 'Configuration' page.
//  Artem Rodygin           2005-09-13      new-113: When record is being viewed the fields names and values should be aligned by top.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-11-16      new-176: Change eTraxis design.
//  Artem Rodygin           2006-01-24      new-203: Email notification functionality (new-002) should be conditionally "compiled".
//  Artem Rodygin           2006-01-24      new-204: Active Directory Support functionality (new-003) should be conditionally "compiled".
//  Artem Rodygin           2006-07-27      new-261: UI design should be adopted to slow connection.
//  Artem Rodygin           2006-10-08      bug-330: /src/config/index.php: Global variable $res_driver was used before it was defined.
//  Artem Rodygin           2006-11-18      bug-388: "Configuration" page does not display path where binary attachments are stored.
//  Artem Rodygin           2006-11-18      bug-389: Motorola LDAP server returns "Insufficient rights" error.
//  Artem Rodygin           2006-11-20      new-391: 'Configuration' page should not displays details of disabled features.
//  Artem Rodygin           2006-12-30      new-475: Turning subscriptions on and off is not clear.
//  Artem Rodygin           2008-03-02      bug-681: Update configuration page with new options.
//  Artem Rodygin           2008-10-12      new-751: LDAP // Multiple Base DN support.
//  Artem Rodygin           2008-11-09      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-01-09      new-743: Include attached files in the notification.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//  Artem Rodygin           2009-07-29      new-832: Required LDAP attributes should be configurable.
//  Artem Rodygin           2009-10-12      new-848: LDAP TLS support.
//  Artem Rodygin           2010-02-01      new-902: Transparent gzip compression of attachments
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
/**#@-*/

$res_auth_types = array
(
    AUTH_TYPE_BUILTIN => 'eTraxis',
    AUTH_TYPE_BASIC   => 'basic HTTP',
    AUTH_TYPE_DIGEST  => 'digest HTTP',
    AUTH_TYPE_NTLM    => 'NTLM',
);

$res_debug_modes = array
(
    DEBUG_MODE_OFF   => RES_DISABLED_ID,
    DEBUG_MODE_TRACE => RES_DEBUG_MODE_TRACE_ID,
    DEBUG_MODE_FULL  => RES_DEBUG_MODE_FULL_ID,
);

init_page();

if (get_user_level() != USER_LEVEL_ADMIN)
{
    debug_write_log(DEBUG_NOTICE, 'User must have admin rights to be allowed.');
    header('Location: ../index.php');
    exit;
}

global $res_driver;

$xml = '<page' . gen_xml_page_header(get_html_resource(RES_CONFIGURATION_ID)) . '>'
     . gen_xml_menu()
     . '<path>'
     . '<pathitem url="index.php">' . get_html_resource(RES_CONFIGURATION_ID) . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<group title="' . get_html_resource(RES_GENERAL_INFO_ID) . '">'
     . '<text label="' . get_html_resource(RES_LOCALROOT_ID)           . '">' . ustr2html(LOCALROOT)                           . '</text>'
     . '<text label="' . get_html_resource(RES_WEBROOT_ID)             . '">' . ustr2html(WEBROOT)                             . '</text>'
     . '<text label="' . get_html_resource(RES_AUTHENTICATION_TYPE_ID) . '">' . ustr2html($res_auth_types[AUTH_TYPE])          . '</text>'
     . '<text label="' . get_html_resource(RES_DEFAULT_LANGUAGE_ID)    . '">' . get_html_resource(RES_LOCALE_ID, LANG_DEFAULT) . '</text>'
     . '</group>'
     . '<group title="' . get_html_resource(RES_SECURITY_ID) . '">'
     . '<text label="' . get_html_resource(RES_MIN_PASSWORD_LENGTH_ID) . '">' . ustr2html(MIN_PASSWORD_LENGTH) . '</text>'
     . '<text label="' . get_html_resource(RES_PASSWORD_EXPIRATION_ID) . '">' . ustr2html(PASSWORD_EXPIRATION) . '</text>'
     . '<text label="' . get_html_resource(RES_SESSION_EXPIRATION_ID)  . '">' . ustr2html(SESSION_EXPIRE)      . '</text>'
     . '<text label="' . get_html_resource(RES_LOCKS_COUNT_ID)         . '">' . ustr2html(LOCKS_COUNT)         . '</text>'
     . '<text label="' . get_html_resource(RES_LOCKS_TIMEOUT_ID)       . '">' . ustr2html(LOCKS_TIMEOUT)       . '</text>'
     . '</group>'
     . '<group title="' . get_html_resource(RES_DATABASE_ID) . '">'
     . '<text label="' . get_html_resource(RES_DATABASE_TYPE_ID)   . '">' . get_html_resource($res_driver[DATABASE_DRIVER]) . '</text>'
     . '<text label="' . get_html_resource(RES_DATABASE_SERVER_ID) . '">' . ustr2html(DATABASE_HOST)                        . '</text>'
     . '<text label="' . get_html_resource(RES_DATABASE_NAME_ID)   . '">' . ustr2html(DATABASE_DBNAME)                      . '</text>'
     . '<text label="' . get_html_resource(RES_DATABASE_USER_ID)   . '">' . ustr2html(DATABASE_USERNAME)                    . '</text>'
     . '</group>'
     . '<group title="' . get_html_resource(RES_ACTIVE_DIRECTORY_ID) . '">'
     . '<text label="' . get_html_resource(RES_STATUS_ID) . '">'  . ustrtolower(get_html_resource(LDAP_ENABLED ? RES_ENABLED2_ID : RES_DISABLED2_ID)) . '</text>';

if (LDAP_ENABLED)
{
    $ldap_attrs = sprintf("%s = %s", get_html_resource(RES_USERNAME_ID), ustr2html(LDAP_ATTR_LOGIN))    . '%br;'
                . sprintf("%s = %s", get_html_resource(RES_FULLNAME_ID), ustr2html(LDAP_ATTR_FULLNAME)) . '%br;'
                . sprintf("%s = %s", get_html_resource(RES_EMAIL_ID),    ustr2html(LDAP_ATTR_EMAIL));

    $xml .= '<text label="' . get_html_resource(RES_LDAP_SERVER_ID)      . '">' . ustr2html(LDAP_HOST) . '</text>'
          . '<text label="' . get_html_resource(RES_PORT_NUMBER_ID)      . '">' . ustr2html(LDAP_PORT) . '</text>'
          . '<text label="' . get_html_resource(RES_TLS_ID)              . '">' . ustrtolower(get_html_resource(LDAP_USE_TLS ? RES_ENABLED2_ID : RES_DISABLED2_ID)) . '</text>'
          . '<text label="' . get_html_resource(RES_BASE_DN_ID)          . '">' . ustr2html(ustr_replace(';', '%br;', LDAP_BASEDN)) . '</text>'
          . '<text label="' . get_html_resource(RES_SEARCH_ACCOUNT_ID)   . '">' . ustr2html(LDAP_USERNAME) . '</text>'
          . '<text label="' . get_html_resource(RES_LDAP_ATTRIBUTE_ID)   . '">' . $ldap_attrs . '</text>'
          . '<text label="' . get_html_resource(RES_LDAP_ENUMERATION_ID) . '">' . ustrtolower(get_html_resource(LDAP_ENUMERATION ? RES_ENABLED2_ID : RES_DISABLED2_ID)) . '</text>'
          . '<text label="' . get_html_resource(RES_ADMINISTRATORS_ID)   . '">' . ustr2html(ustr_replace(',', '%br;', LDAP_ADMINS)) . '</text>';
}

$xml .= '</group>'
      . '<group title="' . get_html_resource(RES_ATTACHMENTS_ID) . '">'
      . '<text label="' . get_html_resource(RES_STATUS_ID) . '">' . ustrtolower(get_html_resource(ATTACHMENTS_ENABLED ? RES_ENABLED2_ID : RES_DISABLED2_ID)) . '</text>';

if (ATTACHMENTS_ENABLED)
{
    $xml .= '<text label="' . get_html_resource(RES_MAX_SIZE_ID)    . '">' . ustrprocess(get_html_resource(RES_KB_ID), ATTACHMENTS_MAXSIZE) . '</text>'
          . '<text label="' . get_html_resource(RES_COMPRESSION_ID) . '">' . ustrtolower(get_html_resource(ATTACHMENTS_COMPRESSED ? RES_ENABLED2_ID : RES_DISABLED2_ID)) . '</text>'
          . '<text label="' . get_html_resource(RES_STORAGE_ID)     . '">' . ustr2html(ATTACHMENTS_PATH) . '</text>';
}

$xml .= '</group>'
      . '<group title="' . get_html_resource(RES_EMAIL_NOTIFICATIONS_ID) . '">'
      . '<text label="' . get_html_resource(RES_STATUS_ID) . '">' . ustrtolower(get_html_resource(EMAIL_NOTIFICATIONS_ENABLED ? RES_ENABLED2_ID : RES_DISABLED2_ID)) . '</text>';

if (EMAIL_NOTIFICATIONS_ENABLED)
{
    $xml .= '<text label="' . get_html_resource(RES_MAX_SIZE_ID) . '">' . ustrprocess(get_html_resource(RES_KB_ID), EMAIL_ATTACHMENTS_MAXSIZE) . '</text>';
}

$xml .= '</group>'
      . '<group title="' . get_html_resource(RES_DEBUG_ID) . '">'
      . '<text label="' . get_html_resource(RES_DEBUG_MODE_ID) . '">' . get_html_resource($res_debug_modes[DEBUG_MODE]) . '</text>';

if (DEBUG_MODE != DEBUG_MODE_OFF)
{
    $xml .= '<text label="' . get_html_resource(RES_DEBUG_LOGS_ID) . '">' . ustr2html(DEBUG_LOGS) . '</text>';
}

$xml .= '</group>'
      . '<button name="back" url="javascript:history.back();" default="true">' . get_html_resource(RES_BACK_ID) . '</button>'
      . '</content>'
      . '</page>';

echo(xml2html($xml));

?>
