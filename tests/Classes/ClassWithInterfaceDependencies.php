<?php

namespace WabLab\DI\Tests\Classes;

class ClassWithInterfaceDependencies
{

    private \Throwable $throwable;

    public function __construct(\Throwable $throwable)
    {
        $this->throwable = $throwable;
    }

    public function getValue() {
        return $this->throwable;
    }
}