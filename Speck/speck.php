<?php
include __DIR__ . "/moveBytes.php";
const mR = 8;
//1001000
//11000
const mL = 3;
$pt1 = 0x7061706572636c69;
$pt2 = 0x7073646f6e657273;
echo "Ключ на входе: " . $key = 1000 . "\n";
echo "Двоичное представление ключа: " . decbin($key) . "\n";

$mover = new moveBytes();
$key = $mover->rotateRight($key, mR);
echo "Смещение ключа вправо: " . $key . "\n";
echo "Двоичное представление ключа: " . decbin($key) . "\n";

$key = $mover->rotateLeft($key, mL);
echo "Смещение ключа влево: " . $key . "\n";
echo "Двоичное представление ключа: " . decbin($key) . "\n";
echo "Двоичное представление слова: " . decbin($pt1) . "\n";
echo "Байтовая длинная слова: " . $pt1 = PHP_INT_SIZE. "\n";
echo "Битовая длинная слова: " . $pt1 = PHP_INT_SIZE * 8  . "\n";


