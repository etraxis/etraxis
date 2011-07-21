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
 * Records
 *
 * This module provides API to work with records.
 * See also {@link http://code.google.com/p/etraxis/wiki/DatabaseSchema#tbl_records tbl_records} database table.
 *
 * @package DBO
 * @subpackage Records
 */

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

//------------------------------------------------------------------------------
//  Definitions.
//------------------------------------------------------------------------------

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

/**#@+
 * Tabs on record view page.
 */
define('RECORD_TAB_MAIN',           1);
define('RECORD_TAB_HISTORY',        2);
define('RECORD_TAB_CHANGES',        3);
define('RECORD_TAB_FIELDS',         4);
define('RECORD_TAB_COMMENTS',       5);
define('RECORD_TAB_ATTACHMENTS',    6);
define('RECORD_TAB_PARENTS',        7);
define('RECORD_TAB_SUBRECORDS',     8);
/**#@-*/

//------------------------------------------------------------------------------
//  Functions.
//------------------------------------------------------------------------------

/**
 * Formats specified record ID, adding template prefix if specified and leading zeroes if required.
 *
 * @param int $id Record ID.
 * @param string $prefix Template prefix.
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
 * @param int $id Record ID.
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
function records_list ($columns, &$sort, &$page, $search_mode = FALSE, $search_text = NULL)
{
    debug_write_log(DEBUG_TRACE, '[records_list]');

    $sort = try_request('sort', try_cookie(COOKIE_RECORDS_SORT . $_SESSION[VAR_VIEW]));
    $sort = ustr2int($sort, -count($columns), count($columns));

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

    save_cookie(COOKIE_RECORDS_SORT . $_SESSION[VAR_VIEW], $sort);
    save_cookie(COOKIE_RECORDS_PAGE . $_SESSION[VAR_VIEW], $page);

    // Add default access conditions for guests and registered users.

    if (get_user_level() == USER_LEVEL_GUEST)
    {
        array_push($clause_select, '0 as read_time');
        array_push($clause_from,   'tbl_templates t');
        array_push($clause_where,  't.guest_access = 1');
        array_push($clause_where,  'r.closure_time is null');
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

    // SQL condition to check that current user is allowed to read the field.

    $sql_field_perms = <<<SQL

        f.field_id in (select f.field_id
                       from
                           tbl_group_perms  gp,
                           tbl_membership   ms,
                           tbl_field_perms  fp,
                           tbl_fields f
                       where
                           fp.field_id   = f.field_id  and
                           fp.group_id   = gp.group_id and
                           ms.group_id   = gp.group_id and
                           ms.account_id = {$_SESSION[VAR_USERID]} and
                           fp.perms      = 1)

        or r.creator_id = {$_SESSION[VAR_USERID]} and
           f.field_id in (select f.field_id
                          from tbl_fields f
                          where f.author_perm >= 1)

        or r.responsible_id = {$_SESSION[VAR_USERID]} and
           f.field_id in (select f.field_id
                          from tbl_fields f
                          where f.responsible_perm >= 1)

        or f.field_id in (select f.field_id
                          from tbl_fields f
                          where f.registered_perm >= 1 or f.guest_access = 1)
SQL;

    // Generate columns of the current view.

    foreach ($columns as $i => $column)
    {
        $i += 1;

        switch ($column['column_type'])
        {
            case COLUMN_TYPE_ID:

                array_push($clause_select, 't.template_prefix');

                if ($i == $sort)
                {
                    array_push($clause_order, 'r.record_id asc');
                }
                elseif (-$i == $sort)
                {
                    array_push($clause_order, 'r.record_id desc');
                }

                break;

            case COLUMN_TYPE_STATE_ABBR:

                array_push($clause_select, 's.state_abbr');

                if ($i == $sort)
                {
                    array_push($clause_order, 's.state_abbr asc');
                }
                elseif (-$i == $sort)
                {
                    array_push($clause_order, 's.state_abbr desc');
                }

                break;

            case COLUMN_TYPE_PROJECT:

                array_push($clause_select, 'p.project_name');

                if ($i == $sort)
                {
                    array_push($clause_order, 'p.project_name asc');
                }
                elseif (-$i == $sort)
                {
                    array_push($clause_order, 'p.project_name desc');
                }

                break;

            case COLUMN_TYPE_SUBJECT:

                array_push($clause_select, 'r.subject');

                if ($i == $sort)
                {
                    array_push($clause_order, 'r.subject asc');
                }
                elseif (-$i == $sort)
                {
                    array_push($clause_order, 'r.subject desc');
                }

                break;

            case COLUMN_TYPE_AUTHOR:

                array_push($clause_select, 'ac.fullname as author_fullname');
                array_push($clause_join,   'left outer join tbl_accounts ac on ac.account_id = r.creator_id');

                if ($i == $sort)
                {
                    array_push($clause_order, 'author_fullname asc');
                }
                elseif (-$i == $sort)
                {
                    array_push($clause_order, 'author_fullname desc');
                }

                break;

            case COLUMN_TYPE_RESPONSIBLE:

                array_push($clause_select, 'ar.fullname as responsible_fullname');
                array_push($clause_join,   'left outer join tbl_accounts ar on ar.account_id = r.responsible_id');

                if ($i == $sort)
                {
                    array_push($clause_order, 'responsible_fullname asc');
                }
                elseif (-$i == $sort)
                {
                    array_push($clause_order, 'responsible_fullname desc');
                }

                break;

            case COLUMN_TYPE_LAST_EVENT:

                if ($i == $sort)
                {
                    array_push($clause_order, 'change_time asc');
                }
                elseif (-$i == $sort)
                {
                    array_push($clause_order, 'change_time desc');
                }

                break;

            case COLUMN_TYPE_AGE:

                array_push($clause_select, '(' . $time . ' - r.creation_time) as opened_age');
                array_push($clause_select, '(r.closure_time - r.creation_time) as closed_age');

                if ($i == $sort)
                {
                    array_push($clause_order, 'closed_age asc');
                    array_push($clause_order, 'opened_age asc');
                }
                elseif (-$i == $sort)
                {
                    array_push($clause_order, 'closed_age desc');
                    array_push($clause_order, 'opened_age desc');
                }

                break;

            case COLUMN_TYPE_CREATION_DATE:

                array_push($clause_select, 'r.creation_time');

                if ($i == $sort)
                {
                    array_push($clause_order, 'creation_time asc');
                }
                elseif (-$i == $sort)
                {
                    array_push($clause_order, 'creation_time desc');
                }

                break;

            case COLUMN_TYPE_TEMPLATE:

                array_push($clause_select, 't.template_name');

                if ($i == $sort)
                {
                    array_push($clause_order, 't.template_name asc');
                }
                elseif (-$i == $sort)
                {
                    array_push($clause_order, 't.template_name desc');
                }

                break;

            case COLUMN_TYPE_STATE_NAME:

                array_push($clause_select, 's.state_name');

                if ($i == $sort)
                {
                    array_push($clause_order, 's.state_name asc');
                }
                elseif (-$i == $sort)
                {
                    array_push($clause_order, 's.state_name desc');
                }

                break;

            case COLUMN_TYPE_LAST_STATE:

                array_push($clause_select, 'st.state_time');
                array_push($clause_from,   '(select record_id, max(event_time) as state_time' .
                                           ' from tbl_events' .
                                           ' where event_type = 1 or event_type = 4' .
                                           ' group by record_id) st');
                array_push($clause_where,  'r.record_id = st.record_id');

                if ($i == $sort)
                {
                    array_push($clause_order, 'state_time asc');
                }
                elseif (-$i == $sort)
                {
                    array_push($clause_order, 'state_time desc');
                }

                break;

            case COLUMN_TYPE_FLOAT:

                array_push($clause_select, "v{$column['column_id']}.value{$column['column_id']}");

                array_push($clause_join,
                           "left outer join " .
                           "(select r.record_id, flv.float_value as value{$column['column_id']} " .
                           "from tbl_records r, tbl_states s, tbl_fields f, tbl_events e, tbl_field_values fv " .
                           "left outer join tbl_float_values flv on fv.value_id = flv.value_id " .
                           "where r.record_id = e.record_id and s.state_id = f.state_id and s.state_name = '{$column['state_name']}' and f.field_id = fv.field_id and f.field_name = '{$column['field_name']}' and f.field_type = " . FIELD_TYPE_FLOAT . " and e.event_id = fv.event_id and fv.is_latest = 1 and ({$sql_field_perms})) v{$column['column_id']} " .
                           "on r.record_id = v{$column['column_id']}.record_id");

                if ($i == $sort)
                {
                    array_push($clause_order, "v{$column['column_id']}.value{$column['column_id']} asc");
                }
                elseif (-$i == $sort)
                {
                    array_push($clause_order, "v{$column['column_id']}.value{$column['column_id']} desc");
                }

                break;

            case COLUMN_TYPE_STRING:

                array_push($clause_select, "v{$column['column_id']}.value{$column['column_id']}");

                array_push($clause_join,
                           "left outer join " .
                           "(select r.record_id, sv.string_value as value{$column['column_id']} " .
                           "from tbl_records r, tbl_states s, tbl_fields f, tbl_events e, tbl_field_values fv " .
                           "left outer join tbl_string_values sv on fv.value_id = sv.value_id " .
                           "where r.record_id = e.record_id and s.state_id = f.state_id and s.state_name = '{$column['state_name']}' and f.field_id = fv.field_id and f.field_name = '{$column['field_name']}' and f.field_type = " . FIELD_TYPE_STRING . " and e.event_id = fv.event_id and fv.is_latest = 1 and ({$sql_field_perms})) v{$column['column_id']} " .
                           "on r.record_id = v{$column['column_id']}.record_id");

                if ($i == $sort)
                {
                    array_push($clause_order, "v{$column['column_id']}.value{$column['column_id']} asc");
                }
                elseif (-$i == $sort)
                {
                    array_push($clause_order, "v{$column['column_id']}.value{$column['column_id']} desc");
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
                           "(select r.record_id, {$txtval} as value{$column['column_id']} " .
                           "from tbl_records r, tbl_states s, tbl_fields f, tbl_events e, tbl_field_values fv " .
                           "left outer join tbl_text_values tv on fv.value_id = tv.value_id " .
                           "where r.record_id = e.record_id and s.state_id = f.state_id and s.state_name = '{$column['state_name']}' and f.field_id = fv.field_id and f.field_name = '{$column['field_name']}' and f.field_type = " . FIELD_TYPE_MULTILINED . " and e.event_id = fv.event_id and fv.is_latest = 1 and ({$sql_field_perms})) v{$column['column_id']} " .
                           "on r.record_id = v{$column['column_id']}.record_id");

                if ($i == $sort)
                {
                    array_push($clause_order, DATABASE_DRIVER == DRIVER_ORACLE9
                                                    ? "to_char(v{$column['column_id']}.value{$column['column_id']}) asc"
                                                    : "v{$column['column_id']}.value{$column['column_id']} asc");
                }
                elseif (-$i == $sort)
                {
                    array_push($clause_order, DATABASE_DRIVER == DRIVER_ORACLE9
                                                    ? "to_char(v{$column['column_id']}.value{$column['column_id']}) desc"
                                                    : "v{$column['column_id']}.value{$column['column_id']} desc");
                }

                break;

            case COLUMN_TYPE_LIST_STRING:

                array_push($clause_select, "v{$column['column_id']}.value{$column['column_id']}");

                array_push($clause_join,
                           "left outer join " .
                           "(select r.record_id, lv.str_value as value{$column['column_id']} " .
                           "from tbl_records r, tbl_states s, tbl_fields f, tbl_events e, tbl_field_values fv " .
                           "left outer join tbl_list_values lv on fv.field_id = lv.field_id and fv.value_id = lv.int_value " .
                           "where r.record_id = e.record_id and s.state_id = f.state_id and s.state_name = '{$column['state_name']}' and f.field_id = fv.field_id and f.field_name = '{$column['field_name']}' and f.field_type = " . FIELD_TYPE_LIST . " and e.event_id = fv.event_id and fv.is_latest = 1 and ({$sql_field_perms})) v{$column['column_id']} " .
                           "on r.record_id = v{$column['column_id']}.record_id");

                if ($i == $sort)
                {
                    array_push($clause_order, "v{$column['column_id']}.value{$column['column_id']} asc");
                }
                elseif (-$i == $sort)
                {
                    array_push($clause_order, "v{$column['column_id']}.value{$column['column_id']} desc");
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
                           "(select r.record_id, fv.value_id as value{$column['column_id']} " .
                           "from tbl_records r, tbl_states s, tbl_fields f, tbl_events e, tbl_field_values fv " .
                           "where r.record_id = e.record_id and s.state_id = f.state_id and s.state_name = '{$column['state_name']}' and f.field_id = fv.field_id and f.field_name = '{$column['field_name']}' and f.field_type = {$types[$column['column_type']]} and e.event_id = fv.event_id and fv.is_latest = 1 and ({$sql_field_perms})) v{$column['column_id']} " .
                           "on r.record_id = v{$column['column_id']}.record_id");

                if ($i == $sort)
                {
                    array_push($clause_order, "v{$column['column_id']}.value{$column['column_id']} asc");
                }
                elseif (-$i == $sort)
                {
                    array_push($clause_order, "v{$column['column_id']}.value{$column['column_id']} desc");
                }

                break;

            default:
                debug_write_log(DEBUG_WARNING, '[records_list] Unknown column type = ' . $column['column_type']);
        }
    }

    // Add search conditions if search is activated.

    if ($search_mode)
    {
        debug_write_log(DEBUG_NOTICE, '[records_list] Search mode is turned on.');

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

    // Add filters, if it's not a search or if it's a filtered search.

    if (!$search_mode || $_SESSION[VAR_USE_FILTERS])
    {
        debug_write_log(DEBUG_NOTICE, '[records_list] Filters are in use.');

        $filters = array();

        $fsort = $fpage = NULL;
        $rs = filters_list($_SESSION[VAR_USERID], TRUE, $fsort, $fpage);

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
                    debug_write_log(DEBUG_WARNING, '[records_list] Unknown filter type = ' . $filter['filter_type']);
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

            if ($filter['filter_flags'] & FILTER_FLAG_POSTPONED)
            {
                array_push($clause_filter, 'r.postpone_time > ' . $time);
            }

            if ($filter['filter_flags'] & FILTER_FLAG_ACTIVE)
            {
                array_push($clause_filter, 'r.postpone_time <=' . $time);
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

                        case FIELD_TYPE_FLOAT:

                            $range = (is_null($row['param1']) && is_null($row['param2']) ? 'fv.value_id is null and ' : NULL);

                            if (!is_null($row['param1']))
                            {
                                $range .= 'fl1.float_value >= fl2.float_value and ';
                            }

                            if (!is_null($row['param2']))
                            {
                                $range .= 'fl1.float_value <= fl3.float_value and ';
                            }

                            array_push($clause_filter,
                                       'r.record_id in ' .
                                       '(select e.record_id ' .
                                       'from tbl_events e, tbl_field_values fv, tbl_float_values fl1, tbl_float_values fl2, tbl_float_values fl3 ' .
                                       'where fv.event_id = e.event_id and ' .
                                       'fv.field_id = '  . $row['field_id'] . ' and ' .
                                       'fv.value_id = fl1.value_id and ' .
                                       $range .
                                       'fl2.value_id = ' . $row['param1'] . ' and ' .
                                       'fl3.value_id = ' . $row['param2'] . ' and ' .
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

    // Add default sorting.

    if ($sort < 0)
    {
        array_push($clause_order, 'r.record_id desc');
    }
    else
    {
        array_push($clause_order, 'r.record_id asc');
    }

    // Bring it all together.

    $sql = 'select '    . implode(', ',    array_unique($clause_select)) .
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
 * @param int $id ID of project which records should be counted.
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
 * @param int $id ID of project which records should be counted.
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
 * @param string $subject Subject of the record (ignored on state change).
 * @param int $record_id Record ID (should be NULL on creation).
 * @param int $state_id ID of new state (current on modification).
 * @param int $creator_id Author of record (used only on modification, otherwise ignored).
 * @param int $responsible_id Responsible of record (used only on modification, otherwise ignored).
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

            case FIELD_TYPE_FLOAT:

                if (ustrlen($value) != 0)
                {
                    if (!is_floatvalue($value))
                    {
                        debug_write_log(DEBUG_NOTICE, '[record_validate] Invalid float value.');
                        return ERROR_INVALID_FLOAT_VALUE;
                    }

                    $minFieldFloat = value_find(FIELD_TYPE_FLOAT, $row['param1']);
                    $maxFieldFloat = value_find(FIELD_TYPE_FLOAT, $row['param2']);

                    if (bccomp($value, $minFieldFloat) < 0 || bccomp($value, $maxFieldFloat) > 0)
                    {
                        $_SESSION['FIELD_NAME']        = $row['field_name'];
                        $_SESSION['MIN_FIELD_INTEGER'] = $minFieldFloat;
                        $_SESSION['MAX_FIELD_INTEGER'] = $maxFieldFloat;
                        debug_write_log(DEBUG_NOTICE, '[record_validate] Float value is out of range.');
                        return ERROR_FLOAT_VALUE_OUT_OF_RANGE;
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
 * @param int &$id ID of newly created record (used as output only).
 * @param string $subject Subject of new record.
 * @param int $state_id ID of initial state of new record.
 * @param int $responsible_id If record should be assigned on creation, then ID of responsible of new record; NULL (default) otherwise.
 * @param int $clone_id If record is being cloned from another, ID of original record, 0 (default) otherwise.
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
            case FIELD_TYPE_FLOAT:
                value_create_float($event['event_id'], $row['field_id'], $row['field_type'], (ustrlen($value) == 0 ? NULL : ustrcut($value, ustrlen(MAX_FIELD_FLOAT))));
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
 * @param int $id ID of record to be modified.
 * @param string $subject New subject of the record.
 * @param int $creator_id Current author of record.
 * @param int $responsible_id Current responsible of record (NULL, if record is not assigned).
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
                case FIELD_TYPE_FLOAT:
                    value_modify_float($id, $event['event_id'], $row['field_id'], (ustrlen($value) == 0 ? NULL : ustrcut($value, ustrlen(MAX_FIELD_FLOAT))));
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
 * @param int $id ID of record to be deleted.
 * @return int Always {@link NO_ERROR}.
 */
function record_delete ($id)
{
    debug_write_log(DEBUG_TRACE, '[record_delete]');
    debug_write_log(DEBUG_DUMP,  '[record_delete] $id = ' . $id);

    $rs = dal_query('attachs/list.sql', $id, 'attachment_id');

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
 * @param int $id ID of record to be postponed.
 * @param int $date Unix timestamp of the date when record will be resumed automatically.
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
 * @param int $id ID of record to be resumed.
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
 * @param int $rid ID of record to be assigned.
 * @param int $aid ID of new responsible.
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
 * @param int $id ID of record to be marked as read.
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
 * Marks specified record as unread (for current user).
 *
 * @param int $id ID of record to be marked as unread.
 * @return int Always {@link NO_ERROR}.
 */
function record_unread ($id)
{
    debug_write_log(DEBUG_TRACE, '[record_unread]');
    debug_write_log(DEBUG_DUMP,  '[record_unread] $id = ' . $id);

    if (get_user_level() != USER_LEVEL_GUEST)
    {
        dal_query('records/unread.sql', $id, $_SESSION[VAR_USERID]);
    }

    return NO_ERROR;
}

/**
 * Change state of specified record.
 *
 * @param int $id ID of record which state should be changed.
 * @param int $state_id New state of the record.
 * @param int $responsible_id ID of new responsible:
 * <ul>
 * <li>account ID, if the record should be assigned</li>
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
            case FIELD_TYPE_FLOAT:
                value_create_float($event['event_id'], $row['field_id'], $row['field_type'], (ustrlen($value) == 0 ? NULL : ustrcut($value, ustrlen(MAX_FIELD_FLOAT))));
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
 * @param int $id Record ID.
 * @param int $permissions User permissions (see also {@link record_get_permissions}).
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
 * @param int $id Record ID.
 * @param int $creator_id Current author of the record.
 * @param int $responsible_id Current responsible of the record (NULL, if record is not assigned).
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
 * @param int $event_id ID of event, registered when comment has been added.
 * @param int $permissions User permissions (see also {@link record_get_permissions}).
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
 * @param int $id Record ID.
 * @param string $comment Text of comment.
 * @param bool $is_confidential Whether the comment is confidential.
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
        $sql = file_get_contents(LOCALROOT . '/sql/comments/oracle/create.sql');

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
 * @param int $attachment_id Attachment ID.
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
 * sorted by attachment name.
 *
 * @param int $id Record ID.
 * @param int &$sort Sort mode (used as output only). The function retrieves current sort mode from
 * client cookie ({@link COOKIE_ATTACHMENTS_SORT}) and updates it, if it's out of valid range.
 * @param int &$page Number of current page tab (used as output only). The function retrieves current
 * page from client cookie ({@link COOKIE_ATTACHMENTS_PAGE}) and updates it, if it's out of valid range.
 * @return CRecordset Recordset with list of attachments.
 */
function attachments_list ($id, &$sort, &$page)
{
    debug_write_log(DEBUG_TRACE, '[attachments_list]');
    debug_write_log(DEBUG_DUMP,  '[attachments_list] $id = ' . $id);

    $sort_modes = array
    (
        1 => 'attachment_name asc',
        2 => 'attachment_size asc, attachment_name asc',
        3 => 'fullname asc, username asc, attachment_name asc',
        4 => 'event_time asc, attachment_name asc',
        5 => 'attachment_name desc',
        6 => 'attachment_size desc, attachment_name desc',
        7 => 'fullname desc, username desc, attachment_name desc',
        8 => 'event_time desc, attachment_name desc',
    );

    $sort = try_request('sort', try_cookie(COOKIE_ATTACHMENTS_SORT));
    $sort = ustr2int($sort, 1, count($sort_modes));

    $page = try_request('page', try_cookie(COOKIE_ATTACHMENTS_PAGE));
    $page = ustr2int($page, 1, MAXINT);

    save_cookie(COOKIE_ATTACHMENTS_SORT, $sort);
    save_cookie(COOKIE_ATTACHMENTS_PAGE, $page);

    return dal_query('attachs/list.sql', $id, $sort_modes[$sort]);
}

/**
 * Adds new attachment to specified record.
 *
 * @param int $id Record ID.
 * @param string $name Attachment name.
 * @param array $attachfile Information about uploaded user file (see {@link http://www.php.net/features.file-upload} for details).
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - attachment is successfully created</li>
 * <li>{@link ERROR_NOT_FOUND} - record cannot be found</li>
 * <li>{@link ERROR_ALREADY_EXISTS} - attachment with specified name already exists</li>
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
 * @param int $id Record ID.
 * @param int $permissions User permissions (see also {@link record_get_permissions}).
 * @param int $attachment_id ID of attachment to be removed.
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

    if (($permissions & PERMIT_REMOVE_FILES) == 0)
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
 * Returns {@link CRecordset DAL recordset} which contains all parent records of specified record, sorted by ID.
 *
 * @param int $id Record ID.
 * @return CRecordset Recordset with list of parent records.
 */
function parents_list ($id)
{
    debug_write_log(DEBUG_TRACE, '[parents_list]');
    debug_write_log(DEBUG_DUMP,  '[parents_list] $id = ' . $id);

    return dal_query('depends/parents.sql', $id);
}

/**
 * Returns {@link CRecordset DAL recordset} which contains all subrecords of specified record, sorted by ID.
 *
 * @param int $id Record ID.
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
 * @param int $parent_id Parent record ID.
 * @param int $subrecord_id Subrecord ID.
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
 * @param int $parent_id Parent record ID.
 * @param int $subrecord_id Subrecord ID.
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
 * @param int $parent_id Parent record ID.
 * @param int $subrecord_id Subrecord ID.
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
 * @param int $template_id ID of record's template.
 * @param int $creator_id Author of record.
 * @param int $responsible_id Responsible of record.
 * @return int Set of binary flags:
 * <ul>
 * <li>{@link PERMIT_CREATE_RECORD} - permission to create new records</li>
 * <li>{@link PERMIT_MODIFY_RECORD} - permission to modify records</li>
 * <li>{@link PERMIT_POSTPONE_RECORD} - permission to postpone records</li>
 * <li>{@link PERMIT_RESUME_RECORD} - permission to resume records</li>
 * <li>{@link PERMIT_REASSIGN_RECORD} - permission to reassign records, which are already assigned on another person</li>
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
 * @param int $record_id Record ID.
 * @param int $account_id ID of account which is being subscribed.
 * @param int $subscribed_by ID of account which is subscribing another one.
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
 * @param int $record_id Record ID.
 * @param int $account_id ID of account which is being unsubscribed.
 * @param int $subscribed_by ID of account which is unsubscribing another one.
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
 * @param int $record_id Record ID.
 * @param int $account_id Account ID.
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
 * @param int $id Record ID.
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
 * @param array $record Record information, as it returned by {@link records_list}.
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
 * @param int $permissions User permissions (see also {@link record_get_permissions}).
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
 * @param int $permissions User permissions (see also {@link record_get_permissions}).
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
 * @param int $permissions User permissions (see also {@link record_get_permissions}).
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
 * @param int $permissions User permissions (see also {@link record_get_permissions}).
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
 * @param int $permissions User permissions (see also {@link record_get_permissions}).
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
 * @param int $permissions User permissions (see also {@link record_get_permissions}).
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
 * Checks whether it's allowed to change state of specified record.
 *
 * @param array $record Record information, as it returned by {@link record_find}.
 * @return bool TRUE if state of the record can be changed, FALSE otherwise.
 */
function can_state_be_changed ($record)
{
    debug_write_log(DEBUG_TRACE, '[can_state_be_changed]');

    return (get_user_level() != USER_LEVEL_GUEST &&
            !$record['is_suspended']             &&
            !$record['is_locked']                &&
            !is_record_postponed($record)        &&
            is_null($record['closure_time']));
}

/**
 * Checks whether specified permissions allow to post a comment in specified record.
 *
 * @param array $record Record information, as it returned by {@link record_find}.
 * @param int $permissions User permissions (see also {@link record_get_permissions}).
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
 * @param int $permissions User permissions (see also {@link record_get_permissions}).
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
            !is_record_frozen($record)           &&
            ($permissions & PERMIT_ATTACH_FILES));
}

/**
 * Checks whether specified permissions allow to remove attached file from specified record.
 *
 * @param array $record Record information, as it returned by {@link record_find}.
 * @param int $permissions User permissions (see also {@link record_get_permissions}).
 * @return bool TRUE if attached file can be removed, FALSE otherwise.
 */
function can_file_be_removed ($record)
{
    debug_write_log(DEBUG_TRACE, '[can_file_be_removed]');

    if (ATTACHMENTS_ENABLED == 0)
    {
        return FALSE;
    }

    return (get_user_level() != USER_LEVEL_GUEST &&
            !$record['is_suspended']             &&
            !$record['is_locked']                &&
            !is_record_frozen($record));
}

/**
 * Checks whether specified permissions allow to add subrecord to specified record.
 *
 * @param array $record Record information, as it returned by {@link record_find}.
 * @param int $permissions User permissions (see also {@link record_get_permissions}).
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
 * @param int $permissions User permissions (see also {@link record_get_permissions}).
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

    // Replace newline characters with "%br;".
    $value = ustr_replace("\n", '%br;', $value);

    // Process BBCode tags.
    $search_text = ($_SESSION[VAR_SEARCH_MODE] ? $_SESSION[VAR_SEARCH_TEXT] : NULL);
    $value = bbcode2xml($value, $bbcode_mode, $search_text);

    debug_write_log(DEBUG_DUMP, '[update_references] return = ' . $value);

    return $value;
}

?>
