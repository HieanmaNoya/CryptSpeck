<?php
include __DIR__ . "/roundSpeck.php";
$mover = new moveBytes();
$rounds = new rounds();
const mR = 8;
const mL = 3;
$key = [0x7061706572636c69, 0x7073646f6e657273];
$pt1 = 0x7061706572636c67;
$pt2 = 0x7073646f6e657252;

echo "Ключ на входе: [" . $key[0] . " и " . $key[1] . "]\n" . "\n";
echo "Двоичное представление ключа[0]: " . decbin($key[0]) . "\n";
echo "Двоичное представление ключа[1]: " . decbin($key[1]) . "\n" . "\n";

$key[0] = $mover->rotateRight($key[0], mR);
$key[1] = $mover->rotateRight($key[1], mR);

echo "Смещение ключа вправо: [" . $key[0] . ", " . $key[1] . "]\n";
echo "Двоичное представление ключа[0]: " . decbin($key[0]) . "\n";
echo "Двоичное представление ключа[1]: " . decbin($key[1]) . "\n" . "\n";

$key[0] = $mover->rotateLeft($key[0], mL);
$key[1] = $mover->rotateLeft($key[1], mL);

echo "Смещение ключа влево: [" . $key[0] . ", " . $key[1] . "]\n";
echo "Двоичное представление ключа[0]: " . decbin($key[0]) . "\n";
echo "Двоичное представление ключа[1]: " . decbin($key[1]) . "\n" . "\n";

echo "Двоичное представление pt1: " . decbin($pt1) . "\n";
echo "Байтовая длина слова: " . PHP_INT_SIZE . "\n";
echo "Битовая длина слова: " . (PHP_INT_SIZE * 8) . "\n" . "\n";

//echo "Результат одного раунда" . $rounds->round($pt1) . "\n";