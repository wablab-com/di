<?php

namespace WabLab\DI\Tests\Classes;

class ClassWithSimpleDependencies
{
    private \DateTime $dateTime;

    public function __construct(\DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    public function getValue() {
        return $this->dateTime;
    }
}