<xsl:stylesheet version='1.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>

<xsl:output method="text" version="1.0" encoding="UTF-8"/>

<xsl:template match="page">
    <xsl:apply-templates select="content"/>
    <xsl:text>--------------------------------------------------------------------------------&#10;</xsl:text>
</xsl:template>

<xsl:template match="content">
    <xsl:apply-templates select="group"/>
</xsl:template>

<xsl:template match="group">
    <xsl:if test="boolean(@title)">
        <xsl:text>--------------------------------------------------------------------------------&#10;</xsl:text>
        <xsl:value-of select="@title"/>
        <xsl:text>&#10;</xsl:text>
        <xsl:text>--------------------------------------------------------------------------------&#10;</xsl:text>
    </xsl:if>
    <xsl:apply-templates select="text|comment|attachment|row"/>
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

<xsl:template match="row">
    <xsl:apply-templates select="cell"/>
    <xsl:text>&#10;</xsl:text>
</xsl:template>

<xsl:template match="cell">
    <xsl:value-of select="."/>
    <xsl:text>&#9;</xsl:text>
</xsl:template>

<xsl:template match="record">
    <xsl:value-of select="."/>
</xsl:template>

<xsl:template match="searchres">
    <xsl:apply-templates/>
</xsl:template>

<xsl:template match="comment">
    <xsl:if test="boolean(@confidential)">
    <xsl:value-of select="@confidential"/>
    <xsl:text>&#10;</xsl:text>
    </xsl:if>
    <xsl:apply-templates/>
    <xsl:text>&#10;</xsl:text>
</xsl:template>

<xsl:template match="attachment">
    <xsl:value-of select="."/>
    <xsl:text> (</xsl:text>
    <xsl:value-of select="@size"/>
    <xsl:text>)&#10;</xsl:text>
</xsl:template>

<xsl:template match="br">
    <xsl:text>&#10;</xsl:text>
</xsl:template>

<xsl:template match="url">
    <xsl:value-of select="."/>
</xsl:template>

<xsl:template match="li">
    <xsl:choose>
       <xsl:when test="name(parent::node()) = 'ol'">
           <xsl:value-of select="position()"/>
           <xsl:text>. </xsl:text>
       </xsl:when>
       <xsl:otherwise>
           <xsl:text>- </xsl:text>
       </xsl:otherwise>
    </xsl:choose>
    <xsl:value-of select="."/>
    <xsl:text>&#10;</xsl:text>
</xsl:template>

</xsl:stylesheet>
