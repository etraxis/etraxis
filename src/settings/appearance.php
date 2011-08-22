<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2005-2011  Artem Rodygin
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
 * @package eTraxis
 * @ignore
 */

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/accounts.php');
/**#@-*/

init_page(LOAD_TAB);

// settings form is submitted

if (try_request('submitted') == 'appearanceform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $locale       = ustr2int($_REQUEST['locale']);
    $text_rows    = ustr2int($_REQUEST['text_rows'],    HTML_TEXTBOX_MIN_HEIGHT, HTML_TEXTBOX_MAX_HEIGHT);
    $page_rows    = ustr2int($_REQUEST['page_rows'],    MIN_PAGE_SIZE, MAX_PAGE_SIZE);
    $page_bkms    = ustr2int($_REQUEST['page_bkms'],    MIN_PAGE_SIZE, MAX_PAGE_SIZE);
    $auto_refresh = ustr2int($_REQUEST['auto_refresh'], MIN_AUTO_REFRESH, MAX_AUTO_REFRESH);
    $theme_name   = ustrcut($_REQUEST['theme_name'],    MAX_THEME_NAME);

    locale_change($_SESSION[VAR_USERID], $locale);

    dal_query('accounts/settings.sql',
              $_SESSION[VAR_USERID],
              $text_rows,
              $page_rows,
              $page_bkms,
              $auto_refresh,
              $theme_name);

    exit;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $locale       = $_SESSION[VAR_LOCALE];
    $text_rows    = $_SESSION[VAR_TEXTROWS];
    $page_rows    = $_SESSION[VAR_PAGEROWS];
    $page_bkms    = $_SESSION[VAR_PAGEBKMS];
    $auto_refresh = $_SESSION[VAR_AUTO_REFRESH];
    $theme_name   = $_SESSION[VAR_THEME_NAME];
}

// local JS functions

$resTitle    = get_js_resource(RES_SETTINGS_ID);
$resError    = get_js_resource(RES_ERROR_ID);
$resMessage1 = get_js_resource(RES_ALERT_SUCCESSFULLY_SAVED_ID);
$resMessage2 = get_js_resource(RES_ALERT_UNKNOWN_ERROR_ID);
$resOK       = get_js_resource(RES_OK_ID);

$xml = <<<JQUERY
<script>

function appearanceRefresh ()
{
    window.open("index.php", "_parent");
}

function appearanceSuccess ()
{
    jqAlert("{$resTitle}", "{$resMessage1}", "{$resOK}", "appearanceRefresh()");
}

function appearanceError ()
{
    jqAlert("{$resError}", "{$resMessage2}", "{$resOK}");
}

</script>
JQUERY;

// generate contents

$xml .= '<form name="appearanceform" action="appearance.php" success="appearanceSuccess" error="appearanceError">'
      . '<group>'
      . '<control name="locale">'
      . '<label>' . get_html_resource(RES_LANGUAGE_ID) . '</label>'
      . '<combobox>';

$supported_locales = get_supported_locales_sorted();

foreach ($supported_locales as $locale_id => $locale_name)
{
    $xml .= ($locale == $locale_id
                ? '<listitem value="' . $locale_id . '" selected="true">'
                : '<listitem value="' . $locale_id . '">')
          . ustr2html($locale_name)
          . '</listitem>';
}

$xml .= '</combobox>'
      . '</control>'
      . '<control name="text_rows" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
      . '<label>' . get_html_resource(RES_ROWS_MULTILINED_TEXT_ID) . '</label>'
      . '<editbox maxlen="' . ustrlen(HTML_TEXTBOX_MAX_HEIGHT) . '">' . ustr2html($text_rows) . '</editbox>'
      . '</control>'
      . '<control name="page_rows" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
      . '<label>' . get_html_resource(RES_ROWS_PER_PAGE_ID) . '</label>'
      . '<editbox maxlen="' . ustrlen(MAX_PAGE_SIZE) . '">' . ustr2html($page_rows) . '</editbox>'
      . '</control>'
      . '<control name="page_bkms" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
      . '<label>' . get_html_resource(RES_BOOKMARKS_PER_PAGE_ID) . '</label>'
      . '<editbox maxlen="' . ustrlen(MAX_PAGE_SIZE) . '">' . ustr2html($page_bkms) . '</editbox>'
      . '</control>'
      . '<control name="auto_refresh" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
      . '<label>' . get_html_resource(RES_AUTO_REFRESH_ID) . '</label>'
      . '<editbox maxlen="' . ustrlen(MAX_AUTO_REFRESH) . '">' . ustr2html($auto_refresh) . '</editbox>'
      . '</control>'
      . '<control name="theme_name">'
      . '<label>' . get_html_resource(RES_THEME_ID) . '</label>'
      . '<combobox>';

$themes_available = get_available_themes_sorted();

foreach ($themes_available as $item)
{
    $xml .= ($item == $theme_name
                ? '<listitem value="' . $item . '" selected="true">'
                : '<listitem value="' . $item . '">')
          . ($item == THEME_DEFAULT
                ? sprintf('%s (%s)', ustr2html($item), get_html_resource(RES_DEFAULT_ID))
                : ustr2html($item))
          . '</listitem>';
}

$xml .= '</combobox>'
      . '</control>'
      . '</group>'
      . '<button default="true">' . get_html_resource(RES_SAVE_ID) . '</button>'
      . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>'
      . '<note>' . ustrprocess(get_html_resource(RES_ALERT_FIELD_VALUE_OUT_OF_RANGE_ID), get_html_resource(RES_ROWS_MULTILINED_TEXT_ID), HTML_TEXTBOX_MIN_HEIGHT, HTML_TEXTBOX_MAX_HEIGHT) . '</note>'
      . '<note>' . ustrprocess(get_html_resource(RES_ALERT_FIELD_VALUE_OUT_OF_RANGE_ID), get_html_resource(RES_ROWS_PER_PAGE_ID), MIN_PAGE_SIZE, MAX_PAGE_SIZE) . '</note>'
      . '<note>' . ustrprocess(get_html_resource(RES_ALERT_FIELD_VALUE_OUT_OF_RANGE_ID), get_html_resource(RES_BOOKMARKS_PER_PAGE_ID), MIN_PAGE_SIZE, MAX_PAGE_SIZE) . '</note>'
      . '<note>' . ustrprocess(get_html_resource(RES_ALERT_FIELD_VALUE_OUT_OF_RANGE_ID), get_html_resource(RES_AUTO_REFRESH_ID), MIN_AUTO_REFRESH, MAX_AUTO_REFRESH) . '</note>'
      . '</form>';

echo(xml2html($xml));

?>
