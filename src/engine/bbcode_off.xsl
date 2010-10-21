<!--
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

  eTraxis - Records tracking web-based system
  Copyright (C) 2010  Artem Rodygin

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

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:output method="xml" version="1.0" encoding="UTF-8" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>

<xsl:template match="bbcode">
    <bbcode>
    <xsl:apply-templates/>
    </bbcode>
</xsl:template>

<xsl:template match="bbcode_b|bbcode_i|bbcode_u|bbcode_s|bbcode_sub|bbcode_sup|bbcode_color|bbcode_size|bbcode_font|bbcode_align|bbcode_h1|bbcode_h2|bbcode_h3|bbcode_h4|bbcode_h5|bbcode_h6|bbcode_list|bbcode_ulist|bbcode_li|bbcode_url|bbcode_mail|bbcode_code|bbcode_quote|bbcode_search">
    <xsl:apply-templates/>
</xsl:template>

</xsl:stylesheet>
