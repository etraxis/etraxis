<?php

/**
 * @package eTraxis
 * @ignore
 */

//--------------------------------------------------------------------------------------------------
//  Copyright (C) 2005-2009 by Artem Rodygin
//--------------------------------------------------------------------------------------------------
//  Author                  Date            Description of modifications
//--------------------------------------------------------------------------------------------------
//  Artem Rodygin           2005-01-08      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2006-12-27      new-472: User must have ability to log out.
//  Daniel Jungbluth        2007-09-04      bug-575: Login and Logout
//  Artem Rodygin           2007-11-30      bug-632: HTTP Authentication problem running as CGI
//  Artem Rodygin           2007-12-27      new-659: Set default language
//  Artem Rodygin           2007-12-30      bug-660: [SF1860788] PHP4 html_entity_decode() is not working
//  Artem Rodygin           2008-03-27      bug-688: Short PHP tags should not be used.
//  Artem Rodygin           2008-10-29      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-02-28      bug-794: [SF2643676] Security problem when logout.
//  Artem Rodygin           2009-06-01      new-824: PHP 4 is discontinued.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
/**#@-*/

session_start();

clear_cookie(COOKIE_AUTH_USERID);
clear_cookie(COOKIE_AUTH_TOKEN);

close_session();

header('WWW-Authenticate: Basic realm="' . get_http_auth_realm() . '"');
header('HTTP/1.0 401 Unauthorized');
header('Location: ../records/index.php');

?>
