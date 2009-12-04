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
/*  Server: Oracle9i                                                                              */
/*------------------------------------------------------------------------------------------------*/
/*  Author                  Date            Description of modifications                          */
/*------------------------------------------------------------------------------------------------*/
/*  Artem Rodygin           2007-11-13      new-618: Extend view and filter set names up to 50    */
/*                                          characters.                                           */
/*  Artem Rodygin           2007-11-14      new-548: Custom links in text fields.                 */
/*------------------------------------------------------------------------------------------------*/

connect etraxis/password@database;

/*------------------------------------------------------------------------------------------------*/
/*  tbl_sys_vars                                                                                  */
/*------------------------------------------------------------------------------------------------*/

update tbl_sys_vars
set var_value = '1.10'
where var_name = 'FEATURE_LEVEL';

/*------------------------------------------------------------------------------------------------*/
/*  tbl_fields                                                                                    */
/*------------------------------------------------------------------------------------------------*/

alter table tbl_fields
add (regex_check   nvarchar2 (1000) null,
     regex_search  nvarchar2 (1000) null,
     regex_replace nvarchar2 (1000) null);

/*------------------------------------------------------------------------------------------------*/
/*  tbl_fsets                                                                                     */
/*------------------------------------------------------------------------------------------------*/

alter table tbl_fsets
modify fset_name nvarchar2 (50) not null;

/*------------------------------------------------------------------------------------------------*/
/*  tbl_views                                                                                     */
/*------------------------------------------------------------------------------------------------*/

alter table tbl_views
modify view_name nvarchar2 (50) not null;

/*------------------------------------------------------------------------------------------------*/
