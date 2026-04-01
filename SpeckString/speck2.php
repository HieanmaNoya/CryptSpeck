<?php
include __DIR__ . "/roundSpeck.php";

$cipher = new Speck64();

$keyHex = "7061706572636c60";
$plain = "Привет Мир!";
echo "Original: $plain\n";

$encrypted = $cipher->encryptString($plain, $keyHex);
echo "Шифрование (hex): " . bin2hex($encrypted) . "\n";

$decrypted = $cipher->decryptString($encrypted, $keyHex);
echo "Дешифровка: $decrypted\n";

if ($plain === $decrypted) {
    echo "Успех: шифровка и дешифровка выполненны успешно.\n";
} else {
    echo "Ошибка: Алгоритм выполнен неверно.\n";
}