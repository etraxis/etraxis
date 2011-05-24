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
/*  Server type: MySQL 5.0                                                                        */
/*------------------------------------------------------------------------------------------------*/

use etraxis;
set @dbname = 'etraxis';

/*------------------------------------------------------------------------------------------------*/

update tbl_sys_vars
set var_value = '3.6'
where var_name = 'FEATURE_LEVEL';

/*------------------------------------------------------------------------------------------------*/

alter table tbl_accounts modify column email varchar (50) null;

alter table tbl_accounts add column timezone int null after locale;
update tbl_accounts set timezone = 0;
alter table tbl_accounts modify column timezone int not null;

alter table tbl_accounts add column auto_refresh int null after page_bkms;
update tbl_accounts set auto_refresh = 0;
alter table tbl_accounts modify column auto_refresh int not null;

/*------------------------------------------------------------------------------------------------*/

alter table tbl_fields add column template_id int null after field_id;
update tbl_fields, tbl_states set tbl_fields.template_id = tbl_states.template_id where tbl_fields.state_id = tbl_states.state_id;
alter table tbl_fields modify column template_id int not null;

alter table tbl_fields add constraint foreign key fk_fields_template_id
(
    template_id
)
references tbl_templates
(
    template_id
);

alter table tbl_fields modify column state_id int null;

alter table tbl_fields add column show_in_emails int null after add_separator;
update tbl_fields set show_in_emails = 0;
alter table tbl_fields modify column show_in_emails int not null;

/*------------------------------------------------------------------------------------------------*/
