<?php
class Speck64
{
    private const MODULUS = "18446744073709551616";

    private static function eightByteStringToBcNumber(string $eightByteString): string
    {
        $eightByteString = substr($eightByteString, 0, 8);
        $bytes = unpack('C*', $eightByteString);
        $number = '0';
        foreach ($bytes as $byte) {
            $number = bcmul($number, '256');
            $number = bcadd($number, (string)$byte);
        }
        return $number;
    }

    private static function bcNumberToEightByteString(string $bcNumber): string
    {
        $bytes = '';
        for ($i = 0; $i < 8; $i++) {
            $byte = bcmod($bcNumber, '256');
            $bytes = chr((int)$byte) . $bytes;
            $bcNumber = bcdiv($bcNumber, '256', 0);
        }
        return $bytes;
    }

    private static function bcNumberToBinaryString(string $bcNumber): string
    {
        $binary = '';
        $bcNumber = bcmod($bcNumber, self::MODULUS);
        for ($i = 0; $i < 64; $i++) {
            $binary = bcmod($bcNumber, '2') . $binary;
            $bcNumber = bcdiv($bcNumber, '2', 0);
        }
        return $binary;
    }

    private static function binaryStringToBcNumber(string $binaryString): string
    {
        $number = '0';
        $length = strlen($binaryString);
        for ($i = 0; $i < $length; $i++) {
            $number = bcmul($number, '2');
            if ($binaryString[$i] === '1') {
                $number = bcadd($number, '1');
            }
        }
        return $number;
    }

    private static function moduloAdd(string $addend1, string $addend2): string
    {
        $sum = bcadd($addend1, $addend2);
        return bcmod($sum, self::MODULUS);
    }

    private static function moduloSubtract(string $minuend, string $subtrahend): string
    {
        $difference = bcsub($minuend, $subtrahend);
        if (bccomp($difference, '0') < 0) {
            $difference = bcadd($difference, self::MODULUS);
        }
        return $difference;
    }

    private static function xor64(string $firstNumber, string $secondNumber): string
    {
        $firstBinary = self::bcNumberToBinaryString($firstNumber);
        $secondBinary = self::bcNumberToBinaryString($secondNumber);
        $resultBinary = '';
        for ($i = 0; $i < 64; $i++) {
            $resultBinary .= ($firstBinary[$i] === $secondBinary[$i]) ? '0' : '1';
        }
        return self::binaryStringToBcNumber($resultBinary);
    }

    private static function rotateRight(string $bcNumber, int $shift): string
    {
        $binary = self::bcNumberToBinaryString($bcNumber);
        $shift %= 64;
        $rotated = substr($binary, -$shift) . substr($binary, 0, -$shift);
        return self::binaryStringToBcNumber($rotated);
    }

    private static function rotateLeft(string $bcNumber, int $shift): string
    {
        $binary = self::bcNumberToBinaryString($bcNumber);
        $shift %= 64;
        $rotated = substr($binary, $shift) . substr($binary, 0, $shift);
        return self::binaryStringToBcNumber($rotated);
    }

    public function encryptionRound(string $leftWord, string $rightWord, string $roundKey): array
    {
        $leftWord = self::rotateRight($leftWord, 8);
        $leftWord = self::moduloAdd($leftWord, $rightWord);
        $leftWord = self::xor64($leftWord, $roundKey);
        $rightWord = self::rotateLeft($rightWord, 3);
        $rightWord = self::xor64($rightWord, $leftWord);
        return [$leftWord, $rightWord];
    }

    public function decryptionRound(string $cipherLeft, string $cipherRight, string $roundKey): array
    {
        $rightWordAfterXor = self::xor64($cipherRight, $cipherLeft);
        $rightWordOriginal = self::rotateRight($rightWordAfterXor, 3);
        $leftWordAfterXor = self::xor64($cipherLeft, $roundKey);
        $leftWordBeforeAdd = self::moduloSubtract($leftWordAfterXor, $rightWordOriginal);
        $leftWordOriginal = self::rotateLeft($leftWordBeforeAdd, 8);
        return [$leftWordOriginal, $rightWordOriginal];
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

    public function encryptString(string $plaintext, string $masterKeyHex): string
    {
        $masterKey = self::eightByteStringToBcNumber(hex2bin($masterKeyHex));
        $plaintextLength = strlen($plaintext);
        $paddedPlaintext = str_pad($plaintext, ceil($plaintextLength / 16) * 16, "\0");
        $blocks = str_split($paddedPlaintext, 16);
        $ciphertext = '';

        foreach ($blocks as $block) {
            $leftWordString = substr($block, 0, 8);
            $rightWordString = substr($block, 8, 8);
            $leftWordNumber = self::eightByteStringToBcNumber($leftWordString);
            $rightWordNumber = self::eightByteStringToBcNumber($rightWordString);
            [$encryptedLeft, $encryptedRight] = $this->encryptBlock($leftWordNumber, $rightWordNumber, $masterKey);
            $ciphertext .= self::bcNumberToEightByteString($encryptedLeft) . self::bcNumberToEightByteString($encryptedRight);
        }

        return $ciphertext;
    }

    public function decryptString(string $ciphertext, string $masterKeyHex): string
    {
        $ciphertextLength = strlen($ciphertext);
        if ($ciphertextLength % 16 !== 0) {
            throw new Exception('Ciphertext length must be multiple of 16 bytes');
        }

        $masterKey = self::eightByteStringToBcNumber(hex2bin($masterKeyHex));
        $blocks = str_split($ciphertext, 16);
        $plaintext = '';

        foreach ($blocks as $block) {
            $leftWordString = substr($block, 0, 8);
            $rightWordString = substr($block, 8, 8);
            $leftWordNumber = self::eightByteStringToBcNumber($leftWordString);
            $rightWordNumber = self::eightByteStringToBcNumber($rightWordString);
            [$decryptedLeft, $decryptedRight] = $this->decryptBlock($leftWordNumber, $rightWordNumber, $masterKey);
            $plaintext .= self::bcNumberToEightByteString($decryptedLeft) . self::bcNumberToEightByteString($decryptedRight);
        }

        return rtrim($plaintext, "\0");
    }
}