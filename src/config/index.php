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

$xml = '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_CONFIGURATION_ID) . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<content>'
     . '<group title="' . get_html_resource(RES_GENERAL_INFO_ID) . '">'
     . '<text label="' . get_html_resource(RES_LOCALROOT_ID)           . '">' . ustr2html(LOCALROOT)                           . '</text>'
     . '<text label="' . get_html_resource(RES_WEBROOT_ID)             . '">' . ustr2html(WEBROOT)                             . '</text>'
     . '<text label="' . get_html_resource(RES_AUTHENTICATION_TYPE_ID) . '">' . ustr2html($res_auth_types[AUTH_TYPE])          . '</text>'
     . '<text label="' . get_html_resource(RES_DEFAULT_LANGUAGE_ID)    . '">' . get_html_resource(RES_LOCALE_ID, LANG_DEFAULT) . '</text>'
     . '<text label="' . get_html_resource(RES_THEME_ID)               . '">' . ustr2html(THEME_DEFAULT)                       . '</text>'
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
      . '</content>';

echo(xml2html($xml, get_html_resource(RES_CONFIGURATION_ID)));

?>
