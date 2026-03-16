<?php
$key = 749274609814782;
$mR = 8;
$mL = 3;
function moveRight($key, $move)
{
    $key = $key >> $move;
    $key = decbin($key);
    return $key;
}
function moveLeft($key, $move)
{
    $key = $key << $move;
    $key = decbin($key);
    return $key;
}

echo moveRight($key, $mR);

