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

function loadStateForm (id)
{
    $("#statediv").load("state.php?id=" + id, $("#stateslistform").serialize());
}

function cancelStateForm ()
{
    $("#statediv").html(null);
}

function submitStateForm (id, state)
{
    $.post("state.php?id=" + id + "&state=" + state, $("#stateform").serialize(), function(data){
        if (data.length == 0)
        {
            window.open("view.php?id=" + id, "_parent");
        }
        else
        {
            alert(data);
        }
    });
}
