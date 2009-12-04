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
/*  Artem Rodygin           2007-10-29      new-564: Filters set.                                 */
/*  Artem Rodygin           2007-11-05      new-571: View should show all records of current      */
/*                                          filters set.                                          */
/*------------------------------------------------------------------------------------------------*/

use etraxis;

/*------------------------------------------------------------------------------------------------*/
/*  tbl_sys_vars                                                                                  */
/*------------------------------------------------------------------------------------------------*/

update tbl_sys_vars
set var_value = '1.9'
where var_name = 'FEATURE_LEVEL';

/*------------------------------------------------------------------------------------------------*/
/*  tbl_accounts                                                                                  */
/*------------------------------------------------------------------------------------------------*/

update tbl_accounts
set view_id = NULL;

alter table tbl_accounts
add fset_id int null;
go

/*------------------------------------------------------------------------------------------------*/
/*  tbl_fsets                                                                                     */
/*------------------------------------------------------------------------------------------------*/

create table tbl_fsets
(
    fset_id     int identity (1,1) not null,
    account_id  int not null,
    fset_name   varchar (100) not null,
    hashed_name char    (32)  not null
);

alter table tbl_fsets add constraint pk_fsets primary key clustered
(
    fset_id
);

alter table tbl_fsets add constraint ix_fsets unique nonclustered
(
    account_id,
    hashed_name
);

alter table tbl_fsets add constraint fk_fsets_account_id foreign key
(
    account_id
)
references tbl_accounts
(
    account_id
);

alter table tbl_accounts add constraint fk_accounts_fset_id foreign key
(
    fset_id
)
references tbl_fsets
(
    fset_id
);

/*------------------------------------------------------------------------------------------------*/
/*  tbl_fset_filters                                                                              */
/*------------------------------------------------------------------------------------------------*/

create table tbl_fset_filters
(
    fset_id   int not null,
    filter_id int not null
);

alter table tbl_fset_filters add constraint pk_fset_filters primary key clustered
(
    fset_id,
    filter_id
);

alter table tbl_fset_filters add constraint fk_fset_filters_fset_id foreign key
(
    fset_id
)
references tbl_fsets
(
    fset_id
);

alter table tbl_fset_filters add constraint fk_fset_filters_filter_id foreign key
(
    filter_id
)
references tbl_filters
(
    filter_id
);

/*------------------------------------------------------------------------------------------------*/
/*  tbl_columns                                                                                   */
/*------------------------------------------------------------------------------------------------*/

drop table tbl_columns;

/*------------------------------------------------------------------------------------------------*/
/*  tbl_views                                                                                     */
/*------------------------------------------------------------------------------------------------*/

delete from tbl_views;
alter table tbl_views drop constraint fk_views_template_id;
alter table tbl_views drop column template_id;

/*------------------------------------------------------------------------------------------------*/
/*  tbl_view_columns                                                                              */
/*------------------------------------------------------------------------------------------------*/

create table tbl_view_columns
(
    column_id    int identity (1,1) not null,
    view_id      int not null,
    state_name   varchar (200) null,
    field_name   varchar (200) null,
    column_type  int not null,
    column_order int not null
);

alter table tbl_view_columns add constraint pk_view_columns primary key clustered
(
    column_id
);

alter table tbl_view_columns add constraint ix_view_columns_name unique nonclustered
(
    view_id,
    state_name,
    field_name,
    column_type
);

alter table tbl_view_columns add constraint ix_view_columns_order unique nonclustered
(
    view_id,
    column_order
);

alter table tbl_view_columns add constraint fk_view_columns_view_id foreign key
(
    view_id
)
references tbl_views
(
    view_id
);

/*------------------------------------------------------------------------------------------------*/
/*  tbl_def_columns                                                                               */
/*------------------------------------------------------------------------------------------------*/

create table tbl_def_columns
(
    column_id    int identity (1,1) not null,
    account_id   int not null,
    state_name   varchar (200) null,
    field_name   varchar (200) null,
    column_type  int not null,
    column_order int not null
);

alter table tbl_def_columns add constraint pk_def_columns primary key clustered
(
    column_id
);

alter table tbl_def_columns add constraint ix_def_columns_name unique nonclustered
(
    account_id,
    state_name,
    field_name,
    column_type
);

alter table tbl_def_columns add constraint ix_def_columns_order unique nonclustered
(
    account_id,
    column_order
);

alter table tbl_def_columns add constraint fk_def_columns_account_id foreign key
(
    account_id
)
references tbl_accounts
(
    account_id
);

/*------------------------------------------------------------------------------------------------*/
