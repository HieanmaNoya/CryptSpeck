<?php

class Speck64
{
    private const MODULUS = "18446744073709551616";
    private static function rotateRight(string $value, int $shift): string
    {
        $binary = self::bcToBin($value);
        $shift %= 64;
        $rotated = substr($binary, -$shift) . substr($binary, 0, -$shift);
        return self::binToBC($rotated);
    }
    private static function rotateLeft(string $value, int $shift): string
    {
        $binary = self::bcToBin($value);
        $shift %= 64;
        $rotated = substr($binary, $shift) . substr($binary, 0, $shift);
        return self::binToBC($rotated);
    }
    private static function bcToBin(string $number): string
    {
        $binary = '';
        $number = bcmod($number, self::MODULUS);
        for ($i = 0; $i < 64; $i++) {
            $binary = bcmod($number, '2') . $binary;
            $number = bcdiv($number, '2', 0);
        }
        return $binary;
    }
    private static function binToBC(string $binary): string
    {
        $number = '0';
        $length = strlen($binary);
        for ($i = 0; $i < $length; $i++) {
            $number = bcmul($number, '2');
            if ($binary[$i] === '1') {
                $number = bcadd($number, '1');
            }
        }
        return $number;
    }
    private static function add(string $a, string $b): string
    {
        $sum = bcadd($a, $b);
        return bcmod($sum, self::MODULUS);
    }
    private static function subtract(string $a, string $b): string
    {
        $difference = bcsub($a, $b);
        if (bccomp($difference, '0') < 0) {
            $difference = bcadd($difference, self::MODULUS);
        }
        return $difference;
    }
    private static function xor64(string $a, string $b): string
    {
        $binA = self::bcToBin($a);
        $binB = self::bcToBin($b);
        $result = '';
        for ($i = 0; $i < 64; $i++) {
            $result .= ($binA[$i] === $binB[$i]) ? '0' : '1';
        }
        return self::binToBC($result);
    }
    public static function formatNumber(string $number): string
    {
        $hex = '';
        $temp = $number;
        for ($i = 0; $i < 16; $i++) {
            $byte = bcmod($temp, '16');
            $hex = dechex((int)$byte) . $hex;
            $temp = bcdiv($temp, '16', 0);
        }
        return '0x' . str_pad($hex, 16, '0', STR_PAD_LEFT);
    }
    public function encryptionRound(string $left, string $right, string $key): array
    {
        $left = self::rotateRight($left, 8);
        $left = self::add($left, $right);
        $left = self::xor64($left, $key);
        $right = self::rotateLeft($right, 3);
        $right = self::xor64($right, $left);
        return [$left, $right];
    }
    public function decryptionRound(string $left, string $right, string $key): array
    {
        $rightAfterXor = self::xor64($right, $left);
        $rightOriginal = self::rotateRight($rightAfterXor, 3);
        $leftAfterXor = self::xor64($left, $key);
        $leftBeforeAdd = self::subtract($leftAfterXor, $rightOriginal);
        $leftOriginal = self::rotateLeft($leftBeforeAdd, 8);
        return [$leftOriginal, $rightOriginal];
    }
    public function encryptBlock(string $plainLeft, string $plainRight, string $masterKey): array
    {
        $left = $plainLeft;
        $right = $plainRight;
        for ($round = 0; $round < 32; $round++) {
            [$left, $right] = $this->encryptionRound($left, $right, $masterKey);
        }
        return [$left, $right];
    }
    public function decryptBlock(string $cipherLeft, string $cipherRight, string $masterKey): array
    {
        $left = $cipherLeft;
        $right = $cipherRight;
        for ($round = 0; $round < 32; $round++) {
            [$left, $right] = $this->decryptionRound($left, $right, $masterKey);
        }
        return [$left, $right];
    }
    public function encryptNumbers(array $numbers, string $masterKey): array
    {
        $count = count($numbers);
        if ($count % 2 !== 0) {
            throw new Exception('Количество чисел должно быть чётным');
        }

        $encrypted = [];
        for ($i = 0; $i < $count; $i += 2) {
            [$encLeft, $encRight] = $this->encryptBlock(
                (string)$numbers[$i],
                (string)$numbers[$i + 1],
                $masterKey
            );
            $encrypted[] = $encLeft;
            $encrypted[] = $encRight;
        }

        return $encrypted;
    }
    public function decryptNumbers(array $numbers, string $masterKey): array
    {
        $count = count($numbers);
        if ($count % 2 !== 0) {
            throw new Exception('Количество чисел должно быть чётным');
        }
        $decrypted = [];
        for ($i = 0; $i < $count; $i += 2) {
            [$decLeft, $decRight] = $this->decryptBlock(
                (string)$numbers[$i],
                (string)$numbers[$i + 1],
                $masterKey
            );
            $decrypted[] = $decLeft;
            $decrypted[] = $decRight;
        }

        return $decrypted;
    }
}
$cipher = new Speck64();
$masterKey = "8097877186001988704"; // 0x7061706572636c60
$originalNumbers = [
    "506117324125124294",   // 0x07061706572636C6
    "506433161520371493",   // 0x07073646F6E65725
    "1234567890123456789",  // 0x112210F47DE98115
    "9876543210987654321"   // 0x891087B8E3B71000 (больше PHP_INT_MAX)
];

echo "Исходные числа:\n";
foreach ($originalNumbers as $index => $number) {
    echo "  [$index] " . Speck64::formatNumber($number) . " ($number)\n";
}
echo "\n";
$encryptedNumbers = $cipher->encryptNumbers($originalNumbers, $masterKey);

echo "Зашифрованные числа:\n";
foreach ($encryptedNumbers as $index => $number) {
    echo "  [$index] " . Speck64::formatNumber($number) . " ($number)\n";
}
echo "\n";
$decryptedNumbers = $cipher->decryptNumbers($encryptedNumbers, $masterKey);
echo "Расшифрованные числа:\n";
foreach ($decryptedNumbers as $index => $number) {
    echo "  [$index] " . Speck64::formatNumber($number) . " ($number)\n";
}
echo "\n";
if ($originalNumbers === $decryptedNumbers) {
    echo "УСПЕХ: Расшифрованные числа совпадают с исходными!\n";
} else {
    echo "ОШИБКА: Расшифрованные числа не совпадают с исходными!\n";
}