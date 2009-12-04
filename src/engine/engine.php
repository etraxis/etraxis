<?php

/**
 * Engine dummy file.
 *
 * This module contains only eTraxis error codes definition. Also the module includes all other
 * modules of eTraxis engine, so this is the only file you have to include in your sources.
 *
 * @package Engine
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
//  Artem Rodygin           2005-08-10      new-008: Predefined metrics.
//  Artem Rodygin           2005-08-15      new-003: Authentication with Active Directory.
//  Artem Rodygin           2005-09-07      new-100: 'Date' field type should be implemented.
//  Artem Rodygin           2005-09-08      new-101: 'Duration' field type should be implemented.
//  Artem Rodygin           2006-01-24      new-204: Active Directory Support functionality (new-003) should be conditionally "compiled".
//  Artem Rodygin           2006-07-24      bug-201: 'Access Forbidden' error with cyrillic named attachments.
//  Artem Rodygin           2006-08-21      new-313: Implement HTTP authentication.
//  Artem Rodygin           2006-11-04      new-364: Default fields values.
//  Artem Rodygin           2006-11-15      bug-381: Attachments of some types are not opened in valid applications.
//  Artem Rodygin           2006-12-14      new-446: Add processing of new upload errors.
//  Yury Udovichenko        2007-11-14      new-548: Custom links in text fields.
//  Artem Rodygin           2007-11-30      bug-632: HTTP Authentication problem running as CGI
//  Artem Rodygin           2008-02-06      new-601: [SF1814666] Export and Import Templates
//  Artem Rodygin           2009-03-11      bug-799: eTraxis doesn't work with XAMPP on Windows.
//  Johannes Gelbaerchen    2009-03-23      bug-804: 'stripos' is not available in PHP4
//  Artem Rodygin           2009-06-01      new-824: PHP 4 is discontinued.
//  Artem Rodygin           2009-06-21      new-828: [SF2809460] Support for SMTP email
//--------------------------------------------------------------------------------------------------

/**
 * Engine configuration.
 */
require_once('../engine/config.php');

//--------------------------------------------------------------------------------------------------
//  Definitions.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Error code.
 */
define('NO_ERROR',                         0);
define('ERROR_UNKNOWN',                    1);
define('ERROR_NOT_FOUND',                  2);
define('ERROR_INCOMPLETE_FORM',            3);
define('ERROR_UNKNOWN_USERNAME',           4);
define('ERROR_ACCOUNT_DISABLED',           5);
define('ERROR_ACCOUNT_LOCKED',             6);
define('ERROR_INVALID_USERNAME',           7);
define('ERROR_ALREADY_EXISTS',             8);
define('ERROR_INVALID_EMAIL',              9);
define('ERROR_PASSWORDS_DO_NOT_MATCH',     10);
define('ERROR_PASSWORD_TOO_SHORT',         11);
define('ERROR_INVALID_INTEGER_VALUE',      12);
define('ERROR_INTEGER_VALUE_OUT_OF_RANGE', 13);
define('ERROR_MIN_MAX_VALUES',             14);
define('ERROR_UPLOAD_INI_SIZE',            15);
define('ERROR_UPLOAD_FORM_SIZE',           16);
define('ERROR_UPLOAD_PARTIAL',             17);
define('ERROR_UPLOAD_NO_FILE',             18);
define('ERROR_UPLOAD_NO_TMP_DIR',          19);
define('ERROR_RECORD_NOT_FOUND',           20);
define('ERROR_INVALID_DATE_VALUE',         21);
define('ERROR_DATE_VALUE_OUT_OF_RANGE',    22);
define('ERROR_INVALID_TIME_VALUE',         23);
define('ERROR_TIME_VALUE_OUT_OF_RANGE',    24);
define('ERROR_UNAUTHORIZED',               25);
define('ERROR_DEFAULT_VALUE_OUT_OF_RANGE', 26);
define('ERROR_UPLOAD_CANT_WRITE',          27);
define('ERROR_UPLOAD_EXTENSION',           28);
define('ERROR_VALUE_FAILS_REGEX_CHECK',    29);
define('ERROR_XML_PARSER',                 30);
/**#@-*/

/**#@+
 * Authentication type.
 */
define('AUTH_TYPE_BUILTIN', 1);
define('AUTH_TYPE_BASIC',   2);
define('AUTH_TYPE_DIGEST',  3);
define('AUTH_TYPE_NTLM',    4);
/**#@-*/

//--------------------------------------------------------------------------------------------------
//  Engine modules.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Engine module.
 */
require_once('../engine/debug.php');
require_once('../engine/smtp.php');
require_once('../engine/utility.php');
require_once('../engine/locale.php');
require_once('../engine/cookies.php');
require_once('../engine/dal.php');
require_once('../engine/ldap.php');
require_once('../engine/sessions.php');
require_once('../engine/charts.php');
require_once('../engine/xml.php');
/**#@-*/

?>
