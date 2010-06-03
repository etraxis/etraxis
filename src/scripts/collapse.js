var events_list  = new Array();
var events_count = 0;

function SwitchLayer (div_id)
{
    ('none' == document.getElementById('div' + div_id).style.display) ? ShowLayer(div_id) : HideLayer(div_id);
}

function ShowLayer (div_id)
{
    var div = document.getElementById('div' + div_id);
    div.style.display = 'block';

    var toggle = document.getElementById('toggle' + div_id);
    toggle.innerHTML = '&minus;';
}

function HideLayer (div_id)
{
    var div = document.getElementById('div' + div_id);
    div.style.display = 'none';

    var toggle = document.getElementById('toggle' + div_id);
    toggle.innerHTML = '+';
}

function ExpandAll ()
{
    for (event_id in events_list)
    {
        ShowLayer(events_list[event_id]);
    }
}

function CollapseAll ()
{
    for (event_id in events_list)
    {
        HideLayer(events_list[event_id]);
    }
}
