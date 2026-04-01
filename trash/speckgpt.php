<?php

class Speck128_128
{
    private const ROUNDS = 32;
    static function bytesToWords(array $bytes): array
    {
        $low = 0;
        $high = 0;
        for ($i = 0; $i < 4; $i++) {
            $low |= ($bytes[$i] & 0xFF) << ($i * 8);
        }
        for ($i = 4; $i < 8; $i++) {
            $high |= ($bytes[$i] & 0xFF) << (($i - 4) * 8);
        }
        return [$high & 0xFFFFFFFF, $low & 0xFFFFFFFF];
    }
    static function wordsToBytes(array $words): array
    {
        [$high, $low] = $words;
        $bytes = [];
        for ($i = 0; $i < 4; $i++) {
            $bytes[] = ($low >> ($i * 8)) & 0xFF;
        }
        for ($i = 0; $i < 4; $i++) {
            $bytes[] = ($high >> ($i * 8)) & 0xFF;
        }
        return $bytes;
    }
    static function add64(array $a, array $b): array
    {
        $lo = ($a[1] + $b[1]) & 0xFFFFFFFF;
        $carry = ($a[1] + $b[1] > 0xFFFFFFFF) ? 1 : 0;
        $hi = ($a[0] + $b[0] + $carry) & 0xFFFFFFFF;
        return [$hi, $lo];
    }
    static function sub64(array $a, array $b): array
    {
        $lo = ($a[1] - $b[1]) & 0xFFFFFFFF;
        $borrow = ($a[1] < $b[1]) ? 1 : 0;
        $hi = ($a[0] - $b[0] - $borrow) & 0xFFFFFFFF;
        return [$hi, $lo];
    }
    static function xor64(array $a, array $b): array
    {
        return [($a[0] ^ $b[0]) & 0xFFFFFFFF, ($a[1] ^ $b[1]) & 0xFFFFFFFF];
    }
    static function rotr64(array $x, int $shift): array
    {
        $shift %= 64;
        if ($shift == 0) return $x;

        $hi = $x[0];
        $lo = $x[1];

        if ($shift < 32) {
            $newLo = (($lo >> $shift) | (($hi & ((1 << $shift) - 1)) << (32 - $shift))) & 0xFFFFFFFF;
            $newHi = (($hi >> $shift) | (($lo & ((1 << $shift) - 1)) << (32 - $shift))) & 0xFFFFFFFF;
        } else {
            $shift2 = $shift - 32;
            $newLo = (($hi >> $shift2) | (($lo & ((1 << $shift2) - 1)) << (32 - $shift2))) & 0xFFFFFFFF;
            $newHi = (($lo >> $shift2) | (($hi & ((1 << $shift2) - 1)) << (32 - $shift2))) & 0xFFFFFFFF;
        }
        return [$newHi, $newLo];
    }
    static function rotl64(array $x, int $shift): array
    {
        $shift %= 64;
        if ($shift == 0) return $x;

        $hi = $x[0];
        $lo = $x[1];

        if ($shift < 32) {
            $newHi = (($hi << $shift) | (($lo >> (32 - $shift)) & ((1 << $shift) - 1))) & 0xFFFFFFFF;
            $newLo = (($lo << $shift) | (($hi >> (32 - $shift)) & ((1 << $shift) - 1))) & 0xFFFFFFFF;
        } else {
            $shift2 = $shift - 32;
            $newHi = (($lo << $shift2) | (($hi >> (32 - $shift2)) & ((1 << $shift2) - 1))) & 0xFFFFFFFF;
            $newLo = (($hi << $shift2) | (($lo >> (32 - $shift2)) & ((1 << $shift2) - 1))) & 0xFFFFFFFF;
        }
        return [$newHi, $newLo];
    }
    static function expandKey(array $k0, array $k1): array
    {
        $roundKeys = array_fill(0, self::ROUNDS, null);
        $roundKeys[0] = $k0;
        $roundKeys[1] = $k1;

        $a = $k0;
        $b = $k1;
        for ($i = 0; $i < self::ROUNDS - 2; $i++) {
            $rotr_b = self::rotr64($b, 8);
            $tmp = self::add64($a, $rotr_b);
            $tmpXor = self::xor64($tmp, [0, $i & 0xFFFFFFFF]);
            $b = self::xor64(self::rotl64($b, 3), $tmpXor);
            $a = $tmpXor;
            $roundKeys[$i + 2] = $a;
        }
        return $roundKeys;
    }

    static function encryptBlock(string $block, string $key): string
    {
        $blockBytes = array_values(unpack('C*', $block));
        $x = self::bytesToWords(array_slice($blockBytes, 0, 8));
        $y = self::bytesToWords(array_slice($blockBytes, 8, 8));

        $keyBytes = array_values(unpack('C*', $key));
        $k0 = self::bytesToWords(array_slice($keyBytes, 0, 8));
        $k1 = self::bytesToWords(array_slice($keyBytes, 8, 8));

        $roundKeys = self::expandKey($k0, $k1);

        for ($i = 0; $i < self::ROUNDS; $i++) {
            $rotr_x = self::rotr64($x, 8);
            $tmp = self::add64($rotr_x, $y);
            $x = self::xor64($tmp, $roundKeys[$i]);
            $rotl_y = self::rotl64($y, 3);
            $y = self::xor64($rotl_y, $x);
        }

        $resultBytes = array_merge(
            self::wordsToBytes($x),
            self::wordsToBytes($y)
        );
        return pack('C*', ...$resultBytes);
    }
    static function decryptBlock(string $block, string $key): string
    {
        $blockBytes = array_values(unpack('C*', $block));
        $x = self::bytesToWords(array_slice($blockBytes, 0, 8));
        $y = self::bytesToWords(array_slice($blockBytes, 8, 8));

        $keyBytes = array_values(unpack('C*', $key));
        $k0 = self::bytesToWords(array_slice($keyBytes, 0, 8));
        $k1 = self::bytesToWords(array_slice($keyBytes, 8, 8));

        $roundKeys = self::expandKey($k0, $k1);

        for ($i = self::ROUNDS - 1; $i >= 0; $i--) {
            $y = self::rotr64(self::xor64($y, $x), 3);
            $tmp = self::rotl64(self::xor64($x, $roundKeys[$i]), 8);
            $x = self::sub64($tmp, $y);
        }

        $resultBytes = array_merge(
            self::wordsToBytes($x),
            self::wordsToBytes($y)
        );
        return pack('C*', ...$resultBytes);
    }
}

$plaintext = "0123456789abcdef";
$key = "0123456789876421";

for ($i = 0; $i <= 32; $i++) {
    $ciphertext = Speck128_128::encryptBlock($plaintext, $key);
}

for ($i = 0; $i <= 32; $i++) {
    $decrypted = Speck128_128::decryptBlock($ciphertext, $key);
}

echo "Original: " . $plaintext . "\n";
echo "Encrypted: " . $ciphertext . "\n";
echo "Decrypted: " . $decrypted . "\n";
echo "Decrypted text: " . $decrypted . "\n";