<?php
class LCG {
    private $state;
    public function __construct($seed, $a = 1103515245, $c = 12345, $m = 1 << 31) {
        $this->state = $seed;
        $this->a = $a;
        $this->c = $c;
        $this->m = $m;
    }
    public function next() {
        $this->state = ($this->a * $this->state + $this->c) % $this->m;
        return $this->state;
    }
}