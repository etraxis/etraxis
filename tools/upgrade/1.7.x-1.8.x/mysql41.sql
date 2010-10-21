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
/*  Artem Rodygin           2007-09-13      new-566: Choose encoding for record dump and export   */
/*                                          of records list.                                      */
/*  Artem Rodygin           2007-09-29      new-584: Extend maxsize of template name.             */
/*------------------------------------------------------------------------------------------------*/

use etraxis;

/*------------------------------------------------------------------------------------------------*/
/*  tbl_sys_vars                                                                                  */
/*------------------------------------------------------------------------------------------------*/

update tbl_sys_vars
set var_value = '1.8'
where var_name = 'FEATURE_LEVEL';

/*------------------------------------------------------------------------------------------------*/
/*  tbl_accounts                                                                                  */
/*------------------------------------------------------------------------------------------------*/

alter table tbl_accounts
add column csv_encoding int null;

update tbl_accounts
set csv_encoding = 1;

alter table tbl_accounts
modify column csv_encoding int not null;

alter table tbl_accounts
add column csv_line_ends int null;

update tbl_accounts
set csv_line_ends = 1;

alter table tbl_accounts
modify column csv_line_ends int not null;

/*------------------------------------------------------------------------------------------------*/
/*  tbl_templates                                                                                 */
/*------------------------------------------------------------------------------------------------*/

alter table tbl_templates
modify column template_name varchar (50) not null;

/*------------------------------------------------------------------------------------------------*/
