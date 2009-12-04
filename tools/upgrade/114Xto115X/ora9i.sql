/*------------------------------------------------------------------------------------------------*/
/*                                                                                                */
/*  eTraxis - Records tracking web-based system.                                                  */
/*  Copyright (C) 2008 by Artem Rodygin                                                           */
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
/*  Server: Oracle9i                                                                              */
/*------------------------------------------------------------------------------------------------*/
/*  Author                  Date            Description of modifications                          */
/*------------------------------------------------------------------------------------------------*/
/*  Artem Rodygin           2008-03-10      new-683: Filters should be sharable with groups, not  */
/*                                          with accounts.                                        */
/*  Artem Rodygin           2008-03-15      new-501: Filter should allow to specify 'none' value  */
/*                                          of 'list' fields.                                     */
/*------------------------------------------------------------------------------------------------*/

connect etraxis/password@database;

/*------------------------------------------------------------------------------------------------*/
/*  tbl_sys_vars                                                                                  */
/*------------------------------------------------------------------------------------------------*/

update tbl_sys_vars
set var_value = '1.15'
where var_name = 'FEATURE_LEVEL';

/*------------------------------------------------------------------------------------------------*/
/*  tbl_filter_sharing                                                                            */
/*------------------------------------------------------------------------------------------------*/

drop table tbl_filter_sharing;
/

create table tbl_filter_sharing
(
    filter_id number (10) not null,
    group_id  number (10) not null
);

alter table tbl_filter_sharing add constraint pk_filter_sharing primary key
(
    filter_id,
    group_id
);

alter table tbl_filter_sharing add constraint fk_filter_sharing_filter_id foreign key
(
    filter_id
)
references tbl_filters
(
    filter_id
);

alter table tbl_filter_sharing add constraint fk_filter_sharing_group_id foreign key
(
    group_id
)
references tbl_groups
(
    group_id
);

/*------------------------------------------------------------------------------------------------*/
/*  tbl_filter_activation                                                                         */
/*------------------------------------------------------------------------------------------------*/

create table tbl_filter_activation
(
    filter_id  number (10) not null,
    account_id number (10) not null
);

alter table tbl_filter_activation add constraint pk_filter_activation primary key
(
    filter_id,
    account_id
);

alter table tbl_filter_activation add constraint fk_filter_activation_filter_id foreign key
(
    filter_id
)
references tbl_filters
(
    filter_id
);

alter table tbl_filter_activation add constraint fk_filter_activation_account_id foreign key
(
    account_id
)
references tbl_accounts
(
    account_id
);

insert into tbl_filter_activation
(filter_id, account_id)

    select filter_id, account_id
    from tbl_filters
    where is_activated = 1;

/*------------------------------------------------------------------------------------------------*/
/*  tbl_filter_fields                                                                             */
/*------------------------------------------------------------------------------------------------*/

alter table tbl_filter_fields
modify param1 number (10) null;

/*------------------------------------------------------------------------------------------------*/
/*  Drop obsolete columns                                                                         */
/*------------------------------------------------------------------------------------------------*/

alter table tbl_filters drop column is_activated;

/*------------------------------------------------------------------------------------------------*/
