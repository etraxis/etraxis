function logout ()
{
    var userAgent = navigator.userAgent.toLowerCase();

    if (userAgent.indexOf("msie") != -1)
    {
        document.execCommand("ClearAuthenticationCache");
    }

    window.open("../logon/logout.php", "_parent");
}
