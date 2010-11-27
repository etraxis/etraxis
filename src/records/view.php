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
require_once('../dbo/fields.php');
require_once('../dbo/values.php');
require_once('../dbo/records.php');
require_once('../dbo/views.php');
/**#@-*/

init_page(GUEST_IS_ALLOWED);

// whether a record's dump was requested

$dump_mode = isset($_REQUEST['dump']);

debug_write_log(DEBUG_NOTICE, 'Dump mode = ' . $dump_mode);

// check that requested record exists

$id     = ustr2int(try_request($dump_mode ? 'dump' : 'id'));
$record = record_find($id);

if (!$record)
{
    debug_write_log(DEBUG_NOTICE, 'Record cannot be found.');
    header('Location: index.php');
    exit;
}

// get current user's permissions and verify them

$permissions = record_get_permissions($record['template_id'], $record['creator_id'], $record['responsible_id']);

if (!can_record_be_displayed($permissions))
{
    if (get_user_level() == USER_LEVEL_GUEST)
    {
        save_cookie(COOKIE_URI, $_SERVER['REQUEST_URI']);
    }

    debug_write_log(DEBUG_NOTICE, 'Record cannot be displayed.');
    header('Location: index.php');
    exit;
}

// find previous and next records

$columns = columns_list();

$sort = $page = NULL;
$list = records_list($columns, $sort, $page, $_SESSION[VAR_SEARCH_MODE], $_SESSION[VAR_SEARCH_TEXT]);

$prev_id = $next_id = $temp_id = NULL;

while (($row = $list->fetch()))
{
    if ($id == $row['record_id'])
    {
        $prev_id = $temp_id;

        if (($row = $list->fetch()))
        {
            $next_id = $row['record_id'];
        }

        break;
    }

    $temp_id = $row['record_id'];
}

// mark the record as read

record_read($id);

// page's title

$title = ustrprocess(get_html_resource(RES_RECORD_X_ID), record_id($id, $record['template_prefix']));

// generate breadcrumbs and tabs

$xml = '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_RECORDS_ID) . '</breadcrumb>'
     . '<breadcrumb url="view.php?id=' . $id . '">' . $title . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<tabs>'
     . gen_record_tabs($record, RECORD_TAB_MAIN)
     . '<content>';

// generate buttons

$xml .= '<button url="index.php">' . get_html_resource(RES_BACK_ID) . '</button>';

if (!is_null($prev_id) || !is_null($next_id))
{
    if (!is_null($prev_id))
    {
        $xml .= '<button url="view.php?id=' . $prev_id . '">%lt;%lt;</button>';
    }
    else
    {
        $xml .= '<button disabled="true">%lt;%lt;</button>';
    }

    if (!is_null($next_id))
    {
        $xml .= '<button url="view.php?id=' . $next_id . '">%gt;%gt;</button>';
    }
    else
    {
        $xml .= '<button disabled="true">%gt;%gt;</button>';
    }
}

$xml .= HTML_SPLITTER
      . '<button url="view.php?dump=' . $id . '">' . get_html_resource(RES_DUMP_ID) . '</button>'
      . HTML_SPLITTER;

$xml .= (can_record_be_modified($record, $permissions)
            ? '<button url="modify.php?id=' . $id . '">'
            : '<button disabled="true">')
      . get_html_resource(RES_MODIFY_ID)
      . '</button>';

$xml .= (can_record_be_deleted($record, $permissions)
            ? '<button url="delete.php?id=' . $id . '" prompt="' . get_html_resource(RES_CONFIRM_DELETE_RECORD_ID) . '">'
            : '<button disabled="false">')
      . get_html_resource(RES_DELETE_ID)
      . '</button>';

$rs = dal_query(DATABASE_DRIVER == DRIVER_ORACLE9 ? 'records/oracle/tfndid.sql' : 'records/tfndid.sql',
                $_SESSION[VAR_USERID],
                $record['project_id'],
                $record['template_id']);

$xml .= ($rs->rows != 0
            ? '<button url="create.php?id=' . $id . '">'
            : '<button disabled="true">')
      . get_html_resource(RES_CLONE_ID)
      . '</button>';

if (is_record_postponed($record))
{
    $xml .= (can_record_be_resumed($record, $permissions)
                ? '<button url="resume.php?id=' . $id . '" prompt="' . get_html_resource(RES_CONFIRM_RESUME_RECORD_ID) . '">'
                : '<button disabled="true">')
          . get_html_resource(RES_RESUME_ID)
          . '</button>';
}
else
{
    $xml .= '<script src="postpone.js"></script>'
          . (can_record_be_postponed($record, $permissions)
                ? '<button action="loadPostponeForm(' . $id . ')">'
                : '<button disabled="true">')
          . get_html_resource(RES_POSTPONE_ID)
          . '</button>';
}

if (EMAIL_NOTIFICATIONS_ENABLED && (get_user_level() != USER_LEVEL_GUEST))
{
    $xml .= HTML_SPLITTER
          . '<button url="subscribe-self.php?id=' . $id . '">' . get_html_resource(is_record_subscribed($id, $_SESSION[VAR_USERID]) ? RES_UNSUBSCRIBE_ID : RES_SUBSCRIBE_ID) . '</button>'
          . '<button url="subscribe.php?id=' . $id . '">' . get_html_resource(RES_SUBSCRIBE_OTHERS_ID) . '</button>';
}

// whether this record can be reassigned

if (can_record_be_reassigned($record, $permissions))
{
    $rs = dal_query('records/responsibles.sql', $record['project_id'], $record['state_id'], $record['creator_id']);

    if ($rs->rows > 1)
    {
        $prompt        = get_html_resource(RES_CONFIRM_ASSIGN_RECORD_ID);
        $msgtitle      = get_html_resource(RES_QUESTION_ID);
        $btnactiontext = get_html_resource(RES_OK_ID);
        $btncanceltext = get_html_resource(RES_CANCEL_ID);

        $script = <<<SCRIPT

        function onAssign (index)
        {
            if (index != 0)
            {
                jqConfirm("{$msgtitle}","{$prompt}","{$btnactiontext}","document.assignform.submit();","{$btncanceltext}");
            }
        }

SCRIPT;

        $xml .= '<form name="assignform" action="assign.php?id=' . $id . '">'
              . '<script>' . $script . '</script>'
              . '<control name="responsible">'
              . '<combobox>';

        while (($row = $rs->fetch()))
        {
            if ($record['responsible_id'] != $row['account_id'])
            {
                $xml .= ($row['account_id'] == $_SESSION[VAR_USERID]
                            ? '<listitem value="' . $row['account_id'] . '" selected="true">'
                            : '<listitem value="' . $row['account_id'] . '">')
                      . ustr2html(sprintf('%s (%s)', $row['fullname'], account_get_username($row['username'])))
                      . '</listitem>';
            }
        }

        $xml .= '</combobox>'
              . '</control>'
              . '<button action="onAssign(assignform.responsible.options[assignform.responsible.selectedIndex].value);">' . get_html_resource(RES_ASSIGN2_ID) . '</button>'
              . '</form>';
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Record cannot be reassigned.');
}

// whether current state can be changed

if (can_state_be_changed($record, $permissions))
{
    $rs = dal_query('depends/listuc.sql', $id);
    $rs = dal_query('records/tramongs.sql', $id, $_SESSION[VAR_USERID], ($rs->rows == 0 ? '' : 'and s.state_type <> 3'));

    if ($rs->rows != 0)
    {
        $xml .= '<form name="stateslistform" action="javascript:loadStateForm(' . $id . ')">'
              . '<script src="state.js"></script>'
              . '<control name="state">'
              . '<combobox>';

        while (($row = $rs->fetch()))
        {
            $xml .= ($record['next_state_id'] == $row['state_id']
                        ? '<listitem value="' . $row['state_id'] . '" selected="true">'
                        : '<listitem value="' . $row['state_id'] . '">')
                  . ustr2html($row['state_name'])
                  . '</listitem>';
        }

        $xml .= '</combobox>'
              . '</control>'
              . '<button default="true">' . get_html_resource(RES_CHANGE_STATE_ID) . '</button>'
              . '</form>'
              . '<div id="statediv"/>';
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'State cannot be changed.');
}

// hidden postpone form

$xml .= '<div id="postponediv"/>';

// generate general information

$xml .= '<group title="' . get_html_resource(RES_GENERAL_INFO_ID) . '">'
      . '<text label="' . get_html_resource(RES_PROJECT_ID)  . '">' . ustr2html($record['project_name'])  . '</text>'
      . '<text label="' . get_html_resource(RES_TEMPLATE_ID) . '">' . ustr2html($record['template_name']) . '</text>'
      . '<text label="' . get_html_resource(RES_STATE_ID)    . '">' . ustr2html($record['state_name'])    . '</text>';

if (is_record_postponed($record))
{
    $xml .= '<text label="' . get_html_resource(RES_POSTPONED_ID) . '">' . get_date($record['postpone_time']) . '</text>';
}

$xml .= '<text label="' . get_html_resource(RES_AGE_ID)         . '">' . get_record_last_event($record) . '/' . get_record_age($record) . '</text>'
      . '<text label="' . get_html_resource(RES_AUTHOR_ID)      . '">' . ustr2html(sprintf('%s (%s)', $record['author_fullname'], account_get_username($record['author_username']))) . '</text>'
      . '<text label="' . get_html_resource(RES_RESPONSIBLE_ID) . '">' . (is_null($record['username']) ? get_html_resource(RES_NONE_ID) : ustr2html(sprintf('%s (%s)', $record['fullname'], account_get_username($record['username'])))) . '</text>'
      . '<text label="' . get_html_resource(RES_SUBJECT_ID)     . '">' . update_references($record['subject'], BBCODE_MINIMUM) . '</text>'
      . '</group>';

// go through the list of all states and their fields

$responsible = FALSE;

$events = dal_query('records/elist2.sql', $id);

while (($event = $events->fetch()))
{
    if ($event['event_type'] == EVENT_RECORD_ASSIGNED)
    {
        $responsible = account_find($event['event_param']);
        $group_title = 'Reassigned';
    }
    elseif ($event['event_type'] == EVENT_RECORD_CREATED ||
            $event['event_type'] == EVENT_RECORD_STATE_CHANGED)
    {
        if ($event['responsible'] == STATE_RESPONSIBLE_REMOVE)
        {
            $responsible = FALSE;
        }
        elseif ($event['responsible'] == STATE_RESPONSIBLE_ASSIGN)
        {
            $responsible = account_find($events->fetch('event_param'));
        }

        $group_title = ustr2html($event['state_name']);
    }
    elseif ($event['event_type'] == EVENT_COMMENT_ADDED ||
            $event['event_type'] == EVENT_CONFIDENTIAL_COMMENT)
    {
        $group_title = get_html_resource(RES_COMMENT_ID);
    }
    elseif ($event['event_type'] == EVENT_FILE_ATTACHED)
    {
        $group_title = get_html_resource(RES_ATTACHMENT_ID);
    }
    else
    {
        continue;
    }

    $group_title .= ' - ' . get_datetime($event['event_time'])
                  . ' - ' . ustr2html(sprintf('%s (%s)', $event['fullname'], account_get_username($event['username'])));

    $xml .= '<group title="' . $group_title . '">';

    if ($event['event_type'] == EVENT_RECORD_CREATED ||
        $event['event_type'] == EVENT_RECORD_STATE_CHANGED)
    {
        $xml .= '<text label="' . get_html_resource(RES_RESPONSIBLE_ID) . '">'
              . ($responsible ? ustr2html(sprintf('%s (%s)', $responsible['fullname'], account_get_username($responsible['username'])))
                              : get_html_resource(RES_NONE_ID))
              . '</text>';

        $fields = dal_query('records/flist2.sql',
                            $id,
                            $event['event_id'],
                            $event['state_id'],
                            $record['creator_id'],
                            is_null($record['responsible_id']) ? 0 : $record['responsible_id'],
                            $_SESSION[VAR_USERID],
                            FIELD_ALLOW_TO_READ);

        while (($field = $fields->fetch()))
        {
            $value = value_find($field['field_type'], $field['value_id']);

            if ($field['field_type'] == FIELD_TYPE_CHECKBOX)
            {
                $value = get_html_resource($value ? RES_YES_ID : RES_NO_ID);
            }
            elseif ($field['field_type'] == FIELD_TYPE_LIST)
            {
                $value = (is_null($value) ? NULL : value_find_listvalue($field['field_id'], $value));
            }
            elseif ($field['field_type'] == FIELD_TYPE_RECORD)
            {
                $value = (is_null($value) ? NULL : 'rec#' . $value);
            }

            $xml .= '<text label="' . ustr2html($field['field_name']) . '">'
                  . (is_null($value) ? get_html_resource(RES_NONE_ID) : update_references($value, BBCODE_ALL, $field['regex_search'], $field['regex_replace']))
                  . '</text>';

            if ($field['add_separator'])
            {
                $xml .= '<hr/>';
            }
        }
    }
    elseif ($event['event_type'] == EVENT_COMMENT_ADDED ||
            $event['event_type'] == EVENT_CONFIDENTIAL_COMMENT)
    {
        $comment = comment_find($event['event_id'], $permissions);

        if ($comment)
        {
            $xml .= ($comment['is_confidential']
                        ? '<text label="' . get_html_resource(RES_CONFIDENTIAL_ID) . '">'
                        : '<text>')
                  . update_references($comment['comment_body'])
                  . '</text>';
        }
    }
    elseif ($event['event_type'] == EVENT_FILE_ATTACHED)
    {
        $rs         = dal_query('attachs/fndk.sql', $event['event_id']);
        $attachment = ($rs->rows == 0 ? FALSE : $rs->fetch());

        if ($attachment)
        {
            $xml .= '<text label="' . get_html_resource(RES_ATTACHMENT_NAME_ID) . '">'
                  . '<url address="download.php?id=' . $attachment['attachment_id'] . '">' . $attachment['attachment_name'] . '</url>'
                  . '</text>';

            $xml .= '<text label="' . get_html_resource(RES_SIZE_ID) . '">'
                  . ustrprocess(get_html_resource(RES_KB_ID), sprintf('%01.2f', $attachment['attachment_size'] / 1024))
                  . '</text>';
        }
    }

    $xml .= '</group>';
}

$xml .= '</content>'
      . '</tabs>';

// generate HTML or dumpfile

if ($dump_mode)
{
    header('Pragma: private');
    header('Cache-Control: private, must-revalidate');
    header('Content-type: text/txt');
    header('Content-Disposition: attachment; filename=dump-' . $id . '.txt');

    $dump = xml2html($xml, $title, 'dump.xsl');
    $dump = html_entity_decode($dump, ENT_QUOTES, 'UTF-8');
    $dump = str_replace('<br>', "\n", $dump);

    if ($_SESSION[VAR_LINE_ENDINGS] != "\n")
    {
        $dump = ustr_replace("\n", $_SESSION[VAR_LINE_ENDINGS], $dump);
    }

    if ($_SESSION[VAR_ENCODING] != 'UTF-8')
    {
        $dump = iconv('UTF-8', $_SESSION[VAR_ENCODING], $dump);
    }

    echo($dump);
}
else
{
    echo(xml2html($xml, $title));
}

?>
