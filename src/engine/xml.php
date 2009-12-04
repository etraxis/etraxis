<?php

/**
 * XML
 *
 * This module is responsible for XML/HTML generation of eTraxis pages.
 *
 * @package Engine
 * @subpackage XML
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
//  Artem Rodygin           2005-07-30      new-018: The 'History' menuitem is useless and should be removed.
//  Artem Rodygin           2005-07-31      new-006: Records search.
//  Artem Rodygin           2005-08-15      new-003: Authentication with Active Directory.
//  Artem Rodygin           2005-08-18      new-030: UI language should be set for each user separately.
//  Artem Rodygin           2005-08-18      new-035: Customizable list size.
//  Artem Rodygin           2005-08-25      new-058: Global groups should be implemented.
//  Artem Rodygin           2005-08-29      new-068: System settings in 'config.php' should be accessable through web-interface.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-09-12      new-107: Number of displayed records should be present on the list of records.
//  Artem Rodygin           2005-09-12      new-110: Increase list boxes size up to 10 rows.
//  Artem Rodygin           2005-09-15      new-124: It's should be able to open items of menu and lists links in separated window (e.g.by right click).
//  Artem Rodygin           2005-09-22      new-141: Source code review.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-10-13      new-157: Name of logged in user should be displayed.
//  Artem Rodygin           2005-11-20      new-176: Change eTraxis design.
//  Artem Rodygin           2005-11-29      new-187: User controls alignment.
//  Artem Rodygin           2006-01-24      new-204: Active Directory Support functionality (new-003) should be conditionally "compiled".
//  Artem Rodygin           2006-02-01      bug-208: 'Total records' prompt should be changed to 'Total'.
//  Artem Rodygin           2006-07-27      new-261: UI design should be adopted to slow connection.
//  Artem Rodygin           2006-08-20      new-313: Implement HTTP authentication.
//  Artem Rodygin           2006-10-16      new-360: Sablotron errors are hard to be debugged.
//  Artem Rodygin           2006-12-10      new-432: Maintenance notice banner.
//  Artem Rodygin           2006-12-11      new-435: Sablotron errors are hard to be debugged.
//  Artem Rodygin           2006-12-11      bug-436: PHP Warning: Sablotron error on line 1: XML parser error 4: not well-formed (invalid token)
//  Artem Rodygin           2006-12-12      bug-441: Comments contain '&br;' instead of new line characters.
//  Artem Rodygin           2006-12-14      bug-443: 'Template permissions' page doesn't work.
//  Artem Rodygin           2006-12-14      new-447: 'XSL' extension should be used instead of 'XSLT' one when PHP5 is in use.
//  Artem Rodygin           2006-12-27      new-472: User must have ability to log out.
//  Artem Rodygin           2007-01-11      bug-476: Encoding corruptions of exit confirmation prompt in IE and Opera.
//  Artem Rodygin           2007-01-15      new-483: JavaScript ability notice.
//  Artem Rodygin           2007-06-30      new-499: Records dump to text file.
//  Artem Rodygin           2007-07-01      bug-538: Record dump doesn't work with PHP5.
//  Artem Rodygin           2007-10-02      new-513: Apply current filter set to search results.
//  Artem Rodygin           2007-11-05      new-571: View should show all records of current filters set.
//  Artem Rodygin           2008-04-19      new-704: Show name of user who is logged in.
//  Artem Rodygin           2008-10-25      bug-695: BBCode // Address between [url] and [/url] is cut when contains a space.
//  Artem Rodygin           2008-10-29      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2008-12-09      new-772: Guest mode should be underlined.
//  Artem Rodygin           2008-12-25      bug-782: Call to undefined function xslt_errno()
//  Artem Rodygin           2009-02-28      bug-794: [SF2643676] Security problem when logout.
//  Artem Rodygin           2009-03-11      bug-799: eTraxis doesn't work with XAMPP on Windows.
//  Artem Rodygin           2009-05-01      Updated for compatibility with HtmlUnit.
//  Artem Rodygin           2009-06-01      new-824: PHP 4 is discontinued.
//  Artem Rodygin           2009-10-12      new-837: Replace "Groups" with "Global groups" in main menu.
//  Artem Rodygin           2009-10-13      new-839: Welcome screen should be blank if no guest is enabled.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/locale.php');
require_once('../engine/debug.php');
require_once('../engine/utility.php');
require_once('../engine/sessions.php');
/**#@-*/

//--------------------------------------------------------------------------------------------------
//  Definitions.
//--------------------------------------------------------------------------------------------------

/**
 * Version info.
 */
define('VERSION', '2.0.2');

/**#@+
 * Size of HTML control.
 */
define('HTML_EDITBOX_SIZE_SMALL',  10);
define('HTML_EDITBOX_SIZE_MEDIUM', 25);
define('HTML_EDITBOX_SIZE_LONG',   50);
define('HTML_LISTBOX_SIZE',        10);
define('HTML_TEXTBOX_WIDTH',       58);
define('HTML_TEXTBOX_HEIGHT',      20);
/**#@-*/

/**
 * BBCode processing mode.
 *
 * No BBCode processing, all tags are hidden.
 */
define('BBCODE_OFF', 0);

/**
 * BBCode processing mode.
 *
 * <ul>
 * <li>"[b]", "[i]", "[u]", "[s]", "[sub]", "[sup]", "[color] are hidden</li>
 * <li>"[search]" is processed</li>
 * <li>all others are displayed as is</li>
 * </ul>
 */
define('BBCODE_SEARCH_ONLY', 1);

/**
 * BBCode processing mode.
 *
 * <ul>
 * <li>"[b]", "[i]", "[u]", "[s]", "[sub]", "[sup]", "[color]", "[search]" are processed</li>
 * <li>others are displayed as is</li>
 * </ul>
 */
define('BBCODE_MINIMUM', 2);

/**
 * BBCode processing mode.
 *
 * All available tags are processed.
 */
define('BBCODE_ALL', 3);
/**#@-*/

//--------------------------------------------------------------------------------------------------
//  Functions.
//--------------------------------------------------------------------------------------------------

/**
 * Transform BBCode tags into XML ones.
 *
 * @param string $bbcode Block of text, which could contain BBCode.
 * @param int $mode Mode of BBCode processing:
 * <ul>
 * <li>{@link BBCODE_OFF} - no BBCode processing, all tags are hidden</li>
 * <li>{@link BBCODE_SEARCH_ONLY} - "[search]" is processed, "[b]", "[i]", "[u]", "[s]", "[sub]", "[sup]", "[color]" are hidden, all others are displayed as is</li>
 * <li>{@link BBCODE_MINIMUM} - "[b]", "[i]", "[u]", "[s]", "[sub]", "[sup]", "[color]", "[search]" are processed, others are displayed as is</li>
 * <li>{@link BBCODE_ALL} - all available tags are processed</li>
 * </ul>
 * @param string $search Text to be searched.
 * @return string Resulted text with processed BBCode.
 */
function bbcode2xml ($bbcode, $mode = BBCODE_ALL, $search = NULL)
{
    debug_write_log(DEBUG_TRACE, '[bbcode2xml]');
    debug_write_log(DEBUG_DUMP,  '[bbcode2xml] $mode = ' . $mode);

    // PCRE to search for.
    $bbcode_pcre = array
    (
        '!(\[b\](.*?)\[/b\])!isu',
        '!(\[i\](.*?)\[/i\])!isu',
        '!(\[u\](.*?)\[/u\])!isu',
        '!(\[s\](.*?)\[/s\])!isu',
        '!(\[sub\](.*?)\[/sub\])!isu',
        '!(\[sup\](.*?)\[/sup\])!isu',
        '!(\[color\=(.*?)\](.*?)\[/color\])!isu',
        '!(\[size\=(.*?)\](.*?)\[/size\])!isu',
        '!(\[font\=(.*?)\](.*?)\[/font\])!isu',
        '!(\[align\=(left|center|right)\](.*?)\[/align\])!isu',
        '!(\[h1\](.*?)\[/h1\])!isu',
        '!(\[h2\](.*?)\[/h2\])!isu',
        '!(\[h3\](.*?)\[/h3\])!isu',
        '!(\[h4\](.*?)\[/h4\])!isu',
        '!(\[h5\](.*?)\[/h5\])!isu',
        '!(\[h6\](.*?)\[/h6\])!isu',
        '!(\[list\](.*?)\[/list\])!isu',
        '!(\[ulist\](.*?)\[/ulist\])!isu',
        '!(\[li\](.*?)\[/li\])!isu',
        '!(\[url\=(.*?)\](.*?)\[/url\])!isu',
        '!(\[mail\=(.*?)\](.*?)\[/mail\])!isu',
        '!(\[code\](.*?)\[/code\])!isu',
        '!(\[quote\](.*?)\[/quote\])!isu',
        '!(\[search\](.*?)\[/search\])!isu',
    );

    // PCRE to replace with.
    $bbcode_xml = array
    (
        BBCODE_OFF => array
        (
            /* [b]      */ '$2',
            /* [i]      */ '$2',
            /* [u]      */ '$2',
            /* [s]      */ '$2',
            /* [sub]    */ '$2',
            /* [sup]    */ '$2',
            /* [color]  */ '$3',
            /* [size]   */ '$3',
            /* [font]   */ '$3',
            /* [align]  */ '$3',
            /* [h1]     */ '$2',
            /* [h2]     */ '$2',
            /* [h3]     */ '$2',
            /* [h4]     */ '$2',
            /* [h5]     */ '$2',
            /* [h6]     */ '$2',
            /* [list]   */ '$2',
            /* [ulist]  */ '$2',
            /* [li]     */ '$2',
            /* [url]    */ '$3',
            /* [mail]   */ '$3',
            /* [code]   */ '$2',
            /* [quote]  */ '$2',
            /* [search] */ '$2',
        ),

        BBCODE_SEARCH_ONLY => array
        (
            /* [b]      */ '$2',
            /* [i]      */ '$2',
            /* [u]      */ '$2',
            /* [s]      */ '$2',
            /* [sub]    */ '$2',
            /* [sup]    */ '$2',
            /* [color]  */ '$3',
            /* [size]   */ '$1',
            /* [font]   */ '$1',
            /* [align]  */ '$1',
            /* [h1]     */ '$1',
            /* [h2]     */ '$1',
            /* [h3]     */ '$1',
            /* [h4]     */ '$1',
            /* [h5]     */ '$1',
            /* [h6]     */ '$1',
            /* [list]   */ '$1',
            /* [ulist]  */ '$1',
            /* [li]     */ '$1',
            /* [url]    */ '$1',
            /* [mail]   */ '$1',
            /* [code]   */ '$1',
            /* [quote]  */ '$1',
            /* [search] */ '<searchres>$2</searchres>',
        ),

        BBCODE_MINIMUM => array
        (
            /* [b]      */ '<b>$2</b>',
            /* [i]      */ '<i>$2</i>',
            /* [u]      */ '<u>$2</u>',
            /* [s]      */ '<s>$2</s>',
            /* [sub]    */ '<sub>$2</sub>',
            /* [sup]    */ '<sup>$2</sup>',
            /* [color]  */ '<span style="color: $2;">$3</span>',
            /* [size]   */ '$1',
            /* [font]   */ '$1',
            /* [align]  */ '$1',
            /* [h1]     */ '$1',
            /* [h2]     */ '$1',
            /* [h3]     */ '$1',
            /* [h4]     */ '$1',
            /* [h5]     */ '$1',
            /* [h6]     */ '$1',
            /* [list]   */ '$1',
            /* [ulist]  */ '$1',
            /* [li]     */ '$1',
            /* [url]    */ '$1',
            /* [mail]   */ '$1',
            /* [code]   */ '$1',
            /* [quote]  */ '$1',
            /* [search] */ '<searchres>$2</searchres>',
        ),

        BBCODE_ALL => array
        (
            /* [b]      */ '<b>$2</b>',
            /* [i]      */ '<i>$2</i>',
            /* [u]      */ '<u>$2</u>',
            /* [s]      */ '<s>$2</s>',
            /* [sub]    */ '<sub>$2</sub>',
            /* [sup]    */ '<sup>$2</sup>',
            /* [color]  */ '<span style="color: $2;">$3</span>',
            /* [size]   */ '<span style="font-size: $2;">$3</span>',
            /* [font]   */ '<span style="font-family: $2;">$3</span>',
            /* [align]  */ '<div style="text-align: $2;">$3</div>',
            /* [h1]     */ '<h1>$2</h1>',
            /* [h2]     */ '<h2>$2</h2>',
            /* [h3]     */ '<h3>$2</h3>',
            /* [h4]     */ '<h4>$2</h4>',
            /* [h5]     */ '<h5>$2</h5>',
            /* [h6]     */ '<h6>$2</h6>',
            /* [list]   */ '<ol>$2</ol>',
            /* [ulist]  */ '<ul>$2</ul>',
            /* [li]     */ '<li>$2</li>',
            /* [url]    */ '<url address="$2">$3</url>',
            /* [mail]   */ '<url address="mailto:$2">$3</url>',
            /* [code]   */ '<pre style="display: inline">$2</pre>',
            /* [quote]  */ '<q>$2</q>',
            /* [search] */ '<searchres>$2</searchres>',
        ),
    );

    // PCRE for opening BBCode tags.
    $tags_open = array
    (
        '!(\[b\])!isu',
        '!(\[i\])!isu',
        '!(\[u\])!isu',
        '!(\[s\])!isu',
        '!(\[sub\])!isu',
        '!(\[sup\])!isu',
        '!(\[color\=(.*?)\])!isu',
        '!(\[size\=(.*?)\])!isu',
        '!(\[font\=(.*?)\])!isu',
        '!(\[align\=(left|center|right)\])!isu',
        '!(\[h1\])!isu',
        '!(\[h2\])!isu',
        '!(\[h3\])!isu',
        '!(\[h4\])!isu',
        '!(\[h5\])!isu',
        '!(\[h6\])!isu',
        '!(\[list\])!isu',
        '!(\[ulist\])!isu',
        '!(\[li\])!isu',
        '!(\[url\=(.*?)\])!isu',
        '!(\[mail\=(.*?)\])!isu',
        '!(\[code\])!isu',
        '!(\[quote\])!isu',
        '!(\[search\])!isu',
    );

    // PCRE for closing BBCode tags.
    $tags_close = array
    (
        '!(\[/b\])!isu',
        '!(\[/i\])!isu',
        '!(\[/u\])!isu',
        '!(\[/s\])!isu',
        '!(\[/sub\])!isu',
        '!(\[/sup\])!isu',
        '!(\[/color\])!isu',
        '!(\[/size\])!isu',
        '!(\[/font\])!isu',
        '!(\[/align\])!isu',
        '!(\[/h1\])!isu',
        '!(\[/h2\])!isu',
        '!(\[/h3\])!isu',
        '!(\[/h4\])!isu',
        '!(\[/h5\])!isu',
        '!(\[/h6\])!isu',
        '!(\[/list\])!isu',
        '!(\[/ulist\])!isu',
        '!(\[/li\])!isu',
        '!(\[/url\])!isu',
        '!(\[/mail\])!isu',
        '!(\[/code\])!isu',
        '!(\[/quote\])!isu',
        '!(\[/search\])!isu',
    );

    // If search mode is on, strip the delimiter and special PCRE characters.
    if (!is_null($search))
    {
        $search = preg_quote($search, '!');
    }

    // Transform "[url]...[/url]" and "[mail]...[/mail]" to "[url=...]...[/url]" and "[mail=...]...[/mail]".
    if ($mode == BBCODE_ALL)
    {
        $bbcode = preg_replace('!\[url\](.*?)\[/url\]!isu',   '[url=$1]$1[/url]',   $bbcode);
        $bbcode = preg_replace('!\[mail\](.*?)\[/mail\]!isu', '[mail=$1]$1[/mail]', $bbcode);
    }

    // Put zero byte before and after each BBCode tag, as a tag border.
    $bbcode = preg_replace($tags_open,  "\0\$1\0", $bbcode);
    $bbcode = preg_replace($tags_close, "\0\$1\0", $bbcode);

    // Split BBCode text into array via zero byte border, so each tag is a separated array item and
    // each text between tags is the same.
    $text = explode("\0", $bbcode);

    // Stack for found opening BBCode tags.
    $stack = array();

    // Evaluate each piece of BBCode text.
    foreach ($text as $i => $str)
    {
        // Flag to determine whether the piece is BBCode tag.
        $is_tag = FALSE;

        // Check whether the piece is opening BBCode tag.
        // If so, push it to the stack.
        foreach ($tags_open as $j => $tag)
        {
            if (($is_tag = preg_match($tag, $str)))
            {
                array_push($stack, $j);
                break;
            }
        }

        // If still not is tag, then it's definitely not an *opening* BBCode tag.
        // Check whether the piece is closing BBCode tag.
        if (!$is_tag)
        {
            foreach ($tags_close as $j => $tag)
            {
                if (($is_tag = preg_match($tag, $str)))
                {
                    $is_closed = FALSE;

                    while (count($stack) > 0 && !$is_closed)
                    {
                        $k = array_pop($stack);

                        if ($k == $j)
                        {
                            $is_closed = TRUE;
                        }
                        else
                        {
                            $close_tag = preg_replace('!(\!\(\\\\\[/(.*)\\\]\)\!isu)!isu', '[/$2]', $tags_close[$k]);
                            $text[$i] = $close_tag . $text[$i];
                        }
                    }

                    break;
                }
            }
        }

        // If still not is tag, then it's definitely user's text between two BBCode tags.
        if (!$is_tag)
        {
            // If this is just an empty line - remove it.
            if ($text[$i] == "\n")
            {
                $text[$i] = NULL;
            }

            // If search mode is on, add "[search]" tags for all corresponding matches.
            if (!is_null($search))
            {
                $text[$i] = preg_replace("!({$search})!isu", '[search]$1[/search]', $text[$i]);
            }
        }
    }

    // If stack of found tags is not empty, it contains all opening tags which were not closed.
    // We have to add corresponding closing tags.
    while (count($stack) > 0)
    {
        $k = array_pop($stack);

        $close_tag = preg_replace('!(\!\(\\\\\[/(.*)\\\]\)\!isu)!isu', '[/$2]', $tags_close[$k]);
        array_push($text, $close_tag);
    }

    // Merge the array into solid block of text.
    $bbcode = implode(NULL, $text);

    // Proceed with PCRE and return the result.
    return preg_replace($bbcode_pcre, $bbcode_xml[$mode], $bbcode);
}

/**
 * Generates XML code for user menu.
 *
 * @return string Generated XML code.
 */
function gen_xml_menu ($hide_menu = FALSE)
{
    debug_write_log(DEBUG_TRACE, '[gen_xml_menu]');

    $xml = get_user_level() == USER_LEVEL_GUEST
         ? '<menu>'
         : '<menu user="' . $_SESSION[VAR_FULLNAME] . '">';

    if (get_user_level() == USER_LEVEL_GUEST)
    {
        if (!$hide_menu)
        {
            $xml .= '<menuitem url="../records/index.php?search=">' . get_html_resource(RES_RECORDS_ID)  . '</menuitem>'
                  . '<menuitem url="../projects/index.php">'        . get_html_resource(RES_PROJECTS_ID) . '</menuitem>';
        }

        $xml .= '<menuitem url="../logon/login.php">' . get_html_resource(RES_LOGIN_ID) . '</menuitem>';
    }
    else
    {
        if (get_user_level() == USER_LEVEL_ADMIN)
        {
            $xml .= '<menuitem url="../records/index.php?search=">' . get_html_resource(RES_RECORDS_ID)       . '</menuitem>'
                  . '<menuitem url="../accounts/index.php">'        . get_html_resource(RES_ACCOUNTS_ID)      . '</menuitem>'
                  . '<menuitem url="../groups/index.php">'          . get_html_resource(RES_GLOBAL_GROUPS_ID) . '</menuitem>'
                  . '<menuitem url="../projects/index.php">'        . get_html_resource(RES_PROJECTS_ID)      . '</menuitem>'
                  . '<menuitem url="../settings/index.php">'        . get_html_resource(RES_SETTINGS_ID)      . '</menuitem>'
                  . '<menuitem url="../config/index.php">'          . get_html_resource(RES_CONFIGURATION_ID) . '</menuitem>';
        }
        else
        {
            $xml .= '<menuitem url="../records/index.php?search=">' . get_html_resource(RES_RECORDS_ID)  . '</menuitem>'
                  . '<menuitem url="../projects/index.php">'        . get_html_resource(RES_PROJECTS_ID) . '</menuitem>'
                  . '<menuitem url="../settings/index.php">'        . get_html_resource(RES_SETTINGS_ID) . '</menuitem>';
        }

        if (!$_SESSION[VAR_LDAPUSER])
        {
            $xml .= '<menuitem url="../chpasswd/index.php">' . get_html_resource(RES_CHANGE_PASSWORD_ID) . '</menuitem>';
        }

        $xml .= '<menuitem url="javascript:onExit();">'
              . get_html_resource(RES_LOGOUT_ID)
              . '</menuitem>';
    }

    $xml .= '</menu>';

    if (get_user_level() != USER_LEVEL_GUEST)
    {
        $xml .= '<script src="../scripts/logout.js"/>';

        $xml .= '<script>'
              . 'function onExit()'
              . '{ if (confirm(\'' . get_html_resource(RES_CONFIRM_LOGOUT_ID) . '\')) logout(); }'
              . '</script>';
    }

    return $xml;
}

/**
 * Generates XML code for page header.
 *
 * @param string $title Title of page being generated.
 * @param string $alert Text of alert (JavaScript-safe, see {@link ustr2js}) to be shown after page is loaded.
 * @param string $focus Name of user control, which must gain focus after page is loaded.
 * @param string $init Custom JavaScript piece of code, which will be executed after page is loaded.
 * @return string Generated XML code.
 */
function gen_xml_page_header ($title = NULL, $alert = NULL, $focus = NULL, $init = NULL)
{
    debug_write_log(DEBUG_TRACE, '[gen_xml_page_header]');
    debug_write_log(DEBUG_DUMP,  '[gen_xml_page_header] $title = ' . $title);
    debug_write_log(DEBUG_DUMP,  '[gen_xml_page_header] $alert = ' . $alert);
    debug_write_log(DEBUG_DUMP,  '[gen_xml_page_header] $focus = ' . $focus);

    $xml = ' version="' . ustrprocess(get_html_resource(RES_VERSION_X_ID), VERSION) . '"'
         . (is_null($title) ? NULL : ' title="' . $title . '"')
         . (is_null($alert) ? NULL : ' alert="' . $alert . '"')
         . (is_null($focus) ? NULL : ' focus="' . $focus . '"')
         . (is_null($init)  ? NULL : ' init="'  . $init  . '"');

    $xml .= ' noscript="' . get_html_resource(RES_ALERT_JAVASCRIPT_ID) . '"';

    if (get_user_level() == USER_LEVEL_GUEST)
    {
        $xml .= ' guest="' . get_html_resource(RES_ALERT_USER_NOT_AUTHORIZED_ID) . '"';
    }

    if (MAINTENANCE_BANNER)
    {
        list($year, $month, $day) = split('-', MAINTENANCE_START_DATE);
        list($hour, $minute)      = split(':', MAINTENANCE_START_TIME);

        $date1 = mktime($hour, $minute, 0, $month, $day, $year);

        list($year, $month, $day) = split('-', MAINTENANCE_FINISH_DATE);
        list($hour, $minute)      = split(':', MAINTENANCE_FINISH_TIME);

        $date2 = mktime($hour, $minute, 0, $month, $day, $year);

        if (version_compare(PHP_VERSION, '5.1.3') >= 0)
        {
            $timezone = date('P');
        }
        else
        {
            $timezone = date('O');
            $timezone = usubstr($timezone, 0, ustrlen($timezone) - 2) . ':' . usubstr($timezone, ustrlen($timezone) - 2);
        }

        $xml .= ' banner="' . ustrprocess(get_html_resource(RES_BANNER_ID),
                                          get_datetime($date1),
                                          get_datetime($date2),
                                          'GMT' . $timezone) . '"';
    }

    return $xml;
}

/**
 * Generates XML code for first "breadcrumb" button.
 *
 * Must be used only when first "breadcrumb" is "Records".
 *
 * @param bool $search_mode TRUE when search mode is turned on, FALSE otherwise.
 * @return string Generated XML code.
 */
function gen_xml_rec_root ($search_mode = FALSE)
{
    debug_write_log(DEBUG_TRACE, '[gen_xml_rec_root]');
    debug_write_log(DEBUG_DUMP,  '[gen_xml_rec_root] $search_mode = ' . $search_mode);

    $xml = '<pathitem url="index.php?search=">' . get_html_resource(RES_RECORDS_ID) . '</pathitem>';

    if ($search_mode)
    {
        $xml .= '<pathitem url="index.php">'
              . get_html_resource($_SESSION[VAR_USE_FILTERS] ? RES_SEARCH_RESULTS_FILTERED_ID : RES_SEARCH_RESULTS_UNFILTERED_ID)
              . '</pathitem>';
    }

    return $xml;
}

/**
 * Generates XML code for bookmarks of list.
 *
 * @param int &$curr_page Number of current page (from 1 to number of pages).
 * @param int $rec_count Total number of records.
 * @param int &$rec_from Number of first record being displayed (from 1 to number of records).
 * @param int &$rec_to Number of last record being displayed (from 1 to number of records).
 * @param string $url URL for using in bookmarks.
 * @return string Generated XML code.
 */
function gen_xml_bookmarks (&$curr_page, $rec_count, &$rec_from, &$rec_to, $url = 'index.php?')
{
    debug_write_log(DEBUG_TRACE, '[gen_xml_bookmarks]');
    debug_write_log(DEBUG_DUMP,  '[gen_xml_bookmarks] $curr_page = ' . $curr_page);
    debug_write_log(DEBUG_DUMP,  '[gen_xml_bookmarks] $rec_count = ' . $rec_count);
    debug_write_log(DEBUG_DUMP,  '[gen_xml_bookmarks] $url       = ' . $url);

    $nav_count = (int)(($rec_count + $_SESSION[VAR_PAGEROWS] - 1) / $_SESSION[VAR_PAGEROWS]);
    $curr_page = ustr2int($curr_page, 1, $nav_count);

    debug_write_log(DEBUG_DUMP, '[gen_xml_bookmarks] $nav_count = ' . $nav_count);
    debug_write_log(DEBUG_DUMP, '[gen_xml_bookmarks] $curr_page = ' . $curr_page);

    $nav_from = (int)(($curr_page - 1) / $_SESSION[VAR_PAGEBKMS]) * $_SESSION[VAR_PAGEBKMS] + 1;
    $nav_to   = $nav_from + $_SESSION[VAR_PAGEBKMS] - 1;

    if ($nav_to > $nav_count)
    {
        $nav_to = $nav_count;
    }

    debug_write_log(DEBUG_DUMP, '[gen_xml_bookmarks] $nav_from = ' . $nav_from);
    debug_write_log(DEBUG_DUMP, '[gen_xml_bookmarks] $nav_to   = ' . $nav_to);

    $rec_from = ($curr_page - 1) * $_SESSION[VAR_PAGEROWS] + 1;
    $rec_to   = $rec_from + $_SESSION[VAR_PAGEROWS] - 1;

    if ($rec_to > $rec_count)
    {
        $rec_to = $rec_count;
    }

    debug_write_log(DEBUG_DUMP, '[gen_xml_bookmarks] $rec_from = ' . $rec_from);
    debug_write_log(DEBUG_DUMP, '[gen_xml_bookmarks] $rec_to   = ' . $rec_to);

    $xml = '<bookmarks total="' . get_html_resource(RES_TOTAL_ID) . ' ' . $rec_count . '">';

    if ($nav_from > 1)
    {
        $xml .= '<bookmark url="' . $url . 'page=1">%lt;%lt;</bookmark>'
              . '<bookmark url="' . $url . 'page=' . ($nav_from - 1) . '">%lt;</bookmark>';
    }

    for ($i = $nav_from; $i <= $nav_to; $i++)
    {
        if ($i == $curr_page)
        {
            $xml .= '<ibookmark>' . $i . '</ibookmark>';
        }
        else
        {
            $xml .= '<bookmark url="' . $url . 'page=' . $i . '">' . $i . '</bookmark>';
        }
    }

    if ($nav_to < $nav_count)
    {
        $xml .= '<bookmark url="' . $url . 'page=' . ($nav_to + 1) . '">%gt;</bookmark>'
              . '<bookmark url="' . $url . 'page=' . $nav_count . '">%gt;%gt;</bookmark>';
    }

    $xml .= '</bookmarks>';

    return $xml;
}

/**
 * Converts generated XML code to HTML.
 *
 * (by changing the default XSLT-file can convert to anything you want)
 *
 * @param string $xml Input XML code.
 * @param string $xsl XSLT-file (path is related to "engine" directory).
 * @return string Generated HTML code.
 */
function xml2html ($xml, $xsl = 'engine.xsl')
{
    debug_write_log(DEBUG_TRACE, '[xml2html]');

    $page = new DOMDocument();
    $xslt = new XSLTProcessor();

    $page->load(LOCALROOT . 'engine/' . $xsl);
    $xslt->importStyleSheet($page);
    $page->loadXML($xml);

    $html = $xslt->transformToXML($page);

    if (!$html)
    {
        debug_write_log(DEBUG_DUMP, '[xml2html] $xml = ' . $xml);
    }
    else
    {
        $html = str_replace('%br;', '<br>', $html);

        mb_regex_encoding('UTF-8');

        $html = mb_eregi_replace('%([A-Za-z]+);',          '&\1;', $html);
        $html = mb_eregi_replace('%(#[0-9]{1,4});',        '&\1;', $html);
        $html = mb_eregi_replace('%(#x[0-9A-Fa-f]{1,4});', '&\1;', $html);
    }

    debug_write_log(DEBUG_PERFORMANCE, 'page size = ' . strlen($html));

    return $html;
}

?>
