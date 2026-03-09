<?php
require 'vendor/autoload.php';
use phpseclib\Crypt\Hash;
// Хэширование
$hash = new Hash();
$message = 'Крипта';
$message2 = 'Крипта.';
$hash->setHash('mdc2');
$result = $hash->hash($message);
$result2 = $hash->hash($message);
$result3 = $hash->hash($message2);
// Проверка: Одинаковый ввод == одинаковый вывод
if($result === $result2) {
    echo "Всё верно" . "\n";
} else {
    echo "Не верно" . "\n";
}

echo bin2hex($result) . "\n"; // Переводим в хекс и выводим результат
// проверка на "лавинный" эффект
// Пока не придумал как их сравнить ввиде кода, но по логике
// Одно малое изменение должно существенно менять Хэш
echo bin2hex($result3);
