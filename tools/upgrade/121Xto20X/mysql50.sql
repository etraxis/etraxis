/*------------------------------------------------------------------------------------------------*/
/*                                                                                                */
/*  eTraxis - Records tracking web-based system.                                                  */
/*  Copyright (C) 2009 by Artem Rodygin                                                           */
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
/*  Server type: MySQL 5.0                                                                        */
/*------------------------------------------------------------------------------------------------*/

use etraxis;
set @dbname = 'etraxis';

/*------------------------------------------------------------------------------------------------*/

update tbl_sys_vars
set var_value = 'MySQL 5.0'
where var_name = 'DATABASE_TYPE';

update tbl_sys_vars
set var_value = '2.0'
where var_name = 'FEATURE_LEVEL';

/*------------------------------------------------------------------------------------------------*/

delimiter $$

create procedure drop_indexes()
begin

    declare done int default 0;
    declare cname varchar(64);
    declare tname varchar(64);

    declare curkey cursor for select table_name, constraint_name from information_schema.table_constraints where constraint_type = 'unique' and constraint_schema = @dbname;
    declare continue handler for not found set done = 1;

    open curkey;

    repeat

        fetch curkey into tname, cname;

        if not done then

            set @cmdstr = concat("alter table ", @dbname, ".", tname, " drop index ", cname);

            prepare stmt from @cmdstr;
            execute stmt;
            deallocate prepare stmt;

        end if;

    until done end repeat;

    close curkey;

end
$$

delimiter ;

call drop_indexes;
drop procedure drop_indexes;

/*------------------------------------------------------------------------------------------------*/

delimiter $$

create procedure drop_keys()
begin

    declare done int default 0;
    declare cname varchar(64);
    declare tname varchar(64);

    declare curkey cursor for select table_name, constraint_name from information_schema.table_constraints where constraint_type = 'foreign key' and constraint_schema = @dbname;
    declare continue handler for not found set done = 1;

    open curkey;

    repeat

        fetch curkey into tname, cname;

        if not done then

            set @cmdstr = concat("alter table ", @dbname, ".", tname, " drop foreign key ", cname);

            prepare stmt from @cmdstr;
            execute stmt;
            deallocate prepare stmt;

        end if;

    until done end repeat;

    close curkey;

end
$$

delimiter ;

call drop_keys;
drop procedure drop_keys;

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

alter table tbl_string_values change column hashed_value value_token char (32) not null;

/*------------------------------------------------------------------------------------------------*/

alter table tbl_sys_vars add constraint unique ix_sys_vars
(
    var_name
);

alter table tbl_accounts add constraint unique ix_accounts
(
    username
);

alter table tbl_projects add constraint unique ix_projects
(
    project_name
);

alter table tbl_groups add constraint unique ix_groups
(
    project_id,
    group_name
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

alter table tbl_fields add constraint unique ix_fields_name
(
    state_id,
    field_name
);

alter table tbl_fields add constraint unique ix_fields_order
(
    state_id,
    field_order
);

alter table tbl_records add constraint unique ix_records
(
    creator_id,
    creation_time
);

alter table tbl_events add constraint unique ix_events
(
    record_id,
    originator_id,
    event_type,
    event_time
);

alter table tbl_changes add constraint unique ix_changes
(
    event_id,
    field_id
);

alter table tbl_string_values add constraint unique ix_string_values
(
    value_token
);

alter table tbl_text_values add constraint unique ix_text_values
(
    value_token
);

alter table tbl_list_values add constraint unique ix_list_values
(
    field_id,
    str_value
);

alter table tbl_comments add constraint unique ix_comments
(
    event_id
);

alter table tbl_attachments add constraint unique ix_attachments
(
    event_id
);

alter table tbl_filters add constraint unique ix_filters
(
    account_id,
    filter_name
);

alter table tbl_fsets add constraint unique ix_fsets
(
    account_id,
    fset_name
);

alter table tbl_views add constraint unique ix_views
(
    account_id,
    view_name
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

alter table tbl_def_columns add constraint unique ix_def_columns_name
(
    account_id,
    state_name,
    field_name,
    column_type
);

alter table tbl_def_columns add constraint unique ix_def_columns_order
(
    account_id,
    column_order
);

alter table tbl_subscribes add constraint unique ix_subscribes
(
    account_id,
    subscribe_name
);

alter table tbl_reminders add constraint unique ix_reminders
(
    account_id,
    reminder_name
);

/*------------------------------------------------------------------------------------------------*/

alter table tbl_groups add constraint foreign key fk_groups_project_id
(
    project_id
)
references tbl_projects
(
    project_id
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

alter table tbl_templates add constraint foreign key fk_templates_project_id
(
    project_id
)
references tbl_projects
(
    project_id
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

alter table tbl_states add constraint foreign key fk_states_template_id
(
    template_id
)
references tbl_templates
(
    template_id
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

alter table tbl_fields add constraint foreign key fk_fields_state_id
(
    state_id
)
references tbl_states
(
    state_id
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

alter table tbl_list_values add constraint foreign key fk_list_values_field_id
(
    field_id
)
references tbl_fields
(
    field_id
);

alter table tbl_comments add constraint foreign key fk_comments_event_id
(
    event_id
)
references tbl_events
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

alter table tbl_filters add constraint foreign key fk_filters_account_id
(
    account_id
)
references tbl_accounts
(
    account_id
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

alter table tbl_fsets add constraint foreign key fk_fsets_account_id
(
    account_id
)
references tbl_accounts
(
    account_id
);

alter table tbl_fset_filters add constraint foreign key fk_fset_filters_fset_id
(
    fset_id
)
references tbl_fsets
(
    fset_id
);

alter table tbl_fset_filters add constraint foreign key fk_fset_filters_filter_id
(
    filter_id
)
references tbl_filters
(
    filter_id
);

alter table tbl_views add constraint foreign key fk_views_account_id
(
    account_id
)
references tbl_accounts
(
    account_id
);

alter table tbl_view_columns add constraint foreign key fk_view_columns_view_id
(
    view_id
)
references tbl_views
(
    view_id
);

alter table tbl_def_columns add constraint foreign key fk_def_columns_account_id
(
    account_id
)
references tbl_accounts
(
    account_id
);

alter table tbl_subscribes add constraint foreign key fk_subscribes_account_id
(
    account_id
)
references tbl_accounts
(
    account_id
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

/*------------------------------------------------------------------------------------------------*/
