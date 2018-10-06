/*----------------------------------------------------------------------------*/
/*                                                                            */
/*  eTraxis - Records tracking web-based system                               */
/*  Copyright (C) 2005-2011  Artem Rodygin                                    */
/*                                                                            */
/*  This program is free software: you can redistribute it and/or modify      */
/*  it under the terms of the GNU General Public License as published by      */
/*  the Free Software Foundation, either version 3 of the License, or         */
/*  (at your option) any later version.                                       */
/*                                                                            */
/*  This program is distributed in the hope that it will be useful,           */
/*  but WITHOUT ANY WARRANTY; without even the implied warranty of            */
/*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             */
/*  GNU General Public License for more details.                              */
/*                                                                            */
/*  You should have received a copy of the GNU General Public License         */
/*  along with this program.  If not, see <http://www.gnu.org/licenses/>.     */
/*                                                                            */
/*----------------------------------------------------------------------------*/
/*  Server type: MySQL 5.0                                                    */
/*----------------------------------------------------------------------------*/

create database etraxis
character set utf8
collate utf8_general_ci;

use etraxis;

create table tbl_sys_vars
(
    var_name varchar (32) not null,
    var_value varchar (100) null
);

alter table tbl_sys_vars add constraint unique ix_sys_vars
(
    var_name
);

create table tbl_accounts
(
    account_id int not null auto_increment primary key,
    username varchar (112) not null,
    fullname varchar (64) not null,
    email varchar (50) not null,
    passwd varchar (32) null,
    description varchar (100) null,
    auth_token varchar (32) null,
    google2fa_secret varchar (32) null,
    token_expire int not null,
    passwd_expire int not null,
    is_admin int not null,
    is_disabled int not null,
    is_ldapuser int not null,
    locks_count int not null,
    lock_time int not null,
    locale int not null,
    timezone int not null,
    text_rows int not null,
    page_rows int not null,
    page_bkms int not null,
    auto_refresh int not null,
    csv_delim int not null,
    csv_encoding int not null,
    csv_line_ends int not null,
    view_id int null,
    theme_name varchar (50) not null
);

alter table tbl_accounts add constraint unique ix_accounts
(
    username
);

create table tbl_projects
(
    project_id int not null auto_increment primary key,
    project_name varchar (25) not null,
    start_time int not null,
    description varchar (100) null,
    is_suspended int not null
);

alter table tbl_projects add constraint unique ix_projects
(
    project_name
);

create table tbl_groups
(
    group_id int not null auto_increment primary key,
    project_id int null,
    group_name varchar (25) not null,
    description varchar (100) null
);

alter table tbl_groups add constraint unique ix_groups
(
    project_id,
    group_name
);

alter table tbl_groups add constraint foreign key fk_groups_project_id
(
    project_id
)
references tbl_projects
(
    project_id
);

create table tbl_membership
(
    group_id int not null,
    account_id int not null
);

alter table tbl_membership add primary key
(
    group_id,
    account_id
);

alter table tbl_membership add constraint foreign key fk_membership_group_id
(
    group_id
)
references tbl_groups
(
    group_id
);

alter table tbl_membership add constraint foreign key fk_membership_account_id
(
    account_id
)
references tbl_accounts
(
    account_id
);

create table tbl_templates
(
    template_id int not null auto_increment primary key,
    project_id int not null,
    template_name varchar (50) not null,
    template_prefix varchar (3) not null,
    critical_age int null,
    frozen_time int null,
    description varchar (100) null,
    is_locked int not null,
    guest_access int not null,
    registered_perm int not null,
    author_perm int not null,
    responsible_perm int not null
);

alter table tbl_templates add constraint unique ix_templates_name
(
    project_id,
    template_name
);

alter table tbl_templates add constraint unique ix_templates_prefix
(
    project_id,
    template_prefix
);

alter table tbl_templates add constraint foreign key fk_templates_project_id
(
    project_id
)
references tbl_projects
(
    project_id
);

create table tbl_group_perms
(
    group_id int not null,
    template_id int not null,
    perms int not null
);

alter table tbl_group_perms add primary key
(
    group_id,
    template_id
);

alter table tbl_group_perms add constraint foreign key fk_group_perms_group_id
(
    group_id
)
references tbl_groups
(
    group_id
);

alter table tbl_group_perms add constraint foreign key fk_group_perms_template_id
(
    template_id
)
references tbl_templates
(
    template_id
);

create table tbl_states
(
    state_id int not null auto_increment primary key,
    template_id int not null,
    state_name varchar (50) not null,
    state_abbr varchar (50) not null,
    state_type int not null,
    next_state_id int null,
    responsible int not null
);

alter table tbl_states add constraint unique ix_states_name
(
    template_id,
    state_name
);

alter table tbl_states add constraint unique ix_states_abbr
(
    template_id,
    state_abbr
);

alter table tbl_states add constraint foreign key fk_states_template_id
(
    template_id
)
references tbl_templates
(
    template_id
);

alter table tbl_states add constraint foreign key fk_states_next_state_id
(
    next_state_id
)
references tbl_states
(
    state_id
);

create table tbl_state_assignees
(
    state_id int not null,
    group_id int not null
);

alter table tbl_state_assignees add primary key
(
    state_id,
    group_id
);

alter table tbl_state_assignees add constraint foreign key fk_state_assignees_state_id
(
    state_id
)
references tbl_states
(
    state_id
);

alter table tbl_state_assignees add constraint foreign key fk_state_assignees_group_id
(
    group_id
)
references tbl_groups
(
    group_id
);

create table tbl_group_trans
(
    state_id_from int not null,
    state_id_to int not null,
    group_id int not null
);

alter table tbl_group_trans add primary key
(
    state_id_from,
    state_id_to,
    group_id
);

alter table tbl_group_trans add constraint foreign key fk_group_trans_state_id_from
(
    state_id_from
)
references tbl_states
(
    state_id
);

alter table tbl_group_trans add constraint foreign key fk_group_trans_state_id_to
(
    state_id_to
)
references tbl_states
(
    state_id
);

alter table tbl_group_trans add constraint foreign key fk_group_trans_group_id
(
    group_id
)
references tbl_groups
(
    group_id
);

create table tbl_role_trans
(
    state_id_from int not null,
    state_id_to int not null,
    role int not null
);

alter table tbl_role_trans add primary key
(
    state_id_from,
    state_id_to,
    role
);

alter table tbl_role_trans add constraint foreign key fk_role_trans_state_id_from
(
    state_id_from
)
references tbl_states
(
    state_id
);

alter table tbl_role_trans add constraint foreign key fk_role_trans_state_id_to
(
    state_id_to
)
references tbl_states
(
    state_id
);

create table tbl_fields
(
    field_id int not null auto_increment primary key,
    template_id int not null,
    state_id int null,
    field_name varchar (50) not null,
    removal_time int not null,
    field_order int not null,
    field_type int not null,
    is_required int not null,
    guest_access int not null,
    registered_perm int not null,
    author_perm int not null,
    responsible_perm int not null,
    add_separator int not null,
    show_in_emails int not null,
    description varchar (1000) null,
    regex_check varchar (500) null,
    regex_search varchar (500) null,
    regex_replace varchar (500) null,
    param1 int null,
    param2 int null,
    value_id int null
);

alter table tbl_fields add constraint unique ix_fields_name
(
    state_id,
    field_name,
    removal_time
);

alter table tbl_fields add constraint unique ix_fields_order
(
    state_id,
    field_order,
    removal_time
);

alter table tbl_fields add constraint foreign key fk_fields_template_id
(
    template_id
)
references tbl_templates
(
    template_id
);

alter table tbl_fields add constraint foreign key fk_fields_state_id
(
    state_id
)
references tbl_states
(
    state_id
);

create table tbl_field_perms
(
    field_id int not null,
    group_id int not null,
    perms int not null
);

alter table tbl_field_perms add primary key
(
    field_id,
    group_id,
    perms
);

alter table tbl_field_perms add constraint foreign key fk_field_perms_field_id
(
    field_id
)
references tbl_fields
(
    field_id
);

alter table tbl_field_perms add constraint foreign key fk_field_perms_group_id
(
    group_id
)
references tbl_groups
(
    group_id
);

create table tbl_records
(
    record_id int not null auto_increment primary key,
    state_id int not null,
    subject varchar (250) not null,
    responsible_id int null,
    creator_id int not null,
    creation_time int not null,
    change_time int not null,
    closure_time int null,
    postpone_time int not null
);

alter table tbl_records add constraint unique ix_records
(
    creator_id,
    creation_time
);

alter table tbl_records add constraint foreign key fk_records_state_id
(
    state_id
)
references tbl_states
(
    state_id
);

alter table tbl_records add constraint foreign key fk_records_responsible_id
(
    responsible_id
)
references tbl_accounts
(
    account_id
);

alter table tbl_records add constraint foreign key fk_records_creator_id
(
    creator_id
)
references tbl_accounts
(
    account_id
);

create table tbl_children
(
    parent_id int not null,
    child_id int not null,
    is_dependency int not null
);

alter table tbl_children add primary key
(
    parent_id,
    child_id
);

alter table tbl_children add constraint foreign key fk_children_record_id
(
    parent_id
)
references tbl_records
(
    record_id
);

alter table tbl_children add constraint foreign key fk_children_dependency_id
(
    child_id
)
references tbl_records
(
    record_id
);

create table tbl_reads
(
    record_id int not null,
    account_id int not null,
    read_time int not null
);

alter table tbl_reads add primary key
(
    record_id,
    account_id
);

alter table tbl_reads add constraint foreign key fk_reads_record_id
(
    record_id
)
references tbl_records
(
    record_id
);

alter table tbl_reads add constraint foreign key fk_reads_account_id
(
    account_id
)
references tbl_accounts
(
    account_id
);

create index ix_rds_comb on tbl_reads (record_id, account_id, read_time);

create table tbl_record_subscribes
(
    record_id int not null,
    account_id int not null,
    subscribed_by int not null
);

alter table tbl_record_subscribes add primary key
(
    record_id,
    account_id,
    subscribed_by
);

alter table tbl_record_subscribes add constraint foreign key fk_recsubscribes_record_id
(
    record_id
)
references tbl_records
(
    record_id
);

alter table tbl_record_subscribes add constraint foreign key fk_recsubscribes_account_id
(
    account_id
)
references tbl_accounts
(
    account_id
);

alter table tbl_record_subscribes add constraint foreign key fk_recsubscribes_subscribed_by
(
    subscribed_by
)
references tbl_accounts
(
    account_id
);

create table tbl_events
(
    event_id int not null auto_increment primary key,
    record_id int not null,
    originator_id int not null,
    event_type int not null,
    event_time int not null,
    event_param int null
);

alter table tbl_events add constraint unique ix_events
(
    record_id,
    originator_id,
    event_type,
    event_time,
    event_param
);

alter table tbl_events add constraint foreign key fk_events_record_id
(
    record_id
)
references tbl_records
(
    record_id
);

alter table tbl_events add constraint foreign key fk_events_originator_id
(
    originator_id
)
references tbl_accounts
(
    account_id
);

create index ix_record on tbl_events (record_id);

create index ix_evts_comb on tbl_events (event_id, record_id);

create table tbl_field_values
(
    event_id int not null,
    field_id int not null,
    field_type int not null,
    value_id int null,
    is_latest int not null
);

alter table tbl_field_values add primary key
(
    event_id,
    field_id
);

alter table tbl_field_values add constraint foreign key fk_field_values_event_id
(
    event_id
)
references tbl_events
(
    event_id
);

alter table tbl_field_values add constraint foreign key fk_field_values_field_id
(
    field_id
)
references tbl_fields
(
    field_id
);

create index ix_value on tbl_field_values (value_id);

create index ix_fva_comb on tbl_field_values (value_id, field_type, is_latest, event_id);

create index ix_fva_comb2 on tbl_field_values (field_id, value_id, is_latest, event_id);

create table tbl_changes
(
    change_id int not null auto_increment primary key,
    event_id int not null,
    field_id int null,
    old_value_id int null,
    new_value_id int null
);

alter table tbl_changes add constraint unique ix_changes
(
    event_id,
    field_id
);

alter table tbl_changes add constraint foreign key fk_changes_event_id
(
    event_id
)
references tbl_events
(
    event_id
);

alter table tbl_changes add constraint foreign key fk_changes_field_id
(
    field_id
)
references tbl_fields
(
    field_id
);

create table tbl_float_values
(
    value_id int not null auto_increment primary key,
    float_value decimal (20,10) not null
);

alter table tbl_float_values add constraint unique ix_float_values
(
    float_value
);

create table tbl_string_values
(
    value_id int not null auto_increment primary key,
    value_token varchar (32) not null,
    string_value varchar (250) not null
);

alter table tbl_string_values add constraint unique ix_string_values
(
    value_token
);

create index ix_svl_id_val on tbl_string_values (value_id, string_value);

create table tbl_text_values
(
    value_id int not null auto_increment primary key,
    value_token varchar (32) not null,
    text_value text not null
);

alter table tbl_text_values add constraint unique ix_text_values
(
    value_token
);

create table tbl_list_values
(
    field_id int not null,
    int_value int not null,
    str_value varchar (50) not null
);

alter table tbl_list_values add primary key
(
    field_id,
    int_value
);

alter table tbl_list_values add constraint unique ix_list_values
(
    field_id,
    str_value
);

alter table tbl_list_values add constraint foreign key fk_list_values_field_id
(
    field_id
)
references tbl_fields
(
    field_id
);

create index ix_lvl_id_val on tbl_list_values (field_id, int_value, str_value);

create table tbl_comments
(
    comment_id int not null auto_increment primary key,
    comment_body text not null,
    event_id int not null,
    is_confidential int not null
);

alter table tbl_comments add constraint unique ix_comments
(
    event_id
);

alter table tbl_comments add constraint foreign key fk_comments_event_id
(
    event_id
)
references tbl_events
(
    event_id
);

create table tbl_attachments
(
    attachment_id int not null auto_increment primary key,
    attachment_name varchar (100) not null,
    attachment_type varchar (100) not null,
    attachment_size int not null,
    event_id int not null,
    is_removed int not null
);

alter table tbl_attachments add constraint unique ix_attachments
(
    event_id
);

alter table tbl_attachments add constraint foreign key fk_attachments_event_id
(
    event_id
)
references tbl_events
(
    event_id
);

create table tbl_filters
(
    filter_id int not null auto_increment primary key,
    account_id int not null,
    filter_name varchar (50) not null,
    filter_type int not null,
    filter_flags int not null,
    filter_param int null
);

alter table tbl_filters add constraint unique ix_filters
(
    account_id,
    filter_name
);

alter table tbl_filters add constraint foreign key fk_filters_account_id
(
    account_id
)
references tbl_accounts
(
    account_id
);

create table tbl_filter_sharing
(
    filter_id int not null,
    group_id int not null
);

alter table tbl_filter_sharing add primary key
(
    filter_id,
    group_id
);

alter table tbl_filter_sharing add constraint foreign key fk_filter_sharing_filter_id
(
    filter_id
)
references tbl_filters
(
    filter_id
);

alter table tbl_filter_sharing add constraint foreign key fk_filter_sharing_group_id
(
    group_id
)
references tbl_groups
(
    group_id
);

create table tbl_filter_activation
(
    filter_id int not null,
    account_id int not null
);

alter table tbl_filter_activation add primary key
(
    filter_id,
    account_id
);

alter table tbl_filter_activation add constraint foreign key fk_filter_activation_filter
(
    filter_id
)
references tbl_filters
(
    filter_id
);

alter table tbl_filter_activation add constraint foreign key fk_filter_activation_account
(
    account_id
)
references tbl_accounts
(
    account_id
);

create table tbl_filter_accounts
(
    filter_id int not null,
    filter_flag int not null,
    account_id int not null
);

alter table tbl_filter_accounts add primary key
(
    filter_id,
    filter_flag,
    account_id
);

alter table tbl_filter_accounts add constraint foreign key fk_filter_accounts_filter_id
(
    filter_id
)
references tbl_filters
(
    filter_id
);

alter table tbl_filter_accounts add constraint foreign key fk_filter_accounts_account_id
(
    account_id
)
references tbl_accounts
(
    account_id
);

create table tbl_filter_states
(
    filter_id int not null,
    state_id int not null
);

alter table tbl_filter_states add primary key
(
    filter_id,
    state_id
);

alter table tbl_filter_states add constraint foreign key fk_filter_states_filter_id
(
    filter_id
)
references tbl_filters
(
    filter_id
);

alter table tbl_filter_states add constraint foreign key fk_filter_states_state_id
(
    state_id
)
references tbl_states
(
    state_id
);

create table tbl_filter_trans
(
    filter_id int not null,
    state_id int not null,
    date1 int not null,
    date2 int not null
);

alter table tbl_filter_trans add primary key
(
    filter_id,
    state_id
);

alter table tbl_filter_trans add constraint foreign key fk_filter_trans_filter_id
(
    filter_id
)
references tbl_filters
(
    filter_id
);

alter table tbl_filter_trans add constraint foreign key fk_filter_trans_state_id
(
    state_id
)
references tbl_states
(
    state_id
);

create table tbl_filter_fields
(
    filter_id int not null,
    field_id int not null,
    param1 int null,
    param2 int null
);

alter table tbl_filter_fields add primary key
(
    filter_id,
    field_id
);

alter table tbl_filter_fields add constraint foreign key fk_filter_fields_filter_id
(
    filter_id
)
references tbl_filters
(
    filter_id
);

alter table tbl_filter_fields add constraint foreign key fk_filter_fields_field_id
(
    field_id
)
references tbl_fields
(
    field_id
);

create table tbl_views
(
    view_id int not null auto_increment primary key,
    account_id int not null,
    view_name varchar (50) not null
);

alter table tbl_views add constraint unique ix_views
(
    account_id,
    view_name
);

alter table tbl_views add constraint foreign key fk_views_account_id
(
    account_id
)
references tbl_accounts
(
    account_id
);

create table tbl_view_columns
(
    column_id int not null auto_increment primary key,
    view_id int not null,
    state_name varchar (50) null,
    field_name varchar (50) null,
    column_type int not null,
    column_order int not null
);

alter table tbl_view_columns add constraint unique ix_view_columns_name
(
    view_id,
    state_name,
    field_name,
    column_type
);

alter table tbl_view_columns add constraint unique ix_view_columns_order
(
    view_id,
    column_order
);

alter table tbl_view_columns add constraint foreign key fk_view_columns_view_id
(
    view_id
)
references tbl_views
(
    view_id
);

create table tbl_view_filters
(
    view_id int not null,
    filter_id int not null
);

alter table tbl_view_filters add primary key
(
    view_id,
    filter_id
);

alter table tbl_view_filters add constraint foreign key fk_view_filters_view_id
(
    view_id
)
references tbl_views
(
    view_id
);

alter table tbl_view_filters add constraint foreign key fk_view_filters_filter_id
(
    filter_id
)
references tbl_filters
(
    filter_id
);

create table tbl_subscribes
(
    subscribe_id int not null auto_increment primary key,
    account_id int not null,
    subscribe_name varchar (25) not null,
    carbon_copy varchar (50) null,
    subscribe_type int not null,
    subscribe_flags int not null,
    subscribe_param int null,
    is_activated int not null
);

alter table tbl_subscribes add constraint unique ix_subscribes
(
    account_id,
    subscribe_name
);

alter table tbl_subscribes add constraint foreign key fk_subscribes_account_id
(
    account_id
)
references tbl_accounts
(
    account_id
);

create table tbl_reminders
(
    reminder_id int not null auto_increment primary key,
    account_id int not null,
    reminder_name varchar (25) not null,
    subject_text varchar (100) null,
    state_id int not null,
    group_id int null,
    group_flag int not null
);

alter table tbl_reminders add constraint unique ix_reminders
(
    account_id,
    reminder_name
);

alter table tbl_reminders add constraint foreign key fk_reminders_account_id
(
    account_id
)
references tbl_accounts
(
    account_id
);

alter table tbl_reminders add constraint foreign key fk_reminders_state_id
(
    state_id
)
references tbl_states
(
    state_id
);

alter table tbl_reminders add constraint foreign key fk_reminders_group_id
(
    group_id
)
references tbl_groups
(
    group_id
);

insert into tbl_sys_vars (var_name, var_value)
values ('DATABASE_TYPE', 'MySQL 5.0');

insert into tbl_sys_vars (var_name, var_value)
values ('FEATURE_LEVEL', '3.9');

insert into tbl_accounts
(
    username,
    fullname,
    email,
    passwd,
    description,
    auth_token,
    token_expire,
    passwd_expire,
    is_admin,
    is_disabled,
    is_ldapuser,
    locks_count,
    lock_time,
    locale,
    timezone,
    text_rows,
    page_rows,
    page_bkms,
    auto_refresh,
    csv_delim,
    csv_encoding,
    csv_line_ends,
    view_id,
    theme_name
)
values
(
    'root@eTraxis',
    'Built-in administrator',
    'root@example.com',
    'd41d8cd98f00b204e9800998ecf8427e',
    'Built-in administrator',
    NULL, 0, 0, 1, 0, 0, 0, 0, 1000, 0, 8, 20, 10, 0, 44, 1, 1, NULL, 'Emerald'
);
