<?php
class moveBytes
{
    private $bits;
    public function __construct($bits = 128)
    {
        $this->bits = $bits;
    }
    public function moveRight($key, $move)
    {
        return $key >> $move;
    }
    public function moveLeft($key, $move)
    {
        return $key << $move;
    }
}
