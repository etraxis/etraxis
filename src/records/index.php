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
//  Artem Rodygin           2005-03-26      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-07-02      bug-004: Sorting of records list causes list failure.
//  Artem Rodygin           2005-07-02      bug-007: Descending sorting of records by ID sorts them wrong.
//  Artem Rodygin           2005-07-20      new-009: Records filter.
//  Artem Rodygin           2005-07-23      new-011: Color scheme should be modified.
//  Artem Rodygin           2005-07-28      new-012: Records field 'description' should be renamed with 'subject'.
//  Artem Rodygin           2005-07-30      new-006: Records search.
//  Artem Rodygin           2005-08-02      new-017: Email notifications filter.
//  Artem Rodygin           2005-08-23      new-053: All the calls of DAL API functions should be moved to DBO API.
//  Artem Rodygin           2005-08-25      new-060: The 'Notifications' button should be unavailable when email notifications functionality is disabled.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-09-06      new-095: Newly created records should be displayed as unread.
//  Artem Rodygin           2005-09-07      new-099: Record creator should be displayed in list of record.
//  Artem Rodygin           2005-09-12      new-106: Mark closed records in the list with different color.
//  Artem Rodygin           2005-09-12      new-109: Remove user login from list of records to decrease size of 'Author' and 'Responsible' columns.
//  Artem Rodygin           2005-09-13      new-114: Change order of columns in the records list.
//  Artem Rodygin           2005-09-17      new-128: Lists are malfunctioning in Opera.
//  Artem Rodygin           2005-09-17      new-125: Email notifications advanced filter.
//  Artem Rodygin           2005-09-18      new-073: Implement search folders.
//  Artem Rodygin           2005-09-27      new-141: Source code review.
//  Artem Rodygin           2005-10-05      new-145: Remove autofocus from buttons.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-11-08      bug-174: Generated pages should contain <!DOCTYPE> tag.
//  Artem Rodygin           2005-11-17      new-176: Change eTraxis design.
//  Artem Rodygin           2005-11-26      new-185: Special color for postponed records.
//  Artem Rodygin           2006-01-20      new-199: Buttons of 'Records' and 'Record xxx-000' pages should be moved at top for convenience.
//  Artem Rodygin           2006-02-10      new-197: Postpone should have a timer for autoresume.
//  Artem Rodygin           2006-04-01      new-233: Email subscriptions functionality (new-125) should be conditionally "compiled".
//  Artem Rodygin           2006-04-09      new-235: Records with new events should be marked as "unread".
//  Artem Rodygin           2006-06-25      new-222: Email reminders.
//  Artem Rodygin           2006-06-28      bug-273: 'Reminders' button should be disabled if no reminder can be created or send.
//  Artem Rodygin           2006-07-27      new-261: UI design should be adopted to slow connection.
//  Artem Rodygin           2006-08-13      new-304: Updated records should be displayed as unread.
//  Artem Rodygin           2006-08-14      bug-310: No records are displayed when list has been sorted by responsible.
//  Artem Rodygin           2006-10-08      bug-357: /src/records/index.php: Global variables $page and $sort were used before they were defined.
//  Artem Rodygin           2006-11-07      new-366: Export to CSV.
//  Artem Rodygin           2006-11-20      new-377: Custom views.
//  Artem Rodygin           2006-11-26      bug-404: Invalid values of 'date' and 'duration' fields in view.
//  Artem Rodygin           2006-12-01      bug-410: View does not show values of custom fields.
//  Artem Rodygin           2006-12-04      bug-417: SQL time is too large when no filters are applied.
//  Artem Rodygin           2006-12-15      new-445: Changing search form method from POST to GET will suppress 'Page is expired' notice when 'Back' browser button is pressed.
//  Artem Rodygin           2007-02-03      new-496: [SF1650934] to show value of "list" instead of index in "records" list
//  Artem Rodygin           2007-03-18      bug-507: 'Back' button does not work on 'Search results' page.
//  Artem Rodygin           2007-10-02      new-513: Apply current filter set to search results.
//  Artem Rodygin           2007-10-29      new-564: Filters set.
//  Artem Rodygin           2007-11-05      new-571: View should show all records of current filters set.
//  Artem Rodygin           2007-11-11      new-619: Replace 'none' with empty space.
//  Artem Rodygin           2007-11-13      bug-620: XML failure when view contains column with '&' in its state or field name.
//  Artem Rodygin           2007-11-13      new-599: Separated "Age" in custom views.
//  Artem Rodygin           2007-11-27      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2007-11-30      new-617: Add 'no view' and 'no filter set' to related comboboxes.
//  Yury Udovichenko        2007-12-25      new-485: Text formating in comments.
//  Yury Udovichenko        2007-12-28      new-656: BBCode // List of tags, allowed in subject, should be limited.
//  Ewoudt Kellerman        2008-04-09      new-700: Default focus on the list of records page.
//  Artem Rodygin           2008-04-25      bug-713: JavaScript Error: document.mainform has no properties
//  Artem Rodygin           2008-04-30      bug-714: Empty date fields show '12/31/1969' in a custom view.
//  Artem Rodygin           2008-05-01      new-715: Show creation time in the list of records.
//  Artem Rodygin           2008-07-02      new-729: [SF2008579] Mark all records as read
//  Artem Rodygin           2008-11-09      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2008-11-18      new-762: Forward logged in user to the page he has tried to open before authentication.
//  Artem Rodygin           2009-03-05      new-797: Numbers and durations should be aligned to right.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/records.php');
require_once('../dbo/filters.php');
require_once('../dbo/views.php');
require_once('../dbo/reminders.php');
/**#@-*/

global $column_type_align;
global $column_type_res;

init_page(GUEST_IS_ALLOWED);

mb_regex_encoding('UTF-8');

$search_mode = try_cookie(COOKIE_SEARCH_MODE, FALSE);
$search_text = try_cookie(COOKIE_SEARCH_TEXT);

if (isset($_REQUEST['search']))
{
    debug_write_log(DEBUG_NOTICE, 'REQUEST["search"] is set.');

    $search_text = ustrcut($_REQUEST['search'], MAX_SEARCH_TEXT);

    if (ustrlen($search_text) == 0 || mb_eregi('^"( )*"$', $search_text))
    {
        $search_mode = FALSE;
        $search_text = try_cookie(COOKIE_SEARCH_TEXT);
    }
    else
    {
        $search_mode = TRUE;

        save_cookie(COOKIE_SEARCH_TEXT, $search_text);
    }

    save_cookie(COOKIE_SEARCH_MODE, $search_mode);
}

if (isset($_REQUEST['use_filters']))
{
    $_SESSION[VAR_USE_FILTERS] = (bool) $_REQUEST['use_filters'];
}

if (isset($_SESSION[VAR_ERROR]))
{
    switch ($_SESSION[VAR_ERROR])
    {
        case NO_ERROR:
            $alert = NULL;
            break;
        case ERROR_UNAUTHORIZED:
            $alert = get_js_resource(RES_ALERT_USER_NOT_AUTHORIZED_ID);
            break;
        case ERROR_UNKNOWN_USERNAME:
            $alert = get_js_resource(RES_ALERT_UNKNOWN_USERNAME_ID);
            break;
        case ERROR_ACCOUNT_DISABLED:
            $alert = get_js_resource(RES_ALERT_ACCOUNT_DISABLED_ID);
            break;
        case ERROR_ACCOUNT_LOCKED:
            $alert = get_js_resource(RES_ALERT_ACCOUNT_LOCKED_ID);
            break;
        default:
            $alert = get_js_resource(RES_ALERT_UNKNOWN_ERROR_ID);
    }

    $_SESSION[VAR_ERROR] = NO_ERROR;
}

$xml = '<page' . gen_xml_page_header(get_html_resource($search_mode ? ($_SESSION[VAR_USE_FILTERS] ? RES_SEARCH_RESULTS_FILTERED_ID : RES_SEARCH_RESULTS_UNFILTERED_ID) : RES_RECORDS_ID), (isset($alert) ? $alert : NULL), ($search_mode ? NULL : 'mainform.id')) . '>'
     . gen_xml_menu()
     . '<path>'
     . gen_xml_rec_root($search_mode)
     . '</path>'
     . '<content>';

if (!$search_mode)
{
    $xml .= '<form name="mainform" method="get" action="view.php">'
          . '<smallbox name="id" size="5" maxlen="' . ustrlen(MAXINT) . '"/>'
          . '<button default="true">' . get_html_resource(RES_GO_ID) . '</button>'
          . '<nbsp/><nbsp/>';

    if (get_user_level() != USER_LEVEL_GUEST)
    {
        if (can_record_be_created())
        {
            $xml .= '<button url="create.php">' . get_html_resource(RES_CREATE_ID) . '</button>';
        }

        $xml .= '<button url="filter.php">' . get_html_resource(RES_FILTERS_ID) . '</button>'
              . '<button url="column.php">' . get_html_resource(RES_COLUMNS_ID) . '</button>';

        if (EMAIL_NOTIFICATIONS_ENABLED)
        {
            $xml .= '<button url="subscribe.php">' . get_html_resource(RES_SUBSCRIPTIONS_ID) . '</button>';

            if (can_reminder_be_created())
            {
                $xml .= '<button url="reminders.php">' . get_html_resource(RES_REMINDERS_ID) . '</button>';
            }
        }

        $xml .= '<button url="index.php?read=1">' . get_html_resource(RES_MARK_AS_READ_ID) . '</button>';
    }

    $xml .= '<button url="export.php">' . get_html_resource(RES_EXPORT_ID) . '</button>'
          . '</form>';

    if (get_user_level() != USER_LEVEL_GUEST)
    {
        $xml .= '<form name="comboform" action="setview.php">'
              . '<combobox name="fset">'
              . '<listitem value="0">' . get_html_resource(RES_NO_FILTERS_SET_ID) . '</listitem>';

        if (is_null($_SESSION[VAR_FSET]))
        {
            $xml .= '<listitem value="-1" selected="true">' . get_html_resource(RES_CURRENT_FILTERS_SET_ID) . '</listitem>';
        }

        $fsets = fsets_list();

        while (($row = $fsets->fetch()))
        {
            $xml .= '<listitem value="' . $row['fset_id'] . ($_SESSION[VAR_FSET] == $row['fset_id'] ? '" selected="true">' : '">') . ustr2html($row['fset_name']) . '</listitem>';
        }

        $xml .= '</combobox>'
              . '<combobox name="view">'
              . '<listitem value="0">' . get_html_resource(RES_NO_VIEW_ID) . '</listitem>';

        if (is_null($_SESSION[VAR_VIEW]))
        {
            $xml .= '<listitem value="-1" selected="true">' . get_html_resource(RES_CURRENT_VIEW_ID) . '</listitem>';
        }

        $views = view_list();

        while (($row = $views->fetch()))
        {
            $xml .= '<listitem value="' . $row['view_id'] . ($_SESSION[VAR_VIEW] == $row['view_id'] ? '" selected="true">' : '">') . ustr2html($row['view_name']) . '</listitem>';
        }

        $xml .= '</combobox>'
              . '<button default="true">' . get_html_resource(RES_SET_ID) . '</button>'
              . '</form>';
    }
}

$columns = column_list();

$sort = $page = NULL;
$list = record_list($columns, $sort, $page, $search_mode, $search_text);

$rec_from = $rec_to = 0;

if ($list->rows != 0)
{
    $xml .= '<list>'
          . gen_xml_bookmarks($page, $list->rows, $rec_from, $rec_to)
          . '<hrow>';

    foreach ($columns as $i => $column)
    {
        $smode = ($sort == $i + 1 ? ($i + 1 + count($columns)) : $i + 1);

        $xml .= '<hcell url="index.php?sort=' . $smode . '&amp;page=' . $page . '" align="left">';

        if ($column['column_type'] >= COLUMN_TYPE_MINIMUM &&
            $column['column_type'] <= COLUMN_TYPE_MAXIMUM)
        {
            $xml .= get_html_resource($column_type_res[$column['column_type']]);
        }
        else
        {
            $xml .= ustr2html($column['field_name']);
        }

        $xml .= '</hcell>';
    }

    $xml .= '</hrow>';

    $mark_as_read = (isset($_REQUEST['read']) && $_REQUEST['read'] == 1);
    $read_time    = time();

    $list->seek($rec_from - 1);

    for ($i = $rec_from; $i <= $rec_to; $i++)
    {
        $row = $list->fetch();
        $url = ' url="view.php?id=' . $row['record_id'] . '"';

        if ($mark_as_read && get_user_level() != USER_LEVEL_GUEST)
        {
            dal_query('records/unread.sql', $row['record_id'], $_SESSION[VAR_USERID]);
            dal_query('records/read.sql',   $row['record_id'], $_SESSION[VAR_USERID], $read_time);

            $row['read_time'] = $read_time;
        }

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

        $bold = ((get_user_level() == USER_LEVEL_GUEST) || ($row['read_time'] >= $row['change_time'])
                ? NULL
                : ' bold="true"');

        $xml .= '<row' . $url . '>';

        foreach ($columns as $column)
        {
            $value = NULL;
            $wrap  = NULL;
            $align = ' align="left"';

            switch ($column['column_type'])
            {
                case COLUMN_TYPE_ID:
                    $value = record_id($row['record_id'], $row['template_prefix']);
                    break;

                case COLUMN_TYPE_STATE:
                    $value = ustr2html($row['state_abbr']);
                    break;

                case COLUMN_TYPE_PROJECT:
                    $value = ustr2html($row['project_name']);
                    break;

                case COLUMN_TYPE_SUBJECT:
                    $value = update_references($row['subject'], BBCODE_SEARCH_ONLY);
                    $wrap  = ' wrap="true"';
                    break;

                case COLUMN_TYPE_AUTHOR:
                    $value = ustr2html($row['author_fullname']);
                    break;

                case COLUMN_TYPE_RESPONSIBLE:
                    $value = ustr2html($row['responsible_fullname']);

                    if (is_null($value))
                    {
                        $value = '<i>' . get_html_resource(RES_NONE_ID) . '</i>';
                    }

                    break;

                case COLUMN_TYPE_LAST_EVENT:
                    $value = get_record_last_event($row);
                    $align = ' align="right"';
                    break;

                case COLUMN_TYPE_AGE:
                    $value = get_record_age($row);
                    $align = ' align="right"';
                    break;

                case COLUMN_TYPE_CREATION_DATE:
                    $value = get_date($row['creation_time']);
                    break;

                case COLUMN_TYPE_NUMBER:
                    $value = $row['value' . $column['column_id']];
                    $align = ' align="right"';
                    break;

                case COLUMN_TYPE_STRING:
                    $value = update_references($row['value' . $column['column_id']], BBCODE_SEARCH_ONLY);
                    $wrap  = ' wrap="true"';
                    break;

                case COLUMN_TYPE_MULTILINED:
                    $value = $row['value' . $column['column_id']];

                    if (ustrlen($value) > MAX_FIELD_STRING + 3)
                    {
                        $value = usubstr($value, 0, MAX_FIELD_STRING) . '...';
                    }

                    $value = update_references($value, BBCODE_SEARCH_ONLY);
                    $wrap  = ' wrap="true"';
                    break;

                case COLUMN_TYPE_CHECKBOX:

                    if (!is_null($row['value' . $column['column_id']]))
                    {
                        $value = get_html_resource($row['value' . $column['column_id']] == 0 ? RES_NO_ID : RES_YES_ID);
                    }

                    break;

                case COLUMN_TYPE_LIST_NUMBER:
                    $value = $row['value' . $column['column_id']];
                    $align = ' align="center"';
                    break;

                case COLUMN_TYPE_LIST_STRING:
                    $value = update_references($row['value' . $column['column_id']], BBCODE_SEARCH_ONLY);
                    break;

                case COLUMN_TYPE_RECORD:

                    if (!is_null($row['value' . $column['column_id']]))
                    {
                        $value = record_id($row['value' . $column['column_id']]);
                    }

                    break;

                case COLUMN_TYPE_DATE:

                    if (!is_null($row['value' . $column['column_id']]))
                    {
                        $value = get_date($row['value' . $column['column_id']]);
                    }

                    break;

                case COLUMN_TYPE_DURATION:

                    if (!is_null($row['value' . $column['column_id']]))
                    {
                        $value = time2ustr($row['value' . $column['column_id']]);
                    }

                    $align = ' align="right"';
                    break;

                default:
                    debug_write_log(DEBUG_WARNING, 'Unknown column type.');
            }

            $xml .= '<cell' . $url . $style . $bold . $align . $wrap . '>' . $value . '</cell>';
        }

        $xml .= '</row>';
    }

    $xml .= '</list>';
}

if ($search_mode)
{
    $xml .= '<button url="index.php?search=">' . get_html_resource(RES_BACK_ID)   . '</button>'
          . '<button url="export.php">'        . get_html_resource(RES_EXPORT_ID) . '</button>';

    if (get_user_level() != USER_LEVEL_GUEST)
    {
        $xml .= '<button url="index.php?use_filters=' . (!$_SESSION[VAR_USE_FILTERS]) . '">'
              . get_html_resource($_SESSION[VAR_USE_FILTERS] ? RES_DISABLE_FILTERS_ID : RES_ENABLE_FILTERS_ID)
              . '</button>';
    }
}
else
{
    $xml .= '<form name="searchform" method="get" action="index.php">'
          . '<editbox name="search" size="' . HTML_EDITBOX_SIZE_LONG . '" maxlen="' . MAX_SEARCH_TEXT . '">' . ustr2html($search_text) . '</editbox>'
          . '<button default="true">' . get_html_resource(RES_SEARCH_ID) . '</button>'
          . '</form>';
}

$xml .= '</content>'
      . '</page>';

echo(xml2html($xml));

?>
