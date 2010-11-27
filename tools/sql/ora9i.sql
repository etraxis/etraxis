/*----------------------------------------------------------------------------*/
/*                                                                            */
/*  eTraxis - Records tracking web-based system                               */
/*  Copyright (C) 2005-2010  Artem Rodygin                                    */
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
/*  Server type: Oracle 9i                                                    */
/*----------------------------------------------------------------------------*/

connect etraxis/password@database;

create table tbl_sys_vars
(
    var_name varchar2 (32) not null,
    var_value varchar2 (100) null
);

alter table tbl_sys_vars add constraint ix_sys_vars unique
(
    var_name
);

create table tbl_accounts
(
    account_id number (10) not null,
    username varchar2 (112) not null,
    fullname nvarchar2 (64) not null,
    email varchar2 (50) not null,
    passwd char (32) not null,
    description nvarchar2 (100) null,
    auth_token char (32) null,
    token_expire number (10) not null,
    passwd_expire number (10) not null,
    is_admin number (10) not null,
    is_disabled number (10) not null,
    is_ldapuser number (10) not null,
    locks_count number (10) not null,
    lock_time number (10) not null,
    locale number (10) not null,
    page_rows number (10) not null,
    page_bkms number (10) not null,
    csv_delim number (10) not null,
    csv_encoding number (10) not null,
    csv_line_ends number (10) not null,
    view_id number (10) null,
    theme_name varchar2 (50) not null
);

alter table tbl_accounts add constraint pk_accounts primary key
(
    account_id
);

alter table tbl_accounts add constraint ix_accounts unique
(
    username
);

create sequence seq_accounts;

create or replace trigger tgi_accounts before insert on tbl_accounts for each row
begin
    select seq_accounts.nextval into :new.account_id from dual;
end;
/

create table tbl_projects
(
    project_id number (10) not null,
    project_name nvarchar2 (25) not null,
    start_time number (10) not null,
    description nvarchar2 (100) null,
    is_suspended number (10) not null
);

alter table tbl_projects add constraint pk_projects primary key
(
    project_id
);

alter table tbl_projects add constraint ix_projects unique
(
    project_name
);

create sequence seq_projects;

create or replace trigger tgi_projects before insert on tbl_projects for each row
begin
    select seq_projects.nextval into :new.project_id from dual;
end;
/

create table tbl_groups
(
    group_id number (10) not null,
    project_id number (10) null,
    group_name nvarchar2 (25) not null,
    description nvarchar2 (100) null
);

alter table tbl_groups add constraint pk_groups primary key
(
    group_id
);

alter table tbl_groups add constraint ix_groups unique
(
    project_id,
    group_name
);

alter table tbl_groups add constraint fk_groups_project_id foreign key
(
    project_id
)
references tbl_projects
(
    project_id
);

create sequence seq_groups;

create or replace trigger tgi_groups before insert on tbl_groups for each row
begin
    select seq_groups.nextval into :new.group_id from dual;
end;
/

create table tbl_membership
(
    group_id number (10) not null,
    account_id number (10) not null
);

alter table tbl_membership add constraint pk_membership primary key
(
    group_id,
    account_id
);

alter table tbl_membership add constraint fk_membership_group_id foreign key
(
    group_id
)
references tbl_groups
(
    group_id
);

alter table tbl_membership add constraint fk_membership_account_id foreign key
(
    account_id
)
references tbl_accounts
(
    account_id
);

create table tbl_templates
(
    template_id number (10) not null,
    project_id number (10) not null,
    template_name nvarchar2 (50) not null,
    template_prefix nvarchar2 (3) not null,
    critical_age number (10) null,
    frozen_time number (10) null,
    description nvarchar2 (100) null,
    is_locked number (10) not null,
    guest_access number (10) not null,
    registered_perm number (10) not null,
    author_perm number (10) not null,
    responsible_perm number (10) not null
);

alter table tbl_templates add constraint pk_templates primary key
(
    template_id
);

alter table tbl_templates add constraint ix_templates_name unique
(
    project_id,
    template_name
);

alter table tbl_templates add constraint ix_templates_prefix unique
(
    project_id,
    template_prefix
);

alter table tbl_templates add constraint fk_templates_project_id foreign key
(
    project_id
)
references tbl_projects
(
    project_id
);

create sequence seq_templates;

create or replace trigger tgi_templates before insert on tbl_templates for each row
begin
    select seq_templates.nextval into :new.template_id from dual;
end;
/

create table tbl_group_perms
(
    group_id number (10) not null,
    template_id number (10) not null,
    perms number (10) not null
);

alter table tbl_group_perms add constraint pk_group_perms primary key
(
    group_id,
    template_id
);

alter table tbl_group_perms add constraint fk_group_perms_group_id foreign key
(
    group_id
)
references tbl_groups
(
    group_id
);

alter table tbl_group_perms add constraint fk_group_perms_template_id foreign key
(
    template_id
)
references tbl_templates
(
    template_id
);

create table tbl_states
(
    state_id number (10) not null,
    template_id number (10) not null,
    state_name nvarchar2 (50) not null,
    state_abbr nvarchar2 (50) not null,
    state_type number (10) not null,
    next_state_id number (10) null,
    responsible number (10) not null
);

alter table tbl_states add constraint pk_states primary key
(
    state_id
);

alter table tbl_states add constraint ix_states_name unique
(
    template_id,
    state_name
);

alter table tbl_states add constraint ix_states_abbr unique
(
    template_id,
    state_abbr
);

alter table tbl_states add constraint fk_states_template_id foreign key
(
    template_id
)
references tbl_templates
(
    template_id
);

create sequence seq_states;

create or replace trigger tgi_states before insert on tbl_states for each row
begin
    select seq_states.nextval into :new.state_id from dual;
end;
/

create table tbl_group_trans
(
    state_id_from number (10) not null,
    state_id_to number (10) not null,
    group_id number (10) not null
);

alter table tbl_group_trans add constraint pk_group_trans primary key
(
    state_id_from,
    state_id_to,
    group_id
);

alter table tbl_group_trans add constraint fk_group_trans_state_id_from foreign key
(
    state_id_from
)
references tbl_states
(
    state_id
);

alter table tbl_group_trans add constraint fk_group_trans_state_id_to foreign key
(
    state_id_to
)
references tbl_states
(
    state_id
);

alter table tbl_group_trans add constraint fk_group_trans_group_id foreign key
(
    group_id
)
references tbl_groups
(
    group_id
);

create table tbl_role_trans
(
    state_id_from number (10) not null,
    state_id_to number (10) not null,
    role number (10) not null
);

alter table tbl_role_trans add constraint pk_role_trans primary key
(
    state_id_from,
    state_id_to,
    role
);

alter table tbl_role_trans add constraint fk_role_trans_state_id_from foreign key
(
    state_id_from
)
references tbl_states
(
    state_id
);

alter table tbl_role_trans add constraint fk_role_trans_state_id_to foreign key
(
    state_id_to
)
references tbl_states
(
    state_id
);

create table tbl_fields
(
    field_id number (10) not null,
    state_id number (10) not null,
    field_name nvarchar2 (50) not null,
    removal_time number (10) not null,
    field_order number (10) not null,
    field_type number (10) not null,
    is_required number (10) not null,
    guest_access number (10) not null,
    registered_perm number (10) not null,
    author_perm number (10) not null,
    responsible_perm number (10) not null,
    add_separator number (10) not null,
    description nvarchar2 (1000) null,
    regex_check nvarchar2 (1000) null,
    regex_search nvarchar2 (1000) null,
    regex_replace nvarchar2 (1000) null,
    param1 number (10) null,
    param2 number (10) null,
    value_id number (10) null
);

alter table tbl_fields add constraint pk_fields primary key
(
    field_id
);

alter table tbl_fields add constraint ix_fields_name unique
(
    state_id,
    field_name,
    removal_time
);

alter table tbl_fields add constraint ix_fields_order unique
(
    state_id,
    field_order,
    removal_time
);

alter table tbl_fields add constraint fk_fields_state_id foreign key
(
    state_id
)
references tbl_states
(
    state_id
);

create sequence seq_fields;

create or replace trigger tgi_fields before insert on tbl_fields for each row
begin
    select seq_fields.nextval into :new.field_id from dual;
end;
/

create table tbl_field_perms
(
    field_id number (10) not null,
    group_id number (10) not null,
    perms number (10) not null
);

alter table tbl_field_perms add constraint pk_field_perms primary key
(
    field_id,
    group_id,
    perms
);

alter table tbl_field_perms add constraint fk_field_perms_field_id foreign key
(
    field_id
)
references tbl_fields
(
    field_id
);

alter table tbl_field_perms add constraint fk_field_perms_group_id foreign key
(
    group_id
)
references tbl_groups
(
    group_id
);

create table tbl_records
(
    record_id number (10) not null,
    state_id number (10) not null,
    subject nvarchar2 (250) not null,
    responsible_id number (10) null,
    creator_id number (10) not null,
    creation_time number (10) not null,
    change_time number (10) not null,
    closure_time number (10) null,
    postpone_time number (10) not null
);

alter table tbl_records add constraint pk_records primary key
(
    record_id
);

alter table tbl_records add constraint ix_records unique
(
    creator_id,
    creation_time
);

alter table tbl_records add constraint fk_records_state_id foreign key
(
    state_id
)
references tbl_states
(
    state_id
);

alter table tbl_records add constraint fk_records_responsible_id foreign key
(
    responsible_id
)
references tbl_accounts
(
    account_id
);

alter table tbl_records add constraint fk_records_creator_id foreign key
(
    creator_id
)
references tbl_accounts
(
    account_id
);

create sequence seq_records;

create or replace trigger tgi_records before insert on tbl_records for each row
begin
    select seq_records.nextval into :new.record_id from dual;
end;
/

create table tbl_children
(
    parent_id number (10) not null,
    child_id number (10) not null,
    is_dependency number (10) not null
);

alter table tbl_children add constraint pk_children primary key
(
    parent_id,
    child_id
);

alter table tbl_children add constraint fk_children_record_id foreign key
(
    parent_id
)
references tbl_records
(
    record_id
);

alter table tbl_children add constraint fk_children_dependency_id foreign key
(
    child_id
)
references tbl_records
(
    record_id
);

create table tbl_reads
(
    record_id number (10) not null,
    account_id number (10) not null,
    read_time number (10) not null
);

alter table tbl_reads add constraint pk_reads primary key
(
    record_id,
    account_id
);

alter table tbl_reads add constraint fk_reads_record_id foreign key
(
    record_id
)
references tbl_records
(
    record_id
);

alter table tbl_reads add constraint fk_reads_account_id foreign key
(
    account_id
)
references tbl_accounts
(
    account_id
);

create table tbl_record_subscribes
(
    record_id number (10) not null,
    account_id number (10) not null,
    subscribed_by number (10) not null
);

alter table tbl_record_subscribes add constraint pk_record_subscribes primary key
(
    record_id,
    account_id,
    subscribed_by
);

alter table tbl_record_subscribes add constraint fk_recsubscribes_record_id foreign key
(
    record_id
)
references tbl_records
(
    record_id
);

alter table tbl_record_subscribes add constraint fk_recsubscribes_account_id foreign key
(
    account_id
)
references tbl_accounts
(
    account_id
);

alter table tbl_record_subscribes add constraint fk_recsubscribes_subscribed_by foreign key
(
    subscribed_by
)
references tbl_accounts
(
    account_id
);

create table tbl_events
(
    event_id number (10) not null,
    record_id number (10) not null,
    originator_id number (10) not null,
    event_type number (10) not null,
    event_time number (10) not null,
    event_param number (10) null
);

alter table tbl_events add constraint pk_events primary key
(
    event_id
);

alter table tbl_events add constraint ix_events unique
(
    record_id,
    originator_id,
    event_type,
    event_time
);

alter table tbl_events add constraint fk_events_record_id foreign key
(
    record_id
)
references tbl_records
(
    record_id
);

alter table tbl_events add constraint fk_events_originator_id foreign key
(
    originator_id
)
references tbl_accounts
(
    account_id
);

create sequence seq_events;

create or replace trigger tgi_events before insert on tbl_events for each row
begin
    select seq_events.nextval into :new.event_id from dual;
end;
/

create index ix_record on tbl_events (record_id);

create table tbl_field_values
(
    event_id number (10) not null,
    field_id number (10) not null,
    field_type number (10) not null,
    value_id number (10) null,
    is_latest number (10) not null
);

alter table tbl_field_values add constraint pk_field_values primary key
(
    event_id,
    field_id
);

alter table tbl_field_values add constraint fk_field_values_event_id foreign key
(
    event_id
)
references tbl_events
(
    event_id
);

alter table tbl_field_values add constraint fk_field_values_field_id foreign key
(
    field_id
)
references tbl_fields
(
    field_id
);

create index ix_value on tbl_field_values (value_id);

create table tbl_changes
(
    event_id number (10) not null,
    field_id number (10) null,
    old_value_id number (10) null,
    new_value_id number (10) null
);

alter table tbl_changes add constraint ix_changes unique
(
    event_id,
    field_id
);

alter table tbl_changes add constraint fk_changes_event_id foreign key
(
    event_id
)
references tbl_events
(
    event_id
);

alter table tbl_changes add constraint fk_changes_field_id foreign key
(
    field_id
)
references tbl_fields
(
    field_id
);

create table tbl_string_values
(
    value_id number (10) not null,
    value_token char (32) not null,
    string_value nvarchar2 (250) not null
);

alter table tbl_string_values add constraint pk_string_values primary key
(
    value_id
);

alter table tbl_string_values add constraint ix_string_values unique
(
    value_token
);

create sequence seq_string_values;

create or replace trigger tgi_string_values before insert on tbl_string_values for each row
begin
    select seq_string_values.nextval into :new.value_id from dual;
end;
/

create table tbl_text_values
(
    value_id number (10) not null,
    value_token char (32) not null,
    text_value clob not null
);

alter table tbl_text_values add constraint pk_text_value primary key
(
    value_id
);

alter table tbl_text_values add constraint ix_text_values unique
(
    value_token
);

create sequence seq_text_values;

create or replace trigger tgi_text_values before insert on tbl_text_values for each row
begin
    select seq_text_values.nextval into :new.value_id from dual;
end;
/

create table tbl_list_values
(
    field_id number (10) not null,
    int_value number (10) not null,
    str_value nvarchar2 (50) not null
);

alter table tbl_list_values add constraint pk_list_value primary key
(
    field_id,
    int_value
);

alter table tbl_list_values add constraint ix_list_values unique
(
    field_id,
    str_value
);

alter table tbl_list_values add constraint fk_list_values_field_id foreign key
(
    field_id
)
references tbl_fields
(
    field_id
);

create table tbl_comments
(
    comment_id number (10) not null,
    comment_body clob not null,
    event_id number (10) not null,
    is_confidential number (10) not null
);

alter table tbl_comments add constraint pk_comments primary key
(
    comment_id
);

alter table tbl_comments add constraint ix_comments unique
(
    event_id
);

alter table tbl_comments add constraint fk_comments_event_id foreign key
(
    event_id
)
references tbl_events
(
    event_id
);

create sequence seq_comments;

create or replace trigger tgi_comments before insert on tbl_comments for each row
begin
    select seq_comments.nextval into :new.comment_id from dual;
end;
/

create table tbl_attachments
(
    attachment_id number (10) not null,
    attachment_name nvarchar2 (100) not null,
    attachment_type varchar2 (100) not null,
    attachment_size number (10) not null,
    event_id number (10) not null,
    is_removed number (10) not null
);

alter table tbl_attachments add constraint pk_attachments primary key
(
    attachment_id
);

alter table tbl_attachments add constraint ix_attachments unique
(
    event_id
);

alter table tbl_attachments add constraint fk_attachments_event_id foreign key
(
    event_id
)
references tbl_events
(
    event_id
);

create sequence seq_attachments;

create or replace trigger tgi_attachments before insert on tbl_attachments for each row
begin
    select seq_attachments.nextval into :new.attachment_id from dual;
end;
/

create table tbl_filters
(
    filter_id number (10) not null,
    account_id number (10) not null,
    filter_name nvarchar2 (50) not null,
    filter_type number (10) not null,
    filter_flags number (10) not null,
    filter_param number (10) null
);

alter table tbl_filters add constraint pk_filters primary key
(
    filter_id
);

alter table tbl_filters add constraint ix_filters unique
(
    account_id,
    filter_name
);

alter table tbl_filters add constraint fk_filters_account_id foreign key
(
    account_id
)
references tbl_accounts
(
    account_id
);

create sequence seq_filters;

create or replace trigger tgi_filters before insert on tbl_filters for each row
begin
    select seq_filters.nextval into :new.filter_id from dual;
end;
/

create table tbl_filter_sharing
(
    filter_id number (10) not null,
    group_id number (10) not null
);

alter table tbl_filter_sharing add constraint pk_filter_sharing primary key
(
    filter_id,
    group_id
);

alter table tbl_filter_sharing add constraint fk_filter_sharing_filter_id foreign key
(
    filter_id
)
references tbl_filters
(
    filter_id
);

alter table tbl_filter_sharing add constraint fk_filter_sharing_group_id foreign key
(
    group_id
)
references tbl_groups
(
    group_id
);

create table tbl_filter_activation
(
    filter_id number (10) not null,
    account_id number (10) not null
);

alter table tbl_filter_activation add constraint pk_filter_activation primary key
(
    filter_id,
    account_id
);

alter table tbl_filter_activation add constraint fk_filter_activation_filter foreign key
(
    filter_id
)
references tbl_filters
(
    filter_id
);

alter table tbl_filter_activation add constraint fk_filter_activation_account foreign key
(
    account_id
)
references tbl_accounts
(
    account_id
);

create table tbl_filter_accounts
(
    filter_id number (10) not null,
    filter_flag number (10) not null,
    account_id number (10) not null
);

alter table tbl_filter_accounts add constraint pk_filter_accounts primary key
(
    filter_id,
    filter_flag,
    account_id
);

alter table tbl_filter_accounts add constraint fk_filter_accounts_filter_id foreign key
(
    filter_id
)
references tbl_filters
(
    filter_id
);

alter table tbl_filter_accounts add constraint fk_filter_accounts_account_id foreign key
(
    account_id
)
references tbl_accounts
(
    account_id
);

create table tbl_filter_states
(
    filter_id number (10) not null,
    state_id number (10) not null
);

alter table tbl_filter_states add constraint pk_filter_states primary key
(
    filter_id,
    state_id
);

alter table tbl_filter_states add constraint fk_filter_states_filter_id foreign key
(
    filter_id
)
references tbl_filters
(
    filter_id
);

alter table tbl_filter_states add constraint fk_filter_states_state_id foreign key
(
    state_id
)
references tbl_states
(
    state_id
);

create table tbl_filter_trans
(
    filter_id number (10) not null,
    state_id number (10) not null,
    date1 number (10) not null,
    date2 number (10) not null
);

alter table tbl_filter_trans add constraint pk_filter_trans primary key
(
    filter_id,
    state_id
);

alter table tbl_filter_trans add constraint fk_filter_trans_filter_id foreign key
(
    filter_id
)
references tbl_filters
(
    filter_id
);

alter table tbl_filter_trans add constraint fk_filter_trans_state_id foreign key
(
    state_id
)
references tbl_states
(
    state_id
);

create table tbl_filter_fields
(
    filter_id number (10) not null,
    field_id number (10) not null,
    param1 number (10) null,
    param2 number (10) null
);

alter table tbl_filter_fields add constraint pk_filter_fields primary key
(
    filter_id,
    field_id
);

alter table tbl_filter_fields add constraint fk_filter_fields_filter_id foreign key
(
    filter_id
)
references tbl_filters
(
    filter_id
);

alter table tbl_filter_fields add constraint fk_filter_fields_field_id foreign key
(
    field_id
)
references tbl_fields
(
    field_id
);

create table tbl_views
(
    view_id number (10) not null,
    account_id number (10) not null,
    view_name nvarchar2 (50) not null
);

alter table tbl_views add constraint pk_views primary key
(
    view_id
);

alter table tbl_views add constraint ix_views unique
(
    account_id,
    view_name
);

alter table tbl_views add constraint fk_views_account_id foreign key
(
    account_id
)
references tbl_accounts
(
    account_id
);

create sequence seq_views;

create or replace trigger tgi_views before insert on tbl_views for each row
begin
    select seq_views.nextval into :new.view_id from dual;
end;
/

create table tbl_view_columns
(
    column_id number (10) not null,
    view_id number (10) not null,
    state_name nvarchar2 (50) null,
    field_name nvarchar2 (50) null,
    column_type number (10) not null,
    column_order number (10) not null
);

alter table tbl_view_columns add constraint pk_view_columns primary key
(
    column_id
);

alter table tbl_view_columns add constraint ix_view_columns_name unique
(
    view_id,
    state_name,
    field_name,
    column_type
);

alter table tbl_view_columns add constraint ix_view_columns_order unique
(
    view_id,
    column_order
);

alter table tbl_view_columns add constraint fk_view_columns_view_id foreign key
(
    view_id
)
references tbl_views
(
    view_id
);

create sequence seq_view_columns;

create or replace trigger tgi_view_columns before insert on tbl_view_columns for each row
begin
    select seq_view_columns.nextval into :new.column_id from dual;
end;
/

create table tbl_view_filters
(
    view_id number (10) not null,
    filter_id number (10) not null
);

alter table tbl_view_filters add constraint pk_view_filters primary key
(
    view_id,
    filter_id
);

alter table tbl_view_filters add constraint fk_view_filters_view_id foreign key
(
    view_id
)
references tbl_views
(
    view_id
);

alter table tbl_view_filters add constraint fk_view_filters_filter_id foreign key
(
    filter_id
)
references tbl_filters
(
    filter_id
);

create table tbl_subscribes
(
    subscribe_id number (10) not null,
    account_id number (10) not null,
    subscribe_name nvarchar2 (25) not null,
    carbon_copy varchar2 (50) null,
    subscribe_type number (10) not null,
    subscribe_flags number (10) not null,
    subscribe_param number (10) null,
    is_activated number (10) not null
);

alter table tbl_subscribes add constraint pk_subscribes primary key
(
    subscribe_id
);

alter table tbl_subscribes add constraint ix_subscribes unique
(
    account_id,
    subscribe_name
);

alter table tbl_subscribes add constraint fk_subscribes_account_id foreign key
(
    account_id
)
references tbl_accounts
(
    account_id
);

create sequence seq_subscribes;

create or replace trigger tgi_subscribes before insert on tbl_subscribes for each row
begin
    select seq_subscribes.nextval into :new.subscribe_id from dual;
end;
/

create table tbl_reminders
(
    reminder_id number (10) not null,
    account_id number (10) not null,
    reminder_name nvarchar2 (25) not null,
    subject_text nvarchar2 (100) null,
    state_id number (10) not null,
    group_id number (10) null,
    group_flag number (10) not null
);

alter table tbl_reminders add constraint pk_reminders primary key
(
    reminder_id
);

alter table tbl_reminders add constraint ix_reminders unique
(
    account_id,
    reminder_name
);

alter table tbl_reminders add constraint fk_reminders_account_id foreign key
(
    account_id
)
references tbl_accounts
(
    account_id
);

alter table tbl_reminders add constraint fk_reminders_state_id foreign key
(
    state_id
)
references tbl_states
(
    state_id
);

alter table tbl_reminders add constraint fk_reminders_group_id foreign key
(
    group_id
)
references tbl_groups
(
    group_id
);

create sequence seq_reminders;

create or replace trigger tgi_reminders before insert on tbl_reminders for each row
begin
    select seq_reminders.nextval into :new.reminder_id from dual;
end;
/

insert into tbl_sys_vars (var_name, var_value)
values ('DATABASE_TYPE', 'Oracle 9i');

insert into tbl_sys_vars (var_name, var_value)
values ('FEATURE_LEVEL', '3.2');

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
    page_rows,
    page_bkms,
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
    NULL, 0, 0, 1, 0, 0, 0, 0, 1000, 20, 10, 44, 1, 1, NULL, 'Emerald'
);
