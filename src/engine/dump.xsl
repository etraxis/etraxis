<!--
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

  eTraxis - Records tracking web-based system
  Copyright (C) 2007-2010  Artem Rodygin

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
-->

<xsl:stylesheet version='1.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>

<xsl:output method="text" version="1.0" encoding="UTF-8"/>

<xsl:template match="content">
    <xsl:apply-templates select="group"/>
    <xsl:text>--------------------------------------------------------------------------------&#10;</xsl:text>
</xsl:template>

<xsl:template match="group">
    <xsl:if test="boolean(@title)">
        <xsl:text>--------------------------------------------------------------------------------&#10;</xsl:text>
        <xsl:value-of select="@title"/>
        <xsl:text>&#10;</xsl:text>
        <xsl:text>--------------------------------------------------------------------------------&#10;</xsl:text>
    </xsl:if>
    <xsl:apply-templates select="text"/>
    <xsl:text>&#10;</xsl:text>
</xsl:template>

<xsl:template match="text">
    <xsl:if test="boolean(@label)">
        <xsl:value-of select="@label"/>
        <xsl:text>: </xsl:text>
    </xsl:if>
    <xsl:apply-templates/>
    <xsl:text>&#10;</xsl:text>
</xsl:template>

</xsl:stylesheet>
