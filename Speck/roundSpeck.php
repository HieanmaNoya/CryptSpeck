<?php
class roundSpeck
{
    #define ROR(x, r) ((x >> r) | (x << (64 - r)))
    #define ROL(x, r) ((x << r) | (x >> (64 - r)))
    static function rotateLeft($value, $shift){
        $bits = 64;
        $mask = (1 << $bits) - 1;
        return (($value << $shift) | ($value >> ($bits - $shift))) & $mask;
    }
    static function rotateRight($value, $shift) {
        $bits = 64;
        $mask = (1 << $bits) - 1;
        return (($value >> $shift) | ($value << ($bits - $shift))) & $mask;
    }
    function round($pt1, $pt2, $key) : array
    {
        $pt1 = self::rotateRight($pt1, 8);
        echo "Перестановка битов вправо: " . decbin((int)$pt1) . "\n";
        $pt1 += $pt2;
        echo "Сложение открытого текста: " . decbin((int)$pt1) . "\n";
        $pt1 ^= $key;
        echo "Ксор открытого текста: " . decbin((int)$pt1) . "\n";
        $pt2 = self::rotateLeft($pt2, 3);
        echo "Перестановка битов влево: " . decbin((int)$pt2) . "\n";
        $pt2 ^= $pt1;
        echo "Ксор открытого текста: " . decbin((int)$pt2) . "\n" . "\n";
        return [(int)$pt1, (int)$pt2];
    }
}
