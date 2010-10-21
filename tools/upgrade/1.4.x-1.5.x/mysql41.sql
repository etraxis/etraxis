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
/*  Server: MySQL 4.1                                                                             */
/*------------------------------------------------------------------------------------------------*/
/*  Author                  Date            Description of modifications                          */
/*------------------------------------------------------------------------------------------------*/
/*  Artem Rodygin           2007-08-06      new-551: Rework dependencies into "parent-child"      */
/*                                          relations.                                            */
/*------------------------------------------------------------------------------------------------*/

use etraxis;

/*------------------------------------------------------------------------------------------------*/
/*  tbl_children                                                                                  */
/*------------------------------------------------------------------------------------------------*/

create table tbl_children
(
    parent_id     int not null,
    child_id      int not null,
    is_dependency int not null
);

alter table tbl_children add primary key
(
    child_id
);

alter table tbl_children add constraint foreign key
(
    parent_id
)
references tbl_records
(
    record_id
);

alter table tbl_children add constraint foreign key
(
    child_id
)
references tbl_records
(
    record_id
);

insert into tbl_children
select record_id as parent_id, dependency_id as child_id, 1 as is_dependency
from tbl_dependencies;

/*------------------------------------------------------------------------------------------------*/
/*  Drop obsolete tables                                                                          */
/*------------------------------------------------------------------------------------------------*/

drop table tbl_dependencies;

/*------------------------------------------------------------------------------------------------*/
