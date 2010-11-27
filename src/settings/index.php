<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2005-2010  Artem Rodygin
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

init_page();

// settings form is submitted

if (try_request('submitted') == 'mainform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $locale       = ustr2int($_REQUEST['locale']);
    $page_rows    = ustr2int($_REQUEST['page_rows'], MIN_PAGE_SIZE, MAX_PAGE_SIZE);
    $page_bkms    = ustr2int($_REQUEST['page_bkms'], MIN_PAGE_SIZE, MAX_PAGE_SIZE);
    $theme_name   = ustrcut($_REQUEST['theme_name'], MAX_THEME_NAME);

    locale_change($_SESSION[VAR_USERID], $locale);

    dal_query('accounts/settings.sql',
              $_SESSION[VAR_USERID],
              $page_rows,
              $page_bkms,
              $theme_name);

    header('Location: ../index.php');
    exit;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $locale     = $_SESSION[VAR_LOCALE];
    $page_rows  = $_SESSION[VAR_PAGEROWS];
    $page_bkms  = $_SESSION[VAR_PAGEBKMS];
    $theme_name = $_SESSION[VAR_THEME_NAME];
}

// generate breadcrumbs

$xml = '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_SETTINGS_ID) . '</breadcrumb>'
     . '</breadcrumbs>';

// generate tabs

$xml .= '<tabs>'
      . '<tab url="index.php" active="true">' . get_html_resource(RES_APPEARANCE_ID) . '</tab>'
      . '<tab url="csv.php">'                 . get_html_resource(RES_CSV_ID)        . '</tab>';

if (!$_SESSION[VAR_LDAPUSER])
{
    $xml .= '<tab url="password.php">' . get_html_resource(RES_CHANGE_PASSWORD_ID) . '</tab>';
}

// generate contents

$xml .= '<content>'
      . '<form name="mainform" action="index.php">'
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
      . '<control name="page_rows" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
      . '<label>' . get_html_resource(RES_ROWS_PER_PAGE_ID) . '</label>'
      . '<editbox maxlen="' . ustrlen(MAX_PAGE_SIZE) . '">' . ustr2html($page_rows) . '</editbox>'
      . '</control>'
      . '<control name="page_bkms" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
      . '<label>' . get_html_resource(RES_BOOKMARKS_PER_PAGE_ID) . '</label>'
      . '<editbox maxlen="' . ustrlen(MAX_PAGE_SIZE) . '">' . ustr2html($page_bkms) . '</editbox>'
      . '</control>';

$xml .= '<control name="theme_name">'
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
      . '<note>' . ustrprocess(get_html_resource(RES_ALERT_INTEGER_VALUE_OUT_OF_RANGE_ID), MIN_PAGE_SIZE, MAX_PAGE_SIZE) . '</note>'
      . '</form>'
      . '</content>'
      . '</tabs>';

echo(xml2html($xml, get_html_resource(RES_SETTINGS_ID)));

?>
