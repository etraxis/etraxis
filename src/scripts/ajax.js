var AJAX_METHOD_GET     = "GET";
var AJAX_METHOD_POST    = "POST";
var AJAX_METHOD_HEAD    = "HEAD";
var AJAX_METHOD_PUT     = "PUT";
var AJAX_METHOD_DELETE  = "DELETE";
var AJAX_METHOD_OPTIONS = "OPTIONS";

var AJAX_STATE_UNSENT           = 0;
var AJAX_STATE_OPENED           = 1;
var AJAX_STATE_HEADERS_RECEIVED = 2;
var AJAX_STATE_LOADING          = 3;
var AJAX_STATE_DONE             = 4;

var HTTP_STATUS_OK              = 200;
var HTTP_STATUS_BAD_REQUEST     = 400;
var HTTP_STATUS_UNAUTHORIZED    = 401;
var HTTP_STATUS_FORBIDDEN       = 403;
var HTTP_STATUS_NOT_FOUND       = 404;

var xmlHttpRequest = null;

try { xmlHttpRequest = new XMLHttpRequest();                    } catch (exception) { }
try { xmlHttpRequest = new ActiveXObject("Msxml2.XMLHTTP.6.0"); } catch (exception) { }
try { xmlHttpRequest = new ActiveXObject("Msxml2.XMLHTTP.3.0"); } catch (exception) { }
try { xmlHttpRequest = new ActiveXObject("Msxml2.XMLHTTP");     } catch (exception) { }
try { xmlHttpRequest = new ActiveXObject("Microsoft.XMLHTTP");  } catch (exception) { }

function form2json (formname)
{
    var form = eval(formname);
    var data = {};

    for (var i = 0; i != form.elements.length; i++)
    {
        var element = form.elements[i];

        if (element.name == "submitted"   ||
            element.name == "responsible" ||
            element.name.substring(0,5) == "field")
        {
            data[element.name] = element.value;
        }
    }

    return JSON.stringify(data);
}
