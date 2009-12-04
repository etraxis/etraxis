<?php

/**
 * @package eTraxis
 * @ignore
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
//  Artem Rodygin           2005-08-18      new-030: UI language should be set for each user separately.
//  Artem Rodygin           2005-08-18      new-035: Customizable list size.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-11-16      new-176: Change eTraxis design.
//  Artem Rodygin           2006-10-08      bug-359: /src/settings/index.php: Global variable $locale_info was used before it was defined.
//  Artem Rodygin           2007-09-12      new-576: [SF1788286] Export to CSV
//  Artem Rodygin           2007-09-13      new-566: Choose encoding for record dump and export of records list.
//  Artem Rodygin           2007-10-12      bug-597: /src/settings/index.php: Global variables $encodings and $line_endings_names were used before they were defined.
//  Artem Rodygin           2008-11-09      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/accounts.php');
/**#@-*/

global $locale_info;
global $encodings;
global $line_endings_names;
global $line_endings_chars;

init_page();

if (try_request('submitted') == 'mainform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $locale       = ustr2int($_REQUEST['locale']);
    $page_rows    = ustr2int($_REQUEST['page_rows'], MIN_PAGE_SIZE, MAX_PAGE_SIZE);
    $page_bkms    = ustr2int($_REQUEST['page_bkms'], MIN_PAGE_SIZE, MAX_PAGE_SIZE);
    $delimiter    = ustrcut($_REQUEST['delimiter'], 1);
    $encoding     = ustr2int($_REQUEST['encoding'], 1, count($encodings));
    $line_endings = ustr2int($_REQUEST['line_endings'], 1, count($line_endings_names));

    if (ustrlen($delimiter) == 0 ||
        ustrpos(CSV_DELIMITERS, $delimiter) === FALSE)
    {
        $delimiter = chr(DEFAULT_DELIMITER);
    }

    locale_change($_SESSION[VAR_USERID], $locale);
    dal_query('accounts/settings.sql',
              $_SESSION[VAR_USERID],
              $page_rows,
              $page_bkms,
              ord($delimiter),
              $encoding,
              $line_endings);

    header('Location: ../index.php');
    exit;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $locale       = $_SESSION[VAR_LOCALE];
    $page_rows    = $_SESSION[VAR_PAGEROWS];
    $page_bkms    = $_SESSION[VAR_PAGEBKMS];
    $delimiter    = $_SESSION[VAR_DELIMITER];
    $encoding     = $_SESSION[VAR_ENCODING];
    $line_endings = $_SESSION[VAR_LINE_ENDINGS];
}

$xml = '<page' . gen_xml_page_header(get_html_resource(RES_SETTINGS_ID), NULL, 'mainform.locale') . '>'
     . gen_xml_menu()
     . '<path>'
     . '<pathitem url="index.php">' . get_html_resource(RES_SETTINGS_ID) . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<form name="mainform" action="index.php">'
     . '<group>'
     . '<combobox label="' . get_html_resource(RES_LANGUAGE_ID) . '" name="locale">';

$supported_locales = array_keys($locale_info);

foreach ($supported_locales as $item)
{
    $xml .= '<listitem value="' . $item . ($locale == $item ? '" selected="true">' : '">')
          . get_html_resource(RES_LOCALE_ID, $item)
          . '</listitem>';
}

$xml .= '</combobox>'
      . '<editbox label="' . get_html_resource(RES_ROWS_PER_PAGE_ID)      . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="page_rows" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(MAX_PAGE_SIZE) . '">' . ustr2html($page_rows) . '</editbox>'
      . '<editbox label="' . get_html_resource(RES_BOOKMARKS_PER_PAGE_ID) . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="page_bkms" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="' . ustrlen(MAX_PAGE_SIZE) . '">' . ustr2html($page_bkms) . '</editbox>'
      . '<editbox label="' . get_html_resource(RES_CSV_DELIMITER_ID)      . '" required="' . get_html_resource(RES_REQUIRED3_ID) . '" name="delimiter" size="' . HTML_EDITBOX_SIZE_SMALL . '" maxlen="1">' . ustr2html($delimiter) . '</editbox>'
      . '<combobox label="' . get_html_resource(RES_CSV_ENCODING_ID) . '" name="encoding">';

foreach ($encodings as $i => $item)
{
    $xml .= '<listitem value="' . $i . ($encoding == $item ? '" selected="true">' : '">')
          . ustr2html($item)
          . '</listitem>';
}

$xml .= '</combobox>'
      . '<combobox label="' . get_html_resource(RES_CSV_LINE_ENDINGS_ID) . '" name="line_endings">';

foreach ($line_endings_names as $i => $item)
{
    $xml .= '<listitem value="' . $i . ($line_endings == $line_endings_chars[$i] ? '" selected="true">' : '">')
          . ustr2html($item)
          . '</listitem>';
}

$xml .= '</combobox>'
      . '</group>'
      . '<button name="ok" default="true">'                       . get_html_resource(RES_OK_ID)     . '</button>'
      . '<button name="cancel" url="javascript:history.back();">' . get_html_resource(RES_CANCEL_ID) . '</button>'
      . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID)                                                    . '</note>'
      . '<note>' . ustrprocess(get_html_resource(RES_ALERT_INTEGER_VALUE_OUT_OF_RANGE_ID), MIN_PAGE_SIZE, MAX_PAGE_SIZE) . '</note>'
      . '</form>'
      . '</content>'
      . '</page>';

echo(xml2html($xml));

?>
