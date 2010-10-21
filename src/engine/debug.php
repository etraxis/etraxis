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
 * Debugging
 *
 * This module implements debug logging to trace functions calls, values of parameters, performance, etc.
 * Module is configurable via {@link DEBUG_MODE} and {@link DEBUG_LOGS}.
 *
 * @package Engine
 * @subpackage Debugging
 */

//------------------------------------------------------------------------------
//  Definitions.
//------------------------------------------------------------------------------

/**#@+
 * Debug mode.
 */
define('DEBUG_MODE_OFF',   0);  // no debug logging
define('DEBUG_MODE_TRACE', 1);  // data-safe debug logging
define('DEBUG_MODE_FULL',  2);  // full debug logging
/**#@-*/

/**#@+
 * Type of debug message.
 */
define('DEBUG_LOG_OPENED',  1);  // log is opened
define('DEBUG_LOG_CLOSED',  2);  // log is closed
define('DEBUG_ERROR',       3);  // error
define('DEBUG_WARNING',     4);  // warning
define('DEBUG_NOTICE',      5);  // information notice
define('DEBUG_TRACE',       6);  // trace route
define('DEBUG_PERFORMANCE', 7);  // performance
define('DEBUG_DUMP',        8);  // user data dump
/**#@-*/

//------------------------------------------------------------------------------
//  Classes.
//------------------------------------------------------------------------------

/**
 * Debug logging, implemented via Singleton pattern.
 * @package Engine
 * @subpackage Debugging
 * @ignore
 */
class CDebugLog
{
    // Static object of itself.
    private static $object = NULL;

    // Handle of opened debug log.
    private $handle = FALSE;

    // Timestamp when page is started to execute.
    // Used to profile execution performance.
    private $timer = NULL;

    // If debugging is not disabled (see {@link DEBUG_MODE}),
    // creates debug log file (see {@link DEBUG_LOGS}) and opens it for appending.
    private function __construct ()
    {
        if (DEBUG_MODE != DEBUG_MODE_OFF)
        {
            $this->handle = fopen(DEBUG_LOGS . session_id() . '.log', 'a');

            if ($this->handle !== FALSE)
            {
                list($msec, $sec) = explode(' ', microtime());
                $this->timer = (float)$msec + (float)$sec;
            }
        }
    }

    // Closes opened debug log file.
    public function __destruct()
    {
        if ($this->handle !== FALSE)
        {
            list($msec, $sec) = explode(' ', microtime());

            $timer = (float)$msec + (float)$sec;
            $timer -= $this->timer;

            self::write(DEBUG_PERFORMANCE, 'PHP time = ' . $timer);
            self::write(DEBUG_LOG_CLOSED);

            fclose($this->handle);
            $this->handle = FALSE;
        }
    }

    // Writes specified message to opened debug log.
    public static function write ($type, $str = NULL)
    {
        if (is_null(self::$object))
        {
            self::$object = new CDebugLog();
            self::write(DEBUG_LOG_OPENED, isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : NULL);
        }

        if ($type == DEBUG_DUMP && DEBUG_MODE != DEBUG_MODE_FULL)
        {
            return TRUE;
        }

        $res = FALSE;

        if (self::$object->handle !== FALSE)
        {
            $prefix = array
            (
                DEBUG_LOG_OPENED  => '[OPENED]   ',
                DEBUG_LOG_CLOSED  => "[CLOSED]\n",
                DEBUG_ERROR       => '[ERROR]    ',
                DEBUG_WARNING     => '[WARNING]  ',
                DEBUG_NOTICE      => '[NOTICE]   ',
                DEBUG_TRACE       => '[TRACE]    ',
                DEBUG_PERFORMANCE => '[PERFORM]  ',
                DEBUG_DUMP        => '[DUMP]     ',
            );

            $today = date('Y-m-d  H:i:s  ');
            $res = (fwrite(self::$object->handle, "{$today}{$prefix[$type]}{$str}\n") != -1);

            if ($type == DEBUG_ERROR)
            {
                error_log("eTraxis Error: {$str}");
            }
        }

        return $res;
    }
}

//------------------------------------------------------------------------------
//  Functions.
//------------------------------------------------------------------------------

/**
 * Writes specified message to debug log.
 *
 * @param int $type Type of debug message.
 * @param string $str The message to be written.
 * @return bool TRUE on success, FALSE otherwise.
 */
function debug_write_log ($type, $str = NULL)
{
    return CDebugLog::write($type, $str);
}

?>
