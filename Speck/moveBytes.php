<?php
class moveBytes
{
    #define ROR(x, r) ((x >> r) | (x << (64 - r)))
    #define ROL(x, r) ((x << r) | (x >> (64 - r)))
    function rotateLeft($value, $shift){
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
