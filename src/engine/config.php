<?php

/**
 * Instance configuration
 *
 * This module contains settings of eTraxis instance, which are required to be updated for
 * particular installation.
 *
 * @package Engine
 */

# eTraxis - Records tracking web-based system.
# Copyright (C) 2003-2009 by Artem Rodygin

# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.

# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

# You should have received a copy of the GNU General Public License along
# with this program; if not, write to the Free Software Foundation, Inc.,
# 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

#---------------------------------------------------------------------------------------------------
# Location
#---------------------------------------------------------------------------------------------------

/**#@+
 * See {@link http://www.etraxis.org/docs-config-etraxis.php online documentation} for details.
 */
define('LOCALROOT', '/usr/local/apache/htdocs/etraxis/');
define('WEBROOT',   'http://www.example.com/etraxis/');
/**#@-*/

#---------------------------------------------------------------------------------------------------
# Database
#---------------------------------------------------------------------------------------------------

/**#@+
 * See {@link http://www.etraxis.org/docs-config-etraxis.php online documentation} for details.
 */
define('DATABASE_DRIVER',   1);
define('DATABASE_HOST',     'localhost');
define('DATABASE_DBNAME',   'etraxis');
define('DATABASE_USERNAME', '%');
define('DATABASE_PASSWORD', '');
/**#@-*/

#---------------------------------------------------------------------------------------------------
# Security Options
#---------------------------------------------------------------------------------------------------

/**#@+
 * See {@link http://www.etraxis.org/docs-security.php online documentation} for details.
 */
define('AUTH_TYPE',           2);
define('MIN_PASSWORD_LENGTH', 6);
define('LOCKS_COUNT',         3);
define('LOCKS_TIMEOUT',       30);
define('PASSWORD_EXPIRATION', 90);
define('SESSION_EXPIRE',      1440);
/**#@-*/

#---------------------------------------------------------------------------------------------------
# Localization
#---------------------------------------------------------------------------------------------------

/**#@+
 * See {@link http://www.etraxis.org/docs-localization.php online documentation} for details.
 */
define('LANG_DEFAULT', 1000);
/**#@-*/

#---------------------------------------------------------------------------------------------------
# Attachments
#---------------------------------------------------------------------------------------------------

/**#@+
 * See {@link http://www.etraxis.org/docs-attachments.php online documentation} for details.
 */
define('ATTACHMENTS_ENABLED', 1);
define('ATTACHMENTS_MAXSIZE', 2048);
define('ATTACHMENTS_PATH',    '/usr/local/etraxis/bins/');
/**#@-*/

#---------------------------------------------------------------------------------------------------
# Email Notifications
#---------------------------------------------------------------------------------------------------

/**#@+
 * See {@link http://www.etraxis.org/docs-emails.php online documentation} for details.
 */
define('EMAIL_NOTIFICATIONS_ENABLED', 1);
define('EMAIL_ATTACHMENTS_MAXSIZE',   0);
/**#@-*/

#---------------------------------------------------------------------------------------------------
# SMTP Settings
#---------------------------------------------------------------------------------------------------

/**#@+
 * See {@link http://www.etraxis.org/docs-smtp.php online documentation} for details.
 */
define('SMTP_SERVER_NAME',    'smtp.example.com');
define('SMTP_SERVER_PORT',    25);
define('SMTP_SERVER_TIMEOUT', 5);
define('SMTP_USERNAME',       '');
define('SMTP_PASSWORD',       '');
define('SMTP_MAILFROM',       'mailfrom@example.com');
define('SMTP_USE_TLS',        0);
/**#@-*/

#---------------------------------------------------------------------------------------------------
# LDAP Authentication
#---------------------------------------------------------------------------------------------------

/**#@+
 * See {@link http://www.etraxis.org/docs-ldap-auth.php online documentation} for details.
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

#---------------------------------------------------------------------------------------------------
# Maintenance
#---------------------------------------------------------------------------------------------------

/**#@+
 * See {@link http://www.etraxis.org/docs-maintenance.php online documentation} for details.
 */
define('MAINTENANCE_BANNER',      0);
define('MAINTENANCE_START_DATE',  '1980-01-01');
define('MAINTENANCE_START_TIME',  '00:00');
define('MAINTENANCE_FINISH_DATE', '2037-12-31');
define('MAINTENANCE_FINISH_TIME', '00:00');
/**#@-*/

#---------------------------------------------------------------------------------------------------
# Debug Logging
#---------------------------------------------------------------------------------------------------

/**#@+
 * See {@link http://www.etraxis.org/docs-debug.php online documentation} for details.
 */
define('DEBUG_MODE', 0);
define('DEBUG_LOGS', '/usr/local/etraxis/logs/');
/**#@-*/

?>
