<?php

/**
 * Localization
 *
 * This module implements multilingual support of eTraxis UI.
 *
 * @package Engine
 * @subpackage Localization
 */

//--------------------------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system.
//  Copyright (C) 2004-2010 by Artem Rodygin
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
//  Artem Rodygin           2005-08-18      new-030: UI language should be set for each user separately.
//  Artem Rodygin           2005-08-28      bug-033: Titles in metrics charts are not readable when Russian is set.
//  Artem Rodygin           2005-08-31      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-09-12      new-105: Format of date values are being entered should depend on user locale settings.
//  Artem Rodygin           2005-09-22      new-141: Source code review.
//  Artem Rodygin           2005-11-13      bug-177: Multibyte string functions should be used instead of 'eregi' and 'split'.
//  Artem Rodygin           2006-01-22      new-202: Add debug logging to 'locale' module.
//  Artem Rodygin           2006-05-07      bug-250: PHP Notice: iconv(): Detected illegal character in input string
//  Artem Rodygin           2006-05-16      new-258: Latvian localization.
//  Artem Rodygin           2006-05-18      bug-260: PHP Warning: date(): Windows does not support dates prior to midnight (00:00:00), January 1, 1970
//  Artem Rodygin           2006-07-22      bug-266: Project metrics charts contain wrong titles when Latvian language is in use.
//  Artem Rodygin           2006-08-03      bug-298: PHP Notice: iconv(): Detected illegal character in input string
//  Artem Rodygin           2006-10-08      bug-341: /src/engine/locale.php: Variable $regs was used before it was defined.
//  Artem Rodygin           2006-10-08      bug-342: /src/engine/locale.php: Global variables $resource_* were used before they were defined.
//  Artem Rodygin           2006-11-06      new-371: ISO-8859-1 should be used for English.
//  Artem Rodygin           2006-11-06      bug-373: Latin7 font should be used in charts for Latvian.
//  Artem Rodygin           2006-11-06      new-372: KOI8-R should be used for Russian.
//  Artem Rodygin           2006-12-04      bug-415: Filter doesn't show all the records which are in specified range of some state dates.
//  Artem Rodygin           2007-09-09      new-577: German localization.
//  Normando Hall           2007-12-26      new-658: Spanish localization.
//  Artem Rodygin           2007-12-27      new-659: Set default language
//  Artem Rodygin           2008-03-31      new-691: Localization module is optimized to avoid prompts duplication.
//  Gregory Van der Steen   2008-04-03      new-693: Dutch localization.
//  Febrina H. Ariendhita   2008-04-07      new-685: Indonesian localization.
//  Muhammet Kara           2008-04-11      new-692: Turkish localization.
//  Artem Rodygin           2008-04-12      bug-702: Apostrophe in metrics chart title is displayed as sequence of "&#039;".
//  Philippe Infarnet       2008-04-29      new-689: French localization.
//  Artem Rodygin           2008-05-01      new-717: Add date format for Canada and Australia.
//  Yasen Vasilev           2008-06-23      new-722: Bulgarian localization.
//  Rodrigo Brayner         2008-07-28      new-737: Brazilian Portuguese localization.
//  Artem Rodygin           2009-03-11      bug-799: eTraxis doesn't work with XAMPP on Windows.
//  Masayoshi Ootsuka       2009-10-19      new-850: Japanese localization.
//  Giacomo Giustozzi       2010-02-01      new-904: Italian localization.
//  Dan Stoenescu           2010-02-05      new-910: Romanian localization.
//  Giacomo Giustozzi       2010-02-04      bug-909: Languages in settings page are not sorted
//  Vit Popelka             2010-07-14      new-948: Czech localization.
//  Radosław Wójtowicz      2010-08-06      new-953: Polish localization.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/resource.php');
require_once('../engine/config.php');
require_once('../engine/debug.php');
require_once('../engine/utility.php');
/**#@-*/

/**#@+
 * Resource file with translated UI prompts.
 */
require_once('../engine/res/english.php');
require_once('../engine/res/french.php');
require_once('../engine/res/german.php');
require_once('../engine/res/italian.php');
require_once('../engine/res/spanish.php');
require_once('../engine/res/portuguese.php');
require_once('../engine/res/dutch.php');
require_once('../engine/res/latvian.php');
require_once('../engine/res/russian.php');
require_once('../engine/res/polish.php');
require_once('../engine/res/czech.php');
require_once('../engine/res/bulgarian.php');
require_once('../engine/res/romanian.php');
require_once('../engine/res/japanese.php');
require_once('../engine/res/turkish.php');
require_once('../engine/res/indonesian.php');
/**#@-*/

//--------------------------------------------------------------------------------------------------
//  Definitions.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Supported language ID.
 */
define('LANG_ENGLISH_US',   1000);
define('LANG_ENGLISH_UK',   1001);
define('LANG_ENGLISH_AUS',  1002);
define('LANG_ENGLISH_CAN',  1003);
define('LANG_FRENCH',       1010);
define('LANG_GERMAN',       1020);
define('LANG_ITALIAN',      1030);
define('LANG_SPANISH',      1040);
define('LANG_CATALAN',      1050);
define('LANG_GALICIAN',     1060);
define('LANG_BASQUE',       1070);
define('LANG_PORTUGUESE',   1080);
define('LANG_DUTCH',        1090);
define('LANG_GREEK',        1100);
define('LANG_IRISH',        1110);
define('LANG_MALTESE',      1120);
define('LANG_DANISH',       2000);
define('LANG_NORWEGIAN',    2010);
define('LANG_SWEDISH',      2020);
define('LANG_FINNISH',      2030);
define('LANG_ESTONIAN',     2040);
define('LANG_LATVIAN',      2050);
define('LANG_LITHUANIAN',   2060);
define('LANG_ICELANDIC',    2070);
define('LANG_RUSSIAN',      3000);
define('LANG_UKRAINIAN',    3010);
define('LANG_BELARUSIAN',   3020);
define('LANG_POLISH',       3030);
define('LANG_CZECH',        3040);
define('LANG_SLOVAK',       3050);
define('LANG_HUNGARIAN',    3060);
define('LANG_SLOVENIAN',    3070);
define('LANG_CROATIAN',     3080);
define('LANG_BOSNIAN',      3090);
define('LANG_SERBIAN',      3100);
define('LANG_ALBANIAN',     3110);
define('LANG_MACEDONIAN',   3120);
define('LANG_BULGARIAN',    3130);
define('LANG_ROMANIAN',     3140);
define('LANG_MOLDAVIAN',    3150);
define('LANG_GEORGIAN',     4000);
define('LANG_ARMENIAN',     4010);
define('LANG_AZERBAIJANI',  4020);
define('LANG_KAZAKH',       4030);
define('LANG_TURKMEN',      4040);
define('LANG_UZBEK',        4050);
define('LANG_TAJIK',        4060);
define('LANG_KYRGYZ',       4070);
define('LANG_JAPANESE',     5000);
define('LANG_CHINESE_SIMP', 5010);
define('LANG_CHINESE_TRAD', 5020);
define('LANG_KOREAN',       5030);
define('LANG_MONGOLIAN',    5040);
define('LANG_TURKISH',      6000);
define('LANG_HEBREW',       6010);
define('LANG_YIDDISH',      6020);
define('LANG_ARABIC',       6030);
define('LANG_KURDISH',      6040);
define('LANG_ASSYRIAN',     6050);
define('LANG_PERSIAN',      6060);
define('LANG_PUSHTU',       6070);
define('LANG_TURKIC',       6080);
define('LANG_URDU',         6090);
define('LANG_PUNJABI',      6100);
define('LANG_SINDHI',       6110);
define('LANG_HINDI',        7000);
define('LANG_TELUGU',       7010);
define('LANG_MARATHI',      7020);
define('LANG_KANNADA',      7030);
define('LANG_GUJARATI',     7040);
define('LANG_MALAYALAM',    7050);
define('LANG_ORIYA',        7060);
define('LANG_ASSAMESE',     7070);
define('LANG_KASHMIRI',     7080);
define('LANG_NEPALI',       7090);
define('LANG_TIBETAN',      7100);
define('LANG_BENGALI',      7110);
define('LANG_BURMESE',      7120);
define('LANG_THAI',         7130);
define('LANG_LAOTHIAN',     7140);
define('LANG_VIETNAMESE',   7150);
define('LANG_INDONESIAN',   7160);
define('LANG_MALAY',        7170);
define('LANG_TAGALOG',      7180);
define('LANG_SINHALESE',    7190);
define('LANG_TAMIL',        7200);
define('LANG_AFRIKAANS',    8000);
define('LANG_SWAHILI',      8010);
define('LANG_HAUSA',        8020);
define('LANG_AMHARIC',      8030);
define('LANG_YORUBA',       8040);
define('LANG_IGBO',         8050);
define('LANG_MALAGASY',     8060);
define('LANG_SOMALI',       8070);
define('LANG_FULAH',        8080);
define('LANG_SHONA',        8090);
define('LANG_ZULU',         8100);
define('LANG_XHOSA',        8110);
define('LANG_KIRUNDI',      8120);
define('LANG_BEMBA',        8130);
define('LANG_WOLOF',        8140);
define('LANG_TSWANA',       8150);
define('LANG_TSONGA',       8160);
define('LANG_LUGANDA',      8170);
define('LANG_LINGALA',      8180);
/**#@-*/

/**#@+
 * Localization parameter.
 */
define('LOCALE_RES_TABLE',   1);
define('LOCALE_SUFFIX',      2);
define('LOCALE_ENCODING',    3);
define('LOCALE_PATH2FONTS',  4);
define('LOCALE_DIRECTION',   5);
define('LOCALE_DATE_FORMAT', 6);
define('LOCALE_TIME_FORMAT', 7);
/**#@-*/

// Prompts tables.
global $resource_english;
global $resource_french;
global $resource_german;
global $resource_italian;
global $resource_spanish;
global $resource_portuguese;
global $resource_dutch;
global $resource_latvian;
global $resource_russian;
global $resource_polish;
global $resource_czech;
global $resource_bulgarian;
global $resource_romanian;
global $resource_japanese;
global $resource_turkish;
global $resource_indonesian;

// Locales.
$locale_info = array
(
    // English (US)
    LANG_ENGLISH_US => array
    (
        LOCALE_RES_TABLE   => $resource_english,
        LOCALE_SUFFIX      => 'US',
        LOCALE_ENCODING    => 'ISO-8859-1',
        LOCALE_PATH2FONTS  => 'latin1',
        LOCALE_DIRECTION   => 'ltr',
        LOCALE_DATE_FORMAT => 'n/j/Y',
        LOCALE_TIME_FORMAT => 'g:i A',
    ),

    // English (UK)
    LANG_ENGLISH_UK => array
    (
        LOCALE_RES_TABLE   => $resource_english,
        LOCALE_SUFFIX      => 'UK',
        LOCALE_ENCODING    => 'ISO-8859-1',
        LOCALE_PATH2FONTS  => 'latin1',
        LOCALE_DIRECTION   => 'ltr',
        LOCALE_DATE_FORMAT => 'd/m/Y',
        LOCALE_TIME_FORMAT => 'H:i',
    ),

    // English (Canada)
    LANG_ENGLISH_CAN => array
    (
        LOCALE_RES_TABLE   => $resource_english,
        LOCALE_SUFFIX      => 'Canada',
        LOCALE_ENCODING    => 'ISO-8859-1',
        LOCALE_PATH2FONTS  => 'latin1',
        LOCALE_DIRECTION   => 'ltr',
        LOCALE_DATE_FORMAT => 'd/m/Y',
        LOCALE_TIME_FORMAT => 'g:i A',
    ),

    // English (Australia)
    LANG_ENGLISH_AUS => array
    (
        LOCALE_RES_TABLE   => $resource_english,
        LOCALE_SUFFIX      => 'Australia',
        LOCALE_ENCODING    => 'ISO-8859-1',
        LOCALE_PATH2FONTS  => 'latin1',
        LOCALE_DIRECTION   => 'ltr',
        LOCALE_DATE_FORMAT => 'j/m/Y',
        LOCALE_TIME_FORMAT => 'g:i A',
    ),

    // French
    LANG_FRENCH => array
    (
        LOCALE_RES_TABLE   => $resource_french,
        LOCALE_SUFFIX      => NULL,
        LOCALE_ENCODING    => 'ISO-8859-1',
        LOCALE_PATH2FONTS  => 'latin1',
        LOCALE_DIRECTION   => 'ltr',
        LOCALE_DATE_FORMAT => 'd/m/Y',
        LOCALE_TIME_FORMAT => 'H:i',
    ),

    // German
    LANG_GERMAN => array
    (
        LOCALE_RES_TABLE   => $resource_german,
        LOCALE_SUFFIX      => NULL,
        LOCALE_ENCODING    => 'ISO-8859-1',
        LOCALE_PATH2FONTS  => 'latin1',
        LOCALE_DIRECTION   => 'ltr',
        LOCALE_DATE_FORMAT => 'd.m.Y',
        LOCALE_TIME_FORMAT => 'H:i',
    ),

    // Italian
    LANG_ITALIAN => array
    (
        LOCALE_RES_TABLE   => $resource_italian,
        LOCALE_SUFFIX      => NULL,
        LOCALE_ENCODING    => 'ISO-8859-1',
        LOCALE_PATH2FONTS  => 'latin1',
        LOCALE_DIRECTION   => 'ltr',
        LOCALE_DATE_FORMAT => 'd/m/Y',
        LOCALE_TIME_FORMAT => 'H:i',
    ),

    // Spanish
    LANG_SPANISH => array
    (
        LOCALE_RES_TABLE   => $resource_spanish,
        LOCALE_SUFFIX      => NULL,
        LOCALE_ENCODING    => 'ISO-8859-1',
        LOCALE_PATH2FONTS  => 'latin1',
        LOCALE_DIRECTION   => 'ltr',
        LOCALE_DATE_FORMAT => 'd/m/Y',
        LOCALE_TIME_FORMAT => 'G:i',
    ),

    // Portuguese (Brazil)
    LANG_PORTUGUESE => array
    (
        LOCALE_RES_TABLE   => $resource_portuguese,
        LOCALE_SUFFIX      => NULL,
        LOCALE_ENCODING    => 'ISO-8859-1',
        LOCALE_PATH2FONTS  => 'latin1',
        LOCALE_DIRECTION   => 'ltr',
        LOCALE_DATE_FORMAT => 'n/j/Y',
        LOCALE_TIME_FORMAT => 'H:i',
    ),

    // Dutch
    LANG_DUTCH => array
    (
        LOCALE_RES_TABLE   => $resource_dutch,
        LOCALE_SUFFIX      => NULL,
        LOCALE_ENCODING    => 'ISO-8859-1',
        LOCALE_PATH2FONTS  => 'latin1',
        LOCALE_DIRECTION   => 'ltr',
        LOCALE_DATE_FORMAT => 'j-n-Y',
        LOCALE_TIME_FORMAT => 'G:i',
    ),

    // Latvian
    LANG_LATVIAN => array
    (
        LOCALE_RES_TABLE   => $resource_latvian,
        LOCALE_SUFFIX      => NULL,
        LOCALE_ENCODING    => 'ISO-8859-13',
        LOCALE_PATH2FONTS  => 'latin7',
        LOCALE_DIRECTION   => 'ltr',
        LOCALE_DATE_FORMAT => 'Y.m.d.',
        LOCALE_TIME_FORMAT => 'G:i',
    ),

    // Russian
    LANG_RUSSIAN => array
    (
        LOCALE_RES_TABLE   => $resource_russian,
        LOCALE_SUFFIX      => NULL,
        LOCALE_ENCODING    => 'KOI8-R',
        LOCALE_PATH2FONTS  => 'koi8r',
        LOCALE_DIRECTION   => 'ltr',
        LOCALE_DATE_FORMAT => 'd.m.Y',
        LOCALE_TIME_FORMAT => 'G:i',
    ),

    // Polish
    LANG_POLISH => array
    (
        LOCALE_RES_TABLE   => $resource_polish,
        LOCALE_SUFFIX      => NULL,
        LOCALE_ENCODING    => 'ISO-8859-2',
        LOCALE_PATH2FONTS  => 'latin2',
        LOCALE_DIRECTION   => 'ltr',
        LOCALE_DATE_FORMAT => 'Y.m.d',
        LOCALE_TIME_FORMAT => 'H:i',
    ),

    // Czech
    LANG_CZECH => array
    (
        LOCALE_RES_TABLE   => $resource_czech,
        LOCALE_SUFFIX      => NULL,
        LOCALE_ENCODING    => 'UTF-8',
        LOCALE_PATH2FONTS  => 'latin2',
        LOCALE_DIRECTION   => 'ltr',
        LOCALE_DATE_FORMAT => 'd.m.Y',
        LOCALE_TIME_FORMAT => 'H:i',
    ),

    // Bulgarian
    LANG_BULGARIAN => array
    (
        LOCALE_RES_TABLE   => $resource_bulgarian,
        LOCALE_SUFFIX      => NULL,
        LOCALE_ENCODING    => 'Windows-1251',
        LOCALE_PATH2FONTS  => 'win1251',
        LOCALE_DIRECTION   => 'ltr',
        LOCALE_DATE_FORMAT => 'd.n.Y',
        LOCALE_TIME_FORMAT => 'H:i',
    ),

    // Romanian
    LANG_ROMANIAN => array
    (
        LOCALE_RES_TABLE   => $resource_romanian,
        LOCALE_SUFFIX      => NULL,
        LOCALE_ENCODING    => 'ISO-8859-1',
        LOCALE_PATH2FONTS  => 'latin1',
        LOCALE_DIRECTION   => 'ltr',
        LOCALE_DATE_FORMAT => 'd.m.Y',
        LOCALE_TIME_FORMAT => 'H:i',
    ),

    // Japanese
    LANG_JAPANESE => array
    (
        LOCALE_RES_TABLE   => $resource_japanese,
        LOCALE_SUFFIX      => NULL,
        LOCALE_ENCODING    => 'UTF-8',
        LOCALE_PATH2FONTS  => 'latin1',
        LOCALE_DIRECTION   => 'ltr',
        LOCALE_DATE_FORMAT => 'Y/m/d',
        LOCALE_TIME_FORMAT => 'G:i',
    ),

    // Turkish
    LANG_TURKISH => array
    (
        LOCALE_RES_TABLE   => $resource_turkish,
        LOCALE_SUFFIX      => NULL,
        LOCALE_ENCODING    => 'ISO-8859-9',
        LOCALE_PATH2FONTS  => 'latin5',
        LOCALE_DIRECTION   => 'ltr',
        LOCALE_DATE_FORMAT => 'd.m.Y',
        LOCALE_TIME_FORMAT => 'H:i',
    ),

    // Indonesian
    LANG_INDONESIAN => array
    (
        LOCALE_RES_TABLE   => $resource_indonesian,
        LOCALE_SUFFIX      => NULL,
        LOCALE_ENCODING    => 'ISO-8859-1',
        LOCALE_PATH2FONTS  => 'latin1',
        LOCALE_DIRECTION   => 'ltr',
        LOCALE_DATE_FORMAT => 'd/m/Y',
        LOCALE_TIME_FORMAT => 'G:i',
    ),
);

/**
 * Session variable to store current UI language.
 */
define('VAR_LOCALE', 'eTraxis_Locale');

//--------------------------------------------------------------------------------------------------
//  Functions.
//--------------------------------------------------------------------------------------------------

/**
 * Returns requested prompt.
 *
 * @param int $res_id ID of prompt (see {@link resource.php})
 * @param int $lang ID of language. If omitted, then language of current user, or (when user is
 * not logged in) default language will be used (see {@link LANG_DEFAULT}).
 * @return string Text of prompt, converted to UTF-8.
 */
function get_resource ($res_id, $lang = NULL)
{
    global $locale_info;

    if (is_null($lang))
    {
        $lang = (isset($_SESSION[VAR_LOCALE]) ? $_SESSION[VAR_LOCALE] : LANG_DEFAULT);
    }

    $res = (isset($locale_info[$lang][LOCALE_RES_TABLE][$res_id])
            ? $locale_info[$lang][LOCALE_RES_TABLE][$res_id]
            : $locale_info[LANG_ENGLISH_US][LOCALE_RES_TABLE][$res_id]);

    $res = iconv($locale_info[$lang][LOCALE_ENCODING], 'UTF-8', $res);

    if ($res_id == RES_LOCALE_ID && !is_null($locale_info[$lang][LOCALE_SUFFIX]))
    {
        $res .= " ({$locale_info[$lang][LOCALE_SUFFIX]})";
    }

    return $res;
}

/**
 * Returns requested prompt, updated to be displayed in HTML.
 *
 * @param int $res_id ID of prompt (see {@link resource.php})
 * @param int $lang ID of language. If omitted, then language of current user, or (when user is
 * not logged in) default language will be used (see {@link LANG_DEFAULT}).
 * @return string Text of prompt, converted to UTF-8. The value is HTML-safe (tags are stripped).
 */
function get_html_resource ($res_id, $lang = NULL)
{
    return ustr2html(get_resource($res_id, $lang));
}

/**
 * Returns requested prompt, updated to be displayed in JavaScript.
 *
 * @param int $res_id ID of prompt (see {@link resource.php})
 * @param int $lang ID of language. If omitted, then language of current user, or (when user is
 * not logged in) default language will be used (see {@link LANG_DEFAULT}).
 * @return string Text of prompt, converted to UTF-8. The value is JavaScript-safe (quotes are stripped).
 */
function get_js_resource ($res_id, $lang = NULL)
{
    return ustr2js(get_resource($res_id, $lang));
}

/**
 * Returns encoding for specified language.
 *
 * @param int $lang ID of language. If omitted, then language of current user, or (when user is
 * not logged in) default language will be used (see {@link LANG_DEFAULT}).
 * @return string Encoding string name (e.g. "ISO-8859-1").
 */
function get_encoding ($lang = NULL)
{
    global $locale_info;

    if (is_null($lang))
    {
        $lang = (isset($_SESSION[VAR_LOCALE]) ? $_SESSION[VAR_LOCALE] : LANG_DEFAULT);
    }

    return $locale_info[$lang][LOCALE_ENCODING];
}

/**
 * Returns direction of specified language.
 *
 * @param int $lang ID of language. If omitted, then language of current user, or (when user is
 * not logged in) default language will be used (see {@link LANG_DEFAULT}).
 * @return string Either "LTR", or "RTL".
 */
function get_direction ($lang = NULL)
{
    global $locale_info;

    if (is_null($lang))
    {
        $lang = (isset($_SESSION[VAR_LOCALE]) ? $_SESSION[VAR_LOCALE] : LANG_DEFAULT);
    }

    return $locale_info[$lang][LOCALE_DIRECTION];
}

/**
 * Returns string with date, formatted according to specified language.
 *
 * @param int $timestamp Unix timestamp (see {@link http://en.wikipedia.org/wiki/Unix_time})
 * @param int $lang ID of language. If omitted, then language of current user, or (when user is
 * not logged in) default language will be used (see {@link LANG_DEFAULT}).
 * @return string String with date.
 */
function get_date ($timestamp, $lang = NULL)
{
    global $locale_info;

    if (is_null($lang))
    {
        $lang = (isset($_SESSION[VAR_LOCALE]) ? $_SESSION[VAR_LOCALE] : LANG_DEFAULT);
    }

    $format = $locale_info[$lang][LOCALE_DATE_FORMAT];

    return date($format, $timestamp);
}

/**
 * Returns string with time, formatted according to specified language.
 *
 * @param int $timestamp Unix timestamp (see {@link http://en.wikipedia.org/wiki/Unix_time})
 * @param int $lang ID of language. If omitted, then language of current user, or (when user is
 * not logged in) default language will be used (see {@link LANG_DEFAULT}).
 * @return string String with time.
 */
function get_time ($timestamp, $lang = NULL)
{
    global $locale_info;

    if (is_null($lang))
    {
        $lang = (isset($_SESSION[VAR_LOCALE]) ? $_SESSION[VAR_LOCALE] : LANG_DEFAULT);
    }

    $format = $locale_info[$lang][LOCALE_TIME_FORMAT];

    return date($format, $timestamp);
}

/**
 * Returns string with date and time, formatted according to specified language.
 *
 * @param int $timestamp Unix timestamp (see {@link http://en.wikipedia.org/wiki/Unix_time})
 * @param int $lang ID of language. If omitted, then language of current user, or (when user is
 * not logged in) default language will be used (see {@link LANG_DEFAULT}).
 * @return string String with date and time, space separated.
 */
function get_datetime ($timestamp, $lang = NULL)
{
    global $locale_info;

    if (is_null($lang))
    {
        $lang = (isset($_SESSION[VAR_LOCALE]) ? $_SESSION[VAR_LOCALE] : LANG_DEFAULT);
    }

    $format = $locale_info[$lang][LOCALE_DATE_FORMAT] . ' ' . $locale_info[$lang][LOCALE_TIME_FORMAT];

    return date($format, $timestamp);
}

/**
 * Converts string presentation of date to Unix timestamp (see {@link http://en.wikipedia.org/wiki/Unix_time}).
 *
 * @param string $str String presentation of date. It must consist to date format of specified language.
 * @param int $lang ID of language. If omitted, then language of current user, or (when user is
 * not logged in) default language will be used (see {@link LANG_DEFAULT}).
 * @return int Valid Unix timestamp on success, or -1 if specified date is not formatted in consistancy
 * with date format of specified language.
 */
function ustr2date ($str, $lang = NULL)
{
    debug_write_log(DEBUG_TRACE, '[ustr2date]');
    debug_write_log(DEBUG_DUMP,  '[ustr2date] $str  = ' . $str);
    debug_write_log(DEBUG_DUMP,  '[ustr2date] $lang = ' . $lang);

    global $locale_info;

    if (is_null($lang))
    {
        $lang = (isset($_SESSION[VAR_LOCALE]) ? $_SESSION[VAR_LOCALE] : LANG_DEFAULT);
    }

    $format = $locale_info[$lang][LOCALE_DATE_FORMAT];

    $date = array(0, 0, 0);

    $regexp = array
    (
        'd' => array('([0-9]{1,2})', 1),
        'j' => array('([0-9]{1,2})', 1),
        'm' => array('([0-9]{1,2})', 0),
        'n' => array('([0-9]{1,2})', 0),
        'Y' => array('([0-9]{4})',   2),
        'y' => array('([0-9]{2})',   2),
    );

    $count = 0;

    for ($i = 0; $i < ustrlen($format); $i++)
    {
        $key = usubstr($format, $i, 1);

        if (array_key_exists($key, $regexp))
        {
            $format = ustr_replace($key, $regexp[$key][0], $format);
            $i += ustrlen($regexp[$key][0]);
            $date[$regexp[$key][1]] = ++$count;
        }
    }

    mb_regex_encoding('UTF-8');

    $regs = NULL;

    if (mb_eregi($format, @iconv('UTF-8', 'ISO-8859-1', $str), $regs))
    {
        debug_write_log(DEBUG_DUMP, '[ustr2date] $regs[$date[0]] = ' . $regs[$date[0]]);
        debug_write_log(DEBUG_DUMP, '[ustr2date] $regs[$date[1]] = ' . $regs[$date[1]]);
        debug_write_log(DEBUG_DUMP, '[ustr2date] $regs[$date[2]] = ' . $regs[$date[2]]);

        if (checkdate($regs[$date[0]], $regs[$date[1]], $regs[$date[2]]))
        {
            return @mktime(0, 0, 0, $regs[$date[0]], $regs[$date[1]], $regs[$date[2]]);
        }
        else
        {
            debug_write_log(DEBUG_NOTICE, '[ustr2date] \'checkdate\' has returned FALSE value.');
            return -1;
        }
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, '[ustr2date] \'mb_eregi\' has returned FALSE value.');
        return -1;
    }
}

/**
 * Converts string presentation of time duration to amount of minutes.
 * Time duration is a string like "hh:mm", where "hh" can be from "0" to "999999" and "mm" can be from "0" to "59".
 *
 * @param string $str String presentation of time duration.
 * @return int Valid amount of minutes on success, or -1 if specified duration is not in consistancy
 * with described format.
 */
function ustr2time ($str)
{
    debug_write_log(DEBUG_TRACE, '[ustr2time]');
    debug_write_log(DEBUG_DUMP,  '[ustr2time] $str = ' . $str);

    mb_regex_encoding('UTF-8');

    $regs = NULL;

    if (mb_eregi('([0-9]{1,6}):([0-9]{1,2})', $str, $regs))
    {
        debug_write_log(DEBUG_DUMP, '[ustr2time] $regs[1] = ' . $regs[1]);
        debug_write_log(DEBUG_DUMP, '[ustr2time] $regs[2] = ' . $regs[2]);

        return ($regs[2] < 60 ? ($regs[1] * 60 + $regs[2]) : -1);
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, '[ustr2time] \'mb_eregi\' has returned FALSE value.');
        return -1;
    }
}

/**
 * Returns array of supported locales sorted alphabetically.
 *
 * @return array Array with supported locales.
 */
function get_supported_locales_sorted ()
{
    debug_write_log(DEBUG_TRACE, '[get_supported_locales_sorted]');

    global $locale_info;

    $supported_locales = array_keys($locale_info);
    $supported_locales_names = array();

    foreach ($supported_locales as $item)
    {
        debug_write_log(DEBUG_DUMP,  '[get_supported_locales_sorted] $item = ' . $item);
        $supported_locales_names[$item] = get_html_resource(RES_LOCALE_ID, $item);
    }

    asort($supported_locales_names);

    return $supported_locales_names;
}

?>
