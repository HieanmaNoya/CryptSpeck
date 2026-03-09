<?php
$algos = openssl_get_md_methods();

$message = 'Крипта';
$message2 = 'Крипта.';

// Вычисляем хэши в бинарном виде
$hash1 = openssl_digest($message, 'mdc2', true);
$hash2 = openssl_digest($message, 'mdc2', true); // такое же сообщение
$hash3 = openssl_digest($message2, 'mdc2', true);

// Проверка одинаковых сообщений
if ($hash1 === $hash2) {
    echo "Всё верно" . "\n";
} else {
    echo "Не верно" . "\n";
}

// Выводим первый хэш в hex
echo bin2hex($hash1) . "\n";

// Выводим хэш второго сообщения (с точкой) в hex
echo bin2hex($hash3) . "\n";

