<?php
$val = 14;
$places = 3;
$res = $val << $places;
p($res, $val, '>>', $places, 'слева вставилась копия знакового бита');


function p($res, $val, $op, $places, $note = '')
{
    $format = '%0' . (PHP_INT_SIZE * 8) . "b\n";

    printf("Выражение: %d = %d %s %d\n", $res, $val, $op, $places);

    echo " Десятичный вид:\n";
    printf(" val=%d\n", $val);
    printf(" res=%d\n", $res);

    echo " Двоичный вид:\n";
    printf(' val=' . $format, $val);
    printf(' res=' . $format, $res);

    if ($note) {
        echo " ЗАМЕЧАНИЕ: $note\n";
    }

    echo "\n\n";
}