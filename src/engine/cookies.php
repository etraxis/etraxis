<?php

/**
 * Cookies
 *
 * This module provides several useful functions to work with cookies.
 *
 * @package Engine
 * @subpackage Cookies
 */

//--------------------------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system.
//  Copyright (C) 2004-2009 by Artem Rodygin
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
//  Artem Rodygin           2004-11-17      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-07-31      new-006: Records search.
//  Artem Rodygin           2005-09-18      new-073: Implement search folders.
//  Artem Rodygin           2005-09-22      new-141: Source code review.
//  Artem Rodygin           2005-10-15      new-160: Cookies values should not be dumped into debug logs.
//  Artem Rodygin           2006-08-20      new-313: Implement HTTP authentication.
//  Artem Rodygin           2006-11-20      new-377: Custom views.
//  Artem Rodygin           2006-12-09      new-423: Client cookies should be instance-depended.
//  Artem Rodygin           2006-12-15      bug-409: User session expires too quick.
//  Artem Rodygin           2007-11-05      new-571: View should show all records of current filters set.
//  Artem Rodygin           2009-03-11      bug-799: eTraxis doesn't work with XAMPP on Windows.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/debug.php');
require_once('../engine/utility.php');
/**#@-*/

//--------------------------------------------------------------------------------------------------
//  Definitions.
//--------------------------------------------------------------------------------------------------

/**
 * Storage method.
 *
 * When TRUE, client cookies are used to store passed values.
 * Otherwise session variables are in use.
 */
define('USE_CLIENT_COOKIES', TRUE);

/**#@+
 * Authorization cookie.
 */
define('COOKIE_AUTH_USERID', 'AuthUserID');
define('COOKIE_AUTH_TOKEN',  'AuthToken');
/**#@-*/

/**
 * Last requested URI.
 */
define('COOKIE_URI', 'URI');

/**#@+
 * Search cookie.
 */
define('COOKIE_SEARCH_MODE', 'SearchMode');
define('COOKIE_SEARCH_TEXT', 'SearchText');
/**#@-*/

/**#@+
 * Sort mode cookie.
 */
define('COOKIE_ACCOUNTS_SORT',  'AccountsSort');
define('COOKIE_PROJECTS_SORT',  'ProjectsSort');
define('COOKIE_GROUPS_SORT',    'GroupsSort');
define('COOKIE_TEMPLATES_SORT', 'TemplatesSort');
define('COOKIE_STATES_SORT',    'StatesSort');
define('COOKIE_FIELDS_SORT',    'FieldsSort');
define('COOKIE_RECORDS_SORT',   'RecordsSort');
define('COOKIE_EVENTS_SORT',    'EventsSort');
define('COOKIE_CHANGES_SORT',   'ChangesSort');
/**#@-*/

/**#@+
 * Current page cookie.
 */
define('COOKIE_ACCOUNTS_PAGE',  'AccountsPage');
define('COOKIE_PROJECTS_PAGE',  'ProjectsPage');
define('COOKIE_GROUPS_PAGE',    'GroupsPage');
define('COOKIE_TEMPLATES_PAGE', 'TemplatesPage');
define('COOKIE_STATES_PAGE',    'StatesPage');
define('COOKIE_FIELDS_PAGE',    'FieldsPage');
define('COOKIE_RECORDS_PAGE',   'RecordsPage');
define('COOKIE_EVENTS_PAGE',    'EventsPage');
define('COOKIE_CHANGES_PAGE',   'ChangesPage');
/**#@-*/

//--------------------------------------------------------------------------------------------------
//  Functions.
//--------------------------------------------------------------------------------------------------

/**
 * Saves specified value in specified cookie.
 *
 * @param string $cookie Cookie name.
 * @param mixed $value Cookie value.
 * @return bool TRUE on success, FALSE otherwise.
 */
function save_cookie ($cookie, $value)
{
    debug_write_log(DEBUG_TRACE, '[save_cookie]');
    debug_write_log(DEBUG_DUMP,  '[save_cookie] $cookie = ' . $cookie);
    debug_write_log(DEBUG_DUMP,  '[save_cookie] $value  = ' . $value);

    $expire = time() + (SECS_IN_DAY * 365);
    $cookie = md5(WEBROOT . $cookie);

    if (USE_CLIENT_COOKIES)
    {
        debug_write_log(DEBUG_NOTICE, '[save_cookie] Client site cookie is created.');
        $res = setcookie($cookie, $value, $expire, '/');
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, '[save_cookie] Server site cookie is created.');
        $_SESSION[$cookie] = $value;
        $res = TRUE;
    }

    return $res;
}

/**
 * Destroys specified cookie.
 *
 * @param string $cookie Cookie name.
 */
function clear_cookie ($cookie)
{
    debug_write_log(DEBUG_TRACE, '[clear_cookie]');
    debug_write_log(DEBUG_DUMP,  '[clear_cookie] $cookie = ' . $cookie);

    $expire = time() - (SECS_IN_DAY * 365);
    $cookie = md5(WEBROOT . $cookie);

    if (USE_CLIENT_COOKIES)
    {
        debug_write_log(DEBUG_NOTICE, '[clear_cookie] Client site cookie is destroyed.');
        setcookie($cookie, NULL, $expire, '/');
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, '[clear_cookie] Server site cookie is destroyed.');
        unset($_SESSION[$cookie]);
    }
}

/**
 * Finds whether the specified cookie exists.
 *
 * @param string $cookie
 * @return bool TRUE if cookie exists, FALSE otherwise.
 */
function is_cookie_saved ($cookie)
{
    debug_write_log(DEBUG_TRACE, '[is_cookie_saved]');
    debug_write_log(DEBUG_DUMP,  '[is_cookie_saved] $cookie = ' . $cookie);

    $cookie = md5(WEBROOT . $cookie);

    if (USE_CLIENT_COOKIES ? isset($_COOKIE[$cookie]) : isset($_SESSION[$cookie]))
    {
        debug_write_log(DEBUG_NOTICE, '[is_cookie_saved] Cookie is saved.');
        return TRUE;
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, '[is_cookie_saved] Cookie is not saved.');
        return FALSE;
    }
}

/**
 * Returns value of specified cookie.
 *
 * If cookie cannot be found, then specified default value is returned.
 *
 * @param string $cookie Cookie name.
 * @param mixed $value Default value.
 * @return mixed Cookie value when cookie exists, or default value otherwise.
 */
function try_cookie ($cookie, $value = NULL)
{
    debug_write_log(DEBUG_TRACE, '[try_cookie]');
    debug_write_log(DEBUG_DUMP,  '[try_cookie] $cookie = ' . $cookie);

    $cookie = md5(WEBROOT . $cookie);

    if (USE_CLIENT_COOKIES ? isset($_COOKIE[$cookie]) : isset($_SESSION[$cookie]))
    {
        debug_write_log(DEBUG_NOTICE, '[try_cookie] Cookie is found.');
        $value = (USE_CLIENT_COOKIES ? $_COOKIE[$cookie] : $_SESSION[$cookie]);
    }

    debug_write_log(DEBUG_DUMP,  '[try_cookie] $value = ' . $value);

    return $value;
}

?>
