<?php

/**
 * Records
 *
 * This module provides API to work with records.
 * See also {@link http://www.etraxis.org/docs-schema.php#tbl_records tbl_records} database table.
 *
 * @package DBO
 * @subpackage Records
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
//  Artem Rodygin           2005-04-09      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-07-02      bug-007: Descending sorting of records by ID sorts them wrong.
//  Artem Rodygin           2005-07-04      new-002: Email notifications.
//  Artem Rodygin           2005-07-28      new-012: Records field 'description' should be renamed with 'subject'.
//  Artem Rodygin           2005-07-28      bug-014: PHP Notice: Undefined variable: event2
//  Artem Rodygin           2005-07-31      new-006: Records search.
//  Artem Rodygin           2005-08-02      new-017: Email notifications filter.
//  Artem Rodygin           2005-08-13      new-022: New records should be viewed immediately after creation.
//  Artem Rodygin           2005-08-13      new-020: Clone the records.
//  Artem Rodygin           2005-08-23      bug-045: When record is being cloned wrong event is recorded.
//  Artem Rodygin           2005-08-23      new-053: All the calls of DAL API functions should be moved to DBO API.
//  Artem Rodygin           2005-08-24      bug-055: List of changes does not filter values of forbidden fields.
//  Artem Rodygin           2005-08-26      new-058: Global groups should be implemented.
//  Artem Rodygin           2005-08-29      bug-066: Metrics of different projects contain same data.
//  Artem Rodygin           2005-08-30      new-074: Increase maximum length of attachment name up to 100 characters.
//  Artem Rodygin           2005-08-30      bug-080: 'Record' type fields of some record should not accept ID of this record.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-09-04      bug-085: Members of global groups cannot view project records if they haven't any permissions in the project.
//  Artem Rodygin           2005-09-05      bug-091: Members of global groups are not able to create records while are allowed.
//  Artem Rodygin           2005-09-06      new-095: Newly created records should be displayed as unread.
//  Artem Rodygin           2005-09-07      new-099: Record creator should be displayed in list of record.
//  Artem Rodygin           2005-09-07      new-102: Increase maximum length of comments and 'multilined text' fields up to 4000 characters.
//  Artem Rodygin           2005-09-07      new-100: 'Date' field type should be implemented.
//  Artem Rodygin           2005-09-08      new-101: 'Duration' field type should be implemented.
//  Artem Rodygin           2005-09-13      new-114: Change order of columns in the records list.
//  Artem Rodygin           2005-09-15      new-120: Default field values of cloned records.
//  Artem Rodygin           2005-09-15      new-122: User should be able to create a filter to display postponed records only.
//  Artem Rodygin           2005-09-17      new-126: States/field values and comments should be displayed one by one.
//  Artem Rodygin           2005-09-18      new-073: Implement search folders.
//  Artem Rodygin           2005-09-27      new-141: Source code review.
//  Artem Rodygin           2005-10-16      bug-161: False modification event.
//  Artem Rodygin           2005-12-11      bug-180: Previous field values are lost.
//  Artem Rodygin           2006-01-22      bug-198: Attached pictures are not shown properly in Opera.
//  Artem Rodygin           2006-01-23      new-200: User should not been requested for attachment name - current one should be always used.
//  Artem Rodygin           2006-01-24      new-203: Email notification functionality (new-002) should be conditionally "compiled".
//  Artem Rodygin           2006-02-10      new-197: Postpone should have a timer for autoresume.
//  Artem Rodygin           2006-03-15      bug-212: Wrong message when date value is out of range.
//  Artem Rodygin           2006-03-19      new-175: Implement user roles in permissions.
//  Artem Rodygin           2006-03-19      bug-216: Permission to create records is ignored.
//  Artem Rodygin           2006-03-20      bug-217: Cannot create new record.
//  Artem Rodygin           2006-03-20      bug-218: Server is overloaded.
//  Artem Rodygin           2006-03-24      new-224: 'Age' column should contain number of days since last action was applied to record (opened only).
//  Artem Rodygin           2006-03-24      new-223: Links should be functional.
//  Artem Rodygin           2006-03-25      bug-225: User is remained on record modification page when 'OK' button has been clicked.
//  Artem Rodygin           2006-03-26      bug-229: Records filters are malfunctional.
//  Artem Rodygin           2006-04-01      bug-232: Links started with 'www' contain invalid URL.
//  Artem Rodygin           2006-04-09      new-235: Records with new events should be marked as "unread".
//  Artem Rodygin           2006-04-21      new-247: The 'responsible' user role should be obliterated.
//  Artem Rodygin           2006-04-22      new-237: Found text should be marked with red when search is activated.
//  Artem Rodygin           2006-04-26      bug-248: Search text in filter is ignored and text from last search is used instead of.
//  Artem Rodygin           2006-04-26      bug-249: Unexpected usage of search text when list of records is being displayed.
//  Artem Rodygin           2006-05-07      new-251: Traceability logging review.
//  Artem Rodygin           2006-05-17      new-005: Oracle support.
//  Artem Rodygin           2006-05-23      bug-262: PHP Warning: ocifetchinto(): OCILobRead: ORA-24806: LOB form mismatch
//  Artem Rodygin           2006-06-19      new-236: Single record subscription.
//  Artem Rodygin           2006-06-25      bug-269: Multilined text values are cut to 1000 characters.
//  Artem Rodygin           2006-06-25      new-222: Email reminders.
//  Artem Rodygin           2006-06-29      bug-287: dbx_error(): Unknown table 'r' in order clause
//  Artem Rodygin           2006-07-24      bug-201: 'Access Forbidden' error with cyrillic named attachments.
//  Artem Rodygin           2006-08-03      bug-299: PHP Warning: mb_strpos(): Empty needle
//  Artem Rodygin           2006-08-13      new-304: Updated records should be displayed as unread.
//  Artem Rodygin           2006-08-14      bug-310: No records are displayed when list has been sorted by responsible.
//  Artem Rodygin           2006-08-21      bug-314: Newly created record shows zero age.
//  Artem Rodygin           2006-09-26      new-318: Group permissions should be template-wide.
//  Artem Rodygin           2006-10-08      bug-334: /src/dbo/records.php: Variable $strvalue appears only once.
//  Artem Rodygin           2006-10-16      new-137: Custom queries.
//  Artem Rodygin           2006-10-17      new-361: Extended custom queries.
//  Artem Rodygin           2006-11-12      bug-380: Single record subscription functionality (new-236) should be conditionally "compiled".
//  Artem Rodygin           2006-11-13      new-368: User should be able to subscribe other persons.
//  Artem Rodygin           2006-11-15      bug-381: Attachments of some types are not opened in valid applications.
//  Artem Rodygin           2006-11-22      new-377: Custom views.
//  Artem Rodygin           2006-11-26      bug-397: View contains records of all existing templates.
//  Artem Rodygin           2006-11-26      bug-400: PHP Warning: odbc_exec(): SQL error: Invalid column name 'author_fullname'.
//  Artem Rodygin           2006-11-26      bug-401: PHP Warning: odbc_exec(): SQL error: The text data type cannot be compared or sorted.
//  Artem Rodygin           2006-12-04      bug-416: Metrics charts display wrong numbers.
//  Artem Rodygin           2006-12-04      bug-417: SQL time is too large when no filters are applied.
//  Artem Rodygin           2006-12-10      new-422: Increase maximum length of string fields.
//  Artem Rodygin           2006-12-10      new-433: Replacing URLs with links is faster with regular expressions.
//  Artem Rodygin           2006-12-11      bug-437: URLs with '<' and '>' characters are not correctly highlighted.
//  Artem Rodygin           2006-12-14      new-446: Add processing of new upload errors.
//  Artem Rodygin           2006-12-15      bug-449: URLs with spaces are cut.
//  Artem Rodygin           2006-12-17      bug-456: PHP Warning: ociexecute(): OCIStmtExecute: ORA-00904: "CEILING": invalid identifier
//  Artem Rodygin           2006-12-26      bug-465: When template is locked all records created by this template must be read only.
//  Artem Rodygin           2006-12-27      bug-470: State permissions must not be used when record is being created.
//  Artem Rodygin           2007-01-11      new-477: User should have ability to comment postponed records.
//  Artem Rodygin           2007-01-11      new-479: Assigned user should not receive notification about changed state.
//  Artem Rodygin           2007-02-03      new-496: [SF1650934] to show value of "list" instead of index in "records" list
//  Artem Rodygin           2007-03-18      bug-498: XML Error: extra content at the end of the document.
//  Artem Rodygin           2007-07-04      new-533: Links between records.
//  Artem Rodygin           2007-07-04      bug-541: PHP Warning: mb_strpos(): Empty delimiter.
//  Artem Rodygin           2007-07-12      new-544: The 'ctype' library should not be used.
//  Artem Rodygin           2007-07-16      new-546: Confidential comments.
//  Artem Rodygin           2007-08-06      new-551: Rework dependencies into "parent-child" relations.
//  Artem Rodygin           2007-08-18      bug-557: PHP Warning: Missing argument 4 for comment_add()
//  Artem Rodygin           2007-09-12      new-574: Filter should allow to specify several states.
//  Artem Rodygin           2007-09-29      new-568: Permissions to operate with record should not depend on permission to view the record.
//  Artem Rodygin           2007-10-01      bug-586: PHP Warning: odbc_exec(): SQL error: Invalid column name 'date1'.
//  Artem Rodygin           2007-10-01      bug-585: PHP Warning: odbc_exec(): SQL error: Operand data type numeric is invalid for modulo operator.
//  Artem Rodygin           2007-10-01      bug-587: Filtering by field values doesn't work correct.
//  Artem Rodygin           2007-10-02      new-513: Apply current filter set to search results.
//  Artem Rodygin           2007-10-08      bug-591: When URL is put in round brackets, it's being opened with right bracket on the end.
//  Artem Rodygin           2007-10-23      bug-604: Search of 'MMC / MME' causes broken page.
//  Artem Rodygin           2007-11-05      new-571: View should show all records of current filters set.
//  Artem Rodygin           2007-11-13      new-599: Separated "Age" in custom views.
//  Artem Rodygin           2007-11-13      new-622: Rename 'children' into 'subrecords'.
//  Yury Udovichenko        2007-11-14      new-548: Custom links in text fields.
//  Artem Rodygin           2007-11-15      bug-628: PHP Warning: odbc_exec(): SQL error: Incorrect syntax near ','.
//  Yury Udovichenko        2007-11-15      bug-629: Having '&' in field's regex could break the page generation.
//  Ewoudt Kellerman        2007-11-19      bug-630: Error when choosing a multiline value to include as a column.
//  Yury Udovichenko        2007-11-20      new-536: Ability to hide postpone records from the list.
//  Yury Udovichenko        2007-11-20      bug-631: Links with port numbers are not shown as links.
//  Artem Rodygin           2007-11-26      new-633: The 'dbx' extension should not be used.
//  Yury Udovichenko        2007-11-26      new-485: Text formating in comments.
//  Artem Rodygin           2007-12-24      bug-650: Search overloads server.
//  Artem Rodygin           2007-12-27      new-657: BBCode // Ability to display tags as is.
//  Yury Udovichenko        2007-12-28      new-656: BBCode // List of tags, allowed in subject, should be limited.
//  Artem Rodygin           2008-01-05      new-648: Template-wide author permissions.
//  Artem Rodygin           2008-01-11      bug-661: MS SQL Server is overloaded.
//  Artem Rodygin           2008-01-11      bug-663: Author permissions are ignored.
//  Artem Rodygin           2008-01-16      bug-665: Notifications // Author permissions are ignored.
//  Yury Udovichenko        2008-01-18      bug-667: XML Parsing Error: not well-formed
//  Artem Rodygin           2008-01-28      new-531: LDAP Guest users
//  Artem Rodygin           2008-01-31      bug-670: Hardcoded user ID in SQL query for records list.
//  Artem Rodygin           2008-02-08      bug-673: Newly created field always has empty strings as its regexps instead of NULL.
//  Denis Makovkin          2008-02-15      bug-674: [SF1893539] Incorrect charset in "Subject" email notifications
//  Artem Rodygin           2008-02-20      bug-675: PHP Warning: preg_replace(): Unknown modifier ')'
//  Artem Rodygin           2008-02-27      new-535: Permissions to attachments removal.
//  Artem Rodygin           2008-02-27      new-676: [SF1898731] Delete Issues from Workflow
//  Artem Rodygin           2008-02-28      new-294: PostgreSQL support.
//  Artem Rodygin           2008-03-15      new-683: Filters should be sharable with groups, not with accounts.
//  Artem Rodygin           2008-03-15      new-501: Filter should allow to specify 'none' value of 'list' fields.
//  Artem Rodygin           2008-04-03      new-694: Filter for unassigned records.
//  Artem Rodygin           2008-04-19      new-705: Multiple parents for subrecords.
//  Artem Rodygin           2008-04-20      new-703: Separated permissions set for current responsible.
//  Artem Rodygin           2008-04-24      bug-711: Cannot skip optional field if it has checking PCRE.
//  Artem Rodygin           2008-04-25      bug-712: Search doesn't work with MySQL 4.1.
//  Artem Rodygin           2008-04-26      bug-707: Permission to view records for responsible does not work in the list of records.
//  Artem Rodygin           2008-05-01      new-715: Show creation time in the list of records.
//  Artem Rodygin           2008-06-21      new-723: Wrap calls of 'mail' function.
//  Artem Rodygin           2008-06-21      bug-721: [SF1982395] DBX error when saving multi-lined textbox
//  Yury Udovichenko        2008-06-26      bug-726: References to other records are parsed wrong, when there are several of them.
//  Artem Rodygin           2008-06-30      bug-727: Notifications are not sent via Lotus Domino SMTP server.
//  Artem Rodygin           2008-07-31      bug-736: Search tags are contained in subject of notification when event is for one of records from the search results list.
//  Artem Rodygin           2008-09-09      bug-741: BBCode tag [code] adds extra newline-character at bottom of code block.
//  Artem Rodygin           2008-09-11      new-716: 'Today' value in date field range.
//  Artem Rodygin           2008-09-17      new-743: Include attached files in the notification.
//  Artem Rodygin           2008-10-27      bug-695: BBCode // Address between [url] and [/url] is cut when contains a space.
//  Artem Rodygin           2008-11-08      bug-760: Backslashes are lost in checking PCRE patterns for textual fields.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2008-11-18      bug-765: Query for list of records is slowed down.
//  Artem Rodygin           2008-12-03      bug-767: Multiple words search slows down database server.
//  Artem Rodygin           2008-12-09      bug-770: MySQL server hangs up on searching.
//  Artem Rodygin           2009-01-09      new-774: 'Anyone' system role permissions.
//  Artem Rodygin           2009-03-02      bug-796: 'rec#' reference doesn't work when leading zero is present.
//  Artem Rodygin           2009-03-27      bug-805: Regular expressions are ignored.
//  Artem Rodygin           2009-03-30      bug-811: Multilined text is cut on export to CSV.
//  Artem Rodygin           2009-04-12      bug-815: Empty "event" field in notification about subscription.
//  Artem Rodygin           2009-04-25      new-801: Range of valid date values must be related to current date.
//  Artem Rodygin           2009-06-05      new-824: PHP 4 is discontinued.
//  Artem Rodygin           2009-06-17      bug-825: Database gets empty strings instead of NULL values.
//  Artem Rodygin           2009-08-31      new-826: Native unicode support for Microsoft SQL Server.
//  Artem Rodygin           2009-09-06      new-827: Microsoft SQL Server 2005/2008 support.
//  Artem Rodygin           2009-10-01      new-845: Template name as standard column type.
//  Artem Rodygin           2009-10-25      new-851: State name as standard column type.
//  Artem Rodygin           2009-11-30      bug-858: Attaching a file is offered when creating new record, even if attachments are disabled or forbidden.
//  Artem Rodygin           2010-01-02      new-771: Multiple sort order.
//  Artem Rodygin           2010-01-26      bug-891: Attachments are not deleted when record is deleted
//  Artem Rodygin           2010-01-26      bug-892: English grammar correction
//  Giacomo Giustozzi       2010-01-28      new-902: Transparent gzip compression of attachments
//  Artem Rodygin           2010-02-06      bug-914: Change e-mail subject encoding
//  Artem Rodygin           2010-04-24      new-933: New column LS/T(Last State Time)
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/accounts.php');
require_once('../dbo/filters.php');
require_once('../dbo/fields.php');
require_once('../dbo/values.php');
require_once('../dbo/events.php');
/**#@-*/

//--------------------------------------------------------------------------------------------------
//  Definitions.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Data restrictions.
 */
define('MAX_RECORD_SUBJECT',  250);
define('MAX_COMMENT_BODY',    4000);
define('MAX_ATTACHMENT_NAME', 100);
define('MAX_SEARCH_TEXT',     100);
define('MAX_SEARCH_WORDS',    5);
/**#@-*/

/**#@+
 * Record operations.
 */
define('OPERATION_CREATE_RECORD', 1);
define('OPERATION_MODIFY_RECORD', 2);
define('OPERATION_CHANGE_STATE',  3);
/**#@-*/

//--------------------------------------------------------------------------------------------------
//  Functions.
//--------------------------------------------------------------------------------------------------

/**
 * Formats specified record ID, adding template prefix if specified and leading zeroes if required.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_records_record_id Record ID}.
 * @param string $prefix {@link http://www.etraxis.org/docs-schema.php#tbl_templates_template_prefix Template prefix}.
 * @return string Formatted record ID.
 */
function record_id ($id, $prefix = NULL)
{
    debug_write_log(DEBUG_TRACE, '[record_id]');
    debug_write_log(DEBUG_DUMP,  '[record_id] $id     = ' . $id);
    debug_write_log(DEBUG_DUMP,  '[record_id] $prefix = ' . $prefix);

    return ustr2html((is_null($prefix) ? NULL : $prefix . '-') . str_pad($id, 3, '0', STR_PAD_LEFT));
}

/**
 * Finds in database and returns the information about specified record.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_records_record_id Record ID}.
 * @return array Array with data if record is found in database, FALSE otherwise.
 */
function record_find ($id)
{
    debug_write_log(DEBUG_TRACE, '[record_find]');
    debug_write_log(DEBUG_DUMP,  '[record_find] $id = ' . $id);

    $rs = dal_query('records/fndid.sql', $id, time());

    return ($rs->rows == 0 ? FALSE : $rs->fetch());
}

/**
 * Returns {@link CRecordset DAL recordset} which contains all records, allowed to be displayed
 * in accordance to current set of filters, search mode, user permissions, etc.
 * Recordset is sorted in accordance with current sort mode.
 *
 * @param array $columns List of columns (see {@link column_list}).
 * @param int &$sort Sort mode (used as output only). The function retrieves current sort mode from
 * client cookie ({@link COOKIE_RECORDS_SORT}) and updates it, if it's out of valid range.
 * @param int &$page Number of current page tab (used as output only). The function retrieves current
 * page from client cookie ({@link COOKIE_RECORDS_PAGE}) and updates it, if it's out of valid range.
 * @param bool $search_mode Whether the search mode is on.
 * @param string $search_text Text to be searched (ignored when search mode is off).
 * @return CRecordset Recordset with list of records.
 */
function record_list ($columns, &$sort, &$page, $search_mode = FALSE, $search_text = NULL)
{
    debug_write_log(DEBUG_TRACE, '[record_list]');

    $sort = explode(':', try_cookie(COOKIE_RECORDS_SORT . $_SESSION[VAR_VIEW]), count($columns));
    $new  = try_request('sort', 0);

    if (try_request('reset', 0))
    {
        $sort = array($new);
    }
    else
    {
        foreach ($sort as $i => $s)
        {
            if (abs($s) == abs($new))
            {
                $sort[$i] = 0;
            }
        }

        array_push($sort, $new);
    }

    $page = try_request('page', try_cookie(COOKIE_RECORDS_PAGE . $_SESSION[VAR_VIEW]));
    $page = ustr2int($page, 1, MAXINT);

    $time = time();

    $clause_select = array('r.record_id',
                           'r.creation_time',
                           'r.change_time',
                           'r.closure_time',
                           'r.postpone_time',
                           't.critical_age');

    $clause_from   = array('tbl_projects p',
                           'tbl_states s');

    $clause_join   = array('tbl_records r');

    $clause_where  = array('p.project_id = t.project_id',
                           't.template_id = s.template_id',
                           's.state_id = r.state_id');

    $clause_order  = array();

    foreach ($sort as $s)
    {
        $keys = array_keys($clause_order);

        if (in_array(+$s, $keys) ||
            in_array(-$s, $keys))
        {
            continue;
        }

        if (abs($s) > 0 && abs($s) <= count($columns))
        {
            $clause_order[$s] = NULL;
        }
    }

    if (empty($clause_order))
    {
        $clause_order[1] = NULL;
    }

    $sort = array_keys($clause_order);

    save_cookie(COOKIE_RECORDS_SORT . $_SESSION[VAR_VIEW], implode(':', $sort));
    save_cookie(COOKIE_RECORDS_PAGE . $_SESSION[VAR_VIEW], $page);

    if (get_user_level() == USER_LEVEL_GUEST)
    {
        array_push($clause_select, '0 as read_time');
        array_push($clause_from,   'tbl_templates t');
        array_push($clause_where,  't.guest_access = 1');
    }
    else
    {
        $perms = 'select gp.template_id, count(gp.group_id) as gnum '
               . 'from tbl_membership ms, tbl_group_perms gp '
               . 'where ms.account_id = %1 and ms.group_id = gp.group_id and '
               . (DATABASE_DRIVER == DRIVER_ORACLE9 ? 'mod(floor(gp.perms / %2), 2) = 1 ' : '(gp.perms & %2) <> 0 ')
               . 'group by template_id';

        $perms = ustrprocess($perms, $_SESSION[VAR_USERID], PERMIT_VIEW_RECORD);

        array_push($clause_from,  "tbl_templates t left outer join ({$perms}) perms on t.template_id = perms.template_id");
        array_push($clause_where, ustrprocess('(perms.gnum is not null' .
                                              ' or r.creator_id = %1' .
                                              ' or r.responsible_id = %1' .
                                              ' or ' . (DATABASE_DRIVER == DRIVER_ORACLE9 ? 'mod(floor(t.registered_perm / %2), 2) = 1' : '(t.registered_perm & %2) <> 0') .
                                              ' or t.guest_access = 1)',
                                              $_SESSION[VAR_USERID], PERMIT_VIEW_RECORD));

        array_push($clause_select, 'rd.read_time');
        array_push($clause_join,   'left outer join tbl_reads rd on rd.record_id = r.record_id and rd.account_id = ' . $_SESSION[VAR_USERID]);
    }

    foreach ($columns as $i => $column)
    {
        $i += 1;

        switch ($column['column_type'])
        {
            case COLUMN_TYPE_ID:

                array_push($clause_select, 't.template_prefix');

                if (in_array($i, $sort))
                {
                    $clause_order[$i] = 'r.record_id';
                }
                elseif (in_array(-$i, $sort))
                {
                    $clause_order[-$i] = 'r.record_id desc';
                }

                break;

            case COLUMN_TYPE_STATE_ABBR:

                array_push($clause_select, 's.state_abbr');

                if (in_array($i, $sort))
                {
                    $clause_order[$i] = 's.state_abbr';
                }
                elseif (in_array(-$i, $sort))
                {
                    $clause_order[-$i] = 's.state_abbr desc';
                }

                break;

            case COLUMN_TYPE_PROJECT:

                array_push($clause_select, 'p.project_name');

                if (in_array($i, $sort))
                {
                    $clause_order[$i] = 'p.project_name';
                }
                elseif (in_array(-$i, $sort))
                {
                    $clause_order[-$i] = 'p.project_name desc';
                }

                break;

            case COLUMN_TYPE_SUBJECT:

                array_push($clause_select, 'r.subject');

                if (in_array($i, $sort))
                {
                    $clause_order[$i] = 'r.subject';
                }
                elseif (in_array(-$i, $sort))
                {
                    $clause_order[-$i] = 'r.subject desc';
                }

                break;

            case COLUMN_TYPE_AUTHOR:

                array_push($clause_select, 'ac.fullname as author_fullname');
                array_push($clause_join,   'left outer join tbl_accounts ac on ac.account_id = r.creator_id');

                if (in_array($i, $sort))
                {
                    $clause_order[$i] = 'author_fullname';
                }
                elseif (in_array(-$i, $sort))
                {
                    $clause_order[-$i] = 'author_fullname desc';
                }

                break;

            case COLUMN_TYPE_RESPONSIBLE:

                array_push($clause_select, 'ar.fullname as responsible_fullname');
                array_push($clause_join,   'left outer join tbl_accounts ar on ar.account_id = r.responsible_id');

                if (in_array($i, $sort))
                {
                    $clause_order[$i] = 'responsible_fullname';
                }
                elseif (in_array(-$i, $sort))
                {
                    $clause_order[-$i] = 'responsible_fullname desc';
                }

                break;

            case COLUMN_TYPE_LAST_EVENT:

                if (in_array($i, $sort))
                {
                    $clause_order[$i] = 'change_time';
                }
                elseif (in_array(-$i, $sort))
                {
                    $clause_order[-$i] = 'change_time desc';
                }

                break;

            case COLUMN_TYPE_AGE:

                array_push($clause_select, '(' . $time . ' - r.creation_time) as opened_age');
                array_push($clause_select, '(r.closure_time - r.creation_time) as closed_age');

                if (in_array($i, $sort))
                {
                    $clause_order[$i] = 'closed_age, opened_age';
                }
                elseif (in_array(-$i, $sort))
                {
                    $clause_order[-$i] = 'closed_age desc, opened_age desc';
                }

                break;

            case COLUMN_TYPE_CREATION_DATE:

                array_push($clause_select, 'r.creation_time');

                if (in_array($i, $sort))
                {
                    $clause_order[$i] = 'creation_time';
                }
                elseif (in_array(-$i, $sort))
                {
                    $clause_order[-$i] = 'creation_time desc';
                }

                break;

            case COLUMN_TYPE_TEMPLATE:

                array_push($clause_select, 't.template_name');

                if (in_array($i, $sort))
                {
                    $clause_order[$i] = 't.template_name';
                }
                elseif (in_array(-$i, $sort))
                {
                    $clause_order[-$i] = 't.template_name desc';
                }

                break;

            case COLUMN_TYPE_STATE_NAME:

                array_push($clause_select, 's.state_name');

                if (in_array($i, $sort))
                {
                    $clause_order[$i] = 's.state_name';
                }
                elseif (in_array(-$i, $sort))
                {
                    $clause_order[-$i] = 's.state_name desc';
                }

                break;

            case COLUMN_TYPE_LAST_STATE:

                array_push($clause_select, 'st.state_time');
                array_push($clause_from,   '(select record_id, max(event_time) as state_time' .
                                           ' from tbl_events' .
                                           ' where event_type = 1 or event_type = 4' .
                                           ' group by record_id) st');
                array_push($clause_where,  'r.record_id = st.record_id');

                if (in_array($i, $sort))
                {
                    $clause_order[$i] = 'state_time';
                }
                elseif (in_array(-$i, $sort))
                {
                    $clause_order[-$i] = 'state_time desc';
                }

                break;

            case COLUMN_TYPE_STRING:

                array_push($clause_select, "v{$column['column_id']}.value{$column['column_id']}");

                array_push($clause_join,
                           "left outer join " .
                           "(select e.record_id, sv.string_value as value{$column['column_id']} " .
                           "from tbl_states s, tbl_fields f, tbl_events e, tbl_field_values fv " .
                           "left outer join tbl_string_values sv on fv.value_id = sv.value_id " .
                           "where s.state_id = f.state_id and s.state_name = '{$column['state_name']}' and f.field_id = fv.field_id and f.field_name = '{$column['field_name']}' and f.field_type = " . FIELD_TYPE_STRING . " and e.event_id = fv.event_id and fv.is_latest = 1) v{$column['column_id']} " .
                           "on r.record_id = v{$column['column_id']}.record_id");

                if (in_array($i, $sort))
                {
                    $clause_order[$i] = "v{$column['column_id']}.value{$column['column_id']}";
                }
                elseif (in_array(-$i, $sort))
                {
                    $clause_order[-$i] = "v{$column['column_id']}.value{$column['column_id']} desc";
                }

                break;

            case COLUMN_TYPE_MULTILINED:

                $txtval = (DATABASE_DRIVER == DRIVER_MSSQL2K ? 'substring(tv.text_value, 1, 4000)' : 'substr(tv.text_value, 1, 4000)');

                if (DATABASE_DRIVER == DRIVER_ORACLE9)
                {
                    array_push($clause_select, "to_char(v{$column['column_id']}.value{$column['column_id']}) as \"value{$column['column_id']}\"");
                }
                else
                {
                    array_push($clause_select, "v{$column['column_id']}.value{$column['column_id']}");
                }

                array_push($clause_join,
                           "left outer join " .
                           "(select e.record_id, {$txtval} as value{$column['column_id']} " .
                           "from tbl_states s, tbl_fields f, tbl_events e, tbl_field_values fv " .
                           "left outer join tbl_text_values tv on fv.value_id = tv.value_id " .
                           "where s.state_id = f.state_id and s.state_name = '{$column['state_name']}' and f.field_id = fv.field_id and f.field_name = '{$column['field_name']}' and f.field_type = " . FIELD_TYPE_MULTILINED . " and e.event_id = fv.event_id and fv.is_latest = 1) v{$column['column_id']} " .
                           "on r.record_id = v{$column['column_id']}.record_id");

                if (in_array($i, $sort))
                {
                    $clause_order[$i] = (DATABASE_DRIVER == DRIVER_ORACLE9
                                      ? "to_char(v{$column['column_id']}.value{$column['column_id']})"
                                      : "v{$column['column_id']}.value{$column['column_id']}");
                }
                elseif (in_array(-$i, $sort))
                {
                    $clause_order[-$i] = (DATABASE_DRIVER == DRIVER_ORACLE9
                                       ? "to_char(v{$column['column_id']}.value{$column['column_id']}) desc"
                                       : "v{$column['column_id']}.value{$column['column_id']} desc");
                }

                break;

            case COLUMN_TYPE_LIST_STRING:

                array_push($clause_select, "v{$column['column_id']}.value{$column['column_id']}");

                array_push($clause_join,
                           "left outer join " .
                           "(select e.record_id, lv.str_value as value{$column['column_id']} " .
                           "from tbl_states s, tbl_fields f, tbl_events e, tbl_field_values fv " .
                           "left outer join tbl_list_values lv on fv.field_id = lv.field_id and fv.value_id = lv.int_value " .
                           "where s.state_id = f.state_id and s.state_name = '{$column['state_name']}' and f.field_id = fv.field_id and f.field_name = '{$column['field_name']}' and f.field_type = " . FIELD_TYPE_LIST . " and e.event_id = fv.event_id and fv.is_latest = 1) v{$column['column_id']} " .
                           "on r.record_id = v{$column['column_id']}.record_id");

                if (in_array($i, $sort))
                {
                    $clause_order[$i] = "v{$column['column_id']}.value{$column['column_id']}";
                }
                elseif (in_array(-$i, $sort))
                {
                    $clause_order[-$i] = "v{$column['column_id']}.value{$column['column_id']} desc";
                }

                break;

            case COLUMN_TYPE_NUMBER:
            case COLUMN_TYPE_CHECKBOX:
            case COLUMN_TYPE_LIST_NUMBER:
            case COLUMN_TYPE_RECORD:
            case COLUMN_TYPE_DATE:
            case COLUMN_TYPE_DURATION:

                $types = array
                (
                    COLUMN_TYPE_NUMBER      => FIELD_TYPE_NUMBER,
                    COLUMN_TYPE_CHECKBOX    => FIELD_TYPE_CHECKBOX,
                    COLUMN_TYPE_LIST_NUMBER => FIELD_TYPE_LIST,
                    COLUMN_TYPE_RECORD      => FIELD_TYPE_RECORD,
                    COLUMN_TYPE_DATE        => FIELD_TYPE_DATE,
                    COLUMN_TYPE_DURATION    => FIELD_TYPE_DURATION,
                );

                array_push($clause_select, "v{$column['column_id']}.value{$column['column_id']}");

                array_push($clause_join,
                           "left outer join " .
                           "(select e.record_id, fv.value_id as value{$column['column_id']} " .
                           "from tbl_states s, tbl_fields f, tbl_events e, tbl_field_values fv " .
                           "where s.state_id = f.state_id and s.state_name = '{$column['state_name']}' and f.field_id = fv.field_id and f.field_name = '{$column['field_name']}' and f.field_type = {$types[$column['column_type']]} and e.event_id = fv.event_id and fv.is_latest = 1) v{$column['column_id']} " .
                           "on r.record_id = v{$column['column_id']}.record_id");

                if (in_array($i, $sort))
                {
                    $clause_order[$i] = "v{$column['column_id']}.value{$column['column_id']}";
                }
                elseif (in_array(-$i, $sort))
                {
                    $clause_order[-$i] = "v{$column['column_id']}.value{$column['column_id']} desc";
                }

                break;

            default:
                debug_write_log(DEBUG_WARNING, '[record_list] Unknown column type = ' . $column['column_type']);
        }
    }

    if ($search_mode)
    {
        debug_write_log(DEBUG_NOTICE, '[record_list] Search mode is turned on.');

        $search = array();

        $search_text = "'%" . ustr2sql($search_text) . "%'";

        $search_in_subject  = (DATABASE_DRIVER == DRIVER_ORACLE9 ? 'lower' : NULL) . '(r.subject)       like ' . $search_text;
        $search_in_svalues  = (DATABASE_DRIVER == DRIVER_ORACLE9 ? 'lower' : NULL) . '(sv.string_value) like ' . $search_text;
        $search_in_tvalues  = (DATABASE_DRIVER == DRIVER_ORACLE9 ? 'lower' : NULL) . '(tv.text_value)   like ' . $search_text;
        $search_in_comments = (DATABASE_DRIVER == DRIVER_ORACLE9 ? 'lower' : NULL) . '(c.comment_body)  like ' . $search_text;

        array_push($search,
                   'select r.record_id ' .
                   'from tbl_records r ' .
                   'where ' . $search_in_subject);

        array_push($search,
                   'select e.record_id ' .
                   'from tbl_events e, tbl_field_values fv, tbl_string_values sv ' .
                   'where e.event_id = fv.event_id and fv.field_type = ' . FIELD_TYPE_STRING . ' and fv.value_id = sv.value_id and fv.is_latest = 1 and ' .
                   $search_in_svalues);

        array_push($search,
                   'select e.record_id ' .
                   'from tbl_events e, tbl_field_values fv, tbl_text_values tv ' .
                   'where e.event_id = fv.event_id and fv.field_type = ' . FIELD_TYPE_MULTILINED . ' and fv.value_id = tv.value_id and fv.is_latest = 1 and ' .
                   $search_in_tvalues);

        array_push($search,
                   'select e.record_id ' .
                   'from tbl_events e, tbl_comments c ' .
                   'where e.event_id = c.event_id and ' .
                   $search_in_comments);

        array_push($clause_where, 'r.record_id in (' . implode(' union ', $search) . ')');
    }

    if (!$search_mode || $_SESSION[VAR_USE_FILTERS])
    {
        debug_write_log(DEBUG_NOTICE, '[record_list] Search mode is turned off.');

        $filters = array();

        $rs = filters_list($_SESSION[VAR_USERID]);

        while (($filter = $rs->fetch()))
        {
            if ($filter['filter_flags'] == 0 &&
                $filter['filter_type']  == FILTER_TYPE_ALL_PROJECTS)
            {
                continue;
            }

            $clause_filter = array();

            switch ($filter['filter_type'])
            {
                case FILTER_TYPE_ALL_PROJECTS:
                    break;

                case FILTER_TYPE_ALL_TEMPLATES:
                    array_push($clause_select, 'p.project_id');
                    array_push($clause_filter, 'p.project_id = ' . $filter['filter_param']);
                    break;

                case FILTER_TYPE_ALL_STATES:
                    array_push($clause_select, 't.template_id');
                    array_push($clause_filter, 't.template_id = ' . $filter['filter_param']);
                    break;

                case FILTER_TYPE_SEL_STATES:
                    $states = filter_states_get($filter['filter_id'], $filter['filter_param']);
                    array_push($clause_select, 's.state_id');
                    array_push($clause_filter, 's.state_id in (' . implode(',', array_unique($states)) . ')');
                    break;

                default:
                    debug_write_log(DEBUG_WARNING, '[record_list] Unknown filter type = ' . $filter['filter_type']);
            }

            if ($filter['filter_flags'] & FILTER_FLAG_CREATED_BY)
            {
                array_push($clause_select, 'r.creator_id');
                array_push($clause_filter,
                           'r.creator_id in ' .
                           '(select account_id ' .
                           'from tbl_filter_accounts ' .
                           'where filter_id = ' . $filter['filter_id'] . ' and filter_flag = ' . FILTER_FLAG_CREATED_BY . ')');
            }

            if ($filter['filter_flags'] & FILTER_FLAG_ASSIGNED_TO)
            {
                array_push($clause_select, 'r.responsible_id');
                array_push($clause_filter,
                           'r.responsible_id in ' .
                           '(select account_id ' .
                           'from tbl_filter_accounts ' .
                           'where filter_id = ' . $filter['filter_id'] . ' and filter_flag = ' . FILTER_FLAG_ASSIGNED_TO . ')');
            }

            if ($filter['filter_flags'] & FILTER_FLAG_UNASSIGNED)
            {
                array_push($clause_select, 'r.responsible_id');
                array_push($clause_filter, 'r.responsible_id is null');
            }

            if (($filter['filter_type'] != FILTER_TYPE_SEL_STATES) &&
                ($filter['filter_flags'] & FILTER_FLAG_UNCLOSED))
            {
                array_push($clause_filter, 'r.closure_time is null');
            }

            if ($filter['filter_type'] != FILTER_TYPE_SEL_STATES)
            {
                if ($filter['filter_flags'] & FILTER_FLAG_POSTPONED)
                {
                    array_push($clause_filter, 'r.postpone_time > ' . $time);
                }

                if ($filter['filter_flags'] & FILTER_FLAG_ACTIVE)
                {
                    array_push($clause_filter, 'r.postpone_time <=' . $time);
                }
            }

            if ($filter['filter_type'] == FILTER_TYPE_ALL_STATES ||
                $filter['filter_type'] == FILTER_TYPE_SEL_STATES)
            {
                $rs2 = dal_query('filters/ftlist.sql', $filter['filter_id']);

                while (($row = $rs2->fetch()))
                {
                    array_push($clause_filter,
                               'r.record_id in ' .
                               '(select record_id ' .
                               'from tbl_events ' .
                               'where (event_type = ' . EVENT_RECORD_CREATED . ' or event_type = ' . EVENT_RECORD_STATE_CHANGED . ') and ' .
                               'event_time >= ' . $row['date1'] . ' and ' .
                               'event_time <= ' . $row['date2'] . ' and ' .
                               'event_param = ' . $row['state_id'] . ')');
                }

                $rs2 = dal_query('filters/fflist.sql', $filter['filter_id']);

                while (($row = $rs2->fetch()))
                {
                    switch ($row['field_type'])
                    {
                        case FIELD_TYPE_CHECKBOX:
                        case FIELD_TYPE_LIST:
                        case FIELD_TYPE_RECORD:

                            $value = (is_null($row['param1'])
                                      ? 'fv.value_id is null'
                                      : 'fv.value_id = ' . $row['param1']);

                            array_push($clause_filter,
                                       'r.record_id in ' .
                                       '(select e.record_id ' .
                                       'from tbl_events e, tbl_field_values fv ' .
                                       'where fv.event_id = e.event_id and ' .
                                       'fv.field_id = ' . $row['field_id'] . ' and ' .
                                       $value . ' and ' .
                                       'fv.is_latest = 1)');

                            break;

                        case FIELD_TYPE_NUMBER:
                        case FIELD_TYPE_DATE:
                        case FIELD_TYPE_DURATION:

                            $range = (is_null($row['param1']) && is_null($row['param2']) ? 'fv.value_id is null and ' : NULL);

                            if (!is_null($row['param1']))
                            {
                                $range .= 'fv.value_id >= ' . $row['param1'] . ' and ';
                            }

                            if (!is_null($row['param2']))
                            {
                                $range .= 'fv.value_id <= ' . $row['param2'] . ' and ';
                            }

                            array_push($clause_filter,
                                       'r.record_id in ' .
                                       '(select e.record_id ' .
                                       'from tbl_events e, tbl_field_values fv ' .
                                       'where fv.event_id = e.event_id and ' .
                                       'fv.field_id = '  . $row['field_id'] . ' and ' .
                                       $range .
                                       'fv.is_latest = 1)');

                            break;

                        case FIELD_TYPE_STRING:

                            if (is_null($row['param1']))
                            {
                                array_push($clause_filter,
                                           'r.record_id in ' .
                                           '(select e.record_id ' .
                                           'from tbl_events e, tbl_field_values fv ' .
                                           'where fv.event_id = e.event_id and ' .
                                           'fv.field_id = ' . $row['field_id'] . ' and ' .
                                           'fv.value_id is null and ' .
                                           'fv.is_latest = 1)');
                            }
                            else
                            {
                                switch (DATABASE_DRIVER)
                                {
                                    case DRIVER_MYSQL50:
                                    case DRIVER_ORACLE9:
                                        $concat = "concat('%', sf.string_value, '%')";
                                        break;
                                    case DRIVER_MSSQL2K:
                                        $concat = "'%' + sf.string_value + '%'";
                                        break;
                                    case DRIVER_PGSQL80:
                                        $concat = "'%' || sf.string_value || '%'";
                                        break;
                                    default: ;  // nop
                                }

                                array_push($clause_filter,
                                           'r.record_id in ' .
                                           '(select e.record_id ' .
                                           'from tbl_events e, tbl_field_values fv, tbl_string_values sv, tbl_string_values sf ' .
                                           'where fv.event_id = e.event_id and ' .
                                           'fv.field_id = ' . $row['field_id'] . ' and ' .
                                           'fv.value_id = sv.value_id and ' .
                                           'sf.value_id = ' . $row['param1'] . ' and ' .
                                           'sv.string_value like ' . $concat . ' and ' .
                                           'fv.is_latest = 1)');
                            }

                            break;

                        case FIELD_TYPE_MULTILINED:

                            if (is_null($row['param1']))
                            {
                                array_push($clause_filter,
                                           'r.record_id in ' .
                                           '(select e.record_id ' .
                                           'from tbl_events e, tbl_field_values fv ' .
                                           'where fv.event_id = e.event_id and ' .
                                           'fv.field_id = ' . $row['field_id'] . ' and ' .
                                           'fv.value_id is null and ' .
                                           'fv.is_latest = 1)');
                            }
                            else
                            {
                                switch (DATABASE_DRIVER)
                                {
                                    case DRIVER_MYSQL50:
                                    case DRIVER_ORACLE9:
                                        $concat = "concat('%', sf.string_value, '%')";
                                        break;
                                    case DRIVER_MSSQL2K:
                                        $concat = "'%' + sf.string_value + '%'";
                                        break;
                                    case DRIVER_PGSQL80:
                                        $concat = "'%' || sf.string_value || '%'";
                                        break;
                                    default: ;  // nop
                                }

                                array_push($clause_filter,
                                           'r.record_id in ' .
                                           '(select e.record_id ' .
                                           'from tbl_events e, tbl_field_values fv, tbl_text_values tv, tbl_string_values sf ' .
                                           'where fv.event_id = e.event_id and ' .
                                           'fv.field_id = ' . $row['field_id'] . ' and ' .
                                           'fv.value_id = tv.value_id and ' .
                                           'sf.value_id = ' . $row['param1'] . ' and ' .
                                           'tv.text_value like ' . $concat . ' and ' .
                                           'fv.is_latest = 1)');
                            }

                            break;
                    }
                }
            }

            array_push($filters, implode(' and ', array_unique($clause_filter)));
        }

        if (count($filters) != 0)
        {
            array_push($clause_where, '(' . implode(' or ', array_unique($filters)) . ')');
        }
    }

    $sql =
        'select '    . implode(', ',    array_unique($clause_select)) .
        ' from '     . implode(', ',    array_unique($clause_from))   .
        ', '         . implode(' ',     array_unique($clause_join))   .
        ' where '    . implode(' and ', array_unique($clause_where))  .
        ' order by ' . implode(', ',    array_unique($clause_order));

    return new CRecordset($sql);
}

/**
 * Returns {@link CRecordset DAL recordset} which contains number of created records per week for specified project.
 * Each row of returned recordset contains two fields:
 * <ul>
 * <li><b>week</b> - number of week after {@link http://en.wikipedia.org/wiki/Unix_time Unix Epoch}.</li>
 * <li><b>amount</b> - number of records, created during this week</li>
 * </ul>
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_projects_project_id ID} of project which records should be counted.
 * @return CRecordset Recordset with list of counts.
 */
function record_opened ($id)
{
    debug_write_log(DEBUG_TRACE, '[record_opened]');
    debug_write_log(DEBUG_DUMP,  '[record_opened] $id = ' . $id);

    return dal_query('records/opened.sql', $id, date('Z'), (DATABASE_DRIVER == DRIVER_ORACLE9 ? 'ceil' : 'ceiling'));
}

/**
 * Returns {@link CRecordset DAL recordset} which contains number of closed records per week for specified project.
 * Each row of returned recordset contains two fields:
 * <ul>
 * <li><b>week</b> - number of week after {@link http://en.wikipedia.org/wiki/Unix_time Unix Epoch}.</li>
 * <li><b>amount</b> - number of records, closed during this week</li>
 * </ul>
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_projects_project_id ID} of project which records should be counted.
 * @return CRecordset Recordset with list of counts.
 */
function record_closed ($id)
{
    debug_write_log(DEBUG_TRACE, '[record_closed]');
    debug_write_log(DEBUG_DUMP,  '[record_closed] $id = ' . $id);

    return dal_query('records/closed.sql', $id, date('Z'), (DATABASE_DRIVER == DRIVER_ORACLE9 ? 'ceil' : 'ceiling'));
}

/**
 * Validates record information (including all custom fields) before creation, modification, or changing state.
 *
 * @param int $operation Code of operation:
 * <ul>
 * <li>{@link OPERATION_CREATE_RECORD} - new record is going to be created</li>
 * <li>{@link OPERATION_MODIFY_RECORD} - record is going to be modified</li>
 * <li>{@link OPERATION_CHANGE_STATE} - state of record is going to be changed</li>
 * </ul>
 * @param string $subject {@link http://www.etraxis.org/docs-schema.php#tbl_records_subject Subject} of the record (ignored on state change).
 * @param int $record_id {@link http://www.etraxis.org/docs-schema.php#tbl_records_record_id Record ID} (should be NULL on creation).
 * @param int $state_id {@link http://www.etraxis.org/docs-schema.php#tbl_states_state_id ID of new state} (current on modification).
 * @param int $creator_id {@link http://www.etraxis.org/docs-schema.php#tbl_records_creator_id Author of record} (used only on modification, otherwise ignored).
 * @param int $responsible_id {@link http://www.etraxis.org/docs-schema.php#tbl_records_responsible_id Responsible of record} (used only on modification, otherwise ignored).
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - data are valid</li>
 * <li>{@link ERROR_INCOMPLETE_FORM} - at least one of required field is empty</li>
 * <li>{@link ERROR_INVALID_INTEGER_VALUE} - value of some custom field of {@link FIELD_TYPE_NUMBER}, {@link FIELD_TYPE_LIST}, or {@link FIELD_TYPE_RECORD} type is not an integer</li>
 * <li>{@link ERROR_INTEGER_VALUE_OUT_OF_RANGE} - value of some custom field of {@link FIELD_TYPE_NUMBER} or {@link FIELD_TYPE_LIST} type is out of valid range</li>
 * <li>{@link ERROR_VALUE_FAILS_REGEX_CHECK} - value of some custom field of {@link FIELD_TYPE_STRING} or {@link FIELD_TYPE_MULTILINED} type fails the custom PCRE check</li>
 * <li>{@link ERROR_RECORD_NOT_FOUND} - value of some custom field of {@link FIELD_TYPE_RECORD} type is not an ID of existing record</li>
 * <li>{@link ERROR_INVALID_DATE_VALUE} - value of some custom field of {@link FIELD_TYPE_DATE} type is not a valid date value</li>
 * <li>{@link ERROR_DATE_VALUE_OUT_OF_RANGE} - value of some custom field of {@link FIELD_TYPE_DATE} type is out of valid range</li>
 * <li>{@link ERROR_INVALID_TIME_VALUE} - value of some custom field of {@link FIELD_TYPE_DURATION} type is not a valid duration value</li>
 * <li>{@link ERROR_TIME_VALUE_OUT_OF_RANGE} - value of some custom field of {@link FIELD_TYPE_DURATION} type is out of valid range</li>
 * </ul>
 */
function record_validate ($operation, $subject, $record_id, $state_id, $creator_id = 0, $responsible_id = 0)
{
    debug_write_log(DEBUG_TRACE, '[record_validate]');
    debug_write_log(DEBUG_DUMP,  '[record_validate] $operation      = ' . $operation);
    debug_write_log(DEBUG_DUMP,  '[record_validate] $subject        = ' . $subject);
    debug_write_log(DEBUG_DUMP,  '[record_validate] $record_id      = ' . $record_id);
    debug_write_log(DEBUG_DUMP,  '[record_validate] $state_id       = ' . $state_id);
    debug_write_log(DEBUG_DUMP,  '[record_validate] $creator_id     = ' . $creator_id);
    debug_write_log(DEBUG_DUMP,  '[record_validate] $responsible_id = ' . $responsible_id);

    // Check the subject.
    if ($operation != OPERATION_CHANGE_STATE &&
        ustrlen($subject) == 0)
    {
        debug_write_log(DEBUG_NOTICE, '[record_validate] At least one required field is empty.');
        return ERROR_INCOMPLETE_FORM;
    }

    // Get the list of custom fields.
    if ($operation != OPERATION_MODIFY_RECORD)
    {
        $rs = dal_query('fields/list.sql', $state_id, 'field_order');
    }
    else
    {
        $rs = dal_query('records/flist.sql',
                        $record_id,
                        $state_id,
                        $creator_id,
                        is_null($responsible_id) ? 0 : $responsible_id,
                        $_SESSION[VAR_USERID],
                        FIELD_ALLOW_TO_WRITE);
    }

    // Check value candidates of all fields.
    // Values of all custom fields are passed to the function via $_REQUEST.
    while (($row = $rs->fetch()))
    {
        $name  = 'field' . $row['field_id'];
        $value = ($row['field_type'] == FIELD_TYPE_CHECKBOX ? isset($_REQUEST[$name]) : trim(try_request($name)));

        // Required custom fields must be filled in.
        if ($row['is_required'] &&
            $row['field_type'] != FIELD_TYPE_CHECKBOX &&
            ustrlen($value) == 0)
        {
            debug_write_log(DEBUG_NOTICE, '[record_validate] At least one required field is empty.');
            return ERROR_INCOMPLETE_FORM;
        }

        // Check value candidate of this field in correspondence with its type and template configuration.
        switch ($row['field_type'])
        {
            case FIELD_TYPE_NUMBER:

                if (ustrlen($value) != 0)
                {
                    if (!is_intvalue($value))
                    {
                        debug_write_log(DEBUG_NOTICE, '[record_validate] Invalid integer value.');
                        return ERROR_INVALID_INTEGER_VALUE;
                    }

                    $intvalue = intval($value);

                    if ($intvalue < $row['param1'] ||
                        $intvalue > $row['param2'])
                    {
                        $_SESSION['FIELD_NAME']        = $row['field_name'];
                        $_SESSION['MIN_FIELD_INTEGER'] = $row['param1'];
                        $_SESSION['MAX_FIELD_INTEGER'] = $row['param2'];
                        debug_write_log(DEBUG_NOTICE, '[record_validate] Integer value is out of range.');
                        return ERROR_INTEGER_VALUE_OUT_OF_RANGE;
                    }
                }

                break;

            case FIELD_TYPE_STRING:
            case FIELD_TYPE_MULTILINED:

                $value = ustrcut($value, $row['param1']);

                // if regexp to check is set - check if value matches it
                if (!is_null($row['regex_check']) && ustrlen($value) != 0)
                {
                    if (preg_match("/{$row['regex_check']}/", $value) == 0)
                    {
                        debug_write_log(DEBUG_NOTICE, '[record_validate] Field value fails regex check.');

                        $_SESSION['FIELD_NAME']  = $row['field_name'];
                        $_SESSION['FIELD_VALUE'] = $value;

                        return ERROR_VALUE_FAILS_REGEX_CHECK;
                    }
                }

                break;

            case FIELD_TYPE_CHECKBOX:
                break;  // nop

            case FIELD_TYPE_LIST:

                if (ustrlen($value) != 0)
                {
                    if (!is_intvalue($value))
                    {
                        debug_write_log(DEBUG_NOTICE, '[record_validate] Invalid list value.');
                        return ERROR_INVALID_INTEGER_VALUE;
                    }

                    $intvalue = intval($value);

                    if ($intvalue < 1 ||
                        $intvalue > MAXINT)
                    {
                        $_SESSION['FIELD_NAME']        = $row['field_name'];
                        $_SESSION['MIN_FIELD_INTEGER'] = 1;
                        $_SESSION['MAX_FIELD_INTEGER'] = MAXINT;
                        debug_write_log(DEBUG_NOTICE, '[record_validate] List value is out of range.');
                        return ERROR_INTEGER_VALUE_OUT_OF_RANGE;
                    }

                    $rsl = dal_query('values/lvfndk1.sql', $row['field_id'], $value);

                    if ($rsl->rows == 0)
                    {
                        debug_write_log(DEBUG_NOTICE, '[record_validate] List value not found.');
                        return ERROR_INVALID_INTEGER_VALUE;
                    }
                }

                break;

            case FIELD_TYPE_RECORD:

                if (ustrlen($value) != 0)
                {
                    if (!is_intvalue($value))
                    {
                        debug_write_log(DEBUG_NOTICE, '[record_validate] Invalid record ID.');
                        return ERROR_INVALID_INTEGER_VALUE;
                    }

                    if (intval($value) == $record_id)
                    {
                        debug_write_log(DEBUG_NOTICE, '[record_validate] The same record ID cannot be specified.');
                        return ERROR_RECORD_NOT_FOUND;
                    }

                    $record = record_find(intval($value));

                    if (!$record)
                    {
                        debug_write_log(DEBUG_NOTICE, '[record_validate] Record not found.');
                        return ERROR_RECORD_NOT_FOUND;
                    }
                }

                break;

            case FIELD_TYPE_DATE:

                if (ustrlen($value) != 0)
                {
                    $today = date_floor($operation == OPERATION_MODIFY_RECORD ? $row['event_time'] : time());

                    $row['param1'] = date_offset($today, $row['param1']);
                    $row['param2'] = date_offset($today, $row['param2']);

                    $date = ustr2date($value);

                    if ($date == -1)
                    {
                        debug_write_log(DEBUG_NOTICE, '[record_validate] Invalid date value.');
                        return ERROR_INVALID_DATE_VALUE;
                    }

                    if ($date < $row['param1'] ||
                        $date > $row['param2'])
                    {
                        debug_write_log(DEBUG_NOTICE, '[record_validate] Date value is out of range.');

                        $_SESSION['FIELD_NAME']        = $row['field_name'];
                        $_SESSION['MIN_FIELD_INTEGER'] = get_date($row['param1']);
                        $_SESSION['MAX_FIELD_INTEGER'] = get_date($row['param2']);

                        return ERROR_DATE_VALUE_OUT_OF_RANGE;
                    }
                }

                break;

            case FIELD_TYPE_DURATION:

                if (ustrlen($value) != 0)
                {
                    $duration = ustr2time($value);

                    if ($duration == -1)
                    {
                        debug_write_log(DEBUG_NOTICE, '[record_validate] Invalid duration value.');
                        return ERROR_INVALID_TIME_VALUE;
                    }

                    if ($duration < $row['param1'] ||
                        $duration > $row['param2'])
                    {
                        $_SESSION['FIELD_NAME']        = $row['field_name'];
                        $_SESSION['MIN_FIELD_INTEGER'] = time2ustr($row['param1']);
                        $_SESSION['MAX_FIELD_INTEGER'] = time2ustr($row['param2']);
                        debug_write_log(DEBUG_NOTICE, '[record_validate] Duration value is out of range.');
                        return ERROR_TIME_VALUE_OUT_OF_RANGE;
                    }
                }

                break;

            default:
                debug_write_log(DEBUG_WARNING, '[record_validate] Unknown field type = ' . $row['field_type']);
        }
    }

    return NO_ERROR;
}

/**
 * Creates new record.
 *
 * @param int &$id {@link http://www.etraxis.org/docs-schema.php#tbl_records_record_id ID} of newly created record (used as output only).
 * @param string $subject {@link http://www.etraxis.org/docs-schema.php#tbl_records_subject Subject} of new record.
 * @param int $state_id {@link http://www.etraxis.org/docs-schema.php#tbl_states_state_id ID of initial state} of new record.
 * @param int $responsible_id If record should be assigned on creation, then {@link http://www.etraxis.org/docs-schema.php#tbl_records_responsible_id ID of responsible} of new record; NULL (default) otherwise.
 * @param int $clone_id If record is being cloned from another, {@link http://www.etraxis.org/docs-schema.php#tbl_records_record_id ID of original record}, 0 (default) otherwise.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - record is successfully created</li>
 * <li>{@link ERROR_NOT_FOUND} - failure on attempt to create new record</li>
 * </ul>
 */
function record_create (&$id, $subject, $state_id, $responsible_id = NULL, $clone_id = 0)
{
    debug_write_log(DEBUG_TRACE, '[record_create]');
    debug_write_log(DEBUG_DUMP,  '[record_create] $subject        = ' . $subject);
    debug_write_log(DEBUG_DUMP,  '[record_create] $state_id       = ' . $state_id);
    debug_write_log(DEBUG_DUMP,  '[record_create] $responsible_id = ' . $responsible_id);

    $time = time();

    // Create a record.
    dal_query('records/create.sql',
              $state_id,
              $subject,
              is_null($responsible_id) ? NULL : $responsible_id,
              $_SESSION[VAR_USERID],
              $time);

    // Find newly created record.
    $rs = dal_query('records/fndk.sql',
                    $_SESSION[VAR_USERID],
                    $time);

    if ($rs->rows == 0)
    {
        debug_write_log(DEBUG_ERROR, '[record_create] Record cannot be found.');
        return ERROR_NOT_FOUND;
    }

    $id = $rs->fetch('record_id');

    if ($clone_id != 0)
    {
        debug_write_log(DEBUG_NOTICE, '[record_create] Record is being cloned.');
        event_create($id, EVENT_RECORD_CLONED, $time - 1, $clone_id);
    }

    $event = event_create($id, EVENT_RECORD_CREATED, $time, $state_id);

    if (!is_null($responsible_id))
    {
        debug_write_log(DEBUG_NOTICE, '[record_create] Responsible is being set.');
        $event2 = event_create($id, EVENT_RECORD_ASSIGNED, time(), $responsible_id);
    }

    // Create current values of all custom fields of new record.
    $rs = dal_query('fields/list.sql', $state_id, 'field_order');

    while (($row = $rs->fetch()))
    {
        $name  = 'field' . $row['field_id'];
        $value = ($row['field_type'] == FIELD_TYPE_CHECKBOX ? isset($_REQUEST[$name]) : trim(try_request($name)));

        switch ($row['field_type'])
        {
            case FIELD_TYPE_NUMBER:
            case FIELD_TYPE_LIST:
            case FIELD_TYPE_RECORD:
                value_create_number($event['event_id'], $row['field_id'], $row['field_type'], (ustrlen($value) == 0 ? NULL : intval($value)));
                break;
            case FIELD_TYPE_STRING:
                value_create_string($event['event_id'], $row['field_id'], $row['field_type'], (ustrlen($value) == 0 ? NULL : ustrcut($value, $row['param1'])));
                break;
            case FIELD_TYPE_MULTILINED:
                value_create_multilined($event['event_id'], $row['field_id'], $row['field_type'], (ustrlen($value) == 0 ? NULL : ustrcut($value, $row['param1'])));
                break;
            case FIELD_TYPE_CHECKBOX:
                value_create_number($event['event_id'], $row['field_id'], $row['field_type'], bool2sql((bool)(ustrlen($value) == 0 ? 0 : intval($value))));
                break;
            case FIELD_TYPE_DATE:
                value_create_number($event['event_id'], $row['field_id'], $row['field_type'], (ustrlen($value) == 0 ? NULL : ustr2date($value)));
                break;
            case FIELD_TYPE_DURATION:
                value_create_number($event['event_id'], $row['field_id'], $row['field_type'], (ustrlen($value) == 0 ? NULL : ustr2time($value)));
                break;
            default:
                debug_write_log(DEBUG_WARNING, '[record_create] Unknown field type = ' . $row['field_type']);
        }
    }

    event_mail($event);

    if (!is_null($responsible_id))
    {
        debug_write_log(DEBUG_NOTICE, '[record_create] Responsible is set.');
        event_mail($event2);
    }

    return NO_ERROR;
}

/**
 * Modifies specified record.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_records_record_id ID} of record to be modified.
 * @param string $subject New {@link http://www.etraxis.org/docs-schema.php#tbl_records_subject subject} of the record.
 * @param int $creator_id Current {@link http://www.etraxis.org/docs-schema.php#tbl_records_creator_id author of record}.
 * @param int $responsible_id Current {@link http://www.etraxis.org/docs-schema.php#tbl_records_responsible_id responsible of record} (NULL, if record is not assigned).
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - record is successfully modified</li>
 * <li>{@link ERROR_NOT_FOUND} - record cannot be found</li>
 * </ul>
 */
function record_modify ($id, $subject, $creator_id, $responsible_id)
{
    debug_write_log(DEBUG_TRACE, '[record_modify]');
    debug_write_log(DEBUG_DUMP,  '[record_modify] $id             = ' . $id);
    debug_write_log(DEBUG_DUMP,  '[record_modify] $subject        = ' . $subject);
    debug_write_log(DEBUG_DUMP,  '[record_modify] $creator_id     = ' . $creator_id);
    debug_write_log(DEBUG_DUMP,  '[record_modify] $responsible_id = ' . $responsible_id);

    $rs = dal_query('records/fndsubj.sql', $id);

    if ($rs->rows == 0)
    {
        debug_write_log(DEBUG_ERROR, '[record_modify] Record cannot be found.');
        return ERROR_NOT_FOUND;
    }

    $event = event_create($id, EVENT_RECORD_MODIFIED, time());

    $row = $rs->fetch();

    if ($row['subject'] != $subject)
    {
        debug_write_log(DEBUG_NOTICE, '[record_modify] Subject is being changed.');

        $old_value_id = value_find_string($row['subject']);
        $new_value_id = value_find_string($subject);

        dal_query('changes/create.sql',
                  $event['event_id'],
                  NULL,
                  is_null($old_value_id) ? NULL : $old_value_id,
                  is_null($new_value_id) ? NULL : $new_value_id);

        dal_query('records/modify.sql',
                  $id,
                  $subject);
    }

    $rs = dal_query('records/elist.sql', $id);

    while (($row = $rs->fetch()))
    {
        $rsf = dal_query('records/flist.sql',
                         $id,
                         $row['state_id'],
                         $creator_id,
                         is_null($responsible_id) ? 0 : $responsible_id,
                         $_SESSION[VAR_USERID],
                         FIELD_ALLOW_TO_WRITE);

        while (($row = $rsf->fetch()))
        {
            $name  = 'field' . $row['field_id'];
            $value = ($row['field_type'] == FIELD_TYPE_CHECKBOX ? isset($_REQUEST[$name]) : trim(try_request($name)));

            switch ($row['field_type'])
            {
                case FIELD_TYPE_NUMBER:
                case FIELD_TYPE_LIST:
                case FIELD_TYPE_RECORD:
                    value_modify_number($id, $event['event_id'], $row['field_id'], (ustrlen($value) == 0 ? NULL : intval($value)));
                    break;
                case FIELD_TYPE_STRING:
                    value_modify_string($id, $event['event_id'], $row['field_id'], (ustrlen($value) == 0 ? NULL : ustrcut($value, $row['param1'])));
                    break;
                case FIELD_TYPE_MULTILINED:
                    value_modify_multilined($id, $event['event_id'], $row['field_id'], (ustrlen($value) == 0 ? NULL : ustrcut($value, $row['param1'])));
                    break;
                case FIELD_TYPE_CHECKBOX:
                    value_modify_number($id, $event['event_id'], $row['field_id'], bool2sql((bool)(ustrlen($value) == 0 ? 0 : intval($value))));
                    break;
                case FIELD_TYPE_DATE:
                    value_modify_number($id, $event['event_id'], $row['field_id'], (ustrlen($value) == 0 ? NULL : ustr2date($value)));
                    break;
                case FIELD_TYPE_DURATION:
                    value_modify_number($id, $event['event_id'], $row['field_id'], (ustrlen($value) == 0 ? NULL : ustr2time($value)));
                    break;
                default:
                    debug_write_log(DEBUG_WARNING, '[record_modify] Unknown field type = ' . $row['field_type']);
            }
        }
    }

    $rs = dal_query('changes/count.sql', $event['event_id']);

    if ($rs->fetch(0) == 0)
    {
        event_destroy($event['event_id']);
    }
    else
    {
        event_mail($event);
    }

    return NO_ERROR;
}

/**
 * Deletes specified record.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_records_record_id ID} of record to be deleted.
 * @return int Always {@link NO_ERROR}.
 */
function record_delete ($id)
{
    debug_write_log(DEBUG_TRACE, '[record_delete]');
    debug_write_log(DEBUG_DUMP,  '[record_delete] $id = ' . $id);

    $rs = dal_query('attachs/list.sql', $id);

    while (($row = $rs->fetch()))
    {
        @unlink(ATTACHMENTS_PATH . $row['attachment_id']);
    }

    dal_query('comments/delall.sql',      $id);
    dal_query('attachs/delall.sql',       $id);
    dal_query('changes/delall.sql',       $id);
    dal_query('values/delall.sql',        $id);
    dal_query('events/delall.sql',        $id);
    dal_query('depends/delall.sql',       $id);
    dal_query('records/unreadall2.sql',   $id);
    dal_query('records/unsubscribe3.sql', $id);
    dal_query('records/delete.sql',       $id);

    return NO_ERROR;
}

/**
 * Postpones specified record.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_records_record_id ID} of record to be postponed.
 * @param int $date {@link http://www.etraxis.org/docs-schema.php#tbl_records_postpone_time Unix timestamp} of the date when record will be resumed automatically.
 * @return int Always {@link NO_ERROR}.
 */
function record_postpone ($id, $date)
{
    debug_write_log(DEBUG_TRACE, '[record_postpone]');
    debug_write_log(DEBUG_DUMP,  '[record_postpone] $id   = ' . $id);
    debug_write_log(DEBUG_DUMP,  '[record_postpone] $date = ' . $date);

    dal_query('records/postpone.sql', $id, $date);

    return NO_ERROR;
}

/**
 * Resumes specified postponed record.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_records_record_id ID} of record to be resumed.
 * @return int Always {@link NO_ERROR}.
 */
function record_resume ($id)
{
    debug_write_log(DEBUG_TRACE, '[record_resume]');
    debug_write_log(DEBUG_DUMP,  '[record_resume] $id = ' . $id);

    dal_query('records/postpone.sql', $id, 0);

    return NO_ERROR;
}

/**
 * Assigns specified record.
 *
 * @param int $rid {@link http://www.etraxis.org/docs-schema.php#tbl_records_record_id ID} of record to be assigned.
 * @param int $aid {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_account_id ID} of new responsible.
 * @return int Always {@link NO_ERROR}.
 */
function record_assign ($rid, $aid)
{
    debug_write_log(DEBUG_TRACE, '[record_assign]');
    debug_write_log(DEBUG_DUMP,  '[record_assign] $rid = ' . $rid);
    debug_write_log(DEBUG_DUMP,  '[record_assign] $aid = ' . $aid);

    dal_query('records/assign.sql', $rid, $aid);

    return NO_ERROR;
}

/**
 * Marks specified record as read (for current user).
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_records_record_id ID} of record to be marked as read.
 * @return int Always {@link NO_ERROR}.
 */
function record_read ($id)
{
    debug_write_log(DEBUG_TRACE, '[record_read]');
    debug_write_log(DEBUG_DUMP,  '[record_read] $id = ' . $id);

    if (get_user_level() != USER_LEVEL_GUEST)
    {
        dal_query('records/unread.sql', $id, $_SESSION[VAR_USERID]);
        dal_query('records/read.sql',   $id, $_SESSION[VAR_USERID], time());
    }

    return NO_ERROR;
}

/**
 * Change state of specified record.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_records_record_id ID} of record which state should be changed.
 * @param int $state_id New {@link http://www.etraxis.org/docs-schema.php#tbl_states_state_id state} of the record.
 * @param int $responsible_id {@link http://www.etraxis.org/docs-schema.php#tbl_records_responsible_id ID of new responsible}:
 * <ul>
 * <li>{@link http://www.etraxis.org/docs-schema.php#tbl_accounts_account_id account ID}, if the record should be assigned</li>
 * <li>NULL, if the current assignment should be removed</li>
 * <li>0, if current assignment should be remained as is</li>
 * </ul>
 * @param bool $close TRUE if new state is final; FALSE otherwise.
 * @return int Always {@link NO_ERROR}.
 */
function state_change ($id, $state_id, $responsible_id, $close = FALSE)
{
    debug_write_log(DEBUG_TRACE, '[state_change]');
    debug_write_log(DEBUG_DUMP,  '[state_change] $id             = ' . $id);
    debug_write_log(DEBUG_DUMP,  '[state_change] $state_id       = ' . $state_id);
    debug_write_log(DEBUG_DUMP,  '[state_change] $responsible_id = ' . $responsible_id);
    debug_write_log(DEBUG_DUMP,  '[state_change] $close          = ' . $close);

    dal_query('records/state.sql',
              $id,
              $state_id);

    $event = event_create($id, EVENT_RECORD_STATE_CHANGED, time(), $state_id);

    $rs = dal_query('fields/list.sql', $state_id, 'field_order');

    while (($row = $rs->fetch()))
    {
        $name  = 'field' . $row['field_id'];
        $value = ($row['field_type'] == FIELD_TYPE_CHECKBOX ? isset($_REQUEST[$name]) : trim(try_request($name)));

        dal_query('values/latest.sql', $id, $row['field_id']);

        switch ($row['field_type'])
        {
            case FIELD_TYPE_NUMBER:
            case FIELD_TYPE_LIST:
            case FIELD_TYPE_RECORD:
                value_create_number($event['event_id'], $row['field_id'], $row['field_type'], (ustrlen($value) == 0 ? NULL : intval($value)));
                break;
            case FIELD_TYPE_STRING:
                value_create_string($event['event_id'], $row['field_id'], $row['field_type'], (ustrlen($value) == 0 ? NULL : ustrcut($value, $row['param1'])));
                break;
            case FIELD_TYPE_MULTILINED:
                value_create_multilined($event['event_id'], $row['field_id'], $row['field_type'], (ustrlen($value) == 0 ? NULL : ustrcut($value, $row['param1'])));
                break;
            case FIELD_TYPE_CHECKBOX:
                value_create_number($event['event_id'], $row['field_id'], $row['field_type'], bool2sql((bool)(ustrlen($value) == 0 ? 0 : intval($value))));
                break;
            case FIELD_TYPE_DATE:
                value_create_number($event['event_id'], $row['field_id'], $row['field_type'], (ustrlen($value) == 0 ? NULL : ustr2date($value)));
                break;
            case FIELD_TYPE_DURATION:
                value_create_number($event['event_id'], $row['field_id'], $row['field_type'], (ustrlen($value) == 0 ? NULL : ustr2time($value)));
                break;
            default:
                debug_write_log(DEBUG_WARNING, '[state_change] Unknown field type = ' . $row['field_type']);
        }
    }

    event_mail($event);

    if ($close)
    {
        debug_write_log(DEBUG_NOTICE, '[state_change] Close record.');
        dal_query('records/close.sql', $id, time());
    }
    else
    {
        if (is_null($responsible_id))
        {
            debug_write_log(DEBUG_NOTICE, '[state_change] Remove responsible.');
            dal_query('records/assign.sql', $id, NULL);
        }
        elseif ($responsible_id != 0)
        {
            debug_write_log(DEBUG_NOTICE, '[state_change] Assign responsible.');
            dal_query('records/assign.sql', $id, $responsible_id);
            $event = event_create($id, EVENT_RECORD_ASSIGNED, time(), $responsible_id);
            event_mail($event);
        }
    }

    return NO_ERROR;
}

/**
 * Returns {@link CRecordset DAL recordset} which contains all events of specified record,
 * sorted in accordance with current sort mode.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_records_record_id Record ID}.
 * @param int $permissions User {@link http://www.etraxis.org/docs-schema.php#tbl_group_perms_perms permissions} (see also {@link record_get_permissions}).
 * @param int &$sort Sort mode (used as output only). The function retrieves current sort mode from
 * client cookie ({@link COOKIE_EVENTS_SORT}) and updates it, if it's out of valid range.
 * @param int &$page Number of current page tab (used as output only). The function retrieves current
 * page from client cookie ({@link COOKIE_EVENTS_PAGE}) and updates it, if it's out of valid range.
 * @return CRecordset Recordset with list of events.
 */
function history_list ($id, $permissions, &$sort, &$page)
{
    debug_write_log(DEBUG_TRACE, '[history_list]');
    debug_write_log(DEBUG_DUMP,  '[history_list] $id          = ' . $id);
    debug_write_log(DEBUG_DUMP,  '[history_list] $permissions = ' . $permissions);

    $sort_modes = array
    (
        1 => 'event_time asc, event_type asc',
        2 => 'fullname asc, username asc, event_time asc, event_type asc',
        3 => 'event_type asc, event_time asc',
        4 => 'event_time desc, event_type desc',
        5 => 'fullname desc, username desc, event_time desc, event_type desc',
        6 => 'event_type desc, event_time desc',
    );

    $sort = try_request('sort', try_cookie(COOKIE_EVENTS_SORT));
    $sort = ustr2int($sort, 1, count($sort_modes));

    $page = try_request('page', try_cookie(COOKIE_EVENTS_PAGE));
    $page = ustr2int($page, 1, MAXINT);

    save_cookie(COOKIE_EVENTS_SORT, $sort);
    save_cookie(COOKIE_EVENTS_PAGE, $page);

    return dal_query('events/list.sql', $id, ($permissions & PERMIT_CONFIDENTIAL_COMMENTS) ? EVENT_UNUSED : EVENT_CONFIDENTIAL_COMMENT, $sort_modes[$sort]);
}

/**
 * Returns {@link CRecordset DAL recordset} which contains all changes of specified record,
 * sorted in accordance with current sort mode.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_records_record_id Record ID}.
 * @param int $creator_id Current {@link http://www.etraxis.org/docs-schema.php#tbl_records_creator_id author of the record}.
 * @param int $responsible_id Current {@link http://www.etraxis.org/docs-schema.php#tbl_records_responsible_id responsible of the record} (NULL, if record is not assigned).
 * @param int &$sort Sort mode (used as output only). The function retrieves current sort mode from
 * client cookie ({@link COOKIE_CHANGES_SORT}) and updates it, if it's out of valid range.
 * @param int &$page Number of current page tab (used as output only). The function retrieves current
 * page from client cookie ({@link COOKIE_CHANGES_PAGE}) and updates it, if it's out of valid range.
 * @return CRecordset Recordset with list of changes.
 */
function changes_list ($id, $creator_id, $responsible_id, &$sort, &$page)
{
    debug_write_log(DEBUG_TRACE, '[changes_list]');
    debug_write_log(DEBUG_DUMP,  '[changes_list] $id             = ' . $id);
    debug_write_log(DEBUG_DUMP,  '[changes_list] $creator_id     = ' . $creator_id);
    debug_write_log(DEBUG_DUMP,  '[changes_list] $responsible_id = ' . $responsible_id);

    $sort_modes = array
    (
        1  => 'event_time asc, field_order asc',
        2  => 'fullname asc, username asc, event_time asc, field_order asc',
        3  => 'field_name asc, event_time asc',
        4  => '',
        5  => '',
        6  => 'event_time desc, field_order desc',
        7  => 'fullname desc, username desc, event_time desc, field_order desc',
        8  => 'field_name desc, event_time desc',
        9  => '',
        10 => '',
    );

    $sort = try_request('sort', try_cookie(COOKIE_CHANGES_SORT));
    $sort = ustr2int($sort, 1, count($sort_modes));

    $page = try_request('page', try_cookie(COOKIE_CHANGES_PAGE));
    $page = ustr2int($page, 1, MAXINT);

    save_cookie(COOKIE_CHANGES_SORT, $sort);
    save_cookie(COOKIE_CHANGES_PAGE, $page);

    return dal_query('changes/list.sql',
                     $id,
                     $creator_id,
                     is_null($responsible_id) ? 0 : $responsible_id,
                     $_SESSION[VAR_USERID],
                     $sort_modes[$sort]);
}

/**
 * Finds in database and returns the information about specified comment.
 *
 * @param int $event_id {@link http://www.etraxis.org/docs-schema.php#tbl_events_event_id ID of event}, registered when comment has been added.
 * @param int $permissions User {@link http://www.etraxis.org/docs-schema.php#tbl_group_perms_perms permissions} (see also {@link record_get_permissions}).
 * @return array Array with data if comment is found in database, FALSE otherwise.
 */
function comment_find ($event_id, $permissions)
{
    debug_write_log(DEBUG_TRACE, '[comment_find]');
    debug_write_log(DEBUG_DUMP,  '[comment_find] $event_id    = ' . $event_id);
    debug_write_log(DEBUG_DUMP,  '[comment_find] $permissions = ' . $permissions);

    $rs  = dal_query('comments/fndk.sql', $event_id);
    $row = $rs->fetch();

    return ($rs->rows == 0 ? FALSE : ($row['is_confidential'] && ($permissions & PERMIT_CONFIDENTIAL_COMMENTS) == 0 ? FALSE : $row));
}

/**
 * Adds new comment to specified record.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_records_record_id Record ID}.
 * @param string $comment {@link http://www.etraxis.org/docs-schema.php#tbl_comments_comment_body Text of comment}.
 * @param bool $is_confidential Whether the comment is {@link http://www.etraxis.org/docs-schema.php#tbl_comments_is_confidential confidential}.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - template is successfully created</li>
 * <li>{@link ERROR_INCOMPLETE_FORM} - at least one of required field is empty</li>
 * <li>{@link ERROR_NOT_FOUND} - record cannot be found</li>
 * </ul>
 */
function comment_add ($id, $comment, $is_confidential = FALSE)
{
    debug_write_log(DEBUG_TRACE, '[comment_add]');
    debug_write_log(DEBUG_DUMP,  '[comment_add] $id              = ' . $id);
    debug_write_log(DEBUG_DUMP,  '[comment_add] $comment         = ' . $comment);
    debug_write_log(DEBUG_DUMP,  '[comment_add] $is_confidential = ' . $is_confidential);

    if (ustrlen($comment) == 0)
    {
        debug_write_log(DEBUG_NOTICE, '[comment_add] At least one required field is empty.');
        return ERROR_INCOMPLETE_FORM;
    }

    $record = record_find($id);

    if (!$record)
    {
        debug_write_log(DEBUG_ERROR, '[comment_add] Record cannot be found.');
        return ERROR_NOT_FOUND;
    }

    $event = event_create($id, ($is_confidential ? EVENT_CONFIDENTIAL_COMMENT : EVENT_COMMENT_ADDED), time());

    if (!$event)
    {
        debug_write_log(DEBUG_ERROR, '[comment_add] Event cannot be found.');
        return ERROR_NOT_FOUND;
    }

    if (DATABASE_DRIVER == DRIVER_ORACLE9)
    {
        $handle = CDatabase::connect();
        $sql = file_get_contents(LOCALROOT . 'sql/comments/oracle/create.sql');

        $stid = ociparse($handle, $sql);
        $clob = ocinewdescriptor($handle, OCI_D_LOB);

        ocibindbyname($stid, ":event_id",        $event['event_id']);
        ocibindbyname($stid, ":comment_body",    $clob, -1, OCI_B_CLOB);
        ocibindbyname($stid, ":is_confidential", bool2sql($is_confidential));

        ociexecute($stid, OCI_DEFAULT);
        $clob->save($comment);
        ocicommit($handle);
    }
    else
    {
        dal_query('comments/create.sql',
                  $comment,
                  $event['event_id'],
                  bool2sql($is_confidential));
    }

    event_mail($event);

    return NO_ERROR;
}

/**
 * Finds in database and returns the information about specified attachment.
 *
 * @param int $attachment_id {@link http://www.etraxis.org/docs-schema.php#tbl_attachments_attachment_id Attachment ID}.
 * @return array Array with data if attachment is found in database, FALSE otherwise.
 */
function attachment_find ($attachment_id)
{
    debug_write_log(DEBUG_TRACE, '[attachment_find]');
    debug_write_log(DEBUG_DUMP,  '[attachment_find] $attachment_id = ' . $attachment_id);

    $rs = dal_query('attachs/fndid.sql', $attachment_id);

    return ($rs->rows == 0 ? FALSE : $rs->fetch());
}

/**
 * Returns {@link CRecordset DAL recordset} which contains all attachments of specified record,
 * sorted by {@link http://www.etraxis.org/docs-schema.php#tbl_attachments_attachment_name attachment name}.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_records_record_id Record ID}.
 * @param int $permissions User {@link http://www.etraxis.org/docs-schema.php#tbl_group_perms_perms permissions} (see also {@link record_get_permissions}).
 * @return CRecordset Recordset with list of attachments.
 */
function attachment_list ($id, $permissions = PERMIT_REMOVE_FILES)
{
    debug_write_log(DEBUG_TRACE, '[attachment_list]');
    debug_write_log(DEBUG_DUMP,  '[attachment_list] $id          = ' . $id);
    debug_write_log(DEBUG_DUMP,  '[attachment_list] $permissions = ' . $permissions);

    if ($permissions & PERMIT_REMOVE_FILES)
    {
        $rs = dal_query('attachs/list.sql', $id);
    }
    else
    {
        $rs = dal_query('attachs/list2.sql', $id, $_SESSION[VAR_USERID]);
    }

    return $rs;
}

/**
 * Adds new attachment to specified record.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_records_record_id Record ID}.
 * @param string $name {@link http://www.etraxis.org/docs-schema.php#tbl_attachments_attachment_name Attachment name}.
 * @param array $attachfile Information about uploaded user file (see {@link http://www.php.net/features.file-upload} for details).
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - attachment is successfully created</li>
 * <li>{@link ERROR_NOT_FOUND} - record cannot be found</li>
 * <li>{@link ERROR_ALREADY_EXISTS} - attachment with specified {@link http://www.etraxis.org/docs-schema.php#tbl_attachments_attachment_name name} already exists</li>
 * <li>{@link ERROR_UPLOAD_INI_SIZE} - the uploaded file exceeds the {@link http://www.php.net/ini.core#ini.upload-max-filesize upload_max_filesize} directive in <i>php.ini</i></li>
 * <li>{@link ERROR_UPLOAD_FORM_SIZE} - the uploaded file exceeds the {@link EMAIL_ATTACHMENTS_MAXSIZE}</li>
 * <li>{@link ERROR_UPLOAD_PARTIAL} - the uploaded file was only partially uploaded</li>
 * <li>{@link ERROR_UPLOAD_NO_FILE} - no file was uploaded</li>
 * <li>{@link ERROR_UPLOAD_NO_TMP_DIR} - missing a temporary folder</li>
 * <li>{@link ERROR_UPLOAD_CANT_WRITE} - failed to write file to disk</li>
 * <li>{@link ERROR_UPLOAD_EXTENSION} - file upload stopped by extension</li>
 * <li>{@link ERROR_UNKNOWN} - unknown failure</li>
 * </ul>
 */
function attachment_add ($id, $name, $attachfile)
{
    debug_write_log(DEBUG_TRACE, '[attachment_add]');
    debug_write_log(DEBUG_DUMP,  '[attachment_add] $id                     = ' . $id);
    debug_write_log(DEBUG_DUMP,  '[attachment_add] $name                   = ' . $name);
    debug_write_log(DEBUG_DUMP,  '[attachment_add] $attachfile["name"]     = ' . $attachfile['name']);
    debug_write_log(DEBUG_DUMP,  '[attachment_add] $attachfile["type"]     = ' . $attachfile['type']);
    debug_write_log(DEBUG_DUMP,  '[attachment_add] $attachfile["size"]     = ' . $attachfile['size']);
    debug_write_log(DEBUG_DUMP,  '[attachment_add] $attachfile["tmp_name"] = ' . $attachfile['tmp_name']);
    debug_write_log(DEBUG_DUMP,  '[attachment_add] $attachfile["error"]    = ' . $attachfile['error']);

    if (ustrlen($name) == 0)
    {
        debug_write_log(DEBUG_NOTICE, '[attachment_add] Attachment name is not specified.');
        $name = ustrcut($attachfile['name'], MAX_ATTACHMENT_NAME);
    }

    $record = record_find($id);

    if (!$record)
    {
        debug_write_log(DEBUG_ERROR, '[attachment_add] Record cannot be found.');
        return ERROR_NOT_FOUND;
    }

    $rs = dal_query('attachs/fndku.sql', $id, $name);

    if ($rs->rows != 0)
    {
        debug_write_log(DEBUG_WARNING, '[attachment_add] Attachment already exists.');
        return ERROR_ALREADY_EXISTS;
    }

    switch ($attachfile['error'])
    {
        case UPLOAD_ERR_OK:
            break;  // nop
        case UPLOAD_ERR_INI_SIZE:
            return ERROR_UPLOAD_INI_SIZE;
        case UPLOAD_ERR_FORM_SIZE:
            return ERROR_UPLOAD_FORM_SIZE;
        case UPLOAD_ERR_PARTIAL:
            return ERROR_UPLOAD_PARTIAL;
        case UPLOAD_ERR_NO_FILE:
            return ERROR_UPLOAD_NO_FILE;
        case UPLOAD_ERR_NO_TMP_DIR:
            return ERROR_UPLOAD_NO_TMP_DIR;
        case UPLOAD_ERR_CANT_WRITE:
            return ERROR_UPLOAD_CANT_WRITE;
        case UPLOAD_ERR_EXTENSION:
            return ERROR_UPLOAD_EXTENSION;
        default:
            return ERROR_UNKNOWN;
    }

    if ($attachfile['size'] > ATTACHMENTS_MAXSIZE * 1024)
    {
        debug_write_log(DEBUG_WARNING, '[attachment_add] Attachment is too large.');
        return ERROR_UPLOAD_FORM_SIZE;
    }

    if (!is_uploaded_file($attachfile['tmp_name']))
    {
        debug_write_log(DEBUG_WARNING, '[attachment_add] Function "is_uploaded_file" warns that file named by "' . $attachfile['tmp_name'] . '" was not uploaded via HTTP POST.');
        return NO_ERROR;
    }

    $event = event_create($id, EVENT_FILE_ATTACHED, time());

    if (!$event)
    {
        debug_write_log(DEBUG_ERROR, '[attachment_add] Event cannot be found.');
        return ERROR_NOT_FOUND;
    }

    dal_query('attachs/create.sql',
              $name,
              $attachfile['type'],
              $attachfile['size'],
              $event['event_id']);

    $rs = dal_query('attachs/fndku.sql', $id, $name);

    if ($rs->rows == 0)
    {
        debug_write_log(DEBUG_ERROR, '[attachment_add] Attachment cannot be found.');
        return ERROR_NOT_FOUND;
    }

    $attachment_id = $rs->fetch('attachment_id');

    $attachment_localname = ATTACHMENTS_PATH . $attachment_id;

    @move_uploaded_file($attachfile['tmp_name'], $attachment_localname);

    if (ATTACHMENTS_COMPRESSED)
    {
        compressfile($attachment_localname);
    }

    event_mail($event, $attachment_id, $name, $attachfile['type'], $attachfile['size']);

    return NO_ERROR;
}

/**
 * Removes specified attachment from its record.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_records_record_id Record ID}.
 * @param int $permissions User {@link http://www.etraxis.org/docs-schema.php#tbl_group_perms_perms permissions} (see also {@link record_get_permissions}).
 * @param int $attachment_id {@link http://www.etraxis.org/docs-schema.php#tbl_attachments_attachment_id ID} of attachment to be removed.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - attachment is successfully removed</li>
 * <li>{@link ERROR_NOT_FOUND} - attachment cannot be found</li>
 * </ul>
 */
function attachment_remove ($id, $permissions, $attachment_id)
{
    debug_write_log(DEBUG_TRACE, '[attachment_remove]');
    debug_write_log(DEBUG_DUMP,  '[attachment_remove] $id            = ' . $id);
    debug_write_log(DEBUG_DUMP,  '[attachment_remove] $permissions   = ' . $permissions);
    debug_write_log(DEBUG_DUMP,  '[attachment_remove] $attachment_id = ' . $attachment_id);

    $attachment = attachment_find($attachment_id);

    if (!$attachment)
    {
        debug_write_log(DEBUG_NOTICE, '[attachment_remove] Attachment cannot be found.');
        return ERROR_NOT_FOUND;
    }

    if (($attachment['originator_id'] != $_SESSION[VAR_USERID]) &&
        (($permissions & PERMIT_REMOVE_FILES) == 0))
    {
        debug_write_log(DEBUG_NOTICE, '[attachment_remove] No permissions to remove this attachment.');
        return ERROR_NOT_FOUND;
    }

    $event = event_create($id, EVENT_FILE_REMOVED, time(), $attachment_id);

    dal_query('attachs/remove.sql', $attachment_id);

    @unlink(ATTACHMENTS_PATH . $attachment_id);

    event_mail($event);

    return NO_ERROR;
}

/**
 * Returns {@link CRecordset DAL recordset} which contains all subrecords of specified record,
 * sorted by {@link http://www.etraxis.org/docs-schema.php#tbl_records_record_id ID}.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_records_record_id Record ID}.
 * @return CRecordset Recordset with list of subrecords.
 */
function subrecords_list ($id)
{
    debug_write_log(DEBUG_TRACE, '[subrecords_list]');
    debug_write_log(DEBUG_DUMP,  '[subrecords_list] $id = ' . $id);

    return dal_query('depends/list.sql', $id);
}

/**
 * Validates subrecord information before creation.
 *
 * @param int $parent_id {@link http://www.etraxis.org/docs-schema.php#tbl_records_record_id Parent record ID}.
 * @param int $subrecord_id {@link http://www.etraxis.org/docs-schema.php#tbl_records_record_id Subrecord ID}.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - data are valid</li>
 * <li>{@link ERROR_INCOMPLETE_FORM} - at least one of required field is empty</li>
 * <li>{@link ERROR_INVALID_INTEGER_VALUE} - subrecord ID is not a valid integer value</li>
 * <li>{@link ERROR_RECORD_NOT_FOUND} - subrecord cannot be found</li>
 * </ul>
 */
function subrecord_validate ($parent_id, $subrecord_id)
{
    debug_write_log(DEBUG_TRACE, '[subrecord_validate]');
    debug_write_log(DEBUG_DUMP,  '[subrecord_validate] $parent_id     = ' . $parent_id);
    debug_write_log(DEBUG_DUMP,  '[subrecord_validate] $subrecord_id = ' . $subrecord_id);

    if (ustrlen($subrecord_id) == 0)
    {
        debug_write_log(DEBUG_NOTICE, '[subrecord_validate] At least one required field is empty.');
        return ERROR_INCOMPLETE_FORM;
    }

    if (!is_intvalue($subrecord_id))
    {
        debug_write_log(DEBUG_NOTICE, '[subrecord_validate] Invalid record ID.');
        return ERROR_INVALID_INTEGER_VALUE;
    }

    if ($parent_id == intval($subrecord_id))
    {
        debug_write_log(DEBUG_NOTICE, '[subrecord_validate] Record cannot be parent of itself.');
        return ERROR_RECORD_NOT_FOUND;
    }

    $record = record_find(intval($subrecord_id));

    if (!$record)
    {
        debug_write_log(DEBUG_NOTICE, '[subrecord_validate] Record not found.');
        return ERROR_RECORD_NOT_FOUND;
    }

    return NO_ERROR;
}

/**
 * Adds new subrecord to specified record.
 *
 * @param int $parent_id {@link http://www.etraxis.org/docs-schema.php#tbl_records_record_id Parent record ID}.
 * @param int $subrecord_id {@link http://www.etraxis.org/docs-schema.php#tbl_records_record_id Subrecord ID}.
 * @param bool $is_dependency .
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - subrecord is successfully added</li>
 * <li>{@link ERROR_NOT_FOUND} - subrecord cannot be found</li>
 * <li>{@link ERROR_ALREADY_EXISTS} - specified subrecord already exists</li>
 * </ul>
 */
function subrecord_add ($parent_id, $subrecord_id, $is_dependency)
{
    debug_write_log(DEBUG_TRACE, '[subrecord_add]');
    debug_write_log(DEBUG_DUMP,  '[subrecord_add] $parent_id     = ' . $parent_id);
    debug_write_log(DEBUG_DUMP,  '[subrecord_add] $subrecord_id  = ' . $subrecord_id);
    debug_write_log(DEBUG_DUMP,  '[subrecord_add] $is_dependency = ' . $is_dependency);

    $rs = dal_query('depends/fnd2.sql', $parent_id, $subrecord_id);

    if ($rs->rows != 0)
    {
        debug_write_log(DEBUG_WARNING, '[subrecord_add] Subrecord already exists.');
        return ERROR_ALREADY_EXISTS;
    }

    $event = event_create($parent_id, EVENT_SUBRECORD_ADDED, time(), $subrecord_id);

    if (!$event)
    {
        debug_write_log(DEBUG_ERROR, '[subrecord_add] Event cannot be found.');
        return ERROR_NOT_FOUND;
    }

    dal_query('depends/create.sql',
              $parent_id,
              $subrecord_id,
              bool2sql($is_dependency));

    event_mail($event);

    return NO_ERROR;
}

/**
 * Removes specified subrecord.
 *
 * @param int $parent_id {@link http://www.etraxis.org/docs-schema.php#tbl_records_record_id Parent record ID}.
 * @param int $subrecord_id {@link http://www.etraxis.org/docs-schema.php#tbl_records_record_id Subrecord ID}.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - subrecord is successfully removed</li>
 * <li>{@link ERROR_NOT_FOUND} - subrecord cannot be found</li>
 * </ul>
 */
function subrecord_remove ($parent_id, $subrecord_id)
{
    debug_write_log(DEBUG_TRACE, '[subrecord_remove]');
    debug_write_log(DEBUG_DUMP,  '[subrecord_remove] $parent_id    = ' . $parent_id);
    debug_write_log(DEBUG_DUMP,  '[subrecord_remove] $subrecord_id = ' . $subrecord_id);

    $rs = dal_query('depends/fnd2.sql', $parent_id, $subrecord_id);

    if ($rs->rows == 0)
    {
        debug_write_log(DEBUG_WARNING, '[subrecord_remove] Subrecord cannot be found.');
        return ERROR_NOT_FOUND;
    }

    $event = event_create($parent_id, EVENT_SUBRECORD_REMOVED, time(), $subrecord_id);

    if (!$event)
    {
        debug_write_log(DEBUG_ERROR, '[subrecord_remove] Event cannot be found.');
        return ERROR_NOT_FOUND;
    }

    dal_query('depends/delete.sql',
              $parent_id,
              $subrecord_id);

    event_mail($event);

    return NO_ERROR;
}

/**
 * Determines and returns set of permissions of current user for some record.
 *
 * @param int $template_id {@link http://www.etraxis.org/docs-schema.php#tbl_templates_template_id ID} of record's template.
 * @param int $creator_id {@link http://www.etraxis.org/docs-schema.php#tbl_records_creator_id Author of record}.
 * @param int $responsible_id {@link http://www.etraxis.org/docs-schema.php#tbl_records_responsible_id Responsible of record}.
 * @return int Set of binary flags:
 * <ul>
 * <li>{@link PERMIT_CREATE_RECORD} - permission to create new records</li>
 * <li>{@link PERMIT_MODIFY_RECORD} - permission to modify records</li>
 * <li>{@link PERMIT_POSTPONE_RECORD} - permission to postpone records</li>
 * <li>{@link PERMIT_RESUME_RECORD} - permission to resume records</li>
 * <li>{@link PERMIT_REASSIGN_RECORD} - permission to reassign records, which are already assigned on another person</li>
 * <li>{@link PERMIT_CHANGE_STATE} - permission to change state of records, which are assigned on another person</li>
 * <li>{@link PERMIT_ADD_COMMENTS} - permission to add comments</li>
 * <li>{@link PERMIT_ATTACH_FILES} - permission to add attachments</li>
 * <li>{@link PERMIT_REMOVE_FILES} - permission to remove attachments</li>
 * <li>{@link PERMIT_CONFIDENTIAL_COMMENTS} - permission to add and read confidential comments</li>
 * <li>{@link PERMIT_SEND_REMINDERS} - permission to send reminders</li>
 * <li>{@link PERMIT_DELETE_RECORD} - permission to delete records from database</li>
 * <li>{@link PERMIT_ADD_SUBRECORDS} - permission to add subrecords</li>
 * <li>{@link PERMIT_REMOVE_SUBRECORDS} - permission to remove subrecords</li>
 * <li>{@link PERMIT_VIEW_RECORD} - permission to read records</li>
 * </ul>
 */
function record_get_permissions ($template_id, $creator_id, $responsible_id)
{
    debug_write_log(DEBUG_TRACE, '[record_get_permissions]');
    debug_write_log(DEBUG_DUMP,  '[record_get_permissions] $template_id    = ' . $template_id);
    debug_write_log(DEBUG_DUMP,  '[record_get_permissions] $creator_id     = ' . $creator_id);
    debug_write_log(DEBUG_DUMP,  '[record_get_permissions] $responsible_id = ' . $responsible_id);

    $permissions = 0;

    if (get_user_level() == USER_LEVEL_GUEST)
    {
        $rs = dal_query('templates/fndid.sql', $template_id);

        if ($rs->rows != 0)
        {
            if ($rs->fetch('guest_access'))
            {
                $permissions = PERMIT_VIEW_RECORD;
            }
        }
    }
    else
    {
        if ($_SESSION[VAR_USERID] == $creator_id ||
            $_SESSION[VAR_USERID] == $responsible_id)
        {
            $permissions = PERMIT_VIEW_RECORD;
        }

        $rs = dal_query('groups/gplist.sql',
                        $template_id,
                        $creator_id,
                        is_null($responsible_id) ? 0 : $responsible_id,
                        $_SESSION[VAR_USERID]);

        while (($row = $rs->fetch()))
        {
            $permissions |= $row['perms'];
        }
    }

    return $permissions;
}

/**
 * Subscribes specified account to all events of specified record.
 *
 * @param int $record_id {@link http://www.etraxis.org/docs-schema.php#tbl_records_record_id Record ID}.
 * @param int $account_id {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_account_id ID of account} which is being subscribed.
 * @param int $subscribed_by {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_account_id ID of account} which is subscribing another one.
 * @return int Always {@link NO_ERROR}.
 */
function record_subscribe ($record_id, $account_id, $subscribed_by)
{
    debug_write_log(DEBUG_TRACE, '[record_subscribe]');
    debug_write_log(DEBUG_DUMP,  '[record_subscribe] $record_id     = ' . $record_id);
    debug_write_log(DEBUG_DUMP,  '[record_subscribe] $account_id    = ' . $account_id);
    debug_write_log(DEBUG_DUMP,  '[record_subscribe] $subscribed_by = ' . $subscribed_by);

    dal_query('records/unsubscribe.sql', $record_id, $account_id, $subscribed_by);
    dal_query('records/subscribe.sql',   $record_id, $account_id, $subscribed_by);

    if ($account_id != $subscribed_by)
    {
        debug_write_log(DEBUG_NOTICE, '[record_subscribe] Inform about subscription.');

        $record     = record_find($record_id);
        $account    = account_find($account_id);
        $subscriber = account_find($subscribed_by);

        $to = $account['email'];

        $rec_id  = record_id($record_id, $record['template_prefix']);
        $subject = "[{$record['project_name']}] {$rec_id}: "
                 . htmlspecialchars_decode(update_references($record['subject'], BBCODE_OFF), ENT_COMPAT);

        $event = array('event_id'    => NULL,
                       'event_type'  => EVENT_RECORD_SUBSCRIBED,
                       'event_param' => $subscriber['fullname']);

        $message = generate_message($record, $event, $account['locale']);

        if (EMAIL_NOTIFICATIONS_ENABLED)
        {
            debug_write_log(DEBUG_NOTICE, '[record_subscribe] Sending email.');
            sendmail($subscriber['fullname'], $subscriber['email'], $to, $subject, $message);
        }
        else
        {
            debug_write_log(DEBUG_NOTICE, '[record_subscribe] Email notifications are disabled.');
        }
    }

    return NO_ERROR;
}

/**
 * Unsubscribes specified account off specified record.
 *
 * @param int $record_id {@link http://www.etraxis.org/docs-schema.php#tbl_records_record_id Record ID}.
 * @param int $account_id {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_account_id ID of account} which is being unsubscribed.
 * @param int $subscribed_by {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_account_id ID of account} which is unsubscribing another one.
 * @return int Always {@link NO_ERROR}.
 */
function record_unsubscribe ($record_id, $account_id, $subscribed_by)
{
    debug_write_log(DEBUG_TRACE, '[record_unsubscribe]');
    debug_write_log(DEBUG_DUMP,  '[record_unsubscribe] $record_id     = ' . $record_id);
    debug_write_log(DEBUG_DUMP,  '[record_unsubscribe] $account_id    = ' . $account_id);
    debug_write_log(DEBUG_DUMP,  '[record_unsubscribe] $subscribed_by = ' . $subscribed_by);

    global $locale_info;

    if ($account_id == $subscribed_by)
    {
        debug_write_log(DEBUG_NOTICE, '[record_unsubscribe] Inform about unsubscription.');

        $record  = record_find($record_id);
        $account = account_find($account_id);

        $supported_locales = array_keys($locale_info);

        foreach ($supported_locales as $locale)
        {
            $to = array();
            $rs = dal_query('records/subscribers.sql', $record_id, $account_id, $locale);

            while (($row = $rs->fetch()))
            {
                array_push($to, $row['email']);
            }

            if (count($to) != 0)
            {
                $recipients = implode(', ', array_unique($to));

                $rec_id  = record_id($record_id, $record['template_prefix']);
                $subject = "[{$record['project_name']}] {$rec_id}: "
                         . htmlspecialchars_decode(update_references($record['subject'], BBCODE_OFF), ENT_COMPAT);

                $event = array('event_id'    => NULL,
                               'event_type'  => EVENT_RECORD_UNSUBSCRIBED,
                               'event_param' => $account['fullname']);

                $message = generate_message($record, $event, $locale);

                if (EMAIL_NOTIFICATIONS_ENABLED)
                {
                    debug_write_log(DEBUG_NOTICE, '[record_unsubscribe] Sending email.');
                    sendmail($account['fullname'], $account['email'], $recipients, $subject, $message);
                }
                else
                {
                    debug_write_log(DEBUG_NOTICE, '[record_unsubscribe] Email notifications are disabled.');
                }
            }
        }

        dal_query('records/unsubscribe2.sql', $record_id, $account_id);
    }
    else
    {
        dal_query('records/unsubscribe.sql', $record_id, $account_id, $subscribed_by);
    }

    return NO_ERROR;
}

/**
 * Checks whether specified account is subscribed to specified record.
 *
 * @param int $record_id {@link http://www.etraxis.org/docs-schema.php#tbl_records_record_id Record ID}.
 * @param int $account_id {@link http://www.etraxis.org/docs-schema.php#tbl_accounts_account_id Account ID}.
 * @return bool TRUE if account is subscribed, FALSE otherwise.
 */
function is_record_subscribed ($record_id, $account_id)
{
    debug_write_log(DEBUG_TRACE, '[is_record_subscribed]');
    debug_write_log(DEBUG_DUMP,  '[is_record_subscribed] $record_id  = ' . $record_id);
    debug_write_log(DEBUG_DUMP,  '[is_record_subscribed] $account_id = ' . $account_id);

    $rs = dal_query('records/fndsubsc.sql', $record_id, $account_id);

    return ($rs->rows != 0);
}

/**
 * Checks whether a specified record has reached critical age.
 *
 * @param array $record Record information, as it returned by {@link record_find}.
 * @return bool TRUE if record's age is already critical, FALSE otherwise.
 */
function is_record_critical ($record)
{
    debug_write_log(DEBUG_TRACE, '[is_recorde_critical]');

    return (is_null($record['closure_time']) &&
            !is_null($record['critical_age']) &&
            $record['creation_time'] + $record['critical_age'] * SECS_IN_DAY < time());
}

/**
 * Checks whether a specified record is frozen.
 *
 * @param array $record Record information, as it returned by {@link record_find}.
 * @return bool TRUE if record is already frozen, FALSE otherwise.
 */
function is_record_frozen ($record)
{
    debug_write_log(DEBUG_TRACE, '[is_record_frozen]');

    return (!is_null($record['closure_time']) &&
            !is_null($record['frozen_time']) &&
            $record['closure_time'] + $record['frozen_time'] * SECS_IN_DAY < time());
}

/**
 * Checks whether a specified record is postponed.
 *
 * @param array $record Record information, as it returned by {@link record_find}.
 * @return bool TRUE if record is currently postponed, FALSE otherwise.
 */
function is_record_postponed ($record)
{
    debug_write_log(DEBUG_TRACE, '[is_record_postponed]');

    return (is_null($record['closure_time']) &&
            $record['postpone_time'] > time());
}

/**
 * Checks whether a specified record is closed.
 *
 * @param array $record Record information, as it returned by {@link record_find}.
 * @return bool TRUE if record is already closed, FALSE otherwise.
 */
function is_record_closed ($record)
{
    debug_write_log(DEBUG_TRACE, '[is_record_closed]');

    return !is_null($record['closure_time']);
}

/**
 * Checks whether a specified record was cloned from another one.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_records_record_id Record ID}.
 * @return int ID of original record if specified one was cloned from it, 0 otherwise.
 */
function is_record_cloned ($id)
{
    debug_write_log(DEBUG_TRACE, '[is_record_cloned]');
    debug_write_log(DEBUG_DUMP,  '[is_record_cloned] $id = ' . $id);

    $rs = dal_query('events/fnd.sql',
                    $id,
                    EVENT_RECORD_CLONED);

    return ($rs->rows == 0 ? 0 : $rs->fetch('event_param'));
}

/**
 * Calculates number of days since the last event of specified record.
 *
 * @param array $record Record information, as it returned by {@link record_find}.
 * @return int Number of days.
 */
function get_record_last_event ($record)
{
    debug_write_log(DEBUG_TRACE, '[get_record_last_event]');

    return ceil((time() - $record['change_time'] + 1) / SECS_IN_DAY);
}

/**
 * Calculates number of days since the last change of state of specified record.
 *
 * @param array $record Record information, as it returned by {@link record_list}.
 * @return int Number of days.
 */
function get_record_last_state ($record)
{
    debug_write_log(DEBUG_TRACE, '[get_record_last_state]');

    return ceil((time() - $record['state_time'] + 1) / SECS_IN_DAY);
}

/**
 * Calculates age of specified record.
 *
 * @param array $record Record information, as it returned by {@link record_find}.
 * @return int Age as number of days.
 */
function get_record_age ($record)
{
    debug_write_log(DEBUG_TRACE, '[get_record_age]');

    return (is_record_closed($record)
        ? ceil($record['closed_age'] / SECS_IN_DAY)
        : ceil(($record['opened_age'] + 1) / SECS_IN_DAY));
}

/**
 * Checks whether specified permissions allow to see a record.
 *
 * @param int $permissions User {@link http://www.etraxis.org/docs-schema.php#tbl_group_perms_perms permissions} (see also {@link record_get_permissions}).
 * @return bool TRUE if record can be displayed, FALSE otherwise.
 */
function can_record_be_displayed ($permissions)
{
    debug_write_log(DEBUG_TRACE, '[can_record_be_displayed]');

    return ($permissions & PERMIT_VIEW_RECORD);
}

/**
 * Checks whether current user is allowed to create new record.
 *
 * @return bool TRUE if user is allowed to create new record, FALSE otherwise.
 */
function can_record_be_created ()
{
    debug_write_log(DEBUG_TRACE, '[can_record_be_created]');

    if (get_user_level() == USER_LEVEL_GUEST)
    {
        return FALSE;
    }

    if (DATABASE_DRIVER == DRIVER_ORACLE9)
    {
        $rs = dal_query('records/oracle/plist.sql', $_SESSION[VAR_USERID]);
    }
    else
    {
        $rs = dal_query('records/plist.sql', $_SESSION[VAR_USERID]);
    }

    return ($rs->rows != 0);
}

/**
 * Checks whether specified permissions allow to modify specified record.
 *
 * @param array $record Record information, as it returned by {@link record_find}.
 * @param int $permissions User {@link http://www.etraxis.org/docs-schema.php#tbl_group_perms_perms permissions} (see also {@link record_get_permissions}).
 * @return bool TRUE if record can be modified, FALSE otherwise.
 */
function can_record_be_modified ($record, $permissions)
{
    debug_write_log(DEBUG_TRACE, '[can_record_be_modified]');

    return (get_user_level() != USER_LEVEL_GUEST &&
            !$record['is_suspended']             &&
            !$record['is_locked']                &&
            !is_record_postponed($record)        &&
            !is_record_frozen($record)           &&
            ($permissions & PERMIT_MODIFY_RECORD));
}

/**
 * Checks whether specified permissions allow to delete specified record.
 *
 * @param array $record Record information, as it returned by {@link record_find}.
 * @param int $permissions User {@link http://www.etraxis.org/docs-schema.php#tbl_group_perms_perms permissions} (see also {@link record_get_permissions}).
 * @return bool TRUE if record can be deleted, FALSE otherwise.
 */
function can_record_be_deleted ($record, $permissions)
{
    debug_write_log(DEBUG_TRACE, '[can_record_be_deleted]');

    return (get_user_level() != USER_LEVEL_GUEST &&
            !$record['is_suspended']             &&
            !$record['is_locked']                &&
            !is_record_postponed($record)        &&
            !is_record_frozen($record)           &&
            ($permissions & PERMIT_DELETE_RECORD));
}

/**
 * Checks whether specified permissions allow to postpone specified record.
 *
 * @param array $record Record information, as it returned by {@link record_find}.
 * @param int $permissions User {@link http://www.etraxis.org/docs-schema.php#tbl_group_perms_perms permissions} (see also {@link record_get_permissions}).
 * @return bool TRUE if record can be postponed, FALSE otherwise.
 */
function can_record_be_postponed ($record, $permissions)
{
    debug_write_log(DEBUG_TRACE, '[can_record_be_postponed]');

    return (get_user_level() != USER_LEVEL_GUEST &&
            !$record['is_suspended']             &&
            !$record['is_locked']                &&
            !is_record_postponed($record)        &&
            is_null($record['closure_time'])     &&
            ($permissions & PERMIT_POSTPONE_RECORD));
}

/**
 * Checks whether specified permissions allow to resume specified record.
 *
 * @param array $record Record information, as it returned by {@link record_find}.
 * @param int $permissions User {@link http://www.etraxis.org/docs-schema.php#tbl_group_perms_perms permissions} (see also {@link record_get_permissions}).
 * @return bool TRUE if record can be resumed, FALSE otherwise.
 */
function can_record_be_resumed ($record, $permissions)
{
    debug_write_log(DEBUG_TRACE, '[can_record_be_resumed]');

    return (get_user_level() != USER_LEVEL_GUEST &&
            !$record['is_suspended']             &&
            !$record['is_locked']                &&
            is_record_postponed($record)         &&
            is_null($record['closure_time'])     &&
            ($permissions & PERMIT_RESUME_RECORD));
}

/**
 * Checks whether specified permissions allow to reassign specified record.
 *
 * @param array $record Record information, as it returned by {@link record_find}.
 * @param int $permissions User {@link http://www.etraxis.org/docs-schema.php#tbl_group_perms_perms permissions} (see also {@link record_get_permissions}).
 * @return bool TRUE if record can be reassigned, FALSE otherwise.
 */
function can_record_be_reassigned ($record, $permissions)
{
    debug_write_log(DEBUG_TRACE, '[can_record_be_reassigned]');

    return (get_user_level() != USER_LEVEL_GUEST &&
            !$record['is_suspended']             &&
            !$record['is_locked']                &&
            !is_record_postponed($record)        &&
            is_null($record['closure_time'])     &&
            !is_null($record['responsible_id'])  &&
            ($permissions & PERMIT_REASSIGN_RECORD));
}

/**
 * Checks whether specified permissions allow to change state of specified record.
 *
 * @param array $record Record information, as it returned by {@link record_find}.
 * @param int $permissions User {@link http://www.etraxis.org/docs-schema.php#tbl_group_perms_perms permissions} (see also {@link record_get_permissions}).
 * @return bool TRUE if state of the record can be changed, FALSE otherwise.
 */
function can_state_be_changed ($record, $permissions)
{
    debug_write_log(DEBUG_TRACE, '[can_state_be_changed]');

    return (get_user_level() != USER_LEVEL_GUEST &&
            !$record['is_suspended']             &&
            !$record['is_locked']                &&
            !is_record_postponed($record)        &&
            is_null($record['closure_time'])     &&
                (is_null($record['responsible_id']) ||
                ($record['responsible_id'] == $_SESSION[VAR_USERID]) ||
                ($permissions & PERMIT_CHANGE_STATE)));
}

/**
 * Checks whether specified permissions allow to post a comment in specified record.
 *
 * @param array $record Record information, as it returned by {@link record_find}.
 * @param int $permissions User {@link http://www.etraxis.org/docs-schema.php#tbl_group_perms_perms permissions} (see also {@link record_get_permissions}).
 * @return bool TRUE if comment can be posted, FALSE otherwise.
 */
function can_comment_be_added ($record, $permissions)
{
    debug_write_log(DEBUG_TRACE, '[can_comment_be_added]');

    return (get_user_level() != USER_LEVEL_GUEST &&
            !$record['is_suspended']             &&
            !$record['is_locked']                &&
            !is_record_frozen($record)           &&
            ($permissions & (PERMIT_ADD_COMMENTS | PERMIT_CONFIDENTIAL_COMMENTS)));
}

/**
 * Checks whether specified permissions allow to attach file to specified record.
 *
 * @param array $record Record information, as it returned by {@link record_find}.
 * @param int $permissions User {@link http://www.etraxis.org/docs-schema.php#tbl_group_perms_perms permissions} (see also {@link record_get_permissions}).
 * @return bool TRUE if file can be attached, FALSE otherwise.
 */
function can_file_be_attached ($record, $permissions)
{
    debug_write_log(DEBUG_TRACE, '[can_file_be_attached]');

    if (ATTACHMENTS_ENABLED == 0)
    {
        return FALSE;
    }

    return (get_user_level() != USER_LEVEL_GUEST &&
            !$record['is_suspended']             &&
            !$record['is_locked']                &&
            !is_record_postponed($record)        &&
            !is_record_frozen($record)           &&
            ($permissions & PERMIT_ATTACH_FILES));
}

/**
 * Checks whether specified permissions allow to remove attached file from specified record.
 *
 * @param array $record Record information, as it returned by {@link record_find}.
 * @param int $permissions User {@link http://www.etraxis.org/docs-schema.php#tbl_group_perms_perms permissions} (see also {@link record_get_permissions}).
 * @return bool TRUE if attached file can be removed, FALSE otherwise.
 */
function can_file_be_removed ($record, $permissions)
{
    debug_write_log(DEBUG_TRACE, '[can_file_be_removed]');

    if (ATTACHMENTS_ENABLED == 0)
    {
        return FALSE;
    }

    if (get_user_level() == USER_LEVEL_GUEST)
    {
        return FALSE;
    }

    if ($permissions & PERMIT_REMOVE_FILES)
    {
        $rs = dal_query('attachs/list.sql', $record['record_id']);
    }
    else
    {
        $rs = dal_query('attachs/list2.sql', $record['record_id'], $_SESSION[VAR_USERID]);
    }

    return (!$record['is_suspended']      &&
            !$record['is_locked']         &&
            !is_record_postponed($record) &&
            !is_record_frozen($record)    &&
            ($rs->rows != 0));
}

/**
 * Checks whether specified permissions allow to add subrecord to specified record.
 *
 * @param array $record Record information, as it returned by {@link record_find}.
 * @param int $permissions User {@link http://www.etraxis.org/docs-schema.php#tbl_group_perms_perms permissions} (see also {@link record_get_permissions}).
 * @return bool TRUE if subrecord can be added, FALSE otherwise.
 */
function can_subrecord_be_added ($record, $permissions)
{
    debug_write_log(DEBUG_TRACE, '[can_subrecord_be_added]');

    return (get_user_level() != USER_LEVEL_GUEST &&
            !$record['is_suspended']             &&
            !$record['is_locked']                &&
            !is_record_postponed($record)        &&
            !is_record_frozen($record)           &&
            ($permissions & PERMIT_ADD_SUBRECORDS));
}

/**
 * Checks whether specified permissions allow to remove subrecord from specified record.
 *
 * @param array $record Record information, as it returned by {@link record_find}.
 * @param int $permissions User {@link http://www.etraxis.org/docs-schema.php#tbl_group_perms_perms permissions} (see also {@link record_get_permissions}).
 * @return bool TRUE if subrecord can be removed, FALSE otherwise.
 */
function can_subrecord_be_removed ($record, $permissions)
{
    debug_write_log(DEBUG_TRACE, '[can_subrecord_be_removed]');

    $rs = dal_query('depends/list.sql', $record['record_id']);

    return (get_user_level() != USER_LEVEL_GUEST      &&
            !$record['is_suspended']                  &&
            !$record['is_locked']                     &&
            !is_record_postponed($record)             &&
            !is_record_frozen($record)                &&
            ($permissions & PERMIT_REMOVE_SUBRECORDS) &&
            ($rs->rows != 0));
}

/**
 * Update specified {@link FIELD_TYPE_STRING string} or {@link FIELD_TYPE_MULTILINED multilined} value.
 *
 * @param string $value {@link FIELD_TYPE_STRING String} or {@link FIELD_TYPE_MULTILINED multilined} value to be processed.
 * @param int $bbcode_mode BBCode processing mode:
 * <ul>
 * <li>{@link BBCODE_OFF} - no BBCode processing, all tags are hidden<li>
 * <li>{@link BBCODE_SEARCH_ONLY} - only search tags are processed<li>
 * <li>{@link BBCODE_MINIMUM} - only basic formatting is processed<li>
 * <li>{@link BBCODE_ALL} - all available tags are processed<li>
 * </ul>
 * @param string $regex_search Search PCRE to transform field values.
 * @param string $regex_replace Replace PCRE to transform field values.
 * @return string Processed input value.
 */
function update_references ($value, $bbcode_mode = BBCODE_ALL, $regex_search = NULL, $regex_replace = NULL)
{
    debug_write_log(DEBUG_TRACE, '[update_references]');
    debug_write_log(DEBUG_DUMP,  '[update_references] $value = ' . $value);
    debug_write_log(DEBUG_DUMP,  '[update_references] $bbcode_mode   = ' . $bbcode_mode);
    debug_write_log(DEBUG_DUMP,  '[update_references] $regex_search  = ' . $regex_search);
    debug_write_log(DEBUG_DUMP,  '[update_references] $regex_replace = ' . $regex_replace);

    // Transform values with regex specified for current field.
    if (ustrlen($regex_search) != 0 && ustrlen($regex_replace) != 0)
    {
        debug_write_log(DEBUG_NOTICE, '[update_references] Regex is specified for this field.');
        $value = preg_replace("/{$regex_search}/isu", $regex_replace, $value);
    }

    // Strip special HTML characters.
    $value = ustr2html($value);

    // Transform "rec#<number>" strings into BBCode [url] tags
    $matches = array();

    if (preg_match_all('/(rec#(\d+))/iu', $value, $matches, PREG_SET_ORDER))
    {
        debug_write_log(DEBUG_NOTICE, '[update_references] "rec#" is found.');

        foreach ($matches as $match)
        {
            $id = (int)$match[2];

            if ($id != 0)
            {
                $record = record_find($id);

                if ($record)
                {
                    $replace = '[url=view.php?id=' . $id . ']' . record_id($id, $record['template_prefix']) . '[/url]';
                    $value   = ustr_replace($match[1], $replace, $value);
                }
            }
        }
    }

    // Remove non-Unix EOLs.
    $value = ustr_replace("\r", NULL, $value);

    // Trim extra EOLs inside "[code]" blocks.
    $value = ustr_replace("[code]\n",  "[code]",  $value);
    $value = ustr_replace("\n[/code]", "[/code]", $value);

    // Process BBCode tags.
    $search_text = (try_cookie(COOKIE_SEARCH_MODE, FALSE) ? try_cookie(COOKIE_SEARCH_TEXT) : NULL);
    $value = bbcode2xml($value, $bbcode_mode, $search_text);

    // Replace newline characters with "%br;".
    $value = ustr_replace("\n", '%br;', $value);

    debug_write_log(DEBUG_DUMP, '[update_references] return = ' . $value);

    return $value;
}

?>
