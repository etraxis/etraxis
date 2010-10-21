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
/*  Server: PostgreSQL 8.0                                                                        */
/*------------------------------------------------------------------------------------------------*/
/*  Author                  Date            Description of modifications                          */
/*------------------------------------------------------------------------------------------------*/
/*  Artem Rodygin           2008-04-19      new-705: Multiple parents for subrecords.             */
/*  Artem Rodygin           2008-04-20      new-703: Separated permissions set for current        */
/*                                          responsible.                                          */
/*------------------------------------------------------------------------------------------------*/

/*------------------------------------------------------------------------------------------------*/
/*  tbl_sys_vars                                                                                  */
/*------------------------------------------------------------------------------------------------*/

update tbl_sys_vars
set var_value = '1.16'
where var_name = 'FEATURE_LEVEL';

/*------------------------------------------------------------------------------------------------*/
/*  tbl_templates                                                                                 */
/*------------------------------------------------------------------------------------------------*/

alter table tbl_templates
add column responsible_perm int null;

update tbl_templates
set responsible_perm = 0;

alter table tbl_templates
alter column responsible_perm set not null;

/*------------------------------------------------------------------------------------------------*/
/*  tbl_fields                                                                                    */
/*------------------------------------------------------------------------------------------------*/

alter table tbl_fields
add column responsible_perm int null;

update tbl_fields
set responsible_perm = 0;

alter table tbl_fields
alter column responsible_perm set not null;

/*------------------------------------------------------------------------------------------------*/
/*  tbl_children                                                                                  */
/*------------------------------------------------------------------------------------------------*/

alter table tbl_children drop constraint tbl_children_pkey;

alter table tbl_children add primary key
(
    parent_id,
    child_id
);

/*------------------------------------------------------------------------------------------------*/
