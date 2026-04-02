<?php
function secureRandom($length = 32) {
    $chars = '012345678910';
    $result = '';
    $maxIndex = strlen($chars) - 1;

    for ($i = 0; $i < $length; $i++) {
        $result .= $chars[random_int(0, $maxIndex)];
    }

    return $result;
}

echo secureRandom(16);