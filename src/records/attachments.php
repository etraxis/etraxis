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
require_once('../dbo/records.php');
/**#@-*/

init_page(LOAD_TAB, GUEST_IS_ALLOWED);

$attachname = NULL;
$xml        = NULL;

// check that requested record exists

$id     = ustr2int(try_request('id'));
$record = record_find($id);

if (!$record)
{
    debug_write_log(DEBUG_NOTICE, 'Record cannot be found.');
    exit;
}

// get current user's permissions and verify them

$permissions = record_get_permissions($record['template_id'], $record['creator_id'], $record['responsible_id']);

if (!can_record_be_displayed($permissions))
{
    debug_write_log(DEBUG_NOTICE, 'Record cannot be displayed.');
    exit;
}

// attachment form is submitted

if (try_request('submitted') == 'attachform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    if (can_file_be_attached($record, $permissions))
    {
        $attachname = ustrcut($_REQUEST['attachname'], MAX_ATTACHMENT_NAME);

        $error = attachment_add($id, $attachname, $_FILES['attachfile']);

        switch ($error)
        {
            case NO_ERROR:
                /**
                 * jQuery Form Plugin uses "success" callback function in both cases - success and failure
                 * (see https://github.com/malsup/form/issues/107 for details).
                 * It makes impossible to distinguish successful response from error messages.
                 * To make the difference a successful response is prefixed with "OK ".
                 * For the same reasons a workaround function "attachmentSuccess2" is appeared (see its code below).
                 */
                header('HTTP/1.0 200 OK');
                $rs = dal_query('attachs/list.sql', $record['record_id'], 'attachment_id');
                echo(sprintf('OK %s (%u)', get_html_resource(RES_ATTACHMENTS_ID), $rs->rows));
                break;

            case ERROR_INCOMPLETE_FORM:
                send_http_error(get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID));
                break;

            case ERROR_ALREADY_EXISTS:
                send_http_error(get_html_resource(RES_ALERT_ATTACHMENT_ALREADY_EXISTS_ID));
                break;

            case ERROR_UPLOAD_INI_SIZE:
                send_http_error(get_html_resource(RES_ALERT_UPLOAD_INI_SIZE_ID));
                break;

            case ERROR_UPLOAD_FORM_SIZE:
                send_http_error(ustrprocess(get_html_resource(RES_ALERT_UPLOAD_FORM_SIZE_ID), ATTACHMENTS_MAXSIZE));
                break;

            case ERROR_UPLOAD_PARTIAL:
                send_http_error(get_html_resource(RES_ALERT_UPLOAD_PARTIAL_ID));
                break;

            case ERROR_UPLOAD_NO_FILE:
                send_http_error(get_html_resource(RES_ALERT_UPLOAD_NO_FILE_ID));
                break;

            case ERROR_UPLOAD_NO_TMP_DIR:
                send_http_error(get_html_resource(RES_ALERT_UPLOAD_NO_TMP_DIR_ID));
                break;

            case ERROR_UPLOAD_CANT_WRITE:
                send_http_error(get_html_resource(RES_ALERT_UPLOAD_CANT_WRITE_ID));
                break;

            case ERROR_UPLOAD_EXTENSION:
                send_http_error(get_html_resource(RES_ALERT_UPLOAD_EXTENSION_ID));
                break;

            default:
                send_http_error(get_html_resource(RES_ALERT_UNKNOWN_ERROR_ID));
        }
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, 'No permissions to attach file.');
    }

    exit;
}

// attachments list is submitted

elseif (try_request('submitted') == 'attachlist')
{
    debug_write_log(DEBUG_NOTICE, 'Attachments are removed.');

    if (can_file_be_removed($record))
    {
        foreach ($_REQUEST as $request)
        {
            if (substr($request, 0, 4) == 'file')
            {
                attachment_remove($id, $permissions, intval(substr($request, 4)));
            }
        }
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, 'Files cannot be removed.');
    }

    $rs = dal_query('attachs/list.sql', $record['record_id'], 'attachment_id');
    echo(sprintf('%s (%u)', get_html_resource(RES_ATTACHMENTS_ID), $rs->rows));
    exit;
}

else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');
}

// local JS functions

$resTitle = get_js_resource(RES_ERROR_ID);
$resOK    = get_js_resource(RES_OK_ID);

$xml = <<<JQUERY
<script>

function attachmentSuccess (data)
{
    var index = $("#tabs").tabs("option", "selected") + 1;
    $("[href=#ui-tabs-" + index + "]").html(data);
    reloadTab();
}

function attachmentError (XMLHttpRequest)
{
    jqAlert("{$resTitle}", XMLHttpRequest.responseText, "{$resOK}");
}

function attachmentSuccess2 (data)
{
    if (data.substr(0,3) == "OK ")  // success
    {
        var index = $("#tabs").tabs("option", "selected") + 1;
        $("[href=#ui-tabs-" + index + "]").html(data.substr(3));
        reloadTab();
    }
    else    // error
    {
        jqAlert("{$resTitle}", data, "{$resOK}");
    }
}

</script>
JQUERY;

// mark the record as read

record_read($id);

// whether user is allowed to add new attachment

if (can_file_be_attached($record, $permissions))
{
    $xml .= '<form name="attachform" action="attachments.php?id=' . $id . '" upload="' . (ATTACHMENTS_MAXSIZE * 1024) . '" success="attachmentSuccess2" error="attachmentError">'
          . '<group title="' . get_html_resource(RES_ATTACHMENT_ID) . '">'
          . '<control name="attachname">'
          . '<label>' . get_html_resource(RES_ATTACHMENT_NAME_ID) . '</label>'
          . '<editbox maxlen="' . MAX_ATTACHMENT_NAME . '">' . ustr2html($attachname) . '</editbox>'
          . '</control>'
          . '<control name="attachfile" required="' . get_html_resource(RES_REQUIRED3_ID) . '">'
          . '<label>' . get_html_resource(RES_ATTACHMENT_FILE_ID) . '</label>'
          . '<filebox/>'
          . '</control>'
          . '</group>'
          . '<button default="true">' . get_html_resource(RES_OK_ID) . '</button>'
          . '<note>' . get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID)                                 . '</note>'
          . '<note>' . ustrprocess(get_html_resource(RES_ALERT_UPLOAD_FORM_SIZE_ID), ATTACHMENTS_MAXSIZE) . '</note>'
          . '</form>';
}

// get the attachments list

$sort = $page = NULL;
$list = attachments_list($id, $sort, $page);

if ($list->rows == 0)
{
    debug_write_log(DEBUG_NOTICE, 'No attachments are found.');

    if (!can_file_be_attached($record, $permissions))
    {
        $xml .= get_html_resource(RES_NONE2_ID);
    }
}
else
{
    // generate list header

    $columns = array
    (
        RES_ATTACHMENT_NAME_ID,
        RES_SIZE_ID,
        RES_ORIGINATOR_ID,
        RES_TIMESTAMP_ID,
    );

    $rec_from = $rec_to = 0;

    $bookmarks = gen_xml_bookmarks($page, $list->rows, $rec_from, $rec_to, 'attachments.php?id=' . $id . '&amp;');

    $xml .= '<form name="attachlist" action="attachments.php?id=' . $id . '" success="attachmentSuccess">'
          . '<list>'
          . '<hrow>'
          . '<hcell checkboxes="true"/>';

    for ($i = 1; $i <= count($columns); $i++)
    {
        $smode = ($sort == $i ? ($i + count($columns)) : $i);

        $xml .= "<hcell url=\"attachments.php?id={$id}&amp;sort={$smode}&amp;page={$page}\">"
              . get_html_resource($columns[$i - 1])
              . '</hcell>';
    }

    $xml .= '</hrow>';

    // go through the attachments list

    $list->seek($rec_from - 1);

    for ($i = $rec_from; $i <= $rec_to; $i++)
    {
        $row = $list->fetch();

        $xml .= (($permissions & PERMIT_REMOVE_FILES) == 0
                    ? '<row name="file' . $row['attachment_id'] . '" url="download.php?id=' . $row['attachment_id'] . '" disabled="true">'
                    : '<row name="file' . $row['attachment_id'] . '" url="download.php?id=' . $row['attachment_id'] . '">')
              . '<cell>' . ustr2html($row['attachment_name']) . '</cell>'
              . '<cell>' . $row['attachment_size'] . '</cell>'
              . '<cell>' . ustr2html(sprintf('%s (%s)', $row['fullname'], account_get_username($row['username']))) . '</cell>'
              . '<cell>' . get_datetime($row['event_time']) . '</cell>'
              . '</row>';
    }

    $xml .= '</list>'
          . '</form>'
          . $bookmarks;

    $xml .= '<button action="$(\'#attachlist\').submit()">'
          . get_html_resource(RES_REMOVE_FILE_ID)
          . '</button>';
}

echo(xml2html($xml));

?>
