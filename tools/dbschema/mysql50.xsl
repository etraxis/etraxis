<xsl:stylesheet version='1.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>

<xsl:output method="text" version="1.0" encoding="UTF-8"/>

<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

<xsl:template match="database">

<xsl:text>/*----------------------------------------------------------------------------*/
/*                                                                            */
/*  eTraxis - Records tracking web-based system                               */
/*  Copyright (C) 2005-2011  Artem Rodygin                                    */
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
/*  along with this program.  If not, see &lt;http://www.gnu.org/licenses/&gt;.     */
/*                                                                            */
/*----------------------------------------------------------------------------*/
/*  Server type: MySQL 5.0                                                    */
/*----------------------------------------------------------------------------*/

create database etraxis
character set utf8
collate utf8_general_ci;

use etraxis;
</xsl:text>

<xsl:apply-templates select="table"/>

<xsl:text>
insert into tbl_sys_vars (var_name, var_value)
values ('DATABASE_TYPE', 'MySQL 5.0');
</xsl:text>

<xsl:text>
insert into tbl_sys_vars (var_name, var_value)
values ('FEATURE_LEVEL', '</xsl:text>
<xsl:value-of select="@version"/>
<xsl:text>');
</xsl:text>

<xsl:text>
insert into tbl_accounts
(
    username,
    fullname,
    email,
    passwd,
    description,
    auth_token,
    token_expire,
    passwd_expire,
    is_admin,
    is_disabled,
    is_ldapuser,
    locks_count,
    lock_time,
    locale,
    text_rows,
    page_rows,
    page_bkms,
    csv_delim,
    csv_encoding,
    csv_line_ends,
    view_id,
    theme_name
)
values
(
    'root@eTraxis',
    'Built-in administrator',
    'root@example.com',
    'd41d8cd98f00b204e9800998ecf8427e',
    'Built-in administrator',
    NULL, 0, 0, 1, 0, 0, 0, 0, 1000, 8, 20, 10, 44, 1, 1, NULL, 'Emerald'
);
</xsl:text>

</xsl:template>

<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

<xsl:template match="table">

<xsl:text>
create table </xsl:text>
<xsl:value-of select="@name"/>
<xsl:text>
(</xsl:text>

<xsl:apply-templates select="column"/>

<xsl:text>
);
</xsl:text>

<xsl:apply-templates select="primary"/>
<xsl:apply-templates select="unique"/>
<xsl:apply-templates select="foreign"/>
<xsl:apply-templates select="index"/>

</xsl:template>

<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

<xsl:template match="column">

<xsl:if test="position() > 1">
<xsl:text>,</xsl:text>
</xsl:if>

<xsl:text>
    </xsl:text>

<xsl:value-of select="."/>
<xsl:text> </xsl:text>

<xsl:if test="@type = 'primary'">
<xsl:text>int not null auto_increment primary key</xsl:text>
</xsl:if>

<xsl:if test="@type = 'str'">
<xsl:text>varchar (</xsl:text>
<xsl:value-of select="@size"/>
<xsl:text>) </xsl:text>
<xsl:if test="not(@null = 'yes')">
<xsl:text>not </xsl:text>
</xsl:if>
<xsl:text>null</xsl:text>
</xsl:if>

<xsl:if test="@type = 'nstr'">
<xsl:text>varchar (</xsl:text>
<xsl:value-of select="@size"/>
<xsl:text>) </xsl:text>
<xsl:if test="not(@null = 'yes')">
<xsl:text>not </xsl:text>
</xsl:if>
<xsl:text>null</xsl:text>
</xsl:if>

<xsl:if test="@type = 'md5'">
<xsl:text>char (32) </xsl:text>
<xsl:if test="not(@null = 'yes')">
<xsl:text>not </xsl:text>
</xsl:if>
<xsl:text>null</xsl:text>
</xsl:if>

<xsl:if test="@type = 'int'">
<xsl:text>int </xsl:text>
<xsl:if test="not(@null = 'yes')">
<xsl:text>not </xsl:text>
</xsl:if>
<xsl:text>null</xsl:text>
</xsl:if>

<xsl:if test="@type = 'byte'">
<xsl:text>int </xsl:text>
<xsl:if test="not(@null = 'yes')">
<xsl:text>not </xsl:text>
</xsl:if>
<xsl:text>null</xsl:text>
</xsl:if>

<xsl:if test="@type = 'word'">
<xsl:text>int </xsl:text>
<xsl:if test="not(@null = 'yes')">
<xsl:text>not </xsl:text>
</xsl:if>
<xsl:text>null</xsl:text>
</xsl:if>

<xsl:if test="@type = 'bool'">
<xsl:text>int </xsl:text>
<xsl:if test="not(@null = 'yes')">
<xsl:text>not </xsl:text>
</xsl:if>
<xsl:text>null</xsl:text>
</xsl:if>

<xsl:if test="@type = 'ntext'">
<xsl:text>text </xsl:text>
<xsl:if test="not(@null = 'yes')">
<xsl:text>not </xsl:text>
</xsl:if>
<xsl:text>null</xsl:text>
</xsl:if>

</xsl:template>

<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

<xsl:template match="primary">

<xsl:if test="not(../column[1]/@type = 'primary')">

<xsl:text>
alter table </xsl:text>
<xsl:value-of select="../@name"/>
<xsl:text> add primary key
(
</xsl:text>

<xsl:for-each select="column">
<xsl:if test="position() > 1">
<xsl:text>,
</xsl:text>
</xsl:if>
<xsl:text>    </xsl:text>
<xsl:value-of select="."/>
</xsl:for-each>

<xsl:text>
);
</xsl:text>

</xsl:if>

</xsl:template>

<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

<xsl:template match="unique">

<xsl:text>
alter table </xsl:text>
<xsl:value-of select="../@name"/>
<xsl:text> add constraint unique </xsl:text>
<xsl:value-of select="@name"/>
<xsl:text>
(
</xsl:text>

<xsl:for-each select="column">
<xsl:if test="position() > 1">
<xsl:text>,
</xsl:text>
</xsl:if>
<xsl:text>    </xsl:text>
<xsl:value-of select="."/>
</xsl:for-each>

<xsl:text>
);
</xsl:text>

</xsl:template>

<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

<xsl:template match="foreign">

<xsl:text>
alter table </xsl:text>
<xsl:value-of select="../@name"/>
<xsl:text> add constraint foreign key </xsl:text>
<xsl:value-of select="@name"/>
<xsl:text>
(
    </xsl:text>
<xsl:value-of select="."/>
<xsl:text>
)
references </xsl:text>
<xsl:value-of select="@table"/>
<xsl:text>
(
    </xsl:text>

<xsl:if test="boolean(@column)">
<xsl:value-of select="@column"/>
</xsl:if>

<xsl:if test="not(boolean(@column))">
<xsl:value-of select="."/>
</xsl:if>

<xsl:text>
);
</xsl:text>

</xsl:template>

<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

<xsl:template match="index">

<xsl:text>
create index </xsl:text>
<xsl:value-of select="@name"/>
<xsl:text> on </xsl:text>
<xsl:value-of select="../@name"/>
<xsl:text> (</xsl:text>

<xsl:for-each select="column">
<xsl:if test="position() > 1">
<xsl:text>, </xsl:text>
</xsl:if>
<xsl:value-of select="."/>
</xsl:for-each>

<xsl:text>);
</xsl:text>

</xsl:template>

<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

</xsl:stylesheet>
