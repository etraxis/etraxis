<?php

/**
 * @package eTraxis
 * @ignore
 */

//--------------------------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system.
//  Copyright (C) 2005-2010 by Artem Rodygin
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
//  Artem Rodygin           2005-04-10      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-07-02      bug-007: Descending sorting of records by ID sorts them wrong.
//  Artem Rodygin           2005-07-04      bug-010: Missing 'require' operator.
//  Artem Rodygin           2005-07-28      new-012: Records field 'description' should be renamed with 'subject'.
//  Artem Rodygin           2005-07-31      new-006: Records search.
//  Artem Rodygin           2005-08-02      new-017: Email notifications filter.
//  Artem Rodygin           2005-08-13      new-020: Clone the records.
//  Artem Rodygin           2005-08-18      bug-034: When record is being postponed, resumed or assigned the confirmations are not displayed.
//  Artem Rodygin           2005-08-23      new-053: All the calls of DAL API functions should be moved to DBO API.
//  Artem Rodygin           2005-08-24      bug-055: List of changes does not filter values of forbidden fields.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-09-06      new-094: Record creator should be displayed in general information of record.
//  Artem Rodygin           2005-09-06      new-095: Newly created records should be displayed as unread.
//  Artem Rodygin           2005-09-07      bug-098: List of users when record is being assigned should contain only allowed users.
//  Artem Rodygin           2005-09-07      new-100: 'Date' field type should be implemented.
//  Artem Rodygin           2005-09-13      new-113: When record is being viewed the fields names and values should be aligned by top.
//  Artem Rodygin           2005-09-15      new-121: Nonbreaking spaces should be used in fields names.
//  Artem Rodygin           2005-09-15      new-123: User should be prompted for optional comment when a record is being postponed.
//  Artem Rodygin           2005-09-17      new-126: States/field values and comments should be displayed one by one.
//  Artem Rodygin           2005-09-21      bug-140: PHP Warning: odbc_exec(): SQL error: Incorrect syntax near the keyword 'and'.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-10-27      new-168: When record is being displayed each state name should be appended with timestamp and user info.
//  Artem Rodygin           2005-11-08      bug-174: Generated pages should contain <!DOCTYPE> tag.
//  Artem Rodygin           2005-11-16      new-176: Change eTraxis design.
//  Artem Rodygin           2005-11-28      bug-183: 'Change state' button doesn't work in Firefox browser.
//  Artem Rodygin           2005-12-11      bug-180: Previous field values are lost.
//  Artem Rodygin           2005-12-11      new-190: Misunderstanding when template contains state named 'Comment'.
//  Artem Rodygin           2006-01-20      new-196: It's not clear that record is postponed when one is being viewed.
//  Artem Rodygin           2006-01-20      new-199: Buttons of 'Records' and 'Record xxx-000' pages should be moved at top for convenience.
//  Artem Rodygin           2006-01-22      bug-198: Attached pictures are not shown properly in Opera.
//  Artem Rodygin           2006-02-10      new-197: Postpone should have a timer for autoresume.
//  Artem Rodygin           2006-03-18      new-175: Implement user roles in permissions.
//  Artem Rodygin           2006-04-21      new-247: The 'responsible' user role should be obliterated.
//  Artem Rodygin           2006-06-19      new-236: Single record subscription.
//  Artem Rodygin           2006-07-12      bug-292: Sablotron fails if page contains '&' character.
//  Artem Rodygin           2006-07-24      bug-201: 'Access Forbidden' error with cyrillic named attachments.
//  Artem Rodygin           2006-07-27      new-261: UI design should be adopted to slow connection.
//  Artem Rodygin           2006-09-26      new-318: Group permissions should be template-wide.
//  Artem Rodygin           2006-11-12      bug-380: Single record subscription functionality (new-236) should be conditionally "compiled".
//  Artem Rodygin           2006-11-13      new-368: User should be able to subscribe other persons.
//  Artem Rodygin           2006-11-15      bug-381: Attachments of some types are not opened in valid applications.
//  Artem Rodygin           2006-11-20      new-392: Local users should not be extended with '@eTraxis' when LDAP is disabled.
//  Artem Rodygin           2006-12-20      bug-461: PHP Warning: Sablotron error on line 1: XML parser error 4: not well-formed (invalid token)
//  Artem Rodygin           2006-12-22      new-462: Postpone date should be displayed as separate field.
//  Artem Rodygin           2006-12-26      bug-465: When template is locked all records created by this template must be read only.
//  Artem Rodygin           2006-12-27      bug-470: State permissions must not be used when record is being created.
//  Artem Rodygin           2007-01-11      new-478: Add URL to ticket in its body.
//  Artem Rodygin           2007-01-17      new-480: User should be able to add a comment directly on the same page the ticket is opened.
//  Artem Rodygin           2007-01-18      bug-487: JavaScript error in the comment box.
//  Artem Rodygin           2007-03-26      new-518: Record view page: add a note about link to another record ability.
//  Artem Rodygin           2007-06-30      new-499: Records dump to text file.
//  Artem Rodygin           2007-07-04      new-533: Links between records.
//  Artem Rodygin           2007-07-16      new-546: Confidential comments.
//  Artem Rodygin           2007-08-06      new-551: Rework dependencies into "parent-child" relations.
//  Artem Rodygin           2007-08-08      new-549: User should be able to create new dependency record.
//  Artem Rodygin           2007-09-09      new-563: Custom separators inside fields set.
//  Artem Rodygin           2007-09-13      new-566: Choose encoding for record dump and export of records list.
//  Artem Rodygin           2007-10-17      new-602: Rename "Add child" to "Attach child".
//  Artem Rodygin           2007-10-23      new-607: Replace "*" with "required" in list of children.
//  Yury Udovichenko        2007-11-02      new-562: Ability to show only last values of any state.
//  Artem Rodygin           2007-11-07      new-612: Display every used state, even it doesn't contain any field.
//  Artem Rodygin           2007-11-13      new-599: Separated "Age" in custom views.
//  Artem Rodygin           2007-11-13      new-622: Rename 'children' into 'subrecords'.
//  Yury Udovichenko        2007-11-14      new-548: Custom links in text fields.
//  Yury Udovichenko        2007-11-19      new-623: Default state in states list.
//  Artem Rodygin           2007-11-27      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2007-12-03      new-639: Highlight postpone date in record general info.
//  Artem Rodygin           2007-12-18      bug-646: Records is reassigned even when "Cancel" was clicked.
//  Yury Udovichenko        2007-12-25      new-485: Text formating in comments.
//  Yury Udovichenko        2007-12-28      new-656: BBCode // List of tags, allowed in subject, should be limited.
//  Artem Rodygin           2008-01-11      bug-663: Author permissions are ignored.
//  Artem Rodygin           2008-01-16      new-666: Buttons "Previous" & "Next" on record view page.
//  Artem Rodygin           2008-02-27      new-676: [SF1898731] Delete Issues from Workflow
//  Artem Rodygin           2008-04-19      new-705: Multiple parents for subrecords.
//  Artem Rodygin           2008-04-20      new-703: Separated permissions set for current responsible.
//  Artem Rodygin           2008-06-20      new-725: Extend combo box.
//  Artem Rodygin           2008-07-15      new-733: Responsible drop down list
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2008-11-18      new-762: Forward logged in user to the page he has tried to open before authentication.
//  Artem Rodygin           2009-01-12      bug-784: Logged in user must be forwarded to the page he has tried to open before authentication.
//  Artem Rodygin           2009-01-13      bug-786: Dump of record to text file loses new line characters.
//  Artem Rodygin           2009-04-12      bug-806: German translation causes two ambiguous "zuruck" buttons.
//  Artem Rodygin           2009-04-25      new-801: Range of valid date values must be related to current date.
//  Artem Rodygin           2009-04-26      new-818: Change buttons layout on viewing record page.
//  Artem Rodygin           2009-06-01      new-824: PHP 4 is discontinued.
//  Artem Rodygin           2009-07-29      bug-825: Database gets empty strings instead of NULL values.
//  Artem Rodygin           2009-07-29      new-833: Default responsible should be current user, when possible.
//  Artem Rodygin           2009-10-13      new-838: Disabled buttons would be better grayed out than invisible.
//  Artem Rodygin           2009-10-13      bug-849: 'Clone' button is available when should be disabled.
//  Artem Rodygin           2010-01-08      bug-888: Cannot enter full size in comment
//  Artem Rodygin           2010-02-05      bug-912: IE6 buttons with arrows rendering problem
//  Giacomo Giustozzi       2010-02-10      new-913: Resizable text boxes
//  Artem Rodygin           2010-02-14      new-919: Show record assignments on record detail page
//  Artem Rodygin           2010-04-16      new-928: Inline state changing.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/accounts.php');
require_once('../dbo/states.php');
require_once('../dbo/fields.php');
require_once('../dbo/values.php');
require_once('../dbo/records.php');
require_once('../dbo/events.php');
require_once('../dbo/views.php');
/**#@-*/

init_page(GUEST_IS_ALLOWED);

$dump_mode = isset($_REQUEST['dump']);

debug_write_log(DEBUG_NOTICE, 'Dump mode = ' . $dump_mode);

$id     = ustr2int(try_request($dump_mode ? 'dump' : 'id'));
$record = record_find($id);

if (!$record)
{
    debug_write_log(DEBUG_NOTICE, 'Record cannot be found.');
    header('Location: index.php');
    exit;
}

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

$search_mode = try_cookie(COOKIE_SEARCH_MODE, FALSE);
$search_text = try_cookie(COOKIE_SEARCH_TEXT);

$columns = column_list();

$sort = $page = NULL;
$list = record_list($columns, $sort, $page, $search_mode, $search_text);

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

record_read($id);

$xml = '<page' . gen_xml_page_header(record_id($id, $record['template_prefix'])) . '>'
     . '<script src="../scripts/json2.js"/>'
     . '<script src="../scripts/ajax.js"/>'
     . '<script src="../scripts/collapse.js"/>'
     . gen_xml_menu()
     . '<path>'
     . gen_xml_rec_root(try_cookie(COOKIE_SEARCH_MODE, FALSE))
     . '<pathitem url="view.php?id=' . $id . '">' . ustrprocess(get_html_resource(RES_RECORD_X_ID), record_id($id, $record['template_prefix'])) . '</pathitem>'
     . '</path>'
     . '<content>'
     . '<group title="' . get_html_resource(RES_GENERAL_INFO_ID) . '">'
     . '<text label="' . get_html_resource(RES_ID_ID) . '"><record id="' . $id . '">' . record_id($id, $record['template_prefix']) . '</record></text>';

$rs = dal_query('depends/fnd.sql', $id);

if ($rs->rows != 0)
{
    $children = array();

    while (($parent = $rs->fetch()))
    {
        array_push($children, '<record id="' . $parent['parent_id'] . '">' . record_id($parent['parent_id'], $parent['template_prefix']) . '</record>');
    }

    $xml .= '<text label="' . get_html_resource(RES_PARENT_ID_ID) . '">' . implode(' ', $children) . '</text>';
}

$xml .= '<text label="' . get_html_resource(RES_PROJECT_ID)     . '">' . ustr2html($record['project_name']) . '</text>'
      . '<text label="' . get_html_resource(RES_TEMPLATE_ID)    . '">' . ustr2html($record['template_name']) . '</text>'
      . '<text label="' . get_html_resource(RES_STATE_ID)       . '">' . ustr2html($record['state_name']) . '</text>';

if (is_record_postponed($record))
{
    $xml .= '<text label="' . get_html_resource(RES_POSTPONED_ID) . '"><searchres>' . get_date($record['postpone_time']) . '</searchres></text>';
}

$xml .= '<text label="' . get_html_resource(RES_AGE_ID)         . '">' . get_record_last_event($record) . '/' . get_record_age($record) . '</text>'
      . '<text label="' . get_html_resource(RES_AUTHOR_ID)      . '">' . ustr2html($record['author_fullname']) . ' (' . ustr2html(account_get_username($record['author_username'])) . ')</text>'
      . '<text label="' . get_html_resource(RES_RESPONSIBLE_ID) . '">' . (is_null($record['username']) ? get_html_resource(RES_NONE_ID) : ustr2html($record['fullname']) . ' (' . ustr2html(account_get_username($record['username'])) . ')') . '</text>'
      . '<text label="' . get_html_resource(RES_SUBJECT_ID)     . '">' . update_references($record['subject'], BBCODE_MINIMUM) . '</text>'
      . '</group>'
      . '<button url="index.php" default="true">' . get_html_resource(RES_BACK_ID) . '</button>';

if (!is_null($prev_id) || !is_null($next_id))
{
    if (!is_null($prev_id))
    {
        $xml .= '<button url="view.php?id=' . $prev_id . '">%and;</button>';
    }
    else
    {
        $xml .= '<button disabled="true">%and;</button>';
    }

    if (!is_null($next_id))
    {
        $xml .= '<button url="view.php?id=' . $next_id . '">%or;</button>';
    }
    else
    {
        $xml .= '<button disabled="true">%or;</button>';
    }
}

$xml .= '<button url="view.php?dump='  . $id . '">' . get_html_resource(RES_DUMP_ID)    . '</button>'
      . '<button url="history.php?id=' . $id . '">' . get_html_resource(RES_HISTORY_ID) . '</button>';

$rs = dal_query('changes/list.sql',
                $id,
                $record['creator_id'],
                is_null($record['responsible_id']) ? 0 : $record['responsible_id'],
                $_SESSION[VAR_USERID],
                'event_time asc, field_name asc');

if ($rs->rows != 0)
{
    $xml .= '<button url="changes.php?id=' . $id . '">' . get_html_resource(RES_CHANGES_ID) . '</button>';
}

if (can_record_be_modified($record, $permissions))
{
    $xml .= '<button url="modify.php?id=' . $id . '">' . get_html_resource(RES_MODIFY_ID) . '</button>';
}
else
{
    $xml .= '<button disabled="true">' . get_html_resource(RES_MODIFY_ID) . '</button>';
}

if (can_record_be_deleted($record, $permissions))
{
    $xml .= '<button url="delete.php?id=' . $id . '" prompt="' . get_html_resource(RES_CONFIRM_DELETE_RECORD_ID) . '">' . get_html_resource(RES_DELETE_ID) . '</button>';
}

if (can_record_be_resumed($record, $permissions))
{
    $xml .= '<button url="resume.php?id=' . $id . '" prompt="' . get_html_resource(RES_CONFIRM_RESUME_RECORD_ID) . '">' . get_html_resource(RES_RESUME_ID) . '</button>';
}

if (can_record_be_postponed($record, $permissions))
{
    $xml .= '<button url="postpone.php?id=' . $id . '">' . get_html_resource(RES_POSTPONE_ID) . '</button>';
}

$rs = dal_query(DATABASE_DRIVER == DRIVER_ORACLE9 ? 'records/oracle/tfndid.sql' : 'records/tfndid.sql',
                $_SESSION[VAR_USERID],
                $record['project_id'],
                $record['template_id']);

if ($rs->rows != 0)
{
    $xml .= '<button url="create.php?id=' . $id . '">' . get_html_resource(RES_CLONE_ID) . '</button>';
}
else
{
    $xml .= '<button disabled="true">' . get_html_resource(RES_CLONE_ID) . '</button>';
}

$splitter = '<br/>';

if (can_record_be_reassigned($record, $permissions))
{
    $rs = dal_query('records/responsibles.sql', $record['project_id'], $record['state_id'], $record['creator_id']);

    if ($rs->rows > 1)
    {
        $splitter = NULL;

        $xml .= '<form name="assignform" action="assign.php?id=' . $id . '">'
              . '<combobox name="responsible" extended="true">';

        while (($row = $rs->fetch()))
        {
            if ($record['responsible_id'] != $row['account_id'])
            {
                $xml .= '<listitem value="' . $row['account_id'] . ($row['account_id'] == $_SESSION[VAR_USERID] ? '" selected="true">' : '">')
                      . ustr2html($row['fullname']) . ' (' . ustr2html(account_get_username($row['username'])) . ')'
                      . '</listitem>';
            }
        }

        $xml .= '</combobox>'
              . '<script>'
              . "\nfunction onAssign(index)\n"
              . "{\n"
              . "    if (index != 0)\n"
              . "    {\n"
              . "        if (confirm('" . get_html_resource(RES_CONFIRM_ASSIGN_RECORD_ID) . "')) document.assignform.submit();\n"
              . "    }\n"
              . "}\n"
              . '</script>'
              . '<button action="onAssign(assignform.responsible.options[assignform.responsible.selectedIndex].value);">' . get_html_resource(RES_ASSIGN2_ID) . '</button>'
              . '</form>';
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Record cannot be reassigned.');
}

if (can_state_be_changed($record, $permissions))
{
    $rs = dal_query('depends/listuc.sql', $id);
    $rs = dal_query('records/tramongs.sql', $id, $_SESSION[VAR_USERID], ($rs->rows == 0 ? '' : 'and s.state_type <> 3'));

    if ($rs->rows != 0)
    {
        $splitter = NULL;

        $script = <<<SCRIPT

function getStateFields ()
{
    var url = "state.php?timestamp=" + new Date().getTime() + "&amp;id=${id}&amp;state=" + escape(document.stateform.state.value);

    xmlHttpRequest.open(AJAX_METHOD_GET, url, true);
    xmlHttpRequest.onreadystatechange = getStateFieldsCallback;
    xmlHttpRequest.send(null);
}

function getStateFieldsCallback ()
{
    if (xmlHttpRequest.readyState == AJAX_STATE_DONE)
    {
        if (xmlHttpRequest.statusText != "OK")
        {
            alert(xmlHttpRequest.statusText);
        }

        if (xmlHttpRequest.status == HTTP_STATUS_OK)
        {
            document.getElementById("statefields").innerHTML = xmlHttpRequest.responseText;
        }
        else
        {
            document.getElementById("statefields").innerHTML = null;
        }
    }
}

function submitFields ()
{
    var url  = "state.php?submitted=fieldsform&amp;timestamp=" + new Date().getTime() + "&amp;id=${id}&amp;state=" + escape(document.stateform.state.value);
    var data = form2json('fieldsform');

    xmlHttpRequest.open(AJAX_METHOD_POST, url, false);
    xmlHttpRequest.onreadystatechange = submitFieldsCallback;
    xmlHttpRequest.setRequestHeader("Content-Type", "text/html;charset=utf-8");
    xmlHttpRequest.send(data);
}

function submitFieldsCallback ()
{
    if (xmlHttpRequest.readyState == AJAX_STATE_DONE)
    {
        if (xmlHttpRequest.status == HTTP_STATUS_OK)
        {
            if (xmlHttpRequest.statusText == HTTP_STATUS_OK_TEXT)
            {
                window.open('view.php?id=${id}', '_parent');
            }
            else
            {
                alert(xmlHttpRequest.statusText);
            }
        }
    }
}

function cancelFields ()
{
    document.getElementById("statefields").innerHTML = null;
}

SCRIPT;

        $xml .= "<script>{$script}</script>\n";

        $xml .= '<form name="stateform" action="javascript:getStateFields()">'
              . '<combobox name="state" extended="true">';

        while (($row = $rs->fetch()))
        {
            $xml .= '<listitem value="' . $row['state_id'] . ($record['next_state_id'] == $row['state_id'] ? '" selected="true">' : '">' )
                  . ustr2html($row['state_name'])
                  . '</listitem>';
        }

        $xml .= '</combobox>'
              . '<button default="true">' . get_html_resource(RES_CHANGE_STATE_ID) . '</button>'
              . '</form>'
              . '<div id="statefields"/>';
    }
}
else
{
    debug_write_log(DEBUG_NOTICE, 'State cannot be changed.');
}

$xml .= $splitter
      . '<button action="ExpandAll();">'       . get_html_resource(RES_EXPAND_ALL_ID)        . '</button>'
      . '<button action="CollapseAll();">'     . get_html_resource(RES_COLLAPSE_ALL_ID)      . '</button>'
      . '<button action="ResetToDefaults();">' . get_html_resource(RES_RESET_TO_DEFAULTS_ID) . '</button>';

if (EMAIL_NOTIFICATIONS_ENABLED && (get_user_level() != USER_LEVEL_GUEST))
{
    $xml .= '<button url="recsubsc.php?id=' . $id . '">' . get_html_resource(is_record_subscribed($id, $_SESSION[VAR_USERID]) ? RES_UNSUBSCRIBE_ID : RES_SUBSCRIBE_ID) . '</button>'
          . '<button url="subother.php?id=' . $id . '">' . get_html_resource(RES_SUBSCRIBE_OTHERS_ID) . '</button>';
}

// list of comments that will be shown
$comments_to_show = array(-1, -2);
$states_to_show   = array();

// script for showing default fieldsets
$script = NULL;

$list = attachment_list($record['record_id']);

$xml .= '<group id="-1" title="' . get_html_resource(RES_ATTACHMENTS_ID) . ' (' . $list->rows . ')">';

if ($list->rows == 0)
{
    $xml .= '<text>' . get_html_resource(RES_NONE2_ID) . '</text>';
}
else
{
    $script .= "default_events_list[++default_events_count] = -1;\n";

    while (($row = $list->fetch()))
    {
        $xml .= '<attachment url="download.php?id=' . $row['attachment_id'] . '" size="' . ustrprocess(get_html_resource(RES_KB_ID), round($row['attachment_size'] / 1024)) . '">'
              . ustr2html($row['attachment_name'])
              . '</attachment>';
    }
}

$xml .= '</group>';

if (ATTACHMENTS_ENABLED)
{
    if (can_file_be_attached($record, $permissions))
    {
        $xml .= '<button url="attach.php?id=' . $id . '">' . get_html_resource(RES_ATTACH_FILE_ID) . '</button>';
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, 'File cannot be attached.');

        $xml .= '<button disabled="true">' . get_html_resource(RES_ATTACH_FILE_ID) . '</button>';
    }

    if (can_file_be_removed($record, $permissions))
    {
        $xml .= '<button url="remove.php?id=' . $id . '">' . get_html_resource(RES_REMOVE_FILE_ID) . '</button>';
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, 'File cannot be removed.');

        $xml .= '<button disabled="true">' . get_html_resource(RES_REMOVE_FILE_ID) . '</button>';
    }
}

$list = subrecords_list($record['record_id']);

$xml .= '<group id="-2" title="' . get_html_resource(RES_SUBRECORDS_ID) . ' (' . $list->rows . ')">';

if ($list->rows == 0)
{
    $xml .= '<text>' . get_html_resource(RES_NONE2_ID) . '</text>';
}
else
{
    $script .= "default_events_list[++default_events_count] = -2;\n";

    while (($row = $list->fetch()))
    {
        $url = ' url="view.php?id=' . $row['record_id'] . '"';

        if (is_record_closed($row))
        {
            $style = ' style="closed"';
        }
        elseif (is_record_postponed($row))
        {
            $style = ' style="cold"';
        }
        elseif (is_record_critical($row))
        {
            $style = ' style="hot"';
        }
        else
        {
            $style = NULL;
        }

        $xml .= '<row'  . $url . '>'
              . '<cell' . $url . $style . ' align="left">' . record_id($row['record_id'], $row['template_prefix']) . '</cell>'
              . '<cell' . $url . $style . ' align="center">' . ($row['is_dependency'] ? get_html_resource(RES_REQUIRED2_ID) : NULL) . '</cell>'
              . '<cell' . $url . $style . ' align="center">' . ustr2html($row['state_abbr']) . '</cell>'
              . '<cell' . $url . $style . ' align="left" wrap="true">' . ustr2html($row['subject']) . '</cell>'
              . '<cell' . $url . $style . ' align="left">' . (is_null($row['fullname']) ? get_html_resource(RES_NONE_ID) : ustr2html($row['fullname'])) . '</cell>'
              . '</row>';
    }
}

$xml .= '</group>';

if (can_subrecord_be_added($record, $permissions))
{
    $xml .= '<button url="create.php?parent=' . $id . '">' . get_html_resource(RES_CREATE_SUBRECORD_ID) . '</button>'
          . '<button url="depadd.php?id='     . $id . '">' . get_html_resource(RES_ATTACH_SUBRECORD_ID) . '</button>';
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Subrecord cannot be added.');

    $xml .= '<button disabled="true">' . get_html_resource(RES_CREATE_SUBRECORD_ID) . '</button>'
          . '<button disabled="true">' . get_html_resource(RES_ATTACH_SUBRECORD_ID) . '</button>';
}

if (can_subrecord_be_removed($record, $permissions))
{
    $xml .= '<button url="deprem.php?id=' . $id . '">' . get_html_resource(RES_REMOVE_SUBRECORD_ID) . '</button>';
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Subrecord cannot be removed.');

    $xml .= '<button disabled="true">' . get_html_resource(RES_REMOVE_SUBRECORD_ID) . '</button>';
}

$responsible = FALSE;

$rs = dal_query('records/elist2.sql', $id);

// going through the list of all events
while (($row = $rs->fetch()))
{
    if ($row['event_type'] == EVENT_COMMENT_ADDED ||
        $row['event_type'] == EVENT_CONFIDENTIAL_COMMENT)
    {
        $comment = comment_find($row['event_id'], $permissions);

        if ($comment)
        {
            // one more comment to show
            $comments_to_show[] = $row['event_id'];

            $xml .= '<group id="' . $row['event_id'] . '" title="' . get_html_resource(RES_COMMENT_ID) . ' - ' . get_datetime($row['event_time']) . ' - ' . ustr2html($row['fullname']) . ' (' . ustr2html(account_get_username($row['username'])) . ')">'
                  . '<text label="' . get_html_resource(RES_RESPONSIBLE_ID) . '">'
                  . ($responsible ? ustr2html($responsible['fullname']) . ' (' . ustr2html(account_get_username($responsible['username'])) . ')'
                                  : get_html_resource(RES_NONE_ID))
                  . '</text>'
                  . ($comment['is_confidential'] ? '<comment confidential="' . get_html_resource(RES_CONFIDENTIAL_ID) . '">' : '<comment>')
                  . update_references($comment['comment_body'])
                  . '</comment>'
                  . '</group>';
        }
    }
    elseif ($row['event_type'] == EVENT_RECORD_ASSIGNED)
    {
        $responsible = account_find($row['event_param']);
    }
    else
    {
        if ($row['responsible'] == STATE_RESPONSIBLE_REMOVE)
        {
            $responsible = FALSE;
        }
        elseif ($row['responsible'] == STATE_RESPONSIBLE_ASSIGN)
        {
            if (($responsible_id = $rs->fetch('event_param')))
            {
                $responsible = account_find($responsible_id);
            }
        }

        $rsf = dal_query('records/flist2.sql',
                         $id,
                         $row['event_id'],
                         $row['state_id'],
                         $record['creator_id'],
                         is_null($record['responsible_id']) ? 0 : $record['responsible_id'],
                         $_SESSION[VAR_USERID],
                         FIELD_ALLOW_TO_READ);

        // new state transition - all comments have to be hidden
        $comments_to_show = array();
        $states_to_show[$row['state_id']] = $row['event_id'];

        $xml .= '<group id="' . $row['event_id'] . '" title="' . ustr2html($row['state_name']) . ' - ' . get_datetime($row['event_time']) . ' - ' . ustr2html($row['fullname']) . ' (' . ustr2html(account_get_username($row['username'])) . ')">';

        $xml .= '<text label="' . get_html_resource(RES_RESPONSIBLE_ID) . '">'
              . ($responsible ? ustr2html($responsible['fullname']) . ' (' . ustr2html(account_get_username($responsible['username'])) . ')'
                              : get_html_resource(RES_NONE_ID))
              . '</text>';

        if ($rsf->rows != 0)
        {
            while (($row = $rsf->fetch()))
            {
                $value = value_find($row['field_type'], $row['value_id']);

                if ($row['field_type'] == FIELD_TYPE_CHECKBOX)
                {
                    $value = get_html_resource($value ? RES_YES_ID : RES_NO_ID);
                }
                elseif ($row['field_type'] == FIELD_TYPE_LIST)
                {
                    $value = (is_null($value) ? NULL : value_find_listvalue($row['field_id'], $value));
                }
                elseif ($row['field_type'] == FIELD_TYPE_RECORD)
                {
                    $value = (is_null($value) ? NULL : 'rec#' . $value);
                }

                $xml .= '<text label="' . ustr2html($row['field_name']) . '">'
                      . (is_null($value) ? get_html_resource(RES_NONE_ID) : update_references($value, BBCODE_ALL, $row['regex_search'], $row['regex_replace']))
                      . '</text>';

                if ($row['add_separator'])
                {
                    $xml .= '<hr/>';
                }
            }
        }

        $xml .= '</group>';
    }
}

// generating JavaScript array of default fieldsets to show
foreach ($comments_to_show as $comment)
{
    $script .= "default_events_list[++default_events_count] = {$comment};\n";
}

foreach ($states_to_show as $state)
{
    $script .= "default_events_list[++default_events_count] = {$state};\n";
}

// JS for showing default fieldsets
$xml .= "<script>\n{$script}ResetToDefaults();\n</script>\n";

if (can_comment_be_added($record, $permissions))
{
    $xml .= '<form name="comment" action="comment.php?id=' . $id . '">'
          . '<group>'
          . '<textbox label="' . get_html_resource(RES_COMMENT_ID) . '" form="comment" name="comment" width="' . HTML_TEXTBOX_WIDTH . '" height="' . HTML_TEXTBOX_MIN_HEIGHT . '" resizeable="true" maxlen="' . MAX_COMMENT_BODY . '"></textbox>'
          . '</group>'
          . '<button default="true">' . get_html_resource(RES_ADD_COMMENT_ID) . '</button>';

    if ($permissions & PERMIT_CONFIDENTIAL_COMMENTS)
    {
        $xml .= '<button action="document.comment.action=\'comment.php?id=' . $id . '&amp;confidential=1\'; document.comment.submit();">' . get_html_resource(RES_ADD_CONFIDENTIAL_COMMENT_ID) . '</button>';
    }

    $xml .= '<note>' . get_html_resource(RES_LINK_TO_ANOTHER_RECORD_ID) . '</note>'
          . '</form>';
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Comment cannot be added.');
}

$xml .= '</content>'
      . '</page>';

if ($dump_mode)
{
    header('Pragma: private');
    header('Cache-Control: private, must-revalidate');
    header('Content-type: text/txt');
    header('Content-Disposition: attachment; filename=dump-' . $id . '.txt');

    $dump = xml2html($xml, 'dump.xsl');
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
    echo(xml2html($xml));
}

?>
