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
set var_value = '3.9'
where var_name = 'FEATURE_LEVEL';

/*------------------------------------------------------------------------------------------------*/

alter table tbl_accounts modify email nvarchar2 (50) not null;
alter table tbl_accounts modify passwd varchar2 (32) null;
alter table tbl_accounts modify auth_token varchar2 (32);

/*------------------------------------------------------------------------------------------------*/

alter table tbl_string_values modify value_token varchar2 (32);

/*------------------------------------------------------------------------------------------------*/

alter table tbl_text_values modify value_token varchar2 (32);

/*------------------------------------------------------------------------------------------------*/

alter table tbl_changes add change_id number (10) not null;

alter table tbl_changes add constraint pk_changes primary key
(
    change_id
);

create sequence seq_changes;

create or replace trigger tgi_changes before insert on tbl_changes for each row
begin
    select seq_changes.nextval into :new.change_id from dual;
end;
/

/*------------------------------------------------------------------------------------------------*/

alter table tbl_states add constraint fk_states_next_state_id foreign key
(
    next_state_id
)
references tbl_states
(
    state_id
);

/*------------------------------------------------------------------------------------------------*/
