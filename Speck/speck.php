<?php
include __DIR__ . "/moveBytes.php";

echo "Ключ на входе: " . $key = 10000000 . "\n";
echo "Двоичное представление ключа: " . decbin($key) . "\n";
$mover = new moveBytes();
$mR = 8;
$mL = 3;

$key = $mover->moveRight($key, $mR);
echo "Смещение ключа вправо после : " . $key . "\n";
echo "Двоичное представление ключа: " . decbin($key) . "\n";

$key = $mover->moveLeft($key, $mL);
echo "Смещение ключа влево: " . $key . "\n";
echo "Двоичное представление ключа: " . decbin($key) . "\n";


