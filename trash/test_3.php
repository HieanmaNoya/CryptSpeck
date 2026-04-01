<?php

class moveBytes
{
    #define ROR(x, r) ((x >> r) | (x << (64 - r)))
    #define ROL(x, r) ((x << r) | (x >> (64 - r)))
    function rotateLeft($value, $shift) {
        $bits = 64;
        $mask = (1 << $bits) - 1;
        return (($value << $shift) | ($value >> ($bits - $shift))) & $mask;
    }

    function rotateRight($value, $shift) {
        $bits = 64;
        $mask = (1 << $bits) - 1;
        return (($value >> $shift) | ($value << ($bits - $shift))) & $mask;
    }
}
$key = [0x7061706572636c69, 0x7073646f6e657273];

$qwe = new moveBytes();
var_dump(decbin($key[0])) . "\n";
printf(decbin($qwe->rotateRight($key[0],8)));

