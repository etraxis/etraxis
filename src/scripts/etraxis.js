//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2005-2011  Artem Rodygin
//
//  This program is free software: you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation, either version 3 of the License, or
//  (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License
//  along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
//------------------------------------------------------------------------------

function logout ()
{
    var userAgent = navigator.userAgent.toLowerCase();

    if (userAgent.indexOf("msie") != -1)
    {
        document.execCommand("ClearAuthenticationCache");
    }

    window.open("../logon/logout.php", "_parent");
}

//------------------------------------------------------------------------------

function clear_topline (element, prompt)
{
    if (element.value == prompt) element.value = '';
}

//------------------------------------------------------------------------------

function reset_topline (element, prompt)
{
    if (element.value == '') element.value = prompt;
}

//------------------------------------------------------------------------------

function toggle_menu (id)
{
    var item = document.getElementById('item' + id);
    var menu = document.getElementById('menu' + id);

    if (menu.style.display == 'none')
    {
        item.className     = 'menuitem_m';
        menu.style.display = 'block';
    }
    else
    {
        item.className     = 'menuitem_p';
        menu.style.display = 'none';
    }
}

//------------------------------------------------------------------------------

function toggle_group (id)
{
    var div    = document.getElementById('div'    + id);
    var toggle = document.getElementById('toggle' + id);

    if (div.style.display == 'none')
    {
        div.style.display = 'block';
        toggle.innerHTML  = '&minus;';
    }
    else
    {
        div.style.display = 'none';
        toggle.innerHTML  = '+';
    }
}

//------------------------------------------------------------------------------

function onTextBox (id, maxlen, resizeable, minrows)
{
    var textbox = document.getElementById(id);

    if (textbox.value.length > maxlen)
    {
        textbox.value = textbox.value.substring(0, maxlen);
    }

    if (resizeable == true)
    {
        var result = $.countLines('#' + id);

        if ((result.visual > minrows) && (result.visual != textbox.rows))
        {
            textbox.rows = (result.visual >= minrows) ? result.visual : minrows;
        }
    }
}

//------------------------------------------------------------------------------

function reloadTab (url)
{
    var selected = $("#tabs").tabs("option", "selected");

    if (url)
    {
        $("#tabs").tabs("url", selected, url);
    }

    $("#tabs").tabs("load", selected);
}

//------------------------------------------------------------------------------

function closeModal ()
{
    $("#modaldlg").dialog("destroy");
}

//------------------------------------------------------------------------------

function jqAlert (title, message, btnClose, funcClose)
{
    var buttons = {};

    buttons[btnClose] = function () {
        $(this).dialog("close");

        if (funcClose)
        {
            eval(funcClose);
        }
    }

    $("#messagebox").dialog({
        buttons: buttons,
        position: "center",
        title: title.toUpperCase()
    });

    $("#messagebox").html(message);
    $("#messagebox").dialog("open");
}

//------------------------------------------------------------------------------

function jqConfirm (title, message, btnOk, btnCancel, funcOk, funcCancel)
{
    var buttons = {};

    buttons[btnOk] = function () {
        $(this).dialog("close");

        if (funcOk)
        {
            eval(funcOk);
        }
    }

    buttons[btnCancel] = function () {
        $(this).dialog("close");

        if (funcCancel)
        {
            eval(funcCancel);
        }
    }

    $("#messagebox").dialog({
        buttons: buttons,
        position: "center",
        title: title.toUpperCase()
    });

    $("#messagebox").html(message);
    $("#messagebox").dialog("open");
}

//------------------------------------------------------------------------------

function jqModal (title, url, btnOk, btnCancel, funcOk, funcCancel, funcLoad)
{
    var buttons = {};

    buttons[btnOk] = function () {
        if (funcOk)
        {
            eval(funcOk);
        }
        else
        {
            $("#modaldlg").dialog("destroy");
        }
    }

    if (btnCancel)
    {
        buttons[btnCancel] = function () {
            if (funcCancel)
            {
                eval(funcCancel);
            }
            else
            {
                $("#modaldlg").dialog("destroy");
            }
        }
    }

    var maxWidth  = $("body").width()  - 32;
    var maxHeight = $("body").height() - 32;

    $("#modaldlg").load(url, function (responseText, textStatus, XMLHttpRequest) {

        if (textStatus == "success")
        {
            $("input.button").button();
            $("span.buttonset").buttonset();

            $("#modaldlg").dialog({
                title: title,
                modal: true,
                width: "auto",
                height: "auto",
                resizable: false,
                buttons: buttons
            });

            if ($("#modaldlg").width() > maxWidth)
            {
                $("#modaldlg").dialog("option", "width", maxWidth);
            }

            if ($("#modaldlg").height() > maxHeight)
            {
                $("#modaldlg").dialog("option", "height", maxHeight);
            }

            // width audjustment IE workaround
            var userAgent = navigator.userAgent.toLowerCase();

            if (userAgent.indexOf("msie") != -1)
            {
                var width = $("#modaldlg").width();

                $("div[aria-labelledby='ui-dialog-title-modaldlg'] div.ui-dialog-titlebar").width(width);
                $("div[aria-labelledby='ui-dialog-title-modaldlg'] div.ui-dialog-buttonpane").width(width);
            }

            $("#modaldlg").dialog("option", "position", "center");

            if (funcLoad)
            {
                eval(funcLoad);
            }
        }
        else
        {
            if (XMLHttpRequest.status == 307)
            {
                window.open(XMLHttpRequest.statusText, "_parent");
            }
            else
            {
                jqAlert(title, XMLHttpRequest.responseText, "OK");
            }
        }
    });
}
