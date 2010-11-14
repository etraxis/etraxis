/*
 *
 * Textarea Line Count - version 1.2
 *
 * http://mosttw.wordpress.com/
 *
 * Licensed under MIT License: http://en.wikipedia.org/wiki/MIT_License
 *
 * Copyright (c) 2010 MostThingsWeb

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE.
 */

(function($){ $.countLines = function(textarea, o){ var ta; if (typeof textarea == "string") ta = $(textarea); else if (typeof textarea == "object") ta = textarea; if (ta.size() != 1) return; var value = ta.val(); var options = $.extend({ recalculateCharWidth : false, chars : "", charsMode : "random", fontAttrs : ["font-family", "font-size", "text-decoration", "font-style", "font-weight"] }, o); var masterCharacters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890"; var counter; switch (options.charsMode){ case "random": options.chars = ""; masterCharacters += ".,?!-+;:'\""; for (counter = 1; counter <= 12; counter++) options.chars += masterCharacters[(Math.floor(Math.random() * masterCharacters.length))]; break; case "alpha": options.chars = masterCharacters; break; case "alpha_extended": options.chars = masterCharacters + ".,?!-+;:'\""; break; case "from_ta": if (value.length < 15) options.chars = masterCharacters; else for (counter = 1; counter <= 15; counter++) options.chars += value[(Math.floor(Math.random() * value.length))]; break; case "custom": break; } if (!$.isArray(options.chars)) options.chars = options.chars.split(""); var id = ""; for (counter = 1; counter <= 10; counter++) id += (Math.floor(Math.random() * 10) + 1); ta.after("<span id='s" + id + "'></span>"); var span = $("#s" + id); span.hide(); $.each(options.fontAttrs, function(i, v){ span.css(v, ta.css(v)); }); var lines = value.split("\n"); var linesLen = lines.length; var averageWidth; if (options.recalculateCharWidth || ta.data("average_char") == null) { var chars = options.chars; var charLen = chars.length; var totalWidth = 0; $.each(chars, function(i, v){ span.text(v); totalWidth += span.width(); }); ta.data("average_char", Math.ceil(totalWidth / charLen)); } averageWidth = ta.data("average_char"); span.remove(); var missingWidth = (ta.outerWidth() - ta.width()) * 2; var lineWidth; var wrappingLines = 0; var wrappingCount = 0; var blankLines = 0; $.each(lines, function(i, v){ lineWidth = ((v.length + 1) * averageWidth) + missingWidth; if (lineWidth >= ta.outerWidth()){ var wrapCount = Math.floor(lineWidth / ta.outerWidth()); wrappingCount += wrapCount; wrappingLines++; } if ($.trim(value) == "") blankLines++; }); var ret = {}; ret["actual"] = linesLen; ret["wrapped"] = wrappingLines; ret["wraps"] = wrappingCount; ret["visual"] = linesLen + wrappingCount; ret["blank"] = blankLines; return ret; }; })(jQuery);
