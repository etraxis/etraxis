<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2009  Artem Rodygin
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
 * SMTP functions
 *
 * This module implements simple SMTP client.
 *
 * @package Engine
 */

/**#@+
 * Dependency.
 */
require_once('../engine/debug.php');
/**#@-*/

//------------------------------------------------------------------------------
//  Definitions.
//------------------------------------------------------------------------------

/**#@+
 * Supported SMTP clients.
 */
define('SMTP_CLIENT_PHP',       1);     // PHP MTA
define('SMTP_CLIENT_BUILDIN',   2);     // build-in client
/**#@-*/

//------------------------------------------------------------------------------
//  Functions.
//------------------------------------------------------------------------------

/**
 * Reads all response lines from opened SMTP session and returns SMTP response code.
 *
 * @param string $link Socket of active SMTP session.
 * @return int TRUE if code of SMTP server response means SUCCESS (2xx/3xx), FALSE otherwise.
 */
function smtp_read_response ($link)
{
    debug_write_log(DEBUG_TRACE, '[smtp_read_response]');

    stream_set_timeout($link, SMTP_SERVER_TIMEOUT);

    while (($response = fgets($link)) !== FALSE)
    {
        debug_write_log(DEBUG_DUMP, '[smtp_read_response] ' . trim($response));

        if (substr($response, 3, 1) === ' ')
        {
            $code = intval(substr($response, 0, 3));

            return $code >= 200 && $code < 400;
        }
    }

    return FALSE;
}

/**
 * Sends specified email via SMTP.
 *
 * @param string $to Email addresses of recipients (comma-separated).
 * @param string $subject Subject of the notification.
 * @param string $message Body of the notification.
 * @param string $headers Email headers.
 * @return bool TRUE if the mail was successfully accepted for delivery, FALSE otherwise.
 */
function smtp_send_mail ($to, $subject, $message, $headers)
{
    debug_write_log(DEBUG_TRACE, '[smtp_send_mail]');

    $link = fsockopen(SMTP_SERVER_NAME, SMTP_SERVER_PORT);

    if ($link === FALSE)
    {
        debug_write_log(DEBUG_WARNING, '[smtp_send_mail] Connection to SMTP server cannot be established.');
        return FALSE;
    }

    if (!smtp_read_response($link))
    {
        debug_write_log(DEBUG_WARNING, '[smtp_send_mail] SMTP server replied a failure.');
        fclose($link);
        return FALSE;
    }

    $requests = array('EHLO ' . SMTP_SERVER_NAME);

    if (SMTP_USE_TLS)
    {
        array_push($requests, 'STARTTLS');
    }

    if (strlen(SMTP_USERNAME) != 0)
    {
        array_push($requests, 'AUTH LOGIN');
        array_push($requests, base64_encode(SMTP_USERNAME));
        array_push($requests, base64_encode(SMTP_PASSWORD));
    }

    array_push($requests, 'MAIL FROM:<' . SMTP_MAILFROM . '>');

    $recipients = explode(', ', $to);

    foreach ($recipients as $recipient)
    {
        array_push($requests, 'RCPT TO:<' . $recipient . '>');
    }

    foreach ($requests as $request)
    {
        debug_write_log(DEBUG_DUMP, '[smtp_send_mail] ' . $request);
        fwrite($link, $request . "\n");

        if (!smtp_read_response($link))
        {
            debug_write_log(DEBUG_WARNING, '[smtp_send_mail] SMTP server replied a failure.');
            fclose($link);
            return FALSE;
        }

        if ($request == 'STARTTLS')
        {
            if (stream_socket_enable_crypto($link, TRUE, STREAM_CRYPTO_METHOD_TLS_CLIENT))
            {
                debug_write_log(DEBUG_NOTICE, '[smtp_send_mail] TLS encryption successfully initiated.');
            }
            else
            {
                debug_write_log(DEBUG_WARNING, '[smtp_send_mail] TLS encryption failed.');
                fclose($link);
                return FALSE;
            }
        }
    }

    debug_write_log(DEBUG_DUMP, '[smtp_send_mail] DATA');
    fwrite($link, "DATA\n");

    if (!smtp_read_response($link))
    {
        debug_write_log(DEBUG_WARNING, '[smtp_send_mail] SMTP server replied a failure.');
        fclose($link);
        return FALSE;
    }

    debug_write_log(DEBUG_DUMP, '[smtp_send_mail] ' . $headers);
    debug_write_log(DEBUG_DUMP, '[smtp_send_mail] Subject: ' . $subject);
    debug_write_log(DEBUG_DUMP, '[smtp_send_mail] ' . $message);
    debug_write_log(DEBUG_DUMP, '[smtp_send_mail] .');

    fwrite($link, "{$headers}\r\n");
    fwrite($link, "Subject: {$subject}\r\n\r\n");
    fwrite($link, "{$message}\r\n");
    fwrite($link, ".\r\n");

    if (!smtp_read_response($link))
    {
        debug_write_log(DEBUG_WARNING, '[smtp_send_mail] SMTP server replied a failure.');
        fclose($link);
        return FALSE;
    }

    debug_write_log(DEBUG_DUMP, '[smtp_send_mail] QUIT');
    fwrite($link, "QUIT\n");

    fclose($link);
    return TRUE;
}

?>
