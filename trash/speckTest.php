<?php
function rotr64($x, $n) {
    return ($x >> $n) | ($x << (64 - $n));
}
function rotl64($x, $n) {
    return ($x << $n) | ($x >> (64 - $n));
}
function speckRound(&$y, &$x, $k) {
    $y = rotr64($y, 8);
    $y = ($y + $x);

    $y = $y ^ $k;
    $x = rotl64($x, 3);

    $x = $x ^ $y;
}

$y = 0x7061706572636c69;
$x = 0x7073646f6e657273;
$k = 0x1918111009080100;

speckRound($y, $x, $k);

printf("Результат раунда: Y=%x, X=%x\n", $y, $x);
// echo "Результат раунда: Y= " . $y . "\n" . "Результат раунда X= " . $x . "\n";
