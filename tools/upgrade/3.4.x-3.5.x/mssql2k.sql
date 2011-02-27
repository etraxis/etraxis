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
set var_value = '3.5'
where var_name = 'FEATURE_LEVEL';

/*------------------------------------------------------------------------------------------------*/

create table tbl_state_assignees
(
    state_id int not null,
    group_id int not null
);

alter table tbl_state_assignees add constraint pk_state_assignees primary key clustered
(
    state_id,
    group_id
);

alter table tbl_state_assignees add constraint fk_state_assignees_state_id foreign key
(
    state_id
)
references tbl_states
(
    state_id
);

alter table tbl_state_assignees add constraint fk_state_assignees_group_id foreign key
(
    group_id
)
references tbl_groups
(
    group_id
);

insert into tbl_state_assignees
(state_id, group_id)
select distinct
    state_id_from as state_id,
    group_id as group_id
from tbl_group_trans;

/*------------------------------------------------------------------------------------------------*/
