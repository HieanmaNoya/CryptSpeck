<?php
include __DIR__ . "/roundSpeck.php";
const mR = 8;
const mL = 3;
$key = [123456789010, 12345678910];
$pt1 = 654321987;
$pt2 = 9876543321;
//var_dump($key[0]);
//var_dump($key[1]);
//var_dump($pt1);
//var_dump($pt2);

function rounds($pt1, $pt2, $key)
{
    $mover = new moveBytes();
    echo decbin($pt1) . "\n";
    $pt1 = $mover->rotateRight($pt1, 8);
    echo decbin($pt1) . "\n";
    $pt1 = $pt1 & $pt2;
    echo decbin($pt1) . "\n";
    $pt1 ^= $key;
    echo decbin($pt1) . "\n";
    $pt2 = $mover->rotateLeft($pt2, 3);
    $pt2 ^= $pt1;
    return $pt1;
}

echo rounds($pt1, $pt2, $key[0]);



