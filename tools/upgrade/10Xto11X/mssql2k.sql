/*------------------------------------------------------------------------------------------------*/
/*                                                                                                */
/*  eTraxis - Records tracking web-based system.                                                  */
/*  Copyright (C) 2007 by Artem Rodygin                                                           */
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
/*  Server: Microsoft SQL Server 2000                                                             */
/*------------------------------------------------------------------------------------------------*/
/*  Author                  Date            Description of modifications                          */
/*------------------------------------------------------------------------------------------------*/
/*  Artem Rodygin           2007-02-04      new-491: Group-wide transition permission.            */
/*------------------------------------------------------------------------------------------------*/

use etraxis;

/*------------------------------------------------------------------------------------------------*/
/*  tbl_group_trans                                                                               */
/*------------------------------------------------------------------------------------------------*/

create table tbl_group_trans
(
    state_id_from int not null,
    state_id_to   int not null,
    group_id      int not null
);

alter table tbl_group_trans add constraint pk_group_trans primary key clustered
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

insert into tbl_group_trans
select t.state_id_from, t.state_id_to, s.group_id
from tbl_state_perms s, tbl_transitions t
where s.state_id = t.state_id_to;

/*------------------------------------------------------------------------------------------------*/
/*  tbl_role_trans                                                                                */
/*------------------------------------------------------------------------------------------------*/

create table tbl_role_trans
(
    state_id_from int not null,
    state_id_to   int not null,
    role          int not null
);

alter table tbl_role_trans add constraint pk_role_trans primary key clustered
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

insert into tbl_role_trans
select t.state_id_from, t.state_id_to, 0 as role
from tbl_states s, tbl_transitions t
where s.state_id = t.state_id_to and s.author_perm = 1;

/*------------------------------------------------------------------------------------------------*/
/*  Drop obsolete columns                                                                         */
/*------------------------------------------------------------------------------------------------*/

alter table tbl_states drop column author_perm;

/*------------------------------------------------------------------------------------------------*/
/*  Drop obsolete tables                                                                          */
/*------------------------------------------------------------------------------------------------*/

drop table tbl_state_perms;
drop table tbl_transitions;

/*------------------------------------------------------------------------------------------------*/
