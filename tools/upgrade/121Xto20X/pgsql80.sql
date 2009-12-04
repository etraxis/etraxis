/*------------------------------------------------------------------------------------------------*/
/*                                                                                                */
/*  eTraxis - Records tracking web-based system.                                                  */
/*  Copyright (C) 2004-2009 by Artem Rodygin                                                      */
/*                                                                                                */
/*  This program is free software; you can redistribute it and/or modify                          */
/*  it under the terms of the GNU General Public License as published by                          */
/*  the Free Software Foundation; either version 2 of the License, or                             */
/*  (at your option) any later version.                                                           */
/*                                                                                                */
/*  This program is distributed in the hope that it will be useful,                               */
/*  but WITHOUT ANY WARRANTY; without even the implied warranty of                                */
/*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                                 */
/*  GNU General Public License for more details.                                                  */
/*                                                                                                */
/*  You should have received a copy of the GNU General Public License along                       */
/*  with this program; if not, write to the Free Software Foundation, Inc.,                       */
/*  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.                                   */
/*                                                                                                */
/*------------------------------------------------------------------------------------------------*/
/*  Server type: PostgreSQL 8.0                                                                   */
/*------------------------------------------------------------------------------------------------*/

update tbl_sys_vars
set var_value = '2.0'
where var_name = 'FEATURE_LEVEL';

/*------------------------------------------------------------------------------------------------*/

create or replace function plpgsql_call_handler() returns language_handler as '$libdir/plpgsql' language c;
create or replace function plpgsql_validator(oid) returns void as '$libdir/plpgsql' language c;
create trusted procedural language plpgsql handler plpgsql_call_handler validator plpgsql_validator;

create function etraxis_drop_constraints() returns void as $$
declare
    const_rec record;
begin
    for const_rec in select pg_class.relname, pg_constraint.conname from pg_class, pg_constraint where pg_class.oid = pg_constraint.conrelid and (pg_constraint.contype = 'f' or pg_constraint.contype = 'u') loop
        execute 'alter table ' || quote_ident(const_rec.tblname) || ' drop constraint ' || quote_ident(const_rec.conname);
    end loop;
end;
$$ language plpgsql;

select etraxis_drop_constraints();
drop function etraxis_drop_constraints();

drop language plpgsql;
drop function plpgsql_call_handler();
drop function plpgsql_validator(oid);

/*------------------------------------------------------------------------------------------------*/

alter table tbl_projects   drop column hashed_name;
alter table tbl_groups     drop column hashed_name;
alter table tbl_templates  drop column hashed_name;
alter table tbl_templates  drop column hashed_prefix;
alter table tbl_states     drop column hashed_name;
alter table tbl_states     drop column hashed_abbr;
alter table tbl_fields     drop column hashed_name;
alter table tbl_filters    drop column hashed_name;
alter table tbl_fsets      drop column hashed_name;
alter table tbl_views      drop column hashed_name;
alter table tbl_subscribes drop column hashed_name;
alter table tbl_reminders  drop column hashed_name;

/*------------------------------------------------------------------------------------------------*/

alter table tbl_string_values rename column hashed_value to value_token;

/*------------------------------------------------------------------------------------------------*/

alter table tbl_sys_vars add constraint ix_sys_vars unique
(
    var_name
);

alter table tbl_accounts add constraint ix_accounts unique
(
    username
);

alter table tbl_projects add constraint ix_projects unique
(
    project_name
);

alter table tbl_groups add constraint ix_groups unique
(
    project_id,
    group_name
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

alter table tbl_fields add constraint ix_fields_name unique
(
    state_id,
    field_name
);

alter table tbl_fields add constraint ix_fields_order unique
(
    state_id,
    field_order
);

alter table tbl_records add constraint ix_records unique
(
    creator_id,
    creation_time
);

alter table tbl_events add constraint ix_events unique
(
    record_id,
    originator_id,
    event_type,
    event_time
);

alter table tbl_changes add constraint ix_changes unique
(
    event_id,
    field_id
);

alter table tbl_string_values add constraint ix_string_values unique
(
    value_token
);

alter table tbl_text_values add constraint ix_text_values unique
(
    value_token
);

alter table tbl_list_values add constraint ix_list_values unique
(
    field_id,
    str_value
);

alter table tbl_comments add constraint ix_comments unique
(
    event_id
);

alter table tbl_attachments add constraint ix_attachments unique
(
    event_id
);

alter table tbl_filters add constraint ix_filters unique
(
    account_id,
    filter_name
);

alter table tbl_fsets add constraint ix_fsets unique
(
    account_id,
    fset_name
);

alter table tbl_views add constraint ix_views unique
(
    account_id,
    view_name
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

alter table tbl_def_columns add constraint ix_def_columns_name unique
(
    account_id,
    state_name,
    field_name,
    column_type
);

alter table tbl_def_columns add constraint ix_def_columns_order unique
(
    account_id,
    column_order
);

alter table tbl_subscribes add constraint ix_subscribes unique
(
    account_id,
    subscribe_name
);

alter table tbl_reminders add constraint ix_reminders unique
(
    account_id,
    reminder_name
);

/*------------------------------------------------------------------------------------------------*/

alter table tbl_groups add constraint fk_groups_project_id foreign key
(
    project_id
)
references tbl_projects
(
    project_id
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

alter table tbl_templates add constraint fk_templates_project_id foreign key
(
    project_id
)
references tbl_projects
(
    project_id
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

alter table tbl_states add constraint fk_states_template_id foreign key
(
    template_id
)
references tbl_templates
(
    template_id
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

alter table tbl_fields add constraint fk_fields_state_id foreign key
(
    state_id
)
references tbl_states
(
    state_id
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

alter table tbl_list_values add constraint fk_list_values_field_id foreign key
(
    field_id
)
references tbl_fields
(
    field_id
);

alter table tbl_comments add constraint fk_comments_event_id foreign key
(
    event_id
)
references tbl_events
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

alter table tbl_filters add constraint fk_filters_account_id foreign key
(
    account_id
)
references tbl_accounts
(
    account_id
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

alter table tbl_fsets add constraint fk_fsets_account_id foreign key
(
    account_id
)
references tbl_accounts
(
    account_id
);

alter table tbl_fset_filters add constraint fk_fset_filters_fset_id foreign key
(
    fset_id
)
references tbl_fsets
(
    fset_id
);

alter table tbl_fset_filters add constraint fk_fset_filters_filter_id foreign key
(
    filter_id
)
references tbl_filters
(
    filter_id
);

alter table tbl_views add constraint fk_views_account_id foreign key
(
    account_id
)
references tbl_accounts
(
    account_id
);

alter table tbl_view_columns add constraint fk_view_columns_view_id foreign key
(
    view_id
)
references tbl_views
(
    view_id
);

alter table tbl_def_columns add constraint fk_def_columns_account_id foreign key
(
    account_id
)
references tbl_accounts
(
    account_id
);

alter table tbl_subscribes add constraint fk_subscribes_account_id foreign key
(
    account_id
)
references tbl_accounts
(
    account_id
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

/*------------------------------------------------------------------------------------------------*/
