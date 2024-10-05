<?php

$svgFiles = glob(__DIR__ . "/svg/*.svg");
$html = <<<EOD
<html>
    <head>
        <meta charset="UTF-8">
        <style>
            img {
                width: 60px;
                display: block;
                margin: 0 auto;
            }

            div {
                display: inline-block;
                text-align: center;
                margin: 3px;
                padding: 2px;
                border: 1px solid #CCC;
            }

            span {
                font-size: 9px;
                display: block;
                font-family: Arial;
            }
        </style>
    </head>
    <body>
EOD;

foreach ($svgFiles as $file) {
    echo basename($file) . "\t";

    $content = file_get_contents($file);

    $matchs = array();

    $content = str_replace('fill:#15a072;', '', $content);
    $content = str_replace('fill:#414a64;', '', $content);
    $content = str_replace('fill:#404964;', '', $content);

    if (preg_match("@\.([^<{]+){stroke:#(15a072|414a64);@", $content, $matchs)) {
        $classnames = explode(',', $matchs[1]);

        foreach ($classnames as $aClass) {
            $classname = trim($aClass, ".");

            $content = str_replace('"' . $classname .  '"', "\"$classname svgStrokeColor\"", $content);
        }

        $content = str_replace('stroke:#15a072;', '', $content);
        $content = str_replace('stroke:#414a64;', '', $content);
    }

    $newFilename = preg_replace('@[()]+@', '', iconv("UTF-8", "ASCII//TRANSLIT", basename($file)));
    $newFilename = preg_replace('@[- _]+@', '-', $newFilename);
    $newFilename = preg_replace('@[^A-Za-z0-9]+\.svg@', '.svg', $newFilename);

    $file = str_replace('/svg', '/svg-out', dirname($file)) . '/' . $newFilename;

    
    echo $newFilename . "\n";

    file_put_contents($file, $content);

    $html .= '  <div><img src="svg-out/'.$newFilename.'"><span>'.$newFilename.'</span></div> ' . "\n";
}

$html .= '</body></html>';
file_put_contents('test.html', $html);

