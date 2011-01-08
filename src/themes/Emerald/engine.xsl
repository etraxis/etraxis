<!--
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

  eTraxis - Records tracking web-based system
  Copyright (C) 2005-2010  Artem Rodygin

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

<xsl:template match="container">
    <xsl:apply-templates/>
</xsl:template>

<xsl:template match="page">
    <html>
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta http-equiv="Content-Style-Type" content="text/css"/>
    <meta http-equiv="Content-Script-Type" content="text/javascript"/>
    <meta name="author" content="Artem Rodygin"/>
    <meta name="copyright" content="Copyright (C) 2003-2010 by Artem Rodygin"/>
    <link rel="shortcut icon" type="image/x-icon" href="../images/favicon.ico"/>
    <xsl:apply-templates select="css"/>
    <title>
        <xsl:value-of select="@title"/>
        <xsl:text> - eTraxis</xsl:text>
    </title>
    </head>
    <body>
    <script type="text/javascript" src="../scripts/get.php?name=jquery.js"></script>
    <script type="text/javascript" src="../scripts/get.php?name=jquery.TextareaLineCount.js"></script>
    <script type="text/javascript" src="../scripts/get.php?name=jquery.ui.js"></script>
    <script type="text/javascript" src="../scripts/get.php?name=jquery.ui.dp.res.js"></script>
    <script type="text/javascript" src="../scripts/get.php?name=etraxis.js"></script>
    <xsl:apply-templates select="script"/>
    <script type="text/javascript">
    $(document).ready(function() {
    $("#messagebox").dialog({autoOpen:false,modal:true,resizable:false});
    <xsl:apply-templates select="scriptonreadyitem"/>
    });
    </script>
    <div id="messagebox"></div>
    <div id="mainmenu"><xsl:apply-templates select="mainmenu"/></div>
    <div id="toolbar">
        <div class="toolbarsplitt spacer"></div>
        <xsl:apply-templates select="contextmenu"/>
        <div id="search">
            <div id="search_box">
                <form name="searchform" method="get" target="_parent" action="../records/index.php">
                <input type="text" class="search" name="search" maxlength="100">
                <xsl:attribute name="value">
                <xsl:value-of select="@last_search"/>
                </xsl:attribute>
                <xsl:attribute name="onfocus">
                <xsl:text>clear_topline(this, '</xsl:text>
                <xsl:value-of select="@search"/>
                <xsl:text>')</xsl:text>
                </xsl:attribute>
                <xsl:attribute name="onblur">
                <xsl:text>reset_topline(this, '</xsl:text>
                <xsl:value-of select="@search"/>
                <xsl:text>')</xsl:text>
                </xsl:attribute>
                </input>
                </form>
            </div>
            <div id="search_button" onclick="document.searchform.submit()"></div>
        </div>
        <div class="toolbarsplitt"></div>
        <div id="quickfind">
            <div id="quickfind_box">
                <form name="quickfindform" method="get" target="_parent" action="../records/view.php">
                <input type="text" class="quickfind" name="id" maxlength="10" value="ID" onfocus="clear_topline(this, 'ID')" onblur="reset_topline(this, 'ID')"/>
                </form>
            </div>
            <div id="quickfind_button" onclick="document.quickfindform.submit()"></div>
        </div>
        <div class="toolbarsplitt"></div>
        <div id="breadcrumb"><xsl:apply-templates select="breadcrumbs"/></div>
        <div id="logout-wrap">
            <div class="toolbarsplitt"></div>
            <div id="current_username">
            <xsl:value-of select="@username"/>
            </div>
            <div class="toolbarsplitt"></div>
            <div id="logout" onclick="onLogoutButton()">
            <xsl:value-of select="@logout"/>
            </div>
        </div>
    </div>
    <div id="contentwrapper">
        <noscript>
            <div id="noscript">JavaScript must be enabled.</div>
        </noscript>
        <div id="banner">
        <xsl:value-of select="@banner"/>
        </div>
        <xsl:apply-templates select="tabs|content"/>
        <div id="copyright">
        <a href="http://code.google.com/p/etraxis/" target="_blank">
        <xsl:text disable-output-escaping="yes">Copyright &amp;copy; 2003-2010 by Artem Rodygin &amp;minus; </xsl:text><xsl:value-of select="@version"/>
        </a>
        </div>
    </div>
    </body>
    </html>
</xsl:template>

<xsl:template match="css">
    <link rel="stylesheet" type="text/css">
    <xsl:attribute name="href">
    <xsl:value-of select="."/>
    </xsl:attribute>
    </link>
</xsl:template>

<xsl:template match="content">
    <div>
    <xsl:attribute name="id">
        <xsl:choose>
           <xsl:when test="name(parent::node()) = 'tabs'">
               <xsl:text>tabbed_content</xsl:text>
           </xsl:when>
           <xsl:otherwise>
               <xsl:text>simple_content</xsl:text>
           </xsl:otherwise>
        </xsl:choose>
    </xsl:attribute>
    <xsl:apply-templates/>
    </div>
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

<!-- Script Functions executing document.ready -->

<xsl:template match="scriptonreadyitem">
    <xsl:value-of select="."/>
</xsl:template>

<!-- Menu -->

<xsl:template match="mainmenu">
    <ul class="mainmenu">
    <li class="mainmenu-splitter"></li>
    <xsl:apply-templates select="menuitem"/>
    <li class="mainmenu-logo">
    <a target="_blank">
    <xsl:attribute name="href">
    <xsl:value-of select="@site"/>
    </xsl:attribute>
    <img alt="">
    <xsl:attribute name="src">
    <xsl:value-of select="@logo"/>
    </xsl:attribute>
    </img>
    </a>
    </li>
    </ul>
</xsl:template>

<xsl:template match="contextmenu">
    <div id="contextmenu">
    <ul id="toolbarcontextmenu">
    <li class="toolbaritem">
    <div id="dropdownicon"></div>
    <ul class="contextmenu">
    <xsl:apply-templates select="submenu|menuitem"/>
    </ul>
    </li>
    </ul>
    </div>
    <div class="toolbarsplitt"></div>
</xsl:template>

<xsl:template match="submenu">
    <xsl:variable name="id" select="generate-id()"/>
    <li>
    <xsl:attribute name="id">
    <xsl:text>item</xsl:text>
    <xsl:value-of select="$id"/>
    </xsl:attribute>
    <xsl:attribute name="onclick">
    <xsl:text>toggle_menu('</xsl:text>
    <xsl:value-of select="$id"/>
    <xsl:text>')</xsl:text>
    </xsl:attribute>
    <xsl:attribute name="class">
    <xsl:choose>
        <xsl:when test="boolean(@expanded = 'true')">
            <xsl:text>menuitem_m</xsl:text>
        </xsl:when>
        <xsl:otherwise>
            <xsl:text>menuitem_p</xsl:text>
        </xsl:otherwise>
    </xsl:choose>
    </xsl:attribute>
    <a class="menuitem">
    <xsl:attribute name="href">
    <xsl:value-of select="@url"/>
    </xsl:attribute>
    <xsl:value-of select="@text"/>
    </a>
    </li>
    <li>
    <ul class="submenu">
    <xsl:attribute name="id">
    <xsl:text>menu</xsl:text>
    <xsl:value-of select="$id"/>
    </xsl:attribute>
    <xsl:attribute name="style">
    <xsl:choose>
        <xsl:when test="boolean(@expanded = 'true')">
            <xsl:text>display:block</xsl:text>
        </xsl:when>
        <xsl:otherwise>
            <xsl:text>display:none</xsl:text>
        </xsl:otherwise>
    </xsl:choose>
    </xsl:attribute>
    <xsl:apply-templates select="submenu|menuitem"/>
    </ul>
    </li>
</xsl:template>

<xsl:template match="menuitem">
    <xsl:choose>
        <xsl:when test="name(parent::node()) = 'mainmenu'">
            <li>
            <xsl:attribute name="class">
            <xsl:text>menuitem</xsl:text>
            </xsl:attribute>
            <a class="menuitem">
            <xsl:choose>
                <xsl:when test="boolean(@url)">
                    <xsl:attribute name="href">
                    <xsl:value-of select="@url"/>
                    </xsl:attribute>
                    <xsl:value-of select="."/>
                </xsl:when>
                <xsl:otherwise>
                    <i>
                    <xsl:value-of select="."/>
                    </i>
                </xsl:otherwise>
            </xsl:choose>
            </a>
            </li>
            <li class="mainmenu-splitter"></li>
        </xsl:when>
        <xsl:otherwise>
            <li>
            <xsl:attribute name="class">
                <xsl:choose>
                    <xsl:when test="name(parent::node()) = 'contextmenu'">
                        <xsl:text>menuitem_b</xsl:text>
                    </xsl:when>
                    <xsl:when test="name(parent::node()) = 'submenu'">
                        <xsl:text>menuitem_b</xsl:text>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:text>menuitem</xsl:text>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:attribute>
            <a class="menuitem">
            <xsl:choose>
                <xsl:when test="boolean(@url)">
                    <xsl:attribute name="href">
                    <xsl:value-of select="@url"/>
                    </xsl:attribute>
                    <xsl:value-of select="."/>
                </xsl:when>
                <xsl:otherwise>
                    <i>
                    <xsl:value-of select="."/>
                    </i>
                </xsl:otherwise>
            </xsl:choose>
            </a>
            </li>
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<!-- Breadcrumbs -->

<xsl:template match="breadcrumbs">
    <ul class="breadcrumbs">
    <xsl:apply-templates select="breadcrumb"/>
    </ul>
</xsl:template>

<xsl:template match="breadcrumb">
    <xsl:if test="position() != 1">
    <li class="splitter">
    <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
    </li>
    </xsl:if>
    <li class="breadcrumb">
    <a class="breadcrumb">
    <xsl:attribute name="href">
    <xsl:value-of select="@url"/>
    </xsl:attribute>
    <xsl:value-of select="."/>
    </a>
    </li>
</xsl:template>

<!-- Tabs -->

<xsl:template match="tabs">
    <ul class="tabs">
    <xsl:apply-templates select="tab"/>
    </ul>
    <xsl:apply-templates select="content"/>
</xsl:template>

<xsl:template match="tab">
    <li>
    <xsl:attribute name="class">
    <xsl:choose>
        <xsl:when test="boolean(@active = 'true')">
            <xsl:text>ftab</xsl:text>
        </xsl:when>
        <xsl:otherwise>
            <xsl:text>btab</xsl:text>
        </xsl:otherwise>
    </xsl:choose>
    </xsl:attribute>
    <a class="tab">
    <xsl:attribute name="href">
    <xsl:value-of select="@url"/>
    </xsl:attribute>
    <xsl:apply-templates/>
    </a>
    </li>
</xsl:template>

<!-- Lists -->

<xsl:template match="list">
    <table class="list" cellpadding="0" cellspacing="0">
    <xsl:apply-templates select="hrow"/>
    <tbody>
    <xsl:apply-templates select="row"/>
    </tbody>
    </table>
</xsl:template>

<xsl:template match="hrow">
    <thead>
    <tr class="header">
    <td></td>
    <xsl:apply-templates select="hcell"/>
    <td></td>
    </tr>
    </thead>
</xsl:template>

<xsl:template match="hcell">
    <td>
    <xsl:choose>
        <xsl:when test="boolean(@checkboxes = 'true')">
            <input type="checkbox" class="check" value="">
            <xsl:attribute name="onclick">
            <xsl:for-each select="../../row">
                <xsl:value-of select="@name"/>
                <xsl:text>.checked=this.checked;</xsl:text>
                <xsl:value-of select="@name"/>
                <xsl:text>.onclick();</xsl:text>
            </xsl:for-each>
            </xsl:attribute>
            </input>
        </xsl:when>
        <xsl:otherwise>
            <xsl:if test="boolean(@url)">
                <xsl:attribute name="onclick">
                    <xsl:text>window.open('</xsl:text>
                    <xsl:value-of select="@url"/>
                    <xsl:text>', '_parent')</xsl:text>
                </xsl:attribute>
            </xsl:if>
            <p>
            <xsl:attribute name="class">
            <xsl:choose>
                <xsl:when test="boolean(@align)">
                    <xsl:value-of select="@align"/>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:text>left</xsl:text>
                </xsl:otherwise>
            </xsl:choose>
            </xsl:attribute>
            <xsl:value-of select="."/>
            </p>
        </xsl:otherwise>
    </xsl:choose>
    </td>
</xsl:template>

<xsl:template match="row">
    <tr>
    <xsl:attribute name="class">
        <xsl:text>row</xsl:text>
        <xsl:value-of select="@color"/>
    </xsl:attribute>
    <xsl:if test="boolean(@url)">
        <xsl:attribute name="onmouseover">
            <xsl:text>this.className='hrow</xsl:text>
            <xsl:value-of select="@color"/>
            <xsl:text>'</xsl:text>
        </xsl:attribute>
        <xsl:attribute name="onmouseout">
            <xsl:text>this.className='row</xsl:text>
            <xsl:value-of select="@color"/>
            <xsl:text>'</xsl:text>
        </xsl:attribute>
    </xsl:if>
    <td class="left"/>
    <xsl:if test="boolean(@name)">
    <td class="check">
    <input type="checkbox" class="check" value="">
    <xsl:attribute name="id">
    <xsl:value-of select="@name"/>
    </xsl:attribute>
    <xsl:attribute name="name">
    <xsl:value-of select="@name"/>
    </xsl:attribute>
    <xsl:attribute name="onclick">
    <xsl:text>this.value = (this.checked ? '</xsl:text>
    <xsl:value-of select="@name"/>
    <xsl:text>' : '')</xsl:text>
    </xsl:attribute>
    <xsl:if test="boolean(@checked)">
        <xsl:attribute name="checked">
        <xsl:text>checked</xsl:text>
        </xsl:attribute>
        <xsl:attribute name="value">
        <xsl:value-of select="@name"/>
        </xsl:attribute>
    </xsl:if>
    <xsl:if test="boolean(@disabled)">
        <xsl:attribute name="disabled">
        <xsl:text>disabled</xsl:text>
        </xsl:attribute>
    </xsl:if>
    </input>
    </td>
    </xsl:if>
    <xsl:apply-templates select="cell"/>
    <td class="right"/>
    </tr>
</xsl:template>

<xsl:template match="cell">
    <td>
    <xsl:if test="boolean(@nowrap = 'true')">
        <xsl:attribute name="nowrap">
        <xsl:text>nowrap</xsl:text>
        </xsl:attribute>
    </xsl:if>
    <a>
    <xsl:attribute name="class">
    <xsl:choose>
        <xsl:when test="boolean(@align)">
            <xsl:value-of select="@align"/>
        </xsl:when>
        <xsl:otherwise>
            <xsl:text>left</xsl:text>
        </xsl:otherwise>
    </xsl:choose>
    </xsl:attribute>
    <xsl:if test="boolean(../@url)">
        <xsl:attribute name="href">
        <xsl:value-of select="../@url"/>
        </xsl:attribute>
    </xsl:if>
    <xsl:choose>
        <xsl:when test="boolean(@bold = 'true')">
            <b><xsl:apply-templates/></b>
        </xsl:when>
        <xsl:otherwise>
            <xsl:apply-templates/>
        </xsl:otherwise>
    </xsl:choose>
    </a>
    </td>
</xsl:template>

<xsl:template match="bookmarks">
    <table class="bookmarks">
    <tr>
    <xsl:apply-templates select="bookmark"/>
    <td class="total"><a class="bookmark"><xsl:value-of select="@total"/></a></td>
    </tr>
    </table>
</xsl:template>

<xsl:template match="bookmark">
    <td>
    <xsl:attribute name="class">
    <xsl:choose>
        <xsl:when test="boolean(@active = 'true')">
            <xsl:text>cbookmark</xsl:text>
        </xsl:when>
        <xsl:otherwise>
            <xsl:text>bookmark</xsl:text>
        </xsl:otherwise>
    </xsl:choose>
    </xsl:attribute>
    <a class="bookmark">
    <xsl:attribute name="href">
    <xsl:value-of select="@url"/>
    </xsl:attribute>
    <xsl:value-of select="."/>
    </a>
    </td>
</xsl:template>

<!-- Forms & Controls -->

<xsl:template match="dual">
    <table class="dual">
    <tr>
    <td>
    <xsl:apply-templates select="dualleft"/>
    </td>
    <td class="dual">
    <xsl:for-each select="button">
    <xsl:apply-templates select="."/>
    <br/>
    </xsl:for-each>
    </td>
    <td>
    <xsl:apply-templates select="dualright"/>
    </td>
    </tr>
    </table>
</xsl:template>

<xsl:template match="dualleft|dualright">
    <xsl:apply-templates/>
</xsl:template>

<xsl:template match="form">
    <form target="_parent" method="post">
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
    <input type="hidden" name="submitted">
    <xsl:attribute name="value">
    <xsl:value-of select="@name"/>
    </xsl:attribute>
    </input>
    <xsl:apply-templates/>
    </form>
</xsl:template>

<xsl:template match="group">
    <xsl:variable name="id" select="generate-id()"/>
    <fieldset>
    <xsl:if test="boolean(@title)">
        <legend>
        <a class="toggle">
        <xsl:attribute name="id">
        <xsl:text>toggle</xsl:text>
        <xsl:value-of select="$id"/>
        </xsl:attribute>
        <xsl:attribute name="href">
        <xsl:text>javascript:toggle_group('</xsl:text>
        <xsl:value-of select="$id"/>
        <xsl:text>');</xsl:text>
        </xsl:attribute>
        <xsl:text disable-output-escaping="yes">&amp;minus;</xsl:text>
        </a>
        <xsl:value-of select="@title"/>
        </legend>
    </xsl:if>
    <div style="display:block">
    <xsl:attribute name="id">
    <xsl:text>div</xsl:text>
    <xsl:value-of select="$id"/>
    </xsl:attribute>
    <table class="form">
    <xsl:apply-templates select="text|control|hr"/>
    </table>
    <xsl:apply-templates select="button"/>
    </div>
    </fieldset>
</xsl:template>

<xsl:template match="text">
    <tr>
    <xsl:if test="boolean(@label)">
        <td class="label">
        <xsl:value-of select="@label"/>
        <xsl:text>:</xsl:text>
        </td>
    </xsl:if>
    <td class="text">
    <xsl:apply-templates/>
    </td>
    </tr>
</xsl:template>

<xsl:template match="control">
    <xsl:choose>
    <xsl:when test="name(parent::node()) = 'group'">
        <tr>
        <xsl:for-each select="label">
        <td>
        <xsl:apply-templates select="."/>
        </td>
        </xsl:for-each>
        <td>
        <xsl:if test="@description">
            <p class="note">
            <xsl:attribute name="onclick">$('#<xsl:value-of select="@name"/>description').slideToggle('fast');</xsl:attribute>
            <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
            </p>
        </xsl:if>
        </td>
        <td>
        <xsl:apply-templates select="control|editbox|passbox|filebox|checkbox|radio|combobox|listbox|textbox|description"/>
        </td>
        </tr>
    </xsl:when>
    <xsl:otherwise>
        <xsl:apply-templates select="control|editbox|passbox|filebox|checkbox|radio|combobox|listbox|textbox|description"/>
    </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<xsl:template match="description">
    <div class="fielddescription" style="display:none;">
    <xsl:attribute name="id">
    <xsl:value-of select="../@name"></xsl:value-of>
    <xsl:text>description</xsl:text>
    </xsl:attribute>
    <xsl:if test="@headline">
        <div class="fielddescriptionhl">
        <xsl:value-of select="@headline"></xsl:value-of>
        </div>
    </xsl:if>
    <div class="fielddescriptionct">
    <xsl:apply-templates/>
    </div>
    </div>
</xsl:template>

<xsl:template match="label">
    <label>
    <xsl:attribute name="for">
    <xsl:value-of select="../@name"/>
    </xsl:attribute>
    <xsl:if test="boolean(../@disabled)">
        <xsl:attribute name="class">
        <xsl:text>disabled</xsl:text>
        </xsl:attribute>
    </xsl:if>
    <xsl:if test="boolean(@checkmark)">
        <input type="checkbox" class="checkbox" value="" onclick="this.value = (this.checked ? 'on' : '')">
        <xsl:attribute name="id">
        <xsl:value-of select="../@name"/>
        </xsl:attribute>
        <xsl:attribute name="name">
        <xsl:value-of select="../@name"/>
        </xsl:attribute>
        <xsl:if test="boolean(@checked)">
            <xsl:attribute name="checked">
            <xsl:text>checked</xsl:text>
            </xsl:attribute>
            <xsl:attribute name="value">
            <xsl:text>on</xsl:text>
            </xsl:attribute>
        </xsl:if>
        <xsl:if test="boolean(../@disabled)">
            <xsl:attribute name="disabled">
            <xsl:text>disabled</xsl:text>
            </xsl:attribute>
        </xsl:if>
        </input>
    </xsl:if>
    <xsl:if test="boolean(. != '')">
        <xsl:value-of select="."/>
        <xsl:text>:</xsl:text>
    </xsl:if>
    <xsl:if test="boolean(../@required)">
        <span class="sup">
        <xsl:attribute name="class">
        <xsl:choose>
            <xsl:when test="boolean(../@disabled)">
                <xsl:text>disabled</xsl:text>
            </xsl:when>
            <xsl:otherwise>
                <xsl:text>required</xsl:text>
            </xsl:otherwise>
        </xsl:choose>
        </xsl:attribute>
        <xsl:text>[</xsl:text>
        <xsl:value-of select="../@required"/>
        <xsl:text>]</xsl:text>
        </span>
    </xsl:if>
    </label>
</xsl:template>

<xsl:template match="editbox">
    <input type="text" class="editbox">
    <xsl:if test="boolean(@small)">
        <xsl:attribute name="class">
        <xsl:text>small</xsl:text>
        </xsl:attribute>
    </xsl:if>
    <xsl:attribute name="id">
    <xsl:value-of select="../@name"/>
    </xsl:attribute>
    <xsl:attribute name="name">
    <xsl:value-of select="../@name"/>
    </xsl:attribute>
    <xsl:if test="boolean(@maxlen)">
        <xsl:attribute name="maxlength">
        <xsl:value-of select="@maxlen"/>
        </xsl:attribute>
    </xsl:if>
    <xsl:if test="boolean(../@disabled)">
        <xsl:attribute name="disabled">
        <xsl:text>disabled</xsl:text>
        </xsl:attribute>
    </xsl:if>
    <xsl:attribute name="value">
    <xsl:value-of select="."/>
    </xsl:attribute>
    </input>
</xsl:template>

<xsl:template match="passbox">
    <input type="password" class="password">
    <xsl:if test="boolean(@small)">
        <xsl:attribute name="class">
        <xsl:text>small</xsl:text>
        </xsl:attribute>
    </xsl:if>
    <xsl:attribute name="id">
    <xsl:value-of select="../@name"/>
    </xsl:attribute>
    <xsl:attribute name="name">
    <xsl:value-of select="../@name"/>
    </xsl:attribute>
    <xsl:if test="boolean(@maxlen)">
        <xsl:attribute name="maxlength">
        <xsl:value-of select="@maxlen"/>
        </xsl:attribute>
    </xsl:if>
    <xsl:if test="boolean(../@disabled)">
        <xsl:attribute name="disabled">
        <xsl:text>disabled</xsl:text>
        </xsl:attribute>
    </xsl:if>
    <xsl:attribute name="value">
    <xsl:value-of select="."/>
    </xsl:attribute>
    </input>
</xsl:template>

<xsl:template match="filebox">
    <input type="file" class="editbox">
    <xsl:if test="boolean(@small)">
        <xsl:attribute name="class">
        <xsl:text>small</xsl:text>
        </xsl:attribute>
    </xsl:if>
    <xsl:attribute name="id">
    <xsl:value-of select="../@name"/>
    </xsl:attribute>
    <xsl:attribute name="name">
    <xsl:value-of select="../@name"/>
    </xsl:attribute>
    <xsl:if test="boolean(@maxlen)">
        <xsl:attribute name="maxlength">
        <xsl:value-of select="@maxlen"/>
        </xsl:attribute>
    </xsl:if>
    <xsl:if test="boolean(../@disabled)">
        <xsl:attribute name="disabled">
        <xsl:text>disabled</xsl:text>
        </xsl:attribute>
    </xsl:if>
    <xsl:attribute name="value">
    <xsl:value-of select="."/>
    </xsl:attribute>
    </input>
</xsl:template>

<xsl:template match="checkbox">
    <label>
    <xsl:attribute name="for">
    <xsl:value-of select="../@name"/>
    </xsl:attribute>
    <xsl:if test="boolean(../@disabled)">
        <xsl:attribute name="class">
        <xsl:text>disabled</xsl:text>
        </xsl:attribute>
    </xsl:if>
    <input type="checkbox" class="checkbox" value="" onclick="this.value = (this.checked ? 'on' : '')">
    <xsl:attribute name="id">
    <xsl:value-of select="../@name"/>
    </xsl:attribute>
    <xsl:attribute name="name">
    <xsl:value-of select="../@name"/>
    </xsl:attribute>
    <xsl:if test="boolean(@checked)">
        <xsl:attribute name="checked">
        <xsl:text>checked</xsl:text>
        </xsl:attribute>
        <xsl:attribute name="value">
        <xsl:text>on</xsl:text>
        </xsl:attribute>
    </xsl:if>
    <xsl:if test="boolean(../@disabled)">
        <xsl:attribute name="disabled">
        <xsl:text>disabled</xsl:text>
        </xsl:attribute>
    </xsl:if>
    </input>
    <xsl:value-of select="."/>
    </label>
</xsl:template>

<xsl:template match="radio">
    <label>
    <xsl:if test="boolean(@name)">
        <xsl:attribute name="for">
        <xsl:value-of select="@name"/>
        </xsl:attribute>
    </xsl:if>
    <xsl:if test="boolean(../@disabled)">
        <xsl:attribute name="class">
        <xsl:text>disabled</xsl:text>
        </xsl:attribute>
    </xsl:if>
    <input type="radio" class="radio">
    <xsl:if test="boolean(@name)">
        <xsl:attribute name="id">
        <xsl:value-of select="@name"/>
        </xsl:attribute>
    </xsl:if>
    <xsl:attribute name="name">
    <xsl:value-of select="../@name"/>
    </xsl:attribute>
    <xsl:attribute name="value">
    <xsl:value-of select="@value"/>
    </xsl:attribute>
    <xsl:if test="boolean(@checked)">
        <xsl:attribute name="checked">
        <xsl:text>checked</xsl:text>
        </xsl:attribute>
    </xsl:if>
    <xsl:if test="boolean(../@disabled)">
        <xsl:attribute name="disabled">
        <xsl:text>disabled</xsl:text>
        </xsl:attribute>
    </xsl:if>
    </input>
    <xsl:value-of select="."/>
    </label>
</xsl:template>

<xsl:template match="combobox">
    <select size="1">
    <xsl:if test="boolean(@small)">
        <xsl:attribute name="class">
        <xsl:text>small</xsl:text>
        </xsl:attribute>
    </xsl:if>
    <xsl:attribute name="id">
    <xsl:value-of select="../@name"/>
    </xsl:attribute>
    <xsl:attribute name="name">
    <xsl:value-of select="../@name"/>
    </xsl:attribute>
    <xsl:if test="boolean(../@disabled)">
        <xsl:attribute name="disabled">
        <xsl:text>disabled</xsl:text>
        </xsl:attribute>
    </xsl:if>
    <xsl:apply-templates select="listitem"/>
    </select>
</xsl:template>

<xsl:template match="dropdown">
    <select class="dropdown" size="1">
    <xsl:attribute name="id">
    <xsl:value-of select="@name"/>
    </xsl:attribute>
    <xsl:attribute name="name">
    <xsl:value-of select="@name"/>
    </xsl:attribute>
    <xsl:if test="boolean(@disabled)">
        <xsl:attribute name="disabled">
        <xsl:text>disabled</xsl:text>
        </xsl:attribute>
    </xsl:if>
    <xsl:apply-templates select="listitem"/>
    </select>
</xsl:template>

<xsl:template match="listbox">
    <select multiple="multiple">
    <xsl:attribute name="id">
    <xsl:value-of select="../@name"/>
    </xsl:attribute>
    <xsl:attribute name="name">
    <xsl:value-of select="../@name"/>
    </xsl:attribute>
    <xsl:attribute name="size">
    <xsl:choose>
        <xsl:when test="boolean(@size)">
            <xsl:value-of select="@size"/>
        </xsl:when>
        <xsl:otherwise>
            <xsl:text>4</xsl:text>
        </xsl:otherwise>
    </xsl:choose>
    </xsl:attribute>
    <xsl:if test="boolean(../@disabled)">
        <xsl:attribute name="disabled">
        <xsl:text>disabled</xsl:text>
        </xsl:attribute>
    </xsl:if>
    <xsl:if test="boolean(@action)">
        <xsl:attribute name="onchange">
        <xsl:value-of select="@action"/>
        </xsl:attribute>
    </xsl:if>
    <xsl:apply-templates select="listitem"/>
    </select>
</xsl:template>

<xsl:template match="listitem">
    <option>
    <xsl:attribute name="value">
    <xsl:value-of select="@value"/>
    </xsl:attribute>
    <xsl:if test="boolean(@selected = 'true')">
        <xsl:attribute name="selected">
        <xsl:text>selected</xsl:text>
        </xsl:attribute>
    </xsl:if>
    <xsl:value-of select="."/>
    </option>
</xsl:template>

<xsl:template match="textbox">
    <textarea cols="0">
    <xsl:attribute name="id">
    <xsl:value-of select="../@name"/>
    </xsl:attribute>
    <xsl:attribute name="name">
    <xsl:value-of select="../@name"/>
    </xsl:attribute>
    <xsl:if test="boolean(@rows)">
        <xsl:attribute name="rows">
        <xsl:value-of select="@rows"/>
        </xsl:attribute>
    </xsl:if>
    <xsl:if test="boolean(../@disabled)">
        <xsl:attribute name="disabled">
        <xsl:text>disabled</xsl:text>
        </xsl:attribute>
    </xsl:if>
    <xsl:if test="boolean(@resizeable = 'true')">
        <xsl:attribute name="style">
        <xsl:text>overflow-y:hidden</xsl:text>
        </xsl:attribute>
    </xsl:if>
    <xsl:attribute name="onchange">
        <xsl:text>onTextBox('</xsl:text>
        <xsl:value-of select="../@name"/>
        <xsl:text>',</xsl:text>
        <xsl:value-of select="@maxlen"/>
        <xsl:text>,</xsl:text>
        <xsl:choose>
            <xsl:when test="boolean(@resizeable)">
                <xsl:value-of select="@resizeable"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:text>false</xsl:text>
            </xsl:otherwise>
        </xsl:choose>
        <xsl:text>,</xsl:text>
        <xsl:value-of select="@rows"/>
        <xsl:text>)</xsl:text>
    </xsl:attribute>
    <xsl:attribute name="onkeydown">
        <xsl:text>onTextBox('</xsl:text>
        <xsl:value-of select="../@name"/>
        <xsl:text>',</xsl:text>
        <xsl:value-of select="@maxlen"/>
        <xsl:text>,</xsl:text>
        <xsl:choose>
            <xsl:when test="boolean(@resizeable)">
                <xsl:value-of select="@resizeable"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:text>false</xsl:text>
            </xsl:otherwise>
        </xsl:choose>
        <xsl:text>,</xsl:text>
        <xsl:value-of select="@rows"/>
        <xsl:text>)</xsl:text>
    </xsl:attribute>
    <xsl:attribute name="onkeyup">
        <xsl:text>onTextBox('</xsl:text>
        <xsl:value-of select="../@name"/>
        <xsl:text>',</xsl:text>
        <xsl:value-of select="@maxlen"/>
        <xsl:text>,</xsl:text>
        <xsl:choose>
            <xsl:when test="boolean(@resizeable)">
                <xsl:value-of select="@resizeable"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:text>false</xsl:text>
            </xsl:otherwise>
        </xsl:choose>
        <xsl:text>,</xsl:text>
        <xsl:value-of select="@rows"/>
        <xsl:text>)</xsl:text>
    </xsl:attribute>
    <xsl:value-of select="."/>
    </textarea>
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
    <xsl:choose>
    <xsl:when test="boolean(@prompt)">
        <xsl:text>jqConfirm('</xsl:text>
        <xsl:value-of select="/page/@msgboxTitle"/>
        <xsl:text>','</xsl:text>
        <xsl:value-of select="@prompt"/>
        <xsl:text>','</xsl:text>
        <xsl:value-of select="/page/@btnOk"/>
        <xsl:text>','</xsl:text>
        <xsl:choose>
            <xsl:when test="boolean(@url)">
                <xsl:text>window.open(\'</xsl:text>
                <xsl:value-of select="@url"/>
                <xsl:text>\',\'_parent\');</xsl:text>
            </xsl:when>
            <xsl:when test="boolean(@action)">
                <xsl:value-of select="@action"/>
            </xsl:when>
        </xsl:choose>
        <xsl:text>','</xsl:text>
        <xsl:value-of select="/page/@btnCancel"/>
        <xsl:text>')</xsl:text>
    </xsl:when>
    <xsl:otherwise>
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
    </xsl:otherwise>
    </xsl:choose>
    </xsl:attribute>
    <xsl:if test="boolean(@disabled)">
        <xsl:attribute name="class">
        <xsl:text>button_disabled</xsl:text>
        </xsl:attribute>
        <xsl:attribute name="disabled">
        <xsl:text>disabled</xsl:text>
        </xsl:attribute>
    </xsl:if>
    <xsl:attribute name="value">
    <xsl:value-of select="."/>
    </xsl:attribute>
    </input>
</xsl:template>

<xsl:template match="note">
    <p class="note">
    <xsl:value-of select="."/>
    </p>
</xsl:template>

<!-- BBCode -->

<xsl:template match="bbcode_b">
    <xsl:text>[b]</xsl:text>
    <xsl:apply-templates/>
    <xsl:text>[/b]</xsl:text>
</xsl:template>

<xsl:template match="bbcode_i">
    <xsl:text>[i]</xsl:text>
    <xsl:apply-templates/>
    <xsl:text>[/i]</xsl:text>
</xsl:template>

<xsl:template match="bbcode_u">
    <xsl:text>[u]</xsl:text>
    <xsl:apply-templates/>
    <xsl:text>[/u]</xsl:text>
</xsl:template>

<xsl:template match="bbcode_s">
    <xsl:text>[s]</xsl:text>
    <xsl:apply-templates/>
    <xsl:text>[/s]</xsl:text>
</xsl:template>

<xsl:template match="bbcode_sub">
    <xsl:text>[sub]</xsl:text>
    <xsl:apply-templates/>
    <xsl:text>[/sub]</xsl:text>
</xsl:template>

<xsl:template match="bbcode_sup">
    <xsl:text>[sup]</xsl:text>
    <xsl:apply-templates/>
    <xsl:text>[/sup]</xsl:text>
</xsl:template>

<xsl:template match="bbcode_color">
    <xsl:text>[color=</xsl:text>
    <xsl:value-of select="@value"/>
    <xsl:text>]</xsl:text>
    <xsl:text>[/color]</xsl:text>
</xsl:template>

<xsl:template match="bbcode_size">
    <xsl:text>[size=</xsl:text>
    <xsl:value-of select="@value"/>
    <xsl:text>]</xsl:text>
    <xsl:text>[/size]</xsl:text>
</xsl:template>

<xsl:template match="bbcode_font">
    <xsl:text>[font=</xsl:text>
    <xsl:value-of select="@value"/>
    <xsl:text>]</xsl:text>
    <xsl:text>[/font]</xsl:text>
</xsl:template>

<xsl:template match="bbcode_align">
    <xsl:text>[align=</xsl:text>
    <xsl:value-of select="@value"/>
    <xsl:text>]</xsl:text>
    <xsl:text>[/align]</xsl:text>
</xsl:template>

<xsl:template match="bbcode_h1">
    <xsl:text>[h1]</xsl:text>
    <xsl:apply-templates/>
    <xsl:text>[/h1]</xsl:text>
</xsl:template>

<xsl:template match="bbcode_h2">
    <xsl:text>[h2]</xsl:text>
    <xsl:apply-templates/>
    <xsl:text>[/h2]</xsl:text>
</xsl:template>

<xsl:template match="bbcode_h3">
    <xsl:text>[h3]</xsl:text>
    <xsl:apply-templates/>
    <xsl:text>[/h3]</xsl:text>
</xsl:template>

<xsl:template match="bbcode_h4">
    <xsl:text>[h4]</xsl:text>
    <xsl:apply-templates/>
    <xsl:text>[/h4]</xsl:text>
</xsl:template>

<xsl:template match="bbcode_h5">
    <xsl:text>[h5]</xsl:text>
    <xsl:apply-templates/>
    <xsl:text>[/h5]</xsl:text>
</xsl:template>

<xsl:template match="bbcode_h6">
    <xsl:text>[h6]</xsl:text>
    <xsl:apply-templates/>
    <xsl:text>[/h6]</xsl:text>
</xsl:template>

<xsl:template match="bbcode_list">
    <xsl:text>[list]</xsl:text>
    <xsl:apply-templates/>
    <xsl:text>[/list]</xsl:text>
</xsl:template>

<xsl:template match="bbcode_ulist">
    <xsl:text>[ulist]</xsl:text>
    <xsl:apply-templates/>
    <xsl:text>[/ulist]</xsl:text>
</xsl:template>

<xsl:template match="bbcode_li">
    <xsl:text>[li]</xsl:text>
    <xsl:apply-templates/>
    <xsl:text>[/li]</xsl:text>
</xsl:template>

<xsl:template match="bbcode_url">
    <xsl:if test="boolean(@value)">
        <xsl:text>[url=</xsl:text>
        <xsl:value-of select="@value"/>
        <xsl:text>]</xsl:text>
        <xsl:apply-templates/>
        <xsl:text>[/url]</xsl:text>
    </xsl:if>
    <xsl:if test="not(boolean(@value))">
        <xsl:text>[url]</xsl:text>
        <xsl:value-of select="."/>
        <xsl:text>[/url]</xsl:text>
    </xsl:if>
</xsl:template>

<xsl:template match="bbcode_mail">
    <xsl:if test="boolean(@value)">
        <xsl:text>[mail=</xsl:text>
        <xsl:value-of select="@value"/>
        <xsl:text>]</xsl:text>
        <xsl:text>[/mail]</xsl:text>
    </xsl:if>
    <xsl:if test="not(boolean(@value))">
        <xsl:text>[mail]</xsl:text>
        <xsl:value-of select="."/>
        <xsl:text>[/mail]</xsl:text>
    </xsl:if>
</xsl:template>

<xsl:template match="bbcode_code">
    <xsl:apply-templates/>
</xsl:template>

<xsl:template match="bbcode_quote">
    <xsl:text>[quote]</xsl:text>
    <xsl:apply-templates/>
    <xsl:text>[/quote]</xsl:text>
</xsl:template>

<xsl:template match="bbcode_search">
    <xsl:apply-templates/>
</xsl:template>

<!-- Text & Format -->

<xsl:template match="div">
    <div>
    <xsl:if test="boolean(@id)">
        <xsl:attribute name="id">
        <xsl:value-of select="@id"/>
        </xsl:attribute>
    </xsl:if>
    <xsl:if test="boolean(@style)">
        <xsl:attribute name="style">
        <xsl:value-of select="@style"/>
        </xsl:attribute>
    </xsl:if>
    <xsl:apply-templates/>
    </div>
</xsl:template>

<xsl:template match="span">
    <span>
    <xsl:if test="boolean(@class)">
        <xsl:attribute name="class">
        <xsl:value-of select="@class"/>
        </xsl:attribute>
    </xsl:if>
    <xsl:if test="boolean(@style)">
        <xsl:attribute name="style">
        <xsl:value-of select="@style"/>
        </xsl:attribute>
    </xsl:if>
    <xsl:apply-templates/>
    </span>
</xsl:template>

<xsl:template match="pre">
    <pre>
    <xsl:if test="boolean(@class)">
        <xsl:attribute name="class">
        <xsl:value-of select="@class"/>
        </xsl:attribute>
    </xsl:if>
    <xsl:apply-templates/>
    </pre>
</xsl:template>

<xsl:template match="blockquote">
    <blockquote>
    <xsl:if test="boolean(@class)">
        <xsl:attribute name="class">
        <xsl:value-of select="@class"/>
        </xsl:attribute>
    </xsl:if>
    <xsl:apply-templates/>
    </blockquote>
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

<xsl:template match="url">
    <a target="_blank">
    <xsl:attribute name="href">
    <xsl:value-of select="@address"/>
    </xsl:attribute>
    <xsl:apply-templates/>
    </a>
</xsl:template>

<xsl:template match="br">
    <br/>
</xsl:template>

<xsl:template match="hr">
    <tr>
    <td colspan="3">
    <hr/>
    </td>
    </tr>
</xsl:template>

<xsl:template match="b|i|u|s|sub|sup|h1|h2|h3|h4|h5|h6|ol|ul|li">
    <xsl:copy>
    <xsl:apply-templates/>
    </xsl:copy>
</xsl:template>

</xsl:stylesheet>
