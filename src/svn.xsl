<?xml version="1.0"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:output method="html" version="1.0" encoding="UTF-8" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>

<xsl:template match="svn">
<html>
<xsl:apply-templates select="index"/>
</html>
</xsl:template>

<xsl:template match="index">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta name="author" content="eTraxis, LLC" />
<meta name="copyright" content="Copyright (C) 2011 by eTraxis, LLC" />
<title>
<xsl:if test="string-length(@base) != 0">
<xsl:value-of select="@base"/>
<xsl:text>: </xsl:text>
</xsl:if>
<xsl:value-of select="@path"/></title>
<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
<link rel="stylesheet" type="text/css" href="/svn.css" />
</head>
<body>
<div id="toolbar">
<div class="toolbar_split spacer"></div>
<xsl:if test="string-length(@base) != 0">
<div class="toolbar_item"><a href="..">[up]</a></div>
<div class="toolbar_split"></div>
</xsl:if>
<div class="toolbar_item">
<xsl:if test="string-length(@base) != 0">
<xsl:value-of select="@base"/>
<xsl:text>: </xsl:text>
<xsl:value-of select="@path"/>
</xsl:if>
<xsl:if test="string-length(@base) = 0">
<a href=".."><xsl:value-of select="@path"/></a>
</xsl:if>
</div>
</div>
<table class="index">
<xsl:apply-templates select="updir"/>
<xsl:apply-templates select="dir"/>
<xsl:apply-templates select="file"/>
</table>
<div id="revision">
<xsl:if test="string-length(@base) != 0">
Last revision: <xsl:value-of select="@rev"/>
</xsl:if>
</div>
</body>
</xsl:template>

<xsl:template match="updir">
<tr class="updir">
<td>
<a href="..">..</a>
</td>
</tr>
</xsl:template>

<xsl:template match="dir">
<tr class="dir">
<td>
<a>
<xsl:attribute name="href">
<xsl:value-of select="@href"/>
</xsl:attribute>
<xsl:value-of select="@name"/>
</a>
</td>
</tr>
</xsl:template>

<xsl:template match="file">
<tr class="file">
<td>
<a>
<xsl:attribute name="href">
/svn.php?r=<xsl:value-of select="../@rev"/>&amp;b=<xsl:value-of select="../@base"/>&amp;p=<xsl:value-of select="../@path"/>&amp;f=<xsl:value-of select="@href"/>
</xsl:attribute>
<xsl:value-of select="@name"/>
</a>
</td>
</tr>
</xsl:template>

</xsl:stylesheet>
