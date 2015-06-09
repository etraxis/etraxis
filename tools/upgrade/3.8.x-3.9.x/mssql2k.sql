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
/*  Server type: Microsoft SQL Server 2000                                                        */
/*------------------------------------------------------------------------------------------------*/

use etraxis;

/*------------------------------------------------------------------------------------------------*/

update tbl_sys_vars
set var_value = '3.9'
where var_name = 'FEATURE_LEVEL';

/*------------------------------------------------------------------------------------------------*/

alter table tbl_accounts alter column email nvarchar (50) not null;
alter table tbl_accounts alter column passwd nvarchar (32) null;
alter table tbl_accounts alter column auth_token nvarchar (32) null;

/*------------------------------------------------------------------------------------------------*/

alter table tbl_string_values drop constraint ix_string_values;
alter table tbl_string_values alter column value_token nvarchar (32) not null;
alter table tbl_string_values add constraint ix_string_values unique nonclustered
(
    value_token
);

/*------------------------------------------------------------------------------------------------*/

alter table tbl_text_values drop constraint ix_text_values;
alter table tbl_text_values alter column value_token nvarchar (32) not null;
alter table tbl_text_values add constraint ix_text_values unique nonclustered
(
    value_token
);

/*------------------------------------------------------------------------------------------------*/

alter table tbl_comments alter column comment_body varchar(max) not null;
alter table tbl_text_values alter column text_value varchar(max) not null;

/*------------------------------------------------------------------------------------------------*/

alter table tbl_changes add change_id int identity (1,1) not null;
go

alter table tbl_changes add constraint pk_changes primary key clustered
(
    change_id
);

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
