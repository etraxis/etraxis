<?php

/**
 * Charts
 *
 * This module implements graphical charts and diagrams.
 *
 * @package Engine
 * @subpackage Charts
 */

//--------------------------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system.
//  Copyright (C) 2005-2009 by Artem Rodygin
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
//  Artem Rodygin           2005-08-10      new-008: Predefined metrics.
//  Artem Rodygin           2005-08-23      bug-052: PHP Warning: Division by zero
//  Artem Rodygin           2005-08-28      bug-033: Titles in metrics charts are not readable when Russian is set.
//  Artem Rodygin           2005-08-29      new-065: Minimum chart size.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-09-04      bug-077: Zero metrics are generated if project is just created but already contains some records.
//  Artem Rodygin           2005-09-22      new-141: Source code review.
//  Artem Rodygin           2005-10-05      bug-152: Right markers of metrics charts are shifted down.
//  Artem Rodygin           2006-03-20      bug-220: Metrics charts are out of borders.
//  Artem Rodygin           2006-10-08      bug-337: /src/engine/charts.php: $item is passed by reference without being modified.
//  Artem Rodygin           2006-10-08      bug-339: /src/engine/charts.php: Use of deprecated call-time pass-by-reference.
//  Artem Rodygin           2006-10-17      bug-362: Metrics chart tags are overlapped.
//  Artem Rodygin           2006-11-04      new-369: Charts: incision should not be present when marker text is absent.
//  Artem Rodygin           2007-07-14      new-545: Chart legend is required.
//  Artem Rodygin           2007-12-25      bug-653: Chart legend shows no color.
//  Artem Rodygin           2008-12-19      bug-779: Chart displays "1.2E+6" instead of "1200000".
//  Artem Rodygin           2009-03-11      bug-799: eTraxis doesn't work with XAMPP on Windows.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/debug.php');
require_once('../engine/locale.php');
/**#@-*/

//--------------------------------------------------------------------------------------------------
//  Definitions.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Minimum chart size.
 */
define('MIN_CHART_WIDTH',  600);
define('MIN_CHART_HEIGHT', 300);
/**#@-*/

/**#@+
 * Font size.
 */
define('FONT_SIZE_TINY',   1);
define('FONT_SIZE_SMALL',  2);
define('FONT_SIZE_MEDIUM', 3);
define('FONT_SIZE_LARGE',  4);
define('FONT_SIZE_GIANT',  5);
/**#@-*/

/**#@+
 * Predefined color.
 */
define('COLOR_ALICE_BLUE',              0xF0F8FF);
define('COLOR_ANTIQUE_WHITE',           0xFAEBD7);
define('COLOR_AQUA',                    0x00FFFF);
define('COLOR_AQUAMARINE',              0x7FFFD4);
define('COLOR_AZURE',                   0xF0FFFF);
define('COLOR_BEIGE',                   0xF5F5DC);
define('COLOR_BISQUE',                  0xFFE4C4);
define('COLOR_BLACK',                   0x000000);
define('COLOR_BLANCHED_ALMOND',         0xFFEBCD);
define('COLOR_BLUE',                    0x0000FF);
define('COLOR_BLUE_VIOLET',             0x8A2BE2);
define('COLOR_BROWN',                   0xA52A2A);
define('COLOR_BURLY_WOOD',              0xDEB887);
define('COLOR_CADET_BLUE',              0x5F9EA0);
define('COLOR_CHARTREUSE',              0x7FFF00);
define('COLOR_CHOCOLATE',               0xD2691E);
define('COLOR_CORAL',                   0xFF7F50);
define('COLOR_CORNFLOWER_BLUE',         0x6495ED);
define('COLOR_CORNSILK',                0xFFF8DC);
define('COLOR_CRIMSON',                 0xDC143C);
define('COLOR_CYAN',                    0x00FFFF);
define('COLOR_DARK_BLUE',               0x00008B);
define('COLOR_DARK_CYAN',               0x008B8B);
define('COLOR_DARK_GOLDEN_ROD',         0xB8860B);
define('COLOR_DARK_GREY',               0xA9A9A9);
define('COLOR_DARK_GREEN',              0x006400);
define('COLOR_DARK_KHAKI',              0xBDB76B);
define('COLOR_DARK_MAGENTA',            0x8B008B);
define('COLOR_DARK_OLIVE_GREEN',        0x556B2F);
define('COLOR_DARK_ORANGE',             0xFF8C00);
define('COLOR_DARK_ORCHID',             0x9932CC);
define('COLOR_DARK_RED',                0x8B0000);
define('COLOR_DARK_SALMON',             0xE9967A);
define('COLOR_DARK_SEA_GREEN',          0x8FBC8F);
define('COLOR_DARK_SLATE_BLUE',         0x483D8B);
define('COLOR_DARK_SLATE_GREY',         0x2F4F4F);
define('COLOR_DARK_TURQUOISE',          0x00CED1);
define('COLOR_DARK_VIOLET',             0x9400D3);
define('COLOR_DEEP_PINK',               0xFF1493);
define('COLOR_DEEP_SKY_BLUE',           0x00BFFF);
define('COLOR_DIM_GREY',                0x696969);
define('COLOR_DODGER_BLUE',             0x1E90FF);
define('COLOR_FIRE_BRICK',              0xB22222);
define('COLOR_FLORAL_WHITE',            0xFFFAF0);
define('COLOR_FOREST_GREEN',            0x228B22);
define('COLOR_FUCHSIA',                 0xFF00FF);
define('COLOR_GAINSBORO',               0xDCDCDC);
define('COLOR_GHOST_WHITE',             0xF8F8FF);
define('COLOR_GOLD',                    0xFFD700);
define('COLOR_GOLDEN_ROD',              0xDAA520);
define('COLOR_GREY',                    0x808080);
define('COLOR_GREEN',                   0x008000);
define('COLOR_GREEN_YELLOW',            0xADFF2F);
define('COLOR_HONEY_DEW',               0xF0FFF0);
define('COLOR_HOT_PINK',                0xFF69B4);
define('COLOR_INDIAN_RED',              0xCD5C5C);
define('COLOR_INDIGO',                  0x4B0082);
define('COLOR_IVORY',                   0xFFFFF0);
define('COLOR_KHAKI',                   0xF0E68C);
define('COLOR_LAVENDER',                0xE6E6FA);
define('COLOR_LAVENDER_BLUSH',          0xFFF0F5);
define('COLOR_LAWN_GREEN',              0x7CFC00);
define('COLOR_LEMON_CHIFFON',           0xFFFACD);
define('COLOR_LIGHT_BLUE',              0xADD8E6);
define('COLOR_LIGHT_CORAL',             0xF08080);
define('COLOR_LIGHT_CYAN',              0xE0FFFF);
define('COLOR_LIGHT_GOLDEN_ROD_YELLOW', 0xFAFAD2);
define('COLOR_LIGHT_GREY',              0xD3D3D3);
define('COLOR_LIGHT_GREEN',             0x90EE90);
define('COLOR_LIGHT_PINK',              0xFFB6C1);
define('COLOR_LIGHT_SALMON',            0xFFA07A);
define('COLOR_LIGHT_SEA_GREEN',         0x20B2AA);
define('COLOR_LIGHT_SKY_BLUE',          0x87CEFA);
define('COLOR_LIGHT_SLATE_GREY',        0x778899);
define('COLOR_LIGHT_STEEL_BLUE',        0xB0C4DE);
define('COLOR_LIGHT_YELLOW',            0xFFFFE0);
define('COLOR_LIME',                    0x00FF00);
define('COLOR_LIME_GREEN',              0x32CD32);
define('COLOR_LINEN',                   0xFAF0E6);
define('COLOR_MAGENTA',                 0xFF00FF);
define('COLOR_MAROON',                  0x800000);
define('COLOR_MEDIUM_AQUA_MARINE',      0x66CDAA);
define('COLOR_MEDIUM_BLUE',             0x0000CD);
define('COLOR_MEDIUM_ORCHID',           0xBA55D3);
define('COLOR_MEDIUM_PURPLE',           0x9370D8);
define('COLOR_MEDIUM_SEA_GREEN',        0x3CB371);
define('COLOR_MEDIUM_SLATE_BLUE',       0x7B68EE);
define('COLOR_MEDIUM_SPRING_GREEN',     0x00FA9A);
define('COLOR_MEDIUM_TURQUOISE',        0x48D1CC);
define('COLOR_MEDIUM_VIOLET_RED',       0xC71585);
define('COLOR_MIDNIGHT_BLUE',           0x191970);
define('COLOR_MINT_CREAM',              0xF5FFFA);
define('COLOR_MISTY_ROSE',              0xFFE4E1);
define('COLOR_MOCCASIN',                0xFFE4B5);
define('COLOR_NAVAJO_WHITE',            0xFFDEAD);
define('COLOR_NAVY',                    0x000080);
define('COLOR_OLD_LACE',                0xFDF5E6);
define('COLOR_OLIVE',                   0x808000);
define('COLOR_OLIVE_DRAB',              0x6B8E23);
define('COLOR_ORANGE',                  0xFFA500);
define('COLOR_ORANGE_RED',              0xFF4500);
define('COLOR_ORCHID',                  0xDA70D6);
define('COLOR_PALE_GOLDEN_ROD',         0xEEE8AA);
define('COLOR_PALE_GREEN',              0x98FB98);
define('COLOR_PALE_TURQUOISE',          0xAFEEEE);
define('COLOR_PALE_VIOLET_RED',         0xD87093);
define('COLOR_PAPAYA_WHIP',             0xFFEFD5);
define('COLOR_PEACH_PUFF',              0xFFDAB9);
define('COLOR_PERU',                    0xCD853F);
define('COLOR_PINK',                    0xFFC0CB);
define('COLOR_PLUM',                    0xDDA0DD);
define('COLOR_POWDER_BLUE',             0xB0E0E6);
define('COLOR_PURPLE',                  0x800080);
define('COLOR_RED',                     0xFF0000);
define('COLOR_ROSY_BROWN',              0xBC8F8F);
define('COLOR_ROYAL_BLUE',              0x4169E1);
define('COLOR_SADDLE_BROWN',            0x8B4513);
define('COLOR_SALMON',                  0xFA8072);
define('COLOR_SANDY_BROWN',             0xF4A460);
define('COLOR_SEA_GREEN',               0x2E8B57);
define('COLOR_SEA_SHELL',               0xFFF5EE);
define('COLOR_SIENNA',                  0xA0522D);
define('COLOR_SILVER',                  0xC0C0C0);
define('COLOR_SKY_BLUE',                0x87CEEB);
define('COLOR_SLATE_BLUE',              0x6A5ACD);
define('COLOR_SLATE_GREY',              0x708090);
define('COLOR_SNOW',                    0xFFFAFA);
define('COLOR_SPRING_GREEN',            0x00FF7F);
define('COLOR_STEEL_BLUE',              0x4682B4);
define('COLOR_TAN',                     0xD2B48C);
define('COLOR_TEAL',                    0x008080);
define('COLOR_THISTLE',                 0xD8BFD8);
define('COLOR_TOMATO',                  0xFF6347);
define('COLOR_TURQUOISE',               0x40E0D0);
define('COLOR_VIOLET',                  0xEE82EE);
define('COLOR_WHEAT',                   0xF5DEB3);
define('COLOR_WHITE',                   0xFFFFFF);
define('COLOR_WHITE_SMOKE',             0xF5F5F5);
define('COLOR_YELLOW',                  0xFFFF00);
define('COLOR_YELLOW_GREEN',            0x9ACD32);
/**#@-*/

//--------------------------------------------------------------------------------------------------
//  Functions.
//--------------------------------------------------------------------------------------------------

/**
 * Finds length of the longest string in sepcified array.
 * @access private
 * @param array $array Array of string values.
 * @return int String length.
 */
function maxlen ($array)
{
    $maxlen = 0;

    foreach ($array as $value)
    {
        $maxlen = max($maxlen, ustrlen($value));
    }

    return $maxlen;
}

/**
 * Loads GDF font from "fonts" directory of eTraxis, related to encoding of current user's language.
 * @access private
 * @param int $fontsize Font size. Possible values are:
 * <ul>
 * <li>{@link FONT_SIZE_TINY}</li>
 * <li>{@link FONT_SIZE_SMALL}</li>
 * <li>{@link FONT_SIZE_MEDIUM}</li>
 * <li>{@link FONT_SIZE_LARGE}</li>
 * <li>{@link FONT_SIZE_GIANT}</li>
 * </ul>
 * @return int Font identifier on success, FALSE otherwise.
 */
function loadfont ($fontsize)
{
    global $locale_info;

    $lang = (isset($_SESSION[VAR_LOCALE]) ? $_SESSION[VAR_LOCALE] : LANG_DEFAULT);

    switch ($fontsize)
    {
        case FONT_SIZE_TINY:
            return imageloadfont("../fonts/{$locale_info[$lang][LOCALE_PATH2FONTS]}/tiny.gdf");
        case FONT_SIZE_SMALL:
            return imageloadfont("../fonts/{$locale_info[$lang][LOCALE_PATH2FONTS]}/small.gdf");
        case FONT_SIZE_MEDIUM:
            return imageloadfont("../fonts/{$locale_info[$lang][LOCALE_PATH2FONTS]}/medium.gdf");
        case FONT_SIZE_LARGE:
            return imageloadfont("../fonts/{$locale_info[$lang][LOCALE_PATH2FONTS]}/large.gdf");
        case FONT_SIZE_GIANT:
            return imageloadfont("../fonts/{$locale_info[$lang][LOCALE_PATH2FONTS]}/giant.gdf");
        default:
            return FALSE;
    }
}

//--------------------------------------------------------------------------------------------------
//  Classes.
//--------------------------------------------------------------------------------------------------

/**
 * For internal use only.
 *
 * @package Engine
 * @subpackage Charts
 * @access private
 */
class CColor
{
    public $color;

    function CColor ($image, $rgb)
    {
        $red   = ($rgb & 0xFF0000) >> 16;
        $green = ($rgb & 0x00FF00) >> 8;
        $blue  = ($rgb & 0x0000FF);

        $this->color = imagecolorallocate($image, $red, $green, $blue);
    }
}

/**
 * For internal use only.
 *
 * @package Engine
 * @subpackage Charts
 * @access private
 */
class CMargin
{
    public $top;
    public $bottom;
    public $left;
    public $right;

    function CMargin ($top = 0, $bottom = 0, $left = 0, $right = 0)
    {
        $this->top    = $top;
        $this->bottom = $bottom;
        $this->left   = $left;
        $this->right  = $right;
    }
}

/**
 * Legend of chart.
 *
 * @package Engine
 * @subpackage Charts
 */
class CLegend
{
    /**
     * Array of string values.
     * @var array
     */
    public $markers;

    /**
     * Array of colors.
     *
     * Each color is integer, e.g. 0xFF0080.
     * @var array
     */
    public $markers_color;

    /**#@+
     * For internal use only.
     * @access private
     */
    public $markers_font;
    public $markers_margin;
    /**#@-*/

    /**
     * Constructor.
     *
     * @return CLegend
     */
    function CLegend ()
    {
        $this->markers        = array();
        $this->markers_color  = array();
        $this->markers_font   = loadfont(FONT_SIZE_SMALL);
        $this->markers_margin = new CMargin(2, 2, 2, 2);
    }
}

/**
 * Axis of the chart.
 *
 * @package Engine
 * @subpackage Charts
 */
class CAxis
{
    /**
     * Array of string values.
     * @var array
     */
    public $markers;

    /**
     * TRUE if text must be displayed in vertical, FALSE otherwise.
     * @var bool
     */
    public $rotate_text;

    /**#@+
     * For internal use only.
     * @access private
     */
    public $title;
    public $title_font;
    public $title_margin;
    public $title_width;
    public $title_height;
    public $markers_font;
    public $markers_margin;
    public $markers_width;
    public $markers_height;
    public $min_value;
    public $max_value;
    public $step_value;
    public $cell_width;
    /**#@-*/

    /**
     * Constructor.
     *
     * @param string $title Title of the axis.
     * @return CAxis
     */
    function CAxis ($title = NULL)
    {
        $this->title          = $title;
        $this->title_font     = loadfont(FONT_SIZE_MEDIUM);
        $this->title_margin   = new CMargin(5, 5, 5, 5);
        $this->title_width    = 0;
        $this->title_height   = 0;
        $this->markers        = array();
        $this->markers_font   = loadfont(FONT_SIZE_SMALL);
        $this->markers_margin = new CMargin(2, 2, 2, 2);
        $this->markers_width  = 0;
        $this->markers_height = 0;
        $this->rotate_text    = FALSE;
        $this->min_value      = NULL;
        $this->max_value      = NULL;
        $this->step_value     = 1;
        $this->cell_width     = imagefontheight($this->markers_font);
    }

    /**
     * Initializes axis.
     *
     * Taking in account specified float <i>data</i>, initializes axis, like:
     * <ul>
     * <li>what are the minimum and maximum values should be available on the axis</li>
     * <li>what is the best size of one cell</li>
     * <li>what are the markers of axis cells</li>
     * <li>will markers be displayed in vertical or horizontal</li>
     * <li>etc.</li>
     * </ul>
     * If you are going to draw several lines, you should call this function for each data array.
     *
     * @param array $data Array of float values, that will be displayed in the chart on this axis.
     * @param float $step_value Optional size of axis cell.
     */
    function init ($data, $step_value = NULL)
    {
        debug_write_log(DEBUG_TRACE, '[CAxis::init]');

        if (is_null($this->min_value))
        {
            $this->min_value = max($data);
        }

        foreach ($data as $value)
        {
            if (!is_null($value) && $this->min_value > $value)
            {
                $this->min_value = $value;
            }
        }

        $this->max_value = max($this->max_value, max($data));

        if (is_null($step_value))
        {
            $this->step_value = ceil(($this->max_value - $this->min_value) / 20);

            $digit = intval(substr($this->step_value, 0, 1));

            switch ($digit)
            {
                case 3:
                case 4:
                    $digit = 5;
                    break;
                case 6:
                case 7:
                case 8:
                case 9:
                    $digit = 10;
                    break;
            }

            $this->step_value = intval($digit . str_pad('', ustrlen($this->step_value) - 1, '0', STR_PAD_RIGHT));
        }
        else
        {
            $this->step_value = $step_value;
        }

        $this->min_value = floor($this->min_value / max($this->step_value, 1)) * $this->step_value;
        $this->max_value = ceil($this->max_value / max($this->step_value, 1)) * $this->step_value;

        if ($this->step_value != 0)
        {
            $this->markers = array();

            for ($i = $this->min_value; $i <= $this->max_value; $i += $this->step_value)
            {
                array_push($this->markers, intval($i));
            }
        }
    }
}

/**
 * Graphical chart.
 *
 * Allows to draw a graphical chart with custom values and axis markers.
 * <br/><br/>
 * <b>Example of usage</b><br/><br/>
 * Let's assume, we have to draw a chart for following data:
 * <pre>
 * Average climate data for Vladivostok
 * (monthly, Celsius)
 * ______________________
 *  Month | Night | Day
 *      1 | -16.3 | -8.8
 *      2 | -13.7 | -5.9
 *      3 |  -5.6 |  1.7
 *      4 |   1.3 |  9.1
 *      5 |   6.4 | 14.7
 *      6 |  10.6 | 17.0
 *      7 |  15.4 | 21.0
 *      8 |  17.4 | 23.0
 *      9 |  12.5 | 19.1
 *     10 |   5.2 | 12.4
 *     11 |  -4.2 |  2.8
 *     12 | -12.5 | -5.5
 * </pre>
 * The code for that could be like following:
 * <code>
 * require_once("../engine/charts.php");
 *
 * $month = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12);
 *
 * $night = array(-16.3, -13.7, -5.6, 1.3, 6.4, 10.6, 15.4, 17.4, 12.5, 5.2, -4.2, -12.5);
 * $day   = array(-8.8, -5.9, 1.7, 9.1, 14.7, 17.0, 21.0, 23.0, 19.1, 12.4, 2.8, -5.5);
 *
 * $chart = new CChart("Average temperature");
 *
 * $chart->x_axis = new CAxis("Month");
 * $chart->y_axis = new CAxis("Celsius");
 *
 * $chart->legend                = new CLegend();
 * $chart->legend->markers       = array("night", "day");
 * $chart->legend->markers_color = array(COLOR_BLUE, COLOR_ORANGE);
 *
 * $chart->x_axis->rotate_text = TRUE;
 * $chart->x_axis->markers = array(
 *     "January", "February", "March", "April",
 *     "May", "June", "July", "August",
 *     "September", "October", "November", "December");
 *
 * $chart->y_axis->init($night);
 * $chart->y_axis->init($day);
 *
 * $chart->init();
 * $chart->drawbase();
 *
 * $chart->drawline($month, $night, COLOR_BLUE);
 * $chart->drawline($month, $day,   COLOR_ORANGE);
 *
 * header("Content-type: image/png");
 * imagepng($chart->image);
 * imagedestroy($chart->image);
 * </code>
 * The code above will generate {@link http://www.etraxis.org/images/cchart.png this PNG file}.
 *
 * @package Engine
 * @subpackage Charts
 */
class CChart
{
    /**
     * Horizontal axis of the chart.
     * @var CAxis
     */
    public $x_axis;

    /**
     * Vertical axis of the chart.
     * @var CAxis
     */
    public $y_axis;

    /**
     * Legend of the chart.
     * @var CLegend
     */
    public $legend;

    /**
     * Generated image resource, that could be used then with standard {@link http://www.php.net/image PHP Image Functions} (e.g. {@link http://www.php.net/imagepng imagepng}).
     * @var resource
     */
    public $image;

    /**#@+
     * For internal use only.
     * @access private
     */
    public $margin;
    public $title;
    public $title_font;
    public $title_margin;
    public $title_width;
    public $title_height;
    public $x_offset;
    public $y_offset;
    public $image_width;
    public $image_height;
    public $chart_width;
    public $chart_height;
    public $legend_x;
    public $legend_y;
    public $legend_width;
    public $legend_height;
    /**#@-*/

    /**
     * Constructor.
     *
     * @param string $title Title of the chart.
     * @return CChart
     */
    function CChart ($title = NULL)
    {
        $this->image         = NULL;
        $this->margin        = new CMargin(10, 10, 10, 10);
        $this->title         = $title;
        $this->title_font    = loadfont(FONT_SIZE_GIANT);
        $this->title_margin  = new CMargin(10, 10, 10, 10);
        $this->title_width   = 0;
        $this->title_height  = 0;
        $this->x_axis        = NULL;
        $this->y_axis        = NULL;
        $this->x_offset      = 0;
        $this->y_offset      = 0;
        $this->image_width   = 0;
        $this->image_height  = 0;
        $this->chart_width   = 0;
        $this->chart_height  = 0;
        $this->legend        = NULL;
        $this->legend_x      = 0;
        $this->legend_y      = 0;
        $this->legend_width  = 0;
        $this->legend_height = 0;
    }

    /**
     * Initializes chart.
     *
     * Taking in account preliminary initialized axis, initializes chart, like what are the width and height of the chart, and so on.
     * You have to call {@link CAxis::init()} function before.
     */
    function init ()
    {
        debug_write_log(DEBUG_TRACE, '[CChart::init]');

        $this->title_width =
            $this->title_margin->left  +
            $this->title_margin->right +
            imagefontwidth($this->title_font) * ustrlen($this->title);

        $this->title_height =
            $this->title_margin->top    +
            $this->title_margin->bottom +
            imagefontheight($this->title_font);

        $this->y_axis->title_width =
            $this->y_axis->title_margin->left  +
            $this->y_axis->title_margin->right +
            imagefontheight($this->y_axis->title_font);

        $this->y_axis->title_height =
            $this->y_axis->title_margin->top    +
            $this->y_axis->title_margin->bottom +
            imagefontwidth($this->y_axis->title_font) * ustrlen($this->y_axis->title);

        $this->y_axis->markers_width =
            $this->y_axis->markers_margin->left  +
            $this->y_axis->markers_margin->right +
            imagefontwidth($this->y_axis->markers_font) * maxlen($this->y_axis->markers);

        $this->x_axis->title_width =
            $this->x_axis->title_margin->left  +
            $this->x_axis->title_margin->right +
            imagefontwidth($this->x_axis->title_font) * ustrlen($this->x_axis->title);

        $this->x_axis->title_height =
            $this->x_axis->title_margin->top    +
            $this->x_axis->title_margin->bottom +
            imagefontheight($this->x_axis->title_font);

        $this->x_axis->markers_height =
            $this->x_axis->markers_margin->top +
            $this->x_axis->markers_margin->bottom;

        if ($this->x_axis->rotate_text)
        {
            $this->x_axis->markers_height += imagefontwidth($this->x_axis->markers_font) * maxlen($this->x_axis->markers);
        }
        else
        {
            $this->x_axis->markers_height += imagefontheight($this->x_axis->markers_font);
        }

        $this->x_offset =
            $this->y_axis->title_width   +
            $this->y_axis->markers_width +
            $this->margin->left;

        $this->y_offset =
            $this->title_height +
            $this->margin->top;

        $this->chart_width = max(MIN_CHART_WIDTH,
                                 $this->title_width,
                                 $this->x_axis->title_width,
                                 $this->x_axis->cell_width * count($this->x_axis->markers));

        $this->chart_height = max(MIN_CHART_HEIGHT,
                                  $this->y_axis->title_height,
                                  $this->y_axis->cell_width * count($this->y_axis->markers));

        $this->image_width  = $this->x_offset + $this->margin->right  + $this->y_axis->markers_width;
        $this->image_height = $this->y_offset + $this->margin->bottom + $this->x_axis->markers_height + $this->x_axis->title_height;

        if (!is_null($this->legend))
        {
            debug_write_log(DEBUG_NOTICE, '[CChart::init] Legend data are found.');

            $this->legend_x = $this->x_offset;

            $this->legend_y =
                $this->y_offset               +
                $this->chart_height           +
                $this->x_axis->markers_height +
                $this->x_axis->title_height;

            $this->legend_width =
                $this->legend->markers_margin->left  +
                $this->legend->markers_margin->right +
                imagefontwidth($this->legend->markers_font) * (maxlen($this->legend->markers) + 2);

            $this->chart_width = $this->legend_width = max($this->chart_width, $this->legend_width);

            $this->legend_height = 0;

            $tmp = $this->legend_width + 1;

            foreach ($this->legend->markers as $marker)
            {
                if ($tmp > $this->legend_width)
                {
                    $this->legend_height += 1;

                    $tmp =
                        $this->legend->markers_margin->left +
                        $this->legend->markers_margin->right;
                }
                else
                {
                    $tmp += imagefontwidth($this->legend->markers_font);
                }

                $tmp +=
                    imagefontheight($this->legend->markers_font) +
                    imagefontwidth($this->legend->markers_font) * ustrlen($marker);
            }

            $this->legend_height *= imagefontheight($this->legend->markers_font);

            $this->legend_height +=
                $this->legend->markers_margin->top +
                $this->legend->markers_margin->bottom;

            $this->image_height += $this->legend_height;
        }

        $this->image_width  += $this->chart_width;
        $this->image_height += $this->chart_height;

        if ($this->x_axis->cell_width * count($this->x_axis->markers) < $this->chart_width)
        {
            debug_write_log(DEBUG_NOTICE, '[CChart::init] Expand X-axis cell width.');
            $this->x_axis->cell_width = $this->chart_width / max(count($this->x_axis->markers), 1);
        }

        if ($this->y_axis->cell_width * count($this->y_axis->markers) < $this->chart_height)
        {
            debug_write_log(DEBUG_NOTICE, '[CChart::init] Expand Y-axis cell width.');
            $this->y_axis->cell_width = $this->chart_height / max(count($this->y_axis->markers), 1);
        }

        debug_write_log(DEBUG_DUMP, '[CChart::init] $this->title_width            = ' . $this->title_width);
        debug_write_log(DEBUG_DUMP, '[CChart::init] $this->title_height           = ' . $this->title_height);
        debug_write_log(DEBUG_DUMP, '[CChart::init] $this->x_axis->title_width    = ' . $this->x_axis->title_width);
        debug_write_log(DEBUG_DUMP, '[CChart::init] $this->x_axis->title_height   = ' . $this->x_axis->title_height);
        debug_write_log(DEBUG_DUMP, '[CChart::init] $this->x_axis->markers_height = ' . $this->x_axis->markers_height);
        debug_write_log(DEBUG_DUMP, '[CChart::init] $this->x_axis->cell_width     = ' . $this->x_axis->cell_width);
        debug_write_log(DEBUG_DUMP, '[CChart::init] $this->y_axis->title_width    = ' . $this->y_axis->title_width);
        debug_write_log(DEBUG_DUMP, '[CChart::init] $this->y_axis->title_height   = ' . $this->y_axis->title_height);
        debug_write_log(DEBUG_DUMP, '[CChart::init] $this->y_axis->markers_width  = ' . $this->y_axis->markers_width);
        debug_write_log(DEBUG_DUMP, '[CChart::init] $this->y_axis->cell_width     = ' . $this->y_axis->cell_width);
        debug_write_log(DEBUG_DUMP, '[CChart::init] $this->x_offset               = ' . $this->x_offset);
        debug_write_log(DEBUG_DUMP, '[CChart::init] $this->y_offset               = ' . $this->y_offset);
        debug_write_log(DEBUG_DUMP, '[CChart::init] $this->chart_width            = ' . $this->chart_width);
        debug_write_log(DEBUG_DUMP, '[CChart::init] $this->chart_height           = ' . $this->chart_height);
        debug_write_log(DEBUG_DUMP, '[CChart::init] $this->image_width            = ' . $this->image_width);
        debug_write_log(DEBUG_DUMP, '[CChart::init] $this->image_height           = ' . $this->image_height);
        debug_write_log(DEBUG_DUMP, '[CChart::init] $this->legend_x               = ' . $this->legend_x);
        debug_write_log(DEBUG_DUMP, '[CChart::init] $this->legend_y               = ' . $this->legend_y);
        debug_write_log(DEBUG_DUMP, '[CChart::init] $this->legend_width           = ' . $this->legend_width);
        debug_write_log(DEBUG_DUMP, '[CChart::init] $this->legend_height          = ' . $this->legend_height);
    }

    /**
     * Draws chart base.
     *
     * The function draws canvas, lines, ceils, axes and their markers, titles, and so on.
     * You have to call {@link CChart::init()} function before.
     */
    function drawbase ()
    {
        debug_write_log(DEBUG_TRACE, '[CChart::drawbase]');

        $this->image = imagecreate($this->image_width + 1, $this->image_height + 1);

        $transparent = new CColor($this->image, 0x7F7F7F);
        imagecolortransparent($this->image, $transparent->color);

        imagefilledrectangle($this->image, 0, 0, $this->image_width, $this->image_height, $transparent->color);

        $white = new CColor($this->image, COLOR_WHITE);
        $black = new CColor($this->image, COLOR_NAVY);
        $lgrey = new CColor($this->image, COLOR_LIGHT_GREY);

        imagefilledrectangle($this->image,
                             $this->x_offset,
                             $this->y_offset,
                             $this->x_offset + $this->chart_width,
                             $this->y_offset + $this->chart_height,
                             $white->color);

        imagestring($this->image, $this->title_font,
                    ($this->image_width - $this->title_width) / 2,
                    $this->margin->top + $this->title_margin->top,
                    iconv('UTF-8', get_encoding(), $this->title), $black->color);

        imagestring($this->image, $this->x_axis->title_font,
                    ($this->chart_width - $this->x_axis->title_width) / 2 + $this->x_offset,
                    $this->y_offset + $this->chart_height + $this->x_axis->title_margin->top + $this->x_axis->markers_height,
                    iconv('UTF-8', get_encoding(), $this->x_axis->title), $black->color);

        foreach ($this->x_axis->markers as $i => $marker)
        {
            if (!is_null($marker))
            {
                if ($this->x_axis->rotate_text)
                {
                    imagestringup($this->image, $this->x_axis->markers_font,
                                  $this->x_offset + $this->x_axis->cell_width * $i - imagefontheight($this->x_axis->markers_font) / 2 + $this->x_axis->cell_width / 2,
                                  $this->y_offset + $this->chart_height + imagefontwidth($this->x_axis->markers_font) * ustrlen($marker) + $this->x_axis->markers_margin->top,
                                  iconv('UTF-8', get_encoding(), $marker), $black->color);
                }
                else
                {
                    imagestring($this->image, $this->x_axis->markers_font,
                                $this->x_offset + $this->x_axis->cell_width * $i - imagefontwidth($this->x_axis->markers_font) * ustrlen($marker) / 2 + $this->x_axis->cell_width / 2,
                                $this->y_offset + $this->chart_height + $this->x_axis->markers_margin->top,
                                iconv('UTF-8', get_encoding(), $marker), $black->color);
                }

                $x = $this->x_offset + $this->x_axis->cell_width * $i + $this->x_axis->cell_width / 2;
                $y = $this->y_offset + $this->chart_height;

                imageline($this->image, $x, $this->y_offset + 1, $x, $y - 1, $lgrey->color);

                if (ustrlen($marker))
                {
                    imageline($this->image, $x, $y, $x, $y + 1, $black->color);
                }
            }
        }

        imagestringup($this->image, $this->y_axis->title_font,
                      $this->margin->left + $this->y_axis->title_margin->left,
                      ($this->chart_height + $this->y_axis->title_height) / 2 + $this->y_offset,
                      iconv('UTF-8', get_encoding(), $this->y_axis->title), $black->color);

        foreach ($this->y_axis->markers as $i => $marker)
        {
            if (!is_null($marker))
            {
                $y = $this->y_offset + $this->chart_height - $this->y_axis->cell_width * $i - $this->y_axis->cell_width / 2;

                imagestring($this->image, $this->y_axis->markers_font,
                            $this->x_offset - imagefontwidth($this->y_axis->markers_font) * ustrlen($marker) - $this->y_axis->markers_margin->left,
                            $y - imagefontheight($this->y_axis->markers_font) / 2,
                            iconv('UTF-8', get_encoding(), $marker), $black->color);

                imagestring($this->image, $this->y_axis->markers_font,
                            $this->x_offset + $this->chart_width + $this->y_axis->markers_margin->left + 2,
                            $y - imagefontheight($this->y_axis->markers_font) / 2,
                            iconv('UTF-8', get_encoding(), $marker), $black->color);

                imageline($this->image, $this->x_offset, $y, $this->x_offset + $this->chart_width, $y, $lgrey->color);

                if (ustrlen($marker))
                {
                    imageline($this->image, $this->x_offset + $this->chart_width, $y, $this->x_offset + $this->chart_width + 1, $y, $black->color);
                    imageline($this->image, $this->x_offset, $y, $this->x_offset - 1, $y, $black->color);
                }
            }
        }

        imagerectangle($this->image,
                       $this->x_offset,
                       $this->y_offset,
                       $this->x_offset + $this->chart_width,
                       $this->y_offset + $this->chart_height,
                       $black->color);

        if (!is_null($this->legend))
        {
            debug_write_log(DEBUG_NOTICE, '[CChart::drawbase] Drawing legend.');

            $x = $this->legend->markers_margin->left;
            $y = $this->legend->markers_margin->top;

            foreach ($this->legend->markers as $i => $marker)
            {
                $color = new CColor($this->image, $this->legend->markers_color[$i]);

                $strlen =
                    imagefontheight($this->legend->markers_font) +
                    imagefontwidth($this->legend->markers_font) * ustrlen($marker);

                if ($x + $strlen + $this->legend->markers_margin->right > $this->legend_width)
                {
                    $x = $this->legend->markers_margin->left;
                    $y += imagefontheight($this->legend->markers_font);
                }

                imagefilledrectangle($this->image,
                                     $this->legend_x + $x + 3,
                                     $this->legend_y + $y + 3,
                                     $this->legend_x + $x - 3 + imagefontheight($this->legend->markers_font),
                                     $this->legend_y + $y - 3 + imagefontheight($this->legend->markers_font),
                                     $color->color);

                imagerectangle($this->image,
                               $this->legend_x + $x + 3,
                               $this->legend_y + $y + 3,
                               $this->legend_x + $x - 3 + imagefontheight($this->legend->markers_font),
                               $this->legend_y + $y - 3 + imagefontheight($this->legend->markers_font),
                               $black->color);

                imagestring($this->image, $this->legend->markers_font,
                            $this->legend_x + $x + imagefontheight($this->legend->markers_font) + 1,
                            $this->legend_y + $y,
                            iconv('UTF-8', get_encoding(), $marker), $black->color);

                $x += $strlen + imagefontwidth($this->legend->markers_font);
            }
        }
    }

    /**
     * For internal use only.
     * @access private
     */
    function getpoint ($data_x, $data_y, $i)
    {
        if (is_null($data_x[$i]) || is_null($data_y[$i]))
        {
            return NULL;
        }

        if (is_null($this->x_axis->min_value) && count($data_x) != 0)
        {
            $this->x_axis->min_value = min($data_x);
        }

        if (is_null($this->y_axis->min_value) && count($data_y) != 0)
        {
            $this->y_axis->min_value = min($data_y);
        }

        $x = $this->x_offset + $this->x_axis->cell_width / 2 + ($data_x[$i] - $this->x_axis->min_value) / max($this->x_axis->step_value, 1) * $this->x_axis->cell_width;
        $y = $this->y_offset - $this->y_axis->cell_width / 2 - ($data_y[$i] - $this->y_axis->min_value) / max($this->y_axis->step_value, 1) * $this->y_axis->cell_width + $this->chart_height;

        return array($x, $y);
    }

    /**
     * Draws line on the chart, according to X and Y input data.
     *
     * You have to call {@link CChart::drawbase()} function before.
     *
     * @param array $data_x Data for horizontal axis (X).
     * @param array $data_y Data for vertical axis (Y).
     * @param int $line_rgb Color of line.
     * @param int $dots_rgb Color of dots on the line; if omitted, no dots will be drawn.
     */
    function drawline ($data_x, $data_y, $line_rgb = COLOR_BLACK, $dots_rgb = NULL)
    {
        debug_write_log(DEBUG_TRACE, '[CChart::drawline]');

        $line = new CColor($this->image, $line_rgb);
        $dots = is_null($dots_rgb) ? NULL : new CColor($this->image, $dots_rgb);

        $cx = $cy = NULL;
        $count = min(count($data_x), count($data_y));

        for ($i = 0; $i < $count; $i++)
        {
            $point = $this->getpoint($data_x, $data_y, $i);

            if (is_null($point))
            {
                continue;
            }

            if (!is_null($cx) && !is_null($cy))
            {
                imageline($this->image, $cx, $cy, $point[0], $point[1], $line->color);
            }

            if (is_null($dots))
            {
                if ($count == 1)
                {
                    imagefilledellipse($this->image, $point[0], $point[1], 5, 5, $line->color);
                }
            }
            else
            {
                imagefilledellipse($this->image, $point[0], $point[1], 5, 5, $dots->color);
            }

            $cx = $point[0];
            $cy = $point[1];
        }
    }
}

?>
