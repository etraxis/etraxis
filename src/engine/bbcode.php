<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2007-2010  Artem Rodygin
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
 * BBCode
 *
 * This module is responsible for BBCode processing.
 *
 * @package Engine
 * @subpackage BBCode
 */

/**#@+
 * Dependency.
 */
require_once('../engine/debug.php');
/**#@-*/

//------------------------------------------------------------------------------
//  Definitions.
//------------------------------------------------------------------------------

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

//------------------------------------------------------------------------------
//  Functions.
//------------------------------------------------------------------------------

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

    // PCRE for opening BBCode tags.
    $bbcode_open_tags = array
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
        '!(\[url\])!isu',
        '!(\[url\=(.*?)\])!isu',
        '!(\[mail\])!isu',
        '!(\[mail\=(.*?)\])!isu',
        '!(\[code\])!isu',
        '!(\[quote\])!isu',
        '!(\[search\])!isu',
    );

    // PCRE for closing BBCode tags.
    $bbcode_close_tags = array
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
        '!(\[/url\])!isu',
        '!(\[/mail\])!isu',
        '!(\[/mail\])!isu',
        '!(\[/code\])!isu',
        '!(\[/quote\])!isu',
        '!(\[/search\])!isu',
    );

    // XML alternatives for opening BBCode tsgs.
    $xml_open_tags = array
    (
        /* [b]      */ '<bbcode_b>',
        /* [i]      */ '<bbcode_i>',
        /* [u]      */ '<bbcode_u>',
        /* [s]      */ '<bbcode_s>',
        /* [sub]    */ '<bbcode_sub>',
        /* [sup]    */ '<bbcode_sup>',
        /* [color]  */ '<bbcode_color value="$2">',
        /* [size]   */ '<bbcode_size value="$2">',
        /* [font]   */ '<bbcode_font value="$2">',
        /* [align]  */ '<bbcode_align value="$2">',
        /* [h1]     */ '<bbcode_h1>',
        /* [h2]     */ '<bbcode_h2>',
        /* [h3]     */ '<bbcode_h3>',
        /* [h4]     */ '<bbcode_h4>',
        /* [h5]     */ '<bbcode_h5>',
        /* [h6]     */ '<bbcode_h6>',
        /* [list]   */ '<bbcode_list>',
        /* [ulist]  */ '<bbcode_ulist>',
        /* [li]     */ '<bbcode_li>',
        /* [url]    */ '<bbcode_url>',
        /* [url]    */ '<bbcode_url value="$2">',
        /* [mail]   */ '<bbcode_mail>',
        /* [mail]   */ '<bbcode_mail value="$2">',
        /* [code]   */ '<bbcode_code>',
        /* [quote]  */ '<bbcode_quote>',
        /* [search] */ '<bbcode_search>',
    );

    // XML alternatives for closing BBCode tsgs.
    $xml_close_tags = array
    (
        /* [/b]      */ '</bbcode_b>',
        /* [/i]      */ '</bbcode_i>',
        /* [/u]      */ '</bbcode_u>',
        /* [/s]      */ '</bbcode_s>',
        /* [/sub]    */ '</bbcode_sub>',
        /* [/sup]    */ '</bbcode_sup>',
        /* [/color]  */ '</bbcode_color>',
        /* [/size]   */ '</bbcode_size>',
        /* [/font]   */ '</bbcode_font>',
        /* [/align]  */ '</bbcode_align>',
        /* [/h1]     */ '</bbcode_h1>',
        /* [/h2]     */ '</bbcode_h2>',
        /* [/h3]     */ '</bbcode_h3>',
        /* [/h4]     */ '</bbcode_h4>',
        /* [/h5]     */ '</bbcode_h5>',
        /* [/h6]     */ '</bbcode_h6>',
        /* [/list]   */ '</bbcode_list>',
        /* [/ulist]  */ '</bbcode_ulist>',
        /* [/li]     */ '</bbcode_li>',
        /* [/url]    */ '</bbcode_url>',
        /* [/url]    */ '</bbcode_url>',
        /* [/mail]   */ '</bbcode_mail>',
        /* [/mail]   */ '</bbcode_mail>',
        /* [/code]   */ '</bbcode_code>',
        /* [/quote]  */ '</bbcode_quote>',
        /* [/search] */ '</bbcode_search>',
    );

    // If search mode is on, strip the delimiter and special PCRE characters.
    if (!is_null($search))
    {
        $search = preg_quote($search, '!');
    }

    // Put zero byte before and after each BBCode tag, as a tag border.
    $bbcode = preg_replace($bbcode_open_tags,  "\0\$1\0", $bbcode);
    $bbcode = preg_replace($bbcode_close_tags, "\0\$1\0", $bbcode);

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
        foreach ($bbcode_open_tags as $j => $tag)
        {
            if (($is_tag = preg_match($tag, $str)))
            {
                array_push($stack, $bbcode_close_tags[$j]);
                break;
            }
        }

        // If still not is tag, then it's definitely not an *opening* BBCode tag.
        // Check whether the piece is closing BBCode tag.
        if (!$is_tag)
        {
            foreach ($bbcode_close_tags as $j => $tag)
            {
                if (($is_tag = preg_match($tag, $str)))
                {
                    $is_closed = FALSE;

                    // Close all previous tags, remained unclosed.
                    while (count($stack) > 0 && !$is_closed)
                    {
                        $k = array_pop($stack);

                        if ($k == $tag)
                        {
                            $is_closed = TRUE;
                        }
                        else
                        {
                            // Add missing closing tag.
                            $close_tag = preg_replace('!(\!\(\\\\\[/(.*)\\\]\)\!isu)!isu', '[/$2]', $k);
                            $text[$i] = $close_tag . $text[$i];
                        }
                    }

                    // If still not closed, then corresponding opening tag was missed.
                    if (!$is_closed)
                    {
                        // Remove current tag.
                        $text[$i] = ustrcut($text[$i], ustrlen($text[$i]) - ustrlen($str), FALSE);
                    }

                    break;
                }
            }
        }

        // If still not is tag, then it's definitely user's text between two BBCode tags.
        if (!$is_tag)
        {
            // If search mode is on, add "[search]" tags for all corresponding matches.
            if (!is_null($search))
            {
                $text[$i] = preg_replace("!({$search})!isu", '[search]$1[/search]', $text[$i]);
            }
        }
    }

    // Close all tags, remained unclosed.
    while (count($stack) > 0)
    {
        $k = array_pop($stack);

        // Add missing closing tag.
        $close_tag = preg_replace('!(\!\(\\\\\[/(.*)\\\]\)\!isu)!isu', '[/$2]', $k);
        array_push($text, $close_tag);
    }

    // Merge the array into solid block of text.
    $bbcode = implode(NULL, $text);

    // Remove extra newline characters before and after some tags.
    $no_extra_newlines_before = array('[/code]', '[/quote]', '[/list]', '[/ulist]', '[li]', '[/li]');
    $no_extra_newlines_after  = array('[code]',  '[quote]',  '[list]',  '[ulist]',  '[li]', '[/li]');

    foreach ($no_extra_newlines_before as $tag)
    {
        $bbcode = ustr_replace('%br;' . $tag, $tag, $bbcode);
    }

    foreach ($no_extra_newlines_after as $tag)
    {
        $bbcode = ustr_replace($tag . '%br;', $tag, $bbcode);
    }

    // Encode existing HTML special characters.
    $bbcode = htmlspecialchars($bbcode, ENT_COMPAT, 'UTF-8');

    // Convert BBCode tags into XML ones.
    $bbcode = preg_replace($bbcode_open_tags,  $xml_open_tags,  $bbcode);
    $bbcode = preg_replace($bbcode_close_tags, $xml_close_tags, $bbcode);

    // XSLTs for different BBCode modes.
    $bbcode_xsl = array
    (
        BBCODE_OFF         => 'bbcode_off.xsl',
        BBCODE_SEARCH_ONLY => 'bbcode_search.xsl',
        BBCODE_MINIMUM     => 'bbcode_minimum.xsl',
        BBCODE_ALL         => 'bbcode_all.xsl',
    );

    // Transform resulted XML into DOM document.
    $page = new DOMDocument();
    $xslt = new XSLTProcessor();

    $page->load(LOCALROOT . 'engine/' . $bbcode_xsl[$mode]);
    $xslt->importStyleSheet($page);
    $page->loadXML("<bbcode>{$bbcode}</bbcode>");

    $dom = $xslt->transformToDoc($page);

    // Remove XML headers from DOM document.
    $root   = $dom->getElementsByTagName('bbcode');
    $bbcode = $dom->saveXML($root->item(0));

    // Decode back all existing HTML special characters, encoded before.
    $bbcode = htmlspecialchars_decode($bbcode, ENT_COMPAT);

    return $bbcode;
}

?>
