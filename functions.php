<?php

function scandir_only_wanted_files($path)
{
    $dirArray = scandir($path);
    foreach ($dirArray as $key => $value) {
        if ($value == '.'|| $value == '..'|| $value == '.DS_Store') {
            unset($dirArray[$key]);
        }
    }
    $dirArray = array_values($dirArray);
    // echo ("<pre>");
    // echo " processed dirArray  \n";
    // print_r($dirArray);
    // echo ("</pre>");
    return $dirArray;
}

function printArray($array, $title)
{
    echo("<pre>");
    echo $title . ":\n";
    print_r($array);
    echo("</pre>");
}

function prettyPrint($text)
{
    echo("<pre>");
    echo "\n" . $text . "\n";
    echo("</pre>");
}
