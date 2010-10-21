//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2005-2010  Artem Rodygin
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

function clear_topline (element, prompt)
{
    if (element.value == prompt) element.value = '';
    element.style.color = '#000';
}

function reset_topline (element, prompt)
{
    if (element.value == '') element.value = prompt;
    element.style.color = '#CCC';
}

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

function onTextBox (id, maxlen, resizeable, minrows)
{
    var textbox = document.getElementById(id);

    if (textbox.value.length > maxlen)
    {
        textbox.value = textbox.value.substring(0, maxlen);
    }

    if (resizeable == true)
    {
        var curLineNum = textbox.value.replace((new RegExp(".{"+textbox.cols+"}","g")),"\n").split("\n").length;

        if ((curLineNum > 0) && (curLineNum != textbox.rows))
        {
            textbox.rows = (curLineNum >= minrows) ? curLineNum : minrows;
        }
    }
}
