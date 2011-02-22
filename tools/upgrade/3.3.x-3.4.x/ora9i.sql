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
set var_value = '3.4'
where var_name = 'FEATURE_LEVEL';

/*------------------------------------------------------------------------------------------------*/

alter table tbl_fields modify regex_check nvarchar2 (500) null;
alter table tbl_fields modify regex_search nvarchar2 (500) null;
alter table tbl_fields modify regex_replace nvarchar2 (500) null;

/*------------------------------------------------------------------------------------------------*/

create table tbl_float_values
(
    value_id number (10) not null,
    float_value number (20,10) not null
);

alter table tbl_float_values add constraint pk_float_values primary key
(
    value_id
);

alter table tbl_float_values add constraint ix_float_values unique
(
    float_value
);

create sequence seq_float_values;

create or replace trigger tgi_float_values before insert on tbl_float_values for each row
begin
    select seq_float_values.nextval into :new.value_id from dual;
end;
/

/*------------------------------------------------------------------------------------------------*/
