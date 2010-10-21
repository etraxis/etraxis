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
/*  Server type: Oracle 9i                                                                        */
/*------------------------------------------------------------------------------------------------*/

connect etraxis/password@database;

/*------------------------------------------------------------------------------------------------*/

update tbl_sys_vars
set var_value = 'Oracle 9i'
where var_name = 'DATABASE_TYPE';

update tbl_sys_vars
set var_value = '2.0'
where var_name = 'FEATURE_LEVEL';

/*------------------------------------------------------------------------------------------------*/

alter table tbl_projects      drop constraint ix_projects;
alter table tbl_groups        drop constraint ix_groups;
alter table tbl_templates     drop constraint ix_templates_name;
alter table tbl_templates     drop constraint ix_templates_prefix;
alter table tbl_states        drop constraint ix_states_name;
alter table tbl_states        drop constraint ix_states_abbr;
alter table tbl_fields        drop constraint ix_fields_name;
alter table tbl_string_values drop constraint ix_string_values;
alter table tbl_filters       drop constraint ix_filters;
alter table tbl_fsets         drop constraint ix_fsets;
alter table tbl_views         drop constraint ix_views;
alter table tbl_subscribes    drop constraint ix_subscribes;
alter table tbl_reminders     drop constraint ix_reminders;

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

alter table tbl_string_values add constraint ix_string_values unique
(
    value_token
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

commit;
