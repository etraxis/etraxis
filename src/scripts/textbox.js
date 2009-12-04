function onTextBox (control, maxlen)
{
    if (eval('document.'+control).value.length > maxlen)
    {
        eval('document.'+control).value = eval('document.'+control).value.substring(0,maxlen);
    }
}
