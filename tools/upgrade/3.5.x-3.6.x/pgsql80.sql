/*------------------------------------------------------------------------------------------------*/
/*                                                                                                */
/*  eTraxis - Records tracking web-based system.                                                  */
/*  Copyright (C) 2011 by Artem Rodygin                                                           */
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
set var_value = '3.6'
where var_name = 'FEATURE_LEVEL';

/*------------------------------------------------------------------------------------------------*/

alter table tbl_accounts alter column email drop not null;

alter table tbl_accounts add column timezone int null;
update tbl_accounts set timezone = 0;
alter table tbl_accounts alter column timezone set not null;

alter table tbl_accounts add column auto_refresh int null;
update tbl_accounts set auto_refresh = 0;
alter table tbl_accounts alter column auto_refresh set not null;

/*------------------------------------------------------------------------------------------------*/

alter table tbl_fields add column template_id int null;
update tbl_fields f set template_id = tbl_states.template_id from tbl_states where f.state_id = tbl_states.state_id;
alter table tbl_fields alter column template_id set not null;

alter table tbl_fields add constraint fk_fields_template_id foreign key
(
    template_id
)
references tbl_templates
(
    template_id
);

alter table tbl_fields alter column state_id drop not null;

alter table tbl_fields add column show_in_emails int null;
update tbl_fields set show_in_emails = 0;
alter table tbl_fields alter column show_in_emails set not null;

/*------------------------------------------------------------------------------------------------*/
