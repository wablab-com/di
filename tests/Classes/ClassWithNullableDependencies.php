<?php

namespace WabLab\DI\Tests\Classes;

class ClassWithNullableDependencies
{

    private ?\DateTime $arg1;
    private ?string $arg2;

    public function __construct(?\DateTime $arg1 = null, ?string $arg2 = null)
    {
        $this->arg1 = $arg1;
        $this->arg2 = $arg2;
    }

    public function getArg1() {
        return $this->arg1;
    }

    public function getArg2() {
        return $this->arg2;
    }
}