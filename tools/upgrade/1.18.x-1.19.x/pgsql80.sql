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
/*  Artem Rodygin           2008-11-11      new-749: Guest access for unauthorized users.         */
/*------------------------------------------------------------------------------------------------*/

/*------------------------------------------------------------------------------------------------*/
/*  tbl_sys_vars                                                                                  */
/*------------------------------------------------------------------------------------------------*/

update tbl_sys_vars
set var_value = '1.19'
where var_name = 'FEATURE_LEVEL';

/*------------------------------------------------------------------------------------------------*/
/*  tbl_templates                                                                                 */
/*------------------------------------------------------------------------------------------------*/

alter table tbl_templates
rename column guest_perm to guest_access;

update tbl_templates
set guest_access = 1
where guest_access <> 0;

/*------------------------------------------------------------------------------------------------*/
/*  tbl_fields                                                                                    */
/*------------------------------------------------------------------------------------------------*/

alter table tbl_fields
rename column guest_perm to guest_access;

update tbl_fields
set guest_access = 1
where guest_access <> 0;

/*------------------------------------------------------------------------------------------------*/
/*  tbl_role_trans                                                                                */
/*------------------------------------------------------------------------------------------------*/

delete from tbl_role_trans
where role = -1;

update tbl_role_trans
set role = -1
where role <> -2;

update tbl_role_trans
set role = -2
where role <> -3;

/*------------------------------------------------------------------------------------------------*/
