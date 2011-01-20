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

if (try_request('submitted') == 'csvform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $delimiter    = ustrcut($_REQUEST['delimiter'], 1);
    $encoding     = ustr2int($_REQUEST['encoding'], 1, count($encodings));
    $line_endings = ustr2int($_REQUEST['line_endings'], 1, count($line_endings_names));

    if (ustrlen($delimiter) == 0 ||
        ustrpos(CSV_DELIMITERS, $delimiter) === FALSE)
    {
        $delimiter = chr(DEFAULT_DELIMITER);
    }

    dal_query('accounts/csv.sql',
              $_SESSION[VAR_USERID],
              ord($delimiter),
              $encoding,
              $line_endings);

    exit;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');

    $delimiter    = $_SESSION[VAR_DELIMITER];
    $encoding     = $_SESSION[VAR_ENCODING];
    $line_endings = $_SESSION[VAR_LINE_ENDINGS];
}

// local JS functions

$resTitle    = get_js_resource(RES_SETTINGS_ID);
$resError    = get_js_resource(RES_ERROR_ID);
$resMessage1 = get_js_resource(RES_ALERT_SUCCESSFULLY_SAVED_ID);
$resMessage2 = get_js_resource(RES_ALERT_UNKNOWN_ERROR_ID);
$resOK       = get_js_resource(RES_OK_ID);

$xml = <<<JQUERY
<script>

function csvSuccess ()
{
    jqAlert("{$resTitle}", "{$resMessage1}", "{$resOK}");
}

function csvError ()
{
    jqAlert("{$resError}", "{$resMessage2}", "{$resOK}");
}

</script>
JQUERY;

// generate contents

$xml .= '<form name="csvform" action="csv.php" success="csvSuccess" error="csvError">'
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
      . '<button default="true">' . get_html_resource(RES_SAVE_ID) . '</button>'
      . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID) . '</note>'
      . '</form>';

echo(xml2html($xml));

?>
