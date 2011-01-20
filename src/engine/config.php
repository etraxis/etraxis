<?php

#-------------------------------------------------------------------------------
#
#  eTraxis - Records tracking web-based system
#  Copyright (C) 2003-2010  Artem Rodygin
#
#  This program is free software: you can redistribute it and/or modify
#  it under the terms of the GNU General Public License as published by
#  the Free Software Foundation, either version 3 of the License, or
#  (at your option) any later version.
#
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details.
#
#  You should have received a copy of the GNU General Public License
#  along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
#-------------------------------------------------------------------------------

/**
 * Instance configuration
 *
 * This module contains settings of eTraxis instance, which are required to be updated for
 * particular installation.
 *
 * @package Engine
 */

#-------------------------------------------------------------------------------
#  Location
#-------------------------------------------------------------------------------

/**#@+
 * See {@link http://code.google.com/p/etraxis/wiki/Installation#Step_4._Configure_eTraxis online documentation} for details.
 */
define('LOCALROOT', '/usr/local/apache/htdocs/etraxis/');
define('WEBROOT',   'http://www.example.com/etraxis/');
/**#@-*/

#-------------------------------------------------------------------------------
#  Database
#-------------------------------------------------------------------------------

/**#@+
 * See {@link http://code.google.com/p/etraxis/wiki/Installation#Step_4._Configure_eTraxis online documentation} for details.
 */
define('DATABASE_DRIVER',   1);
define('DATABASE_HOST',     'localhost');
define('DATABASE_DBNAME',   'etraxis');
define('DATABASE_USERNAME', '%');
define('DATABASE_PASSWORD', '');
/**#@-*/

#-------------------------------------------------------------------------------
#  Security Options
#-------------------------------------------------------------------------------

/**#@+
 * See {@link http://code.google.com/p/etraxis/wiki/SecurityOptions online documentation} for details.
 */
define('MIN_PASSWORD_LENGTH', 6);
define('LOCKS_COUNT',         3);
define('LOCKS_TIMEOUT',       30);
define('PASSWORD_EXPIRATION', 90);
define('SESSION_EXPIRE',      120);
/**#@-*/

#-------------------------------------------------------------------------------
#  Localization
#-------------------------------------------------------------------------------

/**#@+
 * See {@link http://code.google.com/p/etraxis/wiki/Localization online documentation} for details.
 */
define('LANG_DEFAULT', 1000);
/**#@-*/

#-------------------------------------------------------------------------------
#  Customization
#-------------------------------------------------------------------------------

/**#@+
 * See {@link http://code.google.com/p/etraxis/wiki/Customization online documentation} for details.
 */
define('COMPANY_LOGO',  'images/logo.png');
define('COMPANY_SITE',  'http://code.google.com/p/etraxis/');
define('THEME_DEFAULT', 'Emerald');
/**#@-*/

#-------------------------------------------------------------------------------
#  Attachments
#-------------------------------------------------------------------------------

/**#@+
 * See {@link http://code.google.com/p/etraxis/wiki/Attachments online documentation} for details.
 */
define('ATTACHMENTS_ENABLED',    1);
define('ATTACHMENTS_MAXSIZE',    2048);
define('ATTACHMENTS_COMPRESSED', 1);
define('ATTACHMENTS_PATH',       '/usr/local/etraxis/bins/');
/**#@-*/

#-------------------------------------------------------------------------------
#  Email Notifications
#-------------------------------------------------------------------------------

/**#@+
 * See {@link http://code.google.com/p/etraxis/wiki/EmailNotifications online documentation} for details.
 */
define('EMAIL_NOTIFICATIONS_ENABLED', 1);
define('EMAIL_ATTACHMENTS_MAXSIZE',   0);
/**#@-*/

#-------------------------------------------------------------------------------
#  SMTP Settings
#-------------------------------------------------------------------------------

/**#@+
 * See {@link http://code.google.com/p/etraxis/wiki/SMTPSettings online documentation} for details.
 */
define('SMTP_SERVER_NAME',    'smtp.example.com');
define('SMTP_SERVER_PORT',    25);
define('SMTP_SERVER_TIMEOUT', 5);
define('SMTP_USERNAME',       '');
define('SMTP_PASSWORD',       '');
define('SMTP_MAILFROM',       'mailfrom@example.com');
define('SMTP_USE_TLS',        0);
/**#@-*/

#-------------------------------------------------------------------------------
#  LDAP Authentication
#-------------------------------------------------------------------------------

/**#@+
 * See {@link http://code.google.com/p/etraxis/wiki/LDAPAuthentication online documentation} for details.
 */
define('LDAP_ENABLED',       0);
define('LDAP_HOST',          'ldap://localhost');
define('LDAP_PORT',          389);
define('LDAP_USE_TLS',       0);
define('LDAP_BASEDN',        'OU=unit1,DC=example,DC=com; OU=unit2,DC=example,DC=com');
define('LDAP_USERNAME',      '');
define('LDAP_PASSWORD',      '');
define('LDAP_ATTR_LOGIN',    'samaccountname');
define('LDAP_ATTR_FULLNAME', 'displayname');
define('LDAP_ATTR_EMAIL',    'mail');
define('LDAP_ENUMERATION',   0);
define('LDAP_ADMINS',        'Administrator');
/**#@-*/

#-------------------------------------------------------------------------------
#  Maintenance
#-------------------------------------------------------------------------------

/**#@+
 * See {@link http://code.google.com/p/etraxis/wiki/Maintenance online documentation} for details.
 */
define('MAINTENANCE_BANNER',      0);
define('MAINTENANCE_START_DATE',  '1980-01-01');
define('MAINTENANCE_START_TIME',  '00:00');
define('MAINTENANCE_FINISH_DATE', '2037-12-31');
define('MAINTENANCE_FINISH_TIME', '00:00');
/**#@-*/

#-------------------------------------------------------------------------------
#  Debug Logging
#-------------------------------------------------------------------------------

/**#@+
 * See {@link http://code.google.com/p/etraxis/wiki/DebugLogging online documentation} for details.
 */
define('DEBUG_MODE', 0);
define('DEBUG_LOGS', '/usr/local/etraxis/logs/');
/**#@-*/

?>
