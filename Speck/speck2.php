<?php
include __DIR__ . "/roundSpeck.php";
$mover = new roundSpeck();
$key = [0x7061706572636c60, 0x7073646f6e657273];
$pt1 = [0x7061706572636c6];
$pt2 = [0x7073646f6e65725];

for ($i = 0; $i < 32; $i++) {
    echo "РАУНД: " . $i . "\n";
    $round = $mover->round($pt1[0], $pt2[0], $key[0]);
    $pt1[0] =  $round[0];
    $pt2[0] =  $round[1];
}

$binaryArray = array_map('decbin', $round);
print_r($binaryArray). "\n";