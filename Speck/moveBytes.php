<?php
class moveBytes
{
    #define ROR(x, r) ((x >> r) | (x << (64 - r)))
    #define ROL(x, r) ((x << r) | (x >> (64 - r)))
    function rotateLeft($value, $shift) {
        return ($value << $shift) | ($value >> (5 - $shift));
    }
    function rotateRight($value, $shift) {
        return ($value >> $shift) | ($value << (5 - $shift));
    }
}
