<?php

$aliases = array
(
    'c'     => 'cpp',
    'cc'    => 'cpp',
    'cpp'   => 'cpp',
    'cs'    => 'csharp',
    'css'   => 'css',
    'h'     => 'cpp',
    'hpp'   => 'cpp',
    'htm'   => 'xml',
    'html'  => 'xml',
    'java'  => 'java',
    'js'    => 'jscript',
    'php'   => 'php',
    'pl'    => 'perl',
    'sh'    => 'bash',
    'sql'   => 'sql',
    'txt'   => 'plain',
    'xhtml' => 'xml',
    'xml'   => 'xml',
    'xsl'   => 'xml',
    'xslt'  => 'xml',
);

if (!isset($_REQUEST['r']) ||
    !isset($_REQUEST['b']) ||
    !isset($_REQUEST['p']) ||
    !isset($_REQUEST['f']))
{
    exit;
}

$alias = 'plain';

$pos = strrpos($_REQUEST['f'], '.');

if ($pos !== FALSE)
{
    $filetype = substr($_REQUEST['f'], $pos + 1);

    if (array_key_exists($filetype, $aliases))
    {
        $alias = $aliases[$filetype];
    }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta name="author" content="eTraxis, LLC" />
<meta name="copyright" content="Copyright (C) 2011 by eTraxis, LLC" />
<title><?php echo("{$_REQUEST['b']}: {$_REQUEST['p']}/{$_REQUEST['f']}"); ?></title>
<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
<link rel="stylesheet" type="text/css" href="/svn.css" />
<link rel="stylesheet" type="text/css" href="/shCoreEclipse.css" />
<script type="text/javascript" src="/scripts/jquery.js"></script>
<script type="text/javascript" src="/scripts/syntax/shCore.js"></script>
<script type="text/javascript" src="/scripts/syntax/shAutoloader.js"></script>
<style type="text/css">
.syntaxhighlighter {
    font-size: 12px !important;
    overflow: visible !important;
}
</style>
</head>
<body>
<div id="toolbar">
<div class="toolbar_split spacer"></div>
<div class="toolbar_item"><a href="<?php echo("/svn/{$_REQUEST['b']}{$_REQUEST['p']}"); ?>">[up]</a></div>
<div class="toolbar_split"></div>
<div class="toolbar_item"><?php echo("{$_REQUEST['b']}: {$_REQUEST['p']}/{$_REQUEST['f']}"); ?></div>
</div>
<pre id="shCode" class="brush:<?php echo($alias); ?>">
</pre>
<div id="revision">
Last revision: <?php echo($_REQUEST['r']); ?>
</div>
<script type="text/javascript">
$(document).ready(function(){
    $.get('<?php echo("/svn/{$_REQUEST['b']}{$_REQUEST['p']}/{$_REQUEST['f']}"); ?>', function (data) {
        $('#shCode').text(data);
        SyntaxHighlighter.autoloader(
            'applescript /scripts/syntax/shBrushAppleScript.js',
            'as3         /scripts/syntax/shBrushAS3.js',
            'bash        /scripts/syntax/shBrushBash.js',
            'coldfusion  /scripts/syntax/shBrushColdFusion.js',
            'cpp         /scripts/syntax/shBrushCpp.js',
            'csharp      /scripts/syntax/shBrushCSharp.js',
            'css         /scripts/syntax/shBrushCss.js',
            'delphi      /scripts/syntax/shBrushDelphi.js',
            'diff        /scripts/syntax/shBrushDiff.js',
            'erlang      /scripts/syntax/shBrushErlang.js',
            'groovy      /scripts/syntax/shBrushGroovy.js',
            'javafx      /scripts/syntax/shBrushJavaFX.js',
            'java        /scripts/syntax/shBrushJava.js',
            'jscript     /scripts/syntax/shBrushJScript.js',
            'perl        /scripts/syntax/shBrushPerl.js',
            'php         /scripts/syntax/shBrushPhp.js',
            'plain       /scripts/syntax/shBrushPlain.js',
            'powershell  /scripts/syntax/shBrushPowerShell.js',
            'python      /scripts/syntax/shBrushPython.js',
            'ruby        /scripts/syntax/shBrushRuby.js',
            'sass        /scripts/syntax/shBrushSass.js',
            'scala       /scripts/syntax/shBrushScala.js',
            'sql         /scripts/syntax/shBrushSql.js',
            'vb          /scripts/syntax/shBrushVb.js',
            'xml         /scripts/syntax/shBrushXml.js');
        SyntaxHighlighter.defaults['toolbar'] = false;
        SyntaxHighlighter.all();
    });
});
</script>
</body>
</html>
