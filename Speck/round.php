<?php
include __DIR__ . "/moveBytes.php";
const mR = 3;
const mL = 3;
$key = [0x3061706577896c69, 0x4012346f6e657273];
$pt1 = 0x7061706572636c67;
$pt2 = 0x7073646f6e657252;
//var_dump($key[0]);
//var_dump($key[1]);
//var_dump($pt1);
//var_dump($pt2);

function rounds($pt1, $pt2, $key)
{
    $mover = new moveBytes();
    $pt1 = gmp_init($pt1);
    $pt2 = gmp_init($pt2);
    $key = gmp_init($key);
    $pt1 = $mover->rotateRight($pt1, 8);
    $pt1 = gmp_init($pt2);
    $pt1 = ($pt1 ^ $key);
    var_dump($pt2);
    $pt2 = $mover->rotateLeft($pt2, 3);
    var_dump($pt2);
    $pt2 = ($pt2 ^ $pt1);
    var_dump($pt2);
    return $pt1;
}

echo rounds($pt1, $pt2, $key[0]);
