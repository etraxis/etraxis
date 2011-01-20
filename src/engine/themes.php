<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2010  Artem Rodygin
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
 * Themes
 *
 * This module implements Themes support of eTraxis UI.
 *
 * @package Engine
 * @subpackage Themes
 * @author Mark Brockmann
 */

/**#@+
 * Dependency.
 */
require_once('../engine/resource.php');
require_once('../engine/config.php');
require_once('../engine/debug.php');
require_once('../engine/utility.php');
require_once('../engine/sessions.php');
/**#@-*/

//------------------------------------------------------------------------------
//  Definitions.
//------------------------------------------------------------------------------

/**#@+
 * Data restriction.
 */
define('MAX_THEME_NAME', 50);

/**#@+
 * Name of basic theme.
 */
define('DEF_THEME_NAME', 'Emerald');

//------------------------------------------------------------------------------
//  Functions.
//------------------------------------------------------------------------------

/**
 * Returns array of available themes sorted alphabetically.
 *
 * @return array Array with available themes.
 */
function get_available_themes_sorted ()
{
    debug_write_log(DEBUG_TRACE, '[get_available_themes_sorted]');

    $available_themes = array();

    foreach (array_diff(scandir('../themes/'), array ('.', '..')) as $item)
    {
        if (is_dir("../themes/{$item}"))
        {
            debug_write_log(DEBUG_DUMP, '[get_available_themes_sorted] $item = ' . $item);
            $available_themes[] = $item;
        }
    }

    asort($available_themes);

    return $available_themes;
}

/**
 * Returns the file path for the given css file.
 *
 * @return string Path to css file.
 */
function get_theme_css_file ($cssfile)
{
    if (isset($_SESSION[VAR_THEME_NAME]))
    {
        if (is_file(LOCALROOT . 'themes/' . ustr2html($_SESSION[VAR_THEME_NAME]) . '/css/' . $cssfile))
        {
            return LOCALROOT . 'themes/' . ustr2html($_SESSION[VAR_THEME_NAME]) . '/css/' . $cssfile;
        }
    }

    if (is_file(LOCALROOT . 'themes/' . ustr2html(THEME_DEFAULT) . '/css/' . $cssfile))
    {
        return LOCALROOT . 'themes/' . ustr2html(THEME_DEFAULT) . '/css/' . $cssfile;
    }

    if (is_file(LOCALROOT . 'themes/' . DEF_THEME_NAME . '/css/' . $cssfile))
    {
        return LOCALROOT . 'themes/' . DEF_THEME_NAME . '/css/' . $cssfile;
    }

    return NULL;
}

/**
 * Returns the file path for the given xsl file.
 *
 * @return string Path to xsl file.
 */
function get_theme_xsl_file ($xslfile)
{
    debug_write_log(DEBUG_TRACE, '[get_theme_xsl_file]');

    if (isset($_SESSION[VAR_THEME_NAME]))
    {
        debug_write_log(DEBUG_DUMP,  '[get_theme_xsl_file] $_SESSION[VAR_THEME_NAME] = ' . $_SESSION[VAR_THEME_NAME]);

        if (is_file(LOCALROOT . 'themes/' . ustr2html($_SESSION[VAR_THEME_NAME]) . '/' . $xslfile))
        {
            return LOCALROOT . 'themes/' . ustr2html($_SESSION[VAR_THEME_NAME]) . '/' . $xslfile;
        }
    }

    if (is_file(LOCALROOT . 'themes/' . ustr2html(THEME_DEFAULT) . '/' . $xslfile))
    {
        return LOCALROOT . 'themes/' . ustr2html(THEME_DEFAULT) . '/' . $xslfile;
    }

    if (is_file(LOCALROOT . 'themes/' . DEF_THEME_NAME . '/' . $xslfile))
    {
        return LOCALROOT . 'themes/' . DEF_THEME_NAME . '/' . $xslfile;
    }

    debug_write_log(DEBUG_WARNING, '[get_theme_xsl_file] Valid filepath for xsl file "' . $xslfile . '" is not found.');

    return LOCALROOT . 'engine/' . $xslfile;
}

?>
