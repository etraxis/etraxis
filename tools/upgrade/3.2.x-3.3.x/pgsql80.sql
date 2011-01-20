/*------------------------------------------------------------------------------------------------*/
/*                                                                                                */
/*  eTraxis - Records tracking web-based system.                                                  */
/*  Copyright (C) 2011 by Artem Rodygin                                                           */
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
/*  Server type: PostgreSQL 8.0                                                                   */
/*------------------------------------------------------------------------------------------------*/

update tbl_sys_vars
set var_value = '3.3'
where var_name = 'FEATURE_LEVEL';

/*------------------------------------------------------------------------------------------------*/

alter table tbl_accounts add column text_rows int null;
update tbl_accounts set text_rows = 8;
alter table tbl_accounts alter column text_rows set not null;

/*------------------------------------------------------------------------------------------------*/

alter table tbl_events drop constraint ix_events;

alter table tbl_events add constraint ix_events unique
(
    record_id,
    originator_id,
    event_type,
    event_time,
    event_param
);

/*------------------------------------------------------------------------------------------------*/
