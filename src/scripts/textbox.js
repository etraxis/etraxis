function onTextBox (control, maxlen, resizeable, minrows)
{
    var textbox = eval('document.'+control);

    if (textbox.value.length > maxlen)
    {
        textbox.value = textbox.value.substring(0,maxlen);
    }

    if (resizeable == true)
    {
        var curLineNum = textbox.value.replace((new RegExp(".{"+textbox.cols+"}","g")),"\n").split("\n").length;

        if ( (curLineNum > 0) && (curLineNum != textbox.rows) )
            if ( curLineNum > minrows - 1 )
                textbox.rows = curLineNum;
            else
                textbox.rows = minrows;
    }
}
