<?php
include __DIR__ . "/moveBytes.php";
const mR = 8;
const mL = 3;
$key = [123456789010, 12345678910];
$pt1 = 0x7061706572636c6;
$pt2 = 0x7073646f6e65725;
//var_dump($key[0]);
//var_dump($key[1]);
//var_dump($pt1);
//var_dump($pt2);

function rounds($pt1, $pt2, $key)
{
    $mover = new moveBytes();
    var_dump($pt1);
    $pt1 = $mover->rotateRight($pt1, 8);
    var_dump($pt1);
    $pt1 = $pt1 & $pt2;
    var_dump($pt1);
    $pt1 ^= $key;
    var_dump($pt1);
    $pt2 = $mover->rotateLeft($pt2, 3);
    $pt2 ^= $pt1;
    return $pt1;
}

echo rounds($pt1, $pt2, $key[0]);
