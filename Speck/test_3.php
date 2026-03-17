<?php
include __DIR__ . "/moveBytes.php";
$qwe = new moveBytes();
var_dump (decbin($qwe->rotateRight(15,3)) << 1 );

//01111
//11101

