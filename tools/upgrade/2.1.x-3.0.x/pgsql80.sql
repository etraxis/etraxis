/*----------------------------------------------------------------------------*/
/*                                                                            */
/*  eTraxis - Records tracking web-based system                               */
/*  Copyright (C) 2010  Artem Rodygin                                         */
/*                                                                            */
/*  This program is free software: you can redistribute it and/or modify      */
/*  it under the terms of the GNU General Public License as published by      */
/*  the Free Software Foundation, either version 3 of the License, or         */
/*  (at your option) any later version.                                       */
/*                                                                            */
/*  This program is distributed in the hope that it will be useful,           */
/*  but WITHOUT ANY WARRANTY; without even the implied warranty of            */
/*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             */
/*  GNU General Public License for more details.                              */
/*                                                                            */
/*  You should have received a copy of the GNU General Public License         */
/*  along with this program.  If not, see <http://www.gnu.org/licenses/>.     */
/*                                                                            */
/*----------------------------------------------------------------------------*/
/*  Server: PostgreSQL 8.0                                                    */
/*----------------------------------------------------------------------------*/

/*----------------------------------------------------------------------------*/
/*  tbl_sys_vars                                                              */
/*----------------------------------------------------------------------------*/

update tbl_sys_vars
set var_value = '3.0'
where var_name = 'FEATURE_LEVEL';

/*----------------------------------------------------------------------------*/
/*  tbl_accounts                                                              */
/*----------------------------------------------------------------------------*/

alter table tbl_accounts drop column fset_id;

/*----------------------------------------------------------------------------*/
/*  Obsolete tables.                                                          */
/*----------------------------------------------------------------------------*/

drop table tbl_fset_filters;
drop table tbl_fsets;
drop table tbl_def_columns;

/*----------------------------------------------------------------------------*/
/*  tbl_view_filters                                                          */
/*----------------------------------------------------------------------------*/

create table tbl_view_filters
(
    view_id int not null,
    filter_id int not null
) without oids;

alter table tbl_view_filters add constraint pk_view_filters primary key
(
    view_id,
    filter_id
);

alter table tbl_view_filters add constraint fk_view_filters_view_id foreign key
(
    view_id
)
references tbl_views
(
    view_id
);

alter table tbl_view_filters add constraint fk_view_filters_filter_id foreign key
(
    filter_id
)
references tbl_filters
(
    filter_id
);

/*----------------------------------------------------------------------------*/
