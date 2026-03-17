<?php
class moveBytes
{
    function rotateLeft($value, $shift) {
        $shift %= 3;
        return ($value << $shift) | ($value >> (3 - $shift));
    }
    function rotateRight($value, $shift) {
        $shift %= 8;
        return ($value >> $shift) | ($value >> (8 - $shift));
    }
}
