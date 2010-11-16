/*------------------------------------------------------------------------------------------------*/
/*                                                                                                */
/*  eTraxis - Records tracking web-based system.                                                  */
/*  Copyright (C) 2010 by Artem Rodygin                                                           */
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
/*  Server type: Microsoft SQL Server 2000                                                        */
/*------------------------------------------------------------------------------------------------*/

use etraxis;

/*------------------------------------------------------------------------------------------------*/

update tbl_sys_vars
set var_value = '3.1'
where var_name = 'FEATURE_LEVEL';

/*------------------------------------------------------------------------------------------------*/

alter table tbl_accounts add theme_name nvarchar (50) null;
go
update tbl_accounts set theme_name = 'Emerald';
alter table tbl_accounts alter column theme_name nvarchar (50) not null;

/*------------------------------------------------------------------------------------------------*/
