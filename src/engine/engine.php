<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2004-2010  Artem Rodygin
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
 * Engine dummy file.
 *
 * This module contains only eTraxis error codes definition. Also the module includes all other
 * modules of eTraxis engine, so this is the only file you have to include in your sources.
 *
 * @package Engine
 */

/**
 * Engine configuration.
 */
require_once('../engine/config.php');

//------------------------------------------------------------------------------
//  Definitions.
//------------------------------------------------------------------------------

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
define('ERROR_UNKNOWN_AUTH_TYPE',          31);
/**#@-*/

/**#@+
 * Authentication type.
 */
define('AUTH_TYPE_BUILTIN', 1);
define('AUTH_TYPE_BASIC',   2);
define('AUTH_TYPE_DIGEST',  3);
define('AUTH_TYPE_NTLM',    4);
/**#@-*/

//------------------------------------------------------------------------------
//  Engine modules.
//------------------------------------------------------------------------------

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
require_once('../engine/bbcode.php');
require_once('../engine/xml.php');
/**#@-*/

?>
