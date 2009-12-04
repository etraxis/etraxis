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
/*  Artem Rodygin           2007-09-10      new-579: Rework "state abbreviation" into "state      */
/*                                          short name".                                          */
/*  Artem Rodygin           2007-09-11      new-574: Filter should allow to specify several       */
/*                                          states.                                               */
/*  Artem Rodygin           2007-09-12      new-576: [SF1788286] Export to CSV                    */
/*------------------------------------------------------------------------------------------------*/

use etraxis;

/*------------------------------------------------------------------------------------------------*/
/*  tbl_sys_vars                                                                                  */
/*------------------------------------------------------------------------------------------------*/

update tbl_sys_vars
set var_value = '1.7'
where var_name = 'FEATURE_LEVEL';

/*------------------------------------------------------------------------------------------------*/
/*  tbl_accounts                                                                                  */
/*------------------------------------------------------------------------------------------------*/

alter table tbl_accounts
add column csv_delimiter int null;

update tbl_accounts
set csv_delimiter = 44;

alter table tbl_accounts
modify column csv_delimiter int not null;

/*------------------------------------------------------------------------------------------------*/
/*  tbl_states                                                                                    */
/*------------------------------------------------------------------------------------------------*/

alter table tbl_states
modify column state_abbr varchar (50) not null;

/*------------------------------------------------------------------------------------------------*/
/*  tbl_filter_trans                                                                              */
/*------------------------------------------------------------------------------------------------*/

alter table tbl_filter_states rename tbl_filter_trans;

/*------------------------------------------------------------------------------------------------*/
/*  tbl_filter_states                                                                             */
/*------------------------------------------------------------------------------------------------*/

create table tbl_filter_states
(
    filter_id int not null,
    state_id  int not null
);

alter table tbl_filter_states add primary key
(
    filter_id,
    state_id
);

alter table tbl_filter_states add constraint foreign key
(
    filter_id
)
references tbl_filters
(
    filter_id
);

alter table tbl_filter_states add constraint foreign key
(
    state_id
)
references tbl_states
(
    state_id
);

/*------------------------------------------------------------------------------------------------*/
