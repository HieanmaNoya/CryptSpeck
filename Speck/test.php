<?php
$value = 14;
$n = 3;
$result2 = 181;

$mask = (1 << $n) -1;
echo $result2 . "\n";
echo decbin($result2) . "\n";
$result2 = $result2 >> $n;
echo $result2 . "\n";
echo decbin($result2) . "\n";
$drop = $result2 & $mask;
echo decbin($drop) . "\n";