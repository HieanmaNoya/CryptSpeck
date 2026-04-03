<?php
class Speck128
{
    const mod = "18446744073709551616";

    static function bcToBin(string $number): string
    {
        $binary = '';
        $number = bcmod($number, self::mod);
        for ($i = 0; $i < 64; $i++) {
            $binary = bcmod($number, '2') . $binary;
            $number = bcdiv($number, '2', 0);
        }
        return $binary;
    }

    static function binToBC(string $binary): string
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

    static function rotateRight(string $value, int $shift): string
    {
        $binary = self::bcToBin($value);
        $shift %= 64;
        $rotated = substr($binary, -$shift) . substr($binary, 0, -$shift);
        return self::binToBC($rotated);
    }

    static function rotateLeft(string $value, int $shift): string
    {
        $binary = self::bcToBin($value);
        $shift %= 64;
        $rotated = substr($binary, $shift) . substr($binary, 0, $shift);
        return self::binToBC($rotated);
    }

    static function add(string $a, string $b): string
    {
        $sum = bcadd($a, $b);
        return bcmod($sum, self::mod);
    }

    static function sub(string $a, string $b): string
    {
        $difference = bcsub($a, $b);
        if (bccomp($difference, '0') < 0) {
            $difference = bcadd($difference, self::mod);
        }
        return $difference;
    }

    static function xor64(string $a, string $b): string
    {
        $binA = self::bcToBin($a);
        $binB = self::bcToBin($b);
        $result = '';
        for ($i = 0; $i < 64; $i++) {
            $result .= ($binA[$i] === $binB[$i]) ? '0' : '1';
        }
        return self::binToBC($result);
    }

    // SPECK 128/128 раундовая функция с двумя ключами
    function encryptionRound(string $left, string $right, string $key1, string $key2): array
    {
        $left = self::rotateRight($left, 8);
        $left = self::add($left, $right);
        $left = self::xor64($left, $key1);
        $right = self::rotateLeft($right, 3);
        $right = self::xor64($right, $left);
        return [$left, $right, $key2]; // возвращаем обновленные ключи
    }

    function decryptionRound(string $left, string $right, string $key1, string $key2): array
    {
        $rightXor = self::xor64($right, $left);
        $right = self::rotateRight($rightXor, 3);
        $leftXor = self::xor64($left, $key1);
        $leftAdd = self::sub($leftXor, $right);
        $left = self::rotateLeft($leftAdd, 8);
        return [$left, $right, $key2];
    }

    // Генерация раундовых ключей для SPECK 128/128
    function keySchedule(string $keyLeft, string $keyRight): array
    {
        $roundKeys = [];
        $l = $keyRight;
        $k = $keyLeft;

        for ($i = 0; $i < 32; $i++) {
            $roundKeys[] = $k;
            $l = self::rotateRight($l, 8);
            $l = self::add($l, $k);
            $l = self::xor64($l, (string)$i);
            $k = self::rotateLeft($k, 3);
            $k = self::xor64($k, $l);
        }

        return $roundKeys;
    }

    function encryptBlock(string $plainLeft, string $plainRight, string $keyLeft, string $keyRight): array
    {
        $left = $plainLeft;
        $right = $plainRight;
        $roundKeys = $this->keySchedule($keyLeft, $keyRight);

        for ($round = 0; $round < 32; $round++) {
            $left = self::rotateRight($left, 8);
            $left = self::add($left, $right);
            $left = self::xor64($left, $roundKeys[$round]);
            $right = self::rotateLeft($right, 3);
            $right = self::xor64($right, $left);
        }

        return [$left, $right];
    }

    function decryptBlock(string $cipherLeft, string $cipherRight, string $keyLeft, string $keyRight): array
    {
        $left = $cipherLeft;
        $right = $cipherRight;
        $roundKeys = $this->keySchedule($keyLeft, $keyRight);
        $roundKeys = array_reverse($roundKeys);

        for ($round = 0; $round < 32; $round++) {
            $right = self::xor64($right, $left);
            $right = self::rotateRight($right, 3);
            $left = self::xor64($left, $roundKeys[$round]);
            $left = self::sub($left, $right);
            $left = self::rotateLeft($left, 8);
        }

        return [$left, $right];
    }

    static function iv($seed) {
        $rng = self::lcg($seed);
        return [(string)$rng(), (string)$rng()];
    }

    static function lcg($seed) {
        $state = $seed;
        return function() use (&$state) {
            $state = (1103515245 * $state + 12345) % (1 << 31);
            return $state;
        };
    }

    function encryptNumbersCBC(array $numbers, string $keyLeft, string $keyRight, $ivSeed) {
        [$ivLeft, $ivRight] = self::iv($ivSeed);
        $prevLeft = $ivLeft;
        $prevRight = $ivRight;
        $encrypted = [];

        for ($i = 0; $i < count($numbers); $i += 2) {
            $left = self::xor64($numbers[$i], $prevLeft);
            $right = self::xor64($numbers[$i+1], $prevRight);
            [$encLeft, $encRight] = $this->encryptBlock($left, $right, $keyLeft, $keyRight);
            $encrypted[] = $encLeft;
            $encrypted[] = $encRight;
            $prevLeft = $encLeft;
            $prevRight = $encRight;
        }

        return $encrypted;
    }

    function decryptNumbersCBC(array $numbers, string $keyLeft, string $keyRight, $ivSeed) {
        [$ivLeft, $ivRight] = self::iv($ivSeed);
        $prevLeft = $ivLeft;
        $prevRight = $ivRight;
        $decrypted = [];

        for ($i = 0; $i < count($numbers); $i += 2) {
            [$decLeft, $decRight] = $this->decryptBlock($numbers[$i], $numbers[$i+1], $keyLeft, $keyRight);
            $plainLeft = self::xor64($decLeft, $prevLeft);
            $plainRight = self::xor64($decRight, $prevRight);
            $decrypted[] = $plainLeft;
            $decrypted[] = $plainRight;
            $prevLeft = $numbers[$i];
            $prevRight = $numbers[$i+1];
        }

        return $decrypted;
    }
    static function hex2bc(string $hex): string {
        $dec = '0';
        for ($i = 0; $i < strlen($hex); $i++) {
            $dec = bcadd(bcmul($dec, '16'), (string)hexdec($hex[$i]));
        }
        return $dec;
    }
    function mdc2(array $data): array {
        $G0_left  = self::hex2bc("5252525252525252");
        $G0_right = self::hex2bc("5252525252525252");

        $H0_left  = self::hex2bc("2525252525252525");
        $H0_right = self::hex2bc("2525252525252525");

        $G_left = $G0_left;
        $G_right = $G0_right;
        $H_left = $H0_left;
        $H_right = $H0_right;

        for ($i = 0; $i < count($data); $i += 2) {
            $M_left  = $data[$i];
            $M_right = $data[$i+1] ?? "0";

            [$encG_left, $encG_right] = self::encryptBlock($G_left, $G_right, $M_left, $M_right);
            [$encH_left, $encH_right] = self::encryptBlock($H_left, $H_right, $M_left, $M_right);

            $newG_left = self::xor64($encG_left, $H_left);
            $newG_right = self::xor64($encG_right, $H_right);
            $newH_left = self::xor64($encH_left, $G_left);
            $newH_right = self::xor64($encH_right, $G_right);

            $G_left = $newG_left;
            $G_right = $newG_right;
            $H_left = $newH_left;
            $H_right = $newH_right;
        }
        return [$G_left, $G_right, $H_left, $H_right];
    }
}

$cipher = new Speck128();
$keyLeft = "8097877186001988704";
$keyRight = "1234567890123456789";
$ivSeed = 42;

$numbers = [
    "12345678910109876",
    "10987654321123456",
    "88005553535",
    "9876543210987654321"
];

echo "Исходные данные (64-битные числа CBC):\n";
foreach ($numbers as $i => $num) {
    echo "  [$i] $num\n";
}

$encrypted = $cipher->encryptNumbersCBC($numbers, $keyLeft, $keyRight, $ivSeed);
echo "\nЗашифрованные данные (CBC):\n";
foreach ($encrypted as $i => $num) {
    echo "  [$i] $num\n";
}

$decrypted = $cipher->decryptNumbersCBC($encrypted, $keyLeft, $keyRight, $ivSeed);
echo "\nРасшифрованные данные:\n";
foreach ($decrypted as $i => $num) {
    echo "  [$i] $num\n";
}

echo "\nРезультат: ";
if ($numbers === $decrypted) {
    echo "CBC работает корректно (данные совпадают)\n";
} else {
    echo "ОШИБКА: расшифрованные данные не совпадают с исходными\n";
}
$hash = $cipher->mdc2($numbers, $keyLeft, $keyRight);
echo "Хэш исходных: " . $hash[0] . $hash[1] . "\n";

$modified = $numbers;
$modified[2] = "99999999999";
$hashMod = $cipher->mdc2($modified, $keyLeft, $keyRight);
echo "Хэш изменённых: " . $hashMod[0] . $hashMod[1] . "\n";

echo "Хэши " . (($hash[0] === $hashMod[0] && $hash[1] === $hashMod[1]) ? "совпадают (ПЛОХО)" : "разные (ХОРОШО)") . "\n";