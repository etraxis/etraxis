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
/*  Artem Rodygin           2008-12-09      bug-770: MySQL server hangs up on searching.          */
/*  Artem Rodygin           2008-12-10      bug-777: ORA-00972: identifier is too long            */
/*------------------------------------------------------------------------------------------------*/

connect etraxis/password@database;

/*------------------------------------------------------------------------------------------------*/
/*  tbl_sys_vars                                                                                  */
/*------------------------------------------------------------------------------------------------*/

update tbl_sys_vars
set var_value = '1.20'
where var_name = 'FEATURE_LEVEL';

/*------------------------------------------------------------------------------------------------*/
/*  tbl_events                                                                                    */
/*------------------------------------------------------------------------------------------------*/

create index ix_record
on tbl_events (record_id);

/*------------------------------------------------------------------------------------------------*/
/*  tbl_field_values                                                                              */
/*------------------------------------------------------------------------------------------------*/

alter table tbl_field_values
add field_type number (10) null;

update tbl_field_values
set field_type = (select tbl_fields.field_type
                  from tbl_fields
                  where tbl_fields.field_id = tbl_field_values.field_id);

alter table tbl_field_values
modify field_type number (10) not null;

create index ix_value
on tbl_field_values (value_id);

/*------------------------------------------------------------------------------------------------*/
/*  tbl_filter_activation                                                                         */
/*------------------------------------------------------------------------------------------------*/

alter table tbl_filter_activation add constraint fk_filter_activation_account foreign key
(
    account_id
)
references tbl_accounts
(
    account_id
);

/*------------------------------------------------------------------------------------------------*/

commit;
