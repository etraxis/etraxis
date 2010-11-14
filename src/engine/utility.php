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
 * Utility functions
 *
 * This module contains several wide-purpose utility functions.
 *
 * @package Engine
 */

/**#@+
 * Dependency.
 */
require_once('../engine/debug.php');
require_once('../engine/smtp.php');
/**#@-*/

//------------------------------------------------------------------------------
//  Definitions.
//------------------------------------------------------------------------------

/**
 * Maximum integer value.
 */
define('MAXINT', 0x7FFFFFFF);

/**#@+
 * Number of seconds.
 */
define('SECS_IN_DAY',  86400);   // 60 * 60 * 24
define('SECS_IN_WEEK', 604800);  // 60 * 60 * 24 * 7
/**#@-*/

/**
 * Allowed CSV-delimiters.
 */
define('CSV_DELIMITERS', '!#$%&\'()*+,-./:;<=>?@[\]^_`{|}~');

//------------------------------------------------------------------------------
//  Functions.
//------------------------------------------------------------------------------

/**
 * Unicode (UTF-8) analogue of standard {@link http://www.php.net/strlen strlen} PHP function.
 *
 * Returns the length of the given string.
 *
 * @param string $str The UTF-8 encoded string being measured for length.
 * @return int The length (amount of UTF-8 characters) of the string on success, and 0 if the string is empty.
 */
function ustrlen ($str)
{
    return mb_strlen($str, 'UTF-8');
}

/**
 * Unicode (UTF-8) analogue of standard {@link http://www.php.net/strpos strpos} PHP function.
 *
 * Find position of first occurrence of a case-sensitive UTF-8 encoded string.
 * Returns the numeric position (offset in amount of UTF-8 characters) of the first occurrence of <i>needle</i> in the <i>haystack</i> string.
 *
 * @param string $haystack The UTF-8 encoded string being searched in.
 * @param string $needle The UTF-8 encoded string being searched for.
 * @param int $offset The optional <i>offset</i> parameter allows you to specify which character in <i>haystack</i> to start searching.
 * The position returned is still relative to the beginning of <i>haystack</i>.
 * @return int Returns the position as an integer. If <i>needle</i> is not found, the function will return boolean FALSE.
 */
function ustrpos ($haystack, $needle, $offset = 0)
{
    return mb_strpos($haystack, $needle, $offset, 'UTF-8');
}

/**
 * Unicode (UTF-8) analogue of standard {@link http://www.php.net/stripos stripos} PHP function.
 *
 * Find position of first occurrence of a case-insensitive UTF-8 encoded string.
 * Returns the numeric position (offset in amount of UTF-8 characters) of the first occurrence of <i>needle</i> in the <i>haystack</i> string.
 *
 * @param string $haystack The UTF-8 encoded string being searched in.
 * @param string $needle The UTF-8 encoded string being searched for.
 * @param int $offset The optional <i>offset</i> parameter allows you to specify which character in <i>haystack</i> to start searching.
 * The position returned is still relative to the beginning of <i>haystack</i>.
 * @return int Returns the position as an integer. If <i>needle</i> is not found, the function will return boolean FALSE.
 */
function ustripos ($haystack, $needle, $offset = 0)
{
    $haystack = mb_strtolower($haystack, 'UTF-8');
    $needle   = mb_strtolower($needle,   'UTF-8');

    return mb_strpos($haystack, $needle, $offset, 'UTF-8');
}

/**
 * Unicode (UTF-8) analogue of standard {@link http://www.php.net/strrpos strrpos} PHP function.
 *
 * Find position of last occurrence of a case-sensitive UTF-8 encoded string.
 * Returns the numeric position (offset in amount of UTF-8 characters) of the last occurrence of <i>needle</i> in the <i>haystack</i> string.
 *
 * @param string $haystack The UTF-8 encoded string being searched in.
 * @param string $needle The UTF-8 encoded string being searched for.
 * @return int Returns the position as an integer. If <i>needle</i> is not found, the function will return boolean FALSE.
 */
function ustrrpos ($haystack, $needle)
{
    return mb_strrpos($haystack, $needle, 'UTF-8');
}

/**
 * Unicode (UTF-8) analogue of standard {@link http://www.php.net/strtolower strtolower} PHP function.
 *
 * Make a string lowercase.
 *
 * @param string $str The UTF-8 encoded string to be lowercased.
 * @return string Specified string with all alphabetic characters converted to lowercase.
 */
function ustrtolower ($str)
{
    return mb_strtolower($str, 'UTF-8');
}

/**
 * Unicode (UTF-8) analogue of standard {@link http://www.php.net/substr substr} PHP function.
 *
 * Returns the portion of string specified by the <i>start</i> and <i>length</i> parameters.
 *
 * @param string $str The UTF-8 encoded string.
 * @param int $start Start of portion to be returned. Position is counted in amount of UTF-8 characters from the beginning of <i>str</i>.
 * First character's position is 0. Second character position is 1, and so on.
 * @param int $length If <i>length</i> is given, the string returned will contain at most <i>length</i> characters beginning from <i>start</i> (depending on the length of string).
 * If <i>length</i> is omitted, the rest of string from <i>start</i> will be returned.
 * @return string The extracted UTF-8 encoded part of input string.
 */
function usubstr ($str, $start, $length = NULL)
{
    return mb_substr($str, $start, (is_null($length) ? mb_strlen($str, 'UTF-8') : $length), 'UTF-8');
}

/**
 * Unicode (UTF-8) analogue of standard {@link http://www.php.net/str-replace str_replace} PHP function.
 *
 * Replace all occurrences of the search string with the replacement string.
 *
 * @param string $search The UTF-8 encoded string being searched for.
 * @param string $replace The UTF-8 encoded string being replaced with.
 * @param string $subject The UTF-8 encoded string being searched in.
 * @return string The UTF-8 encoded string with the replaced values.
 */
function ustr_replace ($search, $replace, $subject)
{
    $from = 0;
    $len  = ustrlen($search);

    while (TRUE)
    {
        $from = ustripos($subject, $search, $from);

        if ($from === FALSE)
        {
            break;
        }

        $subject = usubstr($subject, 0, $from) . $replace . usubstr($subject, $from + $len);
        $from += ustrlen($replace);
    }

    return $subject;
}

/**
 * Trims UTF-8 encoded string and then cuts it to specified length.
 *
 * @param string $str The UTF-8 encoded string being cut.
 * @param int $maxlen New length of the string (amount of UTF-8 characters).
 * @param bool $trim Whether to trim string before cutting it.
 * @return string Cut string.
 */
function ustrcut ($str, $maxlen, $trim = TRUE)
{
    if ($trim)
    {
        $str = trim($str);
    }

    return mb_strcut($str, 0, $maxlen, 'UTF-8');
}

/**
 * The function accepts variable number of arguments and replaces each "%i" (where <i>i</i> is
 * a natural number) substring of input string with related argument.
 *
 * Passed arguments can be any type of; in case of string they should be UTF-8 encoded.
 *
 * @param string $str The UTF-8 encoded string being processed.
 * @param mixed Value, which each "%1" substring will be replaced with.
 * @param mixed Value, which each "%2" substring will be replaced with.
 * @param mixed ... (and so on)
 * @return string Processed string.
 *
 * <br/>Example:<br/>
 * <code>
 * ustrprocess("Name: %1\nSex: %3\nAge: %2", "Artem", 30, "male");
 * </code>
 * <br/>will output<br/>
 * <pre>
 * Name: Artem
 * Sex: male
 * Age: 30
 * </pre>
 */
function ustrprocess ($str)
{
    for ($i = func_num_args(); $i > 1; $i--)
    {
        $search  = '%' . ($i - 1);
        $replace = func_get_arg($i - 1);
        $str     = ustr_replace($search, $replace, $str);
    }

    return $str;
}

/**
 * Converts UTF-8 encoded string to integer (natural) value.
 *
 * If resulted integer value is less then specified <i>min</i> value, the <i>min</i> value will be returned.
 * If resulted integer value is greater then specified <i>max</i> value, the <i>max</i> value will be returned.
 *
 * @param string $str The UTF-8 encoded string being converted.
 * @param int $min Minimum allowed result value.
 * @param int $max Maximum allowed result value.
 * @return int Natural value of range from <i>min</i> to <i>max</i>.
 */
function ustr2int ($str, $min = 0, $max = MAXINT)
{
    $res = (ustrlen($str) == 0 ? $min : intval($str));

    if ($res < $min)
    {
        $res = $min;
    }
    elseif ($res > $max)
    {
        $res = $max;
    }

    return $res;
}

/**
 * Converts specified amount of minutes to its string representation in format "hh:mm".
 *
 * @param int $time Amount of minutes.
 * @return string String representation (e.g. for 127 it will be "2:07").
 */
function time2ustr ($time)
{
    return intval(floor($time / 60)) . ':' . str_pad($time % 60, 2, '0', STR_PAD_LEFT);
}

/**
 * Strips HTML-tags from UTF-8 encoded string.
 *
 * @param string $str The UTF-8 encoded string.
 * @return string HTML-safe UTF-8 encoded string.
 */
function ustr2html ($str)
{
    if (is_null($str)) return NULL;

    $str = mb_ereg_replace("([\x00-\x08]|[\x0B-\x0C]|[\x0E-\x1F])", NULL, $str);
    return @htmlspecialchars($str, ENT_COMPAT, 'UTF-8');
}

/**
 * Strips quotes from UTF-8 encoded string.
 *
 * @param string $str The UTF-8 encoded string.
 * @return string JavaScript-safe UTF-8 encoded string.
 */
function ustr2js ($str)
{
    return ustr_replace('"', '&quot;', $str);
}

/**
 * Strips apostrophes from UTF-8 encoded string.
 *
 * @param string $str The UTF-8 encoded string.
 * @return string SQL-safe UTF-8 encoded string.
 */
function ustr2sql ($str)
{
    if ((DATABASE_DRIVER == 1) ||
        (DATABASE_DRIVER == 4))
    {
        $str = ustr_replace('\\', '\\\\', $str);
    }

    $str = ustr_replace('\'', '\'\'', $str);

    return $str;
}

/**
 * Convert UTF-8 encoded string to CSV format.
 *
 * @param string $str The UTF-8 encoded string.
 * @param string $delimiter CSV delimiter.
 * @param string $enclosure CSV enclosure.
 * @return string CSV string (UTF-8 encoded).
 */
function ustr2csv ($str, $delimiter = ',', $enclosure = '"')
{
    $str = ustr_replace($enclosure, $enclosure . $enclosure, $str);

    if (ustrpos($str, $enclosure) !== FALSE ||
        ustrpos($str, $delimiter) !== FALSE ||
        ustrpos($str, "\n")       !== FALSE)
    {
        $str = $enclosure . $str . $enclosure;
    }

    return $str;
}

/**
 * Parse UTF-8 encoded CSV string into an array.
 *
 * @param string $str The UTF-8 encoded CSV string.
 * @param string $delimiter CSV delimiter.
 * @param string $enclosure CSV enclosure.
 * @return array Indexed array containing the fields read (UTF-8 encoded), or NULL on error.
 */
function ustr_getcsv ($str, $delimiter = ',', $enclosure = '"')
{
    $csv = mb_split($delimiter, $str);

    for ($i = 0; $i < count($csv); $i++)
    {
        if (usubstr($csv[$i], 0, ustrlen($enclosure)) == $enclosure)
        {
            while ($i < count($csv) &&
                   usubstr($csv[$i], ustrlen($csv[$i]) - ustrlen($enclosure)) != $enclosure)
            {
                $csv[$i] .= $delimiter . $csv[$i + 1];
                array_splice($csv, $i + 1, 1);
            }

            $csv[$i] = usubstr($csv[$i], ustrlen($enclosure), ustrlen($csv[$i]) - ustrlen($enclosure) * 2);
        }
    }

    return $csv;
}

/**
 * Converts boolean value to integer for use in SQL queries.
 *
 * @param bool $value Boolean value.
 * @return int '1' on TRUE, or '0' on FALSE.
 */
function bool2sql ($value)
{
    return ($value ? 1 : 0);
}

/**
 * Returns value of user HTML-form request, if it exists; otherwise returns specified default value.
 *
 * @param string $request Name of user HTML-form request.
 * @param mixed $value Default value.
 * @return mixed User HTML-form request, or default value if specified request cannot be found.
 */
function try_request ($request, $value = NULL)
{
    global $_REQUEST;
    return (isset($_REQUEST[$request]) ? $_REQUEST[$request] : $value);
}

/**
 * Exchanges values of two variables.
 *
 * @param mixed &$value1 First variable.
 * @param mixed &$value2 Second variable.
 */
function swap (&$value1, &$value2)
{
    $temp   = $value1;
    $value1 = $value2;
    $value2 = $temp;
}

/**
 * Finds whether the given UTF-8 encoded string contains valid integer value.
 *
 * @param string $str The UTF-8 encoded string being evaluated.
 * @return bool TRUE if <i>str</i> contains valid integer value, FALSE otherwise.
 */
function is_intvalue ($str)
{
    mb_regex_encoding('UTF-8');
    return mb_eregi('^(\+|\-)*([0-9])+$', $str);
}

/**
 * Finds whether the given UTF-8 encoded string contains valid login
 * (only latin characters, digits, and underline are allowed).
 *
 * @param string $str The UTF-8 encoded string being evaluated.
 * @return bool TRUE if <i>str</i> contains valid login, FALSE otherwise.
 */
function is_username ($str)
{
    mb_regex_encoding('UTF-8');
    return mb_eregi('^([_0-9a-z\.\-])+$', $str);
}

/**
 * Finds whether the given UTF-8 encoded string contains valid email address.
 *
 * @param string $str The UTF-8 encoded string being evaluated.
 * @return bool TRUE if <i>str</i> contains valid email address, FALSE otherwise.
 */
function is_email ($str)
{
    mb_regex_encoding('UTF-8');

    $atom   = '[-a-z0-9!#$%&\'*+/=?^_`{|}~]';      // allowed characters for part before "at" character
    $domain = '([a-z0-9]([-a-z0-9]*[a-z0-9]+)?)';  // allowed characters for part after "at" character

    return mb_eregi("^{$atom}+(\\.{$atom}+)*@({$domain}{1,63}\\.)+{$domain}{2,63}$", $str);
}

/**
 * Sends email notification.
 *
 * @param string $sender Name of sender.
 * @param string $from Email address of sender.
 * @param string $to Email addresses of recipients (comma-separated).
 * @param string $subject Subject of the notification.
 * @param string $message Body of the notification.
 * @param int $attachment_id ID of attachment if it should be included in email, NULL otherwise.
 * @param string $attachment_name Name of attachment if it should be included in email, NULL otherwise.
 * @param string $attachment_type MIME type of attachment if it should be included in email, NULL otherwise.
 * @param int $attachment_size Size of attachment if it should be included in email, NULL otherwise.
 * @return bool TRUE if the mail was successfully accepted for delivery, FALSE otherwise.
 */
function sendmail ($sender, $from, $to, $subject, $message, $attachment_id = NULL, $attachment_name = NULL, $attachment_type = NULL, $attachment_size = NULL)
{
    debug_write_log(DEBUG_TRACE, '[sendmail]');

    if (strtolower(substr(PHP_OS, 0, 3)) == 'win')
    {
        $eol = "\r\n";

        // When PHP is talking to a SMTP server directly, if a full stop is found on the start of
        // a line, it is removed. To counter-act this, replace these occurrences with a double dot.
        $message = ustr_replace("\n.", "\n..", $message);
    }
    elseif (strtolower(substr(PHP_OS, 0, 3)) == 'mac')
    {
        $eol = "\r";
    }
    else
    {
        $eol = "\n";
    }

    $sender        = '=?utf-8?b?' . base64_encode($sender) . '?=';
    $is_attachment = ($attachment_size <= EMAIL_ATTACHMENTS_MAXSIZE * 1024 && !is_null($attachment_id));
    $boundary      = 'eTraxis-boundary:' . md5(uniqid(time()));

    $headers = implode($eol, array('Date: ' . date('r'),
                                   'From: ' . $sender . ' <' . (EMAIL_NOTIFICATIONS_ENABLED == SMTP_CLIENT_BUILDIN ? SMTP_MAILFROM : $from) . '>',
                                   'Reply-To: ' . $from,
                                   'Return-Path: ' . $from,
                                   'Message-ID: <' . md5(uniqid(time())) . '@' . $_SERVER['SERVER_NAME'] . '>',
                                   'X-Priority: 3',
                                   'X-Mailer: eTraxis ' . VERSION,
                                   'MIME-Version: 1.0',
                                   ($is_attachment ? 'Content-Type: multipart/mixed; boundary="' . $boundary . '"'
                                                   : 'Content-Type: text/html; charset="utf-8"')
                                   ));

    if ($is_attachment)
    {
        $message = implode($eol, array('This is a multi-part message in MIME format.',
                                       NULL,
                                       '--' . $boundary,
                                       'Content-Type: text/html; charset="utf-8"',
                                       NULL,
                                       $message,
                                       NULL,
                                       '--' . $boundary,
                                       'Content-Type: ' . $attachment_type . '; name="' . $attachment_name . '"',
                                       'Content-Transfer-Encoding: base64',
                                       'Content-Disposition: attachment; filename="=?utf-8?b?' . base64_encode($attachment_name) . '?="',
                                       NULL,
                                       chunk_split(base64_encode(gzfile_get_contents(ATTACHMENTS_PATH . $attachment_id, $attachment_size))),
                                       NULL,
                                       '--' . $boundary . '--'));
    }

    debug_write_log(DEBUG_DUMP, '[sendmail] $to = ' . $to);
    debug_write_log(DEBUG_DUMP, '[sendmail] $subject = ' . $subject);
    debug_write_log(DEBUG_DUMP, "[sendmail] \$headers =\n{$headers}");
    debug_write_log(DEBUG_DUMP, "[sendmail] \$message =\n{$message}");

    $subject = '=?utf-8?b?' . base64_encode($subject) . '?=';

    switch (EMAIL_NOTIFICATIONS_ENABLED)
    {
        case SMTP_CLIENT_PHP:
            return @mail($to, $subject, $message, $headers);
        case SMTP_CLIENT_BUILDIN:
            return smtp_send_mail($to, $subject, $message, $headers);
        default:
            debug_write_log(DEBUG_WARNING, '[sendmail] Email notifications are disabled.');
    }

    return FALSE;
}

/**
 * Round specified timestamp down to midnight.
 *
 * @param int $timestamp Unix timestamp (see {@link http://en.wikipedia.org/wiki/Unix_time})
 * @return int Unix timestamp (see {@link http://en.wikipedia.org/wiki/Unix_time}) for midnight of the same date.
 */
function date_floor ($timestamp)
{
    $date = getdate($timestamp);
    return mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']);
}

/**
 * Returns timestamp, shifted from specified for particular number of days.
 * Negative offset shifts to past, positive - to future.
 *
 * @param int $timestamp Unix timestamp (see {@link http://en.wikipedia.org/wiki/Unix_time}) to be shifted from
 * @param int $offset Number of days to be shifted for
 * @return int Unix timestamp (see {@link http://en.wikipedia.org/wiki/Unix_time}) for resulting shifted date.
 */
function date_offset ($timestamp, $offset)
{
    $now      = date_floor($timestamp);
    $min_date = date_floor(0);
    $max_date = date_floor(MAXINT);

    // Determine maximum allowed shifts to both past and future to avoid byte overflow error
    // (any timestamp must be in range from 0x00000000 to 0x7FFFFFFF)
    $max_backward = round(($min_date - $now) / SECS_IN_DAY);
    $max_forward  = round(($max_date - $now) / SECS_IN_DAY);

    // If specified offset looks to far in the past, adjust it to the maximum allowed offset.
    if ($offset < $max_backward)
    {
        $offset = $max_backward;
    }

    // If specified offset looks to far in the future, adjust it to the maximum allowed offset.
    if ($offset > $max_forward)
    {
        $offset = $max_forward;
    }

    return strtotime("{$offset} days", $timestamp);
}

/**
 * Generates Basic HTTP Authentication realm, based on client browser.
 *
 * @return string Generated realm.
 */
function get_http_auth_realm ()
{
    $realm = 'eTraxis';

    if (stripos($_SERVER['HTTP_USER_AGENT'], 'Konqueror') !== FALSE ||
        stripos($_SERVER['HTTP_USER_AGENT'], 'Opera')     !== FALSE ||
        stripos($_SERVER['HTTP_USER_AGENT'], 'Safari')    !== FALSE)
    {
        $realm .= '-' . time();
    }

    return $realm;
}

/**
 * Gzips specified file, keeping same name.
 *
 * @param string $srcName Name of the input file.
 */
function compressfile ($srcName)
{
    if (extension_loaded('zlib'))
    {
        $dstName = "{$srcName}.gz";

        $fp = fopen($srcName, 'rb');
        $data = fread($fp, filesize($srcName));
        fclose($fp);

        $zp = gzopen($dstName, 'wb6');
        gzwrite($zp, $data);
        gzclose($zp);

        unlink($srcName);
        rename($dstName, $srcName);
    }
}

/**
 * Equivalent of standard {@link http://www.php.net/file-get-contents file_get_contents} function for a gzipped attachment.
 *
 * @param string $srcName Name of the gzipped file.
 * @param int $uncompressedSize Uncompressed size of the input file.
 * @return string Uncompressed file contents.
 */
function gzfile_get_contents ($srcName, $uncompressedSize)
{
    if (extension_loaded('zlib'))
    {
        $fp = gzopen($srcName, 'rb');
        $data = gzread($fp, $uncompressedSize);
        gzclose($fp);

        return $data;
    }
    else
    {
        return file_get_contents($srcName);
    }
}

?>
