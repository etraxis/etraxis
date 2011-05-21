<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2004-2011  Artem Rodygin
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
 * XML
 *
 * This module is responsible for XML/HTML generation of eTraxis pages.
 *
 * @package Engine
 * @subpackage XML
 */

/**#@+
 * Dependency.
 */
require_once('../engine/locale.php');
require_once('../engine/debug.php');
require_once('../engine/utility.php');
require_once('../engine/themes.php');
require_once('../engine/sessions.php');
require_once('../dbo/reminders.php');
/**#@-*/

//------------------------------------------------------------------------------
//  Definitions.
//------------------------------------------------------------------------------

/**
 * Version info.
 */
define('VERSION', '3.5.8');

/**
 * Number of lines in a <textarea> control.
 */
define('HTML_TEXTBOX_DEFAULT_HEIGHT', 8);
define('HTML_TEXTBOX_MIN_HEIGHT',     2);
define('HTML_TEXTBOX_MAX_HEIGHT',     100);

//------------------------------------------------------------------------------
//  Functions.
//------------------------------------------------------------------------------

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
        $xml .= '<bookmark url="reloadTab(\'' . $url . 'page=1\')">%lt;%lt;</bookmark>'
              . '<bookmark url="reloadTab(\'' . $url . 'page=' . ($nav_from - 1) . '\')">%lt;</bookmark>';
    }

    for ($i = $nav_from; $i <= $nav_to; $i++)
    {
        $xml .= '<bookmark url="reloadTab(\'' . $url . 'page=' . $i . '\')" active="' . ($i == $curr_page ? 'true' : 'false') . '">' . $i . '</bookmark>';
    }

    if ($nav_to < $nav_count)
    {
        $xml .= '<bookmark url="reloadTab(\'' . $url . 'page=' . ($nav_to + 1) . '\')">%gt;</bookmark>'
              . '<bookmark url="reloadTab(\'' . $url . 'page=' . $nav_count . '\')">%gt;%gt;</bookmark>';
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
 * @param string $title Title of page being generated (NULL is $xml contains only part of the page).
 * @param string $xsl XSLT-file (path is related to "engine" directory).
 * @return string Generated HTML code.
 */
function xml2html ($xml, $title = NULL, $xsl = 'engine.xsl')
{
    debug_write_log(DEBUG_TRACE, '[xml2html]');
    debug_write_log(DEBUG_DUMP,  '[xml2html] $title = ' . $title);

    // a page is being generated
    if (!is_null($title))
    {
        // generate page parameters

        $params = array('title'    => $title,
                        'version'  => ustrprocess(get_html_resource(RES_VERSION_X_ID), VERSION),
                        'username' => isset($_SESSION[VAR_FULLNAME]) ? ustr2html($_SESSION[VAR_FULLNAME]) : get_html_resource(RES_GUEST_ID),
                        'logout'   => get_html_resource(get_user_level() == USER_LEVEL_GUEST ? RES_LOGIN_ID : RES_LOGOUT_ID),
                        'search'   => get_html_resource(RES_SEARCH_ID));

        $script = '<script>'
                . 'function onLogoutButton()'
                . '{'
                . (get_user_level() == USER_LEVEL_GUEST
                      ? 'window.open("../logon/index.php", "_parent");'
                      : sprintf('jqConfirm("%s","%s","%s","%s","logout()")',
                                get_html_resource(RES_QUESTION_ID),
                                get_html_resource(RES_CONFIRM_LOGOUT_ID),
                                get_html_resource(RES_OK_ID),
                                get_html_resource(RES_CANCEL_ID)))
                . '}'
                . '</script>';

        if (MAINTENANCE_BANNER)
        {
            list($year, $month, $day) = explode('-', MAINTENANCE_START_DATE);
            list($hour, $minute)      = explode(':', MAINTENANCE_START_TIME);

            $date1 = mktime($hour, $minute, 0, $month, $day, $year);

            list($year, $month, $day) = explode('-', MAINTENANCE_FINISH_DATE);
            list($hour, $minute)      = explode(':', MAINTENANCE_FINISH_TIME);

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

            $params['banner'] = ustrprocess(get_html_resource(RES_BANNER_ID), get_datetime($date1), get_datetime($date2), 'GMT' . $timezone);
        }

        $header = '<?xml version="1.0" encoding="UTF-8"?>'
                . '<page';

        foreach ($params as $name => $value)
        {
            $header .= sprintf(' %s="%s"', $name, $value);
        }

        $header .= '>';

        $header .= '<css>../themes/css.php?name=jquery-ui.css</css>'
                 . '<css>../themes/css.php?name=jquery.jqplot.css</css>'
                 . '<css>../themes/css.php?name=etraxis.css</css>'
                 . '<css>../themes/css.php?name=list.css</css>'
                 . '<css>../themes/css.php?name=combobox.css</css>'
                 . '<css>../themes/css.php?name=buttons.css</css>';

        // generate main menu

        $menu = sprintf('<mainmenu logo="../%s" site="%s">', COMPANY_LOGO, COMPANY_SITE);

        if (get_user_level() == USER_LEVEL_GUEST)
        {
            $menu .= '<menuitem url="../records/index.php">'  . get_html_resource(RES_RECORDS_ID)  . '</menuitem>'
                   . '<menuitem url="../projects/index.php">' . get_html_resource(RES_PROJECTS_ID) . '</menuitem>';
        }
        else
        {
            $menu .= '<menuitem url="../records/index.php">' . get_html_resource(RES_RECORDS_ID) . '</menuitem>'
                   . '<menuitem url="../filters/index.php">' . get_html_resource(RES_FILTERS_ID) . '</menuitem>'
                   . '<menuitem url="../views/index.php">'   . get_html_resource(RES_VIEWS_ID)   . '</menuitem>';

            if (EMAIL_NOTIFICATIONS_ENABLED)
            {
                $menu .= '<menuitem url="../subscriptions/index.php">' . get_html_resource(RES_SUBSCRIPTIONS_ID) . '</menuitem>';

                if (can_reminder_be_created())
                {
                    $menu .= '<menuitem url="../reminders/index.php">' . get_html_resource(RES_REMINDERS_ID) . '</menuitem>';
                }
            }

            $menu .= '<menuitem url="../projects/index.php">' . get_html_resource(RES_PROJECTS_ID) . '</menuitem>';

            if (get_user_level() == USER_LEVEL_ADMIN)
            {

                $menu .= '<menuitem url="../accounts/index.php">' . get_html_resource(RES_ACCOUNTS_ID)      . '</menuitem>'
                       . '<menuitem url="../groups/index.php">'   . get_html_resource(RES_GLOBAL_GROUPS_ID) . '</menuitem>'
                       . '<menuitem url="../config/index.php">'   . get_html_resource(RES_CONFIGURATION_ID) . '</menuitem>';
            }

            $menu .= '<menuitem url="../settings/index.php">' . get_html_resource(RES_SETTINGS_ID) . '</menuitem>';
        }

        $menu .= '</mainmenu>';

        // select requested tab, if one was specified

        $tab = '<onready>'
             . '$("#tabs").tabs().tabs("select", ' . (try_request('tab', 1) - 1) . ');'
             . '</onready>';

        // join all pieces together

        $xml = $header
             . $script
             . $menu
             . $tab
             . $xml
             . '</page>';
    }
    // a part of page is being generated
    else
    {
        // generate parameters

        $params = array('msgboxTitle' => get_html_resource(RES_QUESTION_ID),
                        'btnOk'       => get_html_resource(RES_OK_ID),
                        'btnCancel'   => get_html_resource(RES_CANCEL_ID));

        $header = '<tab-content';

        foreach ($params as $name => $value)
        {
            $header .= sprintf(' %s="%s"', $name, $value);
        }

        $header .= '>';

        // join all pieces together

        $xml = $header
             . $xml
             . '</tab-content>';
    }

    // process resulted XML

    $page = new DOMDocument();
    $xslt = new XSLTProcessor();

    $page->load(get_theme_xsl_file($xsl));
    $xslt->importStyleSheet($page);
    $page->loadXML($xml);

    $html = $xslt->transformToXML($page);

    if (!$html)
    {
        debug_write_log(DEBUG_DUMP, '[xml2html] $xml = ' . $xml);
    }
    else
    {
        $html = str_replace('%br;', '<br/>', $html);

        // built-in compressions: check whether required extensions is available and PHP compression is turned off
        if (!extension_loaded('zlib') || ini_get('zlib.output_compression') || ini_get('output_handler'))
        {
            $html = str_replace('scripts/get.php?name=', 'scripts/', $html);
        }

        // workaround: some PHP configurations insert CDATA tags which break the output
        $html = preg_replace('/<!\[cdata\[(.*?)\]\]>/isu',     '$1', $html);
        $html = preg_replace('/%3C!\[cdata\[(.*?)\]\]%3E/isu', '$1', $html);

        mb_regex_encoding('UTF-8');

        $html = mb_eregi_replace('%([A-Za-z]+);',          '&\1;', $html);
        $html = mb_eregi_replace('%(#[0-9]{1,4});',        '&\1;', $html);
        $html = mb_eregi_replace('%(#x[0-9A-Fa-f]{1,4});', '&\1;', $html);
    }

    debug_write_log(DEBUG_PERFORMANCE, 'page size = ' . strlen($html));

    return $html;
}

?>
