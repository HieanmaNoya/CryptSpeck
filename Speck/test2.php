<?php
$value = 15;
$n = 3;
echo "Битовое значение переменной: " . decbin($value) . "\n";
$mask = (1 << $n) - 1;
$dropped = $value & $mask;
$value2 = $value >> $n;
echo "Битовое значение переменной после переноса: " . decbin($value2) . "\n" . "Число после переноса: " . $value2 . "\n";
echo "Отсечённые биты:  " . decbin($dropped) . "\n" . "Отсечённое число: " . $dropped . "\n";
$x = decbin($dropped) . decbin($value2);
echo "Сложенные биты: " . $x . "\n" . "Получившееся число: " . bindec($x) . "\n";