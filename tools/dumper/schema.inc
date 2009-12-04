<?php

//--------------------------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system.
//  Copyright (C) 2006-2009 by Artem Rodygin
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
//  Artem Rodygin           2006-09-20      Initial creation.
//  Artem Rodygin           2006-09-26      new-318: Group permissions should be template-wide.
//  Artem Rodygin           2006-10-16      new-137: Custom queries.
//  Artem Rodygin           2006-10-17      new-361: Extended custom queries.
//  Artem Rodygin           2006-11-04      new-364: Default fields values.
//  Artem Rodygin           2006-11-05      new-365: Filters sharing.
//  Artem Rodygin           2006-11-08      new-368: User should be able to subscribe other persons.
//  Artem Rodygin           2006-11-15      new-374: Carbon copies in subscriptions.
//  Artem Rodygin           2006-11-20      new-377: Custom views.
//  Artem Rodygin           2006-12-04      bug-417: SQL time is too large when no filters are applied.
//  Artem Rodygin           2006-12-15      bug-409: User session expires too quick.
//  Artem Rodygin           2007-01-05      new-491: Group-wide transition permission.
//  Artem Rodygin           2007-07-04      new-533: Links between records.
//  Artem Rodygin           2007-07-16      new-546: Confidential comments.
//  Artem Rodygin           2007-08-06      new-551: Rework dependencies into "parent-child" relations.
//  Artem Rodygin           2007-09-09      new-563: Custom separators inside fields set.
//  Artem Rodygin           2007-09-11      new-574: Filter should allow to specify several states.
//  Artem Rodygin           2007-09-12      new-576: [SF1788286] Export to CSV
//  Artem Rodygin           2007-09-13      new-566: Choose encoding for record dump and export of records list.
//  Artem Rodygin           2007-10-29      new-564: Filters set.
//  Artem Rodygin           2007-11-07      new-571: View should show all records of current filters set.
//  Artem Rodygin           2007-11-14      new-548: Custom links in text fields.
//  Artem Rodygin           2007-11-19      new-623: Default state in states list.
//  Artem Rodygin           2007-12-18      new-414: Passwords expiration.
//  Artem Rodygin           2008-01-05      new-648: Template-wide author permissions.
//  Artem Rodygin           2008-01-28      new-531: LDAP Guest users
//  Artem Rodygin           2008-03-10      new-683: Filters should be sharable with groups, not with accounts.
//  Artem Rodygin           2008-04-20      new-703: Separated permissions set for current responsible.
//  Artem Rodygin           2008-06-17      bug-724: Import MYSQL41.SQL failed
//  Artem Rodygin           2008-11-11      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2008-12-09      bug-770: MySQL server hangs up on searching.
//  Artem Rodygin           2009-01-09      new-774: 'Anyone' system role permissions.
//  Artem Rodygin           2009-09-30      new-826: Native unicode support for Microsoft SQL Server.
//--------------------------------------------------------------------------------------------------

define('TABLE_NAME',    0);
define('AUTOINCREMENT', 1);
define('FIELDS_ORDER',  2);
define('FIELDS_SET',    3);

$schema = array
(
    array
    (
        'tbl_accounts',
        TRUE,
        'account_id',
        'i:account_id s:username s:fullname s:email s:passwd s:description s:auth_token i:token_expire i:passwd_expire i:is_admin i:is_disabled i:is_ldapuser i:locks_count i:lock_time i:locale i:page_rows i:page_bkms i:csv_delim i:csv_encoding i:csv_line_ends i:fset_id i:view_id'
    ),
    array
    (
        'tbl_projects',
        TRUE,
        'project_id',
        'i:project_id s:project_name i:start_time s:description i:is_suspended'
    ),
    array
    (
        'tbl_groups',
        TRUE,
        'group_id',
        'i:group_id i:project_id s:group_name s:description'
    ),
    array
    (
        'tbl_membership',
        FALSE,
        'group_id, account_id',
        'i:group_id i:account_id'
    ),
    array
    (
        'tbl_templates',
        TRUE,
        'template_id',
        'i:template_id i:project_id s:template_name s:template_prefix i:critical_age i:frozen_time s:description i:is_locked i:guest_access i:registered_perm i:author_perm i:responsible_perm'
    ),
    array
    (
        'tbl_group_perms',
        FALSE,
        'group_id, template_id',
        'i:group_id i:template_id i:perms'
    ),
    array
    (
        'tbl_states',
        TRUE,
        'state_id',
        'i:state_id i:template_id s:state_name s:state_abbr i:state_type i:next_state_id i:responsible'
    ),
    array
    (
        'tbl_group_trans',
        FALSE,
        'state_id_from, state_id_to, group_id',
        'i:state_id_from i:state_id_to i:group_id'
    ),
    array
    (
        'tbl_role_trans',
        FALSE,
        'state_id_from, state_id_to, role',
        'i:state_id_from i:state_id_to i:role'
    ),
    array
    (
        'tbl_fields',
        TRUE,
        'field_id',
        'i:field_id i:state_id s:field_name i:field_order i:field_type i:is_required i:guest_access i:registered_perm i:author_perm i:responsible_perm i:add_separator s:regex_check s:regex_search s:regex_replace i:param1 i:param2 i:value_id'
    ),
    array
    (
        'tbl_field_perms',
        FALSE,
        'field_id, group_id',
        'i:field_id i:group_id i:perms'
    ),
    array
    (
        'tbl_records',
        TRUE,
        'record_id',
        'i:record_id i:state_id s:subject i:responsible_id i:creator_id i:creation_time i:change_time i:closure_time i:postpone_time'
    ),
    array
    (
        'tbl_children',
        FALSE,
        'parent_id, child_id, is_dependency',
        'i:parent_id i:child_id i:is_dependency'
    ),
    array
    (
        'tbl_reads',
        FALSE,
        'record_id, account_id',
        'i:record_id i:account_id i:read_time'
    ),
    array
    (
        'tbl_record_subscribes',
        FALSE,
        'record_id, account_id, subscribed_by',
        'i:record_id i:account_id i:subscribed_by'
    ),
    array
    (
        'tbl_events',
        TRUE,
        'event_id',
        'i:event_id i:record_id i:originator_id i:event_type i:event_time i:event_param'
    ),
    array
    (
        'tbl_field_values',
        FALSE,
        'event_id, field_id',
        'i:event_id i:field_id i:field_type i:value_id i:is_latest'
    ),
    array
    (
        'tbl_changes',
        FALSE,
        'event_id, field_id',
        'i:event_id i:field_id i:old_value_id i:new_value_id'
    ),
    array
    (
        'tbl_string_values',
        TRUE,
        'value_id',
        'i:value_id s:value_token s:string_value'
    ),
    array
    (
        'tbl_text_values',
        TRUE,
        'value_id',
        'i:value_id s:value_token t:text_value'
    ),
    array
    (
        'tbl_list_values',
        FALSE,
        'field_id, int_value',
        'i:field_id i:int_value s:str_value'
    ),
    array
    (
        'tbl_comments',
        TRUE,
        'comment_id',
        'i:comment_id t:comment_body i:event_id i:is_confidential'
    ),
    array
    (
        'tbl_attachments',
        TRUE,
        'attachment_id',
        'i:attachment_id s:attachment_name s:attachment_type i:attachment_size i:event_id i:is_removed'
    ),
    array
    (
        'tbl_filters',
        TRUE,
        'filter_id',
        'i:filter_id i:account_id s:filter_name i:filter_type i:filter_flags i:filter_param'
    ),
    array
    (
        'tbl_filter_sharing',
        FALSE,
        'filter_id, group_id',
        'i:filter_id i:group_id'
    ),
    array
    (
        'tbl_filter_activation',
        FALSE,
        'filter_id, account_id',
        'i:filter_id i:account_id'
    ),
    array
    (
        'tbl_filter_accounts',
        FALSE,
        'filter_id, filter_flag, account_id',
        'i:filter_id i:filter_flag i:account_id'
    ),
    array
    (
        'tbl_filter_states',
        FALSE,
        'filter_id, state_id',
        'i:filter_id i:state_id'
    ),
    array
    (
        'tbl_filter_trans',
        FALSE,
        'filter_id, state_id, date1, date2',
        'i:filter_id i:state_id i:date1 i:date2'
    ),
    array
    (
        'tbl_filter_fields',
        FALSE,
        'filter_id, field_id, param1, param2',
        'i:filter_id i:field_id i:param1 i:param2'
    ),
    array
    (
        'tbl_fsets',
        TRUE,
        'fset_id',
        'i:fset_id i:account_id s:fset_name'
    ),
    array
    (
        'tbl_fset_filters',
        FALSE,
        'fset_id, filter_id',
        'i:fset_id i:filter_id'
    ),
    array
    (
        'tbl_views',
        TRUE,
        'view_id',
        'i:view_id i:account_id s:view_name'
    ),
    array
    (
        'tbl_view_columns',
        TRUE,
        'column_id',
        'i:column_id i:view_id s:state_name s:field_name i:column_type i:column_order'
    ),
    array
    (
        'tbl_def_columns',
        TRUE,
        'column_id',
        'i:column_id i:account_id s:state_name s:field_name i:column_type i:column_order'
    ),
    array
    (
        'tbl_subscribes',
        TRUE,
        'subscribe_id',
        'i:subscribe_id i:account_id s:subscribe_name s:carbon_copy i:subscribe_type i:subscribe_flags i:subscribe_param i:is_activated'
    ),
    array
    (
        'tbl_reminders',
        TRUE,
        'reminder_id',
        'i:reminder_id i:account_id s:reminder_name s:subject_text i:state_id i:group_id i:group_flag'
    ),
);

?>
