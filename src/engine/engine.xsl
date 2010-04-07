<xsl:stylesheet version='1.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>

<xsl:output method="html" version="1.0" encoding="UTF-8" doctype-public="-//W3C//DTD HTML 4.01 Transitional//EN" doctype-system="http://www.w3.org/TR/html4/loose.dtd"/>

<xsl:template match="page">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="author" content="Artem Rodygin"/>
    <meta name="copyright" content="Copyright (C) 2003-2010 by Artem Rodygin"/>
    <link rel="stylesheet" type="text/css" href="../css/etraxis.css"/>
    <link rel="shortcut icon" type="image/x-icon" href="../images/favicon.ico"/>
    <title>
    <xsl:if test="boolean(@title)">
        <xsl:value-of select="@title"/>
        <xsl:text> - </xsl:text>
    </xsl:if>
    <xsl:text>eTraxis</xsl:text>
    </title>
    <script type="text/javascript" src="../scripts/textbox.js"/>
    <script type="text/javascript">
    <xsl:text>function onLoad() { </xsl:text>
    <xsl:if test="boolean(@init)">
        <xsl:value-of select="@init"/>
    </xsl:if>
    <xsl:if test="boolean(@focus)">
        <xsl:text>document.</xsl:text>
        <xsl:value-of select="@focus"/>
        <xsl:text>.focus(); </xsl:text>
    </xsl:if>
    <xsl:if test="boolean(@alert)">
        <xsl:if test="not(boolean(@alert = ''))">
            <xsl:text>alert('</xsl:text>
            <xsl:value-of select="@alert"/>
            <xsl:text>'); </xsl:text>
        </xsl:if>
    </xsl:if>
    <xsl:text>}</xsl:text>
    </script>
    <xsl:apply-templates select="script"/>
    <body onload="onLoad();">
    <table class="container">
    <tr><td>
    <div id="topbox">
        <div id="headerlt"></div>
        <div id="headerrt"></div>
        <div id="header">
            <a class="title"><i>e</i>Traxis</a>
            <a class="version"><xsl:value-of select="@version"/></a>
        </div>
        <xsl:apply-templates select="menu"/>
    </div>
    <div id="content">
        <noscript>
        <p class="banner">
        <xsl:value-of select="@noscript"/>
        </p>
        </noscript>
        <xsl:if test="boolean(@guest)">
            <p class="guestbox">
            <xsl:value-of select="@guest"/>
            </p>
        </xsl:if>
        <xsl:if test="boolean(@banner)">
            <p class="banner">
            <xsl:value-of select="@banner"/>
            </p>
        </xsl:if>
        <xsl:apply-templates select="path"/>
        <xsl:apply-templates select="content"/>
        <div id="footer">
            <xsl:text disable-output-escaping="yes">Copyright &amp;copy; 2003-2010 by </xsl:text>
            <a href="mailto:etraxis@gmail.com">
            <xsl:text>Artem Rodygin</xsl:text>
            </a>
        </div>
    </div>
    </td></tr>
    </table>
    </body>
</xsl:template>

<xsl:template match="script">
    <script type="text/javascript">
    <xsl:if test="boolean(@src)">
        <xsl:attribute name="src">
        <xsl:value-of select="@src"/>
        </xsl:attribute>
    </xsl:if>
    <xsl:if test="not(boolean(@src))">
        <xsl:value-of select="."/>
    </xsl:if>
    </script>
</xsl:template>

<xsl:template match="menu">
    <div id="menult"></div>
    <div id="menurt"></div>
    <div id="menu">
        <xsl:apply-templates select="menuitem"/>
        <xsl:if test="boolean(@user)">
            <a class="user">
            <xsl:value-of select="@user"/>
            </a>
        </xsl:if>
    </div>
</xsl:template>

<xsl:template match="menuitem">
    <a class="menu">
    <xsl:attribute name="href">
    <xsl:value-of select="@url"/>
    </xsl:attribute>
    <xsl:value-of select="."/>
    </a>
</xsl:template>

<xsl:template match="path">
    <div id="crumb">
    <xsl:apply-templates select="pathitem"/>
    </div>
</xsl:template>

<xsl:template match="pathitem">
    <a class="crumb">
    <xsl:attribute name="href">
    <xsl:value-of select="@url"/>
    </xsl:attribute>
    <xsl:value-of select="."/>
    </a>
</xsl:template>

<xsl:template match="content">
    <xsl:apply-templates/>
</xsl:template>

<xsl:template match="form">
    <form target="_parent">
    <xsl:if test="boolean(@method)">
        <xsl:attribute name="method">
        <xsl:value-of select="@method"/>
        </xsl:attribute>
    </xsl:if>
    <xsl:if test="not(boolean(@method))">
        <xsl:attribute name="method">
        <xsl:text>post</xsl:text>
        </xsl:attribute>
    </xsl:if>
    <xsl:attribute name="name">
    <xsl:value-of select="@name"/>
    </xsl:attribute>
    <xsl:attribute name="action">
    <xsl:value-of select="@action"/>
    </xsl:attribute>
    <xsl:if test="boolean(@upload)">
        <xsl:attribute name="enctype">
        <xsl:text>multipart/form-data</xsl:text>
        </xsl:attribute>
        <input type="hidden" name="MAX_FILE_SIZE">
        <xsl:attribute name="value">
        <xsl:value-of select="@upload"/>
        </xsl:attribute>
        </input>
    </xsl:if>
    <xsl:if test="not(boolean(@method = 'get'))">
        <input type="hidden" name="submitted">
        <xsl:attribute name="value">
        <xsl:value-of select="@name"/>
        </xsl:attribute>
        </input>
    </xsl:if>
    <xsl:apply-templates/>
    </form>
</xsl:template>

<xsl:template match="group">
    <fieldset>
    <xsl:if test="boolean(@title)">
        <legend>
        <xsl:if test="boolean(@id)">
            <a class="toggle">
            <xsl:attribute name="id">
            <xsl:text>toggle</xsl:text>
            <xsl:value-of select="@id"/>
            </xsl:attribute>
            <xsl:attribute name="href">
            <xsl:text>javascript:SwitchLayer(</xsl:text>
            <xsl:value-of select="@id"/>
            <xsl:text>);</xsl:text>
            </xsl:attribute>
            <xsl:text>%minus;</xsl:text>
            </a>
        </xsl:if>
        <xsl:value-of select="@title"/>
        </legend>
    </xsl:if>
    <xsl:if test="boolean(@id)">
        <script type="text/javascript">
        <xsl:text>events_list[++events_count] = </xsl:text>
        <xsl:value-of select="@id"/>
        <xsl:text>;</xsl:text>
        </script>
    </xsl:if>
    <div>
    <xsl:if test="boolean(@id)">
        <xsl:attribute name="id">
        <xsl:text>div</xsl:text>
        <xsl:value-of select="@id"/>
        </xsl:attribute>
        <xsl:attribute name="class">
        <xsl:text>groupdiv</xsl:text>
        </xsl:attribute>
        <xsl:attribute name="style">
        <xsl:text>display:block</xsl:text>
        </xsl:attribute>
    </xsl:if>
    <table cellpadding="0" cellspacing="0">
    <xsl:apply-templates select="text|smallbox|editbox|passbox|filebox|checkbox|radios|combobox|listbox|textbox|fieldbox|fieldcheckbox|comment|attachment|image|row|hr"/>
    <tr><td></td><td>
    <xsl:apply-templates select="button|nbsp"/>
    </td></tr>
    </table>
    </div>
    </fieldset>
</xsl:template>

<xsl:template match="text">
    <tr valign="top">
    <xsl:if test="boolean(@label)">
    <td nowrap="">
        <p class="label">
        <xsl:value-of select="@label"/>
        <xsl:text>:</xsl:text>
        </p>
    </td>
    </xsl:if>
    <td class="text" width="100%">
    <xsl:apply-templates/>
    </td>
    </tr>
</xsl:template>

<xsl:template match="smallbox">
    <input class="smallbox" type="text">
    <xsl:attribute name="name">
    <xsl:value-of select="@name"/>
    </xsl:attribute>
    <xsl:attribute name="id">
    <xsl:value-of select="@name"/>
    </xsl:attribute>
    <xsl:attribute name="size">
    <xsl:value-of select="@size"/>
    </xsl:attribute>
    <xsl:attribute name="maxlength">
    <xsl:value-of select="@maxlen"/>
    </xsl:attribute>
    <xsl:if test="boolean(@disabled = 'true')">
        <xsl:attribute name="disabled">
        </xsl:attribute>
    </xsl:if>
    <xsl:if test="boolean(@readonly = 'true')">
        <xsl:attribute name="readonly">
        </xsl:attribute>
    </xsl:if>
    <xsl:attribute name="value">
    <xsl:value-of select="."/>
    </xsl:attribute>
    </input>
</xsl:template>

<xsl:template match="editbox">
    <xsl:if test="boolean(@label)">
        <tr valign="middle">
        <td>
        <label>
        <xsl:if test="boolean(@disabled = 'true')">
            <xsl:attribute name="class">
            <xsl:text>disabled</xsl:text>
            </xsl:attribute>
        </xsl:if>
        <xsl:attribute name="for">
        <xsl:value-of select="@name"/>
        </xsl:attribute>
        <xsl:value-of select="@label"/>
        <xsl:text>:</xsl:text>
        <xsl:if test="boolean(@required)">
            <sup class="required">
            <xsl:text> [</xsl:text>
            <xsl:value-of select="@required"/>
            <xsl:text>]</xsl:text>
            </sup>
        </xsl:if>
        </label>
        </td>
        <td>
        <input class="editbox" type="text">
        <xsl:attribute name="name">
        <xsl:value-of select="@name"/>
        </xsl:attribute>
        <xsl:attribute name="id">
        <xsl:value-of select="@name"/>
        </xsl:attribute>
        <xsl:attribute name="size">
        <xsl:value-of select="@size"/>
        </xsl:attribute>
        <xsl:attribute name="maxlength">
        <xsl:value-of select="@maxlen"/>
        </xsl:attribute>
        <xsl:if test="boolean(@disabled = 'true')">
            <xsl:attribute name="disabled">
            </xsl:attribute>
        </xsl:if>
        <xsl:if test="boolean(@readonly = 'true')">
            <xsl:attribute name="readonly">
            </xsl:attribute>
        </xsl:if>
        <xsl:attribute name="value">
        <xsl:value-of select="."/>
        </xsl:attribute>
        </input>
        </td>
        </tr>
    </xsl:if>
    <xsl:if test="not(boolean(@label))">
        <input class="editbox" type="text">
        <xsl:attribute name="name">
        <xsl:value-of select="@name"/>
        </xsl:attribute>
        <xsl:attribute name="id">
        <xsl:value-of select="@name"/>
        </xsl:attribute>
        <xsl:attribute name="size">
        <xsl:value-of select="@size"/>
        </xsl:attribute>
        <xsl:attribute name="maxlength">
        <xsl:value-of select="@maxlen"/>
        </xsl:attribute>
        <xsl:if test="boolean(@disabled = 'true')">
            <xsl:attribute name="disabled">
            </xsl:attribute>
        </xsl:if>
        <xsl:if test="boolean(@readonly = 'true')">
            <xsl:attribute name="readonly">
            </xsl:attribute>
        </xsl:if>
        <xsl:attribute name="value">
        <xsl:value-of select="."/>
        </xsl:attribute>
        </input>
    </xsl:if>
</xsl:template>

<xsl:template match="passbox">
    <tr valign="middle">
    <td>
    <xsl:if test="boolean(@label)">
        <label>
        <xsl:if test="boolean(@disabled = 'true')">
            <xsl:attribute name="class">
            <xsl:text>disabled</xsl:text>
            </xsl:attribute>
        </xsl:if>
        <xsl:attribute name="for">
        <xsl:value-of select="@name"/>
        </xsl:attribute>
        <xsl:value-of select="@label"/>
        <xsl:text>:</xsl:text>
        <xsl:if test="boolean(@required)">
            <sup class="required">
            <xsl:text> [</xsl:text>
            <xsl:value-of select="@required"/>
            <xsl:text>]</xsl:text>
            </sup>
        </xsl:if>
        </label>
    </xsl:if>
    </td>
    <td>
    <input class="password" type="password">
    <xsl:attribute name="name">
    <xsl:value-of select="@name"/>
    </xsl:attribute>
    <xsl:attribute name="id">
    <xsl:value-of select="@name"/>
    </xsl:attribute>
    <xsl:attribute name="size">
    <xsl:value-of select="@size"/>
    </xsl:attribute>
    <xsl:attribute name="maxlength">
    <xsl:value-of select="@maxlen"/>
    </xsl:attribute>
    <xsl:if test="boolean(@disabled = 'true')">
        <xsl:attribute name="disabled">
        </xsl:attribute>
    </xsl:if>
    <xsl:if test="boolean(@readonly = 'true')">
        <xsl:attribute name="readonly">
        </xsl:attribute>
    </xsl:if>
    <xsl:attribute name="value">
    <xsl:value-of select="."/>
    </xsl:attribute>
    </input>
    </td>
    </tr>
</xsl:template>

<xsl:template match="filebox">
    <tr valign="middle">
    <td>
    <xsl:if test="boolean(@label)">
        <label>
        <xsl:if test="boolean(@disabled = 'true')">
            <xsl:attribute name="class">
            <xsl:text>disabled</xsl:text>
            </xsl:attribute>
        </xsl:if>
        <xsl:attribute name="for">
        <xsl:value-of select="@name"/>
        </xsl:attribute>
        <xsl:value-of select="@label"/>
        <xsl:text>:</xsl:text>
        <xsl:if test="boolean(@required)">
            <sup class="required">
            <xsl:text> [</xsl:text>
            <xsl:value-of select="@required"/>
            <xsl:text>]</xsl:text>
            </sup>
        </xsl:if>
        </label>
    </xsl:if>
    </td>
    <td>
    <input class="editbox" type="file">
    <xsl:attribute name="name">
    <xsl:value-of select="@name"/>
    </xsl:attribute>
    <xsl:attribute name="id">
    <xsl:value-of select="@name"/>
    </xsl:attribute>
    <xsl:attribute name="size">
    <xsl:value-of select="@size"/>
    </xsl:attribute>
    <xsl:if test="boolean(@disabled = 'true')">
        <xsl:attribute name="disabled">
        </xsl:attribute>
    </xsl:if>
    <xsl:if test="boolean(@readonly = 'true')">
        <xsl:attribute name="readonly">
        </xsl:attribute>
    </xsl:if>
    <xsl:attribute name="value">
    <xsl:value-of select="."/>
    </xsl:attribute>
    </input>
    </td>
    </tr>
</xsl:template>

<xsl:template match="checkbox">
    <tr valign="middle">
    <td/>
    <td nowrap="">
    <input type="checkbox" class="checkbox">
    <xsl:attribute name="name">
    <xsl:value-of select="@name"/>
    </xsl:attribute>
    <xsl:attribute name="id">
    <xsl:value-of select="@name"/>
    </xsl:attribute>
    <xsl:if test="boolean(@checked = 'true')">
        <xsl:attribute name="checked">
        </xsl:attribute>
    </xsl:if>
    <xsl:if test="boolean(@disabled = 'true')">
        <xsl:attribute name="disabled">
        </xsl:attribute>
    </xsl:if>
    <xsl:if test="boolean(@readonly = 'true')">
        <xsl:attribute name="onclick">
        <xsl:choose>
            <xsl:when test="boolean(@checked = 'true')">
                <xsl:text>this.checked = true</xsl:text>
            </xsl:when>
            <xsl:otherwise>
                <xsl:text>this.checked = false</xsl:text>
            </xsl:otherwise>
        </xsl:choose>
        </xsl:attribute>
    </xsl:if>
    </input>
    <label>
    <xsl:if test="boolean(@disabled = 'true')">
        <xsl:attribute name="class">
            <xsl:text>disabled</xsl:text>
        </xsl:attribute>
    </xsl:if>
    <xsl:if test="not(boolean(@disabled = 'true'))">
        <xsl:attribute name="class">
            <xsl:text>enabled</xsl:text>
        </xsl:attribute>
    </xsl:if>
    <xsl:attribute name="for">
    <xsl:value-of select="@name"/>
    </xsl:attribute>
    <xsl:value-of select="."/>
    </label>
    </td>
    </tr>
</xsl:template>

<xsl:template match="radios">
    <tr valign="middle">
    <td valign="top">
    <xsl:if test="boolean(@label)">
        <label>
        <xsl:if test="boolean(@disabled = 'true')">
            <xsl:attribute name="class">
            <xsl:text>disabled</xsl:text>
            </xsl:attribute>
        </xsl:if>
        <xsl:attribute name="for">
        <xsl:value-of select="@name"/>
        </xsl:attribute>
        <xsl:value-of select="@label"/>
        <xsl:text>:</xsl:text>
        </label>
    </xsl:if>
    </td>
    <td>
    <xsl:apply-templates select="radio"/>
    </td>
    </tr>
</xsl:template>

<xsl:template match="radio">
    <input class="radio" type="radio">
    <xsl:attribute name="name">
    <xsl:value-of select="@name"/>
    </xsl:attribute>
    <xsl:attribute name="id">
    <xsl:value-of select="@name"/>
    </xsl:attribute>
    <xsl:attribute name="value">
    <xsl:value-of select="@value"/>
    </xsl:attribute>
    <xsl:if test="boolean(@checked = 'true')">
        <xsl:attribute name="checked">
        </xsl:attribute>
    </xsl:if>
    <xsl:if test="boolean(@disabled = 'true')">
        <xsl:attribute name="disabled">
        </xsl:attribute>
    </xsl:if>
    <xsl:if test="boolean(@readonly = 'true')">
        <xsl:attribute name="readonly">
        </xsl:attribute>
    </xsl:if>
    </input>
    <a>
    <xsl:if test="boolean(@disabled = 'true')">
        <xsl:attribute name="class">
            <xsl:text>disabled</xsl:text>
        </xsl:attribute>
    </xsl:if>
    <xsl:if test="not(boolean(@disabled = 'true'))">
        <xsl:attribute name="class">
            <xsl:text>enabled</xsl:text>
        </xsl:attribute>
    </xsl:if>
    <xsl:value-of select="."/>
    </a>
    <br/>
</xsl:template>

<xsl:template match="combobox">
    <xsl:if test="boolean(@label)">
        <tr valign="middle">
        <td>
        <label>
        <xsl:if test="boolean(@disabled = 'true')">
            <xsl:attribute name="class">
            <xsl:text>disabled</xsl:text>
            </xsl:attribute>
        </xsl:if>
        <xsl:attribute name="for">
        <xsl:value-of select="@name"/>
        </xsl:attribute>
        <xsl:value-of select="@label"/>
        <xsl:text>:</xsl:text>
        <xsl:if test="boolean(@required)">
            <sup class="required">
            <xsl:text> [</xsl:text>
            <xsl:value-of select="@required"/>
            <xsl:text>]</xsl:text>
            </sup>
        </xsl:if>
        </label>
        </td>
        <td>
        <select>
        <xsl:attribute name="name">
        <xsl:value-of select="@name"/>
        </xsl:attribute>
        <xsl:attribute name="id">
        <xsl:value-of select="@name"/>
        </xsl:attribute>
        <xsl:if test="boolean(@extended = 'true')">
            <xsl:attribute name="class">
            <xsl:text>extended</xsl:text>
            </xsl:attribute>
        </xsl:if>
        <xsl:if test="boolean(@disabled = 'true')">
            <xsl:attribute name="disabled">
            </xsl:attribute>
        </xsl:if>
        <xsl:apply-templates select="listitem"/>
        </select>
        </td>
        </tr>
    </xsl:if>
    <xsl:if test="not(boolean(@label))">
        <select>
        <xsl:attribute name="name">
        <xsl:value-of select="@name"/>
        </xsl:attribute>
        <xsl:attribute name="id">
        <xsl:value-of select="@name"/>
        </xsl:attribute>
        <xsl:if test="boolean(@extended = 'true')">
            <xsl:attribute name="class">
            <xsl:text>extended</xsl:text>
            </xsl:attribute>
        </xsl:if>
        <xsl:if test="boolean(@disabled = 'true')">
            <xsl:attribute name="disabled">
            </xsl:attribute>
        </xsl:if>
        <xsl:apply-templates select="listitem"/>
        </select>
    </xsl:if>
</xsl:template>

<xsl:template match="listbox">
    <tr valign="middle">
    <td valign="top">
    <xsl:if test="boolean(@label)">
        <label>
        <xsl:if test="boolean(@disabled = 'true')">
            <xsl:attribute name="class">
            <xsl:text>disabled</xsl:text>
            </xsl:attribute>
        </xsl:if>
        <xsl:attribute name="for">
        <xsl:value-of select="@name"/>
        </xsl:attribute>
        <xsl:value-of select="@label"/>
        <xsl:text>:</xsl:text>
        <xsl:if test="boolean(@required)">
            <sup class="required">
            <xsl:text> [</xsl:text>
            <xsl:value-of select="@required"/>
            <xsl:text>]</xsl:text>
            </sup>
        </xsl:if>
        </label>
    </xsl:if>
    </td>
    <td>
    <select>
    <xsl:if test="boolean(@dualbox = 'true')">
        <xsl:attribute name="class">
        <xsl:text>dualbox</xsl:text>
        </xsl:attribute>
    </xsl:if>
    <xsl:if test="not(boolean(@dualbox = 'true'))">
        <xsl:attribute name="class">
        <xsl:text>list</xsl:text>
        </xsl:attribute>
    </xsl:if>
    <xsl:attribute name="name">
    <xsl:value-of select="@name"/>
    </xsl:attribute>
    <xsl:attribute name="id">
    <xsl:value-of select="@name"/>
    </xsl:attribute>
    <xsl:attribute name="size">
    <xsl:value-of select="@size"/>
    </xsl:attribute>
    <xsl:if test="boolean(@disabled = 'true')">
        <xsl:attribute name="disabled">
        </xsl:attribute>
    </xsl:if>
    <xsl:if test="boolean(@multiple = 'true')">
        <xsl:attribute name="multiple">
        </xsl:attribute>
    </xsl:if>
    <xsl:if test="boolean(@action)">
        <xsl:attribute name="onchange">
        <xsl:value-of select="@action"/>
        </xsl:attribute>
    </xsl:if>
    <xsl:apply-templates select="listitem"/>
    </select>
    </td>
    </tr>
</xsl:template>

<xsl:template match="listitem">
    <option>
    <xsl:attribute name="value">
    <xsl:value-of select="@value"/>
    </xsl:attribute>
    <xsl:if test="boolean(@selected = 'true')">
        <xsl:attribute name="selected">
        </xsl:attribute>
    </xsl:if>
    <xsl:value-of select="."/>
    </option>
</xsl:template>

<xsl:template match="textbox">
    <tr valign="middle">
    <td valign="top">
    <xsl:if test="boolean(@label)">
        <label>
        <xsl:attribute name="for">
        <xsl:value-of select="@name"/>
        </xsl:attribute>
        <xsl:value-of select="@label"/>
        <xsl:text>:</xsl:text>
        <xsl:if test="boolean(@required)">
            <sup class="required">
            <xsl:text> [</xsl:text>
            <xsl:value-of select="@required"/>
            <xsl:text>]</xsl:text>
            </sup>
        </xsl:if>
        </label>
    </xsl:if>
    </td>
    <td>
    <textarea>
    <xsl:if test="boolean(@dualbox = 'true')">
        <xsl:attribute name="class">
        <xsl:text>dualbox</xsl:text>
        </xsl:attribute>
    </xsl:if>
    <xsl:attribute name="name">
    <xsl:value-of select="@name"/>
    </xsl:attribute>
    <xsl:attribute name="id">
    <xsl:value-of select="@name"/>
    </xsl:attribute>
    <xsl:attribute name="cols">
    <xsl:value-of select="@width"/>
    </xsl:attribute>
    <xsl:attribute name="rows">
    <xsl:value-of select="@height"/>
    </xsl:attribute>
    <xsl:if test="boolean(@disabled = 'true')">
        <xsl:attribute name="disabled">
        </xsl:attribute>
    </xsl:if>
    <xsl:if test="boolean(@readonly = 'true')">
        <xsl:attribute name="readonly">
        </xsl:attribute>
    </xsl:if>
    <xsl:if test="boolean(@resizeable = 'true')">
        <xsl:attribute name="style">
        <xsl:text>overflow-y:hidden;</xsl:text>
        </xsl:attribute>
    </xsl:if>
    <xsl:attribute name="onchange">
        <xsl:text>onTextBox('</xsl:text>
        <xsl:if test="boolean(@form)">
            <xsl:value-of select="@form"/>
        </xsl:if>
        <xsl:if test="not(boolean(@form))">
            <xsl:text>forms[0]</xsl:text>
        </xsl:if>
        <xsl:text>.</xsl:text>
        <xsl:value-of select="@name"/>
        <xsl:text>',</xsl:text>
        <xsl:value-of select="@maxlen"/>
        <xsl:text>,</xsl:text>
        <xsl:if test="boolean(@resizeable)">
            <xsl:value-of select="@resizeable"/>
        </xsl:if>
        <xsl:if test="not(boolean(@resizeable))">
            <xsl:text>false</xsl:text>
        </xsl:if>
        <xsl:text>,</xsl:text>
        <xsl:value-of select="@height"/>
        <xsl:text>);</xsl:text>
    </xsl:attribute>
    <xsl:attribute name="onkeydown">
        <xsl:text>onTextBox('</xsl:text>
        <xsl:if test="boolean(@form)">
            <xsl:value-of select="@form"/>
        </xsl:if>
        <xsl:if test="not(boolean(@form))">
            <xsl:text>forms[0]</xsl:text>
        </xsl:if>
        <xsl:text>.</xsl:text>
        <xsl:value-of select="@name"/>
        <xsl:text>',</xsl:text>
        <xsl:value-of select="@maxlen"/>
        <xsl:text>,</xsl:text>
        <xsl:if test="boolean(@resizeable)">
            <xsl:value-of select="@resizeable"/>
        </xsl:if>
        <xsl:if test="not(boolean(@resizeable))">
            <xsl:text>false</xsl:text>
        </xsl:if>
        <xsl:text>,</xsl:text>
        <xsl:value-of select="@height"/>
        <xsl:text>);</xsl:text>
    </xsl:attribute>
    <xsl:attribute name="onkeyup">
        <xsl:text>onTextBox('</xsl:text>
        <xsl:if test="boolean(@form)">
            <xsl:value-of select="@form"/>
        </xsl:if>
        <xsl:if test="not(boolean(@form))">
            <xsl:text>forms[0]</xsl:text>
        </xsl:if>
        <xsl:text>.</xsl:text>
        <xsl:value-of select="@name"/>
        <xsl:text>',</xsl:text>
        <xsl:value-of select="@maxlen"/>
        <xsl:text>,</xsl:text>
        <xsl:if test="boolean(@resizeable)">
            <xsl:value-of select="@resizeable"/>
        </xsl:if>
        <xsl:if test="not(boolean(@resizeable))">
            <xsl:text>false</xsl:text>
        </xsl:if>
        <xsl:text>,</xsl:text>
        <xsl:value-of select="@height"/>
        <xsl:text>);</xsl:text>
    </xsl:attribute>
    <xsl:value-of select="."/>
    </textarea>
    </td>
    </tr>
</xsl:template>

<xsl:template match="dualbox">
    <table cellpadding="0" cellspacing="0">
    <tr>
    <td valign="top">
    <xsl:apply-templates select="dualleft"/>
    </td>
    <td valign="middle">
    <xsl:if test="boolean(@nobuttons = 'true')">
        <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
    </xsl:if>
    <xsl:if test="not(boolean(@nobuttons = 'true'))">
        <input class="button dualbutton" type="button" onclick="lform.submit();" value="%gt;%gt;"/><br/>
        <input class="button dualbutton" type="button" onclick="rform.submit();" value="%lt;%lt;"/><br/>
    </xsl:if>
    </td>
    <td valign="top">
    <xsl:apply-templates select="dualright"/>
    </td>
    </tr>
    </table>
</xsl:template>

<xsl:template match="dualleft">
    <form method="post" target="_parent" name="lform">
    <xsl:attribute name="action">
    <xsl:value-of select="@action"/>
    </xsl:attribute>
    <input type="hidden" name="submitted" value="lform"/>
    <xsl:apply-templates select="group"/>
    </form>
</xsl:template>

<xsl:template match="dualright">
    <form method="post" target="_parent" name="rform">
    <xsl:attribute name="action">
    <xsl:value-of select="@action"/>
    </xsl:attribute>
    <input type="hidden" name="submitted" value="rform"/>
    <xsl:apply-templates select="group"/>
    </form>
</xsl:template>

<xsl:template match="fieldbox">
    <tr valign="middle">
    <td>
    <input type="checkbox" class="checkbox">
    <xsl:attribute name="name">
    <xsl:value-of select="@name"/>
    </xsl:attribute>
    <xsl:attribute name="id">
    <xsl:value-of select="@name"/>
    </xsl:attribute>
    <xsl:if test="boolean(@used = 'true')">
        <xsl:attribute name="checked">
        </xsl:attribute>
    </xsl:if>
    </input>
    </td>
    <td nowrap="true">
    <label>
    <xsl:attribute name="for">
    <xsl:value-of select="@name"/>
    </xsl:attribute>
    <xsl:value-of select="@label"/>
    <xsl:text>:</xsl:text>
    </label>
    </td>
    <td>
    <xsl:apply-templates select="smallbox|editbox|combobox"/>
    <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
    </td>
    </tr>
</xsl:template>

<xsl:template match="fieldcheckbox">
    <tr valign="middle">
    <td>
    <input type="checkbox" class="checkbox">
    <xsl:attribute name="name">
    <xsl:value-of select="@name"/>
    </xsl:attribute>
    <xsl:attribute name="id">
    <xsl:value-of select="@name"/>
    </xsl:attribute>
    <xsl:if test="boolean(@used = 'true')">
        <xsl:attribute name="checked">
        </xsl:attribute>
    </xsl:if>
    </input>
    </td>
    <td nowrap="true">
    <label>
    <xsl:attribute name="for">
    <xsl:value-of select="@name"/>
    </xsl:attribute>
    <xsl:value-of select="@label"/>
    <xsl:text>:</xsl:text>
    </label>
    </td>
    <td>
    <input type="checkbox" class="checkbox">
    <xsl:attribute name="name">
    <xsl:text>check_</xsl:text>
    <xsl:value-of select="@name"/>
    </xsl:attribute>
    <xsl:attribute name="id">
    <xsl:text>check_</xsl:text>
    <xsl:value-of select="@name"/>
    </xsl:attribute>
    <xsl:if test="boolean(@checked = 'true')">
        <xsl:attribute name="checked">
        </xsl:attribute>
    </xsl:if>
    </input>
    </td>
    </tr>
</xsl:template>

<xsl:template match="button">
    <input class="button">
    <xsl:attribute name="type">
        <xsl:choose>
            <xsl:when test="boolean(@default = 'true')">
                <xsl:text>submit</xsl:text>
            </xsl:when>
            <xsl:otherwise>
                <xsl:text>button</xsl:text>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:attribute>
    <xsl:if test="boolean(@name)">
        <xsl:attribute name="id">
        <xsl:value-of select="@name"/>
        </xsl:attribute>
        <xsl:attribute name="name">
        <xsl:value-of select="@name"/>
        </xsl:attribute>
    </xsl:if>
    <xsl:attribute name="onclick">
    <xsl:if test="boolean(@prompt)">
        <xsl:text>if (confirm('</xsl:text>
        <xsl:value-of select="@prompt"/>
        <xsl:text>')) </xsl:text>
        <xsl:text>{</xsl:text>
    </xsl:if>
    <xsl:choose>
        <xsl:when test="boolean(@url)">
            <xsl:text>window.open('</xsl:text>
            <xsl:value-of select="@url"/>
            <xsl:text>','_parent');</xsl:text>
        </xsl:when>
        <xsl:when test="boolean(@action)">
            <xsl:value-of select="@action"/>
        </xsl:when>
    </xsl:choose>
    <xsl:if test="boolean(@prompt)">
        <xsl:text>}</xsl:text>
    </xsl:if>
    </xsl:attribute>
    <xsl:if test="boolean(@disabled = 'true')">
        <xsl:attribute name="disabled">
        </xsl:attribute>
    </xsl:if>
    <xsl:attribute name="value">
    <xsl:text> </xsl:text>
    <xsl:value-of select="."/>
    <xsl:text> </xsl:text>
    </xsl:attribute>
    </input>
</xsl:template>

<xsl:template match="note">
    <p>
    <img src="../images/note.gif" width="16" height="16" alt="Note:"/>
    <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
    <xsl:value-of select="."/>
    </p>
</xsl:template>

<xsl:template match="list">
    <table class="list" id="list" cellpadding="0" cellspacing="0">
    <xsl:apply-templates select="hrow"/>
    <xsl:apply-templates select="row"/>
    </table>
    <xsl:apply-templates select="bookmarks"/>
</xsl:template>

<xsl:template match="hrow">
    <thead>
    <tr>
    <td/>
    <xsl:apply-templates select="hcell"/>
    <td/>
    </tr>
    </thead>
</xsl:template>

<xsl:template match="hcell">
    <td nowrap="">
    <xsl:if test="boolean(@width)">
        <xsl:attribute name="width">
        <xsl:value-of select="@width"/>
        <xsl:text>%</xsl:text>
        </xsl:attribute>
    </xsl:if>
    <a>
    <xsl:if test="boolean(@url)">
        <xsl:attribute name="href">
        <xsl:value-of select="@url"/>
        </xsl:attribute>
    </xsl:if>
    <p class="header">
    <xsl:attribute name="align">
    <xsl:value-of select="@align"/>
    </xsl:attribute>
    <xsl:value-of select="."/>
    </p>
    </a>
    </td>
</xsl:template>

<xsl:template match="row">
    <tr valign="top">
    <xsl:if test="boolean(@url)">
        <xsl:attribute name="class">
        <xsl:text>row</xsl:text>
        </xsl:attribute>
        <xsl:attribute name="onmouseover">
        <xsl:text>this.className='hrow'</xsl:text>
        </xsl:attribute>
        <xsl:attribute name="onmouseout">
        <xsl:text>this.className='row'</xsl:text>
        </xsl:attribute>
    </xsl:if>
    <a>
    <xsl:if test="boolean(@url)">
        <xsl:attribute name="href">
        <xsl:value-of select="@url"/>
        </xsl:attribute>
    </xsl:if>
    <td class="left"/>
    <xsl:apply-templates select="cell"/>
    <td class="right"/>
    </a>
    </tr>
</xsl:template>

<xsl:template match="cell">
    <td>
    <xsl:if test="not(boolean(@wrap = 'true'))">
        <xsl:attribute name="nowrap">
        </xsl:attribute>
    </xsl:if>
    <xsl:if test="boolean(@url)">
        <a>
        <xsl:attribute name="href">
        <xsl:value-of select="@url"/>
        </xsl:attribute>
        <p>
        <xsl:attribute name="align">
        <xsl:value-of select="@align"/>
        </xsl:attribute>
        <xsl:attribute name="class">
        <xsl:choose>
            <xsl:when test="boolean(@style = 'hot')">
                <xsl:text>hot</xsl:text>
            </xsl:when>
            <xsl:when test="boolean(@style = 'cold')">
                <xsl:text>cold</xsl:text>
            </xsl:when>
            <xsl:when test="boolean(@style = 'closed')">
                <xsl:text>closed</xsl:text>
            </xsl:when>
            <xsl:otherwise>
                <xsl:text>row</xsl:text>
            </xsl:otherwise>
        </xsl:choose>
        </xsl:attribute>
        <xsl:choose>
            <xsl:when test="boolean(@bold = 'true')">
                <b>
                <xsl:apply-templates/>
                </b>
            </xsl:when>
            <xsl:otherwise>
                <xsl:apply-templates/>
            </xsl:otherwise>
        </xsl:choose>
        </p>
        </a>
    </xsl:if>
    <xsl:if test="not(boolean(@url))">
        <p class="row">
        <xsl:attribute name="align">
        <xsl:value-of select="@align"/>
        </xsl:attribute>
        <xsl:apply-templates/>
        </p>
    </xsl:if>
    </td>
</xsl:template>

<xsl:template match="bookmarks">
    <table class="bookmark" cellpadding="0" cellspacing="0">
    <tr>
    <xsl:apply-templates select="bookmark|ibookmark"/>
    <td class="total">
    <p class="bookmark">
    <xsl:value-of select="@total"/>
    </p>
    </td>
    </tr>
    </table>
</xsl:template>

<xsl:template match="bookmark">
    <td>
    <a>
    <xsl:attribute name="href">
    <xsl:value-of select="@url"/>
    </xsl:attribute>
    <p class="bookmark">
    <xsl:value-of select="."/>
    </p>
    </a>
    </td>
</xsl:template>

<xsl:template match="ibookmark">
    <td class="bookmark">
    <a>
    <xsl:attribute name="href">
    <xsl:value-of select="@url"/>
    </xsl:attribute>
    <p class="bookmark">
    <xsl:value-of select="."/>
    </p>
    </a>
    </td>
</xsl:template>

<xsl:template match="record">
    <a>
    <xsl:attribute name="href">
    <xsl:text>view.php?id=</xsl:text>
    <xsl:value-of select="@id"/>
    </xsl:attribute>
    <xsl:value-of select="."/>
    </a>
</xsl:template>

<xsl:template match="searchres">
    <span class="searchres">
    <xsl:apply-templates/>
    </span>
</xsl:template>

<xsl:template match="comment">
    <tr>
    <td class="comment" colspan="2">
    <xsl:if test="boolean(@confidential)">
    <p class="hot">
    <xsl:value-of select="@confidential"/>
    </p>
    </xsl:if>
    <xsl:apply-templates/>
    </td>
    </tr>
</xsl:template>

<xsl:template match="attachment">
    <tr>
    <td>
    <a class="attachment">
    <xsl:attribute name="href">
    <xsl:value-of select="@url"/>
    </xsl:attribute>
    <xsl:attribute name="type">
    <xsl:value-of select="@type"/>
    </xsl:attribute>
    <img src="../images/attach.gif" width="16" height="16" border="0"/>
    <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
    <xsl:value-of select="."/>
    <xsl:text> (</xsl:text>
    <xsl:value-of select="@size"/>
    <xsl:text>)</xsl:text>
    </a>
    </td>
    </tr>
</xsl:template>

<xsl:template match="image">
    <tr>
    <td>
    <img border="0">
    <xsl:attribute name="src">
    <xsl:value-of select="."/>
    </xsl:attribute>
    </img>
    </td>
    </tr>
</xsl:template>

<xsl:template match="hr">
    <tr>
    <td colspan="2">
    <hr/>
    </td>
    </tr>
</xsl:template>

<xsl:template match="nbsp">
    <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
</xsl:template>

<xsl:template match="br">
    <br/>
</xsl:template>

<xsl:template match="url">
    <a target="_blank">
    <xsl:attribute name="href">
    <xsl:value-of select="@address"/>
    </xsl:attribute>
    <xsl:apply-templates/>
    </a>
</xsl:template>

<xsl:template match="b|i|u|s|sub|sup|h1|h2|h3|h4|h5|h6|ol|ul|li|q">
    <xsl:copy>
    <xsl:apply-templates/>
    </xsl:copy>
</xsl:template>

<xsl:template match="div">
    <div>
    <xsl:attribute name="style">
    <xsl:value-of select="@style"/>
    </xsl:attribute>
    <xsl:apply-templates/>
    </div>
</xsl:template>

<xsl:template match="span">
    <span>
    <xsl:attribute name="style">
    <xsl:value-of select="@style"/>
    </xsl:attribute>
    <xsl:apply-templates/>
    </span>
</xsl:template>

<xsl:template match="pre">
    <pre>
    <xsl:attribute name="style">
    <xsl:value-of select="@style"/>
    </xsl:attribute>
    <xsl:apply-templates/>
    </pre>
</xsl:template>

</xsl:stylesheet>
