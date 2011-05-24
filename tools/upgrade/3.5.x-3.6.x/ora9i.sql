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
/*  Server type: Oracle 9i                                                                        */
/*------------------------------------------------------------------------------------------------*/

connect etraxis/password@database;

/*------------------------------------------------------------------------------------------------*/

update tbl_sys_vars
set var_value = '3.6'
where var_name = 'FEATURE_LEVEL';

/*------------------------------------------------------------------------------------------------*/

alter table tbl_accounts modify email nvarchar2 (50) null;

alter table tbl_accounts add timezone number (10) null;
/
update tbl_accounts set timezone = 0;
alter table tbl_accounts modify timezone number (10) not null;

alter table tbl_accounts add auto_refresh number (10) null;
/
update tbl_accounts set auto_refresh = 0;
alter table tbl_accounts modify auto_refresh number (10) not null;

/*------------------------------------------------------------------------------------------------*/

alter table tbl_fields add template_id number (10) null;
/
update tbl_fields set template_id = (select tbl_states.template_id from tbl_states where tbl_states.state_id = tbl_fields.state_id);
alter table tbl_fields modify template_id number (10) not null;

alter table tbl_fields add constraint fk_fields_template_id foreign key
(
    template_id
)
references tbl_templates
(
    template_id
);

alter table tbl_fields modify state_id number (10) null;

alter table tbl_fields add show_in_emails number (10) null;
/
update tbl_fields set show_in_emails = 0;
alter table tbl_fields modify show_in_emails number (10) not null;

/*------------------------------------------------------------------------------------------------*/
