<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2005-2009  Artem Rodygin
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
 * LDAP
 *
 * This module implements several functions to work with LDAP servers.
 *
 * @package Engine
 * @subpackage LDAP
 */

/**#@+
 * Dependency.
 */
require_once('../config.php');
require_once('../engine/debug.php');
require_once('../engine/utility.php');
/**#@-*/

//------------------------------------------------------------------------------
//  Definitions.
//------------------------------------------------------------------------------

/**#@+
 * LDAP error code.
 */
define('LDAP_SUCCESS',                        0x00);
define('LDAP_OPERATIONS_ERROR',               0x01);
define('LDAP_PROTOCOL_ERROR',                 0x02);
define('LDAP_TIMELIMIT_EXCEEDED',             0x03);
define('LDAP_SIZELIMIT_EXCEEDED',             0x04);
define('LDAP_COMPARE_FALSE',                  0x05);
define('LDAP_COMPARE_TRUE',                   0x06);
define('LDAP_AUTH_METHOD_NOT_SUPPORTED',      0x07);
define('LDAP_STRONG_AUTH_REQUIRED',           0x08);
define('LDAP_PARTIAL_RESULTS',                0x09);
define('LDAP_REFERRAL',                       0x0A);
define('LDAP_ADMINLIMIT_EXCEEDED',            0x0B);
define('LDAP_UNAVAILABLE_CRITICAL_EXTENSION', 0x0C);
define('LDAP_CONFIDENTIALITY_REQUIRED',       0x0D);
define('LDAP_SASL_BIND_INPROGRESS',           0x0E);
define('LDAP_NO_SUCH_ATTRIBUTE',              0x10);
define('LDAP_UNDEFINED_TYPE',                 0x11);
define('LDAP_INAPPROPRIATE_MATCHING',         0x12);
define('LDAP_CONSTRAINT_VIOLATION',           0x13);
define('LDAP_TYPE_OR_VALUE_EXISTS',           0x14);
define('LDAP_INVALID_SYNTAX',                 0x15);
define('LDAP_NO_SUCH_OBJECT',                 0x20);
define('LDAP_ALIAS_PROBLEM',                  0x21);
define('LDAP_INVALID_DN_SYNTAX',              0x22);
define('LDAP_IS_LEAF',                        0x23);
define('LDAP_ALIAS_DEREF_PROBLEM',            0x24);
define('LDAP_INAPPROPRIATE_AUTH',             0x30);
define('LDAP_INVALID_CREDENTIALS',            0x31);
define('LDAP_INSUFFICIENT_ACCESS',            0x32);
define('LDAP_BUSY',                           0x33);
define('LDAP_UNAVAILABLE',                    0x34);
define('LDAP_UNWILLING_TO_PERFORM',           0x35);
define('LDAP_LOOP_DETECT',                    0x36);
define('LDAP_SORT_CONTROL_MISSING',           0x3C);
define('LDAP_INDEX_RANGE_ERROR',              0x3D);
define('LDAP_NAMING_VIOLATION',               0x40);
define('LDAP_OBJECT_CLASS_VIOLATION',         0x41);
define('LDAP_NOT_ALLOWED_ON_NONLEAF',         0x42);
define('LDAP_NOT_ALLOWED_ON_RDN',             0x43);
define('LDAP_ALREADY_EXISTS',                 0x44);
define('LDAP_NO_OBJECT_CLASS_MODS',           0x45);
define('LDAP_RESULTS_TOO_LARGE',              0x46);
define('LDAP_AFFECTS_MULTIPLE_DSAS',          0x47);
define('LDAP_OTHER',                          0x50);
define('LDAP_SERVER_DOWN',                    0x51);
define('LDAP_LOCAL_ERROR',                    0x52);
define('LDAP_ENCODING_ERROR',                 0x53);
define('LDAP_DECODING_ERROR',                 0x54);
define('LDAP_TIMEOUT',                        0x55);
define('LDAP_AUTH_UNKNOWN',                   0x56);
define('LDAP_FILTER_ERROR',                   0x57);
define('LDAP_USER_CANCELLED',                 0x58);
define('LDAP_PARAM_ERROR',                    0x59);
define('LDAP_NO_MEMORY',                      0x5A);
define('LDAP_CONNECT_ERROR',                  0x5B);
define('LDAP_NOT_SUPPORTED',                  0x5C);
define('LDAP_CONTROL_NOT_FOUND',              0x5D);
define('LDAP_NO_RESULTS_RETURNED',            0x5E);
define('LDAP_MORE_RESULTS_TO_RETURN',         0x5F);
define('LDAP_CLIENT_LOOP',                    0x60);
define('LDAP_REFERRAL_LIMIT_EXCEEDED',        0x61);
/**#@-*/

//------------------------------------------------------------------------------
//  Functions.
//------------------------------------------------------------------------------

/**
 * Searches for specified username on LDAP server.
 *
 * The function searches for specified <i>username</i>.
 * If user is found, then his display name and email address are returned, otherwise NULL is returned.
 * If <i>password</i> is specified, then function also tries to authorize on LDAP server using specified <i>username</i> and <i>password</i>.
 * If authorization is failed, NULL is returned, even when user with specified <i>username</i> was successfully found.
 *
 * @param string $username Login of user to be found.
 * @param string $password Password of user.
 * @return array The array which contains two items: first item is display name of user, second one - his email.
 * If user was not found, or cannot be authorized with specified password, then NULL is returned.
 */
function ldap_finduser ($username, $password = NULL)
{
    debug_write_log(DEBUG_TRACE, '[ldap_finduser]');
    debug_write_log(DEBUG_DUMP,  '[ldap_finduser] $username = ' . $username);

    $link = @ldap_connect(LDAP_HOST, LDAP_PORT);

    if (!$link)
    {
        debug_write_log(DEBUG_ERROR, '[ldap_finduser] ldap_connect() error.');
        return NULL;
    }

    $retval = NULL;

    if (!@ldap_set_option($link, LDAP_OPT_PROTOCOL_VERSION, 3))
    {
        debug_write_log(DEBUG_ERROR, '[ldap_finduser] ldap_set_option(LDAP_OPT_PROTOCOL_VERSION) error: ' . ldap_err2str(ldap_errno($link)));
    }
    elseif (!@ldap_set_option($link, LDAP_OPT_REFERRALS, 0))
    {
        debug_write_log(DEBUG_ERROR, '[ldap_finduser] ldap_set_option(LDAP_OPT_REFERRALS) error: ' . ldap_err2str(ldap_errno($link)));
    }
    elseif (LDAP_USE_TLS && !@ldap_start_tls($link))
    {
        debug_write_log(DEBUG_ERROR, '[ldap_finduser] ldap_start_tls() error: ' . ldap_err2str(ldap_errno($link)));
    }
    elseif (!@ldap_bind($link, LDAP_USERNAME, LDAP_PASSWORD))
    {
        debug_write_log(DEBUG_WARNING, '[ldap_finduser] ldap_bind(anonymous) error: ' . ldap_err2str(ldap_errno($link)));
    }
    else
    {
        $attrs  = array('dn', LDAP_ATTR_FULLNAME, LDAP_ATTR_EMAIL);
        $basedn = mb_split(';', LDAP_BASEDN);

        for ($i = 0; $i < count($basedn) && is_null($retval); $i++)
        {
            debug_write_log(DEBUG_DUMP, '[ldap_finduser] $basedn = ' . $basedn[$i]);

            $result = @ldap_search($link, $basedn[$i], sprintf("(%s=%s)", LDAP_ATTR_LOGIN, $username), $attrs);

            if (!$result)
            {
                debug_write_log(DEBUG_WARNING, '[ldap_finduser] ldap_search() error: ' . ldap_err2str(ldap_errno($link)));
            }
            else
            {
                $entries = @ldap_get_entries($link, $result);

                if (!$entries || count($entries) <= 1)
                {
                    debug_write_log(DEBUG_WARNING, '[ldap_finduser] ldap_get_entries() error: ' . ldap_err2str(ldap_errno($link)));
                }
                elseif (!is_null($password) && !@ldap_bind($link, $entries[0]['dn'], $password))
                {
                    debug_write_log(DEBUG_WARNING, '[ldap_finduser] ldap_bind(username) error: ' . ldap_err2str(ldap_errno($link)));
                }
                else
                {
                    if (empty($entries[0][LDAP_ATTR_FULLNAME][0]) ||
                        empty($entries[0][LDAP_ATTR_EMAIL   ][0]))
                    {
                        debug_write_log(DEBUG_NOTICE, '[ldap_finduser] Found entries are empty.');
                    }
                    else
                    {
                        debug_write_log(DEBUG_DUMP, '[ldap_finduser] LDAP(displayname) = ' . $entries[0][LDAP_ATTR_FULLNAME][0]);
                        debug_write_log(DEBUG_DUMP, '[ldap_finduser] LDAP(mail)        = ' . $entries[0][LDAP_ATTR_EMAIL   ][0]);

                        $retval = array($entries[0][LDAP_ATTR_FULLNAME][0],
                                        $entries[0][LDAP_ATTR_EMAIL   ][0]);
                    }
                }
            }
        }
    }

    ldap_close($link);

    return $retval;
}

/**
 * Searches for all users of LDAP server and returns array with all findings.
 *
 * If login, display name, or email of some LDAP user is empty, it will not be returned.
 *
 * @return array Array, where each item is associative array with two items.
 * First item is user's login and accessable via "username" index.
 * Second item is user's display name and accessable via "fullname" index.
 */
function ldap_findallusers ()
{
    debug_write_log(DEBUG_TRACE, '[ldap_findallusers]');

    $link = @ldap_connect(LDAP_HOST, LDAP_PORT);

    if (!$link)
    {
        debug_write_log(DEBUG_ERROR, '[ldap_findallusers] ldap_connect() error.');
        return NULL;
    }

    $retval = array();

    if (!@ldap_set_option($link, LDAP_OPT_PROTOCOL_VERSION, 3))
    {
        debug_write_log(DEBUG_ERROR, '[ldap_findallusers] ldap_set_option(LDAP_OPT_PROTOCOL_VERSION) error: ' . ldap_err2str(ldap_errno($link)));
    }
    elseif (!@ldap_set_option($link, LDAP_OPT_REFERRALS, 0))
    {
        debug_write_log(DEBUG_ERROR, '[ldap_findallusers] ldap_set_option(LDAP_OPT_REFERRALS) error: ' . ldap_err2str(ldap_errno($link)));
    }
    elseif (LDAP_USE_TLS && !@ldap_start_tls($link))
    {
        debug_write_log(DEBUG_ERROR, '[ldap_finduser] ldap_start_tls() error: ' . ldap_err2str(ldap_errno($link)));
    }
    elseif (!@ldap_bind($link, LDAP_USERNAME, LDAP_PASSWORD))
    {
        debug_write_log(DEBUG_WARNING, '[ldap_findallusers] ldap_bind(anonymous) error: ' . ldap_err2str(ldap_errno($link)));
    }
    else
    {
        $attrs  = array(LDAP_ATTR_LOGIN, LDAP_ATTR_FULLNAME, LDAP_ATTR_EMAIL);
        $basedn = mb_split(';', LDAP_BASEDN);

        for ($i = 0; $i < count($basedn); $i++)
        {
            debug_write_log(DEBUG_DUMP, '[ldap_findallusers] $basedn = ' . $basedn[$i]);

            $result = @ldap_search($link, $basedn[$i], sprintf("(&(objectcategory=person)(objectclass=user)(%s=*))", LDAP_ATTR_LOGIN), $attrs);

            if (!$result)
            {
                debug_write_log(DEBUG_WARNING, '[ldap_findallusers] ldap_search() error: ' . ldap_err2str(ldap_errno($link)));
            }
            else
            {
                $entries = @ldap_get_entries($link, $result);

                if (!$entries || count($entries) <= 1)
                {
                    debug_write_log(DEBUG_WARNING, '[ldap_findallusers] ldap_get_entries() error: ' . ldap_err2str(ldap_errno($link)));
                }
                else
                {
                    for ($i = 0; $i < count($entries) - 1; $i++)
                    {
                        if (!empty($entries[$i][LDAP_ATTR_LOGIN   ][0]) &&
                            !empty($entries[$i][LDAP_ATTR_FULLNAME][0]) &&
                            !empty($entries[$i][LDAP_ATTR_EMAIL   ][0]))
                        {
                            $entry = array('username' => $entries[$i][LDAP_ATTR_LOGIN   ][0],
                                           'fullname' => $entries[$i][LDAP_ATTR_FULLNAME][0]);

                            array_push($retval, $entry);
                        }
                    }
                }
            }
        }
    }

    ldap_close($link);

    return $retval;
}

?>
