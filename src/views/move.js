//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2010  Artem Rodygin
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

function moveUp ()
{
    var id = document.getElementById("rcolumns[]").value;

    $.post("move.php?offset=-1&id=" + id, function(data){
        if (data.length != 0)
        {
            document.getElementById("rcolumns[]").innerHTML = data;
        }
    });
}

function moveDown ()
{
    var id = document.getElementById("rcolumns[]").value;

    $.post("move.php?offset=1&id=" + id, function(data){
        if (data.length != 0)
        {
            document.getElementById("rcolumns[]").innerHTML = data;
        }
    });
}
