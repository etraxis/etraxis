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

global $encodings;
global $line_endings_names;
global $line_endings_chars;

init_page();

$error = NO_ERROR;

// settings form is submitted

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

    if (!$_SESSION[VAR_LDAPUSER])
    {
        $passwd1 = ustrcut($_REQUEST['passwd1'], MAX_ACCOUNT_PASSWORD);
        $passwd2 = ustrcut($_REQUEST['passwd2'], MAX_ACCOUNT_PASSWORD);

        if (ustrlen($passwd1) != 0 ||
            ustrlen($passwd2) != 0)
        {
            $error = password_validate($passwd1, $passwd2);

            if ($error == NO_ERROR)
            {
                $error = password_change($_SESSION[VAR_USERID], $passwd1);
            }
        }
    }

    if ($error == NO_ERROR)
    {
        header('Location: ../index.php');
        exit;
    }
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

// generate breadcrumbs

$xml = '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_SETTINGS_ID) . '</breadcrumb>'
     . '</breadcrumbs>';

// generate tabs

$xml .= '<tabs>';

if ($_SESSION[VAR_LDAPUSER])
{
    $xml .= '<tab id="2" active="true">' . get_html_resource(RES_APPEARANCE_ID) . '</tab>'
          . '<tab id="3">'               . get_html_resource(RES_CSV_ID)        . '</tab>';
}
else
{
    $xml .= '<tab id="1" active="true">' . get_html_resource(RES_CHANGE_PASSWORD_ID) . '</tab>'
          . '<tab id="2">'               . get_html_resource(RES_APPEARANCE_ID)      . '</tab>'
          . '<tab id="3">'               . get_html_resource(RES_CSV_ID)             . '</tab>';
}

$xml .= '<content>'
      . '<form name="mainform" action="index.php" upload="' . (ATTACHMENTS_MAXSIZE * 1024) . '">';

// generate "Change password" tab

if (!$_SESSION[VAR_LDAPUSER])
{
    $xml .= '<subpage id="1" active="true">'
          . '<group>'
          . '<control name="passwd1">'
          . '<label>' . get_html_resource(RES_PASSWORD_ID) . '</label>'
          . '<passbox maxlen="' . MAX_ACCOUNT_PASSWORD . '"/>'
          . '</control>'
          . '<control name="passwd2">'
          . '<label>' . get_html_resource(RES_PASSWORD_CONFIRM_ID) . '</label>'
          . '<passbox maxlen="' . MAX_ACCOUNT_PASSWORD . '"/>'
          . '</control>'
          . '</group>'
          . '<note>' . ustrprocess(get_html_resource(RES_ALERT_PASSWORD_TOO_SHORT_ID), MIN_PASSWORD_LENGTH) . '</note>'
          . '</subpage>';
}

// generate "Appearance" tab

$xml .= ($_SESSION[VAR_LDAPUSER]
            ? '<subpage id="2" active="true">'
            : '<subpage id="2">')
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
          . $locale_name
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
      . '</control>'
      . '</group>'
      . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>'
      . '<note>' . ustrprocess(get_html_resource(RES_ALERT_INTEGER_VALUE_OUT_OF_RANGE_ID), MIN_PAGE_SIZE, MAX_PAGE_SIZE) . '</note>'
      . '</subpage>';

// generate "CSV" tab

$xml .= '<subpage id="3">'
      . '<group>'
      . '<control name="delimiter" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
      . '<label>' . get_html_resource(RES_CSV_DELIMITER_ID) . '</label>'
      . '<editbox maxlen="1">' . ustr2html($delimiter) . '</editbox>'
      . '</control>'
      . '<control name="encoding">'
      . '<label>' . get_html_resource(RES_CSV_ENCODING_ID) . '</label>'
      . '<combobox>';

foreach ($encodings as $i => $item)
{
    $xml .= ($encoding == $item
                ? '<listitem value="' . $i . '" selected="true">'
                : '<listitem value="' . $i . '">')
          . ustr2html($item)
          . '</listitem>';
}

$xml .= '</combobox>'
      . '</control>'
      . '<control name="line_endings">'
      . '<label>' . get_html_resource(RES_CSV_LINE_ENDINGS_ID) . '</label>'
      . '<combobox>';

foreach ($line_endings_names as $i => $item)
{
    $xml .= ($line_endings == $line_endings_chars[$i]
                ? '<listitem value="' . $i . '" selected="true">'
                : '<listitem value="' . $i . '">')
          . ustr2html($item)
          . '</listitem>';
}

$xml .= '</combobox>'
      . '</control>'
      . '</group>'
      . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>'
      . '</subpage>';

// generate buttons

$xml .= '<button default="true">' . get_html_resource(RES_SAVE_ID) . '</button>'
      . '</form>';

// if some error was specified to display, force an alert

switch ($error)
{
    case ERROR_INCOMPLETE_FORM:
    case ERROR_PASSWORDS_DO_NOT_MATCH:
        $xml .= "<script>alert('" . get_js_resource(RES_ALERT_PASSWORDS_DO_NOT_MATCH_ID) . "');</script>";
        break;
    case ERROR_PASSWORD_TOO_SHORT:
        $xml .= "<script>alert('" . ustrprocess(get_js_resource(RES_ALERT_PASSWORD_TOO_SHORT_ID), MIN_PASSWORD_LENGTH) . "');</script>";
        break;
    default: ;  // nop
}

$xml .= '</content>'
      . '</tabs>';

echo(xml2html($xml, get_html_resource(RES_SETTINGS_ID)));

?>
