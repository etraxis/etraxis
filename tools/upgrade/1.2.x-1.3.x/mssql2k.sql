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
/*  Artem Rodygin           2007-07-01      new-539: Existing records must not be marked as read  */
/*                                          for newly created user.                               */
/*  Artem Rodygin           2007-07-04      new-533: Links between records.                       */
/*------------------------------------------------------------------------------------------------*/

use etraxis;

/*------------------------------------------------------------------------------------------------*/
/*  tbl_dependencies                                                                              */
/*------------------------------------------------------------------------------------------------*/

create table tbl_dependencies
(
    record_id     int not null,
    dependency_id int not null
);

alter table tbl_dependencies add constraint pk_dependencies primary key clustered
(
    record_id,
    dependency_id
);

alter table tbl_dependencies add constraint fk_dependencies_record_id foreign key
(
    record_id
)
references tbl_records
(
    record_id
);

alter table tbl_dependencies add constraint fk_dependencies_dependency_id foreign key
(
    dependency_id
)
references tbl_records
(
    record_id
);

/*------------------------------------------------------------------------------------------------*/
/*  tbl_reads                                                                                     */
/*------------------------------------------------------------------------------------------------*/

delete from tbl_reads
where read_time in
   (select t.read_time
    from
       (select count(record_id) as records, account_id, read_time
        from tbl_reads
        group by account_id, read_time) t
    where t.records > 1);

/*------------------------------------------------------------------------------------------------*/
